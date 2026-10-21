<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/loan_flow.php';

$application = loan_flow_require_application($conn);
$nextUrl = loan_flow_next_url($conn, $application);
if (basename($nextUrl) !== 'basic-details-form.php') {
    header('Location: ' . $nextUrl);
    exit;
}

include 'includes/header.php';
include 'includes/main-header.php';

function basic_e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<link rel="stylesheet" href="assets/css/loan-flow.css">

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
                    <i class="fa-solid fa-address-card"></i>
                </div>
                <h1 class="lf-card-title">Basic Details</h1>
                <p class="lf-card-sub">Your Data is Completely Secure with us</p>
            </div>

            <div class="lf-card-body">
                <form id="basicForm">

                    <div class="lf-form-group">
                        <label class="lf-label" for="pincode">Pin Code</label>
                        <input class="lf-input" type="text" id="pincode" name="pincode" placeholder="Enter your pin code" maxlength="6" inputmode="numeric" value="<?php echo basic_e($application['pincode'] ?? ''); ?>">
                        <small class="lf-error-text" id="pincodeError"></small>
                    </div>

                    <div class="lf-form-group">
                        <label class="lf-label" for="city">City</label>
                        <input class="lf-input" type="text" id="city" name="city" placeholder="City will fetch automatically" value="<?php echo basic_e($application['city'] ?? ''); ?>">
                        <small class="lf-error-text" id="cityError"></small>
                    </div>

                    <div class="lf-form-group">
                        <label class="lf-label">Employment Type</label>
                        <div class="lf-toggle-group" id="employmentGroup">
                            <button type="button" class="lf-toggle-btn <?php echo ($application['employment_status'] ?? '') === 'salaried' ? 'active' : ''; ?>" data-value="salaried">Salaried</button>
                            <button type="button" class="lf-toggle-btn <?php echo ($application['employment_status'] ?? '') === 'self' ? 'active' : ''; ?>" data-value="self">Self-Employed</button>
                        </div>
                        <small class="lf-error-text" id="empError"></small>
                    </div>

                    <div class="lf-form-group">
                        <label class="lf-label" for="income">Monthly Income</label>
                        <div class="lf-input-prefix-wrap" id="incomeWrap">
                            <div class="lf-input-prefix">₹</div>
                            <input class="lf-input" type="text" id="income" name="income" placeholder="e.g. 45000" value="<?php echo !empty($application['monthly_income']) ? basic_e(number_format((float) $application['monthly_income'], 0, '.', ',')) : ''; ?>">
                        </div>
                        <small class="lf-error-text" id="incomeError"></small>
                    </div>

                    <div class="lf-form-group">
                        <label class="lf-label">Income Received In</label>
                        <div class="lf-toggle-group" id="incomeTypeGroup">
                            <button type="button" class="lf-toggle-btn <?php echo ($application['income_received_in'] ?? '') === 'account' ? 'active' : ''; ?>" data-value="account">Account</button>
                            <button type="button" class="lf-toggle-btn <?php echo ($application['income_received_in'] ?? '') === 'cash' ? 'active' : ''; ?>" data-value="cash">Cash</button>
                            <button type="button" class="lf-toggle-btn <?php echo ($application['income_received_in'] ?? '') === 'cheque' ? 'active' : ''; ?>" data-value="cheque">Cheque</button>
                        </div>
                        <small class="lf-error-text" id="incomeTypeError"></small>
                    </div>

                    <button type="submit" class="lf-btn lf-btn-primary bd-submit" style="margin-top: 10px;">
                        Continue <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    const form = document.getElementById("basicForm");

    // ================= BUTTON TOGGLE =================
    document.querySelectorAll(".lf-toggle-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            btn.parentElement.querySelectorAll(".lf-toggle-btn")
                .forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
        });
    });

    // ================= PINCODE =================
    const pincodeInput = document.getElementById("pincode");
    const cityInput = document.getElementById("city");
    const PINCODE_CACHE_TTL = 30 * 24 * 60 * 60 * 1000;

    function readPincodeCache(pincode) {
        try {
            const raw = sessionStorage.getItem(`waqt_pincode_${pincode}`);
            if (!raw) return null;
            const cached = JSON.parse(raw);
            if (!cached || !cached.city || Date.now() - cached.cachedAt > PINCODE_CACHE_TTL) return null;
            return cached;
        } catch (error) {
            return null;
        }
    }

    function writePincodeCache(pincode, payload) {
        try {
            sessionStorage.setItem(`waqt_pincode_${pincode}`, JSON.stringify({
                ...payload,
                cachedAt: Date.now()
            }));
        } catch (error) {}
    }

    pincodeInput.addEventListener("input", () => {
        pincodeInput.value = pincodeInput.value.replace(/[^0-9]/g, '').slice(0, 6);
    });

    // ================= INCOME FORMAT =================
    const incomeInput = document.getElementById("income");

    incomeInput.addEventListener("input", () => {
        let value = incomeInput.value.replace(/,/g, '').replace(/[^0-9]/g, '');
        if (value) {
            incomeInput.value = Number(value).toLocaleString('en-IN');
        } else {
            incomeInput.value = "";
        }
    });

    pincodeInput.addEventListener("input", function () {

        let pincode = this.value.replace(/[^0-9]/g, '');

        if (pincode.length > 6 || pincode.length === 0) {
            cityInput.value = "";
            cityInput.readOnly = false;
            return;
        }
        
        if (pincode.length === 6) {
            const cached = readPincodeCache(pincode);
            if (cached) {
                cityInput.value = cached.city;
                cityInput.readOnly = true;
                document.getElementById("pincodeError").innerText = "";
                document.getElementById("cityError").innerText = "";
                pincodeInput.classList.remove("lf-error-border");
                cityInput.classList.remove("lf-error-border");
                return;
            }

            cityInput.value = "Fetching...";
            fetch(`api/apply/get_pincode_details.php?pincode=${pincode}`)
                .then(res => res.json())
                .then(data => {

                    if (data.status === "success") {
                        cityInput.value = data.city;
                        cityInput.readOnly = true;
                        writePincodeCache(pincode, {
                            city: data.city,
                            state: data.state || "",
                            region: data.region || ""
                        });
                        document.getElementById("pincodeError").innerText = "";
                        document.getElementById("cityError").innerText = "";
                        pincodeInput.classList.remove("lf-error-border");
                        cityInput.classList.remove("lf-error-border");

                    } else {
                        cityInput.value = "";
                        cityInput.readOnly = false;
                        document.getElementById("pincodeError").innerText = "Invalid pincode";
                        pincodeInput.classList.add("lf-error-border");
                    }

                })
                .catch(() => {
                    cityInput.value = "";
                    cityInput.readOnly = false;
                });
        }
    });

    // ================= SUBMIT =================
    form.addEventListener("submit", function (e) {
        e.preventDefault();

        // Clear errors
        document.querySelectorAll(".lf-error-text").forEach(el => el.innerText = "");
        document.querySelectorAll(".lf-input").forEach(el => el.classList.remove("lf-error-border"));
        document.getElementById("incomeWrap").classList.remove("lf-error-border");

        let isValid = true;

        const pincode = document.getElementById("pincode").value.trim();
        const city = document.getElementById("city").value.trim();
        const income = document.getElementById("income").value.replace(/,/g, '');

        const empBtn = document.querySelector("#employmentGroup .active");
        const incomeBtn = document.querySelector("#incomeTypeGroup .active");



        // VALIDATIONS

        if (pincode.length !== 6) {
            document.getElementById("pincodeError").innerText = "Enter valid 6-digit pincode";
            document.getElementById("pincode").classList.add("lf-error-border");
            isValid = false;
        }

        if (!city || city === "Fetching...") {
            document.getElementById("cityError").innerText = "Enter city";
            document.getElementById("city").classList.add("lf-error-border");
            isValid = false;
        }

        if (!empBtn) {
            document.getElementById("empError").innerText = "Select employment type";
            isValid = false;
        }

        if (!income || isNaN(income) || income <= 0) {
            document.getElementById("incomeError").innerText = "Enter valid income";
            document.getElementById("incomeWrap").classList.add("lf-error-border");
            isValid = false;
        }

        if (!incomeBtn) {
            document.getElementById("incomeTypeError").innerText = "Select income type";
            isValid = false;
        }

        if (!isValid) return;

        const submitBtn = document.querySelector(".bd-submit");

        if (submitBtn.disabled) return;

        submitBtn.disabled = true;
        submitBtn.innerHTML = "<div class='lf-spinner'></div> Saving...";

        const formData = new FormData();
        formData.append('pincode', pincode);
        formData.append('city', city);
        formData.append('income', income);
        formData.append('employment_type', empBtn.dataset.value);
        formData.append('income_received_in', incomeBtn.dataset.value);

        fetch('api/apply/save_basic_details.php', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': window.WAQT_CSRF_TOKEN || ''
            },
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = "pan-verification.php";
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
