<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'init_otp') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['mobile_number'] = $_POST['mobile'] ?? '';
    $_SESSION['Full_Name'] = $_POST['fullName'] ?? '';
    $_SESSION['employee_code'] = $_POST['employeeCode'] ?? '';
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    echo json_encode(['status' => 'success', 'csrf_token' => $_SESSION['csrf_token']]);
    exit;
}

$page_title = "Apply for EDI Loan | Equated Daily Installment | Waqt Finance";
$meta_description = "Flexible daily repayment with Waqt Finance EDI loans. Perfect for small businesses. Securely apply through our premium multi-step application portal.";
$meta_keywords = "EDI loan application, daily installment loan India, MSME daily finance, quick business cash";
$canonical_url = "https://waqtfinance.com/edi-loan-apply";
$meta_robots = "noindex, follow";

// Adding the modern apply CSS
$extra_head = '<link rel="stylesheet" href="assets/css/apply-modern.css?v=20260710-compact-fields">';
$load_sweetalert = true;

include 'includes/header.php';
include 'includes/main-header.php';
?>

<div class="wf-hero-header-wrap" style="background: #062b5d; color: #ffffff; padding: 26px 0; margin-bottom: 24px; border-bottom: 1px solid rgba(255,255,255,0.1); width: 100%;">
    <div class="wf-hero-header-inner" style="max-width: 1120px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; padding: 0 20px; flex-wrap: wrap; gap: 20px;">
        <div style="display: flex; align-items: center; gap: 18px;">
            <i class="fa fa-shield-alt" style="font-size: 38px; color: #ffffff; opacity: 0.95;"></i>
            <div>
                <h1 style="font-size: 25px; font-weight: 700; color: #ffffff; margin: 0 0 2px 0; border: none; padding: 0; font-family: 'Inter', sans-serif !important; letter-spacing: -0.2px;">EDI Loan Application</h1>
                <p style="font-size: 13.5px; color: rgba(255, 255, 255, 0.85); margin: 0; font-family: 'Inter', sans-serif !important;">Smart daily instalment financing for your business</p>
            </div>
        </div>
        <div class="wf-hero-trust-badges" style="display: flex; gap: 28px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 10px; color: #ffffff;">
                <i class="fa fa-lock" style="font-size: 18px; opacity: 0.85;"></i>
                <div style="font-size: 11.5px; line-height: 1.25; font-weight: 500; font-family: 'Inter', sans-serif !important;">
                    <strong style="display: block; font-weight: 700;">Secure</strong> Application
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 10px; color: #ffffff;">
                <i class="fa fa-user-shield" style="font-size: 18px; opacity: 0.85;"></i>
                <div style="font-size: 11.5px; line-height: 1.25; font-weight: 500; font-family: 'Inter', sans-serif !important;">
                    <strong style="display: block; font-weight: 700;">OTP</strong> Verified
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 10px; color: #ffffff;">
                <i class="fa fa-clock" style="font-size: 18px; opacity: 0.85;"></i>
                <div style="font-size: 11.5px; line-height: 1.25; font-weight: 500; font-family: 'Inter', sans-serif !important;">
                    <strong style="display: block; font-weight: 700;">Quick</strong> Processing
                </div>
            </div>
        </div>
    </div>
</div>

