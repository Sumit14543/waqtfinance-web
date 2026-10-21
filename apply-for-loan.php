<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/loan_flow.php';

$application = loan_flow_require_application($conn);
$nextUrl = loan_flow_next_url($conn, $application);
if (basename($nextUrl) !== 'apply-for-loan.php') {
    header('Location: ' . $nextUrl);
    exit;
}

$page_title = "Apply for a Loan | Step-by-Step Application | Waqt Finance";
$meta_description = "Start your loan application process with Waqt Finance. Choose your loan type and amount to get started with our quick and secure application form.";
$meta_keywords = "loan application, apply for personal loan, business loan application, loan process India";
$canonical_url = "https://www.waqtfinance.com/apply-for-loan";
$meta_robots = "noindex, follow";

include 'includes/header.php';
include 'includes/main-header.php';

function loan_req_e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<link rel="stylesheet" href="assets/css/loan-flow.css">

<div class="lf-page">
    <div class="lf-container">
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
                    <i class="fa-solid fa-indian-rupee-sign"></i>
                </div>
                <h1 class="lf-card-title">Loan Requirement</h1>
                <p class="lf-card-sub">Share the amount and purpose for your payday loan.</p>
            </div>

            <div class="lf-card-body">
                <form id="loanDetailsForm">
                    <div class="lf-form-group">
                        <label class="lf-label" for="lnAmount">Loan Amount <span class="lf-req">*</span></label>
                        <div class="lf-input-prefix-wrap" id="amountWrap">
                            <div class="lf-input-prefix">₹</div>
                            <input type="text" id="lnAmount" class="lf-input" placeholder="Enter amount" inputmode="numeric" value="<?php echo !empty($application['loan_amount']) ? loan_req_e(number_format((float) $application['loan_amount'], 0, '.', ',')) : ''; ?>">
                        </div>
                        <small id="amountError" class="lf-error-text"></small>
                    </div>

                    <div class="lf-form-group">
                        <label class="lf-label" for="purpose">Purpose of Loan <span class="lf-req">*</span></label>
                        <div class="lf-select-wrap">
                            <select class="lf-select" name="purpose" id="purpose">
                                <option value="" selected>Select purpose</option>
                                <?php foreach (['Marriage', 'Tuition fees', 'Education', 'Medical / Health'] as $purpose): ?>
                                    <option value="<?php echo loan_req_e($purpose); ?>" <?php echo ($application['loan_purpose'] ?? '') === $purpose ? 'selected' : ''; ?>><?php echo loan_req_e($purpose); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <small id="purposeError" class="lf-error-text"></small>
                    </div>

                    <div class="lf-form-group">
                        <label class="lf-label">Do you have any running loan? <span class="lf-req">*</span></label>
                        <div class="lf-emp-group">
                            <input type="radio" id="runningLoanYes" name="has_running_loan" value="1" <?php echo (string) ($application['has_running_loan'] ?? '1') === '1' ? 'checked' : ''; ?>>
                            <label for="runningLoanYes">Yes</label>

                            <input type="radio" id="runningLoanNo" name="has_running_loan" value="0" <?php echo (string) ($application['has_running_loan'] ?? '') === '0' ? 'checked' : ''; ?>>
                            <label for="runningLoanNo">No</label>
                        </div>
                        <small id="runningLoanError" class="lf-error-text"></small>
                    </div>

                    <button type="submit" class="lf-btn lf-btn-primary" id="continueBtn">
                        Continue <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>
            </div>

            <div class="lf-trust">
                <div class="lf-trust-item"><i class="fa-solid fa-shield-halved"></i> 100% Encrypted</div>
                <div class="lf-trust-item"><i class="fa-solid fa-lock"></i> Secure Connection</div>
            </div>
        </div>
    </div>
</div>

<script>
    window.WAQT_CSRF_TOKEN = "<?= $_SESSION['csrf_token'] ?? '' ?>";

    const loanForm = document.getElementById("loanDetailsForm");
    const lnAmount = document.getElementById("lnAmount");
    const amountWrap = document.getElementById("amountWrap");
    const continueBtn = document.getElementById("continueBtn");

    lnAmount.addEventListener("input", () => {
        let value = lnAmount.value.replace(/[^0-9]/g, '').slice(0, 7);
        lnAmount.value = value ? Number(value).toLocaleString('en-IN') : "";
    });

    function clearLoanErrors() {
        document.querySelectorAll(".lf-error-text").forEach(el => el.textContent = "");
        document.querySelectorAll(".lf-input, .lf-select").forEach(el => el.classList.remove("lf-error-border"));
        amountWrap.classList.remove("lf-error-border");
    }

    loanForm.addEventListener("submit", function (e) {
        e.preventDefault();
        clearLoanErrors();

        const amount = lnAmount.value.replace(/,/g, '');
        const purpose = document.getElementById("purpose").value;
        const loanRadio = document.querySelector('input[name="has_running_loan"]:checked');
        let isValid = true;

        if (!amount || isNaN(amount) || Number(amount) <= 0) {
            document.getElementById("amountError").textContent = "Please enter a valid loan amount";
            amountWrap.classList.add("lf-error-border");
            isValid = false;
        }

        if (!purpose) {
            document.getElementById("purposeError").textContent = "Please select loan purpose";
            document.getElementById("purpose").classList.add("lf-error-border");
            isValid = false;
        }

        if (!loanRadio) {
            document.getElementById("runningLoanError").textContent = "Please select running loan option";
            isValid = false;
        }

        if (!isValid) return;
        if (continueBtn.disabled) return;

        continueBtn.disabled = true;
        continueBtn.innerHTML = "<div class='lf-spinner'></div> Saving...";

        const formData = new FormData();
        formData.append('amount', amount);
        formData.append('purpose', purpose);
        formData.append('has_running_loan', loanRadio.value);

        fetch('api/apply/loan-apply-details.php', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': window.WAQT_CSRF_TOKEN || ''
            },
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = "basic-details-form.php";
                } else {
                    continueBtn.disabled = false;
                    continueBtn.innerHTML = "Continue <i class='fa-solid fa-arrow-right'></i>";
                    alert(data.message || 'Failed to save details');
                }
            })
            .catch(() => {
                continueBtn.disabled = false;
                continueBtn.innerHTML = "Continue <i class='fa-solid fa-arrow-right'></i>";
                alert('Network error. Please try again.');
            });
    });
</script>

<?php include 'includes/footer.php' ?>
