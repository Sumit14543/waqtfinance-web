<?php
ini_set('display_errors', '0');
ini_set('log_errors', '1');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_application') {
    require __DIR__ . '/form-actions/submit-application.php';
    exit;
}

$page_title = "Apply for Loan Online | 24-Hour Approval | Waqt Finance";
$meta_description = "Complete your online loan application in minutes with Waqt Finance. Safe digital KYC, instant eligibility verification & same-day disbursement. Start now!";
$meta_keywords = "apply for loan online Delhi NCR, personal loan application Delhi, business loan application Noida, payday loan apply Gurugram, loan application Ghaziabad";
$canonical_url = "https://waqtfinance.com/apply";
$meta_image = "assets/images/apply.webp";
$meta_image_alt = "Apply for loan online in Delhi NCR with Waqt Finance";
$breadcrumb_label = "Apply for Loan Online";

include 'includes/header.php';
include 'includes/main-header.php';
?>

    
    

    <!-- ================= HERO ================= -->
    <section class="hero text-center">
        <p class="hero-tag">START YOUR JOURNEY</p>
        <h1>Apply for a <span>Loan</span></h1>
        <p class="hero-sub">Complete your application in just 5 minutes. Quick approval, minimal documentation.</p>

        <div class="hero-badges">
            <span>
                <i class="fa-solid fa-shield-halved text-primary me-2" style="font-size: 16px;"></i> 100% Secure
            </span>

            <span>
                <i class="fa-regular fa-clock text-primary me-2" style="font-size: 16px;"></i> Quick Approval
            </span>

            <span>
                <i class="fa-solid fa-circle-check text-primary me-2" style="font-size: 16px;"></i> RBI Registered
            </span>
        </div>
    </section>

    <!-- ================= STEPS ================= -->
    <div class="steps-bar">
        <div class="step active" data-step="1">
            <div class="step-icon">
                <i class="fa-solid fa-list-check"></i>
            </div>
            Loan Details
        </div>
        <div class="step" data-step="2">
            <div class="step-icon">
                <i class="fa-solid fa-user"></i>
            </div>
            Personal Info
        </div>
        <div class="step" data-step="3">
            <div class="step-icon">
                <i class="fa-solid fa-briefcase"></i>
            </div>
            Employment
        </div>
        <div class="step" data-step="4">
            <div class="step-icon">
                <i class="fa-solid fa-file-arrow-up"></i>
            </div>
            Documents
        </div>
        <div class="step" data-step="5">
            <div class="step-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            Review
        </div>
    </div>

    <!-- ================= MAIN ================= -->
    <div class="container my-5">
        <div class="row g-4">

            <!-- ================= LEFT ================= -->
            <div class="col-lg-8 apply-form">

                <!-- STEP 1 -->
                <div class="card-glass step-panel active" id="step-1">
                    <h2 class="mb-2">Loan Details</h2>
                    <p class="muted">Select your loan type and amount</p>

                    <label class="label">Select Loan Type *</label>
                    <input type="hidden" id="loanType" value="Personal Loan">
                    <div class="loan-grid" id="loanTypeGrid">
                        <button type="button" class="loan-type active" data-loan="Personal Loan">
                            <i class="fa-solid fa-user-tie mb-2" style="font-size: 20px;"></i>
                            Personal Loan
                        </button>
                        <button type="button" class="loan-type" data-loan="Business Loan">
                            <i class="fa-solid fa-briefcase mb-2" style="font-size: 20px;"></i>
                            Business Loan
                        </button>
                        <button type="button" class="loan-type" data-loan="Loan Against Property">
                            <i class="fa-solid fa-building mb-2" style="font-size: 20px;"></i>
                            Loan Against Property
                        </button>
                        <button type="button" class="loan-type" data-loan="Gold Loan">
                            <i class="fa-solid fa-coins mb-2" style="font-size: 20px;"></i>
                            Gold Loan
                        </button>
                        <button type="button" class="loan-type" data-loan="Vehicle Loan">
                            <i class="fa-solid fa-car mb-2" style="font-size: 20px;"></i>
                            Vehicle Loan
                        </button>
                        <button type="button" class="loan-type" data-loan="EDI Loan">
                            <i class="fa-solid fa-bolt mb-2" style="font-size: 20px;"></i>
                            EDI Loan
                        </button>
                        <button type="button" class="loan-type" data-loan="PDAY Loan">
                            <i class="fa-solid fa-calendar-day mb-2" style="font-size: 20px;"></i>
                            PDAY Loan
                        </button>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="label">Loan Amount (₹) *</label>
                            <input type="text" id="loanAmount" data-currency inputmode="numeric" autocomplete="off" placeholder="₹ 5,00,000">
                        </div>
                        <div class="col-md-6">
                            <label class="label">Loan Tenure *</label>
                            <select id="loanTenure">
                                <option value="">Select tenure</option>
                            </select>
                        </div>
                    </div>

                    <label class="label mt-3">Purpose of Loan</label>
                    <select id="loanPurpose">
                        <option value="">Select purpose</option>
                    </select>

                    <div class="actions">
                        <button class="btn-next">Next →</button>
                    </div>
                </div>

                <!-- STEP 2 -->
                <div class="card-glass step-panel" id="step-2">
                    <h2 class="mb-2">Personal Information</h2>
                    <p class="mb-3 muted">Provide your personal and contact details</p>

                    <div class="row">
                        <div class="col-md-6">
                            <label>Full Name *</label>
                            <input id="fullName" autocomplete="name" maxlength="100" placeholder="As per PAN">
                        </div>
                        <div class="col-md-6">
                            <label>Email *</label>
                            <input type="email" id="email" autocomplete="email" maxlength="150" placeholder="you@email.com">
                        </div>
                        <div class="col-md-6">
                            <label>Mobile *</label>
                            <input type="tel" id="mobile" inputmode="numeric" autocomplete="tel" maxlength="10" placeholder="10-digit number">
                        </div>
                        <div class="col-md-6">
                            <label>Date of Birth *</label>
                            <input type="date" id="dob">
                        </div>
                    </div>

                    <label class="mt-3">Gender *</label>
                    <div class="radio-group">
                        <label><input type="radio" class="mt-0" name="gender" value="male"> Male</label>
                        <label><input type="radio" class="mt-0" name="gender" value="female"> Female</label>
                        <label><input type="radio" class="mt-0" name="gender" value="other"> Other</label>
                    </div>

                    <label class="mt-3">PAN Number</label>
                    <input id="pan" maxlength="10" autocomplete="off" placeholder="ABCDE1234F">

                    <h5 class="mt-4">Address Details</h5>
                    <textarea id="address" maxlength="500" autocomplete="street-address" placeholder="Full address"></textarea>

                    <div class="row">
                        <div class="col-md-4"><input id="city" maxlength="100" autocomplete="address-level2" placeholder="City"></div>
                        <div class="col-md-4"><input id="state" maxlength="100" autocomplete="address-level1" placeholder="State"></div>
                        <div class="col-md-4"><input id="pincode" inputmode="numeric" maxlength="6" autocomplete="postal-code" placeholder="Pincode"></div>
                    </div>

                    <div class="actions">
                        <button class="btn-prev">← Previous</button>
                        <button class="btn-next">Next →</button>
                    </div>
                </div>

                <!-- STEP 3 -->
                <div class="card-glass step-panel" id="step-3">
                    <h2 class="mb-2">Employment Details</h2>
                    <p class="muted">Tell us about your employment and income</p>
                    <label>Employment Type *</label>
                    <div class="loan-grid" id="employmentTypeGrid">
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6"><input id="company" maxlength="150" autocomplete="organization" placeholder="Company Name"></div>
                        <div class="col-md-6"><input id="designation" maxlength="150" autocomplete="organization-title" placeholder="Designation"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6"><input id="monthlyIncome" data-currency inputmode="numeric" autocomplete="off" placeholder="Monthly Income ₹">
                        </div>
                        <div class="col-md-6"><input id="existingEmi" data-currency inputmode="numeric" autocomplete="off" placeholder="Existing EMIs ₹"></div>
                    </div>

                    <h5 class="mt-4">Bank Details</h5>
                    <div class="row">
                        <div class="col-md-4"><input id="bankName" maxlength="255" autocomplete="off" placeholder="Bank Name"></div>
                        <div class="col-md-4"><input id="accountNumber" inputmode="numeric" maxlength="18" autocomplete="off" placeholder="Account Number"></div>
                        <div class="col-md-4"><input id="ifscCode" maxlength="11" autocomplete="off" placeholder="IFSC Code"></div>
                    </div>

                    <div class="actions">
                        <button class="btn-prev">← Previous</button>
                        <button class="btn-next">Next →</button>
                    </div>
                </div>

                <!-- STEP 4 -->
                <div class="card-glass step-panel" id="step-4">
                    <h2>Document Checklist</h2>
                    <p class="text-muted small mb-4">
                        Keep these documents ready for verification
                    </p>
                    <div class="row g-3" id="documentList">
                    </div>

                    <!-- Upload Info -->
                    <div class="upload-box mt-4">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <div>
                            <h6>Document Upload</h6>
                            <p>
                                After submitting your application, our team will contact you via
                                email or WhatsApp to collect documents.
                            </p>
                        </div>
                    </div>

                    <!-- Pro Tips -->
                    <div class="pro-tips mt-4">
                        <h6>💡 Pro Tips</h6>
                        <ul>
                            <li>Ensure documents are clear and readable</li>
                            <li>Bank statements should be last 6 months</li>
                            <li>Self-employed need last 2 years ITR</li>
                            <li>PDF or image formats only</li>
                        </ul>
                    </div>


                    <div class="actions">
                        <button class="btn-prev">← Previous</button>
                        <button class="btn-next">Next →</button>
                    </div>
                </div>

                <!-- STEP 5 -->
                <div class="card-glass step-panel" id="step-5">
                    <h2>Review Your Application</h2>
                    <p class="mb-3">Please review your details before submitting</p>

                    <div id="reviewData"></div>

                    <div class="consent-wrapper pt-4 border-top">

                        <!-- Terms -->
                        <div class="form-check consent-item">
                            <input class="form-check-input" type="checkbox" id="termsAccepted">
                            <label class="form-check-label" for="termsAccepted">
                                <strong>I agree to the Terms & Conditions <span class="text-warning">*</span></strong>
                               
                            </label>
                        </div>

                        <!-- Credit Check -->
                        <div class="form-check consent-item">
                            <input class="form-check-input" type="checkbox" id="creditCheckConsent">
                            <label class="form-check-label" for="creditCheckConsent">
                                <strong>I consent to credit bureau check <span class="text-warning">*</span></strong>
                                <small>
                                    I authorize WaqtFinance to check my credit score and history.
                                </small>
                            </label>
                        </div>

                        <!-- Communication -->
                        <div class="form-check consent-item">
                            <input class="form-check-input" type="checkbox" id="communicationConsent">
                            <label class="form-check-label" for="communicationConsent">
                                <strong>Receive updates via SMS / WhatsApp / Email</strong>
                                <small>
                                    Get loan updates, offers, and promotional messages.
                                </small>
                            </label>
                        </div>

                         <div class="form-check consent-item">
                            <input class="form-check-input" type="checkbox" checked>
                            <label class="form-check-label" for="communicationConsent">
                                <strong>By submitting your information, you authorize waqt finance to contact you via phone, SMS, RCS, WhatsApp, or email for informational and promotional purposes related to our services. You can opt out of promotional communication at any time by contacting us</strong>
                                
                            </label>
                        </div>

                         <div class="form-check consent-item">
                            <input class="form-check-input" type="checkbox" checked>
                            <label class="form-check-label" for="communicationConsent">
                                <strong>You authorize Waqt Finance to check your credit report for loan eligibility and agree to be contacted. Consent can be withdrawn anytime.</strong>
                                
                            </label>
                        </div>

                    </div>



                    <div class="actions">
                        <button class="btn-prev">← Previous</button>
                        <button class="btn-submit" id="submitApplication">Submit Application</button>
                    </div>
                </div>

            </div>

            <!-- ================= RIGHT ================= -->
            <div class="col-lg-4">
                <div class="side-card">
                    <h5>Need Help?</h5>
                    <p class="mb-3 d-flex align-items-center">
                        <span class="contact-icon">
                            <i class="fa-solid fa-phone"></i>
                        </span>
                        <small class="muted">Call us at
                            <strong class="d-block">9217086610</strong>
                        </small>

                    </p>
                    <p class="mb-3 d-flex align-items-center">
                        <span class="contact-icon">
                            <i class="fa-solid fa-envelope"></i>
                        </span>
                        <small class="muted">Email us at
                            <strong class="d-block">customercare@waqtfinance.com</strong>
                        </small>

                    </p>
                </div>

                <div class="side-card">
                    <h5>Why Choose Waqt Finance?</h5>
                    <ul>
                      <li>Fast approval powered by Waqt Finance</li>
                      <li>Low & flexible interest rates</li>
                      <li>100% transparent process</li>
                      <li>Always-on support, whenever you need</li>
                    </ul>
                </div>

                <div class="side-card secure">
                    <i class="fa-solid fa-shield-halved" style="font-size: 24px; margin-bottom: 8px; display: inline-block;"></i>
                    <p>256-bit SSL Encrypted</p>
                </div>
            </div>

        </div>
    </div>
    <div id="successScreen" style="display:none" class="card-glass text-center">
    <h3>Application Submitted 🎉</h3>
    <p class="muted mt-2">
        Your application reference number:
    </p>
    <!--<h2 id="appRef" class="mt-3"></h2> -->

    <p class="muted mt-3">
        Our team will contact you within 24 hours.
    </p>

    <button class="btn-next mt-4" onclick="location.href='/'">
        Back to Home
    </button>
