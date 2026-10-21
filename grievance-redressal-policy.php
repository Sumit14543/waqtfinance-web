<?php
$page_title = "Grievance Redressal Policy | Waqt Finance";
$meta_description = "Learn about Waqt Finance's Grievance Redressal Policy, complaint resolution process, escalation matrix, and customer support for timely issue resolution.";
$meta_keywords = "grievance redressal, customer complaint policy, nodal officer waqt finance, help desk";
$canonical_url = "https://waqtfinance.com/grievance-redressal-policy";

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
    
    /* Fieldset styles */
    .wf-policy-paper fieldset {
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 24px;
        margin: 24px 0;
        background: #f8fafc;
    }
    .wf-policy-paper legend {
        font-size: 15px;
        font-weight: 800;
        color: #0066D6;
        padding: 0 12px;
        float: none;
        width: auto;
        margin: 0;
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
                <span class="wf-policy-badge"><i class="fa-solid fa-clock"></i> Fast complaint handling</span>
                <h1>Grievance Redressal Policy</h1>
                <p>We are committed to addressing your concerns promptly and fairly. Our grievance redressal system ensures that complaints are acknowledged, investigated, and resolved efficiently, maintaining transparency and building trust.</p>
            </div>
        </div>
    </section>

    <div class="wf-policy-nav-wrap">
        <div class="container">
            <div class="wf-policy-nav-tabs">
                <a href="fair-practice-code" class="wf-policy-tab-btn">Fair Practices Code</a>
                <a href="grievance-redressal-policy" class="wf-policy-tab-btn active">Grievance Redressal Policy</a>
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
                <h2>Grievance Redressal Policy</h2>
                
                <h3>1. INTRODUCTION</h3>
                <p>The Board of directors of WAQT FINANCE PRIVATE LIMITED (“the Company”) has designed and implemented the appropriate grievance redressal mechanism within the organization which ensures that all disputes arising out of the decisions of lending institutions' functionaries are heard and disposed of at least at the next higher level.</p>
                <p>The grievance redressal policy is designed to address the issues, concerns, complaints, or grievances (“Grievances” or “Complaints”) of the borrowers (“Customers”) on the WAQT FINANCE website (<a href="https://waqtfinance.com/">https://waqtfinance.com/</a>) and all associated mobile applications, if any (“Platform”). WAQT FINANCE has established a structured grievance redressal framework to ensure that the redressal of grievances is fair, just, and within the given framework of rules and regulations.</p>
                <p>The Board of directors shall also provide for periodical review of the compliance of the Fair Practices Code and the functioning of the grievance’s redressal mechanism at various levels of management and a consolidated report of such reviews will be submitted to the Board at regular intervals, as may be prescribed by it.</p>

                <h3>2. GRIEVANCE REDRESSAL OFFICER</h3>
                <p>The Board of Directors of the Company have appointed a Grievance Redressal Officer (GRO), who shall be responsible for overall functioning of the Grievance Redressal Mechanism of the Company. The Grievance Redressal Officer shall also be responsible to address grievances escalated to him / her and for ensuring prompt and efficient functioning of grievances redressal mechanism.</p>

                <h3>3. GRIEVANCE REDRESSAL MECHANISM</h3>
                <p>Any Customer having a grievance/complaint/feedback with respect to the product and service offered by WAQT FINANCE may write to the Company’s Customer Service Department in the following manner:</p>

                <h4>3.1 Filing a Complaint through following channels:</h4>
                <ul>
                    <li><strong>Email</strong>: Customers can send their grievance through email at <strong>customercare@waqtfinance.com</strong></li>
                    <li><strong>Letters</strong>: Customers can write to Customer Service, WAQT FINANCE Private Limited, 15K-5 Jyoti Nagar Housing Board Colony Sahakar Marg Jaipur Rajasthan 302005</li>
                    <li><strong>Customer Care Number</strong>: The Customers can directly approach Customer Grievance Cell, where the Company has a dedicated number <strong>9560260240</strong> and a dedicated team that compiles, addresses, and escalates customer’s calls. Customer Care Number: <strong>9560260240</strong> (Between 10:00 am and 6:00 pm, Monday to Saturday, except business holidays). <em>Anonymous complaints will not be addressed.</em></li>
                </ul>

                <h4>3.2 Resolution Process</h4>
                <ul>
                    <li>On receipt of complaint, the dedicated CGRM team shall, within reasonable time, send an acknowledgement of the same to the complainant. All the complaints received shall be recorded.</li>
                    <li>The Customer Service Officer shall ensure that all complaints are resolved in a timely and effective manners, and status of resolution / closure of complaints in records is updated.</li>
                    <li>The Customer Service Officer shall monitor the complaints status to ensure that the complaints are resolved within 3 working days of receipt of complaint.</li>
                    <li>If in any case, the CGRM team needs additional time, the CGRM team will inform the customer the reasons of delay in resolution within the timelines specified above and provide expected timelines for resolution of the complaint.</li>
                    <li>Complaints registered through the above means are addressed within 5 (five) business days.</li>
                    <li>Grievances should be sent through the email registered with WAQT FINANCE.</li>
                </ul>

                <h3>4. TAT (Turn Around Time for Resolution of Complaints)</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Complaint Level / Team</th>
                            <th>TAT (Turn Around Time)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>CGRM team</td>
                            <td>1-14 Days</td>
                        </tr>
                        <tr>
                            <td>GRO &amp; PNO</td>
                            <td>15-21 Days</td>
                        </tr>
                        <tr>
                            <td>Internal Ombudsman</td>
                            <td>22-30 Days</td>
                        </tr>
                    </tbody>
                </table>

                <fieldset>
                    <legend>Level 1 Escalation</legend>
                    <p>Customers are requested to follow either of the following steps for any complaints/grievances:</p>
                    <table>
                        <tbody>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>customercare@waqtfinance.com</td>
                            </tr>
                            <tr>
                                <td><strong>Call:</strong></td>
                                <td>9560260240</td>
                            </tr>
                            <tr>
                                <td><strong>Address:</strong></td>
                                <td>15K-5 Jyoti Nagar Housing Board Colony Sahakar Marg Jaipur Rajasthan 302005</td>
                            </tr>
                        </tbody>
                    </table>
                    <p><em>*Monday to Saturday from 10:00 A.M to 6:00 P.M</em></p>
                </fieldset>

                <fieldset>
                    <legend>Level 2 Escalation</legend>
                    <p>If a complaint is not resolved within 15 days, or the customer is not satisfied, they may escalate the issue to the Grievance Redressal Officer / Principal Nodal Officer.</p>
                    <p><strong>Grievance Redressal Officer</strong></p>
                    <table>
                        <tbody>
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td>Sanjay Kumar</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>customercare@waqtfinance.com</td>
                            </tr>
                            <tr>
                                <td><strong>Address:</strong></td>
                                <td>15K-5 Jyoti Nagar Housing Board Colony Sahakar Marg Jaipur Rajasthan 302005</td>
                            </tr>
                        </tbody>
                    </table>
                </fieldset>

                <fieldset>
                    <legend>Level 3 Escalation</legend>
                    <p>If the complaint addressed to the Grievance Redressal is not taken up or resolved within 21 days, or if the customer is not satisfied with the response received from the Grievance Redressal, they may approach the Internal Ombudsman.</p>
                    <p><strong>Internal Ombudsman</strong></p>
                    <table>
                        <tbody>
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td>Sanjay Kumar</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>customercare@waqtfinance.com </td>
                            </tr>
                            <tr>
                                <td><strong>Address:</strong></td>
                                <td>15K-5 Jyoti Nagar Housing Board Colony Sahakar Marg Jaipur Rajasthan 302005</td>
                            </tr>
                        </tbody>
                    </table>
                </fieldset>

                <fieldset>
                    <legend>Level 4 Escalation</legend>
                    <p>If unresolved within one month, the customer may approach the Ombudsman of the RBI (within one year from the reply received from the Company).</p>
                </fieldset>

                <h3>5. ROLE AND RESPONSIBILITIES OF INTERNAL OMBUDSMAN</h3>
                <ul>
                    <li>The Internal Ombudsman shall not handle complaints received directly from the complainants or members of the public but deal with the complaints that have already been examined by the regulated entity but have been partly or wholly rejected by the regulated entity.</li>
                    <li>The following types of complaints shall be outside the purview of these Directions and shall not be handled by the Internal Ombudsman:
                        <ul>
                            <li>Complaints related to corporate fraud, misappropriation etc., except those resulting from deficiency in service, if any, on the part of the regulated entity.</li>
                            <li>References in the nature of suggestions and commercial decisions of regulated entity. However, service deficiencies in cases falling under ‘commercial decisions’ will be valid complaints for the Internal Ombudsman.</li>
                            <li>Complaints / references relating to internal administration, human resources, or pay and emoluments of staff in the regulated entity.</li>
                            <li>Complaints which have been decided by or are already pending in other fora such as the Consumer Disputes Redressal Commission, courts, etc.</li>
                            <li>Disputes for which remedy has been provided under Section 18 of the Credit Information Companies (Regulation) Act, 2005.</li>
                        </ul>
                    </li>
                    <li>The regulated entity shall forward all rejected / partially rejected complaints under the categories above to the Internal Ombudsman/s. The Internal Ombudsman shall look for inherent deficiency in service in such cases and take a view whether any of these complaints can be exempted as decided by the regulated entity.</li>
                    <li>Complaints that are outside the purview of these Directions should be immediately referred to the regulated entity by the Internal Ombudsman.</li>
                    <li>The Internal Ombudsman shall analyze the pattern of complaints such as product / category wise, consumer group wise, geographical location wise, etc., and suggest means for taking actions to address the root cause of complaints of similar / repeat nature and those that require policy level changes in the regulated entity.</li>
                    <li>The Internal Ombudsman shall examine the complaints based on records available with the regulated entity, including any documents submitted by the complainant and comments or clarifications furnished by the regulated entity to the specific queries of the Internal Ombudsman. The Internal Ombudsman may seek additional information and documents from the complainant, through the regulated entity.</li>
                    <li>The Internal Ombudsman shall, on a quarterly basis, analyse the pattern of all complaints received against the regulated entity, such as entity-wise (for CICs), product-wise, category-wise, consumer group-wise, geographical location-wise, etc., and may provide inputs to the regulated entity for policy intervention, if so warranted.</li>
                </ul>

                <h3>6. AMENDMENT AND REVIEW OF THE POLICY</h3>
                <p>This policy shall be reviewed by the Board/Stakeholders Relationship &amp; Customer Service Committee at annual intervals. Further, the policy shall also be amended whenever required due to changes in relevant regulatory guidelines or for any reason and the same shall be notified to the Board/ Stakeholders Relationship &amp; Customer Service Committee promptly for their recommendation/approval.</p>
                <p>The policy would be available on the company’s website and at all branches (on request). All employees of the company will be made aware of this policy.</p>
                <p>Standard operating procedure (SOP) for Customer Grievance Redressal Mechanism / Escalation Process is marked as Annexure-A</p>
                
                <h3>Annexure-A</h3>
                <p><strong>Standard Operating Procedure (SOP) of Customer Grievance Redressal Mechanism / Escalation Process</strong>: This document has been prepared in terms of the provision of Master Direction - Reserve Bank of India (Internal Ombudsman for Regulated Entities) Directions, 2023 which explains the standard operating procedure for Customer Grievance Redressal Mechanism and the escalation process for the grievances received by the organisation. WAQT FINANCE has designed robust mechanism where customers can register their complaints and get the resolution in the duration of 30 days. Below are the indicative list of complaints/services requests/queries that can be addressed through the available channels:</p>

                <table>
                    <thead>
                        <tr>
                            <th>Nature of Complaint</th>
                            <th>Category of Complaint</th>
                            <th>Sub-categories of Complaints</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td rowspan="3"><strong>Critical Complaints</strong></td>
                            <td>Fraud Conducted by staff</td>
                            <td>Commission demanded for loan sanctioning by field staff; Misuse of customer's KYC and customer's information by the field staff.</td>
                        </tr>
                        <tr>
                            <td>Repayment Issues</td>
                            <td>Collection of installments without acknowledgement; Rude/unprofessional behavior; Coercing recovery practice; Visit made without consent.</td>
                        </tr>
                        <tr>
                            <td>Mis-selling of third-party products</td>
                            <td>Forced selling / Product bundling.</td>
                        </tr>
                        <tr>
                            <td rowspan="5"><strong>Normal Complaints</strong></td>
                            <td>Fraud by external agencies</td>
                            <td>Customer has been duped by an external agency using the name of the Company.</td>
                        </tr>
                        <tr>
                            <td>Interest rates</td>
                            <td>Incomplete disclosure of rate of interest; Excessive ROI charged; Wrong calculation of interest.</td>
                        </tr>
                        <tr>
                            <td>Digital Transaction</td>
                            <td>Loan sanctioned but money not transferred; Issues of digital transaction; Credited to wrong account; Installment deducted twice.</td>
                        </tr>
                        <tr>
                            <td>Updating of repayment records</td>
                            <td>Loan related documents not provided; NOC not received; Less amount credited; Installment/settlement issues.</td>
                        </tr>
                        <tr>
                            <td>Credit Information Report (CIR)</td>
                            <td>Customer data not updated or wrongly updated with credit bureaus.</td>
                        </tr>
                    </tbody>
                </table>

                <h3>Service Requests</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Service Request Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Requests to update/provide the KYCs and loan related documents</td>
                        </tr>
                        <tr>
                            <td>Applied for loan but not received</td>
                        </tr>
                        <tr>
                            <td>Seeking time for delayed payments due to exigencies</td>
                        </tr>
                        <tr>
                            <td>Request for refund excess paid amount</td>
                        </tr>
                        <tr>
                            <td>Foreclosure request</td>
                        </tr>
                        <tr>
                            <td>Request to cancel loan application</td>
                        </tr>
                        <tr>
                            <td>General Queries</td>
                        </tr>
                        <tr>
                            <td>News Leads</td>
                        </tr>
                        <tr>
                            <td>Inquiries related to settlement process or Foreclosure</td>
                        </tr>
                        <tr>
                            <td>Inquiries related to staff Information name and contact number</td>
                        </tr>
                        <tr>
                            <td>Loan status or about remaining balance / giving installment amount</td>
                        </tr>
                    </tbody>
                </table>

                <h3>Reporting to the Committee &amp; RBI</h3>
                <p>Internal Ombudsman will submit reports on cases referred to him/her and his/her analysis on the overall complaints received by the company to the Committee of the Board preferably on quarterly basis, but not less than half yearly intervals.</p>
                <ul>
                    <li>Internal Ombudsman will also submit reports to the RBI as per the prescribed format in the intervals defined by the RBI.</li>
                </ul>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>