<div class="wf-apply-body" style="padding-top: 0 !important; min-height: auto !important; background: transparent !important;">
    <div class="wf-draft-status" id="draftStatus" aria-live="polite" style="max-width: 1120px; margin: 0 auto 10px; padding: 0 20px;"></div>

    <div class="wf-apply-card" data-aos="fade-up">

        <!-- Progress Indicator -->
        <div class="wf-progress-container">
            <div class="wf-sidebar-header-custom" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; width: 100%; grid-column: span 2 !important;">
                <span style="font-size: 14px; font-weight: 700; color: #0b213a;">Application Progress</span>
                <span id="sidebarStepCount" style="font-size: 11px; color: #64748b; font-weight: 600;">Step 1 of 9</span>
            </div>
            <div class="wf-sidebar-progress-bar-custom" style="height: 6px; width: 100%; background: #e2e8f0; border-radius: 10px; overflow: hidden; margin-bottom: 24px; position: relative; grid-column: span 2 !important;">
                <div id="sidebarProgressFill" style="height: 100%; width: 11.1%; background: #18529c; transition: width 0.3s ease;"></div>
            </div>
            <div class="wf-progress-steps">
                <div class="wf-progress-line"></div>
                <div class="wf-step-node active" data-step="1">1</div>
                <div class="wf-step-node" data-step="2">2</div>
                <div class="wf-step-node" data-step="3">3</div>
                <div class="wf-step-node" data-step="4">4</div>
                <div class="wf-step-node" data-step="5">5</div>
                <div class="wf-step-node" data-step="6">6</div>
                <div class="wf-step-node" data-step="7">7</div>
                <div class="wf-step-node" data-step="8">8</div>
                <div class="wf-step-node" data-step="9">9</div>
            </div>
            <div class="wf-step-labels">
                <div class="wf-step-label">
                    <strong style="display: block; font-size: 13px; font-weight: 700; color: #334155; line-height: 1.2;">Basic Details</strong>
                    <small style="display: block; font-size: 11px; color: #64748b; font-weight: 400; margin-top: 2px; line-height: 1.2;">Provide basic information</small>
                </div>
                <div class="wf-step-label">
                    <strong style="display: block; font-size: 13px; font-weight: 700; color: #334155; line-height: 1.2;">Personal Details</strong>
                    <small style="display: block; font-size: 11px; color: #64748b; font-weight: 400; margin-top: 2px; line-height: 1.2;">Tell us about yourself</small>
                </div>
                <div class="wf-step-label">
                    <strong style="display: block; font-size: 13px; font-weight: 700; color: #334155; line-height: 1.2;">KYC Details</strong>
                    <small style="display: block; font-size: 11px; color: #64748b; font-weight: 400; margin-top: 2px; line-height: 1.2;">Identity verification</small>
                </div>
                <div class="wf-step-label">
                    <strong style="display: block; font-size: 13px; font-weight: 700; color: #334155; line-height: 1.2;">Business Details</strong>
                    <small style="display: block; font-size: 11px; color: #64748b; font-weight: 400; margin-top: 2px; line-height: 1.2;">Business information</small>
                </div>
                <div class="wf-step-label">
                    <strong style="display: block; font-size: 13px; font-weight: 700; color: #334155; line-height: 1.2;">Document Upload</strong>
                    <small style="display: block; font-size: 11px; color: #64748b; font-weight: 400; margin-top: 2px; line-height: 1.2;">Upload required documents</small>
                </div>
                <div class="wf-step-label">
                    <strong style="display: block; font-size: 13px; font-weight: 700; color: #334155; line-height: 1.2;">Bank Details</strong>
                    <small style="display: block; font-size: 11px; color: #64748b; font-weight: 400; margin-top: 2px; line-height: 1.2;">Bank account information</small>
                </div>
                <div class="wf-step-label">
                    <strong style="display: block; font-size: 13px; font-weight: 700; color: #334155; line-height: 1.2;">Reference Details</strong>
                    <small style="display: block; font-size: 11px; color: #64748b; font-weight: 400; margin-top: 2px; line-height: 1.2;">Provide references</small>
                </div>
                <div class="wf-step-label">
                    <strong style="display: block; font-size: 13px; font-weight: 700; color: #334155; line-height: 1.2;">Selfie Capture</strong>
                    <small style="display: block; font-size: 11px; color: #64748b; font-weight: 400; margin-top: 2px; line-height: 1.2;">Take a selfie</small>
                </div>
                <div class="wf-step-label">
                    <strong style="display: block; font-size: 13px; font-weight: 700; color: #334155; line-height: 1.2;">Application Review</strong>
                    <small style="display: block; font-size: 11px; color: #64748b; font-weight: 400; margin-top: 2px; line-height: 1.2;">Review & submit</small>
                </div>
            </div>
            <div class="wf-sidebar-help-card" style="margin-top: 40px; background: #f0f7ff; border: 1px solid #dbeafe; border-radius: 12px; padding: 16px; display: flex; align-items: flex-start; gap: 12px; width: 100%; box-sizing: border-box; grid-column: span 2 !important;">
                <i class="fas fa-headset" style="font-size: 20px; color: #18529c; margin-top: 3px;"></i>
                <div>
                    <h4 style="font-size: 13.5px; font-weight: 700; color: #0b213a; margin: 0 0 2px 0;">Need Help?</h4>
                    <p style="font-size: 11.5px; color: #475569; margin: 0 0 6px 0; line-height: 1.3;">Our support team is here to help you.</p>
                    <a href="tel:9217086610" style="font-size: 13.5px; font-weight: 700; color: #18529c; text-decoration: none; display: block;">+91 92170 86610</a>
                </div>
            </div>
            <div class="wf-mobile-progress" aria-live="polite">
                <div class="wf-mobile-progress-head">
                    <span id="mobileStepCount">Step 1 of 9</span>
                    <strong id="mobileStepTitle">Basic Details</strong>
                </div>
                <div class="wf-mobile-progress-track">
                    <span id="mobileProgressFill"></span>
                </div>
            </div>
        </div>

        <form id="applyForm" class="wf-form-content" novalidate>
            <input type="hidden" name="loan_type" value="edi">
            <div class="wf-disclosure-strip" role="note" aria-label="Application disclosure">
                <span><i class="fas fa-shield-alt"></i> Encrypted application</span>
                <span><i class="fas fa-user-check"></i> Consent-based checks</span>
                <span><i class="fas fa-file-signature"></i> Key terms before final acceptance</span>
            </div>
            <!-- STEP 1: BASIC INFO -->
            <div class="wf-step-panel active" id="step-1">
                <div class="wf-step-intro" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                    <div>
                        <h3 style="font-size: 22px; font-weight: 700; color: #0b213a; margin: 0 0 6px 0; font-family: 'Inter', sans-serif !important;">Basic Details</h3>
                        <p style="font-size: 13.5px; color: #64748b; margin: 0; font-family: 'Inter', sans-serif !important; line-height: 1.45;">Enter applicant name and mobile number. OTP verification will open in a secure popup.</p>
                    </div>
                    <div class="wf-secure-badge" style="display: inline-flex; align-items: center; gap: 6px; background: #ecfdf5; border: 1px solid #10b981; color: #10b981; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; font-family: 'Inter', sans-serif !important;">
                        <i class="fas fa-lock"></i> Encrypted & consent-led
                    </div>
                </div>

                <div class="wf-form-grid">
                    <div class="wf-input-group">
                        <label class="wf-label" style="font-weight: 600; color: #334155; font-size: 13.5px; margin-bottom: 6px; font-family: 'Inter', sans-serif !important;">Applicant Name (As per KYC) <span class="text-danger">*</span></label>
                        <div class="wf-input-wrapper">
                            <i class="fas fa-user wf-input-icon"></i>
                            <input type="text" class="wf-control" id="fullName" required placeholder="Full name as per KYC" autocomplete="name">
                        </div>
                        <div class="wf-error-msg"></div>
                    </div>

                    <div class="wf-input-group">
                        <label class="wf-label" style="font-weight: 600; color: #334155; font-size: 13.5px; margin-bottom: 6px; font-family: 'Inter', sans-serif !important;">Mobile Number <span class="text-danger">*</span></label>
                        <div class="wf-input-wrapper">
                            <i class="fas fa-mobile-alt wf-input-icon"></i>
                            <input type="text" class="wf-control" id="phoneField" required maxlength="10" placeholder="9876543210" inputmode="numeric" pattern="[6-9][0-9]{9}" autocomplete="tel-national">
                        </div>
                        <div class="wf-error-msg"></div>
                    </div>
                </div>

                <div class="wf-input-group">
                    <label class="wf-label" style="font-weight: 600; color: #334155; font-size: 13.5px; margin-bottom: 6px; font-family: 'Inter', sans-serif !important;">Employee Code (Optional)</label>
                    <div class="wf-input-wrapper">
                        <i class="fas fa-user-tag wf-input-icon"></i>
                        <input type="text" class="wf-control" id="employeeCode" maxlength="30" placeholder="Enter employee/referral code if any">
                    </div>
                    <div class="wf-field-hint" style="font-size: 12px; color: #64748b; margin-top: 6px; font-weight: 400; font-family: 'Inter', sans-serif !important;">If you have been referred by an employee, please enter the code.</div>
                    <div class="wf-error-msg"></div>
                </div>

                <div class="wf-consent-box" style="border: 1px solid #dbeafe; border-radius: 12px; padding: 20px; background: #ffffff; display: flex; flex-direction: column; gap: 12px; margin-top: 24px;">
                    <div class="wf-consent-summary">
                        <strong>Before you continue</strong>
                        We use these details for eligibility, KYC/credit checks, lender matching, and required service communication.
                    </div>
                    <label class="terms-check" style="display: flex; gap: 12px; align-items: center; cursor: pointer; margin: 0; padding: 0; background: transparent; border: 0; box-shadow: none;">
                        <input type="checkbox" id="termsCheckbox" required style="width: 18px; height: 18px; margin: 0; accent-color: #18529c; cursor: pointer;">
                        <span style="font-size: 13.5px; font-weight: 500; color: #0b213a; font-family: 'Inter', sans-serif !important;">I agree to the <a href="terms-condition" target="_blank" rel="noopener" style="color: #18529c; font-weight: 700; text-decoration: underline;">Terms and Conditions</a> and <a href="privacy-policy" target="_blank" rel="noopener" style="color: #18529c; font-weight: 700; text-decoration: underline;">Privacy Policy</a> <i class="fas fa-external-link-alt" style="font-size: 11px; margin-left: 2px;"></i></span>
                    </label>
                    <div class="wf-error-msg" id="termsError" style="color: #ef4444; font-size: 12px; font-weight: 600; margin-top: -6px; padding-left: 30px;"></div>


                    <label class="terms-check" style="display: flex; gap: 12px; align-items: flex-start; cursor: pointer; margin: 0; padding: 12px; background: #f0f7ff; border-radius: 8px; border: 0; box-shadow: none;">
                        <input type="checkbox" class="my-checkbox" checked style="width: 18px; height: 18px; margin-top: 2px; accent-color: #18529c; cursor: pointer;">
                        <span style="font-size: 13px; line-height: 1.45; color: #334155; font-weight: 500; font-family: 'Inter', sans-serif !important;">You authorize Waqt Finance to contact you by phone, SMS, RCS, WhatsApp, or email about this application and related service updates. Promotional communication can be opted out anytime.</span>
                    </label>

                    <label class="terms-check" style="display: flex; gap: 12px; align-items: flex-start; cursor: pointer; margin: 0; padding: 12px; background: #f0f7ff; border-radius: 8px; border: 0; box-shadow: none;">
                        <input type="checkbox" class="my-checkbox-two" checked style="width: 18px; height: 18px; margin-top: 2px; accent-color: #18529c; cursor: pointer;">
                        <span style="font-size: 13px; line-height: 1.45; color: #334155; font-weight: 500; font-family: 'Inter', sans-serif !important;">You authorize need-based KYC, credit bureau, bank, employment, and lender checks for eligibility. Consent can be withdrawn subject to legal and processing requirements.</span>
                    </label>
                </div>
            </div>

            <!-- STEP 2: PERSONAL DETAILS -->
            <div class="wf-step-panel" id="step-2">
                <div class="wf-step-intro">
                    <h3>Personal Details</h3>
                    <p>Your name is auto-filled from the first step. Add address and contact details.</p>
                </div>

                <div class="wf-input-group">
                    <label class="wf-label">Applicant Name</label>
                    <div class="wf-input-wrapper">
                        <i class="fas fa-user wf-input-icon"></i>
                        <input type="text" class="wf-control" id="fullNameReview" readonly placeholder="Auto-filled from Step 1">
                    </div>
                </div>

                <div class="wf-form-grid">
                    <div class="wf-input-group">
                        <label class="wf-label">Email Address <span class="text-danger">*</span></label>
                        <div class="wf-input-wrapper">
                            <i class="fas fa-envelope wf-input-icon"></i>
                            <input type="email" class="wf-control" id="emailField" required placeholder="name@example.com">
                        </div>
                        <div class="wf-error-msg"></div>
                    </div>

                    <div class="wf-input-group">
                        <label class="wf-label">Pincode <span class="text-danger">*</span></label>
                        <div class="wf-input-wrapper">
                            <i class="fas fa-location-dot wf-input-icon"></i>
                            <input type="text" class="wf-control" id="pincodeField" required maxlength="6" placeholder="110001">
                        </div>
                        <div class="wf-field-hint" id="pincodeLookupStatus"></div>
                        <div class="wf-error-msg"></div>
                    </div>
                </div>

                <div class="wf-input-group">
                    <label class="wf-label">Current Address <span class="text-danger">*</span></label>
                    <div class="wf-input-wrapper">
                        <i class="fas fa-house wf-input-icon"></i>
                        <input type="text" class="wf-control" id="addressField" required placeholder="House no., street, area">
                    </div>
                    <div class="wf-error-msg"></div>
                </div>

                <div class="wf-form-grid">
                    <div class="wf-input-group">
                        <label class="wf-label">Landmark <span class="text-danger">*</span></label>
                        <div class="wf-input-wrapper">
                            <i class="fas fa-map-pin wf-input-icon"></i>
                            <input type="text" class="wf-control" id="landmarkField" required placeholder="Near bank, school, market">
                        </div>
                        <div class="wf-error-msg"></div>
                    </div>

                    <div class="wf-input-group">
                        <label class="wf-label">Residence City <span class="text-danger">*</span></label>
                        <div class="wf-input-wrapper">
                            <i class="fas fa-city wf-input-icon"></i>
                            <input type="text" class="wf-control" id="personalCity" required readonly placeholder="Auto-filled from pincode">
                        </div>
                        <div class="wf-error-msg"></div>
                    </div>
                </div>
            </div>

            <!-- STEP 3: KYC DETAILS -->
            <div class="wf-step-panel" id="step-3">
                <div class="wf-step-intro">
                    <h3>KYC Details</h3>
                    <p>Add Aadhaar and PAN details for identity verification.</p>
                </div>

                <div class="wf-input-group">
                    <label class="wf-label">PAN Card Number <span class="text-danger">*</span></label>
                    <div class="wf-input-wrapper">
                        <i class="fas fa-id-card wf-input-icon"></i>
                        <input type="text" class="wf-control" id="panField" required maxlength="10" style="text-transform:uppercase;" placeholder="ABCDE1234F">
                    </div>
                    <div class="wf-error-msg"></div>
                </div>

                <div class="wf-input-group">
                    <label class="wf-label">Aadhaar Number <span class="text-danger">*</span></label>
                    <div class="wf-input-wrapper">
                        <i class="fas fa-fingerprint wf-input-icon"></i>
                        <input type="text" class="wf-control" id="aadhaarField" required maxlength="12" placeholder="12-digit Aadhaar number">
                    </div>
                    <div class="wf-error-msg"></div>
                </div>

                <div class="wf-form-grid">
                    <div class="wf-input-group">
                        <label class="wf-label">Registration Type <span class="text-danger">*</span></label>
                        <div class="wf-input-wrapper">
                            <i class="fas fa-file-signature wf-input-icon"></i>
                            <select class="wf-control" id="registrationType" required>
                                <option value="">Select registration type</option>
                                <option value="GST Registered">GST Registered</option>
                                <option value="Udyam Registered">Udyam Registered</option>
                                <option value="Shop Act">Shop Act</option>
                                <option value="Trade License">Trade License</option>
                                <option value="Unregistered">Unregistered</option>
                            </select>
                        </div>
                        <div class="wf-error-msg"></div>
                    </div>

                    <div class="wf-input-group">
                        <label class="wf-label" id="businessProofLabel">Business Proof / Shop Registration <span class="text-muted">(Optional)</span></label>
                        <div class="wf-input-wrapper" style="display: flex; gap: 8px; width: 100%;">
                            <div style="position: relative; flex: 1;">
                                <i class="fas fa-store wf-input-icon"></i>
                                <input type="text" class="wf-control" id="businessProofField" maxlength="80" style="padding-right: 32px;" placeholder="GST, Shop Act, Udyam, if available">
                                <i class="fas fa-check-circle text-success" id="businessProofVerifiedIcon" style="display: none; position: absolute; right: 12px; top: 50%; transform: translateY(-50%); z-index: 5; color: #16a34a;"></i>
                            </div>
                            <button type="button" class="btn" id="btnVerifyBusiness" style="display: none; height: 48px; padding: 0 16px; border-radius: 8px; background-color: #18529c; border: none; color: #fff; font-weight: 600; cursor: pointer; transition: all 0.3s ease; white-space: nowrap;">Verify</button>
                        </div>
                        <div class="wf-error-msg" id="businessProofError"></div>
                        <div id="businessProofSuccessMsg" style="display: none; color: #16a34a; font-size: 13px; font-weight: 600; margin-top: 4px;"></div>
                    </div>
                </div>
            </div>

            <!-- STEP 4: BUSINESS DETAILS -->
            <div class="wf-step-panel" id="step-4">
                <div class="wf-step-intro">
                    <h3>Business Details</h3>
                    <p>Add shop profile, income details, and business photos for faster EDI loan review.</p>
                </div>

                <div class="wf-input-group">
                    <label class="wf-label">Shop / Business Name <span class="text-danger">*</span></label>
                    <div class="wf-input-wrapper">
                        <i class="fas fa-store wf-input-icon"></i>
                        <input type="text" class="wf-control" id="shopBusinessName" required placeholder="Enter shop or business name">
                    </div>
                    <div class="wf-error-msg"></div>
                </div>

                <div class="wf-input-group">
                    <label class="wf-label">Business Age <span class="text-danger">*</span></label>
                    <div class="wf-input-wrapper">
                        <i class="fas fa-calendar-check wf-input-icon"></i>
                        <select class="wf-control" id="businessAge" required>
                            <option value="">Select business age</option>
                            <option value="Less than 6 months">Less than 6 months</option>
                            <option value="6 months - 1 year">6 months - 1 year</option>
                            <option value="1 - 3 years">1 - 3 years</option>
                            <option value="3 - 5 years">3 - 5 years</option>
                            <option value="More than 5 years">More than 5 years</option>
                        </select>
                    </div>
                    <div class="wf-error-msg"></div>
                </div>

                <div class="wf-form-grid">
                    <div class="wf-input-group">
                        <label class="wf-label">Daily Sale / Income <span class="text-danger">*</span></label>
                        <div class="wf-input-wrapper">
                            <i class="fas fa-calculator wf-input-icon"></i>
                            <input type="text" class="wf-control" id="dailySales" required placeholder="e.g. 10,00,000" data-currency inputmode="numeric">
                        </div>
                        <div class="wf-error-msg"></div>
                    </div>

                    <div class="wf-input-group">
                        <label class="wf-label">Monthly Income <span class="text-danger">*</span></label>
                        <div class="wf-input-wrapper">
                            <i class="fas fa-chart-line wf-input-icon"></i>
                            <input type="text" class="wf-control" id="monthlyIncome" required placeholder="e.g. 3,00,000" data-currency inputmode="numeric">
                        </div>
                        <div class="wf-error-msg"></div>
                    </div>
                </div>

                <div class="wf-input-group">
                    <label class="wf-label">Business Type <span class="text-danger">*</span></label>
                    <div class="wf-input-wrapper">
                        <i class="fas fa-briefcase wf-input-icon"></i>
                        <select class="wf-control" id="businessType" required>
                            <option value="">Select business type</option>
                            <option value="Kirana / Grocery Store">Kirana / Grocery Store</option>
                            <option value="Retail Shop">Retail Shop</option>
                            <option value="Food / Restaurant">Food / Restaurant</option>
                            <option value="Garments / Boutique">Garments / Boutique</option>
                            <option value="Mobile / Electronics">Mobile / Electronics</option>
                            <option value="Medical / Pharmacy">Medical / Pharmacy</option>
                            <option value="Salon / Beauty">Salon / Beauty</option>
                            <option value="Repair / Services">Repair / Services</option>
                            <option value="Street Vendor">Street Vendor</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="wf-error-msg"></div>
                </div>

                <div class="wf-upload-grid">
                    <div class="wf-input-group">
                        <label class="wf-label">Front Shop Photo <span class="text-danger">*</span></label>
                        <label class="wf-upload-card" for="shopFrontPhoto">
                            <input type="file" id="shopFrontPhoto" accept="image/*" required>
                            <span class="wf-upload-icon"><i class="fas fa-upload"></i></span>
                            <span class="wf-upload-title">Upload Photo</span>
                            <span class="wf-upload-sub" id="shopFrontPhotoName">Shop front image</span>
                        </label>
                        <div class="wf-error-msg"></div>
                    </div>

                    <div class="wf-input-group">
                        <label class="wf-label">Stock Photo 1 <span class="text-danger">*</span></label>
                        <label class="wf-upload-card" for="stockPhoto">
                            <input type="file" id="stockPhoto" accept="image/*" required>
                            <span class="wf-upload-icon"><i class="fas fa-boxes-stacked"></i></span>
                            <span class="wf-upload-title">Upload Photo</span>
                            <span class="wf-upload-sub" id="stockPhotoName">Stock / inventory image 1</span>
                        </label>
                        <div class="wf-error-msg"></div>
                    </div>

                    <div class="wf-input-group">
                        <label class="wf-label">Stock Photo 2 <span class="text-danger">*</span></label>
                        <label class="wf-upload-card" for="stockPhotoTwo">
                            <input type="file" id="stockPhotoTwo" accept="image/*" required>
                            <span class="wf-upload-icon"><i class="fas fa-boxes-stacked"></i></span>
                            <span class="wf-upload-title">Upload Photo</span>
                            <span class="wf-upload-sub" id="stockPhotoTwoName">Stock / inventory image 2</span>
                        </label>
                        <div class="wf-error-msg"></div>
                    </div>
                </div>

                <div class="wf-section-title">Loan Requirement</div>

                <div class="wf-input-group">
                    <label class="wf-label">Loan Amount Required <span class="text-danger">*</span></label>
                    <div class="wf-input-wrapper">
                        <i class="fas fa-indian-rupee-sign wf-input-icon"></i>
                        <input type="text" class="wf-control" id="loanAmount" required placeholder="e.g. 5,00,000" data-currency inputmode="numeric">
                    </div>
                    <div class="wf-error-msg"></div>
                </div>

                <div class="wf-input-group">
                    <label class="wf-label">Purpose of Loan <span class="text-danger">*</span></label>
                    <div class="wf-input-wrapper">
                        <i class="fas fa-bullseye wf-input-icon"></i>
                        <input type="text" class="wf-control" id="purpose" required placeholder="Working capital, stock, etc.">
                    </div>
                    <div class="wf-error-msg"></div>
                </div>
            </div>


            <!-- STEP 5: DOCUMENT UPLOAD -->
            <div class="wf-step-panel" id="step-5">
                <div class="wf-doc-checklist">
                    <div class="wf-doc-header">
                        <h3>Document Upload</h3>
                        <p>Upload clear documents for verification</p>
                    </div>

                    <div class="wf-doc-upload-grid">
                        <label class="wf-doc-upload-card" for="panCardDoc">
                            <input type="file" id="panCardDoc" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                            <span class="wf-doc-icon"><i class="fas fa-file-arrow-up"></i></span>
                            <span>
                                <strong>PAN Card</strong>
                                <small id="panCardDocName">Click or upload document</small>
                            </span>
                            <div class="wf-error-msg"></div>
                        </label>

                        <label class="wf-doc-upload-card" for="aadhaarCardDoc">
                            <input type="file" id="aadhaarCardDoc" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                            <span class="wf-doc-icon"><i class="fas fa-file-arrow-up"></i></span>
                            <span>
                                <strong>Aadhaar Card</strong>
                                <small id="aadhaarCardDocName">Click or upload document</small>
                            </span>
                            <div class="wf-error-msg"></div>
                        </label>

                        <label class="wf-doc-upload-card" for="bankStatementDoc">
                            <input type="file" id="bankStatementDoc" accept=".jpg,.jpeg,.png,.webp,.pdf" required multiple>
                            <span class="wf-doc-icon"><i class="fas fa-file-arrow-up"></i></span>
                            <span>
                                <strong>Bank Statement Last 6 Months</strong>
                                <small id="bankStatementDocName">Click or upload documents</small>
                            </span>
                            <div class="wf-error-msg"></div>
                        </label>

                        <label class="wf-doc-upload-card" for="businessRegistrationDoc">
                            <input type="file" id="businessRegistrationDoc" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                            <span class="wf-doc-icon"><i class="fas fa-file-arrow-up"></i></span>
                            <span>
                                <strong>Business Registration Proof</strong>
                                <small id="businessRegistrationDocName">GST, Udyam, Shop Act, or Trade License</small>
                            </span>
                            <div class="wf-error-msg"></div>
                        </label>

                        <label class="wf-doc-upload-card" for="addressProofDoc">
                            <input type="file" id="addressProofDoc" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                            <span class="wf-doc-icon"><i class="fas fa-file-arrow-up"></i></span>
                            <span>
                                <strong>Address Proof</strong>
                                <small id="addressProofDocName">Click or upload document</small>
                            </span>
                            <div class="wf-error-msg"></div>
                        </label>

                        <label class="wf-doc-upload-card" for="passportPhotoDoc">
                            <input type="file" id="passportPhotoDoc" accept=".jpg,.jpeg,.png,.webp" required>
                            <span class="wf-doc-icon"><i class="fas fa-file-arrow-up"></i></span>
                            <span>
                                <strong>Passport Size Photo</strong>
                                <small id="passportPhotoDocName">Click or upload photo</small>
                            </span>
                            <div class="wf-error-msg"></div>
                        </label>
                    </div>

                    <div class="wf-doc-upload-note">
                        <span class="wf-doc-note-icon"><i class="fas fa-arrow-up-from-bracket"></i></span>
                        <span>
                            <strong>Accepted Formats</strong>
                            <small>Upload JPG, PNG, WEBP, or PDF files. Passport photo should be an image file.</small>
                        </span>
                    </div>
                </div>
            </div>

            <!-- STEP 6: BANK DETAILS -->
            <div class="wf-step-panel" id="step-6">
                <div class="wf-step-intro">
                    <h3>Bank Details</h3>
                    <p>Add account details where loan disbursal can be processed.</p>
                </div>

                <div class="wf-form-grid">
                    <div class="wf-input-group">
                        <label class="wf-label">IFSC Code <span class="text-danger">*</span></label>
                        <div class="wf-input-wrapper">
                            <i class="fas fa-code-branch wf-input-icon"></i>
                            <input type="text" class="wf-control" id="ifscCode" required maxlength="11" placeholder="ABCD0123456">
                        </div>
                        <div class="wf-field-hint" id="ifscLookupStatus"></div>
                        <div class="wf-error-msg"></div>
                    </div>

                    <div class="wf-input-group">
                        <label class="wf-label">Bank Name <span class="text-danger">*</span></label>
                        <div class="wf-input-wrapper">
                            <i class="fas fa-building-columns wf-input-icon"></i>
                            <input type="text" class="wf-control" id="bankName" required readonly placeholder="Auto-filled from IFSC">
                        </div>
                        <div class="wf-error-msg"></div>
                    </div>
                </div>

                <div class="wf-input-group">
                    <label class="wf-label">Branch Name</label>
                    <div class="wf-input-wrapper">
                        <i class="fas fa-location-dot wf-input-icon"></i>
                        <input type="text" class="wf-control" id="branchName" readonly placeholder="Auto-filled from IFSC">
                    </div>
                    <div class="wf-error-msg"></div>
                </div>

                <div class="wf-form-grid">
                    <div class="wf-input-group">
                        <label class="wf-label">Account Holder Name <span class="text-danger">*</span></label>
                        <div class="wf-input-wrapper">
                            <i class="fas fa-user-check wf-input-icon"></i>
                            <input type="text" class="wf-control" id="accountHolder" required placeholder="As per bank account">
                        </div>
                        <div class="wf-error-msg"></div>
                    </div>

                    <div class="wf-input-group">
                        <label class="wf-label">Account Number <span class="text-danger">*</span></label>
                        <div class="wf-input-wrapper">
                            <i class="fas fa-hashtag wf-input-icon"></i>
                            <input type="text" class="wf-control" id="accountNumber" required maxlength="18" placeholder="Enter account number">
                        </div>
                        <div class="wf-error-msg"></div>
                    </div>
                </div>
            </div>

            <!-- STEP 7: REFERENCE DETAILS -->
            <div class="wf-step-panel" id="step-7">
                <div class="wf-step-intro">
                    <h3>Reference Details</h3>
                    <p>Add two contacts our verification team can reach if required.</p>
                </div>

                <div class="wf-reference-grid">
                    <div class="wf-reference-block">
                        <div class="wf-reference-head">
                            <span>1</span>
                            <div>
                                <h4>Reference 1</h4>
                                <p>Primary reference contact</p>
                            </div>
                        </div>
                        <div class="wf-input-group">
                            <label class="wf-label">Full Name <span class="text-danger">*</span></label>
                            <div class="wf-input-wrapper">
                                <i class="fas fa-user wf-input-icon"></i>
                                <input type="text" class="wf-control" id="primaryRefName" required placeholder="Enter full name">
                            </div>
                            <div class="wf-error-msg"></div>
                        </div>
                        <div class="wf-input-group">
                            <label class="wf-label">Mobile Number <span class="text-danger">*</span></label>
                            <div class="wf-input-wrapper">
                                <i class="fas fa-mobile-alt wf-input-icon"></i>
                                <input type="text" class="wf-control" id="primaryRefMobile" required maxlength="10" placeholder="10-digit mobile">
                            </div>
                            <div class="wf-error-msg"></div>
                        </div>
                        <div class="wf-input-group">
                            <label class="wf-label">Relation <span class="text-danger">*</span></label>
                            <div class="wf-input-wrapper">
                                <i class="fas fa-people-arrows wf-input-icon"></i>
                                <select class="wf-control" id="primaryRefRelation" required>
                                    <option value="">Select relation</option>
                                    <option>Father</option><option>Mother</option><option>Brother</option><option>Sister</option>
                                    <option>Spouse</option><option>Friend</option><option>Relative</option><option>Colleague</option><option>Guarantor</option>
                                </select>
                            </div>
                            <div class="wf-error-msg"></div>
                        </div>
                    </div>

                    <div class="wf-reference-block">
                        <div class="wf-reference-head">
                            <span>2</span>
                            <div>
                                <h4>Reference 2</h4>
                                <p>Secondary reference contact</p>
                            </div>
                        </div>
                        <div class="wf-input-group">
                            <label class="wf-label">Full Name <span class="text-danger">*</span></label>
                            <div class="wf-input-wrapper">
                                <i class="fas fa-user wf-input-icon"></i>
                                <input type="text" class="wf-control" id="secondaryRefName" required placeholder="Enter full name">
                            </div>
                            <div class="wf-error-msg"></div>
                        </div>
                        <div class="wf-input-group">
                            <label class="wf-label">Mobile Number <span class="text-danger">*</span></label>
                            <div class="wf-input-wrapper">
                                <i class="fas fa-mobile-alt wf-input-icon"></i>
                                <input type="text" class="wf-control" id="secondaryRefMobile" required maxlength="10" placeholder="10-digit mobile">
                            </div>
                            <div class="wf-error-msg"></div>
                        </div>
                        <div class="wf-input-group">
                            <label class="wf-label">Relation <span class="text-danger">*</span></label>
                            <div class="wf-input-wrapper">
                                <i class="fas fa-people-arrows wf-input-icon"></i>
                                <select class="wf-control" id="secondaryRefRelation" required>
                                    <option value="">Select relation</option>
                                    <option>Father</option><option>Mother</option><option>Brother</option><option>Sister</option>
                                    <option>Spouse</option><option>Friend</option><option>Relative</option><option>Colleague</option><option>Guarantor</option>
                                </select>
                            </div>
                            <div class="wf-error-msg"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 8: SELFIE UPLOAD -->
            <div class="wf-step-panel" id="step-8">
                <div class="wf-step-intro">
                    <h3>Selfie Capture</h3>
                    <p>Open camera and capture a clear live selfie for identity verification.</p>
                </div>

                <div class="wf-selfie-wrap">
                    <div class="wf-selfie-card" id="selfieCard">
                        <span class="wf-selfie-icon"><i class="fas fa-camera-retro"></i></span>
                        <span class="wf-selfie-title">Live Selfie Capture</span>
                        <span class="wf-selfie-sub" id="selfieDocName">Camera not started</span>
                        <video id="selfieVideo" class="wf-selfie-video" autoplay playsinline muted style="display:none;"></video>
                        <canvas id="selfieCanvas" style="display:none;"></canvas>
                        <img id="selfiePreview" class="wf-selfie-preview" alt="Captured selfie preview" style="display:none;">
                        <div class="wf-selfie-actions">
                            <button type="button" class="wf-mini-btn" id="startSelfieBtn"><i class="fas fa-video"></i> Open Camera</button>
                            <button type="button" class="wf-mini-btn wf-mini-primary" id="captureSelfieBtn" style="display:none;"><i class="fas fa-camera"></i> Capture</button>
                            <button type="button" class="wf-mini-btn" id="retakeSelfieBtn" style="display:none;"><i class="fas fa-rotate-left"></i> Retake</button>
                        </div>
                        <div class="wf-error-msg"></div>
                    </div>
                </div>
            </div>

            <!-- STEP 9: REVIEW & SUBMIT -->
            <div class="wf-step-panel" id="step-9">
                <div class="wf-review-hero">
                    <div class="wf-review-icon">
                        <i class="fas fa-shield-check"></i>
                    </div>
                    <div>
                        <h3>Final Review</h3>
                        <p>Check your EDI loan application details before sending it to our team.</p>
                    </div>
                </div>

                <div class="wf-review-grid" id="reviewSummary"></div>

                <!-- Mandatory Details Confirmation Checkbox -->
                <div class="wf-input-group wf-declaration-group" style="margin-bottom: 24px;">
                    <div class="wf-declaration-box" style="background: #f8fafc; border: 1px dashed #cbd5e1; padding: 14px 18px; border-radius: 10px; display: flex; align-items: flex-start; gap: 12px; box-sizing: border-box; width: 100%;">
                        <input type="checkbox" id="confirmDetails" required style="width: 18px; height: 18px; margin-top: 2px; accent-color: #18529c; cursor: pointer; flex-shrink: 0;">
                        <label for="confirmDetails" style="font-size: 13.5px; color: #475569; font-weight: 500; cursor: pointer; user-select: none; line-height: 1.4; margin: 0;">
                            I hereby confirm that all the details provided in this application are correct and verified. I authorize Waqt Finance to process my daily installment loan application.
                        </label>
                    </div>
                    <div class="wf-error-msg" style="color: #ef4444; font-size: 12px; font-weight: 600; margin-top: 6px; padding-left: 30px;"></div>
                </div>

                <div class="wf-auto-submit-status" id="autoSubmitStatus" style="display: none;">
                    <span><i class="fas fa-spinner fa-spin"></i></span>
                    <strong>Submitting application...</strong>
                    <small>Please wait while we securely save your details.</small>
                </div>

            </div>


            <!-- Actions -->
            <div class="wf-form-actions">
                <button type="button" class="wf-btn wf-btn-secondary" id="btnPrev" style="display:none;">
                    <i class="fas fa-chevron-left me-2"></i> Back
                </button>
                <button type="button" class="wf-btn wf-btn-primary" id="btnNext">
                    Save & Continue <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </div>

        </form>


        <div class="wf-security-footer">
            <div class="wf-trust-badge">
                <i class="fas fa-lock"></i>
                <span>SSL Encrypted</span>
            </div>
            <div class="wf-trust-badge">
                <i class="fas fa-check-circle"></i>
                <span>NBFC Registered</span>
            </div>
        </div>
    </div>
    
    <div class="wf-bottom-security-text" style="text-align: center; margin-top: 24px; color: #64748b; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 8px;">
        <i class="fas fa-lock" style="color: #64748b; font-size: 13px;"></i>
        <span style="font-family: 'Inter', sans-serif !important;">Your data is encrypted in transit and used only for this application, verification, lender matching, and legally required records.</span>
    </div>
