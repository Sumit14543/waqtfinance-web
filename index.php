
<?php
$page_title = "Instant Online Loans in India | Quick Approval | Waqt Finance";
$meta_description = "Apply for fast personal, business, payday & vehicle loans up to 10 Lakh with Waqt Finance. Minimal paperwork & 24-hr disbursal. Apply online today!";
$meta_keywords = "loan in India, instant personal loan India, business loan India, payday loan online, best NBFC loan India, quick loans online, instant loan for salaried, instant loan for salaried employee, instant loan for womens, apply for payday loan online, personal loan apply online";
$canonical_url = "https://waqtfinance.com/";
$meta_image = "assets/images/loan-hero.webp";
$meta_image_alt = "Waqt Finance loan services in India";
$breadcrumb_label = "Home";
$seo_schema = [
    [
        "@context" => "https://schema.org",
        "@type" => "WebPage",
        "name" => "Waqt Finance loans in India",
        "url" => "https://waqtfinance.com/",
        "description" => $meta_description,
        "about" => [
            ["@type" => "Thing", "name" => "Personal Loan in India"],
            ["@type" => "Thing", "name" => "Business Loan in India"],
            ["@type" => "Thing", "name" => "Payday Loan in India"],
            ["@type" => "Thing", "name" => "Loan Against Property in India"]
        ],
        "inLanguage" => "en-IN"
    ],
    [
        "@context" => "https://schema.org",
        "@type" => ["Organization", "FinancialService"],
        "@id" => "https://waqtfinance.com/#organization",
        "name" => "Waqt Finance",
        "legalName" => "WAQT FINANCE PVT. LTD",
        "url" => "https://waqtfinance.com/",
        "logo" => "https://waqtfinance.com/assets/images/logo1.webp",
        "image" => "https://waqtfinance.com/assets/images/logo1.webp",
        "description" => "Waqt Finance is a registered NBFC in India offering instant personal loans, business loans, payday loans, EDI loans, vehicle loans and loan against property services.",
        "telephone" => "+91-9217086610",
        "email" => "customercare@waqtfinance.com",
        "address" => [
            "@type" => "PostalAddress",
            "streetAddress" => "BSI Business Park, H-15, Sector 63",
            "addressLocality" => "Noida",
            "addressRegion" => "Uttar Pradesh",
            "postalCode" => "201301",
            "addressCountry" => "IN"
        ],
        "areaServed" => [
            ["@type" => "Country", "name" => "India"],
            ["@type" => "City", "name" => "Delhi"],
            ["@type" => "City", "name" => "Noida"],
            ["@type" => "City", "name" => "Gurugram"],
            ["@type" => "City", "name" => "Ghaziabad"],
            ["@type" => "City", "name" => "Faridabad"]
        ],
        "sameAs" => [
            "https://www.facebook.com/waqtfinance",
            "https://www.linkedin.com/company/waqt-finance-pvt-ltd/",
            "https://www.instagram.com/waqt.finance"
        ],
        "contactPoint" => [
            [
                "@type" => "ContactPoint",
                "telephone" => "+91-9217086610",
                "email" => "customercare@waqtfinance.com",
                "contactType" => "customer support",
                "areaServed" => "IN",
                "availableLanguage" => ["English", "Hindi"]
            ]
        ],
        "identifier" => [
            ["@type" => "PropertyValue", "propertyID" => "CIN", "value" => "U65923DL2022PTC396001"],
            ["@type" => "PropertyValue", "propertyID" => "NBFC Registration Number", "value" => "B.10.00143"]
        ]
    ],
    [
        "@context" => "https://schema.org",
        "@type" => "WebSite",
        "@id" => "https://waqtfinance.com/#website",
        "url" => "https://waqtfinance.com/",
        "name" => "Waqt Finance",
        "publisher" => ["@id" => "https://waqtfinance.com/#organization"],
        "inLanguage" => "en-IN"
    ],
    [
        "@context" => "https://schema.org",
        "@type" => "ItemList",
        "name" => "Waqt Finance loan products",
        "itemListElement" => [
            ["@type" => "ListItem", "position" => 1, "url" => "https://waqtfinance.com/personal-loan", "name" => "Personal Loan"],
            ["@type" => "ListItem", "position" => 2, "url" => "https://waqtfinance.com/business-loan", "name" => "Business Loan"],
            ["@type" => "ListItem", "position" => 3, "url" => "https://waqtfinance.com/pay-day-loan", "name" => "Payday Loan"],
            ["@type" => "ListItem", "position" => 4, "url" => "https://waqtfinance.com/loan-against-property", "name" => "Loan Against Property"],
            ["@type" => "ListItem", "position" => 5, "url" => "https://waqtfinance.com/vehicle-loan", "name" => "Vehicle Loan"],
            ["@type" => "ListItem", "position" => 6, "url" => "https://waqtfinance.com/edi-loan", "name" => "EDI Loan"],
            ["@type" => "ListItem", "position" => 7, "url" => "https://waqtfinance.com/shopkeeper-business-loan", "name" => "Shopkeeper Business Loan"]
        ]
    ]
];
include 'includes/header.php';
?>
<!-- Swiper CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" media="print" onload="this.media='all'"/>

