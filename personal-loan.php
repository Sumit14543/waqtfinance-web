<?php
$page_title = "Instant Personal Loan Online in India | Waqt Finance";
$meta_description = "Get collateral-free personal loans from 50,000 up to 25 Lakh with Waqt Finance. Instant online approval, competitive interest & flexible EMIs. Apply now!";
$meta_keywords = "personal loan in India, instant personal loan online, personal loan apply online, personal loan interest rates, quick personal loan India";
$canonical_url = "https://waqtfinance.com/personal-loan";
$meta_image = "assets/images/personal-loan.webp";
$meta_image_alt = "Personal loan in India by Waqt Finance";
$breadcrumb_label = "Personal Loan";

include 'includes/header.php';
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" media="print" onload="this.media='all'" />
<style>
    .why_wrap.py-5,
    .loan-calculator.py-5,
    .loan-charges.py-5,
    .eligibility-section.py-5,
    .faq-section.py-5,
    .blog-section.py-5,
    section.py-5.position-relative {
        padding-top: 24px !important;
        padding-bottom: 24px !important;
    }

    .text-center.mb-5 { margin-bottom: 24px !important; }
    .important-box.mt-5 { margin-top: 24px !important; }
    .eligibility-section .container.pt-5 { padding-top: 24px !important; }

    .why_wrap .col-lg-8.text-center {
        flex: 0 0 100%;
        width: 100%;
        max-width: 100%;
    }

    .why_wrap .col-lg-8.text-center > p {
        margin-bottom: 12px;
        line-height: 1.6;
        text-align: justify;
        text-justify: inter-word;
    }

    .loan-charges .text-center p,
    .process-section .process-h p,
    .eligibility-section .text-center > p,
    .blog-section .text-center > p {
        text-align: justify;
        text-justify: inter-word;
    }

    .why_wrap .choose_wrap { margin-top: 16px; }
    .final-cta-actions { display: flex; justify-content: center; flex-wrap: wrap; gap: 14px; margin-top: 20px; }
    .final-cta-actions .apply-btn,
    .final-cta-actions .apply-btn-secondary { min-width: 190px; text-align: center; }
    .related-loans-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-top: 20px; }
    .related-loan-card { display: block; height: 100%; padding: 18px 20px; border: 1px solid #d8e2f0; border-radius: 14px; background: #fff; color: #172b4d; text-decoration: none; transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
    .related-loan-card:hover { color: #18529c; border-color: #18529c; box-shadow: 0 8px 20px rgba(24, 82, 156, .12); transform: translateY(-2px); }
    .related-loan-card strong { display: block; margin-bottom: 6px; color: #18529c; font-size: 18px; }
    .related-loan-card span { display: block; color: #4c5b70; line-height: 1.5; }
    .loan-charges .text-center p:last-child,
    .process-section .process_wrap,
    .faq-section .accordion { margin-bottom: 0; }

    @media (max-width: 767.98px) {
        .why_wrap.py-5,
        .loan-calculator.py-5,
        .loan-charges.py-5,
        .eligibility-section.py-5,
        .faq-section.py-5,
        .blog-section.py-5,
        section.py-5.position-relative {
            padding-top: 20px !important;
            padding-bottom: 20px !important;
        }
        .related-loans-grid { grid-template-columns: 1fr; gap: 12px; }
    }
</style>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "LoanOrCredit",
    "name": "Instant Personal Loan in India",
    "url": "https://waqtfinance.com/personal-loan",
    "description": "Get collateral-free personal loans from ₹50,000 up to ₹25 Lakh with Waqt Finance. Instant online approval, competitive interest & flexible EMIs.",
    "provider": {
        "@id": "https://waqtfinance.com/#organization"
    },
    "loanType": "Personal Loan",
    "areaServed": {
        "@type": "Country",
        "name": "India"
    },
    "amount": {
        "@type": "MonetaryAmount",
        "currency": "INR",
        "minValue": 50000,
        "maxValue": 2500000
    },
    "loanTerm": {
        "@type": "QuantitativeValue",
        "minValue": 12,
        "maxValue": 60,
        "unitCode": "MON"
    },
    "annualPercentageRate": {
        "@type": "QuantitativeValue",
        "minValue": 10.49,
        "maxValue": 24,
        "unitText": "PERCENT"
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
            "name": "What documents are required for a personal loan?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "You need basic KYC documents (PAN Card and Aadhaar Card), address proof, last 3 to 6 months bank statements, and income proof (salary slips for salaried applicants or ITR for self-employed individuals)."
            }
        },
        {
            "@type": "Question",
            "name": "How fast is personal loan approval and disbursal at Waqt Finance?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Eligible personal loan applications are typically approved and disbursed within 24 to 48 hours after digital document verification and agreement e-signing."
            }
        },
        {
            "@type": "Question",
            "name": "What is the personal loan interest rate and tenure?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Personal loan interest rates at Waqt Finance start from 10.49% p.a. (ranging up to 24% p.a. based on credit score and income profile) with flexible repayment tenures from 12 to 60 months."
            }
        },
        {
            "@type": "Question",
            "name": "Can I prepay or foreclose my personal loan early?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Yes, you can prepay or foreclose your personal loan after paying 6 monthly EMIs, subject to applicable terms in your sanction letter."
            }
        },
        {
            "@type": "Question",
            "name": "What is the minimum CIBIL score required for a personal loan?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "A CIBIL score of 700 or above is preferred for the best personal loan interest rates, though applications are evaluated holistically based on monthly income and repayment capacity."
            }
        },
        {
            "@type": "Question",
            "name": "How much personal loan amount can I borrow online?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Eligible salaried and self-employed individuals can borrow between ₹50,000 and ₹25 Lakh without any collateral or guarantor."
            }
        }
    ]
}
</script>
<?php include 'includes/main-header.php' ?>
<section class="prop-sec">
    <!-- Left -->
    <div class="prop-left">

        <div class="prop-text">

            <div class="prop-line"></div>

            <h1>Instant Personal Loan in India for Salaried & Self-Employed</h1>


            <p>
                An unexpected medical bill, a wedding around the corner, or a home that needs work — some expenses just don't wait. Apply Personal Loan Online with Waqt Finance and borrow up to ₹25 Lakhs, whether you're salaried or self-employed. No collateral, a short list of documents, and an application you can finish from your phone.
            </p>

            <a href="personal-loan-apply" class="prop-btn">Apply Now</a>

        </div>

    </div>

    <!-- Right -->
    <div class="prop-right">
        <img src="assets/images/ppp-img.webp" alt="Image">
    </div>