</div>

<!-- PAN Verification Modal -->
<div class="wf-pan-overlay" id="panVerifyModal" aria-hidden="true">
    <div class="wf-pan-modal" role="dialog" aria-modal="true" aria-labelledby="panVerifyTitle">
        <button type="button" class="wf-pan-close" id="panCloseBtn" aria-label="Close PAN verification">
            <i class="fas fa-xmark"></i>
        </button>
        <h2 class="wf-pan-title" id="panVerifyTitle">Are These Information Correct?</h2>

        <div class="wf-pan-card">
            <div class="wf-pan-heading">
                <span>PAN VERIFICATION DETAILS</span>
                <img src="assets/images/ashoka4-pillers.png" alt="Ashoka emblem" class="wf-pan-emblem">
            </div>
            <div class="wf-pan-loading" id="panLoadingState">
                <i class="fas fa-spinner fa-spin"></i>
                <span>Fetching PAN details...</span>
            </div>
            <div class="wf-pan-error" id="panVerifyError"></div>
            <div class="wf-pan-details" id="panDetailsBox" style="display:none;">
                <div class="wf-pan-column">
                    <div class="wf-pan-label">Name:</div>
                    <div class="wf-pan-value" id="panNameValue">-</div>

                    <div class="wf-pan-label">Date of Birth:</div>
                    <div class="wf-pan-value" id="panDobValue">-</div>

                    <div class="wf-pan-label">Aadhaar:</div>
                    <div class="wf-pan-value" id="panAadhaarValue">-</div>
                </div>
                <div class="wf-pan-column">
                    <div class="wf-pan-label">PAN:</div>
                    <div class="wf-pan-value" id="panNumberValue">-</div>

                    <div class="wf-pan-label">Gender:</div>
                    <div class="wf-pan-value" id="panGenderValue">-</div>

                    <div class="wf-pan-label">Status:</div>
                    <div class="wf-pan-value text-success-custom" id="panStatusValue">-</div>
                </div>
            </div>
        </div>

        <div class="wf-pan-actions">
            <button type="button" class="wf-pan-action wf-pan-no" id="panRejectBtn">No</button>
            <button type="button" class="wf-pan-action wf-pan-yes" id="panConfirmBtn" disabled>Yes, it is correct</button>
        </div>
    </div>
</div>
</div>

<!-- Location Blocker Overlay -->
<div id="wf-location-blocker" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); z-index: 100000; align-items: center; justify-content: center; padding: 24px; box-sizing: border-box; color: #fff; text-align: center; font-family: 'Inter', sans-serif;">
    <div style="max-width: 450px; background: #ffffff; padding: 36px 24px; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); color: #1e293b;">
        <div style="font-size: 54px; color: #18529c; margin-bottom: 20px;">
            <i class="fas fa-location-crosshairs fa-spin" style="--fa-animation-duration: 4s;"></i>
        </div>
        <h2 style="font-size: 22px; font-weight: 700; color: #0f2742; margin-bottom: 12px; margin-top: 0; font-family: 'Inter', sans-serif !important;">Location Access Required</h2>
        <p style="font-size: 14.5px; color: #64748b; line-height: 1.6; margin-bottom: 24px; font-family: 'Inter', sans-serif !important;">
            To ensure secure credit processing and verify your application region, Waqt Finance requires active location access. Please allow location permissions in your browser settings to continue.
        </p>
        <button type="button" id="btnRequestLocationRetry" style="width: 100%; padding: 14px 24px; background: #18529c; color: #fff; font-weight: 600; border: none; border-radius: 8px; font-size: 14px; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-family: 'Inter', sans-serif !important; box-shadow: 0 4px 6px -1px rgba(24,82,156,0.2);">
            <i class="fas fa-location-arrow"></i> Allow Location Access
        </button>
    </div>
</div>

<!-- Business Verification Modal -->
<div class="wf-pan-overlay" id="businessModal" aria-hidden="true" style="display: none; align-items: center; justify-content: center; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15,23,42,0.6); z-index: 9999;">
    <div class="wf-pan-modal" role="dialog" aria-modal="true" style="background: #fff; width: 90%; max-width: 500px; padding: 24px; border-radius: 16px; position: relative; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <button type="button" class="wf-pan-close" id="businessCloseBtn" style="position: absolute; right: 16px; top: 16px; background: none; border: none; font-size: 20px; cursor: pointer; color: #64748b;">
            <i class="fas fa-xmark"></i>
        </button>
        <h2 class="wf-pan-title" style="font-size: 20px; font-weight: 700; color: #0f2742; margin-bottom: 20px;">Confirm Business Details</h2>

        <div class="wf-pan-card" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; background: #f8fafc; margin-bottom: 20px;">
            <div class="wf-pan-heading" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 12px;">
                <span style="font-size: 12px; font-weight: 800; color: #64748b; letter-spacing: 0.5px;">VERIFIED BUSINESS PROFILE</span>
                <i class="fas fa-shield-alt text-success" style="color: #16a34a; font-size: 16px;"></i>
            </div>
            
            <div class="wf-pan-loading" id="businessLoadingState" style="display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 20px 0;">
                <i class="fas fa-spinner fa-spin" style="font-size: 24px; color: #18529c;"></i>
                <span style="font-size: 14px; color: #64748b;">Verifying details via Digitap...</span>
            </div>
            
            <div class="wf-pan-error" id="businessVerifyError" style="color: #dc2626; font-size: 13.5px; font-weight: 600; text-align: center; display: none; padding: 10px 0;"></div>
            
            <div class="wf-pan-details" id="businessDetailsBox" style="display: none; grid-template-columns: 1fr; gap: 8px; font-size: 14px;">
                <div>
                    <strong style="color: #0f2742;">Business Name:</strong>
                    <div id="bizNameValue" style="color: #475569; margin-top: 2px; font-weight: 600;">-</div>
                </div>
                <div style="margin-top: 8px;">
                    <strong style="color: #0f2742;">GSTIN / Reg No:</strong>
                    <div id="bizRegValue" style="color: #475569; margin-top: 2px;">-</div>
                </div>
                <div style="margin-top: 8px;" id="bizExtraInfoBox">
                    <strong style="color: #0f2742;" id="bizExtraLabel">Type:</strong>
                    <div id="bizExtraValue" style="color: #475569; margin-top: 2px;">-</div>
                </div>
                <div style="margin-top: 8px;" id="bizAddressBox">
                    <strong style="color: #0f2742;">Business Address:</strong>
                    <div id="bizAddressValue" style="color: #475569; margin-top: 2px;">-</div>
                </div>
            </div>
        </div>

        <div class="wf-pan-actions" style="display: flex; gap: 12px; justify-content: flex-end;">
            <button type="button" class="btn btn-secondary" id="businessRejectBtn" style="padding: 10px 16px; border-radius: 8px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-weight: 600; color: #475569;">No, edit</button>
            <button type="button" class="btn btn-primary" id="businessConfirmBtn" style="padding: 10px 16px; border-radius: 8px; background: #18529c; border: none; color: #fff; cursor: pointer; font-weight: 600;" disabled>Confirm & Auto-fill</button>
        </div>
    </div>
</div>

