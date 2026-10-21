<?php
$page_title = "Anti Money Laundering Policy | Waqt Finance";
$meta_description = "Learn about Waqt Finance's Anti Money Laundering (AML) Policy, KYC procedures, fraud prevention measures, and compliance with RBI regulations.";
$meta_keywords = "AML policy, anti-money laundering India, financial compliance, waqt finance policies";
$canonical_url = "https://waqtfinance.com/anti-money-laundering";

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
    .wf-policy-paper h5 {
        color: #0f172a;
        font-size: 16px;
        font-weight: 700;
        margin-top: 28px;
        margin-bottom: 14px;
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
                <span class="wf-policy-badge"><i class="fa-solid fa-building-columns"></i> Reliable funds for life</span>
                <h1>Anti-Money Laundering</h1>
                <p>Ensure secure and compliant financial transactions with our robust anti-money laundering solutions. With real-time monitoring, risk assessments, and regulatory adherence, we keep transactions safe.</p>
            </div>
        </div>
    </section>

    <div class="wf-policy-nav-wrap">
        <div class="container">
            <div class="wf-policy-nav-tabs">
                <a href="fair-practice-code" class="wf-policy-tab-btn">Fair Practices Code</a>
                <a href="grievance-redressal-policy" class="wf-policy-tab-btn">Grievance Redressal Policy</a>
                <a href="interest-rate-policy" class="wf-policy-tab-btn">Interest Rate Policy</a>
                <a href="anti-money-laundering" class="wf-policy-tab-btn active">Anti Money Laundering Policy</a>
                <a href="privacy-policy" class="wf-policy-tab-btn">Privacy Policy</a>
                <a href="terms-condition" class="wf-policy-tab-btn">Terms & Conditions</a>
                <a href="refund-cancellation" class="wf-policy-tab-btn">Refund Policy</a>
            </div>
        </div>
    </div>

    <section class="wf-policy-document-section">
        <div class="container">
            <div class="wf-policy-paper">
                <h2>Anti-Money Laundering Policy</h2>

        <div class="interest">
           <h5> 1. Objective</h5>
        </div>

        <div class="interest-para mt-3">
            <p>
               The Know Your Customer and Anti-Money Laundering (the Policy) applies to WAQT FINANCE PRIVATE LIMITED (hereinafter referred to as ‘WAQT FINANCE  or ‘the Company’). This Policy requires the Company and each employee to:
            </p>

        </div>

        

        <div class="interest-h">
           
            <ul class="mt-3">
                                <li> Protect the Company from being used for money laundering or funding terrorist
                                    activities.</li>
                                <li>Conduct themselves in accordance with the highest ethical standards.</li>
                                <li>Comply with the letter and the spirit of applicable Anti-Money Laundering (AML)
                                    Laws, and the
                                    Company’s KYC &amp; AML procedures.</li>
                                <li>Be alert to and escalate suspicious activity and not knowingly provide advice or
                                    other
                                    assistance to individuals who attempt to violate or avoid money-laundering laws, or
                                    this Policy;</li>
                                <li>Co-operate with the regulatory authorities and the Financial Intelligence Unit as
                                    per the
                                    applicable laws.</li>
                            </ul>
        </div>

        
         <div class="interest mt-3">
           <h5> 2. Applicability & Validity of the Policy</h5>
        </div>

        <div class="interest-para mt-2">
            <p>
               The Policy shall be applicable from such date as approved by the Board of Directors. The Board shall review, validate, update, and approve the Policy as applicable from time to time. Any revisions in specific aspects of this Policy may be communicated through mandates issued by the relevant authority and shall become part of this Policy from the date they become effective.


            </p>

        </div>

        <div class="interest mt-3">
           <h5> 3. Regulatory Reference</h5>
        </div>

        <div class="interest-para mt-2">
            <p>
               RBI vide its circular no. RBI/2015-16/108 DNBR (PD) CC No. 051/03.10.119/2015-16 as duly amended from time to time has instructed all the Non- Banking Finance Company (NBFCs) to adopt the necessary guidelines depending on the activity undertaken by them and ensure that a proper policy framework on ‘Know Your Customer’ and Anti-Money Laundering measures is formulated and put in place with the approval of the Board. Therefore, this Policy is thus being designed in line with Master Direction - Know Your Customer (KYC) Direction, 2016, issued by the RBI, as amended from time to time (“KYC Master Directions”) Prevention of Money Laundering Act -2002, Prevention of Money Laundering (Maintenance of Records) Rules, 2005 and such other regulatory laws as may be applicable.

            </p>

        </div>

        <div class="interest mt-3">
           <h5> 4. Definitions</h5>
        </div>

        <div class="interest-para mt-2">
            <p>
               For the purpose of KYC Norms, definition of various terms used is as under:

            </p>

            <div class="interest-h">
           
            <ul>
                                <li> i. “Aadhaar number” shall have the meaning assigned to it in clause (a) of section
                                    2 of the Aadhaar
                                    (TargetedDelivery of Financial and Other Subsidies, Benefits and Services) Act, 2016
                                    (18 of 2016).</li>
                                <li> ii. “Act” and “Rules” means the Prevention of Money-Laundering Act, 2002 and the
                                    Prevention of
                                    Money- Laundering (Maintenance of Records) Rules, 2005, respectively and amendments
                                    thereto.</li>
                                <li> iii. “Authentication” in the context of Aadhaar authentication, means the process
                                    as defined under
                                    sub-section (c) of section 2 of the Aadhaar (Targeted Delivery of Financial and
                                    Other Subsidies,
                                    Benefits and Services) Act, 2016.</li>
                                <li> iv. “Central KYC Records Registry” (CKYCR) means an entity defined under Rule
                                    2(1)(aa) of Prevention
                                    of Money-Laundering (Maintenance of Records) Rules, 2005, to receive, store,
                                    safeguard and retrieve
                                    the KYC records in digital form of a customer.</li>
                                <li> v. “Certified Copy” - Obtaining a certified copy by the Company shall mean
                                    comparing the copy of the
                                    proof of possession of Aadhaar number where offline verification cannot be carried
                                    out or officially
                                    valid documents produced by the customer with the original and recording the same on
                                    the copy by the
                                    authorised officer of the RE as per the provisions contained in the Act.</li>
                                <li> vi. “Counterfeit Currency Transaction means all cash transactions, where forged or
                                    counterfeit
                                    Indian currency notes have been used as genuine. These cash transactions should also
                                    include
                                    transactions where forgery of valuable security or documents has taken place.</li>
                                <li> vii. “Customer” means a person who is engaged in a financial transaction or
                                    activity with the
                                    Company and includes a person on whose behalf the person who is engaged in the
                                    transaction or
                                    activity, is acting.</li>
                                <li> viii. “Digital KYC” means the capturing live photo of the customer and officially
                                    valid document or
                                    the proof of possession of Aadhaar, where offline verification cannot be carried
                                    out, along with the
                                    latitude and longitude of the location where such live photo is being taken by an
                                    authorized officer
                                    of the Company as per the provisions contained in the Act.</li>
                                <li> ix. “Digital Signature” shall have the same meaning as assigned to it in clause (p)
                                    of subsection
                                    (1) of section (2) of the Information Technology Act, 2000 (21 of 2000).</li>
                                <li> x. “Equivalent e-document” means an electronic equivalent of a document, issued by
                                    the issuing
                                    authority of such document with its valid digital signature including documents
                                    issued to the
                                    digital locker account of the customer as per rule 9 of the Information Technology
                                    (Preservation and
                                    Retention of Information by Intermediaries Providing Digital Locker Facilities)
                                    Rules, 2016.</li>
                                <li> xi. “Know Your Client (KYC) Identifier” means the unique number or code assigned to
                                    a customer by
                                    the Central KYC Records Registry.</li>
                                <li> xii. “KYC Templates” means templates prepared to facilitate collating and reporting
                                    the KYC data to
                                    the Central KYC Records Registry (CKYCR), for individuals and legal entities, as
                                    required by the
                                    relevant Rules.</li>
                                <li> xiii. “Designated Director”- means a person designated by the Board of Directors of
                                    the Company to
                                    ensure overall compliance with the obligations imposed under chapter IV of the
                                    Prevention of Money
                                    Laundering Act (PMLA) and the Rules thereunder and includes:-
                                    <ul>
                                        <li> a ) The Managing Director or a Whole-Time Director duly authorized by the
                                            Board of Directors,</li>
                                        <li> b ) The name, designation and address of the Designated Director shall be
                                            communicated to the
                                            Financial Intelligence Unit-India (FIU-IND).</li>
                                        <li> c ) A person of senior management official designated by the Company as
                                            “Designated Director” to
                                            ensure compliance with the obligations under the Prevention of Money
                                            Laundering (Amendment) Act,
                                            2012. However, in no case, the Principal Officer should be nominated as the
                                            "Designated Director".</li>
                                    </ul>
                                </li>
                                <li> xiv. “Offline verification” shall have the same meaning as assigned to it in clause
                                    (pa) of section
                                    2 of the Aadhaar (Targeted Delivery of Financial and Other Subsidies, Benefits and
                                    Services) Act,
                                    2016 (18 of 2016).</li>
                                <li> xv. “Officially Valid Document” (OVD) means the passport, the driving license,
                                    proof of possession
                                    of Aadhaar number, the Voter's Identity Card issued by the Election Commission of
                                    India, job card
                                    issued by NREGA duly signed by an officer of the State Government and letter issued
                                    by the National
                                    Population Register containing details of name and address.</li>
                                <li> xvi. “Politically Exposed Persons” (PEPs) are individuals who are or have been
                                    entrusted with
                                    prominent public functions by a foreign country, including the Heads of
                                    States/Governments, senior
                                    politicians, senior government or judicial or military officers, senior executives
                                    of state-owned
                                    corporations and important political party officials.</li>
                                <li>xvii. Principal Officer (PO) An official designated by the Board of Directors of the
                                    Company for
                                    overseeing and managing the KYC&amp; AML policies and processes.
                                    <p> The PO will be responsible for ensuring compliance, monitoring transactions, and
                                        sharing and reporting information as required under the law/regulations.</p>
                                    <p> The name, designation and address of the Principal Officer shall be communicated
                                        to the Financial Intelligence Unit-India FIU-IND.</p>

                                </li>
                                <li> xviii. “Senior Management” - for the purpose of KYC compliance shall include
                                    members of the
                                    Management Committee, Designated Director, Head of Compliance, Principal Officer
                                    (PO) and his
                                    supervisor.</li>
                                <li> xix. “Suspicious transaction” means a “transaction” as defined below, including an
                                    attempted
                                    transaction, whether or not made in cash, which, to a person acting in good faith:
                                    <ul>
                                        <li>gives rise to a reasonable ground of suspicion that it may involve proceeds
                                            of an offence specified in the Schedule to the Act, regardless of the value
                                            involved; or</li>
                                        <li>appears to be made in circumstances of unusual or unjustified complexity; or
                                        </li>
                                        <li>appears to not have economic rationale or bona-fide purpose; or</li>
                                        <li>gives rise to a reasonable ground of suspicion that it may involve financing
                                            of the activities relating to terrorism.</li>
                                    </ul>
                                </li>
                                <li> xx. “Video based Customer Identification Process (V-CIP)”: a method of customer
                                    identification by an
                                    official of the Company by undertaking seamless, secure, real-time, consent-based
                                    audio- visual
                                    interaction with the customer to obtain identification information including the
                                    documents required
                                    for CDD purpose, and to ascertain the veracity of the information furnished by the
                                    customer.</li>

                            </ul>
        </div>

        </div>

            
    <div class="interest-h">
        <h5>5. Money Laundering and Terrorist Financing Risk Assessment by the Company:</h5>
           
            <ul class="mt-3">
                                <li> i. The Company shall carry out ‘Money Laundering (ML) and Terrorist Financing (TF)
                                    Risk Assessment’
                                    exercise periodically to identify, assess and take effective measures to mitigate
                                    its money
                                    laundering and terrorist financing risk for clients, countries or geographic areas,
                                    products,
                                    services, transactions or delivery channels, etc. as applicable from time to time.
                                </li>
                                <li>ii. The assessment process shall consider all the relevant risk factors before
                                    determining the level
                                    of overall risk and the appropriate level and type of mitigation to be applied.
                                    While preparing the
                                    internal risk assessment, the Company shall take cognizance of the overall
                                    sector-specific
                                    vulnerabilities, if any, that the regulator/supervisor may share with the Company
                                    from time to time.</li>
                                <li> iii. The risk assessment by the Company shall be properly documented and be
                                    proportionate to the
                                    nature, size, geographical presence, complexity of activities/structure, etc. of the
                                    Company.
                                    iv. The outcome of the exercise shall be put up to the Board or any committee of the
                                    Board to which
                                    power in this regard has been delegated and should be available to competent
                                    authorities and
                                    self-regulating bodies.</li>

                            </ul>
        </div>
        
       
        <div class="interest-para mt-2">
            <p>
               6. The Company shall apply a Risk Based Approach (RBA) for mitigation and management of the identified risk and should have Board approved policies, controls and procedures in this regard. Further, the Company shall monitor the implementation of the controls and enhance them If necessary.

            </p>

        </div>

         <div class="interest mt-3">
           <h5> Customer Acceptance Policy</h5>

           <p class="mt-3">In line with the Reserve Bank of India (RBI) Directions, the Prevention of Money Laundering Act (PMLA) and the Rules thereunder, the Company has formulated Customer Acceptance Policy (CAP) which lays down the broad criteria for acceptance of customers.</p>
        </div>
        

        <div class="interest-h mt-3">
            <p>The features of the Customer Acceptance Policy (CAP) are detailed below:</p>

            <ul class="mt-3">
                                <li> i. The Company shall not open any account(s) in anonymous, fictitious or 'benami'
                                    name(s).</li>
                                <li> ii. No account is opened where the Company is unable to apply appropriate due
                                    diligence measures,
                                    either due to non-cooperation of the customer or non-reliability of the
                                    documents/information
                                    furnished by the customer.</li>
                                <li> iii. No transaction or account-based relationship shall be undertaken without
                                    following the due
                                    diligence procedure.</li>
                                <li> iv. The mandatory information to be sought for KYC purpose while opening an account
                                    and during the
                                    periodic updation shall be specified.</li>
                                <li>v. ‘Optional’/additional information, is obtained with the explicit consent of the
                                    customer after
                                    the account is opened.</li>
                                <li>vi. The Company shall ensure that the identity of the customer does not match with
                                    any person with
                                    known criminal background or with banned entities such as individual terrorists or
                                    terrorist
                                    organizations, etc. For this purpose, the Company shall maintain lists of
                                    individuals or entities
                                    issued by Reserve Bank of India (RBI), United Nations Security Council, other
                                    regulatory &amp;
                                    enforcement agencies, internal lists as the Company may decide from time to time.
                                    Full details of
                                    accounts/ customers bearing resemblance with any of the individuals/ entities in the
                                    list shall be
                                    treated as suspicious and reported.</li>
                                <li> vii. Adequate due diligence is a fundamental requirement for establishing the
                                    identity of the
                                    customer. Identity generally means a set of attributes which together uniquely
                                    identify a natural
                                    person or legal entity. In order to avoid fictitious and fraudulent applications of
                                    the customers,
                                    and to achieve a reasonable degree of satisfaction as to the identity of the
                                    customer, the Company
                                    shall conduct appropriate due diligence.</li>
                                <li>
                                    viii. The Company may rely on third party verification subject to the conditions
                                    prescribed by
                                    Reserve Bank of India (RBI) in this regard.</li>
                                <li> ix. The purpose of commencing the relationship/opening of accounts shall be
                                    established and the
                                    beneficiary of the relationship/account shall also be identified.</li>
                                <li> x. The information collected from the customer shall be kept confidential.</li>
                                <li> xi. Where the Company is unable to apply appropriate KYC measures due to
                                    non-furnishing of
                                    information and /or non-cooperation by the customer, the Company may consider
                                    closing the account or
                                    terminating the business relationship. However, the decision to close an existing
                                    account shall be
                                    taken at a reasonably senior level, after giving due notice to the customer
                                    explaining the reasons
                                    for such a decision.</li>
                                <li> xii. Where an equivalent e-document is obtained from the customer, the Company
                                    shall verify the
                                    digital signature as per the provisions of the Information Technology Act, 2000 (21
                                    of 2000).</li>
                            </ul>
        </div>
       

        <div class="interest mt-3">
          <p>The Company may undertake live V-CIP, to be carried out by an official of the Company, for establishment of an account-based relationship with an individual customer, after obtaining his informed consent:</p>

         <div class="interest-h mt-3">
            <ul>
                                <li> i) The Company shall capture a clear image of PAN card to be displayed by the
                                    customer during the
                                    process, except in cases where e-PAN is provided by the customer. The PAN details
                                    shall be verified
                                    from the database of the issuing authority.</li>
                                <li> ii) Live location of the customer (Geotagging) shall be captured to ensure that
                                    customer is
                                    physically present in India.</li>
                                <li> iii) The official shall ensure that photograph of the customer in the Aadhaar/PAN
                                    details matches
                                    with the customer undertaking the V-CIP and the identification details in
                                    Aadhaar/PAN shall match
                                    with the details provided by the customer.</li>
                                <li>The official shall ensure that the sequence and/or type of questions during video
                                    interactions
                                    are varied in order to establish that the interactions are real-time and not
                                    pre-recorded.</li>
                                <li> v) In case of offline verification of Aadhaar using XML file or Aadhaar Secure QR
                                    Code, it shall be
                                    ensured that the XML file or QR code generation date is not older than 3 working
                                    days from the date
                                    of carrying out V-CIP.</li>
                                <li> vi) All accounts opened through V-CIP shall be made operational only after being
                                    subject to
                                    concurrent audit, to ensure the integrity of process.</li>
                                <li>vii) The Company shall ensure that the process is a seamless, real-time, secured,
                                    end-to-end
                                    encrypted audio- visual interaction with the customer and the quality of the
                                    communication is
                                    adequate to allow identification of the customer beyond doubt. The Company shall
                                    carry out the
                                    liveliness check in order to guard against spoofing and such other fraudulent
                                    manipulations.</li>
                                <li>viii) To ensure security, robustness and end to end encryption, the Company shall
                                    carry out software
                                    and security audit and validation of the V-CIP application before rolling it out.
                                </li>
                                <li> ix) The activity log along with the credentials of the official performing the
                                    V-CIP shall be
                                    preserved and</li>
                                <li> x) The Company shall ensure that the video recording is stored in a safe and secure
                                    manner and bears
                                    the date and time stamp.</li>
                                <li> xi) The Company shall ensure to redact or blackout the Aadhar Number obtained from
                                    the client.</li>
                            </ul>
         </div>

        </div>

        <div class="interest mt-3">
            <h5>7. Risk Management</h5>
            <p class="mt-2">For Risk Management, the Company shall have a risk-based approach which includes the following:</p>

            <div class="interest-h mt-3">
                <ul>
                                <li> i. Customers shall be categorised as low, medium and high-risk category, based on
                                    the assessment and
                                    risk perception of the Company.</li>
                                <li> ii. Broad principles may be laid down by the Company for risk-categorisation of
                                    customers.</li>
                                <li> iii. Risk categorisation shall be undertaken based on parameters such as customer’s
                                    identity,
                                    social/financial status, nature of business activity, and information about the
                                    customer’s business
                                    and their location, geographical risk covering customers as well as transactions,
                                    type of
                                    products/services offered, delivery channel used for delivery of products/services,
                                    types of
                                    transaction undertaken – cash, cheque/monetary instruments, wire transfers, forex
                                    transactions, etc.
                                    While considering customer’s identity, the ability to confirm identity documents
                                    through online or
                                    other services offered by issuing authorities may also be factored in.
                                </li>
                                <li>iv. The risk categorisation of a customer and the specific reasons for such
                                    categorisation shall be
                                    kept confidential and shall not be revealed to the customer to avoid tipping off the
                                    customer.
                                </li>
                            </ul>
            </div>
        </div>


        <div class="interest mt-3">
            <h5>8. List of Customer Identification Documents</h5>
            <p class="mt-2">Following documents are collected for customer identification and address verification:</p>

            <div class="interest-h mt-3">
                <ul>
                                <li> 1. Filling of KYC form and First Primary KYC document: Aadhar ID should be
                                    necessary ID for all loan
                                    disbursed for both borrower and co-borrower.</li>
                                <li> 2. Second (secondary) KYC document:
                                    <ul>
                                        <li>Voter ID issued by Election Commission</li>
                                        <li> Ration Card (only if the borrower name is mentioned on the ration card)
                                        </li>
                                        <li>Driver License (Not expired)</li>
                                        <li>PAN Card or Form 60</li>
                                        <li>Job card issued to the borrower by the state government under MNREGA</li>
                                        <li>Nationalized Banks passbook with Photo ID.</li>
                                        <li>Utility Bill – Electricity (not more than 2 months old)</li>
                                    </ul>
                                </li>
                            </ul>
            </div>
        </div>


        <div class="interest mt-3">
            <h5>9. Customer Identification Procedures</h5>
            <p class="mt-2">The Company shall undertake identification of customers in the following cases:</p>
            <div class="interest-h mt-3">
                <ul>
                                <li>Commencement of an account-based relationship with the customer.</li>
                                <li>When there is a doubt about the authenticity or adequacy of the customer
                                    identification data obtained.</li>
                                <li>Selling their own products, selling third party products as agents, and any other
                                    product for more than Rs. 50,000/-.</li>
                                <li>Carrying out transactions for a non-account-based customer (walk-in customer).</li>
                                <li>The Company shall obtain satisfactory evidence of the identity of the customer
                                    depending upon the perceived risks at the time of commencement of
                                    relationship/opening of account. Such evidence shall be substantiated by reliable
                                    independent documents, data or information or other means like physical verification
                                    etc.</li>
                                <li>The Company shall obtain and verify Permanent Account Number (PAN) of customers as
                                    per the applicable provisions of Income Tax Rule 114B. Form 60 shall be obtained
                                    from persons who do not have PAN.</li>
                                <li>The documents to be accepted by the Company for customer identification shall be
                                    based on the regulatory prescriptions from time to time and finalized after approval
                                    from the Operations Head.</li>
                                <li>Decision-making functions of determining compliance with KYC norms shall not be
                                    outsourced.</li>
                                <li>The customers shall not be required to furnish an additional Officially Valid
                                    Document (OVD), if the OVD submitted for KYC contains proof of identity as well as
                                    proof of address.</li>
                                <li>The customers shall not be required to furnish separate proof of address for
                                    permanent and current addresses if these are different. In case the proof of address
                                    furnished by the customer is the address where the customer is currently residing, a
                                    declaration shall be taken from the customer about their local address on which all
                                    correspondence shall be made by the Company.</li>
                                <li>The local address for correspondence, for which proof of address is not available,
                                    shall be verified through ‘positive confirmation’ such as cheque books, ATM cards,
                                    telephonic conversation, or positive address verification.</li>
                                <li>In case of change in the address mentioned on the ‘proof of address’, fresh proof of
                                    address should be obtained within a period of 6 months.</li>
                            </ul>
            </div>
        </div>

        <div class="interest mt-3">
            <h5>10. Customer Due Diligence (CDD) Procedure</h5>
            <p class="mt-2">The Company shall obtain the following documents from an individual while establishing an
                                account-based relationship:</p>

            <div class="interest-h mt-3">
                <ul>
                                <li>One certified copy of an OVD containing details of identity and address.</li>
                                <li>One recent photograph.</li>
                                <li>Such other documents pertaining to the nature of business or financial status
                                    specified by the Company.</li>
                            </ul>
            </div>                    
        </div>

        <div class="interest mt-3">
            <p>Further, the Company shall carry out authentication of the Customer’s Aadhar Number using
                                e-KYC
                                authentication facilities provided by the Unique Identification Authority of India.
                                Moreover,
                                where the customer has submitted an equivalent e document of any Officially valid
                                document
                                (OVD), the Company shall verify the digital signature as per the provisions of the
                                Information
                                Technology Act, 2000 and rules made thereunder and take a live photo as specified in
                                Reserve
                                Bank Of India (RBI) Circular.
                            </p>

                             <div class="interest-h mt-3">
                                <ul class="mt-3">
                                <li>The information collected from customers for the purpose of opening of account shall
                                    be treated as confidential and shall not be divulged for cross-selling or for any
                                    other purpose without the express permission of the customer.</li>
                                <li>A copy of the marriage certificate issued by the State Government or Gazette
                                    notification indicating change in name together with a certified copy of the OVD in
                                    the existing name of the person shall be obtained for proof of address and identity,
                                    in cases of persons who change their names on account of marriage or otherwise.</li>
                                <li>If an existing KYC compliant customer desires to open another account, there shall
                                    be no need for a fresh CDD exercise provided there is no change in details last
                                    provided under the Company’s KYC norms.</li>
                                <li>Where the Company is suspicious of money laundering or terrorist financing, and it
                                    reasonably believes that performing the CDD process will tip-off the customer, it
                                    shall not pursue the CDD process, and instead file an STR (Suspicious Transaction
                                    Report).</li>
                            </ul>
                             </div>
        </div>

        <div class="interest mt-3">
            <h6 style="font-size:20px;">Prohibited List of Individuals/Entities</h6>
            <p class="mt-3"> The Company shall ensure that in terms of Section 51A of Unlawful Activities
                                (Prevention) (UAPA)
                                Act, 1967 and amendments thereto, any of the existing or new customers are not in the
                                prohibited
                                list of individuals and entities which are periodically prescribed by local regulator
                                from time
                                to time. Compliance monitoring of such individuals / entities are done periodically be
                                screening
                                them against the below lists provided under RBI Directions, as amended from time to
                                time:</p>
        </div>

