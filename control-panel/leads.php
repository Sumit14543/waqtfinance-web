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
<?php
$tabCategoryLabels = [
    'landing' => ['label' => 'Loan type', 'all' => 'All loan types'],
    'contact' => ['label' => 'Subject', 'all' => 'All subjects'],
];
$searchPlaceholders = [
    'landing' => 'Name, mobile, loan type, pincode…',
    'contact' => 'Name, email, phone, subject, message…',
    'cibil' => 'Name, email, mobile, PAN, score, ref ID…',
    'partners' => 'Name, email / mobile, status…',
];
$dateFilterActive = $filters['date_from'] !== '' || $filters['date_to'] !== '';
$initialLeadData = [
    'tab' => $activeTab,
    'page' => $page,
    'limit' => $limit,
    'total_records' => $totalRecords,
    'total_pages' => $totalPages,
    'filters_active' => $filtersActive,
    'filters' => $filters,
    'records' => $records,
    'kpi_cards' => $kpiCards,
];
?>

<style>
    /* Elements toggled with the hidden attribute must stay hidden even when a class sets display. */
    .lt-card [hidden], .lt-tabs [hidden] { display: none !important; }

    /* ---------- Tabs ---------- */
    .lt-tabs {
        display: flex;
        gap: 4px;
        border-bottom: 1px solid var(--border-color);
        margin: -6px 0 22px;
        overflow-x: auto;
        scrollbar-width: none;
    }
    .lt-tabs::-webkit-scrollbar { display: none; }
    .lt-tab {
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
        transition: color 0.15s ease;
    }
    .lt-tab:hover { color: var(--text-main); }
    .lt-tab.active { color: var(--primary); }
    .lt-tab.active::after {
        content: '';
        position: absolute;
        left: 8px;
        right: 8px;
        bottom: -1px;
        height: 2px;
        border-radius: 2px 2px 0 0;
        background: var(--primary);
    }
    .lt-tab .count {
        font-size: 11.5px;
        font-weight: 700;
        padding: 1px 8px;
        border-radius: 999px;
        background: #eef1f6;
        color: var(--text-secondary);
        font-variant-numeric: tabular-nums;
    }
    .lt-tab.active .count { background: var(--primary-soft); color: var(--primary); }
    .lt-tab .count.attn { background: #fef3c7; color: #b45309; }

    /* ---------- KPI ---------- */
    .lt-kpis { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-bottom: 22px; }
    .lt-kpi {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 18px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-xs);
        min-width: 0;
    }
    .lt-kpi-label { font-size: 12.5px; font-weight: 600; color: var(--text-muted); }
    .lt-kpi-value { font-family: 'Outfit', sans-serif; font-size: 26px; font-weight: 700; line-height: 1.2; margin-top: 2px; font-variant-numeric: tabular-nums; }
    .lt-kpi-sub { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
    .lt-kpi-icon {
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
    .lt-kpi.attn .lt-kpi-icon { background: #fffbeb; color: #b45309; }
    #websiteLeadKpiGrid, #websiteLeadResults { transition: opacity 0.18s ease; }
    .lead-ajax-loading { opacity: 0.6; pointer-events: none; }

    /* ---------- Records card + toolbar ---------- */
    .lt-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-xs);
        min-width: 0;
    }
    .lt-toolbar-wrap { padding: 14px 20px; border-bottom: 1px solid var(--border-color); background: var(--bg-subtle); border-radius: var(--radius-lg) var(--radius-lg) 0 0; }
    .lt-toolbar { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .lt-field { position: relative; min-width: 0; }
    .lt-field.sr label, .lt-adv label.sr {
        position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap;
    }
    .lt-field.search { flex: 1 1 260px; }
    .lt-field.search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 12.5px; pointer-events: none; }
    .lt-field.search input { padding-left: 34px; }
    .lt-field.select { flex: 0 1 190px; }
    .lt-score { display: flex; align-items: center; gap: 6px; }
    .lt-score span { font-size: 12.5px; color: var(--text-muted); white-space: nowrap; }
    .lt-score input { width: 76px; }
    .lt-card input, .lt-card select {
        width: 100%;
        height: 38px;
        padding: 0 11px;
        border: 1px solid var(--border-strong);
        border-radius: var(--radius-sm);
        background: #ffffff;
        color: var(--text-main);
        font-size: 13px;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .lt-card select { cursor: pointer; }
    .lt-card input:focus, .lt-card select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-glow); }
    .lt-card .has-value { border-color: #93b4dc; background: #f5f9ff; }
    .lt-btn {
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 14px;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border-strong);
        background: #ffffff;
        color: var(--text-main);
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
        transition: border-color 0.15s ease, color 0.15s ease, background-color 0.15s ease;
    }
    .lt-btn:hover, .lt-btn[aria-expanded="true"] { border-color: var(--primary); color: var(--primary); background: var(--primary-soft); }
    .lt-btn.export { color: #15803d; }
    .lt-btn.export:hover { border-color: #16a34a; background: #f0fdf4; color: #15803d; }
    .lt-btn .badge-n { min-width: 18px; height: 18px; padding: 0 5px; border-radius: 999px; background: var(--primary); color: #fff; font-size: 11px; line-height: 18px; text-align: center; }
    .lt-btn .badge-n[hidden] { display: none; }
    .lt-actions { display: flex; gap: 8px; margin-left: auto; }
    .lt-adv { display: flex; align-items: flex-end; gap: 12px 20px; flex-wrap: wrap; margin-top: 12px; padding-top: 12px; border-top: 1px dashed var(--border-color); }
    .lt-adv[hidden] { display: none; }
    .lt-adv .grp { display: flex; flex-direction: column; gap: 5px; }
    .lt-adv .grp > span { font-size: 11.5px; font-weight: 600; color: var(--text-muted); }
    .lt-adv .pair { display: flex; align-items: center; gap: 6px; }
    .lt-adv .pair > span { color: var(--text-muted); font-size: 12px; }
    .lt-adv input { width: 150px; }
    .lt-adv .quick { display: flex; gap: 6px; flex-wrap: wrap; }
    .lt-adv .quick button { height: 38px; padding: 0 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); background: #fff; color: var(--text-secondary); font-size: 12.5px; font-weight: 600; cursor: pointer; }
    .lt-adv .quick button:hover { border-color: var(--primary); color: var(--primary); }
    .lt-summary { margin-top: 10px; font-size: 12.5px; color: var(--text-muted); }

    /* ---------- Table ---------- */
    .lt-results { padding: 16px 20px 20px; }
    .lt-results .table-responsive { border: 1px solid var(--border-color); border-radius: var(--radius); }
    .lt-results .admin-table th { padding: 11px 14px; background: var(--bg-subtle); }
    .lt-results .admin-table td { padding: 12px 14px; vertical-align: middle; white-space: nowrap; }
    .lt-results .admin-table tbody tr:last-child td { border-bottom: none; }
    .lt-results .admin-table tbody tr[data-row-id] { cursor: pointer; }
    .lt-results .admin-table tbody tr[data-row-id]:hover td { background: #f9fbfd; }
    .lt-results .admin-table th:last-child,
    .lt-results .admin-table td:last-child {
        position: sticky; right: 0; z-index: 1; background: #fff; text-align: right;
        box-shadow: -10px 0 10px -10px rgba(15, 23, 42, 0.18);
    }
    .lt-results .admin-table th:last-child { background: var(--bg-subtle); }
    .lt-results .admin-table tbody tr[data-row-id]:hover td:last-child { background: #f9fbfd; }
    .c-main { font-weight: 600; color: var(--text-main); }
    .c-sub { font-size: 12px; color: var(--text-muted); margin-top: 1px; font-variant-numeric: tabular-nums; }
    .c-muted { color: #94a3b8; }
    .c-mono { font-variant-numeric: tabular-nums; letter-spacing: 0.3px; }
    .c-preview { max-width: 280px; overflow: hidden; text-overflow: ellipsis; color: var(--text-secondary); font-size: 13px; }
    .c-chip { display: inline-block; padding: 2px 9px; border-radius: 999px; background: #f1f5f9; color: var(--text-secondary); font-size: 12px; font-weight: 600; text-transform: capitalize; }
    .c-chip.muted { background: transparent; color: #94a3b8; padding: 0; font-weight: 500; }
    .score-pill { display: inline-flex; align-items: center; gap: 6px; padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; font-variant-numeric: tabular-nums; }
    .score-pill small { font-weight: 600; font-size: 11px; opacity: 0.85; }
    .score-pill.excellent { background: #dcfce7; color: #166534; }
    .score-pill.good { background: #dbeafe; color: #1d4ed8; }
    .score-pill.fair { background: #fef3c7; color: #b45309; }
    .score-pill.poor { background: #fee2e2; color: #b91c1c; }
    .score-pill.none { background: #f1f5f9; color: #64748b; }
    .st-pill { display: inline-flex; align-items: center; gap: 6px; padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; text-transform: capitalize; border: 1px solid transparent; }
    .st-pill::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .st-pill.approved { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
    .st-pill.rejected { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
    .st-pill.pending { background: #fffbeb; color: #b45309; border-color: #fde68a; }
    .row-actions { display: inline-flex; gap: 6px; justify-content: flex-end; }
    .ic-btn {
        width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;
        border-radius: 8px; border: 1px solid var(--border-strong); background: #fff; color: var(--text-secondary);
        font-size: 13px; cursor: pointer; text-decoration: none;
        transition: border-color 0.15s ease, color 0.15s ease, background-color 0.15s ease;
    }
    .ic-btn:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-soft); }
    .ic-btn.wa { color: #15803d; }
    .ic-btn.wa:hover { border-color: #16a34a; background: #f0fdf4; }
    .ic-btn.ok { color: #15803d; border-color: #bbf7d0; }
    .ic-btn.ok:hover { background: #f0fdf4; border-color: #16a34a; }
    .ic-btn.danger { color: #b91c1c; border-color: #fecaca; }
    .ic-btn.danger:hover { background: #fef2f2; border-color: #dc2626; color: #dc2626; }
    .lt-empty { padding: 48px 20px !important; text-align: center !important; white-space: normal !important; color: var(--text-muted); position: static !important; box-shadow: none !important; }
    .lt-empty i.big { display: block; font-size: 26px; color: #cbd5e1; margin-bottom: 10px; }
    .lt-empty strong { display: block; color: var(--text-main); margin-bottom: 4px; }
    .lt-foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-top: 14px; font-size: 12.5px; color: var(--text-muted); }
    .lt-foot .pagination { display: flex; gap: 4px; list-style: none; margin: 0; flex-wrap: wrap; }
    .lt-foot .page-item a, .lt-foot .page-item span {
        min-width: 32px; height: 32px; padding: 0 10px; display: inline-flex; align-items: center; justify-content: center;
        border: 1px solid var(--border-color); border-radius: 7px; background: #fff; color: var(--text-secondary);
        font-size: 12.5px; font-weight: 600; text-decoration: none;
    }
    .lt-foot .page-item a:hover { border-color: var(--primary); color: var(--primary); }
    .lt-foot .page-item.active span { background: var(--primary); border-color: var(--primary); color: #fff; }
    .lt-foot .page-item.disabled span { opacity: 0.45; cursor: not-allowed; }
    .lt-foot .page-item.gap span { border: none; background: transparent; min-width: 16px; padding: 0; }

    /* ---------- Drawer ---------- */
    .drawer-overlay {
        position: fixed; inset: 0; z-index: 1000; display: flex; justify-content: flex-end;
        visibility: hidden; opacity: 0; transition: opacity 0.25s ease, visibility 0s linear 0.25s;
    }
    .drawer-overlay.active { visibility: visible; opacity: 1; transition: opacity 0.25s ease, visibility 0s; }
    .drawer-content {
        width: 100%; max-width: 620px; height: 100%; background: var(--bg-dark); overflow-y: auto; overscroll-behavior: contain;
        box-shadow: -16px 0 40px rgba(15, 23, 42, 0.18); transform: translateX(100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; flex-direction: column;
    }
    .drawer-overlay.active .drawer-content { transform: translateX(0); }
    .drawer-header {
        position: sticky; top: 0; z-index: 5; display: flex; justify-content: space-between; align-items: center; gap: 12px;
        min-height: 56px; padding: 10px 16px 10px 24px; background: #fff; border-bottom: 1px solid var(--border-color);
    }
    .drawer-header .card-title-text { font-size: 14px; font-weight: 600; color: var(--text-muted); }
    .drawer-close { width: 36px; height: 36px; border-radius: 9px; background: none; border: none; color: var(--text-muted); font-size: 17px; cursor: pointer; }
    .drawer-close:hover { background: #f1f5f9; color: var(--text-main); }
    #drawerBody { padding: 20px 24px 40px; flex: 1; }
    .d-hero, .d-card { background: #fff; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 18px 20px; margin-bottom: 14px; box-shadow: var(--shadow-xs); }
    .d-hero-top { display: flex; gap: 14px; align-items: flex-start; }
    .d-avatar { width: 46px; height: 46px; border-radius: 50%; flex-shrink: 0; background: linear-gradient(135deg, #2563eb, #18529c); color: #fff; font-weight: 700; font-size: 16px; display: flex; align-items: center; justify-content: center; }
    .d-name { font-family: 'Outfit', sans-serif; font-size: 20px; font-weight: 700; line-height: 1.25; word-break: break-word; }
    .d-meta { display: flex; align-items: center; gap: 6px 10px; flex-wrap: wrap; margin-top: 4px; font-size: 12.5px; color: var(--text-muted); }
    .d-meta .sep { color: #cbd5e1; }
    .d-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 14px; }
    .d-actions .lt-btn { height: 34px; padding: 0 12px; font-size: 12.5px; }
    .d-actions .lt-btn.wa { color: #15803d; }
    .d-actions .lt-btn.ok { background: #16a34a; border-color: #16a34a; color: #fff; }
    .d-actions .lt-btn.ok:hover { background: #15803d; }
    .d-actions .lt-btn.no { color: #b91c1c; border-color: #fecaca; }
    .d-actions .lt-btn.no:hover { background: #fef2f2; border-color: #dc2626; }
    .d-title { font-family: 'Outfit', sans-serif; font-size: 14.5px; font-weight: 600; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
    .d-title i { color: var(--primary); font-size: 13px; width: 16px; text-align: center; }
    .d-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px 20px; }
    .d-grid .span-2 { grid-column: 1 / -1; }
    .d-label { font-size: 11.5px; color: var(--text-muted); margin-bottom: 3px; }
    .d-val { font-size: 13.5px; font-weight: 600; color: var(--text-main); word-break: break-word; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
    .d-val .empty { color: #94a3b8; font-weight: 500; }
    .d-message { white-space: pre-wrap; line-height: 1.6; font-size: 14px; color: var(--text-main); background: var(--bg-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 14px 16px; }
    .d-score { display: flex; align-items: center; gap: 18px; flex-wrap: wrap; }
    .d-gauge { width: 92px; height: 92px; border-radius: 50%; border: 6px solid #f1f5f9; display: flex; flex-direction: column; align-items: center; justify-content: center; flex-shrink: 0; }
    .d-gauge b { font-family: 'Outfit', sans-serif; font-size: 24px; line-height: 1; }
    .d-gauge small { font-size: 10px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-top: 3px; letter-spacing: 0.4px; }
    .d-gauge.excellent { border-color: #bbf7d0; } .d-gauge.excellent b { color: #16a34a; }
    .d-gauge.good { border-color: #bfdbfe; } .d-gauge.good b { color: #2563eb; }
    .d-gauge.fair { border-color: #fde68a; } .d-gauge.fair b { color: #b45309; }
    .d-gauge.poor { border-color: #fecaca; } .d-gauge.poor b { color: #dc2626; }
    .d-gauge.none b { color: #94a3b8; }
    details.d-card > summary { list-style: none; cursor: pointer; display: flex; justify-content: space-between; align-items: center; }
    details.d-card > summary::-webkit-details-marker { display: none; }
    details.d-card > summary .d-title { margin: 0; }
    details.d-card[open] > summary { margin-bottom: 14px; }
    details.d-card > summary .chev { color: var(--text-muted); font-size: 11px; transition: transform 0.15s ease; }
    details.d-card[open] > summary .chev { transform: rotate(180deg); }
    .d-ua { font-size: 12px; line-height: 1.5; color: var(--text-secondary); background: var(--bg-subtle); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px 10px; word-break: break-all; font-weight: 500; }
    .copy-inline { border: none; background: transparent; color: #94a3b8; cursor: pointer; width: 22px; height: 22px; border-radius: 5px; font-size: 11px; padding: 0; }
    .copy-inline:hover { background: #eef1f6; color: var(--primary); }
    .copy-inline.copied { color: #15803d; }

    @media (max-width: 900px) {
        .lt-kpis { grid-template-columns: minmax(0, 1fr); }
    }
    @media (max-width: 640px) {
        .lt-toolbar-wrap { padding: 12px 16px; }
        .lt-results { padding: 12px 16px 16px; }
        .lt-field.select { flex: 1 1 140px; }
        .lt-actions { width: 100%; margin-left: 0; }
        .lt-actions > * { flex: 1; }
        .lt-adv input { width: auto; flex: 1; }
        .lt-adv .grp, .lt-adv .pair { width: 100%; }
        #drawerBody { padding: 16px 16px 32px; }
        .d-grid { grid-template-columns: minmax(0, 1fr); }
    }
</style>

<!-- Tabs (counts filled from the KPI data) -->
<nav class="lt-tabs" id="websiteLeadTabs" aria-label="Inquiry type">
    <?php foreach ($tabs as $tabKey => $tabMeta): ?>
        <a href="?tab=<?php echo urlencode($tabKey); ?>" data-lead-tab="<?php echo htmlspecialchars($tabKey, ENT_QUOTES, 'UTF-8'); ?>" class="lt-tab<?php echo $activeTab === $tabKey ? ' active' : ''; ?>">
            <i class="fa-solid <?php echo htmlspecialchars($tabMeta['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
            <?php echo htmlspecialchars($tabMeta['label'], ENT_QUOTES, 'UTF-8'); ?>
            <span class="count" data-tab-count="<?php echo htmlspecialchars($tabKey, ENT_QUOTES, 'UTF-8'); ?>"></span>
        </a>
    <?php endforeach; ?>
</nav>

<div class="lt-kpis" id="websiteLeadKpiGrid"></div>

<section class="lt-card">
    <div class="lt-toolbar-wrap">
        <!-- Filters apply automatically (typing is debounced), so there is no Apply button. -->
        <form method="GET" action="leads.php" id="websiteLeadFilterForm" role="search">
            <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="lt-toolbar">
                <div class="lt-field search sr">
                    <label for="lead-search">Search</label>
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input id="lead-search" type="search" name="search" autocomplete="off"
                           placeholder="<?php echo htmlspecialchars($searchPlaceholders[$activeTab], ENT_QUOTES, 'UTF-8'); ?>"
                           value="<?php echo htmlspecialchars($filters['search'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="lt-field select sr" id="lead-category-field"<?php echo isset($tabCategoryLabels[$activeTab]) ? '' : ' hidden'; ?>>
                    <label for="lead-category" id="lead-category-label"><?php echo htmlspecialchars($tabCategoryLabels[$activeTab]['label'] ?? 'Category', ENT_QUOTES, 'UTF-8'); ?></label>
                    <select id="lead-category" name="category">
                        <option value=""><?php echo htmlspecialchars($tabCategoryLabels[$activeTab]['all'] ?? 'All', ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php foreach ($categoryOptions as $option): ?>
                            <option value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $filters['category'] === $option ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucfirst(str_replace(['_', '-'], ' ', $option)), ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="lt-field select sr" id="lead-status-field"<?php echo $activeTab === 'partners' ? '' : ' hidden'; ?>>
                    <label for="lead-status">Partner status</label>
                    <select id="lead-status" name="status">
                        <option value="">All statuses</option>
                        <?php foreach ($statusOptions as $option): ?>
                            <option value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $filters['status'] === $option ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucfirst($option), ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="lt-field lt-score sr" id="lead-score-field"<?php echo $activeTab === 'cibil' ? '' : ' hidden'; ?>>
                    <span>Score</span>
                    <label for="lead-score-min">Minimum score</label>
                    <input id="lead-score-min" type="number" name="score_min" min="300" max="900" inputmode="numeric" placeholder="Min" value="<?php echo htmlspecialchars($filters['score_min'], ENT_QUOTES, 'UTF-8'); ?>">
                    <span>–</span>
                    <label for="lead-score-max">Maximum score</label>
                    <input id="lead-score-max" type="number" name="score_max" min="300" max="900" inputmode="numeric" placeholder="Max" value="<?php echo htmlspecialchars($filters['score_max'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <button type="button" class="lt-btn" id="leadDateToggle" aria-expanded="<?php echo $dateFilterActive ? 'true' : 'false'; ?>" aria-controls="leadDatePanel">
                    <i class="fa-regular fa-calendar"></i> Date
                    <span class="badge-n" id="leadDateBadge" hidden>1</span>
                </button>

                <div class="lt-actions">
                    <a href="leads.php?tab=<?php echo urlencode($activeTab); ?>" class="lt-btn" data-lead-reset title="Clear all filters"<?php echo $filtersActive ? '' : ' hidden'; ?>>
                        <i class="fa-solid fa-xmark"></i> Clear
                    </a>
                    <button type="button" class="lt-btn export" id="leadExportBtn" title="Download the current (filtered) list as CSV">
                        <i class="fa-solid fa-file-arrow-down"></i> Export
                    </button>
                </div>
            </div>

            <div class="lt-adv" id="leadDatePanel"<?php echo $dateFilterActive ? '' : ' hidden'; ?>>
                <div class="grp">
                    <span>Received between</span>
                    <div class="pair">
                        <label class="sr" for="lead-date-from">From date</label>
                        <input id="lead-date-from" type="date" name="date_from" max="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($filters['date_from'], ENT_QUOTES, 'UTF-8'); ?>">
                        <span>to</span>
                        <label class="sr" for="lead-date-to">To date</label>
                        <input id="lead-date-to" type="date" name="date_to" max="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($filters['date_to'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
                <div class="grp">
                    <span>Quick range</span>
                    <div class="quick">
                        <button type="button" data-range="today">Today</button>
                        <button type="button" data-range="yesterday">Yesterday</button>
                        <button type="button" data-range="7">Last 7 days</button>
                        <button type="button" data-range="30">Last 30 days</button>
                    </div>
                </div>
            </div>

            <div class="lt-summary" id="websiteLeadSummary" aria-live="polite"></div>
            <button type="submit" hidden aria-hidden="true" tabindex="-1"></button>
        </form>
    </div>

    <div class="lt-results" id="websiteLeadResults"></div>
</section>

<script type="application/json" id="websiteLeadInitialData"><?php echo json_encode($initialLeadData, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?></script>

<!-- Details Drawer -->
<div class="drawer-overlay" id="detailDrawer">
    <div class="drawer-content" role="dialog" aria-modal="true" aria-labelledby="drawerTitle">
        <div class="drawer-header">
            <h2 class="card-title-text" id="drawerTitle">Inquiry</h2>
            <button type="button" class="drawer-close" id="closeDrawer" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div id="drawerBody"></div>
    </div>
</div>

<script>
(function () {
    // CIBIL report links: private:// documents open through download-file.php.
    function adminCibilPdfUrl(p) {
        const v = String(p || '');
        if (v.startsWith('private://')) return '../download-file.php?file=' + encodeURIComponent(v);
        if (/^https:\/\//.test(v)) return v;
        if (/^uploads\/[\w\/.-]+$/.test(v)) return '../' + v;
        return '';
    }

    const drawer = document.getElementById('detailDrawer');
    const drawerBody = document.getElementById('drawerBody');
    const drawerTitle = document.getElementById('drawerTitle');
    const closeDrawerBtn = document.getElementById('closeDrawer');
    const csrfToken = <?php echo json_encode(csrf_token()); ?>;
    const leadTabsMeta = <?php echo json_encode($tabs, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    const initialData = JSON.parse(document.getElementById('websiteLeadInitialData').textContent || '{}');
    const leadFilterForm = document.getElementById('websiteLeadFilterForm');
    const leadResultsArea = document.getElementById('websiteLeadResults');
    const leadKpiGrid = document.getElementById('websiteLeadKpiGrid');
    const leadSummary = document.getElementById('websiteLeadSummary');
    const leadTabs = document.getElementById('websiteLeadTabs');
    const datePanel = document.getElementById('leadDatePanel');
    const dateToggle = document.getElementById('leadDateToggle');

    const CATEGORY_UI = {
        landing: { label: 'Loan type', all: 'All loan types' },
        contact: { label: 'Subject', all: 'All subjects' }
    };
    const SEARCH_PLACEHOLDERS = {
        landing: 'Name, mobile, loan type, pincode…',
        contact: 'Name, email, phone, subject, message…',
        cibil: 'Name, email, mobile, PAN, score, ref ID…',
        partners: 'Name, email / mobile, status…'
    };
    const ENTITY = { landing: 'quick quote', contact: 'contact inquiry', cibil: 'CIBIL check', partners: 'DSA partner' };
    const ENTITY_PLURAL = { landing: 'quick quotes', contact: 'contact inquiries', cibil: 'CIBIL checks', partners: 'DSA partners' };

    const leadState = { tab: initialData.tab || 'landing', page: initialData.page || 1, totalPages: initialData.total_pages || 1, loading: false };
    let leadRequestId = 0;
    let leadSearchTimer = null;
    let lastFocus = null;
    const leadRecordCache = new Map();

    // ---------- helpers ----------
    function escapeHtml(value) {
        return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }
    const EMPTY = '<span class="empty">—</span>';
    const isBlank = v => v === null || v === undefined || String(v).trim() === '';
    function humanize(value) {
        const s = String(value || '').replace(/[_-]+/g, ' ').trim();
        return s ? s.charAt(0).toUpperCase() + s.slice(1) : '';
    }
    function cleanPhone(value) {
        const d = String(value || '').replace(/\D/g, '').replace(/^91(?=\d{10}$)/, '').replace(/^0(?=\d{10}$)/, '');
        return d.length === 10 ? d : '';
    }
    function isEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(v || '').trim()); }
    function parseDate(v) { const d = new Date(String(v || '').replace(' ', 'T')); return isNaN(d.getTime()) ? null : d; }
    function fmtDateTime(v) {
        const d = parseDate(v);
        if (!d) return isBlank(v) ? EMPTY : escapeHtml(v);
        return d.toLocaleString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
    }
    function dateCell(v) {
        const d = parseDate(v);
        if (!d) return '<span class="c-muted">—</span>';
        return `<div>${d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })}</div>
                <div class="c-sub">${d.toLocaleTimeString('en-IN', { hour: 'numeric', minute: '2-digit', hour12: true })}</div>`;
    }
    function relTime(v) {
        const d = parseDate(v);
        if (!d) return '';
        const s = Math.round((Date.now() - d.getTime()) / 1000);
        if (s < 60) return 'just now';
        if (s < 3600) return Math.floor(s / 60) + ' min ago';
        if (s < 86400) return Math.floor(s / 3600) + ' h ago';
        const days = Math.floor(s / 86400);
        return days === 1 ? 'yesterday' : days + ' days ago';
    }
    function initials(name) {
        const p = String(name || '').trim().split(/\s+/).filter(Boolean);
        return ((p[0] || '?').charAt(0) + (p.length > 1 ? p[p.length - 1].charAt(0) : '')).toUpperCase();
    }
    function scoreInfo(score) {
        const n = parseInt(score, 10);
        if (isBlank(score) || isNaN(n) || n <= 0) return { tone: 'none', label: 'No score', value: '—' };
        if (n >= 750) return { tone: 'excellent', label: 'Excellent', value: n };
        if (n >= 700) return { tone: 'good', label: 'Good', value: n };
        if (n >= 650) return { tone: 'fair', label: 'Fair', value: n };
        return { tone: 'poor', label: 'Poor', value: n };
    }
    function scorePill(score) {
        const s = scoreInfo(score);
        return `<span class="score-pill ${s.tone}">${s.value}${s.tone !== 'none' ? ` <small>${s.label}</small>` : ''}</span>`;
    }
    function statusOf(p) { const s = String(p.status || '').toLowerCase(); return ['approved', 'rejected'].includes(s) ? s : 'pending'; }
    function statusPill(status) { return `<span class="st-pill ${status}">${status}</span>`; }
    function copyBtn(text, label) {
        if (isBlank(text)) return '';
        return `<button type="button" class="copy-inline" data-copy="${escapeHtml(text)}" title="Copy ${escapeHtml(label)}" aria-label="Copy ${escapeHtml(label)}"><i class="fa-regular fa-copy"></i></button>`;
    }
    function nameMain(name) {
        return isBlank(name) ? '<span class="c-muted" style="font-style:italic;font-weight:500;">Name not provided</span>' : escapeHtml(name);
    }
    // Partners register with a single "email or mobile" field.
    function partnerContact(p) {
        const v = String(p.email_or_mobile || '').trim();
        return { phone: cleanPhone(v), email: isEmail(v) ? v : '' };
    }
    function contactOf(type, r) {
        if (type === 'partners') return partnerContact(r);
        return { phone: cleanPhone(r.mobile || r.phone), email: isEmail(r.email) ? String(r.email).trim() : '' };
    }

    // ---------- table ----------
    function iconLink(cls, href, icon, title, external) {
        return `<a class="ic-btn ${cls}" href="${href}"${external ? ' target="_blank" rel="noopener"' : ''} title="${title}" aria-label="${title}"><i class="${icon}"></i></a>`;
    }
    function rowActions(type, r) {
        const c = contactOf(type, r);
        const id = escapeHtml(r.id);
        const parts = [];
        if (type === 'partners') {
            const st = statusOf(r);
            if (st !== 'approved') parts.push(`<button type="button" class="ic-btn ok js-partner-status" data-id="${id}" data-status="approved" title="Approve" aria-label="Approve"><i class="fa-solid fa-check"></i></button>`);
            if (st !== 'rejected') parts.push(`<button type="button" class="ic-btn danger js-partner-status" data-id="${id}" data-status="rejected" title="Reject" aria-label="Reject"><i class="fa-solid fa-xmark"></i></button>`);
        }
        if (c.phone) parts.push(iconLink('', `tel:+91${c.phone}`, 'fa-solid fa-phone', 'Call', false));
        if (c.phone && type !== 'cibil') parts.push(iconLink('wa', `https://wa.me/91${c.phone}`, 'fa-brands fa-whatsapp', 'WhatsApp', true));
        if (type === 'contact' && c.email) parts.push(iconLink('', `mailto:${encodeURIComponent(c.email)}?subject=${encodeURIComponent('Re: your Waqt Finance inquiry')}`, 'fa-solid fa-envelope', 'Reply by email', false));
        parts.push(`<button type="button" class="ic-btn danger js-lead-delete" data-type="${type}" data-id="${id}" title="Delete" aria-label="Delete"><i class="fa-solid fa-trash"></i></button>`);
        return `<div class="row-actions">${parts.join('')}</div>`;
    }
    function rowAttrs(type, r) {
        return `id="lead-row-${type}-${escapeHtml(r.id)}" data-row-id="${escapeHtml(r.id)}" data-row-type="${type}"`;
    }
    const TABLES = {
        landing: {
            head: ['Lead', 'Loan type', 'Pincode', 'Received', 'Actions'],
            row: r => `<tr ${rowAttrs('landing', r)}>
                <td><div class="c-main">${nameMain(r.full_name)}</div><div class="c-sub">${escapeHtml(r.mobile || '')}</div></td>
                <td>${isBlank(r.loan_type) ? '<span class="c-chip muted">Not set</span>' : `<span class="c-chip">${escapeHtml(humanize(r.loan_type))}</span>`}</td>
                <td class="c-mono">${isBlank(r.pincode) ? '<span class="c-muted">—</span>' : escapeHtml(r.pincode)}</td>
                <td>${dateCell(r.created_at)}</td>
                <td>${rowActions('landing', r)}</td></tr>`
        },
        contact: {
            head: ['Sender', 'Subject', 'Message', 'Received', 'Actions'],
            row: r => {
                const msg = String(r.message || '').replace(/\s+/g, ' ').trim();
                return `<tr ${rowAttrs('contact', r)}>
                    <td><div class="c-main">${nameMain(r.name)}</div><div class="c-sub">${escapeHtml([r.phone, r.email].filter(x => !isBlank(x)).join(' · '))}</div></td>
                    <td>${isBlank(r.subject) ? '<span class="c-chip muted">None</span>' : `<span class="c-chip">${escapeHtml(humanize(r.subject))}</span>`}</td>
                    <td><div class="c-preview" title="${escapeHtml(msg)}">${msg ? escapeHtml(msg) : '<span class="c-muted">No message</span>'}</div></td>
                    <td>${dateCell(r.created_at)}</td>
                    <td>${rowActions('contact', r)}</td></tr>`;
            }
        },
        cibil: {
            head: ['Applicant', 'PAN', 'Score', 'Checked', 'Actions'],
            row: r => `<tr ${rowAttrs('cibil', r)}>
                <td><div class="c-main">${nameMain(r.full_name)}</div><div class="c-sub">${escapeHtml([r.mobile, r.email].filter(x => !isBlank(x)).join(' · '))}</div></td>
                <td class="c-mono">${isBlank(r.pan) ? '<span class="c-muted">—</span>' : escapeHtml(String(r.pan).toUpperCase())}</td>
                <td>${scorePill(r.score)}</td>
                <td>${dateCell(r.created_at)}</td>
                <td>${rowActions('cibil', r)}</td></tr>`
        },
        partners: {
            head: ['Partner', 'Email / mobile', 'Status', 'Registered', 'Actions'],
            row: r => `<tr ${rowAttrs('partners', r)}>
                <td><div class="c-main">${nameMain(r.full_name)}</div></td>
                <td>${isBlank(r.email_or_mobile) ? '<span class="c-muted">—</span>' : escapeHtml(r.email_or_mobile)}</td>
                <td>${statusPill(statusOf(r))}</td>
                <td>${dateCell(r.created_at)}</td>
                <td>${rowActions('partners', r)}</td></tr>`
        }
    };

    function renderPagination(data) {
        if (data.total_pages <= 1) return '';
        const cur = data.page, total = data.total_pages;
        const set = new Set([1, total, cur - 1, cur, cur + 1]);
        if (cur <= 3) [2, 3, 4].forEach(p => set.add(p));
        if (cur >= total - 2) [total - 1, total - 2, total - 3].forEach(p => set.add(p));
        const pages = Array.from(set).filter(p => p >= 1 && p <= total).sort((a, b) => a - b);
        let last = 0;
        const items = [];
        pages.forEach(p => {
            if (last && p - last > 1) items.push('<li class="page-item gap"><span>…</span></li>');
            items.push(p === cur ? `<li class="page-item active"><span aria-current="page">${p}</span></li>` : `<li class="page-item"><a href="#" data-lead-page="${p}">${p}</a></li>`);
            last = p;
        });
        const prev = cur <= 1 ? '<li class="page-item disabled"><span><i class="fa-solid fa-chevron-left" style="font-size:10px;"></i></span></li>' : `<li class="page-item"><a href="#" data-lead-page="${cur - 1}" aria-label="Previous page"><i class="fa-solid fa-chevron-left" style="font-size:10px;"></i></a></li>`;
        const next = cur >= total ? '<li class="page-item disabled"><span><i class="fa-solid fa-chevron-right" style="font-size:10px;"></i></span></li>' : `<li class="page-item"><a href="#" data-lead-page="${cur + 1}" aria-label="Next page"><i class="fa-solid fa-chevron-right" style="font-size:10px;"></i></a></li>`;
        return `<ul class="pagination" aria-label="Pagination">${prev}${items.join('')}${next}</ul>`;
    }

    function renderLeadResults(data) {
        const tab = data.tab;
        const table = TABLES[tab] || TABLES.landing;
        const records = data.records || [];
        records.forEach(r => leadRecordCache.set(`${tab}:${r.id}`, r));
        const meta = leadTabsMeta[tab] || {};
        const body = records.length ? records.map(table.row).join('') : `
            <tr><td colspan="${table.head.length}" class="lt-empty">
                <i class="fa-solid ${escapeHtml(meta.icon || 'fa-inbox')} big"></i>
                <strong>${data.filters_active ? `No ${ENTITY_PLURAL[tab]} match these filters` : `No ${ENTITY_PLURAL[tab]} yet`}</strong>
                ${data.filters_active
                    ? 'Try widening the date range or clearing a filter.<br><button type="button" class="lt-btn" style="margin-top:12px;" data-lead-reset-inline><i class="fa-solid fa-xmark"></i> Clear filters</button>'
                    : 'New submissions from the website will appear here.'}
            </td></tr>`;
        const total = Number(data.total_records || 0);
        const limit = Number(data.limit || 10);
        const start = total ? (data.page - 1) * limit + 1 : 0;
        const end = Math.min(data.page * limit, total);

        leadResultsArea.innerHTML = `
            <div style="position:relative;">
                <div class="table-loading-overlay" id="leadTableLoadingOverlay"><div class="suspense-spinner"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading…</span></div></div>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead><tr>${table.head.map((h, i) => `<th${i === table.head.length - 1 ? ' style="text-align:right;"' : ''}>${h}</th>`).join('')}</tr></thead>
                        <tbody>${body}</tbody>
                    </table>
                </div>
                ${total ? `<div class="lt-foot"><span>Showing ${start.toLocaleString('en-IN')}–${end.toLocaleString('en-IN')} of ${total.toLocaleString('en-IN')} · click a row to open it</span>${renderPagination(data)}</div>` : ''}
            </div>
        `;
    }

    // ---------- KPI + tab counts ----------
    function renderKpiCards(cards, tab) {
        const byKey = {};
        (cards || []).forEach(c => { byKey[c.key] = c; });
        Object.keys(leadTabsMeta).forEach(key => {
            const el = leadTabs.querySelector(`[data-tab-count="${key}"]`);
            if (!el) return;
            const card = byKey[key];
            el.textContent = card ? card.value : '';
            el.classList.toggle('attn', key === 'partners' && /^[1-9]/.test(String(card && card.sub || '')));
        });
        const tabCard = byKey[tab];
        const pick = [byKey.today_inquiries, byKey.yesterday_inquiries, tabCard].filter(Boolean);
        leadKpiGrid.innerHTML = pick.map(c => {
            const attn = c.key === 'partners' && /^[1-9]/.test(String(c.sub || ''));
            const label = c.key === tab ? `${c.label} · total` : c.label;
            return `<div class="lt-kpi${attn ? ' attn' : ''}">
                <div style="min-width:0;">
                    <div class="lt-kpi-label">${escapeHtml(label)}</div>
                    <div class="lt-kpi-value">${escapeHtml(c.value)}</div>
                    <div class="lt-kpi-sub">${escapeHtml(c.sub)}</div>
                </div>
                <span class="lt-kpi-icon"><i class="fa-solid ${escapeHtml(c.icon)}"></i></span>
            </div>`;
        }).join('');
    }

    // ---------- filters ----------
    function field(name) { return leadFilterForm.querySelector(`[name="${name}"]`); }
    function updateFilterUi() {
        leadFilterForm.querySelectorAll('input[type="search"], input[type="date"], input[type="number"], select').forEach(el => {
            el.classList.toggle('has-value', String(el.value || '').trim() !== '');
        });
        const dateSet = !!(field('date_from').value || field('date_to').value);
        document.getElementById('leadDateBadge').hidden = !dateSet;
    }

    function syncLeadFilters(data) {
        const tab = data.tab;
        field('tab').value = tab;
        const search = field('search');
        search.placeholder = SEARCH_PLACEHOLDERS[tab] || '';
        search.value = data.filters.search || '';
        ['date_from', 'date_to', 'score_min', 'score_max'].forEach(n => { field(n).value = data.filters[n] || ''; });

        const catField = document.getElementById('lead-category-field');
        const cat = document.getElementById('lead-category');
        const ui = CATEGORY_UI[tab];
        catField.hidden = !ui;
        if (ui) {
            document.getElementById('lead-category-label').textContent = ui.label;
            cat.innerHTML = `<option value="">${ui.all}</option>` + (data.category_options || []).map(o =>
                `<option value="${escapeHtml(o)}" ${data.filters.category === o ? 'selected' : ''}>${escapeHtml(humanize(o))}</option>`).join('');
        }
        const stField = document.getElementById('lead-status-field');
        const st = document.getElementById('lead-status');
        stField.hidden = tab !== 'partners';
        st.innerHTML = '<option value="">All statuses</option>' + (data.status_options || []).map(o =>
            `<option value="${escapeHtml(o)}" ${data.filters.status === o ? 'selected' : ''}>${escapeHtml(humanize(o))}</option>`).join('');
        document.getElementById('lead-score-field').hidden = tab !== 'cibil';

        leadTabs.querySelectorAll('[data-lead-tab]').forEach(t => t.classList.toggle('active', t.getAttribute('data-lead-tab') === tab));
        const reset = leadFilterForm.querySelector('[data-lead-reset]');
        reset.hidden = !data.filters_active;
        reset.setAttribute('href', `leads.php?tab=${encodeURIComponent(tab)}`);
        updateFilterUi();
    }

    function renderSummary(data) {
        const total = Number(data.total_records || 0);
        leadSummary.innerHTML = `${total.toLocaleString('en-IN')} ${data.filters_active ? 'matching ' : ''}${total === 1 ? ENTITY[data.tab] : ENTITY_PLURAL[data.tab]}`
            + (data.filters_active ? ' <span style="color:var(--primary); margin-left:6px;">· filtered</span>' : '');
    }

    function updateLeadUrl(data) {
        const params = new URLSearchParams();
        params.set('tab', data.tab);
        Object.entries(data.filters || {}).forEach(([k, v]) => { if (v !== '' && v !== null) params.set(k, v); });
        if (data.page > 1) params.set('page', data.page);
        history.replaceState(null, '', `leads.php?${params.toString()}`);
    }

    function applyLeadResponse(data, updateUrl = true) {
        leadState.tab = data.tab;
        leadState.page = data.page;
        leadState.totalPages = data.total_pages;
        renderKpiCards(data.kpi_cards || [], data.tab);
        syncLeadFilters(data);
        renderSummary(data);
        renderLeadResults(data);
        if (updateUrl) updateLeadUrl(data);
    }

    function setLeadLoading(on) {
        leadState.loading = on;
        leadResultsArea.classList.toggle('lead-ajax-loading', on);
        leadKpiGrid.classList.toggle('lead-ajax-loading', on);
        document.getElementById('leadTableLoadingOverlay')?.classList.toggle('active', on);
    }

    function refreshWebsiteLeads(options = {}) {
        const requestId = ++leadRequestId;
        const page = options.page || leadState.page || 1;
        setLeadLoading(true);
        const fd = new FormData(leadFilterForm);
        fd.set('action', 'list_website_leads');
        fd.set('csrf_token', csrfToken);
        fd.set('tab', leadState.tab);
        fd.set('page', String(page));
        fd.set('limit', '10');
        fetch('api.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if (requestId !== leadRequestId) return;
                if (data.status !== 'success') throw new Error(data.message || 'Unable to load records.');
                applyLeadResponse(data, options.updateUrl !== false);
            })
            .catch(err => {
                if (requestId !== leadRequestId) return;
                leadResultsArea.innerHTML = `<div style="padding:28px; text-align:center;">
                    <div style="color:var(--danger); font-weight:600;"><i class="fa-solid fa-triangle-exclamation"></i> ${escapeHtml(err.message || 'Unable to load records.')}</div>
                    <button type="button" class="lt-btn" style="margin-top:12px;" data-lead-retry><i class="fa-solid fa-rotate-right"></i> Try again</button></div>`;
            })
            .finally(() => { if (requestId === leadRequestId) setLeadLoading(false); });
    }

    function resetLeadFilters() {
        ['search', 'date_from', 'date_to', 'category', 'status', 'score_min', 'score_max'].forEach(n => { const f = field(n); if (f) f.value = ''; });
    }
    function scheduleLeadRefresh(delay = 350) {
        clearTimeout(leadSearchTimer);
        leadSearchTimer = setTimeout(() => { leadState.page = 1; refreshWebsiteLeads({ page: 1 }); }, delay);
    }

    // Filtered CSV export (same filters as the list), via the POST + CSRF endpoint.
    function exportWebsiteLeadsCsv() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'api.php';
        const add = (n, v) => { const i = document.createElement('input'); i.type = 'hidden'; i.name = n; i.value = v; form.appendChild(i); };
        add('action', 'export_website_leads');
        add('csrf_token', csrfToken);
        add('tab', leadState.tab);
        ['search', 'date_from', 'date_to', 'category', 'status', 'score_min', 'score_max'].forEach(n => add(n, field(n) ? field(n).value : ''));
        document.body.appendChild(form);
        form.submit();
        form.remove();
    }

    // ---------- drawer ----------
    function dField(label, valueHtml, span) {
        return `<div${span ? ' class="span-2"' : ''}><div class="d-label">${escapeHtml(label)}</div><div class="d-val">${isBlank(valueHtml) ? EMPTY : valueHtml}</div></div>`;
    }
    function dText(label, raw, opts = {}) {
        return dField(label, isBlank(raw) ? '' : escapeHtml(raw) + (opts.copy ? copyBtn(raw, label) : ''), opts.span);
    }
    function heroActions(type, r) {
        const c = contactOf(type, r);
        const a = [];
        if (type === 'partners') {
            const st = statusOf(r);
            if (st !== 'approved') a.push(`<button type="button" class="lt-btn ok js-partner-status" data-id="${escapeHtml(r.id)}" data-status="approved"><i class="fa-solid fa-check"></i> Approve</button>`);
            if (st !== 'rejected') a.push(`<button type="button" class="lt-btn no js-partner-status" data-id="${escapeHtml(r.id)}" data-status="rejected"><i class="fa-solid fa-xmark"></i> Reject</button>`);
        }
        if (c.phone) {
            a.push(`<a class="lt-btn" href="tel:+91${c.phone}"><i class="fa-solid fa-phone"></i> Call</a>`);
            a.push(`<a class="lt-btn wa" href="https://wa.me/91${c.phone}" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>`);
        }
        if (c.email) a.push(`<a class="lt-btn" href="mailto:${encodeURIComponent(c.email)}?subject=${encodeURIComponent(type === 'contact' ? 'Re: ' + humanize(r.subject || 'your inquiry') : 'Waqt Finance')}"><i class="fa-solid fa-envelope"></i> Email</a>`);
        a.push(`<button type="button" class="lt-btn no js-lead-delete" data-type="${type}" data-id="${escapeHtml(r.id)}" style="margin-left:auto;"><i class="fa-solid fa-trash"></i> Delete</button>`);
        return `<div class="d-actions">${a.join('')}</div>`;
    }
    function hero(type, r, name, chipHtml) {
        return `<div class="d-hero">
            <div class="d-hero-top">
                <div class="d-avatar">${escapeHtml(initials(name))}</div>
                <div style="flex:1; min-width:0;">
                    <div class="d-name">${isBlank(name) ? '<span class="empty" style="color:#94a3b8;">Name not provided</span>' : escapeHtml(name)}</div>
                    <div class="d-meta">
                        <span>#${escapeHtml(r.id)}</span>
                        ${chipHtml ? `<span class="sep">•</span>${chipHtml}` : ''}
                        <span class="sep">•</span><span title="${escapeHtml(r.created_at || '')}">${escapeHtml(relTime(r.created_at))}</span>
                    </div>
                </div>
            </div>
            ${heroActions(type, r)}
        </div>`;
    }

    function openLeadDrawer(type, r) {
        if (!r) return;
        let html = '';
        if (type === 'landing') {
            drawerTitle.textContent = 'Quick quote';
            html = hero(type, r, r.full_name, isBlank(r.loan_type) ? '' : `<span class="c-chip">${escapeHtml(humanize(r.loan_type))}</span>`)
                + `<section class="d-card"><h3 class="d-title"><i class="fa-solid fa-list-check"></i> Lead details</h3><div class="d-grid">
                    ${dText('Full name', r.full_name)}
                    ${dField('Mobile', isBlank(r.mobile) ? '' : escapeHtml(r.mobile) + copyBtn(r.mobile, 'mobile'))}
                    ${dText('Loan type', humanize(r.loan_type))}
                    ${dText('Pincode', r.pincode, { copy: true })}
                    ${dField('Received', fmtDateTime(r.created_at), true)}
                </div></section>`;
        } else if (type === 'contact') {
            drawerTitle.textContent = 'Contact inquiry';
            const consent = String(r.consent) === '1';
            html = hero(type, r, r.name, isBlank(r.subject) ? '' : `<span class="c-chip">${escapeHtml(humanize(r.subject))}</span>`)
                + `<section class="d-card"><h3 class="d-title"><i class="fa-solid fa-message"></i> Message</h3>
                    <div class="d-message">${isBlank(r.message) ? '<span style="color:#94a3b8;">No message text.</span>' : escapeHtml(r.message)}</div>
                </section>
                <section class="d-card"><h3 class="d-title"><i class="fa-solid fa-user"></i> Sender</h3><div class="d-grid">
                    ${dText('Name', r.name)}
                    ${dField('Phone', isBlank(r.phone) ? '' : escapeHtml(r.phone) + copyBtn(r.phone, 'phone'))}
                    ${dField('Email', isBlank(r.email) ? '' : escapeHtml(r.email) + copyBtn(r.email, 'email'), true)}
                    ${dField('Received', fmtDateTime(r.created_at))}
                    ${dField('Contact consent', consent ? '<span class="st-pill approved">Given</span>' : '<span class="st-pill pending">Not given</span>')}
                </div></section>
                <details class="d-card"><summary><h3 class="d-title"><i class="fa-solid fa-shield-halved"></i> Technical details</h3><i class="fa-solid fa-chevron-down chev"></i></summary>
                    <div class="d-grid">
                        ${dText('IP address', r.ip_address, { copy: true, span: true })}
                        ${dField('Browser / device', isBlank(r.user_agent) ? '' : `<div class="d-ua">${escapeHtml(r.user_agent)}</div>`, true)}
                    </div>
                </details>`;
        } else if (type === 'cibil') {
            drawerTitle.textContent = 'CIBIL check';
            const s = scoreInfo(r.score);
            const pdf = adminCibilPdfUrl(r.pdf_url);
            html = hero(type, r, r.full_name, scorePill(r.score))
                + `<section class="d-card"><h3 class="d-title"><i class="fa-solid fa-gauge-high"></i> Credit score</h3>
                    <div class="d-score">
                        <div class="d-gauge ${s.tone}"><b>${s.value}</b><small>${s.label}</small></div>
                        <div class="d-grid" style="flex:1; min-width:220px;">
                            ${dField('Checked', fmtDateTime(r.created_at), true)}
                            ${dField('Bureau reference', isBlank(r.ref_id) ? '' : `<span style="font-size:12.5px; word-break:break-all; font-weight:500;">${escapeHtml(r.ref_id)}</span>${copyBtn(r.ref_id, 'reference')}`, true)}
                            ${pdf ? dField('Report', `<a class="lt-btn" style="height:32px;" href="${escapeHtml(pdf)}" target="_blank" rel="noopener"><i class="fa-solid fa-file-pdf"></i> Open bureau report (PDF)</a>`, true) : ''}
                        </div>
                    </div>
                </section>
                <section class="d-card"><h3 class="d-title"><i class="fa-solid fa-user"></i> Applicant</h3><div class="d-grid">
                    ${dText('Full name', r.full_name)}
                    ${dField('PAN', isBlank(r.pan) ? '' : escapeHtml(String(r.pan).toUpperCase()) + copyBtn(String(r.pan).toUpperCase(), 'PAN'))}
                    ${dField('Mobile', isBlank(r.mobile) ? '' : escapeHtml(r.mobile) + copyBtn(r.mobile, 'mobile'))}
                    ${dField('Email', isBlank(r.email) ? '' : escapeHtml(r.email) + copyBtn(r.email, 'email'))}
                    ${!isBlank(r.application_id) ? dText('Linked application', r.application_id, { copy: true }) : ''}
                    ${!isBlank(r.lead_id) ? dText('Linked lead', r.lead_id, { copy: true }) : ''}
                </div></section>`;
        } else {
            drawerTitle.textContent = 'DSA partner';
            html = hero('partners', r, r.full_name, statusPill(statusOf(r)))
                + `<section class="d-card"><h3 class="d-title"><i class="fa-solid fa-users"></i> Partner profile</h3><div class="d-grid">
                    ${dText('Full name', r.full_name)}
                    ${dField('Email / mobile', isBlank(r.email_or_mobile) ? '' : escapeHtml(r.email_or_mobile) + copyBtn(r.email_or_mobile, 'contact'))}
                    ${dField('Status', statusPill(statusOf(r)))}
                    ${dField('Registered', fmtDateTime(r.created_at))}
                </div></section>`;
        }
        drawerBody.innerHTML = html;
        drawer.querySelector('.drawer-content').scrollTop = 0;
        if (!drawer.classList.contains('active')) {
            lastFocus = document.activeElement;
            drawer.classList.add('active');
            document.body.style.overflow = 'hidden';
            setTimeout(() => closeDrawerBtn.focus(), 50);
        }
    }

    function closeLeadDrawer() {
        if (!drawer.classList.contains('active')) return;
        drawer.classList.remove('active');
        document.body.style.overflow = '';
        if (lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
    }

    // ---------- actions ----------
    function deleteLeadRecord(id, type) {
        const r = leadRecordCache.get(`${type}:${id}`) || {};
        const who = r.full_name || r.name || `#${id}`;
        Swal.fire({
            title: `Delete this ${ENTITY[type] || 'record'}?`,
            html: 'Delete <b></b>? It will be removed from this list. A copy is kept in the deleted-records archive.',
            icon: 'warning',
            showCancelButton: true,
            focusCancel: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Delete',
            didOpen: popup => { const b = popup.querySelector('.swal2-html-container b'); if (b) b.textContent = who; }
        }).then(result => {
            if (!result.isConfirmed) return;
            Swal.fire({ title: 'Deleting…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            const fd = new FormData();
            fd.append('action', 'delete_application');
            fd.append('id', id);
            fd.append('type', type);
            fd.append('csrf_token', csrfToken);
            fetch('api.php', { method: 'POST', body: fd })
                .then(res => res.json())
                .then(data => {
                    if (data.status !== 'success') throw new Error(data.message || 'Failed to delete record.');
                    Swal.fire({ icon: 'success', title: 'Deleted', text: data.message || 'Record deleted.', timer: 1400, showConfirmButton: false });
                    closeLeadDrawer();
                    refreshWebsiteLeads({ silent: true, updateUrl: false });
                })
                .catch(err => Swal.fire({ icon: 'error', title: 'Delete failed', text: err.message }));
        });
    }

    function updatePartnerStatus(id, status) {
        const r = leadRecordCache.get(`partners:${id}`) || {};
        const approve = status === 'approved';
        Swal.fire({
            title: approve ? 'Approve this partner?' : 'Reject this partner?',
            html: `<b></b> will be marked as <strong>${approve ? 'approved' : 'rejected'}</strong>.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: approve ? '#16a34a' : '#dc2626',
            cancelButtonColor: '#475569',
            confirmButtonText: approve ? 'Approve' : 'Reject',
            didOpen: popup => { const b = popup.querySelector('.swal2-html-container b'); if (b) b.textContent = r.full_name || `Partner #${id}`; }
        }).then(result => {
            if (!result.isConfirmed) return;
            Swal.fire({ title: 'Updating…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            const fd = new FormData();
            fd.append('action', 'update_partner_status');
            fd.append('id', id);
            fd.append('status', status);
            fd.append('csrf_token', csrfToken);
            fetch('api.php', { method: 'POST', body: fd })
                .then(res => res.json())
                .then(data => {
                    if (data.status !== 'success') throw new Error(data.message || 'Failed to update status.');
                    Swal.fire({ icon: 'success', title: approve ? 'Partner approved' : 'Partner rejected', timer: 1300, showConfirmButton: false });
                    const cached = leadRecordCache.get(`partners:${id}`);
                    if (cached) { cached.status = status; if (drawer.classList.contains('active')) openLeadDrawer('partners', cached); }
                    refreshWebsiteLeads({ silent: true, updateUrl: false });
                })
                .catch(err => Swal.fire({ icon: 'error', title: 'Update failed', text: err.message }));
        });
    }

    // ---------- events ----------
    leadFilterForm.addEventListener('submit', e => { e.preventDefault(); leadState.page = 1; refreshWebsiteLeads({ page: 1 }); });
    leadFilterForm.addEventListener('input', e => {
        updateFilterUi();
        if (e.target.matches('[name="search"], [name="score_min"], [name="score_max"]')) scheduleLeadRefresh();
    });
    leadFilterForm.addEventListener('change', e => {
        updateFilterUi();
        if (e.target.matches('select, input[type="date"]')) { leadState.page = 1; refreshWebsiteLeads({ page: 1 }); }
    });
    leadFilterForm.addEventListener('click', e => {
        if (e.target.closest('[data-lead-reset]')) {
            e.preventDefault();
            resetLeadFilters();
            leadState.page = 1;
            refreshWebsiteLeads({ page: 1 });
            return;
        }
        const quick = e.target.closest('[data-range]');
        if (quick) {
            const fmt = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
            const today = new Date();
            let from = new Date(today), to = new Date(today);
            const r = quick.getAttribute('data-range');
            if (r === 'yesterday') { from.setDate(today.getDate() - 1); to = new Date(from); }
            else if (r !== 'today') from.setDate(today.getDate() - (parseInt(r, 10) - 1));
            field('date_from').value = fmt(from);
            field('date_to').value = fmt(to);
            updateFilterUi();
            leadState.page = 1;
            refreshWebsiteLeads({ page: 1 });
        }
    });
    dateToggle.addEventListener('click', () => {
        const open = datePanel.hidden;
        datePanel.hidden = !open;
        dateToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) field('date_from').focus();
    });
    document.getElementById('leadExportBtn').addEventListener('click', exportWebsiteLeadsCsv);

    leadTabs.addEventListener('click', e => {
        const t = e.target.closest('[data-lead-tab]');
        if (!t) return;
        e.preventDefault();
        const next = t.getAttribute('data-lead-tab') || 'landing';
        if (next === leadState.tab) return;
        leadState.tab = next;
        leadState.page = 1;
        field('tab').value = next;
        ['category', 'status', 'score_min', 'score_max'].forEach(n => { const f = field(n); if (f) f.value = ''; });
        leadTabs.querySelectorAll('[data-lead-tab]').forEach(b => b.classList.toggle('active', b === t));
        refreshWebsiteLeads({ page: 1 });
    });

    leadResultsArea.addEventListener('click', e => {
        const pageLink = e.target.closest('[data-lead-page]');
        if (pageLink) {
            e.preventDefault();
            leadState.page = Math.max(1, parseInt(pageLink.getAttribute('data-lead-page'), 10) || 1);
            refreshWebsiteLeads({ page: leadState.page });
            leadResultsArea.scrollIntoView({ behavior: 'smooth', block: 'start' });
            return;
        }
        if (e.target.closest('[data-lead-retry]')) { refreshWebsiteLeads({ page: leadState.page }); return; }
        if (e.target.closest('[data-lead-reset-inline]')) { resetLeadFilters(); leadState.page = 1; refreshWebsiteLeads({ page: 1 }); return; }
        const statusBtn = e.target.closest('.js-partner-status');
        if (statusBtn) { e.preventDefault(); updatePartnerStatus(statusBtn.getAttribute('data-id'), statusBtn.getAttribute('data-status')); return; }
        const del = e.target.closest('.js-lead-delete');
        if (del) { e.preventDefault(); deleteLeadRecord(del.getAttribute('data-id'), del.getAttribute('data-type')); return; }
        if (e.target.closest('a, button, input, select')) return;
        if (window.getSelection && String(window.getSelection()).length > 0) return;
        const row = e.target.closest('tr[data-row-id]');
        if (row) {
            const type = row.getAttribute('data-row-type');
            openLeadDrawer(type, leadRecordCache.get(`${type}:${row.getAttribute('data-row-id')}`));
        }
    });

    drawerBody.addEventListener('click', e => {
        const statusBtn = e.target.closest('.js-partner-status');
        if (statusBtn) { updatePartnerStatus(statusBtn.getAttribute('data-id'), statusBtn.getAttribute('data-status')); return; }
        const del = e.target.closest('.js-lead-delete');
        if (del) { deleteLeadRecord(del.getAttribute('data-id'), del.getAttribute('data-type')); return; }
        const copy = e.target.closest('[data-copy]');
        if (copy) {
            const text = copy.getAttribute('data-copy') || '';
            const done = () => {
                const i = copy.querySelector('i');
                copy.classList.add('copied');
                if (i) i.className = 'fa-solid fa-check';
                setTimeout(() => { copy.classList.remove('copied'); if (i) i.className = 'fa-regular fa-copy'; }, 1200);
            };
            if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(text).then(done).catch(() => {});
            else {
                const ta = document.createElement('textarea');
                ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
                document.body.appendChild(ta); ta.select();
                try { document.execCommand('copy'); done(); } catch (err) {}
                ta.remove();
            }
        }
    });

    closeDrawerBtn.addEventListener('click', closeLeadDrawer);
    drawer.addEventListener('click', e => { if (e.target === drawer) closeLeadDrawer(); });
    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape' || document.querySelector('.swal2-container')) return;
        closeLeadDrawer();
    });

    // ---------- first paint ----------
    if (!initialData.filters) initialData.filters = {};
    initialData.category_options = Array.from(document.getElementById('lead-category').options).slice(1).map(o => o.value);
    initialData.status_options = Array.from(document.getElementById('lead-status').options).slice(1).map(o => o.value);
    applyLeadResponse(initialData, false);
})();
</script>

<?php include 'footer.php'; ?>
