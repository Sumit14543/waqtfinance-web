<?php
declare(strict_types=1);

$pageTitle = 'Business & Personal Applications';
$pageSubtitle = 'Manage Personal, Business, Vehicle, LAP, and Shopkeeper EDI loans';

include 'header.php';

$conn = db();

// Active tab selection
$activeTab = $_GET['tab'] ?? 'general';
$allowedTabs = ['general', 'edi'];
if (!in_array($activeTab, $allowedTabs, true)) {
    $activeTab = 'general';
}

// Pagination Config
$limit = 10;
$page = (int) ($_GET['page'] ?? 1);
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Filters
$search = trim((string) ($_GET['search'] ?? ''));
$city = trim((string) ($_GET['city'] ?? ''));
$category = trim((string) ($_GET['category'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));
$dateFrom = trim((string) ($_GET['date_from'] ?? ''));
$dateTo = trim((string) ($_GET['date_to'] ?? ''));
$amountMin = trim((string) ($_GET['amount_min'] ?? ''));
$amountMax = trim((string) ($_GET['amount_max'] ?? ''));

$validEdiStatuses = ['pending', 'submitted', 'approved', 'rejected'];
if ($activeTab !== 'edi' || !in_array($status, $validEdiStatuses, true)) {
    $status = '';
}

foreach (['dateFrom', 'dateTo'] as $dateVar) {
    if ($$dateVar !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $$dateVar)) {
        $$dateVar = '';
    }
}

foreach (['amountMin', 'amountMax'] as $amountVar) {
    if ($$amountVar !== '' && !is_numeric($$amountVar)) {
        $$amountVar = '';
    }
}

$baseQueryParams = [
    'tab' => $activeTab,
    'search' => $search,
    'city' => $city,
    'category' => $category,
    'status' => $status,
    'date_from' => $dateFrom,
    'date_to' => $dateTo,
    'amount_min' => $amountMin,
    'amount_max' => $amountMax,
];

function master_build_query(array $baseParams, array $overrides = []): string
{
    $query = array_merge($baseParams, $overrides);
    foreach ($query as $key => $value) {
        if ($value === '' || $value === null) {
            unset($query[$key]);
        }
    }

    return http_build_query($query);
}

function master_format_money(float $amount): string
{
    if ($amount >= 10000000) {
        return '₹' . number_format($amount / 10000000, 2) . ' Cr';
    }

    if ($amount >= 100000) {
        return '₹' . number_format($amount / 100000, 2) . ' L';
    }

    return '₹' . number_format($amount, 0);
}

function master_fetch_filter_options(mysqli $conn, string $table, string $column): array
{
    $allowedColumns = [
        'loan_applied_master' => ['city', 'loan_type'],
        'edi_loan_applications' => ['city', 'business_type', 'status'],
    ];

    if (!isset($allowedColumns[$table]) || !in_array($column, $allowedColumns[$table], true)) {
        return [];
    }

    $sql = "SELECT DISTINCT `$column` AS option_value FROM `$table` WHERE `$column` IS NOT NULL AND `$column` != '' ORDER BY `$column` ASC LIMIT 80";
    $result = $conn->query($sql);
    $options = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $options[] = (string) $row['option_value'];
        }
    }

    return $options;
}

function master_build_filters(string $tab, array $filters): array
{
    $conditions = [];
    $params = [];
    $types = '';
    $amountExpression = "CAST(REPLACE(COALESCE(loan_amount, '0'), ',', '') AS DECIMAL(12,2))";

    if ($filters['date_from'] !== '') {
        $conditions[] = 'created_at >= ?';
        $params[] = $filters['date_from'] . ' 00:00:00';
        $types .= 's';
    }

    if ($filters['date_to'] !== '') {
        $conditions[] = 'created_at <= ?';
        $params[] = $filters['date_to'] . ' 23:59:59';
        $types .= 's';
    }

    if ($filters['amount_min'] !== '') {
        $conditions[] = "$amountExpression >= ?";
        $params[] = (float) $filters['amount_min'];
        $types .= 'd';
    }

    if ($filters['amount_max'] !== '') {
        $conditions[] = "$amountExpression <= ?";
        $params[] = (float) $filters['amount_max'];
        $types .= 'd';
    }

    if ($filters['city'] !== '') {
        $conditions[] = 'city = ?';
        $params[] = $filters['city'];
        $types .= 's';
    }

    if ($tab === 'general') {
        if ($filters['category'] !== '') {
            $conditions[] = 'loan_type = ?';
            $params[] = $filters['category'];
            $types .= 's';
        }

        if ($filters['search'] !== '') {
            $conditions[] = '(application_id LIKE ? OR applicant_name LIKE ? OR mobile_number LIKE ? OR pancard_number LIKE ? OR city LIKE ? OR loan_type LIKE ?)';
            $searchWild = '%' . $filters['search'] . '%';
            array_push($params, $searchWild, $searchWild, $searchWild, $searchWild, $searchWild, $searchWild);
            $types .= 'ssssss';
        }
    } else {
        if ($filters['category'] !== '') {
            $conditions[] = 'business_type = ?';
            $params[] = $filters['category'];
            $types .= 's';
        }

        if ($filters['status'] !== '') {
            $conditions[] = 'status = ?';
            $params[] = $filters['status'];
            $types .= 's';
        }

        if ($filters['search'] !== '') {
            $conditions[] = '(application_id LIKE ? OR applicant_name LIKE ? OR mobile_number LIKE ? OR pan_number LIKE ? OR city LIKE ? OR shop_business_name LIKE ? OR pincode LIKE ? OR status LIKE ?)';
            $searchWild = '%' . $filters['search'] . '%';
            array_push($params, $searchWild, $searchWild, $searchWild, $searchWild, $searchWild, $searchWild, $searchWild, $searchWild);
            $types .= 'ssssssss';
        }
    }

    return [
        'where' => !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '',
        'params' => $params,
        'types' => $types,
    ];
}

$totalRecords = 0;
$generalLeads = [];
$ediLeads = [];

$tableName = $activeTab === 'general' ? 'loan_applied_master' : 'edi_loan_applications';
$categoryColumn = $activeTab === 'general' ? 'loan_type' : 'business_type';
$cityOptions = master_fetch_filter_options($conn, $tableName, 'city');
$categoryOptions = master_fetch_filter_options($conn, $tableName, $categoryColumn);
$statusOptions = $activeTab === 'edi'
    ? array_values(array_unique(array_merge($validEdiStatuses, master_fetch_filter_options($conn, $tableName, 'status'))))
    : [];