<div class="interes">

</div>

    <div class="interes mt-3">
        <h5>11. Digital KYC Process</h5>
    </div>
<div class="interest-h mt-3">
<ul>
                                <li> i. The Company shall develop an application for digital KYC process which shall be
                                    made available at
                                    customer touch points for undertaking KYC of their customers and the KYC process
                                    shall be undertaken
                                    only through its authenticated application.</li>
                                <li> ii. The access of the Application shall be controlled by the Company and it should
                                    be ensured that
                                    the same is not used by unauthorized persons. The Application shall be accessed only
                                    through
                                    login-id and password or Live OTP or Time OTP controlled mechanism given by the
                                    Company to its
                                    authorized officials. C. The customer, for the purpose of KYC, shall visit the
                                    location of the
                                    authorized official of the Company or vice- versa. The original Officially valid
                                    document (OVD)
                                    shall be in possession of the customer.</li>
                                <li> The Company must ensure that the Live photograph of the customer is taken by the
                                    authorized officer
                                    and the same photograph is embedded in the Customer Application Form (CAF). Further,
                                    the system
                                    Application of the RE shall put a water-mark in readable form having CAF number, GPS
                                    coordinates,
                                    authorized official’s name, unique employee Code (assigned by Company) and Date
                                    (DD:MM:YYYY) and
                                    time stamp (HH:MM:SS) on the captured live photograph of the customer.</li>
                                <li> iii. The Application of the Company shall have the feature that only live
                                    photograph of the customer
                                    is captured and no printed or video-graphed photograph of the customer is captured.
                                    The background
                                    behind the customer while capturing live photograph should be of white colour and no
                                    other person
                                    shall come into the frame while capturing the live photograph of the customer.</li>
                                <li> iv. Similarly, the live photograph of the original Officially valid document (OVD)
                                    or proof of
                                    possession of Aadhaar where offline verification cannot be carried out (placed
                                    horizontally), shall
                                    be captured vertically from above and water-marking in readable form as mentioned
                                    above shall be
                                    done. No skew or tilt in the mobile device shall be there while capturing the live
                                    photograph of the
                                    original documents. The live photograph of the customer and his original documents
                                    shall be captured
                                    in proper light so that they are clearly readable and identifiable.</li>
                                <li> v. Once the above-mentioned process is completed, a One Time Password (OTP) message
                                    containing the
                                    text that ‘Please verify the details filled in form before sharing OTP’ shall be
                                    sent to customer’s
                                    own mobile number. Upon successful validation of the OTP, it will be treated as
                                    customer signature
                                    on CAF. However, if the customer does not have his/her own mobile number, then
                                    mobile number of
                                    his/her family/relatives/known persons may be used for this purpose and be clearly
                                    mentioned in CAF.
                                    In any case, the mobile number of authorized officer of the Company shall not be
                                    used for customer
                                    signature.</li>
                                <li> vi. The authorized officer shall provide a declaration about the capturing of the
                                    live photograph of
                                    customer and the original document. For this purpose, the authorized official shall
                                    be verified with
                                    One Time Password (OTP) which will be sent to his mobile number registered with the
                                    Company. Upon
                                    successful OTP validation, it shall be treated as authorized officer’s signature on
                                    the declaration.
                                    The live photograph of the authorized official shall also be captured in this
                                    authorized officer’s
                                    declaration. </li>
                                <li> vii. The authorized officer of the Company shall check and verify that:-</li>
                                <ul>
                                    <li>information available
                                        in the picture of document is matching with the information entered by
                                        authorized officer in CAF.</li>
                                    <li> live photograph of the customer matches with the photo available in
                                        the document.; and</li>
                                    <li> all of the necessary details in CAF including mandatory field are
                                        filled
                                        properly.</li>
                                </ul>

                                <li>
                                    viii. On Successful verification, the CAF shall be digitally signed by authorized
                                    officer of the
                                    Company who will Takea print of CAF, get signatures/thumb-impression of customer at
                                    appropriate
                                    place, then scan and upload the same in system. Original hard copy may be returned
                                    to the customer.
                                </li>
                            </ul>
