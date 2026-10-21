<?php
declare(strict_types=1);

$pageTitle = 'Blog CMS Control';
$pageSubtitle = 'Create, update, and manage dynamic articles for the website';

include 'header.php';

$conn = db();

// Pagination Config
$limit = 10;
$page = (int) ($_GET['page'] ?? 1);
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Count total
$countRes = $conn->query("SELECT COUNT(*) FROM blogs");
$totalRecords = (int) ($countRes->fetch_row()[0] ?? 0);
$totalPages = (int) ceil($totalRecords / $limit);
if ($totalPages < 1) $totalPages = 1;

// Article visibility stats
$activeRecords = (int) ($conn->query("SELECT COUNT(*) FROM blogs WHERE status = 'published'")->fetch_row()[0] ?? 0);
$inactiveRecords = (int) ($conn->query("SELECT COUNT(*) FROM blogs WHERE status = 'draft'")->fetch_row()[0] ?? 0);
$categoriesCount = (int) ($conn->query("SELECT COUNT(DISTINCT category) FROM blogs")->fetch_row()[0] ?? 0);

// Fetch blogs
$blogs = [];
$stmt = $conn->prepare("SELECT * FROM blogs ORDER BY id DESC LIMIT ? OFFSET ?");
$stmt->bind_param('ii', $limit, $offset);
$stmt->execute();
$res = $stmt->get_result();
if ($res) {
    $blogs = $res->fetch_all(MYSQLI_ASSOC);
}
?>

<!-- TinyMCE Rich Text Editor CDN -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
<!-- SweetAlert2 CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        grid-template-columns: repeat(4, 1fr);
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
</style>

