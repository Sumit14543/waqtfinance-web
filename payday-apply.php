<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/loan_flow.php';

loan_flow_redirect_to_resume_if_applicable($conn, 'payday-apply.php');

$page_title = "Apply for Payday Loan | Instant Short-Term Cash | Waqt Finance";
$meta_description = "Need cash before the next paycheck? Apply for an instant payday loan with Waqt Finance. Secure, multi-step application for salary advances.";
$meta_keywords = "payday loan application, instant cash, salary advance India, emergency loan online";
$canonical_url = "https://www.waqtfinance.com/payday-apply";
$meta_robots = "noindex, follow";
$body_class = "wf-apply-body-active";

include 'includes/header.php';
include 'includes/main-header.php';
?>


<link rel="stylesheet" href="assets/css/apply-modern.css?v=20260710-compact-fields">

<div class="wf-hero-header-wrap" style="background: #062b5d; color: #ffffff; padding: 26px 0; margin-bottom: 24px; border-bottom: 1px solid rgba(255,255,255,0.1); width: 100%;">
    <div class="wf-hero-header-inner" style="max-width: 1120px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; padding: 0 20px; flex-wrap: wrap; gap: 20px;">
        <div style="display: flex; align-items: center; gap: 18px;">
            <i class="fa fa-shield-alt" style="font-size: 38px; color: #ffffff; opacity: 0.95;"></i>
            <div>
                <h1 style="font-size: 25px; font-weight: 700; color: #ffffff; margin: 0 0 2px 0; border: none; padding: 0; font-family: 'Inter', sans-serif !important; letter-spacing: -0.2px;">Apply for Payday Loan</h1>
                <p style="font-size: 13.5px; color: rgba(255, 255, 255, 0.85); margin: 0; font-family: 'Inter', sans-serif !important;">Get started in minutes with a few simple steps.</p>
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
                    <strong style="display: block; font-weight: 700;">Quick</strong> Advance
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
                <span id="sidebarStepCount" style="font-size: 11px; color: #64748b; font-weight: 600;">Step 1 of 7</span>
            </div>
            <div class="wf-sidebar-progress-bar-custom" style="height: 6px; width: 100%; background: #e2e8f0; border-radius: 10px; overflow: hidden; margin-bottom: 24px; position: relative; grid-column: span 2 !important;">
                <div id="sidebarProgressFill" style="height: 100%; width: 14.3%; background: #18529c; transition: width 0.3s ease;"></div>
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
            </div>
            <div class="wf-step-labels">
                <div class="wf-step-label">
                    <strong style="display: block; font-size: 13px; font-weight: 700; color: #334155; line-height: 1.2;">Basic Details</strong>
                    <small style="display: block; font-size: 11px; color: #64748b; font-weight: 400; margin-top: 2px; line-height: 1.2;">Provide basic details</small>
                </div>
                <div class="wf-step-label">
                    <strong style="display: block; font-size: 13px; font-weight: 700; color: #334155; line-height: 1.2;">PAN Verify</strong>
                    <small style="display: block; font-size: 11px; color: #64748b; font-weight: 400; margin-top: 2px; line-height: 1.2;">Verify PAN card</small>
                </div>
                <div class="wf-step-label">
                    <strong style="display: block; font-size: 13px; font-weight: 700; color: #334155; line-height: 1.2;">Aadhaar Verify</strong>
                    <small style="display: block; font-size: 11px; color: #64748b; font-weight: 400; margin-top: 2px; line-height: 1.2;">Identity verification</small>
                </div>
                <div class="wf-step-label">
                    <strong style="display: block; font-size: 13px; font-weight: 700; color: #334155; line-height: 1.2;">Work Details</strong>
                    <small style="display: block; font-size: 11px; color: #64748b; font-weight: 400; margin-top: 2px; line-height: 1.2;">Add professional info</small>
                </div>
                <div class="wf-step-label">
                    <strong style="display: block; font-size: 13px; font-weight: 700; color: #334155; line-height: 1.2;">Bank Details</strong>
                    <small style="display: block; font-size: 11px; color: #64748b; font-weight: 400; margin-top: 2px; line-height: 1.2;">Link salary account</small>
                </div>
                <div class="wf-step-label">
                    <strong style="display: block; font-size: 13px; font-weight: 700; color: #334155; line-height: 1.2;">References</strong>
                    <small style="display: block; font-size: 11px; color: #64748b; font-weight: 400; margin-top: 2px; line-height: 1.2;">Provide references</small>
                </div>
                <div class="wf-step-label">
                    <strong style="display: block; font-size: 13px; font-weight: 700; color: #334155; line-height: 1.2;">Upload Docs</strong>
                    <small style="display: block; font-size: 11px; color: #64748b; font-weight: 400; margin-top: 2px; line-height: 1.2;">Upload salary slips</small>
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
                    <span id="mobileStepCount">Step 1 of 7</span>
                    <strong id="mobileStepTitle">Basic Details</strong>
                </div>
                <div class="wf-mobile-progress-track">
                    <span id="mobileProgressFill" style="width: 14.3%;"></span>
                </div>
            </div>
        </div>

        <form id="paydayloanForm" class="wf-form-content" method="POST" novalidate>
            <input type="hidden" name="city" id="payday_city" value="<?php echo htmlspecialchars($_GET['city'] ?? ''); ?>">
            <div class="wf-disclosure-strip" role="note" aria-label="Application disclosure">
                <span><i class="fas fa-shield-alt"></i> Encrypted application</span>
                <span><i class="fas fa-user-check"></i> Consent-based checks</span>
                <span><i class="fas fa-file-signature"></i> Key terms before final acceptance</span>
            </div>
            <div class="wf-step-intro" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h3 style="font-size: 22px; font-weight: 700; color: #0b213a; margin: 0 0 6px 0; font-family: 'Inter', sans-serif !important;">Basic Details</h3>
                    <p style="font-size: 13.5px; color: #64748b; margin: 0; font-family: 'Inter', sans-serif !important; line-height: 1.45;">Get started in minutes with a few simple steps.</p>
                </div>
                <div class="wf-secure-badge" style="display: inline-flex; align-items: center; gap: 6px; background: #ecfdf5; border: 1px solid #10b981; color: #10b981; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; font-family: 'Inter', sans-serif !important;">
                    <i class="fas fa-lock"></i> Encrypted & consent-led
                </div>
            </div>

            <div id="paydayFormAlert" class="lf-form-alert" role="alert" aria-live="polite" hidden></div>

            <div class="wf-input-group">
                <label class="wf-label" style="font-weight: 600; color: #334155; font-size: 13.5px; margin-bottom: 6px;">Employment Status <span class="text-danger">*</span></label>
                <div class="wf-toggle-grid">
                    <div class="wf-toggle-item">
                        <input type="radio" id="pdaySalaried" name="emp" value="salaried" checked>
                        <label for="pdaySalaried" class="wf-toggle-label">
                            <i class="fas fa-briefcase"></i>
                            <span>Salaried</span>
                        </label>
                    </div>
                    <div class="wf-toggle-item">
                        <input type="radio" id="pdaySelf" name="emp" value="self">
                        <label for="pdaySelf" class="wf-toggle-label">
                            <i class="fas fa-store"></i>
                            <span>Self Employed</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="wf-grid">
                <div class="wf-input-group">
                    <label class="wf-label" style="font-weight: 600; color: #334155; font-size: 13.5px; margin-bottom: 6px;">Monthly Salary <span class="text-danger">*</span></label>
                    <div class="wf-input-wrapper lf-input-prefix-wrap" id="salaryWrap" style="display: flex; align-items: center; border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; background: #ffffff; transition: border-color 0.2s, box-shadow 0.2s;">
                        <div class="lf-input-prefix" style="background: #f8fafc; padding: 0 16px; border-right: 1px solid #cbd5e1; font-weight: 600; display: flex; align-items: center; justify-content: center; color: #475569; font-size: 16px; height: 48px; min-width: 44px; box-sizing: border-box;">₹</div>
                        <input type="text" name="monthly_salary" id="monthly_salary" data-currency class="wf-control lf-input" placeholder="50,000" inputmode="numeric" autocomplete="off" style="border: none !important; box-shadow: none !important; border-radius: 0 !important; height: 48px; font-size: 15px; font-weight: 500; padding: 0 16px; width: 100%; box-sizing: border-box; outline: none;">
                    </div>
                    <small class="lf-error-text" id="salaryError" style="color: #ef4444; font-size: 12px; font-weight: 600; margin-top: 4px; display: block;"></small>
                </div>

                <div class="wf-input-group">
                    <label class="wf-label" style="font-weight: 600; color: #334155; font-size: 13.5px; margin-bottom: 6px;">Loan Amount Required <span class="text-danger">*</span></label>
                    <div class="wf-input-wrapper lf-input-prefix-wrap" id="loanAmountWrap" style="display: flex; align-items: center; border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; background: #ffffff; transition: border-color 0.2s, box-shadow 0.2s;">
                        <div class="lf-input-prefix" style="background: #f8fafc; padding: 0 16px; border-right: 1px solid #cbd5e1; font-weight: 600; display: flex; align-items: center; justify-content: center; color: #475569; font-size: 16px; height: 48px; min-width: 44px; box-sizing: border-box;">₹</div>
                        <input type="text" name="loan_amount" id="loan_amount" data-currency class="wf-control lf-input" placeholder="25,000" inputmode="numeric" autocomplete="off" style="border: none !important; box-shadow: none !important; border-radius: 0 !important; height: 48px; font-size: 15px; font-weight: 500; padding: 0 16px; width: 100%; box-sizing: border-box; outline: none;">
                    </div>
                    <small class="lf-error-text" id="loanAmountError" style="color: #ef4444; font-size: 12px; font-weight: 600; margin-top: 4px; display: block;"></small>
                </div>

                <div class="wf-input-group">
                    <label class="wf-label" style="font-weight: 600; color: #334155; font-size: 13.5px; margin-bottom: 6px;">Purpose of Loan <span class="text-danger">*</span></label>
                    <div class="wf-input-wrapper">
                        <i class="fas fa-question-circle wf-input-icon"></i>
                        <select class="wf-control lf-select" name="loan_purpose" id="loan_purpose" style="padding-left: 44px !important; height: 48px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14.5px; font-weight: 500; font-family: 'Inter', sans-serif !important; width: 100%; box-sizing: border-box; background-color: #ffffff;">
                            <option value="" selected>Select purpose</option>
                            <option value="Marriage">Marriage</option>
                            <option value="Tuition fees">Tuition fees</option>
                            <option value="Education">Education</option>
                            <option value="Medical / Health">Medical / Health</option>
                        </select>
                    </div>
                    <small class="lf-error-text" id="loanPurposeError" style="color: #ef4444; font-size: 12px; font-weight: 600; margin-top: 4px; display: block;"></small>
                </div>

                <div class="wf-input-group">
                    <label class="wf-label" style="font-weight: 600; color: #334155; font-size: 13.5px; margin-bottom: 6px;">Any running loan? <span class="text-danger">*</span></label>
                    <div style="display: flex; gap: 24px; align-items: center; height: 48px;">
                        <div style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="radio" id="runningLoanNo" name="has_running_loan" value="0" checked style="width: 18px; height: 18px; accent-color: #18529c; cursor: pointer;">
                            <label for="runningLoanNo" style="font-size: 14.5px; font-weight: 600; color: #334155; cursor: pointer; margin: 0;">No</label>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="radio" id="runningLoanYes" name="has_running_loan" value="1" style="width: 18px; height: 18px; accent-color: #18529c; cursor: pointer;">
                            <label for="runningLoanYes" style="font-size: 14.5px; font-weight: 600; color: #334155; cursor: pointer; margin: 0;">Yes</label>
                        </div>
                    </div>
                </div>

                <div class="wf-input-group">
                    <label class="wf-label" style="font-weight: 600; color: #334155; font-size: 13.5px; margin-bottom: 6px;">Phone <span class="text-danger">*</span></label>
                    <div class="wf-input-wrapper">
                        <input type="tel" name="mobile" id="mobile" class="wf-control lf-input" placeholder="Enter mobile number" inputmode="tel" autocomplete="tel-national" style="height: 48px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14.5px; font-weight: 500; font-family: 'Inter', sans-serif !important; width: 100%; box-sizing: border-box;">
                    </div>
                    <small class="lf-error-text" id="mobileError" style="color: #ef4444; font-size: 12px; font-weight: 600; margin-top: 4px; display: block;"></small>
                </div>

                <div class="wf-input-group">
                    <label class="wf-label" style="font-weight: 600; color: #334155; font-size: 13.5px; margin-bottom: 6px;">Email <span class="text-danger">*</span></label>
                    <div class="wf-input-wrapper">
                        <i class="fas fa-envelope wf-input-icon"></i>
                        <input type="email" name="email" id="email" class="wf-control lf-input" placeholder="name@example.com" autocomplete="email" style="padding-left: 44px !important; height: 48px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14.5px; font-weight: 500; font-family: 'Inter', sans-serif !important; width: 100%; box-sizing: border-box;">
                    </div>
                    <small class="lf-error-text" id="emailError" style="color: #ef4444; font-size: 12px; font-weight: 600; margin-top: 4px; display: block;"></small>
                </div>
            </div>

            <div class="wf-consent-box" style="border: 1px solid #dbeafe; border-radius: 12px; padding: 20px; background: #ffffff; display: flex; flex-direction: column; gap: 12px; margin-top: 24px; margin-bottom: 20px;">
                <div class="wf-consent-summary">
                    <strong>Before you continue</strong>
                    We use these details for eligibility, KYC/credit checks, lender matching, and required service communication.
                </div>
                <label class="terms-check" style="display: flex; gap: 12px; align-items: center; cursor: pointer; margin: 0; padding: 0; background: transparent; border: 0; box-shadow: none;">
                    <input type="checkbox" name="terms" id="terms" class="termsCheckbox" required style="width: 18px; height: 18px; margin: 0; accent-color: #18529c; cursor: pointer;">
                    <span style="font-size: 13.5px; font-weight: 500; color: #0b213a; font-family: 'Inter', sans-serif !important;">I agree to the <a href="terms-condition" target="_blank" rel="noopener" style="color: #18529c; font-weight: 700; text-decoration: underline;">Terms and Conditions</a> and <a href="privacy-policy" target="_blank" rel="noopener" style="color: #18529c; font-weight: 700; text-decoration: underline;">Privacy Policy</a> <i class="fas fa-external-link-alt" style="font-size: 11px; margin-left: 2px;"></i></span>
                </label>
                <small class="lf-error-text" id="termsError" style="color: #ef4444; font-size: 12px; font-weight: 600; margin-top: -6px; padding-left: 30px; display: none;">You must agree to the terms</small>

                <label class="terms-check" style="display: flex; gap: 12px; align-items: flex-start; cursor: pointer; margin: 0; padding: 12px; background: #f0f7ff; border-radius: 8px; border: 0; box-shadow: none;">
                    <input type="checkbox" class="my-checkbox second-check" checked style="width: 18px; height: 18px; margin-top: 2px; accent-color: #18529c; cursor: pointer;">
                    <span style="font-size: 13px; line-height: 1.45; color: #334155; font-weight: 500; font-family: 'Inter', sans-serif !important;">You authorize Waqt Finance to contact you by phone, SMS, RCS, WhatsApp, or email about this application and related service updates. Promotional communication can be opted out anytime.</span>
                </label>

                <label class="terms-check" style="display: flex; gap: 12px; align-items: flex-start; cursor: pointer; margin: 0; padding: 12px; background: #f0f7ff; border-radius: 8px; border: 0; box-shadow: none;">
                    <input type="checkbox" class="my-checkbox-two" checked style="width: 18px; height: 18px; margin-top: 2px; accent-color: #18529c; cursor: pointer;">
                    <span style="font-size: 13px; line-height: 1.45; color: #334155; font-weight: 500; font-family: 'Inter', sans-serif !important;">You authorize need-based KYC, credit bureau, employment, and lender checks for eligibility. Consent can be withdrawn subject to legal and processing requirements.</span>
                </label>
            </div>

            <button type="submit" id="mainSubmitBtn" class="lf-btn lf-btn-primary" disabled style="width: 100% !important; max-width: none !important; font-family: 'Inter', sans-serif !important; height: 48px; border-radius: 8px; font-weight: 700; font-size: 15px;">
                Send OTP <i class="fa-solid fa-paper-plane ms-2"></i>
            </button>

        </form>
    </div>

    <div class="wf-bottom-security-text" style="text-align: center; margin-top: 24px; color: #64748b; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 8px;">
        <i class="fas fa-lock" style="color: #64748b; font-size: 13px;"></i>
        <span style="font-family: 'Inter', sans-serif !important;">Your data is encrypted in transit and used only for this application, verification, lender matching, and legally required records.</span>
    </div>
