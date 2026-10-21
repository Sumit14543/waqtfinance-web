<?php
$page_title = "Loan EMI Calculator: Calculate Monthly Installment | Waqt Finance";
$meta_description = "Calculate your exact monthly loan EMI, interest payout & total repayment schedule instantly with the Waqt Finance online EMI calculator. Plan now!";
$meta_keywords = "EMI calculator, loan EMI, monthly repayment calculator, loan interest calculator India";
$canonical_url = "https://waqtfinance.com/emi-calculator";

include 'includes/header.php';
include 'includes/main-header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<div class="container my-5">

    <div class="emi-cal-h text-center mt-4">
        <h1>EMI Calculator</h1>
        <p class="emi-cal-subtitle">Estimate your monthly EMI, total interest, and repayment amount before you apply. Adjust the loan amount, interest rate, and tenure to compare what fits your budget.</p>
    </div>

    <!-- Outer Gray Card wrapper matching the screenshot -->
    <div class="emi-outer-card">
        <div class="emi-trust-strip" aria-label="Calculator highlights">
            <span><i class="fa fa-calculator"></i> Instant EMI estimate</span>
            <span><i class="fa fa-line-chart"></i> Principal and interest split</span>
            <span><i class="fa fa-table"></i> Amortization schedule</span>
        </div>
        
        <!-- Tabs centered at the top -->
        <div class="emi-tabs-row">
            <div class="emi-tabs">
                <button class="active" data-type="personal">Personal</button>
                <button data-type="business">Business</button>
                <button data-type="home">Home</button>
                <button data-type="gold">Gold</button>
                <button data-type="vehicle">Vehicle</button>
            </div>
        </div>

        <!-- Main Body -->
        <div class="emi-main-body">

            <!-- Left Column: Sliders and Inputs -->
            <div class="emi-left-column">

                <div class="calculator-inp-wrap">
                    <div class="calculator-label-row">
                        <label for="loan" class="calculator-inp-label">Loan Amount</label>
                        <div class="num-inp-wrap">
                            <span class="input-prefix">₹</span>
                            <input type="text" class="calculator-num-inp" id="loan" value="2,50,000" inputmode="numeric" aria-label="Loan amount">
                        </div>
                    </div>
                    <div class="slider-container" style="position: relative;">
                        <div class="slider-tooltip" id="loanTooltip">₹0</div>
                        <input type="range" id="loanRange" class="emi-slider" aria-label="Loan amount slider">
                        <div class="min-max-wrap">
                            <span class="min-inp-amt" id="loanMin">₹10k</span>
                            <span class="max-inp-amt" id="loanMax">₹10 Cr</span>
                        </div>
                    </div>
                </div>

                <div class="calculator-inp-wrap">
                    <div class="calculator-label-row">
                        <label for="rate" class="calculator-inp-label">Interest Rate (p.a)</label>
                        <div class="num-inp-wrap">
                            <input type="text" class="calculator-num-inp" id="rate" value="10.5" inputmode="decimal" aria-label="Interest rate per annum">
                            <span class="input-suffix">%</span>
                        </div>
                    </div>
                    <div class="slider-container" style="position: relative;">
                        <div class="slider-tooltip" id="rateTooltip">0%</div>
                        <input type="range" id="rateRange" class="emi-slider" step="0.05" aria-label="Interest rate slider">
                        <div class="min-max-wrap">
                            <span class="min-inp-amt" id="rateMin">5%</span>
                            <span class="max-inp-amt" id="rateMax">25%</span>
                        </div>
                    </div>
                </div>

                <div class="calculator-inp-wrap">
                    <div class="calculator-label-row">
                        <label for="tenure" class="calculator-inp-label" id="tenureLabel">Tenure (Years)</label>
                        <div class="num-inp-wrap">
                            <input type="text" class="calculator-num-inp" id="tenure" value="5" inputmode="numeric" aria-label="Loan tenure">
                            <span class="input-suffix" id="tenureSuffix">Yr</span>
                        </div>
                    </div>
                    <div class="slider-container" style="position: relative;">
                        <div class="slider-tooltip" id="tenureTooltip">0 Yr</div>
                        <input type="range" id="tenureRange" class="emi-slider" aria-label="Loan tenure slider">
                        <div class="min-max-wrap">
                            <span class="min-inp-amt" id="tenureMin">1</span>
                            <span class="max-inp-amt" id="tenureMax">30</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Premium Live Summary Card -->
            <div class="emi-right-column">
                <div class="emi-summary-card">
                    <!-- Top section: Hero Monthly EMI -->
                    <div class="emi-result-cta-box" style="margin-bottom: 20px;">
                        <div class="monthly-emi-label" id="emiLabel">Monthly EMI</div>
                        <div class="monthly-emi-value" id="emi">₹0</div>
                        <p class="emi-result-note">Indicative estimate. Final loan terms depend on eligibility, verification, and applicable policy checks.</p>
                    </div>

                    <!-- Donut Chart -->
                    <div class="emi-chart-section">
                        <div class="emi-chart-wrapper">
                            <svg width="160" height="160" viewBox="0 0 36 36" class="donut-svg">
                                <circle cx="18" cy="18" r="15.9155" fill="transparent" stroke="#F1F5F9" stroke-width="3"></circle>
                                <circle id="donutPrincipal" cx="18" cy="18" r="15.9155" fill="transparent" stroke="#0056D6" stroke-width="3" stroke-dasharray="100 0" stroke-dashoffset="25"></circle>
                                <circle id="donutInterest" cx="18" cy="18" r="15.9155" fill="transparent" stroke="#93C5FD" stroke-width="3" stroke-dasharray="0 100" stroke-dashoffset="25"></circle>
                            </svg>
                            <div class="emi-chart-center-text">
                                <span class="emi-center-title">Total Payable</span>
                                <span class="emi-center-value" id="total">₹0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Breakdown Numbers -->
                    <div class="emi-breakdown-section">
                        <div class="breakdown-row">
                            <span class="breakdown-label">
                                <span class="legend-dot dot-principal"></span> Principal Amount
                            </span>
                            <span class="breakdown-val" id="principalAmt">₹0</span>
                        </div>
                        <div class="breakdown-row">
                            <span class="breakdown-label">
                                <span class="legend-dot dot-interest"></span> Interest Amount
                            </span>
                            <span class="breakdown-val" id="interestAmt">₹0</span>
                        </div>
                        <div class="breakdown-row" style="margin-top: 8px; border-top: 1px dashed #E2E8F0; padding-top: 8px;">
                            <span class="breakdown-label">Interest Share</span>
                            <span class="breakdown-val" id="interestPercentVal">0%</span>
                        </div>
                    </div>

                    <!-- CTAs Group -->
                    <div class="emi-action-buttons">
                        <a href="personal-loan" id="applyBtn" class="btn-action-primary">Apply Now</a>
                        <div class="btn-secondary-grid">
                            <button type="button" id="resetCalculatorBtn" class="btn-action-secondary"><i class="fa fa-refresh"></i> Reset</button>
                            <a href="tel:9217086610" class="btn-action-secondary"><i class="fa fa-phone"></i> Talk to Expert</a>
                            <button onclick="window.print()" class="btn-action-secondary"><i class="fa fa-download"></i> Download</button>
                            <button onclick="navigator.clipboard.writeText(window.location.href); alert('Link copied to clipboard!');" class="btn-action-secondary"><i class="fa fa-share-alt"></i> Share</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <section class="emi-comparison-section" aria-labelledby="emiCompareTitle">
        <div class="emi-comparison-header">
            <div>
                <span class="emi-section-kicker">Compare Plans</span>
                <h2 id="emiCompareTitle">Compare EMI Options</h2>
            </div>
            <p>Review your current selection against a lower monthly EMI and a lower total interest option.</p>
        </div>
        <div class="emi-comparison-grid" id="emiComparisonGrid"></div>
    </section>

    <div class="emi-mobile-sticky" aria-live="polite">
        <div>
            <span>Estimated EMI</span>
            <strong id="mobileStickyEmi">₹0</strong>
        </div>
        <a href="personal-loan" id="mobileStickyApply">Apply</a>
    </div>

    <!-- Premium Loan Amortization Schedule Section -->
    <div class="emi-schedule-container" id="amortizationSection">
        <div class="schedule-header">
            <div class="schedule-heading-copy">
                <h2 class="schedule-title">Loan Amortization Schedule</h2>
                <p class="schedule-subtitle">See how each EMI is divided into Principal and Interest over the entire loan tenure.</p>
            </div>
            <div class="schedule-view-toggle" aria-label="Schedule view">
                <button type="button" class="active" data-schedule-view="monthly">Monthly</button>
                <button type="button" data-schedule-view="yearly">Yearly</button>
            </div>
        </div>

        <!-- Summary Bar (Stat Cards) -->
        <div class="schedule-summary-bar">
            <div class="stat-card">
                <span class="stat-label">Loan Amount</span>
                <span class="stat-val" id="summaryLoan">₹0</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Interest Rate</span>
                <span class="stat-val" id="summaryRate">0%</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Tenure</span>
                <span class="stat-val" id="summaryTenure">-</span>
            </div>
            <div class="stat-card highlight">
                <span class="stat-label">Monthly EMI</span>
                <span class="stat-val" id="summaryEmi">₹0</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Total Interest</span>
                <span class="stat-val" id="summaryInterest">₹0</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Total Payment</span>
                <span class="stat-val" id="summaryPayment">₹0</span>
            </div>
        </div>

        <!-- Filters & Exports row -->
        <div class="schedule-toolbar">
            <div class="toolbar-left">
                <div class="filter-group">
                    <label for="pageSizeSelect">View</label>
                    <select id="pageSizeSelect" class="toolbar-select">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="all">All</option>
                    </select>
                </div>
                <div class="search-group">
                    <input type="text" id="searchEmiInput" placeholder="Search EMI Number..." class="toolbar-input">
                </div>
            </div>
            <div class="toolbar-right">
                <button onclick="downloadPDF()" class="btn-toolbar"><i class="fa fa-file-pdf-o"></i> PDF</button>
                <button onclick="downloadExcel()" class="btn-toolbar"><i class="fa fa-file-excel-o"></i> Excel</button>
                <button onclick="window.print()" class="btn-toolbar"><i class="fa fa-print"></i> Print</button>
                <button onclick="shareSchedule()" class="btn-toolbar"><i class="fa fa-share-alt"></i> Share</button>
            </div>
        </div>

        <!-- Empty State -->
        <div id="scheduleEmptyState" class="schedule-empty-state">
            <div class="empty-illustration">
                <i class="fa fa-calculator"></i>
            </div>
            <p class="empty-text">Calculate your EMI to view repayment schedule.</p>
        </div>

        <!-- Table Wrapper -->
        <div id="scheduleTableWrapper" class="emi-table-wrapper" style="display:none;">
            <!-- Desktop Table view -->
            <table class="emi-table desktop-only">
                <thead>
                    <tr>
                        <th id="schedulePeriodHead">EMI No.</th>
                        <th id="scheduleDateHead">Payment Date</th>
                        <th>Opening Balance</th>
                        <th id="scheduleEmiHead">EMI</th>
                        <th>Principal Paid</th>
                        <th>Interest Paid</th>
                        <th>Closing Balance</th>
                    </tr>
                </thead>
                <tbody id="scheduleBody">
                    <!-- Dynamic monthly rows -->
                </tbody>
            </table>

            <!-- Mobile Accordion Cards view -->
            <div id="scheduleMobileCards" class="mobile-only mobile-cards-wrapper">
                <!-- Dynamic month cards -->
            </div>

            <!-- Pagination Controls -->
            <div class="schedule-pagination" id="schedulePagination">
                <button id="btnPrevPage" class="btn-page"><i class="fa fa-chevron-left"></i> Prev</button>
                <span id="pageInfo" class="page-info">Page 1 of 1</span>
                <button id="btnNextPage" class="btn-page">Next <i class="fa fa-chevron-right"></i></button>
            </div>
        </div>

        <!-- Bottom Summary Row -->
        <div id="scheduleBottomSummary" class="schedule-bottom-summary" style="display:none;">
            <div class="summary-item">Total Principal: <span id="sumPrincipal">₹0</span></div>
            <div class="summary-item">Total Interest: <span id="sumInterest">₹0</span></div>
            <div class="summary-item">Total Amount Paid: <span id="sumPaid">₹0</span></div>
            <div class="summary-item status"><span class="badge badge-success"><i class="fa fa-check-circle"></i> Loan Closed</span></div>
        </div>

        <!-- Graph Section -->
        <div id="scheduleChartsSection" class="schedule-charts-section" style="display:none;">
            <div class="chart-container-box">
                <h4 class="chart-box-title">Principal vs Interest Breakdown</h4>
                <div class="canvas-wrapper">
                    <canvas id="stackedBarChart"></canvas>
                </div>
            </div>
            <div class="chart-container-box">
                <h4 class="chart-box-title">Outstanding Balance Trend</h4>
                <div class="canvas-wrapper">
                    <canvas id="lineChartTrend"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Dynamic Educational Content & FAQs -->
    <div class="emi-content-section" id="dynamicContentArea">
        <!-- Loaded dynamically -->
    </div>

