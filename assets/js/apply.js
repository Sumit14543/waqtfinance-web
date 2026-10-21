/* =========================
   GLOBAL STATE
========================= */
/* =========================
   GLOBAL STATE
========================= */
let currentStep = 1;
const totalSteps = 5; // test
let formSubmitted = false;
const STORAGE_KEY = "waqt_apply_form";
const formData = {};

function employmentIconSVG(type) {
  const value = String(type || "").toLowerCase();

  if (value.includes("government")) {
    return `<i class="fa-solid fa-building-columns mb-2" style="font-size: 20px; display: block;"></i>`;
  }

  if (value.includes("student")) {
    return `<i class="fa-solid fa-graduation-cap mb-2" style="font-size: 20px; display: block;"></i>`;
  }

  if (value.includes("freelancer") || value.includes("gig") || value.includes("contract")) {
    return `<i class="fa-solid fa-laptop-code mb-2" style="font-size: 20px; display: block;"></i>`;
  }

  if (value.includes("driver") || value.includes("fleet")) {
    return `<i class="fa-solid fa-truck-front mb-2" style="font-size: 20px; display: block;"></i>`;
  }

  if (value.includes("farmer")) {
    return `<i class="fa-solid fa-wheat-awn mb-2" style="font-size: 20px; display: block;"></i>`;
  }

  if (value.includes("retired") || value.includes("homemaker")) {
    return `<i class="fa-solid fa-house-chimney-user mb-2" style="font-size: 20px; display: block;"></i>`;
  }

  if (value.includes("rental")) {
    return `<i class="fa-solid fa-house-user mb-2" style="font-size: 20px; display: block;"></i>`;
  }

  if (value.includes("business") || value.includes("owner") || value.includes("proprietor") || value.includes("partner") || value.includes("director") || value.includes("msme") || value.includes("trader")) {
    return `<i class="fa-solid fa-briefcase mb-2" style="font-size: 20px; display: block;"></i>`;
  }

  return `<i class="fa-solid fa-user-tie mb-2" style="font-size: 20px; display: block;"></i>`;
}

const loanData = {
  "Personal Loan": {
    tenure: ["3 Months", "6 Months", "12 Months", "24 Months", "36 Months", "60 Months"],
    purpose: ["Medical Emergency", "Education Expenses", "Wedding", "Travel", "Home Renovation", "Debt Consolidation", "Other Personal Needs"],
    employment: ["Salaried Private", "Salaried Government", "Self-Employed", "Freelancer", "Retired"],
    docs: ["PAN Card", "Aadhaar Card", "Salary Slips Last 3 Months", "Bank Statement Last 6 Months", "Address Proof", "Passport Size Photo"]
  },
  "Business Loan": {
    tenure: ["6 Months", "12 Months", "24 Months", "36 Months", "60 Months", "72 Months"],
    purpose: ["Working Capital", "Business Expansion", "Machinery Purchase", "Stock Purchase", "Marketing", "Cash Flow Management"],
    employment: ["Business Owner", "Proprietor", "Partner", "Director", "MSME Owner", "Trader"],
    docs: ["PAN Card", "Aadhaar Card", "GST / Udyam / Shop License", "Bank Statement Last 12 Months", "ITR Last 2 Years", "Business Address Proof"]
  },
  "Loan Against Property": {
    tenure: ["12 Months", "24 Months", "60 Months", "84 Months", "120 Months", "180 Months", "240 Months"],
    purpose: ["Business Expansion", "Working Capital", "Debt Consolidation", "Education", "Medical Expenses", "Home Renovation"],
    employment: ["Salaried", "Self-Employed", "Business Owner", "Proprietor", "Partner", "Rental Income"],
    docs: ["PAN Card", "Aadhaar Card", "Property Documents", "Property Tax Receipt", "Bank Statement", "Income Proof", "Photo"]
  },
  "Gold Loan": {
    tenure: ["1 Month", "3 Months", "6 Months", "9 Months", "12 Months", "24 Months"],
    purpose: ["Emergency Funds", "Business Requirement", "Medical Expenses", "Education Expenses", "Household Expenses"],
    employment: ["Salaried", "Self-Employed", "Business Owner", "Farmer", "Homemaker", "Retired"],
    docs: ["Aadhaar Card", "PAN Card", "Passport Size Photo", "Gold Ownership Declaration"]
  },
  "Vehicle Loan": {
    tenure: ["12 Months", "24 Months", "36 Months", "48 Months", "60 Months", "72 Months", "84 Months"],
    purpose: ["New Car Purchase", "Used Car Purchase", "Two Wheeler Purchase", "Commercial Vehicle Purchase", "Vehicle Upgrade"],
    employment: ["Salaried", "Self-Employed", "Business Owner", "Commercial Driver", "Fleet Owner", "Farmer"],
    docs: ["PAN Card", "Aadhaar Card", "Address Proof", "Income Proof", "Bank Statement", "Vehicle Quotation", "Driving License"]
  },
  "EDI Loan": {
    tenure: ["7 Days", "15 Days", "30 Days", "45 Days", "60 Days", "90 Days"],
    purpose: ["Urgent Cash Requirement", "Bill Payment", "Medical Emergency", "Short-term Personal Need", "Utility Payment"],
    employment: ["Salaried", "Self-Employed", "Freelancer", "Gig Worker", "Student"],
    docs: ["Aadhaar Card", "PAN Card", "Bank Statement Last 3 Months", "Selfie Verification"]
  },
  "PDAY Loan": {
    tenure: ["7 Days", "15 Days", "30 Days"],
    purpose: ["Salary Gap Management", "Emergency Expenses", "Rent Payment", "Utility Bills", "Daily Expenses"],
    employment: ["Salaried Monthly Income", "Salaried Weekly Income", "Freelancer", "Gig Worker", "Contract Employee"],
    docs: ["Aadhaar Card", "PAN Card", "Salary Proof", "Bank Statement Last 3 Months", "Selfie Verification"]
  }
};