</div>

<!--popup-->

<!-- Popup Modal -->
<div class="wf-overlay" id="termsModal">

    <div class="wf-modal">

        <h2 class="wf-title">Terms and Conditions</h2>

        <div class="wf-content">

            <p><b>PLEASE READ THESE TERMS AND CONDITIONS ("TERMS") CAREFULLY.</b> THESE TERMS CONSTITUTE A LEGALLY
                BINDING AGREEMENT BETWEEN YOU AND <b>Waqt Finance</b>.</p>

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

<!--end popup--><script>
document.addEventListener("DOMContentLoaded", function() {
    const salaryInput = document.getElementById("monthly_salary");
    const salaryWrap = document.getElementById("salaryWrap");
    const loanAmountInput = document.getElementById("loan_amount");
    const loanAmountWrap = document.getElementById("loanAmountWrap");
    const firstStepDraftKey = "waqt_payday_first_step_draft";
    const paydayFormAlert = document.getElementById("paydayFormAlert");

    if (!salaryInput || !loanAmountInput) return;

    function showPaydayFormAlert(message, type = "error") {
        if (!paydayFormAlert) return;
        paydayFormAlert.textContent = message || "Something went wrong. Please try again.";
        paydayFormAlert.className = `lf-form-alert lf-form-alert-${type}`;
        paydayFormAlert.hidden = false;
    }

    function hidePaydayFormAlert() {
        if (!paydayFormAlert) return;
        paydayFormAlert.textContent = "";
        paydayFormAlert.hidden = true;
    }

    function formatMoneyInput(input) {
        let value = input.value.replace(/,/g, '').replace(/[^0-9]/g, '').slice(0, 8);
        input.value = value ? Number(value).toLocaleString('en-IN') : "";
    }

    salaryInput.addEventListener("input", function () {
        formatMoneyInput(this);
    });

    loanAmountInput.addEventListener("input", function () {
        formatMoneyInput(this);
    });

    const mobileField = document.getElementById("mobile");
    if (mobileField) {
        mobileField.addEventListener("input", function () {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }

    function readFirstStepDraft() {
        try {
            const raw = localStorage.getItem(firstStepDraftKey);
            if (!raw) return null;
            const draft = JSON.parse(raw);
            if (!draft || !draft.savedAt) return null;
            const maxAge = 7 * 24 * 60 * 60 * 1000;
            if (Date.now() - draft.savedAt > maxAge) {
                localStorage.removeItem(firstStepDraftKey);
                return null;
            }
            return draft;
        } catch (error) {
            return null;
        }
    }

    function saveFirstStepDraft() {
        try {
            const checkedEmp = document.querySelector('input[name="emp"]:checked');
            const checkedLoan = document.querySelector('input[name="has_running_loan"]:checked');
            localStorage.setItem(firstStepDraftKey, JSON.stringify({
                emp: checkedEmp ? checkedEmp.value : "salaried",
                monthly_salary: document.getElementById("monthly_salary").value,
                loan_amount: document.getElementById("loan_amount").value,
                loan_purpose: document.getElementById("loan_purpose").value,
                has_running_loan: checkedLoan ? checkedLoan.value : "0",
                mobile: document.getElementById("mobile").value,
                email: document.getElementById("email").value,
                terms: document.getElementById("terms").checked,
                savedAt: Date.now()
            }));
        } catch (error) { }
    }

    function restoreFirstStepDraft() {
        const draft = readFirstStepDraft();
        if (!draft) return;

        const emp = document.querySelector(`input[name="emp"][value="${draft.emp || "salaried"}"]`);
        if (emp) emp.checked = true;

        const runningLoan = document.querySelector(`input[name="has_running_loan"][value="${draft.has_running_loan || "0"}"]`);
        if (runningLoan) runningLoan.checked = true;

        document.getElementById("monthly_salary").value = draft.monthly_salary || "";
        document.getElementById("loan_amount").value = draft.loan_amount || "";
        document.getElementById("loan_purpose").value = draft.loan_purpose || "";
        document.getElementById("mobile").value = draft.mobile || "";
        document.getElementById("email").value = draft.email || "";
        const termsCheck = document.getElementById("terms");
        if (termsCheck) termsCheck.checked = Boolean(draft.terms);
    }
   
    const paydayForm = document.getElementById("paydayloanForm");
    if (paydayForm) {
        paydayForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const form = this;

            let salary = document.getElementById("monthly_salary");
            let loanAmount = document.getElementById("loan_amount");
            let loanPurpose = document.getElementById("loan_purpose");
            let mobile = document.getElementById("mobile");
            let email = document.getElementById("email");
            let terms = document.getElementById("terms");

            let isValid = true;
            hidePaydayFormAlert();

            // Clear errors
            document.querySelectorAll(".lf-error-text").forEach(el => el.innerText = "");
            document.querySelectorAll(".lf-input").forEach(el => el.classList.remove("lf-error-border"));
            document.querySelectorAll(".lf-select").forEach(el => el.classList.remove("lf-error-border"));
            salaryWrap.classList.remove("lf-error-border");
            loanAmountWrap.classList.remove("lf-error-border");

            // 💰 Salary validation
            let salaryVal = salary.value.replace(/,/g, '');
            if (salaryVal === "" || isNaN(salaryVal) || salaryVal <= 0) {
                document.getElementById("salaryError").innerText = "Enter valid salary";
                salaryWrap.classList.add("lf-error-border");
                isValid = false;
            }

            let loanAmountVal = loanAmount.value.replace(/,/g, '');
            if (loanAmountVal === "" || isNaN(loanAmountVal) || loanAmountVal < 1000 || loanAmountVal > 10000000) {
                document.getElementById("loanAmountError").innerText = "Enter amount between ₹1,000 and ₹1 Cr";
                loanAmountWrap.classList.add("lf-error-border");
                isValid = false;
            }

            if (!loanPurpose.value) {
                document.getElementById("loanPurposeError").innerText = "Select loan purpose";
                loanPurpose.classList.add("lf-error-border");
                isValid = false;
            }

            // 📱 Phone validation using globally accessible iti
            if (window.iti && !window.iti.isValidNumber()) {
                document.getElementById("mobileError").innerText = "Enter valid mobile number";
                mobile.classList.add("lf-error-border");
                isValid = false;
            }

            // 🪪 Email validation
            let emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
            if (!emailRegex.test(email.value)) {
                document.getElementById("emailError").innerText = "Invalid email address";
                email.classList.add("lf-error-border");
                isValid = false;
            }

            // 📜 Terms
            if (!terms.checked) {
                showPaydayFormAlert("Please accept the terms to continue.");
                isValid = false;
            }

            if (!isValid) return;

            // 📱 full number
            if (window.iti) {
                mobile.value = window.iti.getNumber().replace('+91', '');
            }

            let formData = new FormData(form);

            const btn = document.getElementById("mainSubmitBtn");
            btn.disabled = true;
            btn.innerHTML = "<div class='lf-spinner'></div> Sending OTP...";

            fetch("api/apply/save_payday_form_details.php", {
                method: "POST",
                headers: {
                    "X-CSRF-Token": window.WAQT_CSRF_TOKEN || "<?php echo $_SESSION['csrf_token'] ?? ''; ?>"
                },
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {
                        // Send OTP before redirecting
                        return fetch("api/apply/send_otp.php", {
                            method: "POST",
                            headers: {
                                "X-CSRF-Token": window.WAQT_CSRF_TOKEN || "<?php echo $_SESSION['csrf_token'] ?? ''; ?>"
                            }
                        }).then(res => res.json()).then(otpData => {
                            if (["success", "partial"].includes(otpData.status) || otpData.retry_after) {
                                try { localStorage.removeItem(firstStepDraftKey); } catch (error) { }
                                window.location.href = data.redirect;
                            } else {
                                throw new Error(otpData.message || "Failed to send OTP");
                            }
                        });
                    } else {
                        throw new Error(data.message || "Unable to save details. Please try again.");
                    }
                })
                .catch((err) => {
                    btn.disabled = false;
                    btn.innerHTML = "Send OTP <i class='fa-solid fa-paper-plane ms-2'></i>";
                    if (window.checkFormValidity) window.checkFormValidity();
                    showPaydayFormAlert(err.message || "Network error. Please check your connection and try again.");
                });
        });
    }

    // Expose helpers globally
    window.saveFirstStepDraft = saveFirstStepDraft;
    window.restoreFirstStepDraft = restoreFirstStepDraft;
});
</script>