</section>

<section class="why_wrap py-5 position-relative">
    <div class="container"><div class="row justify-content-center"><div class="col-lg-8 text-center">
        <h2>What is a <span style="color: #18529c;">Personal Loan?</span></h2>
        <p class="mt-2">Strip away the jargon, and a Personal Loan is simply money borrowed against your word — your income and your credit history — rather than against anything you own. There's no house, no gold, no asset sitting as security. You borrow, you use the funds however your situation calls for, and you pay it back in fixed monthly instalments over a tenure you pick yourself.</p>
        <p>Life's bigger expenses rarely announce themselves in advance. A hospital visit, a wedding date that's already set, a repair that can't be postponed, or a stack of smaller debts you'd rather fold into one — a Personal Loan in India exists for exactly these moments. It turns a sudden need for cash into something you can actually plan around, instead of a scramble.</p>
        <p>At Waqt Finance, the whole thing happens online, start to finish — from the moment you apply to the moment funds land in your account. This is Personal Loan Online done the way it should be: no branch visits, no piles of forms, just a process built for someone who needs money now, not next week.</p>
    </div></div></div>
</section>

<section class="why_wrap py-5 position-relative">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 style="color: #000; font-weight:400;">Personal Loan <span style="color: #18529c;">Features & Benefits</span></h2>
                <p class="mt-2">Here's what actually stands out about a Waqt Finance Personal Loan, beyond the basic pitch.</p>
            </div>
        </div>

        <div class="choose_wrap">

            <div class="choose_card">
                <h3>High Personal Loan Amount</h3>
                <p>
                    Our personal loan services provide access to <strong>higher loan amounts</strong> based on your
                    <span>income level, credit profile, and repayment capacity</span>. Eligible applicants can apply
                    for loans up to <strong>₹25 Lakhs</strong>, making it easier to manage major financial needs
                    without
                    affecting savings or long-term investments.
                </p>

                <ul>
                    <li><strong>Loan Amount:</strong> Up to ₹25 Lakhs depending on eligibility.</li>
                    <li><strong>Flexible Eligibility:</strong> Based on salary, credit score, and employment
                        stability.</li>
                    <li><strong>Suitable For:</strong> Wedding expenses, education, travel, business expansion, medical emergencies, and
                        home renovation.</li>
                </ul>
            </div>


            <div class="choose_card">
                <h3>Instant Personal Loan Approval</h3>
                <p>
                    Our <strong>digital personal loan application process</strong> allows borrowers to apply online
                    quickly with minimal steps. Once basic details such as employment information,
                    <span>income proof and identity verification</span> are submitted, the system evaluates your
                    eligibility and processes approval in a short time.
                </p>

                <ul>
                    <li><strong>Simple Online Application</strong> with easy form submission.</li>
                    <li><strong>Quick Verification</strong> through digital documentation.</li>
                    <li><strong>Fast Disbursal</strong> directly into your bank account.</li>
                </ul>
            </div>


            <div class="choose_card">
                <h3>No Collateral Required</h3>
                <p>
                    Personal loans are <strong>unsecured financial products</strong>, meaning borrowers do not need
                    to
                    pledge any valuable asset such as <span>property, gold, or investments</span>. This makes
                    personal
                    loans a convenient option for individuals seeking quick financial assistance.
                </p>

                <ul>
                    <li><strong>No Asset Pledge:</strong> No property or security required.</li>
                    <li><strong>Quick Processing:</strong> Faster approval compared to secured loans.</li>
                    <li><strong>Eligibility Based On:</strong> Credit score, monthly income, and repayment capacity.
                    </li>
                </ul>
            </div>


            <div class="choose_card">
                <h3>Minimal Documentation</h3>
                <p>
                    Applying for a personal loan requires only <strong>basic documentation</strong>, making the
                    process
                    simple and hassle-free. Most lenders require essential documents such as
                    <span>identity proof, address proof, salary slips, and bank statements </span>.
                </p>

                <ul>
                    <li><strong>Identity Proof:</strong> Aadhaar Card / PAN Card / Passport.</li>
                    <li><strong>Address Proof:</strong> Utility bills or government ID.</li>
                    <li><strong>Income Proof:</strong> Salary slips and bank statements ITR.</li>
                </ul>
            </div>


            <div class="choose_card">
                <h3>Flexible Repayment Tenure</h3>
                <p>
                    Personal loans come with <strong>flexible repayment options</strong> that allow borrowers to
                    choose
                    a tenure according to their financial comfort. Loan repayment periods usually range from
                    <span>12 months to 60 months</span>.
                </p>

                <ul>
                    <li><strong>Repayment Period:</strong> 12 to 60 months.</li>
                    <li><strong>EMI Planning:</strong> Estimate monthly installments using an EMI calculator.</li>
                    <li><strong>Financial Flexibility:</strong> Manage repayments according to monthly budget.</li>
                </ul>
            </div>
            <div class="choose_card">
                <h3>Personal Loan – A Quick and Flexible Financial Solution</h3>
                <p>
                    A <strong>personal loan</strong> is one of the most convenient financial solutions for
                    individuals
                    who require immediate funds for personal expenses. Since personal loans are
                    <span>unsecured loans</span>, borrowers do not need to provide collateral.
                </p>

                <ul>
                    <li><strong>Easy Application:</strong> Apply online within minutes.</li>
                    <li><strong>Quick Approval:</strong> Based on income and credit profile.</li>
                    <li><strong>Common Uses:</strong> Medical emergencies, weddings, travel, or education.</li>
                </ul>
            </div>


            <div class="choose_card">
                <h3>Why Personal Loans Are Popular Among Salaried Employees</h3>
                <p>
                    Personal loans are especially beneficial for <strong>salaried professionals</strong> who
                    have a stable monthly income. Lenders often offer <span>competitive interest rates</span>
                    and quicker approvals for salaried applicants.
                </p>

                <ul>
                    <li><strong>Stable Income Advantage</strong> improves eligibility.</li>
                    <li><strong>Competitive Interest Rates</strong> for salaried borrowers.</li>
                    <li><strong>Easy EMI Planning</strong> with online EMI calculators.</li>
                </ul>
            </div>


            <div class="choose_card">
                <h3>Responsible Borrowing and Financial Planning</h3>
                <p>
                    While personal loans offer quick financial assistance, borrowers should always
                    practice <strong>responsible borrowing</strong>. Evaluating income, expenses,
                    and existing liabilities helps maintain financial stability.
                </p>

                <ul>
                    <li><strong>Check EMI Affordability</strong> before applying.</li>
                    <li><strong>Maintain Good Credit Score</strong> with timely repayments.</li>
                    <li><strong>Plan Finances Wisely</strong> to avoid unnecessary debt.</li>
                </ul>
            </div>

        </div>
    </div>
