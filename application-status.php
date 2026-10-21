<?php
include 'includes/header.php';
include 'includes/main-header.php';
require_once __DIR__ . '/includes/loan_flow.php';

start_secure_session();

$conn = db();
$requestedAppId = trim((string) ($_GET['id'] ?? ''));
$sessionAppIds = array_filter([
    trim((string) ($_SESSION['loan_app_id_final'] ?? '')),
    trim((string) ($_SESSION['app_id'] ?? '')),
    trim((string) ($_SESSION['loan_app_id'] ?? '')),
]);
$accessDenied = false;
$appId = '';
$application = null;

if ($requestedAppId !== '') {
    if (in_array($requestedAppId, $sessionAppIds, true)) {
        $appId = $requestedAppId;
    } elseif (!empty($_SESSION['is_authenticated'])) {
        $mobile = trim((string) ($_SESSION['mobile_number'] ?? $_SESSION['user_id'] ?? ''));
        if ($mobile !== '') {
            $stmt = $conn->prepare('SELECT * FROM loan_applications WHERE application_id = ? AND mobile = ? LIMIT 1');
            $stmt->bind_param('ss', $requestedAppId, $mobile);
            $stmt->execute();
            $application = $stmt->get_result()->fetch_assoc();
            if ($application) {
                $appId = $requestedAppId;
                $_SESSION['app_id'] = $appId;
            }
        }
    }

    $accessDenied = $application === null && $appId === '';
} else {
    $appId = trim((string) ($_SESSION['loan_app_id_final'] ?? $_SESSION['app_id'] ?? ''));
}

