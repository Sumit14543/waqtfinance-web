<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/loan_flow.php';

$application = loan_flow_require_application($conn);
$nextUrl = loan_flow_next_url($conn, $application);
if (basename($nextUrl) !== 'pan-verification.php') {
    header('Location: ' . $nextUrl);
    exit;
}

include 'includes/header.php';
include 'includes/main-header.php';
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
                <div class="lf-step active">
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
                    <i class="fa-regular fa-id-card"></i>
                </div>
                <h1 class="lf-card-title">PAN Verification</h1>
                <p class="lf-card-sub">Your data is completely secure with us</p>
            </div>

            <div class="lf-card-body">
                
                <img src="assets/images/pan-card-img.jpg" alt="PAN Preview" class="lf-doc-preview-img">

                <div class="lf-doc-format">
                    Example PAN: ABCDE1234F
                </div>

                <div class="lf-form-group">
                    <label class="lf-label">PAN Number</label>
                    <div class="lf-verify-row">
                        <input type="text" id="panv-input" class="lf-input" placeholder="ABCDE1234F" maxlength="10">
                        <button type="button" id="verifyPanBtn" class="lf-verify-btn">Verify</button>
                    </div>
                    <div id="panv-error" style="display:none; margin-top:8px;"></div>
                </div>

                <div class="lf-divider"></div>

                <div class="lf-form-group">
                    <label class="lf-label" for="full_name">Name (As per PAN)</label>
                    <input type="text" class="lf-input" id="full_name" placeholder="Enter your full name">
                </div>

                <div class="lf-form-group">
                    <label class="lf-label" for="dob">Date of Birth</label>
                    <input type="date" id="dob" class="lf-input">
                </div>

                <div class="lf-check-wrap mb-4">
                    <input type="checkbox" id="panv-terms">
                    <label class="lf-check-label" for="panv-terms">
                        I accept <span>Terms & Conditions</span>
                    </label>
                </div>

                <button class="lf-btn lf-btn-primary panv-button" onclick="panvSubmit()">
                    Continue <i class="fa-solid fa-arrow-right"></i>
                </button>

            </div>
            
            <div class="lf-trust">
                <div class="lf-trust-item"><i class="fa-solid fa-shield-halved"></i> Govt Approved</div>
                <div class="lf-trust-item"><i class="fa-solid fa-lock"></i> Secure Verification</div>
            </div>
        </div>

    </div>
</div>

<script>
    const panInput = document.getElementById("panv-input");
    const verifyBtn = document.getElementById("verifyPanBtn");
    const nameInput = document.getElementById("full_name");
    const dobInput = document.getElementById("dob");
    const errorBox = document.getElementById("panv-error");

    let isPanVerified = false;

    // PAN format validation
    function isValidPAN(pan) {
        return /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/.test(pan);
    }

    // Verify button click
    verifyBtn.addEventListener("click", function () {

        const pan = panInput.value.toUpperCase().trim();

        if (!isValidPAN(pan)) {
            errorBox.innerHTML = '<div class="lf-verify-error"><i class="fa-solid fa-circle-exclamation"></i> Invalid PAN format</div>';
            errorBox.style.display = "block";
            return;
        }

        errorBox.style.display = "none";

        verifyBtn.disabled = true;
        verifyBtn.innerHTML = "<div class='lf-spinner'></div>";

        const formData = new FormData();
        formData.append("pan", pan);

        fetch("api/apply/verify_pan.php", {
            method: "POST",
            headers: {
                'X-CSRF-Token': window.WAQT_CSRF_TOKEN || ''
            },
            body: formData
        })
            .then(res => res.json())
            .then(data => {

                if (data.status === "success") {

                    isPanVerified = true;

                    // Fill data
                    nameInput.value = data.full_name;
                    dobInput.value = data.dob;

                    // Lock fields
                    nameInput.readOnly = true;
                    dobInput.readOnly = true;
                    panInput.readOnly = true;

                    // UI update
                    panInput.classList.add("lf-verified");

                    errorBox.innerHTML = '<div class="lf-verify-success"><i class="fa-solid fa-circle-check"></i> PAN Verified Successfully</div>';
                    errorBox.style.display = "block";

                    verifyBtn.innerHTML = "<i class='fa-solid fa-check'></i>";

                } else {
                    verifyBtn.disabled = false;
                    verifyBtn.innerHTML = "Verify";

                    errorBox.innerHTML = '<div class="lf-verify-error"><i class="fa-solid fa-circle-exclamation"></i> ' + data.message + '</div>';
                    errorBox.style.display = "block";
                }

            })
            .catch(() => {
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = "Verify";

                errorBox.innerHTML = '<div class="lf-verify-error"><i class="fa-solid fa-wifi"></i> API error</div>';
                errorBox.style.display = "block";
            });

    });
    
    function panvSubmit() {

        if (!isPanVerified) {
            alert("Please verify PAN first");
            return;
        }

        const terms = document.getElementById("panv-terms").checked;

        if (!terms) {
            alert("Please accept Terms & Conditions");
            return;
        }

        const btn = document.querySelector(".panv-button");
        btn.disabled = true;
        btn.innerHTML = "<div class='lf-spinner'></div> Saving...";

        const formData = new FormData();
        formData.append('pan', panInput.value);
        formData.append('full_name', nameInput.value);
        formData.append('dob', dobInput.value);

        fetch('api/apply/save_pan_data.php', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': window.WAQT_CSRF_TOKEN || ''
            },
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = "aadhar-varification.php";
                } else {
                    btn.disabled = false;
                    btn.innerHTML = 'Continue <i class="fa-solid fa-arrow-right"></i>';
                    alert(data.message);
                }
            });
    }
</script>

<?php include 'includes/footer.php' ?>
