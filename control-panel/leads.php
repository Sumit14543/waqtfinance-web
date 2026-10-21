<?php
declare(strict_types=1);

$pageTitle = 'Inquiries & Quotes Manager';
$pageSubtitle = 'Quick quotes, contact inquiries, CIBIL checks, and DSA partners';

include 'header.php';
require_once __DIR__ . '/leads-functions.php';

$conn = db();
$tabs = admin_website_lead_tabs();
$activeTab = admin_website_sanitize_tab(trim((string) ($_GET['tab'] ?? 'landing')));
$limit = 10;
$page = max(1, (int) ($_GET['page'] ?? 1));
$filters = admin_website_clean_filters($activeTab, $_GET);
$filtersActive = admin_website_filters_active($filters);
$pageData = admin_website_fetch_page($conn, $activeTab, $filters, $page, $limit);
$records = admin_website_public_records($activeTab, $pageData['records']);
$totalRecords = $pageData['total_records'];
$totalPages = $pageData['total_pages'];
$page = $pageData['page'];
$kpiCards = admin_website_kpi_cards($conn);
$categoryOptions = admin_website_fetch_filter_options($conn, $activeTab, 'category');
$statusOptions = admin_website_fetch_filter_options($conn, $activeTab, 'status');
$search = $filters['search'];
$baseQueryParams = array_merge(['tab' => $activeTab], $filters);