<?php include 'includes/main-header.php'; ?> 

<section class="hero_banner">

    <div class="bg">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="content-h1">
                        <h1>Instant Personal & Business Loans in India - <span>Approved</span> Fast</h1>
                    </div>

                </div>
                <div class="col-lg-6">


                    <div class="loan-box">

                        <h2>Your Personal Loan - <span>Approved</span> Instantly!</h2>
                        <p class="loan-p">Choose your desired loan amount</p>

                        <div id="amount">₹ 2,50,000</div>

                        <input type="range" min="50000" max="3000000" step="50000" value="250000" id="loanSlider" aria-label="Loan Amount Slider">

                        <div class="range-labels">
                            <span>50,000</span>
                            <span>30,00,000</span>
                        </div>

                        <p class="note">Assuming annual interest rate 10.49% and 60 months tenure</p>

                        <div class="emi">
                            Calculated EMI* <strong id="emi" style="font-size: 28px; color: #b91c1c;">₹ 0</strong>
                        </div>

                        <div class="check">
                            <a href="apply" class="apply second-apply text-decoration-none d-inline-flex align-items-center justify-content-center">Apply Now</a>
                            <span>Check your Eligibility</span> <span class="check-icon"><i class="fa-solid fa-angle-right"></i></span>
                        </div>

                        <div class="secure mt-3">
                            <span><i class="fa-solid fa-shield-heart"></i></span>
                            <p>Serving India as a reliable <br> financial partner</p>
                        </div>

                        <div class="bttom-line">
                            <p>Get quick access to funds after your loan is approved.</p>
                        </div>

                        <div class="line">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<section>
    <div class="container py-5">

            <div class="loan-h text text-center">
                <h2>Our Loan Products</h2>
            </div>

        <div class="row">

            <div class="col-lg-3 col-md-6 col-12 mt-4">
                <div class="card h-100 my-one-card">
                    <img src="assets/images/loan-p-one.webp" class="card-img-top" alt="Business Loan Services Waqt Finance" loading="lazy">
                    <div class="card-body">
                        <h5 class="card-title">Business Loan</h5>
                        <p class="card-text">Easy access to all kind of small businesses in India with minimum documentation. You may be a wholesaler or retailer or trader.</p>
                        <div class="mt-3">
                            <a href="business-loan" class="my-btn">apply now <span><i class="fa-solid fa-arrow-right"></i></span></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12 mt-4">
                <div class="card h-100 my-one-card">
                    <img src="assets/images/loan-p-two.webp" class="card-img-top" alt="Vehicle and Car Loan Financing" loading="lazy">
                    <div class="card-body">
                        <h5 class="card-title">Vehicle Loan</h5>
                        <p class="card-text">Get easy and affordable vehicle financing with flexible options, quick processing, and wide access across urban, semi-urban, and rural areas.</p>
                        <div class="mt-3">
                            <a href="vehicle-loan" class="my-btn">apply now <span><i class="fa-solid fa-arrow-right"></i></span></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12 mt-4">
                <div class="card h-100 my-one-card">
                    <img src="assets/images/loan-p-three.webp" class="card-img-top" alt="EDI Loan for Small Businesses" loading="lazy">
                    <div class="card-body">
                        <h5 class="card-title">EDI Loan</h5>
                        <p class="card-text">Get simple financing solutions for small businesses across India with minimal documentation. Ideal for wholesalers, retailers, and traders.</p>
                        <div class="mt-3">
                            <a href="edi-loan" class="my-btn">apply now <span><i class="fa-solid fa-arrow-right"></i></span></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12 mt-4">
                <div class="card h-100 my-one-card">
                    <img src="assets/images/loan-p-four.webp" class="card-img-top" alt="Fast Payday Loan Solutions" loading="lazy">
                    <div class="card-body">
                        <h5 class="card-title">Payday Loans</h5>
                        <p class="card-text">Instant short-term cash in India with minimal documents, for salaried and self-employed individuals, quick and hassle-free access.</p>
                        <div class="mt-3">
                            <a href="pay-day-loan" class="my-btn">apply now <span><i class="fa-solid fa-arrow-right"></i></span></a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<section class="py-4">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h2 class="fw-bold mb-2">Loan Services Across India</h2>
                <p class="text-muted mb-0">Waqt Finance supports customers across India with personal loans, business loans, payday loans, EDI loans, vehicle loans and loan against property options.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="products" class="my-btn" style="text-transform:capitalize;">Explore Our Products <span><i class="fa-solid fa-arrow-right"></i></span></a>
            </div>
        </div>
    </div>