</div>

<script>
let amortizationData = [];
let currentPage = 1;
let pageSize = 10;
let searchQuery = "";
let scheduleView = "monthly";
let barChartInstance = null;
let lineChartInstance = null;

const config={
    personal:{loan:[50000,2500000],tenure:[1,5],rate:[10,24],defaultLoan:300000,defaultTenure:3,defaultRate:14.99,tenureType:"Years"},
    business:{loan:[100000,2500000],tenure:[1,5],rate:[12,26],defaultLoan:500000,defaultTenure:3,defaultRate:16.99,tenureType:"Years"},
    home:{loan:[500000,50000000],tenure:[5,30],rate:[8,18],defaultLoan:2500000,defaultTenure:15,defaultRate:10.5,tenureType:"Years"},
    gold:{loan:[50000,10000000],tenure:[3,36],rate:[9,18],defaultLoan:200000,defaultTenure:12,defaultRate:12,tenureType:"Months"},
    vehicle:{loan:[100000,5000000],tenure:[1,7],rate:[9,18],defaultLoan:500000,defaultTenure:5,defaultRate:11.5,tenureType:"Years"}
};

const applyLinks={
    personal: { text: "Apply for Personal Loan", url: "personal-loan" },
    business: { text: "Apply for Business Loan", url: "business-loan" },
    home: { text: "Apply for Home Loan", url: "loan-against-property" },
    gold: { text: "Apply for Gold Loan", url: "apply" },
    vehicle: { text: "Apply for Vehicle Loan", url: "vehicle-loan" }
};

