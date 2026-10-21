<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/loan_flow.php';

$application = loan_flow_require_application($conn);
$nextUrl = loan_flow_next_url($conn, $application);
if (basename($nextUrl) !== 'mobile-otp.php') {
    header('Location: ' . $nextUrl);
    exit;
}

include 'includes/header.php';
include 'includes/main-header.php';
?>

<link rel="stylesheet" href="assets/css/loan-flow.css">

<style>
    /* Processing Overlay */
    .wf-processing-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 10000;
    }

    .wf-spinner-saas {
        width: 80px;
        height: 80px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid var(--wf-blue);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 20px;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .wf-processing-text {
        font-size: 18px;
        font-weight: 700;
        color: var(--wf-primary);
        text-align: center;
    }

    .lf-resend-active {
        display: inline-block;
        font-weight: 700;
        text-decoration: underline;
    }

    .lf-contact-line {
        align-items: center;
        display: inline-flex;
        gap: 8px;
        justify-content: center;
    }

    .lf-contact-edit-btn {
        align-items: center;
        background: #eef4ff;
        border: 1px solid #d9e6ff;
        border-radius: 999px;
        color: var(--wf-blue);
        cursor: pointer;
        display: inline-flex;
        height: 28px;
        justify-content: center;
        width: 28px;
    }

    .lf-contact-edit-panel {
        border-top: 1px solid #e7edf5;
        display: none;
        margin-top: 18px;
        padding-top: 18px;
    }

    .lf-contact-edit-panel.active {
        display: block;
    }

    .lf-contact-actions {
        display: flex;
        gap: 10px;
        margin-top: 14px;
    }

    .lf-contact-actions .lf-btn {
        flex: 1;
    }

    .lf-btn-secondary {
        background: #f3f6fb;
        color: var(--wf-primary);
    }
</style>

<div class="wf-processing-overlay" id="processingOverlay">
    <div class="wf-spinner-saas"></div>
    <div class="wf-processing-text">
        Analyzing your credit profile...<br>
        <small class="text-muted fw-normal">Please do not refresh this page.</small>
    </div>
</div>

<?php
$mobile = $_SESSION['mobile_number'] ?? ($application['mobile'] ?? '');
$email = $_SESSION['user_email'] ?? ($application['email'] ?? '');
$otpRemaining = max(0, (int) ($_SESSION['otp_expires_at'] ?? 0) - time());
$hasOtpBeenSent = !empty($_SESSION['otp_last_sent_at']);
$otpChannels = $_SESSION['otp_channels'] ?? [];
$channelText = 'your registered mobile number';
if (is_array($otpChannels) && count($otpChannels) > 0) {
    $channelText = implode(' and ', array_map(static fn($channel) => (string) $channel, $otpChannels));
} elseif ($email !== '') {
    $channelText = 'your registered mobile number/email';
}
?>

<div class="lf-page">
    <div class="lf-container">

        <!-- Progress Bar -->
        <div class="lf-progress">
            <div class="lf-progress-track">
                <div class="lf-step active">
                    <div class="lf-step-dot">1</div>
                    <div class="lf-step-label">Basic<br>Details</div>
                </div>
                <div class="lf-step">
                    <div class="lf-step-dot">2</div>
                    <div class="lf-step-label">PAN<br>Verify</div>
                </div>
                <div class="lf-step">
                    <div class="lf-step-dot">3</div>
                    <div class="lf-step-label">Aadhaar<br>Verify</div>
                </div>
                <div class="lf-step">
                    <div class="lf-step-dot">4</div>
                    <div class="lf-step-label">Work<br>Details</div>
                </div>
                <div class="lf-step">
                    <div class="lf-step-dot">5</div>
                    <div class="lf-step-label">Bank<br>Details</div>
                </div>
                <div class="lf-step">
                    <div class="lf-step-dot">6</div>
                    <div class="lf-step-label">References</div>
                </div>
                <div class="lf-step">
                    <div class="lf-step-dot">7</div>
                    <div class="lf-step-label">Upload<br>Docs</div>
                </div>
            </div>
        </div>

        <div class="lf-card">
            <div class="lf-card-header">
                <div class="lf-card-icon">
                    <i class="fa-solid fa-mobile-screen-button"></i>
                </div>
                <h1 class="lf-card-title">Verify Mobile Number</h1>
                <p class="lf-card-sub">
                    <span id="otpChannelText">A 6-digit code has been sent via <?php echo htmlspecialchars($channelText, ENT_QUOTES, 'UTF-8'); ?></span><br>
                    <span class="lf-contact-line">
                        <strong id="contactMobileText" style="color:var(--wf-primary);"><?php echo htmlspecialchars((string) $mobile, ENT_QUOTES, 'UTF-8'); ?></strong>
                        <button type="button" class="lf-contact-edit-btn" id="editContactBtn" aria-label="Edit contact details">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                    </span>
                    <?php if ($email): ?>
                        <br><strong id="contactEmailText" style="color:var(--wf-primary);"><?php echo htmlspecialchars((string) $email, ENT_QUOTES, 'UTF-8'); ?></strong>
                    <?php endif; ?>
                </p>
            </div>

            <div class="lf-card-body">
                <div class="lf-contact-edit-panel" id="contactEditPanel">
                    <div class="lf-form-group">
                        <label class="lf-label" for="editMobile">Mobile Number</label>
                        <input type="tel" id="editMobile" class="lf-input" inputmode="numeric" maxlength="10" value="<?php echo htmlspecialchars((string) $mobile, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="lf-form-group">
                        <label class="lf-label" for="editEmail">Email Address</label>
                        <input type="email" id="editEmail" class="lf-input" value="<?php echo htmlspecialchars((string) $email, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="lf-contact-actions">
                        <button type="button" class="lf-btn lf-btn-primary" id="saveContactBtn">Send New OTP</button>
                        <button type="button" class="lf-btn lf-btn-secondary" id="cancelContactBtn">Cancel</button>
                    </div>
                </div>

                <div class="lf-otp-row">
                    <input class="lf-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                    <input class="lf-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                    <input class="lf-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                    <input class="lf-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                    <input class="lf-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                    <input class="lf-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                </div>

                <div style="text-align: center;">
                    <small id="otpError" class="lf-error-text"
                        style="display:inline-block; margin-bottom:12px;"></small>
                </div>

                <div class="lf-timer-row">
                    <div class="lf-timer-badge">
                        <i class="fa-regular fa-clock"></i> <span id="otpTimer">01:00</span>
                    </div>
                </div>

                <button class="lf-btn lf-btn-primary" id="otpSubmitBtn">
                    Verify & Continue
                </button>

                <div class="lf-resend-row" style="margin-top: 20px;">
                    Didn't receive the code? <a href="javascript:void(0)" id="resendBtn">Resend OTP</a>
                </div>

            </div>

            <div class="lf-trust">
                <div class="lf-trust-item"><i class="fa-solid fa-shield-halved"></i> 100% Encrypted</div>
                <div class="lf-trust-item"><i class="fa-solid fa-lock"></i> Secure Connection</div>
            </div>
        </div>

    </div>
</div>

<script>
    const inputs = document.querySelectorAll(".lf-otp-box");
    const submitBtn = document.getElementById("otpSubmitBtn");
    const resendBtn = document.getElementById("resendBtn");
    const timerEl = document.getElementById("otpTimer");
    const otpRow = document.querySelector(".lf-otp-row");
    const timerRow = document.querySelector(".lf-timer-row");
    const otpChannelText = document.getElementById("otpChannelText");
    const editContactBtn = document.getElementById("editContactBtn");
    const contactEditPanel = document.getElementById("contactEditPanel");
    const editMobile = document.getElementById("editMobile");
    const editEmail = document.getElementById("editEmail");
    const saveContactBtn = document.getElementById("saveContactBtn");
    const cancelContactBtn = document.getElementById("cancelContactBtn");
    const contactMobileText = document.getElementById("contactMobileText");
    const contactEmailText = document.getElementById("contactEmailText");
    const initialOtpRemaining = <?php echo (int) $otpRemaining; ?>;
    const hasOtpBeenSent = <?php echo $hasOtpBeenSent ? 'true' : 'false'; ?>;

    let countdown = 60;
    let timerInterval;
    let otpEntryMode = initialOtpRemaining > 0 || !hasOtpBeenSent;

    // ================= OTP INPUT HANDLING =================

    // Only numbers + auto move + enter submit
    inputs.forEach((input, index) => {

        input.addEventListener("input", function () {
            this.value = this.value.replace(/[^0-9]/g, '');

            if (this.value) {
                this.classList.add('filled');
            } else {
                this.classList.remove('filled');
            }

            if (this.value && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        // Backspace focus
        input.addEventListener("keydown", function (e) {
            if (e.key === "Backspace" && !this.value && index > 0) {
                inputs[index - 1].focus();
                inputs[index - 1].classList.remove('filled');
            }

            // Enter submit
            if (e.key === "Enter") {
                submitOTP();
            }
        });
    });

    // Paste full OTP
    inputs[0].addEventListener("paste", function (e) {
        let paste = e.clipboardData.getData("text").replace(/\D/g, '');
        if (paste.length === 6) {
            inputs.forEach((input, i) => {
                input.value = paste[i];
                input.classList.add('filled');
            });
            submitOTP();
        }
    });

    // auto focus first input
    setTimeout(() => {
        if (inputs.length > 0) inputs[0].focus();
    }, 500);

    // ================= HELPER =================

    function getOTP() {
        return Array.from(inputs).map(i => i.value).join('');
    }

    function clearOTPInputs() {
        inputs.forEach(i => {
            i.value = '';
            i.classList.remove('filled');
        });
        if (inputs.length > 0) inputs[0].focus();
    }

    function setVerifyEnabled(isEnabled) {
        submitBtn.disabled = !isEnabled;
        submitBtn.style.opacity = isEnabled ? "1" : "0.65";
        submitBtn.style.cursor = isEnabled ? "pointer" : "not-allowed";
    }

    function setOtpEntryMode(isEntryMode) {
        otpEntryMode = isEntryMode;
        if (otpRow) otpRow.hidden = !isEntryMode;
        if (timerRow) timerRow.hidden = !isEntryMode;
        submitBtn.innerHTML = isEntryMode ? "Verify & Continue" : "Send New OTP";
        setVerifyEnabled(true);
    }

    function showError(msg, type = "error") {
        const el = document.getElementById("otpError");
        el.innerText = msg;
        el.style.color = type === "success" ? "var(--wf-success)" : "var(--wf-error)";
    }

    function updateOtpChannels(channels) {
        if (!otpChannelText || !Array.isArray(channels) || channels.length === 0) return;
        otpChannelText.innerText = `A 6-digit code has been sent via ${channels.join(" and ")}`;
    }

    function setContactEditMode(isEditing) {
        if (contactEditPanel) contactEditPanel.classList.toggle("active", isEditing);
        if (otpRow) otpRow.hidden = isEditing || !otpEntryMode;
        if (timerRow) timerRow.hidden = isEditing || !otpEntryMode;
        submitBtn.hidden = isEditing;
        document.querySelector(".lf-resend-row").hidden = isEditing;
        if (isEditing) {
            showError("");
            clearInterval(timerInterval);
            setVerifyEnabled(false);
            if (editMobile) editMobile.focus();
        } else if (otpEntryMode) {
            setVerifyEnabled(true);
        } else {
            setVerifyEnabled(true);
        }
    }

    function validateContactInputs() {
        const mobile = (editMobile.value || "").replace(/\D/g, "");
        editMobile.value = mobile.slice(0, 10);

        if (!/^[6-9]\d{9}$/.test(editMobile.value)) {
            showError("Enter a valid 10-digit mobile number.");
            return false;
        }

        if (!/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,10}$/.test((editEmail.value || "").trim())) {
            showError("Enter a valid email address.");
            return false;
        }

        return true;
    }

    function updateContactDetails() {
        showError("");
        if (!validateContactInputs()) return;

        saveContactBtn.disabled = true;
        saveContactBtn.innerHTML = "<div class='lf-spinner'></div> Updating...";

        const formData = new FormData();
        formData.append("mobile", editMobile.value.trim());
        formData.append("email", editEmail.value.trim());

        fetch("api/apply/update_otp_contact.php", {
            method: "POST",
            headers: {
                "X-CSRF-Token": window.WAQT_CSRF_TOKEN || "<?php echo $_SESSION['csrf_token'] ?? ''; ?>"
            },
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.status !== "success") {
                    throw new Error(data.message || "Unable to update contact details.");
                }

                if (contactMobileText) contactMobileText.innerText = data.mobile || editMobile.value.trim();
                if (contactEmailText) contactEmailText.innerText = data.email || editEmail.value.trim();
                setContactEditMode(false);
                setOtpEntryMode(false);
                sendOTP();
            })
            .catch(error => {
                showError(error.message || "Unable to update contact details.");
            })
            .finally(() => {
                saveContactBtn.disabled = false;
                saveContactBtn.innerHTML = "Send New OTP";
            });
    }

    // ================= SUBMIT =================

    function submitOTP() {
        if (!otpEntryMode) {
            sendOTP();
            return;
        }

        const otp = getOTP();

        if (otp.length !== 6) {
            showError("Enter complete 6-digit OTP");
            return;
        }

        showError("");

        submitBtn.disabled = true;
        submitBtn.innerHTML = "<div class='lf-spinner'></div> Verifying...";

        const formData = new FormData();
        formData.append('otp', otp);

        fetch('api/apply/verify_otp.php', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': window.WAQT_CSRF_TOKEN || "<?php echo $_SESSION['csrf_token'] ?? ''; ?>"
            },
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('processingOverlay').style.display = 'flex';
                    setTimeout(() => window.location.href = data.redirect || "apply-for-loan.php", 1200);
                } else {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = "Verify & Continue";
                    showError(data.message || "Invalid OTP");
                }
            })
            .catch((err) => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = "Verify & Continue";
                showError("Network error");
                console.error(err);
            });
    }

    submitBtn.addEventListener("click", submitOTP);

    // ================= TIMER =================

    function startTimer(duration = 60) {
        countdown = duration;
        if (countdown <= 0) {
            expireTimer();
            return;
        }

        let startMin = Math.floor(countdown / 60);
        let startSec = countdown % 60;
        timerEl.innerText = (startMin < 10 ? "0" + startMin : startMin) + ":" + (startSec < 10 ? "0" + startSec : startSec);

        clearInterval(timerInterval);

        timerInterval = setInterval(() => {
            countdown--;

            let m = Math.floor(countdown / 60);
            let s = countdown % 60;
            timerEl.innerText = (m < 10 ? "0" + m : m) + ":" + (s < 10 ? "0" + s : s);

            if (countdown <= 0) {
                expireTimer();
            }
        }, 1000);
    }

    function expireTimer() {
        clearInterval(timerInterval);
        countdown = 0;
        timerEl.innerText = "00:00";
        clearOTPInputs();
        showError("OTP has expired. Please resend a new code.");
        setOtpEntryMode(false);
        resendBtn.style.pointerEvents = "auto";
        resendBtn.style.opacity = "1";
        resendBtn.classList.add("lf-resend-active");
    }

    // ================= SEND OTP =================

    function sendOTP(isInit = false) {
        showError(isInit ? "Sending OTP now..." : "Sending new OTP...", "success");
        setVerifyEnabled(false);
        resendBtn.style.pointerEvents = "none";
        resendBtn.style.opacity = "0.5";
        resendBtn.classList.remove("lf-resend-active");

        fetch('api/apply/send_otp.php', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': window.WAQT_CSRF_TOKEN || "<?php echo $_SESSION['csrf_token'] ?? ''; ?>"
            }
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' || data.status === 'partial') {
                    if (!isInit) showError(data.message || "OTP sent", "success");
                    updateOtpChannels(data.channels);
                    setOtpEntryMode(true);
                    clearOTPInputs();
                    setVerifyEnabled(true);
                    startTimer(data.retry_after || 60);
                } else if (data.retry_after) {
                    if (!isInit) showError(data.message || "Please wait before resending");
                    setOtpEntryMode(true);
                    setVerifyEnabled(true);
                    startTimer(data.retry_after);
                } else {
                    showError(data.message || "Failed to send OTP");
                    setVerifyEnabled(true);
                    resendBtn.style.pointerEvents = "auto";
                    resendBtn.style.opacity = "1";
                }
            })
            .catch(() => {
                showError("Network error");
                setVerifyEnabled(true);
                resendBtn.style.pointerEvents = "auto";
                resendBtn.style.opacity = "1";
            });
    }
    // ================= RESEND =================

    resendBtn.addEventListener("click", function () {
        if (countdown > 0) return;
        clearOTPInputs();
        resendBtn.innerText = "Resend OTP";
        resendBtn.style.pointerEvents = "none";
        resendBtn.style.opacity = "0.5";
        sendOTP();
    });

    if (editMobile) {
        editMobile.addEventListener("input", function () {
            this.value = this.value.replace(/\D/g, "").slice(0, 10);
        });
    }

    if (editContactBtn) {
        editContactBtn.addEventListener("click", function () {
            setContactEditMode(true);
        });
    }

    if (cancelContactBtn) {
        cancelContactBtn.addEventListener("click", function () {
            setContactEditMode(false);
            if (countdown > 0 && otpEntryMode) {
                startTimer(countdown);
            }
        });
    }

    if (saveContactBtn) {
        saveContactBtn.addEventListener("click", updateContactDetails);
    }

    // ================= INIT =================
    if (initialOtpRemaining > 0) {
        resendBtn.style.pointerEvents = "none";
        resendBtn.style.opacity = "0.5";
        setOtpEntryMode(true);
        setVerifyEnabled(true);
        startTimer(initialOtpRemaining);
    } else {
        if (!hasOtpBeenSent) {
            sendOTP(true);
        } else {
            expireTimer();
        }
    }
</script>

<?php include 'includes/footer.php' ?>