/* =========================
   ELEMENT HELPERS
========================= */
const qs = s => document.querySelector(s);
const qsa = s => document.querySelectorAll(s);
const fieldValue = selector => (qs(selector)?.value || "").trim();
const digitsOnly = value => String(value || "").replace(/\D/g, "");
const numericValue = value => Number(digitsOnly(value));

function saveFormData() {
  if (!Object.keys(formData).length) return;

  localStorage.setItem(STORAGE_KEY, JSON.stringify({
    step: currentStep,
    data: formData
  }));
}


/* =========================
   RESTORE FORM STATE
========================= */
function restoreFormState() {
  const saved = localStorage.getItem(STORAGE_KEY);
  if (!saved) return;

  const { data } = JSON.parse(saved);
  if (data.employmentType === "0") {
    delete data.employmentType;
  }

  Object.keys(data).forEach(key => {
    const el = document.getElementById(key);
    if (!el) return;

    if (el.type === "checkbox") el.checked = data[key];
    else {
      el.value = data[key];
      el.dispatchEvent(new Event("change")); // 🔥
    }
  });

  qsa("input[type=radio]").forEach(radio => {
    if (data[radio.name] === radio.value) {
      radio.checked = true;
    }
  });

  Object.assign(formData, data);
}



function showStep(step) {
  qsa(".step-panel").forEach(p => p.classList.remove("active"));
  qs(`#step-${step}`).classList.add("active");

  qsa(".step").forEach((el, i) => {
    el.classList.toggle("active", i + 1 <= step);
  });

  currentStep = step;
}

/* =========================
   LOAN TYPE SELECTION
========================= */
qsa(".loan-type").forEach(btn => {
  btn.addEventListener("click", () => {
    qsa(".loan-type").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");
    formData.loanType = btn.dataset.loan;
    updateLoanFields(formData.loanType);
    saveFormData();
  });
});

/* =========================
   HELPERS
========================= */
function fillOptions(selectId, arr) {
  const select = document.getElementById(selectId);
  if (!select || select.tagName !== "SELECT") return;
  const currentVal = select.value;
  select.innerHTML = `<option value="">Select option</option>`;
  arr.forEach(item => {
    const opt = document.createElement("option");
    opt.value = item;
    opt.textContent = item;
    select.appendChild(opt);
  });
  if (arr.includes(currentVal)) {
    select.value = currentVal;
  }
}

