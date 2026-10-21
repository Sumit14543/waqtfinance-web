<?php
declare(strict_types=1);

$pageTitle = 'CIBIL Credit Reports';
$pageSubtitle = 'Review pulled credit bureau scores, logs, and PDF files';

include 'header.php';

$conn = db();

// Pagination Config
$limit = 10;
$page = (int) ($_GET['page'] ?? 1);
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Filters
$search = trim((string) ($_GET['search'] ?? ''));

// Build queries
$conditions = [];
$params = [];
$types = '';

if ($search !== '') {
    $conditions[] = "(full_name LIKE ? OR mobile LIKE ? OR pan LIKE ? OR ref_id LIKE ?)";
    $searchWild = "%$search%";
    $params[] = $searchWild;
    $params[] = $searchWild;
    $params[] = $searchWild;
    $params[] = $searchWild;
    $types .= 'ssss';
}

$whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Count Total
$countSql = "SELECT COUNT(*) FROM cibil_reports $whereClause";
$countStmt = $conn->prepare($countSql);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalRecords = (int) ($countStmt->get_result()->fetch_row()[0] ?? 0);
$totalPages = (int) ceil($totalRecords / $limit);
if ($totalPages < 1) $totalPages = 1;

// Fetch Records
$fetchSql = "SELECT * FROM cibil_reports $whereClause ORDER BY id DESC LIMIT ? OFFSET ?";
$fetchStmt = $conn->prepare($fetchSql);
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';
$fetchStmt->bind_param($types, ...$params);
$fetchStmt->execute();
$reports = $fetchStmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<style>
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
        color: #4ade80;
        border: 1px solid rgba(34, 197, 94, 0.2);
    }
    .score-good {
        background: rgba(16, 185, 129, 0.1);
        color: #34d399;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }
    .score-fair {
        background: rgba(245, 158, 11, 0.1);
        color: #fbbf24;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }
    .score-poor {
        background: rgba(239, 68, 68, 0.1);
        color: #fca5a5;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
    .score-none {
        background: rgba(156, 163, 175, 0.1);
        color: #9ca3af;
        border: 1px solid rgba(156, 163, 175, 0.2);
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
        max-width: 650px;
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
    .doc-preview-link {
        color: var(--primary);
        text-decoration: underline;
        font-weight: 600;
    }
    .doc-preview-link:hover {
        color: #ffc229;
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
</style>

<div class="data-card">
    <div class="card-header-flex">
        <h2 class="card-title-text"><i class="fa-solid fa-gauge-high" style="color: var(--primary); margin-right: 8px;"></i> Credit Bureau Pull Logs</h2>
    </div>

    <!-- Search Form -->
    <form method="GET" action="cibil.php" class="filter-row">
        <input type="text" name="search" class="search-input" placeholder="Search by name, mobile, PAN, ref id..." value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
        <button type="submit" class="btn-primary-adm">Apply Filter</button>
        <?php if ($search !== ''): ?>
            <a href="cibil.php" class="btn-action" style="width: auto; padding: 0 12px; height: 36px; display: inline-flex; align-items: center; justify-content: center; font-size: 13px;" title="Reset filters">Reset</a>
        <?php endif; ?>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Mobile</th>
                    <th>PAN Number</th>
                    <th>CIBIL Score</th>
                    <th>Pull Date</th>
                    <th>PDF Report</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reports)): ?>
                    <tr>
                        <td colspan="8" class="text-center" style="color: var(--text-muted); padding: 40px 0;">No credit reports recorded in database.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reports as $r): 
                        $score = $r['score'] !== null ? (int)$r['score'] : null;
                        $scoreClass = 'score-none';
                        if ($score !== null) {
                            if ($score >= 750) $scoreClass = 'score-excellent';
                            elseif ($score >= 700) $scoreClass = 'score-good';
                            elseif ($score >= 600) $scoreClass = 'score-fair';
                            else $scoreClass = 'score-poor';
                        }
                    ?>
                        <tr>
                            <td>#<?php echo $r['id']; ?></td>
                            <td><strong style="color: var(--text-main); font-weight: 700;"><?php echo htmlspecialchars($r['full_name'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                            <td><?php echo htmlspecialchars($r['mobile'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><span style="font-family: monospace; font-weight:700; text-transform: uppercase;"><?php echo htmlspecialchars($r['pan'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                            <td><span class="score-badge <?php echo $scoreClass; ?>"><?php echo $score !== null ? $score : 'N/A'; ?></span></td>
                            <td><?php echo date('d M Y, h:i A', strtotime($r['created_at'])); ?></td>
                            <td>
                                <?php if (!empty($r['pdf_url'])): ?>
                                    <a href="../<?php echo htmlspecialchars($r['pdf_url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="doc-preview-link" style="font-size:13px;">
                                        <i class="fa-solid fa-file-pdf"></i> Download PDF
                                    </a>
                                <?php else: ?>
                                    <span style="color:var(--text-muted); font-size:12px;">Not Generated</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="button" class="btn-action view-report-btn" data-report='<?php echo htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8'); ?>' title="Inspect Bureau Log">
                                    <i class="fa-solid fa-code"></i>
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
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">&laquo; Prev</a>
                <?php endif; ?>
            </li>
            
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <li class="page-item <?php echo $p === $page ? 'active' : ''; ?>">
                    <?php if ($p === $page): ?>
                        <span><?php echo $p; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $p; ?>&search=<?php echo urlencode($search); ?>"><?php echo $p; ?></a>
                    <?php endif; ?>
                </li>
            <?php endfor; ?>
            
            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                <?php if ($page >= $totalPages): ?>
                    <span style="opacity:0.5; cursor:not-allowed;">Next &raquo;</span>
                <?php else: ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Next &raquo;</a>
                <?php endif; ?>
            </li>
        </ul>
    <?php endif; ?>
</div>

<!-- Details Drawer Modal -->
<div class="drawer-overlay" id="detailDrawer">
    <div class="drawer-content">
        <div class="drawer-header">
            <h2 class="card-title-text">Credit Bureau Inquiry Log</h2>
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
    const closeDrawer = document.getElementById('closeDrawer');
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[char]));
    const formatPre = (value, color) => `<pre style="background:#080c16; padding:15px; border-radius:8px; border:1px solid rgba(255,255,255,0.05); color:${color}; font-family:monospace; font-size:12px; overflow-x:auto; max-height:250px;">${escapeHtml(value)}</pre>`;
    const formatJsonPre = (value, color) => {
        try {
            return formatPre(JSON.stringify(JSON.parse(value), null, 2), color);
        } catch (e) {
            return formatPre(value, color);
        }
    };
    
    closeDrawer.addEventListener('click', () => drawer.classList.remove('active'));
    drawer.addEventListener('click', (e) => {
        if (e.target === drawer) drawer.classList.remove('active');
    });

    document.querySelectorAll('.view-report-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const r = JSON.parse(btn.getAttribute('data-report'));
            
            let scoreLabel = 'N/A';
            let scoreValue = r.score !== null && r.score !== '' ? Number(r.score) : null;
            if (!Number.isFinite(scoreValue)) {
                scoreValue = null;
            }
            if (scoreValue !== null) {
                if (scoreValue >= 750) scoreLabel = `<span class="score-badge score-excellent">${scoreValue} (Excellent)</span>`;
                else if (scoreValue >= 700) scoreLabel = `<span class="score-badge score-good">${scoreValue} (Good)</span>`;
                else if (scoreValue >= 600) scoreLabel = `<span class="score-badge score-fair">${scoreValue} (Fair)</span>`;
                else scoreLabel = `<span class="score-badge score-poor">${scoreValue} (Poor)</span>`;
            }

            // Safe formatting of JSON strings
            let rawRespHTML = 'N/A';
            if (r.raw_response) {
                rawRespHTML = formatJsonPre(r.raw_response, '#22d3ee');
            }

            let analysisHTML = 'No detailed analysis found.';
            if (r.analysis_json) {
                analysisHTML = formatJsonPre(r.analysis_json, '#a78bfa');
            }

            const pdfUrl = String(r.pdf_url || '');
            const safePdfUrl = escapeHtml(pdfUrl);
            const pdfLinkHTML = pdfUrl
                ? `<a href="../${safePdfUrl}" target="_blank" class="doc-preview-link"><i class="fa-solid fa-file-pdf"></i> View report</a>`
                : 'N/A';
            const createdAt = r.created_at ? new Date(r.created_at).toLocaleString('en-IN') : 'N/A';

            drawerBody.innerHTML = `
                <div style="background: rgba(255,184,0,0.05); padding:16px; border-radius:12px; border:1px dashed var(--primary); margin-bottom: 20px;">
                    <div class="detail-grid" style="grid-template-columns: repeat(2, 1fr);">
                        <div class="detail-item">
                            <div class="detail-label">Report ID</div>
                            <div class="detail-val" style="color:var(--primary); font-size:16px; font-weight:700;">#${escapeHtml(r.id)}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Bureau Score</div>
                            <div class="detail-val" style="margin-top:4px;">${scoreLabel}</div>
                        </div>
                    </div>
                </div>

                <div class="section-title">Inquired Applicant Details</div>
                <div class="detail-grid">
                    <div class="detail-item"><div class="detail-label">Full Name</div><div class="detail-val">${escapeHtml(r.full_name || 'N/A')}</div></div>
                    <div class="detail-item"><div class="detail-label">Mobile Number</div><div class="detail-val">${escapeHtml(r.mobile || 'N/A')}</div></div>
                    <div class="detail-item"><div class="detail-label">Email Address</div><div class="detail-val">${escapeHtml(r.email || 'N/A')}</div></div>
                    <div class="detail-item"><div class="detail-label">PAN Number</div><div class="detail-val" style="text-transform:uppercase; font-family:monospace; font-weight:700;">${escapeHtml(r.pan || 'N/A')}</div></div>
                    <div class="detail-item"><div class="detail-label">Application Reference ID</div><div class="detail-val" style="font-family:monospace;">${escapeHtml(r.application_id || 'N/A')}</div></div>
                    <div class="detail-item"><div class="detail-label">Lead Reference ID</div><div class="detail-val" style="font-family:monospace;">${escapeHtml(r.lead_id || 'N/A')}</div></div>
                </div>

                <div class="section-title">Bureau Inquiry Metadata</div>
                <div class="detail-grid">
                    <div class="detail-item"><div class="detail-label">Reference ID (Bifrost)</div><div class="detail-val" style="font-family:monospace;">${escapeHtml(r.ref_id || 'N/A')}</div></div>
                    <div class="detail-item"><div class="detail-label">API Triggered Time</div><div class="detail-val">${escapeHtml(createdAt)}</div></div>
                    <div class="detail-item"><div class="detail-label">PDF Generated URL</div><div class="detail-val">${pdfLinkHTML}</div></div>
                    <div class="detail-item"><div class="detail-label">Analysis Status</div><div class="detail-val" style="text-transform:capitalize;">${escapeHtml(r.analysis_status || 'N/A')}</div></div>
                </div>

                <div class="section-title">Raw Bureau Response</div>
                ${rawRespHTML}

                <div class="section-title">AI Analysis JSON</div>
                ${analysisHTML}
            `;
            drawer.classList.add('active');
        });
    });
</script>

<?php include 'footer.php'; ?>
