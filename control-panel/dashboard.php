<?php
declare(strict_types=1);

$pageTitle = 'Dashboard';
$pageSubtitle = 'Lead intake, pipeline and recent activity at a glance';

include 'header.php';

$conn = db();

// Helper to safely count table rows
function get_table_count(mysqli $conn, string $table, string $condition = ''): int {
    $sql = "SELECT COUNT(*) FROM " . $table;
    if ($condition !== '') {
        $sql .= " WHERE " . $condition;
    }
    $res = $conn->query($sql);
    if ($res) {
        $row = $res->fetch_row();
        return (int) ($row[0] ?? 0);
    }
    return 0;
}

// Indian digit grouping: 1234567 -> 12,34,567
function dash_inr(float $amount): string {
    $neg = $amount < 0;
    $num = (string) (int) round(abs($amount));
    if (strlen($num) > 3) {
        $last3 = substr($num, -3);
        $rest = substr($num, 0, -3);
        $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
        $num = $rest . ',' . $last3;
    }
    return ($neg ? '-' : '') . $num;
}

function dash_e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Short relative time ("5 min ago", "Yesterday", "18 Nov"); the exact timestamp is on hover.
function dash_when(?string $datetime): string {
    $ts = $datetime ? strtotime($datetime) : false;
    if (!$ts) {
        return '—';
    }
    $diff = time() - $ts;
    if ($diff < 60) {
        $short = 'Just now';
    } elseif ($diff < 3600) {
        $short = (int) floor($diff / 60) . ' min ago';
    } elseif (date('Y-m-d', $ts) === date('Y-m-d')) {
        $short = (int) floor($diff / 3600) . ' h ago';
    } elseif (date('Y-m-d', $ts) === date('Y-m-d', strtotime('yesterday'))) {
        $short = 'Yesterday';
    } elseif (date('Y', $ts) === date('Y')) {
        $short = date('d M', $ts);
    } else {
        $short = date('d M Y', $ts);
    }
    return '<span title="' . date('d M Y, g:i A', $ts) . '">' . $short . '</span>';
}

// Applicant name, or a muted placeholder for drafts saved before the name step.
function dash_name($name): string {
    $name = trim((string) $name);
    if ($name === '' || strtoupper($name) === 'N/A') {
        return '<span style="color: var(--text-muted); font-weight: 500;">Name not provided</span>';
    }
    return dash_e($name);
}

// 🧮 Gather Metrics
$totalApplications = get_table_count($conn, 'loan_applications'); // Payday
$pendingApplications = get_table_count($conn, 'loan_applications', "status = 'submitted'");

$totalMasterLoans = get_table_count($conn, 'loan_applied_master');
$totalEdiLoans = get_table_count($conn, 'edi_loan_applications');
$totalLandingLeads = get_table_count($conn, 'loan_leads');
$totalCibilChecks = get_table_count($conn, 'cibil_reports');
$totalMessages = get_table_count($conn, 'contact_messages');
$totalPartners = get_table_count($conn, 'partner_registrations');
$totalGeneralLoans = get_table_count($conn, 'apply_loan_applications');

// Payday status counts for the pipeline
$statusCounts = [
    'draft' => get_table_count($conn, 'loan_applications', "status = 'draft'"),
    'submitted' => get_table_count($conn, 'loan_applications', "status = 'submitted'"),
    'review' => get_table_count($conn, 'loan_applications', "status = 'review'"),
    'approved' => get_table_count($conn, 'loan_applications', "status = 'approved'"),
    'rejected' => get_table_count($conn, 'loan_applications', "status = 'rejected'"),
    'disbursed' => get_table_count($conn, 'loan_applications', "status = 'disbursed'")
];

// Recent payday applications
$recentApps = [];
$appsRes = $conn->query("SELECT id, application_id, full_name, mobile, loan_amount, status, created_at FROM loan_applications ORDER BY id DESC LIMIT 5");
if ($appsRes) {
    $recentApps = $appsRes->fetch_all(MYSQLI_ASSOC);
}

// Recent general wizard applications
$recentGeneral = [];
$genRes = $conn->query("SELECT id, application_ref, full_name, mobile, loan_amount, loan_type, created_at FROM apply_loan_applications ORDER BY id DESC LIMIT 5");
if ($genRes) {
    $recentGeneral = $genRes->fetch_all(MYSQLI_ASSOC);
}

