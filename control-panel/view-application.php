<?php
declare(strict_types=1);

$pageTitle = 'Application Details';
$pageSubtitle = 'Inspect applicant profile, loan details, documents, and processing state';

include 'header.php';

$conn = db();

$id = (int)($_GET['id'] ?? 0);
$type = trim((string)($_GET['type'] ?? 'payday'));

$backPage = (int)($_GET['page'] ?? 1);
$backSearch = trim((string)($_GET['search'] ?? ''));
$backStatus = trim((string)($_GET['status'] ?? ''));
$backDateFrom = trim((string)($_GET['date_from'] ?? ''));
$backDateTo = trim((string)($_GET['date_to'] ?? ''));
$backAmountMin = trim((string)($_GET['amount_min'] ?? ''));
$backAmountMax = trim((string)($_GET['amount_max'] ?? ''));
$backLoanType = trim((string)($_GET['loan_type'] ?? ''));

$backQuery = [
    'tab' => $type,
    'page' => $backPage,
    'search' => $backSearch,
    'status' => $backStatus,
    'date_from' => $backDateFrom,
    'date_to' => $backDateTo,
    'amount_min' => $backAmountMin,
    'amount_max' => $backAmountMax,
    'loan_type' => $backLoanType,
];
$backParams = http_build_query(array_filter($backQuery, static fn($value) => $value !== '' && $value !== null));

if ($id <= 0 || !in_array($type, ['payday', 'wizard'], true)) {
    echo '<div style="padding: 30px;"><div class="badge-status badge-rejected" style="font-size:16px; padding:15px; display:block; text-align:center;">Invalid Application Parameters.</div></div>';
    include 'footer.php';
    exit;
}

$app = null;
if ($type === 'payday') {
    $stmt = $conn->prepare("SELECT * FROM loan_applications WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $app = $stmt->get_result()->fetch_assoc();
} else {
    $stmt = $conn->prepare("SELECT * FROM apply_loan_applications WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $app = $stmt->get_result()->fetch_assoc();
}

if (!$app) {
    echo '<div style="padding: 30px;"><div class="badge-status badge-rejected" style="font-size:16px; padding:15px; display:block; text-align:center;">Application not found.</div></div>';
    include 'footer.php';
    exit;
}

// Common variables
$displayId = $type === 'payday' ? ($app['application_id'] ?? 'N/A') : ($app['application_ref'] ?? 'N/A');
$loanTypeLabel = $type === 'payday' ? 'Payday Loan' : ($app['loan_type'] ?? 'General Loan');
$displayAmount = is_numeric($app['loan_amount']) ? number_format((float)$app['loan_amount'], 2) : htmlspecialchars((string)$app['loan_amount'], ENT_QUOTES, 'UTF-8');
$createdAt = date('d M Y, h:i A', strtotime($app['created_at']));

// Fetch matched CIBIL bureau report
$cibilReport = null;
$pan = trim((string)($app['pan_number'] ?? ''));
$mobile = trim((string)($app['mobile'] ?? ''));
$appId = trim((string)($app['application_id'] ?? $app['application_ref'] ?? ''));

if ($pan !== '' || $mobile !== '' || $appId !== '') {
    $cStmt = $conn->prepare("SELECT * FROM cibil_reports WHERE application_id = ? OR (pan = ? AND pan != '') OR (mobile = ? AND mobile != '') ORDER BY id DESC LIMIT 1");
    $cStmt->bind_param('sss', $appId, $pan, $mobile);
    $cStmt->execute();
    $cibilReport = $cStmt->get_result()->fetch_assoc();
}

// Fetch document checks
$docChecks = [];
$dcStmt = $conn->prepare("SELECT * FROM lead_document_checks WHERE application_id = ?");
$dcStmt->bind_param('s', $displayId);
$dcStmt->execute();
$dcRes = $dcStmt->get_result();
while ($row = $dcRes->fetch_assoc()) {
    $docChecks[$row['document_key']] = $row;
}

// Fetch activities timeline (comments & changes)
$activities = [];
$actStmt = $conn->prepare("SELECT * FROM lead_activities WHERE application_id = ? ORDER BY id DESC");
$actStmt->bind_param('s', $displayId);
$actStmt->execute();
$activities = $actStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch scheduled followups
$followups = [];
$fupStmt = $conn->prepare("SELECT * FROM lead_followups WHERE application_id = ? ORDER BY due_at ASC");
$fupStmt->bind_param('s', $displayId);
$fupStmt->execute();
$followups = $fupStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch credit assessment memo (CAM) sheets
$camSheet = null;
$camStmt = $conn->prepare("SELECT * FROM lead_cam_sheets WHERE application_id = ? ORDER BY version DESC LIMIT 1");
$camStmt->bind_param('s', $displayId);
$camStmt->execute();
$camSheet = $camStmt->get_result()->fetch_assoc();

$getDocLink = function($path, $name) {
    if (empty($path) || trim($path) === '') {
        return '<span style="color:var(--text-muted); font-style:italic;">Not Uploaded</span>';
    }
    $url = waqt_document_url((string) $path, '../');
    return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" class="doc-preview-link" onclick="viewImageLightbox(' . json_encode($url, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) . ', ' . json_encode($name, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) . '); return false;"><i class="fa-solid fa-file-image"></i> View ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</a>';
};

function mask_admin_sensitive_value(?string $value, int $visibleStart = 0, int $visibleEnd = 4): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return 'N/A';
    }

    $length = strlen($value);
    if ($length <= ($visibleStart + $visibleEnd)) {
        return str_repeat('*', max(4, $length));
    }

    return substr($value, 0, $visibleStart)
        . str_repeat('*', max(4, $length - $visibleStart - $visibleEnd))
        . substr($value, -$visibleEnd);
}

$maskedPanNumber = mask_admin_sensitive_value((string)($app['pan_number'] ?? ''), 5, 1);
$maskedAadhaarNumber = waqt_mask_stored_sensitive_value((string)($app['aadhaar_number'] ?? ''), 0, 4);
if ($maskedAadhaarNumber === '') {
    $maskedAadhaarNumber = 'N/A';
}
$maskedAadhaarStored = !empty($app['aadhaar_masked'])
    ? (string) $app['aadhaar_masked']
    : $maskedAadhaarNumber;
$maskedAadhaarUniqueId = mask_admin_sensitive_value((string)($app['aadhaar_unique_id'] ?? ''), 0, 6);
?>