</div>


<div class="interes mt-3">
    <h5>12. Monitoring of Transactions</h5>
    <p class="mt-2">Ongoing monitoring is an essential element of effective KYC procedures. The Company shall
                                undertake
                                on-going due diligence of customers to ensure that their transactions are consistent
                                with their
                                knowledge about the customers, customers’ business and risk profile; and the source of
                                funds.</p>
</div>

<div class="interes mt-3">
    <h5>13. Reporting to Financial Intelligence Unit – India</h5>
<div class="interest-h mt-3">
<ul>
                                <li>i. In accordance with the requirements under Prevention of Money Laundering Act
                                    (PMLA), the Company
                                    shall furnish the following reports, as and when required, to the Director,
                                    Financial Intelligence
                                    Unit-India (FIU- IND)
                                </li>
                                <ul>
                                    <li>Cash transaction report (CTR)/Counterfeit Currency Report (CCR) - All such cash
                                        transactions
                                        where forged or counterfeit Indian currency notes of bank notes have been used
                                        as genuine as
                                        Counterfeit Currency Report (CCR) for each month shall be submitted to FIU-IND
                                        by 15th of the
                                        succeeding month. While filing CTR, details of individual transactions below
                                        Rupees Fifty Thousand
                                        need not be furnished
                                    </li>
                                    <li>Suspicious Transactions Reporting (STR)- The Company shall endeavor to put in
                                        place automated
                                        systems for monitoring transactions to identify potentially suspicious activity.
                                        Such triggers will
                                        be investigated and any suspicious activity will be reported to Financial
                                        Intelligence Unit-India
                                        (FIU-IND).
                                    </li>

                                </ul>
                                <p> The Company shall file the Suspicious Transaction Report (STR) to FIU-IND within 7
                                    days of arriving
                                    at a conclusion that any transaction, whether cash or non-cash, or a series of
                                    transactions
                                    integrally connected are of suspicious nature. However, in accordance with the
                                    regulatory
                                    requirements, the Company will not put any restriction on operations in the accounts
                                    where an STR
                                    has been filed.
                                </p>
                            </ul>

                            <p class="mt-3"><strong>ii. Confidentiality and Prohibition against disclosing Suspicious Activity
                                    Investigations and Reports-</strong> The Company shall maintain utmost
                                confidentiality in investigating suspicious activities and while
                                reporting Counterfeit Currency Report (CCR)/ Suspicious Transactions Report (STR) to the
                                Financial
                                Intelligence Unit-India (FIU-IND)/ higher authorities. However, the Company may share
                                the
                                information pertaining to the customers with the statutory/ regulatory bodies and other
                                organizations such as banks, credit bureaus, income tax authorities, local government
                                authorities
                                etc.</p>
