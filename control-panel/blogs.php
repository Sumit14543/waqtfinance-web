<?php
declare(strict_types=1);

$pageTitle = 'Blog CMS Control';
$pageSubtitle = 'Create, update, and manage dynamic articles for the website';

include 'header.php';

$conn = db();

// Pagination Config
$limit = 10;
$page = max(1, (int) ($_GET['page'] ?? 1));

// Stats (one query)
$statsRes = $conn->query("SELECT COUNT(*) AS total, SUM(status = 'published') AS active, SUM(status <> 'published') AS inactive, COALESCE(SUM(views), 0) AS views FROM blogs");
$statsRow = $statsRes ? ($statsRes->fetch_assoc() ?: []) : [];
$totalRecords = (int) ($statsRow['total'] ?? 0);
$activeRecords = (int) ($statsRow['active'] ?? 0);
$inactiveRecords = (int) ($statsRow['inactive'] ?? 0);
$totalViews = (int) ($statsRow['views'] ?? 0);

$totalPages = max(1, (int) ceil($totalRecords / $limit));
$page = min($page, $totalPages);
$offset = ($page - 1) * $limit;

// First page of the list: only the columns the table shows (the editor loads one full article on demand).
$blogs = [];
$stmt = $conn->prepare("SELECT id, title, slug, category, read_time, image_url, status, views, author_name, created_at, updated_at FROM blogs ORDER BY id DESC LIMIT ? OFFSET ?");
$stmt->bind_param('ii', $limit, $offset);
$stmt->execute();
$res = $stmt->get_result();
if ($res) {
    $blogs = $res->fetch_all(MYSQLI_ASSOC);
}

$initialBlogData = [
    'records' => $blogs,
    'page' => $page,
    'limit' => $limit,
    'total_records' => $totalRecords,
    'total_pages' => $totalPages,
    'stats' => ['total' => $totalRecords, 'active' => $activeRecords, 'inactive' => $inactiveRecords, 'views' => $totalViews],
];
?>

<!-- TinyMCE Rich Text Editor CDN -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>

