<?php
$page_title = "Digital Lending Disclosures | WAQT Finance";
$meta_description = "RBI Digital Lending disclosures of WAQT Finance: lender details, official apps, Key Fact Statement, cooling-off period, data practices and grievance officer.";
$meta_keywords = "digital lending disclosures, rbi digital lending, waqt finance lender details";
$canonical_url = "https://waqtfinance.com/digital-lending";

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
    
    .wf-policy-paper table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        background: #fff;
    }
    .wf-policy-paper th {
        background: #f1f5f9;
        color: #0f172a;
        font-weight: 700;
        padding: 12px 16px;
        border: 1px solid #e2e8f0;
        font-size: 14px;
        text-align: left;
    }
    .wf-policy-paper td {
        padding: 12px 16px;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 14px;
        line-height: 1.5;
        text-align: left;
        overflow-wrap: anywhere;
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
                <span class="wf-policy-badge"><i class="fa-solid fa-building-columns"></i> RBI Digital Lending Directions</span>
                <h1>Digital Lending Disclosures</h1>
                <p>Who lends to you, our official apps, and how pricing, repayment, your data and complaints work under RBI's digital lending rules.</p>
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
                <a href="digital-lending" class="wf-policy-tab-btn active">Digital Lending</a>
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
                <li><span class="pd-glance-icon"><i class="fas fa-building-columns" aria-hidden="true"></i></span><div><strong>You borrow directly from us</strong><span>WAQT FINANCE PRIVATE LIMITED, an RBI-registered NBFC, sanctions and disburses every loan.</span></div></li>
                <li><span class="pd-glance-icon"><i class="fas fa-file-lines" aria-hidden="true"></i></span><div><strong>Key Fact Statement before you sign</strong><span>You see the APR, all fees and the repayment schedule before you accept the loan.</span></div></li>
                <li><span class="pd-glance-icon"><i class="fas fa-hourglass-half" aria-hidden="true"></i></span><div><strong>Cooling-off period</strong><span>Exit the loan within the cooling-off period by repaying the principal and proportionate APR, with no penalty.</span></div></li>
                <li><span class="pd-glance-icon"><i class="fas fa-shield-halved" aria-hidden="true"></i></span><div><strong>Limited data access</strong><span>The app never reads your contacts, call logs, SMS, photos or files, and your data is stored in India.</span></div></li>
            </ul>
        </div>
    </div>
</section>

    <section class="wf-policy-document-section" id="policy">
        <div class="container">
            <div class="wf-policy-paper">
                <h2>Digital Lending Disclosures</h2>
                <p><strong>Last updated:</strong> 19 November 2026</p>
                <p>This page sets out the disclosures that <strong>WAQT FINANCE PRIVATE LIMITED</strong> makes under the Reserve Bank of India (Digital Lending) Directions, 2025, so that you know who is lending to you and how our digital lending works.</p>

                <h3>1. Regulated Entity (Lender)</h3>
                <table>
                    <tbody>
                        <tr><td><strong>Name:</strong></td><td>WAQT FINANCE PRIVATE LIMITED</td></tr>
                        <tr><td><strong>Type:</strong></td><td>Non-Banking Financial Company registered with the Reserve Bank of India</td></tr>
                        <tr><td><strong>RBI Registration No.:</strong></td><td>B.10.00143</td></tr>
                        <tr><td><strong>CIN:</strong></td><td>U65923DL2022PTC396001</td></tr>
                        <tr><td><strong>Office:</strong></td><td>15K-5 Jyoti Nagar Housing Board Colony, Sahakar Marg, Jaipur, Rajasthan 302005</td></tr>
                        <tr><td><strong>Email:</strong></td><td><a href="mailto:customercare@waqtfinance.com">customercare@waqtfinance.com</a></td></tr>
                    </tbody>
                </table>
                <p>WAQT Finance lends directly to you. Every loan you take through our app or website is sanctioned and disbursed by WAQT FINANCE PRIVATE LIMITED, and your loan agreement is with us.</p>

                <h3>2. Our Digital Lending Apps and Platforms</h3>
                <table>
                    <thead>
                        <tr><th>Name</th><th>Platform</th><th>Owned by</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>WAQT Finance (com.waqtfinance.customer)</td><td>Android app on Google Play</td><td>WAQT FINANCE PRIVATE LIMITED</td></tr>
                        <tr><td>waqtfinance.com</td><td>Website</td><td>WAQT FINANCE PRIVATE LIMITED</td></tr>
                    </tbody>
                </table>
                <p>Please use only these official channels. Before you share any details, check that an app or website claiming to be WAQT Finance appears in the list above.</p>

                <h3>3. Before You Sign: Key Fact Statement</h3>
                <ul>
                    <li>Before you sign the loan agreement, we give you a <strong>Key Fact Statement (KFS)</strong> in a standard format. It shows the loan amount, tenure, interest rate, the <strong>Annual Percentage Rate (APR)</strong>, all fees and charges, the repayment schedule, the cooling-off period, and our grievance contacts.</li>
                    <li>The APR is the all-inclusive annual cost of your loan, including interest and all fees. We do not charge any fee that is not shown in the KFS.</li>
                    <li>After you sign, we send you the KFS, the sanction letter, the loan agreement and its annexures on your registered email or in the app.</li>
                </ul>

                <h3>4. Disbursement and Repayment</h3>
                <ul>
                    <li>Your loan is disbursed directly to your bank account. It is never paid to an agent or any other third party on your behalf.</li>
                    <li>Your repayments go directly to our bank account, through e-NACH mandates, UPI, or payment links generated in our app or by us. Never pay into a personal account or to anyone who asks you to pay outside these channels.</li>
                    <li>We do not increase your credit limit automatically without your explicit consent.</li>
                </ul>

                <h3>5. Cooling-off (Look-up) Period</h3>
                <p>After disbursement you can exit the loan during the cooling-off period by repaying only the principal and the proportionate APR, with no prepayment penalty. The period is at least 1 day for loans with a tenure of less than 7 days and at least 3 days for loans with a tenure of 7 days or more. The exact period is shown in your KFS. See our <a href="refund-cancellation">Refund &amp; Cancellation Policy</a>.</p>

                <h3>6. Your Data</h3>
                <ul>
                    <li>We collect only the data needed for your loan, with your explicit consent, and we tell you the purpose before we collect it.</li>
                    <li>Our app does not access your contact list, call logs, SMS, photos, media or other files. It uses the camera and microphone only for KYC, when you start that step.</li>
                    <li>Your data is stored in India.</li>
                    <li>You can refuse or withdraw consent, and ask us to delete your data, as described in our <a href="privacy-policy">Privacy Policy</a> and <a href="delete-account">Account &amp; Data Deletion</a> page.</li>
                </ul>

                <h3>7. Recovery</h3>
                <ul>
                    <li>Recovery follows our <a href="fair-practice-code">Fair Practices Code</a>. Calls about dues are made only between 9:00 AM and 6:00 PM.</li>
                    <li>We never threaten, abuse or harass borrowers, and never contact your family, friends or colleagues about your loan.</li>
                    <li>If a recovery agent is assigned to your loan, we will tell you the agent's details by SMS or email before the agent contacts you.</li>
                </ul>

                <h3>8. Complaints</h3>
                <p>Our Grievance Redressal Officer is also the Nodal Grievance Redressal Officer for digital lending complaints:</p>
                <table>
                    <tbody>
                        <tr><td><strong>Name:</strong></td><td>Sanjay Kumar</td></tr>
                        <tr><td><strong>Email:</strong></td><td><a href="mailto:customercare@waqtfinance.com">customercare@waqtfinance.com</a></td></tr>
                        <tr><td><strong>Phone:</strong></td><td>9560260240 (Monday to Saturday, 10:00 AM to 6:00 PM)</td></tr>
                    </tbody>
                </table>
                <p>If your complaint is not resolved within 30 days, or you are not satisfied with our reply, you can complain to the Reserve Bank of India at <a href="https://cms.rbi.org.in" target="_blank" rel="noopener noreferrer">https://cms.rbi.org.in</a> or on 14448. See our <a href="grievance-redressal-policy">Grievance Redressal Policy</a> for details.</p>
                <p>To report a person or entity that is falsely using our name or running an unauthorised lending app, use RBI's Sachet portal at <a href="https://sachet.rbi.org.in" target="_blank" rel="noopener noreferrer">https://sachet.rbi.org.in</a>, and inform us at <a href="mailto:customercare@waqtfinance.com">customercare@waqtfinance.com</a>.</p>
            </div>
        </div>
    </section>

<?php include 'includes/policy-help.php'; ?>
<?php include 'includes/footer.php' ?>