function website_leads_build_query(array $baseParams, array $overrides = []): string
{
    $query = array_merge($baseParams, $overrides);
    foreach ($query as $key => $value) {
        if ($value === '' || $value === null) {
            unset($query[$key]);
        }
    }

    return http_build_query($query);
}
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
        flex-wrap: wrap;
    }
    .tab-btn {
        flex: 1;
        min-width: 150px;
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
    .lead-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin: -8px 0 28px;
    }
    .lead-kpi-card {
        display: flex;
        align-items: center;
        gap: 14px;
        min-height: 100px;
        padding: 18px;
        border: 1px solid #dbe4ef;
        border-radius: 8px;
        background: #ffffff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
    }
    .lead-kpi-icon {
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 19px;
    }
    .lead-kpi-card.kpi-blue .lead-kpi-icon {
        background: rgba(24, 82, 156, 0.08);
        color: var(--primary);
    }
    .lead-kpi-card.kpi-green .lead-kpi-icon {
        background: rgba(22, 163, 74, 0.09);
        color: #16a34a;
    }
    .lead-kpi-card.kpi-yellow .lead-kpi-icon {
        background: rgba(202, 138, 4, 0.11);
        color: #ca8a04;
    }
    .lead-kpi-card.kpi-red .lead-kpi-icon {
        background: rgba(239, 68, 68, 0.08);
        color: #dc2626;
    }
    .lead-kpi-info h3 {
        margin: 0;
        color: var(--text-main);
        font-size: 24px;
        line-height: 1.15;
        font-weight: 800;
    }
    .lead-kpi-info p {
        margin: 4px 0 0;
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.6px;
        text-transform: uppercase;
    }
    .lead-kpi-info span {
        display: block;
        margin-top: 4px;
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
    }
    .lead-filter-panel {
        display: grid;
        gap: 16px;
        padding: 18px;
        margin-bottom: 24px;
        border: 1px solid #dbe4ef;
        border-radius: 8px;
        background: #fbfdff;
    }
    .lead-filter-grid {
        display: grid;
        grid-template-columns: minmax(260px, 1.5fr) repeat(4, minmax(150px, 1fr));
        gap: 12px;
        align-items: end;
    }
    .lead-filter-field {
        display: grid;
        gap: 6px;
    }
    .lead-filter-field label {
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.6px;
        text-transform: uppercase;
    }
    .lead-filter-field input,
    .lead-filter-field select {
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
    .lead-filter-field input:focus,
    .lead-filter-field select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-glow);
    }
    .lead-filter-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .lead-filter-summary {
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 750;
    }
    .lead-filter-buttons {
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
    #websiteLeadResults,
    #websiteLeadKpiGrid {
        transition: opacity 0.18s ease, filter 0.18s ease;
    }
    .lead-ajax-loading {
        opacity: 0.58;
        filter: saturate(0.8);
        pointer-events: none;
    }
    .lead-inline-loader {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-height: 180px;
        color: var(--text-muted);
        font-weight: 800;
    }
    .lead-inline-loader::before {
        content: '';
        width: 18px;
        height: 18px;
        border: 3px solid #dbe4ef;
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
    .lead-empty-state {
        display: grid;
        justify-items: center;
        gap: 8px;
        padding: 42px 20px;
        color: var(--text-muted);
        text-align: center;
    }
    .lead-empty-state i {
        width: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: rgba(24, 82, 156, 0.08);
        color: var(--primary);
        font-size: 20px;
    }
    .lead-empty-state strong {
        color: var(--text-main);
        font-size: 14px;
    }
    @media (max-width: 1280px) {
        .lead-filter-grid {
            grid-template-columns: repeat(3, minmax(180px, 1fr));
        }
    }
    @media (max-width: 1180px) {
        .lead-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 720px) {
        .lead-kpi-grid,
        .lead-filter-grid {
            grid-template-columns: 1fr;
        }
        .lead-filter-actions {
            align-items: stretch;
        }
        .lead-filter-buttons,
        .lead-filter-buttons > * {
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
        background: rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(3px);
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
        max-width: 600px;
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

    /* Score indicator colors */
    .score-badge {
        padding: 4px 10px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 700;
        font-family: monospace;
    }
    .score-excellent {
        background: rgba(34, 197, 94, 0.1);
        color: #16a34a;
        border: 1px solid rgba(34, 197, 94, 0.2);
    }
    .score-good {
        background: rgba(59, 130, 246, 0.1);
        color: #2563eb;
        border: 1px solid rgba(59, 130, 246, 0.2);
    }
    .score-fair {
        background: rgba(245, 158, 11, 0.1);
        color: #ca8a04;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }
    .score-poor {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
    .score-none {
        background: rgba(156, 163, 175, 0.1);
        color: #64748b;
        border: 1px solid rgba(156, 163, 175, 0.2);
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

    /* Monospace ID badge */
    .app-id-code {
        font-family: monospace;
        font-size: 13px;
        font-weight: 700;
        color: var(--primary);
        background: rgba(24, 82, 156, 0.05);
        padding: 4px 8px;
        border-radius: 6px;
    }

    /* Action button group styling */
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
    .btn-action-approve {
        border-color: rgba(34, 197, 94, 0.2);
        color: #16a34a;
    }
    .btn-action-approve:hover {
        background: #16a34a;
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(34, 197, 94, 0.25);
    }
    .btn-action-reject {
        border-color: rgba(245, 158, 11, 0.2);
        color: #d97706;
    }
    .btn-action-reject:hover {
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
<div class="tab-nav" id="websiteLeadTabs">
    <?php foreach ($tabs as $tabKey => $tabMeta): ?>
        <a href="?tab=<?php echo urlencode($tabKey); ?>" data-lead-tab="<?php echo htmlspecialchars($tabKey, ENT_QUOTES, 'UTF-8'); ?>" class="tab-btn <?php echo $activeTab === $tabKey ? 'active' : ''; ?>">
            <i class="fa-solid <?php echo htmlspecialchars($tabMeta['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i> <?php echo htmlspecialchars($tabMeta['label'], ENT_QUOTES, 'UTF-8'); ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="lead-kpi-grid" id="websiteLeadKpiGrid">
    <?php foreach ($kpiCards as $card): ?>
        <div class="lead-kpi-card kpi-<?php echo htmlspecialchars($card['tone'], ENT_QUOTES, 'UTF-8'); ?>">
            <div class="lead-kpi-icon">
                <i class="fa-solid <?php echo htmlspecialchars($card['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
            </div>
            <div class="lead-kpi-info">
                <h3><?php echo htmlspecialchars($card['value'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <p><?php echo htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8'); ?></p>
                <span><?php echo htmlspecialchars($card['sub'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="data-card">
    <div class="card-header-flex">
        <h2 class="card-title-text" id="websiteLeadTitle">
            <i class="fa-solid <?php echo htmlspecialchars($tabs[$activeTab]['icon'], ENT_QUOTES, 'UTF-8'); ?>" style="color: var(--primary); margin-right: 8px;"></i>
            <?php echo htmlspecialchars($tabs[$activeTab]['title'], ENT_QUOTES, 'UTF-8'); ?>
        </h2>
    </div>

    <form method="GET" action="leads.php" id="websiteLeadFilterForm" class="lead-filter-panel">
        <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="lead-filter-grid" id="websiteLeadFilterGrid">
            <div class="lead-filter-field">
                <label for="lead-search">Search</label>
                <input id="lead-search" type="text" name="search"
                       placeholder="<?php
                           if ($activeTab === 'landing') echo 'Name, mobile, type, pincode...';
                           elseif ($activeTab === 'contact') echo 'Name, email, phone, subject...';
                           elseif ($activeTab === 'cibil') echo 'Name, email, mobile, PAN, score...';
                           else echo 'Name, email/mobile, status...';
                       ?>"
                       value="<?php echo htmlspecialchars($filters['search'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="lead-filter-field">
                <label for="lead-date-from">From Date</label>
                <input id="lead-date-from" type="date" name="date_from" value="<?php echo htmlspecialchars($filters['date_from'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="lead-filter-field">
                <label for="lead-date-to">To Date</label>
                <input id="lead-date-to" type="date" name="date_to" value="<?php echo htmlspecialchars($filters['date_to'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="lead-filter-field" id="lead-category-field" style="<?php echo $activeTab === 'landing' ? '' : 'display:none;'; ?>">
                <label for="lead-category">Loan Type</label>
                <select id="lead-category" name="category">
                    <option value="">All Loan Types</option>
                    <?php foreach ($categoryOptions as $option): ?>
                        <option value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $filters['category'] === $option ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(ucwords(str_replace(['_', '-'], ' ', $option)), ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="lead-filter-field" id="lead-status-field" style="<?php echo $activeTab === 'partners' ? '' : 'display:none;'; ?>">
                <label for="lead-status">Partner Status</label>
                <select id="lead-status" name="status">
                    <option value="">All Statuses</option>
                    <?php foreach ($statusOptions as $option): ?>
                        <option value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $filters['status'] === $option ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(ucwords(str_replace(['_', '-'], ' ', $option)), ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="lead-filter-field" id="lead-score-min-field" style="<?php echo $activeTab === 'cibil' ? '' : 'display:none;'; ?>">
                <label for="lead-score-min">Min Score</label>
                <input id="lead-score-min" type="number" name="score_min" min="0" max="900" placeholder="650" value="<?php echo htmlspecialchars($filters['score_min'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="lead-filter-field" id="lead-score-max-field" style="<?php echo $activeTab === 'cibil' ? '' : 'display:none;'; ?>">
                <label for="lead-score-max">Max Score</label>
                <input id="lead-score-max" type="number" name="score_max" min="0" max="900" placeholder="900" value="<?php echo htmlspecialchars($filters['score_max'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
        </div>

        <div class="lead-filter-actions">
            <div class="lead-filter-summary" id="websiteLeadSummary">
                <?php echo number_format($totalRecords); ?> matching <?php echo htmlspecialchars($tabs[$activeTab]['label'], ENT_QUOTES, 'UTF-8'); ?>
                <?php if ($filtersActive): ?>
                    <span style="color: var(--primary); margin-left: 8px;">Filters active</span>
                <?php endif; ?>
            </div>
            <div class="lead-filter-buttons">
                <button type="submit" class="btn-primary-adm" style="height:42px; box-shadow:none;">
                    <i class="fa-solid fa-filter"></i> Apply Filter
                </button>
                <a href="leads.php?tab=<?php echo urlencode($activeTab); ?>" class="btn-outline-adm" data-lead-reset title="Reset filters" style="<?php echo $filtersActive ? '' : 'display:none;'; ?>">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
                <a href="export-csv.php?type=<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>" class="btn-export-adm" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px; height: 42px;" target="_blank">
                    <i class="fa-solid fa-file-csv"></i> Export CSV
                </a>
            </div>
        </div>
    </form>

    <!-- Table content -->
    <div id="websiteLeadResults">
    <div class="table-responsive">
        <table class="admin-table">
            <?php if ($activeTab === 'landing'): ?>
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Lead Name</th>
                        <th>Mobile Number</th>
                        <th>Loan Category</th>
                        <th>Pincode</th>
                        <th>Received Date</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="lead-empty-state">
                                    <i class="fa-solid fa-list-check"></i>
                                    <strong>No quick quotes found</strong>
                                    <span>New quick form submissions will appear here.</span>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $r): ?>
                            <tr id="lead-row-landing-<?php echo $r['id']; ?>">
                                <td><span class="app-id-code">#<?php echo $r['id']; ?></span></td>
                                <td><strong style="color: var(--text-main);"><?php echo htmlspecialchars($r['full_name'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                <td><?php echo htmlspecialchars($r['mobile'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><span style="background: rgba(24, 82, 156, 0.08); padding: 4px 10px; border-radius: 6px; font-size: 11.5px; text-transform: uppercase; font-weight: 700; color: var(--primary);"><?php echo htmlspecialchars($r['loan_type'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><?php echo htmlspecialchars($r['pincode'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td style="color: var(--text-muted); font-size:12.5px;"><?php echo date('d M Y, h:i A', strtotime($r['created_at'])); ?></td>
                                <td>
                                    <div class="action-btn-group">
                                        <button type="button" class="btn-action-premium btn-action-view view-landing-btn" data-lead='<?php echo htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8'); ?>' title="View Lead Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn-action-premium btn-action-delete delete-lead-btn" data-id="<?php echo $r['id']; ?>" data-type="landing" title="Delete Quote">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>

            <?php elseif ($activeTab === 'contact'): ?>
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Sender Name</th>
                        <th>Email Address</th>
                        <th>Phone</th>
                        <th>Subject</th>
                        <th>Received Date</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="lead-empty-state">
                                    <i class="fa-solid fa-envelope-open-text"></i>
                                    <strong>No contact inquiries found</strong>
                                    <span>Website contact form messages will appear here.</span>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $r): ?>
                            <tr id="lead-row-contact-<?php echo $r['id']; ?>">
                                <td><span class="app-id-code">#<?php echo $r['id']; ?></span></td>
                                <td><strong style="color: var(--text-main);"><?php echo htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                <td><?php echo htmlspecialchars($r['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($r['phone'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td style="font-weight: 500;"><?php echo htmlspecialchars($r['subject'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td style="color: var(--text-muted); font-size:12.5px;"><?php echo date('d M Y, h:i A', strtotime($r['created_at'])); ?></td>
                                <td>
                                    <div class="action-btn-group">
                                        <button type="button" class="btn-action-premium btn-action-view view-contact-btn" data-contact='<?php echo htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8'); ?>' title="View Message">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <a class="btn-action-premium btn-action-approve" href="tel:<?php echo htmlspecialchars($r['phone'], ENT_QUOTES, 'UTF-8'); ?>" title="Call Sender">
                                            <i class="fa-solid fa-phone"></i>
                                        </a>
                                        <a class="btn-action-premium btn-action-view" href="mailto:<?php echo htmlspecialchars($r['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" title="Email Sender">
                                            <i class="fa-solid fa-envelope"></i>
                                        </a>
                                        <button type="button" class="btn-action-premium btn-action-delete delete-lead-btn" data-id="<?php echo $r['id']; ?>" data-type="contact" title="Delete Message">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>

            <?php elseif ($activeTab === 'cibil'): ?>
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Applicant Name</th>
                        <th>Email Address</th>
                        <th>Mobile Number</th>
                        <th>PAN Number</th>
                        <th>CIBIL Score</th>
                        <th>Date Checked</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="8">
                                <div class="lead-empty-state">
                                    <i class="fa-solid fa-gauge-high"></i>
                                    <strong>No CIBIL checks found</strong>
                                    <span>Credit score checks will appear here.</span>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $r): ?>
                            <tr id="lead-row-cibil-<?php echo $r['id']; ?>">
                                <td><span class="app-id-code">#<?php echo $r['id']; ?></span></td>
                                <td><strong style="color: var(--text-main);"><?php echo htmlspecialchars($r['full_name'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                <td><?php echo htmlspecialchars($r['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($r['mobile'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><span style="font-family: monospace; font-weight: 700; font-size: 13.5px;"><?php echo htmlspecialchars($r['pan'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td>
                                    <?php if ($r['score'] !== null): ?>
                                        <span class="score-badge <?php 
                                            if ($r['score'] >= 750) echo 'score-excellent';
                                            elseif ($r['score'] >= 700) echo 'score-good';
                                            elseif ($r['score'] >= 650) echo 'score-fair';
                                            else echo 'score-poor';
                                        ?>"><?php echo $r['score']; ?></span>
                                    <?php else: ?>
                                        <span class="score-badge score-none">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td style="color: var(--text-muted); font-size:12.5px;"><?php echo date('d M Y, h:i A', strtotime($r['created_at'])); ?></td>
                                <td>
                                    <div class="action-btn-group">
                                        <button type="button" class="btn-action-premium btn-action-view view-cibil-btn" data-cibil='<?php echo htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8'); ?>' title="View Score Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn-action-premium btn-action-delete delete-lead-btn" data-id="<?php echo $r['id']; ?>" data-type="cibil" title="Delete CIBIL Check">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>

            <?php elseif ($activeTab === 'partners'): ?>
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Partner Name</th>
                        <th>Email / Mobile</th>
                        <th>Verification Status</th>
                        <th>Registered Date</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="lead-empty-state">
                                    <i class="fa-solid fa-users"></i>
                                    <strong>No DSA partners found</strong>
                                    <span>Partner registrations will appear here.</span>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $r): ?>
                            <tr id="lead-row-partners-<?php echo $r['id']; ?>">
                                <td><span class="app-id-code">#<?php echo $r['id']; ?></span></td>
                                <td><strong style="color: var(--text-main);"><?php echo htmlspecialchars($r['full_name'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                <td><?php echo htmlspecialchars($r['email_or_mobile'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <span style="padding: 4px 10px; border-radius: 50px; font-size: 11.5px; font-weight: 700; text-transform: uppercase; 
                                        <?php if ($r['status'] === 'approved') echo 'background: rgba(34, 197, 94, 0.1); color: #16a34a; border: 1px solid rgba(34, 197, 94, 0.2);';
                                        elseif ($r['status'] === 'rejected') echo 'background: rgba(239, 68, 68, 0.1); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.2);';
                                        else echo 'background: rgba(245, 158, 11, 0.1); color: #ca8a04; border: 1px solid rgba(245, 158, 11, 0.2);'; ?>">
                                        <?php echo htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td style="color: var(--text-muted); font-size:12.5px;"><?php echo date('d M Y, h:i A', strtotime($r['created_at'])); ?></td>
                                <td>
                                    <div class="action-btn-group">
                                        <button type="button" class="btn-action-premium btn-action-view view-partner-btn" data-partner='<?php echo htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8'); ?>' title="View Partner Profile">
                                            <i class="fa-solid fa-user-gear"></i>
                                        </button>
                                        <?php if ($r['status'] !== 'approved'): ?>
                                            <button type="button" class="btn-action-premium btn-action-approve" onclick="updatePartnerStatus(<?php echo $r['id']; ?>, 'approved')" title="Approve Partner">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($r['status'] !== 'rejected'): ?>
                                            <button type="button" class="btn-action-premium btn-action-reject" onclick="updatePartnerStatus(<?php echo $r['id']; ?>, 'rejected')" title="Reject Partner">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn-action-premium btn-action-delete delete-lead-btn" data-id="<?php echo $r['id']; ?>" data-type="partners" title="Delete Partner">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            <?php endif; ?>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <ul class="pagination">
            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <?php if ($page <= 1): ?>
                    <span style="opacity:0.5; cursor:not-allowed;">&laquo; Prev</span>
                <?php else: ?>
                    <a href="?<?php echo htmlspecialchars(website_leads_build_query($baseQueryParams, ['page' => $page - 1]), ENT_QUOTES, 'UTF-8'); ?>">&laquo; Prev</a>
                <?php endif; ?>
            </li>
            
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <li class="page-item <?php echo $p === $page ? 'active' : ''; ?>">
                    <?php if ($p === $page): ?>
                        <span><?php echo $p; ?></span>
                    <?php else: ?>
                        <a href="?<?php echo htmlspecialchars(website_leads_build_query($baseQueryParams, ['page' => $p]), ENT_QUOTES, 'UTF-8'); ?>"><?php echo $p; ?></a>
                    <?php endif; ?>
                </li>
            <?php endfor; ?>
            
            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                <?php if ($page >= $totalPages): ?>
                    <span style="opacity:0.5; cursor:not-allowed;">Next &raquo;</span>
                <?php else: ?>
                    <a href="?<?php echo htmlspecialchars(website_leads_build_query($baseQueryParams, ['page' => $page + 1]), ENT_QUOTES, 'UTF-8'); ?>">Next &raquo;</a>
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
            <h2 class="card-title-text" id="drawerTitle">Inquiry Detail</h2>
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
    const csrfToken = <?php echo json_encode(csrf_token()); ?>;
    const leadTabsMeta = <?php echo json_encode($tabs, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    const initialLeadRecords = <?php echo json_encode($records, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    const leadFilterForm = document.getElementById('websiteLeadFilterForm');
    const leadResultsArea = document.getElementById('websiteLeadResults');
    const leadKpiGrid = document.getElementById('websiteLeadKpiGrid');
    const leadSummary = document.getElementById('websiteLeadSummary');
    const leadTabs = document.getElementById('websiteLeadTabs');
    const leadTitle = document.getElementById('websiteLeadTitle');
    let leadState = {
        tab: <?php echo json_encode($activeTab); ?>,
        page: <?php echo (int) $page; ?>,
        totalPages: <?php echo (int) $totalPages; ?>,
        loading: false
    };
    let leadRequestId = 0;
    let leadSearchTimer = null;
    const leadRecordCache = new Map();
    
    closeDrawer.addEventListener('click', () => drawer.classList.remove('active'));
    drawer.addEventListener('click', (e) => {
        if (e.target === drawer) drawer.classList.remove('active');
    });

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function displayValue(value, fallback = 'N/A') {
        const text = value === null || value === undefined || value === '' ? fallback : value;
        return escapeHtml(text);
    }

    function readableLabel(value) {
        return String(value || '')
            .replace(/[_-]+/g, ' ')
            .replace(/\b\w/g, char => char.toUpperCase());
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

    function cleanPhone(value) {
        return String(value || '').replace(/\D/g, '').replace(/^91(?=\d{10}$)/, '');
    }

    function cacheLeadRecords(tab, records) {
        (records || []).forEach(record => {
            leadRecordCache.set(`${tab}:${record.id}`, record);
        });
    }

    cacheLeadRecords(leadState.tab, initialLeadRecords);

    function quickActions(record) {
        const phone = cleanPhone(record.mobile || record.phone || '');
        const email = record.email || '';
        const actions = [];

        if (phone) {
            actions.push(`<a class="btn-outline-adm" style="height:36px; padding:0 12px;" href="tel:${phone}"><i class="fa-solid fa-phone"></i> Call</a>`);
            actions.push(`<a class="btn-outline-adm" style="height:36px; padding:0 12px;" href="https://wa.me/91${phone}" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>`);
        }

        if (email) {
            actions.push(`<a class="btn-outline-adm" style="height:36px; padding:0 12px;" href="mailto:${escapeHtml(email)}"><i class="fa-solid fa-envelope"></i> Email</a>`);
        }

        return actions.length ? `<div style="display:flex; gap:8px; flex-wrap:wrap; margin: 0 0 18px;">${actions.join('')}</div>` : '';
    }

    function renderScoreBadge(score) {
        if (score === null || score === undefined || score === '') {
            return '<span class="score-badge score-none">N/A</span>';
        }

        const numericScore = Number(score);
        let rating = 'Poor';
        let ratingClass = 'score-poor';
        if (numericScore >= 750) {
            rating = 'Excellent';
            ratingClass = 'score-excellent';
        } else if (numericScore >= 700) {
            rating = 'Good';
            ratingClass = 'score-good';
        } else if (numericScore >= 650) {
            rating = 'Fair';
            ratingClass = 'score-fair';
        }

        return `<span class="score-badge ${ratingClass}">${escapeHtml(score)} (${rating})</span>`;
    }

    function renderPartnerStatus(status) {
        const safeStatus = status || 'pending';
        let style = 'background: rgba(245, 158, 11, 0.1); color: #ca8a04; border: 1px solid rgba(245, 158, 11, 0.2);';
        if (safeStatus === 'approved') {
            style = 'background: rgba(34, 197, 94, 0.1); color: #16a34a; border: 1px solid rgba(34, 197, 94, 0.2);';
        } else if (safeStatus === 'rejected') {
            style = 'background: rgba(239, 68, 68, 0.1); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.2);';
        }

        return `<span style="padding: 4px 10px; border-radius: 50px; font-size: 11.5px; font-weight: 700; text-transform: uppercase; ${style}">${escapeHtml(safeStatus)}</span>`;
    }

    function emptyRow(tab, colspan) {
        const meta = leadTabsMeta[tab] || leadTabsMeta.landing;
        return `
            <tr>
                <td colspan="${colspan}">
                    <div class="lead-empty-state">
                        <i class="fa-solid ${escapeHtml(meta.icon || 'fa-inbox')}"></i>
                        <strong>No ${escapeHtml(meta.label || 'records')} found</strong>
                        <span>${escapeHtml(meta.empty || 'Records will appear here.')}</span>
                    </div>
                </td>
            </tr>
        `;
    }

    function renderLandingRows(records) {
        if (!records.length) return emptyRow('landing', 7);
        return records.map(lead => `
            <tr id="lead-row-landing-${escapeHtml(lead.id)}">
                <td><span class="app-id-code">#${escapeHtml(lead.id)}</span></td>
                <td><strong style="color: var(--text-main);">${displayValue(lead.full_name)}</strong></td>
                <td>${displayValue(lead.mobile)}</td>
                <td><span style="background: rgba(24, 82, 156, 0.08); padding: 4px 10px; border-radius: 6px; font-size: 11.5px; text-transform: uppercase; font-weight: 700; color: var(--primary);">${displayValue(lead.loan_type)}</span></td>
                <td>${displayValue(lead.pincode)}</td>
                <td style="color: var(--text-muted); font-size:12.5px;">${formatDateTime(lead.created_at)}</td>
                <td>
                    <div class="action-btn-group">
                        <button type="button" class="btn-action-premium btn-action-view js-lead-view" data-type="landing" data-id="${escapeHtml(lead.id)}" title="View Lead Details"><i class="fa-solid fa-eye"></i></button>
                        <a class="btn-action-premium btn-action-approve" href="tel:${cleanPhone(lead.mobile)}" title="Call Lead"><i class="fa-solid fa-phone"></i></a>
                        <a class="btn-action-premium btn-action-view" href="https://wa.me/91${cleanPhone(lead.mobile)}" target="_blank" rel="noopener" title="WhatsApp Lead"><i class="fa-brands fa-whatsapp"></i></a>
                        <button type="button" class="btn-action-premium btn-action-delete js-lead-delete" data-type="landing" data-id="${escapeHtml(lead.id)}" title="Delete Quote"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function renderContactRows(records) {
        if (!records.length) return emptyRow('contact', 7);
        return records.map(contact => `
            <tr id="lead-row-contact-${escapeHtml(contact.id)}">
                <td><span class="app-id-code">#${escapeHtml(contact.id)}</span></td>
                <td><strong style="color: var(--text-main);">${displayValue(contact.name)}</strong></td>
                <td>${displayValue(contact.email)}</td>
                <td>${displayValue(contact.phone)}</td>
                <td style="font-weight: 500;">${displayValue(contact.subject)}</td>
                <td style="color: var(--text-muted); font-size:12.5px;">${formatDateTime(contact.created_at)}</td>
                <td>
                    <div class="action-btn-group">
                        <button type="button" class="btn-action-premium btn-action-view js-lead-view" data-type="contact" data-id="${escapeHtml(contact.id)}" title="View Message"><i class="fa-solid fa-eye"></i></button>
                        <a class="btn-action-premium btn-action-approve" href="tel:${cleanPhone(contact.phone)}" title="Call Sender"><i class="fa-solid fa-phone"></i></a>
                        <a class="btn-action-premium btn-action-view" href="mailto:${escapeHtml(contact.email || '')}" title="Email Sender"><i class="fa-solid fa-envelope"></i></a>
                        <button type="button" class="btn-action-premium btn-action-delete js-lead-delete" data-type="contact" data-id="${escapeHtml(contact.id)}" title="Delete Message"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function renderCibilRows(records) {
        if (!records.length) return emptyRow('cibil', 8);
        return records.map(cibil => `
            <tr id="lead-row-cibil-${escapeHtml(cibil.id)}">
                <td><span class="app-id-code">#${escapeHtml(cibil.id)}</span></td>
                <td><strong style="color: var(--text-main);">${displayValue(cibil.full_name)}</strong></td>
                <td>${displayValue(cibil.email)}</td>
                <td>${displayValue(cibil.mobile)}</td>
                <td><span style="font-family: monospace; font-weight: 700; font-size: 13.5px;">${displayValue(cibil.pan)}</span></td>
                <td>${renderScoreBadge(cibil.score)}</td>
                <td style="color: var(--text-muted); font-size:12.5px;">${formatDateTime(cibil.created_at)}</td>
                <td>
                    <div class="action-btn-group">
                        <button type="button" class="btn-action-premium btn-action-view js-lead-view" data-type="cibil" data-id="${escapeHtml(cibil.id)}" title="View Score Details"><i class="fa-solid fa-eye"></i></button>
                        <a class="btn-action-premium btn-action-approve" href="tel:${cleanPhone(cibil.mobile)}" title="Call Applicant"><i class="fa-solid fa-phone"></i></a>
                        <button type="button" class="btn-action-premium btn-action-delete js-lead-delete" data-type="cibil" data-id="${escapeHtml(cibil.id)}" title="Delete CIBIL Check"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function renderPartnerRows(records) {
        if (!records.length) return emptyRow('partners', 6);
        return records.map(partner => `
            <tr id="lead-row-partners-${escapeHtml(partner.id)}">
                <td><span class="app-id-code">#${escapeHtml(partner.id)}</span></td>
                <td><strong style="color: var(--text-main);">${displayValue(partner.full_name)}</strong></td>
                <td>${displayValue(partner.email_or_mobile)}</td>
                <td>${renderPartnerStatus(partner.status)}</td>
                <td style="color: var(--text-muted); font-size:12.5px;">${formatDateTime(partner.created_at)}</td>
                <td>
                    <div class="action-btn-group">
                        <button type="button" class="btn-action-premium btn-action-view js-lead-view" data-type="partners" data-id="${escapeHtml(partner.id)}" title="View Partner Profile"><i class="fa-solid fa-user-gear"></i></button>
                        ${partner.status !== 'approved' ? `<button type="button" class="btn-action-premium btn-action-approve js-partner-status" data-id="${escapeHtml(partner.id)}" data-status="approved" title="Approve Partner"><i class="fa-solid fa-check"></i></button>` : ''}
                        ${partner.status !== 'rejected' ? `<button type="button" class="btn-action-premium btn-action-reject js-partner-status" data-id="${escapeHtml(partner.id)}" data-status="rejected" title="Reject Partner"><i class="fa-solid fa-xmark"></i></button>` : ''}
                        <button type="button" class="btn-action-premium btn-action-delete js-lead-delete" data-type="partners" data-id="${escapeHtml(partner.id)}" title="Delete Partner"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');
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
                    ${p === current ? `<span>${p}</span>` : `<a href="#" data-lead-page="${p}">${p}</a>`}
                </li>
            `);
            last = p;
        });

        return `
            <ul class="pagination">
                <li class="page-item ${current <= 1 ? 'disabled' : ''}">
                    ${current <= 1 ? '<span style="opacity:0.5; cursor:not-allowed;">&laquo; Prev</span>' : `<a href="#" data-lead-page="${current - 1}">&laquo; Prev</a>`}
                </li>
                ${pageItems.join('')}
                <li class="page-item ${current >= total ? 'disabled' : ''}">
                    ${current >= total ? '<span style="opacity:0.5; cursor:not-allowed;">Next &raquo;</span>' : `<a href="#" data-lead-page="${current + 1}">Next &raquo;</a>`}
                </li>
            </ul>
        `;
    }

    function renderLeadResults(data) {
        const tab = data.tab;
        cacheLeadRecords(tab, data.records || []);
        const tableHeads = {
            landing: '<tr><th style="width: 80px;">ID</th><th>Lead Name</th><th>Mobile Number</th><th>Loan Category</th><th>Pincode</th><th>Received Date</th><th style="width: 150px;">Actions</th></tr>',
            contact: '<tr><th style="width: 80px;">ID</th><th>Sender Name</th><th>Email Address</th><th>Phone</th><th>Subject</th><th>Received Date</th><th style="width: 150px;">Actions</th></tr>',
            cibil: '<tr><th style="width: 80px;">ID</th><th>Applicant Name</th><th>Email Address</th><th>Mobile Number</th><th>PAN Number</th><th>CIBIL Score</th><th>Date Checked</th><th style="width: 130px;">Actions</th></tr>',
            partners: '<tr><th style="width: 80px;">ID</th><th>Partner Name</th><th>Email / Mobile</th><th>Verification Status</th><th>Registered Date</th><th style="width: 150px;">Actions</th></tr>'
        };
        const rowRenderers = {
            landing: renderLandingRows,
            contact: renderContactRows,
            cibil: renderCibilRows,
            partners: renderPartnerRows
        };

        leadResultsArea.innerHTML = `
            <div style="position: relative;">
                <!-- Suspense Loader Overlay -->
                <div class="table-loading-overlay" id="leadTableLoadingOverlay">
                    <div class="suspense-spinner">
                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                        <span>Loading Leads...</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="admin-table">
                    <thead>${tableHeads[tab]}</thead>
                    <tbody>${rowRenderers[tab](data.records || [])}</tbody>
                </table>
            </div>
            ${renderPagination(data)}
        </div>
        `;
    }

    function renderKpiCards(cards) {
        if (!leadKpiGrid) return;
        leadKpiGrid.innerHTML = (cards || []).map(card => `
            <div class="lead-kpi-card kpi-${escapeHtml(card.tone)}">
                <div class="lead-kpi-icon"><i class="fa-solid ${escapeHtml(card.icon)}"></i></div>
                <div class="lead-kpi-info">
                    <h3>${escapeHtml(card.value)}</h3>
                    <p>${escapeHtml(card.label)}</p>
                    <span>${escapeHtml(card.sub)}</span>
                </div>
            </div>
        `).join('');
    }

    function syncLeadFilters(data) {
        const tabInput = leadFilterForm.querySelector('[name="tab"]');
        if (tabInput) tabInput.value = data.tab;

        const meta = leadTabsMeta[data.tab] || leadTabsMeta.landing;
        if (leadTitle) {
            leadTitle.innerHTML = `<i class="fa-solid ${escapeHtml(meta.icon)}" style="color: var(--primary); margin-right: 8px;"></i>${escapeHtml(meta.title)}`;
        }

        const searchInput = document.getElementById('lead-search');
        if (searchInput) {
            const placeholders = {
                landing: 'Name, mobile, type, pincode...',
                contact: 'Name, email, phone, subject...',
                cibil: 'Name, email, mobile, PAN, score...',
                partners: 'Name, email/mobile, status...'
            };
            searchInput.placeholder = placeholders[data.tab] || placeholders.landing;
            searchInput.value = data.filters.search || '';
        }

        ['date_from', 'date_to', 'score_min', 'score_max'].forEach(name => {
            const field = leadFilterForm.querySelector(`[name="${name}"]`);
            if (field) field.value = data.filters[name] || '';
        });

        const categoryField = document.getElementById('lead-category-field');
        const categorySelect = document.getElementById('lead-category');
        if (categoryField && categorySelect) {
            categoryField.style.display = data.tab === 'landing' ? '' : 'none';
            categorySelect.innerHTML = '<option value="">All Loan Types</option>' + (data.category_options || []).map(option => `
                <option value="${escapeHtml(option)}" ${data.filters.category === option ? 'selected' : ''}>${escapeHtml(readableLabel(option))}</option>
            `).join('');
        }

        const statusField = document.getElementById('lead-status-field');
        const statusSelect = document.getElementById('lead-status');
        if (statusField && statusSelect) {
            statusField.style.display = data.tab === 'partners' ? '' : 'none';
            statusSelect.innerHTML = '<option value="">All Statuses</option>' + (data.status_options || []).map(option => `
                <option value="${escapeHtml(option)}" ${data.filters.status === option ? 'selected' : ''}>${escapeHtml(readableLabel(option))}</option>
            `).join('');
        }

        document.getElementById('lead-score-min-field').style.display = data.tab === 'cibil' ? '' : 'none';
        document.getElementById('lead-score-max-field').style.display = data.tab === 'cibil' ? '' : 'none';

        leadTabs?.querySelectorAll('[data-lead-tab]').forEach(tabButton => {
            tabButton.classList.toggle('active', tabButton.getAttribute('data-lead-tab') === data.tab);
        });

        if (leadSummary) {
            const label = meta.label || 'records';
            leadSummary.innerHTML = `
                ${Number(data.total_records || 0).toLocaleString('en-IN')} matching ${escapeHtml(label)}
                ${data.filters_active ? '<span style="color: var(--primary); margin-left: 8px;">Filters active</span>' : ''}
            `;
        }

        const resetButton = leadFilterForm.querySelector('[data-lead-reset]');
        if (resetButton) {
            resetButton.style.display = data.filters_active ? 'inline-flex' : 'none';
            resetButton.setAttribute('href', `leads.php?tab=${encodeURIComponent(data.tab)}`);
        }
    }

    function updateLeadUrl(data) {
        const params = new URLSearchParams();
        params.set('tab', data.tab);
        Object.entries(data.filters || {}).forEach(([key, value]) => {
            if (value !== '') params.set(key, value);
        });
        if (data.page > 1) params.set('page', data.page);
        history.replaceState(null, '', `leads.php?${params.toString()}`);
    }

    function applyLeadResponse(data, updateUrl = true) {
        leadState.tab = data.tab;
        leadState.page = data.page;
        leadState.totalPages = data.total_pages;
        renderKpiCards(data.kpi_cards || []);
        syncLeadFilters(data);
        renderLeadResults(data);
        if (updateUrl) updateLeadUrl(data);
    }

    function setLeadLoading(isLoading) {
        leadState.loading = isLoading;
        leadResultsArea?.classList.toggle('lead-ajax-loading', isLoading);
        leadKpiGrid?.classList.toggle('lead-ajax-loading', isLoading);
        
        const loader = document.getElementById('leadTableLoadingOverlay');
        if (loader) {
            if (isLoading) loader.classList.add('active');
            else loader.classList.remove('active');
        }
    }

    function getLeadPayload(page = leadState.page) {
        const formData = new FormData(leadFilterForm);
        formData.set('action', 'list_website_leads');
        formData.set('csrf_token', csrfToken);
        formData.set('tab', leadState.tab);
        formData.set('page', String(page));
        formData.set('limit', '10');
        return formData;
    }

    function refreshWebsiteLeads(options = {}) {
        if (!leadFilterForm) return;
        const requestId = ++leadRequestId;
        const page = options.page || leadState.page || 1;
        const updateUrl = options.updateUrl !== false;
        setLeadLoading(true);

        if (!options.silent && leadResultsArea && !leadResultsArea.querySelector('table')) {
            leadResultsArea.innerHTML = '<div class="lead-inline-loader">Loading leads...</div>';
        }

        fetch('api.php', {
            method: 'POST',
            body: getLeadPayload(page)
        })
        .then(res => res.json())
        .then(data => {
            if (requestId !== leadRequestId) return;
            if (data.status !== 'success') {
                throw new Error(data.message || 'Unable to load leads.');
            }
            applyLeadResponse(data, updateUrl);
        })
        .catch(err => {
            if (requestId !== leadRequestId) return;
            leadResultsArea.innerHTML = `
                <div style="padding:28px; text-align:center; color:var(--danger); font-weight:800;">
                    <i class="fa-solid fa-triangle-exclamation"></i> ${escapeHtml(err.message || 'Unable to load leads.')}
                </div>
            `;
        })
        .finally(() => {
            if (requestId === leadRequestId) setLeadLoading(false);
        });
    }

    function openLeadDrawer(type, record) {
        if (!record) return;

        if (type === 'landing') {
            drawerTitle.innerText = 'Landing Lead Details';
            drawerBody.innerHTML = `
                ${quickActions(record)}
                <div style="background: rgba(24,82,156,0.05); padding:16px; border-radius:12px; border:1px dashed var(--primary); margin-bottom: 20px;">
                    <div class="detail-label">Lead Reference ID</div>
                    <div class="detail-val" style="color:var(--primary); font-size:16px; font-weight:700;">#${displayValue(record.id)}</div>
                </div>
                <div class="section-title">Lead Parameters</div>
                <div class="detail-grid">
                    <div class="detail-item"><div class="detail-label">Full Name</div><div class="detail-val">${displayValue(record.full_name)}</div></div>
                    <div class="detail-item"><div class="detail-label">Mobile Number</div><div class="detail-val">${displayValue(record.mobile)}</div></div>
                    <div class="detail-item"><div class="detail-label">Loan Type</div><div class="detail-val" style="text-transform:capitalize;">${displayValue(record.loan_type)}</div></div>
                    <div class="detail-item"><div class="detail-label">Pincode</div><div class="detail-val">${displayValue(record.pincode)}</div></div>
                    <div class="detail-item"><div class="detail-label">Created At</div><div class="detail-val">${formatDateTime(record.created_at)}</div></div>
                </div>
            `;
        } else if (type === 'contact') {
            drawerTitle.innerText = 'Support Message Details';
            drawerBody.innerHTML = `
                ${quickActions(record)}
                <div style="background: rgba(24,82,156,0.05); padding:16px; border-radius:12px; border:1px dashed var(--primary); margin-bottom: 20px;">
                    <div class="detail-label">Message Subject</div>
                    <div class="detail-val" style="color:var(--text-main); font-size:16px; font-weight:700;">${displayValue(record.subject)}</div>
                </div>
                <div class="section-title">Sender Information</div>
                <div class="detail-grid">
                    <div class="detail-item"><div class="detail-label">Name</div><div class="detail-val">${displayValue(record.name)}</div></div>
                    <div class="detail-item"><div class="detail-label">Email Address</div><div class="detail-val">${displayValue(record.email)}</div></div>
                    <div class="detail-item"><div class="detail-label">Phone Number</div><div class="detail-val">${displayValue(record.phone)}</div></div>
                    <div class="detail-item"><div class="detail-label">Received At</div><div class="detail-val">${formatDateTime(record.created_at)}</div></div>
                </div>
                <div class="section-title">Message Body</div>
                <div style="background: #f8fafc; padding: 18px; border-radius: 10px; border: 1px solid var(--border-color); line-height: 1.6; color: var(--text-main); margin-bottom: 20px; font-size:14px; white-space: pre-wrap;">${displayValue(record.message, 'No message')}</div>
                <div class="section-title">Metadata Logs</div>
                <div class="detail-grid">
                    <div class="detail-item"><div class="detail-label">IP Address</div><div class="detail-val" style="font-family:monospace;">${displayValue(record.ip_address)}</div></div>
                    <div class="detail-item"><div class="detail-label">Consent Given</div><div class="detail-val">${String(record.consent) === '1' ? 'Yes' : 'No'}</div></div>
                </div>
                <div style="margin-top:15px;">
                    <div class="detail-label">User Agent</div>
                    <div class="detail-val" style="font-size:11.5px; line-height:1.4; color:var(--text-muted); background:#f8fafc; border:1px solid var(--border-color); padding:8px; border-radius:6px; font-family:monospace; margin-top:4px;">${displayValue(record.user_agent)}</div>
                </div>
            `;
        } else if (type === 'cibil') {
            drawerTitle.innerText = 'CIBIL Score Check Details';
            drawerBody.innerHTML = `
                ${quickActions(record)}
                <div style="background: rgba(24,82,156,0.05); padding:16px; border-radius:12px; border:1px dashed var(--primary); margin-bottom: 20px;">
                    <div class="detail-label">Bureau Reference ID</div>
                    <div class="detail-val" style="color:var(--primary); font-size:16px; font-weight:700;">${displayValue(record.ref_id)}</div>
                </div>
                <div class="section-title">Client Information</div>
                <div class="detail-grid">
                    <div class="detail-item"><div class="detail-label">Full Name</div><div class="detail-val">${displayValue(record.full_name)}</div></div>
                    <div class="detail-item"><div class="detail-label">Email Address</div><div class="detail-val">${displayValue(record.email)}</div></div>
                    <div class="detail-item"><div class="detail-label">Mobile Number</div><div class="detail-val">${displayValue(record.mobile)}</div></div>
                    <div class="detail-item"><div class="detail-label">PAN Number</div><div class="detail-val" style="font-family:monospace;">${displayValue(record.pan)}</div></div>
                    <div class="detail-item"><div class="detail-label">CIBIL Score</div><div class="detail-val">${renderScoreBadge(record.score)}</div></div>
                    <div class="detail-item"><div class="detail-label">Checked At</div><div class="detail-val">${formatDateTime(record.created_at)}</div></div>
                </div>
                ${record.pdf_url ? `
                    <div class="section-title">Report Files</div>
                    <div style="margin-top: 10px;">
                        <a href="${escapeHtml(record.pdf_url)}" target="_blank" class="btn-primary-adm" style="text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                            <i class="fa-solid fa-file-pdf"></i> Download Official Bureau Report (PDF)
                        </a>
                    </div>
                ` : ''}
            `;
        } else {
            drawerTitle.innerText = 'DSA Partner Details';
            drawerBody.innerHTML = `
                <div style="background: rgba(24,82,156,0.05); padding:16px; border-radius:12px; border:1px dashed var(--primary); margin-bottom: 20px;">
                    <div class="detail-label">Partner ID</div>
                    <div class="detail-val" style="color:var(--primary); font-size:16px; font-weight:700;">#${displayValue(record.id)}</div>
                </div>
                <div class="section-title">Partner Profile</div>
                <div class="detail-grid">
                    <div class="detail-item"><div class="detail-label">Full Name</div><div class="detail-val">${displayValue(record.full_name)}</div></div>
                    <div class="detail-item"><div class="detail-label">Email or Mobile</div><div class="detail-val">${displayValue(record.email_or_mobile)}</div></div>
                    <div class="detail-item"><div class="detail-label">Status</div><div class="detail-val">${renderPartnerStatus(record.status)}</div></div>
                    <div class="detail-item"><div class="detail-label">Registered At</div><div class="detail-val">${formatDateTime(record.created_at)}</div></div>
                </div>
            `;
        }

        drawer.classList.add('active');
    }

    // AJAX delete handler
    function deleteLeadRecord(id, type) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'This record will be permanently deleted!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deleting record...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                const formData = new FormData();
                formData.append('action', 'delete_application');
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
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'The record has been deleted.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            drawer.classList.remove('active');
                            refreshWebsiteLeads({ silent: true, updateUrl: false });
                        });
                    } else {
                        throw new Error(data.message || 'Failed to delete record.');
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

    // AJAX partner status update handler
    function updatePartnerStatus(id, status) {
        Swal.fire({
            title: 'Update Partner Status?',
            text: `Are you sure you want to mark this partner as ${status.toUpperCase()}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: status === 'approved' ? '#16a34a' : '#d97706',
            cancelButtonColor: '#475569',
            confirmButtonText: `Yes, ${status}`
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Updating status...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                const formData = new FormData();
                formData.append('action', 'update_partner_status');
                formData.append('id', id);
                formData.append('status', status);
                formData.append('csrf_token', csrfToken);

                fetch('api.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Status Updated!',
                            text: `Partner status set to ${status}.`,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            refreshWebsiteLeads({ silent: true, updateUrl: false });
                        });
                    } else {
                        throw new Error(data.message || 'Failed to update status.');
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
        });
    }

    function parseRecordAttribute(button, name) {
        try {
            return JSON.parse(button.getAttribute(name) || '{}');
        } catch (error) {
            return {};
        }
    }

    function recordFromButton(button, type) {
        const id = button.getAttribute('data-id');
        if (id && leadRecordCache.has(`${type}:${id}`)) {
            return leadRecordCache.get(`${type}:${id}`);
        }

        if (type === 'landing') return parseRecordAttribute(button, 'data-lead');
        if (type === 'contact') return parseRecordAttribute(button, 'data-contact');
        if (type === 'cibil') return parseRecordAttribute(button, 'data-cibil');
        return parseRecordAttribute(button, 'data-partner');
    }

    function resetLeadFilters() {
        ['search', 'date_from', 'date_to', 'category', 'status', 'score_min', 'score_max'].forEach(name => {
            const field = leadFilterForm.querySelector(`[name="${name}"]`);
            if (field) field.value = '';
        });
    }

    function scheduleLeadRefresh(delay = 350) {
        clearTimeout(leadSearchTimer);
        leadSearchTimer = setTimeout(() => {
            leadState.page = 1;
            refreshWebsiteLeads({ page: 1 });
        }, delay);
    }

    function exportWebsiteLeadsCsv() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'api.php';

        const appendHidden = (name, value) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            form.appendChild(input);
        };

        appendHidden('action', 'export_website_leads');
        appendHidden('csrf_token', csrfToken);
        appendHidden('tab', leadState.tab);
        ['search', 'date_from', 'date_to', 'category', 'status', 'score_min', 'score_max'].forEach(name => {
            const field = leadFilterForm.querySelector(`[name="${name}"]`);
            appendHidden(name, field ? field.value : '');
        });

        document.body.appendChild(form);
        form.submit();
        form.remove();
    }

    leadFilterForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        leadState.page = 1;
        refreshWebsiteLeads({ page: 1 });
    });

    leadFilterForm?.addEventListener('input', (event) => {
        if (event.target.matches('[name="search"], [name="score_min"], [name="score_max"]')) {
            scheduleLeadRefresh();
        }
    });

    leadFilterForm?.addEventListener('change', (event) => {
        if (event.target.matches('select, input[type="date"]')) {
            leadState.page = 1;
            refreshWebsiteLeads({ page: 1 });
        }
    });

    leadFilterForm?.addEventListener('click', (event) => {
        const resetButton = event.target.closest('[data-lead-reset]');
        if (!resetButton) return;
        event.preventDefault();
        resetLeadFilters();
        leadState.page = 1;
        refreshWebsiteLeads({ page: 1 });
    });

    leadTabs?.addEventListener('click', (event) => {
        const tabButton = event.target.closest('[data-lead-tab]');
        if (!tabButton) return;
        event.preventDefault();

        const nextTab = tabButton.getAttribute('data-lead-tab') || 'landing';
        if (nextTab === leadState.tab) return;
        leadState.tab = nextTab;
        leadState.page = 1;

        const tabInput = leadFilterForm.querySelector('[name="tab"]');
        if (tabInput) tabInput.value = nextTab;
        ['category', 'status', 'score_min', 'score_max'].forEach(name => {
            const field = leadFilterForm.querySelector(`[name="${name}"]`);
            if (field) field.value = '';
        });

        leadTabs.querySelectorAll('[data-lead-tab]').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-lead-tab') === nextTab);
        });
        refreshWebsiteLeads({ page: 1 });
    });

    leadResultsArea?.addEventListener('click', (event) => {
        const pageLink = event.target.closest('[data-lead-page], .pagination a[href]');
        if (pageLink && leadResultsArea.contains(pageLink)) {
            event.preventDefault();
            let nextPage = pageLink.getAttribute('data-lead-page');
            if (!nextPage) {
                const url = new URL(pageLink.href, window.location.href);
                nextPage = url.searchParams.get('page') || '1';
            }
            leadState.page = Math.max(1, parseInt(nextPage, 10) || 1);
            refreshWebsiteLeads({ page: leadState.page });
            return;
        }

        const statusButton = event.target.closest('.js-partner-status');
        if (statusButton && leadResultsArea.contains(statusButton)) {
            event.preventDefault();
            updatePartnerStatus(statusButton.getAttribute('data-id'), statusButton.getAttribute('data-status'));
            return;
        }

        const deleteButton = event.target.closest('.js-lead-delete, .delete-lead-btn');
        if (deleteButton && leadResultsArea.contains(deleteButton)) {
            event.preventDefault();
            deleteLeadRecord(deleteButton.getAttribute('data-id'), deleteButton.getAttribute('data-type'));
            return;
        }

        const viewButton = event.target.closest('.js-lead-view, .view-landing-btn, .view-contact-btn, .view-cibil-btn, .view-partner-btn');
        if (!viewButton || !leadResultsArea.contains(viewButton)) return;
        event.preventDefault();

        let type = viewButton.getAttribute('data-type');
        if (!type) {
            if (viewButton.classList.contains('view-landing-btn')) type = 'landing';
            else if (viewButton.classList.contains('view-contact-btn')) type = 'contact';
            else if (viewButton.classList.contains('view-cibil-btn')) type = 'cibil';
            else type = 'partners';
        }

        openLeadDrawer(type, recordFromButton(viewButton, type));
    });
</script>

<?php include 'footer.php'; ?>