// Recent partner registrations
$recentPartners = [];
$partnersRes = $conn->query("SELECT full_name, email_or_mobile, status, created_at FROM partner_registrations ORDER BY id DESC LIMIT 5");
if ($partnersRes) {
    $recentPartners = $partnersRes->fetch_all(MYSQLI_ASSOC);
}

// Recent messages
$recentMsgs = [];
$msgsRes = $conn->query("SELECT name, phone, subject, created_at FROM contact_messages ORDER BY id DESC LIMIT 5");
if ($msgsRes) {
    $recentMsgs = $msgsRes->fetch_all(MYSQLI_ASSOC);
}

// Top cities across payday + general applications
$geoData = [];
$geoRes = $conn->query("
    SELECT COALESCE(NULLIF(TRIM(city), ''), 'Unknown') AS city_name, COUNT(*) AS count
    FROM (
        SELECT city FROM loan_applications WHERE city IS NOT NULL
        UNION ALL
        SELECT city FROM apply_loan_applications WHERE city IS NOT NULL
    ) AS combined
    GROUP BY city_name
    ORDER BY count DESC
    LIMIT 6
");
if ($geoRes) {
    $geoData = $geoRes->fetch_all(MYSQLI_ASSOC);
}

// Today's and yesterday's leads across all platforms
$todayLeads = 0;
$yesterdayLeads = 0;
$yesterdaySameTime = 0; // yesterday's leads up to the current time of day
$leadTables = ['loan_applications', 'apply_loan_applications', 'loan_applied_master', 'edi_loan_applications', 'loan_leads'];

foreach ($leadTables as $tbl) {
    $tRes = $conn->query("SELECT COUNT(*) FROM $tbl WHERE created_at >= CURDATE() AND created_at < CURDATE() + INTERVAL 1 DAY");
    if ($tRes) {
        $row = $tRes->fetch_row();
        $todayLeads += (int) ($row[0] ?? 0);
    }
    $yRes = $conn->query("SELECT COUNT(*), SUM(created_at <= NOW() - INTERVAL 1 DAY) FROM $tbl WHERE created_at >= CURDATE() - INTERVAL 1 DAY AND created_at < CURDATE()");
    if ($yRes) {
        $row = $yRes->fetch_row();
        $yesterdayLeads += (int) ($row[0] ?? 0);
        $yesterdaySameTime += (int) ($row[1] ?? 0);
    }
}

// Payday drafts are forms the customer started but never submitted; show them separately.
$todayDrafts = 0;
$draftRes = $conn->query("SELECT COUNT(*) FROM loan_applications WHERE status = 'draft' AND created_at >= CURDATE() AND created_at < CURDATE() + INTERVAL 1 DAY");
if ($draftRes) {
    $todayDrafts = (int) ($draftRes->fetch_row()[0] ?? 0);
}

// Trend: today so far against yesterday up to this same time, not against the whole of yesterday.
if ($yesterdaySameTime > 0) {
    $pct = round((($todayLeads - $yesterdaySameTime) / $yesterdaySameTime) * 100);
    if ($pct > 0) {
        $trend = ['tone' => 'up', 'icon' => 'fa-arrow-up', 'text' => '+' . $pct . '%'];
    } elseif ($pct < 0) {
        $trend = ['tone' => 'down', 'icon' => 'fa-arrow-down', 'text' => $pct . '%'];
    } else {
        $trend = ['tone' => 'flat', 'icon' => 'fa-minus', 'text' => '0%'];
    }
} elseif ($todayLeads > 0) {
    $trend = ['tone' => 'up', 'icon' => 'fa-arrow-up', 'text' => 'New'];
} else {
    $trend = ['tone' => 'flat', 'icon' => 'fa-minus', 'text' => 'No change'];
}

// All-time volume tiles (each links to where the records live)
$volumeTiles = [
    ['label' => 'Payday Loans', 'value' => $totalApplications, 'sub' => dash_inr($statusCounts['draft']) . ' incomplete drafts', 'icon' => 'fa-file-invoice-dollar', 'href' => 'applications.php?tab=payday'],
    ['label' => 'General Loans', 'value' => $totalGeneralLoans, 'sub' => 'Loan wizard applications', 'icon' => 'fa-wand-magic-sparkles', 'href' => 'applications.php?tab=wizard'],
    ['label' => 'Quick Loan Inquiries', 'value' => $totalMasterLoans, 'sub' => 'Personal, Business, LAP, Vehicle', 'icon' => 'fa-layer-group', 'href' => 'master-loans.php?tab=general'],
    ['label' => 'Shopkeeper EDI Loans', 'value' => $totalEdiLoans, 'sub' => 'Small business & shopkeepers', 'icon' => 'fa-shop', 'href' => 'master-loans.php?tab=edi'],
    ['label' => 'Website Quick Quotes', 'value' => $totalLandingLeads, 'sub' => 'Landing page lead forms', 'icon' => 'fa-list-check', 'href' => 'leads.php?tab=landing'],
    ['label' => 'CIBIL Checks', 'value' => $totalCibilChecks, 'sub' => 'Credit score inquiries', 'icon' => 'fa-gauge-high', 'href' => 'cibil.php'],
    ['label' => 'DSA Partners', 'value' => $totalPartners, 'sub' => 'Registered DSA agents', 'icon' => 'fa-handshake', 'href' => 'leads.php?tab=partners'],
    ['label' => 'Contact Messages', 'value' => $totalMessages, 'sub' => 'Website inbox', 'icon' => 'fa-envelope-open-text', 'href' => 'leads.php?tab=contact'],
];

$statusLabels = [
    'draft' => 'Draft',
    'submitted' => 'Submitted',
    'review' => 'In review',
    'approved' => 'Approved',
    'rejected' => 'Rejected',
    'disbursed' => 'Disbursed',
];
$statusMax = max(1, max($statusCounts));
$statusTotal = max(1, array_sum($statusCounts));
$geoMax = !empty($geoData) ? max(1, (int) max(array_column($geoData, 'count'))) : 1;
?>

<style>
    /* ---------- Dashboard layout ---------- */
    .dash-section-head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
        margin: 8px 0 12px;
    }
    .dash-section-head h2 {
        font-family: 'Outfit', sans-serif;
        font-size: 15px;
        font-weight: 600;
        color: var(--text-main);
    }
    .dash-section-head span { font-size: 12.5px; color: var(--text-muted); }

    .dash-grid { display: grid; gap: 16px; margin-bottom: 28px; }
    .dash-grid.kpis { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .dash-grid.tiles { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .dash-grid.two { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; margin-bottom: 20px; }

    /* ---------- KPI cards ---------- */
    .kpi {
        position: relative;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 18px 20px;
        box-shadow: var(--shadow-xs);
        text-decoration: none;
        color: inherit;
        display: flex;
        flex-direction: column;
        gap: 6px;
        transition: border-color 0.15s var(--ease), box-shadow 0.15s var(--ease);
    }
    a.kpi:hover { border-color: var(--border-strong); box-shadow: var(--shadow-sm); }
    .kpi.primary {
        background: linear-gradient(135deg, #18529c 0%, #0f3a73 100%);
        border-color: transparent;
        color: #ffffff;
    }
    .kpi-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .kpi-label {
        font-size: 12.5px;
        font-weight: 600;
        color: var(--text-muted);
    }
    .kpi.primary .kpi-label { color: rgba(255, 255, 255, 0.78); }
    .kpi-icon {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        background: var(--primary-soft);
        color: var(--primary);
        flex-shrink: 0;
    }
    .kpi.primary .kpi-icon { background: rgba(255, 255, 255, 0.14); color: #ffffff; }
    .kpi.alert .kpi-icon { background: #fef2f2; color: #dc2626; }
    .kpi.warn .kpi-icon { background: #fffbeb; color: #b45309; }
    .kpi-value-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .kpi-value {
        font-family: 'Outfit', sans-serif;
        font-size: 32px;
        font-weight: 700;
        line-height: 1.1;
        letter-spacing: -0.5px;
        font-variant-numeric: tabular-nums;
    }
    .kpi-sub { font-size: 12.5px; color: var(--text-muted); line-height: 1.45; }
    .kpi.primary .kpi-sub { color: rgba(255, 255, 255, 0.75); }
    .kpi-cta {
        margin-top: auto;
        padding-top: 4px;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--primary);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .kpi-cta i { font-size: 10px; transition: transform 0.15s var(--ease); }
    a.kpi:hover .kpi-cta i { transform: translateX(3px); }

    .trend-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }
    .trend-pill i { font-size: 10px; }
    .trend-pill.up { background: #dcfce7; color: #166534; }
    .trend-pill.down { background: #fee2e2; color: #991b1b; }
    .trend-pill.flat { background: rgba(255, 255, 255, 0.18); color: #ffffff; }
    .kpi:not(.primary) .trend-pill.flat { background: #f1f5f9; color: #475569; }

    /* ---------- Volume tiles ---------- */
    .tile {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        text-decoration: none;
        color: inherit;
        transition: border-color 0.15s var(--ease), box-shadow 0.15s var(--ease);
        min-width: 0;
    }
    .tile:hover { border-color: #b9cbe3; box-shadow: var(--shadow-sm); }
    .tile-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: var(--primary-soft);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .tile-body { min-width: 0; flex: 1; }
    .tile-label { font-size: 12.5px; font-weight: 600; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .tile-value {
        font-family: 'Outfit', sans-serif;
        font-size: 22px;
        font-weight: 700;
        line-height: 1.2;
        color: var(--text-main);
        font-variant-numeric: tabular-nums;
    }
    .tile-sub { font-size: 11.5px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .tile-arrow { color: #cbd5e1; font-size: 12px; transition: color 0.15s var(--ease), transform 0.15s var(--ease); }
    .tile:hover .tile-arrow { color: var(--primary); transform: translateX(2px); }

    /* ---------- Panels ---------- */
    .panel {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-xs);
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-color);
    }
    .panel-title {
        font-family: 'Outfit', sans-serif;
        font-size: 15px;
        font-weight: 600;
        color: var(--text-main);
    }
    .panel-hint { font-size: 12px; color: var(--text-muted); margin-top: 1px; }
    .panel-link {
        font-size: 12.5px;
        font-weight: 600;
        color: var(--primary);
        text-decoration: none;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 8px;
        transition: background-color 0.15s var(--ease);
    }
    .panel-link:hover { background: var(--primary-soft); }
    .panel-link i { font-size: 10px; }
    .panel-body { padding: 16px 20px 20px; }

    /* Ranked horizontal bars (one hue; the label carries identity) */
    .bar-list { display: flex; flex-direction: column; gap: 14px; }
    .bar-row-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 6px;
        font-size: 13px;
    }
    .bar-name { font-weight: 600; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .bar-num { font-weight: 600; color: var(--text-main); font-variant-numeric: tabular-nums; white-space: nowrap; }
    .bar-num small { font-weight: 500; color: var(--text-muted); margin-left: 6px; font-size: 12px; }
    .bar-track { height: 8px; background: #eef1f6; border-radius: 4px; overflow: hidden; }
    .bar-fill { height: 100%; background: #2a78d6; border-radius: 4px; min-width: 2px; }
    .bar-fill.zero { min-width: 0; }

    .pipeline-total {
        display: flex;
        align-items: baseline;
        gap: 8px;
        margin-bottom: 16px;
    }
    .pipeline-total strong {
        font-family: 'Outfit', sans-serif;
        font-size: 26px;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }
    .pipeline-total span { font-size: 12.5px; color: var(--text-muted); }

    /* Activity tables */
    .panel .admin-table th:first-child, .panel .admin-table td:first-child { padding-left: 20px; }
    .panel .admin-table th:last-child, .panel .admin-table td:last-child { padding-right: 20px; }
    .panel .admin-table th { background: var(--bg-subtle); }
    .panel .admin-table td { padding-top: 12px; padding-bottom: 12px; vertical-align: middle; }
    .panel .table-responsive { border-radius: 0 0 var(--radius-lg) var(--radius-lg); }
    .cell-main { font-weight: 600; color: var(--text-main); }
    .cell-sub { font-size: 12px; color: var(--text-muted); margin-top: 1px; }
    .cell-ref { font-size: 12.5px; font-weight: 600; color: var(--primary); text-decoration: none; font-variant-numeric: tabular-nums; }
    a.cell-ref:hover { text-decoration: underline; }
    .cell-amount { font-weight: 600; font-variant-numeric: tabular-nums; }
    .cell-time { font-size: 12.5px; color: var(--text-muted); }
    .cell-clip { max-width: 220px; overflow: hidden; text-overflow: ellipsis; }
    .chip {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 6px;
        background: #f1f5f9;
        color: var(--text-secondary);
        font-size: 12px;
        font-weight: 600;
    }
    .empty-state {
        text-align: center;
        color: var(--text-muted);
        padding: 36px 16px !important;
        white-space: normal !important;
    }
    .empty-state i { display: block; font-size: 20px; color: #cbd5e1; margin-bottom: 8px; }

    @media (max-width: 1280px) {
        .dash-grid.kpis, .dash-grid.tiles { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 1024px) {
        .dash-grid.two { grid-template-columns: minmax(0, 1fr); }
    }
    @media (max-width: 640px) {
        .dash-grid.kpis, .dash-grid.tiles { grid-template-columns: minmax(0, 1fr); }
        .dash-section-head { flex-wrap: wrap; }
        .panel-head { padding: 14px 16px; }
        .panel-body { padding: 14px 16px 16px; }
    }
</style>

<!-- ===== Today at a glance ===== -->
<div class="dash-grid kpis">
    <div class="kpi primary">
        <div class="kpi-top">
            <span class="kpi-label">Today's leads</span>
            <span class="kpi-icon"><i class="fa-solid fa-bolt"></i></span>
        </div>
        <div class="kpi-value-row">
            <span class="kpi-value"><?php echo dash_inr($todayLeads); ?></span>
            <span class="trend-pill <?php echo $trend['tone']; ?>" title="Compared with leads received yesterday up to <?php echo date('g:i A'); ?>">
                <i class="fa-solid <?php echo $trend['icon']; ?>"></i><?php echo $trend['text']; ?>
            </span>
        </div>
        <div class="kpi-sub">
            vs <?php echo dash_inr($yesterdaySameTime); ?> by <?php echo date('g:i A'); ?> yesterday<?php if ($todayDrafts > 0): ?> · incl. <?php echo dash_inr($todayDrafts); ?> payday draft<?php echo $todayDrafts === 1 ? '' : 's'; ?><?php endif; ?>
        </div>
    </div>

    <div class="kpi">
        <div class="kpi-top">
            <span class="kpi-label">Yesterday's leads</span>
            <span class="kpi-icon"><i class="fa-solid fa-calendar-day"></i></span>
        </div>
        <div class="kpi-value-row">
            <span class="kpi-value"><?php echo dash_inr($yesterdayLeads); ?></span>
        </div>
        <div class="kpi-sub">Full day · <?php echo date('D, d M', strtotime('yesterday')); ?></div>
    </div>

    <a class="kpi <?php echo $pendingApplications > 0 ? 'alert' : ''; ?>" href="applications.php?tab=payday&amp;status=submitted">
        <div class="kpi-top">
            <span class="kpi-label">Payday awaiting review</span>
            <span class="kpi-icon"><i class="fa-solid fa-hourglass-half"></i></span>
        </div>
        <div class="kpi-value-row">
            <span class="kpi-value"><?php echo dash_inr($pendingApplications); ?></span>
        </div>
        <div class="kpi-sub">Submitted applications not yet actioned</div>
        <span class="kpi-cta">Review queue <i class="fa-solid fa-arrow-right"></i></span>
    </a>

    <a class="kpi <?php echo $pendingPartnersCount > 0 ? 'warn' : ''; ?>" href="leads.php?tab=partners">
        <div class="kpi-top">
            <span class="kpi-label">DSA awaiting approval</span>
            <span class="kpi-icon"><i class="fa-solid fa-user-clock"></i></span>
        </div>
        <div class="kpi-value-row">
            <span class="kpi-value"><?php echo dash_inr($pendingPartnersCount); ?></span>
        </div>
        <div class="kpi-sub">Partner registrations pending</div>
        <span class="kpi-cta">Open partners <i class="fa-solid fa-arrow-right"></i></span>
    </a>
</div>

<!-- ===== All-time volume ===== -->
<div class="dash-section-head">
    <h2>All-time volume</h2>
    <span>Total records by channel · click a tile to open it</span>
</div>
<div class="dash-grid tiles">
    <?php foreach ($volumeTiles as $tile): ?>
        <a class="tile" href="<?php echo dash_e($tile['href']); ?>">
            <span class="tile-icon"><i class="fa-solid <?php echo $tile['icon']; ?>"></i></span>
            <span class="tile-body">
                <span class="tile-label" style="display:block;"><?php echo dash_e($tile['label']); ?></span>
                <span class="tile-value" style="display:block;"><?php echo dash_inr((float) $tile['value']); ?></span>
                <span class="tile-sub" style="display:block;"><?php echo dash_e($tile['sub']); ?></span>
            </span>
            <i class="fa-solid fa-chevron-right tile-arrow"></i>
        </a>
    <?php endforeach; ?>
</div>

<!-- ===== Pipeline & geography ===== -->
<div class="dash-grid two">
    <section class="panel">
        <div class="panel-head">
            <div>
                <div class="panel-title">Payday loan pipeline</div>
                <div class="panel-hint">Applications by current status</div>
            </div>
            <a class="panel-link" href="applications.php?tab=payday">Open <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="panel-body">
            <div class="pipeline-total">
                <strong><?php echo dash_inr($totalApplications); ?></strong>
                <span>payday applications in total</span>
            </div>
            <div class="bar-list">
                <?php foreach ($statusLabels as $key => $label):
                    $count = (int) $statusCounts[$key];
                    $width = round(($count / $statusMax) * 100, 1);
                    $share = round(($count / $statusTotal) * 100, 1);
                ?>
                    <div title="<?php echo $label . ': ' . dash_inr($count) . ' (' . $share . '% of all)'; ?>">
                        <div class="bar-row-top">
                            <span class="badge-status badge-<?php echo $key; ?>"><?php echo $label; ?></span>
                            <span class="bar-num"><?php echo dash_inr($count); ?><small><?php echo $share; ?>%</small></span>
                        </div>
                        <div class="bar-track"><div class="bar-fill<?php echo $count === 0 ? ' zero' : ''; ?>" style="width: <?php echo $width; ?>%;"></div></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <div>
                <div class="panel-title">Top cities</div>
                <div class="panel-hint">Payday + general applications by city</div>
            </div>
        </div>
        <div class="panel-body">
            <?php if (empty($geoData)): ?>
                <div class="empty-state"><i class="fa-solid fa-map-location-dot"></i>No city data yet.</div>
            <?php else: ?>
                <div class="bar-list">
                    <?php foreach ($geoData as $geo):
                        $count = (int) $geo['count'];
                        $width = round(($count / $geoMax) * 100, 1);
                        $city = ucwords(strtolower((string) $geo['city_name']));
                    ?>
                        <div title="<?php echo dash_e($city) . ': ' . dash_inr($count) . ' applications'; ?>">
                            <div class="bar-row-top">
                                <span class="bar-name"><?php echo dash_e($city); ?></span>
                                <span class="bar-num"><?php echo dash_inr($count); ?></span>
                            </div>
                            <div class="bar-track"><div class="bar-fill" style="width: <?php echo $width; ?>%;"></div></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- ===== Recent activity ===== -->
<div class="dash-section-head" style="margin-top: 8px;">
    <h2>Recent activity</h2>
    <span>Latest 5 in each channel</span>
</div>

<div class="dash-grid two">
    <!-- Recent Payday Applications -->
    <section class="panel">
        <div class="panel-head">
            <div class="panel-title">Payday applications</div>
            <a class="panel-link" href="applications.php?tab=payday">View all <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Received</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentApps)): ?>
                        <tr><td colspan="4" class="empty-state"><i class="fa-solid fa-inbox"></i>No applications yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentApps as $app):
                            $status = (string) ($app['status'] ?? 'draft');
                            $statusKey = array_key_exists($status, $statusLabels) ? $status : 'draft';
                        ?>
                            <tr>
                                <td>
                                    <div class="cell-main"><?php echo dash_name($app['full_name']); ?></div>
                                    <div class="cell-sub">
                                        <a class="cell-ref" href="view-application.php?id=<?php echo (int) $app['id']; ?>&amp;type=payday"><?php echo dash_e($app['application_id']); ?></a>
                                        <?php if (!empty($app['mobile'])): ?> · <?php echo dash_e($app['mobile']); ?><?php endif; ?>
                                    </div>
                                </td>
                                <td class="cell-amount"><?php echo is_numeric($app['loan_amount']) ? '₹' . dash_inr((float) $app['loan_amount']) : '—'; ?></td>
                                <td><span class="badge-status badge-<?php echo $statusKey; ?>"><?php echo dash_e($statusLabels[$status] ?? $status); ?></span></td>
                                <td class="cell-time"><?php echo dash_when($app['created_at'] ?? null); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Recent General Wizard Applications -->
    <section class="panel">
        <div class="panel-head">
            <div class="panel-title">General loan applications</div>
            <a class="panel-link" href="applications.php?tab=wizard">View all <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Amount</th>
                        <th>Loan type</th>
                        <th>Received</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentGeneral)): ?>
                        <tr><td colspan="4" class="empty-state"><i class="fa-solid fa-inbox"></i>No general applications yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentGeneral as $app): ?>
                            <tr>
                                <td>
                                    <div class="cell-main"><?php echo dash_name($app['full_name']); ?></div>
                                    <div class="cell-sub">
                                        <a class="cell-ref" href="view-application.php?id=<?php echo (int) $app['id']; ?>&amp;type=wizard"><?php echo dash_e($app['application_ref']); ?></a>
                                        <?php if (!empty($app['mobile'])): ?> · <?php echo dash_e($app['mobile']); ?><?php endif; ?>
                                    </div>
                                </td>
                                <td class="cell-amount"><?php echo is_numeric($app['loan_amount']) ? '₹' . dash_inr((float) $app['loan_amount']) : dash_e($app['loan_amount'] ?: '—'); ?></td>
                                <td><span class="chip"><?php echo dash_e($app['loan_type'] ?: 'N/A'); ?></span></td>
                                <td class="cell-time"><?php echo dash_when($app['created_at'] ?? null); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<div class="dash-grid two">
    <!-- Recent DSA Registrations -->
    <section class="panel">
        <div class="panel-head">
            <div class="panel-title">New DSA partners</div>
            <a class="panel-link" href="leads.php?tab=partners">View all <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Partner</th>
                        <th>Status</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentPartners)): ?>
                        <tr><td colspan="3" class="empty-state"><i class="fa-solid fa-handshake"></i>No partner registrations yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentPartners as $partner):
                            $pStatus = strtolower(trim((string) ($partner['status'] ?? ''))) ?: 'pending';
                            $pBadge = $pStatus === 'approved' ? 'approved' : ($pStatus === 'rejected' ? 'rejected' : 'review');
                        ?>
                            <tr>
                                <td>
                                    <div class="cell-main"><?php echo dash_name($partner['full_name']); ?></div>
                                    <div class="cell-sub"><?php echo dash_e($partner['email_or_mobile']); ?></div>
                                </td>
                                <td><span class="badge-status badge-<?php echo $pBadge; ?>"><?php echo dash_e($pStatus); ?></span></td>
                                <td class="cell-time"><?php echo dash_when($partner['created_at'] ?? null); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Recent Support Inquiries -->
    <section class="panel">
        <div class="panel-head">
            <div class="panel-title">Contact messages</div>
            <a class="panel-link" href="leads.php?tab=contact">View all <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Contact</th>
                        <th>Subject</th>
                        <th>Received</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentMsgs)): ?>
                        <tr><td colspan="3" class="empty-state"><i class="fa-solid fa-envelope-open"></i>No messages yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentMsgs as $msg): ?>
                            <tr>
                                <td>
                                    <div class="cell-main"><?php echo dash_name($msg['name']); ?></div>
                                    <div class="cell-sub"><?php echo dash_e($msg['phone']); ?></div>
                                </td>
                                <td><div class="cell-clip" title="<?php echo dash_e($msg['subject']); ?>"><?php echo dash_e($msg['subject'] ?: '—'); ?></div></td>
                                <td class="cell-time"><?php echo dash_when($msg['created_at'] ?? null); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php include 'footer.php'; ?>
