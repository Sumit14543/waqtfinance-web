/* ---------- Nice Select ---------- */
const employmentSelect = new Choices('#employment', {
    searchEnabled: false,
    itemSelectText: '',
});

/* ---------- Elements ---------- */
const ageInput = document.getElementById("age");
const incomeInput = document.getElementById("income");
const emiInput = document.getElementById("emi");
const expInput = document.getElementById("experience");
const score = document.getElementById("score");
const scoreText = document.getElementById("scoreText");
const scoreBadge = document.getElementById("scoreBadge");
const checkBtn = document.getElementById("checkBtn");

/* ---------- Credit Score ---------- */
function updateScoreUI(val) {
    scoreText.innerText = val;

    if (!scoreBadge) return; // 🔒 SAFETY

    scoreBadge.className = "badge";

    if (val >= 750) {
        scoreBadge.innerText = "Excellent";
        scoreBadge.classList.add("excellent");
    } else if (val >= 700) {
        scoreBadge.innerText = "Good";
        scoreBadge.classList.add("good");
    } else if (val >= 650) {
        scoreBadge.innerText = "Fair";
        scoreBadge.classList.add("fair");
    } else {
        scoreBadge.innerText = "Poor";
        scoreBadge.classList.add("poor");
    }
}

score.addEventListener("input", () => updateScoreUI(score.value));
updateScoreUI(700);

/* ---------- Money Formatting ---------- */
function formatNumberInput(el) {
    el.addEventListener("input", () => {
        let digits = el.value.replace(/\D/g, "").slice(0, 9);
        el.value = digits ? Number(digits).toLocaleString("en-IN") : "";
        toggleCheckButton();
    });
}
formatNumberInput(incomeInput);
formatNumberInput(emiInput);

function parseMoney(val) {
    return Number(val.replace(/,/g, "")) || 0;
}

function formatAmount(amount) {
    if (amount >= 10000000) return "₹" + (amount / 10000000).toFixed(1) + " Cr";
    if (amount >= 100000) return "₹" + (amount / 100000).toFixed(1) + " L";
    return "₹" + amount.toLocaleString("en-IN");
}

/* ---------- EMI ---------- */
function calculateEMI(P, annualRate, months) {
    let r = annualRate / 12 / 100;
    return Math.round(
        P * r * Math.pow(1 + r, months) /
        (Math.pow(1 + r, months) - 1)
    );
}

function calculateMaxPrincipal(maxEMI, annualRate, months) {
    let r = annualRate / 12 / 100;
    let factor = (Math.pow(1 + r, months) - 1) / (r * Math.pow(1 + r, months));
    return Math.round(maxEMI * factor);
}

/* ---------- Loan Rules ---------- */
const loans = [
    { name: "Personal Loan", minIncome: 25000, minScore: 650, max: 2500000, rate: 12, tenure: 60, tag: "Instant Approval", features: ["Minimal Documentation", "No End-Use Restriction"], link: "personal-loan-apply" },
    { name: "Business Loan", minIncome: 50000, minScore: 680, max: 20000000, rate: 16, tenure: 60, tag: "MSME Growth", features: ["High Loan Limit", "Flexible Daily Options"], link: "business-loan-apply" },
    { name: "Home Loan", minIncome: 40000, minScore: 700, max: 50000000, rate: 9, tenure: 240, tag: "Lowest Interest", features: ["Long Repayment Tenure", "Tax Exemptions Available"], link: "apply?loan=Home Loan" },
    { name: "Gold Loan", minIncome: 15000, minScore: 550, max: 2500000, rate: 10, tenure: 24, tag: "No Income Proof", features: ["Instant Cash Over Table", "Lowest Processing Fees"], link: "apply?loan=Gold Loan" },
    { name: "Education Loan", minIncome: 30000, minScore: 650, max: 5000000, rate: 11, tenure: 120, tag: "Student Priority", features: ["Grace Period Post Course", "Flexible Parental Joint-Sign"], link: "apply?loan=Education Loan" },
    { name: "Vehicle Loan", minIncome: 20000, minScore: 650, max: 10000000, rate: 10, tenure: 60, tag: "Easy Repayment", features: ["Up to 90% On-road Financing", "Minimal Down Payment"], link: "vehicle-loan-apply" }
];