const contentData = {
    personal: {
        title: "Personal Loan EMI Calculator",
        description: "A Personal Loan EMI Calculator is a handy tool that helps you calculate the monthly installment (EMI) you need to pay for a personal loan. Personal loans are generally unsecured and have shorter repayment tenures. Knowing your EMI in advance helps you plan your monthly budget and ensures you borrow responsibly without overextending your financial limits.",
        faqTitle: "Personal Loan FAQs",
        subsections: [
            {
                heading: "What is a Personal Loan EMI?",
                text: "Equated Monthly Installment (EMI) for a personal loan comprises both the principal repayment amount and the interest rate component on the borrowed amount. The EMI remains fixed throughout the loan tenure, making it easier for you to plan your monthly expenses."
            },
            {
                heading: "Key Benefits of Using a Personal Loan EMI Calculator",
                text: "Using Waqt Finance's Personal Loan EMI Calculator gives you instant calculations with complete transparency:",
                list: [
                    "No manual calculations: Avoid complex mathematical formulas and reduce human errors.",
                    "Instant analysis: Swap tenures and interest rates to find the optimal combination that fits your wallet.",
                    "Clear cost breakdown: View how much interest you will pay versus the principal borrowed."
                ]
            }
        ],
        faqs: [
            {
                q: "What is the formula used for calculating Personal Loan EMI?",
                a: "The calculator uses the standard EMI formula: <code>[P x R x (1+R)^N] / [(1+R)^N - 1]</code>, where P is the Loan Amount, R is the Monthly Interest Rate, and N is the tenure in months."
            },
            {
                q: "Can I repay my personal loan early?",
                a: "Yes, most personal loans offer prepayment or foreclosure options after a minimum period (such as 12 EMIs). Some charges may apply depending on the loan terms."
            },
            {
                q: "What factors affect personal loan interest rates?",
                a: "Personal loan rates are influenced by your credit score (CIBIL), monthly income, employment stability, employer profile, and any existing debt obligations."
            }
        ]
    },
    business: {
        title: "Business Loan EMI Calculator",
        description: "Waqt Finance's Business Loan EMI Calculator is designed to assist entrepreneurs and business owners in estimating their monthly installment obligations. Borrowing capital for expansion, purchasing inventory, or securing machinery is a big step. Using our tool ensures you know the exact outflow required to service your commercial debt.",
        faqTitle: "Business Loan FAQs",
        subsections: [
            {
                heading: "How does a Business Loan EMI Calculator help?",
                text: "Knowing your exact monthly repayment enables better cash flow forecasting. It helps you decide if your projected business revenues can comfortably accommodate the new loan repayments without hampering day-to-day operations."
            }
        ],
        faqs: [
            {
                q: "What security is required for a business loan?",
                a: "Waqt Finance offers both unsecured business loans (based on your business vintage, turnover, and IT return history) and secured loans where commercial or residential property is held as collateral."
            },
            {
                q: "What is the maximum tenure for business loans?",
                a: "Typically, business loan tenures range from 1 to 5 years (12 to 60 months) depending on the purpose and profile of the business."
            }
        ]
    },
    home: {
        title: "Home Loan EMI Calculator",
        description: "Waqt Finance's Home Loan EMI Calculator is a user-friendly financial tool designed to help you calculate the monthly installment (EMI) required to purchase your dream home. Our calculator helps you make an informed decision by assessing loan affordability, interest costs, and ownership contributions.",
        faqTitle: "Home Loan FAQs",
        subsections: [
            {
                heading: "What is a Home Loan EMI?",
                text: "EMI refers to the 'Equated Monthly Installment' which is the amount you pay to us on a specific date each month till the loan is repaid in full. The EMI comprises of both the principal and interest components. In the initial years, the interest component is much larger, while towards the latter half, the principal repayment dominates."
            },
            {
                heading: "Illustration: How is EMI on Loan Calculated?",
                text: "The mathematical formula for calculating EMI is: <code>P x R x (1+R)^N / [(1+R)^N - 1]</code> where:<br>P = Principal loan amount<br>N = Loan tenure in months<br>R = Monthly interest rate (Annual rate / 12 / 100).",
                list: [
                    "For example, if you avail a loan of ₹10,00,000 at an annual interest rate of 7.2% for a tenure of 10 years (120 months):",
                    "R = 7.2 / 12 / 100 = 0.006",
                    "EMI = ₹10,00,000 * 0.006 * (1 + 0.006)^120 / ((1 + 0.006)^120 - 1) = ₹11,714",
                    "Total Payable = ₹11,714 * 120 = ₹14,05,703 (Principal: ₹10,00,000, Interest: ₹4,05,703)"
                ]
            },
            {
                heading: "Flexible Repayment Options Offered by Waqt Finance",
                text: "To make home buying stress-free, we offer various customized repayment facilities:",
                list: [
                    "<strong>Step Up Repayment Facility (SURF):</strong> Lower EMIs in the initial years that accelerate proportionately as your income grows.",
                    "<strong>Flexible Loan Installments Plan (FLIP):</strong> Custom payment ratios designed to alter with life-stage capacity changes.",
                    "<strong>Telescopic Repayment Option:</strong> Longer tenures of up to 30 years to minimize EMIs and boost your eligibility."
                ]
            }
        ],
        faqs: [
            {
                q: "What does an EMI mean?",
                a: "EMI stands for Equated Monthly Installment. It is the fixed amount you pay to the lender each month to service your loan, comprising both principal and interest components."
            },
            {
                q: "When does my home loan EMI start?",
                a: "Your EMIs typically start from the month following the month in which the loan is fully disbursed. In under-construction properties, you pay interest only (Pre-EMI) on partial disbursements till project completion."
            },
            {
                q: "What is a Pre-Approved Home Loan?",
                a: "A pre-approved home loan is an in-principle sanction based on your income and financial profile. It allows you to search for properties with a clear budget in mind."
            },
            {
                q: "Can I change my home loan EMI date?",
                a: "Yes, you can request an EMI date shift by submitting a request along with revised bank mandate documents at our nearest branch."
            }
        ]
    },
    gold: {
        title: "Gold Loan EMI Calculator",
        description: "Get instant cash against your gold ornaments with Waqt Finance. Our Gold Loan EMI Calculator helps you estimate monthly interest outflows. Gold loans feature quick processing, minimal documentation, and highly flexible repayment options compared to standard personal loans.",
        faqTitle: "Gold Loan FAQs",
        subsections: [
            {
                heading: "Why choose a Gold Loan?",
                text: "Since gold loans are fully secured by your gold assets, they enjoy lower interest rates and do not require stringent credit score verifications."
            }
        ],
        faqs: [
            {
                q: "How is the gold valued for the loan?",
                a: "We evaluate the purity (karat) and weight of the gold ornaments. The loan amount is determined as a percentage of the prevailing market value of the gold (LTV ratio)."
            },
            {
                q: "What happens to my gold ornaments during the loan?",
                a: "Your gold assets are stored in highly secure, fireproof vaults with 24/7 surveillance and insurance coverage, ensuring complete safety until you close the loan."
            }
        ]
    },
    vehicle: {
        title: "Vehicle Loan EMI Calculator",
        description: "Waqt Finance's Vehicle Loan EMI Calculator helps you plan the purchase of your new or pre-owned car or two-wheeler. Check your monthly installments, calculate the down payment split, and drive away in your dream vehicle with absolute budget peace of mind.",
        faqTitle: "Vehicle Loan FAQs",
        subsections: [
            {
                heading: "Key features of Waqt Finance Vehicle Loans",
                text: "We offer attractive financing options of up to 90% of the on-road price of the vehicle with quick online approvals.",
                list: [
                    "Attractive interest rates on new and pre-owned cars.",
                    "Flexible repayment tenures ranging from 1 to 7 years.",
                    "Quick processing and minimal documentation."
                ]
            }
        ],
        faqs: [
            {
                q: "What documents are required for a car loan?",
                a: "You need identity proof, address proof, income documents (salary slips/form 16 or IT returns), and the vehicle quotation from the authorized dealer."
            },
            {
                q: "Can I get a loan for a second-hand car?",
                a: "Yes, Waqt Finance offers pre-owned car loans with customized interest rates and tenure adjustments based on the age of the vehicle."
            }
        ]
    }
};

