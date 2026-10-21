<?php
$page_title = "Loan Eligibility Calculator: Check Amount Online | Waqt Finance";
$meta_description = "Check your personal and business loan eligibility online in seconds with Waqt Finance. 100% free tool, zero impact on CIBIL score. Calculate now!";
$meta_keywords = "loan eligibility, eligibility calculator India, check loan status, personal loan eligibility";
$canonical_url = "https://waqtfinance.com/eligibility-calculator";

include 'includes/header.php';
include 'includes/main-header.php';
?>
<link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet">
<link href="assets/css/eligibility-calculator.css?v=<?php echo filemtime(__DIR__ . '/assets/css/eligibility-calculator.css'); ?>" rel="stylesheet">

<style>


</style>


<main class="eligibility-page"><div class="red-bg eligibility-hero">
    <div class="text-center mt-5">
        <h1 class="fw-bold">Find the right loan for your profile</h1>
        <p class="text-muted mt-2">Share a few details to see indicative loan options. This check is soft, private and does not guarantee sanction.</p>
    </div>
</div>

<div class="container eligibility-shell mt-3 py-4">

    <!-- Heading -->


    <div class="row g-4">

        <!-- LEFT : FORM -->
        <div class="col-lg-5">
            <div class="card eligibility-card glass-card p-4">
                <h2 class="fw-bold mb-4" style="color: #0f172a;">
                    <i class="fa-solid fa-user-tie text-primary me-2"></i> Your Profile
                </h2>

                <div class="mb-3 wf-form-group">
                    <label class="form-label">Age</label>
                    <div class="wf-input-wrapper">
                        <i class="fa-solid fa-calendar-days wf-input-icon"></i>
                        <input type="number" id="age" class="form-control" placeholder="Enter your age (e.g. 28)">
                    </div>
                </div>

                <div class="mb-3 wf-form-group">
                    <label class="form-label">Monthly Income (₹)</label>
                    <div class="wf-input-wrapper">
                        <i class="fa-solid fa-indian-rupee-sign wf-input-icon"></i>
                        <input type="text" id="income" class="form-control" placeholder="e.g. 50,000">
                    </div>
                </div>

                <div class="mb-3 wf-form-group">
                    <label class="form-label">Employment Type</label>
                    <div class="wf-input-wrapper">
                        <i class="fa-solid fa-briefcase wf-input-icon"></i>
                        <select id="employment">
                            <option value="">Select employment type</option>
                            <option value="salaried">Salaried</option>
                            <option value="self-employed">Self Employed</option>
                            <option value="business-owner">Business Owner</option>
                            <option value="government">Government</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3 wf-form-group">
                    <label class="form-label">Work Experience (Years)</label>
                    <div class="wf-input-wrapper">
                        <i class="fa-solid fa-history wf-input-icon"></i>
                        <input type="number" id="experience" class="form-control" placeholder="Total work experience">
                    </div>
                </div>

                <div class="mb-3 wf-form-group">
                    <label class="form-label">Existing EMIs (₹)</label>
                    <div class="wf-input-wrapper">
                        <i class="fa-solid fa-wallet wf-input-icon"></i>
                        <input type="text" id="emi" class="form-control" placeholder="Total monthly EMIs (optional)">
                    </div>
                </div>

                <div class="mb-4 wf-form-group">
                    <label class="form-label d-flex justify-content-between">
                        Credit Score
                        <span id="scoreBadge" class="badge"></span>
                    </label>
                    <input type="range" class="form-range progress-range emi-slider" min="300" max="900" step="10" value="700" id="score">
                    <div class="d-flex justify-content-between small text-muted">
                        <span>300</span>
                        <strong id="scoreText">700</strong>
                        <span>900</span>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button id="checkBtn" class="btn fw-semibold cursor-pointer" onclick="checkEligibility()">Check Eligibility</button>
                    <button class="btn btn-outline-secondary cursor-pointer" onclick="resetForm()">Reset</button>
                </div>
            </div>
        </div>

        <!-- RIGHT : RESULTS -->
        <div class="col-lg-7">

            <div id="emptyState" class="card eligibility-card empty-state p-5 text-center">
                <i class="fa-solid fa-calculator fa-3x mb-3 text-muted"></i>
                <h5>Enter details to check eligibility</h5>
                <p class="text-muted mb-0">Eligible loan results will appear here</p>
            </div>

            <div id="resultArea" class="d-none">

                <!-- SUMMARY -->
                <div class="card eligibility-card summary-card p-4 mb-4">
                    <h5>Profile Summary</h5>
                    <div class="chart-wrapper">
                        <canvas id="eligibilityChart"></canvas>
                        <div class="chart-label-center">
                            <strong id="eligibilityScoreVal">0/6</strong>
                            <span>Loans</span>
                        </div>
                    </div>
                    <div id="chartFeedback" class="chart-feedback-text mt-2"></div>
                </div>

                <!-- ELIGIBLE -->
                <h5 class="fw-bold text-success mb-3">
                    <i class="fa-solid fa-check-circle me-2"></i>Eligible Loans
                </h5>
                <div id="eligibleLoans" class="row g-3 mb-4"></div>

                <!-- NOT ELIGIBLE -->
                <h5 class="fw-bold text-muted mb-3">
                    <i class="fa-solid fa-xmark-circle me-2"></i>Not Eligible
                </h5>
                <div id="ineligibleLoans" class="row g-3"></div>

            </div>
        </div>

    </div>

