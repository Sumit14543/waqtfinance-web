<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/loan_flow.php';

$application = loan_flow_require_application($conn);
$nextUrl = loan_flow_next_url($conn, $application);
if (basename($nextUrl) !== 'bank-details.php') {
    header('Location: ' . $nextUrl);
    exit;
}

include 'includes/header.php';
include 'includes/main-header.php';

function bank_e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<link rel="stylesheet" href="assets/css/loan-flow.css">

<div class="lf-page">
    <div class="lf-container">
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
                <div class="lf-step done">
                    <div class="lf-step-dot"><i class="fa-solid fa-check"></i></div>
                    <div class="lf-step-label">Aadhaar<br>Verify</div>
                </div>
                <div class="lf-step done">
                    <div class="lf-step-dot"><i class="fa-solid fa-check"></i></div>
                    <div class="lf-step-label">Work<br>Details</div>
                </div>
                <div class="lf-step active">
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
                    <i class="fa-solid fa-building-columns"></i>
                </div>
                <h1 class="lf-card-title">Bank Details</h1>
                <p class="lf-card-sub">Add the account where disbursal can be processed.</p>
            </div>

            <div class="lf-card-body">
                <form id="bankForm">
                    
                     <div class="lf-form-group">
                        <label class="lf-label" for="ifsc_code">IFSC Code <span class="lf-req">*</span></label>
                        <input type="text" id="ifsc_code" class="lf-input" placeholder="ABCD0123456" maxlength="11" autocomplete="off" value="<?php echo bank_e($application['ifsc_code'] ?? ''); ?>">
                        <small class="lf-error-text error" id="ifscError"></small>
                    </div>
                    <div class="lf-form-group">
                        <label class="lf-label" for="bank_name">Bank Name <span class="lf-req">*</span></label>
                        <input type="text" id="bank_name" class="lf-input" readonly value="<?php echo bank_e($application['bank_name'] ?? ''); ?>">
                        <small class="lf-error-text error" id="bankError"></small>
                    </div>
                    <div class="lf-form-group">
    <label class="lf-label" for="branch_name">Branch Name</label>
    <input type="text" id="branch_name" class="lf-input" readonly value="<?php echo bank_e($application['branch_name'] ?? ''); ?>">
</div>

                    <div class="lf-form-group">
                        <label class="lf-label" for="account_holder">Account Holder Name <span class="lf-req">*</span></label>
                        <input type="text" id="account_holder" class="lf-input" placeholder="As per bank account" autocomplete="name" value="<?php echo bank_e($application['account_holder'] ?? ''); ?>">
                        <small class="lf-error-text error" id="holderError"></small>
                    </div>

                    <div class="lf-form-group">
                        <label class="lf-label" for="account_number">Account Number <span class="lf-req">*</span></label>
                        <input type="text" id="account_number" class="lf-input" inputmode="numeric" placeholder="Enter account number" maxlength="18" autocomplete="off" value="<?php echo bank_e($application['account_number'] ?? ''); ?>">
                        <small class="lf-error-text error" id="accountError"></small>
                    </div>

                   

                    <button type="submit" class="lf-btn lf-btn-primary wm-bank-btn">
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
    const IFSC_CACHE_TTL = 30 * 24 * 60 * 60 * 1000;

    function readIfscCache(ifsc) {
        try {
            const raw = sessionStorage.getItem(`waqt_ifsc_${ifsc}`);
            if (!raw) return null;
            const cached = JSON.parse(raw);
            if (!cached || !cached.bank || Date.now() - cached.cachedAt > IFSC_CACHE_TTL) return null;
            return cached;
        } catch (error) {
            return null;
        }
    }

    function writeIfscCache(ifsc, payload) {
        try {
            sessionStorage.setItem(`waqt_ifsc_${ifsc}`, JSON.stringify({
                ...payload,
                cachedAt: Date.now()
            }));
        } catch (error) {}
    }

    function applyBankDetails(data) {
        document.getElementById("bank_name").value = data.bank || "";
        document.getElementById("branch_name").value = data.branch || "";
        document.getElementById("bank_name").classList.remove("lf-error-border");
        document.getElementById("ifscError").innerText = "";
    }

    function fetchBankDetails(ifsc) {
    const cached = readIfscCache(ifsc);
    if (cached) {
        applyBankDetails(cached);
        return;
    }

    fetch(`/api/apply/get_bank_details.php?ifsc=${ifsc}`)
        .then(res => res.json())
        .then(data => {

            if (data.status === 'success') {
                applyBankDetails(data);
                writeIfscCache(ifsc, data);

            } else {
                document.getElementById("bank_name").value = "";
                document.getElementById("branch_name").value = "";

                setBankError("ifsc_code", "ifscError", data.message);
            }

        })
        .catch(() => {
            document.getElementById("bank_name").value = "";
            document.getElementById("branch_name").value = "";

            setBankError("ifsc_code", "ifscError", "Unable to fetch bank details");
        });
}
</script>