</section>

<section class="why_wrap py-5 position-relative section-light">
    <div class="container">
        <div class="row justify-content-center"><div class="col-lg-8 text-center"><h2>Common Uses of a <span style="color: #18529c;">Personal Loan</span></h2><p class="mt-2">Few financial products flex across as many real-life situations as a Personal Loan does.</p></div></div>
        <div class="choose_wrap">
            <div class="choose_card"><h3>Medical Emergency</h3><p>Handle hospital bills the moment they arrive, instead of waiting on insurance paperwork or draining your emergency fund.</p></div>
            <div class="choose_card"><h3>Wedding</h3><p>Cover wedding costs on your own schedule, without leaning too hard on family savings.</p></div>
            <div class="choose_card"><h3>Home Renovation</h3><p>Fix up or upgrade your home without touching money you've set aside for other long-term goals.</p></div>
            <div class="choose_card"><h3>Education</h3><p>Handle tuition, exam fees, or course costs for yourself or someone in your family.</p></div>
            <div class="choose_card"><h3>Debt Consolidation</h3><p>Fold several high-interest debts into one predictable EMI, making your monthly repayments far simpler to track.</p></div>
        </div>
        <div class="row justify-content-center"><div class="col-lg-8 text-center"><p class="mt-4">Whatever's driving the need, a Personal Loan hands you the flexibility to act now and pay it back on your own terms — no asset changes hands to make that happen.</p></div></div>
    </div>
</section>

<section class="process-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 text-center process-h">
                <h2>How to Apply for a <span>Personal Loan Online</span></h2>
                <p>Four steps, start to finish, with Waqt Finance — no branch visit required anywhere along the way.</p>
            </div>
        </div>
        <div class="process_wrap">
            <div class="process_card">
                <div class="process_icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                        <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                        <path d="M10 9H8"></path>
                        <path d="M16 13H8"></path>
                        <path d="M16 17H8"></path>
                    </svg>
                </div>
                <h3>Submit Application</h3>
                <p>Fill out our simple online form with basic details and required documents.</p>
                <span class="process-badge">⏱ 5 minutes</span>
            </div>
            <div class="process_card">
                <div class="process_icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                </div>
                <h3>Document Verification</h3>
                <p>Our team verifies your documents and assesses your loan eligibility.</p>
                <span class="process-badge">⏱ 5 24-48 hours</span>
            </div>
            <div class="process_card">
                <div class="process_icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="m9 12 2 2 4-4"></path>
                    </svg>
                </div>
                <h3>Loan Approval</h3>
                <p>Receive approval notification with loan amount and terms confirmation.</p>
                <span class="process-badge">⏱ 5 Same day</span>
            </div>
            <div class="process_card">
                <div class="process_icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                        <line x1="2" x2="22" y1="10" y2="10"></line>
                    </svg>
                </div>
                <h3>Fund Disbursement</h3>
                <p>Funds are transferred directly to your bank account after e-signing.</p>
                <span class="process-badge">⏱ Within 24 hours</span>
            </div>
        </div>

        <div class="text-center mt-3">
            <div class="sub-title">
                Average disbursement time: 24-48 hours
            </div>
        </div>
    </div>
</section>

<section class="loan-calculator py-5 section-white">

    <div class="text-center mb-5">
        <h2>Personal Loan <span style="color: #18529c;">EMI Calculator</span></h2>
        <p>Calculate your monthly EMI and plan your personal loan repayment</p>
    </div>

    <?php include 'home-content/personal-cal.php' ?>
</section>