</div>
<!-- ============================================================== -->
<!-- KEYWORD-MAPPED SEO CONTENT FOR LOAN ELIGIBILITY CALCULATOR -->
<!-- ============================================================== -->
<section class="calculator-seo-content" style="padding: 60px 0; background: #f8fafc; font-family: 'Inter', sans-serif;">
    <div class="container" style="max-width: 1100px; margin: 0 auto; padding: 0 20px;">
        
        <!-- SECTION 1: PRIMARY KEYWORD TARGETING & WORKING METHODOLOGY -->
        <div style="background: #ffffff; border-radius: 12px; padding: 35px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.04);">
            <h2 style="font-size: 24px; color: #0c1a30; font-weight: 700; margin-bottom: 15px;">
                How to Use the Online Loan Eligibility Calculator
            </h2>
            <p style="font-size: 15px; color: #475569; line-height: 1.8;">
                The <strong>Waqt Finance loan eligibility calculator</strong> is an advanced financial assessment tool designed to help you <strong>check loan eligibility online</strong> before submitting an application. Whether you are seeking a fast <a href="/personal-loan" style="color: #0056b3; font-weight: 600; text-decoration: underline;">personal loan</a>, collateral-free <a href="/business-loan" style="color: #0056b3; font-weight: 600; text-decoration: underline;">business loan</a>, short-term <a href="/pay-day-loan" style="color: #0056b3; font-weight: 600; text-decoration: underline;">payday loan</a>, or <a href="/loan-against-property" style="color: #0056b3; font-weight: 600; text-decoration: underline;">loan against property</a>, this calculator accurately estimates your maximum borrowing limit in seconds without impacting your credit score.
            </p>
            <p style="font-size: 15px; color: #475569; line-height: 1.8;">
                Our <strong>instant loan eligibility check</strong> utilizes the standard <strong>FOIR (Fixed Obligation to Income Ratio)</strong> model mandated for RBI-registered NBFC lenders in India. It computes your net disposable income after deducting active EMIs and living costs, ensuring your repayments remain comfortable and sustainable.
            </p>
        </div>

        <!-- SECTION 2: FACTORS INFLUENCING PERSONAL & BUSINESS LOAN ELIGIBILITY -->
        <div style="background: #ffffff; border-radius: 12px; padding: 35px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.04);">
            <h2 style="font-size: 24px; color: #0c1a30; font-weight: 700; margin-bottom: 15px;">
                Key Factors Influencing Personal & Business Loan Eligibility Criteria
            </h2>
            <p style="font-size: 15px; color: #475569; line-height: 1.7; margin-bottom: 20px;">
                When evaluating your maximum loan sanction amount, NBFC underwriting algorithms evaluate several key eligibility parameters:
            </p>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
                <div style="padding: 18px; border-left: 4px solid #0056b3; background: #f1f5f9; border-radius: 0 8px 8px 0;">
                    <h3 style="font-size: 16px; color: #0c1a30; font-weight: 600; margin-bottom: 6px;">1. Minimum Monthly Salary & Turnover</h3>
                    <p style="font-size: 14px; color: #64748b; line-height: 1.6; margin: 0;">Salaried applicants require a minimum monthly in-hand salary of ₹20,000, while self-employed business owners require verifiable annual turnover of ₹5 Lakh+.</p>
                </div>
                <div style="padding: 18px; border-left: 4px solid #0056b3; background: #f1f5f9; border-radius: 0 8px 8px 0;">
                    <h3 style="font-size: 16px; color: #0c1a30; font-weight: 600; margin-bottom: 6px;">2. CIBIL Score Required for Loan</h3>
                    <p style="font-size: 14px; color: #64748b; line-height: 1.6; margin: 0;">A credit score of 650 or higher ensures instant approval with lowest interest rates. You can also check your <a href="/free-credit-score" style="color: #0056b3; text-decoration: underline;">free credit score</a> before applying.</p>
                </div>
                <div style="padding: 18px; border-left: 4px solid #0056b3; background: #f1f5f9; border-radius: 0 8px 8px 0;">
                    <h3 style="font-size: 16px; color: #0c1a30; font-weight: 600; margin-bottom: 6px;">3. Fixed Obligation to Income Ratio (FOIR)</h3>
                    <p style="font-size: 14px; color: #64748b; line-height: 1.6; margin: 0;">Lenders typically prefer an existing EMI burden below 40%–50% of your net monthly earnings to approve a fresh loan.</p>
                </div>
                <div style="padding: 18px; border-left: 4px solid #0056b3; background: #f1f5f9; border-radius: 0 8px 8px 0;">
                    <h3 style="font-size: 16px; color: #0c1a30; font-weight: 600; margin-bottom: 6px;">4. Work Experience & Business Vintage</h3>
                    <p style="font-size: 14px; color: #64748b; line-height: 1.6; margin: 0;">At least 6 months at your current employer or 1+ year in business demonstrates stable repayment consistency.</p>
                </div>
            </div>
        </div>

        <!-- SECTION 3: HOW MUCH LOAN CAN I GET (SALARY-BASED CALCULATION) -->
        <div style="background: #ffffff; border-radius: 12px; padding: 35px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.04);">
            <h2 style="font-size: 24px; color: #0c1a30; font-weight: 700; margin-bottom: 15px;">
                How Much Personal Loan Can I Get on My Salary?
            </h2>
            <p style="font-size: 15px; color: #475569; line-height: 1.8;">
                The table below provides a representative estimate of borrowable personal loan amounts based on net monthly salary and existing EMI commitments (assuming a 3-year tenure at standard NBFC rates):
            </p>
            <div style="overflow-x: auto; margin-top: 15px;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                    <thead>
                        <tr style="background: #0c1a30; color: #ffffff;">
                            <th style="padding: 12px 16px; border: 1px solid #e2e8f0;">Monthly Net Salary</th>
                            <th style="padding: 12px 16px; border: 1px solid #e2e8f0;">Existing EMIs</th>
                            <th style="padding: 12px 16px; border: 1px solid #e2e8f0;">Max Permissible EMI (50% FOIR)</th>
                            <th style="padding: 12px 16px; border: 1px solid #e2e8f0;">Estimated Loan Eligibility</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background: #ffffff;">
                            <td style="padding: 12px 16px; border: 1px solid #e2e8f0; font-weight: 600;">₹25,000</td>
                            <td style="padding: 12px 16px; border: 1px solid #e2e8f0;">₹0</td>
                            <td style="padding: 12px 16px; border: 1px solid #e2e8f0;">₹12,500</td>
                            <td style="padding: 12px 16px; border: 1px solid #e2e8f0; color: #0056b3; font-weight: 700;">Up to ₹3.5 Lakh</td>
                        </tr>
                        <tr style="background: #f8fafc;">
                            <td style="padding: 12px 16px; border: 1px solid #e2e8f0; font-weight: 600;">₹40,000</td>
                            <td style="padding: 12px 16px; border: 1px solid #e2e8f0;">₹5,000</td>
                            <td style="padding: 12px 16px; border: 1px solid #e2e8f0;">₹15,000</td>
                            <td style="padding: 12px 16px; border: 1px solid #e2e8f0; color: #0056b3; font-weight: 700;">Up to ₹5.0 Lakh</td>
                        </tr>
                        <tr style="background: #ffffff;">
                            <td style="padding: 12px 16px; border: 1px solid #e2e8f0; font-weight: 600;">₹60,000</td>
                            <td style="padding: 12px 16px; border: 1px solid #e2e8f0;">₹10,000</td>
                            <td style="padding: 12px 16px; border: 1px solid #e2e8f0;">₹20,000</td>
                            <td style="padding: 12px 16px; border: 1px solid #e2e8f0; color: #0056b3; font-weight: 700;">Up to ₹7.5 Lakh</td>
                        </tr>
                        <tr style="background: #f8fafc;">
                            <td style="padding: 12px 16px; border: 1px solid #e2e8f0; font-weight: 600;">₹1,00,000+</td>
                            <td style="padding: 12px 16px; border: 1px solid #e2e8f0;">₹15,000</td>
                            <td style="padding: 12px 16px; border: 1px solid #e2e8f0;">₹35,000</td>
                            <td style="padding: 12px 16px; border: 1px solid #e2e8f0; color: #0056b3; font-weight: 700;">Up to ₹10 Lakh+</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p style="font-size: 13px; color: #64748b; margin-top: 10px;">*Figures are indicative and subject to final credit appraisal, bureau verification, and internal underwriting guidelines.</p>
        </div>

        <!-- SECTION 4: TIPS TO IMPROVE LOAN ELIGIBILITY -->
        <div style="background: #ffffff; border-radius: 12px; padding: 35px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.04);">
            <h2 style="font-size: 24px; color: #0c1a30; font-weight: 700; margin-bottom: 15px;">
                Proven Strategies to Increase Your Loan Eligibility
            </h2>
            <ul style="font-size: 15px; color: #475569; line-height: 1.9; padding-left: 20px;">
                <li><strong>Clear Short-Term Credit Lines:</strong> Foreclosing active consumer loans or credit card balances instantly reduces your FOIR and increases your borrowable limit.</li>
                <li><strong>Select a Longer Loan Tenure:</strong> Extending your repayment period reduces the monthly EMI requirement, allowing you to qualify for a higher principal loan amount.</li>
                <li><strong>Add a Co-Borrower:</strong> Adding an earning spouse or parent as a co-applicant combines your household income, helping you pass higher sanction thresholds.</li>
                <li><strong>Maintain Clean Banking Conduct:</strong> Avoiding cheque returns, ECS debit bounces, and late fee penalties gives lenders confidence to offer instant approval.</li>
            </ul>
            <div style="margin-top: 25px;">
                <a href="/apply" style="display: inline-block; background: #0056b3; color: #ffffff; padding: 12px 28px; border-radius: 6px; font-weight: 600; text-decoration: none; font-size: 15px;">Apply for Loan Online &rarr;</a>
            </div>
        </div>

        <!-- SECTION 5: FAQS (STRUCTURED WITH KEYWORDS) -->
        <div style="background: #ffffff; border-radius: 12px; padding: 35px; box-shadow: 0 4px 15px rgba(0,0,0,0.04);">
            <h2 style="font-size: 24px; color: #0c1a30; font-weight: 700; margin-bottom: 20px;">
                Frequently Asked Questions About Loan Eligibility
            </h2>
            <div style="margin-bottom: 20px;">
                <h3 style="font-size: 16px; color: #0c1a30; font-weight: 600; margin-bottom: 6px;">Does using this loan eligibility calculator affect my CIBIL score?</h3>
                <p style="font-size: 14px; color: #475569; line-height: 1.6;">No. Our online loan eligibility calculator is a 100% free estimation tool. It does not trigger a hard inquiry with credit bureaus (CIBIL, Experian, CRIF), so your score remains completely unaffected.</p>
            </div>
            <div style="margin-bottom: 20px;">
                <h3 style="font-size: 16px; color: #0c1a30; font-weight: 600; margin-bottom: 6px;">What documents do I need to prove my loan eligibility?</h3>
                <p style="font-size: 14px; color: #475569; line-height: 1.6;">To verify your eligibility, you will need your PAN Card, Aadhaar Card, latest 3 months' salary slips, and 6 months' bank account statements showing salary credits or business transactions.</p>
            </div>
            <div style="margin-bottom: 20px;">
                <h3 style="font-size: 16px; color: #0c1a30; font-weight: 600; margin-bottom: 6px;">Can I get a loan with a low CIBIL score?</h3>
                <p style="font-size: 14px; color: #475569; line-height: 1.6;">Yes. While prime banks mandate a 750+ score, as an RBI-registered NBFC, Waqt Finance evaluates applications with scores from 600+ based on stable employment and healthy cash flows.</p>
            </div>
            <div>
                <h3 style="font-size: 16px; color: #0c1a30; font-weight: 600; margin-bottom: 6px;">How quickly is the loan disbursed once I am eligible?</h3>
                <p style="font-size: 14px; color: #475569; line-height: 1.6;">After completing instant online verification and e-signing your loan agreement, approved funds are directly transferred to your bank account within 24 to 48 hours.</p>
            </div>
        </div>

    </div>
