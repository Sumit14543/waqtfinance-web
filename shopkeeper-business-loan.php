<?php
$page_title = "Shopkeeper Business Loan Online in India | Waqt Finance";
$meta_description = "Boost retail shop inventory with quick shopkeeper business loans up to ₹10 Lakh. Collateral-free funding, flexible tenure & fast disbursal. Apply now!";
$meta_keywords = "shopkeeper business loan India, kirana store loan online, retail shop loan India, grocery store loan, small shop loan, shop loan online";
$canonical_url = "https://waqtfinance.com/shopkeeper-business-loan";
$meta_image = "assets/images/shopkeeper.webp";
$meta_image_alt = "Shopkeeper business loan in India";
$breadcrumb_label = "Shopkeeper Business Loan";

include 'includes/header.php';
?>
<style>
 /* Premium Hero Section Overrides */
    .prop-sec {
        height: 335px !important;
        overflow: hidden !important;
    }
    .prop-left {
        background: linear-gradient(135deg, #062b5d 0%, #18529c 100%) !important;
        padding: 20px 24px 20px clamp(20px, 6vw, 100px) !important;
        font-family: 'Inter', sans-serif !important;
    }
    .prop-left::after {
        background: #18529c !important;
    }
    .prop-right {
        height: 100% !important;
        padding: 0 !important;
    }
    .prop-right img {
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        top: 0 !important;
        object-fit: cover !important;
        object-position: center 20% !important;
        filter: none !important;
    }
    .prop-text {
        max-width: 540px !important;
        position: relative;
        z-index: 3;
    }
    .prop-text h1 {
        font-family: 'Inter', sans-serif !important;
        font-weight: 800 !important;
        font-size: clamp(22px, 2.5vw, 30px) !important;
        letter-spacing: -0.5px !important;
        line-height: 1.25 !important;
        margin-bottom: 12px !important;
    }
    .prop-text p {
        font-family: 'Inter', sans-serif !important;
        font-size: clamp(14px, 1.4vw, 15.5px) !important;
        color: rgba(255, 255, 255, 0.92) !important;
        line-height: 1.55 !important;
        margin-bottom: 20px !important;
    }

    /* Premium Cards Styling */
    .choose_card, .ui-card, .process_card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03) !important;
        padding: 28px !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-sizing: border-box !important;
    }

    .choose_card:hover, .ui-card:hover, .process_card:hover {
        transform: translateY(-4px) !important;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05), 0 4px 6px -2px rgba(0,0,0,0.03) !important;
        border-color: #cbd5e1 !important;
    }

    /* Headings and Paragraphs inside Cards */
    .choose_card h2.shop-sub-h, 
    .choose_card h3, 
    .process_card h3, 
    .ui-card h5,
    .why_wrap h2.shop-h,
    .invest {
        color: #0b213a !important;
        font-family: 'Inter', sans-serif !important;
        font-weight: 700 !important;
    }

    .choose_card p, .process_card p, .ui-card p {
        color: #475569 !important;
        font-family: 'Inter', sans-serif !important;
        font-size: 14px !important;
        line-height: 1.65 !important;
    }

    /* Premium Bullet List Styling */
    .choose_card ul {
        margin-top: 14px !important;
    }

    .choose_card ul li {
        position: relative !important;
        padding-left: 24px !important;
        margin-bottom: 10px !important;
        color: #475569 !important;
        font-size: 14px !important;
        font-family: 'Inter', sans-serif !important;
    }

    .choose_card ul li::before {
        content: "\2713" !important;
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        color: #10b981 !important;
        font-weight: 800 !important;
        font-size: 15px !important;
    }

    /* Hero & CTA Button Overrides */
    .prop-btn {
        background: #ffffff !important;
        color: #18529c !important;
        border-radius: 8px !important;
        padding: 12px 28px !important;
        font-weight: 700 !important;
        font-size: 15px !important;
        box-shadow: 0 4px 6px rgba(0,0,0,0.08) !important;
        font-family: 'Inter', sans-serif !important;
        display: inline-block !important;
    }

    .prop-btn:hover {
        transform: translateY(-2px) scale(1.02) !important;
        box-shadow: 0 10px 15px rgba(0,0,0,0.12) !important;
        background: #f8fafc !important;
    }

    .apply-btn {
        background: linear-gradient(135deg, #18529c 0%, #0b213a 100%) !important;
        color: #ffffff !important;
        border-radius: 30px !important;
        padding: 14px 44px !important;
        font-weight: 700 !important;
        font-family: 'Inter', sans-serif !important;
        display: inline-block !important;
        box-shadow: 0 4px 6px rgba(24, 82, 156, 0.15) !important;
        transition: all 0.3s ease !important;
        border: none !important;
        text-decoration: none !important;
    }

    .apply-btn:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 10px 15px rgba(24, 82, 156, 0.25) !important;
        background: linear-gradient(135deg, #0b213a 0%, #062b5d 100%) !important;
        color: #ffffff !important;
    }

    /* Process Card SVG Icons */
    .process_card .process_icon {
        color: #18529c !important;
        background: #f0f7ff !important;
        border-radius: 12px !important;
        width: 52px !important;
        height: 52px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin-bottom: 18px !important;
    }

    /* Custom Table Styling */
    .custom-table {
        font-family: 'Inter', sans-serif !important;
    }
    
    .custom-table thead th {
        background: #f8fafc !important;
        color: #0b213a !important;
        font-weight: 700 !important;
        font-size: 13.5px !important;
        border-bottom: 2px solid #e2e8f0 !important;
        padding: 12px 16px !important;
    }

    .custom-table tbody td {
        color: #475569 !important;
        font-size: 14px !important;
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 12px 16px !important;
    }

    /* Responsive adjustments */
    @media(max-width: 768px) {
        .prop-sec {
            background: linear-gradient(135deg, #062b5d 0%, #18529c 100%) !important;
        }
        .choose_wrap {
            grid-template-columns: 1fr !important;
        }
    }
</style>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "LoanOrCredit",
    "name": "Shopkeeper Business Loan",
    "url": "https://waqtfinance.com/shopkeeper-business-loan",
    "description": "Get easy shopkeeper business loans up to ₹10 lakh for Kirana and retail shops with quick approval, flexible eligibility, and fast disbursement.",
    "provider": {
        "@id": "https://waqtfinance.com/#organization"
    },
    "areaServed": {
        "@type": "Country",
        "name": "India"
    },
    "amount": {
        "@type": "MonetaryAmount",
        "currency": "INR",
        "minValue": 50000,
        "maxValue": 1000000
    },
    "loanTerm": "12-60 months",
    "interestRate": {
        "@type": "QuantitativeValue",
        "minValue": 18,
        "maxValue": 30,
        "unitText": "percent per annum"
    },
    "audience": {
        "@type": "BusinessAudience",
        "audienceType": "Small business owners and shopkeepers"
    }
}
</script>

<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        {
            "@type": "Question",
            "name": "What are the purposes for which I can get a loan via Waqt Finance?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Waqt Finance business loans can be used for business expansion, managing working capital needs, handling seasonal expenses, and covering short-term cash flow gaps."
            }
        },
        {
            "@type": "Question",
            "name": "Why should I choose a loan from Waqt Finance?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Waqt Finance offers quick loan approval, minimal documentation, flexible repayment options, and fast fund disbursal to help grow your business smoothly."
            }
        },
        {
            "@type": "Question",
            "name": "Is collateral required for a shop loan from Waqt Finance?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Most loans from Waqt Finance are unsecured, so collateral is generally not required, but it may depend on the loan amount and applicant profile."
            }
        },
        {
            "@type": "Question",
            "name": "What documents are required to apply for a loan from Waqt Finance?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "You need PAN card, address proof, last 6 months bank statements, and business proof like GST registration or Shop Act certificate. Additional documents may be required for higher loan amounts."
            }
        },
        {
            "@type": "Question",
            "name": "What happens if I pre-close my loan with Waqt Finance?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "If you pre-close your loan, you may need to pay foreclosure or prepayment charges as per your loan agreement terms."
            }
        },
        {
            "@type": "Question",
            "name": "Are there any additional charges apart from interest?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Yes, Waqt Finance may charge processing fees, documentation charges, and late payment penalties depending on the loan terms."
            }
        },
        {
            "@type": "Question",
            "name": "What is the eligibility for a business loan from Waqt Finance?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Your business should be active for at least 1 year with minimum monthly sales of around ₹2 lakh to qualify for a loan."
            }
        },
        {
            "@type": "Question",
            "name": "Will my credit score affect my loan approval at Waqt Finance?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Yes, a good credit score improves your chances of approval and may help you get better interest rates."
            }
        },
        {
            "@type": "Question",
            "name": "How can I speed up my loan approval with Waqt Finance?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "You can speed up approval by keeping all documents ready, providing correct details, and maintaining clean and consistent bank statements."
            }
        },
        {
            "@type": "Question",
            "name": "Can I apply for a loan for a supermarket or retail shop at Waqt Finance?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Yes, supermarkets, kirana stores, and other retail businesses are eligible to apply for business loans with Waqt Finance."
            }
        }
    ]
}
</script>