<section class="py-5 position-relative section-light">
    <div class="bg-wrapper">
        <div class="bg-blob blob-1"></div>
        <div class="bg-blob blob-2"></div>
    </div>
    <div class="container">

        <div class="text-center mb-5">
            <h2 class="fw-bold">What Affects Your <span style="color: #18529c;">Interest Rate</span></h2>
            <p class="text-muted">
                Understand the factors that determine your loan interest rate
            </p>
        </div>

        <div class="row g-4">

            <!-- LEFT SIDE -->
            <div class="col-lg-6">

                <div class="ui-card">

                    <h5 class="fw-semibold mb-4">Estimate Your Rate</h5>

                    <div class="mb-4">

                        <div class="d-flex justify-content-between mb-2">
                            <span>Your CIBIL Score</span>
                            <span>
                                <strong id="scoreText">700</strong>
                                <span id="ratingText">(Good)</span>
                            </span>
                        </div>

                        <input type="range" id="scoreSlider" class="range-slider" min="300" max="900" step="10"
                            value="700">

                        <div class="d-flex justify-content-between small text-muted mt-2">
                            <span>300</span>
                            <span>600</span>
                            <span>750</span>
                            <span>900</span>
                        </div>

                    </div>

                    <div class="rate-box mb-4">

                        <div class="small text-muted">Estimated Interest Rate</div>

                        <div class="rate-value" id="rateValue">17.25% p.a.</div>

                        <div class="small text-muted mt-1">
                            Based on your CIBIL score
                        </div>

                    </div>

                    <div>

                        <div class="d-flex justify-content-between small mb-2">
                            <span>Best Rate</span>
                            <span>Maximum Rate</span>
                        </div>

                        <div class="rate-bar mb-2">

                            <div id="rateDot" class="rate-dot"></div>

                        </div>

                        <div class="d-flex justify-content-between small fw-bold">
                            <span class="text-success">10.49%</span>
                            <span class="text-danger">24%</span>
                        </div>

                    </div>

                </div>

            </div>

            <!-- RIGHT SIDE -->
            <div class="col-lg-6">

                <div class="row g-3 mb-3">

                    <div class="col-lg-6 col-12   ">
                        <div class="ui-card">
                            <div class="factor-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                                    <line x1="2" x2="22" y1="10" y2="10"></line>
                                </svg>
                            </div>
                            <h6>CIBIL Score</h6>
                            <p class="small text-muted">
                                Higher credit scores get better rates
                            </p>
                            <span class="badge-pill high-impact">High Impact</span>
                        </div>
                    </div>

                    <div class="col-lg-6 col-12">
                        <div class="ui-card">
                            <div class="factor-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                    <rect width="20" height="14" x="2" y="6" rx="2"></rect>
                                </svg>
                            </div>
                            <h6>Employment</h6>
                            <p class="small text-muted">
                                Salaried employees get lower rates
                            </p>
                            <span class="badge-pill medium-impact">Medium Impact</span>
                        </div>
                    </div>

                    <div class="col-lg-6 col-12">
                        <div class="ui-card">
                            <div class="factor-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path
                                        d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z">
                                    </path>
                                    <path d="m9 12 2 2 4-4"></path>
                                </svg>
                            </div>
                            <h6>Income Level</h6>
                            <p class="small text-muted">
                                Higher income improves approval
                            </p>
                            <span class="badge-pill high-impact">High Impact</span>
                        </div>
                    </div>

                    <div class="col-lg-6 col-12">
                        <div class="ui-card">
                            <div class="factor-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <polyline points="22 17 13.5 8.5 8.5 13.5 2 7"></polyline>
                                    <polyline points="16 17 22 17 22 11"></polyline>
                                </svg>
                            </div>
                            <h6>Existing Loans</h6>
                            <p class="small text-muted">
                                Lower debt improves rate
                            </p>
                            <span class="badge-pill medium-impact">Medium Impact</span>
                        </div>
                    </div>

                </div>

                <div class="ui-card">

                    <h6 class="fw-semibold mb-3">
                        💡 Tips to Get Lower Rates
                    </h6>

                    <ul class="tip-list small text-muted">
                        <li>Maintain CIBIL score above 750</li>
                        <li>Clear existing debts</li>
                        <li>Choose shorter tenure</li>
                        <li>Add co-applicant</li>
                        <li>Apply with your main bank</li>
                    </ul>

                    <a href="#" class="btn btn-danger w-100 mt-2">
                        Check Your Eligibility →
                    </a>

                </div>

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
                Charges & <span class="gradient-text">Fees Disclosure</span>
            </h2>
            <p class="text-muted">
                Your rate lands somewhere between 10.49% and 24% per annum, shaped mainly by your credit score, but also by your employment type, income, and any existing debt. As a rough benchmark, someone with a credit score around 700 typically lands near 17.25% p.a. — push that score past 750, and you're looking at meaningfully better terms.
            </p>
            <p class="text-muted">Other charges are spelled out in your loan agreement. As a registered NBFC, every fee is disclosed upfront, in keeping with RBI expectations around transparent lending — nothing gets added after the fact.</p>
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
                            stroke-linejoin="round" style="color: #2b82d8; width: 25px; margin-top: -5px;"
                            class="me-1">
                            <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                            <line x1="2" x2="22" y1="10" y2="10"></line>
                        </svg>
                        Other Applicable Charges
                    </h5>

                    <div class="charge-row d-flex flex-column flex-md-row justify-content-between align-items-center">
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
                        <span class="text-muted">₹500 + 18% GST</span>
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
                        <span class="text-muted">36% p.a on (POS)</span>
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
                        <span class="text-muted">As per state regulations</span>
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
                        <span class="text-muted">₹500 – ₹5,000</span>
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

            <ul class="small" style="list-style: disc; padding-left: 20px;">

                <li class="mb-1">Minimum lock-in period of 6 months applies to all loans</li>
                <li class="mb-1">Part prepayment minimum amount: ₹10,000 or 1 EMI (whichever is higher)</li>
                <li class="mb-1">Foreclosure/prepayment requests must be submitted 15 days in advance</li>
                <li class="mb-1">NOC will be issued within 7 working days after full repayment</li>
                <li>All charges are subject to change as per RBI guidelines</li>

            </ul>

        </div>

    </div>
</div>