</div>
</div>

<div class="interest-h mt-3">
    <h5>14. Sharing KYC information with Central KYC Records Registry</h5>

    <ul class="mt-3">
                                <li>i. In terms of provision of Rule 9(1A) of the PML Rules, the Company shall capture
                                    customer’s KYC
                                    records and upload onto CKYCR within 10 days of commencement of an account-based
                                    relationship with
                                    the customer as per the Operational Guidelines for uploading the KYC data issued
                                    CERSAI.</li>
                                <li> ii. The Company shall capture the KYC information for sharing with the Central KYC
                                    Records Registry
                                    (C-KYCR)in the manner as prescribed in the Rules as per the prescribed KYC templates
                                    for
                                    ‘individuals’ and ‘Legal Entities’ as applicable. Further, the Company shall upload
                                    the KYC data
                                    pertaining to all types of prescribed accounts with Central KYC Records Registry
                                    (CKYCR), as and
                                    when required, in terms of the provisions of the Rules.</li>
                            </ul>
</div>

<div class="interest-h mt-3">
    <h5>15. Independent Evaluation</h5>
    <p class="mt-2">To provide reasonable assurance that its KYC and AML procedures are functioning
                                effectively, an audit
                                of its KYC and AML processes will covered under Internal Audit of the Company. The audit
                                findings
                                and compliance thereof will be put up before the Audit Committee of the Board.
                            </p>