/* ---------- Validation ---------- */
function validateForm() {
    const age = Number(ageInput.value);
    const income = parseMoney(incomeInput.value);
    const emi = parseMoney(emiInput.value);
    const exp = Number(expInput.value);
    const employment = employmentSelect.getValue(true);

    if (!age || age < 18 || age > 70) {
        alert("Age must be between 18 and 70 years.");
        return false;
    }

    if (!income || income < 10000 || income > 10000000) {
        alert("Please enter a valid monthly income (₹10,000 – ₹1 Cr).");
        return false;
    }

    if (!employment) {
        alert("Please select your employment type.");
        return false;
    }

    if (expInput.value && (exp < 0 || exp > 50 || exp > age - 18)) {
        alert("Please enter valid work experience.");
        return false;
    }

    if (emiInput.value && emi > income) {
        alert("Existing EMIs cannot exceed monthly income.");
        return false;
    }

    return true;
}

/* ---------- Enable Button ---------- */
function toggleCheckButton() {
    const ageValid = Number(ageInput.value) >= 18;
    const incomeValid = parseMoney(incomeInput.value) > 0;
    const employmentValid = employmentSelect.getValue(true);

    checkBtn.disabled = !(ageValid && incomeValid && employmentValid);
}

ageInput.addEventListener("input", toggleCheckButton);
employmentSelect.passedElement.element.addEventListener("change", toggleCheckButton);

/* ---------- Eligibility Chart ---------- */
let eligibilityChart = null;

function updateGaugeChart(eligibleCount, totalCount) {
    const canvas = document.getElementById("eligibilityChart");
    if (!canvas) return;

    const scoreVal = document.getElementById("eligibilityScoreVal");
    if (scoreVal) scoreVal.innerText = `${eligibleCount}/${totalCount}`;

    const feedback = document.getElementById("chartFeedback");
    if (feedback) {
        let percentage = (eligibleCount / totalCount) * 100;
        let text = "";
        let color = "";
        if (percentage === 100) {
            text = "Outstanding profile! You qualify for all loans.";
            color = "#059669";
        } else if (percentage >= 60) {
            text = "Great financial health! You qualify for most loan products.";
            color = "#10b981";
        } else if (percentage >= 30) {
            text = "Moderate profile eligibility. Clearance of current EMIs will improve options.";
            color = "#f59e0b";
        } else {
            text = "Low eligibility score. Try improving CIBIL score or net income.";
            color = "#ef4444";
        }
        feedback.innerText = text;
        feedback.style.color = color;
    }

    const ctx = canvas.getContext("2d");
    const data = [eligibleCount, totalCount - eligibleCount];
    const colors = ["#18529c", "#f1f5f9"];

    if (eligibilityChart) {
        eligibilityChart.data.datasets[0].data = data;
        eligibilityChart.update();
        return;
    }

    eligibilityChart = new Chart(ctx, {
        type: "doughnut",
        data: {
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderWidth: 0,
                hoverBackgroundColor: colors
            }]
        },
        options: {
            rotation: -90,
            circumference: 180,
            cutout: "84%",
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            }
        }
    });
}