<?php include 'includes/main-header.php' ?>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js" defer></script>

<section class="prop-sec">
    <!-- Left -->
    <div class="prop-left">

        <div class="prop-text">

            <div class="prop-line"></div>

            <h1 style="font-size:30px">Shopkeeper Business Loan in India for Retail & Kirana Stores</h1>

            <p>
                Running a kirana store or retail shop and need funds for stock, renovation, or working capital? Apply Shop Loan Online with Waqt Finance and get a Shopkeeper Business Loan of up to &#8377;10 Lakh - unsecured, minimal paperwork, and a quick, fully digital approval process.
            </p>

            <div class="d-flex align-items-center flex-wrap gap-3 mt-2">
                <a href="business-loan-apply" class="prop-btn d-inline-block" style="padding: 10px 24px;">Apply Now</a>
                <a href="#eligibility" class="text-white" style="text-decoration: underline; font-weight: 600;">Check Eligibility &rarr;</a>
            </div>

        </div>

    </div>

    <!-- Right -->
    <div class="prop-right">
        <img src="assets/images/shopkeeper.webp" alt="Shopkeeper Business Loan for Retail and Kirana Stores">
    </div>
</section>

<!-- What is a Shopkeeper Business Loan Section (Stretched) -->
<section class="p-60 position-relative section-light" data-aos="fade-up" style="padding-top: 25px !important; padding-bottom: 30px !important;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <h2 class="text-center mb-4 fw-bold">What is a <span style="color: #18529c;">Shopkeeper Business Loan?</span></h2>
                <p class="text-muted" style="line-height: 1.8; font-size: 16px; margin-bottom: 20px;">A Shopkeeper Business Loan is an unsecured Business Loan for Shopkeepers, offering up to &#8377;10 Lakh to retail and kirana store owners who need funds without pledging property or business assets as collateral. Whether you run a grocery shop, a medical store, or a garment retail outlet, this Shop Loan is built around the specific, faster-moving needs of a small retail business rather than the paperwork-heavy process of a traditional bank loan.</p>
                <p class="text-muted" style="line-height: 1.8; font-size: 16px; margin-bottom: 20px;">Small retail businesses run on a different rhythm than large corporations - inventory needs shift with the season, cash moves daily, and a shop owner rarely has weeks to wait on a financing decision. A Shopkeeper Loan is structured around exactly that reality: a fully online application, minimal documentation, and quick approval, so you can act on a stock opportunity or renovation need without delay.</p>
                <p class="text-muted m-0" style="line-height: 1.8; font-size: 16px;">At Waqt Finance, this entire process - from application to disbursal - happens digitally. This is business financing sized specifically to what a retail shop actually needs, not a scaled-down version of a large corporate loan product.</p>
            </div>
        </div>
    </div>
</section>
<section class="why_wrap p-60 position-relative section-white" style="padding-top: 25px !important; padding-bottom: 30px !important;">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-10 text-center">
                <h2 class="shop-h fw-bold">Features & Benefits of <span style="color:#18529c">Shopkeeper Business Loan</span></h2>
                <p class="mt-3 text-muted">Here's what a Shopkeeper Business Loan from Waqt Finance actually offers.</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Card 1 -->
            <div class="col-lg-3 col-md-6">
                <div class="choose_card h-100">
                    <h3 style="font-weight:600; font-size:18px; color: #18529c !important;">1. Low Interest Rates</h3>
                    <p>While many business loans carry rates between 14% and 35%, shop loans are generally positioned lower, making this a more affordable option for small retail financing - your exact rate depends on your credit profile and business eligibility.</p>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="col-lg-3 col-md-6">
                <div class="choose_card h-100">
                    <h3 style="font-weight:600; font-size:18px; color: #18529c !important;">2. Flexible Repayment</h3>
                    <p>Negotiate a loan term that fits how your shop actually earns, so repaying doesn't add unnecessary stress to running your business.</p>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="col-lg-3 col-md-6">
                <div class="choose_card h-100">
                    <h3 style="font-weight:600; font-size:18px; color: #18529c !important;">3. Tax Benefits</h3>
                    <p>Interest paid on a business loan used to finance your shop can typically be treated as an allowable business expense, offering a tax advantage worth discussing with your accountant.</p>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="col-lg-3 col-md-6">
                <div class="choose_card h-100">
                    <h3 style="font-weight:600; font-size:18px; color: #18529c !important;">4. Fully Online Process</h3>
                    <p>Apply, check your eligibility, and get approved entirely online - no branch visits required at any stage.</p>
                </div>
            </div>

            <!-- Card 5 -->
            <div class="col-lg-4 col-md-6">
                <div class="choose_card h-100">
                    <h3 style="font-weight:600; font-size:18px; color: #18529c !important;">5. Minimal Documentation</h3>
                    <p>Only essential identity, address, and business proof are required, recognizing that many shopkeepers don't maintain extensive corporate paperwork.</p>
                </div>
            </div>

            <!-- Card 6 -->
            <div class="col-lg-4 col-md-6">
                <div class="choose_card h-100">
                    <h3 style="font-weight:600; font-size:18px; color: #18529c !important;">6. No Heavy Collateral Needed</h3>
                    <p>Most applicants qualify without pledging property or business assets, based on business activity and creditworthiness instead.</p>
                </div>
            </div>

            <!-- Card 7 -->
            <div class="col-lg-4 col-md-12">
                <div class="choose_card h-100">
                    <h3 style="font-weight:600; font-size:18px; color: #18529c !important;">7. Quick Approval Within Minutes</h3>
                    <p>Initial eligibility checks and approval decisions move fast, so financing doesn't hold up your business plans.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Common Uses of a Shop Loan Section -->