let chart;
let currentTab = "personal";

const loanInput = document.getElementById("loan");
const loanRange = document.getElementById("loanRange");
const loanMin = document.getElementById("loanMin");
const loanMax = document.getElementById("loanMax");

const tenureInput = document.getElementById("tenure");
const tenureRange = document.getElementById("tenureRange");
const tenureMin = document.getElementById("tenureMin");
const tenureMax = document.getElementById("tenureMax");
const tenureLabel = document.getElementById("tenureLabel");
const tenureSuffix = document.getElementById("tenureSuffix");

const rateInput = document.getElementById("rate");
const rateRange = document.getElementById("rateRange");
const rateMin = document.getElementById("rateMin");
const rateMax = document.getElementById("rateMax");

const emiTxt = document.getElementById("emi");
const principalAmtTxt = document.getElementById("principalAmt");
const interestAmtTxt = document.getElementById("interestAmt");
const totalTxt = document.getElementById("total");
const chartCanvas = document.getElementById("chart");

function formatIndian(num) {
    let x = Math.round(num).toString();
    let lastThree = x.substring(x.length - 3);
    let otherNumbers = x.substring(0, x.length - 3);
    if (otherNumbers !== '') {
        lastThree = ',' + lastThree;
    }
    return otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree;
}

function parseNumber(val) {
    return parseFloat(val.toString().replace(/,/g, '')) || 0;
}

function money(value) {
    return "\u20B9" + formatIndian(Math.round(value));
}

function calculateLoanTotals(principal, months, annualRate) {
    const safePrincipal = parseFloat(principal) || 0;
    const safeMonths = Math.max(0, Math.round(parseFloat(months) || 0));
    const monthlyRate = (parseFloat(annualRate) || 0) / 12 / 100;
    let emi = 0;

    if (safePrincipal > 0 && safeMonths > 0 && monthlyRate > 0) {
        emi = (safePrincipal * monthlyRate * Math.pow(1 + monthlyRate, safeMonths)) / (Math.pow(1 + monthlyRate, safeMonths) - 1) || 0;
    } else if (safePrincipal > 0 && safeMonths > 0) {
        emi = safePrincipal / safeMonths;
    }

    const total = emi * safeMonths;
    const interest = Math.max(total - safePrincipal, 0);

    return {
        emi,
        total,
        interest,
        principal: safePrincipal,
        months: safeMonths,
        annualRate: parseFloat(annualRate) || 0
    };
}

function formatRateLabel(rate) {
    const parsedRate = parseFloat(rate) || 0;
    return parsedRate.toFixed(2).replace(/\.?0+$/, "") + "%";
}

function formatTenureFromMonths(months) {
    const safeMonths = Math.max(0, Math.round(months || 0));
    if (config[currentTab].tenureType === "Years") {
        const years = safeMonths / 12;
        const displayYears = Number.isInteger(years) ? years : years.toFixed(1).replace(/\.0$/, "");
        return displayYears + " Yr";
    }
    return safeMonths + " Mo";
}

function formatTenureDelta(months) {
    const safeMonths = Math.max(0, Math.round(months || 0));
    if (config[currentTab].tenureType === "Years") {
        const years = safeMonths / 12;
        const displayYears = Number.isInteger(years) ? years : years.toFixed(1).replace(/\.0$/, "");
        return displayYears + " Yr";
    }
    return safeMonths + " Mo";
}

function getTenureBoundsInMonths() {
    const activeConfig = config[currentTab];
    const isYears = activeConfig.tenureType === "Years";

    return {
        min: isYears ? activeConfig.tenure[0] * 12 : activeConfig.tenure[0],
        max: isYears ? activeConfig.tenure[1] * 12 : activeConfig.tenure[1],
        step: isYears ? 12 : 3
    };
}

function getComparisonImpact(variant, totals, baseline) {
    if (variant === "current") {
        return {
            text: "Selected plan at " + formatRateLabel(totals.annualRate) + " p.a.",
            className: "neutral"
        };
    }

    if (variant === "lower") {
        if (totals.months === baseline.months) {
            return {
                text: "Already at the longest tenure",
                className: "neutral"
            };
        }

        return {
            text: "EMI lower by " + money(Math.max(baseline.emi - totals.emi, 0)) + "/mo",
            className: "good"
        };
    }

    if (totals.months === baseline.months) {
        return {
            text: "Already at the shortest tenure",
            className: "neutral"
        };
    }

    return {
        text: "Interest saved " + money(Math.max(baseline.interest - totals.interest, 0)),
        className: "good"
    };
}

function renderComparisonCard(scenario, baseline, principal, annualRate) {
    const totals = calculateLoanTotals(principal, scenario.months, annualRate);
    const impact = getComparisonImpact(scenario.variant, totals, baseline);
    const mutedClass = scenario.variant !== "current" && totals.months === baseline.months ? " muted" : "";

    return `
        <article class="emi-compare-card ${scenario.variant}${mutedClass}">
            <div class="emi-compare-top">
                <span class="emi-compare-label">${scenario.title}</span>
                <span class="emi-compare-badge">${scenario.badge}</span>
            </div>
            <strong class="emi-compare-emi">${money(totals.emi)}</strong>
            <span class="emi-compare-sub">Monthly EMI</span>
            <div class="emi-compare-metrics">
                <span><small>Tenure</small><b>${formatTenureFromMonths(totals.months)}</b></span>
                <span><small>Interest</small><b>${money(totals.interest)}</b></span>
                <span><small>Total</small><b>${money(totals.total)}</b></span>
            </div>
            <p class="emi-compare-impact ${impact.className}">${impact.text}</p>
        </article>
    `;
}

