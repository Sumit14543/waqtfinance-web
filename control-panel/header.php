<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

start_secure_session();

// Initialize CSRF token so it is available globally in $_SESSION
csrf_token();

// Auth check (idle sessions are signed out)
if (admin_session_idle_expired()) {
    header("Location: login.php?reason=timeout");
    exit;
}
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}



admin_require_capability('admin.access');
header('Cache-Control: no-store, private, max-age=0');

$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminRole = $_SESSION['admin_role'] ?? 'admin';
$currentPage = basename($_SERVER['PHP_SELF']);

$conn = db();
$stmtPayday = $conn->query("SELECT COUNT(*) FROM loan_applications WHERE status = 'submitted'");
$pendingPaydayCount = $stmtPayday ? (int) $stmtPayday->fetch_row()[0] : 0;

$stmtPartners = $conn->query("SELECT COUNT(*) FROM partner_registrations WHERE status = 'pending' OR status IS NULL OR status = ''");
$pendingPartnersCount = $stmtPartners ? (int) $stmtPartners->fetch_row()[0] : 0;

// Initials for the avatar chip (e.g. "Waqt Admin" -> "WA").
$adminInitials = '';
foreach (preg_split('/\s+/', trim((string) $adminName)) ?: [] as $namePart) {
    if ($namePart !== '' && strlen($adminInitials) < 2) {
        $adminInitials .= strtoupper(substr($namePart, 0, 1));
    }
}
if ($adminInitials === '') {
    $adminInitials = 'A';
}