</section>


<div>
    <?php include 'home-content/calculator.php' ?>
</div>

<div>
    <?php include 'home-content/about-content.php'; ?>
</div>

<div>
    <?php include 'home-content/testimonals.php'; ?>
</div>


<!--blog section -->

<section class="mb-5 position-relative blog-section">

    <div class="container">

        <div class="text-center mb-5">
            <h2 class="fw-bold">
                Explore Our <span class="gradient-text">Latest Articles</span>
            </h2>
            <p class="text-muted">
                Insights, guides, and tips to help you make smarter financial decisions.
            </p>
        </div>

        <!-- Desktop View -->
        <div class="row g-4 justify-content-center d-none d-md-flex">

            <!-- Blog Card -->
            <div class="col-md-4">
                <div class="main-blog-card">
                <article class="blog-card">

                    <div class="blog-image">
                        <img src="assets/images/loan-property.webp" alt="Loan Against Property Complete Guide" loading="lazy">
                    </div>

                    <div class="blog-content">

                        <div class="blog-meta">
                            <span>5 min read</span>
                        </div>

                        <h5 class="blog-title">
                           What Is Loan Against Property? Complete Guide 2026
                        </h5>

                        <p class="blog-excerpt">
                           A Loan Against Property (LAP) is a secured loan in India where you mortgage your residential or commercial property to borrow funds from a bank or financial institution.
                        </p>

                        <a href="guide-to-lap" class="read-more">
                            Read Article →
                        </a>

                    </div>

                </article>
                </div>
            </div>

            <!-- Blog Card -->
            <div class="col-md-4">
                <div class="main-blog-card">
                <article class="blog-card">

                    <div class="blog-image">
                        <img src="assets/images/shop-growth.webp" alt="Shopkeepers Business Loan Growth Tips" loading="lazy">
                    </div>

                    <div class="blog-content">

                        <div class="blog-meta">
                            <span>4 min read</span>
                        </div>

                        <h5 class="blog-title">
                            Shopkeepers Loan Growth
                        </h5>

                        <p class="blog-excerpt">
                            A shopkeepers business loan helps you expand your store, manage inventory, and handle daily expenses while keeping your cash flow stable.
                        </p>

                        <a href="shopkeepers-loan-growth" class="read-more">
                            Read Article →
                        </a>

                    </div>

                </article>
                </div>
            </div>

            <!-- Blog Card -->
            <div class="col-md-4">
                <div class="main-blog-card">
                <article class="blog-card">

                    <div class="blog-image">
                        <img src="assets/images/bus-loan.webp" alt="Business Loans Startup Growth Guide" loading="lazy">
                    </div>

                    <div class="blog-content">

                        <div class="blog-meta">
                            <span>6 min read</span>
                        </div>

                        <h5 class="blog-title">
                            How Business Loans Help Grow Your Startup
                        </h5>

                        <p class="blog-excerpt">
                            Understand different types of business loans and how they
                            can help you scale your business faster.
                        </p>

                        <a href="business-loan-guide" class="read-more">
                            Read Article →
                        </a>

                    </div>

                </article>
                </div>
            </div>

        </div>

        <!-- Mobile Slider -->
        <div class="swiper blogSwiper d-md-none">
            <div class="swiper-wrapper">

                <!-- Slide 1 -->
                <div class="swiper-slide">
                    <div class="main-blog-card">
                        <article class="blog-card">

                            <div class="blog-image">
                                <img src="assets/images/loan-property.webp" alt="Loan Against Property Complete Guide" loading="lazy">
                            </div>

                            <div class="blog-content">

                                <div class="blog-meta">
                                    <span>5 min read</span>
                                </div>

                                <h5 class="blog-title">
                                   What Is Loan Against Property? Complete Guide 2026
                                 </h5>

                                <p class="blog-excerpt">
                                    Learn how home loans work, interest rates, eligibility criteria,
                                    and tips to get your loan approved faster.
                                </p>

                                <a href="guide-to-lap" class="read-more">
                                    Read Article →
                                </a>

                            </div>

                        </article>
                    </div>
                </div>

                <!-- Slide 2 -->
                <div class="swiper-slide">
                    <div class="main-blog-card">
                        <article class="blog-card">

                            <div class="blog-image">
                                <img src="assets/images/shop-growth.webp" alt="Shopkeepers Business Loan Growth Tips" loading="lazy">
                            </div>

                            <div class="blog-content">

                                <div class="blog-meta">
                                    <span>4 min read</span>
                                </div>

                                <h5 class="blog-title">
                                 Shopkeepers Loan Growth
                                </h5>

                                <p class="blog-excerpt">
                                   A shopkeepers business loan helps you expand your store, manage inventory, and handle daily expenses while keeping your cash flow stable.
                                </p>

                                <a href="shopkeepers-loan-growth" class="read-more">
                                    Read Article →
                                </a>

                            </div>

                        </article>
                    </div>
                </div>

                <!-- Slide 3 -->
                <div class="swiper-slide">
                    <div class="main-blog-card">
                        <article class="blog-card">

                            <div class="blog-image">
                                <img src="assets/images/bus-loan.webp" alt="Business Loans Startup Growth Guide" loading="lazy">
                            </div>

                            <div class="blog-content">

                                <div class="blog-meta">
                                    <span>6 min read</span>
                                </div>

                                <h5 class="blog-title">
                                    How Business Loans Help Grow Your Startup
                                </h5>

                                <p class="blog-excerpt">
                                    Understand different types of business loans and how they
                                    can help you scale your business faster.
                                </p>

                                <a href="business-loan-guide" class="read-more">
                                    Read Article →
                                </a>

                            </div>

                        </article>
                    </div>
                </div>

            </div>

            <!-- Pagination -->
            <!-- <div class="swiper-pagination"></div> -->
        </div>

    </div>