$statsRes = $conn->query("
    SELECT
        COUNT(*) AS total_records,
        SUM(CASE WHEN created_at >= CURDATE() AND created_at < DATE_ADD(CURDATE(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) AS today_records,
        SUM(CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND created_at < CURDATE() THEN 1 ELSE 0 END) AS yesterday_records,
        SUM(CASE WHEN created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN 1 ELSE 0 END) AS month_records,
        COALESCE(SUM(CAST(REPLACE(COALESCE(loan_amount, '0'), ',', '') AS DECIMAL(12,2))), 0) AS total_loan_value
    FROM `$tableName`
");
$stats = $statsRes ? ($statsRes->fetch_assoc() ?: []) : [];

$kpiCards = [
    [
        'label' => $activeTab === 'general' ? 'Total General Leads' : 'Total EDI Leads',
        'value' => number_format((int) ($stats['total_records'] ?? 0)),
        'sub' => $activeTab === 'general' ? 'Quick loan enquiries' : 'Shopkeeper applications',
        'icon' => $activeTab === 'general' ? 'fa-layer-group' : 'fa-store',
        'tone' => 'blue',
    ],
    [
        'label' => "Today's Leads",
        'value' => number_format((int) ($stats['today_records'] ?? 0)),
        'sub' => 'Yesterday: ' . number_format((int) ($stats['yesterday_records'] ?? 0)),
        'icon' => 'fa-calendar-day',
        'tone' => 'green',
    ],
    [
        'label' => 'This Month',
        'value' => number_format((int) ($stats['month_records'] ?? 0)),
        'sub' => date('M Y') . ' intake',
        'icon' => 'fa-chart-line',
        'tone' => 'yellow',
    ],
    [
        'label' => 'Loan Value',
        'value' => master_format_money((float) ($stats['total_loan_value'] ?? 0)),
        'sub' => 'Requested amount',
        'icon' => 'fa-indian-rupee-sign',
        'tone' => 'red',
    ],
];

$filtersActive = $search !== '' || $city !== '' || $category !== '' || $status !== '' || $dateFrom !== '' || $dateTo !== '' || $amountMin !== '' || $amountMax !== '';

$filterParts = master_build_filters($activeTab, $baseQueryParams);
$whereClause = $filterParts['where'];
$params = $filterParts['params'];
$types = $filterParts['types'];

$countStmt = $conn->prepare("SELECT COUNT(*) FROM `$tableName` $whereClause");
if ($types !== '') {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalRecords = (int) ($countStmt->get_result()->fetch_row()[0] ?? 0);

$fetchSql = "SELECT * FROM `$tableName` $whereClause ORDER BY id DESC LIMIT ? OFFSET ?";
$fetchStmt = $conn->prepare($fetchSql);
$fetchParams = $params;
$fetchTypes = $types . 'ii';
$fetchParams[] = $limit;
$fetchParams[] = $offset;
$fetchStmt->bind_param($fetchTypes, ...$fetchParams);
$fetchStmt->execute();

if ($activeTab === 'general') {
    $generalLeads = $fetchStmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $ediLeads = $fetchStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$totalPages = (int) ceil($totalRecords / $limit);
if ($totalPages < 1) $totalPages = 1;
?>

<style>
    /* ---------- Tabs (underline style) ---------- */
    .tab-nav {
        display: flex;
        gap: 4px;
        border-bottom: 1px solid var(--border-color);
        margin: -6px 0 22px;
        overflow-x: auto;
        scrollbar-width: none;
    }
    .tab-nav::-webkit-scrollbar { display: none; }
    .tab-btn {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px 12px;
        background: transparent;
        border: none;
        color: var(--text-muted);
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
        transition: color 0.15s var(--ease);
    }
    .tab-btn:hover { color: var(--text-main); }
    .tab-btn.active { color: var(--primary) !important; }
    .tab-btn.active::after {
        content: '';
        position: absolute;
        left: 8px;
        right: 8px;
        bottom: -1px;
        height: 2px;
        border-radius: 2px 2px 0 0;
        background: var(--primary);
    }

    /* ---------- KPI cards (compact) ---------- */
    .master-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin: 0 0 22px;
    }
    .master-kpi-card {
        display: flex;
        flex-direction: row-reverse;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 18px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        background: #ffffff;
        box-shadow: var(--shadow-xs);
        min-width: 0;
    }
    .master-kpi-icon {
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        font-size: 14px;
        background: var(--primary-soft);
        color: var(--primary);
    }
    .master-kpi-info { display: flex; flex-direction: column; min-width: 0; }
    .master-kpi-info h3 {
        order: 2;
        margin: 2px 0 0;
        font-family: 'Outfit', sans-serif;
        color: var(--text-main);
        font-size: 26px;
        line-height: 1.2;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }
    .master-kpi-info p {
        order: 1;
        margin: 0;
        color: var(--text-muted);
        font-size: 12.5px;
        font-weight: 600;
    }
    .master-kpi-info span {
        order: 3;
        display: block;
        margin-top: 2px;
        color: var(--text-muted);
        font-size: 12px;
    }

    /* ---------- Compact filter toolbar ---------- */
    .master-filter-panel {
        margin: -24px -24px 18px;
        padding: 14px 20px;
        border-bottom: 1px solid var(--border-color);
        background: var(--bg-subtle);
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    }
    .master-toolbar {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .master-filter-panel .filter-field { position: relative; min-width: 0; }
    /* Selects are self-describing ("All Cities"), so labels here are for screen readers only. */
    .master-filter-panel .filter-field label {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip: rect(0 0 0 0);
        white-space: nowrap;
    }
    .master-toolbar .fld-search { flex: 1 1 260px; }
    .master-toolbar .fld-search i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 12.5px;
        pointer-events: none;
    }
    .master-toolbar .fld-search input { padding-left: 34px; }
    .master-toolbar .filter-field:not(.fld-search) { flex: 0 1 170px; }

    .master-filter-panel input,
    .master-filter-panel select {
        width: 100%;
        height: 38px;
        padding: 0 11px;
        border: 1px solid var(--border-strong);
        border-radius: var(--radius-sm);
        background: #ffffff;
        color: var(--text-main);
        font-size: 13px;
        outline: none;
        transition: border-color 0.15s var(--ease), box-shadow 0.15s var(--ease);
    }
    .master-filter-panel select { cursor: pointer; }
    .master-filter-panel input:focus,
    .master-filter-panel select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-glow);
    }
    /* A filter that is set gets a tinted border so it is easy to spot. */
    .master-filter-panel select.has-value,
    .master-filter-panel input.has-value { border-color: #93b4dc; background: #f5f9ff; }

    .master-toolbar-actions { display: flex; align-items: center; gap: 8px; margin-left: auto; }

    .btn-outline-adm,
    .btn-export-adm,
    .btn-more-filters {
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 14px;
        border-radius: var(--radius-sm);
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
        transition: border-color 0.15s var(--ease), color 0.15s var(--ease), background-color 0.15s var(--ease);
    }
    .btn-outline-adm,
    .btn-more-filters {
        border: 1px solid var(--border-strong);
        background: #ffffff;
        color: var(--text-main);
    }
    .btn-outline-adm:hover,
    .btn-more-filters:hover,
    .btn-more-filters[aria-expanded="true"] { border-color: var(--primary); color: var(--primary); background: var(--primary-soft); }
    .btn-more-filters .more-count {
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        border-radius: 999px;
        background: var(--primary);
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        line-height: 18px;
        text-align: center;
    }
    .btn-more-filters .more-count[hidden] { display: none; }
    .btn-more-filters .chev { font-size: 10px; transition: transform 0.15s var(--ease); }
    .btn-more-filters[aria-expanded="true"] .chev { transform: rotate(180deg); }
    .btn-export-adm {
        border: 1px solid var(--border-strong);
        background: #ffffff;
        color: #15803d;
    }
    .btn-export-adm:hover { border-color: #16a34a; background: #f0fdf4; }

    /* Date + amount row, hidden until asked for */
    .master-advanced {
        display: flex;
        align-items: flex-end;
        gap: 12px 20px;
        flex-wrap: wrap;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px dashed var(--border-color);
    }
    .master-advanced[hidden] { display: none; }
    .master-advanced .adv-group { display: flex; flex-direction: column; gap: 5px; }
    .master-advanced .adv-label { font-size: 11.5px; font-weight: 600; color: var(--text-muted); }
    .master-advanced .adv-pair { display: flex; align-items: center; gap: 6px; }
    .master-advanced .adv-pair > span { color: var(--text-muted); font-size: 12px; }
    .master-advanced .filter-field { width: 150px; }
    .master-advanced .adv-quick { display: flex; gap: 6px; flex-wrap: wrap; }
    .master-advanced .adv-quick button {
        height: 38px;
        padding: 0 12px;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border-color);
        background: #ffffff;
        color: var(--text-secondary);
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
    }
    .master-advanced .adv-quick button:hover { border-color: var(--primary); color: var(--primary); }

    .master-filter-summary {
        margin-top: 10px;
        color: var(--text-muted);
        font-size: 12.5px;
        font-weight: 500;
    }

    @media (max-width: 1180px) {
        .master-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 720px) {
        .master-kpi-grid { grid-template-columns: minmax(0, 1fr); }
        .master-filter-panel { margin: -18px -18px 16px; padding: 12px 16px; }
        .master-toolbar .filter-field:not(.fld-search) { flex: 1 1 140px; }
        .btn-more-filters { flex: 1 1 140px; }
        .master-toolbar-actions { width: 100%; margin-left: 0; }
        .master-toolbar-actions > * { flex: 1; }
        .master-advanced .adv-group, .master-advanced .adv-pair { width: 100%; }
        .master-advanced .filter-field { width: auto; flex: 1; }
    }

    /* Details Drawer Modal */
    /* Backdrop fades in; only the panel slides. */
    .drawer-overlay {
        position: fixed;
        inset: 0;
        z-index: 1000;
        display: flex;
        justify-content: flex-end;
        visibility: hidden;
        opacity: 0;
        transition: opacity 0.25s ease, visibility 0s linear 0.25s;
    }
    .drawer-overlay.active {
        visibility: visible;
        opacity: 1;
        transition: opacity 0.25s ease, visibility 0s;
    }
    .drawer-content {
        width: 100%;
        max-width: 720px;
        height: 100%;
        background: var(--bg-dark);
        overflow-y: auto;
        overscroll-behavior: contain;
        box-shadow: -16px 0 40px rgba(15, 23, 42, 0.18);
        transform: translateX(100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
    }
    .drawer-overlay.active .drawer-content { transform: translateX(0); }
    .drawer-header {
        position: sticky;
        top: 0;
        z-index: 5;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        min-height: 56px;
        padding: 10px 16px 10px 24px;
        background: #ffffff;
        border-bottom: 1px solid var(--border-color);
    }
    .drawer-header .card-title-text { font-size: 14px; font-weight: 600; color: var(--text-muted); }
    .drawer-close {
        width: 36px;
        height: 36px;
        border-radius: 9px;
        background: none;
        border: none;
        color: var(--text-muted);
        font-size: 17px;
        cursor: pointer;
        transition: background-color 0.15s ease, color 0.15s ease;
    }
    .drawer-close:hover { background: #f1f5f9; color: var(--text-main); }
    #drawerBody { padding: 20px 24px 40px; flex: 1; }
    .section-title {
        font-family: 'Outfit', sans-serif;
        color: var(--primary);
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin: 25px 0 12px 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding-bottom: 6px;
    }
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    .detail-item {
        margin-bottom: 8px;
    }
    .detail-label {
        font-size: 11px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .detail-val {
        font-size: 13.5px;
        color: var(--text-main);
        margin-top: 2px;
        font-weight: 500;
    }
    .doc-preview-link {
        color: var(--primary);
        text-decoration: underline;
        font-weight: 600;
    }
    .doc-preview-link:hover {
        color: #ffc229;
    }
    .status-select-wrap {
        margin-top: 10px;
        display: flex;
        gap: 10px;
    }
    /* Pagination classes */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 8px;
        list-style: none;
        margin-top: 25px;
    }
    .page-item a, .page-item span {
        padding: 8px 14px;
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-muted);
        border-radius: 8px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.3s;
    }
    .page-item.active span, .page-item a:hover {
        color: #ffffff;
        background: var(--primary);
        border-color: var(--primary);
    }

    /* Premium Drawer styling elements */
    .info-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
    }
    .card-title {
        font-size: 13.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--primary);
        margin-bottom: 15px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-family: 'Outfit', sans-serif;
    }
    .grid-fields {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    .field-item {
        margin-bottom: 4px;
    }
    .field-label {
        font-size: 10.5px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    .field-value {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-main);
    }
    .doc-preview-link {
        color: var(--primary);
        text-decoration: none;
        font-weight: 700;
        transition: color 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
    }
    .doc-preview-link:hover {
        color: #ffc229;
    }

    /* Stepper and Checklist Drawer Styles */
    .checklist-table {
        width: 100%;
        border-collapse: collapse;
    }
    .checklist-table th, .checklist-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        text-align: left;
    }
    .checklist-table th {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-muted);
        background: #f8fafc;
    }
    .checklist-status-badge {
        font-size: 10px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 5px;
        text-transform: uppercase;
        display: inline-block;
    }
    .checklist-status-pending { background: #fef3c7; color: #d97706; }
    .checklist-status-verified { background: #dcfce7; color: #15803d; }
    .checklist-status-rejected { background: #fee2e2; color: #b91c1c; }
    
    .checklist-action-btn {
        padding: 4px 8px;
        font-size: 10.5px;
        font-weight: 700;
        border-radius: 5px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.2s;
    }
    .checklist-action-btn:hover {
        background: #f8fafc;
    }
    .checklist-btn-verify { border-color: #22c55e; color: #22c55e; }
    .checklist-btn-verify:hover { background: #dcfce7; }
    .checklist-btn-reject { border-color: #ef4444; color: #ef4444; }
    .checklist-btn-reject:hover { background: #fee2e2; }

    /* Timeline styles */
    .timeline-container {
        position: relative;
        padding-left: 20px;
        margin-top: 15px;
    }
    .timeline-container::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 6px;
        width: 2px;
        background: #e2e8f0;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 15px;
        padding-left: 10px;
    }
    .timeline-item-dot {
        position: absolute;
        left: -19px;
        top: 4px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--primary);
        border: 2px solid #ffffff;
        box-shadow: 0 0 0 3px rgba(24, 82, 156, 0.15);
    }
    .timeline-item-dot.dot-comment { background: #3b82f6; }
    .timeline-item-dot.dot-document { background: #ef4444; }
    .timeline-item-dot.dot-underwriting { background: #10b981; }
    .timeline-item-dot.dot-followup { background: #f59e0b; }
    
    .timeline-item-header {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        font-weight: 750;
        color: var(--text-muted);
        margin-bottom: 2px;
    }
    .timeline-item-actor {
        color: var(--text-main);
    }
    .timeline-item-desc {
        font-size: 12.5px;
        color: var(--text-main);
        line-height: 1.4;
        background: #f8fafc;
        padding: 8px 12px;
        border-radius: 6px;
        border: 1px solid #f1f5f9;
        margin-top: 2px;
    }

    /* Stepper timeline styles */
    .status-timeline {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        padding: 10px 0;
        margin-bottom: 20px;
    }
    .status-timeline::before {
        content: '';
        position: absolute;
        top: 26px;
        left: 5%;
        right: 5%;
        height: 3px;
        background: #f1f5f9;
        z-index: 1;
    }
    .timeline-progress-bar {
        position: absolute;
        top: 26px;
        left: 5%;
        height: 3px;
        background: var(--primary);
        z-index: 2;
        transition: width 0.4s ease;
    }
    .timeline-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 3;
        width: 80px;
    }
    .step-circle {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #ffffff;
        border: 2px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 800;
        color: var(--text-muted);
        transition: all 0.3s;
    }
    .timeline-step.completed .step-circle {
        background: var(--primary);
        border-color: var(--primary);
        color: #ffffff;
    }
    .timeline-step.active .step-circle {
        background: #ffffff;
        border-color: var(--primary);
        color: var(--primary);
        box-shadow: 0 0 0 4px rgba(24, 82, 156, 0.15);
    }
    .timeline-step.rejected .step-circle {
        background: var(--danger);
        border-color: var(--danger);
        color: #ffffff;
    }
    .step-label {
        font-size: 9px;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-top: 6px;
        letter-spacing: 0.5px;
    }
    .timeline-step.completed .step-label,
    .timeline-step.active .step-label {
        color: var(--text-main);
    }
    
    /* CIBIL Score Widget Gauge */
    .cibil-gauge-wrapper {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        border: 6px solid #f1f5f9;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        flex-shrink: 0;
    }
    .cibil-score-value {
        font-family: 'Outfit', sans-serif;
        font-size: 24px;
        font-weight: 800;
    }
    .cibil-score-value.score-excellent { color: #16a34a; }
    .cibil-score-value.score-good { color: #2563eb; }
    .cibil-score-value.score-fair { color: #ca8a04; }
    .cibil-score-value.score-poor { color: #dc2626; }
    .cibil-score-value.score-none { color: #9ca3af; }
    .cibil-score-label {
        font-size: 8px;
        font-weight: 750;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-top: 1px;
        letter-spacing: 0.5px;
    }
    
    /* Loading overlay */
    .drawer-loading {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 50px 0;
        color: var(--text-muted);
    }
    .drawer-spinner {
        width: 36px;
        height: 36px;
        border: 3px solid rgba(24, 82, 156, 0.1);
        border-left-color: var(--primary);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin-bottom: 12px;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* ---------- Leads table (clean, fits without cutting the Actions column) ---------- */
    #masterResultsArea .table-responsive { border: 1px solid var(--border-color); border-radius: var(--radius); }
    #masterResultsArea .admin-table { border-collapse: collapse; margin: 0; }
    #masterResultsArea .admin-table th {
        padding: 11px 14px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: var(--text-muted);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border-color);
    }
    #masterResultsArea .admin-table td {
        padding: 12px 14px;
        font-size: 13.5px;
        color: var(--text-main);
        vertical-align: middle;
        border-bottom: 1px solid #f1f4f8;
    }
    #masterResultsArea .admin-table tbody tr:last-child td { border-bottom: none; }
    #masterResultsArea .admin-table tbody tr[data-row-id] { cursor: pointer; }
    #masterResultsArea .admin-table tbody tr[data-row-id]:hover td { background: #f9fbfd; }
    #masterResultsArea .th-right, #masterResultsArea .td-right { text-align: right; }
    #masterResultsArea .cell-main { font-weight: 600; color: var(--text-main); }
    #masterResultsArea .cell-sub { font-size: 12px; color: var(--text-muted); margin-top: 1px; font-variant-numeric: tabular-nums; }
    #masterResultsArea .cell-muted { color: #94a3b8; }
    #masterResultsArea .cell-id { font-size: 12.5px; font-weight: 600; color: var(--text-secondary); font-variant-numeric: tabular-nums; }
    #masterResultsArea .cell-amount { font-weight: 600; font-variant-numeric: tabular-nums; }
    #masterResultsArea .cell-date { font-size: 13px; font-variant-numeric: tabular-nums; }
    #masterResultsArea .type-chip {
        display: inline-block;
        padding: 2px 9px;
        border-radius: 999px;
        background: #f1f5f9;
        color: var(--text-secondary);
        font-size: 12px;
        font-weight: 600;
        text-transform: capitalize;
    }
    /* Actions stay visible even when a wide table scrolls sideways. */
    #masterResultsArea .admin-table th:last-child,
    #masterResultsArea .admin-table td:last-child {
        position: sticky;
        right: 0;
        z-index: 1;
        background: #ffffff;
        box-shadow: -10px 0 10px -10px rgba(15, 23, 42, 0.18);
    }
    #masterResultsArea .admin-table th:last-child { background: var(--bg-subtle); }
    #masterResultsArea .admin-table tbody tr[data-row-id]:hover td:last-child { background: #f9fbfd; }
    #masterResultsArea .admin-table td { white-space: nowrap; }
    .badge-pending { background: #fffbeb; color: #b45309; border-color: #fde68a; }
    #masterResultsArea .row-actions { display: inline-flex; gap: 6px; justify-content: flex-end; }
    .ml-icon-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid var(--border-strong);
        background: #ffffff;
        color: var(--text-secondary);
        font-size: 13px;
        cursor: pointer;
        transition: border-color 0.15s ease, color 0.15s ease, background-color 0.15s ease;
    }
    .ml-icon-btn:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-soft); }
    .ml-icon-btn.danger { color: #b91c1c; border-color: #fecaca; }
    .ml-icon-btn.danger:hover { background: #fef2f2; border-color: #dc2626; color: #dc2626; }
    #masterResultsArea .empty-block { padding: 48px 20px; text-align: center; color: var(--text-muted); white-space: normal; }
    #masterResultsArea .empty-block i.big { display: block; font-size: 26px; color: #cbd5e1; margin-bottom: 10px; }
    #masterResultsArea .empty-block strong { display: block; color: var(--text-main); margin-bottom: 4px; }
    .results-foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 14px;
        font-size: 12.5px;
        color: var(--text-muted);
    }
    .results-foot .pagination { margin: 0; gap: 4px; }
    .results-foot .page-item a, .results-foot .page-item span {
        min-width: 32px;
        height: 32px;
        padding: 0 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border-color);
        border-radius: 7px;
        background: #ffffff;
        color: var(--text-secondary);
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none;
        transition: border-color 0.15s ease, color 0.15s ease;
    }
    .results-foot .page-item a:hover { background: #ffffff; border-color: var(--primary); color: var(--primary); }
    .results-foot .page-item.active span { background: var(--primary); border-color: var(--primary); color: #ffffff; }
    .results-foot .page-item.disabled span { opacity: 0.45; cursor: not-allowed; }
    .results-foot .page-item.gap span { border: none; background: transparent; min-width: 16px; padding: 0; }

    /* ---------- Drawer edit form ---------- */
    .edit-banner {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        margin-bottom: 14px;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: var(--radius-lg);
    }
    .edit-banner i { color: #b45309; }
    .edit-banner .t { font-weight: 700; color: var(--text-main); font-size: 14px; }
    .edit-banner .s { font-size: 12.5px; color: var(--text-secondary); }
    .edit-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px 16px; }
    .edit-field { display: flex; flex-direction: column; gap: 5px; min-width: 0; }
    .edit-field.span-2 { grid-column: 1 / -1; }
    .edit-field label { font-size: 12.5px; font-weight: 600; color: var(--text-secondary); }
    .edit-field .req { color: #dc2626; }
    .edit-field input, .edit-field select, .edit-field textarea {
        width: 100%;
        height: 40px;
        padding: 0 12px;
        border: 1px solid var(--border-strong);
        border-radius: 8px;
        background: #ffffff;
        color: var(--text-main);
        font-size: 13.5px;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .edit-field textarea { height: auto; min-height: 70px; padding: 10px 12px; resize: vertical; font-family: inherit; }
    .edit-field input:focus, .edit-field select:focus, .edit-field textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-glow); }
    .edit-field input.changed, .edit-field select.changed, .edit-field textarea.changed { border-color: #d97706; background: #fffdf5; }
    .edit-field .hint { font-size: 11.5px; color: var(--text-muted); }
    .edit-prefix { position: relative; }
    .edit-prefix > span { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 13.5px; pointer-events: none; }
    .edit-prefix > input { padding-left: 26px; }
    .edit-actions {
        position: sticky;
        bottom: 0;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin: 6px -24px -40px;
        padding: 12px 24px;
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(6px);
        border-top: 1px solid var(--border-color);
    }
    .edit-actions .state { font-size: 12.5px; color: var(--text-muted); }
    .edit-actions .state.dirty { color: #b45309; font-weight: 600; }
    .edit-actions .btns { display: flex; gap: 8px; }
    .edit-actions .btn-primary-adm { height: 38px; padding: 0 18px; box-shadow: none; }
    .edit-actions .btn-primary-adm:disabled { opacity: 0.5; cursor: not-allowed; }
    .edit-actions .lead-action { height: 38px; }
    @media (max-width: 560px) {
        .edit-grid { grid-template-columns: minmax(0, 1fr); }
        .edit-actions { margin: 6px -16px -32px; padding: 12px 16px; }
    }
    #masterResultsArea,
    #masterKpiGrid {
        transition: opacity 0.18s ease, filter 0.18s ease;
    }
    .master-ajax-loading {
        opacity: 0.58;
        filter: saturate(0.8);
        pointer-events: none;
    }
    .master-inline-loader {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 36px 0;
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 800;
    }
    .master-inline-loader::before {
        content: '';
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 3px solid rgba(24, 82, 156, 0.14);
        border-left-color: var(--primary);
        animation: spin 0.8s linear infinite;
    }

    /* App ID monospace look */
    .app-id-code {
        font-family: monospace;
        font-size: 12.5px;
        font-weight: 700;
        color: var(--primary);
        background: rgba(24, 82, 156, 0.05);
        padding: 4px 8px;
        border-radius: 6px;
    }

    /* Action button styles */
    .action-btn-group {
        display: flex;
        gap: 6px;
        align-items: center;
    }
    .btn-action-premium {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        font-size: 13px;
        border: 1px solid #e2e8f0;
    }
    .btn-action-premium:hover {
        transform: scale(1.05);
    }
    .btn-action-view {
        border-color: #18529c;
        background: #18529c;
        color: #ffffff;
        width: auto;
        padding: 6px 12px;
        gap: 5px;
        font-weight: 700;
        font-size: 12px;
        box-shadow: 0 2px 6px rgba(24, 82, 156, 0.25);
    }
    .btn-action-view:hover {
        background: #103a71;
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(24, 82, 156, 0.35);
    }
    .btn-statement-view {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        width: auto !important;
        min-width: 175px !important;
        height: auto !important;
        padding: 10px 18px !important;
        background: #16a34a !important;
        color: #ffffff !important;
        border: none !important;
        border-radius: 8px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        cursor: pointer !important;
        text-decoration: none !important;
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25) !important;
        transition: all 0.2s ease !important;
    }
    .btn-statement-view:hover {
        background: #15803d !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 16px rgba(22, 163, 74, 0.35) !important;
    }
    .btn-statement-csv {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        width: auto !important;
        min-width: 175px !important;
        height: auto !important;
        padding: 10px 18px !important;
        background: #0f172a !important;
        color: #ffffff !important;
        border: none !important;
        border-radius: 8px !important;
        font-size: 12.5px !important;
        font-weight: 700 !important;
        cursor: pointer !important;
        text-decoration: none !important;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2) !important;
        transition: all 0.2s ease !important;
    }
    .btn-statement-csv:hover {
        background: #1e293b !important;
        transform: translateY(-1px) !important;
    }
    .btn-action-edit {
        border-color: rgba(245, 158, 11, 0.2);
        color: #d97706;
    }
    .btn-action-edit:hover {
        background: #d97706;
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(245, 158, 11, 0.25);
    }
    .btn-action-delete {
        border-color: rgba(239, 68, 68, 0.2);
        color: #dc2626;
    }
    .btn-action-delete:hover {
        background: #dc2626;
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(239, 68, 68, 0.25);
    }

    /* ==========================================================
       Lead detail drawer (view mode)
       ========================================================== */
    #drawerBody .info-card {
        border: 1px solid var(--border-color);
        border-left: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 18px 20px;
        box-shadow: var(--shadow-xs);
        margin-bottom: 14px;
        scroll-margin-top: 112px;
    }
    #drawerBody .card-title {
        font-family: 'Outfit', sans-serif;
        font-size: 14.5px;
        font-weight: 600;
        text-transform: none;
        letter-spacing: 0;
        color: var(--text-main);
        border-bottom: none;
        padding-bottom: 0;
        margin-bottom: 14px;
    }
    #drawerBody .card-title > i:first-child,
    #drawerBody .card-title > span > i:first-child { color: var(--primary); font-size: 13px; width: 16px; text-align: center; }
    #drawerBody .grid-fields { gap: 14px 20px; }
    #drawerBody .field-item { margin: 0; min-width: 0; }
    #drawerBody .field-label {
        font-size: 11.5px;
        font-weight: 500;
        text-transform: none;
        letter-spacing: 0;
        color: var(--text-muted);
        margin-bottom: 3px;
    }
    #drawerBody .field-value {
        font-size: 13.5px;
        font-weight: 600;
        color: var(--text-main);
        word-break: break-word;
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }
    #drawerBody .field-value.mono { font-variant-numeric: tabular-nums; letter-spacing: 0.3px; }
    #drawerBody .field-value .empty { color: #94a3b8; font-weight: 500; }
    #drawerBody .span-2 { grid-column: 1 / -1; }

    /* Hero */
    .lead-hero {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 18px 20px;
        margin-bottom: 14px;
        box-shadow: var(--shadow-xs);
    }
    .lead-hero-top { display: flex; align-items: flex-start; gap: 14px; }
    .lead-avatar {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        flex-shrink: 0;
        background: linear-gradient(135deg, #2563eb, #18529c);
        color: #ffffff;
        font-weight: 700;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .lead-hero-main { flex: 1; min-width: 0; }
    .lead-name {
        font-family: 'Outfit', sans-serif;
        font-size: 20px;
        font-weight: 700;
        color: var(--text-main);
        line-height: 1.25;
        word-break: break-word;
    }
    .lead-meta {
        display: flex;
        align-items: center;
        gap: 6px 10px;
        flex-wrap: wrap;
        margin-top: 4px;
        font-size: 12.5px;
        color: var(--text-muted);
    }
    .lead-meta .sep { color: #cbd5e1; }
    .lead-id { font-weight: 600; color: var(--text-secondary); font-variant-numeric: tabular-nums; }
    .lead-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 2px 9px;
        border-radius: 999px;
        background: #f1f5f9;
        color: var(--text-secondary);
        font-size: 11.5px;
        font-weight: 600;
        text-transform: capitalize;
    }
    .lead-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 14px; }
    .lead-action {
        height: 34px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 0 12px;
        border-radius: 8px;
        border: 1px solid var(--border-strong);
        background: #ffffff;
        color: var(--text-main);
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: border-color 0.15s ease, color 0.15s ease, background-color 0.15s ease;
    }
    .lead-action:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-soft); }
    .lead-action.wa { color: #15803d; }
    .lead-action.wa:hover { border-color: #16a34a; background: #f0fdf4; color: #15803d; }
    .lead-action.primary { background: var(--primary); border-color: var(--primary); color: #ffffff; }
    .lead-action.primary:hover { background: var(--primary-hover); color: #ffffff; }

    /* Key metrics strip */
    .lead-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        margin-top: 16px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        overflow: hidden;
    }
    .lead-stat { padding: 10px 14px; border-right: 1px solid var(--border-color); min-width: 0; }
    .lead-stat:last-child { border-right: none; }
    .lead-stat-label { font-size: 11.5px; color: var(--text-muted); }
    .lead-stat-value {
        font-family: 'Outfit', sans-serif;
        font-size: 17px;
        font-weight: 700;
        color: var(--text-main);
        margin-top: 2px;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .lead-stat-value.good { color: #15803d; }
    .lead-stat-value.bad { color: #b91c1c; }
    .lead-stat-value.warn { color: #b45309; }
    .lead-stat-value.muted { color: #94a3b8; }

    /* Sticky section nav */
    .drawer-nav {
        position: sticky;
        top: 56px;
        z-index: 4;
        display: flex;
        gap: 6px;
        overflow-x: auto;
        scrollbar-width: none;
        margin: 0 -24px 14px;
        padding: 10px 24px;
        background: rgba(244, 246, 250, 0.94);
        backdrop-filter: blur(6px);
        border-bottom: 1px solid var(--border-color);
    }
    .drawer-nav::-webkit-scrollbar { display: none; }
    .drawer-nav button {
        flex-shrink: 0;
        height: 30px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid var(--border-color);
        background: #ffffff;
        color: var(--text-secondary);
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        transition: border-color 0.15s ease, color 0.15s ease, background-color 0.15s ease;
    }
    .drawer-nav button:hover { border-color: var(--primary); color: var(--primary); }
    .drawer-nav button.current { background: #0f172a; border-color: #0f172a; color: #ffffff; }
    .drawer-nav .nav-count {
        margin-left: 4px;
        padding: 0 6px;
        border-radius: 999px;
        background: #fee2e2;
        color: #b91c1c;
        font-size: 10.5px;
    }

    /* Decision banner */
    .decision {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        padding: 16px 18px;
        border-radius: var(--radius-lg);
        border: 1px solid;
        margin-bottom: 14px;
        scroll-margin-top: 112px;
    }
    .decision.declined { background: #fef2f2; border-color: #fecaca; }
    .decision.approved { background: #f0fdf4; border-color: #bbf7d0; }
    .decision.other { background: #fffbeb; border-color: #fde68a; }
    .decision-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        background: #ffffff;
    }
    .decision.declined .decision-icon { color: #dc2626; }
    .decision.approved .decision-icon { color: #16a34a; }
    .decision.other .decision-icon { color: #d97706; }
    .decision-title { font-size: 14.5px; font-weight: 700; color: var(--text-main); }
    .decision-sub { font-size: 12.5px; color: var(--text-secondary); margin-top: 2px; }
    .decision-reasons { margin: 10px 0 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 6px; }
    .decision-reasons li {
        display: flex;
        gap: 8px;
        font-size: 13px;
        color: #7f1d1d;
        line-height: 1.45;
    }
    .decision-reasons li i { color: #dc2626; font-size: 11px; margin-top: 4px; }
    .decision-figures { display: flex; gap: 20px; flex-wrap: wrap; margin-top: 10px; }
    .decision-figures div { font-size: 12px; color: var(--text-muted); }
    .decision-figures strong { display: block; font-size: 14px; color: var(--text-main); font-variant-numeric: tabular-nums; }

    /* Verified tick / pills */
    .vpill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 1px 7px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
    }
    .vpill.ok { background: #dcfce7; color: #166534; }
    .vpill.no { background: #f1f5f9; color: #64748b; }

    .copy-inline {
        border: none;
        background: transparent;
        color: #94a3b8;
        cursor: pointer;
        width: 22px;
        height: 22px;
        border-radius: 5px;
        font-size: 11px;
        padding: 0;
    }
    .copy-inline:hover { background: #eef1f6; color: var(--primary); }
    .copy-inline.copied { color: #15803d; }
    .reveal-btn {
        border: none;
        background: none;
        color: var(--primary);
        font-size: 11.5px;
        font-weight: 600;
        cursor: pointer;
        padding: 0;
    }

    /* Collapsible groups */
    #drawerBody details.info-card > summary {
        list-style: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    #drawerBody details.info-card > summary::-webkit-details-marker { display: none; }
    #drawerBody details.info-card > summary .card-title { margin-bottom: 0; }
    #drawerBody details.info-card > summary .chev { color: var(--text-muted); font-size: 11px; transition: transform 0.15s ease; }
    #drawerBody details.info-card[open] > summary .chev { transform: rotate(180deg); }
    #drawerBody details.info-card[open] > summary { margin-bottom: 14px; }
    .kv-sub {
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 10px 12px;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px 14px;
        width: 100%;
    }
    .kv-sub .k { font-size: 11px; color: var(--text-muted); }
    .kv-sub .v { font-size: 12.5px; font-weight: 600; color: var(--text-main); word-break: break-word; }
    .photo-row { display: flex; gap: 8px; flex-wrap: wrap; }
    .photo-row a img {
        width: 84px;
        height: 84px;
        border-radius: 10px;
        object-fit: cover;
        border: 1px solid var(--border-color);
        display: block;
        transition: transform 0.15s ease;
    }
    .photo-row a:hover img { transform: scale(1.03); }

    /* Statement buttons inside AA card */
    #drawerBody .aa-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--border-color); }

    /* Document checklist as rows */
    .doc-progress { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; font-size: 12.5px; color: var(--text-muted); }
    .doc-progress .track { flex: 1; height: 6px; background: #eef1f6; border-radius: 3px; overflow: hidden; }
    .doc-progress .fill { height: 100%; background: #16a34a; border-radius: 3px; }
    .doc-list { display: flex; flex-direction: column; border: 1px solid var(--border-color); border-radius: var(--radius); overflow: hidden; }
    .doc-item { padding: 12px 14px; border-bottom: 1px solid var(--border-color); background: #ffffff; }
    .doc-item:last-child { border-bottom: none; }
    .doc-item-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
    .doc-name { font-size: 13px; font-weight: 600; color: var(--text-main); }
    .doc-meta { font-size: 11.5px; color: var(--text-muted); margin-top: 2px; }
    .doc-item-right { display: flex; align-items: center; gap: 10px; }
    .doc-item .checklist-status-badge { text-transform: capitalize; font-size: 11px; border-radius: 999px; padding: 2px 9px; }
    .doc-review { display: flex; gap: 6px; margin-top: 10px; flex-wrap: wrap; }
    .doc-review input {
        flex: 1;
        min-width: 160px;
        height: 32px;
        padding: 0 10px;
        border: 1px solid var(--border-strong);
        border-radius: 7px;
        font-size: 12.5px;
        outline: none;
    }
    .doc-review input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-glow); }
    .doc-review .checklist-action-btn { height: 32px; padding: 0 10px; font-size: 12px; border-radius: 7px; display: inline-flex; align-items: center; gap: 5px; }
    .doc-missing { font-size: 12px; color: #94a3b8; font-style: italic; }

    /* Activity */
    #drawerBody .note-form { display: flex; gap: 8px; margin-bottom: 16px; }
    #drawerBody .note-form input {
        flex: 1;
        height: 38px;
        padding: 0 12px;
        border: 1px solid var(--border-strong);
        border-radius: 8px;
        font-size: 13px;
        outline: none;
    }
    #drawerBody .note-form input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-glow); }
    #drawerBody .note-form .btn-primary-adm { height: 38px; padding: 0 14px; box-shadow: none; }
    #drawerBody .timeline-item-header { font-weight: 600; gap: 10px; }
    #drawerBody .timeline-item-desc { background: var(--bg-subtle); border-color: var(--border-color); font-size: 13px; }

    /* Status control */
    #drawerBody .status-select-wrap select { height: 38px !important; padding: 0 12px !important; border-radius: 8px; }
    #drawerBody .status-select-wrap .btn-primary-adm { height: 38px !important; }

    /* Loading skeleton */
    .skel { background: linear-gradient(90deg, #eef1f6 25%, #f6f8fb 50%, #eef1f6 75%); background-size: 200% 100%; animation: skel 1.2s ease-in-out infinite; border-radius: 8px; }
    @keyframes skel { to { background-position: -200% 0; } }

    @media (max-width: 560px) {
        #drawerBody { padding: 16px 16px 32px; }
        .drawer-nav { margin: 0 -16px 14px; padding: 10px 16px; }
        #drawerBody .grid-fields, .kv-sub { grid-template-columns: minmax(0, 1fr); }
        .lead-stat { border-right: none; border-bottom: 1px solid var(--border-color); }
    }
</style>

<!-- Tab Navigation -->
<div class="tab-nav" id="masterTabs">
    <a href="?tab=general" data-master-tab="general" class="tab-btn <?php echo $activeTab === 'general' ? 'active' : ''; ?>">
        <i class="fa-solid fa-layer-group"></i> Business & Personal Loans (New)
    </a>
    <a href="?tab=edi" data-master-tab="edi" class="tab-btn <?php echo $activeTab === 'edi' ? 'active' : ''; ?>">
        <i class="fa-solid fa-store"></i> Shopkeeper (EDI) Applications
    </a>
</div>

<div class="master-kpi-grid" id="masterKpiGrid">
    <?php foreach ($kpiCards as $card): ?>
        <div class="master-kpi-card kpi-<?php echo htmlspecialchars($card['tone'], ENT_QUOTES, 'UTF-8'); ?>">
            <div class="master-kpi-icon">
                <i class="fa-solid <?php echo htmlspecialchars($card['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
            </div>
            <div class="master-kpi-info">
                <h3><?php echo htmlspecialchars($card['value'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <p><?php echo htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8'); ?></p>
                <span><?php echo htmlspecialchars($card['sub'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php $advancedActive = $dateFrom !== '' || $dateTo !== '' || $amountMin !== '' || $amountMax !== ''; ?>
<div class="data-card">
    <!-- Filters apply automatically (typing is debounced), so there is no Apply button. -->
    <form method="GET" action="master-loans.php" id="masterFilterForm" class="master-filter-panel" role="search">
        <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>">

        <div class="master-toolbar">
            <div class="filter-field fld-search">
                <label for="master-search">Search</label>
                <i class="fa-solid fa-magnifying-glass"></i>
                <input id="master-search" type="search" name="search" autocomplete="off"
                       placeholder="<?php echo $activeTab === 'general' ? 'ID, name, mobile, PAN, city...' : 'ID, name, mobile, PAN, shop...'; ?>"
                       value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="filter-field">
                <label for="master-city">City</label>
                <select id="master-city" name="city">
                    <option value="">All Cities</option>
                    <?php foreach ($cityOptions as $option): ?>
                        <option value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $city === $option ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-field">
                <label for="master-category"><?php echo $activeTab === 'general' ? 'Category' : 'Business Type'; ?></label>
                <select id="master-category" name="category">
                    <option value=""><?php echo $activeTab === 'general' ? 'All Categories' : 'All Business Types'; ?></option>
                    <?php foreach ($categoryOptions as $option): ?>
                        <option value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $category === $option ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(ucwords(str_replace(['_', '-'], ' ', $option)), ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if ($activeTab === 'edi'): ?>
                <div class="filter-field" id="master-status-field">
                    <label for="master-status">Status</label>
                    <select id="master-status" name="status">
                        <option value="">All Statuses</option>
                        <?php foreach ($statusOptions as $option): ?>
                            <option value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $status === $option ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucwords(str_replace(['_', '-'], ' ', $option)), ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <button type="button" class="btn-more-filters" id="masterMoreFiltersBtn" aria-expanded="<?php echo $advancedActive ? 'true' : 'false'; ?>" aria-controls="masterAdvancedFilters">
                <i class="fa-solid fa-sliders"></i> Date &amp; amount
                <span class="more-count" id="masterMoreCount" hidden>0</span>
                <i class="fa-solid fa-chevron-down chev"></i>
            </button>

            <div class="master-toolbar-actions">
                <a href="master-loans.php?tab=<?php echo urlencode($activeTab); ?>" class="btn-outline-adm" data-master-reset title="Clear all filters" style="<?php echo $filtersActive ? '' : 'display:none;'; ?>">
                    <i class="fa-solid fa-xmark"></i> Clear
                </a>
                <button type="button" class="btn-export-adm" onclick="exportLeadsCsv()" title="Download the current (filtered) list">
                    <i class="fa-solid fa-file-arrow-down"></i> Export
                </button>
            </div>
        </div>

        <div class="master-advanced" id="masterAdvancedFilters"<?php echo $advancedActive ? '' : ' hidden'; ?>>
            <div class="adv-group">
                <span class="adv-label">Applied between</span>
                <div class="adv-pair">
                    <div class="filter-field">
                        <label for="master-date-from">From date</label>
                        <input id="master-date-from" type="date" name="date_from" max="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <span>to</span>
                    <div class="filter-field">
                        <label for="master-date-to">To date</label>
                        <input id="master-date-to" type="date" name="date_to" max="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
            </div>

            <div class="adv-group">
                <span class="adv-label">Quick range</span>
                <div class="adv-quick">
                    <button type="button" data-range="today">Today</button>
                    <button type="button" data-range="7">Last 7 days</button>
                    <button type="button" data-range="30">Last 30 days</button>
                    <button type="button" data-range="month">This month</button>
                </div>
            </div>

            <div class="adv-group">
                <span class="adv-label">Loan amount (₹)</span>
                <div class="adv-pair">
                    <div class="filter-field">
                        <label for="master-amount-min">Min amount</label>
                        <input id="master-amount-min" type="number" name="amount_min" min="0" step="1000" inputmode="numeric" placeholder="Min" value="<?php echo htmlspecialchars($amountMin, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <span>–</span>
                    <div class="filter-field">
                        <label for="master-amount-max">Max amount</label>
                        <input id="master-amount-max" type="number" name="amount_max" min="0" step="1000" inputmode="numeric" placeholder="Max" value="<?php echo htmlspecialchars($amountMax, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="master-filter-summary" id="masterFilterSummary">
            <?php echo number_format($totalRecords); ?> matching <?php echo $activeTab === 'general' ? 'general leads' : 'EDI applications'; ?>
            <?php if ($filtersActive): ?>
                <span style="color: var(--primary); margin-left: 8px;">Filters active</span>
            <?php endif; ?>
        </div>

        <!-- Lets Enter in a field submit (the AJAX submit listener handles it). -->
        <button type="submit" hidden aria-hidden="true" tabindex="-1"></button>
    </form>

    <!-- Rendered by renderMasterResults() so the first load and every AJAX refresh share one template. -->
    <div id="masterResultsArea" aria-live="polite"></div>
    <?php
    $initialKeys = $activeTab === 'general'
        ? ['id', 'application_id', 'applicant_name', 'mobile_number', 'pancard_number', 'city', 'loan_amount', 'loan_type', 'created_at']
        : ['id', 'application_id', 'applicant_name', 'mobile_number', 'pan_number', 'shop_business_name', 'loan_amount', 'pincode', 'status', 'created_at'];
    // Only the columns the table shows are sent to the browser.
    $initialRecords = array_map(
        static fn(array $row): array => array_intersect_key($row, array_flip($initialKeys)),
        $activeTab === 'general' ? $generalLeads : $ediLeads
    );
    $initialMasterData = [
        'tab' => $activeTab,
        'page' => $page,
        'limit' => $limit,
        'total_records' => $totalRecords,
        'total_pages' => $totalPages,
        'filters_active' => $filtersActive,
        'records' => $initialRecords,
    ];
    ?>
    <script type="application/json" id="masterInitialData"><?php echo json_encode($initialMasterData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?></script>
</div>

<!-- Details Drawer Modal -->
<div class="drawer-overlay" id="detailDrawer">
    <div class="drawer-content" role="dialog" aria-modal="true" aria-labelledby="drawerTitle">
        <div class="drawer-header">
            <h2 class="card-title-text" id="drawerTitle">Application Detail</h2>
            <button type="button" class="drawer-close" id="closeDrawer"><i class="fa-solid fa-xmark"></i></button>
        </div>
        
        <div id="drawerBody">
            <!-- Populated dynamically via JS -->
        </div>
    </div>
</div>

<script>
    const drawer = document.getElementById('detailDrawer');
    const drawerBody = document.getElementById('drawerBody');
    const drawerTitle = document.getElementById('drawerTitle');
    const closeDrawer = document.getElementById('closeDrawer');
    
    closeDrawer.addEventListener('click', () => closeLeadDrawer());
    drawer.addEventListener('click', (e) => {
        if (e.target === drawer) closeLeadDrawer();
    });

    const csrfToken = <?php echo json_encode($_SESSION['csrf_token'] ?? ''); ?>;
    const masterFilterForm = document.getElementById('masterFilterForm');
    const masterResultsArea = document.getElementById('masterResultsArea');
    const masterKpiGrid = document.getElementById('masterKpiGrid');
    const masterFilterSummary = document.getElementById('masterFilterSummary');
    const masterTabs = document.getElementById('masterTabs');
    let masterState = {
        tab: <?php echo json_encode($activeTab); ?>,
        page: <?php echo (int) $page; ?>,
        totalPages: <?php echo (int) $totalPages; ?>,
        loading: false
    };
    let masterSearchTimer = null;
    let masterRequestId = 0;

    // Value for a quoted-free JS argument inside an HTML attribute: JSON string, then HTML-escaped.
    function jsArg(value) {
        return escapeHtml(JSON.stringify(String(value ?? '')));
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatCurrency(value) {
        const amount = Number(String(value ?? 0).replace(/,/g, ''));
        const safeAmount = Number.isFinite(amount) ? amount : 0;
        return '₹' + safeAmount.toLocaleString('en-IN', { maximumFractionDigits: 0 });
    }

    function formatDateTime(value) {
        if (!value) return 'N/A';
        const date = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) return escapeHtml(value);
        return date.toLocaleString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    }

    function readableOptionLabel(value) {
        return String(value || '')
            .replace(/[_-]+/g, ' ')
            .replace(/\b\w/g, char => char.toUpperCase());
    }

    function getFilterPayload(page = masterState.page) {
        const fd = new FormData(masterFilterForm);
        fd.set('action', 'list_master_leads');
        fd.set('csrf_token', csrfToken);
        fd.set('tab', masterState.tab);
        fd.set('page', String(page));
        fd.set('limit', '10');
        return fd;
    }

    function setMasterLoading(isLoading) {
        masterState.loading = isLoading;
        masterResultsArea?.classList.toggle('master-ajax-loading', isLoading);
        masterKpiGrid?.classList.toggle('master-ajax-loading', isLoading);
        
        const loader = document.getElementById('masterTableLoadingOverlay');
        if (loader) {
            if (isLoading) loader.classList.add('active');
            else loader.classList.remove('active');
        }
    }

    function renderKpiCards(cards) {
        if (!masterKpiGrid) return;
        masterKpiGrid.innerHTML = (cards || []).map(card => `
            <div class="master-kpi-card kpi-${escapeHtml(card.tone)}">
                <div class="master-kpi-icon">
                    <i class="fa-solid ${escapeHtml(card.icon)}"></i>
                </div>
                <div class="master-kpi-info">
                    <h3>${escapeHtml(card.value)}</h3>
                    <p>${escapeHtml(card.label)}</p>
                    <span>${escapeHtml(card.sub)}</span>
                </div>
            </div>
        `).join('');
    }

    function renderStatusField(tab, options, selected) {
        let statusField = document.getElementById('master-status-field');
        const categoryField = document.getElementById('master-category')?.closest('.filter-field');

        if (tab !== 'edi') {
            statusField?.remove();
            return;
        }

        const html = `
            <label for="master-status">Status</label>
            <select id="master-status" name="status">
                <option value="">All Statuses</option>
                ${(options || []).map(option => `
                    <option value="${escapeHtml(option)}" ${selected === option ? 'selected' : ''}>${escapeHtml(readableOptionLabel(option))}</option>
                `).join('')}
            </select>
        `;

        if (!statusField) {
            statusField = document.createElement('div');
            statusField.className = 'filter-field';
            statusField.id = 'master-status-field';
            categoryField?.after(statusField);
        }

        statusField.innerHTML = html;
    }

    function syncFilterControls(data) {
        const tabInput = masterFilterForm.querySelector('[name="tab"]');
        if (tabInput) tabInput.value = data.tab;

        const categoryLabel = document.querySelector('label[for="master-category"]');
        if (categoryLabel) categoryLabel.textContent = data.tab === 'general' ? 'Category' : 'Business Type';

        const searchInput = document.getElementById('master-search');
        if (searchInput) {
            searchInput.placeholder = data.tab === 'general'
                ? 'ID, name, mobile, PAN, city...'
                : 'ID, name, mobile, PAN, shop...';
            searchInput.value = data.filters.search || '';
        }

        ['date_from', 'date_to', 'amount_min', 'amount_max'].forEach(name => {
            const field = masterFilterForm.querySelector(`[name="${name}"]`);
            if (field) field.value = data.filters[name] || '';
        });

        const categorySelect = document.getElementById('master-category');
        if (categorySelect) {
            const placeholder = data.tab === 'general' ? 'All Categories' : 'All Business Types';
            categorySelect.innerHTML = `<option value="">${placeholder}</option>` + (data.category_options || []).map(option => `
                <option value="${escapeHtml(option)}" ${data.filters.category === option ? 'selected' : ''}>${escapeHtml(readableOptionLabel(option))}</option>
            `).join('');
        }

        const citySelect = document.getElementById('master-city');
        if (citySelect) {
            citySelect.innerHTML = '<option value="">All Cities</option>' + (data.city_options || []).map(option => `
                <option value="${escapeHtml(option)}" ${data.filters.city === option ? 'selected' : ''}>${escapeHtml(option)}</option>
            `).join('');
        }

        renderStatusField(data.tab, data.status_options || [], data.filters.status || '');
    }

    function updateActiveTab(tab) {
        masterTabs?.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-master-tab') === tab);
        });
    }

    function updateBrowserUrl(data) {
        const params = new URLSearchParams();
        params.set('tab', data.tab);
        Object.entries(data.filters || {}).forEach(([key, value]) => {
            if (value !== '') params.set(key, value);
        });
        if (data.page > 1) params.set('page', data.page);
        history.replaceState(null, '', `master-loans.php?${params.toString()}`);
    }

    // ---------- Table cells ----------
    function amountCell(value) {
        const raw = String(value ?? '').replace(/,/g, '').trim();
        const n = Number(raw);
        if (raw === '' || !Number.isFinite(n)) return '<span class="cell-muted">—</span>';
        return '₹' + n.toLocaleString('en-IN', { maximumFractionDigits: 0 });
    }
    function dateCell(value) {
        if (!value) return '<span class="cell-muted">—</span>';
        const d = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(d.getTime())) return escapeHtml(value);
        return `<div class="cell-date">${d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })}</div>
                <div class="cell-sub">${d.toLocaleTimeString('en-IN', { hour: 'numeric', minute: '2-digit', hour12: true })}</div>`;
    }
    function nameCell(name, subHtml) {
        const n = String(name || '').trim();
        const main = n && n.toUpperCase() !== 'N/A'
            ? escapeHtml(n)
            : '<span class="cell-muted" style="font-style:italic; font-weight:500;">Name not provided</span>';
        return `<div class="cell-main">${main}</div>${subHtml ? `<div class="cell-sub">${subHtml}</div>` : ''}`;
    }
    function categoryLabel(value) {
        const v = String(value || '').trim().toLowerCase();
        if (!v) return '';
        if (v === 'lap') return 'LAP';
        if (v === 'edi') return 'EDI';
        return v.replace(/[_-]+/g, ' ');
    }
    function rowActions(type, lead) {
        const id = escapeHtml(lead.id);
        return `
            <div class="row-actions">
                <button type="button" class="ml-icon-btn js-master-action" data-action="view" data-type="${type}" data-id="${id}" title="View details" aria-label="View details"><i class="fa-solid fa-eye"></i></button>
                <button type="button" class="ml-icon-btn js-master-action" data-action="edit" data-type="${type}" data-id="${id}" title="Edit" aria-label="Edit"><i class="fa-solid fa-pen"></i></button>
                ${type === 'edi' && lead.application_id ? `<button type="button" class="ml-icon-btn sync-edi-crm-btn" data-id="${escapeHtml(lead.application_id)}" title="Sync documents to CRM" aria-label="Sync documents to CRM"><i class="fa-solid fa-cloud-arrow-up"></i></button>` : ''}
                <button type="button" class="ml-icon-btn danger js-master-action" data-action="delete" data-type="${type}" data-id="${id}" title="Delete" aria-label="Delete"><i class="fa-solid fa-trash"></i></button>
            </div>
        `;
    }
    function emptyRow(colspan, filtersActive, label) {
        return `<tr><td colspan="${colspan}" class="empty-block">
            <i class="fa-solid fa-inbox big"></i>
            <strong>${filtersActive ? `No ${label} match these filters` : `No ${label} yet`}</strong>
            ${filtersActive
                ? 'Try widening the date range or clearing a filter.<br><button type="button" class="lead-action" style="margin-top:12px;" onclick="document.querySelector(\'[data-master-reset]\')?.click()"><i class="fa-solid fa-xmark"></i> Clear filters</button>'
                : 'New applications will appear here as they come in.'}
        </td></tr>`;
    }

    function renderGeneralRows(records, filtersActive = false) {
        if (!records.length) return emptyRow(7, filtersActive, 'leads');

        return records.map(lead => {
            const sub = [lead.mobile_number, lead.pancard_number ? String(lead.pancard_number).toUpperCase() : '']
                .filter(Boolean).map(escapeHtml).join(' · ');
            const cat = categoryLabel(lead.loan_type);
            return `
                <tr id="lead-row-general-${escapeHtml(lead.id)}" data-row-id="${escapeHtml(lead.id)}" data-row-type="general">
                    <td>${nameCell(lead.applicant_name, sub)}</td>
                    <td><span class="cell-id">${escapeHtml(lead.application_id || '—')}</span></td>
                    <td>${lead.city ? escapeHtml(lead.city) : '<span class="cell-muted">—</span>'}</td>
                    <td>${cat ? `<span class="type-chip">${escapeHtml(cat)}</span>` : '<span class="cell-muted">Not set</span>'}</td>
                    <td class="td-right cell-amount">${amountCell(lead.loan_amount)}</td>
                    <td>${dateCell(lead.created_at)}</td>
                    <td class="td-right">${rowActions('general', lead)}</td>
                </tr>
            `;
        }).join('');
    }

    function renderEdiRows(records, filtersActive = false) {
        if (!records.length) return emptyRow(7, filtersActive, 'EDI applications');

        return records.map(lead => {
            const status = String(lead.status || 'pending').toLowerCase();
            const sub = [lead.mobile_number, lead.pan_number ? String(lead.pan_number).toUpperCase() : '']
                .filter(Boolean).map(escapeHtml).join(' · ');
            return `
                <tr id="lead-row-edi-${escapeHtml(lead.id)}" data-row-id="${escapeHtml(lead.id)}" data-row-type="edi">
                    <td>${nameCell(lead.applicant_name, sub)}</td>
                    <td><span class="cell-id">${escapeHtml(lead.application_id || '—')}</span></td>
                    <td>
                        <div class="cell-main" style="font-weight:500;">${lead.shop_business_name ? escapeHtml(lead.shop_business_name) : '<span class="cell-muted">—</span>'}</div>
                        ${lead.pincode ? `<div class="cell-sub">PIN ${escapeHtml(lead.pincode)}</div>` : ''}
                    </td>
                    <td class="td-right cell-amount">${amountCell(lead.loan_amount)}</td>
                    <td><span class="badge-status badge-${escapeHtml(status)}" id="badge-${escapeHtml(lead.id)}">${escapeHtml(status)}</span></td>
                    <td>${dateCell(lead.created_at)}</td>
                    <td class="td-right">${rowActions('edi', lead)}</td>
                </tr>
            `;
        }).join('');
    }

    function renderPagination(data) {
        if (data.total_pages <= 1) return '';
        const current = data.page;
        const total = data.total_pages;
        const pages = new Set([1, total, current - 1, current, current + 1]);
        if (current <= 3) [2, 3, 4].forEach(p => pages.add(p));
        if (current >= total - 2) [total - 1, total - 2, total - 3].forEach(p => pages.add(p));
        const list = Array.from(pages).filter(p => p >= 1 && p <= total).sort((a, b) => a - b);

        let last = 0;
        const items = [];
        list.forEach(p => {
            if (last && p - last > 1) items.push('<li class="page-item gap"><span>…</span></li>');
            items.push(p === current
                ? `<li class="page-item active"><span aria-current="page">${p}</span></li>`
                : `<li class="page-item"><a href="#" data-master-page="${p}">${p}</a></li>`);
            last = p;
        });

        return `
            <ul class="pagination" aria-label="Pagination">
                <li class="page-item ${current <= 1 ? 'disabled' : ''}">
                    ${current <= 1 ? '<span aria-hidden="true"><i class="fa-solid fa-chevron-left" style="font-size:10px;"></i></span>' : `<a href="#" data-master-page="${current - 1}" aria-label="Previous page"><i class="fa-solid fa-chevron-left" style="font-size:10px;"></i></a>`}
                </li>
                ${items.join('')}
                <li class="page-item ${current >= total ? 'disabled' : ''}">
                    ${current >= total ? '<span aria-hidden="true"><i class="fa-solid fa-chevron-right" style="font-size:10px;"></i></span>' : `<a href="#" data-master-page="${current + 1}" aria-label="Next page"><i class="fa-solid fa-chevron-right" style="font-size:10px;"></i></a>`}
                </li>
            </ul>
        `;
    }

    function renderMasterResults(data) {
        const isGeneral = data.tab === 'general';
        const head = isGeneral
            ? '<tr><th>Applicant</th><th>Application ID</th><th>City</th><th>Category</th><th class="th-right">Amount</th><th>Applied</th><th class="th-right">Actions</th></tr>'
            : '<tr><th>Applicant</th><th>Application ID</th><th>Shop</th><th class="th-right">Amount</th><th>Status</th><th>Applied</th><th class="th-right">Actions</th></tr>';
        const rows = isGeneral
            ? renderGeneralRows(data.records || [], !!data.filters_active)
            : renderEdiRows(data.records || [], !!data.filters_active);

        const total = Number(data.total_records || 0);
        const limit = Number(data.limit || 10);
        const start = total ? (data.page - 1) * limit + 1 : 0;
        const end = Math.min(data.page * limit, total);

        masterResultsArea.innerHTML = `
            <div style="position: relative;">
                <div class="table-loading-overlay" id="masterTableLoadingOverlay">
                    <div class="suspense-spinner">
                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                        <span>Loading…</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>${head}</thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
                ${total ? `
                    <div class="results-foot">
                        <span>Showing ${start.toLocaleString('en-IN')}–${end.toLocaleString('en-IN')} of ${total.toLocaleString('en-IN')} · click a row to open it</span>
                        ${renderPagination(data)}
                    </div>` : ''}
            </div>
        `;
    }

    function applyMasterResponse(data, updateUrl = true) {
        masterState.tab = data.tab;
        masterState.page = data.page;
        masterState.totalPages = data.total_pages;

        renderKpiCards(data.kpi_cards || []);
        syncFilterControls(data);
        updateActiveTab(data.tab);
        renderMasterResults(data);

        if (masterFilterSummary) {
            masterFilterSummary.innerHTML = `
                ${Number(data.total_records || 0).toLocaleString('en-IN')} matching ${data.tab === 'general' ? 'general leads' : 'EDI applications'}
                ${data.filters_active ? '<span style="color: var(--primary); margin-left: 8px;">Filters active</span>' : ''}
            `;
        }

        const resetButton = masterFilterForm?.querySelector('[data-master-reset]');
        if (resetButton) {
            resetButton.style.display = data.filters_active ? 'inline-flex' : 'none';
            resetButton.setAttribute('href', `master-loans.php?tab=${encodeURIComponent(data.tab)}`);
        }

        updateFilterUi();
        if (updateUrl) updateBrowserUrl(data);
    }

    function refreshMasterLeads(options = {}) {
        if (!masterFilterForm) return;
        const page = options.page || masterState.page || 1;
        const updateUrl = options.updateUrl !== false;
        const requestId = ++masterRequestId;

        setMasterLoading(true);
        if (!options.silent && masterResultsArea && !masterResultsArea.querySelector('table')) {
            masterResultsArea.innerHTML = '<div class="master-inline-loader">Loading leads...</div>';
        }

        fetch('api.php', {
            method: 'POST',
            body: getFilterPayload(page)
        })
        .then(res => res.json())
        .then(data => {
            if (requestId !== masterRequestId) return;
            if (data.status !== 'success') {
                throw new Error(data.message || 'Unable to load leads.');
            }
            applyMasterResponse(data, updateUrl);
        })
        .catch(err => {
            if (requestId !== masterRequestId) return;
            if (masterResultsArea) {
                masterResultsArea.innerHTML = `
                    <div style="padding:28px; text-align:center; color:var(--danger); font-weight:800;">
                        <i class="fa-solid fa-triangle-exclamation"></i> ${escapeHtml(err.message || 'Unable to load leads.')}
                    </div>
                `;
            }
        })
        .finally(() => {
            if (requestId === masterRequestId) setMasterLoading(false);
        });
    }

    const getDocLink = (path, name) => {
        if (!path || path.trim() === '') return '<span style="color:var(--text-muted); font-style:italic;">Not Uploaded</span>';
        const url = path.startsWith('private://')
            ? '../download-file.php?file=' + encodeURIComponent(path)
            : (path.startsWith('http') ? path : '../' + path);
        return `<a href="${escapeHtml(url)}" class="doc-preview-link" onclick="viewImageLightbox(${jsArg(url)}, ${jsArg(name)}); return false;"><i class="fa-solid fa-file-image"></i> View ${escapeHtml(name)}</a>`;
    };

    // Export leads form trigger
    function exportLeadsCsv() {
        const filterForm = document.getElementById('masterFilterForm');
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'api.php';
        
        const actInput = document.createElement('input');
        actInput.type = 'hidden';
        actInput.name = 'action';
        actInput.value = 'export_leads';
        form.appendChild(actInput);

        ['tab', 'search', 'city', 'category', 'status', 'date_from', 'date_to', 'amount_min', 'amount_max'].forEach((fieldName) => {
            const source = filterForm ? filterForm.querySelector(`[name="${fieldName}"]`) : null;
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = fieldName;
            input.value = source ? source.value : '';
            form.appendChild(input);
        });

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?php echo $_SESSION['csrf_token'] ?? ""; ?>';
        form.appendChild(csrfInput);

        document.body.appendChild(form);
        form.submit();
    }

    // Dynamic AJAX lead details fetcher
    let drawerLastFocus = null;
    function loadLeadDetails(id, type, mode = 'view') {
        drawerTitle.innerText = type === 'general'
            ? (mode === 'edit' ? 'Edit general lead' : 'General lead')
            : (mode === 'edit' ? 'Edit EDI application' : 'EDI application');

        // Skeleton keeps the layout stable while the record loads.
        drawerBody.innerHTML = `
            <div class="lead-hero" aria-busy="true">
                <div class="lead-hero-top">
                    <div class="skel" style="width:46px;height:46px;border-radius:50%;"></div>
                    <div style="flex:1;">
                        <div class="skel" style="height:20px;width:55%;"></div>
                        <div class="skel" style="height:12px;width:75%;margin-top:10px;"></div>
                    </div>
                </div>
                <div class="skel" style="height:58px;margin-top:16px;"></div>
            </div>
            <div class="skel" style="height:150px;border-radius:16px;margin-bottom:14px;"></div>
            <div class="skel" style="height:220px;border-radius:16px;"></div>
        `;
        if (!drawer.classList.contains('active')) {
            drawerLastFocus = document.activeElement;
            drawer.classList.add('active');
            document.body.style.overflow = 'hidden';
            setTimeout(() => closeDrawer.focus(), 50);
        }
        drawer.querySelector('.drawer-content').scrollTop = 0;

        const formData = new FormData();
        formData.append('action', 'get_lead_details');
        formData.append('id', id);
        formData.append('type', type);
        formData.append('csrf_token', csrfToken);

        fetch('api.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if (mode === 'edit') {
                    renderLeadDrawerEditForm(data, type);
                } else {
                    renderLeadDrawerContent(data, type);
                }
            } else {
                throw new Error(data.message || 'Failed to fetch details.');
            }
        })
        .catch(err => {
            drawerBody.innerHTML = `
                <div class="info-card" style="text-align:center; padding:36px 20px;">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size:26px; color:var(--danger);"></i>
                    <div style="font-weight:600; margin-top:10px;">${escapeHtml(err.message || 'Failed to load details.')}</div>
                    <button type="button" class="lead-action" style="margin-top:14px;" onclick="loadLeadDetails(${jsArg(id)}, ${jsArg(type)}, ${jsArg(mode)})"><i class="fa-solid fa-rotate-right"></i> Try again</button>
                </div>
            `;
        });
    }

    function closeLeadDrawer(force = false) {
        if (!drawer.classList.contains('active')) return;
        const editForm = document.getElementById('drawerEditForm');
        if (!force && editForm && editForm.dataset.dirty === '1') {
            Swal.fire({
                title: 'Discard unsaved changes?',
                icon: 'warning',
                showCancelButton: true,
                focusCancel: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#475569',
                confirmButtonText: 'Discard',
                cancelButtonText: 'Keep editing'
            }).then(r => { if (r.isConfirmed) closeLeadDrawer(true); });
            return;
        }
        drawer.classList.remove('active');
        document.body.style.overflow = '';
        if (drawerLastFocus && typeof drawerLastFocus.focus === 'function') drawerLastFocus.focus();
    }

    // ---------- Drawer formatting helpers ----------
    const DRAWER_ACRONYMS = { pan: 'PAN', gst: 'GST', ifsc: 'IFSC', ip: 'IP', bre: 'BRE', cibil: 'CIBIL', aa: 'AA', upi: 'UPI', eod: 'EOD', gps: 'GPS', url: 'URL', id: 'ID', crif: 'CRIF', emi: 'EMI', kyc: 'KYC', otp: 'OTP', amb: 'AMB', crm: 'CRM', pdf: 'PDF' };
    function humanizeKey(key) {
        return String(key || '').split(/[_\s]+/).filter(Boolean).map((w, i) => {
            const lower = w.toLowerCase();
            if (DRAWER_ACRONYMS[lower]) return DRAWER_ACRONYMS[lower];
            return i === 0 ? lower.charAt(0).toUpperCase() + lower.slice(1) : lower;
        }).join(' ');
    }
    function isBlank(v) {
        return v === null || v === undefined || (typeof v === 'string' && v.trim() === '') || (Array.isArray(v) && v.length === 0);
    }
    const EMPTY = '<span class="empty">—</span>';
    function fmtInr(v) {
        const n = typeof v === 'number' ? v : parseFloat(String(v ?? '').replace(/,/g, ''));
        if (!isFinite(n)) return isBlank(v) ? EMPTY : escapeHtml(v);
        return '₹' + n.toLocaleString('en-IN', { maximumFractionDigits: 0 });
    }
    function fmtDateTime(v) {
        if (isBlank(v)) return EMPTY;
        const d = new Date(String(v).replace(' ', 'T'));
        if (isNaN(d.getTime())) return escapeHtml(v);
        return d.toLocaleString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
    }
    function relTime(v) {
        const d = new Date(String(v || '').replace(' ', 'T'));
        if (isNaN(d.getTime())) return '';
        const s = Math.round((Date.now() - d.getTime()) / 1000);
        if (s < 60) return 'just now';
        if (s < 3600) return Math.floor(s / 60) + ' min ago';
        if (s < 86400) return Math.floor(s / 3600) + ' h ago';
        const days = Math.floor(s / 86400);
        return days === 1 ? 'yesterday' : days + ' days ago';
    }
    function safeAssetUrl(p) {
        const value = String(p || '');
        if (/^uploads\/[\w\/.-]+$/.test(value)) return '../' + value;
        if (/^https:\/\//.test(value)) return value;
        return '';
    }
    function copyBtn(text, label) {
        if (isBlank(text)) return '';
        return `<button type="button" class="copy-inline" data-copy="${escapeHtml(text)}" title="Copy ${escapeHtml(label || '')}" aria-label="Copy ${escapeHtml(label || 'value')}"><i class="fa-regular fa-copy"></i></button>`;
    }
    function verifiedPill(flag) {
        if (flag === undefined || flag === null || flag === '') return '';
        return flag === true || flag === 1 || flag === '1' || flag === 'true'
            ? '<span class="vpill ok"><i class="fa-solid fa-circle-check"></i> Verified</span>'
            : '<span class="vpill no">Not verified</span>';
    }
    // Renders any JSON value readably: scalars inline, objects as a small key/value grid.
    function renderAny(v) {
        if (isBlank(v)) return EMPTY;
        if (typeof v === 'boolean') return v ? 'Yes' : 'No';
        if (Array.isArray(v)) {
            if (v.every(x => typeof x !== 'object' || x === null)) return v.map(x => escapeHtml(x)).join(', ');
            return v.map(x => renderAny(x)).join('');
        }
        if (typeof v === 'object') {
            const entries = Object.entries(v).filter(([, x]) => !isBlank(x));
            if (!entries.length) return EMPTY;
            return `<div class="kv-sub">${entries.map(([k, x]) => `
                <div><div class="k">${escapeHtml(humanizeKey(k))}</div><div class="v">${typeof x === 'object' ? escapeHtml(JSON.stringify(x)) : (typeof x === 'boolean' ? (x ? 'Yes' : 'No') : escapeHtml(x))}</div></div>
            `).join('')}</div>`;
        }
        return escapeHtml(v);
    }
    function field(label, valueHtml, opts = {}) {
        return `<div class="field-item${opts.span ? ' span-2' : ''}">
            <div class="field-label">${escapeHtml(label)}</div>
            <div class="field-value${opts.mono ? ' mono' : ''}">${isBlank(valueHtml) ? EMPTY : valueHtml}${opts.after || ''}</div>
        </div>`;
    }
    function textField(label, raw, opts = {}) {
        const shown = isBlank(raw) ? '' : escapeHtml(raw);
        return field(label, shown, { ...opts, after: (opts.copy && !isBlank(raw) ? copyBtn(raw, label) : '') + (opts.after || '') });
    }
    function section(id, icon, title, inner, opts = {}) {
        const extra = opts.right ? `<span>${opts.right}</span>` : '';
        if (opts.collapsible) {
            return `<details class="info-card" id="${id}"${opts.open ? ' open' : ''}>
                <summary><h3 class="card-title"><i class="fa-solid ${icon}"></i> ${escapeHtml(title)}</h3><i class="fa-solid fa-chevron-down chev"></i></summary>
                ${inner}
            </details>`;
        }
        return `<section class="info-card" id="${id}">
            <h3 class="card-title" style="display:flex; justify-content:space-between; align-items:center; gap:10px;"><span><i class="fa-solid ${icon}"></i> ${escapeHtml(title)}</span>${extra}</h3>
            ${inner}
        </section>`;
    }
    function maskAadhaar(v) {
        const digits = String(v || '').replace(/\D/g, '');
        if (digits.length < 4) return escapeHtml(v || '');
        return 'XXXX XXXX ' + digits.slice(-4);
    }
    function initialsOf(name) {
        const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
        return ((parts[0] || '?').charAt(0) + (parts.length > 1 ? parts[parts.length - 1].charAt(0) : '')).toUpperCase();
    }
    function phoneActions(mobile) {
        const digits = String(mobile || '').replace(/\D/g, '');
        if (digits.length < 10) return '';
        const ten = digits.slice(-10);
        return `
            <a class="lead-action" href="tel:+91${ten}"><i class="fa-solid fa-phone"></i> Call</a>
            <a class="lead-action wa" href="https://wa.me/91${ten}" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
        `;
    }

    // Render lead drawer content dynamically
    function renderLeadDrawerContent(data, type) {
        const lead = data.application || {};
        const cibil = data.cibil;
        const docChecks = data.docChecks || {};
        const activities = data.activities || [];
        const isGeneral = type === 'general';

        const appDisplayId = lead.application_id || 'N/A';
        let details = {};
        if (isGeneral) {
            try { details = JSON.parse(lead.loan_details || '{}') || {}; } catch (e) { details = { _parse_error: true }; }
        }
        const used = new Set(); // detail keys already shown in a structured section
        const d = (key) => { used.add(key); return details[key]; };
        // Shown via the table columns or a fallback chain, so never repeat them under "More".
        ['applicant_name', 'mobile_number', 'city', 'company_name', 'business_name', 'loan_type', 'created_at'].forEach(k => used.add(k));

        const name = isGeneral ? (lead.applicant_name || d('applicant_name')) : lead.applicant_name;
        const mobile = isGeneral ? (lead.mobile_number || d('mobile_number')) : lead.mobile_number;
        const category = isGeneral ? (lead.loan_type || d('loan_type')) : (lead.business_type || 'EDI loan');
        const source = isGeneral ? (d('source') || 'Portal direct') : (lead.source || 'EDI portal');
        const amount = lead.loan_amount;

        // ---------- Key figures ----------
        const breStatus = isGeneral ? String(d('bre_status') || '').toUpperCase() : '';
        let cibilScore = cibil && cibil.score !== null && cibil.score !== undefined ? parseInt(cibil.score, 10) : (isGeneral && !isBlank(d('cibil_score')) ? parseInt(d('cibil_score'), 10) : null);
        // A score of 0 or a "not checked" status means no bureau pull happened, not a poor score.
        if (cibilScore !== null && (isNaN(cibilScore) || cibilScore <= 0 || /not\s*checked/i.test(String((cibil && cibil.analysis_status) || details.cibil_status || '')))) cibilScore = null;
        const scoreTone = cibilScore === null || isNaN(cibilScore) ? 'muted' : (cibilScore >= 750 ? 'good' : (cibilScore >= 650 ? 'warn' : 'bad'));
        const stats = [
            { label: 'Requested', value: fmtInr(amount) },
        ];
        if (isGeneral) {
            stats.push({ label: 'Auto decision', value: breStatus ? escapeHtml(breStatus.charAt(0) + breStatus.slice(1).toLowerCase()) : '—', tone: breStatus === 'APPROVED' ? 'good' : (breStatus === 'DECLINED' || breStatus === 'REJECTED' ? 'bad' : (breStatus ? 'warn' : 'muted')) });
        } else {
            const st = String(lead.status || 'pending');
            stats.push({ label: 'Status', value: escapeHtml(st.charAt(0).toUpperCase() + st.slice(1)), tone: st === 'approved' ? 'good' : (st === 'rejected' ? 'bad' : 'warn') });
        }
        stats.push({ label: 'CIBIL score', value: cibilScore === null || isNaN(cibilScore) ? '—' : String(cibilScore), tone: scoreTone });
        if (isGeneral && !isBlank(details.monthly_inflow)) {
            stats.push({ label: 'Monthly inflow', value: fmtInr(details.monthly_inflow) });
        } else if (!isGeneral && !isBlank(lead.monthly_income)) {
            stats.push({ label: 'Monthly income', value: fmtInr(lead.monthly_income) });
        }

        const heroHtml = `
            <div class="lead-hero" id="sec-overview">
                <div class="lead-hero-top">
                    <div class="lead-avatar">${escapeHtml(initialsOf(name))}</div>
                    <div class="lead-hero-main">
                        <div class="lead-name">${isBlank(name) ? '<span class="empty">Name not provided</span>' : escapeHtml(name)}</div>
                        <div class="lead-meta">
                            <span class="lead-id">${escapeHtml(appDisplayId)}</span>${copyBtn(appDisplayId, 'application ID')}
                            ${!isBlank(category) ? `<span class="lead-chip">${escapeHtml(String(category).replace(/[_-]+/g, ' '))}</span>` : ''}
                            <span class="sep">•</span>
                            <span title="${escapeHtml(lead.created_at || '')}">Applied ${escapeHtml(relTime(lead.created_at) || '')}</span>
                            <span class="sep">•</span>
                            <span>${escapeHtml(String(source).replace(/[_-]+/g, ' '))}</span>
                        </div>
                    </div>
                </div>
                <div class="lead-actions">
                    ${phoneActions(mobile)}
                    <button type="button" class="lead-action" onclick="loadLeadDetails(${jsArg(lead.id)}, ${jsArg(type)}, 'edit')"><i class="fa-solid fa-pen"></i> Edit</button>
                </div>
                <div class="lead-stats">
                    ${stats.map(s => `<div class="lead-stat"><div class="lead-stat-label">${escapeHtml(s.label)}</div><div class="lead-stat-value ${s.tone || ''}">${s.value}</div></div>`).join('')}
                </div>
            </div>
        `;

        const nav = [{ id: 'sec-overview', label: 'Overview' }];
        let body = '';

        // ---------- EDI: status first (it is the action an underwriter takes) ----------
        if (!isGeneral) {
            const currentStatus = (lead.status || 'pending').toLowerCase();
            const steps = ['pending', 'submitted', 'approved'];
            const isRejected = currentStatus === 'rejected';
            let statusIndex = steps.indexOf(currentStatus);
            if (statusIndex === -1) statusIndex = isRejected ? 2 : 0;
            const progressPct = currentStatus === 'approved' ? 90 : (statusIndex > 0 ? (statusIndex / (steps.length - 1)) * 90 : 0);

            body += section('sec-status', 'fa-clipboard-check', 'Status', `
                <div class="status-timeline" style="margin-bottom:12px;">
                    <div class="timeline-progress-bar" style="width:${progressPct}%;"></div>
                    <div class="timeline-step ${statusIndex >= 0 ? 'completed' : ''} ${currentStatus === 'pending' ? 'active' : ''}"><div class="step-circle">1</div><div class="step-label">Pending</div></div>
                    <div class="timeline-step ${statusIndex >= 1 ? 'completed' : ''} ${currentStatus === 'submitted' ? 'active' : ''}"><div class="step-circle">2</div><div class="step-label">Submitted</div></div>
                    ${isRejected
                        ? '<div class="timeline-step rejected"><div class="step-circle">&times;</div><div class="step-label">Rejected</div></div>'
                        : `<div class="timeline-step ${statusIndex >= 2 ? 'completed' : ''} ${currentStatus === 'approved' ? 'active' : ''}"><div class="step-circle">3</div><div class="step-label">Approved</div></div>`}
                </div>
                <div class="field-label">Change status</div>
                <div class="status-select-wrap" style="margin-top:4px;">
                    <select id="updateStatusSelect" class="select-filter" style="flex:1;">
                        <option value="pending" ${currentStatus === 'pending' ? 'selected' : ''}>Pending</option>
                        <option value="submitted" ${currentStatus === 'submitted' ? 'selected' : ''}>Submitted</option>
                        <option value="approved" ${currentStatus === 'approved' ? 'selected' : ''}>Approved</option>
                        <option value="rejected" ${currentStatus === 'rejected' ? 'selected' : ''}>Rejected</option>
                    </select>
                    <button type="button" class="btn-primary-adm" id="saveStatusBtn" onclick="updateLeadStatus(${jsArg(lead.id)}, '${type}')" style="box-shadow:none;">Update</button>
                </div>
                <small id="statusMsg" style="display:block; margin-top:8px; font-weight:600;"></small>
            `);
            nav.push({ id: 'sec-status', label: 'Status' });
        }

        // ---------- General: auto-underwriting decision ----------
        if (isGeneral && (breStatus || !isBlank(details.bre_approved_limit))) {
            const isApproved = breStatus === 'APPROVED';
            const isDeclined = breStatus === 'DECLINED' || breStatus === 'REJECTED';
            const reasons = Array.isArray(d('bre_decline_reasons')) ? details.bre_decline_reasons : [];
            const limit = d('bre_approved_limit');
            const tenure = parseInt(d('bre_tenure_days') || 0, 10);
            const eod = d('bre_eod_balance');
            const upi = d('bre_upi_credits');
            const crif = d('bre_crif_check');
            const figures = [
                !isBlank(limit) && Number(limit) > 0 ? `<div>Approved limit<strong>${fmtInr(limit)}</strong></div>` : '',
                tenure > 0 ? `<div>Tenure<strong>${Math.round(tenure / 30)} months (${tenure} days)</strong></div>` : '',
                !isBlank(eod) ? `<div>EOD balance<strong>${fmtInr(eod)}</strong></div>` : '',
                !isBlank(upi) ? `<div>UPI credits<strong>${escapeHtml(upi)}</strong></div>` : '',
            ].join('');
            body += `
                <div class="decision ${isApproved ? 'approved' : (isDeclined ? 'declined' : 'other')}" id="sec-decision">
                    <div class="decision-icon"><i class="fa-solid ${isApproved ? 'fa-circle-check' : (isDeclined ? 'fa-circle-xmark' : 'fa-circle-exclamation')}"></i></div>
                    <div style="flex:1; min-width:0;">
                        <div class="decision-title">Auto-underwriting: ${escapeHtml(breStatus ? breStatus.charAt(0) + breStatus.slice(1).toLowerCase() : 'Pending')}</div>
                        <div class="decision-sub">${isApproved ? 'The rules engine pre-approved this application.' : (isDeclined ? `Declined by the rules engine${reasons.length ? ' for ' + reasons.length + ' reason' + (reasons.length === 1 ? '' : 's') : ''}. Review before contacting the customer.` : 'Decision needs manual review.')}</div>
                        ${reasons.length ? `<ul class="decision-reasons">${reasons.map(r => `<li><i class="fa-solid fa-circle-xmark"></i><span>${escapeHtml(r)}</span></li>`).join('')}</ul>` : ''}
                        ${figures ? `<div class="decision-figures">${figures}</div>` : ''}
                        ${!isBlank(crif) ? `<details style="margin-top:10px;"><summary style="cursor:pointer; font-size:12.5px; font-weight:600; color:var(--text-secondary);">CRIF check details</summary><div style="margin-top:8px;">${renderAny(crif)}</div></details>` : ''}
                    </div>
                </div>
            `;
            nav.push({ id: 'sec-decision', label: 'Decision' });
        }

        // ---------- Credit score ----------
        if (cibil || (isGeneral && (!isBlank(details.cibil_score) || !isBlank(details.cibil_status)))) {
            const label = scoreTone === 'good' ? 'Excellent' : (scoreTone === 'warn' ? 'Fair' : (scoreTone === 'bad' ? 'Poor' : 'Not checked'));
            const pdfUrl = isGeneral ? safeAssetUrl(d('cibil_pdf_url')) : '';
            d('cibil_score'); d('cibil_status');
            body += section('sec-credit', 'fa-gauge-high', 'Credit score', `
                <div style="display:flex; gap:18px; align-items:center; flex-wrap:wrap;">
                    <div class="cibil-gauge-wrapper" style="border-color:${scoreTone === 'good' ? '#bbf7d0' : (scoreTone === 'warn' ? '#fde68a' : (scoreTone === 'bad' ? '#fecaca' : '#f1f5f9'))};">
                        <div class="cibil-score-value ${scoreTone === 'good' ? 'score-excellent' : (scoreTone === 'warn' ? 'score-fair' : (scoreTone === 'bad' ? 'score-poor' : 'score-none'))}">${cibilScore === null || isNaN(cibilScore) ? 'N/A' : cibilScore}</div>
                        <div class="cibil-score-label">${label}</div>
                    </div>
                    <div class="grid-fields" style="flex:1; min-width:220px;">
                        ${textField('Bureau status', cibil ? (cibil.analysis_status || 'Verified') : details.cibil_status)}
                        ${textField('Reference ID', cibil ? cibil.ref_id : '', { mono: true, copy: true })}
                        ${pdfUrl ? field('Report', `<a class="doc-preview-link" href="${escapeHtml(pdfUrl)}" target="_blank" rel="noopener"><i class="fa-solid fa-file-pdf"></i> Open CIBIL PDF</a>`, { span: true }) : ''}
                    </div>
                </div>
            `);
            nav.push({ id: 'sec-credit', label: 'Credit' });
        }

        // ---------- Applicant / KYC ----------
        if (isGeneral) {
            body += section('sec-kyc', 'fa-id-card', 'Applicant & KYC', `
                <div class="grid-fields">
                    ${textField('Full name', name)}
                    ${field('Mobile', isBlank(mobile) ? '' : escapeHtml(mobile), { mono: true, after: copyBtn(mobile, 'mobile') + verifiedPill(d('mobile_verified')) })}
                    ${textField('Email', d('email'), { copy: true })}
                    ${field('PAN', isBlank(lead.pancard_number || details.pan_number) ? '' : escapeHtml(String(lead.pancard_number || details.pan_number).toUpperCase()), { mono: true, after: copyBtn(lead.pancard_number || details.pan_number, 'PAN') + verifiedPill(d('pan_verified')) })}
                    ${d('pan_number') === undefined ? '' : ''}
                    ${field('Aadhaar', isBlank(d('aadhaar_masked')) ? '' : escapeHtml(details.aadhaar_masked), { mono: true, after: verifiedPill(d('aadhaar_verified')) })}
                    ${textField('Employment', d('employment_status'))}
                    ${field('Monthly income', isBlank(d('monthly_income')) ? '' : fmtInr(details.monthly_income))}
                    ${field('Applied on', fmtDateTime(lead.created_at || d('created_at')))}
                    ${!isBlank(details.pan_verification_details) ? field('PAN verification', renderAny(d('pan_verification_details')), { span: true }) : ''}
                </div>
            `);
        } else {
            // Only real Aadhaar numbers get masking + reveal; placeholders like "N/A" show as empty.
            const aad = String(lead.aadhaar_number || '').replace(/\D/g, '').length >= 4 ? lead.aadhaar_number : '';
            body += section('sec-kyc', 'fa-id-card', 'Applicant & KYC', `
                <div class="grid-fields">
                    ${textField('Full name', lead.applicant_name)}
                    ${textField('Mobile', lead.mobile_number, { mono: true, copy: true })}
                    ${textField('Email', lead.email, { copy: true })}
                    ${field('PAN', isBlank(lead.pan_number) ? '' : escapeHtml(String(lead.pan_number).toUpperCase()), { mono: true, after: copyBtn(lead.pan_number, 'PAN') })}
                    ${field('Aadhaar', isBlank(aad) ? '' : `<span data-masked="${escapeHtml(maskAadhaar(aad))}" data-full="${escapeHtml(aad)}">${maskAadhaar(aad)}</span>`, { mono: true, after: isBlank(aad) ? '' : '<button type="button" class="reveal-btn" data-reveal>Show</button>' })}
                    ${field('Applied on', fmtDateTime(lead.created_at))}
                    ${textField('City', lead.city)}
                    ${textField('Pincode', lead.pincode, { mono: true })}
                    ${textField('Address', lead.current_address, { span: true })}
                </div>
            `);
        }
        nav.push({ id: 'sec-kyc', label: 'KYC' });

        // ---------- Loan & business ----------
        if (isGeneral) {
            const photos = [];
            const single = safeAssetUrl(d('shop_photo_url')) || safeAssetUrl(d('shop_photo'));
            if (single) photos.push(single);
            (Array.isArray(d('shop_photos')) ? details.shop_photos : []).forEach(p => { const u = safeAssetUrl(p); if (u && !photos.includes(u)) photos.push(u); });
            d('loan_type'); d('loan_amount'); d('application_ref');
            const hasBusiness = ['company_name', 'business_name', 'business_type', 'gst_number', 'udyam_number', 'annual_turnover'].some(k => !isBlank(details[k])) || photos.length;

            body += section('sec-loan', 'fa-indian-rupee-sign', 'Loan request', `
                <div class="grid-fields">
                    ${field('Requested amount', fmtInr(amount))}
                    ${field('Approved limit', isBlank(d('approved_limit')) || Number(details.approved_limit) === 0 ? '' : fmtInr(details.approved_limit))}
                    ${textField('Tenure', d('loan_tenure'))}
                    ${textField('Purpose', d('loan_purpose'))}
                </div>
            `);
            nav.push({ id: 'sec-loan', label: 'Loan' });

            if (hasBusiness) {
                body += section('sec-business', 'fa-store', 'Business', `
                    <div class="grid-fields">
                        ${textField('Business name', d('business_name') || d('company_name'))}
                        ${textField('Business type', d('business_type'))}
                        ${field('GST number', isBlank(d('gst_number')) ? '' : escapeHtml(details.gst_number), { mono: true, after: copyBtn(details.gst_number, 'GST') + verifiedPill(d('gst_verified')) })}
                        ${field('Udyam number', isBlank(d('udyam_number')) ? '' : escapeHtml(details.udyam_number), { mono: true, after: verifiedPill(d('udyam_verified')) })}
                        ${textField('Annual turnover', d('annual_turnover'))}
                        ${photos.length ? field('Shop photos', `<div class="photo-row">${photos.map(u => `<a href="${escapeHtml(u)}" target="_blank" rel="noopener" title="Open photo"><img src="${escapeHtml(u)}" alt="Shop photo" loading="lazy" onerror="this.closest('a').style.display='none'"></a>`).join('')}</div>`, { span: true }) : ''}
                        ${!isBlank(details.gst_verification_details) ? field('GST verification', renderAny(d('gst_verification_details')), { span: true }) : ''}
                        ${!isBlank(details.udyam_verification_details) ? field('Udyam verification', renderAny(d('udyam_verification_details')), { span: true }) : ''}
                    </div>
                `);
                nav.push({ id: 'sec-business', label: 'Business' });
            }
        } else {
            body += section('sec-loan', 'fa-indian-rupee-sign', 'Loan & business', `
                <div class="grid-fields">
                    ${field('Loan amount', fmtInr(lead.loan_amount))}
                    ${textField('Purpose', lead.loan_purpose)}
                    ${field('Monthly income', isBlank(lead.monthly_income) ? '' : fmtInr(lead.monthly_income))}
                    ${field('Daily sales', isBlank(lead.daily_sales) ? '' : fmtInr(lead.daily_sales))}
                    ${textField('Business name', lead.shop_business_name)}
                    ${textField('Business type', lead.business_type)}
                    ${textField('Registration', lead.registration_type)}
                    ${textField('Business age', lead.business_age)}
                </div>
            `);
            nav.push({ id: 'sec-loan', label: 'Business' });

            const ref = (n, rel, mob) => isBlank(n) && isBlank(mob) ? EMPTY : `${escapeHtml(n || '')}${isBlank(rel) ? '' : ` <span class="lead-chip">${escapeHtml(rel)}</span>`}${isBlank(mob) ? '' : ` · <a href="tel:${escapeHtml(mob)}" style="color:var(--primary); text-decoration:none;">${escapeHtml(mob)}</a>`}`;
            body += section('sec-refs', 'fa-users', 'References', `
                <div class="grid-fields">
                    ${field('Primary reference', ref(lead.primary_ref_name, lead.primary_ref_relation, lead.primary_ref_mobile), { span: true })}
                    ${field('Secondary reference', ref(lead.secondary_ref_name, lead.secondary_ref_relation, lead.secondary_ref_mobile), { span: true })}
                </div>
            `);
        }

        // ---------- Bank ----------
        if (isGeneral) {
            const aaTxnId = d('account_aggregator_txn_id') || d('aa_txn_id') || '';
            const isAa = d('account_aggregator_verified') || aaTxnId;
            const hasBank = isAa || ['bank_name', 'account_number', 'ifsc_code'].some(k => !isBlank(details[k]));
            if (hasBank) {
                const aNum = d('account_number') || '';
                d('monthly_inflow'); d('average_monthly_balance'); d('bank_name'); d('ifsc_code'); d('account_type');
                body += section('sec-bank', 'fa-building-columns', 'Bank account', `
                    <div class="grid-fields">
                        ${textField('Bank', details.bank_name)}
                        ${textField('Account number', aNum, { mono: true })}
                        ${textField('IFSC', details.ifsc_code ? String(details.ifsc_code).toUpperCase() : '', { mono: true, copy: true })}
                        ${textField('Account type', details.account_type)}
                        ${field('Monthly inflow', isBlank(details.monthly_inflow) ? '' : fmtInr(details.monthly_inflow))}
                        ${field('Average balance', isBlank(details.average_monthly_balance) ? '' : fmtInr(details.average_monthly_balance))}
                        ${isAa ? textField('AA transaction ID', aaTxnId || 'Verified (auto-resolved)', { mono: true, copy: !!aaTxnId, span: true }) : ''}
                    </div>
                    ${isAa ? `
                        <div class="aa-actions">
                            <button type="button" class="btn-statement-view" onclick="openMasterStatementModal(${jsArg(aaTxnId)}, ${jsArg(encodeURIComponent(lead.mobile_number || ''))}, ${jsArg(encodeURIComponent(aNum || ''))}, ${jsArg(encodeURIComponent(lead.id || lead.application_id || ''))})">
                                <i class="fa-solid fa-file-invoice"></i> View full statement
                            </button>
                            <a href="../api/bank-data/export-statement.php?txn_id=${encodeURIComponent(aaTxnId)}&mobile=${encodeURIComponent(lead.mobile_number || '')}&account=${encodeURIComponent(aNum || '')}&lead_id=${encodeURIComponent(lead.id || lead.application_id || '')}" class="btn-statement-csv" target="_blank" rel="noopener">
                                <i class="fa-solid fa-download"></i> Download CSV
                            </a>
                        </div>` : ''}
                `, { right: isAa ? '<span class="vpill ok"><i class="fa-solid fa-circle-check"></i> Account Aggregator verified</span>' : '' });
                nav.push({ id: 'sec-bank', label: 'Bank' });
            }
        } else {
            body += section('sec-bank', 'fa-building-columns', 'Bank account', `
                <div class="grid-fields">
                    ${textField('Account holder', lead.account_holder)}
                    ${textField('Bank', lead.bank_name)}
                    ${textField('Account number', lead.account_number, { mono: true, copy: true })}
                    ${textField('IFSC', lead.ifsc_code ? String(lead.ifsc_code).toUpperCase() : '', { mono: true, copy: true })}
                </div>
            `);
            nav.push({ id: 'sec-bank', label: 'Bank' });
        }

        // ---------- EDI: document checklist ----------
        let docsPending = 0;
        if (!isGeneral) {
            const verificationItems = [
                { key: 'pan_card_doc', label: 'PAN card', path: lead.pan_card_doc },
                { key: 'aadhaar_card_doc', label: 'Aadhaar card', path: lead.aadhaar_card_doc },
                { key: 'bank_statement_doc', label: 'Bank statement', path: lead.bank_statement_doc },
                { key: 'business_registration_doc', label: 'Business registration', path: lead.business_registration_doc },
                { key: 'passport_photo_doc', label: 'Passport photo', path: lead.passport_photo_doc },
                { key: 'selfie_doc', label: 'Selfie', path: lead.selfie_doc },
                { key: 'shop_front_photo', label: 'Shop front photo', path: lead.shop_front_photo },
                { key: 'stock_photo', label: 'Stock photo 1', path: lead.stock_photo },
                { key: 'stock_photo_two', label: 'Stock photo 2', path: lead.stock_photo_two }
            ];
            const uploaded = verificationItems.filter(i => !isBlank(i.path));
            const verifiedCount = uploaded.filter(i => (docChecks[i.key] || {}).status === 'verified').length;
            docsPending = uploaded.filter(i => !['verified', 'rejected'].includes((docChecks[i.key] || {}).status)).length;
            const pct = uploaded.length ? Math.round((verifiedCount / uploaded.length) * 100) : 0;

            const rows = verificationItems.map(item => {
                const check = docChecks[item.key] || {};
                const currentStatus = check.status || 'pending';
                const meta = check.verified_by ? `${escapeHtml(currentStatus === 'rejected' ? 'Rejected' : 'Verified')} by ${escapeHtml(check.verified_by)}${check.verified_at ? ' · ' + fmtDateTime(check.verified_at) : ''}` : '';
                return `
                    <div class="doc-item" id="doc-row-${escapeHtml(item.key)}">
                        <div class="doc-item-top">
                            <div>
                                <div class="doc-name">${escapeHtml(item.label)}</div>
                                ${meta ? `<div class="doc-meta">${meta}</div>` : ''}
                            </div>
                            <div class="doc-item-right">
                                ${item.path ? getDocLink(item.path, item.label) : '<span class="doc-missing">Not uploaded</span>'}
                                ${item.path ? `<span class="checklist-status-badge checklist-status-${escapeHtml(currentStatus)}" id="badge-doc-${escapeHtml(item.key)}">${escapeHtml(currentStatus)}</span>` : ''}
                            </div>
                        </div>
                        ${item.path ? `
                            <div class="doc-review">
                                <input type="text" id="remark-doc-${escapeHtml(item.key)}" placeholder="Remark (optional)" value="${escapeHtml(check.remark || '')}" aria-label="Remark for ${escapeHtml(item.label)}">
                                <button type="button" class="checklist-action-btn checklist-btn-verify" onclick="updateDrawerDocStatus(${jsArg(lead.id)}, ${jsArg(appDisplayId)}, ${jsArg(item.key)}, ${jsArg(item.label)}, 'verified', '${type}')"><i class="fa-solid fa-check"></i> Approve</button>
                                <button type="button" class="checklist-action-btn checklist-btn-reject" onclick="updateDrawerDocStatus(${jsArg(lead.id)}, ${jsArg(appDisplayId)}, ${jsArg(item.key)}, ${jsArg(item.label)}, 'rejected', '${type}')"><i class="fa-solid fa-xmark"></i> Reject</button>
                            </div>` : ''}
                    </div>
                `;
            }).join('');

            body += section('sec-docs', 'fa-folder-open', 'Documents', `
                <div class="doc-progress">
                    <span>${verifiedCount} of ${uploaded.length} uploaded verified</span>
                    <div class="track"><div class="fill" style="width:${pct}%;"></div></div>
                    <span>${verificationItems.length - uploaded.length} missing</span>
                </div>
                <div class="doc-list">${rows}</div>
            `);
            nav.push({ id: 'sec-docs', label: 'Documents', count: docsPending });
        }

        // ---------- General: location, technical, CRM, remaining fields ----------
        if (isGeneral) {
            const lat = d('latitude'), lng = d('longitude');
            const mapLink = !isBlank(lat) && !isBlank(lng) && isFinite(parseFloat(lat)) && isFinite(parseFloat(lng))
                ? `<a class="doc-preview-link" href="https://www.google.com/maps?q=${encodeURIComponent(parseFloat(lat) + ',' + parseFloat(lng))}" target="_blank" rel="noopener"><i class="fa-solid fa-location-dot"></i> Open in Maps</a>` : '';
            body += section('sec-address', 'fa-location-dot', 'Address & location', `
                <div class="grid-fields">
                    ${textField('Address', d('address'), { span: true })}
                    ${textField('City', lead.city || d('city'))}
                    ${textField('State', d('state'))}
                    ${textField('Pincode', d('pincode'), { mono: true })}
                    ${field('GPS', mapLink)}
                    ${textField('GPS address', d('gps_address'), { span: true })}
                </div>
            `);
            nav.push({ id: 'sec-address', label: 'Address' });

            const sync = d('crm_sync');
            if (sync) {
                const syncOk = sync.status === 'success' || sync.status === 'duplicate';
                const syncLabel = sync.status === 'success' ? 'Sent to CRM' : (sync.status === 'duplicate' ? 'Already in CRM (added to existing lead timeline)' : 'Not sent to CRM');
                body += `<div class="decision ${syncOk ? 'approved' : 'declined'}" style="padding:12px 16px;">
                    <div class="decision-icon" style="width:30px;height:30px;font-size:13px;"><i class="fa-solid fa-arrows-rotate"></i></div>
                    <div><div class="decision-title" style="font-size:13.5px;">${escapeHtml(syncLabel)}</div>
                    <div class="decision-sub">${[sync.lead_number, !syncOk ? sync.message : '', sync.at].filter(x => !isBlank(x)).map(escapeHtml).join(' · ')}</div></div>
                </div>`;
            }

            const tech = ['ip_address', 'created_ip', 'device_fingerprint'].filter(k => !isBlank(details[k]));
            // Never shown: encrypted Aadhaar and the raw statement payload.
            ['aadhaar_encrypted', 'aa_data', 'pan_number', 'created_at', 'account_aggregator_verified'].forEach(k => used.add(k));
            tech.forEach(k => used.add(k));
            const rest = Object.entries(details).filter(([k]) => !used.has(k) && k !== '_parse_error');

            const extraInner = `
                <div class="grid-fields">
                    ${rest.map(([k, v]) => field(humanizeKey(k), renderAny(v), { span: typeof v === 'object' && v !== null })).join('')}
                    ${tech.map(k => textField(humanizeKey(k), details[k], { mono: true, span: k === 'device_fingerprint' })).join('')}
                    ${details._parse_error ? '<div class="span-2" style="color:var(--text-muted); font-style:italic;">Form data could not be read.</div>' : ''}
                </div>
            `;
            if (rest.length || tech.length || details._parse_error) {
                body += section('sec-more', 'fa-list', `More submitted data (${rest.length + tech.length})`, extraInner, { collapsible: true });
            }
        }

        // ---------- Activity ----------
        const activitiesHtml = activities.map(act => {
            let dotClass = 'dot-comment';
            if (act.type === 'document') dotClass = 'dot-document';
            if (act.type === 'underwriting') dotClass = 'dot-underwriting';
            if (act.type === 'followup') dotClass = 'dot-followup';
            return `
                <div class="timeline-item">
                    <div class="timeline-item-dot ${dotClass}"></div>
                    <div class="timeline-item-header">
                        <span class="timeline-item-actor">${escapeHtml(act.actor || 'System')}</span>
                        <span title="${escapeHtml(act.created_at || '')}">${fmtDateTime(act.created_at)}</span>
                    </div>
                    <div class="timeline-item-desc">${escapeHtml(act.description)}</div>
                </div>
            `;
        }).join('');

        body += section('sec-activity', 'fa-clock-rotate-left', 'Notes & activity', `
            <form id="drawerCommentForm" class="note-form" onsubmit="submitDrawerComment(event, ${jsArg(lead.id)}, ${jsArg(appDisplayId)}, '${type}')">
                <input type="text" name="description" placeholder="Add a call log or verification note…" required maxlength="1000" aria-label="New note">
                <button type="submit" class="btn-primary-adm"><i class="fa-solid fa-paper-plane"></i> Add note</button>
            </form>
            ${activitiesHtml === ''
                ? '<div style="font-size:12.5px; color:var(--text-muted); text-align:center; padding:12px 0;">No notes or activity yet.</div>'
                : `<div class="timeline-container">${activitiesHtml}</div>`}
        `, { right: activities.length ? `<span class="lead-chip">${activities.length}</span>` : '' });
        nav.push({ id: 'sec-activity', label: 'Activity', plain: activities.length });

        const navHtml = `<nav class="drawer-nav" aria-label="Jump to section">${nav.map((n, i) => `<button type="button" data-jump="${n.id}"${i === 0 ? ' class="current"' : ''}>${escapeHtml(n.label)}${n.count ? `<span class="nav-count">${n.count}</span>` : ''}</button>`).join('')}</nav>`;

        drawerBody.innerHTML = heroHtml + navHtml + body;
        drawerTitle.innerText = isGeneral ? 'General lead' : 'EDI application';
        setupDrawerScrollSpy();
    }

    // Highlight the section currently in view in the sticky nav.
    let drawerSpyObserver = null;
    function setupDrawerScrollSpy() {
        if (drawerSpyObserver) drawerSpyObserver.disconnect();
        const scroller = drawer.querySelector('.drawer-content');
        const buttons = Array.from(drawerBody.querySelectorAll('.drawer-nav [data-jump]'));
        if (!('IntersectionObserver' in window) || !buttons.length) return;
        drawerSpyObserver = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                buttons.forEach(b => b.classList.toggle('current', b.getAttribute('data-jump') === entry.target.id));
            });
        }, { root: scroller, rootMargin: '-120px 0px -60% 0px', threshold: 0 });
        buttons.forEach(b => {
            const target = document.getElementById(b.getAttribute('data-jump'));
            if (target) drawerSpyObserver.observe(target);
        });
    }

    // Drawer-level click helpers: jump nav, copy, reveal Aadhaar.
    drawerBody.addEventListener('click', (event) => {
        const jump = event.target.closest('[data-jump]');
        if (jump) {
            const target = document.getElementById(jump.getAttribute('data-jump'));
            if (target) {
                if (target.tagName === 'DETAILS') target.open = true;
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            return;
        }
        const copy = event.target.closest('[data-copy]');
        if (copy) {
            const text = copy.getAttribute('data-copy') || '';
            const done = () => {
                const icon = copy.querySelector('i');
                copy.classList.add('copied');
                if (icon) icon.className = 'fa-solid fa-check';
                setTimeout(() => { copy.classList.remove('copied'); if (icon) icon.className = 'fa-regular fa-copy'; }, 1200);
            };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(done).catch(() => {});
            } else {
                const ta = document.createElement('textarea');
                ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
                document.body.appendChild(ta); ta.select();
                try { document.execCommand('copy'); done(); } catch (e) {}
                ta.remove();
            }
            return;
        }
        const reveal = event.target.closest('[data-reveal]');
        if (reveal) {
            const span = reveal.parentElement.querySelector('[data-full]');
            if (!span) return;
            const showing = reveal.textContent === 'Hide';
            span.textContent = showing ? span.getAttribute('data-masked') : span.getAttribute('data-full');
            reveal.textContent = showing ? 'Show' : 'Hide';
        }
    });

    // Update Drawer Doc Status via AJAX
    function updateDrawerDocStatus(leadId, appId, docKey, label, status, type) {
        const remarkInput = document.getElementById('remark-doc-' + docKey);
        const remarkVal = remarkInput ? remarkInput.value : '';

        Swal.fire({
            title: 'Updating document status...',
            text: `Setting ${label} to ${status.toUpperCase()}`,
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData();
        formData.append('action', 'verify_lead_document');
        formData.append('id', leadId);
        formData.append('application_id', appId);
        formData.append('document_key', docKey);
        formData.append('label', label);
        formData.append('status', status);
        formData.append('remark', remarkVal);
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token'] ?? ''; ?>');

        fetch('api.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Document Saved!',
                    timer: 1000,
                    showConfirmButton: false
                });
                
                loadLeadDetails(leadId, type);
            } else {
                throw new Error(data.message || 'Failed to update document.');
            }
        })
        .catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: err.message
            });
        });
    }

    // Submit Drawer Comment via AJAX
    function submitDrawerComment(e, leadId, appId, type) {
        e.preventDefault();
        const form = e.target;
        const noteInput = form.querySelector('input[name="description"]');
        const noteVal = noteInput.value;

        Swal.fire({
            title: 'Saving verification note...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData();
        formData.append('action', 'add_lead_activity');
        formData.append('id', leadId);
        formData.append('application_id', appId);
        formData.append('description', noteVal);
        formData.append('type', 'comment');
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token'] ?? ''; ?>');

        fetch('api.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Note Logged!',
                    timer: 1000,
                    showConfirmButton: false
                });
                loadLeadDetails(leadId, type);
            } else {
                throw new Error(data.message || 'Failed to log note.');
            }
        })
        .catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Note Failed',
                text: err.message
            });
        });
    }

    // Update Lead Status via AJAX
    function updateLeadStatus(appId, type) {
        const select = document.getElementById('updateStatusSelect');
        const newStatus = select.value;
        const statusMsg = document.getElementById('statusMsg');

        statusMsg.style.color = 'var(--primary)';
        statusMsg.innerText = 'Updating status...';

        const formData = new FormData();
        formData.append('action', type === 'edi' ? 'update_edi_status' : 'update_app_status');
        formData.append('id', appId);
        formData.append('status', newStatus);
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token'] ?? ''; ?>');

        fetch('api.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                statusMsg.style.color = 'var(--success)';
                statusMsg.innerText = 'Status updated successfully!';
                
                const badge = document.getElementById(`badge-${appId}`);
                if (badge) {
                    const statusClass = data.mapped_status || newStatus;
                    badge.className = `badge-status badge-${statusClass}`;
                    badge.innerText = statusClass;
                }
                
                loadLeadDetails(appId, type);
                refreshMasterLeads({ silent: true, updateUrl: false });
            } else {
                throw new Error(data.message || 'Failed to update status.');
            }
        })
        .catch(err => {
            statusMsg.style.color = 'var(--danger)';
            statusMsg.innerText = err.message || 'Network error.';
        });
    }

    // Render Edit Form in drawer
    // ---------- Edit form ----------
    function editField(label, name, value, opts = {}) {
        const id = 'edit-' + name;
        const v = value === null || value === undefined ? '' : String(value);
        const attrs = [
            opts.required ? 'required' : '',
            opts.inputmode ? `inputmode="${opts.inputmode}"` : '',
            opts.maxlength ? `maxlength="${opts.maxlength}"` : '',
            opts.upper ? 'data-upper style="text-transform:uppercase;"' : '',
            opts.type === 'number' ? 'min="0" step="any"' : '',
            `autocomplete="${opts.autocomplete || 'off'}"`,
        ].filter(Boolean).join(' ');
        const input = `<input id="${id}" type="${opts.type || 'text'}" name="${name}" value="${escapeHtml(v)}" data-original="${escapeHtml(v)}" ${attrs}>`;
        return `
            <div class="edit-field${opts.span ? ' span-2' : ''}">
                <label for="${id}">${escapeHtml(label)}${opts.required ? ' <span class="req" aria-hidden="true">*</span>' : ''}</label>
                ${opts.prefix ? `<div class="edit-prefix"><span>${opts.prefix}</span>${input}</div>` : input}
                ${opts.hint ? `<div class="hint">${escapeHtml(opts.hint)}</div>` : ''}
            </div>
        `;
    }

    function editSection(icon, title, inner) {
        return `<section class="info-card"><h3 class="card-title"><i class="fa-solid ${icon}"></i> ${escapeHtml(title)}</h3><div class="edit-grid">${inner}</div></section>`;
    }

    function renderLeadDrawerEditForm(data, type) {
        const lead = data.application || {};
        const appDisplayId = lead.application_id || 'N/A';
        const isGeneral = type === 'general';

        let sections = '';
        if (isGeneral) {
            // Keep whatever is stored: an empty or unknown category must not silently become "Personal" on save.
            const knownTypes = [['personal', 'Personal'], ['business', 'Business'], ['vehicle', 'Vehicle'], ['lap', 'LAP (Property)']];
            const currentType = String(lead.loan_type || '');
            const typeOptions = [
                `<option value="" ${currentType === '' ? 'selected' : ''}>Not set</option>`,
                ...knownTypes.map(([v, l]) => `<option value="${v}" ${currentType === v ? 'selected' : ''}>${l}</option>`),
                currentType !== '' && !knownTypes.some(([v]) => v === currentType) ? `<option value="${escapeHtml(currentType)}" selected>${escapeHtml(currentType)}</option>` : '',
            ].join('');

            sections += editSection('fa-user', 'Applicant', `
                ${editField('Full name', 'applicant_name', lead.applicant_name, { required: true, span: true, autocomplete: 'name' })}
                ${editField('Mobile number', 'mobile_number', lead.mobile_number, { required: true, inputmode: 'tel', maxlength: 15 })}
                ${editField('PAN', 'pancard_number', lead.pancard_number, { required: true, upper: true, maxlength: 10, hint: 'Format: ABCDE1234F' })}
                ${editField('City', 'city', lead.city)}
            `);
            sections += editSection('fa-indian-rupee-sign', 'Loan', `
                ${editField('Requested amount', 'loan_amount', lead.loan_amount, { required: true, type: 'number', prefix: '₹', inputmode: 'numeric' })}
                <div class="edit-field">
                    <label for="edit-loan_type">Category</label>
                    <select id="edit-loan_type" name="loan_type" data-original="${escapeHtml(currentType)}">${typeOptions}</select>
                </div>
            `);
        } else {
            sections += editSection('fa-user', 'Applicant & contact', `
                ${editField('Full name', 'applicant_name', lead.applicant_name, { required: true, span: true, autocomplete: 'name' })}
                ${editField('Mobile number', 'mobile_number', lead.mobile_number, { required: true, inputmode: 'tel', maxlength: 15 })}
                ${editField('Email', 'email', lead.email, { required: true, type: 'email' })}
                ${editField('PAN', 'pan_number', lead.pan_number, { required: true, upper: true, maxlength: 10, hint: 'Format: ABCDE1234F' })}
                ${editField('Aadhaar number', 'aadhaar_number', lead.aadhaar_number, { required: true, inputmode: 'numeric', maxlength: 14 })}
                ${editField('City', 'city', lead.city, { required: true })}
                ${editField('Pincode', 'pincode', lead.pincode, { required: true, inputmode: 'numeric', maxlength: 6 })}
                ${editField('Full address', 'current_address', lead.current_address, { required: true, span: true })}
            `);
            sections += editSection('fa-store', 'Business & loan', `
                ${editField('Business / shop name', 'shop_business_name', lead.shop_business_name, { required: true, span: true })}
                ${editField('Registration type', 'registration_type', lead.registration_type)}
                ${editField('Business age', 'business_age', lead.business_age)}
                ${editField('Daily sales', 'daily_sales', lead.daily_sales, { required: true, type: 'number', prefix: '₹', inputmode: 'numeric' })}
                ${editField('Monthly income', 'monthly_income', lead.monthly_income, { required: true, type: 'number', prefix: '₹', inputmode: 'numeric' })}
                ${editField('Loan amount required', 'loan_amount', lead.loan_amount, { required: true, type: 'number', prefix: '₹', inputmode: 'numeric' })}
                ${editField('Loan purpose', 'loan_purpose', lead.loan_purpose)}
            `);
            sections += editSection('fa-building-columns', 'Bank account', `
                ${editField('Account holder', 'account_holder', lead.account_holder, { required: true, span: true })}
                ${editField('Bank name', 'bank_name', lead.bank_name, { required: true })}
                ${editField('Account number', 'account_number', lead.account_number, { required: true, inputmode: 'numeric' })}
                ${editField('IFSC', 'ifsc_code', lead.ifsc_code, { required: true, upper: true, maxlength: 11, hint: 'Format: ABCD0123456' })}
            `);
        }

        drawerTitle.innerText = isGeneral ? 'Edit general lead' : 'Edit EDI application';
        drawerBody.innerHTML = `
            <form id="drawerEditForm" onsubmit="submitDrawerEdit(event, ${jsArg(lead.id)}, '${type}')">
                <div class="edit-banner">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <div style="min-width:0;">
                        <div class="t">Editing ${escapeHtml(lead.applicant_name || 'application')}</div>
                        <div class="s">${escapeHtml(appDisplayId)} · fields marked <span style="color:#dc2626;">*</span> are required</div>
                    </div>
                </div>
                ${sections}
                <div class="edit-actions">
                    <span class="state" id="editState">No changes yet</span>
                    <div class="btns">
                        <button type="button" class="lead-action" id="editCancelBtn">Cancel</button>
                        <button type="submit" class="btn-primary-adm" id="editSaveBtn" disabled><i class="fa-solid fa-check"></i> Save changes</button>
                    </div>
                </div>
            </form>
        `;

        const form = document.getElementById('drawerEditForm');
        const state = document.getElementById('editState');
        const saveBtn = document.getElementById('editSaveBtn');

        // Track changes so Save is only enabled when something changed, and changed fields are marked.
        const syncDirty = () => {
            let changed = 0;
            form.querySelectorAll('[data-original]').forEach(el => {
                const isChanged = String(el.value) !== el.getAttribute('data-original');
                el.classList.toggle('changed', isChanged);
                if (isChanged) changed++;
            });
            form.dataset.dirty = changed ? '1' : '0';
            saveBtn.disabled = changed === 0;
            state.textContent = changed ? `${changed} unsaved change${changed === 1 ? '' : 's'}` : 'No changes yet';
            state.classList.toggle('dirty', changed > 0);
        };
        form.addEventListener('input', (e) => {
            if (e.target.hasAttribute('data-upper')) {
                const pos = e.target.selectionStart;
                e.target.value = e.target.value.toUpperCase();
                try { e.target.setSelectionRange(pos, pos); } catch (err) {}
            }
            syncDirty();
        });
        form.addEventListener('change', syncDirty);

        document.getElementById('editCancelBtn').addEventListener('click', () => {
            const back = () => loadLeadDetails(lead.id, type, 'view');
            if (form.dataset.dirty !== '1') { back(); return; }
            Swal.fire({
                title: 'Discard changes?',
                text: 'Your edits to this application will be lost.',
                icon: 'warning',
                showCancelButton: true,
                focusCancel: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#475569',
                confirmButtonText: 'Discard',
                cancelButtonText: 'Keep editing'
            }).then(r => { if (r.isConfirmed) back(); });
        });

        setTimeout(() => form.querySelector('input')?.focus(), 60);
    }

    // Submit Drawer Edit via AJAX
    function submitDrawerEdit(e, id, type) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        formData.append('action', 'save_lead_details');
        formData.append('id', id);
        formData.append('type', type);
        formData.append('csrf_token', csrfToken);

        Swal.fire({
            title: 'Saving lead details...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        fetch('api.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                form.dataset.dirty = '0';
                Swal.fire({
                    icon: 'success',
                    title: 'Saved Successfully!',
                    text: 'The lead details have been updated.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    loadLeadDetails(id, type, 'view');
                    refreshMasterLeads({ silent: true, updateUrl: false });
                });
            } else {
                throw new Error(data.message || 'Failed to save lead details.');
            }
        })
        .catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Save Failed',
                text: err.message
            });
        });
    }

    // Delete Lead via AJAX
    function deleteLeadRow(id, type) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You will not be able to recover this lead application!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deleting lead...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                const formData = new FormData();
                formData.append('action', 'delete_application');
                formData.append('id', id);
                formData.append('type', type);
                formData.append('csrf_token', '<?php echo $_SESSION['csrf_token'] ?? ''; ?>');

                fetch('api.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'The lead has been deleted.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            closeLeadDrawer();
                            refreshMasterLeads({ silent: true, updateUrl: false });
                        });
                    } else {
                        throw new Error(data.message || 'Failed to delete lead.');
                    }
                })
                .catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Delete Failed',
                        text: err.message
                    });
                });
            }
        });
    }

    function parseLegacyLead(button) {
        try {
            return JSON.parse(button.getAttribute('data-lead') || '{}');
        } catch (error) {
            return {};
        }
    }

    function getMasterAction(button) {
        if (button.classList.contains('js-master-action')) {
            return {
                action: button.getAttribute('data-action'),
                type: button.getAttribute('data-type'),
                id: button.getAttribute('data-id')
            };
        }

        if (button.classList.contains('view-gen-btn') || button.classList.contains('edit-gen-btn')) {
            const lead = parseLegacyLead(button);
            return {
                action: button.classList.contains('edit-gen-btn') ? 'edit' : 'view',
                type: 'general',
                id: lead.id
            };
        }

        if (button.classList.contains('view-edi-btn') || button.classList.contains('edit-edi-btn')) {
            const lead = parseLegacyLead(button);
            return {
                action: button.classList.contains('edit-edi-btn') ? 'edit' : 'view',
                type: 'edi',
                id: lead.id
            };
        }

        if (button.classList.contains('delete-gen-btn')) {
            return { action: 'delete', type: 'general', id: button.getAttribute('data-id') };
        }

        if (button.classList.contains('delete-edi-btn')) {
            return { action: 'delete', type: 'edi', id: button.getAttribute('data-id') };
        }

        return null;
    }

    function resetMasterFilters() {
        if (!masterFilterForm) return;
        ['search', 'city', 'category', 'status', 'date_from', 'date_to', 'amount_min', 'amount_max'].forEach(name => {
            const field = masterFilterForm.querySelector(`[name="${name}"]`);
            if (field) field.value = '';
        });
    }

    function scheduleMasterRefresh(delay = 350) {
        clearTimeout(masterSearchTimer);
        masterSearchTimer = setTimeout(() => {
            masterState.page = 1;
            refreshMasterLeads({ page: 1 });
        }, delay);
    }

    masterFilterForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        masterState.page = 1;
        refreshMasterLeads({ page: 1 });
    });

    // ---- Compact filter toolbar helpers ----
    const moreFiltersBtn = document.getElementById('masterMoreFiltersBtn');
    const advancedPanel = document.getElementById('masterAdvancedFilters');
    const moreCount = document.getElementById('masterMoreCount');
    const ADVANCED_FIELDS = ['date_from', 'date_to', 'amount_min', 'amount_max'];

    // Highlights filters that are set and shows how many date/amount filters are hidden behind the toggle.
    function updateFilterUi() {
        if (!masterFilterForm) return;
        masterFilterForm.querySelectorAll('select, input[type="search"], input[type="date"], input[type="number"]').forEach(el => {
            el.classList.toggle('has-value', String(el.value || '').trim() !== '');
        });
        if (moreCount) {
            const dateSet = ['date_from', 'date_to'].some(n => masterFilterForm.querySelector(`[name="${n}"]`)?.value);
            const amountSet = ['amount_min', 'amount_max'].some(n => masterFilterForm.querySelector(`[name="${n}"]`)?.value);
            const count = (dateSet ? 1 : 0) + (amountSet ? 1 : 0);
            moreCount.textContent = String(count);
            moreCount.hidden = count === 0;
        }
    }

    function setAdvancedOpen(open) {
        if (!advancedPanel || !moreFiltersBtn) return;
        advancedPanel.hidden = !open;
        moreFiltersBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) advancedPanel.querySelector('input')?.focus();
    }

    moreFiltersBtn?.addEventListener('click', () => {
        setAdvancedOpen(advancedPanel ? advancedPanel.hidden : false);
    });

    // Quick date ranges: fill both dates and refresh once.
    advancedPanel?.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-range]');
        if (!btn || !masterFilterForm) return;
        const fmt = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        const today = new Date();
        let from = new Date(today);
        const range = btn.getAttribute('data-range');
        if (range === 'month') {
            from = new Date(today.getFullYear(), today.getMonth(), 1);
        } else if (range !== 'today') {
            from.setDate(today.getDate() - (parseInt(range, 10) - 1));
        }
        masterFilterForm.querySelector('[name="date_from"]').value = fmt(from);
        masterFilterForm.querySelector('[name="date_to"]').value = fmt(today);
        updateFilterUi();
        masterState.page = 1;
        refreshMasterLeads({ page: 1 });
    });

    masterFilterForm?.addEventListener('input', updateFilterUi);
    masterFilterForm?.addEventListener('change', updateFilterUi);
    updateFilterUi();

    masterFilterForm?.addEventListener('input', (event) => {
        if (event.target.matches('[name="search"], [name="amount_min"], [name="amount_max"]')) {
            scheduleMasterRefresh();
        }
    });

    masterFilterForm?.addEventListener('change', (event) => {
        if (event.target.matches('select, input[type="date"]')) {
            masterState.page = 1;
            refreshMasterLeads({ page: 1 });
        }
    });

    masterFilterForm?.addEventListener('click', (event) => {
        const resetButton = event.target.closest('[data-master-reset]');
        if (!resetButton) return;
        event.preventDefault();
        resetMasterFilters();
        masterState.page = 1;
        refreshMasterLeads({ page: 1 });
    });

    masterTabs?.addEventListener('click', (event) => {
        const tabButton = event.target.closest('[data-master-tab]');
        if (!tabButton) return;
        event.preventDefault();

        const nextTab = tabButton.getAttribute('data-master-tab') || 'general';
        if (nextTab === masterState.tab) return;

        masterState.tab = nextTab;
        masterState.page = 1;
        const tabInput = masterFilterForm?.querySelector('[name="tab"]');
        if (tabInput) tabInput.value = nextTab;

        ['city', 'category', 'status'].forEach(name => {
            const field = masterFilterForm?.querySelector(`[name="${name}"]`);
            if (field) field.value = '';
        });

        updateActiveTab(nextTab);
        refreshMasterLeads({ page: 1 });
    });

    masterResultsArea?.addEventListener('click', (event) => {
        const pageLink = event.target.closest('[data-master-page], .pagination a[href]');
        if (pageLink && masterResultsArea.contains(pageLink)) {
            event.preventDefault();
            let nextPage = pageLink.getAttribute('data-master-page');
            if (!nextPage) {
                const url = new URL(pageLink.href, window.location.href);
                nextPage = url.searchParams.get('page') || '1';
            }
            masterState.page = Math.max(1, parseInt(nextPage, 10) || 1);
            refreshMasterLeads({ page: masterState.page });
            return;
        }

        const actionButton = event.target.closest('.js-master-action, .view-gen-btn, .edit-gen-btn, .delete-gen-btn, .view-edi-btn, .edit-edi-btn, .delete-edi-btn');
        if (!actionButton || !masterResultsArea.contains(actionButton)) {
            // Clicking anywhere else on a row opens it (other buttons such as CRM sync handle themselves).
            if (event.target.closest('a, button, input, select')) return;
            if (window.getSelection && String(window.getSelection()).length > 0) return;
            const row = event.target.closest('tr[data-row-id]');
            if (row) loadLeadDetails(row.getAttribute('data-row-id'), row.getAttribute('data-row-type'), 'view');
            return;
        }
        event.preventDefault();

        const action = getMasterAction(actionButton);
        if (!action || !action.id) return;

        if (action.action === 'delete') {
            deleteLeadRow(action.id, action.type);
            return;
        }

        loadLeadDetails(action.id, action.type, action.action === 'edit' ? 'edit' : 'view');
    });

    // First paint uses the same template as AJAX refreshes.
    (function renderInitialMasterResults() {
        const el = document.getElementById('masterInitialData');
        if (!el || !masterResultsArea) return;
        try {
            renderMasterResults(JSON.parse(el.textContent || '{}'));
        } catch (e) {
            refreshMasterLeads({ page: masterState.page || 1, updateUrl: false });
        }
    })();