</div>


        <div class="interest-h mt-3">
            <h5>16. Responsibilities of Senior Management</h5>

            <ul class="mt-3">
                                <li><strong>Designated Director:</strong> The Company shall nominate a “Designated
                                    Director” to ensure compliance with
                                    the obligations prescribed by the Act and the Rules thereunder. The “Designated
                                    Director” can be a
                                    person who holds the position of senior management or equivalent. However, it shall
                                    be ensured that
                                    the Principal Officer is not nominated as the “Designated Director”.</li>

                                <li><strong>Principal Officer:</strong> An official (having knowledge, sufficient
                                    independence, authority, time and
                                    resources to manage and mitigate the AML risks of the business) shall be designated
                                    as the Principal
                                    Officer of the Company. The Principal Officer shall be responsible for ensuring
                                    compliance,
                                    monitoring transactions, and sharing and reporting information as required under the
                                    law/
                                    regulations.
                                </li>
                                <li><strong> Key Responsibilities of the Senior Management</strong>
                                    <ul>
                                        <li>Ensuring overall compliance with regulatory guidelines on KYC/ AML issued
                                            from time to time and
                                            obligations under Act and Rules.</li>
                                        <li>Proper implementation of the company’s KYC &amp; AML policy and procedures.</li>
                                    </ul>
                                </li>
                            </ul>
        </div>

        <div class="interest-h mt-3">
            <section>
                            <h5>17. Record Management</h5>
                            <ul class="mt-3">
                                <li>i. The Company shall introduce a system of maintaining proper record of transactions
                                    required under
                                    Prevention of Money Laundering Act (PMLA) as mentioned below:
                                    <ul>
                                        <li> a) All cash transactions where forged or counterfeit currency notes or bank
                                            notes have been used as
                                            genuine and where any forgery of a valuable security or a document has taken
                                            place facilitating the
                                            transactions.</li>
                                        <li> b) All suspicious transactions whether or not made in cash.</li>
                                        <li> c) Records pertaining to identification of the customer and his/her
                                            address; and</li>
                                        <li> d) Should allow data to be retrieved easily and quickly whenever required
                                            or when requested by the
                                            competent authorities.</li>
                                    </ul>
                                </li>
                                <li> ii. For maintenance, preservation and reporting of customer account information,
                                    with reference to
                                    provisions of PML Act and Rules. The Company shall,
                                    <ul>
                                        <li> a) maintain for at least 5 years from the date of transaction between the
                                            Company and the client,
                                            both domestic and international.</li>
                                        <li> b) maintain for at least 5 years from the date of transaction between the
                                            Company and the client,
                                            all necessary records of transactions so as to permit reconstruction of
                                            individual transactions,
                                            including the following:
                                            <ul>
                                                <li>(i) the nature of the transactions.</li>
                                                <li>(ii) the amount of the transaction and the currency in which it was
                                                    denominated.</li>
                                                <li> (iii) the date on which the transaction was conducted, and</li>
                                                <li>(iv) the parties to the transaction.</li>
                                            </ul>

                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </section>
        </div>


        <div class="interest-h mt-3">
            <h5>18. Hiring of Employees, their Training and Education of Customers</h5>
            <ul class="mt-3">
                                <li>i. Hiring of Employees and Employee training- Adequate screening mechanism as an
                                    integral part of
                                    their personnel recruitment/hiring process shall be put in place. On-going employee
                                    training
                                    programmed shall be put in place.</li>
                                <li> ii. The Company shall endeavor to ensure that the staff dealing with / being
                                    deployed for
                                    KYC/AML/CFT matters have: high integrity and ethical standards, good understanding
                                    of extant
                                    KYC/AML/CFT standards, effective communication skills and ability to keep up with
                                    the changing
                                    KYC/AML/CFT landscape, nationally and internationally. The Company shall also strive
                                    to develop an
                                    environment which fosters open communication and high integrity amongst the staff.
                                </li>
                                <li>
                                    iii. On-going employee training programme shall be put in place so that the members
                                    of staff are
                                    adequately trained in KYC/AML/CFT policy. The focus of the training shall be
                                    different for frontline
                                    staff, compliance staff and staff dealing with new customers. The front desk staff
                                    shall be
                                    specially trained to handle issues arising from lack of customer education. Proper
                                    staffing of the
                                    audit function with persons adequately trained and well-versed in KYC/AML/CFT
                                    policies of the
                                    Company, regulation and related issues shall be ensured.</li>
                                <li> iv. Implementation of KYC Procedures requires the Company to seek information which
                                    may be of
                                    personal nature or which has hitherto never been called for. This can sometimes lead
                                    to a lot of
                                    questioning by the customer as to the motive and purpose of collecting such
                                    information. To meet
                                    such a situation, it is necessary that the customers are educated and apprised about
                                    the sanctity
                                    and objectives of KYC procedures so that the customers do not feel hesitant or have
                                    any reservation
                                    while passing on the information to the Company.
                                </li>
                            </ul>
        </div>


        <div class="interest-h mt-3">
            <h5>19. Secrecy Obligations and Sharing of Information:</h5>
            <ul class="mt-3">
                                <li>The Company shall maintain secrecy regarding the customer information which arises
                                    out of the
                                    contractual relationship between the Company and customer.</li>
                                <li>Information collected from customers for the purpose of opening of account shall be
                                    treated as
                                    confidential and details thereof shall not be divulged for the purpose of cross
                                    selling, or for any
                                    other purpose without the express permission of the customer.</li>
                                <li> While considering the requests for data/information from Government and other
                                    agencies, the
                                    Company shall satisfy themselves that the information being sought is not of such a
                                    nature as will
                                    violate the provisions of the laws relating to secrecy in the transactions.</li>
                                <li><strong>The exceptions to the said rule shall be as under:</strong>
                                    <ul>
                                        <li>Where disclosure is under compulsion of law</li>
                                        <li>Where there is a duty to the public to disclose,</li>
                                        <li>The interest of the Company requires disclosure and</li>
                                        <li>Where the disclosure is made with the express or implied consent of the
                                            customer</li>
                                    </ul>
                                </li>
                            </ul>
        </div>
            </div>
        </div>
    </section>
</main>
<?php include 'includes/footer.php'; ?>
    