function updateComparisonOptions(principal, months, annualRate) {
    const grid = document.getElementById("emiComparisonGrid");
    if (!grid) return;

    const baseline = calculateLoanTotals(principal, months, annualRate);
    const bounds = getTenureBoundsInMonths();
    const longerMonths = Math.min(bounds.max, baseline.months + bounds.step);
    const shorterMonths = Math.max(bounds.min, baseline.months - bounds.step);

    const scenarios = [
        {
            title: "Current Selection",
            badge: "Selected",
            months: baseline.months,
            variant: "current"
        },
        {
            title: "Lower EMI",
            badge: longerMonths > baseline.months ? "+" + formatTenureDelta(longerMonths - baseline.months) : "Max Tenure",
            months: longerMonths,
            variant: "lower"
        },
        {
            title: "Save Interest",
            badge: shorterMonths < baseline.months ? "-" + formatTenureDelta(baseline.months - shorterMonths) : "Min Tenure",
            months: shorterMonths,
            variant: "save"
        }
    ];

    grid.innerHTML = scenarios.map(scenario => renderComparisonCard(scenario, baseline, principal, annualRate)).join("");
}

function formatLabel(n) {
    if (n >= 10000000) return "₹" + (n / 10000000) + " Cr";
    if (n >= 100000) return "₹" + (n / 100000) + " Lac";
    if (n >= 1000) return "₹" + (n / 1000) + "k";
    return "₹" + n;
}

function fillSlider(s) {
    let p = (s.value - s.min) / (s.max - s.min) * 100;
    if (isNaN(p) || p < 0) p = 0;
    if (p > 100) p = 100;
    const sliderBg = `linear-gradient(90deg, #0056D6 0%, #0056D6 ${p}%, #E5ECF6 ${p}%, #E5ECF6 100%)`;
    s.style.setProperty("--slider-percent", p + "%");
    s.style.setProperty("background", sliderBg, "important");
    s.setAttribute("aria-valuemin", s.min);
    s.setAttribute("aria-valuemax", s.max);
    s.setAttribute("aria-valuenow", s.value);
}

let currentCounterValues = {};
function animateCounter(elementId, targetValue, prefix = "") {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    if (currentCounterValues[elementId] === undefined) {
        let cleanVal = element.innerText.replace(/[^0-9.]/g, "");
        currentCounterValues[elementId] = parseFloat(cleanVal) || 0;
    }
    
    const start = currentCounterValues[elementId];
    const end = targetValue;
    const duration = 250; // ms
    const startTime = performance.now();
    
    function step(now) {
        const progress = Math.min((now - startTime) / duration, 1);
        const current = start + (end - start) * progress;
        
        element.innerText = prefix + formatIndian(Math.round(current));
        currentCounterValues[elementId] = current;
        
        if (progress < 1) {
            requestAnimationFrame(step);
        } else {
            element.innerText = prefix + formatIndian(Math.round(end));
            currentCounterValues[elementId] = end;
        }
    }
    requestAnimationFrame(step);
}

function calc() {
    let P = parseNumber(loanInput.value);
    let rawTenure = parseFloat(tenureInput.value) || 0;
    let isYears = config[currentTab].tenureType === "Years";
    let N = isYears ? rawTenure * 12 : rawTenure; // total months
    let rate = parseFloat(rateInput.value) || 0;
    let totals = calculateLoanTotals(P, N, rate);
    let emi = totals.emi;
    let total = totals.total;
    let interest = totals.interest;

    animateCounter("emi", Math.round(emi), "₹");
    animateCounter("principalAmt", Math.round(P), "₹");
    animateCounter("interestAmt", Math.round(interest), "₹");
    animateCounter("total", Math.round(total), "₹");
    animateCounter("mobileStickyEmi", Math.round(emi), "₹");

    // Update labels dynamically based on category
    const emiLabel = document.getElementById("emiLabel");
    if (emiLabel) {
        const capitalize = currentTab.charAt(0).toUpperCase() + currentTab.slice(1);
        emiLabel.innerText = `${capitalize} Loan EMI`;
    }

    updateChart(P, interest);
    updateAmortizationTable(P, N, rate, isYears);
    updateComparisonOptions(P, N, rate);
}

function updateChart(p, i) {
    if (p === 0 && i === 0) p = 1; // Fallback
    const total = p + i;
    const pPct = (p / total) * 100;
    const iPct = (i / total) * 100;
    
    const donutPrincipal = document.getElementById("donutPrincipal");
    const donutInterest = document.getElementById("donutInterest");
    const interestPercentVal = document.getElementById("interestPercentVal");
    
    if (donutPrincipal) {
        donutPrincipal.setAttribute("stroke-dasharray", `${pPct} ${100 - pPct}`);
        donutPrincipal.setAttribute("stroke-dashoffset", "0");
    }
    if (donutInterest) {
        donutInterest.setAttribute("stroke-dasharray", `${iPct} ${100 - iPct}`);
        donutInterest.setAttribute("stroke-dashoffset", -pPct);
    }
    if (interestPercentVal) {
        interestPercentVal.innerText = Math.round(iPct) + "%";
    }
}

