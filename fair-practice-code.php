<?php
$page_title = "Fair Practice Code | Responsible Lending | Waqt Finance";
$meta_description = "Read the Fair Practice Code of Waqt Finance outlining transparent lending practices, customer rights, ethical conduct, and RBI-compliant loan processes.";
$meta_keywords = "fair practice code, ethical lending, customer rights India, NBFC standards waqt finance";
$canonical_url = "https://waqtfinance.com/fair-practice-code";

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
                <span class="wf-policy-badge"><i class="fa-solid fa-shield-halved"></i> Upholding Customer Rights and Trust</span>
                <h1>Fair Practice Code</h1>
                <p>We are committed to maintaining transparency, honesty, and ethical standards in all our dealings. Our fair practice code ensures clear communication, responsible lending, and respect for customer rights.</p>
            </div>
        </div>
    </section>

    <div class="wf-policy-nav-wrap">
        <div class="container">
            <div class="wf-policy-nav-tabs">
                <a href="fair-practice-code" class="wf-policy-tab-btn active">Fair Practices Code</a>
                <a href="grievance-redressal-policy" class="wf-policy-tab-btn">Grievance Redressal Policy</a>
                <a href="interest-rate-policy" class="wf-policy-tab-btn">Interest Rate Policy</a>
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
                <h2>Waqt Finance Private Limited (“Company”)</h2>
                <p><strong>Fair Practices Code Policy (“Code”)</strong>: This is with reference to the guidelines issued by the Reserve Bank of India on Fair Practice Code for all Non-Banking Financial Companies (NBFCs) vide RBI circular RBI/2015-16/16 DNBR (PD) CC.No.054/03.10.119/2015-16 dated July 1, 2015.</p>
                <p>This Code is aimed to provide to all the stakeholders, including but not limited to the Company’s customers, an effective overview of the practices followed in respect of the services offered by the Company.</p>

                <h3>1. The objectives of this Code are:</h3>
                <ul>
                    <li>To implement and promote industry standard practices in dealing with customers;</li>
                    <li>To promote a fair relationship between the customer and the Company and to ensure fair practice while dealing with customers;</li>
                    <li>To provide adequate disclosures on the terms and conditions of a loan to borrowers including applications for loan, appraisal, disbursement etc.</li>
                    <li>To ensure compliance with applicable laws and regulations in matters relating to recovery of loan amount;</li>
                    <li>To ensure compliance with applicable regulations in matters relating to Lending against collateral of gold jewellery, if any.</li>
                    <li>To create and maintain adequate mechanisms for redressal of customer grievances;</li>
                </ul>

                <h3>2. Definition Clause:</h3>
                <ul>
                    <li><strong>“Customer” (s) or you or End-Users</strong> shall mean any person who accesses, downloads, uses, views the Platform and the Services.</li>
                    <li><strong>“Loan”</strong> shall mean the loan that you may apply for through the Platform and which is sanctioned and granted by LENDER, subject to the applicable terms and conditions of the Loan Agreement.</li>
                    <li><strong>“Loan Agreement”</strong> shall mean the loan agreement to be executed between LENDER and the Customer(s) for granting the Loan whether in physical or electronic form as may be applicable from time-to-time.</li>
                    <li><strong>“Online Stores”</strong> shall mean Windows Store, Android Google Play, iOS App store or any other online store or portal where the App will be made available by the Company to the End-Users, from time to time.</li>
                    <li><strong>“Outstanding Amount”(s)</strong> shall mean the Loan, interests and charges due and payable by you to LENDER, on respective due date(s).</li>
                    <li><strong>“Platform”</strong> shall mean the App and the Website collectively.</li>
                    <li><strong>“Services”</strong> shall mean the services of granting, sanctioning, lending of short-term loans, through the Platform by LENDER.</li>
                    <li><strong>“Third Party Platforms”</strong> shall mean social networking platforms, such as Facebook, LinkedIn and other similar platforms.</li>
                    <li><strong>“User Data”</strong> shall mean any data, information, documents or materials submitted with the Company prior to or during the use of the Services.</li>
                    <li><strong>“Website”</strong> shall mean <a href="https://waqtfinance.com/">https://waqtfinance.com/</a> managed and operated by the Company for the provision of Services.</li>
                </ul>

                <h3>3. Target Audience</h3>
                <p>The Fair Practice code and guidelines shall be available in public domain at Company’s website and shall also be placed at the notice board of all the offices.</p>

                <h3>4. Loan Application</h3>
                <ul>
                    <li>The Company shall provide the borrower the information in vernacular language or a language as understood by the borrower, required in relation to the services, so that the borrower can make a meaningful comparison with the terms and conditions offered by other NBFCs and make an informed decision.</li>
                    <li>The Company shall give an acknowledgement of receipt for all loan applications. The Company shall endeavour to provide the time frame within which loan applications shall be processed.</li>
                    <li>There will not be discrimination of its customers based on caste, creed, religion, language and regions.</li>
                    <li>The loan application form shall indicate the list of documents required to be submitted along with the application form.</li>
                    <li>If any additional details/ documents are required, the same shall be reasonably intimated to the customers.</li>
                </ul>

                <h3>5. Loan Appraisal and Terms & Conditions</h3>
                <ul>
                    <li>The Company shall convey in writing to the customer in vernacular language or a language as understood by the borrower, by means of a sanction letter or any other document, the amount of loan sanctioned along with the terms and conditions including applicable rate of interest and shall maintain, in its record, the acceptance of these terms and conditions by the customer.</li>
                    <li>All the terms and conditions of the loan, that the pricing of the loan as mentioned in loan agreement, that there will be no penalty charged on prepayment of any loan.</li>
                    <li>Company shall mention the charges and penal interest charged for late repayment or prepayment in bold in the loan agreement to be executed between the customer and the Company.</li>
                    <li>Upon the execution of the loan agreement, the Company shall furnish a copy of the loan agreement along with a copy of all annexures if any attached to the loan agreement, to the customer.</li>
                    <li>Loan appraisal will adhere to the method of assessing income and repayment capacity mentioned in RBI’s directions RBI/DOR/2021-22/89 DoR.FIN.REC. 95/03.10.038/2021-22 dated March 14, 2022.</li>
                </ul>

                <h3>6. Disbursement of loans including changes in terms and conditions</h3>
                <ul>
                    <li>The Company shall give (7 working days) notice in the vernacular language or a language as understood by the borrower of any change in the terms and conditions – including disbursement schedule, interest rates, service charges, prepayment charges etc.</li>
                    <li>The Company shall, in the loan agreement to be executed between the Company and the customer, ensure that changes in interest rates and charges are effected only prospectively.</li>
                    <li>Any decision to recall/accelerate payment or performance under the loan agreement shall be in consonance with the respective loan agreement.</li>
                    <li>The Company shall, subject to the respective loan agreement, release all securities pertaining to the loan on receipt of full and final payment of the loan amount, other than any legitimate right or lien, and set-off for any other claim that the Company may have against the customer. If such right of set-off is to be exercised, the borrower shall be given notice about the same, with full particulars about the remaining claims and the conditions under which the Company is entitled to retain the securities until the relevant claim is settled / paid.</li>
                </ul>

                <h3>7. Interest Charged</h3>
                <ul>
                    <li>The Company has laid down appropriate internal principles and procedures in determining interest rates and processing and other charges.</li>
                    <li>The Company would adopt an interest rate model taking into account relevant factors such as, cost of funds, margin and risk premium, and determine the rate of interest to be charged for loans and advances.</li>
                    <li>The rates of interest and the approach for gradation of risks shall also be made available on the Company’s website. The information published in the website or otherwise published should be updated whenever there is a change in the rates of interest.</li>
                    <li>The rate of interest should be an annualized rate so that the customer is aware of the rates that would be charged to the account.</li>
                </ul>

                <h3>8. Grievance Redressal Mechanism</h3>
                <p>The company has laid the appropriates grievance redressal mechanism within the organisation to resolve the disputes arising in this regard. All grievances will be addressed to designated officials assigned by the Company. If the customers have any issues or wish to register a complaint, they can reach the Company.</p>

                <h3>9. Non-Coercive Methods of Recovery</h3>
                <ul>
                    <li>Company will ensure that a Board approved Policy is in place with regard to Code of Conduct by field staff and systems for their recruitment, training and supervision. The code shall lay down minimum qualification necessary for the field staff and shall have necessary training tools identified for them to deal with the customers. Training to field staff shall include programs to inculcate appropriate behaviour towards customers without adopting any threatening or abusive coercive debt collection/recovery practices/harassing of relatives, friends or coworkers/publishing the borrowers name/any sort of violence/any misleading information to the borrower debt or consequences of non-repayment. Compensation methods for staff should have more emphasis on areas of service and borrower satisfaction than merely the number of loans mobilized and the rate of recovery.</li>
                    <li>Calling for collection will be held between 9.00 am to 6.00 pm.</li>
                    <li>Generally only employees and related party employees and not outsourced recovery agents be used for recovery in sensitive areas.</li>
                </ul>

                <h3>10. General</h3>
                <ul>
                    <li>The Fair Practice Code in vernacular language shall be displayed in all offices and branch premises.</li>
                    <li>The minimum/ maximum interest charged and the grievance redressal system shall be displayed in all the offices and in the literature issued by the company (in vernacular language) and on the website.</li>
                    <li>The KYC Guidelines of RBI will be complied with. Due diligence shall be carried out as per the regulatory framework and company policies to ensure the repayment capacity of the borrowers.</li>
                    <li>Company will take adequate steps may be taken to ensure that the procedure for application of loan is not cumbersome and loan disbursements are done as per pre-determined time structure.</li>
                    <li>In case of receipt of request from the customer for transfer of customer account, the consent or otherwise i.e. objection of company if any is generally conveyed within 21 days from the date of receipt of request. Such transfer will be as per transparent contractual terms in consonance with all the applicable laws.</li>
                </ul>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>