<section class="py-5 position-relative eligibility-section">
    <div class="bg-wrapper">
        <div class="bg-blob blob-1"></div>
        <div class="bg-blob blob-2"></div>
    </div>
    <div class="container">

        <!-- Heading -->
        <div class="text-center mb-5">

            <h2 class="fw-bold">
                Eligibility <span class="gradient-text">Criteria</span>
            </h2>

            <p class="text-muted">
                Whether you qualify — and on what terms — comes down to how you earn your income.
            </p>

        </div>

        <div class="row g-4 justify-content-center">

            <!-- Salaried Card -->
            <div class="col-lg-5">

                <div class="ui-card">

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="badge-number">1</div>
                        <h5 class="fw-semibold m-0">Salaried Individuals</h5>
                    </div>

                    <ul class="check-list">

                        <li>Age: 21–58 years</li>
                        <li>Minimum income: ₹25,000/month</li>
                        <li>Work experience: 1+ year (6 months in current job)</li>
                        <li>CIBIL Score: 700+</li>

                    </ul>

                </div>
            </div>

            <!-- Self Employed Card -->
            <div class="col-lg-5">

                <div class="ui-card">

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="badge-number">2</div>
                        <h5 class="fw-semibold m-0">Self-Employed</h5>
                    </div>

                    <ul class="check-list">

                        <li>Age: 25–65 years</li>
                        <li>Business vintage: 3+ years</li>
                        <li>Minimum turnover: ₹10 Lakhs/year</li>
                        <li>ITR for last 2 years</li>

                    </ul>

                </div>
            </div>

        </div>

        <p class="text-muted text-center mt-4">Whether your income comes from a Salary Account or your own business, what matters in the end is the same: consistent income, a clean credit history, and documentation that backs both up. A stronger credit score tends to smooth the path toward approval — though every application still gets evaluated on its own merits, subject to eligibility and verification.</p>

        <!-- CTA -->
        <div class="text-center mt-5">

            <a href="#" class="apply-btn btn-lg py-3  w-100 w-md-auto" style="font-size:18px;">

                Check Your Eligibility →

            </a>

        </div>

    </div>

    <div class="container pt-5 mt-2">

        <!-- Heading -->
        <div class="text-center mb-5">

            <h2 class="fw-bold">
                Required <span class="gradient-text">Documents</span>
            </h2>

            <p class="text-muted">
                Have these ready before you start, and the whole process moves noticeably faster.
            </p>

        </div>

        <div class="row g-4">

            <!-- Identity Proof -->
            <div class="col-md-4">

                <div class="ui-card">

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
                        <li>Passport</li>
                        <li>Voter ID</li>

                    </ul>

                </div>
            </div>

            <!-- Address Proof -->
            <div class="col-md-4">

                <div class="ui-card">

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
                        <li>Rent Agreement</li>
                        <li>Aadhaar Card</li>

                    </ul>

                </div>
            </div>

            <!-- Income Proof -->
            <div class="col-md-4">

                <div class="ui-card">

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
                        Income Proof
                    </h5>

                    <ul class="doc-list">

                        <li>Last 3 months salary slips</li>
                        <li>Bank statements (6 months)</li>
                        <li>Form 16 / ITR</li>

                    </ul>

                </div>
            </div>

        </div>

        <p class="text-muted text-center mt-4 mb-0">None of this is paperwork for its own sake — it's simply what's needed to confirm who you are, where you live, and what you earn.</p>

    </div>
</section>

<section class="why_wrap py-5 position-relative">
    <div class="container"><div class="row justify-content-center"><div class="col-lg-8 text-center">
        <h2>Loan Amount, <span style="color: #18529c;">EMI & Repayment</span></h2>
        <p class="mt-2">How much you can borrow depends on your income, credit standing, and repayment capacity — with well-qualified applicants able to access up to ₹25 Lakhs, sized to whatever the actual need is.</p>
        <p>Tenure runs from 12 to 60 months, so you can pick a repayment window that genuinely suits your monthly budget. Shorten it, and you'll pay less interest overall; stretch it out, and your monthly instalment gets lighter.</p>
        <p>For a smaller, more immediate need, a shorter-term version of this same loan keeps the total interest cost down — and applying for it online is just as fast as any other Personal Loan. Compared to other short-term borrowing options, this one is built specifically around your income and credit standing, not around any asset you'd otherwise need to pledge.</p>
        <p>Run the numbers through our EMI calculator before you apply — it breaks down exactly how much goes toward principal versus interest each month. And if you're ahead of schedule financially, part-prepayment kicks in from ₹10,000 or one EMI, whichever is higher.</p>
    </div></div></div>
</section>

<section class="why_wrap py-5 position-relative section-light">
    <div class="container"><div class="row justify-content-center"><div class="col-lg-8 text-center"><h2>Why Choose <span style="color: #18529c;">Waqt Finance?</span></h2><p class="mt-2">The lender you pick matters just as much as the loan amount you're after. Waqt Finance is a registered NBFC, overseen by the Reserve Bank of India — meaning your application runs through a compliant, transparent process, not some informal arrangement.</p></div></div>
        <div class="choose_wrap"><div class="choose_card"><h3>Apply From Anywhere</h3><p>No branch visit required, at any point.</p></div><div class="choose_card"><h3>Paperwork Kept Minimal</h3><p>Only what's genuinely needed to confirm identity, address, and income.</p></div><div class="choose_card"><h3>Every Charge Disclosed</h3><p>Rates and fees laid out clearly before you commit to anything.</p></div><div class="choose_card"><h3>Data Handled Securely</h3><p>Encrypted systems protect your information at every stage.</p></div><div class="choose_card"><h3>Decisions That Move</h3><p>Once documents clear, approvals don't sit around.</p></div><div class="choose_card"><h3>Real Support, Not a Script</h3><p>Assistance available at every stage of your loan journey.</p></div><div class="choose_card"><h3>Lending That Considers You</h3><p>Loan amounts weighed against actual repayment capacity, not just the maximum you're technically eligible for.</p></div></div>
        <div class="row justify-content-center"><div class="col-lg-8 text-center"><p class="mt-4">Rated 4.8/5 by customers across India, with real approvals covering medical emergencies, weddings, and more. Salaried or self-employed, Waqt Finance is built to make this process fast, transparent, and genuinely low-stress.</p></div></div>
    </div>
</section>

