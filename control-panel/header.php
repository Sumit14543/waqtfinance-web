<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

start_secure_session();

// Initialize CSRF token so it is available globally in $_SESSION
csrf_token();

// Auth check
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}



$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminRole = $_SESSION['admin_role'] ?? 'admin';
$currentPage = basename($_SERVER['PHP_SELF']);

$conn = db();
$stmtPayday = $conn->query("SELECT COUNT(*) FROM loan_applications WHERE status = 'submitted'");
$pendingPaydayCount = $stmtPayday ? (int) $stmtPayday->fetch_row()[0] : 0;

$stmtPartners = $conn->query("SELECT COUNT(*) FROM partner_registrations WHERE status = 'pending' OR status IS NULL OR status = ''");
$pendingPartnersCount = $stmtPartners ? (int) $stmtPartners->fetch_row()[0] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Control Center'; ?> | Waqt Finance Admin</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --bg-dark: #f8fafc;
            --bg-card: #ffffff;
            --border-color: #e2e8f0;
            --primary: #18529c; /* Brand Blue */
            --accent: #dc2626;  /* Brand Red */
            --primary-glow: rgba(24, 82, 156, 0.08);
            --text-main: #0f172a;
            --text-muted: #64748b;
            --success: #16a34a;
            --warning: #ca8a04;
            --danger: #dc2626;
            --info: #2563eb;
            --sidebar-width: 260px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            min-height: 100vh;
            color: var(--text-main);
            display: flex;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            background: #0c1a30; /* Dark Navy Blue matching brand logo */
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
            padding: 24px;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 35px;
            text-decoration: none;
        }

        .brand-icon {
            display: none; /* Removed for custom image rendering in brand link */
        }

        .brand-text {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.5px;
        }

        .sidebar-menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
        }

        .menu-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            color: rgba(255, 255, 255, 0.65);
            text-decoration: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body:not(.sidebar-collapsed) .menu-link:hover {
            transform: translateX(6px);
        }

        .menu-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.06);
        }

        .menu-link.active {
            color: #ffffff !important;
            font-weight: 600;
            background: rgba(24, 82, 156, 0.18) !important;
            border-left: none;
        }

        .menu-link.active i {
            color: #60a5fa !important;
            text-shadow: 0 0 8px rgba(96, 165, 250, 0.3);
        }

        .menu-link i {
            font-size: 16px;
            width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            margin-top: auto;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 20px;
        }

        .user-badge {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 12px;
            border-radius: 14px;
            transition: all 0.25s ease;
        }

        .user-badge:hover {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.08);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #60a5fa;
            border: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 16px;
        }

        .user-details {
            overflow: hidden;
        }

        .user-name {
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
            color: #ffffff;
        }

        .user-role {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: rgba(255, 184, 0, 0.08);
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 2px;
        }

        /* Main Content Panel */
        .main-panel {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 0 40px 40px 40px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        /* Top Bar */
        .top-bar {
            position: sticky;
            top: 0;
            z-index: 98;
            background: rgba(248, 250, 252, 0.9);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            margin: 0 -40px 35px -40px;
            padding: 20px 40px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--text-main);
        }

        .page-header p {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .time-box {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 8px 16px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
        }

        /* 🕰️ Real-Time Analog Watch Icon Styling */
        .analog-watch {
            width: 24px !important;
            height: 24px !important;
            border: 2px solid #18529c !important;
            border-radius: 50% !important;
            position: relative !important;
            background: #ffffff !important;
            box-sizing: border-box !important;
            flex-shrink: 0 !important;
            display: inline-block !important;
            margin: 0 4px 0 0 !important;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.1), 0 1px 3px rgba(0,0,0,0.08) !important;
        }
        .analog-watch .marker {
            position: absolute !important;
            background: #94a3b8 !important;
            z-index: 0 !important;
        }
        .analog-watch .m-12 {
            width: 1.5px !important;
            height: 3px !important;
            top: 1px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            background: #18529c !important;
        }
        .analog-watch .m-6 {
            width: 1.5px !important;
            height: 3px !important;
            bottom: 1px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
        }
        .analog-watch .m-3 {
            width: 3px !important;
            height: 1.5px !important;
            right: 1px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
        }
        .analog-watch .m-9 {
            width: 3px !important;
            height: 1.5px !important;
            left: 1px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
        }
        .analog-watch .hand {
            position: absolute !important;
            bottom: 50% !important;
            left: 50% !important;
            transform-origin: bottom center !important;
            background: #0b213a !important;
            border-radius: 1px !important;
            transform: rotate(0deg);
        }
        .analog-watch .hour-hand {
            width: 1.6px !important;
            height: 6px !important;
            margin-left: -0.8px !important;
            z-index: 3 !important;
        }
        .analog-watch .minute-hand {
            width: 1.2px !important;
            height: 8.5px !important;
            margin-left: -0.6px !important;
            background: #475569 !important;
            z-index: 2 !important;
        }
        .analog-watch .second-hand {
            width: 0.8px !important;
            height: 9.5px !important;
            margin-left: -0.4px !important;
            background: #dc2626 !important; /* Waqt logo red! */
            z-index: 1 !important;
        }
        .analog-watch .watch-center {
            width: 4.5px !important;
            height: 4.5px !important;
            background: #18529c !important;
            border-radius: 50% !important;
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            z-index: 4 !important;
        }

        /* Utility Card styles for Admin Panel */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 35px;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 18px;
            padding: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }

        .stat-card:hover {
            border-color: rgba(24, 82, 156, 0.2);
            transform: translateY(-2px);
        }

        .stat-val {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--text-main);
            margin-top: 5px;
        }

        .stat-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            background: rgba(24, 82, 156, 0.08) !important;
            color: var(--primary) !important;
        }

        /* Tables & Lists */
        .data-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .card-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .card-title-text {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 600;
            color: var(--text-main);
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border-radius: 10px;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
        }


        .table-responsive::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }
        .table-responsive::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .admin-table th {
            padding: 14px 16px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text-muted);
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
            white-space: nowrap;
        }

        .admin-table td {
            padding: 16px;
            font-size: 13.5px;
            color: var(--text-main);
            border-bottom: 1px solid #f1f5f9;
            white-space: nowrap;
        }

        .admin-table tr:hover td {
            background: #f8fafc;
        }

        .badge-status {
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-draft { background: rgba(156, 163, 175, 0.1); color: #9ca3af; border: 1px solid rgba(156, 163, 175, 0.2); }
        .badge-submitted { background: rgba(6, 182, 212, 0.1); color: #22d3ee; border: 1px solid rgba(6, 182, 212, 0.2); }
        .badge-review { background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2); }
        .badge-approved { background: rgba(34, 197, 94, 0.1); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.2); }
        .badge-rejected { background: rgba(239, 68, 68, 0.1); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.2); }
        .badge-disbursed { background: rgba(59, 130, 246, 0.1); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.2); }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: var(--text-main);
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-action:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(24, 82, 156, 0.05);
        }

        /* Filter Controls */
        .filter-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 25px;
            background: #ffffff;
            padding: 16px 20px;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            align-items: center;
        }

        .search-input {
            padding: 8px 14px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            border-radius: 8px;
            font-size: 13px;
            color: var(--text-main);
            outline: none;
            min-width: 220px;
            transition: all 0.3s;
        }

        .search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        .select-filter {
            padding: 8px 14px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            border-radius: 8px;
            font-size: 13px;
            color: var(--text-main);
            outline: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .select-filter:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        /* Button styles */
        .btn-primary-adm {
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--primary), #1e5fae);
            border: none;
            border-radius: 8px;
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px var(--primary-glow);
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-primary-adm:hover {
            transform: translateY(-1px);
            background: linear-gradient(135deg, #1d5cb0, var(--primary));
            box-shadow: 0 6px 16px rgba(24, 82, 156, 0.2);
        }

        /* Desktop Collapsed Sidebar CSS */
        body.sidebar-collapsed {
            --sidebar-width: 76px;
        }

        body.sidebar-collapsed .brand-text {
            display: none;
        }

        body.sidebar-collapsed .sidebar-brand {
            justify-content: center;
            margin-bottom: 30px;
        }
        
        body.sidebar-collapsed .sidebar-brand img {
            margin-left: 0 !important;
        }

        body.sidebar-collapsed .menu-link {
            justify-content: center;
            padding: 12px;
            font-size: 0;
            position: relative;
        }

        body.sidebar-collapsed .menu-link i {
            font-size: 18px;
            margin: 0;
        }

        body.sidebar-collapsed .menu-link span {
            display: none;
        }

        body.sidebar-collapsed .sidebar-footer {
            padding-top: 15px;
        }

        body.sidebar-collapsed .user-badge {
            justify-content: center;
            border: none;
            background: transparent;
            padding: 0;
        }

        body.sidebar-collapsed .user-details {
            display: none;
        }

        body.sidebar-collapsed .sidebar-footer .menu-link {
            padding: 12px;
            font-size: 0;
        }

        /* Sidebar Logo Micro-interaction */
        .sidebar-brand img {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar-brand:hover img {
            transform: rotate(8deg) scale(1.08);
        }

        /* Desktop Circular Toggle Button (Centered on right border) */
        .sidebar-toggle-btn {
            position: absolute;
            right: -13px;
            top: 30px;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: #0c1a30;
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: rgba(255, 255, 255, 0.8);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 110;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            outline: none;
        }
        
        .sidebar-toggle-btn:hover {
            background: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 0 10px rgba(24, 82, 156, 0.5);
            transform: scale(1.1);
        }

        /* Responsive sidebar transition on desktop */
        @media (min-width: 769px) {
            .sidebar {
                transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .main-panel {
                transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
        }

        /* Mobile Overlay for Sidebar */
        .sidebar-overlay-mobile {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(2px);
            z-index: 99;
            transition: opacity 0.3s ease;
        }
        .sidebar-overlay-mobile.active {
            display: block;
        }

        /* Mobile Viewport Adjustments */
        @media (max-width: 768px) {
            .sidebar {
                left: calc(-1 * var(--sidebar-width));
                transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 10px 0 30px rgba(0, 0, 0, 0.15);
            }
            .sidebar.active {
                left: 0;
            }
            .main-panel {
                margin-left: 0;
                padding: 0 20px 20px 20px;
            }
            .mobile-toggle-btn {
                display: inline-flex !important;
            }
            .sidebar-toggle-btn {
                display: none !important;
            }
            .top-bar {
                margin: 0 -20px 25px -20px;
                padding: 15px 20px;
                flex-wrap: wrap;
                gap: 15px;
            }
            .time-box {
                display: none;
            }
            .stat-grid {
                grid-template-columns: 1fr !important;
            }
            /* Fix drawers on mobile */
            .drawer-content {
                max-width: 100% !important;
                padding: 24px !important;
            }
            .detail-grid {
                grid-template-columns: 1fr !important;
            }
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
        /* 🌌 Global Page Loading Overlay styling */
        .page-loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            z-index: 99999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 15px;
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
            border: 4px solid rgba(255, 255, 255, 0.25);
            border-left-color: var(--primary);
            border-radius: 50%;
            animation: spin-global-loader 1s linear infinite;
        }
        .loading-text {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 14px;
            color: #ffffff;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        @keyframes spin-global-loader {
            to { transform: rotate(360deg); }
        }

        /* 🌌 Table Suspense Loader Overlay styling */
        .table-loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
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
            gap: 12px;
            color: var(--primary);
        }
        .suspense-spinner i {
            font-size: 32px;
        }
        .suspense-spinner span {
            font-weight: 700;
            font-size: 12px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* 🌌 Global Overlay Styles for consistent premium glassmorphism blur */
        .tox-dialog-wrap,
        .tox-dialog-wrap__backdrop,
        .swal2-container.swal2-backdrop-show,
        .sidebar-overlay-mobile,
        .drawer-overlay {
            background: rgba(15, 23, 42, 0.65) !important;
            backdrop-filter: blur(10px) !important;
            -webkit-backdrop-filter: blur(10px) !important;
        }
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <button type="button" class="sidebar-toggle-btn" id="desktopSidebarToggle" title="Toggle Sidebar">
            <i class="fa-solid fa-angles-left"></i>
        </button>

        <a href="dashboard.php" class="sidebar-brand">
            <img src="../assets/images/logo1.webp" alt="Waqt Finance Logo" style="height: 38px; border-radius: 50%; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
            <span class="brand-text" style="color: #ffffff; font-weight: 700; font-family: 'Outfit'; font-size: 18px; margin-left: 10px;">Waqt Finance</span>
        </a>

        <ul class="sidebar-menu">
            <li>
                <a href="dashboard.php" class="menu-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-chart-pie"></i> <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="applications.php" class="menu-link <?php echo in_array($currentPage, ['applications.php', 'view-application.php', 'edit-application.php'], true) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-file-invoice-dollar"></i> <span>Payday & Legacy Loans</span>
                    <?php if ($pendingPaydayCount > 0): ?>
                        <span style="background: #ef4444; color: #fff; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 10px; margin-left: auto; line-height: 1; min-width: 18px; text-align: center;"><?php echo $pendingPaydayCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="master-loans.php" class="menu-link <?php echo $currentPage === 'master-loans.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-hand-holding-dollar"></i> <span>Business & Personal Loans</span>
                </a>
            </li>
            <li>
                <a href="leads.php" class="menu-link <?php echo $currentPage === 'leads.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-user-tag"></i> <span>Inquiries & Quotes</span>
                    <?php if ($pendingPartnersCount > 0): ?>
                        <span style="background: #f59e0b; color: #fff; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 10px; margin-left: auto; line-height: 1; min-width: 18px; text-align: center;"><?php echo $pendingPartnersCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="cibil.php" class="menu-link <?php echo $currentPage === 'cibil.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-gauge-high"></i> <span>CIBIL Reports</span>
                </a>
            </li>
            <li>
                <a href="blogs.php" class="menu-link <?php echo $currentPage === 'blogs.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-newspaper"></i> <span>Blog CMS</span>
                </a>
            </li>
            <li>
                <a href="locations.php" class="menu-link <?php echo $currentPage === 'locations.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-map-location-dot"></i> <span>Location CMS</span>
                </a>
            </li>
            <li style="margin-top: 15px;">
                <a href="../" target="_blank" class="menu-link">
                    <i class="fa-solid fa-globe"></i> <span>View Website <i class="fa-solid fa-up-right-from-square" style="font-size: 10px; margin-left: 2px;"></i></span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <div class="user-badge">
                <div class="user-avatar">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8'); ?></div>
                    <span class="user-role"><?php echo htmlspecialchars($adminRole, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>
            <a href="javascript:void(0)" onclick="confirmAdminLogout()" class="menu-link" style="color: #fca5a5; background: rgba(239, 68, 68, 0.05);">
                <i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span>
            </a>
        </div>
    </nav>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay-mobile" id="sidebarOverlayMobile"></div>

    <!-- Main Content Panel -->
    <main class="main-panel">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="page-header" style="display: flex; align-items: center; gap: 15px;">
                <button type="button" class="mobile-toggle-btn" id="mobileSidebarToggle" style="display: none; background: none; border: 1px solid var(--border-color); padding: 8px 12px; border-radius: 8px; color: var(--text-main); font-size: 18px; cursor: pointer; transition: all 0.3s; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div>
                    <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); line-height: 1.2;"><?php echo $pageTitle ?? 'Control Center'; ?></h1>
                    <p style="margin-top: 2px;"><?php echo $pageSubtitle ?? 'Waqt Finance Administration Panel'; ?></p>
                </div>
            </div>
            
            <!-- Global Search Bar -->
            <div class="global-search-container" style="flex: 1; max-width: 400px; margin: 0 30px; position: relative;">
                <form action="search-results.php" method="GET" style="display: flex; width: 100%;">
                    <input type="text" name="q" placeholder="Search name, mobile, PAN..." value="<?php echo htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width: 100%; padding: 8px 16px 8px 38px; border: 1px solid var(--border-color); border-radius: 20px; font-size: 13.5px; outline: none; background: var(--bg-dark); transition: all 0.3s; color: var(--text-main);" onfocus="this.style.borderColor='var(--primary)'; this.style.background='#fff';" onblur="this.style.borderColor='var(--border-color)'; this.style.background='var(--bg-dark)';" />
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 13px; pointer-events: none;"></i>
                </form>
            </div>

            <div class="time-box" style="padding: 6px 14px;">
                <div class="analog-watch" id="adminAnalogWatch" title="System Time (Live)">
                    <!-- Dial Markers -->
                    <div class="marker m-12"></div>
                    <div class="marker m-3"></div>
                    <div class="marker m-6"></div>
                    <div class="marker m-9"></div>
                    
                    <div class="hand hour-hand" id="adminHourHand"></div>
                    <div class="hand minute-hand" id="adminMinHand"></div>
                    <div class="hand second-hand" id="adminSecHand"></div>
                    <div class="watch-center"></div>
                </div>
                <span id="adminLiveTime" style="font-weight: 600; color: var(--text-main); font-variant-numeric: tabular-nums;"><?php echo date('d M Y, h:i:s A'); ?></span>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const hourHand = document.getElementById('adminHourHand');
                const minHand = document.getElementById('adminMinHand');
                const secHand = document.getElementById('adminSecHand');
                const liveTimeText = document.getElementById('adminLiveTime');

                function updateClock() {
                    const now = new Date();
                    
                    // 1. Update Analog Watch Hands
                    const secs = now.getSeconds();
                    const mins = now.getMinutes();
                    const hours = now.getHours();

                    // Calculate rotation angles
                    const secDeg = (secs / 60) * 360;
                    const minDeg = ((mins / 60) * 360) + ((secs / 60) * 6);
                    const hourDeg = ((hours % 12) / 12 * 360) + ((mins / 60) * 30);

                    if (secHand) secHand.style.transform = `rotate(${secDeg}deg)`;
                    if (minHand) minHand.style.transform = `rotate(${minDeg}deg)`;
                    if (hourHand) hourHand.style.transform = `rotate(${hourDeg}deg)`;

                    // 2. Update Digital Live Text
                    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                    const day = String(now.getDate()).padStart(2, '0');
                    const month = monthNames[now.getMonth()];
                    const year = now.getFullYear();
                    
                    let hours12 = now.getHours();
                    const ampm = hours12 >= 12 ? 'PM' : 'AM';
                    hours12 = hours12 % 12;
                    hours12 = hours12 ? hours12 : 12; // the hour '0' should be '12'
                    const hoursStr = String(hours12).padStart(2, '0');
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    const seconds = String(now.getSeconds()).padStart(2, '0');

                    // Format like "28 Jun 2026, 02:07:15 PM"
                    const formatted = `${day} ${month} ${year}, ${hoursStr}:${minutes}:${seconds} ${ampm}`;
                    
                    if (liveTimeText) {
                        liveTimeText.textContent = formatted;
                    }
                }

                // Initial run
                updateClock();
                // Update every second
                setInterval(updateClock, 1000);
            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Mobile Sidebar Toggle
                const mobileToggleBtn = document.getElementById('mobileSidebarToggle');
                const sidebar = document.querySelector('.sidebar');
                const overlay = document.getElementById('sidebarOverlayMobile');

                if (mobileToggleBtn && sidebar && overlay) {
                    mobileToggleBtn.addEventListener('click', function() {
                        sidebar.classList.toggle('active');
                        overlay.classList.toggle('active');
                    });

                    overlay.addEventListener('click', function() {
                        sidebar.classList.remove('active');
                        overlay.classList.remove('active');
                    });
                }

                // Desktop Sidebar Toggle
                const desktopToggleBtn = document.getElementById('desktopSidebarToggle');
                const toggleIcon = desktopToggleBtn ? desktopToggleBtn.querySelector('i') : null;
                
                // Retrieve state from localStorage
                if (localStorage.getItem('admin_sidebar_collapsed') === 'true') {
                    document.body.classList.add('sidebar-collapsed');
                    if (toggleIcon) {
                        toggleIcon.className = 'fa-solid fa-angles-right';
                    }
                }

                if (desktopToggleBtn) {
                    desktopToggleBtn.addEventListener('click', function() {
                        document.body.classList.toggle('sidebar-collapsed');
                        const isCollapsed = document.body.classList.contains('sidebar-collapsed');
                        localStorage.setItem('admin_sidebar_collapsed', isCollapsed);
                        
                        if (toggleIcon) {
                            toggleIcon.className = isCollapsed ? 'fa-solid fa-angles-right' : 'fa-solid fa-angles-left';
                        }
                    });
                }
            });
        </script>
