<?php
declare(strict_types=1);

$pageTitle = 'Payday & Legacy Applications';
$pageSubtitle = 'Salary payday loans and old legacy wizard applications';

include 'header.php';

$conn = db();

// Active tab selection
$activeTab = $_GET['tab'] ?? 'payday';
$allowedTabs = ['payday', 'wizard'];
if (!in_array($activeTab, $allowedTabs, true)) {
    $activeTab = 'payday';
}
$isPayday = $activeTab === 'payday';

// Rows per page (whitelisted)
$perPageOptions = [10, 25, 50, 100];
$limit = (int) ($_GET['per_page'] ?? 10);
if (!in_array($limit, $perPageOptions, true)) {
    $limit = 10;
}
$page = max(1, (int) ($_GET['page'] ?? 1));

// Filters
$search = trim((string) ($_GET['search'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));
$dateFrom = trim((string) ($_GET['date_from'] ?? ''));
$dateTo = trim((string) ($_GET['date_to'] ?? ''));
$amountMin = trim((string) ($_GET['amount_min'] ?? ''));
$amountMax = trim((string) ($_GET['amount_max'] ?? ''));
$loanType = trim((string) ($_GET['loan_type'] ?? ''));

$statusLabels = [
    'draft' => 'Draft',
    'submitted' => 'Submitted',
    'review' => 'In review',
    'approved' => 'Approved',
    'rejected' => 'Rejected',
    'disbursed' => 'Disbursed',
];
if (!$isPayday || !array_key_exists($status, $statusLabels)) {
    $status = '';
}

foreach (['dateFrom', 'dateTo'] as $dateVar) {
    if ($$dateVar !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $$dateVar)) {
        $$dateVar = '';
    }
}
foreach (['amountMin', 'amountMax'] as $amountVar) {
    if ($$amountVar !== '' && (!is_numeric($$amountVar) || (float) $$amountVar < 0)) {
        $$amountVar = '';
    }
}
// A reversed range is almost always a typo; swap it instead of returning nothing.
if ($dateFrom !== '' && $dateTo !== '' && $dateFrom > $dateTo) {
    [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
}
if ($amountMin !== '' && $amountMax !== '' && (float) $amountMin > (float) $amountMax) {
    [$amountMin, $amountMax] = [$amountMax, $amountMin];
}

if ($isPayday) {
    $loanType = '';
}

$baseQueryParams = [
    'tab' => $activeTab,
    'search' => $search,
    'status' => $status,
    'date_from' => $dateFrom,
    'date_to' => $dateTo,
    'amount_min' => $amountMin,
    'amount_max' => $amountMax,
    'loan_type' => $loanType,
    'per_page' => $limit === 10 ? '' : (string) $limit,
];

function applications_build_query(array $baseParams, array $overrides = []): string
{
    $query = array_merge($baseParams, $overrides);
    foreach ($query as $key => $value) {
        if ($value === '' || $value === null) {
            unset($query[$key]);
        }
    }

    return http_build_query($query);
}

// Indian digit grouping: 1234567 -> 12,34,567
function applications_inr(float $amount): string
{
    $num = (string) (int) round(abs($amount));
    if (strlen($num) > 3) {
        $num = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', substr($num, 0, -3)) . ',' . substr($num, -3);
    }
    return ($amount < 0 ? '-' : '') . $num;
}

function applications_format_money(float $amount): string
{
    if ($amount >= 10000000) {
        return '₹' . number_format($amount / 10000000, 2) . ' Cr';
    }
    if ($amount >= 100000) {
        return '₹' . number_format($amount / 100000, 2) . ' L';
    }
    return '₹' . applications_inr($amount);
}

function applications_e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$filtersActive = $search !== '' || $status !== '' || $dateFrom !== '' || $dateTo !== '' || $amountMin !== '' || $amountMax !== '' || $loanType !== '';
$today = date('Y-m-d');

// ---------- KPI cards ----------
$kpiCards = [];
if ($isPayday) {
    $statsRes = $conn->query("
        SELECT
            COUNT(*) AS total_applications,
            SUM(CASE WHEN created_at >= CURDATE() AND created_at < DATE_ADD(CURDATE(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) AS today_applications,
            SUM(CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND created_at < CURDATE() THEN 1 ELSE 0 END) AS yesterday_applications,
            SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) AS submitted_applications,
            SUM(CASE WHEN status = 'review' THEN 1 ELSE 0 END) AS review_applications,
            SUM(CASE WHEN status IN ('approved', 'disbursed') THEN 1 ELSE 0 END) AS successful_applications,
            SUM(CASE WHEN status = 'disbursed' THEN 1 ELSE 0 END) AS disbursed_applications
        FROM loan_applications
    ");
    $stats = $statsRes ? ($statsRes->fetch_assoc() ?: []) : [];
    $submittedCount = (int) ($stats['submitted_applications'] ?? 0);

    $kpiCards = [
        ['label' => 'Total applications', 'value' => applications_inr((float) ($stats['total_applications'] ?? 0)), 'sub' => 'All payday leads', 'icon' => 'fa-layer-group', 'tone' => '', 'href' => '?' . applications_build_query(['tab' => 'payday'])],
        ['label' => "Today's applications", 'value' => applications_inr((float) ($stats['today_applications'] ?? 0)), 'sub' => 'Yesterday: ' . applications_inr((float) ($stats['yesterday_applications'] ?? 0)), 'icon' => 'fa-calendar-day', 'tone' => '', 'href' => '?' . applications_build_query(['tab' => 'payday', 'date_from' => $today, 'date_to' => $today])],
        ['label' => 'Awaiting review', 'value' => applications_inr((float) $submittedCount), 'sub' => applications_inr((float) ($stats['review_applications'] ?? 0)) . ' more already in review', 'icon' => 'fa-hourglass-half', 'tone' => $submittedCount > 0 ? 'alert' : '', 'href' => '?' . applications_build_query(['tab' => 'payday', 'status' => 'submitted'])],
        ['label' => 'Approved / disbursed', 'value' => applications_inr((float) ($stats['successful_applications'] ?? 0)), 'sub' => applications_inr((float) ($stats['disbursed_applications'] ?? 0)) . ' disbursed', 'icon' => 'fa-circle-check', 'tone' => 'good', 'href' => '?' . applications_build_query(['tab' => 'payday', 'status' => 'approved'])],
    ];
} else {
    $statsRes = $conn->query("
        SELECT
            COUNT(*) AS total_applications,
            SUM(CASE WHEN created_at >= CURDATE() AND created_at < DATE_ADD(CURDATE(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) AS today_applications,
            SUM(CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND created_at < CURDATE() THEN 1 ELSE 0 END) AS yesterday_applications,
            SUM(CASE WHEN created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN 1 ELSE 0 END) AS month_applications,
            COALESCE(SUM(CAST(REPLACE(loan_amount, ',', '') AS DECIMAL(12,2))), 0) AS total_loan_value
        FROM apply_loan_applications
    ");
    $stats = $statsRes ? ($statsRes->fetch_assoc() ?: []) : [];

    $kpiCards = [
        ['label' => 'Total applications', 'value' => applications_inr((float) ($stats['total_applications'] ?? 0)), 'sub' => 'All legacy wizard leads', 'icon' => 'fa-layer-group', 'tone' => '', 'href' => '?' . applications_build_query(['tab' => 'wizard'])],
        ['label' => "Today's applications", 'value' => applications_inr((float) ($stats['today_applications'] ?? 0)), 'sub' => 'Yesterday: ' . applications_inr((float) ($stats['yesterday_applications'] ?? 0)), 'icon' => 'fa-calendar-day', 'tone' => '', 'href' => '?' . applications_build_query(['tab' => 'wizard', 'date_from' => $today, 'date_to' => $today])],
        ['label' => 'This month', 'value' => applications_inr((float) ($stats['month_applications'] ?? 0)), 'sub' => date('F Y'), 'icon' => 'fa-chart-line', 'tone' => '', 'href' => '?' . applications_build_query(['tab' => 'wizard', 'date_from' => date('Y-m-01'), 'date_to' => $today])],
        ['label' => 'Requested loan value', 'value' => applications_format_money((float) ($stats['total_loan_value'] ?? 0)), 'sub' => 'Sum of all requested amounts', 'icon' => 'fa-indian-rupee-sign', 'tone' => '', 'href' => null],
    ];
}

// Tab counts (unfiltered totals)
$tabCounts = ['payday' => 0, 'wizard' => 0];
$tcRes = $conn->query("SELECT (SELECT COUNT(*) FROM loan_applications), (SELECT COUNT(*) FROM apply_loan_applications)");
if ($tcRes) {
    $tcRow = $tcRes->fetch_row();
    $tabCounts = ['payday' => (int) ($tcRow[0] ?? 0), 'wizard' => (int) ($tcRow[1] ?? 0)];
}

// ---------- Build WHERE (everything except status first, so status chips can show counts) ----------
$table = $isPayday ? 'loan_applications' : 'apply_loan_applications';
$conditions = [];
$params = [];
$types = '';
$amountExpression = $isPayday ? 'loan_amount' : "CAST(REPLACE(loan_amount, ',', '') AS DECIMAL(12,2))";

if ($dateFrom !== '') {
    $conditions[] = 'created_at >= ?';
    $params[] = $dateFrom . ' 00:00:00';
    $types .= 's';
}
if ($dateTo !== '') {
    $conditions[] = 'created_at <= ?';
    $params[] = $dateTo . ' 23:59:59';
    $types .= 's';
}
if ($amountMin !== '') {
    $conditions[] = "$amountExpression >= ?";
    $params[] = (float) $amountMin;
    $types .= 'd';
}
if ($amountMax !== '') {
    $conditions[] = "$amountExpression <= ?";
    $params[] = (float) $amountMax;
    $types .= 'd';
}
if ($search !== '') {
    $idColumn = $isPayday ? 'application_id' : 'application_ref';
    $conditions[] = "($idColumn LIKE ? OR full_name LIKE ? OR mobile LIKE ? OR email LIKE ? OR pan_number LIKE ?)";
    $searchWild = '%' . $search . '%';
    array_push($params, $searchWild, $searchWild, $searchWild, $searchWild, $searchWild);
    $types .= 'sssss';
}
if (!$isPayday && $loanType !== '') {
    $conditions[] = 'loan_type = ?';
    $params[] = $loanType;
    $types .= 's';
}

// Status chip counts honour every other active filter.
$statusChipCounts = array_fill_keys(array_keys($statusLabels), 0);
$statusChipTotal = 0;
if ($isPayday) {
    $chipWhere = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
    $chipStmt = $conn->prepare("SELECT status, COUNT(*) AS c FROM loan_applications $chipWhere GROUP BY status");
    if ($chipStmt) {
        if (!empty($params)) {
            $chipStmt->bind_param($types, ...$params);
        }
        $chipStmt->execute();
        foreach ($chipStmt->get_result()->fetch_all(MYSQLI_ASSOC) as $chipRow) {
            $key = (string) $chipRow['status'];
            $statusChipTotal += (int) $chipRow['c'];
            if (array_key_exists($key, $statusChipCounts)) {
                $statusChipCounts[$key] = (int) $chipRow['c'];
            }
        }
    }
}

if ($isPayday && $status !== '') {
    $conditions[] = 'status = ?';
    $params[] = $status;
    $types .= 's';
}

$whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Count
$totalRecords = 0;
$countStmt = $conn->prepare("SELECT COUNT(*) FROM $table $whereClause");
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalRecords = (int) ($countStmt->get_result()->fetch_row()[0] ?? 0);

$totalPages = max(1, (int) ceil($totalRecords / $limit));
if ($page > $totalPages) {
    $page = $totalPages; // e.g. after deleting the last row on the last page
}
$offset = ($page - 1) * $limit;

// Fetch page
$fetchStmt = $conn->prepare("SELECT * FROM $table $whereClause ORDER BY id DESC LIMIT ? OFFSET ?");
$fetchParams = $params;
$fetchParams[] = $limit;
$fetchParams[] = $offset;
$fetchStmt->bind_param($types . 'ii', ...$fetchParams);
$fetchStmt->execute();
$apps = $fetchStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$loanTypeOptions = [];
if (!$isPayday) {
    $loanTypeRes = $conn->query("SELECT DISTINCT loan_type FROM apply_loan_applications WHERE loan_type IS NOT NULL AND loan_type != '' ORDER BY loan_type ASC");
    if ($loanTypeRes) {
        while ($row = $loanTypeRes->fetch_assoc()) {
            $loanTypeOptions[] = (string) $row['loan_type'];
        }
    }
}

$rangeStart = $totalRecords > 0 ? $offset + 1 : 0;
$rangeEnd = min($offset + $limit, $totalRecords);

// Removable chips describing the active filters
$activeFilterChips = [];
if ($search !== '') $activeFilterChips[] = ['label' => 'Search: “' . $search . '”', 'remove' => ['search' => '']];
if ($status !== '') $activeFilterChips[] = ['label' => 'Status: ' . $statusLabels[$status], 'remove' => ['status' => '']];
if ($loanType !== '') $activeFilterChips[] = ['label' => 'Loan type: ' . $loanType, 'remove' => ['loan_type' => '']];
if ($dateFrom !== '' || $dateTo !== '') {
    $fromLabel = $dateFrom !== '' ? date('d M Y', strtotime($dateFrom)) : '…';
    $toLabel = $dateTo !== '' ? date('d M Y', strtotime($dateTo)) : 'today';
    $activeFilterChips[] = ['label' => ($dateFrom === $dateTo ? 'Date: ' . $fromLabel : 'Date: ' . $fromLabel . ' – ' . $toLabel), 'remove' => ['date_from' => '', 'date_to' => '']];
}
if ($amountMin !== '' || $amountMax !== '') {
    $minLabel = $amountMin !== '' ? '₹' . applications_inr((float) $amountMin) : '₹0';
    $maxLabel = $amountMax !== '' ? '₹' . applications_inr((float) $amountMax) : 'any';
    $activeFilterChips[] = ['label' => 'Amount: ' . $minLabel . ' – ' . $maxLabel, 'remove' => ['amount_min' => '', 'amount_max' => '']];
}

$colCount = $isPayday ? 7 : 7;
?>

<style>
    /* ---------- Tabs ---------- */
    .app-tabs {
        display: flex;
        gap: 4px;
        border-bottom: 1px solid var(--border-color);
        margin: -6px 0 22px;
        overflow-x: auto;
        scrollbar-width: none;
    }
    .app-tabs::-webkit-scrollbar { display: none; }
    .app-tab {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px 12px;
        font-size: 14px;
        font-weight: 600;
        color: var(--text-muted);
        text-decoration: none;
        white-space: nowrap;
        transition: color 0.15s var(--ease);
    }
    .app-tab:hover { color: var(--text-main); }
    .app-tab.active { color: var(--primary); }
    .app-tab.active::after {
        content: '';
        position: absolute;
        left: 8px;
        right: 8px;
        bottom: -1px;
        height: 2px;
        border-radius: 2px 2px 0 0;
        background: var(--primary);
    }
    .app-tab .count {
        font-size: 11.5px;
        font-weight: 700;
        padding: 1px 8px;
        border-radius: 999px;
        background: #eef1f6;
        color: var(--text-secondary);
        font-variant-numeric: tabular-nums;
    }
    .app-tab.active .count { background: var(--primary-soft); color: var(--primary); }

    /* ---------- KPI cards ---------- */
    .app-kpis {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }
    .app-kpi {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 18px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-xs);
        text-decoration: none;
        color: inherit;
        min-width: 0;
        transition: border-color 0.15s var(--ease), box-shadow 0.15s var(--ease);
    }
    a.app-kpi:hover { border-color: #b9cbe3; box-shadow: var(--shadow-sm); }
    .app-kpi.is-current { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-glow); }
    .app-kpi-label { font-size: 12.5px; font-weight: 600; color: var(--text-muted); }
    .app-kpi-value {
        font-family: 'Outfit', sans-serif;
        font-size: 26px;
        font-weight: 700;
        line-height: 1.2;
        margin-top: 2px;
        color: var(--text-main);
        font-variant-numeric: tabular-nums;
    }
    .app-kpi-sub { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
    .app-kpi-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        background: var(--primary-soft);
        color: var(--primary);
    }
    .app-kpi.alert .app-kpi-icon { background: #fef2f2; color: #dc2626; }
    .app-kpi.good .app-kpi-icon { background: #f0fdf4; color: #15803d; }

    /* ---------- Records card ---------- */
    .records-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-xs);
        min-width: 0;
    }
    .records-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding: 18px 20px 14px;
    }
    .records-title {
        font-family: 'Outfit', sans-serif;
        font-size: 16px;
        font-weight: 600;
        color: var(--text-main);
    }
    .records-meta { font-size: 12.5px; color: var(--text-muted); margin-top: 1px; }
    .records-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

    .btn-ghost-adm {
        height: 38px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0 14px;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border-strong);
        background: #ffffff;
        color: var(--text-main);
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: border-color 0.15s var(--ease), color 0.15s var(--ease), background-color 0.15s var(--ease);
    }
    .btn-ghost-adm:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-soft); }
    .btn-ghost-adm i { font-size: 13px; }
    .records-actions .btn-primary-adm { height: 38px; padding: 0 16px; }

    /* Status chips */
    .status-chips {
        display: flex;
        gap: 8px;
        padding: 0 20px 14px;
        overflow-x: auto;
        scrollbar-width: none;
    }
    .status-chips::-webkit-scrollbar { display: none; }
    .status-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 32px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid var(--border-color);
        background: #ffffff;
        color: var(--text-secondary);
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        transition: border-color 0.15s var(--ease), background-color 0.15s var(--ease), color 0.15s var(--ease);
    }
    .status-chip:hover { border-color: var(--border-strong); color: var(--text-main); }
    .status-chip .dot { width: 7px; height: 7px; border-radius: 50%; background: #94a3b8; }
    .status-chip .num { font-variant-numeric: tabular-nums; color: var(--text-muted); font-weight: 600; }
    .status-chip.active { background: #0f172a; border-color: #0f172a; color: #ffffff; }
    .status-chip.active .num { color: rgba(255, 255, 255, 0.7); }
    .status-chip.is-empty { opacity: 0.55; }
    .dot-draft { background: #64748b !important; }
    .dot-submitted { background: #2563eb !important; }
    .dot-review { background: #d97706 !important; }
    .dot-approved { background: #16a34a !important; }
    .dot-rejected { background: #dc2626 !important; }
    .dot-disbursed { background: #0d9488 !important; }

    /* Filter toolbar */
    .filter-bar {
        display: grid;
        grid-template-columns: minmax(220px, 2fr) repeat(auto-fit, minmax(130px, 1fr));
        gap: 10px;
        align-items: end;
        padding: 14px 20px;
        border-top: 1px solid var(--border-color);
        background: var(--bg-subtle);
    }
    .filter-bar .fld { display: flex; flex-direction: column; gap: 5px; min-width: 0; }
    .filter-bar label {
        font-size: 11.5px;
        font-weight: 600;
        color: var(--text-muted);
    }
    .filter-bar input,
    .filter-bar select {
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
    .filter-bar input:focus,
    .filter-bar select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-glow); }
    .filter-bar .search-wrap { position: relative; }
    .filter-bar .search-wrap i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 12.5px;
        pointer-events: none;
    }
    .filter-bar .search-wrap input { padding-left: 34px; }
    .filter-bar .range { display: flex; align-items: center; gap: 6px; }
    .filter-bar .range span { color: var(--text-muted); font-size: 12px; }
    .filter-bar .fld-actions { flex-direction: row; gap: 8px; }
    .filter-bar .fld-actions .btn-primary-adm { height: 38px; padding: 0 16px; justify-content: center; }
    .filter-bar .fld-actions .btn-ghost-adm { padding: 0 12px; }

    .active-filters {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        padding: 10px 20px;
        border-top: 1px solid var(--border-color);
        font-size: 12.5px;
        color: var(--text-muted);
    }
    .filter-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 26px;
        padding: 0 6px 0 10px;
        border-radius: 999px;
        background: var(--primary-soft);
        color: var(--primary);
        font-weight: 600;
        text-decoration: none;
    }
    .filter-pill i {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
    }
    .filter-pill:hover i { background: rgba(24, 82, 156, 0.15); }
    .clear-all { color: var(--text-muted); font-weight: 600; text-decoration: underline; text-underline-offset: 2px; }
    .clear-all:hover { color: var(--danger); }

    /* Table */
    .records-card .table-responsive { border-radius: 0; border-top: 1px solid var(--border-color); }
    .records-card .admin-table th:first-child, .records-card .admin-table td:first-child { padding-left: 20px; }
    .records-card .admin-table th:last-child, .records-card .admin-table td:last-child { padding-right: 20px; }
    .records-card .admin-table td { padding-top: 12px; padding-bottom: 12px; vertical-align: middle; }
    .records-card .admin-table tbody tr { cursor: pointer; }
    .records-card .admin-table tbody tr.row-empty { cursor: default; }
    .th-right, .td-right { text-align: right; }
    .cell-main { font-weight: 600; color: var(--text-main); }
    .cell-main.placeholder { font-weight: 500; color: var(--text-muted); font-style: italic; }
    .cell-sub { font-size: 12px; color: var(--text-muted); margin-top: 1px; max-width: 260px; overflow: hidden; text-overflow: ellipsis; }
    .cell-id {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--text-secondary);
        font-variant-numeric: tabular-nums;
    }
    .copy-btn {
        border: none;
        background: transparent;
        color: #94a3b8;
        cursor: pointer;
        width: 24px;
        height: 24px;
        border-radius: 6px;
        font-size: 11.5px;
        opacity: 0;
        transition: opacity 0.15s var(--ease), background-color 0.15s var(--ease), color 0.15s var(--ease);
    }
    tr:hover .copy-btn, .copy-btn:focus-visible { opacity: 1; }
    .copy-btn:hover { background: #eef1f6; color: var(--primary); }
    .copy-btn.copied { opacity: 1; color: #15803d; }
    .cell-amount { font-weight: 600; font-variant-numeric: tabular-nums; }
    .cell-date { font-size: 13px; color: var(--text-main); font-variant-numeric: tabular-nums; }
    .cell-time { font-size: 12px; color: var(--text-muted); }
    .type-chip {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 6px;
        background: #f1f5f9;
        color: var(--text-secondary);
        font-size: 12px;
        font-weight: 600;
    }
    .row-actions { display: inline-flex; gap: 6px; justify-content: flex-end; }
    .row-actions .btn-action { width: 32px; height: 32px; }
    .row-actions .btn-action.danger { color: #b91c1c; border-color: #fecaca; background: #ffffff; }
    .row-actions .btn-action.danger:hover { background: #fef2f2; border-color: #dc2626; color: #dc2626; }

    .empty-block {
        padding: 56px 20px !important;
        text-align: center;
        white-space: normal !important;
        color: var(--text-muted);
    }
    .empty-block i.big { font-size: 28px; color: #cbd5e1; display: block; margin-bottom: 10px; }
    .empty-block strong { display: block; color: var(--text-main); font-size: 14px; margin-bottom: 4px; }
    .empty-block .btn-ghost-adm { margin-top: 14px; }

    /* Footer: range + page size + pagination */
    .records-foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding: 12px 20px;
        border-top: 1px solid var(--border-color);
        font-size: 12.5px;
        color: var(--text-muted);
    }
    .records-foot .per-page { display: inline-flex; align-items: center; gap: 8px; }
    .records-foot select {
        height: 32px;
        padding: 0 8px;
        border: 1px solid var(--border-strong);
        border-radius: 7px;
        background: #ffffff;
        font-size: 12.5px;
        color: var(--text-main);
        cursor: pointer;
    }
    .records-foot .pagination { display: flex; gap: 4px; list-style: none; margin: 0; }
    .page-item a, .page-item span {
        min-width: 32px;
        height: 32px;
        padding: 0 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border-color);
        background: #ffffff;
        color: var(--text-secondary);
        border-radius: 7px;
        text-decoration: none;
        font-size: 12.5px;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        transition: border-color 0.15s var(--ease), color 0.15s var(--ease);
    }
    .page-item a:hover { border-color: var(--primary); color: var(--primary); }
    .page-item.active span { background: var(--primary); border-color: var(--primary); color: #ffffff; }
    .page-item.disabled span { opacity: 0.45; cursor: not-allowed; }
    .page-item.gap span { border: none; background: transparent; min-width: 18px; padding: 0; }

    @media (max-width: 1180px) {
        .app-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 900px) {
        .filter-bar { grid-template-columns: 1fr 1fr; }
        .filter-bar .fld-search { grid-column: 1 / -1; }
    }
    @media (max-width: 640px) {
        .app-kpis { grid-template-columns: minmax(0, 1fr); }
        .filter-bar { grid-template-columns: minmax(0, 1fr); }
        .filter-bar .fld-actions .btn-primary-adm { flex: 1; }
        .records-actions { width: 100%; }
        .records-actions > * { flex: 1; justify-content: center; }
        .records-foot { justify-content: center; }
        .copy-btn { opacity: 1; }
    }
</style>

<!-- Tabs -->
<nav class="app-tabs" aria-label="Application type">
    <a href="?tab=payday" class="app-tab<?php echo $isPayday ? ' active' : ''; ?>"<?php echo $isPayday ? ' aria-current="page"' : ''; ?>>
        <i class="fa-solid fa-calendar-day"></i> Payday (salary) loans
        <span class="count"><?php echo applications_inr((float) $tabCounts['payday']); ?></span>
    </a>
    <a href="?tab=wizard" class="app-tab<?php echo !$isPayday ? ' active' : ''; ?>"<?php echo !$isPayday ? ' aria-current="page"' : ''; ?>>
        <i class="fa-solid fa-clock-rotate-left"></i> Legacy wizard (old)
        <span class="count"><?php echo applications_inr((float) $tabCounts['wizard']); ?></span>
    </a>
</nav>

<!-- KPIs (click to filter) -->
<div class="app-kpis">
    <?php foreach ($kpiCards as $card):
        $tag = $card['href'] ? 'a' : 'div';
        $isCurrent = false;
        if ($card['href']) {
            parse_str(ltrim($card['href'], '?'), $cardQuery);
            $isCurrent = $filtersActive && $cardQuery == array_filter(['tab' => $activeTab, 'search' => $search, 'status' => $status, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'amount_min' => $amountMin, 'amount_max' => $amountMax, 'loan_type' => $loanType], static fn($v) => $v !== '');
        }
    ?>
        <<?php echo $tag; ?> class="app-kpi <?php echo $card['tone']; ?><?php echo $isCurrent ? ' is-current' : ''; ?>"<?php echo $card['href'] ? ' href="' . applications_e($card['href']) . '"' : ''; ?>>
            <div style="min-width: 0;">
                <div class="app-kpi-label"><?php echo applications_e($card['label']); ?></div>
                <div class="app-kpi-value"><?php echo applications_e($card['value']); ?></div>
                <div class="app-kpi-sub"><?php echo applications_e($card['sub']); ?></div>
            </div>
            <span class="app-kpi-icon"><i class="fa-solid <?php echo $card['icon']; ?>"></i></span>
        </<?php echo $tag; ?>>
    <?php endforeach; ?>
</div>

<section class="records-card">
    <div class="records-head">
        <div>
            <div class="records-title"><?php echo $isPayday ? 'Payday applications' : 'Legacy wizard applications'; ?></div>
            <div class="records-meta" id="recordsMeta">
                <?php if ($totalRecords > 0): ?>
                    Showing <?php echo applications_inr((float) $rangeStart); ?>–<?php echo applications_inr((float) $rangeEnd); ?> of <span data-total><?php echo applications_inr((float) $totalRecords); ?></span><?php echo $filtersActive ? ' matching' : ''; ?> application<?php echo $totalRecords === 1 ? '' : 's'; ?>
                <?php else: ?>
                    No matching applications
                <?php endif; ?>
            </div>
        </div>
        <div class="records-actions">
            <button type="button" class="btn-ghost-adm" id="exportCsvBtn" title="Download the <?php echo $filtersActive ? 'filtered' : 'full'; ?> list as CSV"<?php echo $totalRecords === 0 ? ' disabled style="opacity:.5;cursor:not-allowed;"' : ''; ?>>
                <i class="fa-solid fa-file-arrow-down"></i> Export<?php echo $filtersActive ? ' filtered' : ''; ?> CSV
            </button>
            <?php if ($isPayday): ?>
                <a href="edit-application.php?type=payday&amp;page=<?php echo $page; ?>&amp;<?php echo applications_e(applications_build_query($baseQueryParams)); ?>" class="btn-primary-adm">
                    <i class="fa-solid fa-plus"></i> Add application
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($isPayday): ?>
        <!-- Status quick filters (counts respect the other active filters) -->
        <div class="status-chips" role="tablist" aria-label="Filter by status">
            <a role="tab" aria-selected="<?php echo $status === '' ? 'true' : 'false'; ?>" class="status-chip<?php echo $status === '' ? ' active' : ''; ?>" href="?<?php echo applications_e(applications_build_query($baseQueryParams, ['status' => '', 'page' => ''])); ?>">
                All <span class="num"><?php echo applications_inr((float) $statusChipTotal); ?></span>
            </a>
            <?php foreach ($statusLabels as $key => $label):
                $chipCount = $statusChipCounts[$key];
                $isActiveChip = $status === $key;
            ?>
                <a role="tab" aria-selected="<?php echo $isActiveChip ? 'true' : 'false'; ?>" class="status-chip<?php echo $isActiveChip ? ' active' : ''; ?><?php echo $chipCount === 0 && !$isActiveChip ? ' is-empty' : ''; ?>" href="?<?php echo applications_e(applications_build_query($baseQueryParams, ['status' => $key, 'page' => ''])); ?>">
                    <span class="dot dot-<?php echo $key; ?>"></span><?php echo $label; ?> <span class="num"><?php echo applications_inr((float) $chipCount); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <form method="GET" action="applications.php" class="filter-bar" id="applicationsFilterForm">
        <input type="hidden" name="tab" value="<?php echo applications_e($activeTab); ?>">
        <?php if ($status !== ''): ?><input type="hidden" name="status" value="<?php echo applications_e($status); ?>"><?php endif; ?>
        <?php if ($limit !== 10): ?><input type="hidden" name="per_page" value="<?php echo $limit; ?>"><?php endif; ?>

        <div class="fld fld-search">
            <label for="appSearch">Search</label>
            <div class="search-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" id="appSearch" name="search" placeholder="Name, mobile, email, PAN or <?php echo $isPayday ? 'application ID' : 'reference'; ?>" value="<?php echo applications_e($search); ?>" autocomplete="off">
            </div>
        </div>

        <?php if (!$isPayday): ?>
            <div class="fld">
                <label for="appLoanType">Loan type</label>
                <select name="loan_type" id="appLoanType" data-autosubmit>
                    <option value="">All loan types</option>
                    <?php foreach ($loanTypeOptions as $option): ?>
                        <option value="<?php echo applications_e($option); ?>" <?php echo $loanType === $option ? 'selected' : ''; ?>><?php echo applications_e($option); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="fld">
            <label for="dateFrom">Applied from</label>
            <input type="date" id="dateFrom" name="date_from" max="<?php echo $today; ?>" value="<?php echo applications_e($dateFrom); ?>">
        </div>
        <div class="fld">
            <label for="dateTo">Applied to</label>
            <input type="date" id="dateTo" name="date_to" max="<?php echo $today; ?>" value="<?php echo applications_e($dateTo); ?>">
        </div>
        <div class="fld">
            <label for="amountMin">Amount (₹)</label>
            <div class="range">
                <input type="number" id="amountMin" name="amount_min" min="0" step="1000" inputmode="numeric" placeholder="Min" aria-label="Minimum amount" value="<?php echo applications_e($amountMin); ?>">
                <span>–</span>
                <input type="number" id="amountMax" name="amount_max" min="0" step="1000" inputmode="numeric" placeholder="Max" aria-label="Maximum amount" value="<?php echo applications_e($amountMax); ?>">
            </div>
        </div>
        <div class="fld fld-actions">
            <button type="submit" class="btn-primary-adm"><i class="fa-solid fa-filter"></i> Apply</button>
        </div>
    </form>

    <?php if (!empty($activeFilterChips)): ?>
        <div class="active-filters">
            <span>Filtered by</span>
            <?php foreach ($activeFilterChips as $chip): ?>
                <a class="filter-pill" href="?<?php echo applications_e(applications_build_query($baseQueryParams, $chip['remove'] + ['page' => ''])); ?>" title="Remove this filter">
                    <?php echo applications_e($chip['label']); ?> <i class="fa-solid fa-xmark"></i>
                </a>
            <?php endforeach; ?>
            <a class="clear-all" href="?<?php echo applications_e(applications_build_query(['tab' => $activeTab, 'per_page' => $baseQueryParams['per_page']])); ?>">Clear all</a>
        </div>
    <?php endif; ?>

    <!-- Filtered CSV export (POST + CSRF, uses the filters currently applied) -->
    <form method="POST" action="api.php" id="applicationsExportForm" style="display:none;">
        <input type="hidden" name="action" value="export_applications">
        <input type="hidden" name="csrf_token" value="<?php echo applications_e($_SESSION['csrf_token'] ?? ''); ?>">
        <input type="hidden" name="tab" value="<?php echo applications_e($activeTab); ?>">
        <input type="hidden" name="search" value="<?php echo applications_e($search); ?>">
        <input type="hidden" name="status" value="<?php echo applications_e($status); ?>">
        <input type="hidden" name="date_from" value="<?php echo applications_e($dateFrom); ?>">
        <input type="hidden" name="date_to" value="<?php echo applications_e($dateTo); ?>">
        <input type="hidden" name="amount_min" value="<?php echo applications_e($amountMin); ?>">
        <input type="hidden" name="amount_max" value="<?php echo applications_e($amountMax); ?>">
        <input type="hidden" name="loan_type" value="<?php echo applications_e($loanType); ?>">
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Applicant</th>
                    <th><?php echo $isPayday ? 'Application ID' : 'Reference'; ?></th>
                    <th>Mobile</th>
                    <?php if ($isPayday): ?>
                        <th class="th-right">Amount</th>
                        <th>Status</th>
                    <?php else: ?>
                        <th>Loan type</th>
                        <th class="th-right">Amount</th>
                    <?php endif; ?>
                    <th>Applied</th>
                    <th class="th-right">Actions</th>
                </tr>
            </thead>
            <tbody id="applicationsTbody">
                <?php if (empty($apps)): ?>
                    <tr class="row-empty">
                        <td colspan="<?php echo $colCount; ?>" class="empty-block">
                            <i class="fa-solid fa-inbox big"></i>
                            <?php if ($filtersActive): ?>
                                <strong>No applications match these filters</strong>
                                Try widening the date range or clearing a filter.<br>
                                <a class="btn-ghost-adm" href="?tab=<?php echo $activeTab; ?>"><i class="fa-solid fa-rotate-left"></i> Clear filters</a>
                            <?php else: ?>
                                <strong>No applications yet</strong>
                                New applications will appear here as they come in.
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($apps as $app):
                        $rowId = (int) $app['id'];
                        $displayId = (string) ($isPayday ? ($app['application_id'] ?? '') : ($app['application_ref'] ?? ''));
                        $fullName = trim((string) ($app['full_name'] ?? ''));
                        $hasName = $fullName !== '' && strtoupper($fullName) !== 'N/A';
                        $email = trim((string) ($app['email'] ?? ''));
                        $statusValue = strtolower((string) ($app['status'] ?? ''));
                        $statusClass = preg_replace('/[^a-z0-9_-]/i', '', $statusValue);
                        $rawAmount = str_replace(',', '', (string) ($app['loan_amount'] ?? ''));
                        $displayAmount = is_numeric($rawAmount) ? '₹' . applications_inr((float) $rawAmount) : ($rawAmount !== '' ? applications_e($app['loan_amount']) : '—');
                        $createdTs = !empty($app['created_at']) ? strtotime((string) $app['created_at']) : false;
                        $detailQuery = applications_build_query($baseQueryParams, ['page' => $page]);
                        $viewUrl = 'view-application.php?id=' . $rowId . '&type=' . $activeTab . '&' . $detailQuery;
                        $editUrl = 'edit-application.php?id=' . $rowId . '&type=' . $activeTab . '&' . $detailQuery;
                    ?>
                        <tr id="app-row-<?php echo $rowId; ?>" data-href="<?php echo applications_e($viewUrl); ?>">
                            <td>
                                <?php if ($hasName): ?>
                                    <div class="cell-main"><?php echo applications_e($fullName); ?></div>
                                <?php else: ?>
                                    <div class="cell-main placeholder"><?php echo $statusValue === 'draft' ? 'Incomplete draft' : 'Name not provided'; ?></div>
                                <?php endif; ?>
                                <div class="cell-sub" title="<?php echo applications_e($email); ?>"><?php echo $email !== '' ? applications_e($email) : '—'; ?></div>
                            </td>
                            <td>
                                <span class="cell-id">
                                    <?php echo applications_e($displayId); ?>
                                    <?php if ($displayId !== ''): ?>
                                        <button type="button" class="copy-btn" data-copy="<?php echo applications_e($displayId); ?>" title="Copy ID" aria-label="Copy application ID"><i class="fa-regular fa-copy"></i></button>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td style="font-variant-numeric: tabular-nums;"><?php echo applications_e($app['mobile'] ?? '—'); ?></td>
                            <?php if ($isPayday): ?>
                                <td class="td-right cell-amount"><?php echo $displayAmount; ?></td>
                                <td>
                                    <span class="badge-status badge-<?php echo applications_e($statusClass); ?>" id="badge-<?php echo $rowId; ?>"><?php echo applications_e($statusLabels[$statusValue] ?? ($statusValue !== '' ? $statusValue : 'Unknown')); ?></span>
                                </td>
                            <?php else: ?>
                                <td><span class="type-chip"><?php echo applications_e(($app['loan_type'] ?? '') !== '' ? $app['loan_type'] : 'N/A'); ?></span></td>
                                <td class="td-right cell-amount"><?php echo $displayAmount; ?></td>
                            <?php endif; ?>
                            <td>
                                <?php if ($createdTs): ?>
                                    <div class="cell-date"><?php echo date('d M Y', $createdTs); ?></div>
                                    <div class="cell-time"><?php echo date('h:i A', $createdTs); ?></div>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="td-right">
                                <div class="row-actions">
                                    <a href="<?php echo applications_e($viewUrl); ?>" class="btn-action" title="View details" aria-label="View application"><i class="fa-solid fa-eye"></i></a>
                                    <a href="<?php echo applications_e($editUrl); ?>" class="btn-action" title="Edit" aria-label="Edit application"><i class="fa-solid fa-pen-to-square"></i></a>
                                    <button type="button" class="btn-action danger delete-btn" data-id="<?php echo $rowId; ?>" data-type="<?php echo $activeTab; ?>" data-label="<?php echo applications_e($hasName ? $fullName : $displayId); ?>" title="Delete" aria-label="Delete application"><i class="fa-solid fa-trash-can"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer: page size + pagination -->
    <?php if ($totalRecords > 0): ?>
        <div class="records-foot">
            <div class="per-page">
                <label for="perPageSelect">Rows per page</label>
                <select id="perPageSelect" aria-label="Rows per page">
                    <?php foreach ($perPageOptions as $opt): ?>
                        <option value="<?php echo $opt; ?>" <?php echo $limit === $opt ? 'selected' : ''; ?>><?php echo $opt; ?></option>
                    <?php endforeach; ?>
                </select>
                <span>Page <?php echo $page; ?> of <?php echo applications_inr((float) $totalPages); ?></span>
            </div>

            <?php if ($totalPages > 1): ?>
                <ul class="pagination" aria-label="Pagination">
                    <li class="page-item<?php echo $page <= 1 ? ' disabled' : ''; ?>">
                        <?php if ($page <= 1): ?>
                            <span aria-hidden="true"><i class="fa-solid fa-chevron-left" style="font-size:10px;"></i></span>
                        <?php else: ?>
                            <a class="page-link" href="?<?php echo applications_e(applications_build_query($baseQueryParams, ['page' => $page - 1])); ?>" aria-label="Previous page"><i class="fa-solid fa-chevron-left" style="font-size:10px;"></i></a>
                        <?php endif; ?>
                    </li>
                    <?php
                    $window = 1;
                    $pagesToShow = [];
                    if ($totalPages <= 7) {
                        $pagesToShow = range(1, $totalPages);
                    } else {
                        $pagesToShow[] = 1;
                        $start = max(2, $page - $window);
                        $end = min($totalPages - 1, $page + $window);
                        if ($page <= 3) { $end = 4; }
                        if ($page >= $totalPages - 2) { $start = $totalPages - 3; }
                        if ($start > 2) { $pagesToShow[] = '...'; }
                        for ($i = $start; $i <= $end; $i++) { $pagesToShow[] = $i; }
                        if ($end < $totalPages - 1) { $pagesToShow[] = '...'; }
                        $pagesToShow[] = $totalPages;
                    }
                    foreach ($pagesToShow as $p):
                        if ($p === '...'): ?>
                            <li class="page-item gap"><span>…</span></li>
                        <?php elseif ($p === $page): ?>
                            <li class="page-item active"><span aria-current="page"><?php echo $p; ?></span></li>
                        <?php else: ?>
                            <li class="page-item"><a class="page-link" href="?<?php echo applications_e(applications_build_query($baseQueryParams, ['page' => $p])); ?>"><?php echo $p; ?></a></li>
                        <?php endif;
                    endforeach; ?>
                    <li class="page-item<?php echo $page >= $totalPages ? ' disabled' : ''; ?>">
                        <?php if ($page >= $totalPages): ?>
                            <span aria-hidden="true"><i class="fa-solid fa-chevron-right" style="font-size:10px;"></i></span>
                        <?php else: ?>
                            <a class="page-link" href="?<?php echo applications_e(applications_build_query($baseQueryParams, ['page' => $page + 1])); ?>" aria-label="Next page"><i class="fa-solid fa-chevron-right" style="font-size:10px;"></i></a>
                        <?php endif; ?>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<script>
(function () {
    const csrfToken = <?php echo json_encode((string) ($_SESSION['csrf_token'] ?? '')); ?>;
    const tbody = document.getElementById('applicationsTbody');

    // Whole row opens the application (except when clicking a control or selecting text).
    if (tbody) {
        tbody.addEventListener('click', function (e) {
            const row = e.target.closest('tr[data-href]');
            if (!row || e.target.closest('a, button, input, select')) return;
            if (window.getSelection && String(window.getSelection()).length > 0) return;
            if (e.ctrlKey || e.metaKey) {
                window.open(row.dataset.href, '_blank');
            } else {
                window.location.href = row.dataset.href;
            }
        });
    }

    // Copy application ID
    document.querySelectorAll('.copy-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const text = btn.getAttribute('data-copy') || '';
            const done = function () {
                const icon = btn.querySelector('i');
                btn.classList.add('copied');
                if (icon) icon.className = 'fa-solid fa-check';
                setTimeout(function () {
                    btn.classList.remove('copied');
                    if (icon) icon.className = 'fa-regular fa-copy';
                }, 1200);
            };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(done).catch(function () {});
            } else {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                try { document.execCommand('copy'); done(); } catch (err) {}
                ta.remove();
            }
        });
    });

    // Loan type select applies immediately
    document.querySelectorAll('[data-autosubmit]').forEach(function (el) {
        el.addEventListener('change', function () {
            if (el.form) el.form.requestSubmit ? el.form.requestSubmit() : el.form.submit();
        });
    });

    // Rows per page
    const perPage = document.getElementById('perPageSelect');
    if (perPage) {
        perPage.addEventListener('change', function () {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', perPage.value);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });
    }

    // Filtered CSV export (submits the applied filters, not half-typed ones)
    const exportBtn = document.getElementById('exportCsvBtn');
    const exportForm = document.getElementById('applicationsExportForm');
    if (exportBtn && exportForm) {
        exportBtn.addEventListener('click', function () {
            if (exportBtn.disabled) return;
            exportForm.submit();
            const original = exportBtn.innerHTML;
            exportBtn.disabled = true;
            exportBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Preparing CSV…';
            setTimeout(function () {
                exportBtn.disabled = false;
                exportBtn.innerHTML = original;
            }, 2500);
        });
    }

    // Delete
    document.querySelectorAll('.delete-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const id = btn.getAttribute('data-id');
            const type = btn.getAttribute('data-type') || 'payday';
            const label = btn.getAttribute('data-label') || 'this application';
            Swal.fire({
                title: 'Delete application?',
                html: 'Delete <b></b>? It will be removed from this list. A copy is kept in the deleted-records archive.',
                icon: 'warning',
                showCancelButton: true,
                focusCancel: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#475569',
                confirmButtonText: 'Delete',
                didOpen: function (popup) {
                    const b = popup.querySelector('.swal2-html-container b');
                    if (b) b.textContent = label;
                }
            }).then(function (result) {
                if (!result.isConfirmed) return;
                Swal.fire({ title: 'Deleting…', allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });

                const fd = new FormData();
                fd.append('action', 'delete_application');
                fd.append('id', id);
                fd.append('type', type);
                fd.append('csrf_token', csrfToken);

                fetch('api.php', { method: 'POST', body: fd })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (data.status !== 'success') throw new Error(data.message || 'Failed to delete application.');
                        const row = document.getElementById('app-row-' + id);
                        if (row) row.remove();
                        Swal.fire({ icon: 'success', title: 'Deleted', text: data.message || 'Application deleted.', timer: 1400, showConfirmButton: false });
                        // Reload when the page is now empty so pagination and counts stay correct.
                        if (tbody && !tbody.querySelector('tr[data-href]')) {
                            setTimeout(function () { window.location.reload(); }, 1400);
                        }
                    })
                    .catch(function (err) {
                        Swal.fire({ icon: 'error', title: 'Delete failed', text: err.message || 'Unable to delete application.' });
                    });
            });
        });
    });
})();
</script>

<?php include 'footer.php'; ?>