</script>

<!-- Lightbox Image Modal -->
<div id="imageLightboxModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(8px); z-index: 9999; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
    <span onclick="closeImageLightbox()" style="position: absolute; top: 25px; right: 35px; color: #f8fafc; font-size: 40px; font-weight: 300; cursor: pointer; transition: color 0.2s;" onmouseover="this.style.color='#f43f5e'" onmouseout="this.style.color='#f8fafc'">&times;</span>
    <div style="text-align: center; max-width: 90%; max-height: 85%;">
        <img id="lightboxImg" src="" alt="Document Preview" style="max-width: 100%; max-height: 80vh; border-radius: 8px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); border: 2px solid rgba(255,255,255,0.1);">
        <div id="lightboxCaption" style="color: #cbd5e1; margin-top: 15px; font-weight: 600; font-size: 15px; font-family: 'Plus Jakarta Sans', sans-serif;"></div>
    </div>
</div>

<script>
    function viewImageLightbox(src, caption) {
        if (src.toLowerCase().endsWith('.pdf')) {
            window.open(src, '_blank');
            return;
        }
        
        const modal = document.getElementById('imageLightboxModal');
        const img = document.getElementById('lightboxImg');
        const cap = document.getElementById('lightboxCaption');
        
        img.src = src;
        cap.innerText = caption;
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.style.opacity = '1';
        }, 10);
    }
    
    function closeImageLightbox() {
        const modal = document.getElementById('imageLightboxModal');
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }

    document.addEventListener('keydown', function(e) {
        if (e.key !== 'Escape') return;
        // Close the top-most layer only: SweetAlert handles itself, then the image lightbox, then the drawer.
        if (document.querySelector('.swal2-container')) return;
        const lightbox = document.getElementById('imageLightboxModal');
        if (lightbox && lightbox.style.display && lightbox.style.display !== 'none') {
            closeImageLightbox();
            return;
        }
        closeLeadDrawer();
    });

    document.addEventListener('click', function(event) {
        const syncButton = event.target.closest('.sync-edi-crm-btn');
        if (!syncButton) return;
        event.preventDefault();
        
        const appId = syncButton.getAttribute('data-id');
        if (!appId) return;
        
        Swal.fire({
            title: 'Sync GCS Documents?',
            text: 'This will push all documents for this lead to the CRM from GCS.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Sync Now',
            cancelButtonText: 'Cancel',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return fetch('api-sync-edi-docs.php', {
                    method: 'POST',
                    headers: { 'X-CSRF-Token': csrfToken },
                    body: new URLSearchParams({ app_id: appId, csrf_token: csrfToken })
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(response.statusText);
                        }
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Sync failed: ${error}`);
                    });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                if (result.value.status === 'success') {
                    Swal.fire({
                        title: 'Synced!',
                        text: result.value.message,
                        icon: 'success'
                    });
                } else if (result.value.status === 'warning') {
                    Swal.fire({
                        title: 'Partial Sync',
                        text: result.value.message,
                        icon: 'warning'
                    });
                } else {
                    Swal.fire({
                        title: 'Failed',
                        text: result.value.message || 'Unknown error occurred.',
                        icon: 'error'
                    });
                }
            }
        });
    });

    // View Verified Bank Statement from Digitap Account Aggregator
    window.openMasterStatementModal = function(txnId, mobile, account, leadId) {
        Swal.fire({
            title: 'Loading Bank Statement...',
            html: '<div style="padding:20px;"><i class="fa-solid fa-spinner fa-spin" style="font-size:32px; color:var(--primary);"></i><div style="margin-top:10px; font-weight:600;">Fetching live aggregated transactions...</div></div>',
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: () => {
                const fetchUrl = (txnId && txnId !== 'N/A')
                    ? ('../api/bank-data/report.php?txn_id=' + encodeURIComponent(txnId))
                    : ('../api/bank-data/find-report.php?mobile=' + encodeURIComponent(mobile || '') + '&account=' + encodeURIComponent(account || '') + '&lead_id=' + encodeURIComponent(leadId || ''));

                fetch(fetchUrl)
                    .then(r => r.json())
                    .then(rawRes => {
                        const data = (rawRes && rawRes.report) ? rawRes.report : rawRes;
                        const activeTxn = (rawRes && rawRes.txn_id) ? rawRes.txn_id : (txnId || 'live');

                        if (!data || !data.banks || data.banks.length === 0) {
                            Swal.fire('Notice', 'No bank statement data available for this transaction.', 'info');
                            return;
                        }
                        const bank = data.banks[0];
                        const acc = (bank.accounts && bank.accounts[0]) ? bank.accounts[0] : {};
                        const txns = acc.transactions || [];
                        const holders = acc.customer_info?.holders || [];
                        const holderName = holders[0]?.name || 'N/A';
                        const balance = acc.current_balance ? parseFloat(acc.current_balance) : 0;

                        let totalCr = 0;
                        let totalDr = 0;
                        let crCount = 0;
                        let drCount = 0;
                        const dailyMap = {};

                        txns.forEach(t => {
                            const amt = parseFloat(t.amount) || 0;
                            const d = (t.date || '').slice(0, 10);
                            if (amt >= 0) { totalCr += amt; crCount++; }
                            else { totalDr += Math.abs(amt); drCount++; }

                            if (d) {
                                if (!dailyMap[d]) dailyMap[d] = { date: d, cr: 0, dr: 0, count: 0, txns: [] };
                                if (amt >= 0) { dailyMap[d].cr += amt; dailyMap[d].count++; dailyMap[d].txns.push(t); }
                                else { dailyMap[d].dr += Math.abs(amt); }
                            }
                        });

                        const dailyList = Object.values(dailyMap).sort((a,b) => b.date.localeCompare(a.date));
                        const creditDays = dailyList.filter(row => row.cr > 0);

                        // 90-Day Credit Frequency Analysis
                        let maxDateStr = '';
                        txns.forEach(t => {
                            const d = (t.date || '').slice(0, 10);
                            if (d && (!maxDateStr || d > maxDateStr)) maxDateStr = d;
                        });
                        const maxTs = maxDateStr ? new Date(maxDateStr + 'T23:59:59').getTime() : Date.now();
                        const cutoff90 = maxTs - (90 * 24 * 60 * 60 * 1000);
                        const cutoffDateStr = new Date(cutoff90).toISOString().slice(0, 10);

                        let count90d1000 = 0, count90d2000 = 0, count90d5000 = 0;
                        dailyList.forEach(row => {
                            const rowTs = new Date(row.date + 'T00:00:00').getTime();
                            if (rowTs >= cutoff90) {
                                if (row.cr >= 1000) count90d1000++;
                                if (row.cr >= 2000) count90d2000++;
                                if (row.cr >= 5000) count90d5000++;
                            }
                        });

                        let dailyRows = '';
                        creditDays.forEach((row, i) => {
                            const net = row.cr - row.dr;
                            const netColor = net >= 0 ? '#15803d' : '#b91c1c';
                            const senders = row.txns.map(t => `<div style="font-size:11px; color:#475569; margin-bottom:2px;"><strong style="color:#15803d;">+₹${parseFloat(t.amount).toLocaleString('en-IN', {minimumFractionDigits:2})}</strong> &bull; ${escapeHtml(t.narration || 'Credit')}</div>`).join('');

                            dailyRows += `
                                <tr style="border-bottom:1px solid #e2e8f0; font-size:12px; background:#f0fdf4;">
                                    <td style="padding:7px 8px; color:#64748b;">${i+1}</td>
                                    <td style="padding:7px 8px; font-weight:700; color:#0f172a; white-space:nowrap;"><i class="fa-regular fa-calendar text-primary" style="margin-right:4px;"></i>${escapeHtml(row.date)}</td>
                                    <td style="padding:7px 8px; text-align:right;"><span style="font-weight:800; color:#15803d; background:#dcfce7; padding:3px 7px; border-radius:4px; font-size:13px; border:1px solid #86efac;">+₹${row.cr.toLocaleString('en-IN', {minimumFractionDigits:2})}</span></td>
                                    <td style="padding:7px 8px; text-align:center;"><span style="background:#bbf7d0; color:#166534; font-weight:700; font-size:11px; padding:2px 6px; border-radius:4px;">${escapeHtml(row.count)} Credits</span></td>
                                    <td style="padding:7px 8px; text-align:right; color:#b91c1c; font-weight:600;">-₹${row.dr.toLocaleString('en-IN', {minimumFractionDigits:2})}</td>
                                    <td style="padding:7px 8px; text-align:right; color:${netColor}; font-weight:700;">${net >= 0 ? '+' : '-'}₹${Math.abs(net).toLocaleString('en-IN', {minimumFractionDigits:2})}</td>
                                    <td style="padding:7px 8px; max-width:280px;">${senders}</td>
                                </tr>
                            `;
                        });

                        let txnRows = '';
                        txns.forEach((t, i) => {
                            const rawAmt = parseFloat(t.amount) || 0;
                            const isCr = rawAmt >= 0;
                            const badge = isCr
                                ? '<span style="background:#dcfce7; color:#15803d; padding:2px 6px; border-radius:4px; font-weight:700; font-size:10px;">CREDIT</span>'
                                : '<span style="background:#fee2e2; color:#b91c1c; padding:2px 6px; border-radius:4px; font-weight:700; font-size:10px;">DEBIT</span>';
                            const amtStr = (isCr ? '+₹' : '-₹') + Math.abs(rawAmt).toLocaleString('en-IN', {minimumFractionDigits: 2});
                            const amtColor = isCr ? '#15803d' : '#b91c1c';
                            const balStr = (t.balance !== undefined && t.balance !== null && t.balance !== '') ? ('₹' + parseFloat(t.balance).toLocaleString('en-IN', {minimumFractionDigits: 2})) : '-';

                            txnRows += `
                                <tr style="border-bottom:1px solid #e2e8f0; font-size:11.5px;">
                                    <td style="padding:6px 8px; color:#64748b;">${i+1}</td>
                                    <td style="padding:6px 8px; white-space:nowrap; font-weight:600;">${escapeHtml(t.date || '-')}</td>
                                    <td style="padding:6px 8px; max-width:260px; word-break:break-word; text-align:left;">${escapeHtml(t.narration || '-')}</td>
                                    <td style="padding:6px 8px; font-family:monospace; font-size:10.5px; color:#475569;">${escapeHtml(t.reference || t.transaction_id || '-')}</td>
                                    <td style="padding:6px 8px;">${badge}</td>
                                    <td style="padding:6px 8px; text-align:right; font-weight:700; color:${amtColor}; white-space:nowrap;">${amtStr}</td>
                                    <td style="padding:6px 8px; text-align:right; font-family:monospace;">${balStr}</td>
                                </tr>
                            `;
                        });

                        Swal.fire({
                            title: `${escapeHtml(bank.bank || 'Bank')} - Verified Bank Statement`,
                            width: '1000px',
                            html: `
                                <div style="text-align:left; font-size:13px;">
                                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-bottom:12px; display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                                        <div>
                                            <div style="font-weight:700; color:#0f172a; font-size:14px;">${escapeHtml(holderName)}</div>
                                            <div style="font-size:11.5px; color:#64748b;">A/C: <span style="font-family:monospace; font-weight:700;">${escapeHtml(acc.account_number || 'N/A')}</span> | IFSC: <span style="font-family:monospace; font-weight:700;">${escapeHtml(acc.ifsc_code || 'N/A')}</span></div>
                                        </div>
                                        <div style="text-align:right;">
                                            <div style="font-size:10.5px; color:#64748b; font-weight:700; text-transform:uppercase;">Current Balance</div>
                                            <div style="font-size:16px; font-weight:800; color:#059669;">₹${balance.toLocaleString('en-IN', {minimumFractionDigits: 2})}</div>
                                        </div>
                                    </div>

                                    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:8px; margin-bottom:10px;">
                                        <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:6px; padding:8px; text-align:center;">
                                            <div style="font-size:10px; font-weight:700; color:#1e40af; text-transform:uppercase;">Total Credited (Sum)</div>
                                            <div style="font-size:14px; font-weight:800; color:#1d4ed8;">₹${totalCr.toLocaleString('en-IN', {minimumFractionDigits: 2})}</div>
                                        </div>
                                        <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:6px; padding:8px; text-align:center;">
                                            <div style="font-size:10px; font-weight:700; color:#991b1b; text-transform:uppercase;">Total Debited (Sum)</div>
                                            <div style="font-size:14px; font-weight:800; color:#b91c1c;">₹${totalDr.toLocaleString('en-IN', {minimumFractionDigits: 2})}</div>
                                        </div>
                                        <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; padding:8px; text-align:center;">
                                            <div style="font-size:10px; font-weight:700; color:#166534; text-transform:uppercase;">Days with Credits</div>
                                            <div style="font-size:14px; font-weight:800; color:#15803d;">${creditDays.length} Days Credited</div>
                                        </div>
                                    </div>

                                    <!-- 90-Day Credit Frequency Analysis -->
                                    <div style="background:linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border:1.5px solid #86efac; border-radius:8px; padding:10px 12px; margin-bottom:14px;">
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; flex-wrap:wrap; gap:4px;">
                                            <div style="font-size:11px; font-weight:800; color:#166534; text-transform:uppercase; display:flex; align-items:center; gap:5px;">
                                                <i class="fa-solid fa-chart-line" style="color:#16a34a;"></i> 90-Day Credit Frequency Analysis (Actual Inflow)
                                            </div>
                                            <div style="font-size:10px; font-weight:700; color:#15803d; background:#fff; border:1px solid #bbf7d0; padding:1px 6px; border-radius:10px;">
                                                ${cutoffDateStr} to ${maxDateStr}
                                            </div>
                                        </div>
                                        <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:8px;">
                                            <div style="background:#fff; border:1px solid #bae6fd; border-radius:6px; padding:6px; text-align:center;">
                                                <div style="font-size:10px; font-weight:700; color:#0284c7; text-transform:uppercase;">₹1,000+ Days</div>
                                                <div style="font-size:16px; font-weight:800; color:#0284c7;">${count90d1000} Days</div>
                                                <div style="font-size:9.5px; color:#64748b;">in last 90 days</div>
                                            </div>
                                            <div style="background:#fff; border:1px solid #a7f3d0; border-radius:6px; padding:6px; text-align:center;">
                                                <div style="font-size:10px; font-weight:700; color:#059669; text-transform:uppercase;">₹2,000+ Days</div>
                                                <div style="font-size:16px; font-weight:800; color:#059669;">${count90d2000} Days</div>
                                                <div style="font-size:9.5px; color:#64748b;">in last 90 days</div>
                                            </div>
                                            <div style="background:#fff; border:1px solid #ddd6fe; border-radius:6px; padding:6px; text-align:center;">
                                                <div style="font-size:10px; font-weight:700; color:#7c3aed; text-transform:uppercase;">₹5,000+ Days</div>
                                                <div style="font-size:16px; font-weight:800; color:#7c3aed;">${count90d5000} Days</div>
                                                <div style="font-size:9.5px; color:#64748b;">in last 90 days</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Mode Toggle Tabs -->
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:8px; border-bottom:1px solid #e2e8f0; padding-bottom:8px;">
                                        <div style="display:flex; gap:6px;">
                                            <button type="button" id="swalTabDaily" onclick="document.getElementById('swalDailyTableWrap').style.display='block'; document.getElementById('swalTxnTableWrap').style.display='none'; this.style.background='#0f172a'; this.style.color='#fff'; document.getElementById('swalTabTxns').style.background='#fff'; document.getElementById('swalTabTxns').style.color='#475569';" style="padding:6px 12px; font-size:12px; font-weight:700; border-radius:6px; border:1px solid #0f172a; background:#0f172a; color:#fff; cursor:pointer;">
                                                <i class="fa-solid fa-calendar-day" style="color:#22c55e;"></i> Per-Day Actual Credit (${creditDays.length} Days)
                                            </button>
                                            <button type="button" id="swalTabTxns" onclick="document.getElementById('swalTxnTableWrap').style.display='block'; document.getElementById('swalDailyTableWrap').style.display='none'; this.style.background='#0f172a'; this.style.color='#fff'; document.getElementById('swalTabDaily').style.background='#fff'; document.getElementById('swalTabDaily').style.color='#475569';" style="padding:6px 12px; font-size:12px; font-weight:700; border-radius:6px; border:1px solid #cbd5e1; background:#fff; color:#475569; cursor:pointer;">
                                                <i class="fa-solid fa-list-ul"></i> All Transactions (${txns.length})
                                            </button>
                                        </div>
                                        <div style="display:flex; gap:6px;">
                                            <a href="../api/bank-data/export-statement.php?txn_id=${encodeURIComponent(activeTxn)}&mobile=${encodeURIComponent(mobile || '')}&lead_id=${encodeURIComponent(leadId || '')}&view=daily" target="_blank" style="background:#15803d; color:#fff; padding:6px 12px; border-radius:6px; font-size:11.5px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
                                                <i class="fa-solid fa-download"></i> Daily Credit CSV
                                            </a>
                                            <a href="../api/bank-data/export-statement.php?txn_id=${encodeURIComponent(activeTxn)}&mobile=${encodeURIComponent(mobile || '')}&lead_id=${encodeURIComponent(leadId || '')}" target="_blank" style="background:#0f172a; color:#fff; padding:6px 12px; border-radius:6px; font-size:11.5px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
                                                <i class="fa-solid fa-download"></i> All Txns CSV
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Table 1: Per-Day Actual Credit Table -->
                                    <div id="swalDailyTableWrap" style="max-height:360px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:6px;">
                                        <table style="width:100%; border-collapse:collapse; text-align:left;">
                                            <thead>
                                                <tr style="background:#f1f5f9; font-size:11px; color:#475569; position:sticky; top:0; z-index:1;">
                                                    <th style="padding:8px;">#</th>
                                                    <th style="padding:8px;">Date (Tarikh)</th>
                                                    <th style="padding:8px; text-align:right;">Actual Credit (₹)</th>
                                                    <th style="padding:8px; text-align:center;">Credit Txns</th>
                                                    <th style="padding:8px; text-align:right;">Debit on Day (₹)</th>
                                                    <th style="padding:8px; text-align:right;">Net Flow (₹)</th>
                                                    <th style="padding:8px;">Credit Details / Senders</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${dailyRows || '<tr><td colspan="7" style="text-align:center; padding:20px; color:#64748b;">No credit activity recorded.</td></tr>'}
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Table 2: All Individual Transactions Table -->
                                    <div id="swalTxnTableWrap" style="display:none; max-height:360px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:6px;">
                                        <table style="width:100%; border-collapse:collapse; text-align:left;">
                                            <thead>
                                                <tr style="background:#f1f5f9; font-size:11px; color:#475569; position:sticky; top:0; z-index:1;">
                                                    <th style="padding:8px;">#</th>
                                                    <th style="padding:8px;">Date</th>
                                                    <th style="padding:8px;">Narration</th>
                                                    <th style="padding:8px;">Ref / Txn ID</th>
                                                    <th style="padding:8px;">Type</th>
                                                    <th style="padding:8px; text-align:right;">Amount</th>
                                                    <th style="padding:8px; text-align:right;">Balance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${txnRows}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            `,
                            showCloseButton: true,
                            confirmButtonText: 'Close',
                            confirmButtonColor: '#475569'
                        });
                    })
                    .catch(err => {
                        Swal.fire('Error', 'Failed to retrieve statement: ' + err.message, 'error');
                    });
            }
        });
    };
</script>

<?php include 'footer.php'; ?>