<section class="testimonials py-2 section-light">

    <div class="container">

        <!-- Heading -->
        <div class="text-center mb-5">
            <h2 class="fw-bold">
                What Our <span class="gradient-text">Customers Say</span>
            </h2>
            <p class="text-muted">
                Hear from customers who trusted us with their personal loan needs.
            </p>
        </div>

        <!-- Slick Slider -->
        <div class="testimonial-slider">

            <div>
                <div class="ui-card">

                    <div class="d-flex justify-content-between mb-3">
                        <span class="star">★★★★★</span>
                        <div class="quote">❝</div>
                    </div>

                    <p class="text-muted fst-italic">
                        "Applied for a personal loan for medical expenses. The approval was quick and the
                        documentation process was very smooth."
                    </p>

                    <div class="d-flex align-items-center gap-3 mt-3">
                        <div class="avatar">A</div>
                        <div>
                            <strong>Amit Gupta</strong><br>
                            <small class="text-muted">Marketing Manager, Delhi</small>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between small">
                        <span>Loan Amount:</span>
                        <strong class="warning">₹4 Lakhs</strong>
                    </div>

                </div>
            </div>


            <div>
                <div class="ui-card">

                    <div class="d-flex justify-content-between mb-3">
                        <span class="star">★★★★★</span>
                        <div class="quote">❝</div>
                    </div>

                    <p class="text-muted fst-italic">
                        "The interest rate was reasonable and the team guided me throughout the process. Funds were
                        credited within 24 hours."
                    </p>

                    <div class="d-flex align-items-center gap-3 mt-3">
                        <div class="avatar">P</div>
                        <div>
                            <strong>Pooja Mehta</strong><br>
                            <small class="text-muted">HR Executive, Mumbai</small>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between small">
                        <span>Loan Amount:</span>
                        <strong class="warning">₹3 Lakhs</strong>
                    </div>

                </div>
            </div>


            <div>
                <div class="ui-card">

                    <div class="d-flex justify-content-between mb-3">
                        <span class="star">★★★★★</span>
                        <div class="quote">❝</div>
                    </div>

                    <p class="text-muted fst-italic">
                        "I needed funds for home renovation and the loan approval process was extremely fast. Highly
                        satisfied with the service."
                    </p>

                    <div class="d-flex align-items-center gap-3 mt-3">
                        <div class="avatar">R</div>
                        <div>
                            <strong>Rohit Agarwal</strong><br>
                            <small class="text-muted">Chartered Accountant, Jaipur</small>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between small">
                        <span>Loan Amount:</span>
                        <strong class="warning">₹6 Lakhs</strong>
                    </div>

                </div>
            </div>


            <div>
                <div class="ui-card">

                    <div class="d-flex justify-content-between mb-3">
                        <span class="star">★★★★★</span>
                        <div class="quote">❝</div>
                    </div>

                    <p class="text-muted fst-italic">
                        "The online application was simple and transparent. There were no hidden charges and the EMI
                        plan was very flexible."
                    </p>

                    <div class="d-flex align-items-center gap-3 mt-3">
                        <div class="avatar">S</div>
                        <div>
                            <strong>Sneha Kapoor</strong><br>
                            <small class="text-muted">Interior Designer, Chandigarh</small>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between small">
                        <span>Loan Amount:</span>
                        <strong class="warning">₹5 Lakhs</strong>
                    </div>

                </div>
            </div>


            <div>
                <div class="ui-card">

                    <div class="d-flex justify-content-between mb-3">
                        <span class="star">★★★★★</span>
                        <div class="quote">❝</div>
                    </div>

                    <p class="text-muted fst-italic">
                        "I compared several lenders before applying. This platform offered the best rate and quick
                        approval."
                    </p>

                    <div class="d-flex align-items-center gap-3 mt-3">
                        <div class="avatar">V</div>
                        <div>
                            <strong>Vikram Singh</strong><br>
                            <small class="text-muted">Business Owner, Lucknow</small>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between small">
                        <span>Loan Amount:</span>
                        <strong class="warning">₹8 Lakhs</strong>
                    </div>

                </div>
            </div>


            <div>
                <div class="ui-card">

                    <div class="d-flex justify-content-between mb-3">
                        <span class="star">★★★★★</span>
                        <div class="quote">❝</div>
                    </div>

                    <p class="text-muted fst-italic">
                        "The customer support team was very helpful and explained everything clearly before I
                        applied."
                    </p>

                    <div class="d-flex align-items-center gap-3 mt-3">
                        <div class="avatar">N</div>
                        <div>
                            <strong>Neha Sharma</strong><br>
                            <small class="text-muted">Teacher, Pune</small>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between small">
                        <span>Loan Amount:</span>
                        <strong class="warning">₹2.5 Lakhs</strong>
                    </div>

                </div>
            </div>


        </div>

        <p class="text-center small text-muted mt-4">
            Rated <strong>4.8/5</strong> by 2,000+ happy customers across India
        </p>
    </div>
</section>

<section class="why_wrap py-5 position-relative">
    <div class="container"><div class="row justify-content-center"><div class="col-lg-8 text-center"><h2>Things to Consider Before <span style="color: #18529c;">Applying</span></h2><p class="mt-2">A Personal Loan can genuinely help — but a few minutes of thought beforehand goes a long way.</p></div></div>
        <div class="choose_wrap"><div class="choose_card"><h3>Your Credit Score</h3><p>This single number shapes both your eligibility and the rate you're offered. Check it before applying, so there are no surprises.</p></div><div class="choose_card"><h3>Can Your Budget Absorb the EMI?</h3><p>Work out whether the monthly instalment fits comfortably alongside your existing expenses — not just whether you qualify for the maximum amount.</p></div><div class="choose_card"><h3>The Real Cost</h3><p>Factor in the processing fee and GST alongside the interest rate when working out what this loan actually costs you.</p></div><div class="choose_card"><h3>Tenure Trade-offs</h3><p>A longer tenure eases your EMI but adds up to more interest paid overall — pick the shortest tenure you can genuinely manage.</p></div><div class="choose_card"><h3>Borrow With a Purpose</h3><p>Take only what the situation actually calls for, rather than stretching for the full amount you're eligible for.</p></div></div>
        <div class="row justify-content-center"><div class="col-lg-8 text-center"><p class="mt-4">Spend a few minutes on these questions before you apply, and you'll likely end up with a loan that works in your favor rather than against it.</p></div></div>
    </div>
