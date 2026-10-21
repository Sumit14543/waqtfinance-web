<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/loan_flow.php';

$application = loan_flow_require_application($conn);
$nextUrl = loan_flow_next_url($conn, $application);
if (basename($nextUrl) !== 'company-details.php') {
    header('Location: ' . $nextUrl);
    exit;
}

include 'includes/header.php';
include 'includes/main-header.php';
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
                <div class="lf-step active">
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
                    <i class="fa-solid fa-briefcase"></i>
                </div>
                <h1 class="lf-card-title">Work Details</h1>
                <p class="lf-card-sub">Tell us about your employment details.</p>
            </div>

            <div class="lf-card-body">
                <div id="uanBox" style="display:none;">
                    <label>UAN Number</label>
                    <input type="text" id="uanInput" readonly class="form-control">
                </div>
                <form id="companyForm">
                    <div class="lf-form-group">
                        <label class="lf-label" for="company_name">Company Name <span class="lf-req">*</span></label>
                        <input type="text" id="company_name" name="company_name" class="lf-input"
                            placeholder="Enter company name" autocomplete="organization">
                        <small class="lf-error-text error" id="companyError"></small>
                    </div>

                    <div class="lf-form-group">
                        <label class="lf-label" for="designation">Designation <span class="lf-req">*</span></label>
                        <input type="text" id="designation" name="designation" class="lf-input"
                            placeholder="Enter your designation" autocomplete="organization-title">
                        <small class="lf-error-text error" id="designationError"></small>
                    </div>

                    <div class="lf-form-group">
                        <label class="lf-label" for="office_email">Office Email <span class="lf-req">*</span></label>
                        <input type="email" id="office_email" name="office_email" class="lf-input"
                            placeholder="name@company.com" autocomplete="email">
                        <small class="lf-error-text error" id="emailError"></small>
                    </div>

                    <div class="lf-form-group">
                        <label class="lf-label" for="salary_day">Salary Date <span class="lf-req">*</span></label>
                        <div class="lf-select-wrap">
                            <select id="salary_day" name="salary_day" class="lf-select">
                                <option value="">Select salary date</option>
                            </select>
                        </div>
                        <small class="lf-error-text error" id="salaryError"></small>
                    </div>

                    <div class="lf-form-group">
                        <label class="lf-label" for="office_address">Office Address <span
                                class="lf-req">*</span></label>
                        <textarea id="office_address" name="office_address" class="lf-textarea"
                            placeholder="Enter office address"></textarea>
                        <small class="lf-error-text error" id="addressError"></small>
                    </div>

                    <div class="lf-form-group">
                        <label class="lf-label" for="education">Education <span class="lf-req">*</span></label>
                        <div class="lf-select-wrap">
                            <select id="education" name="education" class="lf-select">
                                <option value="" selected>Select education</option>
                                <option value="10th">10th</option>
                                <option value="12th">12th</option>
                                <option value="Graduate">Graduate</option>
                                <option value="Post Graduate">Post Graduate</option>
                            </select>
                        </div>
                        <small class="lf-error-text error" id="eduError"></small>
                    </div>

                    <div class="lf-form-group">
                        <label class="lf-label" for="experience">Experience (Years) <span
                                class="lf-req">*</span></label>
                        <input type="number" id="experience" name="experience" class="lf-input"
                            placeholder="Enter experience in years" min="0" max="50" inputmode="numeric">
                        <small class="lf-error-text error" id="expError"></small>
                    </div>

                    <button type="submit" class="lf-btn lf-btn-primary wm-submit-btn">
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

    document.addEventListener("DOMContentLoaded", function () {
        fetch('/api/apply/get_uan.php')
            .then(res => res.json())
            .then(data => {

                if (data.uan_number) {
                    document.getElementById("uanBox").style.display = "block";
                    document.getElementById("uanInput").value = data.uan_number;
                } else {
                    document.getElementById("uanBox").style.display = "none";
                }

            })
            .catch(err => console.error(err));
    });

    const salarySelect = document.getElementById("salary_day");

    for (let i = 1; i <= 31; i++) {
        const option = document.createElement("option");
        option.value = i;

        let suffix = "th";
        if (i === 1 || i === 21 || i === 31) suffix = "st";
        else if (i === 2 || i === 22) suffix = "nd";
        else if (i === 3 || i === 23) suffix = "rd";

        option.text = i + suffix;
        salarySelect.appendChild(option);
    }

    const form = document.getElementById("companyForm");
    const submitBtn = document.querySelector(".wm-submit-btn");

    function setFieldError(inputId, errorId, message) {
        document.getElementById(errorId).innerText = message;
        const input = document.getElementById(inputId);
        if (input) input.classList.add("lf-error-border");
    }

    function clearCompanyErrors() {
        document.querySelectorAll(".error").forEach(el => el.innerText = "");
        document.querySelectorAll(".lf-input, .lf-select, .lf-textarea").forEach(el => el.classList.remove("lf-error-border"));
    }

    form.addEventListener("submit", function (e) {
        e.preventDefault();
        clearCompanyErrors();

        let isValid = true;

        const company = document.getElementById("company_name").value.trim();
        const designation = document.getElementById("designation").value.trim();
        const email = document.getElementById("office_email").value.trim();
        const salaryDay = document.getElementById("salary_day").value;
        const address = document.getElementById("office_address").value.trim();
        const education = document.getElementById("education").value;
        const exp = document.getElementById("experience").value;

        if (company.length < 2) {
            setFieldError("company_name", "companyError", "Enter a valid company name");
            isValid = false;
        }

        if (designation.length < 2) {
            setFieldError("designation", "designationError", "Enter a valid designation");
            isValid = false;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) {
            setFieldError("office_email", "emailError", "Enter a valid office email");
            isValid = false;
        }

        if (!salaryDay || Number(salaryDay) < 1 || Number(salaryDay) > 31) {
            setFieldError("salary_day", "salaryError", "Select salary date");
            isValid = false;
        }

        if (address.length < 5) {
            setFieldError("office_address", "addressError", "Enter a valid office address");
            isValid = false;
        }

        if (!education) {
            setFieldError("education", "eduError", "Select education");
            isValid = false;
        }

        if (exp === "" || Number(exp) < 0 || Number(exp) > 50) {
            setFieldError("experience", "expError", "Enter experience between 0 and 50 years");
            isValid = false;
        }

        if (!isValid) return;
        if (submitBtn.disabled) return;

        submitBtn.disabled = true;
        submitBtn.innerHTML = "<div class='lf-spinner'></div> Saving...";

        const formData = new FormData();
        formData.append('company_name', company);
        formData.append('designation', designation);
        formData.append('office_email', email);
        formData.append('salary_day', salaryDay);
        formData.append('office_address', address);
        formData.append('education', education);
        formData.append('experience', exp);

        fetch('api/apply/save_company_detail.php', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': window.WAQT_CSRF_TOKEN || ''
            },
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = "bank-details.php";
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