<style>
    .view-container {
        width: 100%;
        max-width: 100%;
        margin: 0;
        padding-bottom: 50px;
    }
    .action-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 15px;
    }
    .btn-outline {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border: 1px solid var(--border-color);
        background: #ffffff;
        color: var(--text-muted);
        border-radius: 8px;
        font-weight: 600;
        font-size: 13.5px;
        text-decoration: none;
        transition: all 0.25s ease;
        cursor: pointer;
    }
    .btn-outline:hover {
        background: #f8fafc;
        color: var(--text-main);
        border-color: #cbd5e1;
    }

    /* Split Sidebar details layout */
    .view-split-layout {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 30px;
        align-items: start;
    }
    @media (max-width: 992px) {
        .view-split-layout {
            grid-template-columns: 1fr;
        }
    }

    /* Left Sidebar summary */
    .left-summary-panel {
        position: sticky;
        top: 100px;
        display: flex;
        flex-direction: column;
        gap: 20px;
        z-index: 10;
    }
    .summary-profile-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        text-align: center;
    }
    .profile-avatar-wrapper {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid var(--primary);
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px auto;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .profile-avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .profile-avatar-img:hover {
        transform: scale(1.05);
    }
    .summary-profile-card h2 {
        font-family: 'Outfit', sans-serif;
        font-size: 18px;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 5px;
    }
    .profile-app-id {
        font-family: monospace;
        font-size: 11.5px;
        background: #f1f5f9;
        padding: 4px 10px;
        border-radius: 6px;
        display: inline-block;
        color: var(--text-muted);
        margin-bottom: 15px;
        word-break: break-all;
    }
    .profile-badges-row {
        display: flex;
        justify-content: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }
    .status-badge {
        font-size: 10px;
        font-weight: 800;
        padding: 5px 12px;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .priority-badge {
        font-size: 10px;
        font-weight: 800;
        padding: 5px 12px;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #ffffff;
    }
    .priority-high { background: var(--danger); }
    .priority-medium { background: var(--warning); color: #000000; }
    .priority-low { background: var(--success); }

    .profile-meta-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        text-align: left;
        border-top: 1px solid rgba(0,0,0,0.05);
        padding-top: 18px;
    }
    .meta-list-item {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        color: var(--text-muted);
        font-weight: 600;
    }
    .meta-list-item i {
        color: var(--primary);
        font-size: 14px;
        width: 18px;
        text-align: center;
    }
    .form-select-simple {
        height: 38px;
        padding: 0 12px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 13.5px;
        background: #f8fafc;
        outline: none;
        width: 100%;
        font-weight: 600;
        margin-bottom: 10px;
    }

    /* Right content area */
    .right-details-panel {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }
    .details-cards-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
    }
    @media (max-width: 1200px) {
        .details-cards-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .info-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 24px;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        border-left: 4px solid var(--border-color);
    }
    .info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.03);
        border-left-color: var(--primary);
    }
    .card-title {
        font-size: 13.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--primary);
        margin-bottom: 20px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: 'Outfit', sans-serif;
    }
    .grid-fields {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    @media (max-width: 576px) {
        .grid-fields {
            grid-template-columns: 1fr;
        }
    }
    .field-item {
        margin-bottom: 4px;
    }
    .field-label {
        font-size: 10.5px;
        font-weight: 750;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .field-value {
        font-size: 13.5px;
        font-weight: 600;
        color: var(--text-main);
    }
    .doc-preview-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        background: rgba(24, 82, 156, 0.06);
        border: 1px solid rgba(24, 82, 156, 0.1);
        color: var(--primary);
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
        margin-top: 2px;
    }
    .doc-preview-link:hover {
        background: var(--primary);
        color: #ffffff;
        border-color: var(--primary);
    }

    /* Visual Timeline Styles */
    .status-timeline {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        padding: 15px 0 5px 0;
        margin: 10px 0;
    }
    .status-timeline::before {
        content: '';
        position: absolute;
        top: 31px;
        left: 5%;
        right: 5%;
        height: 4px;
        background: #f1f5f9;
        z-index: 1;
    }
    .timeline-progress-bar {
        position: absolute;
        top: 31px;
        left: 5%;
        height: 4px;
        background: var(--primary);
        z-index: 2;
        transition: width 0.4s ease;
    }
    .timeline-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 3;
        width: 80px;
    }
    .step-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #ffffff;
        border: 3px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 800;
        color: var(--text-muted);
        transition: all 0.3s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .timeline-step.completed .step-circle {
        background: var(--primary);
        border-color: var(--primary);
        color: #ffffff;
    }
    .timeline-step.active .step-circle {
        background: #ffffff;
        border-color: var(--primary);
        color: var(--primary);
        box-shadow: 0 0 0 5px rgba(24, 82, 156, 0.15);
    }
    .timeline-step.rejected .step-circle {
        background: var(--danger);
        border-color: var(--danger);
        color: #ffffff;
    }
    .step-label {
        font-size: 10px;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-top: 8px;
        letter-spacing: 0.5px;
        text-align: center;
    }
    .timeline-step.completed .step-label,
    .timeline-step.active .step-label {
        color: var(--text-main);
    }
    .timeline-step.rejected .step-label {
        color: var(--danger);
    }

    /* CIBIL Score Widget Gauge */
    .cibil-gauge-wrapper {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        border: 8px solid #f1f5f9;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        flex-shrink: 0;
    }
    .cibil-score-value {
        font-family: 'Outfit', sans-serif;
        font-size: 28px;
        font-weight: 800;
    }
    .cibil-score-value.score-excellent { color: #16a34a; }
    .cibil-score-value.score-good { color: #2563eb; }
    .cibil-score-value.score-fair { color: #ca8a04; }
    .cibil-score-value.score-poor { color: #dc2626; }
    .cibil-score-value.score-none { color: #9ca3af; }
    .cibil-score-label {
        font-size: 9.5px;
        font-weight: 750;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-top: 2px;
        letter-spacing: 0.5px;
    }

    /* Collapsible Section Styles */
    .collapsible-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 14px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .collapsible-header {
        background: #f8fafc;
        padding: 18px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        user-select: none;
        border-bottom: 1px solid var(--border-color);
        transition: background-color 0.2s;
    }
    .collapsible-header:hover {
        background: #f1f5f9;
    }
    .collapsible-header-title {
        font-family: 'Outfit', sans-serif;
        font-size: 13.5px;
        font-weight: 800;
        color: var(--text-main);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .collapsible-icon {
        font-size: 15px;
        color: var(--text-muted);
        transition: transform 0.3s;
    }
    .collapsible-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .collapsible-content.expanded {
        max-height: 2000px;
    }
    .collapsible-inner {
        padding: 24px;
    }
    pre.raw-payload {
        background: #0f172a;
        color: #e2e8f0;
        padding: 15px;
        border-radius: 8px;
        font-family: monospace;
        font-size: 12px;
        overflow-x: auto;
        max-height: 280px;
        margin-top: 8px;
    }
    
    /* Premium checklist layout */
    .checklist-table {
        width: 100%;
        border-collapse: collapse;
    }
    .checklist-table th, .checklist-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        text-align: left;
    }
    .checklist-table th {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-muted);
        background: #f8fafc;
    }
    .checklist-status-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        text-transform: uppercase;
        display: inline-block;
    }
    .checklist-status-pending { background: #fef3c7; color: #d97706; }
    .checklist-status-verified { background: #dcfce7; color: #15803d; }
    .checklist-status-rejected { background: #fee2e2; color: #b91c1c; }
    
    .checklist-action-btn {
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 700;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.2s;
    }
    .checklist-action-btn:hover {
        background: #f8fafc;
    }
    .checklist-btn-verify { border-color: #22c55e; color: #22c55e; }
    .checklist-btn-verify:hover { background: #dcfce7; }
    .checklist-btn-reject { border-color: #ef4444; color: #ef4444; }
    .checklist-btn-reject:hover { background: #fee2e2; }

    /* Case Notes / Timeline styles */
    .timeline-container {
        position: relative;
        padding-left: 20px;
        margin-top: 15px;
    }
    .timeline-container::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 6px;
        width: 2px;
        background: #e2e8f0;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
        padding-left: 15px;
    }
    .timeline-item-dot {
        position: absolute;
        left: -19px;
        top: 4px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--primary);
        border: 2px solid #ffffff;
        box-shadow: 0 0 0 3px rgba(24, 82, 156, 0.15);
    }
    .timeline-item-dot.dot-comment { background: #3b82f6; }
    .timeline-item-dot.dot-document { background: #ef4444; }
    .timeline-item-dot.dot-underwriting { background: #10b981; }
    .timeline-item-dot.dot-followup { background: #f59e0b; }
    
    .timeline-item-header {
        display: flex;
        justify-content: space-between;
        font-size: 11.5px;
        font-weight: 750;
        color: var(--text-muted);
        margin-bottom: 4px;
    }
    .timeline-item-actor {
        color: var(--text-main);
    }
    .timeline-item-desc {
        font-size: 13px;
        color: var(--text-main);
        line-height: 1.45;
        background: #f8fafc;
        padding: 10px 14px;
        border-radius: 8px;
        border: 1px solid #f1f5f9;
        margin-top: 4px;
    }

    /* CAM credit memo styles */
    .cam-grid-fields {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
    }
    @media (max-width: 992px) {
        .cam-grid-fields {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 576px) {
        .cam-grid-fields {
            grid-template-columns: 1fr;
        }
    }
    .cam-section-divider {
        grid-column: 1 / -1;
        border-top: 1px dashed #e2e8f0;
        margin: 10px 0;
        padding-top: 10px;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--primary);
        letter-spacing: 0.5px;
    }
    .cam-edit-form {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease-out;
        background: #f8fafc;
        border-radius: 10px;
        border: 1px dashed #cbd5e1;
    }
    .cam-edit-form.expanded {
        max-height: 1500px;
        padding: 20px;
        margin-top: 15px;
    }
    .form-group-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
    }
    @media (max-width: 768px) {
        .form-group-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 480px) {
        .form-group-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="view-container">
    <div class="action-header">
        <a href="applications.php?<?php echo $backParams; ?>" class="btn-outline">
            <i class="fa-solid fa-arrow-left"></i> Back to Applications List
        </a>
        <a href="edit-application.php?id=<?php echo $id; ?>&type=<?php echo $type; ?>&<?php echo $backParams; ?>" class="btn-outline" style="border-color: rgba(24, 82, 156, 0.3); color: var(--primary);">
            <i class="fa-solid fa-pen-to-square"></i> Edit Details
        </a>
    </div>

    <div class="view-split-layout">
        <!-- Left Column: Sticky Applicant Details Panel -->
        <div class="left-summary-panel">
            <div class="summary-profile-card">
                <div class="profile-avatar-wrapper">
                    <?php
                    $hasSelfie = false;
                    $selfieUrl = '';
                    if ($type === 'payday' && !empty($app['selfie_image'])) {
                        $selfiePath = $app['selfie_image'];
                        $selfieUrl = waqt_document_url((string) $selfiePath, '../');
                        $hasSelfie = true;
                    }
                    
                    if ($hasSelfie):
                    ?>
                        <img src="<?php echo htmlspecialchars($selfieUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Profile Photo" class="profile-avatar-img" onclick="viewImageLightbox('<?php echo htmlspecialchars($selfieUrl, ENT_QUOTES, 'UTF-8'); ?>', 'Profile Image')">
                    <?php else:
                        $initials = '';
                        if (!empty($app['full_name'])) {
                            $parts = explode(' ', trim($app['full_name']));
                            $initials .= strtoupper(substr($parts[0], 0, 1));
                            if (count($parts) > 1) {
                                $initials .= strtoupper(substr($parts[count($parts) - 1], 0, 1));
                            }
                        }
                        if ($initials === '') $initials = 'U';
                    ?>
                        <span style="font-family: 'Outfit', sans-serif; font-size: 32px; font-weight: 800; color: var(--primary);"><?php echo $initials; ?></span>
                    <?php endif; ?>
                </div>

                <h2><?php echo htmlspecialchars($app['full_name'] ?? 'Applicant Profile', ENT_QUOTES, 'UTF-8'); ?></h2>
                <div class="profile-app-id"><?php echo htmlspecialchars($displayId, ENT_QUOTES, 'UTF-8'); ?></div>
                
                <div class="profile-badges-row">
                    <span class="status-badge badge-status badge-<?php echo $app['status'] ?? 'draft'; ?>" id="headerStatusBadge">
                        <?php echo htmlspecialchars($app['status'] ?? 'submitted', ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <?php if ($type === 'payday'): ?>
                        <?php 
                        $priority = strtolower(trim((string)($app['priority'] ?? 'medium')));
                        ?>
                        <span class="priority-badge priority-<?php echo $priority; ?>" id="headerPriorityBadge">
                            <?php echo $priority; ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="profile-meta-list">
                    <div class="meta-list-item"><i class="fa-solid fa-tags"></i> <span><?php echo htmlspecialchars($loanTypeLabel, ENT_QUOTES, 'UTF-8'); ?></span></div>
                    <div class="meta-list-item"><i class="fa-solid fa-calendar"></i> <span>Applied: <?php echo $createdAt; ?></span></div>
                    <?php if ($type === 'payday'): ?>
                        <div class="meta-list-item"><i class="fa-solid fa-user-tie"></i> <span>Agent: <span id="headerAgentName"><?php echo htmlspecialchars((string)($app['assigned_to'] ?? 'Unassigned'), ENT_QUOTES, 'UTF-8'); ?></span></span></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sticky status updater (Payday only) -->
            <?php if ($type === 'payday'): ?>
                <div class="summary-profile-card">
                    <h3 style="font-size:12px; font-weight:800; color:var(--primary); text-transform:uppercase; margin-bottom:12px; border-bottom:1px solid rgba(0,0,0,0.05); padding-bottom:6px; text-align:left;"><i class="fa-solid fa-clipboard-check"></i> Process Stage</h3>
                    <form id="statusUpdateForm" style="display: flex; flex-direction: column; gap: 10px;">
                        <input type="hidden" name="action" value="update_app_status">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        
                        <select name="status" id="statusSelect" class="form-select-simple">
                            <option value="draft" <?php echo $app['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="submitted" <?php echo $app['status'] === 'submitted' ? 'selected' : ''; ?>>Submitted</option>
                            <option value="review" <?php echo $app['status'] === 'review' ? 'selected' : ''; ?>>Review</option>
                            <option value="approved" <?php echo $app['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $app['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            <option value="disbursed" <?php echo $app['status'] === 'disbursed' ? 'selected' : ''; ?>>Disbursed</option>
                        </select>
                        
                        <button type="submit" class="btn-primary-adm" style="height: 38px; padding: 0 16px; font-size:12.5px; box-shadow: none; width: 100%;">Update Status</button>
                    </form>
                </div>

                <!-- Case Assignment Quick Actions Widget -->
                <div class="summary-profile-card">
                    <h3 style="font-size:12px; font-weight:800; color:var(--primary); text-transform:uppercase; margin-bottom:12px; border-bottom:1px solid rgba(0,0,0,0.05); padding-bottom:6px; text-align:left;"><i class="fa-solid fa-user-tie"></i> Quick Case Assignment</h3>
                    <form id="caseAssignmentForm" style="display: flex; flex-direction: column; gap: 10px;">
                        <input type="hidden" name="action" value="update_case_assignment">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        
                        <div class="form-group" style="text-align:left;">
                            <label class="field-label" style="font-size:9.5px; margin-bottom:2px;">Assigned Agent</label>
                            <input type="text" name="assigned_to" id="agentInput" class="form-input-simple" style="height:36px; width:100%; border:1px solid var(--border-color); padding:0 10px; border-radius:6px; font-size:13px; font-weight:600;" placeholder="Agent name / email" value="<?php echo htmlspecialchars((string)($app['assigned_to'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        
                        <div class="form-group" style="text-align:left;">
                            <label class="field-label" style="font-size:9.5px; margin-bottom:2px;">Case Priority</label>
                            <select name="priority" id="prioritySelect" class="form-select-simple" style="height:36px; margin:0;">
                                <option value="low" <?php echo ($app['priority'] ?? '') === 'low' ? 'selected' : ''; ?>>Low</option>
                                <option value="medium" <?php echo ($app['priority'] ?? '') === 'medium' || empty($app['priority']) ? 'selected' : ''; ?>>Medium</option>
                                <option value="high" <?php echo ($app['priority'] ?? '') === 'high' ? 'selected' : ''; ?>>High</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn-primary-adm" style="height: 38px; padding: 0 16px; font-size:12.5px; box-shadow: none; width: 100%; background: #0c1a30;">Update Case</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Detail Cards Grid -->
        <div class="right-details-panel">
            
            <!-- Stage Timeline Indicator (Payday only) -->
            <?php if ($type === 'payday'): ?>
                <?php
                $currentStatus = strtolower(trim((string)($app['status'] ?? 'draft')));
                $steps = ['draft', 'submitted', 'review', 'approved', 'disbursed'];
                $statusIndex = array_search($currentStatus, $steps);
                
                $isRejected = ($currentStatus === 'rejected');
                if ($statusIndex === false) {
                    $statusIndex = $isRejected ? 3 : 0;
                }
                
                // Progress bar width percentage
                $progressPct = 0;
                if ($statusIndex > 0) {
                    $progressPct = (int)(($statusIndex / (count($steps) - 1)) * 90);
                }
                if ($currentStatus === 'disbursed') {
                    $progressPct = 90;
                }
                ?>
                <div class="info-card" style="border-left: 4px solid var(--primary);">
                    <h3 class="card-title" style="border: none; margin-bottom: 5px; padding: 0;"><i class="fa-solid fa-route"></i> Application Lifecycle stage</h3>
                    <div class="status-timeline">
                        <div class="timeline-progress-bar" style="width: <?php echo $progressPct; ?>%;"></div>
                        
                        <div class="timeline-step <?php echo $statusIndex >= 0 ? 'completed' : ''; ?> <?php echo $currentStatus === 'draft' ? 'active' : ''; ?>">
                            <div class="step-circle"><?php echo $currentStatus === 'draft' ? '<i class="fa-solid fa-spinner fa-spin"></i>' : '1'; ?></div>
                            <div class="step-label">Draft</div>
                        </div>
                        
                        <div class="timeline-step <?php echo $statusIndex >= 1 ? 'completed' : ''; ?> <?php echo $currentStatus === 'submitted' ? 'active' : ''; ?>">
                            <div class="step-circle"><?php echo $currentStatus === 'submitted' ? '<i class="fa-solid fa-spinner fa-spin"></i>' : '2'; ?></div>
                            <div class="step-label">Submitted</div>
                        </div>
                        
                        <div class="timeline-step <?php echo $statusIndex >= 2 ? 'completed' : ''; ?> <?php echo $currentStatus === 'review' ? 'active' : ''; ?>">
                            <div class="step-circle"><?php echo $currentStatus === 'review' ? '<i class="fa-solid fa-spinner fa-spin"></i>' : '3'; ?></div>
                            <div class="step-label">Review</div>
                        </div>
                        
                        <?php if ($isRejected): ?>
                            <div class="timeline-step rejected">
                                <div class="step-circle">&times;</div>
                                <div class="step-label">Rejected</div>
                            </div>
                        <?php else: ?>
                            <div class="timeline-step <?php echo $statusIndex >= 3 ? 'completed' : ''; ?> <?php echo $currentStatus === 'approved' ? 'active' : ''; ?>">
                                <div class="step-circle"><?php echo $currentStatus === 'approved' ? '<i class="fa-solid fa-spinner fa-spin"></i>' : '4'; ?></div>
                                <div class="step-label">Approved</div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="timeline-step <?php echo $statusIndex >= 4 ? 'completed' : ''; ?> <?php echo $currentStatus === 'disbursed' ? 'active' : ''; ?>">
                            <div class="step-circle">5</div>
                            <div class="step-label">Disbursed</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- CIBIL Credit score matching widget -->
            <?php if ($cibilReport): 
                $score = $cibilReport['score'] !== null ? (int)$cibilReport['score'] : null;
                $scoreClass = 'score-none';
                $ratingLabel = 'No Score';
                if ($score !== null) {
                    if ($score >= 750) { $scoreClass = 'score-excellent'; $ratingLabel = 'Excellent'; }
                    elseif ($score >= 700) { $scoreClass = 'score-good'; $ratingLabel = 'Good'; }
                    elseif ($score >= 600) { $scoreClass = 'score-fair'; $ratingLabel = 'Fair'; }
                    else { $scoreClass = 'score-poor'; $ratingLabel = 'Poor'; }
                }
            ?>
                <div class="info-card" style="border-left: 4px solid #16a34a;">
                    <h3 class="card-title" style="margin-bottom:15px;"><i class="fa-solid fa-gauge-high"></i> Pulled Credit Score Details</h3>
                    <div style="display:flex; gap:25px; align-items:center; flex-wrap:wrap;">
                        <div class="cibil-gauge-wrapper" style="border-color: <?php 
                            if ($scoreClass === 'score-excellent') echo 'rgba(22, 163, 74, 0.2)';
                            elseif ($scoreClass === 'score-good') echo 'rgba(37, 99, 235, 0.2)';
                            elseif ($scoreClass === 'score-fair') echo 'rgba(202, 138, 4, 0.2)';
                            else echo 'rgba(220, 38, 38, 0.2)';
                        ?>;">
                            <div class="cibil-score-value <?php echo $scoreClass; ?>"><?php echo $score ?? 'N/A'; ?></div>
                            <div class="cibil-score-label"><?php echo $ratingLabel; ?></div>
                        </div>
                        <div style="flex:1; min-width:250px;">
                            <div class="grid-fields">
                                <div class="field-item">
                                    <div class="field-label">Bureau Status</div>
                                    <div class="field-value" style="text-transform:capitalize;"><?php echo htmlspecialchars($cibilReport['analysis_status'] ?? 'Verified', ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                                <div class="field-item">
                                    <div class="field-label">Bureau Ref ID</div>
                                    <div class="field-value" style="font-family:monospace;"><?php echo htmlspecialchars($cibilReport['ref_id'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                                <div class="field-item">
                                    <div class="field-label">Pulled Date</div>
                                    <div class="field-value"><?php echo date('d M Y, h:i A', strtotime($cibilReport['created_at'])); ?></div>
                                </div>
                                <div class="field-item" style="grid-column: span 2; margin-top:8px;">
                                    <?php if (!empty($cibilReport['pdf_url'])): ?>
                                        <a href="../<?php echo htmlspecialchars($cibilReport['pdf_url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="doc-preview-link" style="background:#16a34a; color:#ffffff; border-color:#16a34a;"><i class="fa-solid fa-file-pdf"></i> Download Bureau PDF Report</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="details-cards-grid">
                <!-- Panel 1: Loan Requirements -->
                <div class="info-card">
                    <h3 class="card-title"><i class="fa-solid fa-indian-rupee-sign"></i> Loan Requirements</h3>
                    <div class="grid-fields">
                        <div class="field-item">
                            <div class="field-label"><i class="fa-solid fa-wallet"></i> Requested Loan Amount</div>
                            <div class="field-value" style="color: var(--primary); font-size: 15px; font-weight: 750;">₹<?php echo $displayAmount; ?></div>
                        </div>
                        <div class="field-item">
                            <div class="field-label"><i class="fa-solid fa-circle-info"></i> Loan Purpose</div>
                            <div class="field-value"><?php echo htmlspecialchars($app['loan_purpose'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <?php if ($type === 'payday'): ?>
                            <div class="field-item">
                                <div class="field-label"><i class="fa-solid fa-arrows-spin"></i> Existing Loan Running</div>
                                <div class="field-value"><?php echo ($app['has_running_loan'] ?? 0) == 1 ? 'Yes' : 'No'; ?></div>
                            </div>
                            <div class="field-item">
                                <div class="field-label"><i class="fa-solid fa-spinner"></i> Current Step / Stage</div>
                                <div class="field-value" style="text-transform: capitalize; color: var(--accent); font-weight: 700;"><?php echo htmlspecialchars($app['current_step'] ?? 'Completed', ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        <?php else: ?>
                            <div class="field-item">
                                <div class="field-label"><i class="fa-solid fa-calendar-days"></i> Requested Tenure</div>
                                <div class="field-value"><?php echo htmlspecialchars((string)($app['loan_tenure'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Panel 2: Customer Profile Details -->
                <div class="info-card">
                    <h3 class="card-title"><i class="fa-solid fa-user"></i> Customer Profile Details</h3>
                    <div class="grid-fields">
                        <div class="field-item">
                            <div class="field-label"><i class="fa-solid fa-signature"></i> Full Name</div>
                            <div class="field-value"><?php echo htmlspecialchars((string)($app['full_name'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="field-item">
                            <div class="field-label"><i class="fa-solid fa-mobile-screen"></i> Mobile Number</div>
                            <div class="field-value"><?php echo htmlspecialchars((string)($app['mobile'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="field-item">
                            <div class="field-label"><i class="fa-solid fa-envelope"></i> Email Address</div>
                            <div class="field-value"><?php echo htmlspecialchars((string)($app['email'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="field-item">
                            <div class="field-label"><i class="fa-solid fa-id-card"></i> PAN Number</div>
                            <div class="field-value" style="font-family: monospace; font-size: 14px; font-weight: 700; text-transform: uppercase;"><?php echo htmlspecialchars($maskedPanNumber, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="field-item">
                            <div class="field-label"><i class="fa-solid fa-cake-candles"></i> Date of Birth</div>
                            <div class="field-value"><?php echo !empty($app['dob']) ? date('d M Y', strtotime((string)$app['dob'])) : 'N/A'; ?></div>
                        </div>
                        <?php if ($type === 'payday'): ?>
                            <div class="field-item">
                                <div class="field-label"><i class="fa-solid fa-graduation-cap"></i> Education</div>
                                <div class="field-value"><?php echo htmlspecialchars((string)($app['education'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="field-item">
                                <div class="field-label"><i class="fa-solid fa-calendar-check"></i> Experience</div>
                                <div class="field-value"><?php echo !empty($app['experience_years']) ? htmlspecialchars((string)$app['experience_years']) . ' Years' : 'N/A'; ?></div>
                            </div>
                            <div class="field-item">
                                <div class="field-label"><i class="fa-solid fa-fingerprint"></i> Aadhaar</div>
                                <div class="field-value" style="font-family: monospace;"><?php echo htmlspecialchars($maskedAadhaarNumber, ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="field-item">
                                <div class="field-label"><i class="fa-solid fa-eye-slash"></i> Aadhaar (Masked)</div>
                                <div class="field-value" style="font-family: monospace;"><?php echo htmlspecialchars($maskedAadhaarStored, ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="field-item">
                                <div class="field-label"><i class="fa-solid fa-shield-halved"></i> Aadhaar Unique ID</div>
                                <div class="field-value" style="font-size:11.5px; font-family:monospace; color:var(--text-muted); word-break:break-all;"><?php echo htmlspecialchars($maskedAadhaarUniqueId, ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="field-item">
                                <div class="field-label"><i class="fa-solid fa-square-check"></i> Aadhaar Verified</div>
                                <div class="field-value"><?php echo ($app['aadhaar_verified'] ?? 0) == 1 ? 'Yes ✅' : 'No ❌'; ?></div>
                            </div>
                            <div class="field-item" style="grid-column: span 2;">
                                <div class="field-label"><i class="fa-solid fa-chart-line"></i> Credit Report Link</div>
                                <div class="field-value">
                                    <?php if (!empty($app['cibil_report_url'])): ?>
                                        <a href="<?php echo htmlspecialchars((string)$app['cibil_report_url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="doc-preview-link" style="color: var(--primary); font-weight: 700; text-decoration: underline;"><i class="fa-solid fa-square-poll-vertical"></i> View External Credit Report</a>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-style: italic; font-size:12.5px;">No Credit Report Linked</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="field-item">
                                <div class="field-label"><i class="fa-solid fa-venus-mars"></i> Gender</div>
                                <div class="field-value" style="text-transform: capitalize;"><?php echo htmlspecialchars((string)($app['gender'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Panel 3: Employment Information -->
                <div class="info-card">
                    <h3 class="card-title"><i class="fa-solid fa-briefcase"></i> Employment Information</h3>
                    <div class="grid-fields">
                        <div class="field-item">
                            <div class="field-label"><i class="fa-solid fa-user-check"></i> Employment Type</div>
                            <div class="field-value" style="text-transform: capitalize;"><?php echo htmlspecialchars($type === 'payday' ? ((string)($app['employment_status'] ?? 'N/A')) : ((string)($app['employment_type'] ?? 'N/A')), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="field-item">
                            <div class="field-label"><i class="fa-solid fa-building"></i> Company Name</div>
                            <div class="field-value"><?php echo htmlspecialchars((string)($app['company_name'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="field-item">
                            <div class="field-label"><i class="fa-solid fa-user-ninja"></i> Designation</div>
                            <div class="field-value"><?php echo htmlspecialchars((string)($app['designation'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="field-item">
                            <div class="field-label"><i class="fa-solid fa-money-check-dollar"></i> Monthly Net Income</div>
                            <div class="field-value" style="color: var(--success); font-weight: 750;">₹<?php echo is_numeric($app['monthly_income']) ? number_format((float)$app['monthly_income'], 2) : htmlspecialchars((string)$app['monthly_income'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <?php if ($type === 'payday'): ?>
                            <div class="field-item">
                                <div class="field-label"><i class="fa-solid fa-building-columns"></i> Income Received Mode</div>
                                <div class="field-value" style="text-transform: capitalize;"><?php echo htmlspecialchars((string)($app['income_received_in'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="field-item">
                                <div class="field-label"><i class="fa-solid fa-hashtag"></i> UAN Number</div>
                                <div class="field-value" style="font-family:monospace;"><?php echo htmlspecialchars((string)($app['uan_number'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="field-item">
                                <div class="field-label"><i class="fa-solid fa-envelope-open-text"></i> Office Email</div>
                                <div class="field-value"><?php echo htmlspecialchars((string)($app['office_email'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="field-item">
                                <div class="field-label"><i class="fa-solid fa-map-pin"></i> Office Pincode</div>
                                <div class="field-value"><?php echo htmlspecialchars((string)($app['office_pincode'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="field-item">
                                <div class="field-label"><i class="fa-solid fa-calendar-day"></i> Salary Day of Month</div>
                                <div class="field-value"><?php echo !empty($app['salary_day']) ? htmlspecialchars((string)$app['salary_day']) . 'th' : 'N/A'; ?></div>
                            </div>
                            <div class="field-item" style="grid-column: span 2;">
                                <div class="field-label"><i class="fa-solid fa-map-location"></i> Office Address</div>
                                <div class="field-value"><?php echo htmlspecialchars((string)($app['office_address'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        <?php else: ?>
                            <div class="field-item">
                                <div class="field-label"><i class="fa-solid fa-percent"></i> Existing EMIs</div>
                                <div class="field-value">₹<?php echo is_numeric($app['existing_emi']) ? number_format((float)$app['existing_emi'], 2) : htmlspecialchars((string)$app['existing_emi'] ?? '0', ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Panel 4: Residential Address & Bank Account Details -->
                <div class="info-card">
                    <h3 class="card-title"><i class="fa-solid fa-house-user"></i> Residential & Bank Details</h3>
                    <div class="grid-fields">
                        <?php if ($type === 'payday'): ?>
                            <div class="field-item">
                                <div class="field-label"><i class="fa-solid fa-city"></i> City</div>
                                <div class="field-value"><?php echo htmlspecialchars((string)($app['city'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="field-item">
                                <div class="field-label"><i class="fa-solid fa-map-pin"></i> Pincode</div>
                                <div class="field-value"><?php echo htmlspecialchars((string)($app['pincode'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        <?php else: ?>
                            <div class="field-item" style="grid-column: span 2;">
                                <div class="field-label"><i class="fa-solid fa-location-dot"></i> Address Details</div>
                                <div class="field-value"><?php echo htmlspecialchars((string)($app['address'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="field-item">
                                <div class="field-label"><i class="fa-solid fa-city"></i> City & State</div>
                                <div class="field-value"><?php echo htmlspecialchars(((string)($app['city'] ?? 'N/A')) . ', ' . ((string)($app['state'] ?? 'N/A')), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="field-item">
                                <div class="field-label"><i class="fa-solid fa-map-pin"></i> Pincode</div>
                                <div class="field-value"><?php echo htmlspecialchars((string)($app['pincode'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <div style="grid-column: span 2; border-top: 1px dashed var(--border-color); margin: 6px 0;"></div>

                        <div class="field-item">
                            <div class="field-label"><i class="fa-solid fa-bank"></i> Bank Name</div>
                            <div class="field-value"><?php echo htmlspecialchars((string)($app['bank_name'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="field-item">
                            <div class="field-label"><i class="fa-solid fa-code-branch"></i> Branch Name</div>
                            <div class="field-value"><?php echo htmlspecialchars((string)($app['branch_name'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="field-item">
                            <div class="field-label"><i class="fa-solid fa-user-tag"></i> Account Holder</div>
                            <div class="field-value"><?php echo htmlspecialchars((string)($app['account_holder'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="field-item">
                            <div class="field-label"><i class="fa-solid fa-hashtag"></i> Account Number</div>
                            <div class="field-value" style="font-family: monospace;"><?php echo htmlspecialchars((string)($app['account_number'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="field-item" style="grid-column: span 2;">
                            <div class="field-label"><i class="fa-solid fa-building-columns"></i> IFSC Code</div>
                            <div class="field-value" style="font-family: monospace; font-size: 14.5px; font-weight: 700; color: var(--primary); text-transform: uppercase;"><?php echo htmlspecialchars((string)($app['ifsc_code'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($type === 'payday'): ?>
                <!-- Panel 5: References / Contacts (Payday only) -->
                <div class="info-card">
                    <h3 class="card-title"><i class="fa-solid fa-users"></i> References / Contacts</h3>
                    <div class="grid-fields" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                        <div class="field-item">
                            <div class="field-label"><i class="fa-solid fa-user-group"></i> Primary Reference</div>
                            <div class="field-value">
                                <span style="font-weight: 700; color: var(--text-main);"><?php echo htmlspecialchars((string)($app['reference1_name'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></span> 
                                (<?php echo htmlspecialchars((string)($app['reference1_relation'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?>) - 
                                <a href="tel:<?php echo htmlspecialchars((string)($app['reference1_mobile'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" style="color: var(--primary); text-decoration: none; font-weight: 600;"><?php echo htmlspecialchars((string)($app['reference1_mobile'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></a>
                            </div>
                        </div>
                        <div class="field-item">
                            <div class="field-label"><i class="fa-solid fa-user-group"></i> Secondary Reference</div>
                            <div class="field-value">
                                <span style="font-weight: 700; color: var(--text-main);"><?php echo htmlspecialchars((string)($app['reference2_name'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></span> 
                                (<?php echo htmlspecialchars((string)($app['reference2_relation'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?>) - 
                                <a href="tel:<?php echo htmlspecialchars((string)($app['reference2_mobile'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" style="color: var(--primary); text-decoration: none; font-weight: 600;"><?php echo htmlspecialchars((string)($app['reference2_mobile'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel 6: Underwriter's Document Verification Checklist -->
                <div class="info-card" style="border-left-color: var(--warning);">
                    <h3 class="card-title"><i class="fa-solid fa-folder-open"></i> Underwriter's Document Verification Checklist</h3>
                    <div class="table-responsive" style="margin-top:10px;">
                        <table class="checklist-table">
                            <thead>
                                <tr>
                                    <th>Document Description</th>
                                    <th>Link / Preview</th>
                                    <th>Verification Status</th>
                                    <th>Remarks / Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $verificationItems = [
                                    'selfie_image' => ['label' => 'Selfie Photo / Facial KYC', 'path' => $app['selfie_image'] ?? ''],
                                    'company_id_card' => ['label' => 'Company / Employee ID Card', 'path' => $app['company_id_card'] ?? ''],
                                    'salary_slip_current' => ['label' => 'Salary Slip (Current Month)', 'path' => $app['salary_slip_current'] ?? ''],
                                    'salary_slip_previous' => ['label' => 'Salary Slip (Previous Month)', 'path' => $app['salary_slip_previous'] ?? ''],
                                    'salary_slip_old' => ['label' => 'Salary Slip (Old Month)', 'path' => $app['salary_slip_old'] ?? ''],
                                    'video_kyc' => ['label' => 'Video KYC Recording', 'path' => $app['video_kyc'] ?? ''],
                                ];

                                foreach ($verificationItems as $key => $item):
                                    $checkRecord = $docChecks[$key] ?? null;
                                    $currentStatus = $checkRecord ? $checkRecord['status'] : 'pending';
                                    $currentRemark = $checkRecord ? $checkRecord['remark'] : '';
                                    $verifiedBy = $checkRecord ? $checkRecord['verified_by'] : '';
                                    $verifiedAt = $checkRecord && $checkRecord['verified_at'] ? date('d M Y, h:i A', strtotime($checkRecord['verified_at'])) : '';
                                ?>
                                    <tr id="doc-row-<?php echo $key; ?>">
                                        <td style="font-weight: 700; font-size:13px; color: var(--text-main);">
                                            <?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
                                            <?php if ($verifiedBy !== ''): ?>
                                                <div style="font-size:10px; font-weight:600; color:var(--text-muted); margin-top:2px;">
                                                    Verified by: <?php echo htmlspecialchars($verifiedBy, ENT_QUOTES, 'UTF-8'); ?> @ <?php echo $verifiedAt; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            if (empty($item['path'])) {
                                                echo '<span style="color:var(--text-muted); font-style:italic;">Not Uploaded</span>';
                                            } else {
                                                if ($key === 'video_kyc') {
                                                    $vUrl = waqt_document_url((string) $item['path'], '../');
                                                    echo '<a href="' . htmlspecialchars($vUrl, ENT_QUOTES, 'UTF-8') . '" target="_blank" class="doc-preview-link"><i class="fa-solid fa-video"></i> Download Video</a>';
                                                } else {
                                                    echo $getDocLink($item['path'], $item['label']);
                                                }
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <span class="checklist-status-badge checklist-status-<?php echo $currentStatus; ?>" id="badge-doc-<?php echo $key; ?>">
                                                <?php echo htmlspecialchars($currentStatus, ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($item['path'])): ?>
                                                <div style="display:flex; flex-direction:column; gap:6px; max-width:240px;">
                                                    <input type="text" id="remark-doc-<?php echo $key; ?>" class="form-input-simple" style="height:28px; font-size:11px; padding:0 8px; border:1px solid var(--border-color); border-radius:4px;" placeholder="Add remarks..." value="<?php echo htmlspecialchars($currentRemark, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <div style="display:flex; gap:6px;">
                                                        <button type="button" class="checklist-action-btn checklist-btn-verify" onclick="updateDocStatus('<?php echo $key; ?>', '<?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>', 'verified')">
                                                            <i class="fa-solid fa-circle-check"></i> Approve
                                                        </button>
                                                        <button type="button" class="checklist-action-btn checklist-btn-reject" onclick="updateDocStatus('<?php echo $key; ?>', '<?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>', 'rejected')">
                                                            <i class="fa-solid fa-circle-xmark"></i> Reject
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span style="color:var(--text-muted); font-size:11.5px; font-style:italic;">No Actions Available</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Credit Assessment Memo Underwriting Card -->
                <div class="info-card" style="border-left-color: var(--success);">
                    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(0,0,0,0.05); padding-bottom:10px; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
                        <h3 class="card-title" style="border:none; margin:0; padding:0;"><i class="fa-solid fa-file-invoice"></i> Credit Assessment Memo (CAM) Sheet</h3>
                        <button type="button" class="btn-primary-adm" style="background:#0c1a30; font-size:11.5px; padding:6px 12px; height:auto; box-shadow:none;" onclick="toggleCamEditForm()">
                            <i class="fa-solid fa-pen-to-square"></i> <?php echo $camSheet ? 'Revise Underwriting Memo' : 'Create Underwriting Memo'; ?>
                        </button>
                    </div>
                    
                    <?php if ($camSheet): ?>
                        <div class="cam-grid-fields">
                            <div class="field-item">
                                <div class="field-label">CAM Version</div>
                                <div class="field-value" style="color:var(--primary); font-weight:800;">v<?php echo htmlspecialchars((string)$camSheet['version']); ?></div>
                            </div>
                            <div class="field-item">
                                <div class="field-label">Policy Status</div>
                                <div class="field-value"><span class="checklist-status-badge checklist-status-<?php echo htmlspecialchars($camSheet['status'] === 'approved' ? 'verified' : ($camSheet['status'] === 'rejected' ? 'rejected' : 'pending')); ?>"><?php echo htmlspecialchars($camSheet['status']); ?></span></div>
                            </div>
                            <div class="field-item">
                                <div class="field-label">Recommended Amount</div>
                                <div class="field-value" style="color:var(--primary); font-weight:750;">₹<?php echo number_format((float)$camSheet['recommended_amount'], 2); ?></div>
                            </div>
                            <div class="field-item">
                                <div class="field-label">Approved Amount</div>
                                <div class="field-value" style="color:var(--success); font-weight:800; font-size:14.5px;">₹<?php echo number_format((float)$camSheet['approved_amount'], 2); ?></div>
                            </div>

                            <div class="cam-section-divider">Income & Policy Checks</div>
                            <div class="field-item">
                                <div class="field-label">Monthly Gross Income</div>
                                <div class="field-value">₹<?php echo number_format((float)$camSheet['monthly_income'], 2); ?></div>
                            </div>
                            <div class="field-item">
                                <div class="field-label">Monthly Obligations</div>
                                <div class="field-value">₹<?php echo number_format((float)$camSheet['monthly_expenses'], 2); ?></div>
                            </div>
                            <div class="field-item">
                                <div class="field-label">DTI Ratio</div>
                                <div class="field-value" style="font-weight:700; color: <?php echo (float)$camSheet['dti_ratio'] > 50 ? 'var(--danger)' : 'var(--success)'; ?>;"><?php echo number_format((float)$camSheet['dti_ratio'], 1); ?>%</div>
                            </div>
                            <div class="field-item">
                                <div class="field-label">Disposable Buffer</div>
                                <div class="field-value"><?php echo number_format((float)$camSheet['repayment_buffer'], 2); ?>x</div>
                            </div>

                            <div class="cam-section-divider">Risk Grading</div>
                            <div class="field-item">
                                <div class="field-label">Risk Grade Rating</div>
                                <div class="field-value" style="font-family:'Outfit'; font-weight:800; font-size:16px;"><?php echo htmlspecialchars($camSheet['risk_grade']); ?></div>
                            </div>
                            <div class="field-item">
                                <div class="field-label">Bureau Risk</div>
                                <div class="field-value"><?php echo htmlspecialchars($camSheet['credit_risk']); ?></div>
                            </div>
                            <div class="field-item">
                                <div class="field-label">Repayment Capacity</div>
                                <div class="field-value"><?php echo htmlspecialchars($camSheet['repayment_capacity']); ?></div>
                            </div>
                            <div class="field-item">
                                <div class="field-label">Deviation Level</div>
                                <div class="field-value"><?php echo htmlspecialchars($camSheet['deviation_level']); ?></div>
                            </div>
                            
                            <div class="cam-section-divider" style="margin-bottom:5px;">Assessment Decision Remarks</div>
                            <div class="field-item" style="grid-column: span 2;">
                                <div class="field-label">Verification Conditions</div>
                                <div class="field-value" style="font-size:12.5px; font-weight:500; color:var(--text-muted); line-height:1.4;"><?php echo !empty($camSheet['conditions']) ? htmlspecialchars($camSheet['conditions']) : 'No conditions linked.'; ?></div>
                            </div>
                            <div class="field-item" style="grid-column: span 2;">
                                <div class="field-label">Underwriter notes</div>
                                <div class="field-value" style="font-size:12.5px; font-weight:500; color:var(--text-muted); line-height:1.4;"><?php echo !empty($camSheet['notes']) ? htmlspecialchars($camSheet['notes']) : 'No notes added.'; ?></div>
                            </div>
                            <div class="field-item" style="grid-column: span 4; margin-top:5px; border-top:1px solid #f1f5f9; padding-top:10px; font-size:11px; color:var(--text-muted);">
                                Generated by: <strong><?php echo htmlspecialchars($camSheet['created_by']); ?></strong> on <?php echo date('d M Y, h:i A', strtotime($camSheet['created_at'])); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div style="text-align:center; padding:25px 0; color:var(--text-muted); font-style:italic;">
                            No Credit Assessment Memo recorded for this application. Please create one to complete underwriting check.
                        </div>
                    <?php endif; ?>

                    <!-- Underwriter CAM Form Editor -->
                    <div class="cam-edit-form" id="camEditSection">
                        <h4 style="font-size:13px; font-weight:800; color:var(--primary); margin-bottom:15px; text-transform:uppercase; letter-spacing:0.5px;"><i class="fa-solid fa-gears"></i> Underwriting Parameters Calculator</h4>
                        <form id="camDetailsSheetForm">
                            <input type="hidden" name="action" value="save_lead_cam_sheet">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($displayId, ENT_QUOTES, 'UTF-8'); ?>">
                            
                            <div class="form-group-grid">
                                <div class="form-group">
                                    <label class="field-label">Requested Loan Amt (₹)</label>
                                    <input type="number" name="requested_amount" id="camReqAmt" class="form-input-simple" style="height:36px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px; width:100%; font-size:13.5px; font-weight:600;" value="<?php echo $camSheet ? (float)$camSheet['requested_amount'] : (float)$app['loan_amount']; ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="field-label">Recommended Loan Amt (₹)</label>
                                    <input type="number" name="recommended_amount" id="camRecAmt" class="form-input-simple" style="height:36px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px; width:100%; font-size:13.5px; font-weight:600;" value="<?php echo $camSheet ? (float)$camSheet['recommended_amount'] : (float)$app['loan_amount']; ?>" oninput="calculateCamFields()">
                                </div>
                                <div class="form-group">
                                    <label class="field-label">Approved Loan Amt (₹)</label>
                                    <input type="number" name="approved_amount" id="camApprvAmt" class="form-input-simple" style="height:36px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px; width:100%; font-size:13.5px; font-weight:600;" value="<?php echo $camSheet ? (float)$camSheet['approved_amount'] : (float)$app['loan_amount']; ?>" oninput="calculateCamFields()">
                                </div>
                                <div class="form-group">
                                    <label class="field-label">Tenure (Days)</label>
                                    <input type="number" name="loan_term_days" id="camTenure" class="form-input-simple" style="height:36px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px; width:100%; font-size:13.5px; font-weight:600;" value="<?php echo $camSheet ? (int)$camSheet['loan_term_days'] : 30; ?>" oninput="calculateCamFields()">
                                </div>
                                <div class="form-group">
                                    <label class="field-label">Interest Rate (% Per Day)</label>
                                    <input type="number" step="0.01" name="interest_rate" id="camIntRate" class="form-input-simple" style="height:36px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px; width:100%; font-size:13.5px; font-weight:600;" value="<?php echo $camSheet ? (float)$camSheet['interest_rate'] : 1.0; ?>" oninput="calculateCamFields()">
                                </div>
                                <div class="form-group">
                                    <label class="field-label">Processing Fee Rate (%)</label>
                                    <input type="number" step="0.01" name="processing_fee_rate" id="camProcRate" class="form-input-simple" style="height:36px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px; width:100%; font-size:13.5px; font-weight:600;" value="<?php echo $camSheet ? (float)$camSheet['processing_fee_rate'] : 2.0; ?>" oninput="calculateCamFields()">
                                </div>
                                <div class="form-group">
                                    <label class="field-label">Total Repayment (₹)</label>
                                    <input type="number" name="total_repayment" id="camTotalRepay" class="form-input-simple" style="height:36px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px; width:100%; font-size:13.5px; font-weight:700; color:var(--primary); background:#e2e8f0;" value="<?php echo $camSheet ? (float)$camSheet['total_repayment'] : 0; ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="field-label">Monthly Gross Income (₹)</label>
                                    <input type="number" name="monthly_income" id="camMonthlyInc" class="form-input-simple" style="height:36px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px; width:100%; font-size:13.5px; font-weight:600;" value="<?php echo $camSheet ? (float)$camSheet['monthly_income'] : (float)($app['monthly_income'] ?? 0); ?>" oninput="calculateCamFields()">
                                </div>
                                <div class="form-group">
                                    <label class="field-label">Monthly Obligations (₹)</label>
                                    <input type="number" name="monthly_expenses" id="camMonthlyExp" class="form-input-simple" style="height:36px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px; width:100%; font-size:13.5px; font-weight:600;" value="<?php echo $camSheet ? (float)$camSheet['monthly_expenses'] : 0; ?>" oninput="calculateCamFields()">
                                </div>
                                <div class="form-group">
                                    <label class="field-label">Debt-to-Income (DTI) %</label>
                                    <input type="text" id="camDtiRatio" class="form-input-simple" style="height:36px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px; width:100%; font-size:13.5px; font-weight:700; background:#e2e8f0;" value="<?php echo $camSheet ? number_format((float)$camSheet['dti_ratio'], 1) . '%' : '0.0%'; ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="field-label">Disposable Buffer</label>
                                    <input type="number" step="0.01" name="repayment_buffer" id="camRepayBuffer" class="form-input-simple" style="height:36px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px; width:100%; font-size:13.5px; font-weight:700; background:#e2e8f0;" value="<?php echo $camSheet ? (float)$camSheet['repayment_buffer'] : 0; ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="field-label">Recommendation</label>
                                    <select name="recommendation" class="form-select-simple" style="height:36px; margin:0;">
                                        <option value="Approve" <?php echo ($camSheet['recommendation'] ?? '') === 'Approve' ? 'selected' : ''; ?>>Approve</option>
                                        <option value="Reject" <?php echo ($camSheet['recommendation'] ?? '') === 'Reject' ? 'selected' : ''; ?>>Reject</option>
                                        <option value="Hold" <?php echo ($camSheet['recommendation'] ?? '') === 'Hold' ? 'selected' : ''; ?>>Hold / Review</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="field-label">Credit Bureau Risk</label>
                                    <select name="credit_risk" class="form-select-simple" style="height:36px; margin:0;">
                                        <option value="Low" <?php echo ($camSheet['credit_risk'] ?? '') === 'Low' ? 'selected' : ''; ?>>Low Risk</option>
                                        <option value="Medium" <?php echo ($camSheet['credit_risk'] ?? '') === 'Medium' || empty($camSheet['credit_risk']) ? 'selected' : ''; ?>>Medium Risk</option>
                                        <option value="High" <?php echo ($camSheet['credit_risk'] ?? '') === 'High' ? 'selected' : ''; ?>>High Risk</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="field-label">Repayment Capacity</label>
                                    <select name="repayment_capacity" class="form-select-simple" style="height:36px; margin:0;">
                                        <option value="Excellent" <?php echo ($camSheet['repayment_capacity'] ?? '') === 'Excellent' ? 'selected' : ''; ?>>Excellent</option>
                                        <option value="Good" <?php echo ($camSheet['repayment_capacity'] ?? '') === 'Good' || empty($camSheet['repayment_capacity']) ? 'selected' : ''; ?>>Good</option>
                                        <option value="Average" <?php echo ($camSheet['repayment_capacity'] ?? '') === 'Average' ? 'selected' : ''; ?>>Average</option>
                                        <option value="Poor" <?php echo ($camSheet['repayment_capacity'] ?? '') === 'Poor' ? 'selected' : ''; ?>>Poor</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="field-label">Risk Grade Rating</label>
                                    <select name="risk_grade" class="form-select-simple" style="height:36px; margin:0;">
                                        <option value="A" <?php echo ($camSheet['risk_grade'] ?? '') === 'A' ? 'selected' : ''; ?>>A (Very Low)</option>
                                        <option value="B" <?php echo ($camSheet['risk_grade'] ?? '') === 'B' ? 'selected' : ''; ?>>B (Low)</option>
                                        <option value="C" <?php echo ($camSheet['risk_grade'] ?? '') === 'C' || empty($camSheet['risk_grade']) ? 'selected' : ''; ?>>C (Medium)</option>
                                        <option value="D" <?php echo ($camSheet['risk_grade'] ?? '') === 'D' ? 'selected' : ''; ?>>D (High)</option>
                                        <option value="E" <?php echo ($camSheet['risk_grade'] ?? '') === 'E' ? 'selected' : ''; ?>>E (Decline)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="field-label">Deviation Level</label>
                                    <select name="deviation_level" class="form-select-simple" style="height:36px; margin:0;">
                                        <option value="None" <?php echo ($camSheet['deviation_level'] ?? '') === 'None' ? 'selected' : ''; ?>>None</option>
                                        <option value="Minor" <?php echo ($camSheet['deviation_level'] ?? '') === 'Minor' ? 'selected' : ''; ?>>Minor</option>
                                        <option value="Major" <?php echo ($camSheet['deviation_level'] ?? '') === 'Major' ? 'selected' : ''; ?>>Major Deviation</option>
                                    </select>
                                </div>
                                <div class="form-group" style="grid-column: span 2;">
                                    <label class="field-label">Verification Conditions</label>
                                    <input type="text" name="conditions" class="form-input-simple" style="height:36px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px; width:100%; font-size:13px;" placeholder="e.g. Bank details & salary slip must match" value="<?php echo htmlspecialchars($camSheet['conditions'] ?? 'Bank details, KYC, CIBIL and income documents must be verified before disbursement.'); ?>">
                                </div>
                                <div class="form-group" style="grid-column: span 3;">
                                    <label class="field-label">Credit Notes & Underwriting Justification</label>
                                    <textarea name="notes" class="form-input-simple" style="height:70px; border:1px solid var(--border-color); border-radius:6px; padding:8px 10px; width:100%; font-size:13px;" placeholder="Add justification notes for this underwriting action..."><?php echo htmlspecialchars($camSheet['notes'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            
                            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:15px; border-top:1px solid var(--border-color); padding-top:12px;">
                                <button type="button" class="btn-outline" style="padding:6px 12px; font-size:12px;" onclick="toggleCamEditForm()">Cancel</button>
                                <button type="submit" class="btn-primary-adm" style="padding:6px 16px; font-size:12px; background:var(--success);">Save Underwriting Memo</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- CRM Case Timeline & Activity Logs Card -->
                <div class="info-card" style="border-left-color: var(--info);">
                    <h3 class="card-title"><i class="fa-solid fa-clock-rotate-left"></i> CRM Interaction Logs & Timeline</h3>
                    
                    <!-- Form to add comment -->
                    <form id="addCaseCommentForm" style="display:flex; gap:10px; margin-bottom:20px; align-items:flex-start;">
                        <input type="hidden" name="action" value="add_lead_activity">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($displayId, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="type" value="comment">
                        
                        <textarea name="description" class="form-input-simple" style="flex:1; height:45px; border:1px solid var(--border-color); border-radius:8px; padding:10px; font-size:13px; resize:none;" placeholder="Type case comment / callback note here..." required></textarea>
                        <button type="submit" class="btn-primary-adm" style="height:45px; padding:0 20px; font-size:13px; box-shadow:none;"><i class="fa-solid fa-paper-plane"></i> Save Note</button>
                    </form>

                    <div class="timeline-container">
                        <?php if (empty($activities)): ?>
                            <div style="text-align:center; padding:15px 0; color:var(--text-muted); font-style:italic; font-size:12.5px;">
                                No activity events or case comments recorded yet.
                            </div>
                        <?php else: ?>
                            <?php foreach ($activities as $act): 
                                $dotClass = 'dot-comment';
                                if ($act['type'] === 'document') $dotClass = 'dot-document';
                                if ($act['type'] === 'underwriting') $dotClass = 'dot-underwriting';
                                if ($act['type'] === 'followup') $dotClass = 'dot-followup';
                            ?>
                                <div class="timeline-item">
                                    <div class="timeline-item-dot <?php echo $dotClass; ?>"></div>
                                    <div class="timeline-item-header">
                                        <span class="timeline-item-actor"><i class="fa-solid fa-user-tie" style="font-size:10px; color:var(--text-muted);"></i> <?php echo htmlspecialchars($act['actor'] ?? 'System'); ?></span>
                                        <span><?php echo date('d M Y, h:i A', strtotime($act['created_at'])); ?></span>
                                    </div>
                                    <div class="timeline-item-desc">
                                        <?php echo htmlspecialchars($act['description']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Follow-ups Callback Reminders Card -->
                <div class="info-card" style="border-left-color: #f59e0b;">
                    <h3 class="card-title"><i class="fa-solid fa-calendar-check"></i> Follow-up Callback Reminders</h3>
                    
                    <form id="scheduleFollowupForm" style="display:grid; grid-template-columns: 200px 180px 1fr auto; gap:12px; margin-bottom:20px; align-items:flex-end; background:#f8fafc; padding:14px; border-radius:10px; border:1px solid #e2e8f0;">
                        <input type="hidden" name="action" value="schedule_lead_followup">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($displayId, ENT_QUOTES, 'UTF-8'); ?>">
                        
                        <div class="form-group" style="margin:0;">
                            <label class="field-label" style="margin-bottom:4px;">Callback Due Date / Time</label>
                            <input type="datetime-local" name="due_at" class="form-input-simple" style="height:34px; border:1px solid var(--border-color); border-radius:6px; padding:0 8px; font-size:12.5px; width:100%;" required>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="field-label" style="margin-bottom:4px;">Callback Reason</label>
                            <select name="reason" class="form-select-simple" style="height:34px; margin:0; font-size:12.5px;">
                                <option value="Document Pending">Document Pending</option>
                                <option value="Salary Verification Callback">Salary Verification</option>
                                <option value="Bank Details Correction">Bank Details Correction</option>
                                <option value="Reference Call Back">Reference Callback</option>
                                <option value="Other / Audit Call">Other Check</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="field-label" style="margin-bottom:4px;">Scheduler Notes</label>
                            <input type="text" name="notes" class="form-input-simple" style="height:34px; border:1px solid var(--border-color); border-radius:6px; padding:0 10px; font-size:12.5px; width:100%;" placeholder="e.g. Call at 5 PM when free">
                        </div>
                        <button type="submit" class="btn-primary-adm" style="height:34px; padding:0 15px; font-size:12px; box-shadow:none; background:#f59e0b;"><i class="fa-solid fa-plus"></i> Schedule Callback</button>
                    </form>

                    <div class="table-responsive">
                        <table class="checklist-table">
                            <thead>
                                <tr>
                                    <th>Scheduled Callback Date</th>
                                    <th>Reason / Notes</th>
                                    <th>Scheduler Agent</th>
                                    <th>State</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($followups)): ?>
                                    <tr>
                                        <td colspan="4" style="text-align:center; padding:15px 0; color:var(--text-muted); font-style:italic; font-size:12.5px;">No follow-up callbacks scheduled yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($followups as $fup): ?>
                                        <tr>
                                            <td style="font-weight:700; color:var(--text-main); font-size:13px;"><?php echo date('d M Y, h:i A', strtotime($fup['due_at'])); ?></td>
                                            <td>
                                                <strong style="color:var(--primary); font-size:12.5px;"><?php echo htmlspecialchars($fup['reason']); ?></strong>
                                                <?php if ($fup['notes'] !== ''): ?>
                                                    <div style="font-size:11.5px; color:var(--text-muted); margin-top:2px;">Notes: <?php echo htmlspecialchars($fup['notes']); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td style="font-size:12.5px;"><?php echo htmlspecialchars($fup['actor']); ?></td>
                                            <td>
                                                <span class="checklist-status-badge <?php echo $fup['status'] === 'completed' ? 'checklist-status-verified' : 'checklist-status-pending'; ?>">
                                                    <?php echo $fup['status']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <!-- Panel 5: Declaration & Consents & Checklist (Wizard only) -->
                <div class="info-card">
                    <h3 class="card-title"><i class="fa-solid fa-signature"></i> Declaration & Consents</h3>
                    <div class="grid-fields">
                        <?php
                        $consentObj = ['terms' => true, 'credit' => true, 'communication' => true];
                        try {
                            if (!empty($app['consents'])) {
                                $consentObj = json_decode($app['consents'], true) ?? $consentObj;
                            }
                        } catch(\Exception $e){}
                        ?>
                        <div class="field-item">
                            <div class="field-label"><i class="fa-solid fa-check-double"></i> Terms & Conditions</div>
                            <div class="field-value"><?php echo ($consentObj['terms'] ?? false) ? 'Accepted ✅' : 'Declined ❌'; ?></div>
                        </div>
                        <div class="field-item">
                            <div class="field-label"><i class="fa-solid fa-check-double"></i> Credit Inquiry Consent</div>
                            <div class="field-value"><?php echo ($consentObj['credit'] ?? false) ? 'Accepted ✅' : 'Declined ❌'; ?></div>
                        </div>
                        <div class="field-item">
                            <div class="field-label"><i class="fa-solid fa-check-double"></i> Communication Opt-in</div>
                            <div class="field-value"><?php echo ($consentObj['communication'] ?? false) ? 'Accepted ✅' : 'Declined ❌'; ?></div>
                        </div>
                        
                        <div style="grid-column: span 2; border-top: 1px dashed var(--border-color); margin: 6px 0;"></div>
                        
                        <?php
                        $docsObj = [];
                        try {
                            if (!empty($app['documents'])) {
                                $docsObj = json_decode($app['documents'], true) ?? [];
                            }
                        } catch(\Exception $e){}
                        ?>
                        <div class="field-item" style="grid-column: span 2;">
                            <div class="field-label"><i class="fa-solid fa-list-check"></i> Wizard Checklist Status</div>
                            <div class="field-value" style="font-family: monospace; font-size:12px; color:var(--text-muted); margin-top: 4px;">
                                <?php 
                                if (!empty($docsObj)) {
                                    foreach($docsObj as $k => $v) {
                                        echo '<span style="background:var(--bg-dark); padding:3px 8px; border-radius:4px; margin-right:5px; border:1px solid var(--border-color); display:inline-block;">' . htmlspecialchars(strtoupper($k)) . ': ' . htmlspecialchars(strval($v)) . '</span>';
                                    }
                                } else {
                                    echo 'No Checklist Uploads Recorded';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Panel 6: Collapsible Developer Sync Section -->
            <div class="collapsible-card">
                <div class="collapsible-header" onclick="toggleCollapseSection()">
                    <div class="collapsible-header-title">
                        <i class="fa-solid fa-database" style="color:var(--primary);"></i> Integration & Ingestion Sync Metadata
                    </div>
                    <i class="fa-solid fa-chevron-down collapsible-icon" id="collapseChevron"></i>
                </div>
                <div class="collapsible-content" id="collapseContent">
                    <div class="collapsible-inner">
                        <div class="grid-fields" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                            <div class="field-item">
                                <div class="field-label">Ingestion Lead Source</div>
                                <div class="field-value"><?php echo htmlspecialchars((string)($app['source'] ?? 'Portal Direct'), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="field-item">
                                <div class="field-label">External System App ID</div>
                                <div class="field-value" style="font-family: monospace;"><?php echo htmlspecialchars((string)($app['external_application_id'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <?php if ($type === 'payday'): ?>
                                <div class="field-item">
                                    <div class="field-label">Sync Status Flag</div>
                                    <div class="field-value">
                                        <?php 
                                        $sStatus = strtolower(trim((string)($app['sync_status'] ?? 'pending')));
                                        $sColor = 'var(--warning)';
                                        if ($sStatus === 'synced') $sColor = 'var(--success)';
                                        if ($sStatus === 'failed') $sColor = 'var(--danger)';
                                        ?>
                                        <span class="status-badge-inline" style="background:<?php echo $sColor; ?>; color:#ffffff; font-size:10px; padding:3px 8px; border-radius:4px;"><?php echo $sStatus; ?></span>
                                    </div>
                                </div>
                                <div class="field-item">
                                    <div class="field-label">Synced Timestamp</div>
                                    <div class="field-value"><?php echo !empty($app['synced_at']) ? date('d M Y, h:i A', strtotime((string)$app['synced_at'])) : 'Never Synced'; ?></div>
                                </div>
                                <div class="field-item">
                                    <div class="field-label">Origin Ingestion System</div>
                                    <div class="field-value"><?php echo htmlspecialchars((string)($app['source_system'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                                <div class="field-item">
                                    <div class="field-label">Origin Lead ID</div>
                                    <div class="field-value" style="font-family: monospace;"><?php echo htmlspecialchars((string)($app['source_lead_id'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                                <div class="field-item">
                                    <div class="field-label">Origin App ID</div>
                                    <div class="field-value" style="font-family: monospace;"><?php echo htmlspecialchars((string)($app['source_application_id'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                                <div class="field-item">
                                    <div class="field-label">Origin State / Status</div>
                                    <div class="field-value"><?php echo htmlspecialchars((string)($app['source_status'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                                <div class="field-item">
                                    <div class="field-label">Ingestion Timestamp</div>
                                    <div class="field-value"><?php echo !empty($app['ingested_at']) ? date('d M Y, h:i A', strtotime((string)$app['ingested_at'])) : 'N/A'; ?></div>
                                </div>
                                <div class="field-item" style="grid-column: span 2; width: 100%;">
                                    <div class="field-label">Source Sync Payload Data</div>
                                    <?php if (!empty($app['source_payload'])): ?>
                                        <pre class="raw-payload"><?php echo htmlspecialchars((string)$app['source_payload'], ENT_QUOTES, 'UTF-8'); ?></pre>
                                    <?php else: ?>
                                        <div class="field-value" style="font-style:italic; color:var(--text-muted); font-size:12.5px;">No Sync Payload Recorded</div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lightbox Image Modal -->
<div id="imageLightboxModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(8px); z-index: 9999; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
    <span onclick="closeImageLightbox()" style="position: absolute; top: 25px; right: 35px; color: #f8fafc; font-size: 40px; font-weight: 300; cursor: pointer; transition: color 0.2s;" onmouseover="this.style.color='#f43f5e'" onmouseout="this.style.color='#f8fafc'">&times;</span>
    <div style="text-align: center; max-width: 90%; max-height: 85%;">
        <img id="lightboxImg" src="" alt="Document Preview" style="max-width: 100%; max-height: 80vh; border-radius: 8px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); border: 2px solid rgba(255,255,255,0.1);">
        <div id="lightboxCaption" style="color: #cbd5e1; margin-top: 15px; font-weight: 600; font-size: 15px; font-family: 'Plus Jakarta Sans', sans-serif;"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function toggleCollapseSection() {
        const content = document.getElementById('collapseContent');
        const chevron = document.getElementById('collapseChevron');
        if (content.classList.contains('expanded')) {
            content.classList.remove('expanded');
            chevron.style.transform = 'rotate(0deg)';
        } else {
            content.classList.add('expanded');
            chevron.style.transform = 'rotate(180deg)';
        }
    }

    function viewImageLightbox(src, caption) {
        if (src.toLowerCase().endsWith('.pdf')) {
            window.open(src, '_blank');
            return;
        }
        
        const modal = document.getElementById('imageLightboxModal');
        const img = document.getElementById('lightboxImg');
        const cap = document.getElementById('lightboxCaption');
        
        img.src = src;
        cap.innerText = caption;
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.style.opacity = '1';
        }, 10);
    }
    
    function closeImageLightbox() {
        const modal = document.getElementById('imageLightboxModal');
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageLightbox();
        }
    });

    // AJAX status submission
    const statusForm = document.getElementById('statusUpdateForm');
    if (statusForm) {
        statusForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('csrf_token', '<?php echo $_SESSION['csrf_token'] ?? ''; ?>');
            const selectedStatus = document.getElementById('statusSelect').value;
            
            Swal.fire({
                title: 'Updating status...',
                text: 'Please wait while we record the state change.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

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
                        text: `The application status is now set to ${selectedStatus.toUpperCase()}.`,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    
                    // Update header status badge
                    const badge = document.getElementById('headerStatusBadge');
                    if (badge) {
                        badge.className = `status-badge badge-status badge-${selectedStatus}`;
                        badge.innerText = selectedStatus;
                    }
                    
                    // Reload timeline states without full reload
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    throw new Error(data.message || 'Failed to update status.');
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: err.message || 'A network error occurred.'
                });
            });
        });
    }

    // AJAX Case Assignment & Priority submission
    const assignmentForm = document.getElementById('caseAssignmentForm');
    if (assignmentForm) {
        assignmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('csrf_token', '<?php echo $_SESSION['csrf_token'] ?? ''; ?>');
            
            const selectedAgent = document.getElementById('agentInput').value;
            const selectedPriority = document.getElementById('prioritySelect').value;

            Swal.fire({
                title: 'Updating case assignments...',
                text: 'Saving agent and priority changes.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('api.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Case Assigned!',
                        text: 'Agent & Priority details saved successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    
                    const headerAgent = document.getElementById('headerAgentName');
                    if (headerAgent) {
                        headerAgent.innerText = selectedAgent !== '' ? selectedAgent : 'Unassigned';
                    }
                    
                    const headerPriority = document.getElementById('headerPriorityBadge');
                    if (headerPriority) {
                        headerPriority.className = `priority-badge priority-${selectedPriority}`;
                        headerPriority.innerText = selectedPriority;
                    }
                } else {
                    throw new Error(data.message || 'Failed to assign case.');
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Assignment Failed',
                    text: err.message || 'A network error occurred.'
                });
            });
        });
    }

    // Toggle CAM Section form
    function toggleCamEditForm() {
        const sec = document.getElementById('camEditSection');
        if (sec.classList.contains('expanded')) {
            sec.classList.remove('expanded');
        } else {
            sec.classList.add('expanded');
            calculateCamFields();
        }
    }

    // Dynamic calculation of DTI, Total Repayment, Disposable Buffer
    function calculateCamFields() {
        const approvedAmt = parseFloat(document.getElementById('camApprvAmt').value) || 0;
        const tenureDays = parseInt(document.getElementById('camTenure').value) || 0;
        const interestRate = parseFloat(document.getElementById('camIntRate').value) || 0;
        const procRate = parseFloat(document.getElementById('camProcRate').value) || 0;
        const monthlyInc = parseFloat(document.getElementById('camMonthlyInc').value) || 0;
        const monthlyExp = parseFloat(document.getElementById('camMonthlyExp').value) || 0;

        const dti = monthlyInc > 0 ? (monthlyExp / monthlyInc) * 100 : 0;
        document.getElementById('camDtiRatio').value = dti.toFixed(1) + '%';

        const totalInterest = (approvedAmt * interestRate * tenureDays) / 100;
        const procFee = (approvedAmt * procRate) / 100;
        const gst = procFee * 0.18;
        const totalRepay = approvedAmt + totalInterest + procFee + gst;
        document.getElementById('camTotalRepay').value = Math.round(totalRepay);

        const dailyObligation = tenureDays > 0 ? totalRepay / tenureDays : 0;
        const netDispMonthly = monthlyInc - monthlyExp;
        const netDispDaily = netDispMonthly / 30;
        const buffer = dailyObligation > 0 ? (netDispDaily / dailyObligation) * 30 : 0;
        document.getElementById('camRepayBuffer').value = buffer.toFixed(2);
    }

    // Update Doc Verification Status via AJAX
    function updateDocStatus(docKey, label, status) {
        const remarkInput = document.getElementById('remark-doc-' + docKey);
        const remarkVal = remarkInput ? remarkInput.value : '';

        Swal.fire({
            title: 'Updating document verification...',
            text: `Setting ${label} to ${status.toUpperCase()}`,
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData();
        formData.append('action', 'verify_lead_document');
        formData.append('id', '<?php echo $id; ?>');
        formData.append('application_id', '<?php echo htmlspecialchars($displayId, ENT_QUOTES, 'UTF-8'); ?>');
        formData.append('document_key', docKey);
        formData.append('label', label);
        formData.append('status', status);
        formData.append('remark', remarkVal);
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token'] ?? ''; ?>');

        fetch('api.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Document Checked!',
                    text: 'Status recorded successfully.',
                    timer: 1500,
                    showConfirmButton: false
                });

                const badge = document.getElementById('badge-doc-' + docKey);
                if (badge) {
                    badge.className = `checklist-status-badge checklist-status-${status}`;
                    badge.innerText = status;
                }
                
                setTimeout(() => { window.location.reload(); }, 1000);
            } else {
                throw new Error(data.message || 'Failed to verify document.');
            }
        })
        .catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Check Failed',
                text: err.message || 'Network error occurred.'
            });
        });
    }

    // Submit CRM Comments / Callback notes
    const commentForm = document.getElementById('addCaseCommentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('csrf_token', '<?php echo $_SESSION['csrf_token'] ?? ''; ?>');

            Swal.fire({
                title: 'Saving internal case notes...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            fetch('api.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Note Logged!',
                        timer: 1000,
                        showConfirmButton: false
                    });
                    setTimeout(() => { window.location.reload(); }, 800);
                } else {
                    throw new Error(data.message || 'Failed to save notes.');
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Logging Failed',
                    text: err.message
                });
            });
        });
    }

    // Submit Followups Callback Scheduler Form
    const followupForm = document.getElementById('scheduleFollowupForm');
    if (followupForm) {
        followupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('csrf_token', '<?php echo $_SESSION['csrf_token'] ?? ''; ?>');

            Swal.fire({
                title: 'Scheduling callback alert...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            fetch('api.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Callback Scheduled!',
                        timer: 1000,
                        showConfirmButton: false
                    });
                    setTimeout(() => { window.location.reload(); }, 800);
                } else {
                    throw new Error(data.message || 'Failed to save callback.');
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Scheduler Failed',
                    text: err.message
                });
            });
        });
    }

    // Submit Underwriting CAM Sheet
    const camForm = document.getElementById('camDetailsSheetForm');
    if (camForm) {
        camForm.addEventListener('submit', function(e) {
            e.preventDefault();
            calculateCamFields();
            const formData = new FormData(this);
            formData.append('csrf_token', '<?php echo $_SESSION['csrf_token'] ?? ''; ?>');

            Swal.fire({
                title: 'Saving credit assessment memo...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            fetch('api.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Memo Saved successfully!',
                        text: 'CAM version ' + data.version + ' is active now.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    setTimeout(() => { window.location.reload(); }, 1200);
                } else {
                    throw new Error(data.message || 'Failed to save memo.');
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Saving Memo Failed',
                    text: err.message
                });
            });
        });
    }
</script>

<?php include 'footer.php'; ?>