</div>



<!-- Popup Modal -->
<div class="wf-overlay" id="termsModal">

    <div class="wf-modal">

        <h2 class="wf-title">Terms and Conditions</h2>

        <div class="wf-content">

            <p><b>PLEASE READ THESE TERMS AND CONDITIONS ("TERMS") CAREFULLY.</b> THESE TERMS CONSTITUTE A LEGALLY BINDING AGREEMENT BETWEEN YOU AND <b>Waqt Finance</b>.</p>

            <p>
                I have read and agreed to the Terms of Use and hereby appoint Waqt Finance
                to receive my credit information from credit bureaus, KYC from Government
                Regulated Database. By submitting my details, I override my NDNC registration
                to authorize Waqt Finance / representatives to contact me through Call, SMS,
                Email, Whatsapp or any other mode.
            </p>

            <p>
                BY CLICKING "I ACCEPT" OR "I AGREE," OR BY DOWNLOADING, INSTALLING,
                OR USING THE SERVICES IN ANY MANNER, YOU ACKNOWLEDGE THAT YOU HAVE
                READ, UNDERSTOOD, AND AGREE TO BE BOUND BY THESE TERMS.
            </p>

            <h2>DEFINITIONS</h2>

            <ul>
                <li><b>Company:</b> Waqt Finance</li>
                <li><b>User:</b> Any person using the platform</li>
                <li><b>Services:</b> Loan facilitation services</li>
                <li><b>Platform:</b> Website and application managed by Waqt Finance</li>
                <li><b>Loan:</b> Credit facility provided through partnered lenders</li>
                <li><b>User Data:</b> Personal and financial information submitted by users</li>
            </ul>

            <h2>DESCRIPTION OF SERVICES</h2>

            <p>
                The Platform enables eligible users to apply for loans subject to verification
                and validation of required documents and information.
            </p>

            <p>
                By using the Services, you consent to the Company accessing, importing,
                and processing your data for identity verification, credit assessment,
                fraud prevention, and operational purposes.
            </p>

            <p>
                Data obtained from KYC systems, UIDAI, or Government databases shall be
                used in accordance with applicable RBI Guidelines and the Prevention of
                Money Laundering Act, 2002.
            </p>

            <p>
                Loan disbursement and repayment obligations shall be governed by the
                respective Loan Agreement executed between the borrower and lender.
            </p>

            <p>
                Customers are responsible for maintaining confidentiality of account
                credentials and activities conducted through their account.
            </p>

            <h2>CONSENT FOR COMMUNICATION</h2>

            <p>
                You expressly authorize Waqt Finance, its representatives, partners,
                and affiliates to contact you through Call, SMS, Email, WhatsApp,
                or other communication channels for service updates, verification,
                recovery communication, and promotional offers.
            </p>

            <h2>REGULATIONS</h2>

            <p>By using the Platform and Services, you agree not to:</p>

            <ul>
                <li>Engage in fraudulent or unlawful activities.</li>
                <li>Copy, reproduce, or misuse any platform content.</li>
                <li>Upload harmful, abusive, defamatory, or illegal content.</li>
                <li>Impersonate another person or entity.</li>
                <li>Transmit malware, viruses, or malicious software.</li>
                <li>Violate any applicable laws or regulations.</li>
            </ul>

            <h2>CONTENT AVAILABLE</h2>

            <p>
                Waqt Finance makes no warranties regarding the completeness,
                accuracy, or reliability of content available on the Platform.
                The content should not be considered financial or investment advice.
            </p>

            <h2>PROPRIETARY RIGHTS</h2>

            <p>
                All rights, title, trademarks, logos, designs, software,
                and intellectual property related to the Platform belong
                exclusively to Waqt Finance.
            </p>

            <h2>USE OF THIRD-PARTY SERVICES</h2>

            <p>
                Our Platform may rely on third-party APIs and verification services
                to provide KYC validation, credit checks, and operational support.
            </p>

            <ul>
                <li>Surepass</li>
                <li>Novel Pattern</li>
                <li>Credgenics</li>
            </ul>

            <h2>TERMINATION</h2>

            <p>
                Waqt Finance reserves the right to suspend or terminate access
                to the Services in case of violation of these Terms, fraud,
                non-payment, or legal requirements.
            </p>

            <p>
                Termination shall not affect repayment obligations or liabilities
                owed to the lender.
            </p>

            <h2>INDEMNITY</h2>

            <p>
                You agree to indemnify and hold harmless Waqt Finance,
                its employees, affiliates, agents, and partners from any claims,
                damages, liabilities, or expenses arising out of misuse of the Platform
                or violation of these Terms.
            </p>

            <h2>LIMITATION OF LIABILITY</h2>

            <p>
                Waqt Finance shall not be liable for any direct, indirect,
                incidental, special, or consequential damages arising from use
                or inability to use the Platform or Services.
            </p>

            <h2>FORCE MAJEURE</h2>

            <p>
                Waqt Finance shall not be held responsible for delays or failure
                in performance due to events beyond reasonable control including
                natural disasters, internet outages, system failures, or government actions.
            </p>

            <h2>YOUR RIGHTS</h2>

            <h3>Access and Update Information</h3>

            <p>
                You may request access to or correction of your personal data
                maintained by the Company.
            </p>

            <h3>Withdraw Consent</h3>

            <p>
                You may withdraw consent for data processing subject to legal
                and regulatory requirements.
            </p>

            <h3>Marketing Opt-Out</h3>

            <p>
                You may opt out of promotional communications at any time.
            </p>

            <h2>CHANGES TO TERMS</h2>

            <p>
                Waqt Finance reserves the right to update or modify these Terms
                at any time. Continued use of the Platform shall constitute acceptance
                of revised Terms.
            </p>

            <h2>DISCLOSURE</h2>

            <p>
                The Grievance Redressal Officer under the Consumer Protection Act, 2019 is:
            </p>

            <div class="wf-contact">
              
                <p><b>Phone:</b> <a href="tel:9217997310">9217997310</a></p>
                <p><b>Email:</b> <a href="mailto:customercare@waqtfinance.com">customercare@waqtfinance.com</a></p>
            </div>

            <h2>CONTACT INFORMATION</h2>

            <div class="wf-contact">
                <p>Email: <a href="mailto:customercare@waqtfinance.com">customercare@waqtfinance.com</a></p>
                <p>Phone: <a href="tel:9217086610">9217086610</a></p>
                <p>Support Hours: Monday to Saturday, 09:00 AM to 05:30 PM</p>
            </div>

        </div>

        <div class="wf-footer">
            <button class="wf-btn-decline" id="declineBtn">Decline</button>
            <button class="wf-btn-accept" id="acceptBtn">Agree</button>
        </div>

    </div>

</div>

<!--end popup-->

<script>

const checkbox = document.getElementById("termsAccepted");
const modal = document.getElementById("termsModal");
const acceptBtn = document.getElementById("acceptBtn");
const declineBtn = document.getElementById("declineBtn");

/* Checkbox click => popup open */
checkbox.addEventListener("click", function(e){

    // Prevent direct checking
    e.preventDefault();

    // Show popup
    modal.style.display = "flex";
});

/* Agree button */
acceptBtn.addEventListener("click", function(){

    checkbox.checked = true;
    modal.style.display = "none";
});

/* Decline button */
declineBtn.addEventListener("click", function(){

    checkbox.checked = false;
    modal.style.display = "none";
});

</script>



<script defer src="<?php echo $prefix; ?>assets/js/apply.js?v=<?php echo filemtime(__DIR__ . '/assets/js/apply.js'); ?>"></script>
    <?php include 'includes/footer.php' ?>

    
   