<?php include 'includes/footer.php' ?>
<style>
    .lf-form-alert {
        border-radius: 8px;
        font-size: 14px;
        line-height: 1.45;
        margin-bottom: 16px;
        padding: 12px 14px;
    }

    .lf-form-alert-error {
        background: #fff1f1;
        border: 1px solid #ffc6c6;
        color: #a11f1f;
    }

    .lf-form-alert-success {
        background: #eefaf4;
        border: 1px solid #b9ead0;
        color: #17623b;
    }

    /* Adjust intl-tel-input width inside lf-form-group */
    .iti {
        width: 100%;
        display: block;
    }

    .iti__flag-container {
        z-index: 5;
    }

    .lf-error-border {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1) !important;
    }

    /* Employment Status Premium Cards styling */
    body:has(.wf-apply-body) .wf-toggle-label {
        position: relative;
        border: 1px solid #cbd5e1 !important;
        border-radius: 12px !important;
        background: #ffffff !important;
        cursor: pointer;
        min-height: 76px !important;
        padding: 12px 16px !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 2px 4px rgba(15, 23, 42, 0.01) !important;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    body:has(.wf-apply-body) .wf-toggle-label:hover {
        transform: translateY(-3px) !important;
        border-color: #cbd5e1 !important;
        box-shadow: 0 10px 18px -4px rgba(15, 23, 42, 0.06), 0 4px 6px -2px rgba(15, 23, 42, 0.02) !important;
    }

    body:has(.wf-apply-body) .wf-toggle-item input:checked + .wf-toggle-label {
        background: #f0f7ff !important;
        border-color: #18529c !important;
        color: #18529c !important;
        box-shadow: 0 4px 12px rgba(24, 82, 156, 0.1) !important;
    }

    body:has(.wf-apply-body) .wf-toggle-item input:checked + .wf-toggle-label i {
        color: #18529c !important;
    }

    body:has(.wf-apply-body) .wf-toggle-item input:checked + .wf-toggle-label span {
        color: #18529c !important;
    }

    body:has(.wf-apply-body) .wf-toggle-label i {
        font-size: 24px !important;
        margin-bottom: 5px !important;
        color: #64748b !important;
        transition: color 0.2s ease;
    }

    body:has(.wf-apply-body) .wf-toggle-label span {
        text-transform: none !important;
        font-size: 13.5px !important;
        font-weight: 600 !important;
        color: #475569 !important;
        transition: color 0.2s ease;
    }

    /* Add a premium check badge in the corner when selected */
    body:has(.wf-apply-body) .wf-toggle-item input:checked + .wf-toggle-label::after {
        content: "\f058"; /* FontAwesome circle check mark */
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 16px;
        color: #18529c;
        animation: scaleIn 0.2s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
    }

    @keyframes scaleIn {
        0% { transform: scale(0); }
        100% { transform: scale(1); }
    }

    /* Dial code flag addon styling */
    body:has(.wf-apply-body) #mobile {
        padding-left: 92px !important;
    }

    body:has(.wf-apply-body) .iti__selected-flag {
        background: #f8fafc !important;
        border-right: 1px solid #cbd5e1 !important;
        border-top-left-radius: 7px !important;
        border-bottom-left-radius: 7px !important;
        height: calc(100% - 2px) !important;
        margin: 1px 0 1px 1px !important;
        padding-right: 8px !important;
    }

    body:has(.wf-apply-body) .iti__selected-flag:hover {
        background: #f1f5f9 !important;
    }