</section>

<!-- FAQ SCHEMA MARKUP (ENABLES SERP FAQ ACCORDION RICH SNIPPETS) -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Does using this loan eligibility calculator affect my CIBIL score?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "No. Our online loan eligibility calculator is a 100% free estimation tool. It does not trigger a hard inquiry with credit bureaus, so your score remains unaffected."
      }
    },
    {
      "@type": "Question",
      "name": "What documents do I need to prove my loan eligibility?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "You will need your PAN Card, Aadhaar Card, latest 3 months salary slips, and 6 months bank account statements showing salary credits or business transactions."
      }
    },
    {
      "@type": "Question",
      "name": "Can I get a loan with a low CIBIL score?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Yes. As an RBI-registered NBFC, Waqt Finance evaluates applications with scores from 600+ based on current income stability and healthy cash flows."
      }
    },
    {
      "@type": "Question",
      "name": "How quickly is the loan disbursed once I am eligible?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "After online verification and e-signing, approved funds are disbursed directly into your bank account within 24 to 48 hours."
      }
    }
  ]
}
</script>
</main>
<script>
    document.addEventListener("DOMContentLoaded", () => {

        function updateRangeProgress(range) {
            const min = Number(range.min) || 0;
            const max = Number(range.max) || 100;
            const value = Number(range.value);

            const percent = ((value - min) / (max - min)) * 100;

            range.style.setProperty(
                'background',
                `linear-gradient(to right, #18529c 0%, #18529c ${percent}%, #e2e8f0 ${percent}%, #e2e8f0 100%)`,
                'important'
            );
        }

        document.querySelectorAll(".progress-range").forEach(range => {
            updateRangeProgress(range);
            range.addEventListener("input", () => {
                updateRangeProgress(range);
            });
        });
        console.log(document.querySelectorAll(".progress-range"))
    });
</script>

<?php include 'includes/footer.php' ?>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="assets/js/eligibility.js"></script>
</body>

</html>
