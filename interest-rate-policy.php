<?php
$page_title = "Interest Rate Policy | Loan Pricing | Waqt Finance";
$meta_description = "Learn about the Interest Rate Policy at Waqt Finance. Understand how loan interest rates are determined, pricing factors, applicable charges, and transparent lending practices.";
$meta_keywords = "interest rate policy, loan interest rates, NBFC interest model, transparent lending India";
$canonical_url = "https://waqtfinance.com/interest-rate-policy";

include 'includes/header.php';
include 'includes/main-header.php';
?>

<style>
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
    
    /* Table styles inside policy document */
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
        }
        .wf-policy-tab-btn {
            text-align: center;
        }
    }
</style>

<main>
    <section class="wf-policy-hero">
        <div class="container">
            <div class="wf-policy-hero-content">
                <span class="wf-policy-badge"><i class="fa-solid fa-percent"></i> Rates for Your Needs</span>
                <h1>Interest Rate Policy</h1>
                <p>We offer competitive and transparent interest rates tailored to your financial needs. Our policy ensures clarity, fairness, and consistency, helping you plan repayments confidently.</p>
            </div>
        </div>
    </section>

    <div class="wf-policy-nav-wrap">
        <div class="container">
            <div class="wf-policy-nav-tabs">
                <a href="fair-practice-code" class="wf-policy-tab-btn">Fair Practices Code</a>
                <a href="grievance-redressal-policy" class="wf-policy-tab-btn">Grievance Redressal Policy</a>
                <a href="interest-rate-policy" class="wf-policy-tab-btn active">Interest Rate Policy</a>
                <a href="anti-money-laundering" class="wf-policy-tab-btn">Anti Money Laundering Policy</a>
                <a href="privacy-policy" class="wf-policy-tab-btn">Privacy Policy</a>
                <a href="terms-condition" class="wf-policy-tab-btn">Terms & Conditions</a>
                <a href="refund-cancellation" class="wf-policy-tab-btn">Refund Policy</a>
            </div>
        </div>
    </div>

    <section class="wf-policy-document-section">
        <div class="container">
            <div class="wf-policy-paper">
                <h2>Interest Rate Policy</h2>
                
                <h3>1. Background</h3>
                <p>The Reserve Bank of India (RBI) issued Circular DNBS / PD / CC No. 95/ 03.05.002/ 2006-07 on May 24, 2007, advising Boards of Non-Banking Finance Companies (NBFCs) to establish appropriate internal principles and procedures for determining interest rates, processing, and other charges. Additionally, Notification No. DNBS. 204 / CGM (ASR)-2009 dated January 2, 2009, and the Guidelines on Fair Practices Code for NBFCs, as amended from time to time (RBI Regulations), require NBFCs to make the interest rates and the approach for risk gradation available on their websites.</p>
                <p>In compliance with these RBI Regulations and the Fair Practices Code adopted by the Company, this Interest Rate Policy outlines the Interest Rate Model and the Company's approach to risk gradation for its lending business.</p>

                <h3>2. Objective of the Policy</h3>
                <p>To arrive at the benchmark rates to be used for different types of customer segments and to decide on the principals and approach of charging spreads to arrive at final rates charged from customers.</p>

                <h3>3. Review of the Policy</h3>
                <p>The Policy shall be reviewed once a year or in between if required due to changes required in the model, for example any addition/deletion of a particular component forming part of benchmark of calculation.</p>

                <h3>4. Organisation Structure</h3>
                <h4>I. Board Of Directors</h4>
                <p>The Board of Directors shall have oversight for the Investment Policy of the Company. In order to effectively implement the Interest Rate Policy, the Board may delegate certain operational aspects to ALCO as deemed fit.</p>

                <h4>II. Assets Liability Committee</h4>
                <p>ALCO shall be responsible for taking decision to change the benchmark rate. The ALCO meeting will be held monthly and any changes/no changes in the benchmark rate would be decided by ALCO and would be put up to board in subsequent meeting. Business can have their internal pricing policies under the overall framework of board approved interest rate policy for company for deciding the spreads to arrive at final rate. Any changes to business level internal pricing policies (if any) would need to be approved by the designated authorities (Managing Director, CFO, CRO, Treasurer, Head Compliance, and Business Head).</p>

                <table>
                    <thead>
                        <tr>
                            <th>Sr. No</th>
                            <th>Designation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Managing Director and CEO</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Chief Financial Officer</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Chief Risk Officer</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>Treasurer</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>General Counsel</td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>Head Compliance</td>
                        </tr>
                        <tr>
                            <td>7</td>
                            <td>Respective Business Head / Group Business Head</td>
                        </tr>
                    </tbody>
                </table>

                <h3>5. Interest Rate Model</h3>
                <ul>
                    <li>The interest rate and yield for each loan product will be determined by the Asset Liability Management Committee (ALCO) periodically.</li>
                    <li>Interest rates will be based on various factors, including the loan term, payment terms (monthly, quarterly, yearly), repayment terms, moratorium periods, bullet payments, back-ended payment schedules, and zero-coupon structured loans.</li>
                    <li>Factors influencing the interest rate include the cost of borrowed funds, matching tenor cost, market liquidity, refinance avenues, competition offerings, customer relationship tenure, disbursement costs, and other cost-related elements (Cost of Fund). Additionally, credit and default risk, customer segment, customer profile, professional qualifications, earning and employment stability, repayment ability, customer yield, risk premium, primary and collateral securities, past repayment track record, external ratings, creditworthiness, and industry trends will also be considered.</li>
                    <li>Business costs will be factored into the interest rates, including transaction complexity, capital risk weightage, transaction size, borrower location, and other related costs. The markup will reflect additional costs/overheads and the designed margin.</li>
                    <li>The Company may use an interest rate model where the same product and tenor availed during the same period may have different interest rates for different customers based on the factors mentioned above. Thus, interest rates may vary between customers and their loans.</li>
                    <li>The annualized interest rate will be communicated to the customer. Interest rates may be fixed, floating, or variable. The prime lending rate for floating rates will be reviewed periodically. For floating rates, the interest rate will be reviewed and decided by the Company periodically. The methodology for calculating the annual percentage rate may change with ALCO's approval.</li>
                    <li>Interest rates will be computed on daily balances and charged on monthly or other rests as decided by the committee in accordance with applicable rules and regulations.</li>
                    <li>Customers will be informed of the interest rates at the time of loan sanction/availment, and the apportionment of equated installments towards interest and principal dues will be made available to the customer.</li>
                    <li>Changes in interest rates will be prospective, and customers will be informed of any changes.</li>
                    <li>The Company may consider moratorium periods for interest payment and principal repayment, with appropriate pricing built in, in line with the product program.</li>
                    <li>For staggered disbursements, the interest rate will be reviewed and may vary according to the prevailing rate at the time of disbursement or as decided by the Company.</li>
                </ul>

                <h3>5.2 Additional Interest, Penal Interest, Charges, etc.</h3>
                <ul>
                    <li>The Company may levy additional interest for adhoc facilities and penal interest/default interest for any delay or default in payments. Details of penal interest charges for late repayment will be mentioned in the loan agreement and communicated in the sanction letter.</li>
                    <li>The Company may levy processing/documentation and other charges as expressly stated in the loan documents. Additional financial charges such as processing charges, documentation charges, cheque bouncing charges, pre-payment/foreclosure charges, part disbursement charges, cheque swaps, cash handling charges, RTGS/other remittance charges, commitment fees, charges for issuing NO DUE certificates, NOC, letters ceding charge on assets/security, security swap &amp; exchange charges, etc., will be levied as necessary. Applicable taxes, such as Goods and Services Tax, will be collected at prevailing rates.</li>
                    <li>Such additional interest, penal interest, and other charges may vary based on the loan product, exposure limit, customer segment, geographical location, and generally represent the cost of rendering services to the customers. Market practices will also be considered when deciding the charges.</li>
                    <li>The interest rate applicable to each customer may change based on the situation and the management’s perceived risk on a case-by-case basis.</li>
                    <li>Changes in interest rates will be decided periodically, depending on changes in benchmark rates, market volatility, and competitor reviews.</li>
                    <li>Customers will be informed of changes in interest rates or other charges in a manner deemed fit, as per the terms of the loan documents. Any revisions in interest rates or other charges will be prospective.</li>
                    <li>Claims for refunds or waivers of charges/penal interest/additional interest will generally not be entertained. The Company retains the sole discretion to address such requests if any.</li>
                </ul>

                <h3>5.3 Content on the Website</h3>
                <p>Appropriate disclosure regarding this Interest Rate Policy will be made on: <a href="https://waqtfinance.com/">https://waqtfinance.com/</a></p>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>