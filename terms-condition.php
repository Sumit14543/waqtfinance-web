<?php
$page_title = "Terms & Conditions | Borrower Agreement | Waqt Finance";
$meta_description = "Review the official Waqt Finance Terms & Conditions covering loan eligibility, borrower obligations, privacy practices, and platform user agreements.";
$meta_keywords = "terms and conditions, user agreement, waqt finance rules, legal terms India";
$canonical_url = "https://waqtfinance.com/terms-condition";

include 'includes/header.php';
include 'includes/main-header.php';
?>

<style>
  .wf-legal-page {
    background-color: #f8fafc !important;
    font-family: 'Inter', sans-serif !important;
    padding: 60px 0 120px 0 !important; /* Generous bottom padding to prevent touching the CTA strip */
  }

  .wf-legal-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 48px;
    align-items: start;
    box-sizing: border-box;
  }

  /* Left Sidebar Index */
  .wf-legal-sidebar {
    position: sticky;
    top: 100px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 24px;
    max-height: calc(80vh - 120px);
    overflow-y: auto;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  }

  .wf-legal-sidebar h5 {
    font-size: 14px;
    font-weight: 800;
    color: #0f2742;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 16px;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 10px;
  }

  .wf-legal-sidebar nav {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .wf-sidebar-link {
    font-size: 13.5px;
    font-weight: 600;
    color: #64748b !important;
    text-decoration: none !important;
    padding: 10px 14px;
    border-radius: 8px;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    border-left: 3px solid transparent;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .wf-sidebar-link i {
    font-size: 14px;
    width: 20px;
    text-align: center;
    color: #94a3b8;
    transition: all 0.2s ease;
  }

  .wf-sidebar-link:hover {
    color: #18529c !important;
    background: #f1f5f9;
    transform: translateX(4px);
  }

  .wf-sidebar-link:hover i {
    color: #18529c;
  }

  .wf-sidebar-link.active {
    color: #18529c !important;
    background: #eef2ff;
    border-left-color: #18529c;
    box-shadow: 0 2px 8px rgba(24, 82, 156, 0.08);
  }

  .wf-sidebar-link.active i {
    color: #18529c;
  }

  /* Right Content Panels */
  .wf-legal-content {
    display: flex;
    flex-direction: column;
    gap: 32px;
  }

  .wf-legal-intro {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 32px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    margin-bottom: 12px;
  }

  .wf-legal-intro p {
    font-size: 15px;
    color: #334155;
    line-height: 1.7;
    margin-bottom: 16px;
  }

  .wf-legal-intro p:last-child {
    margin-bottom: 0;
  }

  .wf-legal-card {
    scroll-margin-top: 110px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 32px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    transition: box-shadow 0.3s ease;
  }

  .wf-legal-card:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
  }

  .wf-legal-card h3 {
    font-size: 18px !important;
    font-weight: 800 !important;
    color: #0f2742 !important;
    margin: 0 0 16px 0 !important;
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 12px;
  }

  .wf-legal-card p {
    font-size: 14.5px;
    color: #475569;
    line-height: 1.7;
    margin-bottom: 16px;
  }

  .wf-legal-card p:last-child {
    margin-bottom: 0;
  }

  .wf-legal-card ul {
    list-style: none !important;
    padding: 0 !important;
    margin: 16px 0 !important;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .wf-legal-card ul li {
    font-size: 14.5px;
    color: #475569;
    line-height: 1.6;
    padding-left: 24px;
    position: relative;
  }

  .wf-legal-card ul li::before {
    content: "•";
    position: absolute;
    left: 8px;
    color: #18529c;
    font-weight: bold;
    font-size: 18px;
    line-height: 1;
    top: 2px;
  }

  .wf-legal-card ul li b {
    color: #0f2742;
    font-weight: 700;
  }

  /* Thin custom scrollbar for sidebar */
  .wf-legal-sidebar::-webkit-scrollbar {
    width: 5px;
  }

  .wf-legal-sidebar::-webkit-scrollbar-track {
    background: transparent;
  }

  .wf-legal-sidebar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
  }

  .wf-legal-sidebar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
  }

  /* Responsive styling overrides */
  @media (max-width: 991px) {
    .wf-legal-container {
      grid-template-columns: 1fr;
      gap: 32px;
    }

    .wf-legal-sidebar {
      display: none; /* Hide sidebar on tablet & mobile */
    }
  }

  @media (max-width: 768px) {
    .wf-legal-intro, .wf-legal-card {
      padding: 24px;
    }
  }
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
  @media (max-width: 768px) {
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

<section class="wf-policy-hero">
    <div class="container">
        <div class="wf-policy-hero-content">
            <span class="wf-policy-badge"><i class="fa-solid fa-gavel"></i> Know the Rules</span>
            <h1>Terms & Condition</h1>
            <p>As modified by the Information Technology Act of 2000, this document is an electronic record according to applicable regulations, the amended requirements for electronic records in several legislation, and the Act itself.</p>
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
            <a href="terms-condition" class="wf-policy-tab-btn active">Terms & Conditions</a>
            <a href="refund-cancellation" class="wf-policy-tab-btn">Refund Policy</a>
        </div>
    </div>
</div>

<div class="wf-legal-page" id="Condition">
    <div class="wf-legal-container">
        
        <!-- Left Sidebar Index -->
        <aside class="wf-legal-sidebar">
            <h2>Index</h2>
            <nav>
                <a href="#sec-1" class="wf-sidebar-link active"><i class="fa-solid fa-book-bookmark"></i> 1. Definitions</a>
                <a href="#sec-2" class="wf-sidebar-link"><i class="fa-solid fa-hand-holding-dollar"></i> 2. Services</a>
                <a href="#sec-3" class="wf-sidebar-link"><i class="fa-solid fa-user-plus"></i> 3. Registration</a>
                <a href="#sec-4" class="wf-sidebar-link"><i class="fa-solid fa-user-check"></i> 4. Eligibility</a>
                <a href="#sec-5" class="wf-sidebar-link"><i class="fa-solid fa-shield-halved"></i> 5. Security</a>
                <a href="#sec-6" class="wf-sidebar-link"><i class="fa-solid fa-key"></i> 6. License & Access</a>
                <a href="#sec-7" class="wf-sidebar-link"><i class="fa-solid fa-envelope-open-text"></i> 7. Communications</a>
                <a href="#sec-8" class="wf-sidebar-link"><i class="fa-solid fa-tags"></i> 8. Pricing</a>
                <a href="#sec-9" class="wf-sidebar-link"><i class="fa-solid fa-clipboard-list"></i> 9. User Obligations</a>
                <a href="#sec-10" class="wf-sidebar-link"><i class="fa-solid fa-triangle-exclamation"></i> 10. Disclaimers</a>
                <a href="#sec-11" class="wf-sidebar-link"><i class="fa-solid fa-comments"></i> 11. Feedback</a>
                <a href="#sec-12" class="wf-sidebar-link"><i class="fa-solid fa-copyright"></i> 12. Copyright</a>
                <a href="#sec-13" class="wf-sidebar-link"><i class="fa-solid fa-handshake-angle"></i> 13. Indemnification</a>
                <a href="#sec-14" class="wf-sidebar-link"><i class="fa-solid fa-user-slash"></i> 14. Termination</a>
                <a href="#sec-15" class="wf-sidebar-link"><i class="fa-solid fa-thumbs-up"></i> 15. Consent</a>
                <a href="#sec-16" class="wf-sidebar-link"><i class="fa-solid fa-circle-nodes"></i> 16. Third-Party Info</a>
                <a href="#sec-17" class="wf-sidebar-link"><i class="fa-solid fa-scale-balanced"></i> 17. Obedience to Laws</a>
                <a href="#sec-18" class="wf-sidebar-link"><i class="fa-solid fa-gavel"></i> 18. Dispute Resolution</a>
                <a href="#sec-19" class="wf-sidebar-link"><i class="fa-solid fa-lock"></i> 19. Privacy</a>
                <a href="#sec-20" class="wf-sidebar-link"><i class="fa-solid fa-folder-open"></i> 20. Miscellaneous</a>
                <a href="#sec-21" class="wf-sidebar-link"><i class="fa-solid fa-headset"></i> 21. Get in Touch</a>
            </nav>
        </aside>

        <!-- Right Legal Content -->
        <div class="wf-legal-content">
            <div class="wf-legal-intro">
                <p>As modified by the Information Technology Act of 2000, this document is an electronic record according to applicable regulations, the amended requirements for electronic records in several legislation, and the Act itself. This electronic document was created by a computer system and does not require digital or physical signatures.</p>
                <p>The Information Technology (Intermediaries Guidelines) Rules, 2011, Rule 3 (1), which requires the posting of rules, regulations, privacy policies, and Terms of Use for access or usage of the domain name www.waqtfinance.com and related applications, is followed in its publication.</p>
                <p>Kindly carefully read these terms and conditions ("terms"). You acknowledge and agree to be bound by these terms by clicking "I accept" or "I agree," or by downloading, installing, or using the services. Please refrain from clicking "I accept" or "I agree," when downloading, installing, or using the services if you do not agree.</p>
            </div>

            <div id="sec-1" class="wf-legal-card">
                <h2>1. DEFINITIONS</h2>
                <ul>
                    <li><b>WAQT FINANCE PVT. LTD</b> will be referred to as "Company," "we," or "us".</li>
                    <li>The terms "Customer(s)," "you," or "End-Users" designate anyone who uses, downloads, accesses, or views the Platform and its contents.</li>
                    <li>"Loan" refers to the loan that you could ask for via the Platform and that the LENDER approves and grants, as long as you abide by the terms and conditions of the loan agreement.</li>
                    <li>The term "Loan Agreement" refers to the contract that is signed by the LENDER and the Customer(s) to issue the Loan, whether it is done so electronically or in physical form, depending on the circumstances.</li>
                    <li>"Outstanding Amount(s)" refers to the loan balance, interest, and other amounts that you owe the lender on the appropriate due date or dates.</li>
                    <li>"Platform" refers to the Website as a whole.</li>
                    <li>"Services" will relate to the lending, approving, and awarding of either short- or long-term loans by the LENDER via the Platform.</li>
                    <li>Social networking sites like Facebook, LinkedIn, and others of a similar nature are referred to as "Third Party Platforms".</li>
                    <li>Any information, documents, materials, or data that users send to the company before or during using the services is referred to as "User Data".</li>
                    <li>"Website" refers to the website that the company manages and runs to provide services: www.waqtfinance.com.</li>
                </ul>
            </div>

            <div id="sec-2" class="wf-legal-card">
                <h2>2. SERVICES</h2>
                <p>If you meet the requirements listed on the website for eligibility, you can apply for a loan through the services offered by WAQT FINANCE PVT. LTD.. You acknowledge that for the LENDER to approve the loan, the Company must gather, authenticate, track your location, verify, and confirm the User Data, papers, and details. You must register with the Company by logging in with your Third-Party Platforms ("User Account") to use the Services. You have to share and upload the User Data on the Application Form during the application procedure.</p>
                <p>Personal information that can be found in User Data includes your name, email address, gender, date of birth, mobile number, passwords, photograph, and information about your phone. It also includes information from Third-Party Platforms, financial information from Credit Information Companies, bank documents, salary slips, bank statements, PAN cards, bank account numbers, and other pertinent details ("Personal Information"). </p>
                <p>You pledge to always submit accurate, complete, and correct personal information. You grant us permission to import your information and Personal Information that is scattered across Third-Party Platforms to authenticate or verify as part of the Services. You recognize and agree that we may periodically ask Third-Party Platforms for changes to such Personal Information and that we may get such updated information from them. The Company will only act on your express instructions or agreement and will not exercise its discretion in any transactions carried out on your behalf. Additionally, you give the Company permission to periodically choose which Credit Information Companies to get your credit information report from.</p>
                <p>The Company will process the User Data and other documents and details after you check and upload them in the Application Form. If you satisfy the eligibility requirements and other terms specified by the LENDER for loan sanctioning, the LENDER may approve the loan after the Company has finished verifying the documents. The ECS/NACH mandate form, check, or any other document that the lender may demand must then be filled out and uploaded. The actual paperwork, including signatures on documents needed for loan processing and sanctioning, may be gathered by the Company. The LENDER will disburse the loan by the terms and conditions of the Loan Agreement after the Company has collected the necessary documentation. Within the allotted 48 hours, the approved loan will be promptly deposited into your bank account by electronic transfer.</p>
                <p>The Outstanding Amount(s) must be repaid to the LENDER within the deadline(s) specified on the Application Form. You understand and agree that until you fulfil your obligations to pay the LENDER the Outstanding Amount(s), the Company reserves the right to track your location ("Track") while the Services are being provided. If you stop using our services, you will still be liable for the Outstanding Amount(s) and will not be released from this obligation. You recognize and agree that, while using the Services, you will be fully accountable for any activity that takes place under your User Account. You agree that the Company will not be held accountable or liable for any disputes, claims, or losses resulting from the use or abuse of the Services. You alone are in charge of protecting the privacy of your User Account and any other connected activity under your User Account while using the Services. Without having to provide a reason, the Company retains the right to approve or refuse your registration for the Services.</p>
                <p>You recognize and agree that the functionality of the electronic devices and internet connection you use to access the Platform is entirely your responsibility. The availability of hardware, software requirements, an internet connection, and other characteristics and specifications that may occasionally be needed are all prerequisites for the Platform's or the Services' functionality on your electronic device.The Company keeps a record of the User Data you provide during registration for your convenience. To use or access the Platform, you do not need to log into your User Account each time. By agreeing to these Terms, you understand and agree that we may track, retrieve, and utilize User Data—including, but not limited to, your Personal Information—for authentication and any updates about your login information.</p>
            </div>

            <div id="sec-3" class="wf-legal-card">
                <h2>3. REGISTRATION</h2>
                <p>Users may sign up with any email service provider, including but not limited to Gmail, by providing their email address. Add the below details in the application form:</p>
                <ul>
                    <li><b>Name</b></li>
                    <li><b>Unique login name</b></li>
                    <li><b>Email address</b></li>
                    <li><b>Gender</b></li>
                    <li><b>Date of birth</b></li>
                    <li><b>Mobile number</b></li>
                    <li><b>Password</b></li>
                    <li><b>Job kind and location</b></li>
                    <li><b>Pay stub or other evidence of income</b></li>
                    <li><b>PAN card information</b></li>
                    <li><b>Bank statements</b></li>
                </ul>
                <p>Users can also link to their Google Plus or Facebook accounts, among other social networking platforms. For communications about their online purchases, users are required to maintain the accuracy and correctness of their account and registration information.</p>
                <p>It is the users' responsibility to keep their account information private and to take full responsibility for any actions made through it. <b>WAQT FINANCE PVT. LTD.</b> disclaims any liability for any actions taken by the user or any other party as a result of improper management of the user's account. If their accounts are accessed by unauthorized parties, users must contact us. It is forbidden for users to try to access or change any account that they do not own.</p>
            </div>

            <div id="sec-4" class="wf-legal-card">
                <h2>4. ELIGIBILITY</h2>
                <p>Users who are incapable of entering into contracts, such as children and insolvent individuals who have not been discharged, are not permitted to access the <b>WAQT FINANCE PVT. LTD.</b> website. On the other hand, under the supervision of a parent or legal guardian who accepts to be bound by these Terms of Use, you may use the Site if the client is 13 to 18 age. If Finance Private Limited determines or is informed that you are a minor, we retain the right to cancel or reject your registration, as well as to prevent you from accessing the Site.</p>
            </div>

            <div id="sec-5" class="wf-legal-card">
                <h2>5. SECURITY</h2>
                <p>The <b>WAQT FINANCE PVT. LTD.</b> website offers safe and secure transaction processing. Encrypting user data during transactions helps to avoid accidental disclosure to outside parties. Credit and debit card information is not received, stored, or retained by the Company in any way.</p>
            </div>

            <div id="sec-6" class="wf-legal-card">
                <h2>6. LICENSE AND ACCESS</h2>
                <p>You are granted a limited sublicense by <b>WAQT FINANCE PVT. LTD.</b> to access and use the Site for personal use. However, you are not permitted to download (except from page caching) or alter it, in whole or in part, without the Company's prior written approval. This limited sublicense excludes the following: downloading or copying information for the benefit of another merchant; using data mining, robots, or similar data gathering and extraction tools; reselling or commercial use of the Site or its contents; collecting and using product listings, descriptions, or prices; and using the Site or its contents in any other way.</p>
                <p>Without the company's prior written consent, no portion of the site may be copied, duplicated, sold, resold, visited, or used in any other way for commercial gain. Without the Company's and its affiliates' prior written authorization, you are not permitted to frame or use framing methods to enclose any trademark, logo, or other proprietary material (including images, text, page layout, or form) of the Mobile Application. Without the company's express written agreement, you are not permitted to use any Meta tags or other "hidden text" that makes use of its name or trademarks.</p>
                <p>You agree not to use hacking, password mining, or any other illegal methods to attempt to obtain unauthorized access to any part of the site, any feature, server, computer, or network connected to the site, or any of the services provided on or through the site. Additionally, you commit to not hosting, displaying, uploading, changing, publishing, transmitting, updating, or sharing any information that:</p>
                <ul>
                    <li><b>WAQT FINANCE PVT. LTD.</b> is owned by someone else and over which you have no legal claim.</li>
                    <li>Is egregiously offensive, intimidating, blasphemous, insulting, obscene, pornographic, pedophilic, libelous, invading another person's privacy, hateful, offensive to certain races or ethnicities, derogatory, or otherwise illegal in any way.</li>
                    <li>Neglects minors in any manner.</li>
                    <li>Violates any intellectual property rights, including patents, trademarks, copyrights, and others.</li>
                    <li>Breaches all current laws.</li>
                    <li>Deceives or misleads the recipient regarding the source of these messages or sends any content that is blatantly insulting or ominous.</li>
                    <li>Pretends to be someone else.</li>
                    <li>Includes computer code, files, or programs intended to disrupt, destroy, or restrict the operation of any computer resource, such as software viruses.</li>
                    <li>Endangers any nation's unity, integrity, defence, security, or sovereignty; jeopardizes friendly relations with other nations; subverts public order; encourages the commission of any crime that can be prosecuted; obstructs the investigation of any crime; or is offensive to any other country.</li>
                    <li>Is in any way deceptive or proven to be false.</li>
                </ul>
                <p>You accept responsibility for adhering to all applicable local, state, federal, and international laws and regulations by using the Site. If you violate these conditions, WAQT FINANCE PVT. LTD. may terminate your access to the Site and take legal action if it is deemed necessary.</p>
            </div>

            <div id="sec-7" class="wf-legal-card">
                <h2>7. COMMUNICATIONS</h2>
                <p>You agree to receive emails and promotional materials from the company and its affiliates. The Company may send SMS messages and make calls via third-party platforms. You consent to receive SMS communications from us at any time you think fit by using our website. This permission to be contacted may be used for marketing and promotional calls as well as calls for clarification.</p>
                <p>You have the option to unsubscribe from these communications and/or newsletters by contacting our customer support team or sending an email to customercare@waqtfinance.com with your request. In addition, Service Providers with whom we have contracted may get in touch with you to further our rights, responsibilities, and duties under this agreement and all other policies that we abide by. No other calls will be made; this contact will only be made to achieve these goals.</p>
            </div>

            <div id="sec-8" class="wf-legal-card">
                <h2>8. PRICING</h2>
                <p>It costs nothing to use this mobile application, and accessing the services and using the website is free of charge. Nonetheless, we maintain the right to change the fees for the provided services. Users will be informed of any changes of this nature, and it will be up to you to decide whether or not to continue using our services. As soon as these modifications are published on the website, they become operative. You accept the updated pricing conditions by using the services once the changes are posted.</p>
            </div>

            <div id="sec-9" class="wf-legal-card">
                <h2>9. USER OBLIGATIONS</h2>
                <p>You have specific obligations and limitations when using the WAQT FINANCE Pvt. Ltd</p>
                <ul>
                    <li><b>Activities Prohibited:</b> You pledge not to use WAQT FINANCE PVT. LTD for any illicit purposes, such as money laundering, embezzlement, or any other improper use of borrowed money. It is forbidden to utilize this site for any illegal purposes.</li>
                    <li><b>Intellectual Property and Content:</b> Without our prior written consent, you are not allowed to cut, duplicate, distribute, modify, recreate, reverse engineer, post, publish, transfer, or sell any data or software that you receive from WAQT FINANCE PVT. LTD. Restricted use could be permitted with express permission. It is forbidden to reproduce content in bulk, copy it for personal or professional use, or alter data or information on the website without authorization.</li>
                    <li><b>Limitations on Access:</b> By using the offered interface, you agree not to access or attempt to access WAQT FINANCE PVT. LTD, its materials, or services in any other way. Deep-linking, robots, spiders, and other automated tools, software, algorithms, or processes to view, obtain, duplicate, or track any part of the website or its content are forbidden. It is prohibited to gain unauthorized access to any part of the website, its features, or any other systems or networks that are linked to it.</li>
                    <li><b>Uploading and Posting Content:</b> If you submit or post data or information on WAQT FINANCE PVT. LTD, you are responsible for making sure that it is legal and not offensive. You promise not to:
                        <ul>
                            <li>Harass, intimidate, threaten, discredit, undermine, abrogate, dehumanize, or infringe upon the legal rights of others.</li>
                            <li>Participate in any conduct that impedes or ruins the ability to use the website or its services.</li>
                            <li>Falsely claim or otherwise misrepresent your association with any person or entity, or attempt to impersonate them.</li>
                            <li>Publish, post, or otherwise distribute any content that is deemed to be extremely harmful, intimidating, defamatory, obscene, pornographic, pedophilic, libelous, invading someone else's privacy, hateful, or offensive to any particular race or ethnicity, or that is connected to or promotes money laundering or gambling, among other illegal activities.</li>
                            <li>Do not upload any file to WAQT FINANCE PVT. LTD that violates another party's trademark, patent, or copyright.</li>
                            <li>Files containing viruses, damaged files, or any other similar software or programs that could harm the website's functionality or the computer of another person should not be uploaded or distributed.</li>
                            <li>Do not download any file that another user has posted that you know, or should know, isn't allowed to be disseminated lawfully in this way.</li>
                            <li>Do not investigate, scan, or test whether WAQT FINANCE PVT. LTD or any network connected to it is vulnerable, and make sure no security or authentication procedures are being broken.</li>
                            <li>Do not look up, trace, or attempt to trace any information on another user or website visitor.</li>
                            <li>Do not cause damage to WAQT FINANCE PVT. LTD, its servers, accounts, passwords, system resources, or any other network that is connected to or accessible through it. Disrupting or tampering with the website's security is forbidden.</li>
                            <li>Do not gather or keep information about other users regarding behaviour and activities that are forbidden by these terms.</li>
                            <li>Do not use WAQT FINANCE PVT. LTD or any of its materials or content for any illegal or forbidden purpose as stipulated in these terms of service.</li>
                            <li>Do not break any rules of behaviour or other regulations that apply to a certain service.</li>
                            <li>Do not break any current laws or regulations that are in effect, whether they are inside or outside of India.</li>
                            <li>Do not make WAQT FINANCE PVT. LTD liable for something or cause it to lose its internet service provider's or other suppliers' services, whether partially or fully.</li>
                        </ul>
                    </li>
                    <li><b>Promotion and Requests:</b> Without our prior explicit consent, you are not permitted to advertise to other users or solicit them to buy or sell any goods or services, including those linked to WAQT FINANCE PVT. LTD or related to us. These rules prohibit using any information from the website to harass, abuse, or cause harm to any person. They also prohibit contacting, advertising to, soliciting, or selling to someone other than us without first receiving our express approval.</li>
                    <li><b>Content Monitoring:</b> We are under no duty to keep an eye on the content that is published on WAQT FINANCE PVT. LTD. On the other hand, we reserve the right to delete or alter any information that allegedly breaches these terms of service or any applicable laws. The information you publish on the internet and in your private conversations is entirely your responsibility. We disclaim all responsibility and liability for any content posted, as well as for any claims, losses, or damages arising from the use or presentation of content on the website. You guarantee that the content you provide and the information it contains are within your legal rights and do not violate any third parties' intellectual property rights or contain defamatory, tortious, or illegal information.</li>
                    <li><b>Security Procedures:</b> It is forbidden for you to use hacking, password mining, or any other illegal methods to attempt to obtain unauthorized access to any part of WAQT FINANCE PVT. LTD, or any other systems or networks linked to it. It is strictly forbidden to take any action that jeopardizes the peace, stability, defence, security, or sovereignty of India or any other nation, ties of amity with other countries, public order, the prevention of criminal investigations, or the incitement of criminal activity.</li>
                    <li><b>Limitations on Usage:</b> It is expressly forbidden to use WAQT FINANCE PVT. LTD or any of its materials or content for any purpose that is against the law or forbidden by these terms of service. You promise not to take part in any activities that are against the terms of service, including any applicable extra conditions of the website contained herein or elsewhere, or that violate any current laws or regulations inside or outside of India.</li>
                    <li><b>Observance of the Law:</b> You pledge to abide by all current laws and rules, whether they are from inside or outside of India. This entails following any behaviour codes or other rules relevant to a certain service.</li>
                    <li><b>Content Prohibited:</b> You promise not to post, publish, or otherwise make incorrect, inaccurate, or misleading information available. It is not permitted to directly or indirectly offer, attempt to offer, trade, or attempt to trade anything restricted or forbidden by the terms of any applicable law, rule, regulation, or guideline.</li>
                    <li><b>Accountability and Duty:</b> You pledge not to hold WAQT FINANCE PVT. LTD legally responsible or cause the partial or complete loss of our internet service provider's or other suppliers' services. We reserve the right, at our discretion, to limit the number of emails or messages that a user may send to other users in 24 hours. Any information about you that we deem necessary to comply with a law, rule, or legitimate request from the government may be disclosed by us.</li>
                </ul>
                <p>By visiting or utilizing the <b>WAQT FINANCE PVT. LTD.</b> website, you accept these terms and conditions as a user and commit to following them. Your access to the website and its services may be suspended or terminated if you violate these terms.</p>
            </div>

            <div id="sec-10" class="wf-legal-card">
                <h2>10. DISCLAIMS ABOUT SERVICES</h2>
                <p>Neither <b>WAQT FINANCE PVT. LTD.</b> nor any other party shall be held liable for any mistakes or omissions. You understand that the services we offer you are the only thing we have to do with you. Regarding any reservations you make, we don't take ownership, rights, or interests in any way. We are not responsible for any delays or damages resulting from services rendered by third parties, nor do we have any influence over their calibre or delivery. Our accountability is restricted to the provision of service access; it does not encompass problems that may arise from how these services are carried out.</p>
            </div>

            <div id="sec-11" class="wf-legal-card">
                <h2>11. REVIEWS, FEEDBACK AND SUBMISSIONS</h2>
                <p>All evaluations, remarks, critiques, ideas, proposals, and other submissions (collectively, "Comments") made or offered on our website, or about its use, will exclusively belong to <b>WAQT FINANCE PVT. LTD.</b> You grant us all worldwide rights, titles, and interests in any copyrights and other intellectual property rights about the Comments that you submit. Consequently, we will be the only owners of the Comments, and we can use them for anything we want without asking permission or paying you anything.</p>
                <p>Any Comments you make may be used, reproduced, disclosed, altered, and transformed into derivative works, as well as published, displayed, and distributed for any commercial or non-commercial purpose by <b>WAQT FINANCE PVT. LTD.</b> We are not compelled to provide remuneration for any Comments that users submit, nor are we required to keep any Comments confidential.</p>
                <p>Through the submission of Comments, you confirm that they won't:</p>
                <ul>
                    <li>Violate any third-party rights, such as copyright, trademarks, privacy, or other proprietary or personal rights, or the terms and conditions of use</li>
                    <li>Include content that is defamatory, illegal, abusive, threatening, or pornographic</li>
                    <li>Comprise spam, mass emails, chain letters, political campaigns, software viruses, and commercial solicitations.</li>
                </ul>
                <p>We reserve the right, but are under no duty, to keep an eye on, modify, or delete any Comments that users publish on our website. You permit us to use your name about any feedback you send us. You consent to not using a fictitious email address, pretending to be someone else, or making any other misleading statements about the source of any Comments. The information contained in any Comments you make is entirely your responsibility. By posting comments, you agree to release and hold harmless <b>WAQT FINANCE PVT. LTD.</b> and its affiliates from any liability or claims arising from them. You assume all risk when relying on third-party comments on our website.</p>
            </div>

            <div id="sec-12" class="wf-legal-card">
                <h2>12. COPYRIGHT AND TRADEMARK THE COMPANY</h2>
                <p>All intellectual property rights related to the text, programs, products, processes, technology, photographs, content, and other items included on WAQT FINANCE PVT. LTD are retained by WAQT FINANCE PVT. LTD and its suppliers and licensors. Use of the website or access to it does not imply or provide any license to WAQT FINANCE PVT. LTD or any other third party's intellectual property rights. All intellectual property on the website, including copyright, is owned by or licensed to WAQT FINANCE PVT. LTD. Without the company's express written consent, it is completely forbidden to use any content on the website for any unauthorized purpose, including copying, storing, altering, or distributing any materials in whole or in part.</p>
                <p>The names, logos, design marks, trademarks, service marks, and slogans used on this website belong to WAQT FINANCE PVT. LTD or to one of its partners, affiliates, suppliers, or service providers. The owners of all other trademarks and service marks not held by the Company are the rightful owners. No permission is given to use any trademark, service mark, or logo in conjunction with the website's content. No one is permitted to use any name, logo, or mark in any way by accessing or using this website.</p>
            </div>

            <div id="sec-13" class="wf-legal-card">
                <h2>13. INDEMNIFICATION AND LIMITATION OF LIABILITY</h2>
                <p>If you breach or fail to perform any representation, warranty, covenant, or agreement made by you under these terms of service, you agree to indemnify, defend, and hold harmless <b>WAQT FINANCE PVT. LTD.</b> , including its affiliates, vendors, agents, and employees, from any losses, liabilities, claims, damages, demands, costs, and expenses, including legal fees and associated disbursements (and interest thereon). This indemnity obligation covers all third-party claims resulting from or connected to your use of our services, claims that third parties were harmed by the material you submitted, claims that you violated these terms of service, and claims that you violated another party's intellectual property rights, among other rights.</p>
                <p><b>WAQT FINANCE PVT. LTD.</b>, its officers, directors, workers, partners, and suppliers shall not be responsible for any punitive, consequential, incidental, or special damages to you or any other user. This covers—without being limited to—damages brought on by the loss of usage, data, or profits, regardless of whether such damages were anticipated or if we were informed that they would occur. This limitation of liability applies to any claim relating to or arising out of your use of or access to our website, services, or materials, regardless of the legal theory under which such damages are claimed, including breach of contract, warranty, negligence, tortious action, or any other claim.</p>
            </div>

            <div id="sec-14" class="wf-legal-card">
                <h2>14. TERMINATION</h2>
                <p>Until terminated by you or <b>WAQT FINANCE PVT. LTD.</b>, this User Agreement is in force. You may stop using the offered services at any time to end this Agreement. This Agreement may be terminated by WAQT FINANCE PVT. LTD. at any time, with or without warning, and you may not be able to use the services again straight away. The Company shall not be liable for any termination by the Company. Upon termination, the Company's rights to any submitted Comments and your agreement to indemnify the Company under the terms of this Agreement will remain in effect. The termination of this Agreement will not impact any liabilities incurred under the Agreement before the termination date, nor will it release you from any payment obligations for services rendered before the termination date.</p>
            </div>

            <div id="sec-15" class="wf-legal-card">
                <h2>15. CONSENT TO COMMUNICATIONS</h2>
                <p>By utilizing our services, you agree to receive communications from <b>WAQT FINANCE PVT. LTD.</b> via email, text messaging, automated phone dialing, voicemails that have already been recorded and automated phone calls. This permission covers correspondence from any service linked to your account. You understand that there may be costs associated with these conversations under your phone plan, and you are in control of paying those costs. We'll get in touch with you using the contact details you supply. You must explicitly modify your communication options with us if you would rather not receive such communications.</p>
            </div>

            <div id="sec-16" class="wf-legal-card">
                <h2>16. INFORMATION HOSTED BY THIRD PARTIES</h2>
                <p>Third-party information is hosted on our website. Although we have made every effort to assure the accuracy of this information, we cannot guarantee its veracity or authenticity. By using our site, you acknowledge that WAQT FINANCE PVT. LTD. is not responsible for any errors or fabrications in third-party information.</p>
            </div>

            <div id="sec-17" class="wf-legal-card">
                <h3>17. OBEDIENCE TO THE LAWS</h3>
                <p>Whether utilizing <b>WAQT FINANCE PVT. LTD.</b> services domestically or abroad, users are required to abide by all relevant regulations. Adherence to municipal, state, and federal laws is required.</p>
            </div>

            <div id="sec-18" class="wf-legal-card">
                <h3>18. DISPUTES AND APPLICABILITY</h3>
                <p>Every disagreement about rights granted, payment, reimbursements, and other claims, among other things, will be settled using a two-phase Alternative Dispute Resolution process.</p>
                <ul>
                    <li><b>Conflict Resolution:</b> If there is a disagreement, a single mediator who is an impartial third party will try to settle it first. The parties will mutually agree on the mediator before they choose them. A name for the lone mediator may be suggested by any party. The suggested mediator will be appointed if both parties approve of them. The Company retains the right to select the ultimate mediator if two suggested mediators are unable to agree. The parties are not required to abide by the mediator's decision.</li>
                    <li><b>Arbitration:</b> If neither party is satisfied with the outcome of mediation, arbitration will take place. The parties shall be bound by the arbitral ruling. Three people will make up the Arbitration Board: one appointed by each side, and a third nominated by the two appointed members together with their approval. The arbitration will take place in Gurugram, India, with all communication in English. The arbitrator's decision is final and enforceable against both parties.</li>
                </ul>
                <p>The matter will be brought to the courts in New Delhi, India if it cannot be settled through this two-step alternative dispute resolution process. This procedure guarantees that all disagreements are resolved amicably and quickly while upholding the neutrality and consent of both parties. You consent to the jurisdiction of the courts in New Delhi, India, as well as to this dispute resolution procedure by utilizing the services of <b>WAQT FINANCE PVT. LTD</b>.</p>
            </div>

            <div id="sec-19" class="wf-legal-card">
                <h3>19. PRIVACY</h3>
                <p>To make an informed decision about your personal information, we invite you to read the Privacy Policy and make use of the information it provides. Please be aware that some of the statements, data, content, and information you provide on the website (including photos) may reveal personal information about you, including your age, gender, nationality, and ethnic origin. You understand and concur that providing this kind of information is entirely voluntary. Furthermore, you grant us permission to access, store, and disclose any information you give us at any time while using the website. Further information about information disclosures to third parties can be found in our privacy policy. You accept the terms stated in the Privacy Policy by using our services.</p>
            </div>

            <div id="sec-20" class="wf-legal-card">
                <h3>20. MISCELLANEOUS PROVISIONS</h3>
                <ul>
                    <li><b>Whole Agreement:</b> The whole agreement and understanding between the User and <b>WAQT FINANCE PVT. LTD.</b> is contained in the terms and conditions listed below, as well as any additional or alternative terms that the User expressly agrees to. Any previous or contemporaneous talks, agreements, warranties, representations, and other communications between the parties about the services rendered are superseded by this agreement.</li>
                    <li><b>Release:</b> The right of any party to enforce a provision of this Agreement at a later date is unaffected by the failure of that party to do so at any point. Neither party's waiver of any behaviour or other violation of this agreement in one or more instances shall be interpreted as, or be deemed to be, a further or continuing waiver of any other breach or another breach of this Agreement. In addition to any rights and remedies at law or in justice, all rights and remedies under this Agreement are cumulative.</li>
                </ul>
            </div>

            <div id="sec-21" class="wf-legal-card">
                <h3>21. GET IN TOUCH</h3>
                <p>Please send us an email at <b>customercare@waqtfinance.com</b> if you have any questions concerning this Agreement, the website's policies, or your usage of the Service. We are available to help and answer any questions you may have about our offerings.</p>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const links = document.querySelectorAll(".wf-sidebar-link");
    const cards = document.querySelectorAll(".wf-legal-card");
    let currentActiveIndex = -1;

    function highlightActiveLink() {
        let index = cards.length;
        
        // Optimize active section detection when scrolled to the bottom of the page
        const isAtBottom = (window.innerHeight + window.scrollY) >= (document.documentElement.scrollHeight - 50);
        if (isAtBottom) {
            index = cards.length - 1;
        } else {
            while(--index && window.scrollY + 160 < cards[index].offsetTop) {}
        }
        
        // Only run update if active section has actually changed (prevents scroll thrashing)
        if (currentActiveIndex !== index) {
            currentActiveIndex = index;
            links.forEach((link) => link.classList.remove("active"));
            
            if(index >= 0 && index < links.length) {
                const activeLink = links[index];
                activeLink.classList.add("active");
                
                // Auto scroll sidebar container to keep active link in view
                activeLink.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    }

    highlightActiveLink();
    window.addEventListener("scroll", highlightActiveLink);
});
</script>

<?php include 'includes/footer.php' ?>