<!-- BLOG LIST PANEL -->
<div class="data-card panel-card" id="listPanel">
    <!-- Dashboard Stats Grid -->
    <div class="cms-stats-grid">
        <div class="cms-stat-card">
            <div class="cms-stat-icon stat-blue">
                <i class="fa-solid fa-newspaper"></i>
            </div>
            <div class="cms-stat-info">
                <h3><?php echo $totalRecords; ?></h3>
                <p>Total Articles</p>
            </div>
        </div>
        
        <div class="cms-stat-card">
            <div class="cms-stat-icon stat-green">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="cms-stat-info">
                <h3 id="activeArticlesCount"><?php echo $activeRecords; ?></h3>
                <p>Active</p>
            </div>
        </div>
        
        <div class="cms-stat-card">
            <div class="cms-stat-icon stat-yellow">
                <i class="fa-solid fa-pen-nib"></i>
            </div>
            <div class="cms-stat-info">
                <h3 id="inactiveArticlesCount"><?php echo $inactiveRecords; ?></h3>
                <p>Inactive</p>
            </div>
        </div>
        
        <div class="cms-stat-card">
            <div class="cms-stat-icon stat-red">
                <i class="fa-solid fa-tags"></i>
            </div>
            <div class="cms-stat-info">
                <h3><?php echo $categoriesCount; ?></h3>
                <p>Categories</p>
            </div>
        </div>
    </div>

    <div class="card-header-flex">
        <h2 class="card-title-text"><i class="fa-solid fa-newspaper" style="color: var(--primary); margin-right: 8px;"></i> Articles</h2>
        <button type="button" class="btn-primary-adm" id="createPostBtn">
            <i class="fa-solid fa-plus"></i> Write New Article
        </button>
    </div>

    <!-- Search & Filter Controls -->
    <div class="list-controls-flex">
        <div class="cms-search-input">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="cmsSearch" placeholder="Search by title, author, slug or status...">
        </div>
        
        <select id="cmsCategoryFilter" class="cms-filter-select">
            <option value="">All Categories</option>
            <option value="personal">Personal Loan</option>
            <option value="business">Business Loan</option>
            <option value="edi">EDI Small Loan</option>
            <option value="vehicle">Vehicle Loan</option>
            <option value="lap">Loan Against Property</option>
            <option value="payday">PayDay Loan</option>
            <option value="finance">Finance Tips</option>
        </select>
        
        <select id="cmsStatusFilter" class="cms-filter-select">
            <option value="">All Statuses</option>
            <option value="published">Active</option>
            <option value="draft">Inactive</option>
        </select>
    </div>

    <div class="table-responsive" style="position: relative;" id="blogsTableContainer">
        <!-- Suspense Loader Overlay -->
        <div class="table-loading-overlay" id="tableLoadingOverlay">
            <div class="suspense-spinner">
                <i class="fa-solid fa-circle-notch fa-spin"></i>
                <span>Loading Articles...</span>
            </div>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Cover</th>
                    <th>Article Title</th>
                    <th>Category</th>
                    <th>Slug URL</th>
                    <th>Read Time</th>
                    <th>Views</th>
                    <th>Status</th>
                    <th>Created Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="blogsTableBody">
                <?php if (empty($blogs)): ?>
                    <tr>
                        <td colspan="9" class="text-center" style="color: var(--text-muted); padding: 40px 0;">No articles written yet. Click "Write New Article" to get started!</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($blogs as $b): ?>
                        <?php
                            $blogStatus = (($b['status'] ?? 'draft') === 'published') ? 'published' : 'draft';
                            $isActive = $blogStatus === 'published';
                            $statusLabel = $isActive ? 'Active' : 'Inactive';
                            $nextStatus = $isActive ? 'draft' : 'published';
                            $toggleTitle = $isActive ? 'Set Inactive' : 'Set Active';
                        ?>
                        <tr id="blog-row-<?php echo (int) $b['id']; ?>"
                            data-status="<?php echo htmlspecialchars($blogStatus, ENT_QUOTES, 'UTF-8'); ?>"
                            data-status-label="<?php echo strtolower($statusLabel); ?>"
                            data-category="<?php echo htmlspecialchars(strtolower((string) ($b['category'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>"
                            data-author="<?php echo htmlspecialchars(strtolower((string) ($b['author_name'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>">
                            <td>
                                <img src="../<?php echo htmlspecialchars($b['image_url'] ?? 'assets/images/default-blog.jpg', ENT_QUOTES, 'UTF-8'); ?>" class="blog-thumb" alt="cover">
                            </td>
                            <td>
                                <strong style="color: var(--text-main); font-weight: 700;"><?php echo htmlspecialchars($b['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            </td>
                            <td><span style="text-transform: capitalize; color: var(--text-main);"><?php echo htmlspecialchars($b['category'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                            <td style="font-family: monospace; font-size:12px; color: var(--info);">/blog/<?php echo htmlspecialchars($b['slug'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($b['read_time'] ?? '5 Min Read', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><strong style="color: var(--text-main);"><?php echo number_format((int)($b['views'] ?? 0)); ?></strong></td>
                            <td>
                                <span class="badge-status <?php echo $isActive ? 'badge-approved' : 'badge-draft'; ?> status-badge"><?php echo $statusLabel; ?></span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($b['created_at'])); ?></td>
                            <td>
                                <div class="row-actions">
                                    <button type="button" class="btn-action edit-btn" data-blog='<?php echo htmlspecialchars(json_encode($b, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8'); ?>' title="Edit Article">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button"
                                            class="btn-action status-toggle-btn <?php echo $isActive ? 'is-active' : 'is-inactive'; ?>"
                                            data-id="<?php echo (int) $b['id']; ?>"
                                            data-next-status="<?php echo htmlspecialchars($nextStatus, ENT_QUOTES, 'UTF-8'); ?>"
                                            title="<?php echo $toggleTitle; ?>"
                                            aria-label="<?php echo $toggleTitle; ?>">
                                        <i class="fa-solid <?php echo $isActive ? 'fa-toggle-on' : 'fa-toggle-off'; ?>"></i>
                                    </button>
                                    <button type="button" class="btn-action delete-btn" data-id="<?php echo (int) $b['id']; ?>" title="Delete Article">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div id="paginationWrapper" style="margin-top: 20px;">
        <?php if ($totalPages > 1): ?>
            <ul class="pagination">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <?php if ($page <= 1): ?>
                        <span style="opacity:0.5; cursor:not-allowed;">&laquo; Prev</span>
                    <?php else: ?>
                        <a href="#" data-page="<?php echo $page - 1; ?>">&laquo; Prev</a>
                    <?php endif; ?>
                </li>
                
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <li class="page-item <?php echo $p === $page ? 'active' : ''; ?>">
                        <?php if ($p === $page): ?>
                            <span><?php echo $p; ?></span>
                        <?php else: ?>
                            <a href="#" data-page="<?php echo $p; ?>"><?php echo $p; ?></a>
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>
                
                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                    <?php if ($page >= $totalPages): ?>
                        <span style="opacity:0.5; cursor:not-allowed;">Next &raquo;</span>
                    <?php else: ?>
                        <a href="#" data-page="<?php echo $page + 1; ?>">Next &raquo;</a>
                    <?php endif; ?>
                </li>
            </ul>
        <?php endif; ?>
    </div>
</div>

<!-- BLOG FORM PANEL (EDIT / CREATE) -->
<div class="data-card panel-card hidden" id="formPanel">
    <div class="card-header-flex" style="border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 25px;">
        <h2 class="card-title-text" id="formTitle"><i class="fa-solid fa-pen-nib" style="color: var(--primary); margin-right: 8px;"></i> Write New Article</h2>
        <button type="button" class="btn-action" id="backToListBtn" style="width: auto; padding: 0 16px; height: 38px; display: inline-flex; align-items: center; gap: 8px;" title="Cancel editing">
            <i class="fa-solid fa-arrow-left-long"></i> Back to List
        </button>
    </div>

    <form id="blogForm" method="POST" enctype="multipart/form-data">
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
                    <label class="form-label" for="title">Article Title</label>
                    <input type="text" name="title" id="title" class="form-control-adm" placeholder="Enter an engaging, catchy title..." required>
                    <div class="char-counter" id="title-counter">0 / 150 characters</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="slug">URL Slug</label>
                    <input type="text" name="slug" id="slug" class="form-control-adm" placeholder="url-slug-goes-here" required>
                    <div class="field-support-row">
                        <div class="slug-preview">
                            <i class="fa-solid fa-link"></i>
                            <span>/blog/</span><strong id="slugPreviewText">url-slug-goes-here</strong>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Excerpt / Summary</label>
                    <textarea name="excerpt" id="excerpt" class="form-control-adm" placeholder="Brief summary of the article (useful for feeds)..."></textarea>
                    <div class="char-counter" id="excerpt-counter">0 / 300 characters</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Content Body</label>
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
                <div class="tabs-nav">
                    <button type="button" class="tab-btn active" id="tabBtn-settings" onclick="switchSettingsTab('settings')"><i class="fa-solid fa-sliders"></i> Settings</button>
                    <button type="button" class="tab-btn" id="tabBtn-trust" onclick="switchSettingsTab('trust')"><i class="fa-solid fa-user-pen"></i> EEAT & CTA</button>
                    <button type="button" class="tab-btn" id="tabBtn-seo" onclick="switchSettingsTab('seo')"><i class="fa-solid fa-magnifying-glass-chart"></i> SEO Engine</button>
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
                            <option value="draft">Inactive</option>
                            <option value="published">Active</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Featured Cover Image</label>
                        <!-- Drag & Drop Zone -->
                        <div class="upload-dropzone" id="dropzone">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <p id="dropzoneText">Drag and drop cover image here or click to select</p>
                            <span>Supports JPG, PNG, WEBP up to 5MB</span>
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
            <span id="formFeedback" style="align-self: center; font-weight: 600; font-size: 13.5px;"></span>
            <button type="button" class="btn-action" id="cancelBtn" style="width: auto; padding:0 24px; height: 42px;">Cancel</button>
            <button type="submit" class="btn-primary-adm" id="saveBlogBtn" style="padding: 0 30px; height: 42px; display: inline-flex; align-items: center; justify-content: center;">Save Post</button>
        </div>
    </form>
</div>

<script>
    // Initialize TinyMCE Editor
    tinymce.init({
        selector: '#editor-container',
        height: 500,
        menubar: 'file edit view insert format tools table help',
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

                    const json = JSON.parse(xhr.responseText);

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
        el.classList.add('is-invalid');
        el.classList.remove('is-valid');
        
        let feedback = el.parentNode.querySelector('.invalid-feedback-text');
        if (!feedback) {
            feedback = document.createElement('span');
            feedback.className = 'invalid-feedback-text';
            el.parentNode.appendChild(feedback);
        }
        feedback.textContent = msg;
        return false;
    }

    function setValid(el) {
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
        
        const titleVal = titleInput.value.trim();
        const slugVal = slugInput.value.trim();
        const categoryVal = categoryInput.value;
        
        // TinyMCE content check
        const editorText = tinymce.get('editor-container') ? tinymce.get('editor-container').getContent({format: 'text'}).trim() : '';
        
        // E-E-A-T Author check if active
        const authorVal = document.getElementById('author_name').value.trim();
        const isPublished = statusInput.value === 'published';
        const authorOk = !isPublished || authorVal !== '';
        
        // CTA Link check if CTA text is provided
        const ctaText = document.getElementById('cta_text').value.trim();
        const ctaLink = document.getElementById('cta_link').value.trim();
        const ctaOk = ctaText === '' || ctaLink !== '';
        
        // Basic validators
        const titleOk = titleVal.length >= 5 && titleVal.length <= 150;
        const slugRegex = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;
        const slugOk = slugRegex.test(slugVal);
        const contentOk = editorText !== '';
        
        if (titleOk && slugOk && categoryVal !== '' && contentOk && authorOk && ctaOk) {
            saveBlogBtn.removeAttribute('disabled');
            saveBlogBtn.style.opacity = '1';
            saveBlogBtn.style.cursor = 'pointer';
        } else {
            saveBlogBtn.setAttribute('disabled', 'true');
            saveBlogBtn.style.opacity = '0.5';
            saveBlogBtn.style.cursor = 'not-allowed';
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

    // Auto-generate slug from title
    titleInput.addEventListener('input', function() {
        if (blogIdInput.value === "0") { // Only autogenerate for new posts
            slugInput.value = slugifyText(this.value);
        }
        updateSlugPreview();
        updateReadiness();
        checkRequiredFormFields();
    });

    slugInput.addEventListener('input', function() {
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

    function fetchBlogs(page = 1) {
        const query = cmsSearch ? cmsSearch.value.trim() : '';
        const catVal = cmsCategoryFilter ? cmsCategoryFilter.value : '';
        const statusVal = cmsStatusFilter ? cmsStatusFilter.value : '';
        
        // Show suspense overlay loader
        const loader = document.getElementById('tableLoadingOverlay');
        if (loader) loader.classList.add('active');
        
        const formData = new FormData();
        formData.append('action', 'get_blogs');
        formData.append('page', page);
        formData.append('search', query);
        formData.append('category', catVal);
        formData.append('status', statusVal);
        formData.append('csrf_token', csrfToken);

        fetch('api.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                // Hide loader
                if (loader) loader.classList.remove('active');
                if (data.status === 'success') {
                    blogsTableBody.innerHTML = data.html;
                    const paginationWrapper = document.getElementById('paginationWrapper');
                    if (paginationWrapper) {
                        paginationWrapper.innerHTML = data.pagination;
                        bindPaginationEvents();
                    }
                    currentPage = data.page;
                    
                    const newUrl = window.location.pathname + '?page=' + data.page;
                    history.pushState({ page: data.page }, '', newUrl);

                    bindRowActionEvents();
                } else {
                    if (loader) loader.classList.remove('active');
                    blogsTableBody.innerHTML = '<tr><td colspan="9" class="text-center" style="color: red; padding: 20px;">Error: ' + data.message + '</td></tr>';
                }
            })
            .catch(err => {
                if (loader) loader.classList.remove('active');
                blogsTableBody.innerHTML = '<tr><td colspan="9" class="text-center" style="color: red; padding: 20px;">Connection failed.</td></tr>';
            });
    }

    let searchTimeout = null;
    if (cmsSearch) {
        cmsSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                fetchBlogs(1);
            }, 300);
        });
    }
    if (cmsCategoryFilter) {
        cmsCategoryFilter.addEventListener('change', function() {
            fetchBlogs(1);
        });
    }
    if (cmsStatusFilter) {
        cmsStatusFilter.addEventListener('change', function() {
            fetchBlogs(1);
        });
    }

    function getStatusMeta(status) {
        const active = status === 'published';
        return {
            label: active ? 'Active' : 'Inactive',
            labelSearch: active ? 'active' : 'inactive',
            nextStatus: active ? 'draft' : 'published',
            badgeClass: active ? 'badge-approved' : 'badge-draft',
            buttonClass: active ? 'is-active' : 'is-inactive',
            iconClass: active ? 'fa-toggle-on' : 'fa-toggle-off',
            title: active ? 'Set Inactive' : 'Set Active'
        };
    }

    function updateCountText(el, delta) {
        if (!el || delta === 0) return;
        const current = parseInt(el.textContent.replace(/[^\d]/g, ''), 10) || 0;
        el.textContent = Math.max(0, current + delta).toLocaleString('en-IN');
    }

    function adjustStatusCounters(oldStatus, newStatus) {
        if (oldStatus === newStatus) return;

        const activeCount = document.getElementById('activeArticlesCount');
        const inactiveCount = document.getElementById('inactiveArticlesCount');

        if (oldStatus === 'published') updateCountText(activeCount, -1);
        if (oldStatus === 'draft') updateCountText(inactiveCount, -1);
        if (newStatus === 'published') updateCountText(activeCount, 1);
        if (newStatus === 'draft') updateCountText(inactiveCount, 1);
    }

    function updateBlogRowStatus(row, status) {
        if (!row) return;
        const meta = getStatusMeta(status);
        const badge = row.querySelector('.status-badge');
        const toggleBtn = row.querySelector('.status-toggle-btn');

        row.dataset.status = status;
        row.dataset.statusLabel = meta.labelSearch;

        if (badge) {
            badge.textContent = meta.label;
            badge.classList.remove('badge-approved', 'badge-draft');
            badge.classList.add(meta.badgeClass);
        }

        if (toggleBtn) {
            toggleBtn.setAttribute('data-next-status', meta.nextStatus);
            toggleBtn.setAttribute('title', meta.title);
            toggleBtn.setAttribute('aria-label', meta.title);
            toggleBtn.className = `btn-action status-toggle-btn ${meta.buttonClass}`;
            const icon = toggleBtn.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-toggle-on', 'fa-toggle-off');
                icon.classList.add(meta.iconClass);
            }
        }
    }

    function bindPaginationEvents() {
        const paginationLinks = document.querySelectorAll('.pagination a');
        paginationLinks.forEach(link => {
            link.onclick = function(e) {
                e.preventDefault();
                const page = this.getAttribute('data-page');
                if (page) {
                    fetchBlogs(page);
                }
            };
        });
    }

    function bindRowActionEvents() {
        // Edit button binding
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.onclick = function() {
                const blog = JSON.parse(btn.getAttribute('data-blog'));
                blogIdInput.value = blog.id;
                titleInput.value = blog.title;
                slugInput.value = blog.slug;
                excerptInput.value = blog.excerpt || '';
                categoryInput.value = blog.category;
                readTimeInput.value = blog.read_time || '5 Min Read';
                readTimeTouched = true;
                statusInput.value = blog.status || 'draft';
                if (removeImageInput) removeImageInput.value = "0";
                metaTitleInput.value = blog.meta_title || '';
                metaDescInput.value = blog.meta_description || '';
                metaKeywordsInput.value = blog.meta_keywords || '';
                
                document.getElementById('author_name').value = blog.author_name || '';
                document.getElementById('author_designation').value = blog.author_designation || '';
                document.getElementById('reviewed_by').value = blog.reviewed_by || '';
                document.getElementById('cta_text').value = blog.cta_text || '';
                document.getElementById('cta_link').value = blog.cta_link || '';
                
                if (tinymce.get('editor-container')) {
                    tinymce.get('editor-container').setContent(blog.content || '');
                }
                
                if (blog.image_url) {
                    imgPreview.src = `../${blog.image_url}`;
                    imgPreview.classList.remove('hidden');
                    previewPlaceholder.classList.add('hidden');
                    if (clearImageBtn) clearImageBtn.classList.remove('hidden');
                    if (dropzoneText) dropzoneText.textContent = "Cover image selected";
                } else {
                    resetCoverPreview(false);
                }
                
                // FAQs
                const faqsContainer = document.getElementById('faqsRowContainer');
                faqsContainer.innerHTML = '';
                const faqsArr = JSON.parse(blog.faqs || '[]');
                faqsArr.forEach(faq => {
                    addFaqRow(faq.q || '', faq.a || '');
                });
                
                switchSettingsTab('settings');
                updateSlugPreview();
                updateContentMetrics();
                updateGooglePreview();
                updateReadiness();
                if (typeof checkRequiredFormFields === 'function') {
                    setTimeout(checkRequiredFormFields, 150);
                }
                location.hash = 'edit-' + blog.id;
                showForm("Edit Article");
            };
        });

        // Status Toggle button binding
        document.querySelectorAll('.status-toggle-btn').forEach(btn => {
            btn.onclick = function() {
                const id = btn.getAttribute('data-id');
                const nextStatus = btn.getAttribute('data-next-status') || 'draft';
                const fd = new FormData();
                fd.append('action', 'toggle_blog_status');
                fd.append('id', id);
                fd.append('status', nextStatus);
                fd.append('csrf_token', csrfToken);

                btn.disabled = true;

                fetch('api.php', {
                    method: 'POST',
                    body: fd
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status !== 'success') {
                        throw new Error(data.message || 'Status update failed.');
                    }

                    const row = document.getElementById(`blog-row-${id}`);
                    const newStatus = data.blog_status || nextStatus;
                    const oldStatus = row ? row.dataset.status : '';
                    adjustStatusCounters(oldStatus, newStatus);
                    updateBlogRowStatus(row, newStatus);

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message || 'Blog status updated.',
                        showConfirmButton: false,
                        timer: 1600
                    });
                })
                .catch(err => {
                    Swal.fire({
                        title: 'Status Not Updated',
                        text: err.message || 'Network error.',
                        icon: 'error',
                        confirmButtonColor: '#dc2626'
                    });
                })
                .finally(() => {
                    btn.disabled = false;
                });
            };
        });

        // Delete button binding
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.onclick = function() {
                const id = btn.getAttribute('data-id');
                Swal.fire({
                    title: 'Delete Blog Post?',
                    text: "Are you absolutely sure you want to delete this blog post? This action cannot be undone.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, Delete It!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Deleting...',
                            text: 'Removing post from database...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        const fd = new FormData();
                        fd.append('action', 'delete_blog');
                        fd.append('id', id);
                        fd.append('csrf_token', csrfToken);

                        fetch('api.php', {
                            method: 'POST',
                            body: fd
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status !== 'success') {
                                throw new Error(data.message || 'Deletion failed.');
                            }
                            Swal.fire({
                                title: 'Deleted!',
                                text: data.message || 'Post deleted.',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                fetchBlogs(currentPage);
                            });
                        })
                        .catch(err => {
                            Swal.fire({
                                title: 'Deletion Failed',
                                text: err.message || 'Network error.',
                                icon: 'error',
                                confirmButtonColor: '#dc2626'
                            });
                        });
                    }
                });
            };
        });
    }

    // Draft Auto-Save system
    const AUTO_SAVE_KEY = 'waqt_finance_blog_draft';
    const recoveryBanner = document.getElementById('recoveryBanner');
    const restoreDraftBtn = document.getElementById('restoreDraftBtn');
    const discardDraftBtn = document.getElementById('discardDraftBtn');
    const recoveryTime = document.getElementById('recoveryTime');

    function saveDraftToLocalStorage() {
        if (blogIdInput.value !== "0") return;

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
            savedAt: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        };

        localStorage.setItem(AUTO_SAVE_KEY, JSON.stringify(draftData));
    }

    setInterval(saveDraftToLocalStorage, 10000);

    function checkAndShowDraftRecovery() {
        if (blogIdInput.value !== "0") return;

        const savedDataStr = localStorage.getItem(AUTO_SAVE_KEY);
        if (savedDataStr) {
            try {
                const draft = JSON.parse(savedDataStr);
                if (recoveryBanner && recoveryTime) {
                    recoveryTime.textContent = draft.savedAt;
                    recoveryBanner.classList.remove('hidden');
                }
            } catch (e) {
                localStorage.removeItem(AUTO_SAVE_KEY);
            }
        }
    }

    if (restoreDraftBtn) {
        restoreDraftBtn.addEventListener('click', () => {
            const savedDataStr = localStorage.getItem(AUTO_SAVE_KEY);
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
            localStorage.removeItem(AUTO_SAVE_KEY);
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
                <input type="text" class="form-control-adm faq-question-input" value="${q}" placeholder="Question (e.g. Is parking available?)" required style="width: 100%;">
                <textarea class="form-control-adm faq-answer-input" placeholder="Answer..." required style="width: 100%; min-height: 60px;">${a}</textarea>
            </div>
            <button type="button" class="btn-remove-row" onclick="removeRow('faq-row-${randId}')">
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

    // Toggle panels helper
    function showForm(titleText) {
        listPanel.classList.add('hidden');
        formPanel.classList.remove('hidden');
        formTitle.innerHTML = `<i class="fa-solid fa-pen-nib" style="color: var(--primary); margin-right: 8px;"></i> ${titleText}`;
        formFeedback.innerText = "";
    }

    function showList() {
        formPanel.classList.add('hidden');
        listPanel.classList.remove('hidden');
        if (recoveryBanner) recoveryBanner.classList.add('hidden');
        if (location.hash) {
            history.pushState("", document.title, window.location.pathname + window.location.search);
        }
    }

    // Handle Routing based on URL Hash
    function handleRouting() {
        const hash = location.hash;
        if (hash === '#create') {
            if (formPanel.classList.contains('hidden')) {
                const createBtn = document.getElementById('createPostBtn');
                if (createBtn) {
                    createBtn.click();
                } else {
                    showForm("Write New Article");
                }
            }
        } else if (hash.startsWith('#edit-')) {
            const id = hash.replace('#edit-', '');
            if (formPanel.classList.contains('hidden') || blogIdInput.value != id) {
                let found = false;
                document.querySelectorAll('.edit-btn').forEach(btn => {
                    const blog = JSON.parse(btn.getAttribute('data-blog'));
                    if (blog && blog.id == id) {
                        btn.click();
                        found = true;
                    }
                });
                if (!found) {
                    showList();
                }
            }
        } else {
            showList();
        }
    }

    // Handle hash change for browser back/forward buttons
    window.addEventListener('hashchange', handleRouting);

    // Buttons
    document.getElementById('createPostBtn').addEventListener('click', () => {
        blogForm.reset();
        blogIdInput.value = "0";
        readTimeTouched = false;
        if (tinymce.get('editor-container')) {
            tinymce.get('editor-container').setContent('');
        }
        resetCoverPreview(false);
        document.getElementById('faqsRowContainer').innerHTML = '';
        switchSettingsTab('settings');
        updateSlugPreview();
        updateContentMetrics();
        updateGooglePreview();
        updateReadiness();
        checkAndShowDraftRecovery();
        if (typeof checkRequiredFormFields === 'function') checkRequiredFormFields();
        location.hash = 'create';
        showForm("Write New Article");
    });

    document.getElementById('backToListBtn').addEventListener('click', () => {
        location.hash = '';
        showList();
    });
    document.getElementById('cancelBtn').addEventListener('click', () => {
        location.hash = '';
        showList();
    });

    // Edit Blog Handler
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const blog = JSON.parse(btn.getAttribute('data-blog'));
            
            // Populate form
            blogIdInput.value = blog.id;
            titleInput.value = blog.title;
            slugInput.value = blog.slug;
            excerptInput.value = blog.excerpt || '';
            categoryInput.value = blog.category;
            readTimeInput.value = blog.read_time || '5 Min Read';
            readTimeTouched = true;
            statusInput.value = blog.status || 'draft';
            if (removeImageInput) removeImageInput.value = "0";
            metaTitleInput.value = blog.meta_title || '';
            metaDescInput.value = blog.meta_description || '';
            metaKeywordsInput.value = blog.meta_keywords || '';
            
            // E-E-A-T & CTA fields
            document.getElementById('author_name').value = blog.author_name || '';
            document.getElementById('author_designation').value = blog.author_designation || '';
            document.getElementById('reviewed_by').value = blog.reviewed_by || '';
            document.getElementById('cta_text').value = blog.cta_text || '';
            document.getElementById('cta_link').value = blog.cta_link || '';
            
            // Set TinyMCE content
            if (tinymce.get('editor-container')) {
                tinymce.get('editor-container').setContent(blog.content || '');
            }
            
            // Image Preview
            if (blog.image_url) {
                imgPreview.src = `../${blog.image_url}`;
                imgPreview.classList.remove('hidden');
                previewPlaceholder.classList.add('hidden');
                if (clearImageBtn) clearImageBtn.classList.remove('hidden');
                if (dropzoneText) dropzoneText.textContent = "Cover image selected";
            } else {
                resetCoverPreview(false);
            }

            // Load FAQs
            const faqsContainer = document.getElementById('faqsRowContainer');
            faqsContainer.innerHTML = '';
            const faqsArr = JSON.parse(blog.faqs || '[]');
            faqsArr.forEach(faq => {
                addFaqRow(faq.q || '', faq.a || '');
            });
            
            switchSettingsTab('settings');
            updateSlugPreview();
            updateContentMetrics();
            updateGooglePreview();
            updateReadiness();
            if (typeof checkRequiredFormFields === 'function') {
                setTimeout(checkRequiredFormFields, 150);
            }
            location.hash = 'edit-' + blog.id;
            showForm("Edit Article");
        });
    });

    // Delete Blog Handler
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            
            Swal.fire({
                title: 'Delete Blog Post?',
                text: "Are you absolutely sure you want to delete this blog post? This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Delete It!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Removing post from database...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const fd = new FormData();
                    fd.append('action', 'delete_blog');
                    fd.append('id', id);
                    fd.append('csrf_token', csrfToken);

                    fetch('api.php', {
                        method: 'POST',
                        body: fd
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const row = document.getElementById(`blog-row-${id}`);
                            if (row) row.remove();
                            
                            Swal.fire({
                                title: 'Deleted!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonColor: '#18529c'
                            });
                        } else {
                            throw new Error(data.message || 'Deletion failed.');
                        }
                    })
                    .catch(err => {
                        Swal.fire({
                            title: 'Error!',
                            text: err.message || 'Network error.',
                            icon: 'error',
                            confirmButtonColor: '#dc2626'
                        });
                    });
                }
            });
        });
    });

    // Form Submit Handler
    blogForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
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
                text: 'Please provide an Author Name in the "EEAT & CTA" tab before activating this article.',
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
                text: 'Please enter a target link for your Call-to-Action in the "EEAT & CTA" tab.',
                icon: 'warning',
                confirmButtonColor: '#18529c'
            });
            return;
        } else {
            setValid(ctaLinkEl);
        }
        
        if (!isValid) {
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
                localStorage.removeItem(AUTO_SAVE_KEY);
                Swal.fire({
                    title: 'Saved Successfully!',
                    text: 'The blog article has been stored successfully.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
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
        });
    });

    // Initial bindings & routing check on page load
    bindRowActionEvents();
    bindPaginationEvents();
    handleRouting();
</script>

<?php include 'footer.php'; ?>