</section>

<section class="why_wrap py-5 position-relative section-light"><div class="container"><div class="row justify-content-center"><div class="col-lg-8 text-center"><h2>Personal Loan vs <span style="color: #18529c;">Credit Card Loan</span></h2><p class="mt-2">Both let you borrow without collateral — but they behave quite differently once you're actually using them.</p><p>If you're after a larger sum with a predictable monthly payment and a clear finish line, a Personal Loan is usually the more structured, often cheaper route. A credit card can work fine for smaller, short-lived needs — but let a balance ride for months, and it tends to cost more than a fixed-tenure Personal Loan would have.</p></div></div></div></section>

<section class="why_wrap py-5 position-relative"><div class="container"><div class="row justify-content-center"><div class="col-lg-8 text-center"><h2>Ready to Move Your <span style="color: #18529c;">Plans Forward?</span></h2><p class="mt-2">A medical emergency, a wedding, a home that needs work — whatever it is, it doesn't have to wait. Apply Personal Loan Online with Waqt Finance and get through a process that's built to be quick and straightforward.</p><div class="final-cta-actions"><a href="personal-loan-apply" class="apply-btn btn-lg py-3" style="font-size:18px;">Apply Now</a><a href="eligibility-calculator" class="apply-btn-secondary btn-lg py-3" style="font-size:18px;">Check Eligibility</a></div></div></div></div></section>

<div class="position-relative">
    <div class="bg-wrapper faq_blob">
        <div class="bg-blob blob-1"></div>
        <div class="bg-blob blob-2"></div>
    </div>
    <section id="faq" class="faq-section py-5 position-relative section-alt">
        <div class="container">

            <!-- Heading -->
            <div class="text-center mx-auto mb-5" style="max-width: 640px;">
                <h2 class="mt-3 mb-3">
                    Frequently Asked <span class="gradient-text">Questions</span>
                </h2>
                <p class="text-muted">
                    Everything you need to know about WaqtFinance.
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
                            What documents are required for a personal loan?
                        </button>
                    </h3>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            You need basic KYC documents (PAN Card and Aadhaar Card), address proof, last 3 to 6 months bank statements, and income proof (salary slips for salaried applicants or ITR for self-employed individuals).
                        </div>
                    </div>
                </div>

                <div class="accordion-item ui-card border-0 mb-3">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faq2">
                            How fast is personal loan approval and disbursal at Waqt Finance?
                        </button>
                    </h3>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Eligible personal loan applications are typically approved and disbursed within 24 to 48 hours after digital document verification and agreement e-signing.
                        </div>
                    </div>
                </div>

                <div class="accordion-item ui-card border-0 mb-3">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faq3">
                            What is the personal loan interest rate and tenure?
                        </button>
                    </h3>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Personal loan interest rates at Waqt Finance start from 10.49% p.a. (ranging up to 24% p.a. based on credit score and income profile) with flexible repayment tenures from 12 to 60 months.
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12">
                <div class="accordion-item ui-card border-0 mb-3">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faq4">
                            Can I prepay or foreclose my personal loan early?
                        </button>
                    </h3>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes, you can prepay or foreclose your personal loan after paying 6 monthly EMIs, subject to applicable terms in your sanction letter.
                        </div>
                    </div>
                </div>

                <div class="accordion-item ui-card border-0 mb-3">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faq5">
                            What is the minimum CIBIL score required for a personal loan?
                        </button>
                    </h3>
                    <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            A CIBIL score of 700 or above is preferred for the best personal loan interest rates, though applications are evaluated holistically based on monthly income and repayment capacity.
                        </div>
                    </div>
                </div>

                <div class="accordion-item ui-card border-0">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faq6">
                            How much personal loan amount can I borrow online?
                        </button>
                    </h3>
                    <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Eligible salaried and self-employed individuals can borrow between ₹50,000 and ₹25 Lakh without any collateral or guarantor.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
        </div>
</div>
</section>


<!--<section class="why_wrap py-5 position-relative section-light">-->
<!--    <div class="container">-->
<!--        <div class="text-center"><h2>Explore Related <span style="color: #18529c;">Loan Options</span></h2></div>-->
<!--        <div class="related-loans-grid">-->
<!--            <a class="related-loan-card" href="business-loan"><strong>Business Loan</strong><span>For working capital and business expansion.</span></a>-->
<!--            <a class="related-loan-card" href="pay-day-loan"><strong>Payday Loan</strong><span>Short-term salary advance for salaried individuals.</span></a>-->
<!--            <a class="related-loan-card" href="edi-loan"><strong>EDI Loan</strong><span>For immediate cash needs and short-term financial support.</span></a>-->
<!--            <a class="related-loan-card" href="loan-against-property"><strong>Loan Against Property</strong><span>Secured financing against residential or commercial property.</span></a>-->
<!--            <a class="related-loan-card" href="vehicle-loan"><strong>Vehicle Loan</strong><span>For purchasing a car or bike.</span></a>-->
<!--        </div>-->
<!--    </div>-->
<!--</section>-->