<section class="p-60 position-relative section-light" data-aos="fade-up" style="padding-top: 25px !important; padding-bottom: 30px !important;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Common Uses of a <span style="color: #18529c;" class="gradient-text">Shop Loan</span></h2>
            <p class="text-muted">
                Whatever's driving your need - a first-time expansion or ongoing inventory management - this financing adapts to a retail shop's actual cash flow pattern rather than a generic business timeline.
            </p>
        </div>
        
        <div class="row g-4">
            <!-- Card 1 -->
            <div class="col-lg-4 col-md-6">
                <div class="ui-card h-100">
                    <h5 class="fw-semibold mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #2a7de1; width: 22px; margin-top: -4px;" class="me-1">
                            <rect width="18" height="18" x="3" y="3" rx="2"></rect>
                            <path d="M3 9h18"></path>
                            <path d="M9 21V9"></path>
                        </svg>
                        Loan for Shop Expansion
                    </h5>
                    <p class="text-muted">Cover licenses and permits, business insurance, additional inventory, and advertising costs that come with growing your shop's footprint.</p>
                </div>
            </div>
            
            <!-- Card 2 -->
            <div class="col-lg-4 col-md-6">
                <div class="ui-card h-100">
                    <h5 class="fw-semibold mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #2a7de1; width: 22px; margin-top: -4px;" class="me-1">
                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                            <path d="M3 6h18"></path>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                        Loan for Inventory Purchase
                    </h5>
                    <p class="text-muted">Stock up on wholesale goods or seasonal inventory without straining your daily working capital and cash flow.</p>
                </div>
            </div>
            
            <!-- Card 3 -->
            <div class="col-lg-4 col-md-6">
                <div class="ui-card h-100">
                    <h5 class="fw-semibold mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #2a7de1; width: 22px; margin-top: -4px;" class="me-1">
                            <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                            <line x1="2" x2="22" y1="10" y2="10"></line>
                        </svg>
                        Working Capital Loan
                    </h5>
                    <p class="text-muted">Manage rent, staff wages, and supplier payments smoothly during slower sales periods or business cycles.</p>
                </div>
            </div>
        </div>
    </div>
</section>



<section>
    <?php include 'home-content/loan-eligibility.php'; ?>
</section>


<!-- Who Can Apply Section (Stretched Description) -->
<section class="why_wrap position-relative pb-5" style="background: #ffffff; padding-top: 25px !important; padding-bottom: 30px !important;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Who Can Apply? <span style="color: #18529c;" class="gradient-text">Real Shopkeepers, Real Needs</span></h2>
            <p class="text-muted text-start" style="line-height: 1.8; font-size: 16px;">
                A Shopkeeper Business Loan is built for the retail businesses that make up India's everyday commerce - including Kirana Shop Owners (neighborhood grocery and daily essentials stores), Grocery Store Owners (larger format provision and grocery stores), Retail Shop Owners (general retail across categories), Medical Store Owners (pharmacies and chemist shops), Garment Shop Owners (clothing and apparel retailers), Electronics Shop Owners (consumer electronics and appliance retailers), and Hardware Shop Owners (hardware, tools, and construction material retailers).
            </p>
            <p class="text-muted text-start m-0" style="line-height: 1.8; font-size: 16px;">
                Whatever category your shop falls into, eligibility comes down to your business's operating history, your credit profile, and your monthly sales - not rigid corporate documentation. Even MSME-registered and non-registered shopkeepers can both apply, and lenders increasingly factor in your UPI transaction history and digital payment activity as evidence of consistent business performance, which can meaningfully improve approval odds for shops with steady daily sales.
            </p>
        </div>
        
        <div class="row g-4">
            <!-- Kirana Owners Card -->
            <div class="col-lg-6">
                <div class="choose_card h-100">
                    <h2 class="shop-sub-h m-3" style="font-size:22px; font-weight:700;">Shopkeeper Business Loan for Kirana Store Owners</h2>

                    <p class="m-3">
                        Kirana stores are one of the most important and common small businesses in India.
                        These stores play a key role in daily household needs and local supply chains.
                        In recent years, with the growth of digital payments and online grocery platforms,
                        kirana shop owners have faced increased competition and rising operational demands.
                        Because of this, maintaining proper inventory levels and regularly upgrading stock has become essential for smooth business operations.
                    </p>

                    <p class="m-3">
                        A business loan can help kirana store owners manage multiple financial needs easily.
                        It can be used for purchasing wholesale stock at better rates, expanding the shop space,
                        upgrading refrigeration and storage systems, and handling daily working capital requirements.
                        Many shopkeepers also use such loans to open a new branch or strengthen their digital payment setup
                        to improve customer convenience and sales efficiency.
                    </p>

                    <p class="m-3">
                        One of the major advantages for kirana shop owners today is that many lenders offer flexible loan options
                        based on UPI transaction history. If a shop has regular daily sales and consistent
                        digital payment inflows, the chances of loan approval increase significantly.
                    </p>

                    <ul class="check-list m-3">
                        <li>Wholesale stock purchases for better margins</li>
                        <li>Refrigerator and freezer installation or upgrade</li>
                        <li>Shop expansion or renovation</li>
                        <li>Daily working capital management</li>
                        <li>Opening a new branch or outlet</li>
                        <li>Digital payment system setup and upgrade</li>
                    </ul>

                    <p class="m-3">
                        Overall, a kirana store business loan is a practical financial solution that helps small shop owners
                        maintain cash flow, improve inventory management, and expand their business in a competitive market.
                    </p>
                </div>
            </div>

            <!-- Retail Shop Card -->
            <div class="col-lg-6">
                <div class="choose_card h-100">
                    <h3 class="m-3" style="font-size:22px; font-weight:700; color: #0b213a;">Retail Shop Loan Without Collateral</h3>
                    <p class="m-3">
                        Many small and medium retail shop owners do not have property or physical assets available to pledge as security.
                        In such cases, unsecured business loans become a highly useful financial option. These loans are designed to support
                        shopkeepers who need quick funding without going through complex mortgage or collateral requirements.
                    </p>

                    <p class="m-3">
                        Unsecured business loans are becoming increasingly popular because they offer faster processing and simpler eligibility criteria.
                        Instead of focusing only on collateral, lenders evaluate business performance, bank statements, and cash flow stability.
                        This makes it easier for small retailers to access funding based on their actual business activity.
                    </p>

                    <p class="m-3">
                        These loans are suitable for a wide range of business needs such as expanding a retail shop, purchasing inventory,
                        managing seasonal demand, or covering short-term working capital gaps. The application process is usually online,
                        and approval decisions are made quickly.
                    </p>

                    <ul class="check-list m-3">
                        <li>No property or asset mortgage required</li>
                        <li>Fast and simple loan processing</li>
                        <li>Minimal documentation requirements</li>
                        <li>Fully online application process</li>
                        <li>Suitable for small and medium business funding needs</li>
                    </ul>

                    <p class="m-3">
                        In summary, retail shop loans without collateral provide an accessible and flexible financing solution
                        for business owners who want to grow their operations without risking personal or physical assets.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>


<section class="bg-white p-60 mb-5" style="padding-top: 25px !important; padding-bottom: 30px !important;">
    <div class="text-center mb-5">
        <h2>Shopkeeper Business Loan <span style="color:#18529c">EMI Calculator</span></h2>
        <p>Calculate your monthly EMI easily and plan your Shopkeeper Business Loan repayment with confidence.</p>
    </div>
    <?php include 'home-content/shopkeeper-business-loan-emi-calculator.php'; ?>