<!-- OTP Verification Modal -->
<div class="wf-otp-overlay" id="otpModal" aria-hidden="true">
    <div class="wf-otp-modal" role="dialog" aria-modal="true" aria-labelledby="otpModalTitle">
        <button type="button" class="wf-otp-close" id="otpCloseBtn" aria-label="Close OTP verification">
            <i class="fas fa-xmark"></i>
        </button>
        <div class="wf-otp-icon">
            <i class="fas fa-shield-halved"></i>
        </div>
        <h2 id="otpModalTitle">Verify Mobile Number</h2>
        <p>Enter the 6-digit OTP sent to <strong id="otpMobileText"></strong></p>

        <input type="hidden" id="otpField" maxlength="6">
        <div class="wf-otp-digits" id="otpDigits">
            <input type="text" class="wf-otp-digit" inputmode="numeric" maxlength="1" aria-label="OTP digit 1">
            <input type="text" class="wf-otp-digit" inputmode="numeric" maxlength="1" aria-label="OTP digit 2">
            <input type="text" class="wf-otp-digit" inputmode="numeric" maxlength="1" aria-label="OTP digit 3">
            <input type="text" class="wf-otp-digit" inputmode="numeric" maxlength="1" aria-label="OTP digit 4">
            <input type="text" class="wf-otp-digit" inputmode="numeric" maxlength="1" aria-label="OTP digit 5">
            <input type="text" class="wf-otp-digit" inputmode="numeric" maxlength="1" aria-label="OTP digit 6">
        </div>

        <div class="wf-otp-error" id="otpErrorMsg"></div>
        <div class="wf-otp-meta">
            <span>Time remaining: <strong id="otpTimer">01:00</strong></span>
            <span id="otpAttemptsInfo">Attempts remaining: 5</span>
        </div>
        <button type="button" class="wf-btn wf-btn-primary wf-otp-verify" id="otpVerifyBtn">
            Verify OTP <i class="fas fa-check-circle ms-2"></i>
        </button>
        <div class="wf-otp-actions">
            <a href="javascript:void(0)" id="resendOtpBtn" style="pointer-events: none; opacity: 0.5;">Resend OTP</a>
            <span>|</span>
            <a href="javascript:void(0)" id="changeNumberBtn">Change Number</a>
        </div>
    </div>
</div>


<!--popup-->

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

const checkbox = document.getElementById("termsCheckbox");
const modal = document.getElementById("termsModal");
const acceptBtn = document.getElementById("acceptBtn");
const declineBtn = document.getElementById("declineBtn");
const termsError = document.getElementById("termsError");

function syncTermsUi(isAccepted) {
    const group = checkbox.closest(".terms-check");
    group?.classList.toggle("error", false);
    group?.classList.toggle("success", Boolean(isAccepted));
    checkbox.classList.toggle("is-invalid", !isAccepted);
    if (termsError) termsError.innerText = "";
    checkbox.dispatchEvent(new Event("change", { bubbles: true }));
    checkbox.dispatchEvent(new Event("input", { bubbles: true }));
}

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
    syncTermsUi(true);
    modal.style.display = "none";
});

/* Decline button */
declineBtn.addEventListener("click", function(){

    checkbox.checked = false;
    syncTermsUi(false);
    modal.style.display = "none";
});

</script>