if ($application === null && $appId !== '') {
    $stmt = $conn->prepare('SELECT * FROM loan_applications WHERE application_id = ?');
    $stmt->bind_param('s', $appId);
    $stmt->execute();
    $application = $stmt->get_result()->fetch_assoc();
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money($value): string
{
    if ($value === null || $value === '') {
        return 'Not provided';
    }

    return '₹' . number_format((float) $value, 0, '.', ',');
}

function label_value($value): string
{
    if ($value === null || $value === '') {
        return 'Not provided';
    }

    return ucwords(str_replace(['_', '-'], ' ', (string) $value));
}
?>

<link rel="stylesheet" href="assets/css/loan-flow.css">

<style>
    .as-container {
        max-width: 1120px;
        margin: 0 auto;
    }

    .as-shell {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 340px;
        gap: 18px;
        align-items: start;
    }

    .as-panel {
        background: #fff;
        border: 1px solid var(--wf-border);
        border-radius: var(--wf-radius);
        box-shadow: var(--wf-shadow-sm);
    }

    .as-main {
        overflow: hidden;
    }

    .as-command {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 22px;
        border-bottom: 1px solid var(--wf-border);
        background: #fbfdff;
    }

    .as-command-title {
        margin: 0;
        color: var(--wf-primary);
        font-size: 15px;
        font-weight: 800;
    }

    .as-command-sub {
        margin: 3px 0 0;
        color: var(--wf-muted);
        font-size: 12px;
    }

    .as-command-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .as-mini-btn {
        height: 38px;
        border-radius: var(--wf-radius-xs);
        border: 1px solid var(--wf-border);
        background: #fff;
        color: var(--wf-text);
        padding: 0 12px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
    }

    .as-mini-btn.primary {
        border-color: var(--wf-blue);
        background: var(--wf-blue);
        color: #fff;
    }

    .as-hero {
        padding: 28px 28px 18px;
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 18px;
        align-items: center;
    }

    .as-status-icon {
        width: 70px;
        height: 70px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        background: var(--wf-blue-light);
        color: var(--wf-blue);
    }

    .as-status-icon.submitted,
    .as-status-icon.review { background: #eff6ff; color: #2563eb; }
    .as-status-icon.approved,
    .as-status-icon.disbursed { background: var(--wf-success-bg); color: var(--wf-success); }
    .as-status-icon.rejected { background: var(--wf-error-bg); color: var(--wf-error); }
    .as-status-icon.draft { background: #f8fafc; color: var(--wf-muted); }

    .as-title {
        color: var(--wf-primary);
        font-size: 28px;
        line-height: 1.2;
        font-weight: 850;
        margin: 0 0 7px;
    }

    .as-sub {
        color: var(--wf-muted);
        margin: 0;
        font-size: 14px;
        line-height: 1.5;
    }

    .as-meta {
        display: flex;
        gap: 9px;
        flex-wrap: wrap;
        margin-top: 14px;
    }

    .as-chip {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 34px;
        border: 1px solid var(--wf-border);
        background: #f8fafc;
        color: var(--wf-text);
        border-radius: 999px;
        padding: 7px 12px;
        font-size: 12px;
        font-weight: 800;
        max-width: 100%;
    }

    .as-progress-area {
        padding: 0 28px 20px;
    }

    .as-progress-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        color: var(--wf-muted);
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .as-body-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 300px;
        gap: 18px;
        padding: 0 28px 28px;
    }

    .as-section {
        border-top: 1px solid var(--wf-border);
        padding-top: 18px;
    }

    .as-section-title {
        font-size: 15px;
        font-weight: 850;
        color: var(--wf-primary);
        margin: 0 0 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .as-note {
        border-radius: var(--wf-radius-xs);
        padding: 14px;
        background: var(--wf-blue-light);
        color: var(--wf-primary);
        font-size: 13px;
        line-height: 1.55;
        margin-top: 14px;
    }

    .as-checklist {
        display: grid;
        gap: 10px;
    }

    .as-check-item {
        display: grid;
        grid-template-columns: 30px 1fr auto;
        gap: 10px;
        align-items: center;
        border: 1px solid var(--wf-border);
        border-radius: var(--wf-radius-xs);
        padding: 11px 12px;
        background: #fbfdff;
    }

    .as-check-dot {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e2e8f0;
        color: var(--wf-muted);
        font-size: 12px;
    }

    .as-check-item.done .as-check-dot {
        background: var(--wf-success-bg);
        color: var(--wf-success);
    }

    .as-check-title {
        color: var(--wf-text);
        font-size: 13px;
        font-weight: 800;
    }

    .as-check-sub {
        color: var(--wf-muted);
        font-size: 12px;
        margin-top: 2px;
    }

    .as-pill {
        border-radius: 999px;
        padding: 5px 9px;
        background: #f1f5f9;
        color: var(--wf-muted);
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .as-check-item.done .as-pill {
        background: var(--wf-success-bg);
        color: #047857;
    }

    .as-side {
        display: grid;
        gap: 14px;
    }

    .as-side .as-panel {
        padding: 20px;
    }

    .as-summary-list {
        display: grid;
        gap: 10px;
    }

    .as-summary-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding: 10px 0;
        border-bottom: 1px solid var(--wf-border);
    }

    .as-summary-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .as-summary-row span {
        color: var(--wf-muted);
        font-size: 12px;
        font-weight: 700;
    }

    .as-summary-row strong {
        color: var(--wf-text);
        font-size: 13px;
        font-weight: 850;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .as-support-box {
        background: #f8fafc;
        border: 1px solid var(--wf-border);
        border-radius: var(--wf-radius-xs);
        padding: 13px;
        color: var(--wf-text);
        font-size: 13px;
        line-height: 1.5;
    }

    .as-actions {
        display: grid;
        gap: 10px;
        margin-top: 12px;
    }

    .as-actions .lf-btn {
        height: 46px;
    }

    .as-empty {
        max-width: 560px;
        margin: 0 auto;
        padding: 34px 28px;
        text-align: center;
    }

    .as-empty .as-status-icon {
        margin-bottom: 14px;
    }

    @media (max-width: 980px) {
        .as-shell,
        .as-body-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 620px) {
        .as-command,
        .as-hero {
            grid-template-columns: 1fr;
            align-items: start;
        }

        .as-command {
            display: grid;
        }

        .as-command-actions {
            justify-content: stretch;
        }

        .as-mini-btn {
            flex: 1;
            justify-content: center;
        }

        .as-title {
            font-size: 22px;
        }

        .as-hero,
        .as-progress-area,
        .as-body-grid {
            padding-left: 20px;
            padding-right: 20px;
        }

        .as-check-item {
            grid-template-columns: 30px 1fr;
        }

        .as-pill {
            grid-column: 2;
            width: max-content;
        }
    }

    /* Underwriting Simulator Overlay CSS */
    .underwriting-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: radial-gradient(120% 120% at 50% 10%, #f8fafc 30%, #f1f5f9 70%, #e2e8f0 100%);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        transition: opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.6s;
    }
    
    .underwriting-overlay.fade-out {
        opacity: 0;
        visibility: hidden;
    }

    .underwriting-card {
        background: #ffffff;
        border-radius: 28px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05), 0 30px 60px -15px rgba(15, 23, 42, 0.1);
        border: 1px solid rgba(226, 232, 240, 0.8);
        padding: 48px;
        max-width: 480px;
        width: 100%;
        text-align: center;
        animation: cardPop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
    }
    
    @keyframes cardPop {
        from { transform: scale(0.9) translateY(10px); opacity: 0; }
        to { transform: scale(1) translateY(0); opacity: 1; }
    }

    .underwriting-spinner-container {
        position: relative;
        width: 100px;
        height: 100px;
        margin: 0 auto 24px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .underwriting-spinner {
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        border: 3.5px solid #f1f5f9;
        border-top-color: #18529c;
        animation: spin 1s infinite linear;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .underwriting-spinner-logo {
        width: 42px;
        height: 42px;
        object-fit: contain;
        animation: pulseLogo 1.6s infinite ease-in-out;
    }
    
    @keyframes pulseLogo {
        0%, 100% { transform: scale(1); opacity: 0.95; }
        50% { transform: scale(1.12); opacity: 1; }
    }

    .underwriting-title {
        font-size: 22px;
        font-weight: 850;
        color: #0f172a;
        margin-bottom: 8px;
        letter-spacing: -0.4px;
    }

    .underwriting-subtitle {
        font-size: 13.5px;
        color: #64748b;
        line-height: 1.5;
        margin-bottom: 32px;
    }

    .underwriting-steps {
        display: flex;
        flex-direction: column;
        gap: 16px;
        text-align: left;
    }

    .underwriting-step {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        transition: all 0.3s ease;
    }
    
    .underwriting-step.active {
        color: #18529c;
    }
    
    .underwriting-step.success {
        color: #0f172a;
    }

    .underwriting-step .step-icon {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        background: #f1f5f9;
        color: #94a3b8;
        transition: all 0.3s ease;
    }

    .underwriting-step.active .step-icon {
        background: #eff6ff;
        color: #18529c;
    }

    .underwriting-step.success .step-icon {
        background: #ecfdf5;
        color: #10b981;
        font-size: 12px;
    }
</style>

<div class="lf-page">
    <div class="as-container">
        <?php if (!$application): ?>
            <div class="as-panel as-empty">
                <div class="as-status-icon draft">
                    <i class="fa-regular fa-folder-open"></i>
                </div>
                <h1 class="as-title"><?php echo $accessDenied ? 'Application Access Expired' : 'No Active Application'; ?></h1>
                <p class="as-sub"><?php echo $accessDenied ? 'Please login or verify your mobile number again to view this application status.' : 'Start a new payday loan application and track every update here.'; ?></p>
                <div class="as-actions">
                    <a href="<?php echo $accessDenied ? 'customer-login.php' : 'payday-apply.php'; ?>" class="lf-btn lf-btn-primary"><?php echo $accessDenied ? 'Login Again' : 'Apply Now'; ?> <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
        <?php else:
            $currentStatus = strtolower((string) ($application['status'] ?? 'submitted'));
            ?>
            <!-- Underwriting & Profile Analysis Simulator Overlay -->
            <div id="underwriting-loader" class="underwriting-overlay">
                <div class="underwriting-card">
                    <div class="underwriting-spinner-container">
                        <div class="underwriting-spinner"></div>
                        <img src="assets/images/fev-icon.png" alt="Waqt Logo" class="underwriting-spinner-logo">
                    </div>
                    <h3 class="underwriting-title">Analyzing Your Profile</h3>
                    <p class="underwriting-subtitle">Please wait while our automated underwriting system performs validation checks...</p>
                    
                    <div class="underwriting-steps">
                        <div class="underwriting-step" id="step-1">
                            <span class="step-icon"><i class="fa-solid fa-spinner fa-spin"></i></span>
                            <span class="step-text">Verifying identity and KYC declarations...</span>
                        </div>
                        <div class="underwriting-step" id="step-2">
                            <span class="step-icon"><i class="fa-regular fa-circle"></i></span>
                            <span class="step-text">Evaluating credit bureau suitability...</span>
                        </div>
                        <div class="underwriting-step" id="step-3">
                            <span class="step-icon"><i class="fa-regular fa-circle"></i></span>
                            <span class="step-text">Analyzing income stability & documentation...</span>
                        </div>
                        <div class="underwriting-step" id="step-4">
                            <span class="step-icon"><i class="fa-regular fa-circle"></i></span>
                            <span class="step-text">Generating live tracking workspace...</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Script to simulate profile verification -->
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const appId = "<?= e($appId) ?>";
                    const storageKey = "underwriting_checked_" + appId;
                    
                    const loader = document.getElementById("underwriting-loader");
                    if (!loader) return;
                    
                    // If already simulated for this application in the current browser session, bypass instantly
                    if (sessionStorage.getItem(storageKey) === "true") {
                        loader.style.display = "none";
                        return;
                    }

                    // Run the step-by-step verification simulation
                    const steps = [
                        { id: "step-1", nextDelay: 1200 },
                        { id: "step-2", nextDelay: 1200 },
                        { id: "step-3", nextDelay: 1200 },
                        { id: "step-4", nextDelay: 800 }
                    ];

                    function runStep(index) {
                        if (index >= steps.length) {
                            // All steps completed, fade out the loader overlay
                            setTimeout(() => {
                                loader.classList.add("fade-out");
                                // Mark as completed in session storage
                                sessionStorage.setItem(storageKey, "true");
                                setTimeout(() => {
                                    loader.style.display = "none";
                                }, 600);
                            }, 500);
                            return;
                        }

                        const current = steps[index];
                        const currentEl = document.getElementById(current.id);
                        if (currentEl) {
                            currentEl.classList.add("active");
                            currentEl.querySelector(".step-icon").innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
                        }

                        setTimeout(() => {
                            if (currentEl) {
                                currentEl.classList.remove("active");
                                currentEl.classList.add("success");
                                currentEl.querySelector(".step-icon").innerHTML = '<i class="fa-solid fa-circle-check"></i>';
                            }
                            
                            // Trigger the next step
                            runStep(index + 1);
                        }, current.nextDelay);
                    }

                    // Start step 1 after a tiny delay
                    setTimeout(() => {
                        runStep(0);
                    }, 300);
                });
            </script>
            <?php
            $submittedDate = !empty($application['created_at']) ? date('d M Y', strtotime($application['created_at'])) : 'Not available';
            $updatedDate = !empty($application['updated_at']) ? date('d M Y, h:i A', strtotime($application['updated_at'])) : 'Not available';

            $statusContent = [
                'draft' => [
                    'icon' => 'fa-regular fa-pen-to-square',
                    'title' => 'Application In Progress',
                    'msg' => 'Your application has been started but final documents are not submitted yet.',
                    'note' => 'Complete the pending steps to submit your loan application for verification.',
                    'progress' => 20,
                    'activeStep' => 1,
                    'sla' => 'Action needed',
                ],
                'submitted' => [
                    'icon' => 'fa-solid fa-circle-check',
                    'title' => 'Application Submitted',
                    'msg' => 'Your application is received and queued for verification.',
                    'note' => 'Our team will verify your details and documents. Keep your phone reachable for any confirmation call.',
                    'progress' => 45,
                    'activeStep' => 2,
                    'sla' => 'Usually reviewed within 24-48 hours',
                ],
                'review' => [
                    'icon' => 'fa-solid fa-magnifying-glass-chart',
                    'title' => 'Under Review',
                    'msg' => 'Your documents and eligibility details are being reviewed.',
                    'note' => 'This stage may require additional checks. You will be notified when a decision is available.',
                    'progress' => 65,
                    'activeStep' => 3,
                    'sla' => 'Verification in progress',
                ],
                'approved' => [
                    'icon' => 'fa-solid fa-thumbs-up',
                    'title' => 'Loan Approved',
                    'msg' => 'Congratulations. Your application has been approved.',
                    'note' => 'Please wait for the offer or agreement confirmation from the Waqt Finance team.',
                    'progress' => 85,
                    'activeStep' => 4,
                    'sla' => 'Offer confirmation pending',
                ],
                'rejected' => [
                    'icon' => 'fa-solid fa-circle-xmark',
                    'title' => 'Application Not Approved',
                    'msg' => 'We could not approve this application at the moment.',
                    'note' => 'You may review your eligibility and apply again later with updated details.',
                    'progress' => 100,
                    'activeStep' => 4,
                    'sla' => 'Decision completed',
                ],
                'disbursed' => [
                    'icon' => 'fa-solid fa-building-columns',
                    'title' => 'Amount Disbursed',
                    'msg' => 'The loan amount has been credited as per the approved terms.',
                    'note' => 'Please keep repayment dates in mind and contact support for any servicing help.',
                    'progress' => 100,
                    'activeStep' => 5,
                    'sla' => 'Disbursal completed',
                ],
            ];

            $current = $statusContent[$currentStatus] ?? $statusContent['submitted'];
            $timeline = [
                ['key' => 'submitted', 'label' => 'Submitted', 'icon' => 'fa-solid fa-file-circle-check'],
                ['key' => 'review', 'label' => 'Verification', 'icon' => 'fa-solid fa-magnifying-glass'],
                ['key' => 'approved', 'label' => $currentStatus === 'rejected' ? 'Decision' : 'Approval', 'icon' => 'fa-solid fa-scale-balanced'],
                ['key' => 'disbursed', 'label' => 'Disbursal', 'icon' => 'fa-solid fa-indian-rupee-sign'],
            ];

            $checks = [
                ['label' => 'Mobile verified', 'sub' => 'OTP verification completed', 'done' => !empty($_SESSION['otp_verified']) || !empty($application['mobile'])],
                ['label' => 'PAN details', 'sub' => 'Identity information captured', 'done' => !empty($application['pan_number']) && !empty($application['full_name'])],
                ['label' => 'Aadhaar KYC', 'sub' => 'KYC reference stored securely', 'done' => !empty($application['aadhaar_number']) || !empty($application['aadhaar_masked'])],
                ['label' => 'Work details', 'sub' => 'Employment profile available', 'done' => !empty($application['company_name']) && !empty($application['office_email'])],
                ['label' => 'Bank details', 'sub' => 'Disbursal account added', 'done' => !empty($application['bank_name']) && !empty($application['ifsc_code'])],
                ['label' => 'References', 'sub' => 'Two contacts added for verification', 'done' => loan_flow_reference_count($conn, (string) $application['application_id']) >= 2],
                ['label' => 'Documents', 'sub' => 'Salary slip and selfie uploaded', 'done' => !empty($application['salary_slip_current']) && !empty($application['selfie_image'])],
            ];
            $doneChecks = count(array_filter($checks, fn($item) => $item['done']));
            $checkPercent = (int) round(($doneChecks / count($checks)) * 100);
            ?>

            <div class="as-shell">
                <main class="as-panel as-main">
                    <div class="as-command">
                        <div>
                            <h1 class="as-command-title">Loan Application Workspace</h1>
                            <p class="as-command-sub">Status, verification progress, and next steps in one place.</p>
                        </div>
                        <div class="as-command-actions">
                            <button type="button" class="as-mini-btn" id="copyAppId" data-app-id="<?= e($application['application_id']) ?>">
                                <i class="fa-regular fa-copy"></i> Copy ID
                            </button>
                            <a class="as-mini-btn" href="contact-us.php"><i class="fa-regular fa-circle-question"></i> Help</a>
                        </div>
                    </div>

                    <div class="as-hero">
                        <div class="as-status-icon <?= e($currentStatus) ?>">
                            <i class="<?= e($current['icon']) ?>"></i>
                        </div>
                        <div>
                            <h2 class="as-title"><?= e($current['title']) ?></h2>
                            <p class="as-sub"><?= e($current['msg']) ?></p>
                            <div class="as-meta">
                                <span class="as-chip"><i class="fa-regular fa-id-badge"></i> <?= e($application['application_id']) ?></span>
                                <span class="as-chip"><i class="fa-regular fa-calendar"></i> <?= e($submittedDate) ?></span>
                                <span class="as-chip"><i class="fa-regular fa-clock"></i> <?= e($current['sla']) ?></span>
                                <span class="lf-badge <?= e($currentStatus) ?>"><?= e(label_value($currentStatus)) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="as-progress-area">
                        <div class="as-progress-head">
                            <span>Application progress</span>
                            <span><?= (int) $current['progress'] ?>%</span>
                        </div>
                        <div class="lf-progress-bar-wrap" aria-label="Application progress">
                            <div class="lf-progress-bar-fill" style="width: <?= (int) $current['progress'] ?>%;"></div>
                        </div>
                    </div>

                    <div class="as-body-grid">
                        <section class="as-section">
                            <h3 class="as-section-title"><i class="fa-solid fa-route"></i> Decision Timeline</h3>
                            <ul class="lf-timeline">
                                <?php foreach ($timeline as $index => $item):
                                    $stepNumber = $index + 2;
                                    $class = $stepNumber < (int) $current['activeStep'] ? 'done' : ($stepNumber === (int) $current['activeStep'] ? 'active' : '');
                                    if ($currentStatus === 'rejected' && $item['key'] === 'approved') {
                                        $class = 'active';
                                    }
                                    ?>
                                    <li class="lf-timeline-item <?= e($class) ?>">
                                        <div class="lf-timeline-dot"><i class="<?= e($item['icon']) ?>"></i></div>
                                        <div class="lf-timeline-content">
                                            <div class="lf-timeline-label"><?= e($item['label']) ?></div>
                                            <div class="lf-timeline-meta">
                                                <?= $class === 'done' ? 'Completed' : ($class === 'active' ? 'Current stage' : 'Pending') ?>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <div class="as-note">
                                <strong>Next update:</strong> <?= e($current['note']) ?>
                            </div>
                        </section>

                        <section class="as-section">
                            <h3 class="as-section-title"><i class="fa-solid fa-list-check"></i> Verification</h3>
                            <div class="as-progress-head">
                                <span><?= $doneChecks ?> of <?= count($checks) ?> complete</span>
                                <span><?= $checkPercent ?>%</span>
                            </div>
                            <div class="lf-progress-bar-wrap" aria-label="Verification checklist progress">
                                <div class="lf-progress-bar-fill" style="width: <?= $checkPercent ?>%;"></div>
                            </div>
                            <div class="as-checklist">
                                <?php foreach ($checks as $check): ?>
                                    <div class="as-check-item <?= $check['done'] ? 'done' : '' ?>">
                                        <div class="as-check-dot">
                                            <i class="fa-solid <?= $check['done'] ? 'fa-check' : 'fa-minus' ?>"></i>
                                        </div>
                                        <div>
                                            <div class="as-check-title"><?= e($check['label']) ?></div>
                                            <div class="as-check-sub"><?= e($check['sub']) ?></div>
                                        </div>
                                        <div class="as-pill"><?= $check['done'] ? 'Done' : 'Pending' ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    </div>
                </main>

                <aside class="as-side">
                    <section class="as-panel">
                        <h3 class="as-section-title"><i class="fa-regular fa-file-lines"></i> Application Summary</h3>
                        <div class="as-summary-list">
                            <div class="as-summary-row"><span>Loan Amount</span><strong><?= e(money($application['loan_amount'] ?? null)) ?></strong></div>
                            <div class="as-summary-row"><span>Loan Purpose</span><strong><?= e(label_value($application['loan_purpose'] ?? null)) ?></strong></div>
                            <div class="as-summary-row"><span>Applicant</span><strong><?= e($application['full_name'] ?: 'Not provided') ?></strong></div>
                            <div class="as-summary-row"><span>Mobile</span><strong><?= e($application['mobile'] ?? 'Not provided') ?></strong></div>
                            <div class="as-summary-row"><span>City</span><strong><?= e($application['city'] ?: 'Not provided') ?></strong></div>
                            <div class="as-summary-row"><span>Income</span><strong><?= e(money($application['monthly_income'] ?? null)) ?></strong></div>
                            <div class="as-summary-row"><span>Employment</span><strong><?= e(label_value($application['employment_status'] ?? null)) ?></strong></div>
                            <div class="as-summary-row"><span>Last Updated</span><strong><?= e($updatedDate) ?></strong></div>
                        </div>
                    </section>

                    <section class="as-panel">
                        <h3 class="as-section-title"><i class="fa-solid fa-circle-play"></i> What Happens Next?</h3>
                        
                        <div style="display: flex; flex-direction: column; gap: 18px; margin-top: 14px;">
                            <!-- Step 1 -->
                            <div style="display: flex; gap: 14px; align-items: flex-start;">
                                <div style="width: 28px; height: 28px; border-radius: 50%; background: #ecfdf5; color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; flex-shrink: 0;">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                                <div>
                                    <h4 style="font-size: 13px; font-weight: 800; color: #0f172a; margin: 0 0 2px;">Application Received</h4>
                                    <p style="font-size: 12px; color: #64748b; margin: 0; line-height: 1.4;">Your profile has been saved successfully in our secure database.</p>
                                </div>
                            </div>
                            
                            <!-- Step 2 -->
                            <div style="display: flex; gap: 14px; align-items: flex-start;">
                                <div style="width: 28px; height: 28px; border-radius: 50%; background: #eff6ff; color: #18529c; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0; position: relative;">
                                    <i class="fa-solid fa-spinner fa-spin" style="font-size: 12px;"></i>
                                </div>
                                <div>
                                    <h4 style="font-size: 13px; font-weight: 800; color: #18529c; margin: 0 0 2px;">Document Verification</h4>
                                    <p style="font-size: 12px; color: #475569; margin: 0; line-height: 1.4;">Our review team will verify your submitted salary slips & KYC. <strong>Keep your mobile phone active</strong> as our agent may call you shortly.</p>
                                </div>
                            </div>
                            
                            <!-- Step 3 -->
                            <div style="display: flex; gap: 14px; align-items: flex-start;">
                                <div style="width: 28px; height: 28px; border-radius: 50%; background: #f1f5f9; color: #94a3b8; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0;">
                                    3
                                </div>
                                <div>
                                    <h4 style="font-size: 13px; font-weight: 800; color: #64748b; margin: 0 0 2px;">Loan Agreement Signature</h4>
                                    <p style="font-size: 12px; color: #64748b; margin: 0; line-height: 1.4;">Upon approval, a digital loan agreement link will be SMSed to your registered mobile number for e-signing.</p>
                                </div>
                            </div>
                            
                            <!-- Step 4 -->
                            <div style="display: flex; gap: 14px; align-items: flex-start;">
                                <div style="width: 28px; height: 28px; border-radius: 50%; background: #f1f5f9; color: #94a3b8; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0;">
                                    4
                                </div>
                                <div>
                                    <h4 style="font-size: 13px; font-weight: 800; color: #64748b; margin: 0 0 2px;">Instant Disbursal</h4>
                                    <p style="font-size: 12px; color: #64748b; margin: 0; line-height: 1.4;">The approved loan funds will be disbursed directly into your validated bank account within 2 hours of e-signing.</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="as-panel">
                        <h3 class="as-section-title"><i class="fa-solid fa-headset"></i> Need Assistance?</h3>
                        <div class="as-support-box">
                            Keep your application ID ready when contacting support. Our team may call you if more information is required.
                        </div>
                        <div class="as-actions">
                            <?php if ($currentStatus === 'draft'): ?>
                                <a href="<?= e(loan_flow_next_url($conn, $application)) ?>" class="lf-btn lf-btn-primary">Continue Application <i class="fa-solid fa-arrow-right"></i></a>
                            <?php elseif ($currentStatus === 'rejected'): ?>
                                <a href="payday-apply.php" class="lf-btn lf-btn-primary">Reapply <i class="fa-solid fa-arrow-right"></i></a>
                            <?php else: ?>
                                <a href="contact-us.php" class="lf-btn lf-btn-primary">Contact Support <i class="fa-solid fa-phone"></i></a>
                            <?php endif; ?>
                            <a href="pay-day-loan.php" class="lf-btn lf-btn-outline">View Product</a>
                        </div>
                    </section>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($application): ?>
    <script>
        const copyBtn = document.getElementById("copyAppId");
        if (copyBtn) {
            copyBtn.addEventListener("click", async () => {
                const original = copyBtn.innerHTML;
                try {
                    await navigator.clipboard.writeText(copyBtn.dataset.appId || "");
                    copyBtn.innerHTML = "<i class='fa-solid fa-check'></i> Copied";
                    setTimeout(() => copyBtn.innerHTML = original, 1600);
                } catch (err) {
                    copyBtn.innerHTML = "<i class='fa-solid fa-check'></i> ID Ready";
                    setTimeout(() => copyBtn.innerHTML = original, 1600);
                }
            });
        }
    </script>
<?php endif; ?>

<?php if ($application && !in_array(strtolower((string) ($application['status'] ?? '')), ['approved', 'rejected', 'disbursed'], true)): ?>
    <script>
        setTimeout(() => {
            location.reload();
        }, 50000);
    </script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