function updateAmortizationTable(P, N_months, annualRate, isYears) {
    // 1. Clear array
    amortizationData = [];
    
    const emptyState = document.getElementById("scheduleEmptyState");
    const tableWrapper = document.getElementById("scheduleTableWrapper");
    const bottomSummary = document.getElementById("scheduleBottomSummary");
    const chartsSection = document.getElementById("scheduleChartsSection");
    
    if (P <= 0 || N_months <= 0) {
        if(emptyState) emptyState.style.display = "block";
        if(tableWrapper) tableWrapper.style.display = "none";
        if(bottomSummary) bottomSummary.style.display = "none";
        if(chartsSection) chartsSection.style.display = "none";
        return;
    }
    
    if(emptyState) emptyState.style.display = "none";
    if(tableWrapper) tableWrapper.style.display = "block";
    if(bottomSummary) bottomSummary.style.display = "flex";
    if(chartsSection) chartsSection.style.display = "grid";
    
    let balance = P;
    let r = annualRate / 12 / 100;
    const standardTotals = calculateLoanTotals(P, N_months, annualRate);
    const standardEmi = standardTotals.emi;
    
    let totalInterest = 0;
    let totalPayment = 0;
    let startDate = new Date();
    startDate.setMonth(startDate.getMonth() + 1); // Starting next month
    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    
    for (let month = 1; month <= N_months; month++) {
        let interestPaid = r > 0 ? balance * r : 0;
        let principalPaid = standardEmi - interestPaid;
        let currentEmi = standardEmi;
        
        if (principalPaid > balance || month === N_months) {
            principalPaid = balance;
            currentEmi = principalPaid + interestPaid;
        }
        
        let closingBalance = balance - principalPaid;
        if (closingBalance < 0) closingBalance = 0;
        
        let dateStr = monthNames[startDate.getMonth()] + " " + startDate.getFullYear();
        startDate.setMonth(startDate.getMonth() + 1);
        
        amortizationData.push({
            emiNo: month,
            date: dateStr,
            opening: balance,
            emi: currentEmi,
            principal: principalPaid,
            interest: interestPaid,
            closing: closingBalance
        });
        
        totalInterest += interestPaid;
        totalPayment += currentEmi;
        balance = closingBalance;
    }
    
    // Update summary stat cards
    document.getElementById("summaryLoan").innerText = "₹" + formatIndian(Math.round(P));
    document.getElementById("summaryRate").innerText = annualRate + "%";
    document.getElementById("summaryTenure").innerText = isYears ? (N_months / 12) + " Yr" : N_months + " Mo";
    document.getElementById("summaryEmi").innerText = "₹" + formatIndian(Math.round(standardEmi));
    document.getElementById("summaryInterest").innerText = "₹" + formatIndian(Math.round(totalInterest));
    document.getElementById("summaryPayment").innerText = "₹" + formatIndian(Math.round(totalPayment));
    
    // Bottom Summary
    document.getElementById("sumPrincipal").innerText = "₹" + formatIndian(Math.round(P));
    document.getElementById("sumInterest").innerText = "₹" + formatIndian(Math.round(totalInterest));
    document.getElementById("sumPaid").innerText = "₹" + formatIndian(Math.round(totalPayment));
    
    // Initial pagination render
    currentPage = 1;
    initToolbarEvents();
    filterAndRender();
    renderCharts();
}

let toolbarEventsInitialized = false;
function initToolbarEvents() {
    if (toolbarEventsInitialized) return;
    
    const sizeSelect = document.getElementById("pageSizeSelect");
    if(sizeSelect) {
        sizeSelect.addEventListener("change", (e) => {
            pageSize = e.target.value;
            currentPage = 1;
            filterAndRender();
        });
    }
    
    const searchInput = document.getElementById("searchEmiInput");
    if(searchInput) {
        searchInput.addEventListener("input", (e) => {
            searchQuery = e.target.value.trim();
            currentPage = 1;
            filterAndRender();
        });
    }
    
    const btnPrev = document.getElementById("btnPrevPage");
    if(btnPrev) {
        btnPrev.addEventListener("click", () => {
            if (currentPage > 1) {
                currentPage--;
                filterAndRender();
            }
        });
    }
    
    const btnNext = document.getElementById("btnNextPage");
    if(btnNext) {
        btnNext.addEventListener("click", () => {
            const limit = pageSize === "all" ? Math.max(filteredData.length, 1) : parseInt(pageSize);
            const totalPages = Math.ceil(filteredData.length / limit) || 1;
            if (currentPage < totalPages) {
                currentPage++;
                filterAndRender();
            }
        });
    }

    document.querySelectorAll("[data-schedule-view]").forEach(button => {
        button.addEventListener("click", () => {
            const nextView = button.dataset.scheduleView || "monthly";
            if (scheduleView === nextView) return;

            scheduleView = nextView;
            searchQuery = "";
            const searchInput = document.getElementById("searchEmiInput");
            if (searchInput) searchInput.value = "";
            document.querySelectorAll("[data-schedule-view]").forEach(viewButton => {
                viewButton.classList.toggle("active", viewButton.dataset.scheduleView === scheduleView);
            });
            currentPage = 1;
            filterAndRender();
        });
    });
    
    toolbarEventsInitialized = true;
}

function getScheduleRowsForView() {
    if (scheduleView !== "yearly") {
        return amortizationData.map(row => ({
            ...row,
            rowKey: "emi-" + row.emiNo
        }));
    }

    const yearlyRows = new Map();
    amortizationData.forEach(row => {
        const year = row.date.split(" ")[1] || row.date;
        if (!yearlyRows.has(year)) {
            yearlyRows.set(year, {
                emiNo: year,
                date: year,
                opening: row.opening,
                emi: 0,
                principal: 0,
                interest: 0,
                closing: row.closing,
                rowKey: "year-" + year
            });
        }

        const yearRow = yearlyRows.get(year);
        yearRow.emi += row.emi;
        yearRow.principal += row.principal;
        yearRow.interest += row.interest;
        yearRow.closing = row.closing;
    });

    return Array.from(yearlyRows.values());
}

function updateScheduleViewLabels() {
    const periodHead = document.getElementById("schedulePeriodHead");
    const dateHead = document.getElementById("scheduleDateHead");
    const emiHead = document.getElementById("scheduleEmiHead");
    const searchInput = document.getElementById("searchEmiInput");

    if (periodHead) periodHead.innerText = scheduleView === "yearly" ? "Year" : "EMI No.";
    if (dateHead) dateHead.innerText = scheduleView === "yearly" ? "Period" : "Payment Date";
    if (emiHead) emiHead.innerText = scheduleView === "yearly" ? "Total EMI Paid" : "EMI";
    if (searchInput) searchInput.placeholder = scheduleView === "yearly" ? "Search Year..." : "Search EMI Number...";
}