<script>
    const form = document.getElementById("bankForm");
    const accountInput = document.getElementById("account_number");
    const ifscInput = document.getElementById("ifsc_code");
    const submitBtn = document.querySelector(".wm-bank-btn");

    accountInput.addEventListener("input", () => {
        accountInput.value = accountInput.value.replace(/\D/g, '').slice(0, 18);
    });

    
    let debounceTimer;

ifscInput.addEventListener("input", (e) => {
        e.target.value = e.target.value.toUpperCase();
    clearTimeout(debounceTimer);

   const ifsc = ifscInput.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().slice(0, 11)

    // 🔥 CLEAR OLD DATA
    document.getElementById("bank_name").value = "";
    document.getElementById("branch_name").value = "";

    if (ifsc.length === 11) {
        document.getElementById("bank_name").value = "Fetching...";
    document.getElementById("branch_name").value = "Fetching...";
        debounceTimer = setTimeout(() => {
            fetchBankDetails(ifsc);
        }, 500);
    }
});
    function setBankError(inputId, errorId, message) {
        document.getElementById(errorId).innerText = message;
        document.getElementById(inputId).classList.add("lf-error-border");
    }

    function clearBankErrors() {
        document.querySelectorAll(".error").forEach(el => el.innerText = "");
        document.querySelectorAll(".lf-input").forEach(el => el.classList.remove("lf-error-border"));
    }

    form.addEventListener("submit", function (e) {
        e.preventDefault();
        clearBankErrors();

        let isValid = true;

        const bank = document.getElementById("bank_name").value.trim();
        const branch = document.getElementById("branch_name").value.trim();
        const holder = document.getElementById("account_holder").value.trim();
        const account = accountInput.value.trim();
        const ifsc = ifscInput.value.trim();
        const ifscRegex = /^[A-Z]{4}0[A-Z0-9]{6}$/;


        if (holder.length < 3) {
            setBankError("account_holder", "holderError", "Enter a valid account holder name");
            isValid = false;
        }

        if (!/^\d{9,18}$/.test(account)) {
            setBankError("account_number", "accountError", "Account number must be 9 to 18 digits");
            isValid = false;
        }

        if (!ifscRegex.test(ifsc)) {
            setBankError("ifsc_code", "ifscError", "Enter valid IFSC code");
            isValid = false;
        }
        
if (!bank) {
    setBankError("bank_name", "bankError", "Bank not detected from IFSC");
    isValid = false;
}

        if (!isValid) return;
        if (submitBtn.disabled) return;

        submitBtn.disabled = true;
        submitBtn.innerHTML = "<div class='lf-spinner'></div> Saving...";

        const formData = new FormData();
        formData.append('bank_name', bank);
        formData.append('branch_name', branch);
        formData.append('account_holder', holder);
        formData.append('account_number', account);
        formData.append('ifsc_code', ifsc);

        fetch('api/apply/save_bank_detail.php', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': window.WAQT_CSRF_TOKEN || ''
            },
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = "reference-details.php";
                } else {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = "Continue <i class='fa-solid fa-arrow-right'></i>";
                    alert(data.message);
                }
            })
            .catch(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = "Continue <i class='fa-solid fa-arrow-right'></i>";
                alert("Network error");
            });
    });
</script>

<?php include 'includes/footer.php' ?>
