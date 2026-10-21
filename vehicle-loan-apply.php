    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'init_otp') {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['mobile_number'] = $_POST['mobile'] ?? '';
        $_SESSION['Full_Name'] = $_POST['fullName'] ?? '';
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        echo json_encode(['status' => 'success', 'csrf_token' => $_SESSION['csrf_token']]);
        exit;
    }

    $page_title = "Apply for Vehicle Loan | New & Used Car Finance | Waqt Finance";
    $meta_description = "Drive your dreams with Waqt Finance. Securely apply for a car or two-wheeler loan through our premium, multi-step application portal.";
    $meta_keywords = "vehicle loan application, car finance India, auto loan online, private vehicle funding";
    $canonical_url = "https://waqtfinance.com/vehicle-loan-apply";
    $meta_robots = "noindex, follow";

    // Adding the modern apply CSS
    $extra_head = '<link rel="stylesheet" href="assets/css/apply-modern.css?v=20260710-compact-fields">';

    include 'includes/header.php';
    include 'includes/main-header.php';
    ?>

    <div class="wf-hero-header-wrap" style="background: #062b5d; color: #ffffff; padding: 26px 0; margin-bottom: 24px; border-bottom: 1px solid rgba(255,255,255,0.1); width: 100%;">
        <div class="wf-hero-header-inner" style="max-width: 1120px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; padding: 0 20px; flex-wrap: wrap; gap: 20px;">
            <div style="display: flex; align-items: center; gap: 18px;">
                <i class="fa fa-shield-alt" style="font-size: 38px; color: #ffffff; opacity: 0.95;"></i>
                <div>
                    <h1 style="font-size: 25px; font-weight: 700; color: #ffffff; margin: 0 0 2px 0; border: none; padding: 0; font-family: 'Inter', sans-serif !important; letter-spacing: -0.2px;">Vehicle Loan Application</h1>
                    <p style="font-size: 13.5px; color: rgba(255, 255, 255, 0.85); margin: 0; font-family: 'Inter', sans-serif !important;">Accelerate your journey with secure auto finance</p>
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
                    <span id="sidebarStepCount" style="font-size: 11px; color: #64748b; font-weight: 600;">Step 1 of 4</span>
                </div>
                <div class="wf-sidebar-progress-bar-custom" style="height: 6px; width: 100%; background: #e2e8f0; border-radius: 10px; overflow: hidden; margin-bottom: 24px; position: relative; grid-column: span 2 !important;">
                    <div id="sidebarProgressFill" style="height: 100%; width: 25%; background: #18529c; transition: width 0.3s ease;"></div>
                </div>
                <div class="wf-progress-steps">
                    <div class="wf-progress-line"></div>
                    <div class="wf-step-node active" data-step="1">1</div>
                    <div class="wf-step-node" data-step="2">2</div>
                    <div class="wf-step-node" data-step="3">3</div>
                    <div class="wf-step-node" data-step="4">4</div>
                </div>
                <div class="wf-step-labels">
                    <div class="wf-step-label">
                        <strong style="display: block; font-size: 13px; font-weight: 700; color: #334155; line-height: 1.2;">Applicant</strong>
                        <small style="display: block; font-size: 11px; color: #64748b; font-weight: 400; margin-top: 2px; line-height: 1.2;">Verify your identity</small>
                    </div>
                    <div class="wf-step-label">
                        <strong style="display: block; font-size: 13px; font-weight: 700; color: #334155; line-height: 1.2;">OTP Verification</strong>
                        <small style="display: block; font-size: 11px; color: #64748b; font-weight: 400; margin-top: 2px; line-height: 1.2;">Verify your mobile</small>
                    </div>
                    <div class="wf-step-label">
                        <strong style="display: block; font-size: 13px; font-weight: 700; color: #334155; line-height: 1.2;">Vehicle Info</strong>
                        <small style="display: block; font-size: 11px; color: #64748b; font-weight: 400; margin-top: 2px; line-height: 1.2;">Add vehicle details</small>
                    </div>
                    <div class="wf-step-label">
                        <strong style="display: block; font-size: 13px; font-weight: 700; color: #334155; line-height: 1.2;">Verification</strong>
                        <small style="display: block; font-size: 11px; color: #64748b; font-weight: 400; margin-top: 2px; line-height: 1.2;">Final security check</small>
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
                        <span id="mobileStepCount">Step 1 of 4</span>
                        <strong id="mobileStepTitle">Applicant</strong>
                    </div>
                    <div class="wf-mobile-progress-track">
                        <span id="mobileProgressFill"></span>
                    </div>
                </div>
            </div>

            <form id="applyForm" class="wf-form-content" novalidate>
                <input type="hidden" name="loan_type" value="vehicle">
                <div class="wf-disclosure-strip" role="note" aria-label="Application disclosure">
                    <span><i class="fas fa-shield-alt"></i> Encrypted application</span>
                    <span><i class="fas fa-user-check"></i> Consent-based checks</span>
                    <span><i class="fas fa-file-signature"></i> Key terms before final acceptance</span>
                </div>
                <!-- STEP 1: APPLICANT INFO -->
                <div class="wf-step-panel active" id="step-1">
                    <div class="wf-step-intro" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                        <div>
                            <h3 style="font-size: 22px; font-weight: 700; color: #0b213a; margin: 0 0 6px 0; font-family: 'Inter', sans-serif !important;">Applicant</h3>
                            <p style="font-size: 13.5px; color: #64748b; margin: 0; font-family: 'Inter', sans-serif !important; line-height: 1.45;">Verify your identity by entering your full name and mobile number.</p>
                        </div>
                        <div class="wf-secure-badge" style="display: inline-flex; align-items: center; gap: 6px; background: #ecfdf5; border: 1px solid #10b981; color: #10b981; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; font-family: 'Inter', sans-serif !important;">
                            <i class="fas fa-lock"></i> Encrypted & consent-led
                        </div>
                    </div>

                    <div class="wf-grid">
                        <div class="wf-input-group">
                            <label class="wf-label" style="font-weight: 600; color: #334155; font-size: 13.5px; margin-bottom: 6px; font-family: 'Inter', sans-serif !important;">Applicant Full Name <span class="text-danger">*</span></label>
                            <div class="wf-input-wrapper">
                                <i class="fas fa-user wf-input-icon"></i>
                                <input type="text" class="wf-control" id="fullName" required placeholder="Full Name" autocomplete="name">
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

                        <div class="wf-input-group">
                            <label class="wf-label" style="font-weight: 600; color: #334155; font-size: 13.5px; margin-bottom: 6px; font-family: 'Inter', sans-serif !important;">Personal PAN Number <span class="text-danger">*</span></label>
                            <div class="wf-input-wrapper">
                                <i class="fas fa-id-card wf-input-icon"></i>
                                <input type="text" class="wf-control" id="panField" required maxlength="10" style="text-transform:uppercase;" placeholder="ABCDE1234F" autocapitalize="characters" autocomplete="off">
                            </div>
                            <div class="wf-error-msg"></div>
                        </div>

                        <div class="wf-input-group">
                            <label class="wf-label" style="font-weight: 600; color: #334155; font-size: 13.5px; margin-bottom: 6px; font-family: 'Inter', sans-serif !important;">Email Address <span style="font-size: 11px; font-weight: normal; color: #64748b;">(Optional)</span></label>
                            <div class="wf-input-wrapper">
                                <i class="fas fa-envelope wf-input-icon"></i>
                                <input type="email" class="wf-control" id="emailField" placeholder="you@email.com" autocomplete="email">
                            </div>
                            <div class="wf-error-msg"></div>
                        </div>
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
                            <span style="font-size: 13px; line-height: 1.45; color: #334155; font-weight: 500; font-family: 'Inter', sans-serif !important;">You authorize need-based KYC, credit bureau, and vehicle/asset verification checks for eligibility. Consent can be withdrawn subject to legal and processing requirements.</span>
                        </label>
                    </div>
                </div>

                <!-- STEP 2: OTP VERIFICATION -->
                <div class="wf-step-panel" id="step-2">
                    <div class="text-center mb-4">
                        <div class="mb-3" style="font-size: 48px; color: var(--wf-primary);">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3 class="fw-bold">Verify Mobile</h3>
                        <p class="text-muted">Enter the 6-digit OTP sent to your mobile number.</p>
                    </div>

                    <div class="wf-input-group">
                        <label class="wf-label">One Time Password (OTP) <span class="text-danger">*</span></label>
                        <div class="wf-input-wrapper">
                            <i class="fas fa-key wf-input-icon"></i>
                            <input type="text" class="wf-control text-center fs-4 letter-spacing-2" id="otpField" maxlength="6" placeholder="------" inputmode="numeric" pattern="[0-9]{6}" autocomplete="one-time-code">
                        </div>
                        <div class="wf-error-msg" id="otpErrorMsg"></div>
                    </div>

                    <div class="text-center mt-3">
                        <div id="otpTimerContainer">
                            <span class="text-muted">Time remaining: <span id="otpTimer" class="fw-bold text-primary">01:00</span></span>
                        </div>
                        <div id="otpAttemptsInfo" class="mt-1 small text-muted">Attempts remaining: 5</div>
                        <br>
                        <div class="d-flex justify-content-center gap-3 align-items-center mt-1">
                            <a href="javascript:void(0)" id="resendOtpBtn" class="text-decoration-none" style="pointer-events: none; opacity: 0.5;">Resend OTP</a>
                            <span class="text-muted">|</span>
                            <a href="javascript:void(0)" id="changeNumberBtn" class="text-decoration-none text-primary">Change Number</a>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: VEHICLE & FINANCIALS -->
                <div class="wf-step-panel" id="step-3">
                    <div class="wf-input-group">
                        <label class="wf-label">Vehicle Type <span class="text-danger">*</span></label>
                        <div class="wf-input-wrapper">
                            <i class="fas fa-car wf-input-icon"></i>
                            <select class="wf-control" id="vehicleType" required>
                                <option value="">Select vehicle type</option>
                                <option value="car">Car</option>
                                <option value="bike">Two Wheeler</option>
                                <option value="commercial">Commercial Vehicle</option>
                            </select>
                        </div>
                        <div class="wf-error-msg"></div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="wf-input-group">
                                <label class="wf-label">On-Road Price <span class="text-danger">*</span></label>
                                <div class="wf-input-wrapper">
                                        <input type="text" class="wf-control ps-3" id="vehiclePrice" data-currency required placeholder="8,00,000" inputmode="numeric" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="wf-input-group">
                                <label class="wf-label">Loan Required <span class="text-danger">*</span></label>
                                <div class="wf-input-wrapper">
                                        <input type="text" class="wf-control ps-3" id="loanAmount" data-currency required placeholder="6,00,000" inputmode="numeric" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="wf-input-group">
                        <label class="wf-label">Monthly Net Income <span class="text-danger">*</span></label>
                        <div class="wf-input-wrapper">
                            <i class="fas fa-wallet wf-input-icon"></i>
                                <input type="text" class="wf-control" id="income" data-currency required placeholder="40,000" inputmode="numeric" autocomplete="off">
                        </div>
                        <div class="wf-error-msg"></div>
                    </div>
                </div>

                <!-- STEP 4: REVIEW & SUBMIT -->
                <div class="wf-step-panel" id="step-4">
                    <div class="text-center mb-4">
                        <div class="mb-3" style="font-size: 48px; color: var(--wf-success);">
                            <i class="fas fa-shield-check"></i>
                        </div>
                        <h3 class="fw-bold">Final Confirmation</h3>
                        <p class="text-muted">You are almost there. Please accept the terms to complete your application.</p>
                    </div>

                    <div class="wf-input-group">
                        <label class="wf-label">Current City <span class="text-danger">*</span></label>
                        <div class="wf-input-wrapper">
                            <i class="fas fa-map-marker-alt wf-input-icon"></i>
                            <input type="text" class="wf-control" id="city" required placeholder="Enter City" autocomplete="address-level2">
                        </div>
                        <div class="wf-error-msg"></div>
                    </div>

                    <div class="wf-terms-box">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="termsConsent" required>
                            <label class="form-check-label ps-2 mt-0" for="termsConsent" style="font-size: 13px; color: var(--wf-text-muted);">
                                I confirm that the information provided is accurate and I agree to the <a href="terms-condition" class="text-primary fw-bold">Terms & Conditions</a> and <a href="privacy-policy" class="text-primary fw-bold">Privacy Policy</a> of Waqt Finance.
                            </label>
                        </div>
                        <div class="wf-error-msg" style="margin-left: 25px;"></div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="wf-form-actions">
                    <button type="button" class="wf-btn wf-btn-secondary" id="btnPrev" style="display:none;">
                        <i class="fas fa-chevron-left me-2"></i> Back
                    </button>
                    <button type="button" class="wf-btn wf-btn-primary mt-3" id="btnNext">
                        Save & Continue <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>

            </form>

            <div class="wf-security-footer">
                <div class="wf-trust-badge">
                    <i class="fas fa-shield-alt"></i>
                    <span>Data Safeguarded</span>
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


    <script>
        class VehicleLoanApplication {
            constructor() {
                this.currentStep = 1;
                this.totalSteps = 4;
                this.form = document.getElementById('applyForm');
            this.latitude = '';
            this.longitude = '';
            this.gpsAddress = '';
            this.deviceFingerprint = this.generateDeviceFingerprint();
                this.panels = document.querySelectorAll('.wf-step-panel');
                this.nodes = document.querySelectorAll('.wf-step-node');
                this.progressLine = document.querySelector('.wf-progress-line');
                this.btnPrev = document.getElementById('btnPrev');
                this.btnNext = document.getElementById('btnNext');
                this.csrfToken = window.WAQT_CSRF_TOKEN || '';
                this.timerInterval = null;
                this.otpAttempts = 0;
                this.maxAttempts = 5;

                this.init();
            }

            init() {
                if (!this.form) return;

                this.btnNext.addEventListener('click', () => this.nextStep());
                this.btnPrev.addEventListener('click', () => this.prevStep());

                this.form.querySelectorAll('input, select').forEach(input => {
                    input.addEventListener('blur', (e) => this.validateField(e.target));
                    input.addEventListener('input', (e) => {
                        if (e.target.classList.contains('is-invalid') || e.target.closest('.wf-input-group')?.classList.contains('error')) {
                            this.validateField(e.target);
                        }
                        this.handleMasking(e.target);
                    });
                });

                document.getElementById('resendOtpBtn').addEventListener('click', () => this.sendOTP());
                document.getElementById('changeNumberBtn').addEventListener('click', () => {
                    this.prevStep();
                    setTimeout(() => {
                        const phoneInput = document.getElementById('phoneField');
                        if (phoneInput) {
                            phoneInput.focus();
                            phoneInput.select();
                        }
                    }, 100);
                });

                this.updateUI();
            this.checkAndRequestLocation();

                // OTP field auto-enable next btn
                document.getElementById('otpField').addEventListener('input', (e) => {
                    if (this.currentStep === 2) {
                        this.btnNext.disabled = e.target.value.length !== 6;
                    }
                });
                document.getElementById('otpField').addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        if (this.currentStep === 2 && e.target.value.length === 6) {
                            e.preventDefault();
                            this.nextStep();
                        }
                    }
                });
            }

            handleMasking(field) {
                if (field.id === 'panField') {
                    field.value = field.value.toUpperCase().substring(0, 10);
                }
                if (field.id === 'phoneField') {
                    field.value = field.value.replace(/\D/g, '').substring(0, 10);
                }
                if (field.id === 'otpField') {
                    field.value = field.value.replace(/\D/g, '').substring(0, 6);
                }
                if (field.id === 'vehiclePrice' || field.id === 'loanAmount' || field.id === 'income') {
                    let val = field.value.replace(/,/g, '').replace(/\D/g, '').substring(0, 9);
                    field.value = val ? Number(val).toLocaleString('en-IN') : "";
                }
            }

            validateField(field) {
                if (!field.closest) return true; // Safety
                const group = field.closest('.wf-input-group') || field.closest('.form-check');
                if (!group) return true;

                let isValid = true;
                let errorMsg = "";

                if (field.hasAttribute('required')) {
                    if (field.type === 'checkbox') {
                        if (!field.checked) {
                            isValid = false;
                            errorMsg = "You must agree to the terms";
                        }
                    } else if (!field.value.trim()) {
                        isValid = false;
                        errorMsg = "This field is required";
                    }
                }

                if (isValid && field.value.trim()) {
                    if (field.id === 'panField' && !/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/.test(field.value.trim())) {
                        isValid = false;
                        errorMsg = "Invalid PAN format (e.g. ABCDE1234F)";
                    } else if (field.id === 'phoneField' && !/^[6-9]\d{9}$/.test(field.value.trim())) {
                        isValid = false;
                        errorMsg = "Invalid 10-digit mobile number";
                    } else if (field.id === 'emailField' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value.trim())) {
                        isValid = false;
                        errorMsg = "Invalid email address format";
                    } else if (field.id === 'otpField' && this.currentStep === 2 && field.value.trim().length !== 6) {
                        isValid = false;
                        errorMsg = "Please enter 6-digit OTP";
                    }
                }


                const errorLabel = group.querySelector('.wf-error-msg') || document.getElementById('otpErrorMsg');
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

                fields.forEach(field => {
                    if (!this.validateField(field)) {
                        stepValid = false;
                    }
                });

                if (!stepValid) {
                    this.form.classList.add('shake');
                    setTimeout(() => this.form.classList.remove('shake'), 400);
                }

                return stepValid;
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

                        // Auto-fill city input
                        const addr = data.address || {};
                        const city = addr.city || addr.town || addr.village || addr.suburb || '';
                        const cityInput = document.getElementById('city');

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

        nextStep() {
                if (!this.validateCurrentStep()) return;

                if (this.currentStep === 1) {
                    this.initOTP();
                } else if (this.currentStep === 2) {
                    this.verifyOTPAndSubmit();
                } else if (this.currentStep === 4) {
                    this.submitFinalApplication();
                } else if (this.currentStep < this.totalSteps) {
                    this.currentStep++;
                    this.updateUI();
            this.checkAndRequestLocation();
                }
            }

            prevStep() {
                if (this.currentStep > 1) {
                    this.currentStep--;
                    this.updateUI();
            this.checkAndRequestLocation();
                }
            }

            updateUI() {
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

                const sidebarCountEl = document.getElementById('sidebarStepCount');
                if (sidebarCountEl) sidebarCountEl.innerText = `Step ${this.currentStep} of ${this.totalSteps}`;
                const sidebarProgressEl = document.getElementById('sidebarProgressFill');
                if (sidebarProgressEl) sidebarProgressEl.style.width = `${(this.currentStep / this.totalSteps) * 100}%`;

                const mobileCountEl = document.getElementById('mobileStepCount');
                if (mobileCountEl) mobileCountEl.innerText = `Step ${this.currentStep} of ${this.totalSteps}`;
                const mobileTitleEl = document.getElementById('mobileStepTitle');
                if (mobileTitleEl) {
                    const titles = ["Applicant", "OTP Verification", "Vehicle Info", "Verification"];
                    mobileTitleEl.innerText = titles[this.currentStep - 1] || '';
                }
                const mobileProgressEl = document.getElementById('mobileProgressFill');
                if (mobileProgressEl) mobileProgressEl.style.width = `${(this.currentStep / this.totalSteps) * 100}%`;

                this.btnPrev.style.display = this.currentStep === 1 ? 'none' : 'flex';

                if (this.currentStep === 1) {
                    this.btnNext.innerHTML = 'Send OTP <i class="fas fa-paper-plane ms-2"></i>';
                } else if (this.currentStep === 2) {
                    this.btnNext.innerHTML = 'Verify OTP <i class="fas fa-check-circle ms-2"></i>';
                } else if (this.currentStep === 4) {
                    this.btnNext.innerHTML = 'Submit Application <i class="fas fa-check-circle ms-2"></i>';
                } else {
                    this.btnNext.innerHTML = 'Save & Continue <i class="fas fa-arrow-right ms-2"></i>';
                }

                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }

            renderPreview(targetId = 'dataPreview') {
                const previewContainer = document.getElementById(targetId);
                if (!previewContainer) return;

                const data = {
                    applicant: {
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
                                label: "PAN Number",
                                value: document.getElementById('panField').value
                            }
                        ]
                    },
                    vehicle: {
                        title: "Vehicle & Loan Details",
                        icon: "fa-car",
                        items: [{
                                label: "Vehicle Type",
                                value: document.getElementById('vehicleType').value.charAt(0).toUpperCase() + document.getElementById('vehicleType').value.slice(1)
                            },
                            {
                                label: "On-Road Price",
                                value: '₹' + Number(document.getElementById('vehiclePrice').value.replace(/,/g, '')).toLocaleString('en-IN'),
                                isAmount: true
                            },
                            {
                                label: "Loan Amount",
                                value: '₹' + Number(document.getElementById('loanAmount').value.replace(/,/g, '')).toLocaleString('en-IN'),
                                isAmount: true
                            },
                            {
                                label: "City",
                                value: document.getElementById('city').value
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


            initOTP() {
                this.btnNext.disabled = true;
                this.btnNext.innerHTML = 'Processing... <i class="fas fa-spinner fa-spin ms-2"></i>';
                this.otpAttempts = 0; // Reset attempts on fresh init

                const fd = new FormData();
                fd.append('action', 'init_otp');
                fd.append('mobile', document.getElementById('phoneField').value);
                fd.append('fullName', document.getElementById('fullName').value);

            const initOtpUrl = window.location.pathname.replace(/\.php$/i, '') || 'vehicle-loan-apply';
    fetch(initOtpUrl
    , {
                        method: 'POST',
                        body: fd
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            if (data.csrf_token) this.csrfToken = data.csrf_token;
                            this.currentStep = 2;
                            this.updateUI();
            this.checkAndRequestLocation();
                            this.sendOTP();
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

            sendOTP() {
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
                    document.getElementById('otpField').closest('.wf-input-group').classList.remove('error');
                }
                this.btnNext.disabled = true;

                const fd = new FormData();
                fd.append('mobile', document.getElementById('phoneField').value);

                fetch('api/apply/send_mobile_otp.php', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-Token': this.csrfToken
                        },
                        body: fd
                    })
                    .then(res => res.json())
                    .then(data => {
                        resendBtn.innerHTML = 'Resend OTP';
                        if (data.status === 'success') {
                            this.startTimer(data.retry_after || 60);
                        } else {
                            otpErrorMsg.innerText = data.message || "Failed to send OTP";
                            resendBtn.style.pointerEvents = 'auto';
                            resendBtn.style.opacity = '1';
                        }
                    })
                    .catch(err => {
                        resendBtn.innerHTML = 'Resend OTP';
                        console.log("OTP Send Error:", err);
                        resendBtn.style.pointerEvents = 'auto';
                        resendBtn.style.opacity = '1';
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

            verifyOTPAndSubmit() {
                const otpField = document.getElementById('otpField');
                const otpVal = otpField.value;
                const otpErrorMsg = document.getElementById('otpErrorMsg');
                const attemptsInfo = document.getElementById('otpAttemptsInfo');

                if (otpVal.length !== 6) {
                    otpErrorMsg.innerText = "Please enter 6-digit OTP";
                    return;
                }

                this.btnNext.disabled = true;
                this.btnNext.innerHTML = 'Verifying... <i class="fas fa-spinner fa-spin ms-2"></i>';

                const fd = new FormData();
                fd.append('otp', otpVal);

                fetch('api/apply/verify_mobile_otp.php', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-Token': this.csrfToken
                        },
                        body: fd
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            this.currentStep = 3;
                            this.updateUI();
            this.checkAndRequestLocation();
                            this.btnNext.disabled = false;
                        } else {
                            this.otpAttempts++;
                            const remaining = this.maxAttempts - this.otpAttempts;
                            
                            if (attemptsInfo) attemptsInfo.innerText = `Attempts remaining: ${remaining > 0 ? remaining : 0}`;
                            
                            if (this.otpAttempts >= this.maxAttempts || data.message.includes('Too many attempts')) {
                                this.btnNext.disabled = true;
                                throw new Error("Too many incorrect attempts. Please resend OTP or try a different number.");
                            }
                            
                            throw new Error(data.message || 'Invalid OTP');
                        }
                    })
                    .catch(err => {
                        this.btnNext.innerHTML = 'Verify OTP <i class="fas fa-check-circle ms-2"></i>';
                        otpErrorMsg.innerText = err.message;
                        otpField.closest('.wf-input-group').classList.add('error');
                        
                        if (this.otpAttempts < this.maxAttempts) {
                            this.btnNext.disabled = false;
                        }
                    });
            }

            submitFinalApplication() {
                this.btnNext.disabled = true;
                this.btnNext.innerHTML = 'Submitting... <i class="fas fa-spinner fa-spin ms-2"></i>';

                const fd = new FormData();
                fd.append('loan_type', 'vehicle');
                fd.append('fullName', document.getElementById('fullName').value);
                fd.append('phoneField', document.getElementById('phoneField').value);
                fd.append('panField', document.getElementById('panField').value);
                fd.append('emailField', document.getElementById('emailField').value);
                fd.append('vehicleType', document.getElementById('vehicleType').value);
                fd.append('vehiclePrice', document.getElementById('vehiclePrice').value.replace(/,/g, ''));
                fd.append('loanAmount', document.getElementById('loanAmount').value.replace(/,/g, ''));
                fd.append('income', document.getElementById('income').value.replace(/,/g, ''));
                fd.append('city', document.getElementById('city').value);
            fd.append('latitude', this.latitude || '');
            fd.append('longitude', this.longitude || '');
            fd.append('gpsAddress', this.gpsAddress || '');
            fd.append('deviceFingerprint', this.deviceFingerprint || '');


                fetch('api/apply/save_common_loan.php', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-Token': this.csrfToken
                        },
                        body: fd
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            this.form.style.opacity = '0';
                            this.form.style.transform = 'translateY(-20px)';
                            setTimeout(() => {
                                // Hide card header and progress bar for a clean success look
                                const cardHeader = document.querySelector('.wf-card-header');
                                const progressContainer = document.querySelector('.wf-progress-container');
                                if (cardHeader) cardHeader.style.display = 'none';
                                if (progressContainer) progressContainer.style.display = 'none';

                                this.form.style.display = 'none';
                                const successContainer = document.createElement('div');
                                successContainer.className = 'wf-success-full-screen';
                                successContainer.innerHTML = `
                                    <div class="success-icon-section">
                                        <div class="check-circle">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    </div>
                                    <h2 class="success-title">Application Submitted!</h2>
                                    <p class="success-subtitle">We've received your Vehicle loan application. Our team will review the details and get back to you shortly.</p>
                                    <div style="font-size: 15.5px; font-weight: 700; color: #a5b4fc; background: rgba(255,255,255,0.05); padding: 8px 16px; border-radius: 8px; margin: 15px auto; display: table;">Reference ID: ${data.application_id}</div>
                                    
                                    <div class="summary-card-mini">
                                        <div class="summary-header">Application Details</div>
                                        <div id="finalSummary"></div>
                                    </div>

                                    <div class="success-actions">
                                        <a href="tel:9217086610" class="btn-modern-primary">
                                            <span>Any enquiry: <i class="fas fa-phone ms-1"></i> 9217086610</span>
                                        </a>
                                    </div>
                                `;
                                this.form.parentNode.insertBefore(successContainer, this.form.nextSibling);
                                this.renderPreview('finalSummary');

                                this.btnPrev.style.display = 'none';
                                this.btnNext.style.display = 'none';
                            }, 400);
                        } else {
                            throw new Error(data.message || 'Submission failed');
                        }
                    })
                    .catch(err => {
                        this.btnNext.disabled = false;
                        this.btnNext.innerHTML = 'Submit Application <i class="fas fa-check-circle ms-2"></i>';
                        alert(err.message);
                    });
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            new VehicleLoanApplication();
        });
    </script>

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

<?php include 'includes/footer.php' ?>
