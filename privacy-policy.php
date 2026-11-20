<?php
$page_title = "Privacy Policy | Data Protection & Information Security";
$meta_description = "Understand how Waqt Finance collects, uses, stores, and protects your personal information while ensuring privacy, security, and regulatory compliance.";
$meta_keywords = "privacy policy, data protection, personal information safety, waqt finance privacy";
$canonical_url = "https://waqtfinance.com/privacy-policy";

include 'includes/header.php';
include 'includes/main-header.php';
?>

<style>
    /* Policy Page Hero section */
    .wf-policy-hero {
        background: radial-gradient(circle at 50% 50%, #062b5d 0%, #031530 100%);
        color: #fff;
        padding: 80px 0 70px;
        text-align: center;
    }
    .wf-policy-hero-content {
        max-width: 800px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .wf-policy-badge {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #fff;
        padding: 6px 14px;
        border-radius: 99px;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .wf-policy-hero h1 {
        color: #fff;
        font-size: clamp(32px, 4.5vw, 48px);
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 16px;
    }
    .wf-policy-hero p {
        color: rgba(255, 255, 255, 0.9);
        font-size: 16.5px;
        line-height: 1.7;
        margin: 0;
    }

    /* Policy Navigation Tabs */
    .wf-policy-nav-wrap {
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
        padding: 12px 0;
        position: sticky;
        top: 90px !important;
        z-index: 100;
    }
    .wf-policy-nav-tabs {
        display: flex;
        gap: 8px;
        overflow-x: auto !important;
        flex-wrap: nowrap !important;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        padding: 4px 16px;
    }
    .wf-policy-nav-tabs::-webkit-scrollbar {
        display: none;
    }
    @media (min-width: 1200px) {
        .wf-policy-nav-tabs {
            justify-content: center;
        }
    }
    @media (max-width: 1199px) {
        .wf-policy-nav-tabs {
            justify-content: flex-start;
        }
    }
    .wf-policy-tab-btn {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569 !important;
        padding: 8px 16px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none !important;
        white-space: nowrap !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .wf-policy-tab-btn:hover {
        background: #f1f5f9;
        color: #18529c !important;
        border-color: #18529c;
    }
    .wf-policy-tab-btn.active {
        background: linear-gradient(135deg, #062b5d 0%, #18529c 100%);
        color: #fff !important;
        border-color: #062b5d;
        box-shadow: 0 4px 12px rgba(6, 43, 93, 0.2);
    }
    
    /* Policy Document Container */
    .wf-policy-document-section {
        padding: 60px 0;
        background: #f8fafc;
    }
    .wf-policy-paper {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 45px;
        box-shadow: 0 4px 25px rgba(15, 23, 42, 0.02);
        max-width: 960px;
        margin: 0 auto;
        text-align: left;
    }
    .wf-policy-paper h2 {
        color: #062b5d;
        font-size: 24px;
        font-weight: 800;
        margin-bottom: 24px;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 12px;
        letter-spacing: -0.5px;
    }
    .wf-policy-paper h3 {
        color: #0f172a;
        font-size: 18px;
        font-weight: 700;
        margin-top: 32px;
        margin-bottom: 16px;
    }
    .wf-policy-paper h4 {
        color: #0f172a;
        font-size: 16px;
        font-weight: 700;
        margin-top: 24px;
        margin-bottom: 12px;
    }
    .wf-policy-paper p {
        color: #475569;
        font-size: 15px;
        line-height: 1.8;
        margin-bottom: 18px;
    }

    .wf-policy-paper ul {
        list-style: none !important;
        list-style-type: none !important;
        padding: 0 !important;
        padding-left: 0 !important;
        margin: 0 0 24px 0 !important;
    }
    .wf-policy-paper ul li {
        position: relative;
        padding-left: 24px;
        color: #475569;
        font-size: 15px;
        line-height: 1.7;
        margin-bottom: 12px;
    }
    .wf-policy-paper ul li::before {
        content: "\f00c";
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        color: #22c55e;
        position: absolute;
        left: 0;
        top: 2px;
        font-size: 13px;
    }
    
    @media (max-width: 768px) {
        .wf-policy-paper {
            padding: 24px;
        }
        .wf-policy-nav-tabs {
            flex-direction: column;
            width: 100%;
            padding: 0 16px;
        }
        .wf-policy-tab-btn {
            text-align: center;
        }
    }
    
    /* Hero Glass Card for premium glassmorphism overlay */
    .hero-glass-card {
        background: rgba(6, 43, 93, 0.3);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.12);
        padding: 45px;
        border-radius: 24px;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.4);
    }
    @media (max-width: 576px) {
        .hero-glass-card {
            padding: 25px 20px;
            border-radius: 16px;
        }
    }

/* Policy tabs: keep every tab reachable (safe centering) and use a scrolling row on small screens */
.wf-policy-nav-tabs { justify-content: flex-start !important; flex-direction: row !important; width: auto !important; padding-left: 2px !important; padding-right: 2px !important; }
.wf-policy-nav-tabs > :first-child { margin-left: auto; }
.wf-policy-nav-tabs > :last-child { margin-right: auto; }
.wf-policy-tab-btn { flex-shrink: 0; }
@media (min-width: 1200px) {
    .wf-policy-nav-tabs { gap: 6px !important; }
    .wf-policy-tab-btn { padding: 8px 13px !important; }
}
</style>

    <section class="wf-policy-hero">
        <div class="container">
            <div class="wf-policy-hero-content">
                <span class="wf-policy-badge"><i class="fa-solid fa-user-shield"></i> Protecting Your Privacy</span>
                <h1>Privacy Policy</h1>
                <p>We collect only the data we need to provide your loan, we never sell it, and we share it only as this policy explains.</p>
                <div class="pd-hero-meta"><span><i class="far fa-calendar" aria-hidden="true"></i> Last updated: 19 November 2026</span><span data-pd-reading hidden></span><button type="button" class="pd-print" data-pd-print><i class="fas fa-print" aria-hidden="true"></i> Print or save PDF</button></div>
            </div>
        </div>
    </section>

    <!-- Policy Navigation Tabs -->
    <div class="wf-policy-nav-wrap">
        <div class="container">
            <div class="wf-policy-nav-tabs">
                <a href="fair-practice-code" class="wf-policy-tab-btn">Fair Practices Code</a>
                <a href="grievance-redressal-policy" class="wf-policy-tab-btn">Grievance Redressal Policy</a>
                <a href="interest-rate-policy" class="wf-policy-tab-btn">Interest Rate Policy</a>
                <a href="anti-money-laundering" class="wf-policy-tab-btn">Anti Money Laundering Policy</a>
                <a href="privacy-policy" class="wf-policy-tab-btn active">Privacy Policy</a>
                <a href="terms-condition" class="wf-policy-tab-btn">Terms & Conditions</a>
                <a href="refund-cancellation" class="wf-policy-tab-btn">Refund Policy</a>
                <a href="digital-lending" class="wf-policy-tab-btn">Digital Lending</a>
            </div>
        </div>
    </div>

    <!-- Policy Document Content -->
<!-- Plain-language summary -->
<section class="pd-glance-section" aria-labelledby="pd-glance-title">
    <div class="container">
        <div class="pd-glance">
            <div class="pd-glance-head">
                <div><span class="pd-eyebrow">At a glance</span><h2 id="pd-glance-title">The key points</h2></div>
                <p>A plain-language summary. The full policy below is the binding text.</p>
            </div>
            <ul class="pd-glance-list">
                <li><span class="pd-glance-icon"><i class="fas fa-ban" aria-hidden="true"></i></span><div><strong>We never sell your data</strong><span>We use your data only to provide and service your loan, and share it only as this policy explains.</span></div></li>
                <li><span class="pd-glance-icon"><i class="fas fa-mobile-screen" aria-hidden="true"></i></span><div><strong>No access to contacts, SMS or photos</strong><span>The app never reads your contacts, call logs, SMS, photos or files. It uses the camera and microphone only for KYC.</span></div></li>
                <li><span class="pd-glance-icon"><i class="fas fa-server" aria-hidden="true"></i></span><div><strong>Stored in India</strong><span>Your personal data is stored on servers in India, as RBI requires.</span></div></li>
                <li><span class="pd-glance-icon"><i class="fas fa-user-gear" aria-hidden="true"></i></span><div><strong>You stay in control</strong><span>Ask us to access, correct or delete your data, or withdraw consent, by writing to <a href="mailto:customercare@waqtfinance.com">customercare@waqtfinance.com</a>.</span></div></li>
            </ul>
        </div>
    </div>
</section>

    <section class="wf-policy-document-section" id="policy">
        <div class="container">
            <div class="wf-policy-paper">
                <h2>Privacy Policy</h2>
                <p><strong>Last updated:</strong> 19 November 2026</p>
                <p>
                    This Privacy Policy explains how <strong>WAQT FINANCE PRIVATE LIMITED</strong> (CIN: U65923DL2022PTC396001), a Non-Banking Financial Company registered with the Reserve Bank of India (Registration No. B.10.00143) (the "Company", "we", "our" or "us"), collects, uses, shares, stores and protects your personal data when you use our website <a href="https://waqtfinance.com/">https://waqtfinance.com/</a> and our mobile application "WAQT Finance" (package name <strong>com.waqtfinance.customer</strong>) (together, the "Platform").
                </p>
                <p>
                    We process personal data in accordance with the Digital Personal Data Protection Act, 2023, the Information Technology Act, 2000 and the rules made under it, and the directions of the Reserve Bank of India, including the Reserve Bank of India (Digital Lending) Directions, 2025. By using the Platform you agree to the processing described in this policy. Where the law requires your consent, we ask for it separately and you may withdraw it at any time as described in Section 9.
                </p>

                <h3>1. Definitions</h3>
                <ul>
                    <li>"You", "your" and "User" mean any person who visits the Platform, applies for a loan or holds a loan with the Company.</li>
                    <li>"Personal Data" means any data about you that identifies you or can be used to identify you.</li>
                    <li>"Third Party" means any person other than you and the Company, including the service providers listed in Section 5.</li>
                </ul>

                <h3>2. Personal Data We Collect</h3>
                <p>We collect only the data needed to assess, sanction, disburse, service and recover your loan and to comply with law. Depending on the product you apply for, this includes:</p>
                <ul>
                    <li><strong>Identity and contact details:</strong> name, mobile number, email address, date of birth, gender, current and permanent address, pincode and photograph.</li>
                    <li><strong>KYC details:</strong> PAN, Aadhaar (masked or as permitted by law, through Aadhaar e-KYC, offline verification or DigiLocker with your consent), selfie and liveness check, and video KYC recording where applicable.</li>
                    <li><strong>Employment and income details:</strong> employer or business details, salary slips, income and occupation.</li>
                    <li><strong>Financial details:</strong> bank account number and IFSC, bank statements shared by you or fetched through the Account Aggregator framework with your consent, and credit information reports obtained from credit information companies with your consent.</li>
                    <li><strong>Loan and repayment details:</strong> application details, loan agreement, Key Fact Statement, e-sign records, e-NACH / mandate details, repayment and payment transaction records.</li>
                    <li><strong>Device and usage data:</strong> device model, operating system, app version, an app installation identifier, IP address and technical logs used for security, fraud prevention and to fix errors.</li>
                    <li><strong>Communication records:</strong> your messages, complaints, and recordings of calls with our customer care, where you are informed of the recording.</li>
                </ul>

                <h3>3. Device Permissions and Data We Do Not Access</h3>
                <p>The mobile app asks for a device permission only when you start the feature that needs it, and only after telling you why:</p>
                <ul>
                    <li><strong>Camera:</strong> to capture a selfie, a document photo, or for video KYC.</li>
                    <li><strong>Microphone:</strong> only during video KYC.</li>
                    <li><strong>Biometric (fingerprint / face unlock):</strong> to unlock the app. Your biometric data stays on your device and is never sent to us.</li>
                    <li><strong>Notifications:</strong> to send loan, payment and security alerts. You can turn these off in your device settings.</li>
                </ul>
                <p>In line with RBI's Digital Lending Directions, <strong>we do not access your contact list, call logs, SMS, photos, media files or other files</strong> on your device. The mobile app does not access your device location.</p>
                <p>When you apply on our website, your browser may ask once for permission to share your approximate location so that we can confirm that we serve your area. This happens only with your permission and is not repeated in the background. To convert your location into a city and pincode, the coordinates are sent to the OpenStreetMap Nominatim service.</p>

                <h3>4. How We Use Your Personal Data</h3>
                <ul>
                    <li>To verify your identity and complete KYC as required by RBI's KYC Directions and the Prevention of Money Laundering Act, 2002.</li>
                    <li>To assess your eligibility and creditworthiness, and to sanction, disburse and service your loan.</li>
                    <li>To collect repayments, including through e-NACH mandates and payment gateways, and to issue receipts, statements and No Objection Certificates.</li>
                    <li>To report your loan and repayment information to credit information companies, as required by law.</li>
                    <li>To send you service messages about your application, loan, dues and account security.</li>
                    <li>To prevent and detect fraud, protect the security of the Platform and investigate misuse.</li>
                    <li>To handle your requests and complaints.</li>
                    <li>To comply with legal and regulatory obligations and with lawful requests from authorities.</li>
                    <li>To send you marketing messages, only where you have agreed to receive them. You may opt out at any time.</li>
                </ul>
                <p>We do not use your personal data for any purpose that is not stated here without first telling you and, where required, taking your consent.</p>

                <h3>5. Sharing Your Personal Data</h3>
                <p><strong>We do not sell or rent your personal data.</strong> We share it only to the extent needed for the purposes in Section 4, and only with:</p>
                <ul>
                    <li><strong>Credit information companies</strong> (such as CRIF High Mark), to obtain your credit report with your consent and to report your loan as required by law.</li>
                    <li><strong>Account Aggregator ecosystem partners</strong> (such as Setu and Digitap), to fetch your bank statements only after you approve the consent request.</li>
                    <li><strong>KYC and verification service providers</strong>, for PAN, Aadhaar / DigiLocker, bank account, selfie, liveness and video KYC verification.</li>
                    <li><strong>Payment and mandate service providers</strong> (such as Cashfree Payments), banks and NPCI, to disburse your loan and collect repayments.</li>
                    <li><strong>E-sign service providers</strong>, to execute loan documents electronically.</li>
                    <li><strong>Technology providers</strong>, such as cloud hosting, SMS, email and notification services, which process data only on our instructions.</li>
                    <li><strong>Regulators, courts and law enforcement agencies</strong> (such as RBI, FIU-IND and the police), where required by law.</li>
                    <li><strong>Any entity that acquires or merges with the Company</strong>, after informing you, and only on terms that protect your data at least as well as this policy.</li>
                </ul>
                <p>Every service provider we use is bound by a contract to keep your data confidential, to use it only for the service it provides to us, and to protect it with reasonable security practices. We do not let any service provider store your personal data longer than needed for that service.</p>

                <h3>6. Where Your Data Is Stored</h3>
                <p>Your personal data is stored on servers located in India, as required by RBI. If any data is processed outside India for a specific service, we will ensure that it is brought back to and stored in India within the time allowed by RBI, and that it is protected by law and contract.</p>

                <h3>7. How Long We Keep Your Data</h3>
                <ul>
                    <li>If your loan application is not approved or you withdraw it, we keep your data only for as long as needed to comply with law, prevent fraud and resolve disputes.</li>
                    <li>If you take a loan, we keep your data while the loan is active and for the period required after its closure by the Prevention of Money Laundering Act, 2002, RBI directions and other laws (generally at least five years after the business relationship ends).</li>
                    <li>When the retention period ends, we delete or anonymise your data.</li>
                </ul>

                <h3>8. Security</h3>
                <p>We protect your data with reasonable security practices and procedures, including encryption of data in transit (HTTPS/TLS), access controls limited to authorised staff who need the data for their work, secure storage of passwords and tokens, and monitoring for misuse. If a personal data breach occurs that affects you, we will inform you and the authorities as required by law.</p>
                <p>Please do not share your OTP, PIN or password with anyone, including people who claim to be from WAQT Finance. Our staff will never ask for your OTP or password.</p>

                <h3>9. Your Rights</h3>
                <p>Subject to applicable law, you have the right to:</p>
                <ul>
                    <li><strong>Access:</strong> ask for a summary of the personal data we hold about you and how we use it.</li>
                    <li><strong>Correction and update:</strong> ask us to correct inaccurate or incomplete data.</li>
                    <li><strong>Withdraw consent:</strong> withdraw any consent you have given, for example for marketing messages or for a specific data use. Withdrawal does not affect processing done before it, and some services may stop if they depend on that consent.</li>
                    <li><strong>Erasure:</strong> ask us to delete your data that we no longer need. We will delete it except where we must keep it by law or to service an active loan. See our <a href="delete-account">Account &amp; Data Deletion</a> page.</li>
                    <li><strong>Nomination:</strong> nominate a person to exercise these rights on your behalf in the event of your death or incapacity.</li>
                    <li><strong>Grievance redressal:</strong> raise a complaint with our Grievance Officer, and if you are not satisfied, with the Data Protection Board of India.</li>
                </ul>
                <p>To exercise any right, email <a href="mailto:customercare@waqtfinance.com">customercare@waqtfinance.com</a> from your registered email address, or use <strong>Profile &rarr; Privacy and consent</strong> in the mobile app. We will respond within 30 days.</p>
                <p>Deleting the app or closing your account does not cancel any amount you still owe under your loan agreement.</p>

                <h3>10. Cookies and Analytics</h3>
                <p>Our website uses cookies and similar technologies to keep you signed in, remember your preferences, keep the website secure and understand how visitors use it. We use Google Analytics and Google Tag Manager for website analytics, and Google may use cookies to show you ads based on your visits. You can block or delete cookies in your browser settings, opt out of Google personalised ads at <a href="https://adssettings.google.com/" target="_blank" rel="noopener noreferrer">Google Ads Settings</a>, and install the <a href="https://tools.google.com/dlpage/gaoptout" target="_blank" rel="noopener noreferrer">Google Analytics Opt-out Browser Add-on</a>. Some features of the website may not work if cookies are blocked.</p>

                <h3>11. Links to Other Websites</h3>
                <p>The Platform may link to websites and services run by others. We are not responsible for their privacy practices, and we recommend that you read their privacy policies before sharing any data with them.</p>

                <h3>12. Children</h3>
                <p>Our services are only for persons who are 18 years of age or older. We do not knowingly collect personal data of children. If you believe that a child has shared personal data with us, please contact us and we will delete it.</p>

                <h3>13. Grievance Officer</h3>
                <p>If you have any question or complaint about this policy or how we handle your personal data, please contact our Grievance Officer:</p>
                <ul>
                    <li><strong>Name:</strong> Sanjay Kumar</li>
                    <li><strong>Email:</strong> <a href="mailto:customercare@waqtfinance.com">customercare@waqtfinance.com</a></li>
                    <li><strong>Address:</strong> WAQT Finance Private Limited, 15K-5 Jyoti Nagar Housing Board Colony, Sahakar Marg, Jaipur, Rajasthan 302005</li>
                    <li><strong>Hours:</strong> Monday to Saturday, 10:00 AM to 6:00 PM, except public holidays</li>
                </ul>
                <p>We will acknowledge your complaint promptly and resolve it within 30 days. For complaints about your loan, see our <a href="grievance-redressal-policy">Grievance Redressal Policy</a>.</p>

                <h3>14. Changes to This Policy</h3>
                <p>We may update this policy from time to time. We will post the updated policy on this page with a new "Last updated" date and, where the changes are significant, inform you through the app, SMS or email before they take effect.</p>
            </div>
        </div>
    </section>

<?php include 'includes/policy-help.php'; ?>
<?php include 'includes/footer.php' ?>