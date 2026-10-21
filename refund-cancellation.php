<?php
$page_title = "Refund and Cancellation Policy | Waqt Finance";
$meta_description = "Understand Waqt Finance's Loan Refund & Cancellation Policy. Check refund rules, cancellation process, eligibility, timelines, and customer assistance.";
$meta_keywords = "refund policy, loan cancellation, cooling-off period, waqt finance refund";
$canonical_url = "https://waqtfinance.com/refund-cancellation";

include 'includes/header.php';
include 'includes/main-header.php';
?>

<!-- Custom Premium CSS for Refund Page -->
<style>
:root {
    --primary-blue: #18529c; /* sync with brand blue */
    --dark-navy: #0F172A;
    --muted-slate: #475569;
    --bg-light: #F8FAFC;
    --border-color: #E2E8F0;
    --success-green: #16A34A;
}

.refund-hero {
    background: radial-gradient(circle at 50% 50%, #062b5d 0%, #031530 100%);
    padding: 80px 0 110px;
    color: #FFFFFF;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.refund-hero-content {
    max-width: 800px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.refund-hero h1 {
    font-size: clamp(32px, 4.5vw, 48px);
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 16px;
    color: #FFFFFF;
}

.refund-hero p {
    font-size: 16.5px;
    line-height: 1.7;
    color: rgba(255, 255, 255, 0.9);
    margin: 0;
}

.refund-hero-badge {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #FFFFFF;
    padding: 6px 16px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
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

/* Key Highlights Section */
.refund-highlights {
    margin-top: -45px;
    position: relative;
    z-index: 20;
    margin-bottom: 56px;
}

.highlights-wrapper {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

.highlight-stat-card {
    background: #FFFFFF;
    border: 1px solid var(--border-color);
    border-left: 4px solid var(--border-color);
    padding: 24px 32px;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
    display: flex;
    align-items: center;
    gap: 20px;
    text-align: left;
    transition: all 0.3s ease;
}

.highlight-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 15px 30px rgba(15, 23, 42, 0.08);
    border-left-color: var(--primary-blue);
}

.highlight-stat-icon {
    width: 48px;
    height: 48px;
    background: #EFF6FF;
    color: var(--primary-blue);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.highlight-stat-card:hover .highlight-stat-icon {
    background: var(--primary-blue);
    color: #fff;
}

.highlight-stat-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.highlight-stat-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--muted-slate);
    letter-spacing: 0.5px;
}

.highlight-stat-value {
    font-size: 17px;
    font-weight: 800;
    color: var(--dark-navy);
}

/* Structured Clauses Section */
.refund-body-section {
    background-color: var(--bg-light);
    padding: 60px 0 80px 0;
}

.refund-section-header {
    text-align: center;
    margin-bottom: 48px;
}

.refund-section-header h2 {
    font-size: 28px;
    font-weight: 800;
    color: var(--dark-navy);
    margin-bottom: 8px;
}

.refund-section-header p {
    font-size: 15px;
    color: var(--muted-slate);
}

.refund-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 32px;
}

.policy-card {
    background: #FFFFFF;
    border: 1px solid var(--border-color);
    border-top: 4px solid #cbd5e1;
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.01);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    text-align: left;
}

.policy-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 15px 30px rgba(15, 23, 42, 0.08);
    border-top-color: var(--primary-blue);
}

.policy-card-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 20px;
}

.policy-card-icon {
    width: 44px;
    height: 44px;
    background: #EFF6FF;
    color: var(--primary-blue);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: all 0.3s ease;
}

.policy-card:hover .policy-card-icon {
    background: var(--primary-blue);
    color: #fff;
}

.policy-card-title {
    font-size: 18px;
    font-weight: 800;
    color: var(--dark-navy);
    margin: 0;
}

.policy-card-desc {
    font-size: 14.5px;
    line-height: 1.6;
    color: var(--muted-slate);
    margin: 0;
}

