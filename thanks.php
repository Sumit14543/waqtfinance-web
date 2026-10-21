<?php
$page_title = "Thank You | Waqt Finance";
$meta_description = "Your submission has been successfully received by Waqt Finance. Our team will review your details and get back to you shortly.";
$meta_keywords = "thank you, loan application submitted, contact submitted, waqt finance success";
$canonical_url = "https://www.waqtfinance.com/thanks";

include 'includes/header.php';
include 'includes/main-header.php';

// Fetch Display Reference ID from Session
$display_ref = 'N/A';
$app_id = $_SESSION['loan_app_id_final'] ?? $_SESSION['app_id'] ?? '';
if ($app_id !== '') {
    $db = db();
    $id_val = null;
    $prefix = 'WAQT';

    // 1. Try edi_loan_applications
    $stmt = $db->prepare("SELECT id FROM edi_loan_applications WHERE application_id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $app_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if ($res) {
            $id_val = $res['id'];
            $prefix = 'WAQT-EDI';
        }
        $stmt->close();
    }

    // 2. Try loan_applications (common table)
    if (!$id_val) {
        $stmt = $db->prepare("SELECT id, loan_type FROM loan_applications WHERE application_id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("s", $app_id);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            if ($res) {
                $id_val = $res['id'];
                $type = strtoupper($res['loan_type'] ?? 'GEN');
                $prefix = 'WAQT-' . $type;
            }
            $stmt->close();
        }
    }

    // 3. Try apply_loan_applications
    if (!$id_val) {
        $stmt = $db->prepare("SELECT id, loan_type FROM apply_loan_applications WHERE application_ref = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("s", $app_id);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            if ($res) {
                $id_val = $res['id'];
                $type = strtoupper($res['loan_type'] ?? 'GEN');
                $prefix = 'WAQT-' . $type;
            }
            $stmt->close();
        }
    }

    if ($id_val) {
        $display_ref = $prefix . '-' . str_pad((string)$id_val, 6, '0', STR_PAD_LEFT);
    }
}

// Detect if this is a contact submission redirect
$is_contact = isset($_GET['type']) && $_GET['type'] === 'contact';
?>