<section class="py-5 position-relative blog-section">

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

            <div class="col-md-4">
                <div class="main-blog-card">
                    <article class="blog-card">
                        <div class="blog-image">
                            <img src="assets/images/personal-8.webp" alt="Personal Loan Guide">
                        </div>
                        <div class="blog-content">
                            <div class="blog-meta">
                                <span>5 min read</span>
                            </div>
                            <h5 class="blog-title">
                                Mistake to Avoid While Taking Personal Loan
                            </h5>
                            <p class="blog-excerpt">
                                Always read the terms carefully to avoid hidden costs.
                            </p>
                            <a href="blog/mistake-to-avoid-personal-loan" class="read-more">
                                Read Article →
                            </a>
                        </div>
                    </article>
                </div>
            </div>

            <div class="col-md-4">
                <div class="main-blog-card">
                    <article class="blog-card">
                        <div class="blog-image">
                            <img src="assets/images/loan-interest.webp" alt="Personal Loan Interest">
                        </div>
                        <div class="blog-content">
                            <div class="blog-meta">
                                <span>4 min read</span>
                            </div>
                            <h5 class="blog-title">
                                How to get a personal loan with low interest rate
                            </h5>
                            <p class="blog-excerpt">
                                Getting a personal loan at a low interest rate can help you save a significant amount of money over time.
                            </p>
                            <a href="blog/low-interest-rate" class="read-more">
                                Read Article →
                            </a>
                        </div>
                    </article>
                </div>
            </div>

            <div class="col-md-4">
                <div class="main-blog-card">
                    <article class="blog-card">
                        <div class="blog-image">
                            <img src="assets/images/personal-11.webp" alt="CIBIL Score Personal Loan">
                        </div>
                        <div class="blog-content">
                            <div class="blog-meta">
                                <span>6 min read</span>
                            </div>
                            <h5 class="blog-title">
                                How CIBIL Score Affect Your Personal Loan.
                            </h5>
                            <p class="blog-excerpt">
                                Your CIBIL Score plays a key role in personal loan approval.
                            </p>
                            <a href="blog/how-cibil-score-affects-your-personal-loan" class="read-more">
                                Read Article →
                            </a>
                        </div>
                    </article>
                </div>
            </div>

        </div>

        <!-- Mobile Slider -->
        <div class="swiper blogSwiper d-block d-md-none">

            <div class="swiper-wrapper">

                <div class="swiper-slide">
                    <div class="main-blog-card">
                        <article class="blog-card">
                            <div class="blog-image">
                                <img src="assets/images/personal-8.webp" alt="Personal Loan Guide">
                            </div>
                            <div class="blog-content">
                                <div class="blog-meta">
                                    <span>5 min read</span>
                                </div>
                                <h5 class="blog-title">
                                    Mistake to Avoid While Taking Personal Loan
                                </h5>
                                <p class="blog-excerpt">
                                    Always read the terms carefully to avoid hidden costs.
                                </p>
                                <a href="blog/mistake-to-avoid-personal-loan" class="read-more">
                                    Read Article →
                                </a>
                            </div>
                        </article>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="main-blog-card">
                        <article class="blog-card">
                            <div class="blog-image">
                                <img src="assets/images/loan-interest.webp" alt="Personal Loan Interest">
                            </div>
                            <div class="blog-content">
                                <div class="blog-meta">
                                    <span>4 min read</span>
                                </div>
                                <h5 class="blog-title">
                                    How to get a personal loan with low interest rate
                                </h5>
                                <p class="blog-excerpt">
                                    Getting a personal loan at a low interest rate can help you save a significant amount of money over time.
                                </p>
                                <a href="blog/low-interest-rate" class="read-more">
                                    Read Article →
                                </a>
                            </div>
                        </article>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="main-blog-card">
                        <article class="blog-card">
                            <div class="blog-image">
                                <img src="assets/images/personal-11.webp" alt="CIBIL Score Personal Loan">
                            </div>
                            <div class="blog-content">
                                <div class="blog-meta">
                                    <span>6 min read</span>
                                </div>
                                <h5 class="blog-title">
                                    How CIBIL Score Affect Your Personal Loan.
                                </h5>
                                <p class="blog-excerpt">
                                    Your CIBIL Score plays a key role in personal loan approval.
                                </p>
                                <a href="blog/how-cibil-score-affects-your-personal-loan" class="read-more">
                                    Read Article →
                                </a>
                            </div>
                        </article>
                    </div>
                </div>

            </div>

        </div>

    </div>
</section>



<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" defer></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    if (typeof Swiper !== 'undefined') {
        var swiper = new Swiper(".blogSwiper", {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 3000,
            },
        });
    }
});
</script>
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

<script>
    const scoreSlider = document.getElementById("scoreSlider");
    const scoreText = document.getElementById("scoreText");
    const ratingText = document.getElementById("ratingText");
    const rateValue = document.getElementById("rateValue");
    const rateDot = document.getElementById("rateDot");

    function getRating(score) {
        if (score < 550) {
            return {
                label: "Poor",
                color: "#dc3545"
            }; // red
        } else if (score < 650) {
            return {
                label: "Fair",
                color: "#fd7e14"
            }; // orange
        } else if (score < 750) {
            return {
                label: "Good",
                color: "#ffc107"
            }; // yellow
        } else if (score < 850) {
            return {
                label: "Very Good",
                color: "#20c997"
            }; // green
        } else {
            return {
                label: "Excellent",
                color: "#198754"
            }; // dark green
        }
    }

    function getInterestRate(score) {
        // simple mapping (you can adjust logic)
        if (score < 550) return 24;
        if (score < 650) return 21;
        if (score < 750) return 17.25;
        if (score < 850) return 13.5;
        return 10.49;
    }

    function updateUI(score) {
        scoreText.textContent = score;

        const rating = getRating(score);
        ratingText.textContent = `(${rating.label})`;
        ratingText.style.color = rating.color;

        const rate = getInterestRate(score);
        rateValue.textContent = `${rate}% p.a.`;

        // Move dot on bar (0–100%)
        const percent = (score - 300) / (900 - 300) * 100;
        rateDot.style.left = percent + "%";

        // Update range slider active track color
        scoreSlider.style.background = `linear-gradient(to right, #18529c 0%, #18529c ${percent}%, #D9DEE7 ${percent}%, #D9DEE7 100%)`;
    }

    // init
    updateUI(scoreSlider.value);

    // on change
    scoreSlider.addEventListener("input", function() {
        updateUI(parseInt(this.value));
    });
</script>


<?php include 'includes/footer.php'; ?>
</body>

</html>
