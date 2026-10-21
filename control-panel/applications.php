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

// Pagination Config
$limit = 10;
$page = (int) ($_GET['page'] ?? 1);
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Filters
$search = trim((string) ($_GET['search'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));
$dateFrom = trim((string) ($_GET['date_from'] ?? ''));
$dateTo = trim((string) ($_GET['date_to'] ?? ''));
$amountMin = trim((string) ($_GET['amount_min'] ?? ''));
$amountMax = trim((string) ($_GET['amount_max'] ?? ''));
$loanType = trim((string) ($_GET['loan_type'] ?? ''));

$validPaydayStatuses = ['draft', 'submitted', 'review', 'approved', 'rejected', 'disbursed'];
if ($activeTab !== 'payday' || !in_array($status, $validPaydayStatuses, true)) {
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

if ($activeTab !== 'wizard') {
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

function applications_format_money(float $amount): string
{
    if ($amount >= 10000000) {
        return '₹' . number_format($amount / 10000000, 2) . ' Cr';
    }

    if ($amount >= 100000) {
        return '₹' . number_format($amount / 100000, 2) . ' L';
    }

    return '₹' . number_format($amount, 0);
}

$filtersActive = $search !== '' || $status !== '' || $dateFrom !== '' || $dateTo !== '' || $amountMin !== '' || $amountMax !== '' || $loanType !== '';

// Build queries based on tab
$totalRecords = 0;
$apps = [];

$kpiCards = [];
if ($activeTab === 'payday') {
    $statsRes = $conn->query("
        SELECT
            COUNT(*) AS total_applications,
            SUM(CASE WHEN created_at >= CURDATE() AND created_at < DATE_ADD(CURDATE(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) AS today_applications,
            SUM(CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND created_at < CURDATE() THEN 1 ELSE 0 END) AS yesterday_applications,
            SUM(CASE WHEN status IN ('submitted', 'review') THEN 1 ELSE 0 END) AS processing_applications,
            SUM(CASE WHEN status IN ('approved', 'disbursed') THEN 1 ELSE 0 END) AS successful_applications
        FROM loan_applications
    ");
    $stats = $statsRes ? ($statsRes->fetch_assoc() ?: []) : [];

    $kpiCards = [
        ['label' => 'Total Applications', 'value' => number_format((int)($stats['total_applications'] ?? 0)), 'sub' => 'All payday leads', 'icon' => 'fa-layer-group', 'tone' => 'blue'],
        ['label' => "Today's Applications", 'value' => number_format((int)($stats['today_applications'] ?? 0)), 'sub' => 'Yesterday: ' . number_format((int)($stats['yesterday_applications'] ?? 0)), 'icon' => 'fa-calendar-day', 'tone' => 'green'],
        ['label' => 'Submitted / Review', 'value' => number_format((int)($stats['processing_applications'] ?? 0)), 'sub' => 'Needs follow-up', 'icon' => 'fa-clipboard-list', 'tone' => 'yellow'],
        ['label' => 'Approved / Disbursed', 'value' => number_format((int)($stats['successful_applications'] ?? 0)), 'sub' => 'Positive pipeline', 'icon' => 'fa-circle-check', 'tone' => 'red'],
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
        ['label' => 'Total Applications', 'value' => number_format((int)($stats['total_applications'] ?? 0)), 'sub' => 'All legacy wizard leads', 'icon' => 'fa-layer-group', 'tone' => 'blue'],
        ['label' => "Today's Applications", 'value' => number_format((int)($stats['today_applications'] ?? 0)), 'sub' => 'Yesterday: ' . number_format((int)($stats['yesterday_applications'] ?? 0)), 'icon' => 'fa-calendar-day', 'tone' => 'green'],
        ['label' => 'This Month', 'value' => number_format((int)($stats['month_applications'] ?? 0)), 'sub' => date('M Y'), 'icon' => 'fa-chart-line', 'tone' => 'yellow'],
        ['label' => 'Loan Value', 'value' => applications_format_money((float)($stats['total_loan_value'] ?? 0)), 'sub' => 'Requested amount', 'icon' => 'fa-indian-rupee-sign', 'tone' => 'red'],
    ];
}

$conditions = [];
$params = [];
$types = '';
$amountExpression = $activeTab === 'payday' ? 'loan_amount' : "CAST(REPLACE(loan_amount, ',', '') AS DECIMAL(12,2))";

if ($dateFrom !== '') {
    $conditions[] = "created_at >= ?";
    $params[] = $dateFrom . ' 00:00:00';
    $types .= 's';
}

if ($dateTo !== '') {
    $conditions[] = "created_at <= ?";
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

if ($activeTab === 'payday') {
    if ($search !== '') {
        $conditions[] = "(application_id LIKE ? OR full_name LIKE ? OR mobile LIKE ? OR email LIKE ? OR pan_number LIKE ?)";
        $searchWild = "%$search%";
        $params[] = $searchWild;
        $params[] = $searchWild;
        $params[] = $searchWild;
        $params[] = $searchWild;
        $params[] = $searchWild;
        $types .= 'sssss';
    }

    if ($status !== '') {
        $conditions[] = "status = ?";
        $params[] = $status;
        $types .= 's';
    }

    $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
    
    // Count Total
    $countSql = "SELECT COUNT(*) FROM loan_applications $whereClause";
    $countStmt = $conn->prepare($countSql);
    if (!empty($params)) {
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $totalRecords = (int) ($countStmt->get_result()->fetch_row()[0] ?? 0);
    
    // Fetch Applications
    $fetchSql = "SELECT * FROM loan_applications $whereClause ORDER BY id DESC LIMIT ? OFFSET ?";
    $fetchStmt = $conn->prepare($fetchSql);
    $fetchParams = $params;
    $fetchTypes = $types . 'ii';
    $fetchParams[] = $limit;
    $fetchParams[] = $offset;
    $fetchStmt->bind_param($fetchTypes, ...$fetchParams);
    $fetchStmt->execute();
    $apps = $fetchStmt->get_result()->fetch_all(MYSQLI_ASSOC);

} else {
    // Wizard applications (from apply_loan_applications)
    if ($search !== '') {
        $conditions[] = "(application_ref LIKE ? OR full_name LIKE ? OR mobile LIKE ? OR email LIKE ? OR pan_number LIKE ?)";
        $searchWild = "%$search%";
        $params[] = $searchWild;
        $params[] = $searchWild;
        $params[] = $searchWild;
        $params[] = $searchWild;
        $params[] = $searchWild;
        $types .= 'sssss';
    }

    if ($loanType !== '') {
        $conditions[] = "loan_type = ?";
        $params[] = $loanType;
        $types .= 's';
    }

    $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
    
    // Count Total
    $countSql = "SELECT COUNT(*) FROM apply_loan_applications $whereClause";
    $countStmt = $conn->prepare($countSql);
    if (!empty($params)) {
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $totalRecords = (int) ($countStmt->get_result()->fetch_row()[0] ?? 0);
    
    // Fetch Applications
    $fetchSql = "SELECT * FROM apply_loan_applications $whereClause ORDER BY id DESC LIMIT ? OFFSET ?";
    $fetchStmt = $conn->prepare($fetchSql);
    $fetchParams = $params;
    $fetchTypes = $types . 'ii';
    $fetchParams[] = $limit;
    $fetchParams[] = $offset;
    $fetchStmt->bind_param($fetchTypes, ...$fetchParams);
    $fetchStmt->execute();
    $apps = $fetchStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$loanTypeOptions = [];
if ($activeTab === 'wizard') {
    $loanTypeRes = $conn->query("SELECT DISTINCT loan_type FROM apply_loan_applications WHERE loan_type IS NOT NULL AND loan_type != '' ORDER BY loan_type ASC");
    if ($loanTypeRes) {
        while ($row = $loanTypeRes->fetch_assoc()) {
            $loanTypeOptions[] = (string) $row['loan_type'];
        }
    }
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
        background: var(--primary) !important;
        box-shadow: 0 4px 10px rgba(24, 82, 156, 0.2);
    }
    .applications-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin: -10px 0 28px;
    }
    .applications-kpi-card {
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
    .applications-kpi-icon {
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 20px;
    }
    .applications-kpi-card.kpi-blue .applications-kpi-icon {
        background: rgba(24, 82, 156, 0.08);
        color: var(--primary);
    }
    .applications-kpi-card.kpi-green .applications-kpi-icon {
        background: rgba(22, 163, 74, 0.09);
        color: #16a34a;
    }
    .applications-kpi-card.kpi-yellow .applications-kpi-icon {
        background: rgba(202, 138, 4, 0.11);
        color: #ca8a04;
    }
    .applications-kpi-card.kpi-red .applications-kpi-icon {
        background: rgba(239, 68, 68, 0.08);
        color: #dc2626;
    }
    .applications-kpi-info h3 {
        margin: 0;
        color: var(--text-main);
        font-size: 24px;
        line-height: 1.15;
        font-weight: 800;
    }
    .applications-kpi-info p {
        margin: 4px 0 0;
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.6px;
        text-transform: uppercase;
    }
    .applications-kpi-info span {
        display: block;
        margin-top: 4px;
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
    }
    @media (max-width: 1180px) {
        .applications-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 640px) {
        .applications-kpi-grid {
            grid-template-columns: 1fr;
        }
    }
    .applications-filter-panel {
        display: grid;
        gap: 16px;
        padding: 18px;
        margin-bottom: 22px;
        border: 1px solid #dbe4ef;
        border-radius: 8px;
        background: #fbfdff;
    }
    .applications-filter-grid {
        display: grid;
        grid-template-columns: minmax(260px, 1.4fr) repeat(5, minmax(140px, 1fr));
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
    .filter-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .filter-action-buttons {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .filter-summary {
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 700;
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
    @media (max-width: 1200px) {
        .applications-filter-grid {
            grid-template-columns: repeat(3, minmax(180px, 1fr));
        }
    }
    @media (max-width: 720px) {
        .applications-filter-grid {
            grid-template-columns: 1fr;
        }
        .filter-actions {
            align-items: stretch;
        }
        .filter-action-buttons,
        .filter-action-buttons > * {
            width: 100%;
        }
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

    /* Loading Overlay Spinner */
    .page-loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(4px);
        z-index: 10000;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s ease;
    }
    .page-loading-overlay.active {
        opacity: 1;
        pointer-events: auto;
    }
    .loading-spinner {
        width: 48px;
        height: 48px;
        border: 4px solid rgba(255, 255, 255, 0.2);
        border-left-color: #ffffff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 15px;
    }
    .loading-text {
        color: #ffffff;
        font-family: 'Outfit', sans-serif;
        font-size: 16px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>

<!-- Tab Navigation -->
<div class="tab-nav">
    <a href="?tab=payday" class="tab-btn <?php echo $activeTab === 'payday' ? 'active' : ''; ?>">
        <i class="fa-solid fa-calendar-day"></i> Payday (Salary) Loans
    </a>
    <a href="?tab=wizard" class="tab-btn <?php echo $activeTab === 'wizard' ? 'active' : ''; ?>">
        <i class="fa-solid fa-history"></i> Legacy Wizard Applications (Old)
    </a>
</div>

<div class="applications-kpi-grid">
    <?php foreach ($kpiCards as $card): ?>
        <div class="applications-kpi-card kpi-<?php echo htmlspecialchars($card['tone'], ENT_QUOTES, 'UTF-8'); ?>">
            <div class="applications-kpi-icon">
                <i class="fa-solid <?php echo htmlspecialchars($card['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
            </div>
            <div class="applications-kpi-info">
                <h3><?php echo htmlspecialchars($card['value'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <p><?php echo htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8'); ?></p>
                <span><?php echo htmlspecialchars($card['sub'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="data-card">
    <div class="card-header-flex">
        <h2 class="card-title-text">
            <i class="fa-solid fa-folder-open" style="color: var(--primary); margin-right: 8px;"></i> 
            <?php echo $activeTab === 'payday' ? 'Payday (Salary) Loan Lead Records' : 'Legacy Wizard (Old) Lead Records'; ?>
        </h2>
        <?php if ($activeTab === 'payday'): ?>
            <a href="edit-application.php?type=payday&page=<?php echo $page; ?>&<?php echo applications_build_query($baseQueryParams); ?>" class="btn-primary-adm" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-plus"></i> Add Application
            </a>
        <?php endif; ?>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="applications.php" class="applications-filter-panel filter-row" id="applicationsFilterForm">
        <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>">

        <div class="applications-filter-grid">
            <div class="filter-field">
                <label for="appSearch">Search</label>
                <input type="text" id="appSearch" name="search" placeholder="Name, email, phone, PAN, ID..." value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <?php if ($activeTab === 'payday'): ?>
                <div class="filter-field">
                    <label for="appStatus">Status</label>
                    <select name="status" id="appStatus">
                        <option value="">All Statuses</option>
                        <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="submitted" <?php echo $status === 'submitted' ? 'selected' : ''; ?>>Submitted</option>
                        <option value="review" <?php echo $status === 'review' ? 'selected' : ''; ?>>Review</option>
                        <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        <option value="disbursed" <?php echo $status === 'disbursed' ? 'selected' : ''; ?>>Disbursed</option>
                    </select>
                </div>
            <?php else: ?>
                <div class="filter-field">
                    <label for="appLoanType">Loan Type</label>
                    <select name="loan_type" id="appLoanType">
                        <option value="">All Loan Types</option>
                        <?php foreach ($loanTypeOptions as $option): ?>
                            <option value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $loanType === $option ? 'selected' : ''; ?>><?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="filter-field">
                <label for="dateFrom">From Date</label>
                <input type="date" id="dateFrom" name="date_from" value="<?php echo htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="filter-field">
                <label for="dateTo">To Date</label>
                <input type="date" id="dateTo" name="date_to" value="<?php echo htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="filter-field">
                <label for="amountMin">Min Amount</label>
                <input type="number" id="amountMin" name="amount_min" min="0" step="1000" placeholder="0" value="<?php echo htmlspecialchars($amountMin, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="filter-field">
                <label for="amountMax">Max Amount</label>
                <input type="number" id="amountMax" name="amount_max" min="0" step="1000" placeholder="100000" value="<?php echo htmlspecialchars($amountMax, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
        </div>

        <div class="filter-actions">
            <span class="filter-summary"><?php echo number_format($totalRecords); ?> matching application<?php echo $totalRecords === 1 ? '' : 's'; ?></span>
            <div class="filter-action-buttons">
                <button type="submit" class="btn-primary-adm"><i class="fa-solid fa-filter"></i> Apply Filter</button>
                <a href="export-csv.php?type=<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>" class="btn-export-adm" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;" target="_blank">
                    <i class="fa-solid fa-file-csv"></i> Export CSV
                </a>
                <?php if ($filtersActive): ?>
                    <a href="applications.php?tab=<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>" class="btn-outline-adm" title="Reset filters">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <form method="POST" action="api.php" id="applicationsExportForm" style="display:none;">
        <input type="hidden" name="action" value="export_applications">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="status" value="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="date_from" value="<?php echo htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="date_to" value="<?php echo htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="amount_min" value="<?php echo htmlspecialchars($amountMin, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="amount_max" value="<?php echo htmlspecialchars($amountMax, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="loan_type" value="<?php echo htmlspecialchars($loanType, ENT_QUOTES, 'UTF-8'); ?>">
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>App ID</th>
                    <th>Full Name</th>
                    <th>Mobile</th>
                    <th>Email</th>
                    <th>Loan Type</th>
                    <th>Amount</th>
                    <th>Applied Date</th>
                    <?php if ($activeTab === 'payday'): ?>
                        <th>Status</th>
                    <?php endif; ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($apps)): ?>
                    <tr>
                        <td colspan="<?php echo $activeTab === 'payday' ? 9 : 8; ?>" class="text-center" style="color: var(--text-muted); padding: 40px 0;">No applications matching these criteria.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($apps as $app): ?>
                        <?php 
                            $displayId = $activeTab === 'payday' ? $app['application_id'] : $app['application_ref']; 
                            $loanTypeLabel = $activeTab === 'payday' ? 'Payday' : ($app['loan_type'] ?? 'N/A');
                            $statusValue = (string) ($app['status'] ?? '');
                            $statusClass = preg_replace('/[^a-z0-9_-]/i', '', $statusValue);
                            // Format loan amount correctly (apply_loan_applications store it as text or decimal)
                            $displayAmount = is_numeric($app['loan_amount']) ? number_format((float)$app['loan_amount'], 2) : htmlspecialchars($app['loan_amount'], ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr id="app-row-<?php echo (int) $app['id']; ?>">
                            <td><strong style="color: var(--text-main); font-weight: 700;"><?php echo htmlspecialchars($displayId, ENT_QUOTES, 'UTF-8'); ?></strong></td>
                            <td><?php echo htmlspecialchars($app['full_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($app['mobile'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($app['email'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><span style="background: rgba(24, 82, 156, 0.08); padding: 3px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; color: var(--primary); white-space: nowrap;"><?php echo htmlspecialchars($loanTypeLabel, ENT_QUOTES, 'UTF-8'); ?></span></td>
                            <td>&#8377;<?php echo $displayAmount; ?></td>
                            <td><?php echo date('d M Y, h:i A', strtotime($app['created_at'])); ?></td>
                            <?php if ($activeTab === 'payday'): ?>
                                <td><span class="badge-status badge-<?php echo htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8'); ?>" id="badge-<?php echo (int) $app['id']; ?>"><?php echo htmlspecialchars($statusValue, ENT_QUOTES, 'UTF-8'); ?></span></td>
                            <?php endif; ?>
                            <td style="white-space: nowrap;">
                                <a href="view-application.php?id=<?php echo (int) $app['id']; ?>&type=<?php echo $activeTab; ?>&page=<?php echo $page; ?>&<?php echo applications_build_query($baseQueryParams); ?>" class="btn-action" title="View Details" style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; text-decoration: none;">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="edit-application.php?id=<?php echo (int) $app['id']; ?>&type=<?php echo $activeTab; ?>&page=<?php echo $page; ?>&<?php echo applications_build_query($baseQueryParams); ?>" class="btn-action" title="Edit Details" style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; text-decoration: none; margin-left: 4px;">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <button type="button" class="btn-action delete-btn" data-id="<?php echo (int) $app['id']; ?>" data-type="<?php echo $activeTab; ?>" title="Delete Application" style="border-color: rgba(239, 68, 68, 0.2); color: #fca5a5; margin-left: 4px; display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: transparent; cursor: pointer; border-radius: 6px; border: 1px solid rgba(239, 68, 68, 0.2);">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <ul class="pagination">
            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <?php if ($page <= 1): ?>
                    <span style="opacity:0.5; cursor:not-allowed;">&laquo; Prev</span>
                <?php else: ?>
                    <a href="?<?php echo applications_build_query($baseQueryParams, ['page' => $page - 1]); ?>">&laquo; Prev</a>
                <?php endif; ?>
            </li>
            
            <?php
            $window = 2; // Sliding window range around active page
            $pagesToShow = [];
            if ($totalPages <= 7) {
                for ($i = 1; $i <= $totalPages; $i++) {
                    $pagesToShow[] = $i;
                }
            } else {
                $pagesToShow[] = 1;
                $start = max(2, $page - $window);
                $end = min($totalPages - 1, $page + $window);
                
                if ($start > 2) {
                    $pagesToShow[] = '...';
                }
                
                for ($i = $start; $i <= $end; $i++) {
                    $pagesToShow[] = $i;
                }
                
                if ($end < $totalPages - 1) {
                    $pagesToShow[] = '...';
                }
                
                $pagesToShow[] = $totalPages;
            }

            foreach ($pagesToShow as $p):
                if ($p === '...'):
            ?>
                    <li class="page-item disabled">
                        <span style="border: none; background: transparent; cursor: default;">...</span>
                    </li>
                <?php else: ?>
                    <li class="page-item <?php echo $p === $page ? 'active' : ''; ?>">
                        <?php if ($p === $page): ?>
                            <span><?php echo $p; ?></span>
                        <?php else: ?>
                            <a href="?<?php echo applications_build_query($baseQueryParams, ['page' => $p]); ?>"><?php echo $p; ?></a>
                        <?php endif; ?>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
            
            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                <?php if ($page >= $totalPages): ?>
                    <span style="opacity:0.5; cursor:not-allowed;">Next &raquo;</span>
                <?php else: ?>
                    <a href="?<?php echo applications_build_query($baseQueryParams, ['page' => $page + 1]); ?>">Next &raquo;</a>
                <?php endif; ?>
            </li>
        </ul>
    <?php endif; ?>
</div>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Delete Application Click Handler
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const type = btn.getAttribute('data-type') || 'payday';
            Swal.fire({
                title: 'Delete application?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#475569',
                confirmButtonText: 'Yes, delete it'
            }).then((result) => {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Deleting application...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                const fd = new FormData();
                fd.append('action', 'delete_application');
                fd.append('id', id);
                fd.append('type', type);
                fd.append('csrf_token', '<?php echo $_SESSION['csrf_token'] ?? ''; ?>');

                fetch('api.php', {
                    method: 'POST',
                    body: fd
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        const row = document.getElementById(`app-row-${id}`);
                        if (row) row.remove();
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted',
                            text: 'Application deleted successfully.',
                            timer: 1300,
                            showConfirmButton: false
                        });
                    } else {
                        throw new Error(data.message || "Failed to delete application.");
                    }
                })
                .catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Delete failed',
                        text: err.message || 'Unable to delete application.'
                    });
                });
            });
        });
    });

// Local loading overlay replaced by global footer handler

    const exportForm = document.getElementById('applicationsExportForm');
    function syncExportFilters() {
        if (!filterForm || !exportForm) return;

        ['search', 'status', 'date_from', 'date_to', 'amount_min', 'amount_max', 'loan_type'].forEach(name => {
            const source = filterForm.querySelector(`[name="${name}"]`);
            const target = exportForm.querySelector(`[name="${name}"]`);
            if (source && target) {
                target.value = source.value;
            }
        });
    }

    if (filterForm && exportForm) {
        filterForm.addEventListener('input', syncExportFilters);
        filterForm.addEventListener('change', syncExportFilters);
        exportForm.addEventListener('submit', () => {
            syncExportFilters();
            loadingText.innerText = "Preparing Excel Download...";
            loadingOverlay.classList.add('active');
            setTimeout(() => loadingOverlay.classList.remove('active'), 1800);
        });
    }
</script>

<?php include 'footer.php'; ?>