</section>

<!-- Shop Loan Amount, EMI & Repayment Section -->
<section class="p-60 position-relative section-light" data-aos="fade-up" style="padding-top: 25px !important; padding-bottom: 30px !important;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">
                Shop Loan Amount, <span style="color: #18529c;" class="gradient-text">EMI & Repayment</span>
            </h2>
            <p class="text-muted">
                Plan your loan amount and repayment terms according to your shop's cash flow
            </p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <p class="text-muted" style="line-height: 1.8; font-size: 16px; margin-bottom: 20px; text-align: left !important;">Your Shop Loan amount goes up to &#8377;10 Lakh, sized to your business's monthly sales, credit profile, and repayment capacity.</p>
                <p class="text-muted" style="line-height: 1.8; font-size: 16px; margin-bottom: 20px; text-align: left !important;">Tenure ranges from 24 to 48 months on this loan. Use our Shopkeeper Business Loan EMI Calculator to plan your repayment - for example, a &#8377;5,00,000 loan over 24 months at 18% p.a. works out to a monthly EMI of approximately &#8377;24,962, with total interest of &#8377;99,089 over the loan term (total repayment: &#8377;5,99,089). Adjust your loan amount, tenure, and rate in the calculator to see your specific numbers before applying.</p>
                <p class="text-muted m-0" style="line-height: 1.8; font-size: 16px; text-align: left !important;">Important terms to know: Minimum lock-in period of 6 months applies to all loans. Part-prepayment minimum: &#8377;10,000 or 1 EMI (whichever is higher). Foreclosure/prepayment requests must be submitted 15 days in advance. NOC will be issued within 7 working days after full repayment. All charges are subject to change as per RBI guidelines.</p>
            </div>
        </div>
    </div>
</section>



<div class="loan-charges py-5 position-relative section-alt">
    <div class="bg-wrapper">
        <div class="bg-blob blob-1"></div>
        <div class="bg-blob blob-2"></div>
    </div>
    <div class="container">

        <div class="text-center mb-5">
            <h2 class="fw-bold">
                Shopkeeper Business Loan Interest <span class="gradient-text" style="color:#18529c">Rates and Charges</span>
            </h2>
            <p class="text-muted text-start" style="line-height: 1.8; font-size: 16px;">
                Shop Loan Interest Rate at Waqt Finance is calculated based on your credit score, business profile, and repayment capacity. Based on the EMI calculator, rates on a Shopkeeper Business Loan generally range from 18% to 30% per annum - notably, this is a different (and typically higher) range than Waqt Finance's standard Business Loan, reflecting the more accessible eligibility criteria and lower collateral requirements of this product. GST @ 18% applies to all fees and charges.
            </p>
        </div>

        <div class="row g-4">

            <div class="col-lg-6">

                <div class="ui-card h-100">

                    <h5 class="fw-semibold mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" style="color: #2b82d8; width: 20px; margin-top: -5px;"
                            class="me-1">
                            <path d="M8 2v4"></path>
                            <path d="M16 2v4"></path>
                            <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                            <path d="M3 10h18"></path>
                        </svg>
                        Prepayment & Foreclosure Charges
                    </h5>

                    <div class="table-container">

                        <table class="table custom-table">

                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Fixed Rate</th>
                                    <th>Floating Rate</th>
                                </tr>
                            </thead>

                            <tbody>

                                <tr>
                                    <td>Within 6 months</td>
                                    <td>Not Allowed</td>
                                    <td>Not Allowed</td>
                                </tr>

                                <tr>
                                    <td>6-12 months</td>
                                    <td>6% to 10% of outstanding</td>
                                    <td>6% to 10% of outstanding</td>
                                </tr>

                                <tr>
                                    <td>12-24 months</td>
                                    <td>6% to 10% of outstanding</td>
                                    <td>Nil*</td>
                                </tr>

                                <tr>
                                    <td>After 24 months</td>
                                    <td>6% to 10% of outstanding</td>
                                    <td>Nil*</td>
                                </tr>

                            </tbody>
                        </table>
                    </div>

                    <div class="note-box mt-3">

                        <strong> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" style="color: #2b82d8; width: 20px;" class="me-1">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" x2="12" y1="8" y2="12"></line>
                                <line x1="12" x2="12.01" y1="16" y2="16"></line>
                            </svg> Note:</strong>
                        As per RBI guidelines, no prepayment charges are applicable on floating rate loans to
                        individual
                        borrowers after 12 months from disbursement.

                    </div>

                </div>
            </div>

            <!-- RIGHT CARD -->
            <div class="col-lg-6">

                <div class="ui-card h-100">

                    <h5 class="fw-semibold mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" style="color:#2a7de1;  width: 25px; margin-top: -5px;"
                            class="me-1">
                            <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                            <line x1="2" x2="22" y1="10" y2="10"></line>
                        </svg>
                        Other Applicable Charges
                    </h5>

                    <div class="charge-row d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                                    <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                                    <path d="M10 9H8"></path>
                                    <path d="M16 13H8"></path>
                                    <path d="M16 17H8"></path>
                                </svg>
                            </div>
                            <span>Processing Fee</span>
                        </div>
                        <span class="text-muted">6 to 10% of loan amount</span>
                    </div>

                    <div class="charge-row d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                                    <line x1="2" x2="22" y1="10" y2="10"></line>
                                </svg>
                            </div>
                            <span>Cheque/NACH Bounce</span>
                        </div>
                        <span class="text-muted">&#8377;500 + 18% GST</span>
                    </div>

                    <div class="charge-row d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path
                                        d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3">
                                    </path>
                                    <path d="M12 9v4"></path>
                                    <path d="M12 17h.01"></path>
                                </svg>
                            </div>
                            <span>Late Payment Fee</span>
                        </div>
                        <span class="text-muted">36% p.a. on the overdue amount (POS)</span>
                    </div>

                    <div class="charge-row d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <line x1="19" x2="5" y1="5" y2="19"></line>
                                    <circle cx="6.5" cy="6.5" r="2.5"></circle>
                                    <circle cx="17.5" cy="17.5" r="2.5"></circle>
                                </svg>
                            </div>
                            <span>Penal Interest</span>
                        </div>
                        <span class="text-muted">3% p.a. additional</span>
                    </div>

                    <div class="charge-row d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M5 22h14"></path>
                                    <path
                                        d="M19.27 13.73A2.5 2.5 0 0 0 17.5 13h-11A2.5 2.5 0 0 0 4 15.5V17a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1.5c0-.66-.26-1.3-.73-1.77Z">
                                    </path>
                                    <path d="M14 13V8.5C14 7 15 7 15 5a3 3 0 0 0-3-3c-1.66 0-3 1-3 3s1 2 1 3.5V13">
                                    </path>
                                </svg>
                            </div>
                            <span>Stamp Duty</span>
                        </div>
                        <span class="text-muted">As per applicable state regulations</span>
                    </div>

                    <div class="charge-row d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                                    <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                                    <path d="M10 9H8"></path>
                                    <path d="M16 13H8"></path>
                                    <path d="M16 17H8"></path>
                                </svg>
                            </div>
                            <span>Documentation Charges</span>
                        </div>
                        <span class="text-muted">&#8377;500 - &#8377;5,000</span>
                    </div>


                    <div class="note-box mt-3">

                        <strong class="d-block">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" style="color: #2b82d8; width: 20px;" class="me-1">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" x2="12" y1="8" y2="12"></line>
                                <line x1="12" x2="12.01" y1="16" y2="16"></line>
                            </svg>
                            GST Notice:</strong>
                        GST @ 18% is applicable on all fees and charges. Actual charges may vary based on loan type
                        and
                        amount.

                    </div>

                </div>
            </div>

        </div>

        <div class="important-box mt-5 mx-auto">

            <h3 class="fw-semibold mb-3 d-flex align-items-center gap-2">
                <div class="icon-box">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"></path>
                        <path d="M12 9v4"></path>
                        <path d="M12 17h.01"></path>
                    </svg>
                </div>
                Important Information
            </h3>

            <ul class="text-muted small" style="list-style: disc; padding-left: 20px;">

                <li class="mb-1">Minimum lock-in period of 6 months applies to all loans</li>
                <li class="mb-1">Part prepayment minimum amount: &#8377;10,000 or 1 EMI (whichever is higher)</li>
                <li class="mb-1">Foreclosure/prepayment requests must be submitted 15 days in advance</li>
                <li class="mb-1">NOC will be issued within 7 working days after full repayment</li>
                <li>All charges are subject to change as per RBI guidelines</li>

            </ul>

        </div>

    </div>