<script>
    class EDILoanApplication {
        constructor() {
            this.currentStep = 1;
            this.totalSteps = 9;
            this.form = document.getElementById('applyForm');
            this.panels = document.querySelectorAll('.wf-step-panel');
            this.nodes = document.querySelectorAll('.wf-step-node');
            this.progressLine = document.querySelector('.wf-progress-line');
            this.btnPrev = document.getElementById('btnPrev');
            this.btnNext = document.getElementById('btnNext');
            this.otpModal = document.getElementById('otpModal');
            this.otpVerifyBtn = document.getElementById('otpVerifyBtn');
            this.otpDigitInputs = Array.from(document.querySelectorAll('.wf-otp-digit'));
            this.panModal = document.getElementById('panVerifyModal');
            this.panVerifyTimer = null;
            this.panVerified = false;
            this.panConfirmed = false;
            this.panVerifiedNumber = '';
            this.csrfToken = window.WAQT_CSRF_TOKEN || '';
            this.timerInterval = null;
            this.otpAttempts = 0;
            this.maxAttempts = 5;
            this.otpSent = false;
            this.otpVerified = false;
            this.pincodeLookupController = null;
            this.ifscLookupController = null;
            this.selfieStream = null;
            this.selfieBlob = null;
            this.draftKey = 'waqt_edi_loan_apply_draft_v2';
            this.stepTitles = [
                'Basic Details',
                'Personal Details',
                'KYC Details',
                'Business Details',
                'Document Upload',
                'Bank Details',
                'Reference Details',
                'Selfie Capture',
                'Final Review'
            ];
            this.draftTimer = null;
            this.restoreInProgress = false;
            this.autoSubmitStarted = false;
            this.submittedApplication = false;

            this.init();
        }

        init() {
            if (!this.form) return;

            this.btnNext.addEventListener('click', () => this.nextStep());
            this.btnPrev.addEventListener('click', () => this.prevStep());
            this.restoreDraft();

            this.form.querySelectorAll('input, select').forEach(input => {
                input.addEventListener('blur', (e) => this.validateField(e.target));
                input.addEventListener('input', (e) => {
                    if (e.target.classList.contains('is-invalid') || e.target.closest('.wf-input-group')?.classList.contains('error')) {
                        this.validateField(e.target);
                    }
                    this.handleMasking(e.target);
                    this.queueDraftSave();
                });
                input.addEventListener('change', () => this.queueDraftSave());
            });

            document.getElementById('pincodeField').addEventListener('input', (e) => this.handlePincodeInput(e.target.value));
            document.getElementById('ifscCode').addEventListener('input', (e) => this.handleIfscInput(e.target.value));
            this.bindFileNamePreview('shopFrontPhoto', 'shopFrontPhotoName');
            this.bindFileNamePreview('stockPhoto', 'stockPhotoName');
            this.bindFileNamePreview('stockPhotoTwo', 'stockPhotoTwoName');
            this.bindFileNamePreview('panCardDoc', 'panCardDocName');
            this.bindFileNamePreview('aadhaarCardDoc', 'aadhaarCardDocName');
            this.bindFileNamePreview('bankStatementDoc', 'bankStatementDocName');
            this.bindFileNamePreview('businessRegistrationDoc', 'businessRegistrationDocName');
            this.bindFileNamePreview('addressProofDoc', 'addressProofDocName');
            this.bindFileNamePreview('passportPhotoDoc', 'passportPhotoDocName');
            document.getElementById('startSelfieBtn').addEventListener('click', () => this.startSelfieCamera());
            document.getElementById('captureSelfieBtn').addEventListener('click', () => this.captureSelfie());
            document.getElementById('retakeSelfieBtn').addEventListener('click', () => this.retakeSelfie());

            document.getElementById('resendOtpBtn').addEventListener('click', () => this.sendOTP());
            document.getElementById('changeNumberBtn').addEventListener('click', () => this.resetOTP());
            document.getElementById('otpCloseBtn').addEventListener('click', () => this.closeOtpModal());
            this.otpVerifyBtn.addEventListener('click', () => this.verifyOTPAndContinue());
            this.otpModal.addEventListener('click', (e) => {
                if (e.target === this.otpModal) this.closeOtpModal();
            });
            document.getElementById('panCloseBtn').addEventListener('click', () => this.closePanModal());
            document.getElementById('panRejectBtn').addEventListener('click', () => this.rejectPanDetails());
            document.getElementById('panConfirmBtn').addEventListener('click', () => this.confirmPanDetails());
            this.panModal.addEventListener('click', (e) => {
                if (e.target === this.panModal) this.closePanModal();
            });

            // Digitap Business Proof Verification Hooks
            this.businessVerified = false;
            this.businessConfirmed = false;
            this.businessVerifiedNumber = '';
            this.businessVerifiedName = '';
            this.businessVerifiedDetails = '';
            this.businessAddress = '';
            this.faceModelsLoaded = false;
            this.latitude = '';
            this.longitude = '';
            this.gpsAddress = '';
            this.deviceFingerprint = this.generateDeviceFingerprint();

            document.getElementById('registrationType').addEventListener('change', (e) => this.handleRegistrationTypeChange(e.target.value));
            document.getElementById('btnVerifyBusiness').addEventListener('click', () => this.verifyBusinessProof());
            document.getElementById('businessCloseBtn').addEventListener('click', () => this.closeBusinessModal());
            document.getElementById('businessRejectBtn').addEventListener('click', () => this.closeBusinessModal());
            document.getElementById('businessConfirmBtn').addEventListener('click', () => this.confirmBusinessDetails());
            document.getElementById('businessModal').addEventListener('click', (e) => {
                if (e.target === document.getElementById('businessModal')) this.closeBusinessModal();
            });

            this.otpDigitInputs.forEach((input, index) => {
                input.addEventListener('input', (e) => this.handleOtpDigitInput(e, index));
                input.addEventListener('keydown', (e) => this.handleOtpDigitKeydown(e, index));
                input.addEventListener('paste', (e) => this.handleOtpPaste(e));
            });

            this.updateUI();
            this.checkAndRequestLocation();
        }

        draftFieldIds() {
            return [
                'fullName', 'phoneField', 'employeeCode', 'emailField', 'addressField', 'landmarkField',
                'personalCity', 'pincodeField', 'panField', 'aadhaarField', 'businessProofField',
                'shopBusinessName', 'registrationType', 'businessAge', 'dailySales', 'monthlyIncome',
                'businessType', 'loanAmount', 'purpose', 'ifscCode', 'bankName', 'branchName',
                'accountHolder', 'accountNumber', 'primaryRefName', 'primaryRefMobile',
                'primaryRefRelation', 'secondaryRefName', 'secondaryRefMobile', 'secondaryRefRelation',
                'termsCheckbox'
            ];
        }

        queueDraftSave() {
            if (this.restoreInProgress) return;
            clearTimeout(this.draftTimer);
            this.draftTimer = setTimeout(() => this.saveDraft(), 350);
        }

        saveDraft() {
            try {
                const values = {};
                this.draftFieldIds().forEach(id => {
                    const field = document.getElementById(id);
                    if (!field || field.type === 'file') return;
                    values[id] = field.type === 'checkbox' ? field.checked : field.value;
                });
                localStorage.setItem(this.draftKey, JSON.stringify({
                    time: Date.now(),
                    values
                }));
                this.setDraftStatus('');
            } catch (error) {
                this.setDraftStatus('');
            }
        }

        restoreDraft() {
            try {
                const raw = localStorage.getItem(this.draftKey);
                if (!raw) return;
                const draft = JSON.parse(raw);
                if (!draft?.values || Date.now() - draft.time > 7 * 86400000) {
                    localStorage.removeItem(this.draftKey);
                    return;
                }
                this.restoreInProgress = true;
                Object.entries(draft.values).forEach(([id, value]) => {
                    const field = document.getElementById(id);
                    if (!field || field.type === 'file') return;
                    if (field.type === 'checkbox') {
                        field.checked = Boolean(value);
                    } else {
                        field.value = value;
                        if (field.id === 'panField') field.value = field.value.toUpperCase().substring(0, 10);
                        if (['phoneField', 'aadhaarField', 'pincodeField', 'accountNumber', 'primaryRefMobile', 'secondaryRefMobile'].includes(field.id)) {
                            const limits = {
                                phoneField: 10,
                                aadhaarField: 12,
                                pincodeField: 6,
                                accountNumber: 18,
                                primaryRefMobile: 10,
                                secondaryRefMobile: 10
                            };
                            field.value = field.value.replace(/\D/g, '').substring(0, limits[field.id]);
                        }
                        if (field.id === 'ifscCode') field.value = field.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().substring(0, 11);
                    }
                });
                this.restoreInProgress = false;
                this.setDraftStatus('');

                // Trigger registration type change handler to restore button and placeholder UI states
                const regTypeSelect = document.getElementById('registrationType');
                if (regTypeSelect && regTypeSelect.value) {
                    const savedProofValue = document.getElementById('businessProofField') ? document.getElementById('businessProofField').value : '';
                    this.handleRegistrationTypeChange(regTypeSelect.value, true);
                    const proofField = document.getElementById('businessProofField');
                    if (proofField && savedProofValue) {
                        proofField.value = savedProofValue;
                    }
                }
            } catch (error) {
                this.restoreInProgress = false;
                localStorage.removeItem(this.draftKey);
            }
        }

        clearDraft() {
            try {
                localStorage.removeItem(this.draftKey);
            } catch (error) {}
            this.setDraftStatus('');
        }

        setDraftStatus(message) {
            return;
        }

        handleMasking(field) {
            if (field.id === 'panField') {
                field.value = field.value.toUpperCase().substring(0, 10);
                this.handlePanInput(field.value);
            }
            if (field.id === 'phoneField') {
                field.value = field.value.replace(/\D/g, '').substring(0, 10);
            }
            if (field.id === 'otpField') {
                field.value = field.value.replace(/\D/g, '').substring(0, 6);
            }
            if (field.id === 'aadhaarField') {
                field.value = field.value.replace(/\D/g, '').substring(0, 12);
            }
            if (field.id === 'pincodeField') {
                field.value = field.value.replace(/\D/g, '').substring(0, 6);
            }
            if (field.id === 'ifscCode') {
                field.value = field.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().substring(0, 11);
            }
            if (field.id === 'accountNumber') {
                field.value = field.value.replace(/\D/g, '').substring(0, 18);
            }
            if (field.id === 'primaryRefMobile' || field.id === 'secondaryRefMobile') {
                field.value = field.value.replace(/\D/g, '').substring(0, 10);
            }
        }

        bindFileNamePreview(inputId, labelId) {
            const input = document.getElementById(inputId);
            const label = document.getElementById(labelId);
            if (!input || !label) return;
            const defaultText = label.innerText;
            
            const card = input.closest('.wf-upload-card, .wf-doc-upload-card, .wf-selfie-card');
            
            const updatePreview = () => {
                const files = input.files ? Array.from(input.files) : [];
                
                // Remove existing preview container if any
                const existingPreview = card?.querySelector('.wf-upload-preview-container');
                if (existingPreview) {
                    existingPreview.remove();
                }
                
                if (files.length === 0) {
                    card?.classList.remove('wf-has-preview');
                    card?.classList.remove('has-file');
                    label.innerText = defaultText;
                    return;
                }
                
                // We have files!
                card?.classList.add('wf-has-preview');
                card?.classList.add('has-file');
                
                const container = document.createElement('div');
                container.className = 'wf-upload-preview-container';
                
                // Create Remove button
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'wf-upload-preview-remove';
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                removeBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    e.preventDefault();
                    input.value = '';
                    // Dispatch change event to trigger updatePreview
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });
                container.appendChild(removeBtn);
                
                const file = files[0];
                const isImage = file.type.startsWith('image/');
                
                if (isImage) {
                    // Create image preview
                    const img = document.createElement('img');
                    img.className = 'wf-upload-preview-image';
                    img.alt = 'Uploaded image preview';
                    img.src = URL.createObjectURL(file);
                    // Free memory when image loads
                    img.onload = () => URL.revokeObjectURL(img.src);
                    container.appendChild(img);
                } else {
                    // Create document icon (PDF or other)
                    const icon = document.createElement('div');
                    icon.className = 'wf-upload-preview-icon';
                    icon.innerHTML = file.type === 'application/pdf' 
                        ? '<i class="fas fa-file-pdf"></i>' 
                        : '<i class="fas fa-file-lines"></i>';
                    container.appendChild(icon);
                }
                
                // Add filename and size meta
                const nameEl = document.createElement('div');
                nameEl.className = 'wf-upload-preview-filename';
                nameEl.innerText = files.length > 1 ? `${files.length} files selected` : file.name;
                container.appendChild(nameEl);
                
                if (files.length === 1) {
                    const sizeKB = (file.size / 1024).toFixed(1);
                    const metaEl = document.createElement('div');
                    metaEl.className = 'wf-upload-preview-meta';
                    metaEl.innerText = `${sizeKB} KB`;
                    container.appendChild(metaEl);
                }
                
                card.appendChild(container);
            };
            
            input.addEventListener('change', updatePreview);
        }

        handlePincodeInput(value) {
            const pincode = value.replace(/\D/g, '').substring(0, 6);
            const cityField = document.getElementById('personalCity');
            const status = document.getElementById('pincodeLookupStatus');

            cityField.value = '';
            cityField.closest('.wf-input-group')?.classList.remove('success', 'error');
            if (status) {
                status.className = 'wf-field-hint';
                status.innerText = '';
            }

            if (this.pincodeLookupController) {
                this.pincodeLookupController.abort();
                this.pincodeLookupController = null;
            }

            if (pincode.length !== 6) return;

            if (status) {
                status.className = 'wf-field-hint loading';
                status.innerText = 'Fetching city from pincode...';
            }

            const cached = this.readPincodeCache(pincode);
            if (cached) {
                this.applyPincodeDetails(cached);
                return;
            }

            this.pincodeLookupController = new AbortController();
            fetch(`api/apply/get_pincode_details.php?pincode=${encodeURIComponent(pincode)}`, {
                    signal: this.pincodeLookupController.signal
                })
                .then(res => this.parseJsonResponse(res))
                .then(data => {
                    if (data.status === 'success' && data.city) {
                        this.writePincodeCache(pincode, data);
                        this.applyPincodeDetails(data);
                    } else {
                        this.applyPincodeError(data.message || 'Invalid pincode');
                    }
                })
                .catch(err => {
                    if (err.name !== 'AbortError') {
                        this.applyPincodeError('Unable to fetch city. Please check pincode.');
                    }
                });
        }

        readPincodeCache(pincode) {
            try {
                const raw = sessionStorage.getItem(`wf_pincode_${pincode}`);
                if (!raw) return null;
                const cached = JSON.parse(raw);
                if (!cached || Date.now() - cached.time > 86400000) return null;
                return cached.data;
            } catch (error) {
                return null;
            }
        }

        writePincodeCache(pincode, data) {
            try {
                sessionStorage.setItem(`wf_pincode_${pincode}`, JSON.stringify({
                    time: Date.now(),
                    data
                }));
            } catch (error) {}
        }

        applyPincodeDetails(data) {
            const cityField = document.getElementById('personalCity');
            const status = document.getElementById('pincodeLookupStatus');
            cityField.value = data.city || '';
            cityField.closest('.wf-input-group')?.classList.add('success');
            if (status) {
                status.className = 'wf-field-hint success';
                status.innerText = '';
            }
        }

        applyPincodeError(message) {
            const cityField = document.getElementById('personalCity');
            const status = document.getElementById('pincodeLookupStatus');
            cityField.value = '';
            if (status) {
                status.className = 'wf-field-hint error';
                status.innerText = message;
            }
        }

        handleIfscInput(value) {
            const ifsc = value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().substring(0, 11);
            const bankField = document.getElementById('bankName');
            const branchField = document.getElementById('branchName');
            const status = document.getElementById('ifscLookupStatus');

            bankField.value = '';
            branchField.value = '';
            bankField.closest('.wf-input-group')?.classList.remove('success', 'error');
            if (status) {
                status.className = 'wf-field-hint';
                status.innerText = '';
            }

            if (this.ifscLookupController) {
                this.ifscLookupController.abort();
                this.ifscLookupController = null;
            }

            if (ifsc.length !== 11) return;

            if (status) {
                status.className = 'wf-field-hint loading';
                status.innerText = 'Fetching bank details...';
            }

            const cached = this.readIfscCache(ifsc);
            if (cached) {
                this.applyIfscDetails(cached);
                return;
            }

            this.ifscLookupController = new AbortController();
            fetch(`api/apply/get_bank_details.php?ifsc=${encodeURIComponent(ifsc)}`, {
                    signal: this.ifscLookupController.signal
                })
                .then(res => this.parseJsonResponse(res))
                .then(data => {
                    if (data.status === 'success' && data.bank) {
                        this.writeIfscCache(ifsc, data);
                        this.applyIfscDetails(data);
                    } else {
                        this.applyIfscError(data.message || 'Invalid IFSC code');
                    }
                })
                .catch(err => {
                    if (err.name !== 'AbortError') {
                        this.applyIfscError('Unable to fetch bank details');
                    }
                });
        }

        readIfscCache(ifsc) {
            try {
                const raw = sessionStorage.getItem(`wf_ifsc_${ifsc}`);
                if (!raw) return null;
                const cached = JSON.parse(raw);
                if (!cached || Date.now() - cached.time > 86400000) return null;
                return cached.data;
            } catch (error) {
                return null;
            }
        }

        writeIfscCache(ifsc, data) {
            try {
                sessionStorage.setItem(`wf_ifsc_${ifsc}`, JSON.stringify({
                    time: Date.now(),
                    data
                }));
            } catch (error) {}
        }

        applyIfscDetails(data) {
            document.getElementById('bankName').value = data.bank || '';
            document.getElementById('branchName').value = data.branch || '';
            document.getElementById('bankName').closest('.wf-input-group')?.classList.add('success');
            const status = document.getElementById('ifscLookupStatus');
            if (status) {
                status.className = 'wf-field-hint success';
                status.innerText = data.branch ? `${data.bank} - ${data.branch}` : data.bank;
            }
        }

        applyIfscError(message) {
            document.getElementById('bankName').value = '';
            document.getElementById('branchName').value = '';
            const status = document.getElementById('ifscLookupStatus');
            if (status) {
                status.className = 'wf-field-hint error';
                status.innerText = message;
            }
        }

        handlePanInput(value) {
            const pan = value.toUpperCase().trim();
            if (pan !== this.panVerifiedNumber) {
                this.panVerified = false;
                this.panConfirmed = false;
            }
            clearTimeout(this.panVerifyTimer);
            if (!/^[A-Z]{5}[0-9]{4}[A-Z]$/.test(pan)) return;
            if (this.panVerified && this.panConfirmed && pan === this.panVerifiedNumber) return;
            this.panVerifyTimer = setTimeout(() => this.verifyPan(pan), 250);
        }

        openPanModal() {
            this.panModal.classList.add('active');
            this.panModal.setAttribute('aria-hidden', 'false');
        }

        closePanModal() {
            this.panModal.classList.remove('active');
            this.panModal.setAttribute('aria-hidden', 'true');
        }

        setPanModalLoading() {
            document.getElementById('panLoadingState').style.display = 'flex';
            document.getElementById('panVerifyError').innerText = '';
            document.getElementById('panDetailsBox').style.display = 'none';
            document.getElementById('panConfirmBtn').disabled = true;
        }

        verifyPan(pan) {
            this.openPanModal();
            this.setPanModalLoading();

            const fd = new FormData();
            fd.append('pan', pan);

            fetch('api/apply/verify_pan.php', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: fd
                })
                .then(res => this.parseJsonResponse(res))
                .then(data => {
                    document.getElementById('panLoadingState').style.display = 'none';
                    if (data.status !== 'success') {
                        throw new Error(data.message || 'PAN verification failed');
                    }

                    this.panVerified = true;
                    this.panConfirmed = false;
                    this.panVerifiedNumber = pan;
                    this.renderPanDetails(data);
                    document.getElementById('panConfirmBtn').disabled = false;
                })
                .catch(err => {
                    this.panVerified = false;
                    this.panConfirmed = false;
                    document.getElementById('panLoadingState').style.display = 'none';
                    document.getElementById('panVerifyError').innerText = err.message || 'PAN verification failed';
                    document.getElementById('panConfirmBtn').disabled = true;
                });
        }

        renderPanDetails(data) {
            document.getElementById('panDetailsBox').style.display = 'grid';
            document.getElementById('panNameValue').innerText = data.full_name || 'N/A';
            document.getElementById('panDobValue').innerText = this.formatPanDate(data.dob) || 'N/A';
            document.getElementById('panAadhaarValue').innerText = data.aadhaar || 'N/A';
            document.getElementById('panStatusValue').innerText = data.pan_status || 'Valid';
            document.getElementById('panNumberValue').innerText = data.pan || document.getElementById('panField').value;
            document.getElementById('panGenderValue').innerText = data.gender || 'N/A';
        }

        formatPanDate(value) {
            if (!value) return '';
            const clean = String(value).trim();
            if (/^\d{4}-\d{2}-\d{2}$/.test(clean)) {
                const [y, m, d] = clean.split('-');
                return `${d}/${m}/${y}`;
            }
            return clean;
        }

        confirmPanDetails() {
            if (!this.panVerified) return;
            this.panConfirmed = true;
            document.getElementById('panField').readOnly = true;
            document.getElementById('panField').closest('.wf-input-group')?.classList.add('success');
            this.closePanModal();
        }

        rejectPanDetails() {
            this.panVerified = false;
            this.panConfirmed = false;
            this.panVerifiedNumber = '';
            const panField = document.getElementById('panField');
            panField.readOnly = false;
            panField.value = '';
            panField.focus();
            this.closePanModal();
        }

        showPanFieldError(message) {
            const panField = document.getElementById('panField');
            const group = panField.closest('.wf-input-group');
            const errorLabel = group?.querySelector('.wf-error-msg');
            group?.classList.remove('success');
            group?.classList.add('error');
            panField.classList.add('is-invalid');
            if (errorLabel) errorLabel.innerText = message;
        }

        handleRegistrationTypeChange(val, keepValue = false) {
            const proofLabel = document.getElementById('businessProofLabel');
            const proofField = document.getElementById('businessProofField');
            const verifyBtn = document.getElementById('btnVerifyBusiness');
            const verifiedIcon = document.getElementById('businessProofVerifiedIcon');
            const successMsg = document.getElementById('businessProofSuccessMsg');

            // Reset verification state if type changes
            if (!keepValue) {
                this.businessVerified = false;
                this.businessConfirmed = false;
                this.businessVerifiedNumber = '';
                this.businessVerifiedName = '';
                this.businessVerifiedDetails = '';
                this.businessAddress = '';
                if (verifiedIcon) verifiedIcon.style.display = 'none';
                if (successMsg) successMsg.style.display = 'none';
                if (proofField) {
                    proofField.readOnly = false;
                    proofField.value = '';
                }
            }

            if (val === 'GST Registered') {
                if (proofLabel) proofLabel.innerHTML = 'GST Number <span class="text-danger">*</span>';
                if (proofField) {
                    proofField.placeholder = 'Enter 15-digit GSTIN';
                    proofField.setAttribute('required', 'required');
                }
                if (verifyBtn) verifyBtn.style.display = 'block';
            } else if (val === 'Udyam Registered') {
                if (proofLabel) proofLabel.innerHTML = 'Udyam Registration Number <span class="text-danger">*</span>';
                if (proofField) {
                    proofField.placeholder = 'Enter Udyam Number (e.g. UDYAM-XX-00-0000000)';
                    proofField.setAttribute('required', 'required');
                }
                if (verifyBtn) verifyBtn.style.display = 'block';
            } else {
                if (proofLabel) proofLabel.innerHTML = 'Business Proof / Shop Registration <span class="text-muted">(Optional)</span>';
                if (proofField) {
                    proofField.placeholder = 'GST, Shop Act, Udyam, if available';
                    proofField.removeAttribute('required');
                }
                if (verifyBtn) verifyBtn.style.display = 'none';
            }

            if (proofField) this.validateField(proofField);
        }

        openBusinessModal() {
            const modal = document.getElementById('businessModal');
            if (modal) {
                modal.style.display = 'flex';
                modal.setAttribute('aria-hidden', 'false');
            }
        }

        closeBusinessModal() {
            const modal = document.getElementById('businessModal');
            if (modal) {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
            }
        }

        setBusinessModalLoading() {
            const loading = document.getElementById('businessLoadingState');
            const errBox = document.getElementById('businessVerifyError');
            const details = document.getElementById('businessDetailsBox');
            const confirmBtn = document.getElementById('businessConfirmBtn');

            if (loading) loading.style.display = 'flex';
            if (errBox) {
                errBox.style.display = 'none';
                errBox.innerText = '';
            }
            if (details) details.style.display = 'none';
            if (confirmBtn) confirmBtn.disabled = true;
        }

        formatBusinessAddress(type, details) {
            if (!details) return '';
            if (type === 'gst') {
                const addr = details.pradr?.addr || details.pradr || details.principal_place_address || {};
                const parts = [
                    addr.bnm || addr.building_name || '',
                    addr.flat_no || '',
                    addr.st || addr.street || '',
                    addr.loc || addr.locality || '',
                    addr.dst || addr.district || '',
                    addr.stcd || addr.state || '',
                    addr.pncd || addr.pincode || ''
                ];
                return parts.filter(p => p.trim() !== '').join(', ');
            } else {
                const addr = details.official_address || details.officialAddress || details.profile?.official_address || details.profile?.officialAddress || {};
                const parts = [
                    addr.flat_no || addr.flatNo || '',
                    addr.premises || '',
                    addr.road || '',
                    addr.city || '',
                    addr.district || '',
                    addr.state || '',
                    addr.pincode || ''
                ];
                let formatted = parts.filter(p => p.trim() !== '').join(', ');
                if (!formatted && Array.isArray(details.plant_locations || details.location_of_plant)) {
                    const plant = (details.plant_locations || details.location_of_plant)[0] || {};
                    const plantParts = [
                        plant.flat_no || plant.flatNo || '',
                        plant.premises || '',
                        plant.road || '',
                        plant.city || '',
                        plant.district || '',
                        plant.state || '',
                        plant.pincode || ''
                    ];
                    formatted = plantParts.filter(p => p.trim() !== '').join(', ');
                }
                return formatted || '';
            }
        }

        verifyBusinessProof() {
            const typeSelect = document.getElementById('registrationType').value;
            const proofVal = document.getElementById('businessProofField').value.trim();
            const errorLabel = document.getElementById('businessProofError');
            const proofField = document.getElementById('businessProofField');

            if (!proofVal) {
                this.showBusinessError('Please enter a document number');
                return;
            }

            let apiType = '';
            if (typeSelect === 'GST Registered') {
                if (!/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/.test(proofVal.toUpperCase())) {
                    this.showBusinessError('Invalid GSTIN format');
                    return;
                }
                apiType = 'gst';
            } else if (typeSelect === 'Udyam Registered') {
                if (!/^UDYAM-[A-Z]{2}-\d{2}-\d{7}$/.test(proofVal.toUpperCase())) {
                    this.showBusinessError('Invalid Udyam format (e.g. UDYAM-DL-01-1234567)');
                    return;
                }
                apiType = 'udyam';
            } else {
                return;
            }

            // Clear errors
            if (errorLabel) errorLabel.innerText = '';
            if (proofField) proofField.classList.remove('is-invalid');

            this.openBusinessModal();
            this.setBusinessModalLoading();

            const fd = new FormData();
            fd.append('type', apiType);
            fd.append('value', proofVal);

            fetch('api/apply/verify_business.php', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': this.csrfToken
                },
                body: fd
            })
            .then(res => this.parseJsonResponse(res))
            .then(data => {
                const loading = document.getElementById('businessLoadingState');
                if (loading) loading.style.display = 'none';
                
                if (data.status !== 'success') {
                    throw new Error(data.message || 'Verification failed');
                }

                this.businessVerified = true;
                this.businessVerifiedNumber = proofVal;
                this.businessVerifiedName = data.business_name;
                this.businessVerifiedDetails = JSON.stringify(data.details);
                this.businessAddress = this.formatBusinessAddress(apiType, data.details);

                // Render details inside modal
                const detailsBox = document.getElementById('businessDetailsBox');
                if (detailsBox) detailsBox.style.display = 'grid';
                
                document.getElementById('bizNameValue').innerText = data.business_name;
                document.getElementById('bizRegValue').innerText = proofVal.toUpperCase();
                
                const extraLabel = document.getElementById('bizExtraLabel');
                const extraVal = document.getElementById('bizExtraValue');
                if (apiType === 'gst') {
                    if (extraLabel) extraLabel.innerText = 'GST Status:';
                    if (extraVal) extraVal.innerText = data.details.status || 'Active';
                } else {
                    if (extraLabel) extraLabel.innerText = 'Enterprise Type:';
                    if (extraVal) extraVal.innerText = data.details.enterprise_type || 'Micro';
                }

                const addrValBox = document.getElementById('bizAddressValue');
                if (addrValBox) {
                    addrValBox.innerText = this.businessAddress || 'Not Available';
                }
                const confirmBtn = document.getElementById('businessConfirmBtn');
                if (confirmBtn) confirmBtn.disabled = false;
            })
            .catch(err => {
                this.businessVerified = false;
                this.businessVerifiedDetails = '';
                this.businessAddress = '';
                const loading = document.getElementById('businessLoadingState');
                if (loading) loading.style.display = 'none';
                
                const errBox = document.getElementById('businessVerifyError');
                if (errBox) {
                    errBox.innerText = err.message || 'Verification failed';
                    errBox.style.display = 'block';
                }
                
                const confirmBtn = document.getElementById('businessConfirmBtn');
                if (confirmBtn) confirmBtn.disabled = true;
            });
        }

        showBusinessError(msg) {
            const errorLabel = document.getElementById('businessProofError');
            const proofField = document.getElementById('businessProofField');
            if (errorLabel) errorLabel.innerText = msg;
            if (proofField) {
                proofField.classList.add('is-invalid');
                const group = proofField.closest('.wf-input-group');
                if (group) {
                    group.classList.remove('success');
                    group.classList.add('error');
                }
            }
        }

        confirmBusinessDetails() {
            if (!this.businessVerified) return;
            this.businessConfirmed = true;
            
            // Mark field verified
            const proofField = document.getElementById('businessProofField');
            if (proofField) {
                proofField.readOnly = true;
                const group = proofField.closest('.wf-input-group');
                if (group) {
                    group.classList.add('success');
                    group.classList.remove('error');
                }
            }
            
            const verifiedIcon = document.getElementById('businessProofVerifiedIcon');
            if (verifiedIcon) verifiedIcon.style.display = 'block';
            
            // Auto fill Shop Name in step 4
            const shopNameField = document.getElementById('shopBusinessName');
            if (shopNameField) {
                shopNameField.value = this.businessVerifiedName;
                shopNameField.dispatchEvent(new Event('input', { bubbles: true }));
            }
            
            const successMsg = document.getElementById('businessProofSuccessMsg');
            if (successMsg) {
                successMsg.innerText = `Verified: ${this.businessVerifiedName}`;
                successMsg.style.display = 'block';
            }

            this.closeBusinessModal();
        }

        validateField(field) {
            if (!field.closest) return true; // Safety
            const group = field.closest('.wf-input-group') || field.closest('.terms-check') || field.closest('.form-check') || field.closest('.wf-doc-upload-card') || field.closest('.wf-selfie-card');
            if (!group) return true;

            let isValid = true;
            let errorMsg = "";
            const value = field.type === 'file' ? '' : field.value.trim();

            if (field.hasAttribute('required')) {
                if (field.type === 'checkbox') {
                    if (!field.checked) {
                        isValid = false;
                        errorMsg = field.id === 'confirmDetails' 
                            ? "Please confirm that your details are correct before submitting" 
                            : "You must agree to the terms";
                    }
                } else if (field.type === 'file') {
                    if (!field.files || field.files.length === 0) {
                        isValid = false;
                        errorMsg = "Please upload this document";
                    }
                } else if (!value) {
                    isValid = false;
                    errorMsg = "This field is required";
                }
            }

            if (isValid && field.type === 'file' && field.files && field.files.length) {
                const imageOnlyIds = ['shopFrontPhoto', 'stockPhoto', 'stockPhotoTwo', 'passportPhotoDoc'];
                const allowedTypes = imageOnlyIds.includes(field.id)
                    ? ['image/jpeg', 'image/png', 'image/webp']
                    : ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
                const invalidFile = Array.from(field.files).find(file => !allowedTypes.includes(file.type) || file.size > 5 * 1024 * 1024);
                if (invalidFile) {
                    isValid = false;
                    errorMsg = imageOnlyIds.includes(field.id)
                        ? 'Upload JPG, PNG, or WEBP under 5MB'
                        : 'Upload JPG, PNG, WEBP, or PDF under 5MB';
                }
            }

            if (isValid && value) {
                if (field.id === 'panField' && !/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/.test(value)) {
                    isValid = false;
                    errorMsg = "Invalid PAN format (e.g. ABCDE1234F)";
                } else if (field.id === 'phoneField' && !/^[6-9]\d{9}$/.test(value)) {
                    isValid = false;
                    errorMsg = "Invalid 10-digit mobile number";
                } else if (field.id === 'otpField' && this.currentStep === 1 && this.otpSent && value.length !== 6) {
                    isValid = false;
                    errorMsg = "Please enter 6-digit OTP";
                } else if (field.id === 'emailField' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    isValid = false;
                    errorMsg = "Enter a valid email address";
                } else if (field.id === 'aadhaarField' && !/^\d{12}$/.test(value)) {
                    isValid = false;
                    errorMsg = "Enter 12-digit Aadhaar number";
                } else if (field.id === 'pincodeField' && !/^\d{6}$/.test(value)) {
                    isValid = false;
                    errorMsg = "Enter 6-digit pincode";
                } else if (field.id === 'ifscCode' && !/^[A-Z]{4}0[A-Z0-9]{6}$/.test(value)) {
                    isValid = false;
                    errorMsg = "Enter valid IFSC code";
                } else if (field.id === 'accountNumber' && !/^\d{9,18}$/.test(value)) {
                    isValid = false;
                    errorMsg = "Account number must be 9 to 18 digits";
                } else if ((field.id === 'primaryRefMobile' || field.id === 'secondaryRefMobile') && !/^[6-9]\d{9}$/.test(value)) {
                    isValid = false;
                    errorMsg = "Enter valid 10-digit mobile number";
                } else if (['dailySales', 'monthlyIncome', 'loanAmount'].includes(field.id) && Number(value.replace(/,/g, '')) <= 0) {
                    isValid = false;
                    errorMsg = "Enter an amount greater than 0";
                } else if (field.id === 'businessProofField') {
                    const regType = document.getElementById('registrationType').value;
                    if (regType === 'GST Registered' && !/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/.test(value.toUpperCase())) {
                        isValid = false;
                        errorMsg = "Invalid GSTIN format (e.g. 27AAAAA1111A1Z1)";
                    } else if (regType === 'Udyam Registered' && !/^UDYAM-[A-Z]{2}-\d{2}-\d{7}$/.test(value.toUpperCase())) {
                        isValid = false;
                        errorMsg = "Invalid Udyam format (e.g. UDYAM-DL-01-1234567)";
                    }
                }
            }

            const errorLabel = group.querySelector('.wf-error-msg') || (field.id === 'termsCheckbox' ? document.getElementById('termsError') : null) || document.getElementById('otpErrorMsg');
            if (isValid) {
                group.classList.remove('error');
                group.classList.add('success');
                field.classList.remove('is-invalid');
                if (errorLabel && field.id !== 'termsConsent') errorLabel.innerText = "";
            } else {
                group.classList.remove('success');
                group.classList.add('error');
                field.classList.add('is-invalid');
                if (errorLabel && field.id !== 'termsConsent') errorLabel.innerText = errorMsg;
            }

            return isValid;
        }

        validateCurrentStep() {
            const currentPanel = this.panels[this.currentStep - 1];
            const fields = currentPanel.querySelectorAll('input[required], select[required]');
            let stepValid = true;
            let firstInvalid = null;

            fields.forEach(field => {
                if (!this.validateField(field)) {
                    stepValid = false;
                    if (!firstInvalid && field.type !== 'hidden') firstInvalid = field;
                }
            });

            if (this.currentStep === 3) {
                const regType = document.getElementById('registrationType').value;
                if ((regType === 'GST Registered' || regType === 'Udyam Registered') && !this.businessConfirmed) {
                    this.showBusinessError('Please verify your business details before proceeding');
                    stepValid = false;
                    if (!firstInvalid) firstInvalid = document.getElementById('businessProofField');
                }
            }

            if (this.currentStep === 7 && !this.validateReferences()) {
                stepValid = false;
            }
            if (this.currentStep === 8 && !this.selfieBlob) {
                this.setSelfieError('Please capture a live selfie');
                stepValid = false;
            }

            if (!stepValid) {
                this.form.classList.add('shake');
                setTimeout(() => this.form.classList.remove('shake'), 400);
                this.focusFirstInvalid(firstInvalid || currentPanel.querySelector('.error input, .error select'));
            }

            return stepValid;
        }

        focusFirstInvalid(field) {
            if (!field) return;
            const target = field.closest('.wf-input-group, .wf-doc-upload-card, .wf-selfie-card, .terms-check') || field;
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            setTimeout(() => {
                if (field.type !== 'file' && field.type !== 'checkbox') field.focus();
            }, 250);
        }

        setSelfieError(message) {
            const card = document.getElementById('selfieCard');
            const error = card.querySelector('.wf-error-msg');
            card.classList.add('error');
            card.classList.remove('success');
            if (error) error.innerText = message;
        }

        clearSelfieError() {
            const card = document.getElementById('selfieCard');
            const error = card.querySelector('.wf-error-msg');
            card.classList.remove('error');
            if (error) error.innerText = '';
        }

        async startSelfieCamera() {
            this.clearSelfieError();
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.setSelfieError('Camera is not available in this browser');
                return;
            }
            try {
                this.selfieStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user' },
                    audio: false
                });
                const video = document.getElementById('selfieVideo');
                video.srcObject = this.selfieStream;
                video.style.display = 'block';
                document.getElementById('selfiePreview').style.display = 'none';
                document.getElementById('captureSelfieBtn').style.display = 'inline-flex';
                document.getElementById('retakeSelfieBtn').style.display = 'none';
                document.getElementById('startSelfieBtn').style.display = 'none';
                document.getElementById('selfieDocName').innerText = 'Camera ready. Capture selfie.';
            } catch (error) {
                this.setSelfieError('Unable to open camera. Please allow camera permission.');
            }
        }

        async captureSelfie() {
            const video = document.getElementById('selfieVideo');
            const canvas = document.getElementById('selfieCanvas');
            const captureBtn = document.getElementById('captureSelfieBtn');
            if (!video.videoWidth || !video.videoHeight) {
                this.setSelfieError('Camera is still loading. Try again.');
                return;
            }

            this.clearSelfieError();
            captureBtn.disabled = true;
            captureBtn.innerHTML = 'Verifying face... <i class="fas fa-spinner fa-spin ms-2"></i>';
            document.getElementById('selfieDocName').innerText = 'Analyzing image...';

            try {
                if (typeof faceapi === 'undefined') {
                    throw new Error('Face detection library is loading. Please wait a moment.');
                }
                if (!this.faceModelsLoaded) {
                    await faceapi.nets.tinyFaceDetector.loadFromUri('assets/models');
                    this.faceModelsLoaded = true;
                }

                const detections = await faceapi.detectAllFaces(
                    video,
                    new faceapi.TinyFaceDetectorOptions({ inputSize: 160, scoreThreshold: 0.5 })
                );

                if (detections.length === 0) {
                    throw new Error('No face detected. Please position your face clearly in a well-lit area.');
                }
                if (detections.length > 1) {
                    throw new Error('Multiple faces detected. Please ensure only one person is in the frame.');
                }

                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                canvas.toBlob((blob) => {
                    if (!blob) {
                        this.setSelfieError('Unable to capture selfie');
                        captureBtn.disabled = false;
                        captureBtn.innerHTML = '<i class="fas fa-camera"></i> Capture';
                        return;
                    }
                    this.selfieBlob = blob;
                    const preview = document.getElementById('selfiePreview');
                    preview.src = URL.createObjectURL(blob);
                    preview.style.display = 'block';
                    video.style.display = 'none';
                    captureBtn.disabled = false;
                    captureBtn.innerHTML = '<i class="fas fa-camera"></i> Capture';
                    captureBtn.style.display = 'none';
                    document.getElementById('retakeSelfieBtn').style.display = 'inline-flex';
                    document.getElementById('selfieDocName').innerText = 'Selfie captured successfully';
                    document.getElementById('selfieCard').classList.add('success');
                    this.stopSelfieCamera();
                    this.clearSelfieError();
                }, 'image/jpeg', 0.9);

            } catch (err) {
                this.setSelfieError(err.message || 'Face verification failed. Try again.');
                captureBtn.disabled = false;
                captureBtn.innerHTML = '<i class="fas fa-camera"></i> Capture';
                document.getElementById('selfieDocName').innerText = 'Face verification failed';
            }
        }

        retakeSelfie() {
            this.selfieBlob = null;
            document.getElementById('selfiePreview').style.display = 'none';
            document.getElementById('selfieDocName').innerText = 'Camera not started';
            document.getElementById('selfieCard').classList.remove('success');
            document.getElementById('startSelfieBtn').style.display = 'inline-flex';
            document.getElementById('retakeSelfieBtn').style.display = 'none';
            this.startSelfieCamera();
        }

        stopSelfieCamera() {
            if (this.selfieStream) {
                this.selfieStream.getTracks().forEach(track => track.stop());
                this.selfieStream = null;
            }
            document.getElementById('selfieVideo').srcObject = null;
        }

        validateReferences() {
            const applicantMobile = document.getElementById('phoneField').value.trim();
            const primary = document.getElementById('primaryRefMobile');
            const secondary = document.getElementById('secondaryRefMobile');
            let valid = true;

            if (primary.value && secondary.value && primary.value === secondary.value) {
                this.setFieldError(secondary, 'Reference numbers must be different');
                valid = false;
            }

            if (applicantMobile && (primary.value === applicantMobile || secondary.value === applicantMobile)) {
                if (primary.value === applicantMobile) this.setFieldError(primary, 'Reference cannot be applicant mobile');
                if (secondary.value === applicantMobile) this.setFieldError(secondary, 'Reference cannot be applicant mobile');
                valid = false;
            }

            return valid;
        }

        setFieldError(field, message) {
            const group = field.closest('.wf-input-group');
            const errorLabel = group?.querySelector('.wf-error-msg');
            group?.classList.remove('success');
            group?.classList.add('error');
            field.classList.add('is-invalid');
            if (errorLabel) errorLabel.innerText = message;
        }

        generateDeviceFingerprint() {
            try {
                const parts = [
                    navigator.userAgent || '',
                    navigator.language || '',
                    screen.width || 0,
                    screen.height || 0,
                    screen.colorDepth || 0,
                    new Date().getTimezoneOffset(),
                    !!window.indexedDB,
                    !!window.sessionStorage,
                    !!window.localStorage
                ];
                const rawString = parts.join('||');
                let hash = 0;
                for (let i = 0; i < rawString.length; i++) {
                    const char = rawString.charCodeAt(i);
                    hash = ((hash << 5) - hash) + char;
                    hash |= 0;
                }
                return 'DEV_' + Math.abs(hash).toString(16);
            } catch (e) {
                return 'DEV_UNKNOWN';
            }
        }

        checkAndRequestLocation() {
            const blocker = document.getElementById('wf-location-blocker');
            const retryBtn = document.getElementById('btnRequestLocationRetry');

            if (!blocker) return;

            blocker.style.display = 'flex';
            blocker.setAttribute('aria-hidden', 'false');

            if (retryBtn) {
                const newRetryBtn = retryBtn.cloneNode(true);
                retryBtn.parentNode.replaceChild(newRetryBtn, retryBtn);
                newRetryBtn.addEventListener('click', () => this.requestLocationAccess());
            }

            this.requestLocationAccess();
        }

        requestLocationAccess() {
            const blocker = document.getElementById('wf-location-blocker');
            const blockerText = blocker ? blocker.querySelector('p') : null;
            const retryBtn = document.getElementById('btnRequestLocationRetry');

            if (retryBtn) {
                retryBtn.disabled = true;
                retryBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking Location...';
            }

            if (!navigator.geolocation) {
                if (blockerText) {
                    blockerText.innerHTML = '<span class="text-danger">Your browser does not support Location services. Please use a modern browser to apply.</span>';
                }
                if (retryBtn) retryBtn.style.display = 'none';
                return;
            }

            if (blockerText) {
                blockerText.innerText = 'Requesting location permission... Please click "Allow" on the browser prompt.';
            }

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    this.latitude = position.coords.latitude.toString();
                    this.longitude = position.coords.longitude.toString();
                    
                    if (blockerText) {
                        blockerText.innerText = 'Verifying your location region... Please wait.';
                    }

                    try {
                        const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${this.latitude}&lon=${this.longitude}`, {
                            headers: {
                                'User-Agent': 'WaqtFinance/1.0 (contact@waqtmoney.com)'
                            }
                        });
                        const data = await res.json();
                        this.gpsAddress = data.display_name || '';

                        // Verify country is India (Geofencing country lock)
                        const countryCode = (data.address?.country_code || '').toLowerCase();
                        if (countryCode !== 'in') {
                            if (blockerText) {
                                blockerText.innerHTML = '<span class="text-danger">Waqt Finance only accepts applications from residents located within India. Applications from your current region are not supported.</span>';
                            }
                            if (retryBtn) retryBtn.style.display = 'none';
                            return; // Keep blocker active, prevent form access
                        }

                        // Auto-fill address, pincode, and city fields
                        const addr = data.address || {};
                        const postcode = addr.postcode || '';
                        const city = addr.city || addr.town || addr.village || addr.suburb || '';
                        const displayAddr = data.display_name || '';

                        const addressInput = document.getElementById('addressField');
                        const pincodeInput = document.getElementById('pincodeField');
                        const cityInput = document.getElementById('personalCity');

                        if (addressInput) {
                            addressInput.value = displayAddr;
                            this.validateField(addressInput);
                        }
                        if (pincodeInput && postcode) {
                            pincodeInput.value = postcode;
                            this.validateField(pincodeInput);
                        }
                        if (cityInput && city) {
                            cityInput.value = city;
                            this.validateField(cityInput);
                        }

                        if (blocker) {
                            blocker.style.display = 'none';
                            blocker.setAttribute('aria-hidden', 'true');
                        }
                    } catch (err) {
                        if (blocker) {
                            blocker.style.display = 'none';
                            blocker.setAttribute('aria-hidden', 'true');
                        }
                    } finally {
                        if (retryBtn) {
                            retryBtn.disabled = false;
                            retryBtn.innerHTML = '<i class="fas fa-location-arrow"></i> Allow Location Access';
                        }
                    }
                },
                (error) => {
                    console.error("Location error:", error);
                    let errMsg = 'To ensure secure credit processing, Waqt Finance requires active location access. Please allow location permissions in your browser settings to continue.';
                    if (error.code === error.PERMISSION_DENIED) {
                        errMsg = 'Location permission was denied. Please click the site settings/lock icon in your browser address bar, change Location to "Allow", and click the button below to retry.' +
                                 '<div style="margin-top: 20px; background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 12px; padding: 16px; font-size: 13.5px; color: #475569; display: flex; flex-direction: column; align-items: center; gap: 10px; font-family: \'Inter\', sans-serif !important;">' +
                                 '  <div style="font-weight: 700; color: #0f2742; display: flex; align-items: center; gap: 8px;">' +
                                 '    <i class="fas fa-arrow-pointer" style="color: #18529c;"></i> How to Unblock:' +
                                 '  </div>' +
                                 '  <div style="display: flex; align-items: center; gap: 10px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 16px; font-family: monospace; font-size: 13px; width: 100%; box-sizing: border-box; justify-content: center; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">' +
                                 '    <i class="fas fa-lock" style="color: #16a34a;"></i> <span style="color: #0f2742; font-weight: 600;">waqtfinance.com</span>' +
                                 '  </div>' +
                                 '  <div style="line-height: 1.5; font-size: 13px; text-align: center;">' +
                                 '    Click the <strong>Lock (🔒) icon</strong> on the top left of your browser address bar, set <strong>Location</strong> to <strong>Allow</strong>, and reload the page.' +
                                 '  </div>' +
                                 '</div>';
                    } else if (error.code === error.POSITION_UNAVAILABLE) {
                        errMsg = 'Location information is unavailable. Please make sure location services are turned on in your device settings and click retry.';
                    } else if (error.code === error.TIMEOUT) {
                        errMsg = 'Location request timed out. Please click retry to try again.';
                    }
                    
                    if (blockerText) {
                        blockerText.innerHTML = `<span class="text-danger">${errMsg}</span>`;
                    }

                    if (retryBtn) {
                        retryBtn.disabled = false;
                        retryBtn.innerHTML = '<i class="fas fa-location-arrow"></i> Allow Location Access';
                    }
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        nextStep() {
            if (!this.validateCurrentStep()) return;

            if (this.currentStep === 1) {
                if (!this.otpSent) {
                    this.initOTP();
                } else if (!this.otpVerified) {
                    this.openOtpModal();
                }
            } else if (this.currentStep === 3 && !this.panConfirmed) {
                this.showPanFieldError('Please verify and confirm PAN details first');
            } else if (this.currentStep === 9) {
                this.submitFinalApplication();
            } else if (this.currentStep < this.totalSteps) {
                this.currentStep++;
                this.updateUI();
            }
        }

        prevStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
                this.updateUI();
            }
        }

        updateUI() {
            document.querySelector('.wf-apply-card')?.classList.toggle('review-wide', this.currentStep === 9);

            this.panels.forEach((p, i) => {
                p.classList.toggle('active', i === (this.currentStep - 1));
            });

            // Toggle active/completed on labels
            const labels = document.querySelectorAll('.wf-step-label');
            labels.forEach((l, i) => {
                const stepNum = i + 1;
                l.classList.remove('active', 'completed');
                if (stepNum < this.currentStep) {
                    l.classList.add('completed');
                } else if (stepNum === this.currentStep) {
                    l.classList.add('active');
                }
            });

            this.nodes.forEach((n, i) => {
                const stepNum = i + 1;
                n.classList.remove('active', 'completed');
                if (stepNum < this.currentStep) {
                    n.classList.add('completed');
                    n.innerHTML = '<i class="fas fa-check"></i>';
                } else if (stepNum === this.currentStep) {
                    n.classList.add('active');
                    n.innerHTML = stepNum;
                } else {
                    n.innerHTML = stepNum;
                }
            });

            const progress = ((this.currentStep - 1) / (this.totalSteps - 1)) * 100;
            this.progressLine.style.width = `${progress}%`;
            document.getElementById('mobileStepCount').innerText = `Step ${this.currentStep} of ${this.totalSteps}`;
            document.getElementById('mobileStepTitle').innerText = this.stepTitles[this.currentStep - 1] || '';
            document.getElementById('mobileProgressFill').style.width = `${(this.currentStep / this.totalSteps) * 100}%`;

            const sidebarCountEl = document.getElementById('sidebarStepCount');
            if (sidebarCountEl) sidebarCountEl.innerText = `Step ${this.currentStep} of ${this.totalSteps}`;
            const sidebarProgressEl = document.getElementById('sidebarProgressFill');
            if (sidebarProgressEl) sidebarProgressEl.style.width = `${(this.currentStep / this.totalSteps) * 100}%`;

            this.btnPrev.style.display = (this.currentStep === 1) ? 'none' : 'flex';
            this.btnNext.style.display = 'flex';

            if (this.currentStep === 1) {
                this.btnNext.innerHTML = this.otpSent ? 'Open OTP Popup <i class="fas fa-up-right-from-square ms-2"></i>' : 'Send OTP <i class="fas fa-paper-plane ms-2"></i>';
            } else if (this.currentStep === 9) {
                this.btnNext.innerHTML = 'Submit Application <i class="fas fa-paper-plane ms-2"></i>';
            } else {
                this.btnNext.innerHTML = 'Save & Continue <i class="fas fa-arrow-right ms-2"></i>';
            }

            if (this.currentStep === 2) {
                document.getElementById('fullNameReview').value = document.getElementById('fullName').value;
            }
            if (this.currentStep === 9) {
                this.renderReviewSummary();
            }

            this.btnNext.disabled = false;
            this.queueDraftSave();

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char]));
        }

        fileSummary(inputId) {
            const input = document.getElementById(inputId);
            const files = input && input.files ? Array.from(input.files) : [];
            if (files.length === 0) return 'Not uploaded';
            if (files.length === 1) {
                const name = files[0].name;
                if (name.length > 25) {
                    return `✓ ${name.substring(0, 15)}...${name.substring(name.length - 8)}`;
                }
                return `✓ ${name}`;
            }
            return `✓ ${files.length} files`;
        }

        renderReviewSummary() {
            const target = document.getElementById('reviewSummary');
            if (!target) return;

            const money = (id) => {
                const value = Number(document.getElementById(id)?.value.replace(/,/g, '') || 0);
                return value ? `Rs. ${value.toLocaleString('en-IN')}` : 'N/A';
            };
            const value = (id) => document.getElementById(id)?.value || 'N/A';
            const maskedAadhaar = value('aadhaarField') !== 'N/A' ? `XXXX XXXX ${value('aadhaarField').slice(-4)}` : 'N/A';

            const sections = [{
                    title: 'Applicant',
                    icon: 'fa-user',
                    items: [
                        ['Name', value('fullName')],
                        ['Mobile', value('phoneField')],
                        ['Employee Code', value('employeeCode') || 'N/A'],
                        ['Email', value('emailField')],
                        ['Address', value('addressField')],
                        ['Landmark', value('landmarkField')],
                        ['City / Pincode', `${value('personalCity')} - ${value('pincodeField')}`],
                    ]
                },
                {
                    title: 'KYC',
                    icon: 'fa-id-card',
                    items: [
                        ['PAN', value('panField')],
                        ['Aadhaar', maskedAadhaar],
                        ['Business Proof', value('businessProofField') || 'N/A'],
                    ]
                },
                {
                    title: 'Business',
                    icon: 'fa-store',
                    items: [
                        ['Business Name', value('shopBusinessName')],
                        ['Registration Type', value('registrationType')],
                        ['Business Age', value('businessAge')],
                        ['Business Type', value('businessType')],
                        ['Daily Sale / Income', money('dailySales')],
                        ['Monthly Income', money('monthlyIncome')],
                        ['Loan Amount', money('loanAmount')],
                        ['Loan Purpose', value('purpose')],
                    ]
                },
                {
                    title: 'Documents',
                    icon: 'fa-file-arrow-up',
                    items: [
                        ['Shop Front Photo', this.fileSummary('shopFrontPhoto')],
                        ['Stock Photo', this.fileSummary('stockPhoto')],
                        ['Stock Photo 2', this.fileSummary('stockPhotoTwo')],
                        ['PAN Card', this.fileSummary('panCardDoc')],
                        ['Aadhaar Card', this.fileSummary('aadhaarCardDoc')],
                        ['Bank Statement', this.fileSummary('bankStatementDoc')],
                        ['Business Registration', this.fileSummary('businessRegistrationDoc')],
                        ['Address Proof', this.fileSummary('addressProofDoc')],
                        ['Passport Photo', this.fileSummary('passportPhotoDoc')],
                        ['Selfie', this.selfieBlob ? 'Live selfie captured' : 'Not captured'],
                    ]
                },
                {
                    title: 'Bank',
                    icon: 'fa-building-columns',
                    items: [
                        ['IFSC', value('ifscCode')],
                        ['Bank Name', value('bankName')],
                        ['Branch', value('branchName')],
                        ['Account Holder', value('accountHolder')],
                        ['Account Number', value('accountNumber')],
                    ]
                },
                {
                    title: 'References',
                    icon: 'fa-user-group',
                    items: [
                        ['Reference 1', `${value('primaryRefName')} (${value('primaryRefRelation')})`],
                        ['Reference 1 Mobile', value('primaryRefMobile')],
                        ['Reference 2', `${value('secondaryRefName')} (${value('secondaryRefRelation')})`],
                        ['Reference 2 Mobile', value('secondaryRefMobile')],
                    ]
                }
            ];

            target.innerHTML = sections.map(section => {
                const isFullWidth = section.title === 'Applicant' || section.title === 'Documents';
                const cardClass = isFullWidth ? 'wf-review-card full-width' : 'wf-review-card';
                return `
                    <div class="${cardClass}">
                        <div class="wf-review-card-head">
                            <span><i class="fas ${section.icon}"></i></span>
                            <h4>${this.escapeHtml(section.title)}</h4>
                        </div>
                        <div class="wf-review-rows">
                            ${section.items.map(([label, itemValue]) => `
                                <div class="wf-review-row">
                                    <span>${this.escapeHtml(label)}</span>
                                    <strong>${this.escapeHtml(itemValue || 'N/A')}</strong>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }).join('');
        }

        resetOTP() {
            this.otpSent = false;
            this.otpVerified = false;
            this.otpAttempts = 0;
            clearInterval(this.timerInterval);
            this.closeOtpModal();
            this.clearOtpDigits();
            document.getElementById('otpTimer').textContent = '01:00';
            document.getElementById('otpAttemptsInfo').innerText = `Attempts remaining: ${this.maxAttempts}`;
            document.getElementById('otpErrorMsg').innerText = '';
            const phoneInput = document.getElementById('phoneField');
            phoneInput.readOnly = false;
            document.getElementById('fullName').readOnly = false;
            document.getElementById('employeeCode').readOnly = false;
            this.updateUI();
            setTimeout(() => {
                phoneInput.focus();
                phoneInput.select();
            }, 100);
        }

        markOtpSentUI() {
            this.otpSent = true;
            document.getElementById('phoneField').readOnly = true;
            document.getElementById('fullName').readOnly = true;
            document.getElementById('employeeCode').readOnly = true;
            document.getElementById('otpMobileText').innerText = document.getElementById('phoneField').value;
            this.openOtpModal();
            this.updateUI();
            this.checkAndRequestLocation();
        }

        openOtpModal() {
            document.getElementById('otpMobileText').innerText = document.getElementById('phoneField').value;
            this.otpModal.classList.add('active');
            this.otpModal.setAttribute('aria-hidden', 'false');
            setTimeout(() => this.otpDigitInputs[0]?.focus(), 60);
        }

        closeOtpModal() {
            this.otpModal.classList.remove('active');
            this.otpModal.setAttribute('aria-hidden', 'true');
        }

        clearOtpDigits() {
            document.getElementById('otpField').value = '';
            this.otpDigitInputs.forEach(input => input.value = '');
        }

        syncOtpField() {
            document.getElementById('otpField').value = this.otpDigitInputs.map(input => input.value).join('');
        }

        handleOtpDigitInput(e, index) {
            e.target.value = e.target.value.replace(/\D/g, '').slice(-1);
            this.syncOtpField();
            if (e.target.value && index < this.otpDigitInputs.length - 1) {
                this.otpDigitInputs[index + 1].focus();
            }
            if (document.getElementById('otpField').value.length === 6) {
                this.otpVerifyBtn.disabled = false;
            }
        }

        handleOtpDigitKeydown(e, index) {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                this.otpDigitInputs[index - 1].focus();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                this.syncOtpField();
                if (document.getElementById('otpField').value.length === 6) {
                    this.verifyOTPAndContinue();
                }
            }
        }

        handleOtpPaste(e) {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
            pasted.split('').forEach((digit, index) => {
                if (this.otpDigitInputs[index]) this.otpDigitInputs[index].value = digit;
            });
            this.syncOtpField();
            this.otpDigitInputs[Math.min(pasted.length, 5)]?.focus();
        }

        renderPreview(targetId = 'dataPreview') {
            const previewContainer = document.getElementById(targetId);
            if (!previewContainer) return;


            const data = {
                personal: {
                    title: "Applicant Details",
                    icon: "fa-user-circle",
                    items: [{
                            label: "Full Name",
                            value: document.getElementById('fullName').value
                        },
                        {
                            label: "Mobile Number",
                            value: document.getElementById('phoneField').value
                        },
                        {
                            label: "Employee Code",
                            value: document.getElementById('employeeCode').value || 'N/A'
                        },
                        {
                            label: "Email",
                            value: document.getElementById('emailField').value
                        },
                        {
                            label: "Address",
                            value: document.getElementById('addressField').value
                        },
                        {
                            label: "PAN Number",
                            value: document.getElementById('panField').value
                        },
                        {
                            label: "Aadhaar Number",
                            value: document.getElementById('aadhaarField').value ? 'XXXX XXXX ' + document.getElementById('aadhaarField').value.slice(-4) : 'N/A'
                        }
                    ]
                },
                loan: {
                    title: "Business & Loan Details",
                    icon: "fa-briefcase",
                    items: [{
                            label: "Business Name",
                            value: document.getElementById('shopBusinessName').value
                        },
                        {
                            label: "Business Type",
                            value: document.getElementById('businessType').value
                        },
                        {
                            label: "Daily Revenue",
                            value: '₹' + Number(document.getElementById('dailySales').value.replace(/,/g, '')).toLocaleString('en-IN'),
                            isAmount: true
                        },
                        {
                            label: "Monthly Income",
                            value: 'Rs. ' + Number(document.getElementById('monthlyIncome').value.replace(/,/g, '')).toLocaleString('en-IN'),
                            isAmount: true
                        },
                        {
                            label: "Loan Amount",
                            value: '₹' + Number(document.getElementById('loanAmount').value.replace(/,/g, '')).toLocaleString('en-IN'),
                            isAmount: true
                        },
                        {
                            label: "Loan Purpose",
                            value: document.getElementById('purpose').value
                        },
                        {
                            label: "Business City",
                            value: document.getElementById('personalCity').value
                        },
                        {
                            label: "Bank",
                            value: document.getElementById('bankName').value
                        }
                    ]
                }
            };

            let html = `
                <div class="wf-preview-card">
                    <div class="wf-preview-header">
                        <h4>Application Summary</h4>
                        <div class="wf-status-pill">
                            <i class="fas fa-check-circle"></i> Verified
                        </div>
                    </div>
                    <div class="wf-preview-body">
            `;

            for (const section in data) {
                html += `
                    <div class="wf-preview-section">
                        <div class="wf-preview-section-title">
                            <i class="fas ${data[section].icon}"></i> ${data[section].title}
                        </div>
                `;
                data[section].items.forEach(item => {
                    html += `
                        <div class="wf-preview-row">
                            <span class="wf-preview-label">${item.label}</span>
                            <span class="wf-preview-value ${item.isAmount ? 'amount' : ''}">${item.value || 'N/A'}</span>
                        </div>
                    `;
                });
                html += `</div>`;
            }

            html += `
                    </div>
                </div>
            `;

            previewContainer.innerHTML = html;
        }

        parseJsonResponse(res) {
            return res.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (error) {
                    console.error('Expected JSON response, received:', text);
                    throw new Error('Server returned an invalid response. Please refresh and try again.');
                }
            });
        }



        initOTP() {
            this.btnNext.disabled = true;
            this.btnNext.innerHTML = 'Processing... <i class="fas fa-spinner fa-spin ms-2"></i>';
            this.otpAttempts = 0; // Reset attempts on fresh init

            const fd = new FormData();

            fd.append('action', 'init_otp');
            fd.append('mobile', document.getElementById('phoneField').value);
            fd.append('fullName', document.getElementById('fullName').value);
            fd.append('employeeCode', document.getElementById('employeeCode').value);

            const initOtpUrl = window.location.pathname.replace(/\.php$/i, '') || 'edi-loan-apply';

            fetch(initOtpUrl, {
                    method: 'POST',
                    body: fd
                })
                .then(res => this.parseJsonResponse(res))
                .then(data => {
                    if (data.status === 'success') {
                        if (data.csrf_token) this.csrfToken = data.csrf_token;
                        this.sendOTP(true);
                    } else {
                        throw new Error(data.message || "Failed to initialize OTP");
                    }
                })
                .catch(err => {
                    this.btnNext.disabled = false;
                    this.btnNext.innerHTML = 'Send OTP <i class="fas fa-paper-plane ms-2"></i>';
                    alert(err.message);
                });
        }

        sendOTP(openAfterSuccess = false) {
            const resendBtn = document.getElementById('resendOtpBtn');
            const otpErrorMsg = document.getElementById('otpErrorMsg');
            const attemptsInfo = document.getElementById('otpAttemptsInfo');

            resendBtn.style.pointerEvents = 'none';
            resendBtn.style.opacity = '0.5';
            resendBtn.innerHTML = 'Sending...';

            // Reset state for new OTP
            this.otpAttempts = 0;
            if (attemptsInfo) attemptsInfo.innerText = `Attempts remaining: ${this.maxAttempts}`;
            if (otpErrorMsg) {
                otpErrorMsg.innerText = "";
            }
            this.clearOtpDigits();

            const fd = new FormData();
            fd.append('mobile', document.getElementById('phoneField').value);

            fetch('api/apply/send_mobile_otp.php', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: fd
                })
                .then(res => this.parseJsonResponse(res))
                .then(data => {
                    resendBtn.innerHTML = 'Resend OTP';
                    if (data.status === 'success') {
                        this.markOtpSentUI();
                        this.startTimer(data.retry_after || 60);
                        if (openAfterSuccess) this.openOtpModal();
                    } else {
                        this.otpSent = false;
                        otpErrorMsg.innerText = data.message || "Failed to send OTP";
                        this.openOtpModal();
                        resendBtn.style.pointerEvents = 'auto';
                        resendBtn.style.opacity = '1';
                        this.btnNext.disabled = false;
                    }
                })
                .catch(err => {
                    this.otpSent = false;
                    resendBtn.innerHTML = 'Resend OTP';
                    otpErrorMsg.innerText = err.message || "Failed to send OTP";
                    this.openOtpModal();
                    resendBtn.style.pointerEvents = 'auto';
                    resendBtn.style.opacity = '1';
                    this.btnNext.disabled = false;
                });
        }

        startTimer(duration) {
            let timer = duration;
            const timerDisplay = document.getElementById('otpTimer');
            const resendBtn = document.getElementById('resendOtpBtn');

            clearInterval(this.timerInterval);

            this.timerInterval = setInterval(() => {
                let minutes = parseInt(timer / 60, 10);
                let seconds = parseInt(timer % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                timerDisplay.textContent = minutes + ":" + seconds;

                if (--timer < 0) {
                    clearInterval(this.timerInterval);
                    resendBtn.style.pointerEvents = 'auto';
                    resendBtn.style.opacity = '1';
                    timerDisplay.textContent = "00:00";
                }
            }, 1000);
        }

        verifyOTPAndContinue() {
            const otpField = document.getElementById('otpField');
            this.syncOtpField();
            const otpVal = otpField.value;
            const otpErrorMsg = document.getElementById('otpErrorMsg');
            const attemptsInfo = document.getElementById('otpAttemptsInfo');

            if (otpVal.length !== 6) {
                otpErrorMsg.innerText = "Please enter 6-digit OTP";
                return;
            }

            this.otpVerifyBtn.disabled = true;
            this.otpVerifyBtn.innerHTML = 'Verifying... <i class="fas fa-spinner fa-spin ms-2"></i>';

            const fd = new FormData();
            fd.append('otp', otpVal);

            fetch('api/apply/verify_mobile_otp.php', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: fd
                })
                .then(res => this.parseJsonResponse(res))
                .then(data => {
                    if (data.status === 'success') {
                        this.otpVerified = true;
                        this.closeOtpModal();
                        this.currentStep = 2;
                        this.updateUI();
                        this.otpVerifyBtn.disabled = false;
                        this.otpVerifyBtn.innerHTML = 'Verify OTP <i class="fas fa-check-circle ms-2"></i>';
                    } else {
                        this.otpAttempts++;
                        const remaining = this.maxAttempts - this.otpAttempts;

                        if (attemptsInfo) attemptsInfo.innerText = `Attempts remaining: ${remaining > 0 ? remaining : 0}`;

                        if (this.otpAttempts >= this.maxAttempts || data.message.includes('Too many attempts')) {
                            this.otpVerifyBtn.disabled = true;
                            throw new Error("Too many incorrect attempts. Please resend OTP or try a different number.");
                        }

                        throw new Error(data.message || 'Invalid OTP');
                    }
                })
                .catch(err => {
                    this.otpVerifyBtn.innerHTML = 'Verify OTP <i class="fas fa-check-circle ms-2"></i>';
                    otpErrorMsg.innerText = err.message;

                    // Only re-enable if we haven't hit the limit
                    if (this.otpAttempts < this.maxAttempts) {
                        this.otpVerifyBtn.disabled = false;
                    }
                });
        }

        setAutoSubmitStatus(type, title, message = '') {
            const status = document.getElementById('autoSubmitStatus');
            if (!status) return;

            if (type === 'success') {
                status.className = 'wf-auto-submit-status success-screen';
                status.style.display = 'block';

                // Hide step review elements
                const hero = document.querySelector('.wf-review-hero');
                if (hero) hero.style.display = 'none';
                const summary = document.getElementById('reviewSummary');
                if (summary) summary.style.display = 'none';
                const decl = document.querySelector('.wf-declaration-group');
                if (decl) decl.style.display = 'none';

                // Make container wide (hiding progress sidebar)
                const applyCard = document.querySelector('.wf-apply-card');
                if (applyCard) applyCard.classList.add('wf-submitted');

                status.innerHTML = `
                    <div class="wf-success-screen">
                        <div class="wf-success-icon-wrap">
                            <div class="wf-success-icon-ring"></div>
                            <i class="fas fa-check-circle wf-success-icon-check"></i>
                        </div>
                        <h2 class="wf-success-title">Application Submitted!</h2>
                        <p class="wf-success-desc">Thank you for choosing Waqt Finance. Your application has been securely uploaded and is under priority review.</p>
                        
                        <div class="wf-success-card">
                            <div class="wf-success-card-row">
                                <span>Application Reference</span>
                                <strong>${message}</strong>
                            </div>
                            <div class="wf-success-card-row">
                                <span>Status</span>
                                <span class="wf-status-badge">Priority Review</span>
                            </div>
                        </div>
                        
                        <div class="wf-success-next-steps">
                            <h4>What Happens Next?</h4>
                            <div class="wf-step-item">
                                <div class="wf-step-num">1</div>
                                <div>
                                    <strong>Document Verification</strong>
                                    <p>Our verification team will review your uploaded PAN, Aadhaar, and Shop photos.</p>
                                </div>
                            </div>
                            <div class="wf-step-item">
                                <div class="wf-step-num">2</div>
                                <div>
                                    <strong>Credit Appraisal</strong>
                                    <p>We will calculate your daily repayment capacity and check your eligibility.</p>
                                </div>
                            </div>
                            <div class="wf-step-item">
                                <div class="wf-step-num">3</div>
                                <div>
                                    <strong>Final Approval & Disbursement</strong>
                                    <p>Upon approval, funds will be disbursed directly to your bank account.</p>
                                </div>
                            </div>
                        </div>

                        <div class="wf-success-actions">
                            <a href="https://wa.me/919217086610" target="_blank" class="wf-btn wf-btn-whatsapp">
                                <i class="fab fa-whatsapp me-2"></i> Chat Support
                            </a>
                            <a href="tel:9217086610" class="wf-btn wf-btn-call">
                                <i class="fas fa-phone-alt me-2"></i> Contact Us
                            </a>
                        </div>
                    </div>
                `;
                return;
            }

            let icon = 'fa-spinner fa-spin';
            let supportActions = '';

            if (type === 'error') {
                icon = 'fa-triangle-exclamation';
                supportActions = `
                    <div class="wf-submit-support-actions" style="margin-top: 12px;">
                        <a href="https://wa.me/919217086610" target="_blank" class="wf-btn wf-btn-whatsapp" style="padding: 8px 16px; font-size: 13px;">
                            <i class="fab fa-whatsapp me-2"></i> Chat Support
                        </a>
                    </div>
                `;
            }

            status.className = `wf-auto-submit-status ${type}`;
            status.style.display = 'grid';
            status.innerHTML = `
                <span><i class="fas ${icon}"></i></span>
                <strong>${this.escapeHtml(title)}</strong>
                <small>${this.escapeHtml(message)}</small>
                ${supportActions}
            `;
        }

        submitFinalApplication() {
            this.btnNext.disabled = true;
            this.btnPrev.disabled = true;
            this.btnNext.innerHTML = 'Submitting... <i class="fas fa-spinner fa-spin ms-2"></i>';
            this.setAutoSubmitStatus('loading', 'Submitting application...', 'Please wait while we securely save your details.');

            const fd = new FormData();
            fd.append('loan_type', 'edi');
            fd.append('fullName', document.getElementById('fullName').value);
            fd.append('phoneField', document.getElementById('phoneField').value);
            fd.append('employeeCode', document.getElementById('employeeCode').value);
            fd.append('emailField', document.getElementById('emailField').value);
            fd.append('addressField', document.getElementById('addressField').value);
            fd.append('landmarkField', document.getElementById('landmarkField').value);
            fd.append('personalCity', document.getElementById('personalCity').value);
            fd.append('pincodeField', document.getElementById('pincodeField').value);
            fd.append('aadhaarField', document.getElementById('aadhaarField').value);
            fd.append('panField', document.getElementById('panField').value);
            fd.append('businessProofField', document.getElementById('businessProofField').value);
            fd.append('businessVerifiedDetails', this.businessVerifiedDetails || '');
            fd.append('businessAddress', this.businessAddress || '');
            fd.append('latitude', this.latitude || '');
            fd.append('longitude', this.longitude || '');
            fd.append('gpsAddress', this.gpsAddress || '');
            fd.append('deviceFingerprint', this.deviceFingerprint || '');
            fd.append('shopBusinessName', document.getElementById('shopBusinessName').value);
            fd.append('registrationType', document.getElementById('registrationType').value);
            fd.append('businessAge', document.getElementById('businessAge').value);
            fd.append('monthlyIncome', document.getElementById('monthlyIncome').value.replace(/,/g, ''));
            fd.append('businessType', document.getElementById('businessType').value);
            fd.append('dailySales', document.getElementById('dailySales').value.replace(/,/g, ''));
            fd.append('loanAmount', document.getElementById('loanAmount').value.replace(/,/g, ''));
            fd.append('purpose', document.getElementById('purpose').value);
            fd.append('city', document.getElementById('personalCity').value);
            fd.append('ifscCode', document.getElementById('ifscCode').value);
            fd.append('bankName', document.getElementById('bankName').value);
            fd.append('branchName', document.getElementById('branchName').value);
            fd.append('accountHolder', document.getElementById('accountHolder').value);
            fd.append('accountNumber', document.getElementById('accountNumber').value);
            fd.append('primaryRefName', document.getElementById('primaryRefName').value);
            fd.append('primaryRefMobile', document.getElementById('primaryRefMobile').value);
            fd.append('primaryRefRelation', document.getElementById('primaryRefRelation').value);
            fd.append('secondaryRefName', document.getElementById('secondaryRefName').value);
            fd.append('secondaryRefMobile', document.getElementById('secondaryRefMobile').value);
            fd.append('secondaryRefRelation', document.getElementById('secondaryRefRelation').value);
            ['panCardDoc', 'aadhaarCardDoc', 'bankStatementDoc', 'businessRegistrationDoc', 'addressProofDoc', 'passportPhotoDoc'].forEach(id => {
                Array.from(document.getElementById(id).files || []).forEach(file => fd.append(id + '[]', file));
            });
            if (document.getElementById('shopFrontPhoto').files[0]) {
                fd.append('shopFrontPhoto', document.getElementById('shopFrontPhoto').files[0]);
            }
            if (document.getElementById('stockPhoto').files[0]) {
                fd.append('stockPhoto', document.getElementById('stockPhoto').files[0]);
            }
            if (document.getElementById('stockPhotoTwo').files[0]) {
                fd.append('stockPhotoTwo', document.getElementById('stockPhotoTwo').files[0]);
            }
            if (this.selfieBlob) {
                fd.append('selfieDoc[]', this.selfieBlob, 'live-selfie.jpg');
            }

            fetch('api/apply/save_common_loan.php', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: fd
                })
                .then(res => this.parseJsonResponse(res))
                .then(data => {
                    if (data.status === 'success') {
                        this.clearDraft();
                        this.submittedApplication = true;
                        this.setAutoSubmitStatus(
                            'success',
                            'Application saved successfully',
                            data.application_id ? data.application_id : 'WAQT-EDI-LEAD'
                        );
                        this.btnPrev.style.display = 'none';
                        this.btnNext.style.display = 'none';
                    } else {
                        throw new Error(data.message || 'Submission failed');
                    }
                })
                .catch(err => {
                    this.autoSubmitStarted = false;
                    this.btnPrev.disabled = false;
                    this.btnNext.disabled = false;
                    this.btnPrev.style.display = 'flex';
                    this.btnNext.style.display = 'flex';
                    this.btnNext.innerHTML = 'Retry Submit <i class="fas fa-rotate-right ms-2"></i>';
                    this.setAutoSubmitStatus('error', 'Application could not be saved', err.message);
                });
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        new EDILoanApplication();
    });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/face-api.js/0.22.2/face-api.min.js"></script>
<?php include 'includes/footer.php' ?>