</section>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" defer></script>

<script>
    var swiper = new Swiper(".blogSwiper", {
        slidesPerView: 1,
        spaceBetween: 20,
        loop: true,

        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },

        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
    });

    // Home Page Instant Loan Calculator Slider
    document.addEventListener("DOMContentLoaded", function() {
        const loanSlider = document.getElementById("loanSlider");
        const amountText = document.getElementById("amount");
        const emiText = document.getElementById("emi");

        if (loanSlider && amountText && emiText) {
            function calculateIndexEmi() {
                const p = parseFloat(loanSlider.value) || 250000;
                const r = 13 / 12 / 100; // 13% interest
                const n = 60; // 60 months
                const emi = (p * r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1);
                
                amountText.textContent = "₹ " + Number(p).toLocaleString('en-IN');
                emiText.textContent = "₹ " + Number(Math.round(emi)).toLocaleString('en-IN');
                
                // Dynamically color range track
                const min = parseFloat(loanSlider.min) || 50000;
                const max = parseFloat(loanSlider.max) || 3000000;
                const pct = (p - min) / (max - min) * 100;
                loanSlider.style.background = `linear-gradient(to right, #0056D6 0%, #0056D6 ${pct}%, #D9DEE7 ${pct}%, #D9DEE7 100%)`;
            }

            loanSlider.addEventListener("input", calculateIndexEmi);
            calculateIndexEmi(); // Initialize
        }
    });
</script>







<?php include 'includes/footer.php' ?>