</div>



<section class="py-5 position-relative" style="padding-top: 25px !important; padding-bottom: 30px !important;">
    <div class="bg-wrapper">
        <div class="bg-blob blob-1"></div>
        <div class="bg-blob blob-2"></div>
    </div>
    <div class="container">

        <!-- Heading -->
        <div class="text-center mb-5">

            <h2 style="font-weight:400">
                Eligibility for Shopkeeper Business <span style="color:red" class="gradient-text">Loan</span>
            </h2>

            <p class="text-muted">
                Check if you qualify for our Shopkeeper Business Loan
            </p>

        </div>

        <div class="row g-4 justify-content-center">

            <!-- Salaried Card -->
            <div class="col-lg-5">

                <div class="ui-card">

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <!-- <div class="badge-number">1</div> -->
                        <h5 class="fw-semibold m-0">Shopkeeper Business Loan</h5>
                    </div>

                    <ul class="check-list">

                        <li>Age: 21-65 years</li>
                        <li>Indian citizen</li>
                        <li>Credit score 650+</li>
                        <li>1+ year business</li>
                        <li>Shopkeepers eligible</li>

                    </ul>

                </div>
            </div>



        </div>

        <!-- CTA -->
        <div class="text-center mt-5">

            <a href="business-loan-apply" class="apply-btn btn-lg py-3" style="font-size:18px;">

                Check Your Eligibility &rarr;

            </a>

        </div>

    </div>

    <div class="container pt-5 mt-5">

        <!-- Heading -->
        <div class="text-center mb-5">

            <h2 class="fw-bold">
                Documents Required for Shopkeeper Business <span class="gradient-text" style="color:#18529c">Loan</span>
            </h2>

            <p class="text-muted">
                Getting your Shop Loan Documents ready in advance helps speed up verification and approval.
            </p>

        </div>

        <div class="row g-4">

            <!-- Identity Proof -->
            <div class="col-md-4">

                <div class="ui-card h-100">

                    <h5 class="fw-semibold mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" style="color: #2b82d8; width: 20px ; margin-top: -5px;">
                            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                            <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                            <path d="M10 9H8"></path>
                            <path d="M16 13H8"></path>
                            <path d="M16 17H8"></path>
                        </svg>
                        Identity Proof
                    </h5>

                    <ul class="doc-list">
                        <li>Aadhaar Card</li>
                        <li>PAN Card</li>
                        <li>Voter ID</li>
                    </ul>

                </div>
            </div>

            <!-- Address Proof -->
            <div class="col-md-4">

                <div class="ui-card h-100">

                    <h5 class="fw-semibold mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" style="color: #2b82d8; width: 20px ; margin-top: -5px;">
                            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                            <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                            <path d="M10 9H8"></path>
                            <path d="M16 13H8"></path>
                            <path d="M16 17H8"></path>
                        </svg>
                        Address Proof
                    </h5>

                    <ul class="doc-list">
                        <li>Utility Bills (not older than 3 months)</li>
                        <li>Passport</li>
                        <li>Aadhaar Card</li>
                    </ul>

                </div>
            </div>

            <!-- Income Proof -->
            <div class="col-md-4">

                <div class="ui-card h-100">

                    <h5 class="fw-semibold mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" style="color: #2b82d8; width: 20px ; margin-top: -5px;">
                            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                            <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                            <path d="M10 9H8"></path>
                            <path d="M16 13H8"></path>
                            <path d="M16 17H8"></path>
                        </svg>
                        Income & Business Proof
                    </h5>

                    <ul class="doc-list">
                        <li>Business Proof / Shop Proof</li>
                        <li>Bank statements (last 6 months)</li>
                        <li>Self-Declaration of Income</li>
                        <li>GST Registration Certificate</li>
                        <li>MSME Certificate</li>
                    </ul>

                </div>
            </div>

        </div>

        <!-- Stretched footer notice -->
        <div class="text-center mt-5">
            <p class="text-muted small text-start" style="line-height: 1.6;">
                Note that a GST Registration Certificate and MSME Certificate are listed among possible documents, but MSME registration itself isn't mandatory for eligibility - it's one of several factors that can strengthen your application alongside consistent bank statement activity and self-declared income.
            </p>
        </div>

    </div>
</section>



<!-- How to Apply Section -->
<section class="p-60 position-relative section-light" data-aos="fade-up" style="padding-top: 25px !important; padding-bottom: 30px !important;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">How to Apply for a <span style="color: #18529c;" class="gradient-text">Shop Loan Online?</span></h2>
            <p class="text-muted">Apply Shop Loan Online with Waqt Finance in a few simple steps:</p>
        </div>
        
        <div class="row g-4">
            <!-- Step 1 -->
            <div class="col-lg-3 col-md-6">
                <div class="process_card h-100">
                    <div class="process_icon" style="color: #18529c !important; background: #f0f7ff !important; border-radius: 12px !important; width: 52px !important; height: 52px !important; display: flex !important; align-items: center !important; justify-content: center !important; margin-bottom: 18px !important;">
                        <span style="font-weight: 800; font-size: 20px; font-family: 'Inter', sans-serif;">1</span>
                    </div>
                    <h3>Check Your Eligibility</h3>
                    <p>Share your name, mobile number, MSME registration status, and monthly sales range to get an instant loan estimate.</p>
                </div>
            </div>
            
            <!-- Step 2 -->
            <div class="col-lg-3 col-md-6">
                <div class="process_card h-100">
                    <div class="process_icon" style="color: #18529c !important; background: #f0f7ff !important; border-radius: 12px !important; width: 52px !important; height: 52px !important; display: flex !important; align-items: center !important; justify-content: center !important; margin-bottom: 18px !important;">
                        <span style="font-weight: 800; font-size: 20px; font-family: 'Inter', sans-serif;">2</span>
                    </div>
                    <h3>Submit Your Application</h3>
                    <p>Complete the full application with your business and personal details.</p>
                </div>
            </div>
            
            <!-- Step 3 -->
            <div class="col-lg-3 col-md-6">
                <div class="process_card h-100">
                    <div class="process_icon" style="color: #18529c !important; background: #f0f7ff !important; border-radius: 12px !important; width: 52px !important; height: 52px !important; display: flex !important; align-items: center !important; justify-content: center !important; margin-bottom: 18px !important;">
                        <span style="font-weight: 800; font-size: 20px; font-family: 'Inter', sans-serif;">3</span>
                    </div>
                    <h3>Document Verification</h3>
                    <p>Our team verifies your submitted documents and evaluates your eligibility.</p>
                </div>
            </div>
            
            <!-- Step 4 -->
            <div class="col-lg-3 col-md-6">
                <div class="process_card h-100">
                    <div class="process_icon" style="color: #18529c !important; background: #f0f7ff !important; border-radius: 12px !important; width: 52px !important; height: 52px !important; display: flex !important; align-items: center !important; justify-content: center !important; margin-bottom: 18px !important;">
                        <span style="font-weight: 800; font-size: 20px; font-family: 'Inter', sans-serif;">4</span>
                    </div>
                    <h3>Loan Approval & Disbursal</h3>
                    <p>Once verification is complete, receive your approved amount and terms, with funds disbursed to your account.</p>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-5">
            <p class="text-muted small text-start" style="line-height: 1.6;">
                This Business Loan for Shopkeepers Online process is designed for shop owners who don't have time to spare on lengthy paperwork - your data stays secure and is never shared with third parties throughout.
            </p>
        </div>
    </div>
