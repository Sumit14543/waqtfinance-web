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
</style>

    <section class="wf-policy-hero">
        <div class="container">
            <div class="wf-policy-hero-content">
                <span class="wf-policy-badge"><i class="fa-solid fa-user-shield"></i> Protecting Your Privacy</span>
                <h1>Privacy Policy</h1>
                <p>We value your privacy. Your personal information is safe with us and is used only to provide and improve our services. We never share your data without consent.</p>
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
            </div>
        </div>
    </div>

    <!-- Policy Document Content -->
    <section class="wf-policy-document-section" id="policy">
        <div class="container">
            <div class="wf-policy-paper">
                <h2>Privacy Policy</h2>
                <p>
                    As a Private Limited Company established by the Companies Act of 1956, we, <strong>WAQT FINANCE PVT. LTD</strong> (henceforth referred to as the "Company"), are represented by our members. Our members shall be assumed to include each of our respective legal heirs, representatives, administrators, permitted successors, and assigns unless the context of this declaration indicates otherwise. This policy's author assures you of our steadfast commitment to safeguarding your privacy and the private information you give to us. This privacy statement describes the www.waqtfinance.com website (henceforth referred to as the "Website") developed by WAQT FINANCE PVT. LTD.
                </p>
                <p>
                    So that we can keep providing you with services, we might collect and, in some situations, disclose information about you. To better protect your privacy, we provide this notice explaining our information collection and disclosure practices and the choices you have regarding the acquisition and use of your information. All terms that are capitalized going forward will signify what this agreement says. Furthermore, the headers in this document are only used to arrange the various sections of the agreement. Neither the user nor the author may deduce any meaning from the contents of this privacy policy by using the titles of the pages.
                </p>

                <h3>Definition</h3>
                <ul>
                    <li>In this policy, the terms "We", "Our", and "Us" refer to the creators and operators of Waqt Finance.</li>
                    <li>The terms "You", "Your", "Yourself", and "User" refer to natural or legal persons who use the website or mobile application.</li>
                    <li>"Personal Information" refers to any information that can be used to identify you personally that we may have from you.</li>
                    <li>"Third Parties" refers to any other website, company, or individual other than the User and the creator of this application.</li>
                    <li>This privacy policy explains how we handle your personal information to protect your privacy. We value your trust and are committed to safeguarding your data while providing excellent platform services.</li>
                </ul>
                <p>If you have any questions or concerns about this policy, please utilize the methods available on our website or mobile application to get in touch with us.</p>

                <h3>1. Information Collected</h3>
                <p>If you meet the requirements listed in the Website for eligibility, you can apply for a loan using the Services. You accept that <strong>WAQT FINANCE PVT. LTD</strong> (henceforth referred to as the "Company") shall gather and verify user data, documents, and details as may be necessary to authorize the Loan, as well as authenticate, track, and validate your whereabouts. You must register with the Company by logging in after providing us with your email address and mobile number to use the Services. You will need to share and upload the User Data on the Application Form during the application process. User data will include your personal information, which may include, but is not limited to, your financial information such as pay stubs, bank statements, PAN cards, Aadhar card details, bank account numbers, data from credit reference agencies, name, email address, gender, date of birth, mobile number, passwords, photograph, mobile phone information such as contact numbers, SMS, call logs, and browsing history, data and login credentials of third-party platforms if any, data needed for Know Your Customer (KYC) compliances, requirements, and other pertinent information ("Personal Information"). You guarantee that the personal data will always be true, correct, and comprehensive.</p>
                <p>
                    You grant us permission to import your information and Personal Information that is scattered over third-party platforms to authenticate or verify as part of the Services. You recognize and agree that we have the right to ask for updates on such Personal Information from time to time, and that we may also obtain such updated data from third-party platforms. The Company will only act on your express instructions or agreement and will not exercise its discretion in any transactions carried out on your behalf. Additionally, you give the Company permission to periodically choose which credit information businesses to get your credit information report from.
                </p>
                <p>
                    The Company will process the User Data and/or additional documents and details once you have verified and uploaded them into the Application Form. If the Company's verification of the documents is complete, you may be approved for the loan, provided that you meet the eligibility requirements and fulfil any further requirements. After that, you must complete and upload the ENACH/NACH/ECS mandate form, check, or any other necessary documents. Following document completion, the Loan will be disbursed by the Company by the terms and conditions outlined in the Loan Agreement. Within the allotted four working hours, the approved Loan will be distributed electronically right into the User's bank account. You must pay back the outstanding balances to the lender by the deadlines specified in the loan agreement or sanction letter. Your removal, uninstallation, or cessation of using our services will not absolve you of the duty to reimburse any outstanding sums.
                </p>
                <p>
                    <strong>WAQT FINANCE PVT. LTD</strong> is dedicated to protecting your personal information and keeping it private. You agree that your information will be collected, used, and disclosed by this privacy policy by using our services. Please use the channels on our website application to contact us with any questions or issues you may have about this policy or your personal information. We are committed to giving you the knowledgeable counsel and assistance you require to safely and effectively reach your financial goals.
                </p>

                <h3>2. External Links on the Website</h3>
                <p>Advertisements and links to other websites, apps, resources, or material may be found on the website. The external links that are supplied by individuals or businesses other than <strong>WAQT FINANCE PVT. LTD</strong> (hereinafter referred to as the "Company") on the Website / Mobile App are not under our control. You understand and accept that we have no control over any external websites, mobile apps, businesses, or individuals that may acquire or disclose your personal information. Furthermore, no advertisements, goods, or other content on or accessible via such third-party websites, mobile applications, or resources are endorsed by us.</p>
                <p>You further acknowledge and agree that we shall not be responsible for any loss or damage that you may suffer as a result of third-party websites, mobile apps, or other resources collecting and/or disclosing your Personal Information, or as a result of your reliance on the existence, completeness, or accuracy of any advertising, products, or other materials on, or accessible from, such websites or resources. These outside websites and resource providers might have privacy rules of their own that control how your Personal Information is gathered, stored, retained, and disclosed. To learn more about the procedures and handling of your Personal Information, we advise you to examine the privacy policies of any external websites or mobile applications that you access through links on our website.</p>
                <p>
                    Although <strong>WAQT FINANCE PVT. LTD</strong> is dedicated to safeguarding your privacy, we nonetheless advise you to exercise caution and due diligence when visiting outside websites. Please use the contact options on our website application to get in touch with us if you have any queries or issues regarding our privacy policies or the usage of external links.
                </p>

                <h3>3. Our Use of Your Information</h3>
                <p>When necessary, we will get in touch with you using the information you gave us when you registered. Please see our Terms of Service for further information regarding the type of communications that we receive. Furthermore, we might gather and retain sensitive personal information about you as well as your personal information for internal record-keeping needs. We use tracking data, including device IDs and IP addresses, to identify you and compile general demographic data.</p>
                <p>
                    We will give the information you disclosed to us and any information about you to the company we are purchased by or merged with, should that company decide to acquire us. In the event of a merger or acquisition, we will provide you with notice via email or by prominently posting a notice on the website or mobile application before the transfer of your data and its subsequent protection under a new privacy statement.
                </p>
                <p>We at <strong>WAQT FINANCE PVT. LTD</strong> are dedicated to protecting the privacy and security of your data. Please utilize the channels on our website or mobile application to contact us if you have any questions or issues about how we use your information.</p>

                <h3>4. Cookies</h3>
                <p><strong>WAQT FINANCE PVT. LTD</strong> (henceforth referred to as the "Company") employs "cookies" and other data collection tools on specific pages of our website. Cookies are little files that are stored on your computer's hard disk to provide personalized services and improve user experience. Our website has certain features that are only accessible with cookies enabled. They also help us provide content that is relevant to your interests. Cookies can be used to recognize users who have registered or logged in.</p>
                <p>Cookies may be used by outside vendors, such as Google, to display advertisements to you based on your usage of our website. If offered, you can visit these third parties' websites to choose not to have cookies used for interest-based advertising. Go to Google Ads Settings to disable the Double Click cookie, which is used by Google and its partners to display ads based on your interests.</p>
                <p>Additionally, Google Analytics Advertising is used on our website, enabling Google to gather information about visitors, including anonymous identifiers and Google advertising cookies. You can download and install the Google Analytics Opt-out Add-on from the given link if you would rather not have Google Analytics gather your data. We pledge to protect your privacy while offering a customized experience. Please use the contact information on our website or mobile application to get in touch with us if you have any queries about how we utilize cookies.</p>

                <h3>5. Confidentiality</h3>
                <p>Your information is treated as confidential at <strong>WAQT FINANCE PVT. LTD</strong> (hereinafter referred to as the "Company") and will not be shared with any outside parties unless required by law or to enable the full provision of our services on our website. Your personal information is never sold, shared, or rented by us to marketing firms or other businesses that send unsolicited emails. We shall follow our privacy policy and terms of service in all correspondence. Please use the channels on our website or mobile application to contact us with any questions.</p>

                <h3>6. Information Disclosed to Outside Parties</h3>
                <p>We are unable to ensure that your Personal Information will never be disclosed in ways that are not specified in this Privacy Policy due to the state of regulations. Although we use industry best practices to safeguard your privacy, we are unable to guarantee that any private communications or personally identifying information you provide will stay private.</p>
                
                <h4>External Service Companies</h4>
                <p>There may be optional services offered by outside service providers on our website. These providers' use of your information will be subject to their respective privacy rules if you choose to use these services, provide them with information, or permit them to gather information about you. To learn how these outside service providers handle your information, we advise you to read their privacy policies.</p>
                
                <h4>Additional Corporate Organizations</h4>
                <p>To improve your experience using our website/mobile app and related services worldwide, we may share your Personal Information with our parent company, subsidiaries, and affiliated companies. The only goal of this data exchange is to enhance your use of our services. Information accessed by these entities will be handled with a minimum of care equivalent to that which they offer for data gathered from their other members. To maintain service continuity, your information might be shared in the case of a merger, acquisition, or other combination with another business organization. As soon as any such changes take place, you will be informed.</p>
                
                <h4>Law and Order</h4>
                <p>To enforce the law, including those about fraud prevention, intellectual property rights, and other legal rights, we collaborate with law enforcement organizations and other parties. To investigate fraud, infringements on intellectual property, or other illegal acts that might put us or you in danger of legal repercussions, we reserve the right to share your Personal Information with law enforcement and government authorities as we, in our sole discretion, think necessary or appropriate. By publishing anything publicly accessible on our website, you agree to allow third parties to access and use the information you provide. Even though we work hard to protect your information, it's crucial to remember that anything you willingly provide online may be accessible to others. To keep informed about how your information is handled and safeguarded, we advise you to periodically review our Privacy Policy and use caution when sharing any personal information.</p>
                
                <p>Please use the contact options on our website or mobile application to get in touch with us if you have any questions or concerns about how we share your information with outside parties. To the best of our abilities, we are dedicated to protecting the privacy and security of your information.</p>

                <h3>7. Accessing, Examining, and Modifying Your Profile</h3>
                <p>You can check and amend the details you submitted when registering with <strong>WAQT FINANCE PVT. LTD</strong> (henceforth referred to as the "Company"), except your display name and login. You can email us at <a href="mailto:customercare@waqtfinance.com">customercare@waqtfinance.com</a> or use the option on the homepage of our website or mobile app to make adjustments.</p>
                <p>If you update any information, we could keep a copy of your prior information. Please write us at <a href="mailto:customercare@waqtfinance.com">customercare@waqtfinance.com</a> as soon as possible if you believe that any information we have on you is inaccurate or incomplete, or if you would like your profile to be removed so that others cannot see it. If any errors are discovered, they will be quickly fixed. Although we make every effort to comply with your requests, please be aware that some information might be kept on file for particular uses, like handling disputes, fixing issues, and upholding our terms and conditions. Additionally, some of your information may remain in our databases owing to technological and regulatory limitations, such as backup systems. It is therefore impossible to ensure that every piece of personally identifying information has been eliminated.</p>
                <p>Please use the contact options on our website if you have any more queries or worries about viewing or changing your profile.</p>

                <h3>8. Security</h3>
                <p>We at <strong>WAQT FINANCE PVT. LTD</strong> (henceforth referred to as the "Company") value your data highly and take precautions to guard it from loss and unwanted access. To protect Personal Information and other data we handle, we employ industry-standard security procedures. You understand and concur that any security breaches that occur outside of our regular security protocols are your responsibility and are at your own risk.</p>

                <h3>9. Indemnity</h3>
                <p>As a condition of using our website or mobile app, you undertake to indemnify and hold harmless <strong>WAQT FINANCE PVT. LTD</strong> (hereafter referred to as the "Company") against any disputes, lawsuits, or claims resulting from your disclosure of Personal Information to third parties. This covers all access to and usage of resources, apps, and websites operated by third parties. We disclaim all responsibility for any actions done by third parties concerning the Personal Information you have given them. It is at your own risk to utilize these third-party services.</p>

                <h3>10. Severability</h3>
                <p>Except as otherwise specifically stated or as indicated by the context of the agreement, each paragraph of this privacy policy shall be and remain independent of, separate from, and severable from all other paragraphs hereof. The remaining paragraphs of this privacy policy will not be impacted by the decision or declaration that one or more of the paragraphs are void.</p>

                <h3>11. Modification</h3>
                <p>Periodically, we may amend our privacy policy. Any modifications will be announced via email or on the home page of our website and mobile app. To keep informed about how we protect your information, please examine these changes. Examine all of your options before deciding on a financial decision because borrowing money might be expensive.</p>
            </div>
        </div>
    </section>

<?php include 'includes/footer.php' ?>