.policy-card-list {
    margin: 16px 0 0 0;
    padding-left: 20px;
    list-style-type: disc;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.policy-card-list li {
    font-size: 14px;
    line-height: 1.5;
    color: var(--muted-slate);
}

/* Grievance redressal wrapper card */
.grievance-banner-card {
    background: #FFFFFF;
    border: 1px solid var(--border-color);
    border-left: 5px solid var(--primary-blue);
    border-radius: 20px;
    padding: 40px;
    margin-top: 48px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 32px;
    flex-wrap: wrap;
    text-align: left;
    transition: all 0.3s ease;
}

.grievance-banner-card:hover {
    box-shadow: 0 15px 40px rgba(15, 23, 42, 0.08);
}

.grievance-content {
    flex: 1;
    min-width: 300px;
}

.grievance-title {
    font-size: 20px;
    font-weight: 800;
    color: var(--dark-navy);
    margin-bottom: 12px;
}

.grievance-desc {
    font-size: 14.5px;
    line-height: 1.6;
    color: var(--muted-slate);
    margin: 0;
}

.btn-grievance {
    background: var(--primary-blue);
    color: #FFFFFF !important;
    padding: 12px 28px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 14.5px;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.btn-grievance:hover {
    background: #0b458f;
    transform: translateY(-1px);
}

/* Responsive styles */
@media(max-width: 992px) {
    .highlights-wrapper {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    .refund-grid {
        grid-template-columns: 1fr;
        gap: 24px;
    }
    .refund-hero h1 {
        font-size: 34px;
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
</style>

<!-- Hero Section -->
<section class="refund-hero">
    <div class="container">
        <div class="refund-hero-content">
            <div class="refund-hero-badge">
                <i class="fas fa-info-circle"></i>
                <span>Official Policy Guidelines</span>
            </div>
            <h1>Refund & Cancellation</h1>
            <p>Customers may cancel their loan application or related service request within 24 hours of payment or application submission. Once the cancellation request is approved, the refund will be credited to the original payment method within 7–10 business days.</p>
        </div>
    </div>
</section>

<!-- Highlights bar -->
<section class="container refund-highlights">
    <div class="highlights-wrapper">
        <div class="highlight-stat-card">
            <div class="highlight-stat-icon"><i class="fas fa-clock"></i></div>
            <div class="highlight-stat-info">
                <span class="highlight-stat-label">Cancellation Period</span>
                <span class="highlight-stat-value">Within 24 Hours</span>
            </div>
        </div>
        <div class="highlight-stat-card">
            <div class="highlight-stat-icon"><i class="fas fa-history"></i></div>
            <div class="highlight-stat-info">
                <span class="highlight-stat-label">Refund Timeframe</span>
                <span class="highlight-stat-value">72 Hours (Approval)</span>
            </div>
        </div>
        <div class="highlight-stat-card">
            <div class="highlight-stat-icon"><i class="fas fa-credit-card"></i></div>
            <div class="highlight-stat-info">
                <span class="highlight-stat-label">Credit Destination</span>
                <span class="highlight-stat-value">Original Payment</span>
            </div>
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
            <a href="refund-cancellation" class="wf-policy-tab-btn active">Refund Policy</a>
        </div>
    </div>
</div>

<!-- Main policy clauses -->
<section class="refund-body-section" id="clauses">
    <div class="container">
        <div class="refund-section-header">
            <h2>Policy Details & Clauses</h2>
            <p>Please read our terms and conditions below regarding cancellations and refund processing.</p>
        </div>
        
        <div class="refund-grid">
            <!-- Card 1: Loan Cancellation -->
            <div class="policy-card">
                <div class="policy-card-header">
                    <div class="policy-card-icon"><i class="fas fa-ban"></i></div>
                    <h3 class="policy-card-title">Loan Cancellation</h3>
                </div>
                <p class="policy-card-desc">Customers have the right to cancel their loan application at any stage prior to disbursement.</p>
                <ul class="policy-card-list">
                    <li>To initiate cancellation, submit a written request to WAQT FINANCE PVT. LTD, clearly stating your intention.</li>
                    <li>Any processing fees or validation charges incurred up to the point of cancellation will be deducted from the refundable amount.</li>
                </ul>
            </div>
            
            <!-- Card 2: Cooling-off Period -->
            <div class="policy-card">
                <div class="policy-card-header">
                    <div class="policy-card-icon"><i class="fas fa-snowflake"></i></div>
                    <h3 class="policy-card-title">Cooling-off Period</h3>
                </div>
                <p class="policy-card-desc">As per regulatory guidelines, customers are entitled to a cooling-off period during which they may cancel their loan without incurring any penalty.</p>
                <p class="policy-card-desc" style="margin-top: 12px;">The duration and conditions of the cooling-off period will be communicated to customers at the time of loan application, as mandated by regulatory requirements.</p>
            </div>
            
            <!-- Card 3: Disbursement Conditions -->
            <div class="policy-card">
                <div class="policy-card-header">
                    <div class="policy-card-icon"><i class="fas fa-check-double"></i></div>
                    <h3 class="policy-card-title">Disbursement Conditions</h3>
                </div>
                <p class="policy-card-desc">Once a loan is approved and disbursed, cancellation requests will not be entertained, except in exceptional circumstances.</p>
                <p class="policy-card-desc" style="margin-top: 12px;">Customers are required to adhere strictly to the repayment schedule outlined in the loan agreement signed at the time of loan disbursement.</p>
            </div>
            
            <!-- Card 4: Refund Policy -->
            <div class="policy-card">
                <div class="policy-card-header">
                    <div class="policy-card-icon"><i class="fas fa-undo"></i></div>
                    <h3 class="policy-card-title">Refund Policy</h3>
                </div>
                <p class="policy-card-desc">If a cancellation request is approved within the permissible period, WAQT FINANCE PVT. LTD will initiate the refund process promptly.</p>
                <ul class="policy-card-list">
                    <li>Refunds will be processed using the same mode of payment used for the initial transaction unless otherwise agreed.</li>
                    <li>Approved refunds will be returned to the original source within 72 hours of approval.</li>
                    <li>Any applicable interest or fees will be calculated based on the actual duration the funds were utilized.</li>
                </ul>
            </div>
            
            <!-- Card 5: Communication -->
            <div class="policy-card">
                <div class="policy-card-header">
                    <div class="policy-card-icon"><i class="fas fa-comments"></i></div>
                    <h3 class="policy-card-title">Communication of Policy</h3>
                </div>
                <p class="policy-card-desc">The cancellation policy is communicated to customers at the time of loan application and incorporated into the loan agreement.</p>
                <p class="policy-card-desc" style="margin-top: 12px;">Additionally, WAQT FINANCE PVT. LTD customer service representatives are available to provide assistance and guidance on the cancellation process upon request.</p>
            </div>
            
            <!-- Card 6: Additional Services -->
            <div class="policy-card">
                <div class="policy-card-header">
                    <div class="policy-card-icon"><i class="fas fa-shield-alt"></i></div>
                    <h3 class="policy-card-title">Additional Services</h3>
                </div>
                <p class="policy-card-desc">In cases where WAQT FINANCE PVT. LTD offers supplementary services or products alongside loans (such as insurance or advisory services), separate cancellation policies will apply to these services.</p>
                <p class="policy-card-desc" style="margin-top: 12px;">Customers will be informed about the terms and conditions governing the cancellation of these additional services at the time of purchase or engagement.</p>
            </div>
            
            <!-- Card 7: Right to Amend Policy -->
            <div class="policy-card">
                <div class="policy-card-header">
                    <div class="policy-card-icon"><i class="fas fa-sync-alt"></i></div>
                    <h3 class="policy-card-title">Right to Amend Policy</h3>
                </div>
                <p class="policy-card-desc">WAQT FINANCE PVT. LTD reserves the right to amend or update this cancellation policy at any time, as necessary.</p>
                <p class="policy-card-desc" style="margin-top: 12px;">In the event of any changes, customers will be notified through suitable channels, including updates on the WAQT FINANCE PVT. LTD website and direct customer communications.</p>
            </div>
        </div>
        
        <!-- Grievance Redressal Banner -->
        <div class="grievance-banner-card">
            <div class="grievance-content">
                <h3 class="grievance-title">Grievance Redressal</h3>
                <p class="grievance-desc">In the event of any concerns or disputes related to the cancellation policy or its implementation, customers may contact our Grievance Redressal Officer for a prompt resolution.</p>
            </div>
            <a href="contact-us" class="btn-grievance"><i class="fas fa-headset"></i> Write to Officer</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
    