/* ---------- Eligibility Check ---------- */
function checkEligibility() {
    if (!validateForm()) return;

    const income = parseMoney(incomeInput.value);
    const credit = Number(score.value);
    const emi = parseMoney(emiInput.value);

    // Dynamic Debt-to-Income check: Max allowed EMI ratio (50% default, 60% for excellent credit)
    const allowedFOIR = credit >= 750 ? 0.60 : 0.50;
    const maxAllowedEMI = income * allowedFOIR;
    const availableEMI = maxAllowedEMI - emi;

    let eligible = [], ineligible = [];

    loans.forEach(l => {
        // 1. Check basic criteria (income & CIBIL score)
        let failedCriteria = [];
        if (income < l.minIncome) {
            failedCriteria.push(`Monthly Income ₹${l.minIncome.toLocaleString('en-IN')}`);
        }
        if (credit < l.minScore) {
            failedCriteria.push(`CIBIL Score ${l.minScore}`);
        }

        if (failedCriteria.length > 0) {
            ineligible.push({ loan: l, reason: `Requires ${failedCriteria.join(" & ")}` });
            return;
        }

        // 2. Check debt service capacity
        if (availableEMI <= 0) {
            ineligible.push({ loan: l, reason: `Debt capacity exceeded (EMIs > ${allowedFOIR * 100}% of income)` });
            return;
        }

        // Calculate maximum principal they can borrow based on repayment capacity
        const principalByEMI = calculateMaxPrincipal(availableEMI, l.rate, l.tenure);
        const eligibleAmount = Math.min(principalByEMI, income * 24, l.max);

        // If the affordable principal is too low (e.g. less than ₹25,000)
        if (eligibleAmount < 25000) {
            ineligible.push({ loan: l, reason: `Repayment capacity too low for this loan type` });
            return;
        }

        // Qualifies! Save actual eligible loan amount
        eligible.push({ loan: l, amount: eligibleAmount });
    });

    document.getElementById("emptyState").classList.add("d-none");
    document.getElementById("resultArea").classList.remove("d-none");

    // Render eligible loans
    document.getElementById("eligibleLoans").innerHTML =
        eligible.map(item => {
            const l = item.loan;
            const loanAmount = item.amount;
            const calculatedEMI = calculateEMI(loanAmount, l.rate, l.tenure);

            return `
      <div class="col-md-6">
        <div class="loan-card">
          <h6>
            ${l.name}
            <span class="loan-tag">${l.tag}</span>
          </h6>
          <div class="emi-preview">${formatAmount(calculatedEMI)} <span style="font-size: 13px; font-weight: 500; color: #64748b;">/ mo</span></div>
          <div class="small text-muted mb-2">Max Amount: ${formatAmount(loanAmount)} (@ ${l.rate}% p.a.)</div>
          
          <ul class="loan-features">
            ${l.features.map(f => `<li><i class="fa-solid fa-circle-check"></i> ${f}</li>`).join("")}
          </ul>

          <a href="${l.link}" class="btn btn-sm btn-warning">
            Apply Now <i class="fa-solid fa-arrow-trend-up ms-1"></i>
          </a>
        </div>
      </div>`;
        }).join("");

    // Render ineligible loans
    document.getElementById("ineligibleLoans").innerHTML =
        ineligible.map(item => {
            const l = item.loan;
            const reasonText = item.reason;

            return `
      <div class="col-md-6">
        <div class="loan-card opacity-50">
          <h6>${l.name}</h6>
          <div class="reason-badge">
            <i class="fa-solid fa-circle-exclamation me-1"></i> ${reasonText}
          </div>
        </div>
      </div>`;
        }).join("");

    // Update gauge chart
    updateGaugeChart(eligible.length, loans.length);
}

/* ---------- Reset ---------- */
function resetForm() {
    ageInput.value = "";
    incomeInput.value = "";
    emiInput.value = "";
    expInput.value = "";

    employmentSelect.removeActiveItems();

    score.value = 700;
    updateScoreUI(700);
    
    // Trigger progress update for custom range styling
    const event = new Event("input", { bubbles: true });
    score.dispatchEvent(event);

    checkBtn.disabled = true;

    document.getElementById("eligibleLoans").innerHTML = "";
    document.getElementById("ineligibleLoans").innerHTML = "";
    
    if (eligibilityChart) {
        eligibilityChart.destroy();
        eligibilityChart = null;
    }

    document.getElementById("resultArea").classList.add("d-none");
    document.getElementById("emptyState").classList.remove("d-none");
}
toggleCheckButton();
