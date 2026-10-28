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
        return 'Rs. ' . number_format($amount / 10000000, 2) . ' Cr';
    }

    if ($amount >= 100000) {
        return 'Rs. ' . number_format($amount / 100000, 2) . ' L';
    }

    return 'Rs. ' . number_format($amount, 0);
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
    .tab-nav {
        display: flex;
        width: 100%;
        background: #f1f5f9;
        padding: 6px;
        border-radius: 12px;
        gap: 6px;
        margin-bottom: 30px;
        border: 1px solid var(--border-color);
    }
    .tab-btn {
        flex: 1;
        justify-content: center;
        background: transparent;
        border: none;
        color: var(--text-muted);
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        padding: 10px 20px;
        border-radius: 9px;
        transition: all 0.25s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .tab-btn:hover {
        color: var(--primary);
        background: rgba(24, 82, 156, 0.04);
    }
    .tab-btn.active {
        color: #ffffff !important;
        background: var(--primary);
        box-shadow: 0 4px 10px rgba(24, 82, 156, 0.2);
    }
    .master-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin: -10px 0 28px;
    }
    .master-kpi-card {
        display: flex;
        align-items: center;
        gap: 14px;
        min-height: 104px;
        padding: 18px;
        border: 1px solid #dbe4ef;
        border-radius: 8px;
        background: #ffffff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
    }
    .master-kpi-icon {
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 20px;
    }
    .master-kpi-card.kpi-blue .master-kpi-icon {
        background: rgba(24, 82, 156, 0.08);
        color: var(--primary);
    }
    .master-kpi-card.kpi-green .master-kpi-icon {
        background: rgba(22, 163, 74, 0.09);
        color: #16a34a;
    }
    .master-kpi-card.kpi-yellow .master-kpi-icon {
        background: rgba(202, 138, 4, 0.11);
        color: #ca8a04;
    }
    .master-kpi-card.kpi-red .master-kpi-icon {
        background: rgba(239, 68, 68, 0.08);
        color: #dc2626;
    }
    .master-kpi-info h3 {
        margin: 0;
        color: var(--text-main);
        font-size: 24px;
        line-height: 1.15;
        font-weight: 800;
    }
    .master-kpi-info p {
        margin: 4px 0 0;
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.6px;
        text-transform: uppercase;
    }
    .master-kpi-info span {
        display: block;
        margin-top: 4px;
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
    }
    .master-filter-panel {
        display: grid;
        gap: 16px;
        padding: 18px;
        margin-bottom: 24px;
        border: 1px solid #dbe4ef;
        border-radius: 8px;
        background: #fbfdff;
    }
    .master-filter-grid {
        display: grid;
        grid-template-columns: minmax(260px, 1.4fr) repeat(6, minmax(140px, 1fr));
        gap: 12px;
        align-items: end;
    }
    .filter-field {
        display: grid;
        gap: 6px;
    }
    .filter-field label {
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.6px;
        text-transform: uppercase;
    }
    .filter-field input,
    .filter-field select {
        width: 100%;
        height: 42px;
        padding: 9px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #ffffff;
        color: var(--text-main);
        font-size: 13.5px;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .filter-field input:focus,
    .filter-field select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-glow);
    }
    .master-filter-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .master-filter-summary {
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 750;
    }
    .master-filter-buttons {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .btn-outline-adm,
    .btn-export-adm {
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-outline-adm {
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: var(--text-main);
    }
    .btn-outline-adm:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: rgba(24, 82, 156, 0.05);
    }
    .btn-export-adm {
        border: 1px solid rgba(22, 163, 74, 0.25);
        background: rgba(22, 163, 74, 0.08);
        color: #15803d;
    }
    .btn-export-adm:hover {
        background: #16a34a;
        color: #ffffff;
    }
    @media (max-width: 1320px) {
        .master-filter-grid {
            grid-template-columns: repeat(4, minmax(180px, 1fr));
        }
    }
    @media (max-width: 1180px) {
        .master-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 720px) {
        .master-kpi-grid,
        .master-filter-grid {
            grid-template-columns: 1fr;
        }
        .master-filter-actions {
            align-items: stretch;
        }
        .master-filter-buttons,
        .master-filter-buttons > * {
            width: 100%;
        }
    }

    /* Details Drawer Modal */
    .drawer-overlay {
        position: fixed;
        top: 0;
        right: -100%;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(5px);
        z-index: 1000;
        transition: right 0.4s ease;
        display: flex;
        justify-content: flex-end;
    }
    .drawer-overlay.active {
        right: 0;
    }
    .drawer-content {
        width: 100%;
        max-width: 650px;
        height: 100%;
        background: #ffffff;
        border-left: 1px solid var(--border-color);
        padding: 35px;
        overflow-y: auto;
        box-shadow: -10px 0 30px rgba(0, 0, 0, 0.05);
    }
    .drawer-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 15px;
    }
    .drawer-close {
        background: none;
        border: none;
        color: var(--text-muted);
        font-size: 20px;
        cursor: pointer;
    }
    .drawer-close:hover {
        color: var(--text-main);
    }
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

    /* Premium Table overrides */
    .admin-table {
        border-collapse: separate !important;
        border-spacing: 0 8px !important;
        margin-top: 15px;
    }
    .admin-table thead tr {
        background: #f8fafc;
    }
    .admin-table th {
        padding: 14px 16px !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        color: var(--text-muted) !important;
        letter-spacing: 0.6px;
        border-bottom: 2px solid #e2e8f0 !important;
    }
    .admin-table tbody tr {
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        transition: all 0.2s ease;
    }
    .admin-table tbody tr:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
        background: #fafcff;
    }
    .admin-table td {
        padding: 16px !important;
        border-top: 1px solid #f1f5f9 !important;
        border-bottom: 1px solid #f1f5f9 !important;
        font-size: 13.5px !important;
        color: var(--text-main) !important;
        vertical-align: middle !important;
    }
    .admin-table td:first-child {
        border-left: 1px solid #f1f5f9 !important;
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
    }
    .admin-table td:last-child {
        border-right: 1px solid #f1f5f9 !important;
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
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
        border-color: rgba(24, 82, 156, 0.2);
        color: var(--primary);
    }
    .btn-action-view:hover {
        background: var(--primary);
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(24, 82, 156, 0.25);
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

<div class="data-card">
    <form method="GET" action="master-loans.php" id="masterFilterForm" class="master-filter-panel">
        <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="master-filter-grid">
            <div class="filter-field">
                <label for="master-search">Search</label>
                <input id="master-search" type="text" name="search"
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

            <div class="filter-field">
                <label for="master-date-from">From Date</label>
                <input id="master-date-from" type="date" name="date_from" value="<?php echo htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="filter-field">
                <label for="master-date-to">To Date</label>
                <input id="master-date-to" type="date" name="date_to" value="<?php echo htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="filter-field">
                <label for="master-amount-min">Min Amount</label>
                <input id="master-amount-min" type="number" name="amount_min" min="0" step="1000" placeholder="0" value="<?php echo htmlspecialchars($amountMin, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="filter-field">
                <label for="master-amount-max">Max Amount</label>
                <input id="master-amount-max" type="number" name="amount_max" min="0" step="1000" placeholder="100000" value="<?php echo htmlspecialchars($amountMax, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
        </div>

        <div class="master-filter-actions">
            <div class="master-filter-summary" id="masterFilterSummary">
                <?php echo number_format($totalRecords); ?> matching <?php echo $activeTab === 'general' ? 'general leads' : 'EDI applications'; ?>
                <?php if ($filtersActive): ?>
                    <span style="color: var(--primary); margin-left: 8px;">Filters active</span>
                <?php endif; ?>
            </div>

            <div class="master-filter-buttons">
                <button type="submit" class="btn-primary-adm" style="height:42px; box-shadow:none;">
                    <i class="fa-solid fa-filter"></i> Apply Filter
                </button>
                <a href="master-loans.php?tab=<?php echo urlencode($activeTab); ?>" class="btn-outline-adm" data-master-reset title="Reset filters" style="<?php echo $filtersActive ? '' : 'display:none;'; ?>">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
                <button type="button" class="btn-export-adm" onclick="exportLeadsCsv()">
                    <i class="fa-solid fa-file-excel"></i> Download Excel
                </button>
            </div>
        </div>
    </form>

    <div id="masterResultsArea">
    <?php if ($activeTab === 'general'): ?>
        <div class="card-header-flex" style="margin-top: 15px;">
            <h2 class="card-title-text"><i class="fa-solid fa-layer-group" style="color: var(--primary); margin-right: 8px;"></i> Business & Personal Leads (New)</h2>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>App ID</th>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>PAN</th>
                        <th>City</th>
                        <th>Amount</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($generalLeads)): ?>
                        <tr>
                            <td colspan="9" class="text-center" style="color: var(--text-muted); padding: 40px 0;">No general leads found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($generalLeads as $lead): ?>
                            <tr id="lead-row-general-<?php echo $lead['id']; ?>">
                                <td><span class="app-id-code"><?php echo htmlspecialchars($lead['application_id'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><strong style="color: var(--text-main);"><?php echo htmlspecialchars($lead['applicant_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                <td><?php echo htmlspecialchars($lead['mobile_number'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><span style="font-family: monospace; font-weight: 700; font-size:13.5px;"><?php echo htmlspecialchars($lead['pancard_number'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><?php echo htmlspecialchars($lead['city'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><strong style="color: var(--text-main);">â‚¹<?php echo number_format((float)($lead['loan_amount'] ?? 0), 2); ?></strong></td>
                                <td><span style="text-transform: uppercase; background: rgba(24,82,156,0.08); padding: 4px 10px; border-radius: 6px; font-size: 11.5px; font-weight: 700; color: var(--primary);"><?php echo htmlspecialchars($lead['loan_type'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td style="color: var(--text-muted); font-size: 12.5px;"><?php echo date('d M Y, h:i A', strtotime($lead['created_at'])); ?></td>
                                <td style="white-space: nowrap;">
                                    <div class="action-btn-group">
                                        <button type="button" class="btn-action-premium btn-action-view view-gen-btn" data-lead='<?php echo htmlspecialchars(json_encode($lead), ENT_QUOTES, 'UTF-8'); ?>' title="View Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn-action-premium btn-action-edit edit-gen-btn" data-lead='<?php echo htmlspecialchars(json_encode($lead), ENT_QUOTES, 'UTF-8'); ?>' title="Edit Lead">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button type="button" class="btn-action-premium btn-action-delete delete-gen-btn" data-id="<?php echo $lead['id']; ?>" title="Delete Lead">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <div class="card-header-flex" style="margin-top: 15px;">
            <h2 class="card-title-text"><i class="fa-solid fa-store" style="color: var(--primary); margin-right: 8px;"></i> EDI Loan Applications</h2>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>App ID</th>
                        <th>Applicant Name</th>
                        <th>Mobile</th>
                        <th>PAN</th>
                        <th>Shop Name</th>
                        <th>Amount</th>
                        <th>Pincode</th>
                        <th>Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ediLeads)): ?>
                        <tr>
                            <td colspan="9" class="text-center" style="color: var(--text-muted); padding: 40px 0;">No EDI applications found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ediLeads as $lead): ?>
                            <tr id="lead-row-edi-<?php echo $lead['id']; ?>">
                                <td><span class="app-id-code"><?php echo htmlspecialchars($lead['application_id'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><strong style="color: var(--text-main);"><?php echo htmlspecialchars($lead['applicant_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                <td><?php echo htmlspecialchars($lead['mobile_number'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><span style="font-family: monospace; font-weight: 700; font-size:13.5px;"><?php echo htmlspecialchars($lead['pan_number'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><?php echo htmlspecialchars($lead['shop_business_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><strong style="color: var(--text-main);">â‚¹<?php echo number_format((float)($lead['loan_amount'] ?? 0), 2); ?></strong></td>
                                <td><?php echo htmlspecialchars($lead['pincode'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><span class="badge-status badge-<?php echo htmlspecialchars($lead['status'] ?? 'pending', ENT_QUOTES, 'UTF-8'); ?>" id="badge-<?php echo $lead['id']; ?>"><?php echo htmlspecialchars($lead['status'] ?? 'pending', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td style="white-space: nowrap;">
                                    <div class="action-btn-group">
                                        <button type="button" class="btn-action-premium btn-action-view view-edi-btn" data-lead='<?php echo htmlspecialchars(json_encode($lead), ENT_QUOTES, 'UTF-8'); ?>' title="View Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn-action-premium btn-action-edit edit-edi-btn" data-lead='<?php echo htmlspecialchars(json_encode($lead), ENT_QUOTES, 'UTF-8'); ?>' title="Edit Lead">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button type="button" class="btn-action-premium btn-action-delete delete-edi-btn" data-id="<?php echo $lead['id']; ?>" title="Delete Lead">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                        <button type="button" class="btn-action-premium sync-edi-crm-btn" data-id="<?php echo htmlspecialchars($lead['application_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" title="Sync Documents with CRM" style="background: #2563eb; color: #ffffff;">
                                            <i class="fa-solid fa-cloud-arrow-up"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <ul class="pagination">
            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <?php if ($page <= 1): ?>
                    <span style="opacity:0.5; cursor:not-allowed;">&laquo; Prev</span>
                <?php else: ?>
                    <a href="?<?php echo htmlspecialchars(master_build_query($baseQueryParams, ['page' => $page - 1]), ENT_QUOTES, 'UTF-8'); ?>">&laquo; Prev</a>
                <?php endif; ?>
            </li>
            
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <li class="page-item <?php echo $p === $page ? 'active' : ''; ?>">
                    <?php if ($p === $page): ?>
                        <span><?php echo $p; ?></span>
                    <?php else: ?>
                        <a href="?<?php echo htmlspecialchars(master_build_query($baseQueryParams, ['page' => $p]), ENT_QUOTES, 'UTF-8'); ?>"><?php echo $p; ?></a>
                    <?php endif; ?>
                </li>
            <?php endfor; ?>
            
            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                <?php if ($page >= $totalPages): ?>
                    <span style="opacity:0.5; cursor:not-allowed;">Next &raquo;</span>
                <?php else: ?>
                    <a href="?<?php echo htmlspecialchars(master_build_query($baseQueryParams, ['page' => $page + 1]), ENT_QUOTES, 'UTF-8'); ?>">Next &raquo;</a>
                <?php endif; ?>
            </li>
        </ul>
    <?php endif; ?>
    </div>
</div>

<!-- Details Drawer Modal -->
<div class="drawer-overlay" id="detailDrawer">
    <div class="drawer-content">
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
    
    closeDrawer.addEventListener('click', () => drawer.classList.remove('active'));
    drawer.addEventListener('click', (e) => {
        if (e.target === drawer) drawer.classList.remove('active');
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
        return 'Rs. ' + safeAmount.toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
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

    function renderGeneralRows(records) {
        if (!records.length) {
            return '<tr><td colspan="9" class="text-center" style="color: var(--text-muted); padding: 40px 0;">No general leads found.</td></tr>';
        }

        return records.map(lead => `
            <tr id="lead-row-general-${escapeHtml(lead.id)}">
                <td><span class="app-id-code">${escapeHtml(lead.application_id || 'N/A')}</span></td>
                <td><strong style="color: var(--text-main);">${escapeHtml(lead.applicant_name || 'N/A')}</strong></td>
                <td>${escapeHtml(lead.mobile_number || 'N/A')}</td>
                <td><span style="font-family: monospace; font-weight: 700; font-size:13.5px;">${escapeHtml(lead.pancard_number || 'N/A')}</span></td>
                <td>${escapeHtml(lead.city || 'N/A')}</td>
                <td><strong style="color: var(--text-main);">${formatCurrency(lead.loan_amount)}</strong></td>
                <td><span style="text-transform: uppercase; background: rgba(24,82,156,0.08); padding: 4px 10px; border-radius: 6px; font-size: 11.5px; font-weight: 700; color: var(--primary);">${escapeHtml(lead.loan_type || 'N/A')}</span></td>
                <td style="color: var(--text-muted); font-size: 12.5px;">${formatDateTime(lead.created_at)}</td>
                <td style="white-space: nowrap;">
                    <div class="action-btn-group">
                        <button type="button" class="btn-action-premium btn-action-view js-master-action" data-action="view" data-type="general" data-id="${escapeHtml(lead.id)}" title="View Details"><i class="fa-solid fa-eye"></i></button>
                        <button type="button" class="btn-action-premium btn-action-edit js-master-action" data-action="edit" data-type="general" data-id="${escapeHtml(lead.id)}" title="Edit Lead"><i class="fa-solid fa-pen"></i></button>
                        <button type="button" class="btn-action-premium btn-action-delete js-master-action" data-action="delete" data-type="general" data-id="${escapeHtml(lead.id)}" title="Delete Lead"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function renderEdiRows(records) {
        if (!records.length) {
            return '<tr><td colspan="9" class="text-center" style="color: var(--text-muted); padding: 40px 0;">No EDI applications found.</td></tr>';
        }

        return records.map(lead => {
            const status = lead.status || 'pending';
            return `
                <tr id="lead-row-edi-${escapeHtml(lead.id)}">
                    <td><span class="app-id-code">${escapeHtml(lead.application_id || 'N/A')}</span></td>
                    <td><strong style="color: var(--text-main);">${escapeHtml(lead.applicant_name || 'N/A')}</strong></td>
                    <td>${escapeHtml(lead.mobile_number || 'N/A')}</td>
                    <td><span style="font-family: monospace; font-weight: 700; font-size:13.5px;">${escapeHtml(lead.pan_number || 'N/A')}</span></td>
                    <td>${escapeHtml(lead.shop_business_name || 'N/A')}</td>
                    <td><strong style="color: var(--text-main);">${formatCurrency(lead.loan_amount)}</strong></td>
                    <td>${escapeHtml(lead.pincode || 'N/A')}</td>
                    <td><span class="badge-status badge-${escapeHtml(status)}" id="badge-${escapeHtml(lead.id)}">${escapeHtml(status)}</span></td>
                    <td style="white-space: nowrap;">
                        <div class="action-btn-group">
                            <button type="button" class="btn-action-premium btn-action-view js-master-action" data-action="view" data-type="edi" data-id="${escapeHtml(lead.id)}" title="View Details"><i class="fa-solid fa-eye"></i></button>
                            <button type="button" class="btn-action-premium btn-action-edit js-master-action" data-action="edit" data-type="edi" data-id="${escapeHtml(lead.id)}" title="Edit Lead"><i class="fa-solid fa-pen"></i></button>
                            <button type="button" class="btn-action-premium btn-action-delete js-master-action" data-action="delete" data-type="edi" data-id="${escapeHtml(lead.id)}" title="Delete Lead"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function renderPagination(data) {
        if (data.total_pages <= 1) return '';
        const pages = [];
        const current = data.page;
        const total = data.total_pages;
        const addPage = value => {
            if (!pages.includes(value)) pages.push(value);
        };

        addPage(1);
        for (let p = current - 2; p <= current + 2; p++) {
            if (p > 1 && p < total) addPage(p);
        }
        addPage(total);
        pages.sort((a, b) => a - b);

        let last = 0;
        const pageItems = [];
        pages.forEach(p => {
            if (last && p - last > 1) {
                pageItems.push('<li class="page-item disabled"><span style="border: none; background: transparent; cursor: default;">...</span></li>');
            }
            pageItems.push(`
                <li class="page-item ${p === current ? 'active' : ''}">
                    ${p === current ? `<span>${p}</span>` : `<a href="#" data-master-page="${p}">${p}</a>`}
                </li>
            `);
            last = p;
        });

        return `
            <ul class="pagination">
                <li class="page-item ${current <= 1 ? 'disabled' : ''}">
                    ${current <= 1 ? '<span style="opacity:0.5; cursor:not-allowed;">&laquo; Prev</span>' : `<a href="#" data-master-page="${current - 1}">&laquo; Prev</a>`}
                </li>
                ${pageItems.join('')}
                <li class="page-item ${current >= total ? 'disabled' : ''}">
                    ${current >= total ? '<span style="opacity:0.5; cursor:not-allowed;">Next &raquo;</span>' : `<a href="#" data-master-page="${current + 1}">Next &raquo;</a>`}
                </li>
            </ul>
        `;
    }

    function renderMasterResults(data) {
        const isGeneral = data.tab === 'general';
        const title = isGeneral
            ? '<i class="fa-solid fa-layer-group" style="color: var(--primary); margin-right: 8px;"></i> Business & Personal Leads (New)'
            : '<i class="fa-solid fa-store" style="color: var(--primary); margin-right: 8px;"></i> EDI Loan Applications';
        const head = isGeneral
            ? `
                <tr>
                    <th>App ID</th><th>Name</th><th>Mobile</th><th>PAN</th><th>City</th><th>Amount</th><th>Category</th><th>Date</th><th style="width: 120px;">Actions</th>
                </tr>
            `
            : `
                <tr>
                    <th>App ID</th><th>Applicant Name</th><th>Mobile</th><th>PAN</th><th>Shop Name</th><th>Amount</th><th>Pincode</th><th>Status</th><th style="width: 120px;">Actions</th>
                </tr>
            `;
        const rows = isGeneral ? renderGeneralRows(data.records || []) : renderEdiRows(data.records || []);

        masterResultsArea.innerHTML = `
            <div style="position: relative;">
                <!-- Suspense Loader Overlay -->
                <div class="table-loading-overlay" id="masterTableLoadingOverlay">
                    <div class="suspense-spinner">
                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                        <span>Loading Leads...</span>
                    </div>
                </div>
                <div class="card-header-flex" style="margin-top: 15px;">
                    <h2 class="card-title-text">${title}</h2>
                </div>
                <div class="table-responsive">
                    <table class="admin-table">
                    <thead>${head}</thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
            ${renderPagination(data)}
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
        return `<a href="${url}" class="doc-preview-link" onclick="viewImageLightbox('${url}', '${name}'); return false;"><i class="fa-solid fa-file-image"></i> View ${name}</a>`;
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
    function loadLeadDetails(id, type, mode = 'view') {
        drawerTitle.innerText = type === 'general'
            ? (mode === 'edit' ? 'Edit General Lead' : 'General Lead Details')
            : (mode === 'edit' ? 'Edit EDI Application' : 'EDI Application Details');
            
        drawerBody.innerHTML = `
            <div class="drawer-loading">
                <div class="drawer-spinner"></div>
                <div style="font-family:'Outfit'; font-weight:700; font-size:14px;">Fetching lead data...</div>
            </div>
        `;
        drawer.classList.add('active');

        const formData = new FormData();
        formData.append('action', 'get_lead_details');
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
                <div style="padding:20px; text-align:center; color:var(--danger); font-weight:700;">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size:30px; margin-bottom:10px;"></i>
                    <div>${err.message || 'Failed to load details.'}</div>
                </div>
            `;
        });
    }

    // Render lead drawer content dynamically
    function renderLeadDrawerContent(data, type) {
        const lead = data.application;
        const cibil = data.cibil;
        const docChecks = data.docChecks || {};
        const activities = data.activities || [];
        
        const appDisplayId = lead.application_id || 'N/A';
        const sourceSystem = lead.source || 'Portal Direct';
        
        let headerHtml = `
            <div style="background: rgba(24,82,156,0.05); padding:16px; border-radius:12px; border:1px dashed var(--primary); margin-bottom: 20px;">
                <div class="grid-fields">
                    <div class="field-item">
                        <div class="field-label">${type === 'general' ? 'General Application ID' : 'EDI Application ID'}</div>
                        <div class="field-value" style="color:var(--primary); font-size:15px; font-weight:800;">${appDisplayId}</div>
                    </div>
                    <div class="field-item">
                        <div class="field-label">Source System</div>
                        <div class="field-value" style="text-transform:uppercase; font-weight:800;">${sourceSystem}</div>
                    </div>
                </div>
            </div>
        `;

        let timelineHtml = '';
        if (type === 'edi') {
            const currentStatus = (lead.status || 'pending').toLowerCase();
            const steps = ['pending', 'submitted', 'approved'];
            const isRejected = currentStatus === 'rejected';
            let statusIndex = steps.indexOf(currentStatus);
            if (statusIndex === -1) {
                statusIndex = isRejected ? 2 : 0;
            }
            let progressPct = 0;
            if (statusIndex > 0) {
                progressPct = (statusIndex / (steps.length - 1)) * 90;
            }
            if (currentStatus === 'approved') progressPct = 90;

            timelineHtml = `
                <div class="info-card" style="border-left-color: var(--primary);">
                    <h3 class="card-title" style="border: none; margin-bottom: 5px;"><i class="fa-solid fa-route"></i> Application Stepper Timeline</h3>
                    <div class="status-timeline">
                        <div class="timeline-progress-bar" style="width: ${progressPct}%;"></div>
                        <div class="timeline-step ${statusIndex >= 0 ? 'completed' : ''} ${currentStatus === 'pending' ? 'active' : ''}">
                            <div class="step-circle">${currentStatus === 'pending' ? '<i class="fa-solid fa-spinner fa-spin"></i>' : '1'}</div>
                            <div class="step-label">Pending</div>
                        </div>
                        <div class="timeline-step ${statusIndex >= 1 ? 'completed' : ''} ${currentStatus === 'submitted' ? 'active' : ''}">
                            <div class="step-circle">${currentStatus === 'submitted' ? '<i class="fa-solid fa-spinner fa-spin"></i>' : '2'}</div>
                            <div class="step-label">Submitted</div>
                        </div>
                        ${isRejected ? `
                            <div class="timeline-step rejected">
                                <div class="step-circle">&times;</div>
                                <div class="step-label">Rejected</div>
                            </div>
                        ` : `
                            <div class="timeline-step ${statusIndex >= 2 ? 'completed' : ''} ${currentStatus === 'approved' ? 'active' : ''}">
                                <div class="step-circle">${currentStatus === 'approved' ? '<i class="fa-solid fa-spinner fa-spin"></i>' : '3'}</div>
                                <div class="step-label">Approved</div>
                            </div>
                        `}
                    </div>
                </div>
            `;
        }

        let cibilHtml = '';
        if (cibil) {
            const score = cibil.score !== null ? parseInt(cibil.score) : null;
            let scoreClass = 'score-none';
            let ratingLabel = 'No Score';
            if (score !== null) {
                if (score >= 750) { scoreClass = 'score-excellent'; ratingLabel = 'Excellent'; }
                else if (score >= 700) { scoreClass = 'score-good'; ratingLabel = 'Good'; }
                else if (score >= 600) { scoreClass = 'score-fair'; ratingLabel = 'Fair'; }
                else { scoreClass = 'score-poor'; ratingLabel = 'Poor'; }
            }

            cibilHtml = `
                <div class="info-card" style="border-left-color: #16a34a;">
                    <h3 class="card-title" style="margin-bottom:12px;"><i class="fa-solid fa-gauge-high"></i> Credit Score Integration</h3>
                    <div style="display:flex; gap:20px; align-items:center; flex-wrap:wrap;">
                        <div class="cibil-gauge-wrapper" style="border-color: ${
                            scoreClass === 'score-excellent' ? 'rgba(22, 163, 74, 0.2)' :
                            scoreClass === 'score-good' ? 'rgba(37, 99, 235, 0.2)' :
                            scoreClass === 'score-fair' ? 'rgba(202, 138, 4, 0.2)' : 'rgba(220, 38, 38, 0.2)'
                        };">
                            <div class="cibil-score-value ${scoreClass}">${score || 'N/A'}</div>
                            <div class="cibil-score-label">${ratingLabel}</div>
                        </div>
                        <div style="flex:1; min-width:200px;">
                            <div class="grid-fields">
                                <div class="field-item">
                                    <div class="field-label">Bureau Status</div>
                                    <div class="field-value">${cibil.analysis_status || 'Verified'}</div>
                                </div>
                                <div class="field-item">
                                    <div class="field-label">Ref ID</div>
                                    <div class="field-value" style="font-family:monospace; font-size:12px;">${cibil.ref_id || 'N/A'}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        let mainInfoHtml = '';
        if (type === 'general') {
            let parsedDetails = '';
            let aaCardHtml = '';
            try {
                const details = JSON.parse(lead.loan_details || '{}');
                const aaTxnId = details.account_aggregator_txn_id || details.aa_txn_id || '';
                const isAa = details.account_aggregator_verified || aaTxnId;
                if (isAa) {
                    const bName = details.bank_name || 'Bank Account';
                    const aNum = details.account_number || '';
                    const ifsc = details.ifsc_code || '';
                    const inflow = parseFloat(details.monthly_inflow || 0);
                    const amb = parseFloat(details.average_monthly_balance || 0);

                    aaCardHtml = `
                        <div class="info-card" style="border-left: 4px solid #16a34a; background: #f0fdf4;">
                            <h3 class="card-title" style="color: #166534; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                                <span><i class="fa-solid fa-building-columns"></i> Verified Bank Statement (RBI NBFC-AA)</span>
                                <span class="badge" style="background:#16a34a; color:#fff; font-size:11px; padding:3px 8px; border-radius:4px;"><i class="fa-solid fa-circle-check"></i> Aggregated</span>
                            </h3>
                            <div class="grid-fields">
                                <div class="field-item"><div class="field-label">Bank Name</div><div class="field-value" style="font-weight:700;">${bName}</div></div>
                                <div class="field-item"><div class="field-label">Account Number</div><div class="field-value" style="font-family:monospace; font-weight:700;">${aNum}</div></div>
                                <div class="field-item"><div class="field-label">IFSC Code</div><div class="field-value" style="font-family:monospace; color:var(--primary); font-weight:700;">${ifsc}</div></div>
                                <div class="field-item"><div class="field-label">Monthly Inflow</div><div class="field-value" style="color:#16a34a; font-weight:800;">₹${inflow.toLocaleString('en-IN')}</div></div>
                                <div class="field-item"><div class="field-label">Avg Balance (AMB)</div><div class="field-value" style="font-weight:700;">₹${amb.toLocaleString('en-IN')}</div></div>
                                <div class="field-item"><div class="field-label">Aggregator Txn ID</div><div class="field-value" style="font-family:monospace; font-size:12px;">${aaTxnId || 'N/A'}</div></div>
                            </div>
                            ${aaTxnId ? `
                            <div style="display:flex; gap:10px; margin-top:14px; padding-top:12px; border-top:1px dashed #86efac; flex-wrap:wrap;">
                                <button type="button" class="btn-action" onclick="openMasterStatementModal('${aaTxnId}')" style="background:#16a34a; color:#fff; border:none; padding:8px 16px; border-radius:6px; font-weight:700; cursor:pointer; font-size:12px; display:inline-flex; align-items:center; gap:6px;">
                                    <i class="fa-solid fa-file-invoice"></i> View Full Statement
                                </button>
                                <a href="../api/bank-data/export-statement.php?txn_id=${encodeURIComponent(aaTxnId)}" class="btn-action" target="_blank" style="background:#0f172a; color:#fff; text-decoration:none; padding:8px 16px; border-radius:6px; font-weight:700; font-size:12px; display:inline-flex; align-items:center; gap:6px;">
                                    <i class="fa-solid fa-download"></i> Download Statement (CSV)
                                </a>
                            </div>
                            ` : ''}
                        </div>
                    `;
                }

                parsedDetails = Object.entries(details).map(([k, v]) => {
                    const label = k.replace(/_/g, ' ').toUpperCase();
                    if ((k === 'shop_photo_url' || k === 'shop_photo') && v && typeof v === 'string') {
                        const fullUrl = (v.startsWith('http') || v.startsWith('data:')) ? v : '../' + v;
                        return `<div class="field-item" style="grid-column: span 2;">
                            <div class="field-label"><i class="fa-solid fa-store text-primary"></i> ${label}</div>
                            <div class="field-value">
                                <a href="${fullUrl}" target="_blank" title="View Full Photo">
                                    <img src="${fullUrl}" style="max-width:180px; max-height:120px; border-radius:8px; border:1.5px solid #cbd5e1; object-fit:cover; display:block; margin-top:4px; box-shadow:0 2px 6px rgba(0,0,0,0.08);" alt="Shop Photo">
                                </a>
                            </div>
                        </div>`;
                    }
                    if (k === 'shop_photos' && Array.isArray(v) && v.length > 0) {
                        const imgs = v.map(p => {
                            const fullUrl = (p.startsWith('http') || p.startsWith('data:')) ? p : '../' + p;
                            return `<a href="${fullUrl}" target="_blank" style="display:inline-block;" title="View Photo">
                                <img src="${fullUrl}" style="width:75px; height:75px; border-radius:8px; border:1.5px solid #cbd5e1; object-fit:cover; box-shadow:0 1px 4px rgba(0,0,0,0.06);" alt="Shop Photo">
                            </a>`;
                        }).join('');
                        return `<div class="field-item" style="grid-column: span 2;">
                            <div class="field-label"><i class="fa-solid fa-images text-primary"></i> ${label}</div>
                            <div class="field-value" style="display:flex; flex-wrap:wrap; gap:8px; margin-top:6px;">${imgs}</div>
                        </div>`;
                    }
                    return `<div class="field-item"><div class="field-label">${label}</div><div class="field-value">${v}</div></div>`;
                }).join('');
            } catch(e) {
                parsedDetails = `<div style="grid-column: span 2; font-style:italic; color:var(--text-muted);">JSON parse failed.</div>`;
            }

            mainInfoHtml = `
                <div class="info-card">
                    <h3 class="card-title"><i class="fa-solid fa-user"></i> Client Identity Info</h3>
                    <div class="grid-fields">
                        <div class="field-item"><div class="field-label">Applicant Name</div><div class="field-value">${lead.applicant_name}</div></div>
                        <div class="field-item"><div class="field-label">Mobile Number</div><div class="field-value">${lead.mobile_number}</div></div>
                        <div class="field-item"><div class="field-label">PAN Card Number</div><div class="field-value" style="font-family:monospace; font-weight:700; text-transform:uppercase;">${lead.pancard_number}</div></div>
                        <div class="field-item"><div class="field-label">City Location</div><div class="field-value">${lead.city || 'N/A'}</div></div>
                        <div class="field-item"><div class="field-label">Category Type</div><div class="field-value" style="text-transform:uppercase; font-weight:700;">${lead.loan_type}</div></div>
                        <div class="field-item"><div class="field-label">Ingestion Date</div><div class="field-value">${new Date(lead.created_at).toLocaleString('en-IN')}</div></div>
                    </div>
                </div>

                <div class="info-card">
                    <h3 class="card-title"><i class="fa-solid fa-indian-rupee-sign"></i> Loan Requirements</h3>
                    <div class="grid-fields">
                        <div class="field-item" style="grid-column: span 2;"><div class="field-label">Requested Loan Amount</div><div class="field-value" style="font-weight:800; color:var(--accent); font-size: 15px;">₹${parseFloat(lead.loan_amount).toLocaleString('en-IN')}</div></div>
                    </div>
                </div>

                ${aaCardHtml}

                <div class="info-card">
                    <h3 class="card-title"><i class="fa-solid fa-list"></i> Form Parameters</h3>
                    <div class="grid-fields">
                        ${parsedDetails}
                    </div>
                </div>
            `;
        } else {
            mainInfoHtml = `
                <div class="info-card">
                    <h3 class="card-title"><i class="fa-solid fa-indian-rupee-sign"></i> Loan Details</h3>
                    <div class="grid-fields">
                        <div class="field-item"><div class="field-label">Loan Amount Required</div><div class="field-value" style="color: var(--primary); font-size: 15px; font-weight: 750;">â‚¹${parseFloat(lead.loan_amount).toLocaleString('en-IN')}</div></div>
                        <div class="field-item"><div class="field-label">Loan Purpose</div><div class="field-value">${lead.loan_purpose}</div></div>
                        <div class="field-item"><div class="field-label">Monthly Gross Income</div><div class="field-value" style="color: var(--success); font-weight: 700;">â‚¹${parseFloat(lead.monthly_income).toLocaleString('en-IN')}</div></div>
                    </div>
                </div>

                <div class="info-card">
                    <h3 class="card-title"><i class="fa-solid fa-user"></i> Applicant Identity Info</h3>
                    <div class="grid-fields">
                        <div class="field-item"><div class="field-label">Full Name</div><div class="field-value">${lead.applicant_name}</div></div>
                        <div class="field-item"><div class="field-label">Mobile Number</div><div class="field-value">${lead.mobile_number}</div></div>
                        <div class="field-item"><div class="field-label">Email Address</div><div class="field-value">${lead.email}</div></div>
                        <div class="field-item"><div class="field-label">PAN Number</div><div class="field-value" style="font-family:monospace; font-weight: 700; text-transform: uppercase;">${lead.pan_number}</div></div>
                        <div class="field-item"><div class="field-label">Aadhaar Number</div><div class="field-value" style="font-family:monospace;">${lead.aadhaar_number}</div></div>
                        <div class="field-item"><div class="field-label">Pincode</div><div class="field-value">${lead.pincode}</div></div>
                        <div class="field-item"><div class="field-label">City</div><div class="field-value">${lead.city}</div></div>
                        <div class="field-item" style="grid-column: span 2;"><div class="field-label">Full Address</div><div class="field-value">${lead.current_address}</div></div>
                    </div>
                </div>

                <div class="info-card">
                    <h3 class="card-title"><i class="fa-solid fa-store"></i> Business & Shop Parameters</h3>
                    <div class="grid-fields">
                        <div class="field-item"><div class="field-label">Business Name</div><div class="field-value">${lead.shop_business_name}</div></div>
                        <div class="field-item"><div class="field-label">Registration Type</div><div class="field-value">${lead.registration_type}</div></div>
                        <div class="field-item"><div class="field-label">Business Age</div><div class="field-value">${lead.business_age}</div></div>
                        <div class="field-item"><div class="field-label">Business Type</div><div class="field-value">${lead.business_type}</div></div>
                        <div class="field-item"><div class="field-label">Daily Sales</div><div class="field-value" style="color: var(--success); font-weight:700;">â‚¹${parseFloat(lead.daily_sales).toLocaleString('en-IN')}</div></div>
                    </div>
                </div>

                <div class="info-card">
                    <h3 class="card-title"><i class="fa-solid fa-users"></i> Reference Details</h3>
                    <div class="grid-fields" style="grid-template-columns: 1fr; gap: 10px;">
                        <div class="field-item">
                            <div class="field-label">Primary Reference</div>
                            <div class="field-value"><span style="font-weight:700;">${lead.primary_ref_name}</span> (${lead.primary_ref_relation}) - <a href="tel:${lead.primary_ref_mobile}" style="color: var(--primary); text-decoration:none;">${lead.primary_ref_mobile}</a></div>
                        </div>
                        <div class="field-item">
                            <div class="field-label">Secondary Reference</div>
                            <div class="field-value"><span style="font-weight:700;">${lead.secondary_ref_name}</span> (${lead.secondary_ref_relation}) - <a href="tel:${lead.secondary_ref_mobile}" style="color: var(--primary); text-decoration:none;">${lead.secondary_ref_mobile}</a></div>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <h3 class="card-title"><i class="fa-solid fa-building-columns"></i> Bank Details</h3>
                    <div class="grid-fields">
                        <div class="field-item"><div class="field-label">Account Holder</div><div class="field-value">${lead.account_holder}</div></div>
                        <div class="field-item"><div class="field-label">Bank Name</div><div class="field-value">${lead.bank_name}</div></div>
                        <div class="field-item"><div class="field-label">Account Number</div><div class="field-value" style="font-family:monospace;">${lead.account_number}</div></div>
                        <div class="field-item"><div class="field-label">IFSC Code</div><div class="field-value" style="font-family:monospace; font-weight:700; text-transform:uppercase; color:var(--primary);">${lead.ifsc_code}</div></div>
                    </div>
                </div>
            `;
        }

        let checklistHtml = '';
        if (type === 'edi') {
            const verificationItems = [
                { key: 'pan_card_doc', label: 'PAN Card Proof', path: lead.pan_card_doc },
                { key: 'aadhaar_card_doc', label: 'Aadhaar Card Proof', path: lead.aadhaar_card_doc },
                { key: 'bank_statement_doc', label: 'Bank Statement', path: lead.bank_statement_doc },
                { key: 'business_registration_doc', label: 'Business Reg Certificate', path: lead.business_registration_doc },
                { key: 'passport_photo_doc', label: 'Passport Photo', path: lead.passport_photo_doc },
                { key: 'selfie_doc', label: 'Selfie Photo', path: lead.selfie_doc },
                { key: 'shop_front_photo', label: 'Shop Front Photo', path: lead.shop_front_photo },
                { key: 'stock_photo', label: 'Stock Photo 1', path: lead.stock_photo },
                { key: 'stock_photo_two', label: 'Stock Photo 2', path: lead.stock_photo_two }
            ];

            const tableRows = verificationItems.map(item => {
                const check = docChecks[item.key] || {};
                const currentStatus = check.status || 'pending';
                const currentRemark = check.remark || '';
                const verifiedBy = check.verified_by || '';
                const verifiedAt = check.verified_at ? new Date(check.verified_at).toLocaleString('en-IN') : '';

                return `
                    <tr id="doc-row-${item.key}">
                        <td style="font-weight: 700; font-size:12px; color: var(--text-main);">
                            ${item.label}
                            ${verifiedBy !== '' ? `<div style="font-size:9px; color:var(--text-muted); margin-top:2px;">Verified by: ${verifiedBy} @ ${verifiedAt}</div>` : ''}
                        </td>
                        <td>${getDocLink(item.path, item.label)}</td>
                        <td>
                            <span class="checklist-status-badge checklist-status-${currentStatus}" id="badge-doc-${item.key}">
                                ${currentStatus}
                            </span>
                        </td>
                        <td>
                            ${item.path ? `
                                <div style="display:flex; flex-direction:column; gap:5px; max-width:200px;">
                                    <input type="text" id="remark-doc-${item.key}" class="form-input-simple" style="height:26px; font-size:11px; padding:0 6px; border:1px solid var(--border-color); border-radius:4px;" placeholder="Add remarks..." value="${currentRemark}">
                                    <div style="display:flex; gap:5px;">
                                        <button type="button" class="checklist-action-btn checklist-btn-verify" onclick="updateDrawerDocStatus('${lead.id}', '${appDisplayId}', '${item.key}', '${item.label}', 'verified', '${type}')">
                                            <i class="fa-solid fa-circle-check"></i> Approve
                                        </button>
                                        <button type="button" class="checklist-action-btn checklist-btn-reject" onclick="updateDrawerDocStatus('${lead.id}', '${appDisplayId}', '${item.key}', '${item.label}', 'rejected', '${type}')">
                                            <i class="fa-solid fa-circle-xmark"></i> Reject
                                        </button>
                                    </div>
                                </div>
                            ` : `<span style="color:var(--text-muted); font-size:11px; font-style:italic;">No Upload</span>`}
                        </td>
                    </tr>
                `;
            }).join('');

            checklistHtml = `
                <div class="info-card" style="border-left-color: var(--warning);">
                    <h3 class="card-title"><i class="fa-solid fa-folder-open"></i> Underwriter Document Verification Checklist</h3>
                    <div class="table-responsive" style="margin-top:10px;">
                        <table class="checklist-table">
                            <thead>
                                <tr>
                                    <th>Document Description</th>
                                    <th>Link / Preview</th>
                                    <th>Verification Status</th>
                                    <th>Remarks / Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableRows}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        }

        const activitiesHtml = activities.map(act => {
            let dotClass = 'dot-comment';
            if (act.type === 'document') dotClass = 'dot-document';
            if (act.type === 'underwriting') dotClass = 'dot-underwriting';
            if (act.type === 'followup') dotClass = 'dot-followup';
            return `
                <div class="timeline-item">
                    <div class="timeline-item-dot ${dotClass}"></div>
                    <div class="timeline-item-header">
                        <span class="timeline-item-actor"><i class="fa-solid fa-user-tie" style="font-size:9px; color:var(--text-muted);"></i> ${act.actor || 'System'}</span>
                        <span>${new Date(act.created_at).toLocaleString('en-IN')}</span>
                    </div>
                    <div class="timeline-item-desc">${act.description}</div>
                </div>
            `;
        }).join('');

        const timelineLoggerHtml = `
            <div class="info-card" style="border-left-color: var(--info);">
                <h3 class="card-title"><i class="fa-solid fa-clock-rotate-left"></i> CRM Timeline & Underwriting Logs</h3>
                
                <form id="drawerCommentForm" style="display:flex; gap:8px; margin-bottom:15px;" onsubmit="submitDrawerComment(event, '${lead.id}', '${appDisplayId}', '${type}')">
                    <input type="text" name="description" class="form-input-simple" style="flex:1; height:34px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px; font-size:12.5px;" placeholder="Add verification note or call log callback..." required>
                    <button type="submit" class="btn-primary-adm" style="height:34px; box-shadow:none; padding:0 12px; font-size:12px;"><i class="fa-solid fa-paper-plane"></i> Save</button>
                </form>

                <div class="timeline-container">
                    ${activitiesHtml === '' ? '<div style="font-style:italic; font-size:12px; color:var(--text-muted); text-align:center; padding:10px 0;">No activities logged for this case.</div>' : activitiesHtml}
                </div>
            </div>
        `;

        const statusHtml = type === 'edi' ? `
            <div class="info-card" style="border-left-color: var(--primary);">
                <h3 class="card-title" style="border: none; margin-bottom: 5px;"><i class="fa-solid fa-clipboard-check"></i> Status Management</h3>
                <div style="margin-bottom:10px;">
                    <div class="field-label">Current Application Status</div>
                    <div class="status-select-wrap">
                        <select id="updateStatusSelect" class="select-filter" style="flex: 1; padding: 10px 14px; height: 42px;">
                            <option value="pending" ${lead.status === 'pending' ? 'selected' : ''}>Pending</option>
                            <option value="submitted" ${lead.status === 'submitted' ? 'selected' : ''}>Submitted</option>
                            <option value="approved" ${lead.status === 'approved' ? 'selected' : ''}>Approved</option>
                            <option value="rejected" ${lead.status === 'rejected' ? 'selected' : ''}>Rejected</option>
                        </select>
                        <button type="button" class="btn-primary-adm" id="saveStatusBtn" onclick="updateLeadStatus('${lead.id}', '${type}')" style="box-shadow:none; height: 42px;">Update</button>
                    </div>
                    <small id="statusMsg" style="display:block; margin-top:8px; font-weight:600;"></small>
                </div>
            </div>
        ` : '';

        drawerBody.innerHTML = `
            ${headerHtml}
            ${timelineHtml}
            ${cibilHtml}
            ${mainInfoHtml}
            ${checklistHtml}
            ${timelineLoggerHtml}
            ${statusHtml}
        `;
    }

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
    function renderLeadDrawerEditForm(data, type) {
        const lead = data.application;
        const appDisplayId = lead.application_id || 'N/A';
        
        if (type === 'general') {
            drawerBody.innerHTML = `
                <form id="drawerEditForm" onsubmit="submitDrawerEdit(event, '${lead.id}', '${type}')">
                    <div style="background: rgba(24,82,156,0.05); padding:16px; border-radius:12px; border:1px dashed var(--primary); margin-bottom: 20px;">
                        <div class="field-label">General Application ID</div>
                        <div class="field-value" style="color:var(--primary); font-size:15px; font-weight:800;">${appDisplayId}</div>
                    </div>

                    <div class="info-card">
                        <h3 class="card-title"><i class="fa-solid fa-user-pen"></i> Edit Identity & Requirements</h3>
                        <div style="display:flex; flex-direction:column; gap:12px;">
                            <div>
                                <label class="field-label">Applicant Name</label>
                                <input type="text" name="applicant_name" class="form-input-simple" style="width:100%; height:38px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px;" value="${lead.applicant_name}" required>
                            </div>
                            <div>
                                <label class="field-label">Mobile Number</label>
                                <input type="text" name="mobile_number" class="form-input-simple" style="width:100%; height:38px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px;" value="${lead.mobile_number}" required>
                            </div>
                            <div>
                                <label class="field-label">PAN Card Number</label>
                                <input type="text" name="pancard_number" class="form-input-simple" style="width:100%; height:38px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px; text-transform:uppercase;" value="${lead.pancard_number}" required>
                            </div>
                            <div>
                                <label class="field-label">City Location</label>
                                <input type="text" name="city" class="form-input-simple" style="width:100%; height:38px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px;" value="${lead.city || ''}">
                            </div>
                            <div>
                                <label class="field-label">Requested Loan Amount (â‚¹)</label>
                                <input type="number" name="loan_amount" class="form-input-simple" style="width:100%; height:38px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px;" value="${lead.loan_amount}" required>
                            </div>
                            <div>
                                <label class="field-label">Category Type</label>
                                <select name="loan_type" class="select-filter" style="width:100%; height:38px; padding: 0 10px;">
                                    <option value="personal" ${lead.loan_type === 'personal' ? 'selected' : ''}>Personal</option>
                                    <option value="business" ${lead.loan_type === 'business' ? 'selected' : ''}>Business</option>
                                    <option value="vehicle" ${lead.loan_type === 'vehicle' ? 'selected' : ''}>Vehicle</option>
                                    <option value="lap" ${lead.loan_type === 'lap' ? 'selected' : ''}>LAP (Property)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex; gap:10px; margin-top:20px;">
                        <button type="submit" class="btn-primary-adm" style="flex:1; height:42px; box-shadow:none;"><i class="fa-solid fa-save"></i> Save Changes</button>
                        <button type="button" class="btn-action" style="flex:1; height:42px; border:1px solid var(--border-color); color:var(--text-muted); text-align:center; display:flex; align-items:center; justify-content:center;" onclick="loadLeadDetails('${lead.id}', '${type}', 'view')">Cancel</button>
                    </div>
                </form>
            `;
            return;
        }

        // EDI edit form
        drawerBody.innerHTML = `
            <form id="drawerEditForm" onsubmit="submitDrawerEdit(event, '${lead.id}', '${type}')">
                <div style="background: rgba(24,82,156,0.05); padding:16px; border-radius:12px; border:1px dashed var(--primary); margin-bottom: 20px;">
                    <div class="field-label">EDI Application ID</div>
                    <div class="field-value" style="color:var(--primary); font-size:15px; font-weight:800;">${appDisplayId}</div>
                </div>

                <div class="info-card">
                    <h3 class="card-title"><i class="fa-solid fa-user-pen"></i> Edit Personal & Contact Info</h3>
                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <div>
                            <label class="field-label">Full Name</label>
                            <input type="text" name="applicant_name" class="form-input-simple" style="width:100%; height:38px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px;" value="${lead.applicant_name}" required>
                        </div>
                        <div>
                            <label class="field-label">Mobile Number</label>
                            <input type="text" name="mobile_number" class="form-input-simple" style="width:100%; height:38px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px;" value="${lead.mobile_number}" required>
                        </div>
                        <div>
                            <label class="field-label">Email Address</label>
                            <input type="email" name="email" class="form-input-simple" style="width:100%; height:38px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px;" value="${lead.email || ''}" required>
                        </div>
                        <div>
                            <label class="field-label">PAN Number</label>
                            <input type="text" name="pan_number" class="form-input-simple" style="width:100%; height:38px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px; text-transform:uppercase;" value="${lead.pan_number}" required>
                        </div>
                        <div>
                            <label class="field-label">Aadhaar Number</label>
                            <input type="text" name="aadhaar_number" class="form-input-simple" style="width:100%; height:38px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px;" value="${lead.aadhaar_number}" required>
                        </div>
                        <div>
                            <label class="field-label">City</label>
                            <input type="text" name="city" class="form-input-simple" style="width:100%; height:38px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px;" value="${lead.city || ''}" required>
                        </div>
                        <div>
                            <label class="field-label">Pincode</label>
                            <input type="text" name="pincode" class="form-input-simple" style="width:100%; height:38px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px;" value="${lead.pincode || ''}" required>
                        </div>
                        <div>
                            <label class="field-label">Full Address</label>
                            <input type="text" name="current_address" class="form-input-simple" style="width:100%; height:38px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px;" value="${lead.current_address || ''}" required>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <h3 class="card-title"><i class="fa-solid fa-store"></i> Edit Business & Loan Info</h3>
                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <div>
                            <label class="field-label">Business/Shop Name</label>
                            <input type="text" name="shop_business_name" class="form-input-simple" style="width:100%; height:38px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px;" value="${lead.shop_business_name || ''}" required>
                        </div>
                        <div>
                            <label class="field-label">Registration Type</label>
                            <input type="text" name="registration_type" class="form-input-simple" style="width:100%; height:38px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px;" value="${lead.registration_type || ''}">
                        </div>
                        <div>
                            <label class="field-label">Business Age</label>
                            <input type="text" name="business_age" class="form-input-simple" style="width:100%; height:38px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px;" value="${lead.business_age || ''}">
                        </div>
                        <div>
                            <label class="field-label">Daily Sales (â‚¹)</label>
                            <input type="number" name="daily_sales" class="form-input-simple" style="width:100%; height:38px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px;" value="${lead.daily_sales}" required>
                        </div>
                        <div>
                            <label class="field-label">Monthly Income (â‚¹)</label>
                            <input type="number" name="monthly_income" class="form-input-simple" style="width:100%; height:38px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px;" value="${lead.monthly_income}" required>
                        </div>
                        <div>
                            <label class="field-label">Loan Amount Required (â‚¹)</label>
                            <input type="number" name="loan_amount" class="form-input-simple" style="width:100%; height:38px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px;" value="${lead.loan_amount}" required>
                        </div>
                        <div>
                            <label class="field-label">Loan Purpose</label>
                            <input type="text" name="loan_purpose" class="form-input-simple" style="width:100%; height:38px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px;" value="${lead.loan_purpose || ''}">
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <h3 class="card-title"><i class="fa-solid fa-building-columns"></i> Edit Bank Account Info</h3>
                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <div>
                            <label class="field-label">Account Holder Name</label>
                            <input type="text" name="account_holder" class="form-input-simple" style="width:100%; height:38px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px;" value="${lead.account_holder || ''}" required>
                        </div>
                        <div>
                            <label class="field-label">Bank Name</label>
                            <input type="text" name="bank_name" class="form-input-simple" style="width:100%; height:38px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px;" value="${lead.bank_name || ''}" required>
                        </div>
                        <div>
                            <label class="field-label">Account Number</label>
                            <input type="text" name="account_number" class="form-input-simple" style="width:100%; height:38px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px;" value="${lead.account_number || ''}" required>
                        </div>
                        <div>
                            <label class="field-label">IFSC Code</label>
                            <input type="text" name="ifsc_code" class="form-input-simple" style="width:100%; height:38px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px; text-transform:uppercase;" value="${lead.ifsc_code || ''}" required>
                        </div>
                    </div>
                </div>

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn-primary-adm" style="flex:1; height:42px; box-shadow:none;"><i class="fa-solid fa-save"></i> Save Changes</button>
                    <button type="button" class="btn-action" style="flex:1; height:42px; border:1px solid var(--border-color); color:var(--text-muted); text-align:center; display:flex; align-items:center; justify-content:center;" onclick="loadLeadDetails('${lead.id}', '${type}', 'view')">Cancel</button>
                </div>
            </form>
        `;
    }

    // Submit Drawer Edit via AJAX
    function submitDrawerEdit(e, id, type) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        formData.append('action', 'save_lead_details');
        formData.append('id', id);
        formData.append('type', type);
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token'] ?? ''; ?>');

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
                            drawer.classList.remove('active');
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
        if (!actionButton || !masterResultsArea.contains(actionButton)) return;
        event.preventDefault();

        const action = getMasterAction(actionButton);
        if (!action || !action.id) return;

        if (action.action === 'delete') {
            deleteLeadRow(action.id, action.type);
            return;
        }

        loadLeadDetails(action.id, action.type, action.action === 'edit' ? 'edit' : 'view');
    });
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
        if (e.key === 'Escape') {
            closeImageLightbox();
        }
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
                return fetch('api-sync-edi-docs.php?app_id=' + encodeURIComponent(appId))
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
    window.openMasterStatementModal = function(txnId) {
        Swal.fire({
            title: 'Loading Bank Statement...',
            html: '<div style="padding:20px;"><i class="fa-solid fa-spinner fa-spin" style="font-size:32px; color:var(--primary);"></i><div style="margin-top:10px; font-weight:600;">Fetching live aggregated transactions...</div></div>',
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: () => {
                fetch('../api/bank-data/report.php?txn_id=' + encodeURIComponent(txnId))
                    .then(r => r.json())
                    .then(data => {
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
                            const senders = row.txns.map(t => `<div style="font-size:11px; color:#475569; margin-bottom:2px;"><strong style="color:#15803d;">+₹${parseFloat(t.amount).toLocaleString('en-IN', {minimumFractionDigits:2})}</strong> &bull; ${t.narration || 'Credit'}</div>`).join('');

                            dailyRows += `
                                <tr style="border-bottom:1px solid #e2e8f0; font-size:12px; background:#f0fdf4;">
                                    <td style="padding:7px 8px; color:#64748b;">${i+1}</td>
                                    <td style="padding:7px 8px; font-weight:700; color:#0f172a; white-space:nowrap;"><i class="fa-regular fa-calendar text-primary" style="margin-right:4px;"></i>${row.date}</td>
                                    <td style="padding:7px 8px; text-align:right;"><span style="font-weight:800; color:#15803d; background:#dcfce7; padding:3px 7px; border-radius:4px; font-size:13px; border:1px solid #86efac;">+₹${row.cr.toLocaleString('en-IN', {minimumFractionDigits:2})}</span></td>
                                    <td style="padding:7px 8px; text-align:center;"><span style="background:#bbf7d0; color:#166534; font-weight:700; font-size:11px; padding:2px 6px; border-radius:4px;">${row.count} Credits</span></td>
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
                                    <td style="padding:6px 8px; white-space:nowrap; font-weight:600;">${t.date || '-'}</td>
                                    <td style="padding:6px 8px; max-width:260px; word-break:break-word; text-align:left;">${t.narration || '-'}</td>
                                    <td style="padding:6px 8px; font-family:monospace; font-size:10.5px; color:#475569;">${t.reference || t.transaction_id || '-'}</td>
                                    <td style="padding:6px 8px;">${badge}</td>
                                    <td style="padding:6px 8px; text-align:right; font-weight:700; color:${amtColor}; white-space:nowrap;">${amtStr}</td>
                                    <td style="padding:6px 8px; text-align:right; font-family:monospace;">${balStr}</td>
                                </tr>
                            `;
                        });

                        Swal.fire({
                            title: `${bank.bank || 'Bank'} - Verified Bank Statement`,
                            width: '1000px',
                            html: `
                                <div style="text-align:left; font-size:13px;">
                                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-bottom:12px; display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                                        <div>
                                            <div style="font-weight:700; color:#0f172a; font-size:14px;">${holderName}</div>
                                            <div style="font-size:11.5px; color:#64748b;">A/C: <span style="font-family:monospace; font-weight:700;">${acc.account_number || 'N/A'}</span> | IFSC: <span style="font-family:monospace; font-weight:700;">${acc.ifsc_code || 'N/A'}</span></div>
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
                                            <a href="../api/bank-data/export-statement.php?txn_id=${encodeURIComponent(txnId)}&view=daily" target="_blank" style="background:#15803d; color:#fff; padding:6px 12px; border-radius:6px; font-size:11.5px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
                                                <i class="fa-solid fa-download"></i> Daily Credit CSV
                                            </a>
                                            <a href="../api/bank-data/export-statement.php?txn_id=${encodeURIComponent(txnId)}" target="_blank" style="background:#0f172a; color:#fff; padding:6px 12px; border-radius:6px; font-size:11.5px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
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