<style>
    /* TinyMCE Editor Customizations */
    .tox-tinymce {
        border: 1px solid #cbd5e1 !important;
        border-radius: 10px !important;
    }

    /* Form Styles */
    .form-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
    }
    @media (max-width: 900px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--text-muted);
        margin-bottom: 8px;
    }
    .form-control-adm {
        width: 100%;
        padding: 10px 14px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        color: #0f172a;
        font-size: 13.5px;
        outline: none;
        transition: all 0.3s;
    }
    .form-control-adm:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-glow);
    }
    textarea.form-control-adm {
        resize: vertical;
        min-height: 80px;
    }
    .field-support-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 6px;
        flex-wrap: wrap;
    }
    .slug-preview {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        max-width: 100%;
        padding: 8px 10px;
        border: 1px solid #dbe4ef;
        border-radius: 8px;
        background: #f8fafc;
        color: var(--text-muted);
        font-size: 12px;
        font-weight: 600;
        line-height: 1.3;
    }
    .slug-preview i {
        color: var(--primary);
    }
    .slug-preview strong {
        color: var(--primary);
        word-break: break-word;
    }
    .content-metrics {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 10px;
        flex-wrap: wrap;
    }
    .content-metric {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 30px;
        padding: 6px 10px;
        border: 1px solid #dbe4ef;
        border-radius: 8px;
        background: #f8fafc;
        color: var(--text-muted);
        font-size: 12px;
        font-weight: 700;
    }
    .content-metric i {
        color: var(--primary);
    }
    .form-sidebar {
        align-self: start;
        position: sticky;
        top: 20px;
    }
    .publish-readiness-panel {
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px solid var(--border-color);
    }
    .readiness-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }
    .readiness-title {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--text-main);
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .readiness-title i {
        color: var(--primary);
    }
    .readiness-score {
        padding: 4px 9px;
        border-radius: 50px;
        background: rgba(24, 82, 156, 0.08);
        color: var(--primary);
        font-size: 11px;
        font-weight: 800;
    }
    .readiness-list {
        display: grid;
        gap: 8px;
    }
    .readiness-item {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 8px 10px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #ffffff;
        color: var(--text-muted);
        font-size: 12px;
        font-weight: 700;
        transition: all 0.2s ease;
    }
    .readiness-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #64748b;
        font-size: 10px;
        flex: 0 0 auto;
    }
    .readiness-item.is-ready {
        color: #166534;
        border-color: rgba(34, 197, 94, 0.22);
        background: rgba(34, 197, 94, 0.06);
    }
    .readiness-item.is-ready .readiness-icon {
        background: #22c55e;
        color: #ffffff;
    }
    .form-actions-bar {
        position: sticky;
        bottom: 0;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 30px;
        padding: 16px 0 0;
        border-top: 1px solid var(--border-color);
        background: linear-gradient(180deg, rgba(255,255,255,0.92), #ffffff 35%);
    }
    
    .panel-card {
        transition: all 0.4s ease;
    }
    .hidden {
        display: none !important;
    }
    
    /* Blog thumbnail preview */
    .blog-thumb {
        width: 60px;
        height: 40px;
        border-radius: 6px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
    }
    .row-actions {
        display: flex;
        align-items: center;
        gap: 6px;
        min-width: 114px;
    }
    .status-toggle-btn.is-active {
        border-color: rgba(34, 197, 94, 0.25);
        background: rgba(34, 197, 94, 0.08);
        color: #16a34a;
    }
    .status-toggle-btn.is-inactive {
        border-color: rgba(100, 116, 139, 0.25);
        background: #f8fafc;
        color: #64748b;
    }
    .status-toggle-btn:disabled {
        cursor: wait;
        opacity: 0.65;
    }
    .delete-btn {
        border-color: rgba(239, 68, 68, 0.2);
        color: #ef4444;
    }
    .delete-btn:hover {
        border-color: rgba(239, 68, 68, 0.35);
        background: rgba(239, 68, 68, 0.06);
        color: #dc2626;
    }
    .preview-img-container {
        width: 100%;
        height: 150px;
        border-radius: 10px;
        border: 1px dashed rgba(24, 82, 156, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #f8fafc;
        margin-top: 10px;
    }
    .preview-img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
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

    /* CMS Stats Dashboard Grid */
    .cms-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 190px), 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }
    @media (max-width: 900px) {
        .cms-stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 480px) {
        .cms-stats-grid {
            grid-template-columns: 1fr;
        }
    }
    .cms-stat-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.01);
        transition: all 0.3s;
    }
    .cms-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
    }
    .cms-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .stat-blue { background: rgba(24, 82, 156, 0.08); color: var(--primary); }
    .stat-green { background: rgba(22, 163, 74, 0.08); color: var(--success); }
    .stat-yellow { background: rgba(202, 138, 4, 0.08); color: var(--warning); }
    .stat-red { background: rgba(220, 38, 38, 0.08); color: var(--danger); }
    
    .cms-stat-info h3 {
        font-size: 22px;
        font-weight: 700;
        color: var(--text-main);
        line-height: 1.2;
    }
    .cms-stat-info p {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 2px;
    }

    /* FAQ rows styling */
    .dynamic-row-item {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
        align-items: flex-start;
    }
    .dynamic-row-item input, .dynamic-row-item textarea {
        background: #ffffff !important;
    }
    .btn-remove-row {
        background: #fee2e2;
        color: #ef4444;
        border: 1px solid #fca5a5;
        border-radius: 6px;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    .btn-remove-row:hover {
        background: #fca5a5;
        color: #b91c1c;
    }

    /* Settings Tabs */
    .tabs-nav {
        display: flex;
        gap: 8px;
        border-bottom: 2px solid var(--border-color);
        margin-bottom: 20px;
        padding-bottom: 2px;
    }
    .tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: none;
        border: none;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 700;
        color: var(--text-muted);
        cursor: pointer;
        border-bottom: 2px solid transparent;
        margin-bottom: -4px;
        transition: all 0.3s;
        outline: none;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .tab-btn:hover {
        color: var(--primary);
    }
    .tab-btn.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }
    @media (max-width: 900px) {
        .form-sidebar {
            position: static;
        }
    }
    @media (max-width: 640px) {
        .tabs-nav {
            overflow-x: auto;
        }
        .tab-btn {
            flex: 0 0 auto;
            padding: 10px 12px;
            white-space: nowrap;
        }
        .form-actions-bar {
            align-items: stretch;
            flex-wrap: wrap;
        }
        .form-actions-bar #formFeedback {
            width: 100%;
        }
    }

    /* Drag & Drop Zone */
    .upload-dropzone {
        border: 2px dashed rgba(24, 82, 156, 0.2);
        background: #f8fafc;
        border-radius: 12px;
        padding: 25px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    .upload-dropzone:hover, .upload-dropzone.dragover {
        border-color: var(--primary);
        background: var(--primary-glow);
    }
    .upload-dropzone i {
        font-size: 32px;
        color: var(--primary);
    }
    .upload-dropzone p {
        font-size: 13px;
        color: var(--text-muted);
        font-weight: 500;
    }
    .upload-dropzone span {
        font-size: 11px;
        color: #94a3b8;
    }
    .clear-image-btn {
        margin-top: 10px;
        background: #fee2e2;
        color: #ef4444;
        border: 1px solid #fca5a5;
        border-radius: 6px;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .clear-image-btn:hover {
        background: #ef4444;
        color: #ffffff;
        border-color: #ef4444;
    }

    /* Google Snippet Preview */
    .google-preview-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 16px;
        margin-top: 15px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
    }
    .google-preview-header {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 12px;
        letter-spacing: 0.5px;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 6px;
    }
    .google-preview-site {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: #202124;
        margin-bottom: 4px;
        font-family: Arial, sans-serif;
    }
    .google-preview-favicon {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #f1f3f4;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: #5f6368;
    }
    .google-preview-title {
        font-size: 18px;
        color: #1a0dab;
        margin-bottom: 4px;
        cursor: pointer;
        word-break: break-word;
        line-height: 1.3;
        font-family: Arial, sans-serif;
    }
    .google-preview-title:hover {
        text-decoration: underline;
    }
    .google-preview-desc {
        font-size: 13px;
        color: #4d5156;
        line-height: 1.58;
        word-break: break-word;
        font-family: Arial, sans-serif;
    }

    /* Search & Filter Layout */
    .list-controls-flex {
        display: flex;
        gap: 15px;
        margin-top: 20px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        align-items: center;
    }
    .cms-search-input {
        flex: 1;
        min-width: 250px;
        position: relative;
    }
    .cms-search-input input {
        width: 100%;
        padding: 10px 14px 10px 40px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 13.5px;
        outline: none;
        transition: all 0.3s;
    }
    .cms-search-input input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-glow);
    }
    .cms-search-input i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 14px;
    }
    .cms-filter-select {
        padding: 10px 14px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 13.5px;
        outline: none;
        min-width: 160px;
        transition: all 0.3s;
        cursor: pointer;
    }
    .cms-filter-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-glow);
    }

    /* Auto-save notification */
    .recovery-banner {
        background: #fffbeb;
        border: 1px solid #fef3c7;
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #b45309;
        font-size: 13.5px;
        font-weight: 500;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
    }
    .recovery-banner button {
        background: #d97706;
        color: #ffffff;
        border: none;
        border-radius: 6px;
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
    }
    .recovery-banner button:hover {
        background: #b45309;
    }

    /* Input validation & Char counters styling */
    .char-counter {
        text-align: right;
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 4px;
        font-weight: 600;
    }
    .char-counter.limit-exceeded {
        color: #ef4444 !important;
    }
    .char-counter.limit-warning {
        color: #eab308 !important;
    }
    
    .form-control-adm.is-valid {
        border-color: #22c55e !important;
    }
    .form-control-adm.is-invalid {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
    }
    .invalid-feedback-text {
        font-size: 11px;
        color: #ef4444;
        font-weight: 600;
        margin-top: 4px;
        display: block;
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
    /* ==========================================================
       Article list
       ========================================================== */
    .bl-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
    .bl-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 22px; }
    .bl-stat {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 2px;
        padding: 16px 18px;
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-xs);
        text-align: left;
        font: inherit;
        color: inherit;
        cursor: pointer;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .bl-stat:hover { border-color: #b9cbe3; box-shadow: var(--shadow-sm); }
    .bl-stat.static { cursor: default; }
    .bl-stat.static:hover { border-color: var(--border-color); box-shadow: var(--shadow-xs); }
    .bl-stat.is-current { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-glow); }
    .bl-stat-label { font-size: 12.5px; font-weight: 600; color: var(--text-muted); }
    .bl-stat-value { font-family: 'Outfit', sans-serif; font-size: 26px; font-weight: 700; line-height: 1.2; font-variant-numeric: tabular-nums; }
    .bl-stat-icon {
        position: absolute; top: 16px; right: 18px; width: 34px; height: 34px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center; font-size: 14px;
        background: var(--primary-soft); color: var(--primary);
    }
    .bl-stat-icon.good { background: #f0fdf4; color: #15803d; }
    .bl-stat-icon.warn { background: #fffbeb; color: #b45309; }

    .bl-card { background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-lg); box-shadow: var(--shadow-xs); min-width: 0; }
    .bl-toolbar {
        display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
        padding: 14px 20px; background: var(--bg-subtle); border-bottom: 1px solid var(--border-color);
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    }
    .bl-toolbar [hidden] { display: none !important; }
    .bl-search { position: relative; flex: 1 1 280px; }
    .bl-search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 12.5px; pointer-events: none; }
    .bl-search input, .bl-select {
        width: 100%; height: 38px; padding: 0 11px; border: 1px solid var(--border-strong); border-radius: var(--radius-sm);
        background: #ffffff; color: var(--text-main); font-size: 13px; outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .bl-search input { padding-left: 34px; }
    .bl-select { width: auto; min-width: 160px; cursor: pointer; }
    .bl-search input:focus, .bl-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-glow); }
    .bl-btn {
        height: 38px; display: inline-flex; align-items: center; gap: 8px; padding: 0 14px;
        border-radius: var(--radius-sm); border: 1px solid var(--border-strong); background: #ffffff;
        color: var(--text-main); font-size: 13px; font-weight: 600; cursor: pointer;
    }
    .bl-btn:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-soft); }
    .bl-new { height: 38px; margin-left: auto; }

    .bl-table-wrap { padding: 16px 20px 20px; }
    .bl-table-wrap .table-responsive { border: 1px solid var(--border-color); border-radius: var(--radius); }
    .bl-table th { padding: 11px 14px; }
    .bl-table td { padding: 10px 14px; vertical-align: middle; }
    .bl-table tbody tr:last-child td { border-bottom: none; }
    .bl-table th:last-child, .bl-table td:last-child {
        position: sticky; right: 0; z-index: 1; background: #ffffff; text-align: right;
        box-shadow: -10px 0 10px -10px rgba(15, 23, 42, 0.18);
    }
    .bl-table th:last-child { background: var(--bg-subtle); }
    .bl-table tbody tr:hover td:last-child { background: #f9fbfd; }
    .bl-article { display: flex; align-items: center; gap: 12px; min-width: 280px; max-width: 460px; white-space: normal; }
    .bl-thumb {
        position: relative; flex-shrink: 0; width: 64px; height: 44px; border-radius: 8px; overflow: hidden;
        background: #eef1f6; border: 1px solid var(--border-color);
        display: flex; align-items: center; justify-content: center; color: #b6c2d2; font-size: 15px;
    }
    .bl-thumb img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .bl-title {
        font-weight: 600; color: var(--text-main); line-height: 1.35;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .bl-slug { font-size: 12px; color: var(--text-muted); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 440px; }
    .bl-warn { color: #b45309; font-weight: 600; }
    .bl-sub { font-size: 12px; color: var(--text-muted); }
    .bl-table td:nth-child(5) { white-space: nowrap; }
    .bl-num { text-align: right; font-weight: 600; font-variant-numeric: tabular-nums; }
    .bl-chip { display: inline-block; padding: 2px 9px; border-radius: 999px; background: #f1f5f9; color: var(--text-secondary); font-size: 12px; font-weight: 600; white-space: nowrap; }
    .bl-status { display: inline-flex; align-items: center; gap: 6px; padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; border: 1px solid transparent; white-space: nowrap; }
    .bl-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .bl-status.on { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
    .bl-status.off { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }
    .bl-actions { display: inline-flex; gap: 6px; justify-content: flex-end; }
    .bl-ic {
        width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;
        border-radius: 8px; border: 1px solid var(--border-strong); background: #ffffff; color: var(--text-secondary);
        font-size: 12.5px; cursor: pointer; text-decoration: none;
        transition: border-color 0.15s ease, color 0.15s ease, background-color 0.15s ease;
    }
    .bl-ic:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-soft); }
    .bl-ic:disabled { opacity: 0.5; cursor: wait; }
    .bl-ic.danger { color: #b91c1c; border-color: #fecaca; }
    .bl-ic.danger:hover { background: #fef2f2; border-color: #dc2626; }
    .bl-empty { padding: 48px 20px !important; text-align: center !important; color: var(--text-muted); white-space: normal; position: static !important; box-shadow: none !important; }
    .bl-empty i { display: block; font-size: 26px; color: #cbd5e1; margin-bottom: 10px; }
    .bl-empty strong { display: block; color: var(--text-main); margin-bottom: 8px; }
    .bl-foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-top: 14px; font-size: 12.5px; color: var(--text-muted); }
    .bl-foot .pagination { display: flex; gap: 4px; list-style: none; margin: 0; flex-wrap: wrap; }
    .bl-foot .page-item a, .bl-foot .page-item span {
        min-width: 32px; height: 32px; padding: 0 10px; display: inline-flex; align-items: center; justify-content: center;
        border: 1px solid var(--border-color); border-radius: 7px; background: #fff; color: var(--text-secondary);
        font-size: 12.5px; font-weight: 600; text-decoration: none;
    }
    .bl-foot .page-item a:hover { background: #fff; border-color: var(--primary); color: var(--primary); }
    .bl-foot .page-item.active span { background: var(--primary); border-color: var(--primary); color: #fff; }
    .bl-foot .page-item.disabled span { opacity: 0.45; cursor: not-allowed; }
    .bl-foot .page-item.gap span { border: none; background: transparent; min-width: 16px; padding: 0; }

    /* ==========================================================
       Editor refinements
       ========================================================== */
    .form-grid { grid-template-columns: minmax(0, 1fr) minmax(320px, 380px); gap: 28px; }
    .tabs-nav { gap: 2px; overflow-x: auto; scrollbar-width: none; border-bottom-width: 1px; }
    .tabs-nav::-webkit-scrollbar { display: none; }
    .tab-btn { white-space: nowrap; text-transform: none; letter-spacing: 0; font-size: 13px; font-weight: 600; padding: 10px 12px; flex: 0 0 auto; }
    .form-label { text-transform: none; letter-spacing: 0; font-size: 13px; }
    .form-hint-missing { font-size: 12.5px; color: var(--text-muted); font-weight: 500; }
    .form-hint-missing b { color: #b45309; font-weight: 600; }

    @media (max-width: 1100px) {
        .bl-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 640px) {
        .bl-stats { grid-template-columns: minmax(0, 1fr); }
        .bl-toolbar { padding: 12px 16px; }
        .bl-select { flex: 1 1 140px; }
        .bl-new { width: 100%; justify-content: center; margin-left: 0; }
        .bl-table-wrap { padding: 12px 16px 16px; }
    }

    /* Writing workspace: keep secondary tools visible without competing with content. */
    #formPanel { padding: 24px; max-width: 1520px; margin-inline: auto; }
    #formPanel .form-grid > div { min-width: 0; }
    #formPanel .form-sidebar { position: static; padding: 18px; border: 1px solid #e2e8f0; border-radius: 14px; background: #f8fafc; }
    #formPanel .tabs-nav { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 4px; padding: 4px; background: #eaf0f7; border: 0; border-radius: 10px; margin-bottom: 22px; }
    #formPanel .tab-btn { border: 0; border-radius: 7px; padding: 11px 8px; justify-content: center; min-height: 42px; }
    #formPanel .tab-btn.active { background: white; color: #18529c; box-shadow: 0 1px 4px #0f172a15; }
    #formPanel .form-control-adm { min-height: 44px; font-size: 14px; }
    #formPanel #title { font-size: 21px; font-weight: 600; padding: 14px; }
    #formPanel #excerpt { min-height: 96px; }
    #formPanel .form-label { color: #334155; margin-bottom: 7px; }
    .editor-intro, .field-help { color: #64748b; font-size: 12px; line-height: 1.6; }
    .editor-intro { margin: -12px 0 22px; }
    #formPanel .upload-dropzone { padding: 20px 12px; background: white; }
    #formPanel .preview-img-container { height: 150px; }
    #formPanel .readiness-list { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    #formPanel .form-actions-bar { padding: 16px 0 8px; gap: 12px; background: #fff; box-shadow: 0 -8px 16px #fff; }
    #formPanel :is(button, [role="button"]):focus-visible { outline: 3px solid #18529c; outline-offset: 3px; }
    #draftSaveStatus { font-size: 12px; color: #64748b; display: block; margin-top: 8px; }
    @media (max-width: 1100px) { #formPanel .form-grid { grid-template-columns: minmax(0, 1fr) 320px; gap: 20px; } }
    @media (max-width: 900px) { #formPanel .form-grid { grid-template-columns: minmax(0, 1fr); } }
    @media (max-width: 600px) {
        #formPanel { padding: 16px; }
        #formPanel .card-header-flex { flex-wrap: wrap; gap: 12px; }
        #formPanel .form-sidebar { padding: 14px; }
        #formPanel .form-actions-bar { flex-wrap: wrap; }
        #formPanel #formFeedback { flex-basis: 100%; }
        #formPanel #saveBlogBtn { flex: 1; padding-inline: 14px !important; }
    }

</style>

<!-- BLOG LIST PANEL -->
<div class="panel-card" id="listPanel">
    <!-- Stats double as quick status filters -->
    <div class="bl-stats">
        <button type="button" class="bl-stat" data-stat-filter="">
            <span class="bl-stat-label">Total articles</span>
            <span class="bl-stat-value" id="statTotal"><?php echo number_format($totalRecords); ?></span>
            <i class="fa-solid fa-newspaper bl-stat-icon"></i>
        </button>
        <button type="button" class="bl-stat" data-stat-filter="published" title="Show only active articles">
            <span class="bl-stat-label">Active (live)</span>
            <span class="bl-stat-value" id="activeArticlesCount"><?php echo number_format($activeRecords); ?></span>
            <i class="fa-solid fa-circle-check bl-stat-icon good"></i>
        </button>
        <button type="button" class="bl-stat" data-stat-filter="draft" title="Show only inactive articles">
            <span class="bl-stat-label">Inactive (hidden)</span>
            <span class="bl-stat-value" id="inactiveArticlesCount"><?php echo number_format($inactiveRecords); ?></span>
            <i class="fa-solid fa-eye-slash bl-stat-icon warn"></i>
        </button>
        <div class="bl-stat static">
            <span class="bl-stat-label">Total views</span>
            <span class="bl-stat-value" id="statViews"><?php echo number_format($totalViews); ?></span>
            <i class="fa-solid fa-chart-simple bl-stat-icon"></i>
        </div>
    </div>

    <section class="bl-card">
        <div class="bl-toolbar">
            <div class="bl-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <label for="cmsSearch" class="bl-sr">Search articles</label>
                <input type="search" id="cmsSearch" placeholder="Search title, slug, author or keywords…" autocomplete="off">
            </div>
            <label for="cmsCategoryFilter" class="bl-sr">Category</label>
            <select id="cmsCategoryFilter" class="bl-select">
                <option value="">All categories</option>
                <option value="personal">Personal loan</option>
                <option value="business">Business loan</option>
                <option value="edi">EDI small loan</option>
                <option value="vehicle">Vehicle loan</option>
                <option value="lap">Loan against property</option>
                <option value="payday">Payday loan</option>
                <option value="finance">Finance tips</option>
            </select>
            <label for="cmsStatusFilter" class="bl-sr">Status</label>
            <select id="cmsStatusFilter" class="bl-select">
                <option value="">All statuses</option>
                <option value="published">Published ? live on website</option>
                <option value="draft">Draft ? hidden from website</option>
            </select>
            <button type="button" class="bl-btn" id="cmsClearFilters" hidden><i class="fa-solid fa-xmark"></i> Clear</button>
            <button type="button" class="btn-primary-adm bl-new" id="createPostBtn">
                <i class="fa-solid fa-plus"></i> Write new article
            </button>
        </div>

        <div class="bl-table-wrap">
            <div class="table-responsive" style="position: relative;" id="blogsTableContainer">
                <div class="table-loading-overlay" id="tableLoadingOverlay">
                    <div class="suspense-spinner">
                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                        <span>Loading…</span>
                    </div>
                </div>
                <table class="admin-table bl-table">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th style="text-align:right;">Views</th>
                            <th>Updated</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="blogsTableBody"></tbody>
                </table>
            </div>
            <div id="paginationWrapper"></div>
        </div>
    </section>
</div>
<script type="application/json" id="blogInitialData"><?php echo json_encode($initialBlogData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?></script>

<!-- BLOG FORM PANEL (EDIT / CREATE) -->
<div class="data-card panel-card hidden" id="formPanel">
    <div class="card-header-flex" style="border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 25px;">
        <h2 class="card-title-text" id="formTitle"><i class="fa-solid fa-pen-nib" style="color: var(--primary); margin-right: 8px;"></i> Write New Article</h2>
        <button type="button" class="btn-action" id="backToListBtn" style="width: auto; padding: 0 16px; height: 38px; display: inline-flex; align-items: center; gap: 8px;" title="Cancel editing">
            <i class="fa-solid fa-arrow-left-long"></i> Back to List
        </button>
    </div>

    <p class="editor-intro">Start with your story. Fields marked * are required. Save as a draft to keep the article off the website.</p>
    <form id="blogForm" novalidate method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save_blog">
        <input type="hidden" name="id" id="blog_id" value="0">
        <input type="hidden" name="remove_image" id="remove_image" value="0">
        
        <!-- Unsaved Draft Recovery Alert -->
        <div class="recovery-banner hidden" id="recoveryBanner">
            <span><i class="fa-solid fa-triangle-exclamation"></i> We found an unsaved draft of this article in your browser (saved <span id="recoveryTime"></span>).</span>
            <div>
                <button type="button" id="restoreDraftBtn">Restore Draft</button>
                <button type="button" id="discardDraftBtn" style="background: none; border: 1px solid #d97706; color: #d97706; padding: 5px 12px; margin-left: 8px;">Dismiss</button>
            </div>
        </div>

        <div class="form-grid">
            <!-- Left Column: Content -->
            <div>
                <div class="form-group">
                    <label class="form-label" for="title">Article title *</label>
                    <input type="text" name="title" id="title" class="form-control-adm" placeholder="Enter an engaging, catchy title..." required>
                    <div class="char-counter" id="title-counter">0 / 150 characters</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="slug">URL slug *</label>
                    <input type="text" name="slug" id="slug" class="form-control-adm" placeholder="url-slug-goes-here" required>
                    <div class="field-support-row">
                        <div class="slug-preview">
                            <i class="fa-solid fa-link"></i>
                            <span>/blog/</span><strong id="slugPreviewText">url-slug-goes-here</strong>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="excerpt">Excerpt / Summary</label>
                    <textarea name="excerpt" id="excerpt" class="form-control-adm" placeholder="Brief summary of the article (useful for feeds)..."></textarea>
                    <div class="char-counter" id="excerpt-counter">0 / 300 characters</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="editor-container">Article content *</label>
                    <textarea name="content" id="editor-container" class="form-control-adm" style="min-height: 400px;" placeholder="Write your article content here..."></textarea>
                    <div class="content-metrics">
                        <span class="content-metric"><i class="fa-solid fa-file-lines"></i><strong id="wordCount">0</strong> words</span>
                        <span class="content-metric"><i class="fa-solid fa-clock"></i><strong id="autoReadTime">0</strong> min read</span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Settings, E-E-A-T, CTA & SEO Tabs -->
            <div class="form-sidebar">
                <!-- Tab Navigation -->
                <div class="tabs-nav" role="tablist" aria-label="Article settings">
                    <button type="button" class="tab-btn active" id="tabBtn-settings" onclick="switchSettingsTab('settings')"><i class="fa-solid fa-sliders"></i> Settings</button>
                    <button type="button" class="tab-btn" id="tabBtn-trust" onclick="switchSettingsTab('trust')"><i class="fa-solid fa-user-pen"></i> Author & CTA</button>
                    <button type="button" class="tab-btn" id="tabBtn-seo" onclick="switchSettingsTab('seo')"><i class="fa-solid fa-magnifying-glass-chart"></i> SEO</button>
                    <button type="button" class="tab-btn" id="tabBtn-faq" onclick="switchSettingsTab('faq')"><i class="fa-solid fa-circle-question"></i> FAQs</button>
                </div>

                <!-- Tab 1: General Settings & Cover -->
                <div class="tab-content active" id="tabContent-settings">
                    <div class="form-group">
                        <label class="form-label" for="category">Category</label>
                        <select name="category" id="category" class="form-control-adm" required>
                            <option value="personal">Personal Loan</option>
                            <option value="business">Business Loan</option>
                            <option value="edi">EDI Small Loan</option>
                            <option value="vehicle">Vehicle Loan</option>
                            <option value="lap">Loan Against Property</option>
                            <option value="payday">PayDay Loan</option>
                            <option value="finance">Finance Tips</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="read_time">Read Time</label>
                        <input type="text" name="read_time" id="read_time" class="form-control-adm" placeholder="5 Min Read" value="5 Min Read">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="status">Article Visibility</label>
                        <select name="status" id="status" class="form-control-adm">
                            <option value="draft">Draft ? hidden from website</option>
                            <option value="published">Published ? live on website</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="featured_image">Featured Cover Image</label>
                        <!-- Drag & Drop Zone -->
                        <div class="upload-dropzone" id="dropzone" role="button" tabindex="0" aria-label="Choose cover image" aria-describedby="coverHelp">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <p id="dropzoneText">Drag and drop cover image here or click to select</p>
                            <span id="coverHelp">JPG, PNG or WEBP ? Up to 5 MB</span>
                            <input type="file" name="featured_image" id="featured_image" accept=".jpg,.jpeg,.png,.webp" style="display: none;">
                        </div>
                        <div class="preview-img-container">
                            <img src="" id="imgPreview" class="preview-img hidden" alt="Featured Cover Preview">
                            <span id="previewPlaceholder" style="color:var(--text-muted); font-size:12px;">No cover selected</span>
                        </div>
                        <button type="button" class="clear-image-btn hidden" id="clearImageBtn">
                            <i class="fa-solid fa-trash"></i> Remove Cover
                        </button>
                    </div>
                </div>

                <!-- Tab 2: E-E-A-T Credentials & Conversions -->
                <div class="tab-content" id="tabContent-trust">
                    <div class="section-title" style="margin-bottom: 12px; font-weight: 700; border-bottom: 1px solid var(--border-color); padding-bottom: 5px; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; color: var(--primary);">E-E-A-T Credentials</div>

                    <div class="form-group">
                        <label class="form-label" for="author_name">Author Name</label>
                        <input type="text" name="author_name" id="author_name" class="form-control-adm" placeholder="e.g. Vikram Malhotra">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="author_designation">Author Designation</label>
                        <input type="text" name="author_designation" id="author_designation" class="form-control-adm" placeholder="e.g. Senior Credit Analyst">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="reviewed_by">Reviewed By (Expert Reviewer)</label>
                        <input type="text" name="reviewed_by" id="reviewed_by" class="form-control-adm" placeholder="e.g. CA Ananya Sen">
                    </div>

                    <div class="section-title" style="margin-top: 25px; margin-bottom: 12px; font-weight: 700; border-bottom: 1px solid var(--border-color); padding-bottom: 5px; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; color: var(--primary);">Contextual Call-to-Action (CTA)</div>

                    <div class="form-group">
                        <label class="form-label" for="cta_text">CTA Text / Headline</label>
                        <input type="text" name="cta_text" id="cta_text" class="form-control-adm" placeholder="e.g. Need a Quick Business Loan? Apply now">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="cta_link">CTA Target Link</label>
                        <input type="text" name="cta_link" id="cta_link" class="form-control-adm" placeholder="e.g. /personal-loan-apply.php">
                        <small style="color: var(--text-muted); font-size:11px; margin-top:4px; display:block;">Relative URL path (e.g. /personal-loan-apply.php).</small>
                    </div>
                </div>

                <!-- Tab 3: SEO Meta Tags & Google Simulator -->
                <div class="tab-content" id="tabContent-seo">
                    <div class="section-title" style="margin-bottom: 12px; font-weight: 700; border-bottom: 1px solid var(--border-color); padding-bottom: 5px; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; color: var(--primary);">SEO Meta Tags</div>

                    <div class="form-group">
                        <label class="form-label" for="meta_title">Meta Title</label>
                        <input type="text" name="meta_title" id="meta_title" class="form-control-adm" placeholder="Custom SEO Title (defaults to title)">
                        <div class="char-counter" id="meta-title-counter">0 / 60 characters</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="meta_description">Meta Description</label>
                        <textarea name="meta_description" id="meta_description" class="form-control-adm" placeholder="Google snippet description (max 160 chars)..."></textarea>
                        <div class="char-counter" id="meta-desc-counter">0 / 160 characters</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="meta_keywords">Meta Keywords</label>
                        <input type="text" name="meta_keywords" id="meta_keywords" class="form-control-adm" placeholder="loan, finance, tips, cibil">
                    </div>

                    <!-- Google Search Preview Simulator -->
                    <div class="google-preview-card">
                        <div class="google-preview-header">Google Search Result Preview</div>
                        <div class="google-preview-site">
                            <div class="google-preview-favicon"><i class="fa-solid fa-globe"></i></div>
                            <div>Waqt Finance &rsaquo; blog</div>
                        </div>
                        <div class="google-preview-title" id="previewTitle">Please enter meta title...</div>
                        <div class="google-preview-desc" id="previewDesc">Please enter meta description to simulate the Google search snippet...</div>
                    </div>
                </div>

                <!-- Tab 4: Blog FAQs -->
                <div class="tab-content" id="tabContent-faq">
                    <div class="section-title" style="margin-bottom: 12px; font-weight: 700; border-bottom: 1px solid var(--border-color); padding-bottom: 5px; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; color: var(--primary);">Frequently Asked Questions (FAQs)</div>
                    
                    <div class="field-help-text" style="font-size: 11px; color: var(--text-muted); margin-bottom: 12px;">Configure custom questions and answers for this article.</div>
                    
                    <div class="dynamic-rows-box" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; margin-top: 10px;">
                        <div id="faqsRowContainer"></div>
                        <button type="button" class="btn-add-row" onclick="addFaqRow('', '')" style="background: #ffffff; border: 1px dashed var(--primary); color: var(--primary); font-weight: 600; font-size: 12px; padding: 8px 16px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; width: 100%; justify-content: center; margin-top: 5px;">
                            <i class="fa-solid fa-plus"></i> Add Custom FAQ
                        </button>
                    </div>
                </div>

                <div class="publish-readiness-panel" id="publishReadiness">
                    <div class="readiness-header">
                        <span class="readiness-title"><i class="fa-solid fa-list-check"></i> Readiness</span>
                        <span class="readiness-score" id="readinessScore">0/6 ready</span>
                    </div>
                    <div class="readiness-list">
                        <div class="readiness-item" data-check="title"><span class="readiness-icon"><i class="fa-solid fa-check"></i></span><span>Title</span></div>
                        <div class="readiness-item" data-check="slug"><span class="readiness-icon"><i class="fa-solid fa-check"></i></span><span>Slug</span></div>
                        <div class="readiness-item" data-check="content"><span class="readiness-icon"><i class="fa-solid fa-check"></i></span><span>Content</span></div>
                        <div class="readiness-item" data-check="cover"><span class="readiness-icon"><i class="fa-solid fa-check"></i></span><span>Cover</span></div>
                        <div class="readiness-item" data-check="author"><span class="readiness-icon"><i class="fa-solid fa-check"></i></span><span>Author</span></div>
                        <div class="readiness-item" data-check="seo"><span class="readiness-icon"><i class="fa-solid fa-check"></i></span><span>SEO</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions-bar">
            <span id="formFeedback" style="align-self: center; margin-right: auto; font-weight: 600; font-size: 13.5px;" aria-live="polite"></span>
            <button type="button" class="btn-action" id="cancelBtn" style="width: auto; padding:0 24px; height: 42px;">Cancel</button>
            <button type="submit" class="btn-primary-adm" id="saveBlogBtn" style="padding: 0 30px; height: 42px; display: inline-flex; align-items: center; justify-content: center;"><i class="fa-solid fa-check"></i> Save article</button>
        </div>
    </form>
</div>

<script>
    function escapeHtml(value) {
        return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    // Initialize TinyMCE Editor
    tinymce.init({
        selector: '#editor-container',
        height: 480,
        toolbar_mode: "sliding",
        content_style: "body { font-family: system-ui, sans-serif; font-size: 16px; line-height: 1.75; margin: 24px; max-width: 760px; }",
        menubar: 'edit insert format tools table',
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount', 'emoticons'
        ],
        toolbar: [
            'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough forecolor backcolor',
            'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table emoticons | code preview fullscreen'
        ],
        branding: false,
        promotion: false,
        skin: 'oxide',
        content_css: 'default',
        
        // Image upload integration to allow browsing and uploading local files
        image_title: true,
        automatic_uploads: true,
        file_picker_types: 'image',
        images_upload_handler: function (blobInfo, progress) {
            return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.withCredentials = false;
                xhr.open('POST', 'api.php');

                xhr.onload = function() {
                    if (xhr.status < 200 || xhr.status >= 300) {
                        reject('HTTP Error: ' + xhr.status);
                        return;
                    }

                    let json;
                    try { json = JSON.parse(xhr.responseText); } catch (error) { reject('Upload returned an invalid response. Please try again.'); return; }

                    if (!json || typeof json.location != 'string') {
                        reject('Invalid JSON: ' + xhr.responseText);
                        return;
                    }

                    resolve(json.location);
                };

                xhr.onerror = function () {
                    reject('Image upload failed due to an XHR Transport error. Code: ' + xhr.status);
                };

                const formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                formData.append('action', 'upload_editor_image');
                formData.append('csrf_token', csrfToken);

                xhr.send(formData);
            });
        },
        setup: function (editor) {
            editor.on('change keyup input', function () {
                editor.save();
                if (typeof updateContentMetrics === 'function') {
                    updateContentMetrics();
                }
                if (typeof updateReadiness === 'function') {
                    updateReadiness();
                }
                if (typeof checkRequiredFormFields === 'function') {
                    checkRequiredFormFields();
                }
            });
        }
    });

    const listPanel = document.getElementById('listPanel');
    const formPanel = document.getElementById('formPanel');
    const blogForm = document.getElementById('blogForm');
    const blogIdInput = document.getElementById('blog_id');
    const removeImageInput = document.getElementById('remove_image');
    const formTitle = document.getElementById('formTitle');
    const formFeedback = document.getElementById('formFeedback');
    const csrfToken = <?php echo json_encode($_SESSION['csrf_token'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    const excerptInput = document.getElementById('excerpt');
    const categoryInput = document.getElementById('category');
    const readTimeInput = document.getElementById('read_time');
    const statusInput = document.getElementById('status');
    const slugPreviewText = document.getElementById('slugPreviewText');
    const wordCountEl = document.getElementById('wordCount');
    const autoReadTimeEl = document.getElementById('autoReadTime');
    const readinessScore = document.getElementById('readinessScore');
    const metaTitleInput = document.getElementById('meta_title');
    const metaDescInput = document.getElementById('meta_description');
    const metaKeywordsInput = document.getElementById('meta_keywords');
    
    const fileInput = document.getElementById('featured_image');
    const imgPreview = document.getElementById('imgPreview');
    const previewPlaceholder = document.getElementById('previewPlaceholder');
    const clearImageBtn = document.getElementById('clearImageBtn');
    const dropzoneText = document.getElementById('dropzoneText');
    const dropzone = document.getElementById('dropzone');
    let readTimeTouched = false;
    let lastAutoReadTime = '5 Min Read';

    // Validation Feedback Helpers
    function setInvalid(el, msg) {
        el.setAttribute('aria-invalid', 'true');
        el.classList.add('is-invalid');
        el.classList.remove('is-valid');
        
        let feedback = el.parentNode.querySelector('.invalid-feedback-text');
        if (!feedback) {
            feedback = document.createElement('span');
            feedback.className = 'invalid-feedback-text';
            el.parentNode.appendChild(feedback);
        }
        feedback.id = el.id + '-error';
        el.setAttribute('aria-describedby', feedback.id);
        feedback.textContent = msg;
        return false;
    }

    function setValid(el) {
        el.removeAttribute('aria-invalid');
        el.removeAttribute('aria-describedby');
        el.classList.remove('is-invalid');
        el.classList.add('is-valid');
        const feedback = el.parentNode.querySelector('.invalid-feedback-text');
        if (feedback) feedback.remove();
        return true;
    }

    // Dynamic Character Counter Manager
    function initCharCounter(inputEl, counterEl, maxLength) {
        if (!inputEl || !counterEl) return;
        
        function updateCounter() {
            const len = inputEl.value.length;
            counterEl.textContent = `${len} / ${maxLength} characters`;
            
            if (len > maxLength) {
                counterEl.classList.add('limit-exceeded');
                counterEl.classList.remove('limit-warning');
            } else if (len >= maxLength * 0.9) {
                counterEl.classList.add('limit-warning');
                counterEl.classList.remove('limit-exceeded');
            } else {
                counterEl.classList.remove('limit-warning', 'limit-exceeded');
            }
        }
        
        inputEl.addEventListener('input', updateCounter);
        inputEl.addEventListener('keyup', updateCounter);
        inputEl.addEventListener('change', updateCounter);
        
        updateCounter();
    }

    // Initialize Character Counters
    initCharCounter(titleInput, document.getElementById('title-counter'), 150);
    initCharCounter(excerptInput, document.getElementById('excerpt-counter'), 300);
    initCharCounter(metaTitleInput, document.getElementById('meta-title-counter'), 60);
    initCharCounter(metaDescInput, document.getElementById('meta-desc-counter'), 160);

    function slugifyText(text) {
        return text.toLowerCase()
            .replace(/[^a-z0-9\s\-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
    }

    function getEditorText() {
        const editor = tinymce.get('editor-container');
        if (editor) {
            return editor.getContent({format: 'text'}).trim();
        }

        const fallback = document.getElementById('editor-container');
        return fallback ? fallback.value.trim() : '';
    }

    function countWords(text) {
        if (text === '') return 0;
        return text.split(/\s+/).filter(Boolean).length;
    }

    function estimateReadMinutes(words) {
        return words > 0 ? Math.max(1, Math.ceil(words / 220)) : 0;
    }

    function setReadinessItem(name, isReady) {
        const item = document.querySelector(`.readiness-item[data-check="${name}"]`);
        if (!item) return;
        item.classList.toggle('is-ready', Boolean(isReady));
    }

    function hasSelectedCover() {
        return imgPreview && !imgPreview.classList.contains('hidden') && imgPreview.getAttribute('src') !== '';
    }

    function updateSlugPreview() {
        if (!slugPreviewText) return;
        const slug = slugInput.value.trim() || 'url-slug-goes-here';
        slugPreviewText.textContent = slug;
    }

    function updateContentMetrics() {
        const words = countWords(getEditorText());
        const minutes = estimateReadMinutes(words);

        if (wordCountEl) wordCountEl.textContent = words.toLocaleString('en-IN');
        if (autoReadTimeEl) autoReadTimeEl.textContent = String(minutes);

        if (readTimeInput && !readTimeTouched && minutes > 0) {
            lastAutoReadTime = `${minutes} Min Read`;
            readTimeInput.value = lastAutoReadTime;
        }
    }

    window.updateReadiness = function() {
        const titleVal = titleInput.value.trim();
        const slugVal = slugInput.value.trim();
        const editorWords = countWords(getEditorText());
        const authorVal = document.getElementById('author_name').value.trim();
        const metaTitleVal = metaTitleInput.value.trim();
        const metaDescVal = metaDescInput.value.trim();
        const slugRegex = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;

        const checks = {
            title: titleVal.length >= 5 && titleVal.length <= 150,
            slug: slugRegex.test(slugVal),
            content: editorWords >= 50,
            cover: hasSelectedCover(),
            author: statusInput.value !== 'published' || authorVal !== '',
            seo: metaTitleVal.length > 0 && metaTitleVal.length <= 60 && metaDescVal.length >= 50 && metaDescVal.length <= 160
        };

        let readyCount = 0;
        Object.keys(checks).forEach(key => {
            if (checks[key]) readyCount += 1;
            setReadinessItem(key, checks[key]);
        });

        if (readinessScore) {
            readinessScore.textContent = `${readyCount}/6 ready`;
        }
    };

    // Form Button Disable/Enable manager
    const saveBlogBtn = document.getElementById('saveBlogBtn');
    
    window.checkRequiredFormFields = function() {
        if (!saveBlogBtn) return;
        const missing = [];
        const titleLen = titleInput.value.trim().length;
        if (titleLen < 5) missing.push('title (5+ characters)');
        if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slugInput.value.trim())) missing.push('URL slug');
        const editorText = tinymce.get('editor-container') ? tinymce.get('editor-container').getContent({format: 'text'}).trim() : '';
        if (editorText === '') missing.push('content');
        if (statusInput.value === 'published' && document.getElementById('author_name').value.trim() === '') missing.push('author name (Author & CTA tab)');
        if (document.getElementById('cta_text').value.trim() !== '' && document.getElementById('cta_link').value.trim() === '') missing.push('CTA link');
        // The button stays clickable; submitting shows exactly which field needs attention.
        if (formFeedback && !formFeedback.dataset.locked) {
            formFeedback.innerHTML = missing.length
                ? '<span class="form-hint-missing">Still needed: <b></b></span>'
                : '<span class="form-hint-missing" style="color:#15803d;"><i class="fa-solid fa-check"></i> Ready to save</span>';
            const b = formFeedback.querySelector('b');
            if (b) b.textContent = missing.join(', ');
        }
    };

    // Attach listeners for save button state check
    [titleInput, slugInput, categoryInput, statusInput, excerptInput,
     metaTitleInput, metaDescInput, metaKeywordsInput,
     document.getElementById('author_name'), 
     document.getElementById('cta_text'), 
     document.getElementById('cta_link')].forEach(inputEl => {
        if (inputEl) {
            inputEl.addEventListener('input', checkRequiredFormFields);
            inputEl.addEventListener('keyup', checkRequiredFormFields);
            inputEl.addEventListener('change', checkRequiredFormFields);
            inputEl.addEventListener('input', updateReadiness);
            inputEl.addEventListener('change', updateReadiness);
        }
    });

    if (readTimeInput) {
        readTimeInput.addEventListener('input', () => {
            if (readTimeInput.value.trim() !== lastAutoReadTime) {
                readTimeTouched = true;
            }
        });
    }

    let slugManuallyEdited = false;
    // Auto-generate slug from title
    titleInput.addEventListener('input', function() {
        if (blogIdInput.value === "0" && !slugManuallyEdited) {
            slugInput.value = slugifyText(this.value);
        }
        updateSlugPreview();
        updateReadiness();
        checkRequiredFormFields();
    });

    slugInput.addEventListener('input', function() {
        slugManuallyEdited = true;
        const cleanedSlug = slugifyText(this.value);
        if (this.value !== cleanedSlug) {
            this.value = cleanedSlug;
        }
        updateSlugPreview();
        updateReadiness();
    });

    // Drag & Drop Cover Image Handlers
    if (dropzone && fileInput) {
        dropzone.addEventListener('click', () => fileInput.click());
        dropzone.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); fileInput.click(); } });

        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('dragover');
            if (dropzoneText) dropzoneText.textContent = "Drop the image to upload...";
        });

        ['dragleave', 'dragend'].forEach(type => {
            dropzone.addEventListener(type, () => {
                dropzone.classList.remove('dragover');
                if (dropzoneText) dropzoneText.textContent = "Drag and drop cover image here or click to select";
            });
        });

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            if (dropzoneText) dropzoneText.textContent = "Drag and drop cover image here or click to select";
            
            if (e.dataTransfer.files.length) {
                const file = e.dataTransfer.files[0];
                if (validateCoverFile(file)) {
                    fileInput.files = e.dataTransfer.files;
                    triggerImagePreview(file);
                }
            }
        });

        fileInput.addEventListener('change', function() {
            if (this.files.length) {
                const file = this.files[0];
                if (validateCoverFile(file)) {
                    triggerImagePreview(file);
                } else {
                    this.value = "";
                }
            }
        });
    }

    function validateCoverFile(file) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        const maxSize = 5 * 1024 * 1024;

        if (!file) return false;

        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                title: 'Unsupported Cover Image',
                text: 'Please upload a JPG, PNG, or WEBP image.',
                icon: 'warning',
                confirmButtonColor: '#18529c'
            });
            return false;
        }

        if (file.size > maxSize) {
            Swal.fire({
                title: 'Cover Image Too Large',
                text: 'Please upload an image up to 5MB.',
                icon: 'warning',
                confirmButtonColor: '#18529c'
            });
            return false;
        }

        return true;
    }

    function triggerImagePreview(file) {
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imgPreview.src = e.target.result;
                imgPreview.classList.remove('hidden');
                previewPlaceholder.classList.add('hidden');
                if (clearImageBtn) clearImageBtn.classList.remove('hidden');
                if (removeImageInput) removeImageInput.value = "0";
                if (dropzoneText) dropzoneText.textContent = file.name;
                updateReadiness();
            }
            reader.readAsDataURL(file);
        }
    }

    function resetCoverPreview(markForRemoval) {
        if (fileInput) fileInput.value = "";
        if (imgPreview) {
            imgPreview.src = "";
            imgPreview.classList.add('hidden');
        }
        if (previewPlaceholder) previewPlaceholder.classList.remove('hidden');
        if (clearImageBtn) clearImageBtn.classList.add('hidden');
        if (dropzoneText) dropzoneText.textContent = "Drag and drop cover image here or click to select";
        if (removeImageInput) removeImageInput.value = markForRemoval ? "1" : "0";
        updateReadiness();
    }

    if (clearImageBtn && fileInput) {
        clearImageBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            resetCoverPreview(true);
        });
    }

    // Google Search Snippet Live Preview Sync
    const previewTitle = document.getElementById('previewTitle');
    const previewDesc = document.getElementById('previewDesc');

    function updateGooglePreview() {
        if (!previewTitle || !previewDesc) return;

        // Sync Meta Title
        let titleText = metaTitleInput.value.trim();
        if (titleText === '') {
            titleText = titleInput.value.trim() !== '' ? titleInput.value.trim() : 'Please enter meta title...';
        }
        previewTitle.textContent = titleText;

        // Sync Meta Description
        let descText = metaDescInput.value.trim();
        if (descText === '') {
            descText = 'Please enter meta description to simulate the Google search snippet...';
        }
        previewDesc.textContent = descText;
    }

    if (metaTitleInput) metaTitleInput.addEventListener('input', updateGooglePreview);
    if (titleInput) titleInput.addEventListener('input', updateGooglePreview);
    if (metaDescInput) metaDescInput.addEventListener('input', updateGooglePreview);
    updateSlugPreview();
    updateContentMetrics();
    updateReadiness();

    // AJAX Search, Filter & Pagination implementation
    const cmsSearch = document.getElementById('cmsSearch');
    const cmsCategoryFilter = document.getElementById('cmsCategoryFilter');
    const cmsStatusFilter = document.getElementById('cmsStatusFilter');
    const blogsTableBody = document.getElementById('blogsTableBody');

    let currentPage = <?php echo $page; ?>;

    // ---------- Article list (rendered client-side from JSON) ----------
    const CATEGORY_LABELS = {
        personal: 'Personal', business: 'Business', edi: 'EDI', vehicle: 'Vehicle',
        lap: 'LAP', payday: 'Payday', finance: 'Finance tips'
    };
    const listState = { page: currentPage, totalPages: 1 };
    let listRequestId = 0;

    function categoryLabel(c) {
        const key = String(c || '').toLowerCase();
        return CATEGORY_LABELS[key] || (key ? key.charAt(0).toUpperCase() + key.slice(1) : '—');
    }
    function shortDate(v) {
        const d = new Date(String(v || '').replace(' ', 'T'));
        return isNaN(d.getTime()) ? '—' : d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
    }
    function coverSrc(url) {
        const u = String(url || '');
        if (/^https:\/\//.test(u)) return u;
        if (/^(assets|uploads)\/[\w\/.-]+$/.test(u)) return '../' + u;
        return '';
    }
    function thumbHtml(url) {
        const src = coverSrc(url);
        // A missing file shows a neutral placeholder instead of a broken-image icon.
        return `<span class="bl-thumb">${src ? `<img src="${escapeHtml(src)}" alt="" loading="lazy" onerror="this.remove()">` : ''}<i class="fa-regular fa-image"></i></span>`;
    }

    function blogRow(b) {
        const active = b.status === 'published';
        const id = escapeHtml(b.id);
        const missingAuthor = !String(b.author_name || '').trim();
        return `<tr id="blog-row-${id}" data-id="${id}" data-status="${active ? 'published' : 'draft'}">
            <td>
                <div class="bl-article">
                    ${thumbHtml(b.image_url)}
                    <div style="min-width:0;">
                        <div class="bl-title" title="${escapeHtml(b.title)}">${escapeHtml(b.title)}</div>
                        <div class="bl-slug">/blog/${escapeHtml(b.slug)}${missingAuthor ? ' · <span class="bl-warn" title="An author is required before the article can be activated">no author</span>' : ''}</div>
                    </div>
                </div>
            </td>
            <td><span class="bl-chip">${escapeHtml(categoryLabel(b.category))}</span></td>
            <td><span class="bl-status ${active ? 'on' : 'off'} status-badge">${active ? 'Active' : 'Inactive'}</span></td>
            <td class="bl-num">${Number(b.views || 0).toLocaleString('en-IN')}</td>
            <td><div>${shortDate(b.updated_at || b.created_at)}</div><div class="bl-sub">${escapeHtml(b.read_time || '')}</div></td>
            <td>
                <div class="bl-actions">
                    ${active ? `<a class="bl-ic" href="../blog/${encodeURIComponent(b.slug)}" target="_blank" rel="noopener" title="View on website" aria-label="View on website"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>` : ''}
                    <button type="button" class="bl-ic" data-act="edit" data-id="${id}" title="Edit" aria-label="Edit"><i class="fa-solid fa-pen"></i></button>
                    <button type="button" class="bl-ic ${active ? 'is-on' : ''}" data-act="toggle" data-id="${id}" data-next-status="${active ? 'draft' : 'published'}" title="${active ? 'Set inactive (hide from website)' : 'Set active (show on website)'}" aria-label="${active ? 'Set inactive' : 'Set active'}"><i class="fa-solid ${active ? 'fa-eye-slash' : 'fa-eye'}"></i></button>
                    <button type="button" class="bl-ic danger" data-act="delete" data-id="${id}" title="Delete" aria-label="Delete"><i class="fa-solid fa-trash"></i></button>
                </div>
            </td>
        </tr>`;
    }

    function blogPagination(page, total) {
        if (total <= 1) return '';
        const set = new Set([1, total, page - 1, page, page + 1]);
        if (page <= 3) [2, 3, 4].forEach(p => set.add(p));
        if (page >= total - 2) [total - 1, total - 2, total - 3].forEach(p => set.add(p));
        const pages = Array.from(set).filter(p => p >= 1 && p <= total).sort((a, b) => a - b);
        let last = 0;
        const items = [];
        pages.forEach(p => {
            if (last && p - last > 1) items.push('<li class="page-item gap"><span>…</span></li>');
            items.push(p === page ? `<li class="page-item active"><span aria-current="page">${p}</span></li>` : `<li class="page-item"><a href="#" data-page="${p}">${p}</a></li>`);
            last = p;
        });
        const prev = page <= 1 ? '<li class="page-item disabled"><span><i class="fa-solid fa-chevron-left" style="font-size:10px;"></i></span></li>' : `<li class="page-item"><a href="#" data-page="${page - 1}" aria-label="Previous page"><i class="fa-solid fa-chevron-left" style="font-size:10px;"></i></a></li>`;
        const next = page >= total ? '<li class="page-item disabled"><span><i class="fa-solid fa-chevron-right" style="font-size:10px;"></i></span></li>' : `<li class="page-item"><a href="#" data-page="${page + 1}" aria-label="Next page"><i class="fa-solid fa-chevron-right" style="font-size:10px;"></i></a></li>`;
        return `<ul class="pagination">${prev}${items.join('')}${next}</ul>`;
    }

    function renderStats(stats) {
        if (!stats) return;
        const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = Number(v || 0).toLocaleString('en-IN'); };
        set('statTotal', stats.total);
        set('activeArticlesCount', stats.active);
        set('inactiveArticlesCount', stats.inactive);
        set('statViews', stats.views);
    }

    function renderBlogList(data) {
        listState.page = data.page || 1;
        listState.totalPages = data.total_pages || 1;
        currentPage = listState.page;
        const filtered = !!((cmsSearch && cmsSearch.value.trim()) || (cmsCategoryFilter && cmsCategoryFilter.value) || (cmsStatusFilter && cmsStatusFilter.value));
        const records = data.records || [];
        blogsTableBody.innerHTML = records.length ? records.map(blogRow).join('') : `
            <tr><td colspan="6" class="bl-empty">
                <i class="fa-regular fa-newspaper"></i>
                <strong>${filtered ? 'No articles match these filters' : 'No articles yet'}</strong>
                ${filtered ? '<button type="button" class="bl-btn" data-act="clear-filters"><i class="fa-solid fa-xmark"></i> Clear filters</button>' : 'Click “Write new article” to publish your first post.'}
            </td></tr>`;
        const total = Number(data.total_records || 0);
        const limit = Number(data.limit || 10);
        const start = total ? (listState.page - 1) * limit + 1 : 0;
        const end = Math.min(listState.page * limit, total);
        const wrapper = document.getElementById('paginationWrapper');
        if (wrapper) {
            wrapper.innerHTML = total ? `<div class="bl-foot"><span>Showing ${start}–${end} of ${total.toLocaleString('en-IN')} article${total === 1 ? '' : 's'}</span>${blogPagination(listState.page, listState.totalPages)}</div>` : '';
        }
        const clearBtn = document.getElementById('cmsClearFilters');
        if (clearBtn) clearBtn.hidden = !filtered;
        document.querySelectorAll('[data-stat-filter]').forEach(card => {
            card.classList.toggle('is-current', (cmsStatusFilter ? cmsStatusFilter.value : '') === card.getAttribute('data-stat-filter') && card.getAttribute('data-stat-filter') !== '');
        });
        renderStats(data.stats);
    }

    function fetchBlogs(page = 1) {
        const requestId = ++listRequestId;
        const loader = document.getElementById('tableLoadingOverlay');
        if (loader) loader.classList.add('active');

        const formData = new FormData();
        formData.append('action', 'get_blogs');
        formData.append('page', page);
        formData.append('search', cmsSearch ? cmsSearch.value.trim() : '');
        formData.append('category', cmsCategoryFilter ? cmsCategoryFilter.value : '');
        formData.append('status', cmsStatusFilter ? cmsStatusFilter.value : '');
        formData.append('csrf_token', csrfToken);

        return fetch('api.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (requestId !== listRequestId) return;
                if (data.status !== 'success') throw new Error(data.message || 'Could not load articles.');
                renderBlogList(data);
                // replaceState: paging/filtering should not fill the browser Back history.
                const url = new URL(window.location.href);
                if (data.page > 1) url.searchParams.set('page', data.page); else url.searchParams.delete('page');
                history.replaceState(history.state, '', url.pathname + url.search + url.hash);
            })
            .catch(err => {
                if (requestId !== listRequestId) return;
                blogsTableBody.innerHTML = `<tr><td colspan="6" class="bl-empty"><strong style="color:var(--danger);">${escapeHtml(err.message || 'Connection failed.')}</strong><button type="button" class="bl-btn" data-act="retry"><i class="fa-solid fa-rotate-right"></i> Try again</button></td></tr>`;
            })
            .finally(() => { if (requestId === listRequestId && loader) loader.classList.remove('active'); });
    }

    let searchTimeout = null;
    if (cmsSearch) {
        cmsSearch.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => fetchBlogs(1), 300);
        });
    }
    [cmsCategoryFilter, cmsStatusFilter].forEach(sel => sel && sel.addEventListener('change', () => fetchBlogs(1)));

    function clearListFilters() {
        if (cmsSearch) cmsSearch.value = '';
        if (cmsCategoryFilter) cmsCategoryFilter.value = '';
        if (cmsStatusFilter) cmsStatusFilter.value = '';
        fetchBlogs(1);
    }

    // Stat cards double as quick status filters.
    document.querySelectorAll('[data-stat-filter]').forEach(card => {
        card.addEventListener('click', () => {
            if (cmsStatusFilter) cmsStatusFilter.value = card.getAttribute('data-stat-filter');
            fetchBlogs(1);
        });
    });
    const cmsClearFilters = document.getElementById('cmsClearFilters');
    if (cmsClearFilters) cmsClearFilters.addEventListener('click', clearListFilters);

    // btn is optional (Undo from the toast has no row button).
    function toggleBlogStatus(id, nextStatus, btn = null, isUndo = false) {
        const fd = new FormData();
        fd.append('action', 'toggle_blog_status');
        fd.append('id', id);
        fd.append('status', nextStatus);
        fd.append('csrf_token', csrfToken);
        if (btn) btn.disabled = true;
        fetch('api.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success') throw new Error(data.message || 'Status update failed.');
                const nowLive = nextStatus === 'published';
                adminToast(
                    data.message || (nowLive ? 'Article is now live on the website.' : 'Article is now hidden from the website.'),
                    'success',
                    isUndo ? {} : { actionText: 'Undo', onAction: () => toggleBlogStatus(id, nowLive ? 'draft' : 'published', null, true) }
                );
                fetchBlogs(currentPage);
            })
            .catch(err => {
                const needsAuthor = /author/i.test(err.message || '');
                Swal.fire({
                    title: 'Status not updated',
                    text: err.message || 'Network error.',
                    icon: 'error',
                    showCancelButton: needsAuthor,
                    confirmButtonText: needsAuthor ? 'Add author now' : 'OK',
                    cancelButtonText: 'Later',
                    confirmButtonColor: '#18529c'
                }).then(r => {
                    if (needsAuthor && r.isConfirmed) {
                        loadBlogForEdit(id, 'trust');
                    }
                });
            })
            .finally(() => { if (btn) btn.disabled = false; });
    }

    function deleteBlog(id) {
        const row = document.getElementById(`blog-row-${id}`);
        const title = row ? (row.querySelector('.bl-title')?.textContent || '') : '';
        Swal.fire({
            title: 'Delete this article?',
            html: '<b></b><br>It will be removed from the website and this list. A copy is kept in the deleted-records archive.',
            icon: 'warning',
            showCancelButton: true,
            focusCancel: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Delete',
            didOpen: popup => { const b = popup.querySelector('.swal2-html-container b'); if (b) b.textContent = title || `Article #${id}`; }
        }).then(result => {
            if (!result.isConfirmed) return;
            Swal.fire({ title: 'Deleting…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            const fd = new FormData();
            fd.append('action', 'delete_blog');
            fd.append('id', id);
            fd.append('csrf_token', csrfToken);
            fetch('api.php', { method: 'POST', body: fd })
                .then(res => res.json())
                .then(data => {
                    if (data.status !== 'success') throw new Error(data.message || 'Deletion failed.');
                    Swal.fire({ title: 'Deleted', text: data.message || 'Article deleted.', icon: 'success', timer: 1500, showConfirmButton: false });
                    fetchBlogs(currentPage);
                })
                .catch(err => Swal.fire({ title: 'Deletion failed', text: err.message || 'Network error.', icon: 'error', confirmButtonColor: '#dc2626' }));
        });
    }

    // One delegated handler for every row/pagination control (rows are re-rendered on each fetch).
    document.getElementById('listPanel').addEventListener('click', e => {
        const pageLink = e.target.closest('[data-page]');
        if (pageLink) {
            e.preventDefault();
            fetchBlogs(parseInt(pageLink.getAttribute('data-page'), 10) || 1);
            return;
        }
        const actEl = e.target.closest('[data-act]');
        if (!actEl) return;
        const act = actEl.getAttribute('data-act');
        if (act === 'edit') loadBlogForEdit(actEl.getAttribute('data-id'));
        else if (act === 'toggle') toggleBlogStatus(actEl.getAttribute('data-id'), actEl.getAttribute('data-next-status') || 'draft', actEl);
        else if (act === 'delete') deleteBlog(actEl.getAttribute('data-id'));
        else if (act === 'clear-filters') clearListFilters();
        else if (act === 'retry') fetchBlogs(currentPage);
    });

    // ---------- Editor: load one article by id (works for any page and for #edit-ID links) ----------
    function populateEditor(blog) {
        blogIdInput.value = blog.id;
        titleInput.value = blog.title || '';
        slugInput.value = blog.slug || '';
        excerptInput.value = blog.excerpt || '';
        categoryInput.value = blog.category || 'personal';
        readTimeInput.value = blog.read_time || '5 Min Read';
        readTimeTouched = true;
        statusInput.value = blog.status === 'published' ? 'published' : 'draft';
        if (removeImageInput) removeImageInput.value = '0';
        if (fileInput) fileInput.value = '';
        metaTitleInput.value = blog.meta_title || '';
        metaDescInput.value = blog.meta_description || '';
        metaKeywordsInput.value = blog.meta_keywords || '';
        document.getElementById('author_name').value = blog.author_name || '';
        document.getElementById('author_designation').value = blog.author_designation || '';
        document.getElementById('reviewed_by').value = blog.reviewed_by || '';
        document.getElementById('cta_text').value = blog.cta_text || '';
        document.getElementById('cta_link').value = blog.cta_link || '';
        if (tinymce.get('editor-container')) tinymce.get('editor-container').setContent(blog.content || '');

        const src = coverSrc(blog.image_url);
        if (src) {
            imgPreview.onerror = () => {
                imgPreview.classList.add('hidden');
                previewPlaceholder.classList.remove('hidden');
                previewPlaceholder.textContent = 'Current cover file was not found on this server. Upload a new one to replace it.';
                updateReadiness();
            };
            imgPreview.src = src;
            imgPreview.classList.remove('hidden');
            previewPlaceholder.classList.add('hidden');
            previewPlaceholder.textContent = 'No cover selected';
            if (clearImageBtn) clearImageBtn.classList.remove('hidden');
            if (dropzoneText) dropzoneText.textContent = 'Click or drop an image to replace the cover';
        } else {
            resetCoverPreview(false);
        }

        const faqsContainer = document.getElementById('faqsRowContainer');
        faqsContainer.innerHTML = '';
        let faqsArr = [];
        try { faqsArr = JSON.parse(blog.faqs || '[]') || []; } catch (err) { faqsArr = []; }
        faqsArr.forEach(faq => addFaqRow(faq.q || '', faq.a || ''));

        refreshCharCounters();
        updateSlugPreview();
        updateContentMetrics();
        updateGooglePreview();
        updateReadiness();
        checkRequiredFormFields();
    }

    function refreshCharCounters() {
        [titleInput, excerptInput, metaTitleInput, metaDescInput].forEach(el => el && el.dispatchEvent(new Event('change')));
    }

    function loadBlogForEdit(id, tab = 'settings') {
        Swal.fire({ title: 'Opening article…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        const fd = new FormData();
        fd.append('action', 'get_blog');
        fd.append('id', id);
        fd.append('csrf_token', csrfToken);
        return fetch('api.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success' || !data.blog) throw new Error(data.message || 'Article not found.');
                Swal.close();
                populateEditor(data.blog);
                switchSettingsTab(tab);
                if (location.hash !== '#edit-' + data.blog.id) history.replaceState(null, '', location.pathname + location.search + '#edit-' + data.blog.id);
                showForm('Edit article');
                markFormClean();
            })
            .catch(err => {
                Swal.fire({ title: 'Could not open article', text: err.message || 'Network error.', icon: 'error', confirmButtonColor: '#18529c' });
                showList(true);
            });
    }

    // Draft Auto-Save system
    const draftStorage = {
        getItem(key) { try { return localStorage.getItem(key); } catch (error) { return null; } },
        setItem(key, value) { try { localStorage.setItem(key, value); } catch (error) {} },
        removeItem(key) { try { localStorage.removeItem(key); } catch (error) {} }
    };
    const AUTO_SAVE_KEY = 'waqt_finance_blog_draft';
    const recoveryBanner = document.getElementById('recoveryBanner');
    const restoreDraftBtn = document.getElementById('restoreDraftBtn');
    const discardDraftBtn = document.getElementById('discardDraftBtn');
    const recoveryTime = document.getElementById('recoveryTime');

    function saveDraftToLocalStorage() {
        if (blogIdInput.value !== "0" || formPanel.classList.contains("hidden") || !recoveryBanner.classList.contains("hidden")) return;

        const title = titleInput.value.trim();
        const excerpt = excerptInput.value.trim();
        const content = tinymce.get('editor-container') ? tinymce.get('editor-container').getContent() : '';
        const slug = slugInput.value.trim();
        const category = categoryInput.value;
        const readTime = readTimeInput.value.trim();
        const authorName = document.getElementById('author_name').value.trim();
        const authorDesignation = document.getElementById('author_designation').value.trim();
        const reviewedBy = document.getElementById('reviewed_by').value.trim();
        const ctaText = document.getElementById('cta_text').value.trim();
        const ctaLink = document.getElementById('cta_link').value.trim();
        const metaTitle = metaTitleInput.value.trim();
        const metaDesc = metaDescInput.value.trim();
        const metaKeywords = metaKeywordsInput.value.trim();

        const textContent = tinymce.get('editor-container') ? tinymce.get('editor-container').getContent({format: 'text'}).trim() : '';
        if (title === '' && textContent === '') return;

        const draftData = {
            title, slug, excerpt, content, category, readTime,
            authorName, authorDesignation, reviewedBy, ctaText, ctaLink,
            metaTitle, metaDesc, metaKeywords,
            faqs: Array.from(document.querySelectorAll("#faqsRowContainer .dynamic-row-item")).map(row => ({ q: row.querySelector(".faq-question-input").value, a: row.querySelector(".faq-answer-input").value })),
            savedAt: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        };

        try { draftStorage.setItem(AUTO_SAVE_KEY, JSON.stringify(draftData)); } catch (error) { console.warn("Browser draft storage unavailable", error); }
    }

    setInterval(saveDraftToLocalStorage, 10000);

    function checkAndShowDraftRecovery() {
        if (blogIdInput.value !== "0") return;

        const savedDataStr = draftStorage.getItem(AUTO_SAVE_KEY);
        if (savedDataStr) {
            try {
                const draft = JSON.parse(savedDataStr);
                if (recoveryBanner && recoveryTime) {
                    recoveryTime.textContent = draft.savedAt;
                    recoveryBanner.classList.remove('hidden');
                }
            } catch (e) {
                draftStorage.removeItem(AUTO_SAVE_KEY);
            }
        }
    }

    if (restoreDraftBtn) {
        restoreDraftBtn.addEventListener('click', () => {
            const savedDataStr = draftStorage.getItem(AUTO_SAVE_KEY);
            if (savedDataStr) {
                try {
                    const draft = JSON.parse(savedDataStr);
                    
                    titleInput.value = draft.title || '';
                    slugInput.value = draft.slug || '';
                    excerptInput.value = draft.excerpt || '';
                    categoryInput.value = draft.category || 'personal';
                    readTimeInput.value = draft.readTime || '5 Min Read';
                    readTimeTouched = true;
                    
                    document.getElementById('author_name').value = draft.authorName || '';
                    document.getElementById('author_designation').value = draft.authorDesignation || '';
                    document.getElementById('reviewed_by').value = draft.reviewedBy || '';
                    document.getElementById('cta_text').value = draft.ctaText || '';
                    document.getElementById('cta_link').value = draft.ctaLink || '';
                    
                    metaTitleInput.value = draft.metaTitle || '';
                    metaDescInput.value = draft.metaDesc || '';
                    metaKeywordsInput.value = draft.metaKeywords || '';

                    if (draft.content && tinymce.get('editor-container')) {
                        tinymce.get('editor-container').setContent(draft.content);
                    }

                    slugManuallyEdited = true;
                    document.getElementById('faqsRowContainer').innerHTML = '';
                    (draft.faqs || []).forEach(faq => addFaqRow(faq.q, faq.a));
                    refreshCharCounters();
                    updateSlugPreview();
                    updateContentMetrics();
                    updateGooglePreview();
                    updateReadiness();
                    checkRequiredFormFields();
                    Swal.fire({
                        title: 'Draft Restored!',
                        text: 'Your auto-saved progress has been recovered successfully.',
                        icon: 'success',
                        confirmButtonColor: '#18529c'
                    });
                } catch (e) {
                    console.error(e);
                }
            }
            if (recoveryBanner) recoveryBanner.classList.add('hidden');
        });
    }

    if (discardDraftBtn) {
        discardDraftBtn.addEventListener('click', () => {
            draftStorage.removeItem(AUTO_SAVE_KEY);
            if (recoveryBanner) recoveryBanner.classList.add('hidden');
        });
    }

    // Tabs switching logic
    window.switchSettingsTab = function(tabId) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

        const activeBtn = document.getElementById('tabBtn-' + tabId);
        const activeContent = document.getElementById('tabContent-' + tabId);

        if (activeBtn) activeBtn.classList.add('active');
        if (activeContent) activeContent.classList.add('active');
    };

    document.querySelectorAll('.tab-btn').forEach((btn, index, buttons) => {
        const key = btn.id.replace('tabBtn-', '');
        btn.setAttribute('role', 'tab');
        btn.setAttribute('aria-controls', 'tabContent-' + key);
        const panel = document.getElementById('tabContent-' + key);
        panel.setAttribute('role', 'tabpanel');
        panel.setAttribute('aria-labelledby', btn.id);
        btn.addEventListener('keydown', e => {
            let next = index;
            if (e.key === 'ArrowRight') next = (index + 1) % buttons.length;
            else if (e.key === 'ArrowLeft') next = (index + buttons.length - 1) % buttons.length;
            else if (e.key === 'Home') next = 0;
            else if (e.key === 'End') next = buttons.length - 1;
            else return;
            e.preventDefault(); buttons[next].click(); buttons[next].focus();
        });
    });
    const switchTab = window.switchSettingsTab;
    window.switchSettingsTab = function(key) {
        switchTab(key);
        document.querySelectorAll('.tab-btn').forEach(btn => {
            const active = btn.classList.contains('active');
            btn.setAttribute('aria-selected', String(active)); btn.tabIndex = active ? 0 : -1;
        });
    };
    switchSettingsTab('settings');

    // Dynamic row handlers for FAQs
    window.addFaqRow = function(q = '', a = '') {
        const container = document.getElementById('faqsRowContainer');
        
        // Validation: If adding a new row (empty q & a), check if the last FAQ is completed
        if (q === '' && a === '') {
            const rows = container.querySelectorAll('.dynamic-row-item');
            if (rows.length > 0) {
                const lastRow = rows[rows.length - 1];
                const qInput = lastRow.querySelector('.faq-question-input');
                const aInput = lastRow.querySelector('.faq-answer-input');
                if (qInput && aInput && (qInput.value.trim() === '' || aInput.value.trim() === '')) {
                    Swal.fire({
                        title: 'Incomplete FAQ!',
                        text: 'Please complete the question and answer of the previous FAQ before adding another.',
                        icon: 'warning',
                        confirmButtonColor: 'var(--primary)'
                    });
                    return;
                }
            }
        }

        const randId = Math.random().toString(36).substring(2, 9);
        
        const row = document.createElement('div');
        row.className = 'dynamic-row-item';
        row.id = 'faq-row-' + randId;
        
        row.innerHTML = `
            <div style="flex:1; display:flex; flex-direction:column; gap:6px; margin-bottom: 8px;">
                <input type="text" class="form-control-adm faq-question-input" value="${escapeHtml(q)}" aria-label="FAQ question" placeholder="Question about this article" required style="width: 100%;">
                <textarea class="form-control-adm faq-answer-input" aria-label="FAQ answer" placeholder="Answer..." required style="width: 100%; min-height: 60px;">${escapeHtml(a)}</textarea>
            </div>
            <button type="button" class="btn-remove-row" aria-label="Remove FAQ" onclick="removeRow('faq-row-${randId}')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;
        container.appendChild(row);
    };

    window.removeRow = function(rowId) {
        const row = document.getElementById(rowId);
        if (row) {
            row.remove();
        }
    };

    // ---------- Unsaved-changes tracking ----------
    let formSnapshot = '';
    function formStateString() {
        const fields = Array.from(blogForm.querySelectorAll('input:not([type="file"]):not([type="hidden"]), select, textarea'))
            .filter(el => el.id !== 'editor-container')
            .map(el => el.value);
        const editor = tinymce.get('editor-container');
        fields.push(editor ? editor.getContent() : '');
        fields.push(fileInput && fileInput.files && fileInput.files.length ? 'new-file' : '');
        fields.push(removeImageInput ? removeImageInput.value : '');
        return JSON.stringify(fields);
    }
    function markFormClean() {
        // TinyMCE normalises content a moment after setContent, so take the snapshot after that settles.
        setTimeout(() => { formSnapshot = formStateString(); }, 200);
    }
    function isFormDirty() {
        return !formPanel.classList.contains('hidden') && formSnapshot !== '' && formStateString() !== formSnapshot;
    }
    function confirmDiscard() {
        if (!isFormDirty()) return Promise.resolve(true);
        return Swal.fire({
            title: 'Discard unsaved changes?',
            text: 'Your edits to this article have not been saved.',
            icon: 'warning',
            showCancelButton: true,
            focusCancel: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Discard',
            cancelButtonText: 'Keep editing'
        }).then(r => r.isConfirmed);
    }
    window.addEventListener('beforeunload', e => {
        if (isFormDirty()) { e.preventDefault(); e.returnValue = ''; }
    });

    // ---------- Panels & routing ----------
    function showForm(titleText) {
        listPanel.classList.add('hidden');
        formPanel.classList.remove('hidden');
        formTitle.innerHTML = `<i class="fa-solid fa-pen-nib" style="color: var(--primary); margin-right: 8px;"></i> ${escapeHtml(titleText)}`;
        checkRequiredFormFields();
        window.scrollTo({ top: 0 });
    }

    function showList(force = false) {
        const go = () => {
            formSnapshot = '';
            formPanel.classList.add('hidden');
            listPanel.classList.remove('hidden');
            if (recoveryBanner) recoveryBanner.classList.add('hidden');
            if (location.hash) history.replaceState(null, '', location.pathname + location.search);
        };
        if (force) { go(); return Promise.resolve(true); }
        return confirmDiscard().then(ok => { if (ok) go(); return ok; });
    }

    function openCreateForm() {
        blogForm.reset();
        slugManuallyEdited = false;
        blogIdInput.value = '0';
        readTimeTouched = false;
        if (tinymce.get('editor-container')) tinymce.get('editor-container').setContent('');
        resetCoverPreview(false);
        if (removeImageInput) removeImageInput.value = '0';
        document.getElementById('faqsRowContainer').innerHTML = '';
        blogForm.querySelectorAll('.is-invalid, .is-valid').forEach(el => el.classList.remove('is-invalid', 'is-valid'));
        blogForm.querySelectorAll('.invalid-feedback-text').forEach(el => el.remove());
        switchSettingsTab('settings');
        refreshCharCounters();
        updateSlugPreview();
        updateContentMetrics();
        updateGooglePreview();
        updateReadiness();
        checkAndShowDraftRecovery();
        checkRequiredFormFields();
        if (location.hash !== '#create') history.replaceState(null, '', location.pathname + location.search + '#create');
        showForm('Write new article');
        markFormClean();
        setTimeout(() => titleInput.focus(), 50);
    }

    function handleRouting() {
        const hash = location.hash;
        if (hash === '#create') {
            if (formPanel.classList.contains('hidden') || blogIdInput.value !== '0') openCreateForm();
        } else if (hash.startsWith('#edit-')) {
            const id = parseInt(hash.replace('#edit-', ''), 10);
            if (id > 0 && (formPanel.classList.contains('hidden') || String(blogIdInput.value) !== String(id))) loadBlogForEdit(id);
            else if (!(id > 0)) showList(true);
        } else if (!formPanel.classList.contains('hidden')) {
            showList();
        }
    }
    window.addEventListener('hashchange', handleRouting);

    document.getElementById('createPostBtn').addEventListener('click', openCreateForm);
    document.getElementById('backToListBtn').addEventListener('click', () => showList());
    document.getElementById('cancelBtn').addEventListener('click', () => showList());

    // Form Submit Handler
    blogForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!blogForm.checkValidity()) {
            const invalid = blogForm.querySelector(':invalid');
            const panel = invalid && invalid.closest('.tab-content');
            if (panel) switchSettingsTab(panel.id.replace('tabContent-', ''));
            if (invalid) { invalid.focus(); invalid.reportValidity(); }
            return;
        }
        
        if (document.getElementById('saveBlogBtn').disabled) return;
        // Sync editor content back to textarea
        tinymce.triggerSave();
        
        let isValid = true;
        
        // 1. Title Validate
        const titleVal = titleInput.value.trim();
        if (titleVal.length < 5 || titleVal.length > 150) {
            isValid = setInvalid(titleInput, "Title must be between 5 and 150 characters.") && isValid;
        } else {
            setValid(titleInput);
        }
        
        // 2. Slug Validate
        const slugVal = slugInput.value.trim();
        const slugRegex = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;
        if (!slugRegex.test(slugVal)) {
            isValid = setInvalid(slugInput, "Slug must be lowercase alphanumeric characters separated by hyphens (e.g. my-article-slug).") && isValid;
        } else {
            setValid(slugInput);
        }
        
        // 3. Excerpt Validate
        const excerptVal = excerptInput.value.trim();
        if (excerptVal.length > 0 && (excerptVal.length < 10 || excerptVal.length > 300)) {
            isValid = setInvalid(excerptInput, "Excerpt must be between 10 and 300 characters.") && isValid;
        } else {
            setValid(excerptInput);
        }
        
        // 4. Content Body Validate
        const editorText = tinymce.get('editor-container') ? tinymce.get('editor-container').getContent({format: 'text'}).trim() : '';
        if (editorText === '') {
            Swal.fire({
                title: 'Content Required',
                text: 'Blog content body cannot be empty.',
                icon: 'warning',
                confirmButtonColor: '#18529c'
            });
            return;
        }
        
        // 5. Author Name Validation (E-E-A-T guideline)
        const authorEl = document.getElementById('author_name');
        const authorVal = authorEl.value.trim();
        if (statusInput.value === 'published' && authorVal === '') {
            switchSettingsTab('trust');
            isValid = setInvalid(authorEl, "Author Name is required for active articles (E-E-A-T guidelines).") && isValid;
            
            Swal.fire({
                title: 'E-E-A-T Trust Score Alert',
                text: 'Please add an author name in the "Author & CTA" tab before making this article active.',
                icon: 'info',
                confirmButtonColor: '#18529c'
            });
            return;
        } else {
            setValid(authorEl);
        }
        
        // 6. CTA Link Validation
        const ctaTextEl = document.getElementById('cta_text');
        const ctaLinkEl = document.getElementById('cta_link');
        if (ctaTextEl.value.trim() !== '' && ctaLinkEl.value.trim() === '') {
            switchSettingsTab('trust');
            isValid = setInvalid(ctaLinkEl, "CTA target link is required if CTA text is provided.") && isValid;
            
            Swal.fire({
                title: 'CTA Field Required',
                text: 'Please enter a target link for your call-to-action in the "Author & CTA" tab.',
                icon: 'warning',
                confirmButtonColor: '#18529c'
            });
            return;
        } else {
            setValid(ctaLinkEl);
        }
        
        if (!isValid) {
            const first = blogForm.querySelector('.is-invalid');
            if (first) first.focus();
            Swal.fire({
                title: 'Validation Errors',
                text: 'Please review and fix the highlighted fields before saving.',
                icon: 'error',
                confirmButtonColor: '#dc2626'
            });
            return;
        }

        Swal.fire({
            title: 'Saving Article...',
            text: 'Processing your request, please wait.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const saveButton = document.getElementById('saveBlogBtn');
        saveButton.disabled = true;
        try {
            const editor = tinymce.get('editor-container');
            if (editor) {
                const uploads = await editor.uploadImages();
                if (uploads.some(upload => upload.status === false)) throw new Error('An article image could not upload. Retry before saving.');
                editor.save();
            }
        } catch (error) {
            saveButton.disabled = false;
            Swal.fire({ title: 'Image upload failed', text: error.message || 'Please retry the image upload.', icon: 'error' });
            return;
        }
        const formData = new FormData(this);
        formData.append('csrf_token', csrfToken);

        // Prepare FAQs array
        const faqRows = document.querySelectorAll('#faqsRowContainer .dynamic-row-item');
        const faqsArr = [];
        faqRows.forEach(row => {
            const q = row.querySelector('.faq-question-input').value.trim();
            const a = row.querySelector('.faq-answer-input').value.trim();
            if (q !== '' || a !== '') {
                faqsArr.push({ q: q, a: a });
            }
        });
        formData.append('faqs', JSON.stringify(faqsArr));

        fetch('api.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                draftStorage.removeItem(AUTO_SAVE_KEY);
                const wasNew = blogIdInput.value === '0';
                Swal.fire({
                    title: 'Article saved',
                    text: statusInput.value === 'published' ? 'It is live on the website.' : 'It is saved as inactive (hidden from the website).',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
                showList(true);
                fetchBlogs(wasNew ? 1 : currentPage);
            } else {
                throw new Error(data.message || 'Operation failed.');
            }
        })
        .catch(err => {
            Swal.fire({
                title: 'Error Saving',
                text: err.message || 'Network error occurred.',
                icon: 'error',
                confirmButtonColor: '#dc2626'
            });
        }).finally(() => { saveButton.disabled = false; });
    });

    // Initial bindings & routing check on page load
    renderBlogList(JSON.parse(document.getElementById('blogInitialData').textContent || '{}'));
    handleRouting();
</script>

<?php include 'footer.php'; ?>