function filterAndRender() {
    updateScheduleViewLabels();

    const sourceData = getScheduleRowsForView();
    const normalizedQuery = searchQuery.toLowerCase();

    if (normalizedQuery !== "") {
        filteredData = sourceData.filter(row => {
            return row.emiNo.toString().toLowerCase().includes(normalizedQuery) ||
                row.date.toString().toLowerCase().includes(normalizedQuery);
        });
    } else {
        filteredData = [...sourceData];
    }
    
    // Paginate
    const limit = pageSize === "all" ? Math.max(filteredData.length, 1) : parseInt(pageSize);
    const totalPages = Math.ceil(filteredData.length / limit) || 1;
    
    if (currentPage > totalPages) currentPage = totalPages;
    
    const startIndex = (currentPage - 1) * limit;
    const paginatedData = filteredData.slice(startIndex, startIndex + limit);
    
    // Render Desktop Table
    const scheduleBody = document.getElementById("scheduleBody");
    if (scheduleBody) {
        scheduleBody.innerHTML = "";
        paginatedData.forEach(row => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${row.emiNo}</td>
                <td>${row.date}</td>
                <td>₹${formatIndian(Math.round(row.opening))}</td>
                <td>₹${formatIndian(Math.round(row.emi))}</td>
                <td>₹${formatIndian(Math.round(row.principal))}</td>
                <td>₹${formatIndian(Math.round(row.interest))}</td>
                <td>₹${formatIndian(Math.round(row.closing))}</td>
            `;
            scheduleBody.appendChild(tr);
        });
    }
    
    // Render Mobile Cards
    const mobileCards = document.getElementById("scheduleMobileCards");
    if (mobileCards) {
        mobileCards.innerHTML = "";
        paginatedData.forEach(row => {
            const rowKey = row.rowKey || "emi-" + row.emiNo;
            const rowTitle = scheduleView === "yearly" ? "Year " + row.date : row.date + " (EMI #" + row.emiNo + ")";
            const card = document.createElement("div");
            card.className = "mobile-accordion-card";
            card.innerHTML = `
                <div class="card-header-row" onclick="toggleMobileCard('${rowKey}')">
                    <span class="card-title">${rowTitle}</span>
                    <span class="card-emi">₹${formatIndian(Math.round(row.emi))}</span>
                    <span class="accordion-icon" id="cardIcon-${rowKey}">+</span>
                </div>
                <div class="card-body-content" id="cardContent-${rowKey}" style="max-height:0px; overflow:hidden; transition: max-height 0.25s ease-out;">
                    <div class="card-body-inner">
                        <div class="card-info-row"><span>Opening Balance:</span> <span>₹${formatIndian(Math.round(row.opening))}</span></div>
                        <div class="card-info-row"><span>Principal Paid:</span> <span>₹${formatIndian(Math.round(row.principal))}</span></div>
                        <div class="card-info-row"><span>Interest Paid:</span> <span>₹${formatIndian(Math.round(row.interest))}</span></div>
                        <div class="card-info-row"><span>Closing Balance:</span> <span>₹${formatIndian(Math.round(row.closing))}</span></div>
                    </div>
                </div>
            `;
            mobileCards.appendChild(card);
        });
    }
    
    // Update pagination controls
    const pageInfo = document.getElementById("pageInfo");
    if (pageInfo) pageInfo.innerText = `Page ${currentPage} of ${totalPages}`;
    
    const btnPrev = document.getElementById("btnPrevPage");
    if (btnPrev) btnPrev.disabled = (currentPage === 1);
    
    const btnNext = document.getElementById("btnNextPage");
    if (btnNext) btnNext.disabled = (currentPage === totalPages);
    
    const paginator = document.getElementById("schedulePagination");
    if (paginator) {
        paginator.style.display = (pageSize === "all" || totalPages <= 1) ? "none" : "flex";
    }
}

window.toggleMobileCard = function(emiNo) {
    const content = document.getElementById(`cardContent-${emiNo}`);
    const icon = document.getElementById(`cardIcon-${emiNo}`);
    if (content.style.maxHeight && content.style.maxHeight !== "0px") {
        content.style.maxHeight = "0px";
        icon.innerText = "+";
    } else {
        content.style.maxHeight = content.scrollHeight + "px";
        icon.innerText = "−";
    }
};

function renderCharts() {
    const ctxBar = document.getElementById("stackedBarChart");
    const ctxLine = document.getElementById("lineChartTrend");
    if (!ctxBar || !ctxLine) return;
    
    if (barChartInstance) barChartInstance.destroy();
    if (lineChartInstance) lineChartInstance.destroy();
    
    // Sample datasets to prevent charting overload for long tenures (Lazy chart sampling)
    let sampleData = [...amortizationData];
    if (amortizationData.length > 60) {
        // Sample to keep chart responsive and legible
        const step = Math.ceil(amortizationData.length / 24);
        sampleData = amortizationData.filter((_, idx) => idx % step === 0);
        // Ensure last month is always included
        if (!sampleData.includes(amortizationData[amortizationData.length - 1])) {
            sampleData.push(amortizationData[amortizationData.length - 1]);
        }
    }
    
    const labels = sampleData.map(row => row.date);
    const principals = sampleData.map(row => row.principal);
    const interests = sampleData.map(row => row.interest);
    const balances = sampleData.map(row => row.closing);
    
    // 1. Stacked Bar Chart
    barChartInstance = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Principal Paid',
                    data: principals,
                    backgroundColor: '#0056D6', // WAQT Blue
                    borderRadius: 4
                },
                {
                    label: 'Interest Paid',
                    data: interests,
                    backgroundColor: '#93C5FD', // Light Blue
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { stacked: true, grid: { display: false } },
                y: { stacked: true, grid: { color: '#F1F5F9' } }
            },
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
    
    // 2. Line Chart
    lineChartInstance = new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Outstanding Balance',
                data: balances,
                borderColor: '#0056D6',
                backgroundColor: 'rgba(0, 86, 214, 0.05)',
                fill: true,
                tension: 0.3,
                borderWidth: 3,
                pointRadius: 2,
                pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { grid: { display: false } },
                y: { grid: { color: '#F1F5F9' } }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
}

window.downloadExcel = function() {
    const exportRows = getScheduleRowsForView();
    if (exportRows.length === 0) return;
    
    const firstColumn = scheduleView === "yearly" ? "Year" : "EMI No.";
    const secondColumn = scheduleView === "yearly" ? "Period" : "Payment Date";
    let csv = `${firstColumn},${secondColumn},Opening Balance,EMI,Principal Paid,Interest Paid,Closing Balance\r\n`;
    exportRows.forEach(row => {
        csv += `${row.emiNo},${row.date},${Math.round(row.opening)},${Math.round(row.emi)},${Math.round(row.principal)},${Math.round(row.interest)},${Math.round(row.closing)}\r\n`;
    });
    
    const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", scheduleView === "yearly" ? "repayment_schedule_yearly.csv" : "repayment_schedule.csv");
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

window.downloadPDF = function() {
    window.print();
};

let prePrintPageSize = "10";
window.onbeforeprint = function() {
    const sizeSelect = document.getElementById("pageSizeSelect");
    if (sizeSelect) {
        prePrintPageSize = sizeSelect.value;
        pageSize = "all";
        filterAndRender();
    }
};

window.onafterprint = function() {
    const sizeSelect = document.getElementById("pageSizeSelect");
    if (sizeSelect) {
        pageSize = prePrintPageSize;
        filterAndRender();
    }
};

window.shareSchedule = function() {
    navigator.clipboard.writeText(window.location.href);
    alert('Repayment schedule sharing link copied to clipboard!');
};

function renderDynamicContent(type) {
    const container = document.getElementById("dynamicContentArea");
    if (!container) return;

    const data = contentData[type];
    let html = `
        <h2 class="emi-content-header">${data.title}</h2>
        <p class="emi-content-para">${data.description}</p>
    `;

    if (data.subsections && data.subsections.length > 0) {
        data.subsections.forEach(sub => {
            html += `
                <h3 class="emi-content-subheading">${sub.heading}</h3>
                <p class="emi-content-para">${sub.text}</p>
            `;
            if (sub.list && sub.list.length > 0) {
                html += `<ul class="emi-content-list">`;
                sub.list.forEach(item => {
                    html += `<li>${item}</li>`;
                });
                html += `</ul>`;
            }
        });
    }

    if (data.faqs && data.faqs.length > 0) {
        html += `
            <div class="emi-faq-wrap">
                <h3 class="emi-faq-title">${data.faqTitle}</h3>
        `;
        data.faqs.forEach((faq, index) => {
            html += `
                <div class="emi-faq-item">
                    <button class="emi-faq-trigger" onclick="toggleFaq(${index})">
                        <span>${faq.q}</span>
                        <span class="faq-icon" id="faqIcon-${index}">+</span>
                    </button>
                    <div class="emi-faq-content" id="faqContent-${index}">
                        <div class="emi-faq-content-inner">
                            ${faq.a}
                        </div>
                    </div>
                </div>
            `;
        });
        html += `</div>`;
    }

    container.innerHTML = html;
}

window.toggleFaq = function(index) {
    const content = document.getElementById(`faqContent-${index}`);
    const icon = document.getElementById(`faqIcon-${index}`);
    
    if (content.style.maxHeight && content.style.maxHeight !== "0px") {
        content.style.maxHeight = "0px";
        icon.innerText = "+";
    } else {
        document.querySelectorAll('.emi-faq-content').forEach(c => {
            c.style.maxHeight = "0px";
        });
        document.querySelectorAll('.emi-faq-trigger span.faq-icon').forEach(i => {
            i.innerText = "+";
        });
        
        content.style.maxHeight = content.scrollHeight + "px";
        icon.innerText = "−";
    }
}

function updateTooltipPos(slider) {
    const tooltipId = slider.id.replace("Range", "Tooltip");
    const tooltip = document.getElementById(tooltipId);
    if (tooltip) {
        let p = (slider.value - slider.min) / (slider.max - slider.min);
        if (isNaN(p) || p < 0) p = 0;
        if (p > 1) p = 1;
        
        // Calc offset for 28px thumb
        tooltip.style.left = `calc(${p * 100}% + ${(14 - (p * 28))}px)`;
        
        if (slider.id === "loanRange") {
            tooltip.innerText = "₹" + formatIndian(slider.value);
        } else if (slider.id === "rateRange") {
            tooltip.innerText = slider.value + "%";
        } else {
            tooltip.innerText = slider.value + (config[currentTab].tenureType === "Years" ? " Yr" : " Mo");
        }
    }
}

function syncInputs(input, slider, isCurrency) {
    function checkValidation() {
        let cleanVal = input.value.replace(/[^0-9.]/g, '');
        let num = parseFloat(cleanVal) || 0;
        let parent = input.closest(".num-inp-wrap");

        if (num < parseFloat(slider.min) || num > parseFloat(slider.max)) {
            if (parent) parent.classList.add("input-error");
        } else {
            if (parent) parent.classList.remove("input-error");
        }
    }

    function showTooltip() {
        const tooltipId = slider.id.replace("Range", "Tooltip");
        const tooltip = document.getElementById(tooltipId);
        if (tooltip) {
            tooltip.classList.add("active");
            updateTooltipPos(slider);
        }
    }

    function hideTooltip() {
        const tooltipId = slider.id.replace("Range", "Tooltip");
        const tooltip = document.getElementById(tooltipId);
        if (tooltip) {
            tooltip.classList.remove("active");
        }
    }

    input.addEventListener('input', () => {
        let cleanVal = input.value.replace(/[^0-9.]/g, '');
        let num = parseFloat(cleanVal) || 0;
        
        slider.value = num;
        fillSlider(slider);
        calc();
        checkValidation();
        updateTooltipPos(slider);
    });

    input.addEventListener('blur', () => {
        let cleanVal = input.value.replace(/[^0-9.]/g, '');
        let num = parseFloat(cleanVal) || 0;
        
        if (num < parseFloat(slider.min)) num = parseFloat(slider.min);
        if (num > parseFloat(slider.max)) num = parseFloat(slider.max);
        
        slider.value = num;
        input.value = isCurrency ? formatIndian(num) : num;
        fillSlider(slider);
        calc();

        let parent = input.closest(".num-inp-wrap");
        if (parent) parent.classList.remove("input-error");
        updateTooltipPos(slider);
    });

    slider.addEventListener('input', () => {
        input.value = isCurrency ? formatIndian(slider.value) : slider.value;
        fillSlider(slider);
        calc();

        let parent = input.closest(".num-inp-wrap");
        if (parent) parent.classList.remove("input-error");
        updateTooltipPos(slider);
    });

    // Tooltip trigger events
    slider.addEventListener('mousedown', showTooltip);
    slider.addEventListener('touchstart', showTooltip);
    slider.addEventListener('focus', showTooltip);
    slider.addEventListener('input', showTooltip);
    
    slider.addEventListener('mouseup', hideTooltip);
    slider.addEventListener('touchend', hideTooltip);
    slider.addEventListener('mouseleave', hideTooltip);
    slider.addEventListener('change', hideTooltip);
    slider.addEventListener('blur', hideTooltip);
}

function load(type) {
    currentTab = type;
    let c = config[type];
    const defaultLoan = typeof c.defaultLoan !== "undefined" ? c.defaultLoan : c.loan[0];
    const defaultTenure = typeof c.defaultTenure !== "undefined" ? c.defaultTenure : c.tenure[0];
    const defaultRate = typeof c.defaultRate !== "undefined" ? c.defaultRate : c.rate[0];

    loanRange.min = c.loan[0]; loanRange.max = c.loan[1]; loanRange.value = defaultLoan;
    tenureRange.min = c.tenure[0]; tenureRange.max = c.tenure[1]; tenureRange.value = defaultTenure;
    rateRange.min = c.rate[0]; rateRange.max = c.rate[1]; rateRange.value = defaultRate;

    loanMin.innerText = formatLabel(c.loan[0]);
    loanMax.innerText = formatLabel(c.loan[1]);

    tenureMin.innerText = c.tenure[0];
    tenureMax.innerText = c.tenure[1];

    rateMin.innerText = c.rate[0] + "%";
    rateMax.innerText = c.rate[1] + "%";

    loanInput.value = formatIndian(defaultLoan);
    tenureInput.value = defaultTenure;
    rateInput.value = defaultRate;

    if (c.tenureType === "Years") {
        tenureLabel.innerText = "Tenure (Years)";
        tenureSuffix.innerText = "Yr";
    } else {
        tenureLabel.innerText = "Tenure (Months)";
        tenureSuffix.innerText = "Mo";
    }

    fillSlider(loanRange);
    fillSlider(tenureRange);
    fillSlider(rateRange);

    calc();
    renderDynamicContent(type);

    updateTooltipPos(loanRange);
    updateTooltipPos(tenureRange);
    updateTooltipPos(rateRange);

    const btnData = applyLinks[type];
    const applyBtn = document.getElementById("applyBtn");
    if(applyBtn) {
        btnData ? applyBtn.innerText = btnData.text : null;
        btnData ? applyBtn.href = btnData.url : null;
    }
    const mobileStickyApply = document.getElementById("mobileStickyApply");
    if (mobileStickyApply && btnData) {
        mobileStickyApply.href = btnData.url;
    }
}

syncInputs(loanInput, loanRange, true);
syncInputs(tenureInput, tenureRange, false);
syncInputs(rateInput, rateRange, false);

document.querySelectorAll(".emi-tabs button").forEach(btn => {
    btn.onclick = () => {
        document.querySelectorAll(".emi-tabs button").forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
        load(btn.dataset.type);
    };
});

const resetCalculatorBtn = document.getElementById("resetCalculatorBtn");
if (resetCalculatorBtn) {
    resetCalculatorBtn.addEventListener("click", () => {
        load(currentTab);
    });
}

load("personal");
</script>

<?php include 'includes/footer.php' ?>