</section>

<!-- Why Choose Waqt Finance Section -->
<section class="p-60 position-relative section-white" data-aos="fade-up" style="padding-top: 25px !important; padding-bottom: 30px !important;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Why Choose <span style="color: #18529c;" class="gradient-text">Waqt Finance?</span></h2>
            <p class="text-muted text-start" style="line-height: 1.8; font-size: 16px;">
                Why should I choose a loan from Waqt Finance? Because this Shopkeeper Business Loan is built specifically around what small retail owners need - not a scaled-down corporate loan.
            </p>
        </div>
        
        <div class="row g-4 justify-content-center">
            <div class="col-lg-8">
                <div class="ui-card">
                    <h5 class="fw-semibold mb-4 text-center">What sets Waqt Finance apart for shop owners:</h5>
                    <ul class="check-list" style="list-style: none; padding-left: 0;">
                        <li style="margin-bottom: 12px; position: relative; padding-left: 25px;">
                            <span style="color: #18529c; position: absolute; left: 0;">&#10003;</span>
                            <strong>Quick Approval Within Minutes</strong> - Fast initial eligibility checks, so you're not left waiting.
                        </li>
                        <li style="margin-bottom: 12px; position: relative; padding-left: 25px;">
                            <span style="color: #18529c; position: absolute; left: 0;">&#10003;</span>
                            <strong>Minimal Documentation</strong> - Only what's genuinely needed for identity, address, and business verification.
                        </li>
                        <li style="margin-bottom: 12px; position: relative; padding-left: 25px;">
                            <span style="color: #18529c; position: absolute; left: 0;">&#10003;</span>
                            <strong>No Heavy Collateral Needed</strong> - Most applicants qualify without pledging property or shop assets.
                        </li>
                        <li style="margin-bottom: 12px; position: relative; padding-left: 25px;">
                            <span style="color: #18529c; position: absolute; left: 0;">&#10003;</span>
                            <strong>Flexible EMI Repayment</strong> - Terms structured around how your shop actually earns.
                        </li>
                        <li style="margin-bottom: 12px; position: relative; padding-left: 25px;">
                            <span style="color: #18529c; position: absolute; left: 0;">&#10003;</span>
                            <strong>Complete Transparency</strong> - Every charge and fee disclosed upfront, with no hidden costs.
                        </li>
                        <li style="margin-bottom: 12px; position: relative; padding-left: 25px;">
                            <span style="color: #18529c; position: absolute; left: 0;">&#10003;</span>
                            <strong>Digital-First Process</strong> - Check eligibility, apply, and track your application entirely online.
                        </li>
                        <li style="margin-bottom: 0; position: relative; padding-left: 25px;">
                            <span style="color: #18529c; position: absolute; left: 0;">&#10003;</span>
                            <strong>Trusted Track Record</strong> - Rated 4.8/5 by 5,000+ small business owners across India.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-5">
            <p class="text-muted text-start" style="line-height: 1.6;">
                Whether you run a kirana store, a medical shop, or a garment outlet, Waqt Finance is built to help you access financing quickly, without the paperwork burden of a traditional lender.
            </p>
        </div>
    </div>
</section>

