<style>
/* WRAPPER (Bootstrap-safe) */
.wf-landing-wrap {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 40px 20px;
    gap: 40px;
    background: #f8fafc;
    font-family: 'Inter', sans-serif !important;
}

/* LEFT SECTION */
.wf-info-panel {
    flex: 1.1;
    max-width: 540px;
    color: #1e293b;
}

.wf-info-title {
    font-size: 36px;
    font-weight: 800;
    line-height: 1.2;
    color: #0f172a;
    letter-spacing: -0.5px;
    margin-bottom: 16px;
    font-family: 'Inter', sans-serif !important;
}

.wf-info-text {
    font-size: 15px;
    color: #475569;
    line-height: 1.6;
    margin-bottom: 24px;
    font-family: 'Inter', sans-serif !important;
}

.wf-info-points {
    margin-top: 24px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.wf-info-point {
    font-size: 14.5px;
    color: #334155;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: 'Inter', sans-serif !important;
}

.wf-info-badge {
    display: inline-block;
    background: rgba(24, 82, 156, 0.08);
    color: #18529c;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12.5px;
    font-weight: 600;
    margin-bottom: 16px;
    letter-spacing: 0.2px;
}

/* RIGHT SECTION */
.wf-form-panel {
    flex: 0.9;
    max-width: 440px;
    width: 100%;
}

/* CARD */
.wf-form-card {
    background: #ffffff;
    padding: 32px 28px;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.03), 0 8px 10px -6px rgba(15, 23, 42, 0.03), 0 0 1px 0 rgba(15, 23, 42, 0.1);
}

/* HEADER */
.wf-form-title {
    font-size: 20px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 6px;
    text-align: center;
}

.wf-form-subtitle {
    font-size: 13px;
    color: #64748b;
    text-align: center;
    margin-bottom: 24px;
}

/* INPUT */
.wf-input-field {
    width: 100%;
    padding: 11px 14px;
    margin-bottom: 14px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 14.5px;
    font-weight: 500;
    outline: none;
    box-sizing: border-box;
    transition: all 0.2s ease;
    color: #1e293b;
    background: #ffffff;
}

.wf-input-field:focus {
    border-color: #18529c;
    box-shadow: 0 0 0 3px rgba(24, 82, 156, 0.1);
}

.wf-input-field::placeholder {
    color: #94a3b8;
}

/* SECTION BOX */
.wf-section-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 14px 16px;
    border-radius: 8px;
    margin-bottom: 14px;
    box-sizing: border-box;
}

.wf-section-box > div:first-child {
    font-size: 13.5px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 8px;
}