</style>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const phoneInput = document.querySelector("#mobile");
    if (!phoneInput) return;

    const iti = window.intlTelInput(phoneInput, {
        initialCountry: "in",
        separateDialCode: true,
        preferredCountries: ["in"],
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"
    });

    // Make iti accessible to submit validation
    window.iti = iti;

    // Form active/inactive logic
    const salaryInputEl = document.getElementById("monthly_salary");
    const loanAmountInputEl = document.getElementById("loan_amount");
    const loanPurposeInputEl = document.getElementById("loan_purpose");
    const mobileInputEl = document.getElementById("mobile");
    const emailInputEl = document.getElementById("email");
    const termsInputEl = document.getElementById("terms");
    const mainSubmitBtnEl = document.getElementById("mainSubmitBtn");

    function checkFormValidity() {
        if (!salaryInputEl || !loanAmountInputEl || !loanPurposeInputEl || !mobileInputEl || !emailInputEl || !termsInputEl || !mainSubmitBtnEl) return;
        let sVal = salaryInputEl.value.replace(/,/g, '');
        let isValid = true;

        if (sVal === "" || isNaN(sVal) || sVal <= 0) isValid = false;

        let lVal = loanAmountInputEl.value.replace(/,/g, '');
        if (lVal === "" || isNaN(lVal) || Number(lVal) < 1000 || Number(lVal) > 10000000) isValid = false;

        if (!loanPurposeInputEl.value) isValid = false;

        if (mobileInputEl.value.trim() === "") isValid = false;
        // Ensure intl-tel-input says it's valid
        if (typeof iti !== 'undefined' && !iti.isValidNumber()) isValid = false;

        let emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        if (!emailRegex.test(emailInputEl.value)) isValid = false;

        if (!termsInputEl.checked) isValid = false;

        mainSubmitBtnEl.disabled = !isValid;
    }

    [salaryInputEl, loanAmountInputEl, loanPurposeInputEl, mobileInputEl, emailInputEl, termsInputEl].forEach(el => {
        if (el) {
            el.addEventListener("input", checkFormValidity);
            el.addEventListener("change", checkFormValidity);
            el.addEventListener("input", saveFirstStepDraft);
            el.addEventListener("change", saveFirstStepDraft);
        }
    });

    document.querySelectorAll('input[name="emp"], input[name="has_running_loan"]').forEach(el => {
        el.addEventListener("change", saveFirstStepDraft);
    });

    // Check validity on phone input specific events
    phoneInput.addEventListener('countrychange', checkFormValidity);

    restoreFirstStepDraft();
    checkFormValidity();
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const checkbox = document.querySelector(".termsCheckbox");
    const modal = document.getElementById("termsModal");
    const acceptBtn = document.getElementById("acceptBtn");
    const declineBtn = document.getElementById("declineBtn");
    if (!checkbox || !modal || !acceptBtn || !declineBtn) return;

    /* Checkbox click => popup open */
    checkbox.addEventListener("click", function (e) {
        modal.style.display = "flex";
    });

    acceptBtn.addEventListener("click", function () {
        checkbox.checked = true;
        modal.style.display = "none";
        // Trigger validity check since checkFormValidity is listening to checkbox change
        const termsInputEl = document.getElementById("terms");
        if (termsInputEl) {
            termsInputEl.dispatchEvent(new Event("change", { bubbles: true }));
        }
    });

    declineBtn.addEventListener("click", function () {
        checkbox.checked = false;
        modal.style.display = "none";
        const termsInputEl = document.getElementById("terms");
        if (termsInputEl) {
            termsInputEl.dispatchEvent(new Event("change", { bubbles: true }));
        }
    });
});
</script>