<!-- Shop Loan vs Business Loan Section -->
<section class="p-60 position-relative section-light" data-aos="fade-up" style="padding-top: 25px !important; padding-bottom: 30px !important;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">
                Shop Loan vs <span style="color: #18529c;" class="gradient-text">Business Loan</span>
            </h2>
            <p class="text-muted">
                A Shopkeeper Business Loan and Waqt Finance's standard Business Loan are genuinely different products:
            </p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="table-container">
                    <table class="table custom-table">
                        <thead>
                            <tr>
                                <th>Factor</th>
                                <th>Shop Loan</th>
                                <th>Business Loan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Max Loan Amount</strong></td>
                                <td>Up to &#8377;10 Lakh</td>
                                <td>Higher amounts for larger businesses</td>
                            </tr>
                            <tr>
                                <td><strong>Interest Rate</strong></td>
                                <td>~18%-30% p.a.</td>
                                <td>Generally lower, ~10.49%-24% p.a.</td>
                            </tr>
                            <tr>
                                <td><strong>Minimum Credit Score</strong></td>
                                <td>650+</td>
                                <td>700+</td>
                            </tr>
                            <tr>
                                <td><strong>Business Vintage Required</strong></td>
                                <td>1+ year</td>
                                <td>3+ years</td>
                            </tr>
                            <tr>
                                <td><strong>Best Suited For</strong></td>
                                <td>Kirana, retail, medical, and garment shop owners with shorter operating history</td>
                                <td>Established MSMEs and businesses with longer track records</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-muted mt-4 text-start small" style="line-height: 1.6;">
                    If you run a smaller, newer retail shop and need faster access with a more accessible credit bar, a Shop Loan is the more natural fit - even if it comes at a somewhat higher rate. If your business has a longer track record and larger funding needs, Waqt Finance's standard Business Loan may offer better terms.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="testimonials p-60 section-white" data-aos="fade-up" style="padding-top: 25px !important; padding-bottom: 30px !important;">

    <div class="container">

        <!-- Heading -->
        <div class="text-center mb-5">
            <h2 class="fw-bold">
                Trusted by <span class="gradient-text" style="color:#18529c">Shopkeepers Across India</span>
            </h2>
            <p class="text-muted text-center">
                See how small business owners are growing their shops with easy and fast business loans from Waqt Finance.
            </p>
        </div>

        <!-- Slick Slider -->
        <div class="testimonial-slider">

            <div>
                <div class="ui-card">

                    <div class="d-flex justify-content-between mb-3">
                        <span class="star">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                        <div class="quote">&#10077;</div>
                    </div>

                    <p class="text-muted fst-italic">
                        "I needed stock for my kirana shop during festival season. Waqt Finance approved my loan quickly and helped me increase sales."
                    </p>

                    <div class="d-flex align-items-center gap-3 mt-3">
                        <div class="avatar">R</div>
                        <div>
                            <strong>Rajesh Verma</strong><br>
                            <small class="text-muted">Kirana Store Owner, Delhi</small>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between small">
                        <span>Loan Amount:</span>
                        <strong class="warning">&#8377;3 Lakhs</strong>
                    </div>

                </div>
            </div>


            <div>
                <div class="ui-card">

                    <div class="d-flex justify-content-between mb-3">
                        <span class="star">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                        <div class="quote">&#10077;</div>
                    </div>

                    <p class="text-muted fst-italic">
                        "The process was simple and documentation was minimal. I expanded my mobile accessories shop within a week."
                    </p>

                    <div class="d-flex align-items-center gap-3 mt-3">
                        <div class="avatar">A</div>
                        <div>
                            <strong>Ankit Sharma</strong><br>
                            <small class="text-muted">Mobile Shop Owner, Noida</small>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between small">
                        <span>Loan Amount:</span>
                        <strong class="warning">&#8377;5 Lakhs</strong>
                    </div>

                </div>
            </div>


            <div>
                <div class="ui-card">

                    <div class="d-flex justify-content-between mb-3">
                        <span class="star">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                        <div class="quote">&#10077;</div>
                    </div>

                    <p class="text-muted fst-italic">
                        "I renovated my garment store with the help of this business loan. The EMI options were very flexible for my monthly income."
                    </p>

                    <div class="d-flex align-items-center gap-3 mt-3">
                        <div class="avatar">S</div>
                        <div>
                            <strong>Sunil Gupta</strong><br>
                            <small class="text-muted">Garment Shop Owner, Ghaziabad</small>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between small">
                        <span>Loan Amount:</span>
                        <strong class="warning">&#8377;4 Lakhs</strong>
                    </div>

                </div>
            </div>


            <div>
                <div class="ui-card">

                    <div class="d-flex justify-content-between mb-3">
                        <span class="star">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                        <div class="quote">&#10077;</div>
                    </div>

                    <p class="text-muted fst-italic">
                        "Got instant approval for my hardware business. Funds were credited very fast and helped me manage supplier payments."
                    </p>

                    <div class="d-flex align-items-center gap-3 mt-3">
                        <div class="avatar">M</div>
                        <div>
                            <strong>Manoj Yadav</strong><br>
                            <small class="text-muted">Hardware Store Owner, Jaipur</small>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between small">
                        <span>Loan Amount:</span>
                        <strong class="warning">&#8377;6 Lakhs</strong>
                    </div>

                </div>
            </div>


            <div>
                <div class="ui-card">

                    <div class="d-flex justify-content-between mb-3">
                        <span class="star">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                        <div class="quote">&#10077;</div>
                    </div>

                    <p class="text-muted fst-italic">
                        "I compared many lenders but Waqt Finance offered the fastest service and transparent repayment terms for my business."
                    </p>

                    <div class="d-flex align-items-center gap-3 mt-3">
                        <div class="avatar">V</div>
                        <div>
                            <strong>Vikas Jain</strong><br>
                            <small class="text-muted">Electronics Shop Owner, Lucknow</small>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between small">
                        <span>Loan Amount:</span>
                        <strong class="warning">&#8377;7 Lakhs</strong>
                    </div>

                </div>
            </div>


            <div>
                <div class="ui-card">

                    <div class="d-flex justify-content-between mb-3">
                        <span class="star">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                        <div class="quote">&#10077;</div>
                    </div>

                    <p class="text-muted fst-italic">
                        "Very supportive team and quick verification process. This loan helped me increase inventory in my cosmetic shop."
                    </p>

                    <div class="d-flex align-items-center gap-3 mt-3">
                        <div class="avatar">P</div>
                        <div>
                            <strong>Priyanka Arora</strong><br>
                            <small class="text-muted">Cosmetic Shop Owner, Chandigarh</small>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between small">
                        <span>Loan Amount:</span>
                        <strong class="warning">&#8377;2.5 Lakhs</strong>
                    </div>

                </div>
            </div>

        </div>

        <p class="text-center small text-muted mt-4">
            Rated <strong>4.8/5</strong> by 5,000+ small business owners across India
        </p>

    </div>

</section>





<div class="position-relative">
    <div class="bg-wrapper faq_blob">
        <div class="bg-blob blob-1"></div>
        <div class="bg-blob blob-2"></div>
    </div>
    <section id="faq" class="faq-section p-60 position-relative section-alt" style="padding-top: 25px !important; padding-bottom: 30px !important;">
        <div class="container">

            <!-- Heading -->
            <div class="text-center mx-auto mb-5" style="max-width: 640px;">
                <h2 class="mt-3 mb-3" style="font-weight:400">
                    Shopkeeper Business Loan <span style="color:red" class="gradient-text">FAQs</span>
                </h2>
                <p class="text-muted">
                    Everything you need to know about Shopkeeper Business Loan eligibility, documents, interest rates, and approval process at Waqt Finance.
                </p>
            </div>

            <!-- Accordion -->
            <div class="accordion faq-accordion" id="faqAccordion">
        <div class="row">
            <div class="col-lg-6 col-12">
                <div class="accordion-item ui-card border-0 mb-3">
                    <h3 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faq1" aria-expanded="true">
                            What are the purposes for which I can get a loan via Waqt Finance?
                        </button>
                    </h3>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Waqt Finance business loans can be used for business expansion, managing working capital needs, handling seasonal expenses, and covering short-term cash flow gaps.
                        </div>
                    </div>
                </div>

            <div class="accordion-item ui-card border-0 mb-3">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faq2">
                            Why should I choose a loan from Waqt Finance?
                        </button>
                    </h3>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Waqt Finance offers quick loan approval, minimal documentation, flexible repayment options, and fast fund disbursal to help grow your business smoothly.
                        </div>
                    </div>
                </div>

            <div class="accordion-item ui-card border-0 mb-3">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faq3">
                            Is collateral required for a shop loan from Waqt Finance?
                        </button>
                    </h3>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Most loans from Waqt Finance are unsecured, so collateral is generally not required, but it may depend on the loan amount and applicant profile.
                        </div>
                    </div>
                </div>
                
                <div class="collapse more-faqs-collapse" id="moreFaqsLeft">
                    <div class="mt-3">
                        <div class="accordion-item ui-card border-0 mb-3">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faq4">
                            What documents are required to apply for a loan from Waqt Finance?
                        </button>
                    </h3>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            You need PAN card, address proof, last 6 months bank statements, and business proof like GST registration or Shop Act certificate, with extra documents for higher loan amounts.
                        </div>
                    </div>
                </div>

            <div class="accordion-item ui-card border-0 mb-3">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faq5">
                            What happens if I pre-close my loan with Waqt Finance?
                        </button>
                    </h3>
                    <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            If you pre-close your loan, you may need to pay foreclosure or prepayment charges as per your loan agreement terms.
                        </div>
                    </div>
                </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12">
                <div class="accordion-item ui-card border-0 mb-3">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faq6">
                            Are there any additional charges apart from interest?
                        </button>
                    </h3>
                    <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes, Waqt Finance may charge processing fees, documentation charges, and late payment penalties depending on the loan terms.
                        </div>
                    </div>
                </div>

            <div class="accordion-item ui-card border-0 mb-3">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faq7">
                            What is the eligibility for a business loan from Waqt Finance?
                        </button>
                    </h3>
                    <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Your business should be active for at least 1 year with minimum monthly sales of around &#8377;2 lakh to qualify for a loan.
                        </div>
                    </div>
                </div>

            <div class="accordion-item ui-card border-0 mb-3">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faq8">
                            Will my credit score affect my loan approval at Waqt Finance?
                        </button>
                    </h3>
                    <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes, a good credit score improves your chances of approval and may help you get better interest rates.
                        </div>
                    </div>
                </div>
                
                <div class="collapse more-faqs-collapse" id="moreFaqsRight">
                    <div class="mt-3">
                        <div class="accordion-item ui-card border-0 mb-3">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faq9">
                            How can I speed up my loan approval with Waqt Finance?
                        </button>
                    </h3>
                    <div id="faq9" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            You can speed up approval by keeping all documents ready, providing correct details, and maintaining clean and consistent bank statements.
                        </div>
                    </div>
                </div>

            <div class="accordion-item ui-card border-0">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faq10">
                            Can I apply for a loan for a supermarket or retail shop at Waqt Finance?
                        </button>
                    </h3>
                    <div id="faq10" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes, supermarkets, kirana stores, and other retail businesses are eligible to apply for business loans with Waqt Finance.
                        </div>
                    </div>
                </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <button class="btn btn-outline-primary" id="faqToggleBtn" type="button" data-bs-toggle="collapse" data-bs-target=".more-faqs-collapse" style="border-color: #18529c; color: #18529c; padding: 10px 24px; border-radius: 30px; font-weight: 500; transition: all 0.3s ease;">
                View More FAQs
            </button>
        </div>
        
        <script>
            window.addEventListener('load', function () {
                const firstCollapse = document.getElementById('moreFaqsLeft');
                const toggleBtn = document.getElementById('faqToggleBtn');
                if (firstCollapse && toggleBtn) {
                    firstCollapse.addEventListener('show.bs.collapse', function () {
                        toggleBtn.innerText = 'Show Less FAQs';
                    });
                    firstCollapse.addEventListener('hide.bs.collapse', function () {
                        toggleBtn.innerText = 'View More FAQs';
                    });
                }
            });
        </script>
    </div>

    <!-- Final CTA Section -->
    <section class="p-60 position-relative section-alt text-center" data-aos="zoom-in" style="padding-top: 25px !important; padding-bottom: 30px !important;">
        <div class="container">
            <h3 class="fw-bold mb-3">Ready to Grow Your Shop?</h3>
            <p class="text-muted max-width-700 mx-auto mb-4" style="font-size: 16px; line-height: 1.6;">
                Ready to grow your shop? Apply Shop Loan Online with Waqt Finance today and get up to &#8377;10 Lakh with minimal paperwork and quick approval.
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="business-loan-apply" class="apply-btn" style="border-radius: 30px; padding: 12px 30px; font-weight: 500;">Apply Now</a>
                <a href="#eligibility" class="btn btn-outline-primary" style="border-color: #18529c; color: #18529c; border-radius: 30px; padding: 12px 30px; font-weight: 500; display: inline-block;">Check Eligibility</a>
            </div>
        </div>
    </section>