function updateLoanFields(loanType) {
  if (!loanType) return;
  
  // Find matching key case-insensitively
  const normalizedKey = Object.keys(loanData).find(
    k => k.toLowerCase() === loanType.toLowerCase()
  );
  
  if (!normalizedKey || !loanData[normalizedKey]) {
    console.error("Loan data not found for type:", loanType);
    return;
  }

  const data = loanData[normalizedKey];
  if (formData.employmentType && (formData.employmentType === "0" || !data.employment.includes(formData.employmentType))) {
    formData.employmentType = "";
  }

  // Sync hidden input if exists
  const hiddenLoan = document.getElementById("loanType");
  if (hiddenLoan) hiddenLoan.value = normalizedKey;

  fillOptions("loanTenure", data.tenure);
  fillOptions("loanPurpose", data.purpose);

  // Update Employment Type Buttons
  const empGrid = document.getElementById("employmentTypeGrid");
  if (empGrid) {
    empGrid.innerHTML = "";
    data.employment.forEach(emp => {
      const btn = document.createElement("button");
      btn.className = "emp-type";
      btn.type = "button";
      btn.dataset.emp = emp;
      btn.innerHTML = `${employmentIconSVG(emp)} ${emp}`;
      if (formData.employmentType === emp) {
        btn.classList.add("active");
      }
      btn.onclick = () => {
        qsa(".emp-type").forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
        formData.employmentType = emp;
        saveFormData();
      };
      empGrid.appendChild(btn);
    });
  }

  // Update Document Checklist
  const docGrid = document.getElementById("documentList");
  if (docGrid) {
    docGrid.innerHTML = "";
    data.docs.forEach(doc => {
      const col = document.createElement("div");
      col.className = "col-md-6";
      col.innerHTML = `
        <div class="doc-card">
            <div class="doc-icon">
                <i class="fa-regular fa-file-lines" style="font-size: 20px;"></i>
            </div>
            <div>
                <h6>${doc}</h6>
                <p>Required for verification</p>
            </div>
        </div>
      `;
      docGrid.appendChild(col);
    });
  }
}

/* =========================
   CURRENCY FORMAT
========================= */
function formatCurrencyInput(el) {
  el.addEventListener("input", (e) => {
    let cursorPos = el.selectionStart;

    // Only digits
    let raw = el.value.replace(/[^\d]/g, "");

    if (!raw) {
      el.value = "";
      return;
    }

    // Indian number format
    let formatted = Number(raw).toLocaleString("en-IN");

    el.value = formatted;

    // Cursor fix (basic)
    el.setSelectionRange(el.value.length, el.value.length);
  });

  // Prevent typing letters
  el.addEventListener("keypress", (e) => {
    if (!/[0-9]/.test(e.key)) {
      e.preventDefault();
    }
  });
}


qsa("[data-currency]").forEach(formatCurrencyInput);


const phoneInput = qs("#mobile");

if (phoneInput) {
  phoneInput.addEventListener("input", () => {
    phoneInput.value = phoneInput.value
      .replace(/\D/g, "")   // digits only
      .slice(0, 10);        // max 10 digits
  });
}

const pincodeInput = qs("#pincode");
if (pincodeInput) {
  pincodeInput.addEventListener("input", () => {
    pincodeInput.value = digitsOnly(pincodeInput.value).slice(0, 6);
  });
}

const accountInput = qs("#accountNumber");
if (accountInput) {
  accountInput.addEventListener("input", () => {
    accountInput.value = digitsOnly(accountInput.value).slice(0, 18);
  });
}

["#pan", "#ifscCode"].forEach(selector => {
  const input = qs(selector);
  if (!input) return;
  input.addEventListener("input", () => {
    input.value = input.value.toUpperCase().replace(/\s/g, "");
  });
});

/* =========================
   AUTO SAVE ON INPUT
========================= */
document.addEventListener("input", e => {
  const el = e.target;

  if (!el.id && !el.name) return;

  // checkbox
  if (el.type === "checkbox") {
    formData[el.id] = el.checked;
  }
  // radio
  else if (el.type === "radio") {
    formData[el.name] = el.value;
  }
  // normal input/select
  else {
    formData[el.id] = el.value;
  }

  saveFormData();
});