// Sidebar navigation, grouped by purpose. 'match' lists every page that should highlight the item.
$adminNavGroups = [
    'Overview' => [
        ['href' => 'dashboard.php', 'icon' => 'fa-chart-pie', 'label' => 'Dashboard', 'match' => ['dashboard.php']],
    ],
    'Applications' => [
        ['href' => 'applications.php', 'icon' => 'fa-file-invoice-dollar', 'label' => 'Payday & Legacy', 'match' => ['applications.php', 'view-application.php', 'edit-application.php'], 'badge' => $pendingPaydayCount, 'badgeTone' => 'red', 'badgeTitle' => 'submitted, awaiting review'],
        ['href' => 'master-loans.php', 'icon' => 'fa-hand-holding-dollar', 'label' => 'Business & Personal', 'match' => ['master-loans.php']],
    ],
    'Leads & Credit' => [
        ['href' => 'leads.php', 'icon' => 'fa-user-tag', 'label' => 'Inquiries & Quotes', 'match' => ['leads.php'], 'badge' => $pendingPartnersCount, 'badgeTone' => 'amber', 'badgeTitle' => 'DSA partners awaiting approval'],
        ['href' => 'cibil.php', 'icon' => 'fa-gauge-high', 'label' => 'CIBIL Reports', 'match' => ['cibil.php']],
    ],
    'Content' => [
        ['href' => 'blogs.php', 'icon' => 'fa-newspaper', 'label' => 'Blog CMS', 'match' => ['blogs.php']],
        ['href' => 'locations.php', 'icon' => 'fa-map-location-dot', 'label' => 'Location CMS', 'match' => ['locations.php', 'location-products.php']],
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Control Center'; ?> | Waqt Finance Admin</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* ==========================================================
           Design tokens
           ========================================================== */
        :root {
            --bg-dark: #f4f6fa;           /* app canvas (name kept for existing pages) */
            --bg-card: #ffffff;
            --bg-subtle: #f8fafc;
            --border-color: #e5e9f0;
            --border-strong: #d5dbe5;
            --primary: #18529c;           /* Brand Blue */
            --primary-hover: #134485;
            --primary-soft: #eef4fb;
            --accent: #dc2626;            /* Brand Red */
            --primary-glow: rgba(24, 82, 156, 0.12);
            --text-main: #0f172a;
            --text-secondary: #475569;
            --text-muted: #64748b;
            --success: #15803d;
            --warning: #b45309;
            --danger: #dc2626;
            --info: #2563eb;

            --sidebar-bg: #0b1729;
            --sidebar-text: rgba(226, 232, 240, 0.72);
            --sidebar-muted: rgba(148, 163, 184, 0.55);
            --sidebar-width: 264px;
            --topbar-height: 68px;

            --radius-sm: 8px;
            --radius: 12px;
            --radius-lg: 16px;
            --shadow-xs: 0 1px 2px rgba(15, 23, 42, 0.04);
            --shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.06), 0 1px 2px rgba(15, 23, 42, 0.03);
            --shadow-md: 0 6px 16px -4px rgba(15, 23, 42, 0.10);
            --ease: cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', 'Outfit', system-ui, sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            min-height: 100vh;
            color: var(--text-main);
            display: flex;
            overflow-x: hidden;
            font-size: 14px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        /* Keyboard focus is always visible; mouse clicks stay clean. */
        a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }

        .text-muted { color: var(--text-muted); }
        .text-center { text-align: center; }

        /* ==========================================================
           Sidebar
           ========================================================== */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
            padding: 18px 14px;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 8px 18px;
            margin-bottom: 6px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            text-decoration: none;
        }

        .sidebar-brand img {
            height: 34px;
            width: 34px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .brand-icon { display: none; }

        .brand-text {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.3px;
            white-space: nowrap;
        }

        .brand-text small {
            display: block;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 10.5px;
            font-weight: 600;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: var(--sidebar-muted);
            margin-top: -1px;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            margin: 0 -6px;
            padding: 0 6px;
            scrollbar-width: none;
        }
        .sidebar-nav::-webkit-scrollbar { display: none; }

        .nav-group-label {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.9px;
            text-transform: uppercase;
            color: var(--sidebar-muted);
            padding: 18px 12px 6px;
            white-space: nowrap;
        }

        .sidebar-menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .menu-link {
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
            height: 40px;
            padding: 0 12px;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 500;
            white-space: nowrap;
            transition: background-color 0.15s var(--ease), color 0.15s var(--ease);
        }

        .menu-link > span:not(.menu-badge) {
            overflow: hidden;
            text-overflow: ellipsis;
            flex: 1;
            min-width: 0;
        }

        .menu-link:not(.active):hover {
            color: #ffffff;
            background: transparent;
            box-shadow: inset 2px 0 #64748b;
        }

        .menu-link.active {
            color: #ffffff !important;
            font-weight: 600;
            background: rgba(96, 165, 250, 0.14) !important;
        }

        .menu-link.active::before {
            content: '';
            position: absolute;
            left: -14px;
            top: 9px;
            bottom: 9px;
            width: 3px;
            border-radius: 0 3px 3px 0;
            background: #60a5fa;
        }

        .menu-link.active i { color: #93c5fd !important; }

        .menu-link i {
            font-size: 15px;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }

        .menu-badge {
            margin-left: auto;
            flex-shrink: 0;
            min-width: 22px;
            height: 20px;
            padding: 0 7px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            line-height: 20px;
            text-align: center;
            color: #ffffff;
            font-variant-numeric: tabular-nums;
        }
        .menu-badge.red { background: #dc2626; }
        .menu-badge.amber { background: #d97706; }

        .menu-link .ext-icon {
            font-size: 10px;
            width: auto;
            opacity: 0.6;
            margin-left: auto;
        }

        .sidebar-footer {
            margin-top: 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            padding-top: 14px;
        }

        .user-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px;
            border-radius: var(--radius);
            background: rgba(255, 255, 255, 0.03);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            flex-shrink: 0;
            background: linear-gradient(135deg, #2563eb, #18529c);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .user-details {
            overflow: hidden;
            flex: 1;
            min-width: 0;
        }

        .user-name {
            font-size: 13.5px;
            font-weight: 600;
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
            color: #ffffff;
            line-height: 1.3;
        }

        .user-role {
            font-size: 11px;
            color: var(--sidebar-muted);
            text-transform: capitalize;
            display: block;
        }

        .logout-btn {
            width: 34px;
            height: 34px;
            flex-shrink: 0;
            border-radius: 9px;
            border: none;
            background: transparent;
            color: var(--sidebar-text);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            transition: background-color 0.15s var(--ease), color 0.15s var(--ease);
        }
        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.14);
            color: #fca5a5;
        }

        /* Desktop collapse toggle, sits on the sidebar's right edge */
        .sidebar-toggle-btn {
            position: absolute;
            right: -12px;
            top: 26px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #ffffff;
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            z-index: 110;
            box-shadow: var(--shadow-sm);
            transition: color 0.15s var(--ease), border-color 0.15s var(--ease);
        }

        .sidebar-toggle-btn:hover {
            color: var(--primary);
            border-color: var(--primary);
        }

        /* ==========================================================
           Main panel & top bar
           ========================================================== */
        .main-panel {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 0 32px 40px 32px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .top-bar {
            position: sticky;
            top: 0;
            z-index: 98;
            min-height: var(--topbar-height);
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: saturate(180%) blur(10px);
            -webkit-backdrop-filter: saturate(180%) blur(10px);
            margin: 0 -32px 28px -32px;
            padding: 12px 32px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 24px;
        }

        .page-header {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .page-header h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: var(--text-main);
            line-height: 1.25;
            letter-spacing: -0.2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .page-header p {
            font-size: 12.5px;
            color: var(--text-muted);
            margin-top: 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .mobile-toggle-btn {
            display: none;
            width: 38px;
            height: 38px;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-main);
            font-size: 16px;
            cursor: pointer;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            justify-content: flex-end;
            min-width: 0;
        }

        .global-search-container {
            position: relative;
            flex: 1;
            max-width: 380px;
        }

        .global-search-container form { display: flex; width: 100%; }

        .global-search-input {
            width: 100%;
            height: 40px;
            padding: 0 44px 0 38px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-size: 13.5px;
            outline: none;
            background: var(--bg-subtle);
            color: var(--text-main);
            transition: border-color 0.15s var(--ease), background-color 0.15s var(--ease), box-shadow 0.15s var(--ease);
        }

        .global-search-input::placeholder { color: #94a3b8; }

        .global-search-input:focus {
            border-color: var(--primary);
            background: #ffffff;
            box-shadow: 0 0 0 3px var(--primary-glow);
        }
        .global-search-input:focus-visible { outline: none; }

        .global-search-container .search-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 13px;
            pointer-events: none;
        }

        .global-search-container kbd {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-family: inherit;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-bottom-width: 2px;
            border-radius: 5px;
            padding: 0 6px;
            line-height: 18px;
            pointer-events: none;
        }
        .global-search-input:focus + .search-icon + kbd { display: none; }

        .time-box {
            display: flex;
            align-items: center;
            gap: 8px;
            height: 40px;
            padding: 0 14px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-size: 13px;
            color: var(--text-secondary);
            white-space: nowrap;
            flex-shrink: 0;
        }
        .time-box i { color: var(--primary); font-size: 13px; }
        .time-box .time-date { color: var(--text-muted); }
        .time-box .time-clock { font-weight: 600; color: var(--text-main); font-variant-numeric: tabular-nums; }

        /* ==========================================================
           Shared components used by every admin page
           ========================================================== */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            box-shadow: var(--shadow-xs);
            transition: border-color 0.15s var(--ease), box-shadow 0.15s var(--ease);
        }

        .stat-card:hover {
            border-color: var(--border-strong);
            box-shadow: var(--shadow-sm);
        }

        .stat-val {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--text-main);
            margin-top: 4px;
            line-height: 1.2;
            font-variant-numeric: tabular-nums;
        }

        .stat-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-icon-wrapper {
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            background: var(--primary-soft) !important;
            color: var(--primary) !important;
        }

        .data-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow-xs);
            margin-bottom: 24px;
        }

        .card-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .card-title-text {
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 600;
            color: var(--text-main);
        }

        /* Page numbers wrap onto a second line instead of pushing the page sideways. */
        .main-panel ul.pagination { flex-wrap: wrap; row-gap: 6px; max-width: 100%; }

        /* Wide data tables scroll inside their card on smaller screens instead of widening the page. */
        @media (max-width: 1100px) {
            .main-panel table.admin-table { display: block; max-width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .main-panel .table-responsive table.admin-table { display: table; overflow: visible; }
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border-radius: 10px;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }
        .table-responsive::-webkit-scrollbar { height: 6px; width: 6px; }
        .table-responsive::-webkit-scrollbar-track { background: transparent; }
        .table-responsive::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .table-responsive::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .admin-table th {
            padding: 11px 16px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-subtle);
            white-space: nowrap;
        }

        .admin-table td {
            padding: 14px 16px;
            font-size: 13.5px;
            color: var(--text-main);
            border-bottom: 1px solid #f1f4f8;
            white-space: nowrap;
        }

        .admin-table tbody tr:last-child td { border-bottom: none; }

        .admin-table tr:hover td { background: #f9fbfd; }

        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 600;
            text-transform: capitalize;
            line-height: 1.5;
            border: 1px solid transparent;
        }
        .badge-status::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        /* Text colours are dark enough to read on white (WCAG AA). */
        .badge-draft { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }
        .badge-submitted { background: #eff6ff; color: #1d4ed8; border-color: #dbeafe; }
        .badge-review { background: #fffbeb; color: #b45309; border-color: #fde68a; }
        .badge-approved { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
        .badge-rejected { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
        .badge-disbursed { background: #f0fdfa; color: #0f766e; border-color: #99f6e4; }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid var(--border-strong);
            background: #ffffff;
            color: var(--text-secondary);
            cursor: pointer;
            transition: border-color 0.15s var(--ease), color 0.15s var(--ease), background-color 0.15s var(--ease);
            text-decoration: none;
        }

        .btn-action:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-soft);
        }

        /* Filter controls */
        .filter-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
            background: #ffffff;
            padding: 14px 16px;
            border-radius: var(--radius);
            border: 1px solid var(--border-color);
            align-items: center;
        }

        .search-input,
        .select-filter {
            height: 38px;
            padding: 0 12px;
            border: 1px solid var(--border-strong);
            background: #ffffff;
            border-radius: var(--radius-sm);
            font-size: 13px;
            color: var(--text-main);
            outline: none;
            transition: border-color 0.15s var(--ease), box-shadow 0.15s var(--ease);
        }

        .search-input { min-width: 220px; }
        .select-filter { cursor: pointer; }

        .search-input:focus,
        .select-filter:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        .btn-primary-adm {
            padding: 9px 18px;
            background: var(--primary);
            border: 1px solid var(--primary);
            border-radius: var(--radius-sm);
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: var(--shadow-xs);
            transition: background-color 0.15s var(--ease), border-color 0.15s var(--ease);
            text-decoration: none;
        }

        .btn-primary-adm:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        /* ==========================================================
           Collapsed sidebar (desktop only)
           ========================================================== */
        @media (min-width: 769px) {
            .sidebar { transition: width 0.25s var(--ease); }
            .main-panel { transition: margin-left 0.25s var(--ease); }

            body.sidebar-collapsed { --sidebar-width: 72px; }

            body.sidebar-collapsed .sidebar { padding-left: 12px; padding-right: 12px; }
            body.sidebar-collapsed .brand-text,
            body.sidebar-collapsed .menu-link > span,
            body.sidebar-collapsed .user-details,
            body.sidebar-collapsed .menu-link .ext-icon { display: none; }

            body.sidebar-collapsed .sidebar-brand { justify-content: center; padding-left: 0; padding-right: 0; }

            body.sidebar-collapsed .nav-group-label {
                font-size: 0;
                padding: 10px 0;
            }
            body.sidebar-collapsed .nav-group-label::after {
                content: '';
                display: block;
                height: 1px;
                margin: 0 10px;
                background: rgba(255, 255, 255, 0.07);
            }

            body.sidebar-collapsed .sidebar-nav { overflow: visible; }

            body.sidebar-collapsed .menu-link { justify-content: center; padding: 0; }
            body.sidebar-collapsed .menu-link i { font-size: 16px; }
            body.sidebar-collapsed .menu-link.active::before { left: -12px; }

            /* Pending counts become a dot on the icon. */
            body.sidebar-collapsed .menu-link .menu-badge {
                display: block;
                position: absolute;
                top: 7px;
                right: 9px;
                min-width: 0;
                width: 8px;
                height: 8px;
                padding: 0;
                font-size: 0;
                box-shadow: 0 0 0 2px var(--sidebar-bg);
            }

            /* Tooltip with the page name. */
            body.sidebar-collapsed .menu-link[data-tip]:hover::after {
                content: attr(data-tip);
                position: absolute;
                left: calc(100% + 14px);
                top: 50%;
                transform: translateY(-50%);
                background: #0f172a;
                color: #ffffff;
                font-size: 12px;
                font-weight: 600;
                padding: 6px 10px;
                border-radius: 6px;
                white-space: nowrap;
                box-shadow: var(--shadow-md);
                pointer-events: none;
                z-index: 120;
            }

            body.sidebar-collapsed .user-badge {
                flex-direction: column;
                background: transparent;
                padding: 0;
                gap: 8px;
            }
        }

        /* ==========================================================
           Mobile
           ========================================================== */
        .sidebar-overlay-mobile {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 99;
        }
        .sidebar-overlay-mobile.active { display: block; }

        @media (max-width: 1180px) {
            .time-box .time-date { display: none; }
        }

        @media (max-width: 960px) {
            .time-box { display: none; }
        }

        @media (max-width: 768px) {
            .sidebar {
                left: calc(-1 * var(--sidebar-width));
                transition: left 0.25s var(--ease);
                box-shadow: 10px 0 30px rgba(0, 0, 0, 0.15);
            }
            .sidebar.active { left: 0; }
            .main-panel {
                margin-left: 0;
                padding: 0 16px 24px 16px;
            }
            .mobile-toggle-btn { display: inline-flex !important; }
            .sidebar-toggle-btn { display: none !important; }
            .top-bar {
                margin: 0 -16px 20px -16px;
                padding: 12px 16px;
                flex-wrap: wrap;
                gap: 12px;
            }
            .top-actions { flex-basis: 100%; }
            .global-search-container { max-width: none; }
            .global-search-container kbd { display: none; }
            .stat-grid { grid-template-columns: 1fr !important; }
            .data-card { padding: 18px; }
            .drawer-content {
                max-width: 100% !important;
                padding: 24px !important;
            }
            .detail-grid { grid-template-columns: 1fr !important; }
        }

        @media (max-width: 576px) {
            .filter-row {
                flex-direction: column;
                align-items: stretch;
            }
            .filter-row .search-input,
            .filter-row .select-filter,
            .filter-row button,
            .filter-row a {
                width: 100% !important;
                text-align: center;
            }
        }

        /* ==========================================================
           Loading states
           ========================================================== */
        .page-loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 99999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 14px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }
        .page-loading-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 255, 255, 0.25);
            border-left-color: #ffffff;
            border-radius: 50%;
            animation: spin-global-loader 0.8s linear infinite;
        }
        .loading-text {
            font-weight: 600;
            font-size: 13px;
            color: #ffffff;
            letter-spacing: 0.3px;
        }
        @keyframes spin-global-loader {
            to { transform: rotate(360deg); }
        }

        .table-loading-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }
        .table-loading-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }
        .suspense-spinner {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            color: var(--primary);
        }
        .suspense-spinner i { font-size: 26px; }
        .suspense-spinner span {
            font-weight: 600;
            font-size: 12px;
            letter-spacing: 0.3px;
        }

        /* Consistent backdrop for every modal / drawer */
        .tox-dialog-wrap,
        .tox-dialog-wrap__backdrop,
        body:not(.swal2-toast-shown) .swal2-container.swal2-backdrop-show,
        .sidebar-overlay-mobile,
        .drawer-overlay {
            background: rgba(15, 23, 42, 0.5) !important;
            backdrop-filter: blur(4px) !important;
            -webkit-backdrop-filter: blur(4px) !important;
        }

        /* Toasts: small, non-blocking notices. No backdrop, the page stays usable. */
        body.swal2-toast-shown .swal2-container {
            background: transparent !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            pointer-events: none;
            padding: calc(var(--topbar-height, 64px) + 8px) 20px 20px !important;
            width: auto !important;
            height: auto !important;
            bottom: auto !important;
        }
        body.swal2-toast-shown .swal2-container .swal2-popup.swal2-toast {
            pointer-events: auto;
            display: flex !important;
            align-items: center;
            gap: 10px;
            width: 380px;
            max-width: calc(100vw - 32px);
            padding: 12px 14px 12px 16px !important;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--toast-accent, var(--primary));
            border-radius: var(--radius, 10px);
            box-shadow: 0 10px 30px -8px rgba(15, 23, 42, 0.25), 0 2px 6px rgba(15, 23, 42, 0.06) !important;
            overflow: hidden;
        }
        .swal2-popup.swal2-toast.toast-success { --toast-accent: #16a34a; }
        .swal2-popup.swal2-toast.toast-error { --toast-accent: #dc2626; }
        .swal2-popup.swal2-toast.toast-warning { --toast-accent: #d97706; }
        .swal2-popup.swal2-toast.toast-info { --toast-accent: var(--primary); }
        .swal2-popup.swal2-toast .swal2-icon {
            order: 1;
            flex-shrink: 0;
            width: 20px !important;
            height: 20px !important;
            min-width: 20px;
            margin: 0 !important;
            border: none !important;
            color: var(--toast-accent, var(--primary)) !important;
        }
        .swal2-popup.swal2-toast .swal2-icon .swal2-icon-content { font-size: 18px !important; }
        .swal2-popup.swal2-toast .swal2-title { order: 2; }
        .swal2-popup.swal2-toast .swal2-actions { order: 3; }
        .swal2-popup.swal2-toast .swal2-close { order: 4; }
        .swal2-popup.swal2-toast .swal2-title {
            flex: 1 1 auto;
            margin: 0 !important;
            padding: 0 !important;
            font-family: inherit;
            font-size: 13.5px !important;
            font-weight: 600 !important;
            line-height: 1.4;
            color: var(--text-main);
            text-align: left;
        }
        .swal2-popup.swal2-toast .swal2-actions {
            flex-shrink: 0;
            margin: 0 !important;
            padding: 0 !important;
            width: auto;
        }
        .swal2-popup.swal2-toast .swal2-actions .swal2-confirm {
            margin: 0 !important;
            padding: 5px 12px !important;
            font-size: 12.5px !important;
            font-weight: 700;
            border-radius: 7px !important;
            background: var(--primary-soft) !important;
            color: var(--primary) !important;
            box-shadow: none !important;
        }
        .swal2-popup.swal2-toast .swal2-actions .swal2-confirm:hover { background: #dbe7f6 !important; }
        .swal2-popup.swal2-toast .swal2-close {
            position: static !important;
            flex-shrink: 0;
            width: 26px; height: 26px;
            margin: 0 !important;
            font-size: 20px !important;
            color: #94a3b8 !important;
            border-radius: 6px;
        }
        .swal2-popup.swal2-toast .swal2-close:hover { color: var(--text-main) !important; background: #f1f5f9 !important; }
        .swal2-popup.swal2-toast .swal2-timer-progress-bar-container { left: 0; right: 0; bottom: 0; height: 3px; }
        .swal2-popup.swal2-toast .swal2-timer-progress-bar { height: 3px; background: var(--toast-accent, var(--primary)); opacity: 0.55; }
        @media (max-width: 768px) {
            body.swal2-toast-shown .swal2-container { padding: 12px !important; left: 0 !important; right: 0 !important; }
            body.swal2-toast-shown .swal2-container .swal2-popup.swal2-toast { width: 100%; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition-duration: 0.01ms !important; animation-duration: 0.01ms !important; }
        }
    </style>
</head>
<body>
    <script>
        // Apply the saved collapsed state before first paint so the sidebar does not flash open.
        try {
            if (localStorage.getItem('admin_sidebar_collapsed') === 'true') {
                document.body.classList.add('sidebar-collapsed');
            }
        } catch (e) {}
    </script>

    <!-- Sidebar Navigation -->
    <nav class="sidebar" id="adminSidebar" aria-label="Admin navigation">
        <button type="button" class="sidebar-toggle-btn" id="desktopSidebarToggle" title="Collapse sidebar" aria-label="Toggle sidebar">
            <i class="fa-solid fa-chevron-left"></i>
        </button>

        <a href="dashboard.php" class="sidebar-brand">
            <img src="../assets/images/logo1.webp" alt="Waqt Finance">
            <span class="brand-text">Waqt Finance<small>Control Panel</small></span>
        </a>

        <div class="sidebar-nav">
            <?php foreach ($adminNavGroups as $groupLabel => $items): ?>
                <div class="nav-group-label"><?php echo htmlspecialchars($groupLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                <ul class="sidebar-menu">
                    <?php foreach ($items as $item):
                        $isActive = in_array($currentPage, $item['match'], true);
                        $label = htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8');
                        $badge = (int) ($item['badge'] ?? 0);
                    ?>
                        <li>
                            <a href="<?php echo $item['href']; ?>" class="menu-link<?php echo $isActive ? ' active' : ''; ?>" data-tip="<?php echo $label; ?>"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                                <i class="fa-solid <?php echo $item['icon']; ?>"></i>
                                <span><?php echo $label; ?></span>
                                <?php if ($badge > 0): ?>
                                    <span class="menu-badge <?php echo $item['badgeTone']; ?>" title="<?php echo $badge . ' ' . htmlspecialchars($item['badgeTitle'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo $badge > 999 ? '999+' : $badge; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>

            <div class="nav-group-label">Website</div>
            <ul class="sidebar-menu">
                <li>
                    <a href="../" target="_blank" rel="noopener" class="menu-link" data-tip="View Website" title="Open the public website in a new tab">
                        <i class="fa-solid fa-globe"></i>
                        <span>View Website</span>
                        <i class="fa-solid fa-arrow-up-right-from-square ext-icon"></i>
                    </a>
                </li>
            </ul>
        </div>

        <div class="sidebar-footer">
            <div class="user-badge">
                <div class="user-avatar" title="<?php echo htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($adminInitials, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8'); ?></div>
                    <span class="user-role"><?php echo htmlspecialchars($adminRole, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <button type="button" class="logout-btn" onclick="confirmAdminLogout()" title="Log out" aria-label="Log out">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay-mobile" id="sidebarOverlayMobile"></div>

    <!-- Main Content Panel -->
    <main class="main-panel">
        <!-- Top Bar -->
        <header class="top-bar">
            <div class="page-header">
                <button type="button" class="mobile-toggle-btn" id="mobileSidebarToggle" aria-label="Open menu" aria-controls="adminSidebar" aria-expanded="false">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div style="min-width: 0;">
                    <h1><?php echo $pageTitle ?? 'Control Center'; ?></h1>
                    <p><?php echo $pageSubtitle ?? 'Waqt Finance Administration Panel'; ?></p>
                </div>
            </div>

            <div class="top-actions">
                <!-- Global Search -->
                <div class="global-search-container">
                    <form action="search-results.php" method="GET" role="search">
                        <input type="search" name="q" id="globalSearchInput" class="global-search-input" placeholder="Search name, mobile, PAN..." aria-label="Search applications" autocomplete="off" value="<?php echo htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <kbd title="Press / to search">/</kbd>
                    </form>
                </div>

                <div class="time-box" title="System time (live)">
                    <i class="fa-regular fa-clock"></i>
                    <span class="time-date" id="adminLiveDate"><?php echo date('D, d M Y'); ?></span>
                    <span class="time-clock" id="adminLiveTime"><?php echo date('h:i A'); ?></span>
                </div>
            </div>
        </header>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Live clock: date + hh:mm. Minutes are enough for an operations panel; a ticking second hand is just noise.
                const dateEl = document.getElementById('adminLiveDate');
                const timeEl = document.getElementById('adminLiveTime');
                const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                function updateClock() {
                    const now = new Date();
                    let h = now.getHours();
                    const ampm = h >= 12 ? 'PM' : 'AM';
                    h = h % 12 || 12;
                    if (timeEl) timeEl.textContent = String(h).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0') + ' ' + ampm;
                    if (dateEl) dateEl.textContent = days[now.getDay()] + ', ' + String(now.getDate()).padStart(2, '0') + ' ' + months[now.getMonth()] + ' ' + now.getFullYear();
                }
                updateClock();
                setInterval(updateClock, 15000);

                // Press "/" anywhere (outside a text field) to jump to global search.
                const searchInput = document.getElementById('globalSearchInput');
                document.addEventListener('keydown', function(e) {
                    if (e.key !== '/' || e.ctrlKey || e.metaKey || e.altKey) return;
                    const el = document.activeElement;
                    const tag = el ? el.tagName : '';
                    if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || (el && el.isContentEditable)) return;
                    if (searchInput) {
                        e.preventDefault();
                        searchInput.focus();
                        searchInput.select();
                    }
                });

                // Mobile sidebar
                const mobileToggleBtn = document.getElementById('mobileSidebarToggle');
                const sidebar = document.querySelector('.sidebar');
                const overlay = document.getElementById('sidebarOverlayMobile');
                if (mobileToggleBtn && sidebar && overlay) {
                    mobileToggleBtn.addEventListener('click', function() {
                        sidebar.classList.toggle('active');
                        overlay.classList.toggle('active');
                        mobileToggleBtn.setAttribute('aria-expanded', String(sidebar.classList.contains('active')));
                    });
                    document.addEventListener('keydown', e => { if (e.key === 'Escape' && sidebar.classList.contains('active')) { sidebar.classList.remove('active'); overlay.classList.remove('active'); mobileToggleBtn.setAttribute('aria-expanded', 'false'); mobileToggleBtn.focus(); } });
                    overlay.addEventListener('click', function() {
                        sidebar.classList.remove('active');
                        overlay.classList.remove('active');
                        mobileToggleBtn.setAttribute('aria-expanded', 'false');
                    });
                }

                // Desktop collapse toggle (state remembered per browser)
                const desktopToggleBtn = document.getElementById('desktopSidebarToggle');
                const toggleIcon = desktopToggleBtn ? desktopToggleBtn.querySelector('i') : null;
                function syncToggle() {
                    const collapsed = document.body.classList.contains('sidebar-collapsed');
                    if (toggleIcon) toggleIcon.className = collapsed ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left';
                    if (desktopToggleBtn) { desktopToggleBtn.title = collapsed ? 'Expand sidebar' : 'Collapse sidebar'; desktopToggleBtn.setAttribute('aria-expanded', String(!collapsed)); desktopToggleBtn.setAttribute('aria-controls', 'adminSidebar'); }
                }
                syncToggle();
                if (desktopToggleBtn) {
                    desktopToggleBtn.addEventListener('click', function() {
                        document.body.classList.toggle('sidebar-collapsed');
                        try {
                            localStorage.setItem('admin_sidebar_collapsed', document.body.classList.contains('sidebar-collapsed'));
                        } catch (e) {}
                        syncToggle();
                    });
                }
            });
        </script>