<section class="py-5 position-relative blog-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">
                Explore Our <span class="gradient-text">Latest Articles</span>
            </h2>
            <p class="text-muted text-center">
                Insights, guides, and tips to help retail shopkeepers &amp; kirana store owners grow their business.
            </p>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- Blog Card 1 -->
            <div class="col-md-4">
                <div class="main-blog-card">
                    <article class="blog-card">
                        <div class="blog-image">
                            <img src="assets/images/shopkeeper.webp" alt="How to Apply for a Shopkeeper Business Loan in Delhi NCR">
                        </div>
                        <div class="blog-content">
                            <div class="blog-meta">
                                <span>5 min read</span>
                            </div>
                            <h5 class="blog-title">
                                How to Apply for a Shopkeeper Business Loan in Delhi NCR
                            </h5>
                            <p class="blog-excerpt">
                                Step-by-step online application guide, eligibility criteria, and documents required for retail &amp; kirana store loans.
                            </p>
                            <a href="blog/how-to-apply-for-a-shopkeeper-business-loan-in-delhi-ncr" class="read-more">
                                Read Article →
                            </a>
                        </div>
                    </article>
                </div>
            </div>

            <!-- Blog Card 2 -->
            <div class="col-md-4">
                <div class="main-blog-card">
                    <article class="blog-card">
                        <div class="blog-image">
                            <img src="assets/images/buss-6.webp" alt="Shopkeeper Business Loan Eligibility in Noida">
                        </div>
                        <div class="blog-content">
                            <div class="blog-meta">
                                <span>4 min read</span>
                            </div>
                            <h5 class="blog-title">
                                Shopkeeper Business Loan Eligibility &amp; Documents Guide
                            </h5>
                            <p class="blog-excerpt">
                                Check minimum daily sales, business vintage, and simple KYC requirements to qualify for an unsecured shop loan.
                            </p>
                            <a href="blog/shopkeeper-business-loan-eligibility-in-noida" class="read-more">
                                Read Article →
                            </a>
                        </div>
                    </article>
                </div>
            </div>

            <!-- Blog Card 3 -->
            <div class="col-md-4">
                <div class="main-blog-card">
                    <article class="blog-card">
                        <div class="blog-image">
                            <img src="assets/images/women-img.webp" alt="Working Capital Loan for Small Business Guide">
                        </div>
                        <div class="blog-content">
                            <div class="blog-meta">
                                <span>6 min read</span>
                            </div>
                            <h5 class="blog-title">
                                Working Capital Loan for Small Business: A Complete Guide
                            </h5>
                            <p class="blog-excerpt">
                                Discover how small businesses and retail shops use working capital loans to stock inventory and manage cash flow.
                            </p>
                            <a href="blog/working-capital-loan-for-small-business-a-complete-guide" class="read-more">
                                Read Article →
                            </a>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Internal Links Section -->
<section class="py-4 bg-light border-top" style="font-family: 'Inter', sans-serif !important;">
<!-- Internal Links Section -->
<section class="py-4 bg-light border-top" style="font-family: 'Inter', sans-serif !important;">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="d-flex flex-wrap justify-content-center gap-3" style="font-size: 14px;">
                    <a href="business-loan" class="text-decoration-none text-muted" title="Business Loan - for larger, established business financing needs"><strong>Business Loan</strong></a>
                    <span class="text-muted">|</span>
                    <a href="personal-loan" class="text-decoration-none text-muted" title="Personal Loan - for personal expenses without pledging business assets"><strong>Personal Loan</strong></a>
                    <span class="text-muted">|</span>
                    <a href="pay-day-loan" class="text-decoration-none text-muted" title="Payday Loan - short-term salary advance for salaried individuals"><strong>Payday Loan</strong></a>
                    <span class="text-muted">|</span>
                    <a href="edi-loan" class="text-decoration-none text-muted" title="EDI Loan - daily installment financing for shopkeepers and vendors"><strong>EDI Loan</strong></a>
                    <span class="text-muted">|</span>
                    <a href="loan-against-property" class="text-decoration-none text-muted" title="Loan Against Property - secured financing against residential or commercial property"><strong>Loan Against Property</strong></a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
<script>
    $('.testimonial-slider').slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 1500,
        dots: true,
        arrows: true,
        responsive: [{
                breakpoint: 992,
                settings: {
                    slidesToShow: 2
                }
            },
            {
                breakpoint: 576,
                settings: {
                    slidesToShow: 1
                }
            }
        ]
    });
</script>