/* =========================
   VALIDATION
========================= */
/* =========================
   VALIDATION HELPERS
========================= */
function showValidationError(message, selector) {
  const el = qs(selector);
  if (el) {
    el.classList.add("is-invalid");
    
    // Remove is-invalid when user corrects input
    el.addEventListener("input", function clearErr() {
      el.classList.remove("is-invalid");
      el.removeEventListener("input", clearErr);
    }, { once: true });
    
    if (el.tagName === 'SELECT' || el.classList.contains('loan-grid') || el.classList.contains('radio-group')) {
      el.addEventListener("change", function clearErrChg() {
        el.classList.remove("is-invalid");
        el.removeEventListener("change", clearErrChg);
      }, { once: true });
    }
    
    const panel = el.closest(".step-panel");
    if (panel) {
      panel.classList.add("shake-panel");
      setTimeout(() => panel.classList.remove("shake-panel"), 450);
    }
  }

  Swal.fire({
    icon: 'warning',
    title: 'Required Field',
    text: message,
    confirmButtonColor: '#18529c',
    confirmButtonText: 'Okay'
  }).then(() => {
    if (el) {
      setTimeout(() => {
        if (el.focus) el.focus();
      }, 150);
    }
  });
  return false;
}

function validateStep(step) {
  switch (step) {
    case 1:
      if (!formData.loanType) return showValidationError("Please select a loan type", "#loanTypeGrid"), false;
      if (!fieldValue("#loanAmount")) return showValidationError("Please enter the loan amount", "#loanAmount"), false;
      if (numericValue(fieldValue("#loanAmount")) < 1000) return showValidationError("Enter a valid loan amount (minimum ₹1,000)", "#loanAmount"), false;
      if (!fieldValue("#loanTenure")) return showValidationError("Please select a loan tenure", "#loanTenure"), false;

      formData.loanAmount = fieldValue("#loanAmount");
      formData.loanTenure = fieldValue("#loanTenure");
      formData.loanPurpose = fieldValue("#loanPurpose");
      break;

    case 2:
      const phone = fieldValue("#mobile");
      const email = fieldValue("#email").toLowerCase();
      const pan = fieldValue("#pan").toUpperCase();
      const dob = fieldValue("#dob");
      const pincode = fieldValue("#pincode");

      if (!fieldValue("#fullName")) return showValidationError("Please enter your full name", "#fullName"), false;
      if (!/^[A-Za-z][A-Za-z .'-]{1,98}$/.test(fieldValue("#fullName"))) return showValidationError("Please enter a valid full name (alphabets only)", "#fullName"), false;
      if (!email) return showValidationError("Please enter your email address", "#email"), false;
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) return showValidationError("Please enter a valid email address", "#email"), false;
      if (!/^[6-9]\d{9}$/.test(phone)) return showValidationError("Please enter a valid 10-digit mobile number", "#mobile"), false;
      if (!dob) return showValidationError("Please select your date of birth", "#dob"), false;
      if (!isAdult(dob)) return showValidationError("Applicant must be at least 18 years old", "#dob"), false;
      if (!qs("input[name=gender]:checked")) return showValidationError("Please select your gender", "input[name=gender]"), false;
      if (pan && !/^[A-Z]{5}[0-9]{4}[A-Z]$/.test(pan)) return showValidationError("Please enter a valid PAN number format (e.g. ABCDE1234F)", "#pan"), false;
      if (pincode && !/^[1-9][0-9]{5}$/.test(pincode)) return showValidationError("Please enter a valid 6-digit pincode", "#pincode"), false;

      formData.fullName = fieldValue("#fullName");
      formData.email = email;
      formData.mobile = phone;
      formData.dob = dob;
      formData.gender = qs("input[name=gender]:checked")?.value || "";
      break;

    case 3:
      formData.employmentType = getSelectedEmploymentType();
      if (!formData.employmentType) return showValidationError("Please select your employment type", "#employmentTypeGrid"), false;
      if (!fieldValue("#monthlyIncome")) return showValidationError("Please enter your monthly income", "#monthlyIncome"), false;
      if (numericValue(fieldValue("#monthlyIncome")) < 1000) return showValidationError("Please enter a valid monthly income (minimum ₹1,000)", "#monthlyIncome"), false;
      if (fieldValue("#existingEmi") && numericValue(fieldValue("#existingEmi")) > numericValue(fieldValue("#monthlyIncome"))) {
        return showValidationError("Existing EMI cannot be greater than monthly income", "#existingEmi"), false;
      }
      if (fieldValue("#accountNumber") && !/^\d{9,18}$/.test(fieldValue("#accountNumber"))) {
        return showValidationError("Please enter a valid bank account number (9 to 18 digits)", "#accountNumber"), false;
      }
      if (fieldValue("#ifscCode") && !/^[A-Z]{4}0[A-Z0-9]{6}$/.test(fieldValue("#ifscCode").toUpperCase())) {
        return showValidationError("Please enter a valid IFSC code (e.g. SBIN0001234)", "#ifscCode"), false;
      }

      formData.company = fieldValue("#company");
      formData.designation = fieldValue("#designation");
      formData.monthlyIncome = fieldValue("#monthlyIncome");
      formData.existingEmi = fieldValue("#existingEmi") || "0";
      break;

    case 5:
      if (!qs("#termsAccepted").checked) {
        return showValidationError("Please accept the Terms & Conditions to proceed", "#termsAccepted"), false;
      }

      if (!qs("#creditCheckConsent").checked) {
        return showValidationError("Credit check consent is required to check your eligibility", "#creditCheckConsent"), false;
      }

      formData.communicationConsent = qs("#communicationConsent").checked;
      break;
  }
  return true;
}

function isAdult(dateString) {
  const dob = new Date(dateString);
  if (Number.isNaN(dob.getTime())) return false;
  const today = new Date();
  let age = today.getFullYear() - dob.getFullYear();
  const monthDiff = today.getMonth() - dob.getMonth();
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) age--;
  return age >= 18 && age <= 100;
}