<style>
    body {
        background: radial-gradient(120% 120% at 50% 10%, #f0f7ff 30%, #e0f2fe 70%, #ffffff 100%) !important;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        position: relative;
    }

    /* Decorative Ambient Background Blobs */
    .bg-blob {
        position: fixed;
        width: 450px;
        height: 450px;
        border-radius: 50%;
        filter: blur(80px);
        z-index: -1;
        pointer-events: none;
        opacity: 0.45;
        animation: floatBlob 12s ease-in-out infinite;
    }
    
    .blob-1 {
        top: -100px;
        left: -100px;
        background: radial-gradient(circle, rgba(24, 82, 156, 0.25) 0%, rgba(24, 82, 156, 0) 70%);
    }
    
    .blob-2 {
        bottom: -150px;
        right: -150px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.2) 0%, rgba(16, 185, 129, 0) 70%);
        animation-delay: -6s;
    }

    @keyframes floatBlob {
        0%, 100% { transform: translateY(0) scale(1); }
        50% { transform: translateY(-30px) scale(1.08); }
    }

    /* Confetti Celebration Overlay */
    .confetti-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        pointer-events: none;
        z-index: 1;
    }

    .confetti {
        position: absolute;
        width: 10px;
        height: 10px;
        border-radius: 2px;
        top: -20px;
        opacity: 0;
        animation: fall 4s infinite linear;
    }

    .c-1 { left: 10%; background: #18529c; animation-delay: 0.2s; animation-duration: 3.5s; }
    .c-2 { left: 25%; background: #10b981; animation-delay: 1.1s; animation-duration: 4.2s; }
    .c-3 { left: 40%; background: #f59e0b; animation-delay: 0.5s; animation-duration: 3.8s; }
    .c-4 { left: 55%; background: #ef4444; animation-delay: 1.8s; animation-duration: 4.5s; }
    .c-5 { left: 70%; background: #6366f1; animation-delay: 0.8s; animation-duration: 3.9s; }
    .c-6 { left: 85%; background: #06b6d4; animation-delay: 1.4s; animation-duration: 4.1s; }
    .c-7 { left: 15%; background: #ec4899; animation-delay: 2.2s; animation-duration: 3.6s; }
    .c-8 { left: 50%; background: #8b5cf6; animation-delay: 0.3s; animation-duration: 4s; }
    .c-9 { left: 80%; background: #10b981; animation-delay: 2.5s; animation-duration: 4.3s; }
    .c-10 { left: 95%; background: #18529c; animation-delay: 1.9s; animation-duration: 3.7s; }

    @keyframes fall {
        0% { transform: translateY(0) translateX(0) rotate(0deg); opacity: 1; }
        50% { opacity: 0.9; }
        100% { transform: translateY(100vh) translateX(50px) rotate(360deg); opacity: 0; }
    }

    .thankyou-wrapper {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 80px 20px;
        position: relative;
    }

    .thankyou-card {
        position: relative;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.7);
        border-radius: 32px;
        padding: 56px 48px;
        max-width: 550px;
        width: 100%;
        text-align: center;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.02), 0 30px 70px -10px rgba(15, 23, 42, 0.08);
        animation: cardFadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        overflow: hidden;
    }

    @keyframes cardFadeIn {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Ripple Success Icon Wrapper */
    .success-icon-wrapper {
        position: relative;
        width: 120px;
        height: 120px;
        margin: 0 auto 28px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .icon-ring {
        position: absolute;
        border-radius: 50%;
        background: rgba(16, 185, 129, 0.06);
        animation: pulseRing 2.5s infinite cubic-bezier(0.25, 0, 0, 1);
    }

    .ring-1 {
        width: 96px;
        height: 96px;
    }

    .ring-2 {
        width: 120px;
        height: 120px;
        animation-delay: 0.8s;
    }

    @keyframes pulseRing {
        0% { transform: scale(0.9); opacity: 1; }
        100% { transform: scale(1.45); opacity: 0; }
    }

    .check-icon {
        position: relative;
        width: 76px;
        height: 76px;
        background: linear-gradient(135deg, #10b981, #059669);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 38px;
        color: #ffffff;
        box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4);
        z-index: 2;
        animation: popIcon 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
    }

    @keyframes popIcon {
        0% { transform: scale(0); }
        100% { transform: scale(1); }
    }

    .thankyou-title {
        font-size: 34px;
        font-weight: 800;
        background: linear-gradient(135deg, #18529c 0%, #0d4182 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 12px;
        letter-spacing: -0.8px;
    }

    .thankyou-text {
        font-size: 15.5px;
        color: #475569;
        line-height: 1.6;
        font-weight: 500;
        margin-bottom: 32px;
    }

    /* Modern Status Card */
    .status-card {
        background: rgba(255, 255, 255, 0.6);
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.01);
        display: flex;
        flex-direction: column;
        gap: 16px;
        text-align: left;
    }

    .status-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 12px;
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.2s ease;
    }

    .status-row:last-child {
        padding-bottom: 0;
        border-bottom: none;
    }

    .status-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .status-icon {
        width: 32px;
        height: 32px;
        background: #f0f7ff;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #18529c;
        font-size: 14px;
    }

    .status-icon.green {
        background: #f0fdf4;
        color: #16a34a;
    }

    .status-icon.orange {
        background: #fffbeb;
        color: #d97706;
    }

    .status-label {
        font-size: 14px;
        font-weight: 600;
        color: #64748b;
    }

    .status-value {
        font-size: 14px;
        font-weight: 700;
        color: #0f2742;
    }

    .status-value.ref-code {
        font-family: 'Courier New', Courier, monospace;
        font-size: 14.5px;
        letter-spacing: 0.5px;
        background: #f8fafc;
        padding: 4px 10px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }

    .badge-status {
        padding: 5px 12px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }
    
    .badge-status.green {
        background: #f0fdf4;
        color: #16a34a;
        border: 1px solid #bbf7d0;
    }

    .btn-home {
        margin-top: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 15px 30px;
        background: linear-gradient(135deg, #18529c 0%, #0d4182 100%);
        color: #ffffff;
        text-decoration: none;
        border-radius: 14px;
        font-weight: 700;
        font-size: 15.5px;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 4px 14px rgba(24, 82, 156, 0.25);
        border: none;
        width: 100%;
        cursor: pointer;
    }

    .btn-home:hover {
        background: linear-gradient(135deg, #0d4182 0%, #082d5c 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(24, 82, 156, 0.35);
        color: #ffffff;
    }

    .btn-home i {
        transition: transform 0.2s ease;
    }

    .btn-home:hover i {
        transform: scale(1.1);
    }
</style>

<!-- Background Blobs -->
<div class="bg-blob blob-1"></div>
<div class="bg-blob blob-2"></div>

<div class="thankyou-wrapper">
    <div class="thankyou-card">
        <!-- Confetti Overlay -->
        <div class="confetti-container">
            <div class="confetti c-1"></div>
            <div class="confetti c-2"></div>
            <div class="confetti c-3"></div>
            <div class="confetti c-4"></div>
            <div class="confetti c-5"></div>
            <div class="confetti c-6"></div>
            <div class="confetti c-7"></div>
            <div class="confetti c-8"></div>
            <div class="confetti c-9"></div>
            <div class="confetti c-10"></div>
        </div>

        <!-- Ripple Success Icon -->
        <div class="success-icon-wrapper">
            <div class="icon-ring ring-1"></div>
            <div class="icon-ring ring-2"></div>
            <div class="check-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <?php if ($is_contact): ?>
            <!-- TITLE -->
            <div class="thankyou-title">Thank You!</div>

            <!-- MESSAGE -->
            <div class="thankyou-text">
                Your message has been successfully sent.<br>
                Our customer care team will get back to you shortly.
            </div>

            <!-- STATUS BOX -->
            <div class="status-card">
                <div class="status-row">
                    <div class="status-left">
                        <div class="status-icon green">
                            <i class="fa-solid fa-envelope-open-text"></i>
                        </div>
                        <span class="status-label">Enquiry Status</span>
                    </div>
                    <span class="badge-status green">Received</span>
                </div>
                <div class="status-row">
                    <div class="status-left">
                        <div class="status-icon">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <span class="status-label">Expected Response</span>
                    </div>
                    <span class="status-value">Within 24 Hours</span>
                </div>
            </div>
        <?php else: ?>
            <!-- TITLE -->
            <div class="thankyou-title">Thank You!</div>

            <!-- MESSAGE -->
            <div class="thankyou-text">
                Your loan application has been successfully submitted.<br>
                Our verification team is reviewing your details.
            </div>

            <!-- STATUS BOX -->
            <div class="status-card">
                <div class="status-row">
                    <div class="status-left">
                        <div class="status-icon">
                            <i class="fa-solid fa-hashtag"></i>
                        </div>
                        <span class="status-label">Application Ref</span>
                    </div>
                    <span class="status-value ref-code"><?php echo htmlspecialchars($display_ref); ?></span>
                </div>
                <div class="status-row">
                    <div class="status-left">
                        <div class="status-icon orange">
                            <i class="fa-solid fa-hourglass-half"></i>
                        </div>
                        <span class="status-label">Application Status</span>
                    </div>
                    <span class="badge-status">Under Review</span>
                </div>
                <div class="status-row">
                    <div class="status-left">
                        <div class="status-icon">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <span class="status-label">Expected Response</span>
                    </div>
                    <span class="status-value">Within 24 Hours</span>
                </div>
            </div>
        <?php endif; ?>

        <!-- BUTTON -->
        <a href="index" class="btn-home">
            <i class="fa-solid fa-house"></i> Back to Home
        </a>
    </div>
</div>

<?php
include 'includes/footer.php';
?>