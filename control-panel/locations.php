<?php
declare(strict_types=1);

$pageTitle = 'Locations CMS Control';
$pageSubtitle = 'Create, update, and manage dynamic location pages for the website';

include 'header.php';
require_once __DIR__ . '/../includes/location_data.php';

$conn = db();
// Fresh installs: the CMS table has to exist before locations can be listed or edited.
if (!waqt_location_table_exists($conn, 'location_pages')) {
    echo '<div class="data-card" style="max-width:760px;"><h2 class="card-title-text"><i class="fa-solid fa-database" style="color:var(--warning); margin-right:8px;"></i> Location CMS needs a one-time setup</h2>'
        . '<p style="margin:12px 0 0; line-height:1.6;">The website is showing the default locations from <code>config/location-pages.php</code>. To manage locations here, run <code>storage/migrations/2026_11_19_location_cms.sql</code> on the database, then <code>php storage/maintenance/seed_location_data.php --apply</code>.</p></div>';
    include 'footer.php';
    return;
}
$locHasExtras = waqt_location_column_exists($conn, 'location_pages', 'phone');
$locHasSort = waqt_location_column_exists($conn, 'location_pages', 'sort_order');

// Apply filters before pagination so search covers every location.
$limit = 10;
$page = max(1, (int) ($_GET['page'] ?? 1));
$search = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 150);
$typeFilter = in_array($_GET['type'] ?? '', ['region', 'office', 'service-area'], true) ? $_GET['type'] : '';
$statusFilter = in_array($_GET['status'] ?? '', ['draft', 'published'], true) ? $_GET['status'] : '';
$where = []; $params = []; $types = '';
if ($search !== '') {
    $where[] = '(LOCATE(?, name) > 0 OR LOCATE(?, slug) > 0 OR LOCATE(?, title) > 0)';
    array_push($params, $search, $search, $search); $types .= 'sss';
}
foreach (['type' => $typeFilter, 'status' => $statusFilter] as $column => $value) {
    if ($value !== '') { $where[] = "$column = ?"; $params[] = $value; $types .= 's'; }
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$countStmt = $conn->prepare('SELECT COUNT(*) FROM location_pages' . $whereSql);
if ($params) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalRecords = (int) $countStmt->get_result()->fetch_row()[0];
$totalPages = max(1, (int) ceil($totalRecords / $limit));
$page = min($page, $totalPages);
$offset = ($page - 1) * $limit;
$pageUrl = static fn(int $number): string => '?' . htmlspecialchars(http_build_query(['q' => $search, 'type' => $typeFilter, 'status' => $statusFilter, 'page' => $number]), ENT_QUOTES, 'UTF-8');
// Stats
$activeRecords = (int) ($conn->query("SELECT COUNT(*) FROM location_pages WHERE status = 'published'")->fetch_row()[0] ?? 0);
$inactiveRecords = (int) ($conn->query("SELECT COUNT(*) FROM location_pages WHERE status = 'draft'")->fetch_row()[0] ?? 0);
$regionsCount = (int) ($conn->query("SELECT COUNT(*) FROM location_pages WHERE type = 'region'")->fetch_row()[0] ?? 0);
$officesCount = (int) ($conn->query("SELECT COUNT(*) FROM location_pages WHERE type = 'office'")->fetch_row()[0] ?? 0);
$serviceAreasCount = (int) ($conn->query("SELECT COUNT(*) FROM location_pages WHERE type = 'service-area'")->fetch_row()[0] ?? 0);

// Fetch locations
$locationsList = [];
$stmt = $conn->prepare("SELECT * FROM location_pages $whereSql ORDER BY id DESC LIMIT ? OFFSET ?");
$listParams = [...$params, $limit, $offset];
$stmt->bind_param($types . 'ii', ...$listParams);
$stmt->execute();
$res = $stmt->get_result();
if ($res) {
    $locationsList = $res->fetch_all(MYSQLI_ASSOC);
}
?>

<!-- TinyMCE Rich Text Editor CDN -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
<!-- SweetAlert2 CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* CMS Editor Layout styles */
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
    .field-help-text {
        font-size: 11px;
        color: var(--text-muted);
    }
    .field-counter-text {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
    }
    .field-counter-text.warning {
        color: var(--warning);
    }
    .field-counter-text.danger {
        color: var(--danger);
    }

    /* Modal / Tab Styles */
    .cms-tabs-header {
        display: flex;
        gap: 8px;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 24px;
        overflow-x: auto;
    }
    .cms-tab-btn {
        padding: 10px 20px;
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-muted);
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.2s;
    }
    .cms-tab-btn:hover {
        color: var(--primary);
        background: #f8fafc;
    }
    .cms-tab-btn.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }

    /* Dynamic Row Manager styles */
    .dynamic-rows-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
        margin-top: 10px;
    }
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
    .btn-add-row {
        background: #ffffff;
        border: 1px dashed var(--primary);
        color: var(--primary);
        font-weight: 600;
        font-size: 12px;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .btn-add-row:hover {
        background: var(--primary-glow);
    }

    /* UI Helper utility classes */
    .card-adm {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);
        margin-bottom: 30px;
    }
    .card-adm.hidden {
        display: none;
    }
    .card-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        margin-bottom: 24px;
    }
    .card-title-text {
        font-family: 'Outfit', sans-serif;
        font-size: 18px;
        font-weight: 700;
        color: var(--text-main);
    }
    .btn-primary-adm {
        background: var(--primary);
        color: #ffffff;
        font-weight: 600;
        font-size: 13.5px;
        padding: 10px 18px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(24, 82, 156, 0.15);
    }
    .btn-primary-adm:hover {
        background: #0f3d73;
        transform: translateY(-1px);
    }
    .btn-secondary-adm {
        background: #f1f5f9;
        color: #475569;
        font-weight: 600;
        font-size: 13.5px;
        padding: 10px 18px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-secondary-adm:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .list-controls-flex {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .cms-search-input {
        flex: 1;
        min-width: 250px;
        position: relative;
    }
    .cms-search-input i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 14px;
    }
    .cms-search-input input {
        width: 100%;
        padding: 10px 14px 10px 40px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        color: #0f172a;
        font-size: 13.5px;
        outline: none;
        transition: all 0.2s;
    }
    .cms-search-input input:focus {
        border-color: var(--primary);
    }
    .cms-filter-select {
        padding: 10px 14px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 13.5px;
        color: #0f172a;
        background: #ffffff;
        outline: none;
        cursor: pointer;
        min-width: 150px;
    }
    .admin-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }
    .admin-table th {
        background: #f8fafc;
        padding: 14px 16px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-muted);
        border-bottom: 1px solid #cbd5e1;
        letter-spacing: 0.5px;
    }
    .admin-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 13.5px;
        color: #334155;
    }
    .admin-table tr:hover {
        background: #f8fafc;
    }
    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .badge-approved {
        background: #d1fae5;
        color: #065f46;
    }
    .badge-draft {
        background: #f1f5f9;
        color: #475569;
    }
    .row-actions {
        display: flex;
        gap: 8px;
    }
    .btn-action {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-action:hover {
        background: #f1f5f9;
        color: var(--text-main);
    }
    .btn-action.delete-btn:hover {
        background: #fee2e2;
        color: #ef4444;
        border-color: #fca5a5;
    }
    .btn-action.status-toggle-btn.is-active {
        color: var(--success);
        border-color: #a7f3d0;
    }
    .btn-action.status-toggle-btn.is-active:hover {
        background: #ecfdf5;
    }
    .btn-action.status-toggle-btn.is-inactive {
        color: var(--text-muted);
    }
    .btn-action.status-toggle-btn.is-inactive:hover {
        background: #f8fafc;
    }
    .btn-action.view-live-btn:hover {
        background: #eff6ff;
        color: #3b82f6;
        border-color: #bfdbfe;
    }
    .text-center {
        text-align: center;
    }
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 24px;
        list-style: none;
    }
    .page-item a, .page-item span {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px 14px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        color: #475569;
        background: #ffffff;
        transition: all 0.2s;
    }
    .page-item.active span {
        background: var(--primary);
        color: #ffffff;
        border-color: var(--primary);
    }
    .page-item a:hover {
        background: #f1f5f9;
        color: var(--text-main);
    }

    .stats-grid.hidden { display: none !important; }
    .stats-grid { gap: 14px !important; margin-bottom: 20px !important; }
    .stats-grid > div { padding: 18px !important; }
    .cms-results, .cms-editor-help { color: #64748b; font-size: 13px; margin: 12px 0 18px; line-height: 1.6; }
    #editorContainer .form-grid { grid-template-columns: minmax(0, 1fr) minmax(260px, 340px); gap: 24px; }
    #editorContainer .form-grid > div { min-width: 0; }
    #editorContainer .form-grid > div:last-child { padding: 18px; border: 1px solid #e2e8f0; border-radius: 12px; background: #f8fafc; align-self: start; }
    .form-label { text-transform: none; letter-spacing: 0; font-size: 13px; color: #334155; }
    .form-control-adm { min-height: 44px; font-size: 14px; }
    .cms-tabs-header { flex-wrap: wrap; gap: 4px; padding-bottom: 8px; }
    .cms-tab-btn { min-height: 44px; font-size: 13px; padding: 10px 14px; }
    .cms-tab-btn.active { background: #eff6ff; border-radius: 8px 8px 0 0; }
    #locationErrors { padding: 14px; border: 1px solid #fca5a5; border-radius: 8px; background: #fef2f2; color: #991b1b; margin-bottom: 16px; }
    [aria-invalid="true"] { border-color: #dc2626 !important; }
    :is(.cms-tab-btn, .btn-action, .btn-primary-adm, .btn-secondary-adm):focus-visible { outline: 3px solid #18529c; outline-offset: 3px; }
    .list-controls-flex { flex-wrap: wrap; }
    .admin-table { min-width: 850px; }
    .admin-table th { white-space: nowrap; }
    .row-actions { flex-wrap: nowrap; }
    @media(max-width: 950px) { #editorContainer .form-grid { grid-template-columns: minmax(0, 1fr); } }
    @media(max-width: 600px) { .card-adm { padding: 16px; } .card-header-flex { flex-wrap: wrap; gap: 12px; } .cms-tab-btn { flex: 1 1 140px; } .cms-search-input { flex-basis: 100%; } }

</style>

<!-- Stats overview grid cards -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="stat-card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 20px; box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04); display: flex; align-items: center; gap: 15px;">
        <div class="stat-icon" style="width: 48px; height: 48px; background: var(--primary-glow); color: var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px;"><i class="fa-solid fa-map-location-dot"></i></div>
        <div>
            <h3 style="font-size: 24px; font-weight: 800; font-family: 'Outfit', sans-serif; color: var(--text-main);"><?php echo $activeRecords + $inactiveRecords; ?></h3>
            <p style="font-size: 12px; font-weight: 600; color: var(--text-muted);">Total Locations</p>
        </div>
    </div>
    <div class="stat-card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 20px; box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04); display: flex; align-items: center; gap: 15px;">
        <div class="stat-icon" style="width: 48px; height: 48px; background: rgba(22, 163, 74, 0.08); color: var(--success); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px;"><i class="fa-solid fa-circle-check"></i></div>
        <div>
            <h3 style="font-size: 24px; font-weight: 800; font-family: 'Outfit', sans-serif; color: var(--text-main);"><?php echo $activeRecords; ?></h3>
            <p style="font-size: 12px; font-weight: 600; color: var(--text-muted);">Active Pages</p>
        </div>
    </div>
    <div class="stat-card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 20px; box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04); display: flex; align-items: center; gap: 15px;">
        <div class="stat-icon" style="width: 48px; height: 48px; background: rgba(100, 116, 139, 0.08); color: var(--text-muted); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px;"><i class="fa-solid fa-file-pen"></i></div>
        <div>
            <h3 style="font-size: 24px; font-weight: 800; font-family: 'Outfit', sans-serif; color: var(--text-main);"><?php echo $inactiveRecords; ?></h3>
            <p style="font-size: 12px; font-weight: 600; color: var(--text-muted);">Draft Pages</p>
        </div>
    </div>
</div>

<!-- CONTAINER 1: LISTING VIEW -->
<div class="card-adm" id="listContainer">
    <div class="card-header-flex">
        <h2 class="card-title-text"><i class="fa-solid fa-location-arrow" style="color: var(--primary); margin-right: 8px;"></i> Location Pages</h2>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="location-products.php" class="btn-secondary-adm" style="text-decoration:none;"><i class="fa-solid fa-layer-group"></i> Product content</a>
            <a href="../locations" class="btn-secondary-adm" style="text-decoration:none;" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square"></i> View locations page</a>
            <button type="button" class="btn-primary-adm" id="createLocationBtn">
                <i class="fa-solid fa-plus"></i> Add New Location
            </button>
        </div>
    </div>

    <!-- Controls -->
    <form class="list-controls-flex" method="get" role="search">
        <div class="cms-search-input">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="cmsSearch" name="q" aria-label="Search all locations" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search by name, slug or title...">
        </div>
        
        <select id="cmsTypeFilter" name="type" aria-label="Location type" class="cms-filter-select">
            <option value="">All Types</option>
            <option value="region">Region</option>
            <option value="office">Office Location</option>
            <option value="service-area">Online Service Area</option>
        </select>
        
        <select id="cmsStatusFilter" name="status" aria-label="Publication status" class="cms-filter-select">
            <option value="">All Statuses</option>
            <option value="published">Active</option>
            <option value="draft">Draft</option>
        </select>
        <button class="btn-primary-adm" type="submit">Apply filters</button>
        <a class="btn-secondary-adm" href="locations.php">Reset</a>
    </form>

    <p class="cms-results" role="status"><?php echo number_format($totalRecords); ?> matching locations | Page <?php echo $page; ?> of <?php echo $totalPages; ?></p>
    <!-- Table -->
    <div style="width: 100%; overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Location Name</th>
                    <th>Slug URL</th>
                    <th>Type</th>
                    <th>Areas Covered</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="locationsTableBody">
                <?php if (empty($locationsList)): ?>
                    <tr>
                        <td colspan="6" class="text-center" style="color: var(--text-muted); padding: 40px 0;">No locations match this view. Reset filters or add a new location.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($locationsList as $loc): ?>
                        <?php
                            $locStatus = $loc['status'] === 'published' ? 'published' : 'draft';
                            $isActive = $locStatus === 'published';
                            $statusLabel = $isActive ? 'Active' : 'Draft';
                            $nextStatus = $isActive ? 'draft' : 'published';
                            $toggleTitle = $isActive ? 'Mark Draft' : 'Mark Active';
                            $areasArr = json_decode($loc['areas'], true) ?? [];
                            $areasString = implode(', ', $areasArr);
                            if (strlen($areasString) > 50) $areasString = substr($areasString, 0, 47) . '...';
                        ?>
                        <tr id="loc-row-<?php echo (int) $loc['id']; ?>"
                            data-status="<?php echo htmlspecialchars($locStatus, ENT_QUOTES, 'UTF-8'); ?>"
                            data-type="<?php echo htmlspecialchars($loc['type'], ENT_QUOTES, 'UTF-8'); ?>">
                            <td>
                                <strong style="color: var(--text-main); font-weight: 700;"><?php echo htmlspecialchars($loc['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            </td>
                            <td style="font-family: monospace; font-size:12px; color: var(--info);">/loan-in-<?php echo htmlspecialchars($loc['slug'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <span style="text-transform: capitalize; font-weight: 600;">
                                    <?php 
                                        if ($loc['type'] === 'office') echo 'Office Location';
                                        elseif ($loc['type'] === 'region') echo 'Region';
                                        else echo 'Service Area';
                                    ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($areasString, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <span class="badge-status <?php echo $isActive ? 'badge-approved' : 'badge-draft'; ?> status-badge"><?php echo $statusLabel; ?></span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a class="btn-action view-live-btn" href="../loan-in-<?php echo htmlspecialchars($loc['slug'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" title="View Live Page">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                    <button type="button" class="btn-action edit-btn" data-location='<?php echo htmlspecialchars(json_encode($loc, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8'); ?>' title="Edit Location">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button"
                                            class="btn-action status-toggle-btn <?php echo $isActive ? 'is-active' : 'is-inactive'; ?>"
                                            data-id="<?php echo (int) $loc['id']; ?>"
                                            data-next-status="<?php echo htmlspecialchars($nextStatus, ENT_QUOTES, 'UTF-8'); ?>"
                                            title="<?php echo $toggleTitle; ?>">
                                        <i class="fa-solid <?php echo $isActive ? 'fa-toggle-on' : 'fa-toggle-off'; ?>"></i>
                                    </button>
                                    <button type="button" class="btn-action delete-btn" data-id="<?php echo (int) $loc['id']; ?>" title="Delete Location">
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
    <?php if ($totalPages > 1): ?>
        <ul class="pagination">
            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <?php if ($page <= 1): ?>
                    <span style="opacity:0.5; cursor:not-allowed;">&laquo; Prev</span>
                <?php else: ?>
                    <a href="<?php echo $pageUrl($page - 1); ?>">&laquo; Prev</a>
                <?php endif; ?>
            </li>
            
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <li class="page-item <?php echo $p === $page ? 'active' : ''; ?>">
                    <?php if ($p === $page): ?>
                        <span><?php echo $p; ?></span>
                    <?php else: ?>
                        <a href="<?php echo $pageUrl($p); ?>"><?php echo $p; ?></a>
                    <?php endif; ?>
                </li>
            <?php endfor; ?>
            
            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                <?php if ($page >= $totalPages): ?>
                    <span style="opacity:0.5; cursor:not-allowed;">Next &raquo;</span>
                <?php else: ?>
                    <a href="<?php echo $pageUrl($page + 1); ?>">Next &raquo;</a>
                <?php endif; ?>
            </li>
        </ul>
    <?php endif; ?>
</div>

<!-- CONTAINER 2: EDITOR VIEW -->
<div class="card-adm hidden" id="editorContainer">
    <div class="card-header-flex">
        <h2 class="card-title-text" id="formTitle"><i class="fa-solid fa-pen-nib" style="color: var(--primary); margin-right: 8px;"></i> Configure Location</h2>
        <button type="button" class="btn-secondary-adm" id="backToListBtn">
            <i class="fa-solid fa-arrow-left"></i> Back to List
        </button>
    </div>

    <p class="cms-editor-help">Create a local page, then add optional FAQs and product overrides. Required fields are marked *.</p>
    <div id="locationErrors" role="alert" tabindex="-1" hidden></div>
    <form id="locationForm" novalidate method="POST">
        <input type="hidden" name="id" id="locationId" value="0">
        
        <div class="cms-tabs-header">
            <button type="button" class="cms-tab-btn active" id="tabBtn-basic" onclick="switchTab('basic')"><i class="fa-solid fa-circle-info"></i> Basic Info</button>
            <button type="button" class="cms-tab-btn" id="tabBtn-guidance" onclick="switchTab('guidance')"><i class="fa-solid fa-hand-holding-dollar"></i> Areas & Guidance</button>
            <button type="button" class="cms-tab-btn" id="tabBtn-seo" onclick="switchTab('seo')"><i class="fa-solid fa-magnifying-glass-chart"></i> SEO & Connections</button>
            <button type="button" class="cms-tab-btn" id="tabBtn-faq" onclick="switchTab('faq')"><i class="fa-solid fa-circle-question"></i> FAQs</button>
            <button type="button" class="cms-tab-btn" id="tabBtn-overrides" onclick="switchTab('overrides')"><i class="fa-solid fa-tags"></i> Product Overrides</button>
        </div>

        <div class="form-grid">
            <!-- Left Main Form Area -->
            <div>
                <!-- TAB 1: BASIC INFO -->
                <div class="tab-content active" id="tabContent-basic">
                    <div class="form-group">
                        <label class="form-label" for="loc_name">Location name *</label>
                        <input type="text" id="loc_name" maxlength="120" name="name" class="form-control-adm" placeholder="e.g. Delhi NCR, Noida & Greater Noida" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="loc_slug">URL slug *</label>
                        <input type="text" id="loc_slug" maxlength="80" pattern="[a-z0-9]+(-[a-z0-9]+)*" name="slug" class="form-control-adm" placeholder="e.g. delhi-ncr, noida" required>
                        <div class="field-support-row">
                            <span class="field-help-text">This will create the page URL: <strong><span id="locationUrlPreview">/loan-in-[slug]</span></strong></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="loc_title">Page heading (H1) *</label>
                        <input type="text" id="loc_title" maxlength="255" name="title" class="form-control-adm" placeholder="e.g. Loan Options Across Delhi NCR" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="loc_intro">Introduction *</label>
                        <textarea id="loc_intro" name="intro" class="form-control-adm" placeholder="Brief introduction text appearing under the heading..." required></textarea>
                    </div>

                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <label class="form-label" for="loc_local_note" style="margin-bottom:0;">Local Guidance Note</label>
                            <button type="button" id="btnInsertTemplate" style="background: none; border: none; color: var(--primary); font-size: 11px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; text-transform: uppercase;">
                                <i class="fa-solid fa-file-invoice"></i> Insert Default Template
                            </button>
                        </div>
                        <textarea id="loc_local_note" name="local_note" class="form-control-adm" placeholder="Information note alerting customers about digital services or office guidelines..."></textarea>
                    </div>
                </div>

                <!-- TAB 2: AREAS & GUIDANCE -->
                <div class="tab-content" id="tabContent-guidance">
                    <div class="form-group">
                        <label class="form-label" for="loc_areas">Areas Served (Comma-separated list)</label>
                        <input type="text" id="loc_areas" class="form-control-adm" placeholder="e.g. Dwarka, Rohini, Lajpat Nagar, Pari Chowk">
                        <div class="field-support-row">
                            <span class="field-help-text">Enter the neighborhoods or sectors served, separated by commas.</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Step-by-Step Scenarios / Guidance Rows</label>
                        <div class="field-help-text" style="margin-bottom: 8px;">Create customized local guidance. These appear as numbered sections.</div>
                        
                        <div class="dynamic-rows-box">
                            <div id="scenariosRowContainer"></div>
                            <button type="button" class="btn-add-row" onclick="addScenarioRow('', '')">
                                <i class="fa-solid fa-plus"></i> Add Guidance Step
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: SEO & CONNECTIONS -->
                <div class="tab-content" id="tabContent-seo">
                    <div class="form-group">
                        <label class="form-label" for="loc_meta_title">SEO Meta Title</label>
                        <input type="text" id="loc_meta_title" maxlength="255" name="meta_title" class="form-control-adm" placeholder="e.g. Loans in Noida & Greater Noida | Waqt Finance">
                        <div class="field-support-row">
                            <span class="field-help-text">Title tag for Google search results page. Recommendation: 50-60 characters.</span>
                            <span class="field-counter-text" id="meta-title-counter">0/60</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="loc_meta_desc">SEO Meta Description</label>
                        <textarea id="loc_meta_desc" maxlength="1000" name="meta_description" class="form-control-adm" placeholder="Provide a meta description summarized for Search engines (Google recommendation: 150-160 characters)..."></textarea>
                        <div class="field-support-row">
                            <span class="field-help-text">Brief page summary appearing under the link.</span>
                            <span class="field-counter-text" id="meta-desc-counter">0/160</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nearby / Related Service Areas</label>
                        <div class="field-help-text" style="margin-bottom: 8px;">Configure the list of nearby cities linked at the bottom of the page.</div>
                        
                        <div class="dynamic-rows-box">
                            <div id="nearbyRowContainer"></div>
                            <button type="button" class="btn-add-row" onclick="addNearbyRow('', '')">
                                <i class="fa-solid fa-plus"></i> Link Nearby Location
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: FAQs -->
                <div class="tab-content" id="tabContent-faq">
                    <div class="form-group">
                        <label class="form-label">Custom Frequently Asked Questions (FAQs)</label>
                        <div class="field-help-text" style="margin-bottom: 8px;">Configure custom questions and answers. These will automatically appear in the accordion and SEO schema.</div>
                        
                        <div class="dynamic-rows-box">
                            <div id="faqsRowContainer"></div>
                            <button type="button" class="btn-add-row" onclick="addFaqRow('', '')">
                                <i class="fa-solid fa-plus"></i> Add Custom FAQ
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TAB 5: PRODUCT OVERRIDES -->
                <div class="tab-content" id="tabContent-overrides">
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 20px; font-size: 13px; color: var(--text-muted); display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-circle-info" style="color: var(--primary); font-size: 16px;"></i>
                        <div>
                            <strong>SEO Content Overrides:</strong> By default, product-specific location pages (like <code>/edi-loan-in-pune</code>) auto-generate content dynamically. Use the inputs below to write unique content and custom meta tags to optimize individual pages for search engine ranking.
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 15px; margin-bottom: 20px; border-bottom: 1px dashed #cbd5e1; padding-bottom: 10px; overflow-x: auto;">
                        <button type="button" class="btn-action-premium" onclick="switchProductOverride('personal-loan')" id="overrideBtn-personal-loan" style="padding:6px 12px; font-size:12px; background:var(--primary); color:#fff; border:none; border-radius:6px; cursor:pointer;">Personal Loan</button>
                        <button type="button" class="btn-action-premium" onclick="switchProductOverride('business-loan')" id="overrideBtn-business-loan" style="padding:6px 12px; font-size:12px; background:#fff; border:1px solid #cbd5e1; color:#1e293b; border-radius:6px; cursor:pointer;">Business Loan</button>
                        <button type="button" class="btn-action-premium" onclick="switchProductOverride('pay-day-loan')" id="overrideBtn-pay-day-loan" style="padding:6px 12px; font-size:12px; background:#fff; border:1px solid #cbd5e1; color:#1e293b; border-radius:6px; cursor:pointer;">Payday Loan</button>
                        <button type="button" class="btn-action-premium" onclick="switchProductOverride('edi-loan')" id="overrideBtn-edi-loan" style="padding:6px 12px; font-size:12px; background:#fff; border:1px solid #cbd5e1; color:#1e293b; border-radius:6px; cursor:pointer;">EDI Loan</button>
                        <button type="button" class="btn-action-premium" onclick="switchProductOverride('vehicle-loan')" id="overrideBtn-vehicle-loan" style="padding:6px 12px; font-size:12px; background:#fff; border:1px solid #cbd5e1; color:#1e293b; border-radius:6px; cursor:pointer;">Vehicle Loan</button>
                        <button type="button" class="btn-action-premium" onclick="switchProductOverride('loan-against-property')" id="overrideBtn-loan-against-property" style="padding:6px 12px; font-size:12px; background:#fff; border:1px solid #cbd5e1; color:#1e293b; border-radius:6px; cursor:pointer;">LAP</button>
                    </div>

                    <?php
                    $productsList = [
                        'personal-loan' => 'Personal Loan',
                        'business-loan' => 'Business Loan',
                        'pay-day-loan' => 'Payday Loan',
                        'edi-loan' => 'EDI Loan',
                        'vehicle-loan' => 'Vehicle Loan',
                        'loan-against-property' => 'Loan Against Property (LAP)'
                    ];
                    foreach ($productsList as $p_key => $p_name):
                    ?>
                    <div class="product-override-content-box" id="overrideBox-<?php echo $p_key; ?>" style="display: <?php echo $p_key === 'personal-loan' ? 'block' : 'none'; ?>;">
                        <h4 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 700; color: #0f172a; border-left: 3px solid var(--primary); padding-left: 10px;"><?php echo $p_name; ?> Overrides</h4>
                        
                        <div class="form-group">
                            <label class="form-label" for="override_title_<?php echo $p_key; ?>">Custom Page Title / Heading</label>
                            <input type="text" id="override_title_<?php echo $p_key; ?>" class="form-control-adm override-field" data-prod="<?php echo $p_key; ?>" data-field="title" placeholder="e.g. Unique Heading for <?php echo $p_name; ?> in Pune">
                            <div class="field-help-text">Overrides the main H1 heading on the product page. Leaves the default title if empty.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="override_meta_title_<?php echo $p_key; ?>">Custom Meta Title</label>
                            <input type="text" id="override_meta_title_<?php echo $p_key; ?>" class="form-control-adm override-field" data-prod="<?php echo $p_key; ?>" data-field="meta_title" placeholder="e.g. Customized Meta Title for SEO">
                            <div class="field-help-text">Overrides the HTML <code>&lt;title&gt;</code> tag for search engines.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="override_meta_desc_<?php echo $p_key; ?>">Custom Meta Description</label>
                            <textarea id="override_meta_desc_<?php echo $p_key; ?>" class="form-control-adm override-field" data-prod="<?php echo $p_key; ?>" data-field="meta_description" placeholder="e.g. Unique Meta Description for Google search results page..."></textarea>
                            <div class="field-help-text">Overrides the HTML search snippet description.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="override_desc_<?php echo $p_key; ?>">Custom Unique Description / Content</label>
                            <div class="field-help-text" style="margin-bottom: 6px;">Write highly custom, descriptive unique local content for this product in this location. (Supports full HTML formatting).</div>
                            <textarea id="override_desc_<?php echo $p_key; ?>" class="form-control-adm override-field rich-override-editor" data-prod="<?php echo $p_key; ?>" data-field="description" placeholder="Write unique paragraphs, guides, regional details..."></textarea>
                        </div>

                        <div class="form-group" style="margin-top: 20px;">
                            <h5 style="margin: 0 0 10px 0; font-size: 13px; font-weight: 700; color: #0f172a; border-left: 2px solid var(--primary); padding-left: 8px;">Product Parameters Overrides</h5>
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                                <div>
                                    <label class="form-label" for="override_min_amount_<?php echo $p_key; ?>" style="font-size: 11px;">Minimum Loan Amount</label>
                                    <input type="text" id="override_min_amount_<?php echo $p_key; ?>" class="form-control-adm override-field" data-prod="<?php echo $p_key; ?>" data-field="min_amount" placeholder="e.g. ₹10,000">
                                </div>
                                <div>
                                    <label class="form-label" for="override_tenure_<?php echo $p_key; ?>" style="font-size: 11px;">Repayment Tenure</label>
                                    <input type="text" id="override_tenure_<?php echo $p_key; ?>" class="form-control-adm override-field" data-prod="<?php echo $p_key; ?>" data-field="tenure" placeholder="e.g. 3 to 12 Months">
                                </div>
                                <div>
                                    <label class="form-label" for="override_interest_<?php echo $p_key; ?>" style="font-size: 11px;">Interest Rate</label>
                                    <input type="text" id="override_interest_<?php echo $p_key; ?>" class="form-control-adm override-field" data-prod="<?php echo $p_key; ?>" data-field="interest" placeholder="e.g. Starting at 11.99% p.a.">
                                </div>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 20px; border-top: 1px solid #f1f5f9; padding-top: 15px;">
                            <label class="form-label">Product-Specific FAQs (Overrides)</label>
                            <div class="field-help-text" style="margin-bottom: 8px;">Configure custom Q&A accordions specifically for this product page. Leaves the default FAQs if empty.</div>
                            
                            <div class="dynamic-rows-box" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px;">
                                <div id="prodFaqsContainer_<?php echo $p_key; ?>"></div>
                                <button type="button" class="btn-add-row" onclick="addProdFaqRow('<?php echo $p_key; ?>', '', '')" style="margin-top: 5px;">
                                    <i class="fa-solid fa-plus"></i> Add Product FAQ
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right Sidebar Panel -->
            <div>
                <div class="form-group">
                    <label class="form-label" for="loc_status">Status</label>
                    <select id="loc_status" name="status" class="form-control-adm">
                        <option value="draft">Draft - hidden</option>
                        <option value="published">Published - live</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="loc_type">Location Type</label>
                    <select id="loc_type" name="type" class="form-control-adm" onchange="toggleTypeFields()">
                        <option value="service-area">Online Service Area</option>
                        <option value="office">Office Location (With address)</option>
                        <option value="region">Region Page</option>
                    </select>
                </div>

                <div class="form-group" id="addressGroup" style="display:none;">
                    <label class="form-label" for="loc_address">Physical Address</label>
                    <textarea id="loc_address" name="address" class="form-control-adm" placeholder="e.g. BSI Business Park, Sector 63, Noida, Uttar Pradesh 201301"></textarea>
                </div>

                <?php if ($locHasExtras): ?>
                <div class="form-group">
                    <label class="form-label" for="loc_phone">Support phone for this page</label>
                    <input type="text" id="loc_phone" name="phone" class="form-control-adm" placeholder="Leave empty to use +91 92170 86610" maxlength="30">
                </div>
                <div class="form-group">
                    <label class="form-label" for="loc_map_url">Google Maps link (offices)</label>
                    <input type="url" id="loc_map_url" name="map_url" class="form-control-adm" placeholder="https://maps.google.com/..." maxlength="500">
                </div>
                <div class="form-group">
                    <label class="form-label" for="loc_hero_image">Hero background image</label>
                    <input type="text" id="loc_hero_image" name="hero_image" class="form-control-adm" placeholder="e.g. assets/images/noida-office.webp" maxlength="255">
                    <small style="color:var(--text-muted);">Path of an image already uploaded to the site, or an https link. Leave empty for the plain blue hero.</small>
                </div>
                <?php else: ?>
                <p style="font-size:12.5px; color:var(--text-muted); margin:8px 0 0;">Run <code>storage/migrations/2026_11_19_location_cms.sql</code> to add the phone, map link, hero image and display-order fields.</p>
                <?php endif; ?>
                <?php if ($locHasSort): ?>
                <div class="form-group">
                    <label class="form-label" for="loc_sort_order">Display order</label>
                    <input type="number" id="loc_sort_order" name="sort_order" class="form-control-adm" value="0" step="1" min="0" max="9999">
                    <small style="color:var(--text-muted);">Lower numbers appear first in the menu, footer and locations page.</small>
                </div>
                <?php endif; ?>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn-primary-adm" style="width:100%; justify-content:center;" id="saveLocationBtn">
                        <i class="fa-solid fa-floppy-disk"></i> Save location page
                    </button>
                    <button type="button" class="btn-action-premium" style="width:100%; justify-content:center; margin-top:10px; padding: 10px; display: none; align-items: center; gap: 8px; border: 1px solid #cbd5e1; border-radius: 8px; background: #f8fafc; color: #1e293b; font-weight: 600; font-size: 13.5px; cursor: pointer; transition: all 0.2s;" id="previewLocationBtn">
                        <i class="fa-solid fa-eye"></i> View saved page
                    </button>
                    <button type="button" class="btn-secondary-adm" style="width:100%; justify-content:center; margin-top:10px;" id="cancelBtn">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function escapeHtml(value) {
        return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    const csrfToken = <?php echo json_encode($_SESSION['csrf_token'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

    // Initialize TinyMCE Rich Text Editor
    tinymce.init({
        selector: '#loc_local_note',
        height: 320,
        menubar: 'file edit view insert format tools table help',
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount', 'emoticons'
        ],
        toolbar: [
            'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough forecolor backcolor',
            'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link table | code preview fullscreen'
        ],
        branding: false,
        promotion: false,
        skin: 'oxide',
        content_css: 'default',
        setup: function (editor) {
            editor.on('change keyup input', function () {
                editor.save();
            });
        }
    });

    tinymce.init({
        selector: '.rich-override-editor',
        height: 250,
        menubar: 'edit insert format table help',
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'charmap', 'preview',
            'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'table', 'help', 'wordcount'
        ],
        toolbar: [
            'undo redo | bold italic underline | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | link table | code'
        ],
        branding: false,
        promotion: false,
        skin: 'oxide',
        content_css: 'default',
        setup: function (editor) {
            editor.on('change keyup input', function () {
                editor.save();
            });
        }
    });

    // Tab switching for product overrides
    function switchProductOverride(prodId) {
        document.querySelectorAll('.product-override-content-box').forEach(box => box.style.display = 'none');
        document.querySelectorAll('[id^="overrideBtn-"]').forEach(btn => {
            btn.style.background = '#fff';
            btn.style.border = '1px solid #cbd5e1';
            btn.style.color = '#1e293b';
        });

        document.getElementById('overrideBox-' + prodId).style.display = 'block';
        const activeBtn = document.getElementById('overrideBtn-' + prodId);
        if (activeBtn) {
            activeBtn.style.background = 'var(--primary)';
            activeBtn.style.color = '#fff';
            activeBtn.style.border = 'none';
        }
        try { sessionStorage.setItem('activeProductOverride', prodId); } catch (error) {}
    }

    // Tab switching controller
    function switchTab(tabId) {
        document.querySelectorAll('.cms-tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

        const tabBtn = document.getElementById('tabBtn-' + tabId);
        const tabContent = document.getElementById('tabContent-' + tabId);
        if (tabBtn && tabContent) {
            tabBtn.classList.add('active');
            tabContent.classList.add('active');
        }
        document.querySelectorAll('.cms-tab-btn').forEach(btn => { const active = btn.classList.contains('active'); btn.setAttribute('aria-selected', String(active)); btn.tabIndex = active ? 0 : -1; });
    }

    // Toggle address input field based on selection
    function toggleTypeFields() {
        const type = document.getElementById('loc_type').value;
        const addressGroup = document.getElementById('addressGroup');
        if (type === 'office') {
            addressGroup.style.display = 'block';
            document.getElementById('loc_address').setAttribute('required', 'required');
        } else {
            addressGroup.style.display = 'none';
            document.getElementById('loc_address').removeAttribute('required');
        }
    }

    // Dynamic row handlers for Scenarios
    function addScenarioRow(title = '', text = '') {
        const container = document.getElementById('scenariosRowContainer');
        
        // Validation: If adding a new row (empty title & text), check if the last scenario is completed
        if (title === '' && text === '') {
            const rows = container.querySelectorAll('.dynamic-row-item');
            if (rows.length > 0) {
                const lastRow = rows[rows.length - 1];
                const titleInput = lastRow.querySelector('.scenario-title-input');
                const textInput = lastRow.querySelector('.scenario-text-input');
                if (titleInput && textInput && (titleInput.value.trim() === '' || textInput.value.trim() === '')) {
                    Swal.fire({
                        title: 'Incomplete Guidance Step!',
                        text: 'Please complete the title and description of the previous guidance step before adding another.',
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
        row.id = 'scenario-row-' + randId;
        
        row.innerHTML = `
            <div style="flex:1; display:flex; flex-direction:column; gap:6px;">
                <input type="text" class="form-control-adm scenario-title-input" value="${escapeHtml(title)}" placeholder="Step Label (e.g. Salaried applicants)" required>
                <textarea class="form-control-adm scenario-text-input" placeholder="Guidance notes..." required>${escapeHtml(text)}</textarea>
            </div>
            <button type="button" class="btn-remove-row" aria-label="Remove row" onclick="removeRow('scenario-row-${randId}')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;
        row.querySelectorAll('input, textarea').forEach(el => { if (!el.hasAttribute('aria-label')) el.setAttribute('aria-label', el.placeholder || 'Row content'); });
        container.appendChild(row);
    }

    // Dynamic row handlers for FAQs
    function addFaqRow(q = '', a = '') {
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
            <div style="flex:1; display:flex; flex-direction:column; gap:6px;">
                <input type="text" class="form-control-adm faq-question-input" value="${escapeHtml(q)}" placeholder="Question about this location" aria-label="FAQ question" required>
                <textarea class="form-control-adm faq-answer-input" placeholder="Answer..." required aria-label="FAQ answer">${escapeHtml(a)}</textarea>
            </div>
            <button type="button" class="btn-remove-row" aria-label="Remove row" onclick="removeRow('faq-row-${randId}')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;
        row.querySelectorAll('input, textarea').forEach(el => { if (!el.hasAttribute('aria-label')) el.setAttribute('aria-label', el.placeholder || 'Row content'); });
        container.appendChild(row);
    }

    // Dynamic row handlers for Nearby links
    function addNearbyRow(slug = '', label = '') {
        const container = document.getElementById('nearbyRowContainer');
        
        // Validation: If adding a new row (empty slug & label), check if the last nearby link is completed
        if (slug === '' && label === '') {
            const rows = container.querySelectorAll('.dynamic-row-item');
            if (rows.length > 0) {
                const lastRow = rows[rows.length - 1];
                const slugInput = lastRow.querySelector('.nearby-slug-input');
                const labelInput = lastRow.querySelector('.nearby-label-input');
                if (slugInput && labelInput && (slugInput.value.trim() === '' || labelInput.value.trim() === '')) {
                    Swal.fire({
                        title: 'Incomplete Nearby Link!',
                        text: 'Please complete the URL slug and label of the previous nearby link before adding another.',
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
        row.id = 'nearby-row-' + randId;
        
        row.innerHTML = `
            <input type="text" class="form-control-adm nearby-slug-input" value="${escapeHtml(slug)}" placeholder="URL Slug (e.g. gurugram)" style="flex:1;" required>
            <input type="text" class="form-control-adm nearby-label-input" value="${escapeHtml(label)}" placeholder="Label (e.g. Gurugram)" style="flex:1;" required>
            <button type="button" class="btn-remove-row" aria-label="Remove row" onclick="removeRow('nearby-row-${randId}')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;
        row.querySelectorAll('input, textarea').forEach(el => { if (!el.hasAttribute('aria-label')) el.setAttribute('aria-label', el.placeholder || 'Row content'); });
        container.appendChild(row);
    }

    function removeRow(rowId) {
        const row = document.getElementById(rowId);
        if (row) {
            row.remove();
        }
    }

    function addProdFaqRow(prodKey, qText, aText) {
        const container = document.getElementById('prodFaqsContainer_' + prodKey);
        if (!container) return;

        const randId = Math.floor(Math.random() * 1000000);
        const rowId = 'prod-faq-row-' + prodKey + '-' + randId;
        const row = document.createElement('div');
        row.className = 'dynamic-row-item';
        row.id = rowId;
        row.style.marginBottom = '12px';
        row.style.borderBottom = '1px dashed #e2e8f0';
        row.style.paddingBottom = '12px';

        row.innerHTML = `
            <div style="flex: 1; display: flex; flex-direction: column; gap: 6px;">
                <input type="text" class="form-control-adm prod-faq-q-input" placeholder="FAQ Question" required aria-label="Product FAQ question" value="${escapeHtml(qText)}" style="font-weight:600;">
                <textarea class="form-control-adm prod-faq-a-input" placeholder="FAQ Answer" rows="2" required aria-label="Product FAQ answer">${escapeHtml(aText)}</textarea>
            </div>
            <button type="button" class="btn-remove-row" aria-label="Remove row" onclick="removeRow('${rowId}')" title="Delete Row" style="align-self: center;">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        `;
        row.querySelectorAll('input, textarea').forEach(el => { if (!el.hasAttribute('aria-label')) el.setAttribute('aria-label', el.placeholder || 'Row content'); });
        container.appendChild(row);
    }

    let locationSnapshot = '';
    function locationState() {
        if (window.tinymce) tinymce.triggerSave();
        return JSON.stringify(Array.from(document.querySelectorAll('#locationForm input, #locationForm textarea, #locationForm select')).map(el => el.value));
    }
    function locationDirty() { return !document.getElementById('editorContainer').classList.contains('hidden') && locationSnapshot && locationSnapshot !== locationState(); }
    function requestList() {
        if (locationDirty() && !confirm('Discard unsaved location changes?')) return;
        showList();
    }
    window.addEventListener('beforeunload', e => { if (locationDirty()) { e.preventDefault(); e.returnValue = ''; } });
    // UI State switching
    function showList() {
        locationSnapshot = '';
        document.querySelector('.stats-grid').classList.remove('hidden');
        document.getElementById('listContainer').classList.remove('hidden');
        document.getElementById('editorContainer').classList.add('hidden');
        if (location.hash) {
            history.replaceState("", document.title, window.location.pathname + window.location.search);
        }
    }

    function showEditor(titleText) {
        document.querySelector('.stats-grid').classList.add('hidden');
        document.getElementById('listContainer').classList.add('hidden');
        document.getElementById('editorContainer').classList.remove('hidden');
        document.getElementById('formTitle').innerHTML = `<i class="fa-solid fa-pen-nib" style="color: var(--primary); margin-right: 8px;"></i> ${escapeHtml(titleText)}`;
        
        setTimeout(() => { locationSnapshot = locationState(); }, 250);
        document.getElementById('locationUrlPreview').textContent = '/loan-in-' + (document.getElementById('loc_slug').value || '[slug]');
        const activeTab = 'basic';
        switchTab(activeTab);

        const activeProd = 'personal-loan';
        switchProductOverride(activeProd);
    }

    // Hash state management is handled inside DOMContentLoaded below

    // Char counters helper
    function initCharCounter(inputEl, counterEl, maxLen) {
        if (!inputEl || !counterEl) return;
        const update = () => {
            const len = inputEl.value.length;
            counterEl.textContent = `${len}/${maxLen}`;
            counterEl.className = 'field-counter-text';
            if (len > maxLen) {
                counterEl.classList.add('danger');
            } else if (len > maxLen * 0.85) {
                counterEl.classList.add('warning');
            }
        };
        inputEl.addEventListener('input', update);
        update();
    }

    document.addEventListener("DOMContentLoaded", function() {
        const nameInput = document.getElementById('loc_name');
        const slugInput = document.getElementById('loc_slug');
        const titleInput = document.getElementById('loc_title');
        const metaTitleInput = document.getElementById('loc_meta_title');
        const metaDescInput = document.getElementById('loc_meta_desc');

        initCharCounter(metaTitleInput, document.getElementById('meta-title-counter'), 60);
        initCharCounter(metaDescInput, document.getElementById('meta-desc-counter'), 160);

        const customFields = new Set();
        [slugInput, titleInput, metaTitleInput].forEach(field => field.addEventListener('input', e => { if (e.isTrusted) customFields.add(field.id); }));
        const refreshUrl = () => { document.getElementById('locationUrlPreview').textContent = '/loan-in-' + (slugInput.value || '[slug]'); };
        slugInput.addEventListener('input', refreshUrl);
        // Slug Auto-generation logic
        nameInput.addEventListener('input', function() {
            if (document.getElementById('locationId').value === "0") {
                const slugified = nameInput.value
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .trim();
                if (!customFields.has('loc_slug')) slugInput.value = slugified.replace(/^-+|-+$/g, '');
                refreshUrl();
                if (!customFields.has('loc_title')) titleInput.value = 'Loan Options Across ' + nameInput.value;
                if (!customFields.has('loc_meta_title')) metaTitleInput.value = 'Loans in ' + nameInput.value + ' | Waqt Finance';
                // Trigger counter events manually
                metaTitleInput.dispatchEvent(new Event('input'));
            }
        });

        // Sanitize manual slug input to keep URL standard compliance
        slugInput.addEventListener('input', function() {
            slugInput.value = slugInput.value
                .toLowerCase()
                .replace(/[^a-z0-9-]/g, '')
                .replace(/-+/g, '-');
        });

        // Preview button click handler
        const previewBtn = document.getElementById('previewLocationBtn');
        if (previewBtn) {
            previewBtn.addEventListener('click', function() {
                const slug = document.getElementById('loc_slug').value.trim();
                if (slug) {
                    window.open(`../loan-in-${encodeURIComponent(slug)}`, '_blank', 'noopener');
                }
            });
        }

        // Insert template content click handler
        const insertTplBtn = document.getElementById('btnInsertTemplate');
        if (insertTplBtn) {
            insertTplBtn.addEventListener('click', function() {
                if (document.getElementById('loc_local_note').value.trim() && !confirm('Replace the existing local guidance with the template?')) return;
                const templateHtml = `<h3>Why choose Waqt Finance:</h3>\n<ul>\n  <li><strong>Fast Disbursal:</strong> Processing time depends on verification, product eligibility and lender assessment.</li>\n  <li><strong>100% Digital Process:</strong> Complete security checks and document verification online.</li>\n  <li><strong>Flexible Terms:</strong> Choose loan repayment terms tailored to your salary or business sales.</li>\n</ul>\n<p><strong>Note:</strong> We currently operate online-only services in this area. Contact our customer service team for current service hours and assistance.</p>`;
                if (tinymce.get('loc_local_note')) {
                    tinymce.get('loc_local_note').setContent(templateHtml);
                    tinymce.get('loc_local_note').save();
                } else {
                    document.getElementById('loc_local_note').value = templateHtml;
                }
            });
        }

        // Search filter client side
        const cmsSearch = document.getElementById('cmsSearch');
        const cmsTypeFilter = document.getElementById('cmsTypeFilter');
        const cmsStatusFilter = document.getElementById('cmsStatusFilter');
        const rows = document.querySelectorAll('#locationsTableBody tr');

        cmsTypeFilter.value = <?php echo json_encode($typeFilter); ?>;
        cmsStatusFilter.value = <?php echo json_encode($statusFilter); ?>;

        // CREATE NEW BUTTON
        document.getElementById('createLocationBtn').addEventListener('click', () => {
            customFields.clear();
            sessionStorage.removeItem('activeTab');
            sessionStorage.removeItem('activeProductOverride');
            document.getElementById('locationErrors').hidden = true;
            document.getElementById('locationForm').reset();
            document.getElementById('locationId').value = "0";
            document.querySelectorAll('#locationForm [aria-invalid]').forEach(el => el.removeAttribute('aria-invalid'));
            if (document.getElementById('previewLocationBtn')) document.getElementById('previewLocationBtn').style.display = 'none';
            document.getElementById('scenariosRowContainer').innerHTML = '';
            document.getElementById('nearbyRowContainer').innerHTML = '';
            document.getElementById('faqsRowContainer').innerHTML = '';
            
            // Clear product override fields
            document.querySelectorAll('.override-field').forEach(field => {
                field.value = '';
                const prod = field.getAttribute('data-prod');
                if (field.classList.contains('rich-override-editor') && tinymce.get('override_desc_' + prod)) {
                    tinymce.get('override_desc_' + prod).setContent('');
                }
            });
            const productsListKeys = ['personal-loan', 'business-loan', 'pay-day-loan', 'edi-loan', 'vehicle-loan', 'loan-against-property'];
            productsListKeys.forEach(prod => {
                const container = document.getElementById('prodFaqsContainer_' + prod);
                if (container) {
                    container.innerHTML = '';
                }
            });
            switchProductOverride('personal-loan'); // reset active sub-tab

            // Add default row structures
            addScenarioRow('Salaried applicants', 'Explore personal or short-term options based on income and repayment capacity.');
            addScenarioRow('Businesses and retailers', 'Compare working-capital, shopkeeper and EDI application journeys.');
            
            if (tinymce.get('loc_local_note')) {
                tinymce.get('loc_local_note').setContent('');
            }
            
            toggleTypeFields();
            location.hash = 'create';
            showEditor("Create New Location Page");
        });

        document.getElementById('backToListBtn').addEventListener('click', requestList);
        document.getElementById('cancelBtn').addEventListener('click', requestList);

        // EDIT BUTTON CLICK
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const loc = JSON.parse(btn.getAttribute('data-location'));
                document.getElementById('locationId').value = loc.id;
                if (document.getElementById('previewLocationBtn')) document.getElementById('previewLocationBtn').style.display = 'inline-flex';
                document.getElementById('loc_name').value = loc.name;
                document.getElementById('loc_slug').value = loc.slug;
                document.getElementById('loc_title').value = loc.title;
                document.getElementById('loc_meta_title').value = loc.meta_title;
                document.getElementById('loc_meta_desc').value = loc.meta_description;
                document.getElementById('loc_type').value = loc.type;
                document.getElementById('loc_intro').value = loc.intro;
                document.getElementById('loc_local_note').value = loc.local_note;
                
                if (tinymce.get('loc_local_note')) {
                    tinymce.get('loc_local_note').setContent(loc.local_note || '');
                }
                
                document.getElementById('loc_address').value = loc.address || '';
                document.getElementById('loc_status').value = loc.status;
                ['phone', 'map_url', 'hero_image', 'sort_order'].forEach(function (field) {
                    const input = document.getElementById('loc_' + field);
                    if (input) input.value = loc[field] ?? (field === 'sort_order' ? 0 : '');
                });

                // Load Areas
                const areasArr = JSON.parse(loc.areas || '[]');
                document.getElementById('loc_areas').value = areasArr.join(', ');

                // Load Scenarios
                const scenariosContainer = document.getElementById('scenariosRowContainer');
                scenariosContainer.innerHTML = '';
                const scenariosArr = JSON.parse(loc.scenarios || '[]');
                scenariosArr.forEach(sc => {
                    addScenarioRow(sc[0] || '', sc[1] || '');
                });

                // Load Nearby
                const nearbyContainer = document.getElementById('nearbyRowContainer');
                nearbyContainer.innerHTML = '';
                const nearbyObj = JSON.parse(loc.nearby || '{}');
                for (const [key, value] of Object.entries(nearbyObj)) {
                    addNearbyRow(key, value);
                }

                // Load FAQs
                const faqsContainer = document.getElementById('faqsRowContainer');
                faqsContainer.innerHTML = '';
                const faqsArr = JSON.parse(loc.faqs || '[]');
                faqsArr.forEach(faq => {
                    addFaqRow(faq.q || '', faq.a || '');
                });

                 // Calculate dynamic default placeholders based on the current city name
                const cityName = loc.name || 'City';
                const topAreas = areasArr.slice(0, 5).join(', ') || cityName;

                const productsMeta = <?php echo json_encode(waqt_location_product_templates(true), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

                // Reset and calculate placeholders for all override fields
                document.querySelectorAll('.override-field').forEach(field => {
                    field.value = '';
                    const prod = field.getAttribute('data-prod');
                    const prop = field.getAttribute('data-field');
                    
                    const prodName = productsMeta[prod] ? productsMeta[prod].name : '';
                    const minAmount = productsMeta[prod] ? productsMeta[prod].min_amount : '₹5,000';
                    const tenure = productsMeta[prod] ? productsMeta[prod].tenure : '12 to 60 Months';

                    let defaultVal = '';
                    if (prop === 'title') {
                        defaultVal = `${prodName} in ${cityName}`;
                    } else if (prop === 'meta_title') {
                        defaultVal = `${prodName} in ${cityName} | Online Application | Waqt Finance`;
                    } else if (prop === 'meta_description') {
                        defaultVal = `Apply online for ${prodName} in ${cityName} with Waqt Finance. Get ${minAmount} funding with tenure options of ${tenure}. Serving customers in ${topAreas}.`;
                    } else if (prop === 'description') {
                        defaultVal = `Apply online with Waqt Finance for ${prodName.toLowerCase()} options in ${cityName}. We support borrowers with a digital application process, consent-based checks, document review, and clear terms before acceptance.`;
                    } else if (prop === 'min_amount') {
                        defaultVal = minAmount;
                    } else if (prop === 'tenure') {
                        defaultVal = tenure;
                    } else if (prop === 'interest') {
                        defaultVal = productsMeta[prod] ? (prod === 'personal-loan' ? 'Starting at 10.99% p.a.' : 'Competitive rates') : 'Competitive rates';
                    }
                    
                    field.setAttribute('placeholder', 'Inherits shared product content; leave blank to keep inheritance.');
                    
                    if (field.classList.contains('rich-override-editor') && tinymce.get('override_desc_' + prod)) {
                        tinymce.get('override_desc_' + prod).setContent('');
                        // We also set the placeholder inside TinyMCE editor body!
                        const editor = tinymce.get('override_desc_' + prod);
                        if (editor.getBody()) {
                            editor.getBody().setAttribute('data-placeholder', defaultVal);
                        }
                    }
                });

                // Clear all product FAQ containers
                const productsListKeys = ['personal-loan', 'business-loan', 'pay-day-loan', 'edi-loan', 'vehicle-loan', 'loan-against-property'];
                productsListKeys.forEach(prod => {
                    const container = document.getElementById('prodFaqsContainer_' + prod);
                    if (container) {
                        container.innerHTML = '';
                    }
                });

                // Robust parser for product_content
                let productContent = {};
                try {
                    let rawContent = loc.product_content;
                    if (rawContent) {
                        if (typeof rawContent === 'string') {
                            productContent = JSON.parse(rawContent);
                            if (typeof productContent === 'string') {
                                productContent = JSON.parse(productContent); // handle double stringification
                            }
                        } else if (typeof rawContent === 'object') {
                            productContent = rawContent;
                        }
                    }
                } catch(e) {
                    console.error("Error parsing product_content:", e);
                }
                if (!productContent || typeof productContent !== 'object') {
                    productContent = {};
                }

                // Load only saved overrides. Shared content remains inherited until explicitly edited.
                document.querySelectorAll('.override-field').forEach(field => {
                    const prod = field.getAttribute('data-prod');
                    const prop = field.getAttribute('data-field');
                    
                    const prodName = productsMeta[prod] ? productsMeta[prod].name : '';
                    const minAmount = productsMeta[prod] ? productsMeta[prod].min_amount : '₹5,000';
                    const tenure = productsMeta[prod] ? productsMeta[prod].tenure : '12 to 60 Months';

                    let defaultVal = '';
                    if (prop === 'title') {
                        defaultVal = `${prodName} in ${cityName}`;
                    } else if (prop === 'meta_title') {
                        defaultVal = `${prodName} in ${cityName} | Online Application | Waqt Finance`;
                    } else if (prop === 'meta_description') {
                        defaultVal = `Apply online for ${prodName} in ${cityName} with Waqt Finance. Get ${minAmount} funding with tenure options of ${tenure}. Serving customers in ${topAreas}.`;
                    } else if (prop === 'description') {
                        defaultVal = `Apply online with Waqt Finance for ${prodName.toLowerCase()} options in ${cityName}. We support borrowers with a digital application process, consent-based checks, document review, and clear terms before acceptance.`;
                    } else if (prop === 'min_amount') {
                        defaultVal = minAmount;
                    } else if (prop === 'tenure') {
                        defaultVal = tenure;
                    } else if (prop === 'interest') {
                        defaultVal = productsMeta[prod] ? (prod === 'personal-loan' ? 'Starting at 10.99% p.a.' : 'Competitive rates') : 'Competitive rates';
                    }

                    let val = ''; // Empty means inherit shared product content.
                    if (productContent[prod] && productContent[prod][prop] !== undefined && productContent[prod][prop] !== '') {
                        val = productContent[prod][prop];
                    }

                    field.value = val;
                    if (field.classList.contains('rich-override-editor') && tinymce.get('override_desc_' + prod)) {
                        tinymce.get('override_desc_' + prod).setContent(val || '');
                    }
                });

                // Populate product-specific FAQs
                productsListKeys.forEach(prod => {
                    let faqsToLoad = [];
                    if (productContent[prod] && Array.isArray(productContent[prod]['faqs'])) {
                        faqsToLoad = productContent[prod]['faqs'];

                    }
                    
                    faqsToLoad.forEach(faq => {
                        addProdFaqRow(prod, faq.q || '', faq.a || '');
                    });
                });

                switchProductOverride('personal-loan'); // reset active sub-tab

                // Update fields
                toggleTypeFields();
                metaTitleInput.dispatchEvent(new Event('input'));
                metaDescInput.dispatchEvent(new Event('input'));
                
                location.hash = 'edit-' + loc.id;
                showEditor("Edit Location: " + loc.name);
                document.getElementById('locationErrors').hidden = true;
                document.querySelectorAll('#locationForm [aria-invalid]').forEach(el => el.removeAttribute('aria-invalid'));
            });
        });

        // STATUS TOGGLE BUTTON CLICK
        document.querySelectorAll('.status-toggle-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const nextStatus = btn.getAttribute('data-next-status') || 'draft';

                Swal.fire({
                    title: 'Change Page Visibility?',
                    text: `Do you want to mark this location as ${nextStatus === 'published' ? 'Active' : 'Draft'}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: 'var(--primary)',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, change'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('csrf_token', csrfToken);
                        formData.append('action', 'update_location_status');
                        formData.append('id', id);
                        formData.append('status', nextStatus);

                        fetch('api.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    title: 'Status Updated!',
                                    text: data.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', data.message || 'Status update failed.', 'error');
                            }
                        })
                        .catch(err => {
                            Swal.fire('Error', 'Connection failed.', 'error');
                        });
                    }
                });
            });
        });

        // DELETE BUTTON CLICK
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');

                Swal.fire({
                    title: 'Delete Location?',
                    text: "Are you sure you want to permanently delete this location? This will break its URL and navigation links.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: 'var(--danger)',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, delete it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('csrf_token', csrfToken);
                        formData.append('action', 'delete_location');
                        formData.append('id', id);

                        fetch('api.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: data.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', data.message || 'Deletion failed.', 'error');
                            }
                        })
                        .catch(err => {
                            Swal.fire('Error', 'Connection failed.', 'error');
                        });
                    }
                });
            });
        });

        // FORM SUBMISSION (CREATE/UPDATE)
        document.getElementById('locationForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Save TinyMCE main content
            if (tinymce.get('loc_local_note')) {
                tinymce.get('loc_local_note').save();
            }

            // Save TinyMCE overrides content
            document.querySelectorAll('.rich-override-editor').forEach(field => {
                const prod = field.getAttribute('data-prod');
                if (tinymce.get('override_desc_' + prod)) {
                    tinymce.get('override_desc_' + prod).save();
                }
            });

            // Manual validation to avoid hidden field HTML5 browser validation blocks
            const name = document.getElementById('loc_name').value.trim();
            const slug = document.getElementById('loc_slug').value.trim();
            const title = document.getElementById('loc_title').value.trim();
            const intro = document.getElementById('loc_intro').value.trim();
            const localNote = document.getElementById('loc_local_note').value.trim();
            const metaTitle = document.getElementById('loc_meta_title').value.trim();
            const metaDesc = document.getElementById('loc_meta_desc').value.trim();

            const errors = [];
            this.querySelectorAll('[aria-invalid]').forEach(el => el.removeAttribute('aria-invalid'));
            const candidates = Array.from(this.querySelectorAll('input, select, textarea')).filter(el => el.id !== 'loc_local_note' && !el.classList.contains('rich-override-editor'));
            candidates.forEach(el => { if (!el.checkValidity() || (el.required && !el.value.trim())) { errors.push(el); el.setAttribute('aria-invalid', 'true'); } });
            const errorBox = document.getElementById('locationErrors');
            errorBox.hidden = !errors.length;
            if (errors.length) {
                errorBox.textContent = 'Please complete or correct the highlighted fields. Slugs use lowercase letters, numbers and single hyphens.';
                const first = errors[0];
                const panel = first.closest('.tab-content');
                if (panel) switchTab(panel.id.replace('tabContent-', ''));
                const prodBox = first.closest('[id^="overrideBox-"]');
                if (prodBox) switchProductOverride(prodBox.id.replace('overrideBox-', ''));
                first.focus(); return;
            }

            // Prepare Areas array
            const areasVal = document.getElementById('loc_areas').value.trim();
            const areasArr = areasVal.split(',').map(a => a.trim()).filter(a => a !== '');

            // Prepare Scenarios array of arrays
            const scenarioRows = document.querySelectorAll('#scenariosRowContainer .dynamic-row-item');
            const scenariosArr = [];
            scenarioRows.forEach(row => {
                const title = row.querySelector('.scenario-title-input').value.trim();
                const text = row.querySelector('.scenario-text-input').value.trim();
                if (title !== '' || text !== '') {
                    scenariosArr.push([title, text]);
                }
            });

            // Prepare Nearby object
            const nearbyRows = document.querySelectorAll('#nearbyRowContainer .dynamic-row-item');
            const nearbyObj = {};
            nearbyRows.forEach(row => {
                const slug = row.querySelector('.nearby-slug-input').value.trim().toLowerCase();
                const label = row.querySelector('.nearby-label-input').value.trim();
                if (slug !== '' && label !== '') {
                    nearbyObj[slug] = label;
                }
            });

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

            // Prepare product overrides JSON object
            const productOverrides = {};
            const productsListKeys = ['personal-loan', 'business-loan', 'pay-day-loan', 'edi-loan', 'vehicle-loan', 'loan-against-property'];
            
            productsListKeys.forEach(prod => {
                const prodBox = document.getElementById('overrideBox-' + prod);
                if (prodBox) {
                    // 1. Collect standard inputs (title, meta_title, meta_description, description, min_amount, tenure, interest)
                    const fields = prodBox.querySelectorAll('.override-field');
                    fields.forEach(field => {
                        const prop = field.getAttribute('data-field');
                        const val = field.value.trim();
                        if (val !== '') {
                            if (!productOverrides[prod]) {
                                productOverrides[prod] = {};
                            }
                            productOverrides[prod][prop] = val;
                        }
                    });

                    // 2. Collect product-specific FAQs
                    const faqRows = prodBox.querySelectorAll('.dynamic-row-item');
                    const faqsArr = [];
                    faqRows.forEach(row => {
                        const q = row.querySelector('.prod-faq-q-input').value.trim();
                        const a = row.querySelector('.prod-faq-a-input').value.trim();
                        if (q !== '' || a !== '') {
                            faqsArr.push({ q: q, a: a });
                        }
                    });

                    if (faqsArr.length > 0) {
                        if (!productOverrides[prod]) {
                            productOverrides[prod] = {};
                        }
                        productOverrides[prod]['faqs'] = faqsArr;
                    }
                }
            });

            // Build FormData
            const form = document.getElementById('locationForm');
            const formData = new FormData(form);
            formData.append('csrf_token', csrfToken);
            formData.append('action', 'save_location');
            formData.append('areas', JSON.stringify(areasArr));
            formData.append('scenarios', JSON.stringify(scenariosArr));
            formData.append('nearby', JSON.stringify(nearbyObj));
            formData.append('faqs', JSON.stringify(faqsArr));
            formData.append('product_content', JSON.stringify(productOverrides));

            // Visual feedback loading
            const saveBtn = document.getElementById('saveLocationBtn');
            if (saveBtn.disabled) return;
            const origHTML = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving Location...';

            fetch('api.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = origHTML;

                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Saved Successfully!',
                        text: data.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        locationSnapshot = '';
                        history.replaceState(null, '', location.pathname + location.search);
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message || 'Failed to save location.', 'error');
                }
            })
            .catch(err => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = origHTML;
                Swal.fire('Error', 'Connection failed.', 'error');
            });
        });

        // Hash Routing & State Restoration on Refresh/History Back-Forward
        function checkUrlHash() {
            const hash = location.hash;
            if (!hash || hash === '#') {
                showList();
                return;
            }

            if (hash === '#create') {
                if (document.getElementById('editorContainer').classList.contains('hidden')) document.getElementById('createLocationBtn').click();
            } else if (hash.startsWith('#edit-')) {
                const locId = hash.substring(6);
                const row = document.getElementById('loc-row-' + locId);
                if (row) {
                    const editBtn = row.querySelector('.edit-btn');
                    if (editBtn) {
                        const currentFormId = document.getElementById('locationId').value;
                        if (currentFormId !== locId) {
                            editBtn.click();
                        }
                    }
                } else {
                    showList();
                }
            }
        }

        window.addEventListener('hashchange', function() {
            const hash = location.hash;
            if (hash === '#create' || hash.startsWith('#edit-')) {
                checkUrlHash();
            } else if (!hash || hash === '#') {
                if (locationDirty() && !confirm('Discard unsaved location changes?')) { history.replaceState(null, '', location.pathname + location.search + (document.getElementById('locationId').value === '0' ? '#create' : '#edit-' + document.getElementById('locationId').value)); return; }
                showList();
            }
        });

        document.querySelectorAll('.cms-tab-btn').forEach((btn, i, buttons) => {
            const key = btn.id.replace('tabBtn-', '');
            btn.setAttribute('role', 'tab'); btn.setAttribute('aria-controls', 'tabContent-' + key);
            const panel = document.getElementById('tabContent-' + key);
            panel.setAttribute('role', 'tabpanel'); panel.setAttribute('aria-labelledby', btn.id);
            btn.addEventListener('keydown', e => {
                let next = i;
                if (e.key === 'ArrowRight') next = (i + 1) % buttons.length;
                else if (e.key === 'ArrowLeft') next = (i + buttons.length - 1) % buttons.length;
                else if (e.key === 'Home') next = 0;
                else if (e.key === 'End') next = buttons.length - 1;
                else return;
                e.preventDefault(); buttons[next].click(); buttons[next].focus();
            });
        });
        document.querySelector('.cms-tabs-header').setAttribute('role', 'tablist');
        document.querySelector('.cms-tabs-header').setAttribute('aria-label', 'Location content sections');
        document.querySelectorAll('.dynamic-row-item input, .dynamic-row-item textarea').forEach(el => el.setAttribute('aria-label', el.placeholder || 'Row content'));
        // Run check on initial load to restore view state
        checkUrlHash();
    });
</script>

<?php include 'footer.php'; ?>