/* RADIO */
.wf-radio-group {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.wf-radio-item {
    font-size: 13px;
    font-weight: 500;
    background: #ffffff;
    padding: 8px 14px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    cursor: pointer;
    color: #475569;
    transition: all 0.15s ease;
    box-sizing: border-box;
}

.wf-radio-item:hover {
    border-color: #94a3b8;
    color: #1e293b;
}

.wf-radio-item.active {
    background: #18529c !important;
    color: #ffffff !important;
    border-color: #18529c !important;
    box-shadow: 0 2px 6px rgba(24, 82, 156, 0.2) !important;
}

/* BUTTON */
.wf-submit-btn {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 8px;
    background: #18529c !important;
    color: #ffffff;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: none;
    margin-top: 8px;
}

.wf-submit-btn:hover {
    background: #0b213a !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(24, 82, 156, 0.2) !important;
}

/* FOOTER */
.wf-footer-text {
    font-size: 11.5px;
    text-align: center;
    color: #94a3b8;
    margin-top: 12px;
}

/* RESPONSIVE */
@media (max-width: 900px) {
    .wf-landing-wrap {
        flex-direction: column;
        gap: 30px;
        padding: 30px 20px;
    }

    .wf-info-panel {
        text-align: center;
        max-width: 100%;
    }

    .wf-info-points {
        align-items: center;
    }
}
@media (max-width: 600px){
    .wf-info-title{
        font-size: 26px;
    }
}
</style>

<div class="wf-landing-wrap">

    <!-- LEFT SIDE -->
    <div class="wf-info-panel">

        <div class="wf-info-badge">Business Loan for Shopkeepers</div>

        <div class="wf-info-title">
            Grow Your Business Faster with Easy Loans
        </div>

        <div class="wf-info-text">
            Quick financing for your business regardless of whether you have a small retail outlet or a big store without much paperwork.
        </div>

        <div class="wf-info-points">
            <div class="wf-info-point"><span style="color: #10b981; font-weight: bold; margin-right: 4px;">✓</span> Quick approval within minutes</div>
            <div class="wf-info-point"><span style="color: #10b981; font-weight: bold; margin-right: 4px;">✓</span> Minimal documentation required</div>
            <div class="wf-info-point"><span style="color: #10b981; font-weight: bold; margin-right: 4px;">✓</span> Flexible EMI repayment options</div>
            <div class="wf-info-point"><span style="color: #10b981; font-weight: bold; margin-right: 4px;">✓</span> No heavy collateral needed</div>
        </div>

    </div>

    <!-- RIGHT SIDE -->
    <div class="wf-form-panel">

        <div class="wf-form-card">

            <div class="wf-form-title">Check Eligibility</div>
            <div class="wf-form-subtitle">Fill details to get instant loan estimate</div>

            <input type="text" id="fullName" class="wf-input-field" placeholder="Full Name (As per PAN)">
            <input type="tel" id="mobileNumber" class="wf-input-field" placeholder="Mobile Number" maxlength="10">

            <div class="wf-section-box">
                <div>MSME Registered?</div>
                <div class="wf-radio-group" data-name="msme">
                    <div class="wf-radio-item" data-value="Yes">Yes</div>
                    <div class="wf-radio-item" data-value="No">No</div>
                </div>
            </div>

            <div class="wf-section-box">
                <div>Monthly Sales</div>
                <div class="wf-radio-group" data-name="sales">
                    <div class="wf-radio-item" data-value="Up to 2L">Up to 2L</div>
                    <div class="wf-radio-item" data-value="2–4L">2–4L</div>
                    <div class="wf-radio-item" data-value="4–10L">4–10L</div>
                    <div class="wf-radio-item" data-value="10L+">10L+</div>
                </div>
            </div>

            <button class="wf-submit-btn" onclick="checkEligibility()">Submit</button>

            <div id="wfResult" style="display:none; margin-top:15px; padding:12px; border-radius:10px; background:#f1f7ff; color:#123; font-size:14px; font-weight:600;"></div>

            <div class="wf-footer-text">
                Your data is safe and never shared with third parties.
            </div>

        </div>

    </div>

</div>

<script>
let selectedData = {
    msme: "",
    sales: ""
};

document.querySelectorAll(".wf-radio-group").forEach(group => {
    group.querySelectorAll(".wf-radio-item").forEach(item => {
        item.addEventListener("click", function () {
            group.querySelectorAll(".wf-radio-item").forEach(i => i.classList.remove("active"));
            this.classList.add("active");

            let name = group.getAttribute("data-name");
            selectedData[name] = this.getAttribute("data-value");
        });
    });
});

function checkEligibility() {
    let name = document.getElementById("fullName").value.trim();
    let mobile = document.getElementById("mobileNumber").value.trim();
    let resultBox = document.getElementById("wfResult");

    if(!name || !mobile) {
        alert("Please enter Name and Mobile Number");
        return;
    }
    if(mobile.length < 10) {
        alert("Please enter a valid 10-digit mobile number");
        return;
    }
    if(!selectedData.msme || !selectedData.sales) {
        alert("Please select MSME status and Monthly Sales");
        return;
    }

    resultBox.style.display = "block";
    resultBox.innerHTML = `Calculating eligibility for ${name}...`;

    // AJAX to API
    let formData = new FormData();
    formData.append("full_name", name);
    formData.append("mobile", mobile);
    formData.append("msme", selectedData.msme);
    formData.append("sales", selectedData.sales);

    fetch("api/apply/check_eligibility.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === "success") {
            resultBox.style.background = "#e6f4ea";
            resultBox.style.color = "#137333";
            resultBox.innerHTML = `Congratulations! You are eligible for an estimated loan of <strong>₹${data.amount}</strong>. Redirecting to application form...`;
            setTimeout(() => {
                window.location.href = "edi-loan-apply";
            }, 3000);
        } else {
            resultBox.style.background = "#fce8e6";
            resultBox.style.color = "#c5221f";
            resultBox.innerHTML = data.message || "We could not find an immediate offer. Our team will contact you shortly.";
        }
    })
    .catch(err => {
        resultBox.style.background = "#fce8e6";
        resultBox.style.color = "#c5221f";
        resultBox.innerHTML = "Error processing request. Please try again.";
    });
}
</script>
