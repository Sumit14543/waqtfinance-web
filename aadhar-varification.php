<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/loan_flow.php';

$application = loan_flow_require_application($conn);
$nextUrl = loan_flow_next_url($conn, $application);
if (basename($nextUrl) !== 'aadhar-varification.php') {
    header('Location: ' . $nextUrl);
    exit;
}

include 'includes/header.php';
include 'includes/main-header.php';

$aadhaarError = $_SESSION['aadhaar_error'] ?? '';
unset($_SESSION['aadhaar_error']);
?>

<link rel="stylesheet" href="assets/css/loan-flow.css">

<div class="lf-page">
    <div class="lf-container">

        <!-- Progress Bar -->
        <div class="lf-progress">
            <div class="lf-progress-track">
                <div class="lf-step done">
                    <div class="lf-step-dot"><i class="fa-solid fa-check"></i></div>
                    <div class="lf-step-label">Basic<br>Details</div>
                </div>
                <div class="lf-step done">
                    <div class="lf-step-dot"><i class="fa-solid fa-check"></i></div>
                    <div class="lf-step-label">PAN<br>Verify</div>
                </div>
                <div class="lf-step active">
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
                    <i class="fa-solid fa-fingerprint"></i>
                </div>
                <h1 class="lf-card-title">Complete Your KYC</h1>
                <p class="lf-card-sub">Your data is completely secure with us</p>
            </div>

            <div class="lf-card-body">
                <?php if ($aadhaarError): ?>
                    <div class="lf-alert lf-alert-error">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span><?php echo htmlspecialchars((string) $aadhaarError, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                <?php endif; ?>

                <img src="assets/images/aadhar-img.png" class="lf-doc-preview-img"
                    style="object-fit:contain; background:#f8fafc; padding:10px;">

                <div class="lf-doc-format">
                    Example Aadhaar: 1234 5678 9012
                </div>

                <div class="lf-form-group">
                    <label class="lf-label" for="aadhaarInput">Enter Aadhaar Number</label>
                    <input type="text" id="aadhaarInput" class="lf-input"
                        placeholder="Enter your 12-digit Aadhaar number" maxlength="14" inputmode="numeric"
                        style="letter-spacing:1px; font-size:16px;">
                    <small id="aadhaarError" class="lf-error-text"></small>
                </div>

                <button class="lf-btn lf-btn-primary" id="aadhaarBtn">
                    Verify & Continue <i class="fa-solid fa-arrow-right"></i>
                </button>

            </div>

            <div class="lf-trust">
                <div class="lf-trust-item"><i class="fa-solid fa-shield-halved"></i> UIDAI Compliant</div>
                <div class="lf-trust-item"><i class="fa-solid fa-lock"></i> AES-256 Encryption</div>
            </div>
        </div>

    </div>
</div>

<script>
    const input = document.getElementById("aadhaarInput");
    const btn = document.getElementById("aadhaarBtn");
    const errorBox = document.getElementById("aadhaarError");

    let isVerified = false;

    // ================= FORMAT =================
    input.addEventListener("input", function () {

        let value = this.value.replace(/[^0-9]/g, '');
        value = value.slice(0, 12);

        let formatted = value.replace(/(\d{4})(?=\d)/g, '$1 ');
        this.value = formatted;

    });

    // ================= VERHOEFF CHECK =================
    function isValidAadhaar(num) {
        const d = [
            [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
            [1, 2, 3, 4, 0, 6, 7, 8, 9, 5],
            [2, 3, 4, 0, 1, 7, 8, 9, 5, 6],
            [3, 4, 0, 1, 2, 8, 9, 5, 6, 7],
            [4, 0, 1, 2, 3, 9, 5, 6, 7, 8],
            [5, 9, 8, 7, 6, 0, 4, 3, 2, 1],
            [6, 5, 9, 8, 7, 1, 0, 4, 3, 2],
            [7, 6, 5, 9, 8, 2, 1, 0, 4, 3],
            [8, 7, 6, 5, 9, 3, 2, 1, 0, 4],
            [9, 8, 7, 6, 5, 4, 3, 2, 1, 0]
        ];

        const p = [
            [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
            [1, 5, 7, 6, 2, 8, 3, 0, 9, 4],
            [5, 8, 0, 3, 7, 9, 6, 1, 4, 2],
            [8, 9, 1, 6, 0, 4, 3, 5, 2, 7],
            [9, 4, 5, 3, 1, 2, 6, 8, 7, 0],
            [4, 2, 8, 6, 5, 7, 3, 9, 0, 1],
            [2, 7, 9, 3, 8, 0, 6, 4, 1, 5],
            [7, 0, 4, 6, 9, 1, 3, 2, 5, 8]
        ];

        let c = 0;
        const arr = num.split('').reverse().map(Number);

        for (let i = 0; i < arr.length; i++) {
            c = d[c][p[i % 8][arr[i]]];
        }

        return c === 0;
    }

    // ================= SUBMIT =================
    btn.addEventListener("click", function () {

        let rawValue = input.value.replace(/\s/g, '');

        input.classList.remove('lf-error-border');

        // validation
        if (rawValue.length !== 12) {
            errorBox.innerHTML = '<div class="lf-verify-error"><i class="fa-solid fa-circle-exclamation"></i> Enter 12-digit Aadhaar number</div>';
            input.classList.add('lf-error-border');
            return;
        }

        if (!isValidAadhaar(rawValue)) {
            errorBox.innerHTML = '<div class="lf-verify-error"><i class="fa-solid fa-circle-exclamation"></i> Invalid Aadhaar number</div>';
            input.classList.add('lf-error-border');
            return;
        }

        errorBox.innerText = "";

        if (btn.disabled) return;

        btn.disabled = true;
        btn.innerHTML = "<div class='lf-spinner'></div> Verifying...";

        const formData = new FormData();
        formData.append('aadhaar', rawValue);

        fetch('api/apply/save_aadhar_detail.php', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': window.WAQT_CSRF_TOKEN || ''
            },
            body: formData
        })
            .then(res => {
                if (!res.ok) throw new Error("Server error");
                return res.json();
            })
            .then(data => {

                if (data.status === 'success') {

                    btn.innerHTML = "<div class='lf-spinner'></div> Redirecting...";
                    errorBox.innerHTML = '<div class="lf-verify-success"><i class="fa-solid fa-circle-check"></i> Redirecting to Aadhaar Verification...</div>';

                    setTimeout(() => {
                        window.location.href = data.message; 
                    }, 800);

                } else {
                    btn.disabled = false;
                    btn.innerHTML = 'Verify & Continue <i class="fa-solid fa-arrow-right"></i>';
                    errorBox.innerHTML = '<div class="lf-verify-error"><i class="fa-solid fa-circle-exclamation"></i> ' + data.message + '</div>';
                }

            })
            .catch((err) => {
                console.error(err);

                btn.disabled = false;
                btn.innerHTML = 'Verify & Continue <i class="fa-solid fa-arrow-right"></i>';

                errorBox.innerHTML = '<div class="lf-verify-error"><i class="fa-solid fa-wifi"></i> Network error</div>';
            });
    })
</script>

<?php include 'includes/footer.php' ?>