function getSelectedEmploymentType() {
  const loanType = formData.loanType || fieldValue("#loanType") || "Personal Loan";
  const normalizedKey = Object.keys(loanData).find(
    k => k.toLowerCase() === loanType.toLowerCase()
  );
  const allowedEmployment = normalizedKey ? loanData[normalizedKey].employment : [];
  const active = qs(".emp-type.active");
  const value = (active?.dataset.emp || formData.employmentType || "").trim();
  return allowedEmployment.includes(value) ? value : "";
}

/* =========================
   REVIEW BINDING
========================= */
const escapeHtml = value => String(value ?? "")
  .replace(/&/g, "&amp;")
  .replace(/</g, "&lt;")
  .replace(/>/g, "&gt;")
  .replace(/"/g, "&quot;")
  .replace(/'/g, "&#039;");
const safe = v => v ? escapeHtml(v) : "-";
function getStoredData() {
  const raw = localStorage.getItem(STORAGE_KEY);
  if (!raw) return {};
  const parsed = JSON.parse(raw);
  const data = parsed.data || {};
  if (data.employmentType === "0") {
    delete data.employmentType;
    localStorage.setItem(STORAGE_KEY, JSON.stringify({
      ...parsed,
      data
    }));
  }
  return data;
}

function renderReview() {
  const d = getStoredData();
  Object.keys(d).forEach(key => {
    d[key] = safe(d[key]);
  });
  qs("#reviewData").innerHTML = `
  
  <!-- ================= Loan Details ================= -->
  <div class="pr-card p-4 mb-3">
    <h3 class="font-semibold mb-3 d-flex align-items-center gap-2">
      ${loanIconSVG} Loan Details
    </h3>

    <div class="row small">
      <div class="col-6">
        <span class="text-muted">Loan Type</span>
        <p class="fw-semibold text-capitalize">${safe(d.loanType)}</p>
      </div>
      <div class="col-6">
        <span class="text-muted">Amount</span>
        <p class="fw-semibold">₹${d.loanAmount}</p>
      </div>
      <div class="col-6">
        <span class="text-muted">Tenure</span>
        <p class="fw-semibold">${safe(d.loanTenure)}</p>
      </div>
      <div class="col-6">
        <span class="text-muted">Purpose</span>
        <p class="fw-semibold text-capitalize">${safe(d.loanPurpose)}</p>
      </div>
    </div>
  </div>

  <!-- ================= Personal Info ================= -->
  <div class="pr-card p-4 mb-3">
    <h3 class="font-semibold mb-3 d-flex align-items-center gap-2">
      ${userIconSVG} Personal Information
    </h3>

    <div class="row small">
      <div class="col-6">
        <span class="text-muted">Name</span>
        <p class="fw-semibold">${safe(d.fullName)}</p>
      </div>
      <div class="col-6">
        <span class="text-muted">Email</span>
        <p class="fw-semibold">${safe(d.email)}</p>
      </div>
      <div class="col-6">
        <span class="text-muted">Phone</span>
        <p class="fw-semibold">${safe(d.mobile)}</p>
      </div>
      <div class="col-6">
        <span class="text-muted">City</span>
        <p class="fw-semibold">${safe(d.city)}</p>
      </div>
    </div>
  </div>

  <!-- ================= Employment ================= -->
  <div class="pr-card p-4">
    <h3 class="font-semibold mb-3 d-flex align-items-center gap-2">
      ${briefcaseIconSVG} Employment Details
    </h3>

    <div class="row small">
      <div class="col-6">
        <span class="text-muted">Employment Type</span>
        <p class="fw-semibold text-capitalize">${safe(d.employmentType)}</p>
      </div>
      <div class="col-6">
        <span class="text-muted">Company</span>
        <p class="fw-semibold">${safe(d.company)}</p>
      </div>
      <div class="col-6">
        <span class="text-muted">Monthly Income</span>
        <p class="fw-semibold">₹${d.monthlyIncome}</p>
      </div>
      <div class="col-6">
        <span class="text-muted">Existing EMI</span>
        <p class="fw-semibold">₹${d.existingEmi || "0"}</p>
      </div>
    </div>
  </div>
  `;
}
function restoreLoanType() {
  const d = getStoredData();
  if (!d.loanType) return;

  qsa(".loan-type").forEach(btn => {
    const isActive = btn.dataset.loan.toLowerCase() === d.loanType.toLowerCase();
    btn.classList.toggle("active", isActive);
    if (isActive) {
        formData.loanType = btn.dataset.loan; // Ensure formData has the correct case
    }
  });
}

function restoreEmploymentType() {
  const d = getStoredData();
  if (!d.employmentType) return;

  // Active state is handled by updateLoanFields
}



/* =========================
   NAVIGATION
========================= */
qsa(".btn-next").forEach(btn => {
  btn.addEventListener("click", () => {
    if (!validateStep(currentStep)) return;

    if (currentStep === 4) {
      renderReview();
    }

    currentStep++;
    saveFormData();
    showStep(currentStep);
  });
});


qsa(".btn-prev").forEach(btn => {
  btn.addEventListener("click", () => {
    if (currentStep === 1) return;

    currentStep--;
    saveFormData();
    showStep(currentStep);
  });
});


/* =========================
   SUBMIT
========================= */

const submitBtn = qs("#submitApplication");
if (submitBtn) {
  submitBtn.addEventListener("click", () => {
    if (!validateStep(5)) return;

    const fd = new FormData();

    // STEP 1
    fd.append("loanType", formData.loanType);
    fd.append("loanAmount", formData.loanAmount);
    fd.append("loanTenure", formData.loanTenure);
    fd.append("loanPurpose", formData.loanPurpose);

    // STEP 2
    fd.append("fullName", formData.fullName);
    fd.append("email", formData.email);
    fd.append("mobile", formData.mobile);
    fd.append("dob", formData.dob);
    fd.append("gender", formData.gender);
    fd.append("pan", qs("#pan").value);

    fd.append("address", qs("#address").value);
    fd.append("city", qs("#city").value);
    fd.append("state", qs("#state").value);
    fd.append("pincode", qs("#pincode").value);

    // STEP 3
    const selectedEmploymentType = getSelectedEmploymentType();
    if (!selectedEmploymentType) {
      alert("Select employment type");
      return;
    }
    fd.append("employmentType", selectedEmploymentType);
    fd.append("employment_type", selectedEmploymentType);
    fd.append("company", fieldValue("#company"));
    fd.append("designation", fieldValue("#designation"));
    fd.append("monthlyIncome", formData.monthlyIncome);
    fd.append("existingEmi", formData.existingEmi);

    fd.append("bankName", fieldValue("#bankName"));
    fd.append("accountNumber", fieldValue("#accountNumber"));
    fd.append("ifscCode", fieldValue("#ifscCode").toUpperCase());

    // CONSENTS
    fd.append("terms", qs("#termsAccepted").checked ? 1 : "");
    fd.append("credit", qs("#creditCheckConsent").checked ? 1 : "");
    fd.append("communication", qs("#communicationConsent")?.checked ? 1 : "");

    submitApplication(fd);
  });
}

function submitApplication(formData) {
  const btn = qs("#submitApplication");
  btn.disabled = true;
  btn.innerText = "Submitting...";

formData.append("action", "submit_application");

fetch("apply", {
    method: "POST",
    headers: {
      "X-CSRF-Token": window.WAQT_CSRF_TOKEN || ""
    },
    body: formData
})
    .then(res => res.json())
    .then(data => {

      if (!data.status) {
        Swal.fire({
          icon: "error",
          title: "Submission Failed",
          text: data.message
        });

        btn.disabled = false;
        btn.innerText = "Submit Application";
        return;
      }

      // ✅ SUCCESS
      Swal.fire({
        icon: "success",
        title: "Application Submitted 🎉",
        html: `
          <p>Your loan application has been submitted successfully.</p>
          <p><strong>Reference ID:</strong> ${data.reference}</p>
        `,
        confirmButtonText: "Continue",
        confirmButtonColor: "#fbbf24",
        allowOutsideClick: false
      }).then(() => {
        formSubmitted = true;
        localStorage.removeItem(STORAGE_KEY);
        // reset JS state
        currentStep = 1;
        Object.keys(formData).forEach(k => delete formData[k]);

        // reset form fields
        document.querySelector("form")?.reset();

        // reset buttons
        qsa(".loan-type, .emp-type").forEach(b => b.classList.remove("active"));

        // go to first step
        showStep(1);
      });
    })
    .catch((err) => {
      console.error(err);

      Swal.fire({
        icon: "error",
        title: "Server Error",
        text: "Something went wrong. Please try again later."
      });

      btn.disabled = false;
      btn.innerText = "Submit Application";
    });
}




function getStoredStep() {
  const raw = localStorage.getItem(STORAGE_KEY);
  if (!raw) return 1;
  return JSON.parse(raw).step || 1;
}

window.addEventListener("beforeunload", () => {
  if (!formSubmitted) {
    saveFormData();
  }
});
document.addEventListener("DOMContentLoaded", () => {
  const raw = localStorage.getItem(STORAGE_KEY);

  let hasData = false;
  if (raw) {
    try {
      const parsed = JSON.parse(raw);
      const data = parsed.data || {};
      const keys = Object.keys(data).filter(k => k !== "loanType");
      hasData = keys.some(k => data[k] && String(data[k]).trim() !== "");
    } catch(e) {}
  }

  if (!raw || !hasData) {
    if (raw) {
      try {
        const parsed = JSON.parse(raw);
        formData.loanType = parsed.data?.loanType || "Personal Loan";
      } catch(e) {
        formData.loanType = "Personal Loan";
      }
    } else {
      formData.loanType = "Personal Loan";
    }
    updateLoanFields(formData.loanType);
    showStep(1);
    return;
  }

  Swal.fire({
    title: "Resume Application?",
    text: "You have an incomplete loan application.",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Resume",
    cancelButtonText: "Start Fresh",
    confirmButtonColor: "#18529c",
    cancelButtonColor: "#64748b"
  }).then(res => {
    if (res.isConfirmed) {
      restoreLoanType();
      updateLoanFields(formData.loanType); // Populate dynamic fields before restoring state
      restoreFormState();
      restoreEmploymentType();
      showStep(getStoredStep());
    } else {
      localStorage.removeItem(STORAGE_KEY);
      formData.loanType = "Personal Loan";
      updateLoanFields(formData.loanType);
      showStep(1);
    }
  });
});
