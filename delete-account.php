<?php
$page_title = "Delete Account & Data | WAQT Finance";
$meta_description = "How to request deletion of your WAQT Finance app account and personal data, what we delete, and what we must keep by law.";
$meta_keywords = "delete account, data deletion, waqt finance account deletion";
$canonical_url = "https://waqtfinance.com/delete-account";

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
                <span class="wf-policy-badge"><i class="fa-solid fa-user-xmark"></i> Your Data, Your Choice</span>
                <h1>Account &amp; Data Deletion</h1>
                <p>Ask us to delete your WAQT Finance account and personal data. Here is how, what we delete, and what the law requires us to keep.</p>
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
                <a href="privacy-policy" class="wf-policy-tab-btn">Privacy Policy</a>
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
                <li><span class="pd-glance-icon"><i class="fas fa-paper-plane" aria-hidden="true"></i></span><div><strong>Ask in the app, by email or by phone</strong><span>Use Profile &rarr; Privacy and consent in the app, email <a href="mailto:customercare@waqtfinance.com">customercare@waqtfinance.com</a>, or call 95602 60240.</span></div></li>
                <li><span class="pd-glance-icon"><i class="fas fa-calendar-check" aria-hidden="true"></i></span><div><strong>Done within 30 days</strong><span>We acknowledge your request within 2 working days and complete it within 30 days.</span></div></li>
                <li><span class="pd-glance-icon"><i class="fas fa-box-archive" aria-hidden="true"></i></span><div><strong>Some records are kept by law</strong><span>KYC and loan records are kept for at least five years after your loan closes, as the law requires.</span></div></li>
                <li><span class="pd-glance-icon"><i class="fas fa-triangle-exclamation" aria-hidden="true"></i></span><div><strong>Loan dues are not cancelled</strong><span>Deleting your account does not cancel any amount you still owe.</span></div></li>
            </ul>
        </div>
    </div>
</section>

    <section class="wf-policy-document-section" id="policy">
        <div class="container">
            <div class="wf-policy-paper">
                <h2>Account &amp; Data Deletion</h2>
                <p><strong>Last updated:</strong> 19 November 2026</p>
                <p>This page explains how you can ask <strong>WAQT FINANCE PRIVATE LIMITED</strong> to delete your account and personal data for the <strong>WAQT Finance</strong> mobile app (package name <strong>com.waqtfinance.customer</strong>) and the website <a href="https://waqtfinance.com/">waqtfinance.com</a>. You do not need to keep the app installed to make a request.</p>

                <h3>1. How to Request Deletion</h3>
                <p>Use any one of these options:</p>
                <ul>
                    <li><strong>In the app:</strong> open the WAQT Finance app, go to <strong>Profile &rarr; Privacy and consent</strong>, and tap <strong>Request account/data deletion</strong>. This opens a pre-filled email to our customer care.</li>
                    <li><strong>By email:</strong> write to <a href="mailto:customercare@waqtfinance.com?subject=Account%20and%20personal%20data%20deletion%20request">customercare@waqtfinance.com</a> from your registered email address with the subject "Account and personal data deletion request". Please include your full name and registered mobile number. Do not send your OTP, password or full Aadhaar number.</li>
                    <li><strong>By phone:</strong> call our customer care on 9560260240, Monday to Saturday, 10:00 AM to 6:00 PM.</li>
                </ul>
                <p>To protect your account, we will confirm your identity, usually with an OTP sent to your registered mobile number, before we act on the request.</p>

                <h3>2. What Happens Next</h3>
                <ul>
                    <li>We will acknowledge your request within 2 working days.</li>
                    <li>We will complete the request within 30 days and confirm it to you by email or SMS.</li>
                    <li>Once your account is deleted, you will be signed out of the app on all devices and will not be able to sign in again with that account.</li>
                </ul>

                <h3>3. Data We Delete</h3>
                <ul>
                    <li>Your app account, sign-in sessions and device registrations.</li>
                    <li>Notification settings and marketing preferences.</li>
                    <li>Incomplete loan applications and documents you uploaded for them, unless we must keep them by law.</li>
                    <li>Any other personal data that we no longer need for a legal or regulatory purpose.</li>
                </ul>

                <h3>4. Data We Must Keep</h3>
                <p>As a lender regulated by the Reserve Bank of India, we are required by law to keep some records even after you ask us to delete your account:</p>
                <ul>
                    <li><strong>KYC records, loan agreements, transaction and repayment records:</strong> kept for at least five years after your loan is closed or the business relationship ends, as required by the Prevention of Money Laundering Act, 2002 and RBI directions.</li>
                    <li><strong>Credit information:</strong> your loan history reported to credit information companies is kept by them as required by the Credit Information Companies (Regulation) Act, 2005.</li>
                    <li><strong>Records needed for disputes, fraud prevention or legal proceedings:</strong> kept until the matter is closed.</li>
                </ul>
                <p>We use the data we keep only for these legal purposes. When the retention period ends, we delete or anonymise it.</p>

                <h3>5. If You Have an Active Loan</h3>
                <p>If you have an outstanding loan, we can delete data that is not needed to service it, such as marketing preferences, but we cannot close your loan account until the loan is fully repaid. Deleting the app or your account does not cancel any amount you owe under your loan agreement. You can still view your loan details and make repayments by contacting customer care.</p>

                <h3>6. Questions or Complaints</h3>
                <p>For more details, read our <a href="privacy-policy">Privacy Policy</a>. If you are not satisfied with how we handled your request, you can contact our Grievance Officer as described in our <a href="grievance-redressal-policy">Grievance Redressal Policy</a>.</p>
            </div>
        </div>
    </section>

<?php include 'includes/policy-help.php'; ?>
<?php include 'includes/footer.php' ?>