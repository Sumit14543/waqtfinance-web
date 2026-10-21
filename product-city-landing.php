<?php
declare(strict_types=1);

$product_key = trim((string) ($_GET['product'] ?? ''));
$city_key = trim((string) ($_GET['city'] ?? ''));

$products = [
    'personal-loan' => [
        'name' => 'Personal Loan',
        'apply_url' => 'personal-loan-apply',
        'min_amount' => '₹50,000',
        'max_amount_val' => 1500000,
        'default_rate' => 12.99,
        'default_tenure' => 24,
        'tenure' => '12 to 60 Months',
        'interest' => 'Starting at 10.99% p.a.',
        'desc' => 'Instant personal loan options for medical emergencies, wedding costs, home renovations, and travel across India.',
        'features' => [
            '100% paperless digital journey',
            'No collateral or guarantor required',
            'Flexible repayment options',
            'Quick digital verification via Aadhaar/OTP'
        ],
        'eligibility' => [
            'Age: 21 to 58 years',
            'Employment: Salaried with monthly credit in bank account',
            'Minimum Net Income: ₹25,000 per month',
            'Credit Score: 700+ preferred'
        ],
        'documents' => [
            'Identity Proof: PAN Card & Aadhaar Card',
            'Address Proof: Aadhaar, Electricity Bill, or Rent Agreement',
            'Income Proof: Last 3 months bank statements showing salary credits',
            'Employment Proof: Employee ID Card or Offer Letter'
        ],
        'choice_scenarios' => [
            'title' => 'When a Personal Loan is the Right Choice',
            'items' => [
                [
                    'q' => 'Temporary Cash Flow Gaps',
                    'a' => 'Ideal when you are waiting for a bonus, consulting fee, or salary increments in the coming months.'
                ],
                [
                    'q' => 'Medical & Emergencies',
                    'a' => 'Quick funding during health emergencies, family hospitalizations, or critical vehicle repairs.'
                ],
                [
                    'q' => 'Debt Consolidation',
                    'a' => 'Combine multiple credit card bills or small dues into a single fixed EMI with a clear repayment roadmap.'
                ]
            ]
        ],
        'benefits_vs_risks' => [
            'benefits' => [
                'Fast approval cycles without pledging collateral',
                'Lower total interest outgo when repaid in short cycles',
                'Helps improve credit score with timely repayments'
            ],
            'risks' => [
                'Higher EMI payments on shorter loan durations',
                'Risk of late fees and credit score damage on defaults',
                'Processing charges up to 3% apply upon sanction'
            ]
        ],
        'common_mistakes' => [
            [
                'q' => 'Ignoring the Total Cost',
                'a' => 'Many focus only on interest rates while ignoring processing fees, documentation charges, and penal rates.'
            ],
            [
                'q' => 'Underestimating EMIs',
                'a' => 'Taking a shorter tenure to save interest can choke your daily cash flow with high monthly installments.'
            ],
            [
                'q' => 'Borrowing Without Visibility',
                'a' => 'Applying without confirmed salary or alternative income streams increases the probability of default.'
            ]
        ],
        'faqs' => [
            [
                'q' => 'How quickly is a Personal Loan approved?',
                'a' => 'After documents are uploaded, the application moves through eligibility, verification and policy checks. Timelines vary by applicant profile and document completeness.'
            ],
            [
                'q' => 'Do I need any security or collateral?',
                'a' => 'No. Personal Loans at Waqt Finance are unsecured, meaning you do not need to pledge any property, vehicle, or assets.'
            ],
            [
                'q' => 'Can a short-term personal loan be approved without a high credit score?',
                'a' => 'Yes, applicants with moderate credit scores can get approved, though a credit score above 700 helps secure the best interest rates.'
            ],
            [
                'q' => 'Is a 12-month loan better than a 36-month loan?',
                'a' => 'A 12-month loan saves you significant total interest but carries a higher monthly EMI. Choose based on your monthly cash flow comfort.'
            ],
            [
                'q' => 'Will prepaying my loan improve my credit score?',
                'a' => 'Paying off a loan early shows positive repayment behavior, though regular timely monthly payments have the strongest positive impact on your CIBIL profile.'
            ]
        ]
    ],
    'business-loan' => [
        'name' => 'Business Loan',
        'apply_url' => 'business-loan-apply',
        'min_amount' => '₹1,00,000',
        'max_amount_val' => 2500000,
        'default_rate' => 15.99,
        'default_tenure' => 36,
        'tenure' => '12 to 72 Months',
        'interest' => 'Competitive rates',
        'desc' => 'Collateral-free business funding for working capital, stock purchase, machinery upgrades, and business expansion.',
        'features' => [
            'Unsecured funding up to ₹25 Lakhs',
            'Quick processing for registered MSMEs',
            'Flexible repayment terms suited to cash flow',
            'Transparent terms with no hidden charges'
        ],
        'eligibility' => [
            'Business Type: Sole Proprietor, Partnership, or Private Limited',
            'Business Vintage: Active operations for at least 1 year',
            'Minimum Annual Turnover: ₹12 Lakhs audited/reported',
            'Credit Profile: No active defaults in last 12 months'
        ],
        'documents' => [
            'KYC: PAN & Aadhaar of the promoter/owner',
            'Business Registration: GST registration, Udyam certificate, or Shop Establishment License',
            'Financial Proof: 6 months current bank statements',
            'Tax Proof: Latest GST returns or ITR (if applicable)'
        ],
        'choice_scenarios' => [
            'title' => 'When a Business Loan is the Right Choice',
            'items' => [
                [
                    'q' => 'Inventory Cycles',
                    'a' => 'Perfect for purchasing raw materials or stocking inventory ahead of high-demand seasonal cycles.'
                ],
                [
                    'q' => 'Equipment & Upgrades',
                    'a' => 'Secure capital to upgrade IT hardware, commercial machinery, or purchase transport assets.'
                ],
                [
                    'q' => 'Vendor Cash Gaps',
                    'a' => 'Manage cash flow gaps when waiting for client payments or pending vendor invoices.'
                ]
            ]
        ],
        'benefits_vs_risks' => [
            'benefits' => [
                'Collateral-free MSME financing up to ₹25 Lakhs',
                'Flexible tenure structures aligning with billing cycles',
                'Interest costs can be claimed as business expenses'
            ],
            'risks' => [
                'Higher daily/monthly commitment on short terms',
                'Defaults can negatively affect promoter credit rating',
                'Late payment charges and bounce penalties apply'
            ]
        ],
        'common_mistakes' => [
            [
                'q' => 'Mixing Personal and Business Funds',
                'a' => 'Using personal bank accounts to manage business loan repayments makes accounting and audit compliance complex.'
            ],
            [
                'q' => 'Borrowing Without Cash Flow Matching',
                'a' => 'If your client payment cycles are 90 days, choosing a monthly EMI without reserves can lead to defaults.'
            ],
            [
                'q' => 'Neglecting Business Documentation',
                'a' => 'Not maintaining valid GST registrations or tax records restricts eligibility for higher amounts.'
            ]
        ],
        'faqs' => [
            [
                'q' => 'Is a GST registration mandatory for a Business Loan?',
                'a' => 'While GST registration is preferred and helps secure higher loan amounts, small businesses with valid local establishment licenses or Udyam certificates can also apply.'
            ],
            [
                'q' => 'How can I use the business loan funds?',
                'a' => 'Funds can be used for any legitimate business activity, including buying raw materials, paying salaries, scaling inventory, or upgrading equipment.'
            ],
            [
                'q' => 'Do business loans require a guarantor?',
                'a' => 'Our unsecured business loans do not require third-party guarantors, though promoters must provide personal guarantees.'
            ],
            [
                'q' => 'What is the maximum business loan amount available?',
                'a' => 'Waqt Finance provides unsecured business loans up to ₹25 Lakhs based on turnover, bank transaction volumes, and repayment history.'
            ]
        ]
    ],
    'pay-day-loan' => [
        'name' => 'Payday Loan',
        'apply_url' => 'payday-apply',
        'min_amount' => '₹3,000',
        'max_amount_val' => 150000,
        'default_rate' => 18.00,
        'default_tenure' => 1,
        'tenure' => 'Up to 1 Month',
        'interest' => 'Flat short-term rates',
        'desc' => 'Short-term cash advance to manage sudden emergency expenses and salary gaps before your next pay day.',
        'features' => [
            'Salary advance support from ₹3,000 to ₹1.5 Lakhs',
            'Processing fees up to 6% plus applicable taxes',
            'Online verification via Aadhaar/PAN checks with borrower consent',
            'Convenient single-payment option on salary date'
        ],
        'eligibility' => [
            'Employment: Full-time salaried employees',
            'Salary Mode: Direct bank transfer (cash/cheque salary not accepted)',
            'Minimum Salary: ₹15,000 per month',
            'Age: 21 to 58 years'
        ],
        'documents' => [
            'KYC: Aadhaar Card & PAN Card',
            'Salary Bank Statement: Last 3 months statement in PDF format',
            'Work Proof: Corporate Email Verification or Salary Slip'
        ],
        'choice_scenarios' => [
            'title' => 'When a Payday Loan is the Right Choice',
            'items' => [
                [
                    'q' => 'Mid-Month Salary Gaps',
                    'a' => 'Bridge small utility or bill gaps when salary is delayed or exhausted in the second half of the month.'
                ],
                [
                    'q' => 'Immediate Medical Purchase',
                    'a' => 'Useful for pharmacy purchases, urgent diagnostic tests, or medical emergencies before salary date.'
                ],
                [
                    'q' => 'Credit Card Bill Defense',
                    'a' => 'Avoid heavy credit card late payment fees and CIBIL impact by paying off card dues on time using payday funds.'
                ]
            ]
        ],
        'benefits_vs_risks' => [
            'benefits' => [
                'Digital review and bank account credit after approval',
                'No multi-year debt burden; repays in a single shot',
                'Helps manage mid-month liquidity issues safely'
            ],
            'risks' => [
                'High flat interest rate calculated over small durations',
                'Requires complete repayment from the next salary credit',
                'Late payment charges apply daily for overdue bills'
            ]
        ],
        'common_mistakes' => [
            [
                'q' => 'Over-borrowing',
                'a' => 'Borrowing more than your repayment capability will lead to a severe budget crisis on salary day.'
            ],
            [
                'q' => 'Relying on Payday Loans for Routine Expenses',
                'a' => 'Using payday loans repeatedly to buy groceries indicates basic budget deficits that need planning, not credit.'
            ],
            [
                'q' => 'Delaying the Repayment Date',
                'a' => 'Missing the payment auto-debit triggers bounce charges and damages credit score severely.'
            ]
        ],
        'faqs' => [
            [
                'q' => 'How does a Payday Loan work?',
                'a' => 'It is a salary advance. You borrow a small amount (₹3,00,000 max) to cover a temporary cash shortage and repay it in a single payment when your next salary is credited.'
            ],
            [
                'q' => 'Will applying affect my credit score?',
                'a' => 'We perform soft credit checks for initial eligibility which do not hurt your credit rating. Repaying on time will help build your credit profile.'
            ],
            [
                'q' => 'How is a payday loan different from a standard personal loan?',
                'a' => 'Standard personal loans are repaid over 12-60 months in equal monthly installments, while payday loans are repaid in a single installment within 7 to 30 days.'
            ],
            [
                'q' => 'Can self-employed individuals apply for a payday loan?',
                'a' => 'No. Payday loans are designed strictly for salaried individuals with steady payroll credits. Self-employed owners can explore our Business or EDI loans instead.'
            ],
            [
                'q' => 'What happens if I cannot pay on time?',
                'a' => 'Late payment interest and administrative penalty fees will apply. Also, it will be reported to credit bureaus, hurting your future borrowing capabilities.'
            ]
        ]
    ],
    'edi-loan' => [
        'name' => 'EDI Loan',
        'apply_url' => 'edi-loan-apply',
        'min_amount' => '₹25,000',
        'max_amount_val' => 500000,
        'default_rate' => 18.00,
        'default_tenure' => 3,
        'tenure' => '7 to 90 Days',
        'interest' => 'Daily installment finance',
        'desc' => 'Daily installment business loan designed specifically for retail shopkeepers, street vendors, and small traders.',
        'features' => [
            'Convenient daily repayment collection model',
            'Matches daily cash counter sales',
            'Instant Udyam/GST verification & fast processing',
            'No monthly heavy payment stress'
        ],
        'eligibility' => [
            'Profile: Retailers, Kirana shops, vendors, and small traders',
            'Business Location: Operating from a physical store or stable setup',
            'Vintage: Minimum 6 months of continuous operation',
            'Daily Income: Steady daily sales counter'
        ],
        'documents' => [
            'KYC: Owner PAN Card & Aadhaar Card',
            'Business Proof: Udyam Registration or Shop license',
            'Banking: Last 6 months bank statement (current or savings)'
        ],
        'choice_scenarios' => [
            'title' => 'When an EDI Loan is the Right Choice',
            'items' => [
                [
                    'q' => 'Retail Cash Counter Sales',
                    'a' => 'Ideal for businesses receiving daily cash and card sales, matching daily micro-collection schedules.'
                ],
                [
                    'q' => 'Festival Inventory Spikes',
                    'a' => 'Quickly buy seasonal merchandise, crackers, clothing, or sweets, and pay off daily as counter sales happen.'
                ],
                [
                    'q' => 'Kirana Restocking',
                    'a' => 'Manage weekly inventory refills without locking up your primary savings capital.'
                ]
            ]
        ],
        'benefits_vs_risks' => [
            'benefits' => [
                'No heavy monthly installment pressure on the business',
                'Fits Kirana and retail counter daily revenue cycles',
                'Extremely low verification barriers for micro-traders'
            ],
            'risks' => [
                'Requires sufficient daily bank balance for e-NACH pulls',
                'Only useful for short cycle working capital needs',
                'Late penalties accrue daily if debit bounces'
            ]
        ],
        'common_mistakes' => [
            [
                'q' => 'Failing to Maintain Daily Bank Balance',
                'a' => 'If the daily balance drops below the debit value, bank bounce fees and e-NACH charges will multiply rapidly.'
            ],
            [
                'q' => 'Borrowing for Non-Income Assets',
                'a' => 'Using daily repayment loans to buy slow-moving assets will choke your daily operational cash reserves.'
            ]
        ],
        'faqs' => [
            [
                'q' => 'What is an EDI Loan?',
                'a' => 'EDI stands for Easy Daily Installment. Instead of paying a heavy monthly EMI, a small installment is collected daily, matching the daily revenue cycle of shopkeepers.'
            ],
            [
                'q' => 'How is the daily payment collected?',
                'a' => 'Collection is automated through secure e-NACH/Auto-debit from your registered bank account daily, making the cycle hassle-free.'
            ],
            [
                'q' => 'Are there any pre-payment charges?',
                'a' => 'Yes, traders can pay off the outstanding principal early to save interest, subject to standard foreclosure terms.'
            ],
            [
                'q' => 'Is a physical office visit necessary?',
                'a' => 'No. The entire process, from KYC submission to e-mandate registration, is managed completely online.'
            ]
        ]
    ],
    'vehicle-loan' => [
        'name' => 'Vehicle Loan',
        'apply_url' => 'vehicle-loan-apply',
        'min_amount' => '₹50,000',
        'max_amount_val' => 1000000,
        'default_rate' => 10.99,
        'default_tenure' => 36,
        'tenure' => '12 to 84 Months',
        'interest' => 'Attractive auto rates',
        'desc' => 'Flexible finance options for new and pre-owned cars, two-wheelers, and commercial vehicles with easy processing.',
        'features' => [
            'Up to 90% funding on vehicle asset value',
            'Dealer direct payouts for fast delivery',
            'Simple documentation and registration support',
            'Competitive interest rates starting at 10.99% p.a.'
        ],
        'eligibility' => [
            'Applicant: Salaried professionals, self-employed, or firm owners',
            'Min Income: ₹2,00,000 per annum (income proof required)',
            'Age: 21 to 65 years at loan maturity',
            'CIBIL Score: 700+ preferred for best rates'
        ],
        'documents' => [
            'KYC: PAN Card, Aadhaar Card, or Passport',
            'Address Proof: Utility bills or driving license',
            'Income Proof: 3 months salary slips or last year ITR',
            'Vehicle Details: Quotation from authorized dealer (or registration copy for pre-owned)'
        ],
        'choice_scenarios' => [
            'title' => 'When a Vehicle Loan is the Right Choice',
            'items' => [
                [
                    'q' => 'Buying a Commercial Auto',
                    'a' => 'Perfect when expanding your logistics business or cargo delivery fleet with customized EMIs.'
                ],
                [
                    'q' => 'Pre-Owned Car Purchase',
                    'a' => 'Finance used passenger cars that fit your budget, subject to quick asset inspection.'
                ]
            ]
        ],
        'benefits_vs_risks' => [
            'benefits' => [
                'Covers up to 90% of the vehicle ex-showroom cost',
                'Lower interest rates because the loan is secured by the asset',
                'Flexible tenure choices going up to 7 years'
            ],
            'risks' => [
                'The vehicle remains hypothecated to the lender until full closure',
                'Non-payment can lead to asset repossession by the lender',
                'Pre-owned vehicles require valuation and fitness certification'
            ]
        ],
        'common_mistakes' => [
            [
                'q' => 'Ignoring Hypothecation Closure',
                'a' => 'After fully paying the loan, failing to remove the hypothecation from the RC at the RTO will restrict future sale.'
            ],
            [
                'q' => 'Skipping Insurance Verification',
                'a' => 'Maintaining comprehensive vehicle insurance is mandatory during the entire tenure; neglecting it violates loan covenants.'
            ]
        ],
        'faqs' => [
            [
                'q' => 'Can I get a loan for a used car or bike?',
                'a' => 'Yes, Waqt Finance offers pre-owned vehicle loans subject to valuation and fitness checks of the vehicle.'
            ],
            [
                'q' => 'Is a guarantor required for a vehicle loan?',
                'a' => 'Generally, no guarantor is required if you meet our credit score and income eligibility rules.'
            ],
            [
                'q' => 'What is hypothecation?',
                'a' => 'Hypothecation means the vehicle acts as collateral. The registration certificate (RC) will mention Waqt Finance as the mortgagee until the loan is fully repaid.'
            ]
        ]
    ],
    'loan-against-property' => [
        'name' => 'Loan Against Property',
        'apply_url' => 'loan-against-property-apply',
        'min_amount' => '₹5,00,000',
        'max_amount_val' => 5000000,
        'default_rate' => 9.99,
        'default_tenure' => 120,
        'tenure' => '12 to 240 Months',
        'interest' => 'Lower secured loan rates',
        'desc' => 'Unlock maximum cash value from your residential, commercial, or industrial property with long-term repayment tenure.',
        'features' => [
            'High value funding against property market value',
            'Lower interest rates compared to unsecured loans',
            'Long tenure up to 15 years for comfortable EMIs',
            'Continue using the property during the loan tenure'
        ],
        'eligibility' => [
            'Property Owner: Salaried, self-employed, traders, or companies',
            'Property Status: Clear title, approved plan, and mortgage-free',
            'Age Limit: 21 to 65 years',
            'Stable Income: Verifiable through ITR, salary, or business banking'
        ],
        'documents' => [
            'Identity & Address: Aadhaar, PAN Card, and utility documents',
            'Property Documents: Registry copy, chains of title, tax receipts, and approved building plan',
            'Income Proof: Last 2 years ITR with financial statements or 6 months bank statement'
        ],
        'choice_scenarios' => [
            'title' => 'When a Loan Against Property is the Right Choice',
            'items' => [
                [
                    'q' => 'High Value Capital Needs',
                    'a' => 'When you need heavy long-term capital for business setup, children\'s higher education abroad, or factory purchases.'
                ],
                [
                    'q' => 'Refinancing Expensive Loans',
                    'a' => 'Consolidate short-term business debts into a single, low-interest long-term property loan to reduce monthly cash stress.'
                ]
            ]
        ],
        'benefits_vs_risks' => [
            'benefits' => [
                'Significantly lower interest rates compared to personal/business loans',
                'Extended repayment tenures up to 15-20 years',
                'Larger loan amounts (up to 70% of property market value)'
            ],
            'risks' => [
                'Risk of property foreclosure and loss of asset in case of defaults',
                'Lengthy legal and technical title verification cycles',
                'Valuation and processing charges are relatively higher'
            ]
        ],
        'common_mistakes' => [
            [
                'q' => 'Borrowing Without Title Clearance',
                'a' => 'Applying with incomplete registry chains, outstanding property taxes, or missing parent deeds leads to rejection.'
            ],
            [
                'q' => 'Over-leveraging property values',
                'a' => 'Borrowing more than your monthly income capacity can support makes the long-term EMI schedule a severe risk.'
            ]
        ],
        'faqs' => [
            [
                'q' => 'What types of properties are eligible for LAP?',
                'a' => 'Residential houses, flats, commercial offices, shops, and registered industrial plots are eligible, provided they have clear titles and approved layouts.'
            ],
            [
                'q' => 'How much loan amount can I expect?',
                'a' => 'You can secure up to 60-70% of the market valuation of the property, subject to income assessment checks.'
            ],
            [
                'q' => 'Can I continue using the property during the loan tenure?',
                'a' => 'Yes, you retain full operational usage, tenancy, or occupancy rights of the mortgaged property during the loan cycle.'
            ],
            [
                'q' => 'What is the processing time for a Loan Against Property?',
                'a' => 'Because LAP involves detailed physical verification, valuation, and legal title search, processing typically takes 7 to 10 working days.'
            ]
        ]
    ]
];

$cities = [
    'delhi-ncr' => [
        'name' => 'Delhi NCR',
        'type' => 'AdministrativeArea',
        'areas' => ['Delhi', 'Noida', 'Greater Noida', 'Gurugram', 'Ghaziabad', 'Faridabad'],
        'desc' => 'across Delhi NCR, including New Delhi, Noida, Gurugram, Ghaziabad, Faridabad, and Greater Noida.',
        'office' => 'Noida Sector 63 Corporate HQ'
    ],
    'delhi' => [
        'name' => 'Delhi',
        'type' => 'City',
        'areas' => ['Dwarka', 'Karol Bagh', 'Lajpat Nagar', 'Connaught Place', 'Rohini', 'Pitampura', 'Dwarka Mor'],
        'desc' => 'across Delhi, including Dwarka, Karol Bagh, Lajpat Nagar, Connaught Place, Rohini, and Pitampura.',
        'office' => 'Delhi customer support zone'
    ],
    'noida' => [
        'name' => 'Noida',
        'type' => 'City',
        'areas' => ['Sector 63', 'Sector 62', 'Sector 18', 'Greater Noida West', 'Pari Chowk', 'Sector 15', 'Sector 63 HQ'],
        'desc' => 'across Noida and Greater Noida, including Sector 63, Sector 62, Sector 18, Greater Noida West, and Pari Chowk.',
        'office' => 'Corporate Head Office in Noida Sector 63'
    ],
    'gurugram' => [
        'name' => 'Gurugram',
        'type' => 'City',
        'areas' => ['DLF Cyber City', 'Udyog Vihar', 'Golf Course Road', 'Sohna Road', 'Sector 14', 'Sector 45', 'Sector 56'],
        'desc' => 'across Gurugram, including DLF Cyber City, Udyog Vihar, Golf Course Road, Sohna Road, and major sectors.',
        'office' => 'Gurugram customer support zone'
    ]
];

// Check validity of product
if (!isset($products[$product_key])) {
    include '404.php';
    exit;
}

// Always try to load city from DB first (so admin panel edits take precedence!)
require_once __DIR__ . '/config/db.php';
$conn = db();
$stmt = $conn->prepare("SELECT * FROM location_pages WHERE slug = ? AND status = 'published' LIMIT 1");
if ($stmt) {
    $stmt->bind_param('s', $city_key);
    $stmt->execute();
    $dbCityRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($dbCityRow) {
        $dbAreas = json_decode((string) $dbCityRow['areas'], true) ?? [];
        $cities[$city_key] = [
            'name' => $dbCityRow['name'],
            'type' => $dbCityRow['type'] === 'office' ? 'AdministrativeArea' : 'City',
            'areas' => $dbAreas,
            'desc' => 'across ' . $dbCityRow['name'] . ', including ' . implode(', ', array_slice($dbAreas, 0, 5)) . '.',
            'office' => $dbCityRow['type'] === 'office' ? $dbCityRow['address'] : $dbCityRow['name'] . ' customer support zone',
            'product_content' => $dbCityRow['product_content'] ?? null
        ];
    }
}

// Check if city exists (either loaded from DB or hardcoded fallback)
if (!isset($cities[$city_key])) {
    include '404.php';
    exit;
}

$prod = $products[$product_key];
$city = $cities[$city_key];

// Default page meta and heading content
$loan_range_map = ['personal-loan' => '₹50,000 to ₹25 Lakh', 'business-loan' => '₹1 Lakh to ₹25 Lakh', 'pay-day-loan' => '₹3,000 to ₹1.5 Lakh before salary', 'edi-loan' => '₹25,000 to ₹5 Lakh (daily EDI)', 'vehicle-loan' => 'up to 90% on-road vehicle finance', 'loan-against-property' => '₹5 Lakh to ₹5 Crore'];
$range_text = $loan_range_map[$product_key] ?? "loans from {$prod['min_amount']}";
$page_title = "{$prod['name']} in {$city['name']} | Apply Online | Waqt Finance";
$meta_description = "Apply online for {$prod['name']} in {$city['name']} at Waqt Finance. Get {$range_text} with fast online approval & flexible EMIs.";
$meta_keywords = "{$product_key} in {$city_key}, {$prod['name']} {$city['name']}, online loan {$city['name']}, {$product_key} online {$city['name']}";
$page_heading = "{$prod['name']} in {$city['name']}";
$page_description_paragraph = "Apply online with Waqt Finance for " . strtolower($prod['name']) . " options in {$city['name']}. We support borrowers with a digital application process, consent-based checks, document review, and clear terms before acceptance.";

// Merge/Override content if database overrides exist
if (!empty($city['product_content'])) {
    $overrides = json_decode((string) $city['product_content'], true);
    if (is_array($overrides) && isset($overrides[$product_key])) {
        $p_overrides = $overrides[$product_key];
        
        // 1. Structural overrides first (so they flow into defaults if specific text overrides are empty)
        if (!empty($p_overrides['min_amount'])) {
            $prod['min_amount'] = $p_overrides['min_amount'];
        }
        if (!empty($p_overrides['tenure'])) {
            $prod['tenure'] = $p_overrides['tenure'];
        }
        if (!empty($p_overrides['interest'])) {
            $prod['interest'] = $p_overrides['interest'];
            if (preg_match('/(\d+(\.\d+)?)/', $p_overrides['interest'], $m)) {
                $prod['default_rate'] = floatval($m[1]);
            }
        }
        if (!empty($p_overrides['faqs']) && is_array($p_overrides['faqs'])) {
            $prod['faqs'] = $p_overrides['faqs'];
        }

        // 2. Text overrides
        if (!empty($p_overrides['title'])) {
            $page_heading = $p_overrides['title'];
        }
                if (!empty($p_overrides['meta_title']) && strlen(trim((string) $p_overrides['meta_title'])) <= 65) {
            $page_title = $p_overrides['meta_title'];
        }
        if (!empty($p_overrides['meta_description']) && strlen(trim((string) $p_overrides['meta_description'])) <= 160 && stripos((string) $p_overrides['meta_description'], 'Serving customers in') === false) {
            $meta_description = $p_overrides['meta_description'];
        } else {
            // Recompute with overridden min_amount and tenure
              $meta_description = "Apply online for {$prod['name']} in {$city['name']} at Waqt Finance. Get {$range_text} with fast online approval & flexible EMIs.";
        }
        if (!empty($p_overrides['description'])) {
            $page_description_paragraph = $p_overrides['description'];
        }
    }
}

$canonical_url = "https://waqtfinance.com/{$product_key}-in-{$city_key}";
$meta_image = "assets/images/loan-hero.webp";
$meta_image_alt = "{$prod['name']} in {$city['name']} by Waqt Finance";
$breadcrumb_label = "{$prod['name']} in {$city['name']}";

$seo_schema = [
    [
        "@context" => "https://schema.org",
        "@type" => "Service",
        "name" => "{$prod['name']} in {$city['name']}",
        "url" => $canonical_url,
        "description" => $meta_description,
        "provider" => [
            "@id" => "https://waqtfinance.com/#organization"
        ],
        "areaServed" => [
            ["@type" => $city['type'], "name" => $city['name']]
        ],
        "serviceType" => [
            $prod['name']
        ]
    ]
];

// Build & Append FAQ Schema if FAQs exist for improved Google Rich Snippets ranking
if (!empty($prod['faqs']) && is_array($prod['faqs'])) {
    $faq_entities = [];
    foreach ($prod['faqs'] as $faq) {
        if (!empty($faq['q']) && !empty($faq['a'])) {
            $faq_entities[] = [
                "@type" => "Question",
                "name" => $faq['q'],
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => strip_tags((string) $faq['a'])
                ]
            ];
        }
    }
    if (!empty($faq_entities)) {
        $seo_schema[] = [
            "@context" => "https://schema.org",
            "@type" => "FAQPage",
            "mainEntity" => $faq_entities
        ];
    }
}

$extra_head = <<<HTML
<style>
    :root {
        --wf-primary: #062b5d;
        --wf-secondary: #18529c;
        --wf-accent: #dc2626;
        --wf-slate-50: #f8fafc;
        --wf-slate-100: #f1f5f9;
        --wf-slate-200: #e2e8f0;
        --wf-slate-600: #475569;
        --wf-slate-800: #1e293b;
        --wf-slate-900: #0f172a;
    }
    
    .wf-local-hero {
        background: radial-gradient(circle at 50% 50%, #062b5d 0%, #031530 100%);
        color: #fff;
        padding: 85px 0 75px;
        text-align: center;
    }
    .wf-local-hero-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        max-width: 800px;
        margin: 0 auto;
    }
    .wf-local-hero h1 {
        color: #fff;
        font-size: clamp(34px, 5vw, 54px);
        font-weight: 800;
        line-height: 1.15;
        margin-bottom: 20px;
        letter-spacing: -0.5px;
        text-align: center;
    }
    .wf-local-hero p {
        color: rgba(255, 255, 255, 0.9);
        font-size: 18px;
        line-height: 1.75;
        max-width: 720px;
        margin-bottom: 30px;
        text-align: center;
    }
    
    .wf-local-section {
        padding: 55px 0;
    }
    .wf-local-section.wf-local-band {
        background: var(--wf-slate-50);
        border-top: 1px solid var(--wf-slate-100);
        border-bottom: 1px solid var(--wf-slate-100);
    }
    .wf-local-section h2 {
        color: var(--wf-primary);
        font-size: 32px;
        font-weight: 800;
        margin-bottom: 12px;
        letter-spacing: -0.5px;
        text-align: center;
    }
    .wf-local-sub {
        color: var(--wf-slate-600);
        max-width: 760px;
        line-height: 1.7;
        margin: 0 auto 35px auto;
        text-align: center;
        font-size: 16px;
    }
    
    /* Bento Structure */
    .wf-product-bento {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 20px;
        max-width: 960px;
        margin: 0 auto;
    }
    .wf-product-bento-card {
        background: #fff;
        border: 1px solid var(--wf-slate-200);
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.02);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .wf-product-bento-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.05);
    }
    .wf-product-bento-card strong {
        color: var(--wf-slate-600);
        display: block;
        font-size: 13px;
        text-transform: uppercase;
        margin-bottom: 8px;
        letter-spacing: 0.5px;
        text-align: center;
    }
    .wf-product-bento-card span {
        font-size: 22px;
        font-weight: 800;
        color: var(--wf-secondary);
        text-align: center;
    }
    
    .wf-local-btn {
        background: #fff;
        color: var(--wf-primary) !important;
        border-radius: 8px;
        padding: 12px 24px;
        font-weight: 800;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .wf-local-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
    }
    
    /* Interactive Tabs Section */
    .wf-tabs-container {
        max-width: 800px;
        margin: 0 auto;
        background: #fff;
        border: 1px solid var(--wf-slate-200);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.03);
    }
    .wf-tabs-nav {
        display: flex;
        background: var(--wf-slate-50);
        border-bottom: 1px solid var(--wf-slate-200);
    }
    .wf-tab-btn {
        flex: 1;
        padding: 16px;
        border: none;
        background: transparent;
        font-size: 15px;
        font-weight: 700;
        color: var(--wf-slate-600);
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
    }
    .wf-tab-btn.active {
        color: var(--wf-secondary);
        background: #fff;
        box-shadow: inset 0 -3px 0 var(--wf-secondary);
    }
    .wf-tabs-content {
        padding: 30px;
    }
    .wf-tab-pane {
        display: none;
    }
    .wf-tab-pane.active {
        display: block;
    }
    .wf-checklist {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .wf-checklist li {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 15px;
        color: var(--wf-slate-800);
        line-height: 1.4;
        text-align: left;
    }
    .wf-checklist li i {
        color: #22c55e;
        margin-top: 3px;
        font-size: 16px;
    }
    
    /* EMI Calculator Widget */
    .wf-calc-widget {
        max-width: 960px;
        margin: 0 auto;
        background: #fff;
        border: 1px solid var(--wf-slate-200);
        border-radius: 20px;
        padding: 35px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 40px;
    }
    .wf-calc-sliders {
        display: flex;
        flex-direction: column;
        gap: 28px;
        text-align: left;
    }
    .wf-slider-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .wf-slider-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 700;
        color: var(--wf-slate-800);
        font-size: 15px;
        margin: 0;
    }
    .wf-slider-header span {
        color: var(--wf-secondary);
        font-size: 18px;
        font-weight: 800;
    }
    .wf-slider-input {
        width: 100%;
        height: 6px;
        background: var(--wf-slate-200);
        border-radius: 5px;
        outline: none;
        -webkit-appearance: none;
    }
    .wf-slider-input::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--wf-secondary);
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        transition: transform 0.1s;
    }
    .wf-slider-input::-webkit-slider-thumb:hover {
        transform: scale(1.15);
    }
    .wf-calc-results {
        background: var(--wf-slate-50);
        border-radius: 16px;
        padding: 28px;
        border: 1px solid var(--wf-slate-200);
        display: flex;
        flex-direction: column;
        justify-content: center;
        text-align: center;
        gap: 20px;
    }
    .wf-calc-row {
        display: flex;
        flex-direction: column;
        gap: 4px;
        text-align: center;
    }
    .wf-calc-row strong {
        font-size: 13px;
        text-transform: uppercase;
        color: var(--wf-slate-600);
        letter-spacing: 0.5px;
        text-align: center;
    }
    .wf-calc-row span {
        font-size: 26px;
        font-weight: 800;
        color: var(--wf-primary);
        text-align: center;
    }
    
    /* How to Apply Stepper */
    .wf-apply-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid var(--wf-slate-200);
        box-shadow: 0 4px 30px rgba(15, 23, 42, 0.03);
        max-width: 800px;
        margin: 0 auto;
        padding: 45px 50px;
    }
    .wf-apply-timeline {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 36px;
    }
    .wf-apply-line {
        position: absolute;
        left: 12px;
        top: 10px;
        bottom: 10px;
        width: 2px;
        background: #fbcfe8;
        z-index: 1;
    }
    .wf-apply-step {
        position: relative;
        display: flex;
        align-items: flex-start;
        gap: 20px;
        z-index: 2;
    }
    .wf-apply-badge {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #b91c1c;
        border: 4px solid #fbcfe8;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .wf-apply-text {
        font-size: 16px;
        color: var(--wf-slate-600);
        line-height: 1.6;
        text-align: left;
    }
    .wf-apply-text strong {
        color: var(--wf-slate-850);
        font-weight: 800;
    }
    .wf-apply-btn-wrapper {
        text-align: center;
        margin-top: 35px;
    }
    .wf-apply-now-btn {
        background: #b91c1c;
        color: #ffffff !important;
        padding: 14px 45px;
        border-radius: 99px;
        font-size: 15px;
        font-weight: 800;
        text-decoration: none !important;
        display: inline-block;
        transition: all 0.2s ease;
        box-shadow: 0 4px 15px rgba(185, 28, 28, 0.2);
    }
    .wf-apply-now-btn:hover {
        background: #991b1b;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(185, 28, 28, 0.3);
    }
    
    /* Services Two-Column Grid */
    .wf-city-services-grid {
        display: grid;
        grid-template-columns: 1.1fr 0.9fr;
        gap: 40px;
        max-width: 960px;
        margin: 0 auto;
        text-align: left;
        align-items: stretch;
    }
    .wf-city-services-left {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .wf-city-services-left p {
        color: var(--wf-slate-600);
        line-height: 1.8;
        font-size: 15.5px;
        margin-bottom: 18px;
    }
    .wf-city-services-left p:last-child {
        margin-bottom: 0;
    }
    .wf-localities-serviced {
        background: #fff;
        border: 1px solid var(--wf-slate-200);
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 4px 15px rgba(15, 23, 42, 0.02);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .wf-localities-serviced h4 {
        font-size: 15px;
        font-weight: 700;
        color: var(--wf-primary);
        margin: 0 0 16px 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .wf-localities-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .wf-localities-grid span {
        font-size: 13px;
        color: var(--wf-slate-800);
        background: var(--wf-slate-50);
        padding: 6px 12px;
        border-radius: 6px;
        border: 1px solid var(--wf-slate-200);
        font-weight: 500;
    }

    /* Choice Scenarios */
    .wf-scenarios-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        max-width: 960px;
        margin: 0 auto;
    }
    .wf-scenario-card {
        background: #fff;
        border: 1px solid var(--wf-slate-200);
        border-radius: 12px;
        padding: 24px;
        text-align: left;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.02);
        transition: transform 0.2s ease;
    }
    .wf-scenario-card:hover {
        transform: translateY(-2px);
    }
    .wf-scenario-icon {
        font-size: 24px;
        color: var(--wf-secondary);
        margin-bottom: 14px;
    }
    .wf-scenario-card h3 {
        font-size: 17px;
        font-weight: 700;
        color: var(--wf-primary);
        margin-bottom: 10px;
    }
    .wf-scenario-card p {
        color: var(--wf-slate-600);
        font-size: 14px;
        line-height: 1.6;
        margin: 0;
    }

    /* Comparison Table */
    .wf-comp-table-wrap {
        max-width: 960px;
        margin: 0 auto;
        overflow-x: auto;
        border-radius: 12px;
        border: 1px solid var(--wf-slate-200);
        box-shadow: 0 4px 15px rgba(15, 23, 42, 0.02);
    }
    .wf-comp-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
    }
    .wf-comp-table th {
        background: var(--wf-primary);
        color: #fff;
        padding: 16px 20px;
        font-weight: 700;
        text-align: left;
        font-size: 15px;
    }
    .wf-comp-table td {
        padding: 16px 20px;
        border-bottom: 1px solid var(--wf-slate-100);
        color: var(--wf-slate-800);
        font-size: 14.5px;
        line-height: 1.6;
        width: 50%;
        text-align: left;
    }

    /* Common Mistakes */
    .wf-mistakes-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        max-width: 960px;
        margin: 0 auto;
    }
    .wf-mistake-card {
        background: #fdf2f2;
        border-left: 4px solid var(--wf-accent);
        border-radius: 8px;
        padding: 20px;
        text-align: left;
    }
    .wf-mistake-card h4 {
        color: #991b1b;
        margin: 0 0 8px 0;
        font-size: 16px;
        font-weight: 700;
    }
    .wf-mistake-card p {
        color: var(--wf-slate-600);
        font-size: 14px;
        line-height: 1.6;
        margin: 0;
    }

    /* Accordion FAQ */
    .wf-faq-wrap {
        max-width: 800px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .wf-faq-item {
        background: #fff;
        border: 1px solid var(--wf-slate-200);
        border-radius: 10px;
        overflow: hidden;
    }
    .wf-faq-trigger {
        width: 100%;
        padding: 20px 24px;
        background: transparent;
        border: none;
        text-align: left;
        font-size: 17px;
        font-weight: 700;
        color: var(--wf-slate-900);
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
    }
    .wf-faq-trigger span {
        font-size: 14px;
        color: var(--wf-slate-600);
        transition: transform 0.2s ease;
    }
    .wf-faq-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.25s ease-out;
        background: var(--wf-slate-50);
        border-top: 0 solid var(--wf-slate-200);
        text-align: left;
    }
    .wf-faq-content p {
        padding: 20px 24px;
        color: var(--wf-slate-600);
        line-height: 1.6;
        font-size: 15px;
        margin: 0;
    }
    .wf-faq-item.active .wf-faq-trigger span {
        transform: rotate(180deg);
    }
    .wf-faq-item.active .wf-faq-content {
        border-top-width: 1px;
    }
    
    @media (max-width: 991px) {
        .wf-calc-widget,
        .wf-city-services-grid,
        .wf-scenarios-grid,
        .wf-mistakes-grid,
        .wf-product-bento {
            grid-template-columns: 1fr;
        }
        .wf-calc-widget {
            gap: 30px;
        }
        .wf-checklist {
            grid-template-columns: 1fr;
        }
        .wf-city-services-left {
            margin-bottom: 10px;
        }
    }
</style>
HTML;

include 'includes/header.php';
include 'includes/main-header.php';
?>

<main>
    <!-- Premium Hero Section -->
    <section class="wf-local-hero">
        <div class="container">
            <div class="wf-local-hero-content">
                <h1><?php echo htmlspecialchars($page_heading, ENT_QUOTES, 'UTF-8'); ?></h1>
                <div class="wf-local-description-box" style="margin-bottom: 20px; color: #cbd5e1; font-size: 15.5px; line-height: 1.6; font-family: 'Plus Jakarta Sans', sans-serif;">
                    <?php echo waqt_sanitize_rich_html($page_description_paragraph); ?>
                </div>
                <div class="wf-local-trust-strip" aria-label="Loan application highlights">
                    <span><i class="fa-solid fa-shield-halved"></i> Consent-based checks</span>
                    <span><i class="fa-solid fa-file-invoice"></i> Clear terms before acceptance</span>
                    <span><i class="fa-solid fa-location-dot"></i> Serving <?php echo htmlspecialchars($city['name'], ENT_QUOTES, 'UTF-8'); ?> areas</span>
                </div>
                <div>
                    <a class="wf-local-btn" href="<?php echo $prod['apply_url']; ?>?city=<?php echo urlencode($city_key); ?>">Apply Now <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Bento Overview Grid -->
    <section class="wf-local-section">
        <div class="container">
            <h2>Loan Details & Structure</h2>
            <p class="wf-local-sub">Below is the general structure for a <?php echo strtolower($prod['name']); ?> for applicants in <?php echo $city['name']; ?>. Final eligibility checks and documents review apply.</p>
            <div class="wf-local-area-chips" aria-label="Popular service areas in <?php echo htmlspecialchars($city['name'], ENT_QUOTES, 'UTF-8'); ?>">
                <?php foreach (array_slice($city['areas'], 0, 6) as $area): ?>
                    <span><?php echo htmlspecialchars($area, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endforeach; ?>
            </div>

            <div class="wf-product-bento">
                <div class="wf-product-bento-card">
                    <strong>Minimum Amount</strong>
                    <span><?php echo $prod['min_amount']; ?></span>
                </div>
                <div class="wf-product-bento-card">
                    <strong>Repayment Tenure</strong>
                    <span><?php echo $prod['tenure']; ?></span>
                </div>
                <div class="wf-product-bento-card">
                    <strong>Interest Rate</strong>
                    <span><?php echo $prod['interest']; ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- EMI Calculator widget (Calculates dynamically) -->
    <section class="wf-local-section wf-local-band">
        <div class="container">
            <h2>Calculate Your EMI</h2>
            <p class="wf-local-sub">Enter your loan details below to instantly calculate your estimated monthly installment.</p>
            
            <div class="wf-calc-widget">
                <div class="wf-calc-sliders">
                    <div class="wf-slider-group">
                        <label class="wf-slider-header" for="loanAmtSlider">Loan Amount 
                            <span id="loanAmtVal"><?php echo $prod['min_amount']; ?></span>
                        </label>
                        <input type="range" class="wf-slider-input" id="loanAmtSlider" min="<?php echo (int)filter_var($prod['min_amount'], FILTER_SANITIZE_NUMBER_INT); ?>" max="<?php echo $prod['max_amount_val']; ?>" step="5000" value="<?php echo (int)filter_var($prod['min_amount'], FILTER_SANITIZE_NUMBER_INT); ?>">
                    </div>
                    
                    <div class="wf-slider-group">
                        <label class="wf-slider-header" for="interestSlider">Interest Rate (p.a.) 
                            <span id="interestVal"><?php echo $prod['default_rate']; ?>%</span>
                        </label>
                        <input type="range" class="wf-slider-input" id="interestSlider" min="9" max="36" step="0.25" value="<?php echo $prod['default_rate']; ?>">
                    </div>
                    
                    <div class="wf-slider-group">
                        <label class="wf-slider-header" for="tenureSlider">Tenure (Months) 
                            <span id="tenureVal"><?php echo $prod['default_tenure']; ?> months</span>
                        </label>
                        <input type="range" class="wf-slider-input" id="tenureSlider" min="3" max="84" step="1" value="<?php echo $prod['default_tenure']; ?>">
                    </div>
                </div>
                
                <div class="wf-calc-results">
                    <div class="wf-calc-row">
                        <strong>Estimated EMI</strong>
                        <span id="emiResult">₹0</span>
                    </div>
                    <div class="wf-calc-row">
                        <strong>Total Repayment Amount</strong>
                        <span id="totalRepayResult" style="font-size: 19px; color: var(--wf-slate-600);">₹0</span>
                    </div>
                    <a class="wf-local-btn" href="<?php echo $prod['apply_url']; ?>?city=<?php echo urlencode($city_key); ?>" style="background: var(--wf-primary); color: #fff !important; margin-top: 10px;">Apply for This Loan</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Choice Scenarios Section -->
    <?php if (isset($prod['choice_scenarios'])): ?>
    <section class="wf-local-section">
        <div class="container">
            <h2><?php echo $prod['choice_scenarios']['title']; ?></h2>
            <p class="wf-local-sub">Choosing the right loan setup is critical. Here are common scenarios where this fits perfectly:</p>
            
            <div class="wf-scenarios-grid">
                <?php foreach ($prod['choice_scenarios']['items'] as $item): ?>
                    <div class="wf-scenario-card">
                        <div class="wf-scenario-icon"><i class="fa-solid fa-circle-check"></i></div>
                        <h3><?php echo $item['q']; ?></h3>
                        <p><?php echo $item['a']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Benefits vs Risks Section -->
    <?php if (isset($prod['benefits_vs_risks'])): ?>
    <section class="wf-local-section wf-local-band">
        <div class="container">
            <h2>Benefits vs Risks</h2>
            <p class="wf-local-sub">Evaluate strategic advantages against potential financial trade-offs before proceeding.</p>
            
            <div class="wf-comp-table-wrap">
                <table class="wf-comp-table">
                    <thead>
                        <tr>
                            <th>Key Benefits</th>
                            <th>Key Risks & Considerations</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $max_rows = max(count($prod['benefits_vs_risks']['benefits']), count($prod['benefits_vs_risks']['risks']));
                        for ($i = 0; $i < $max_rows; $i++): 
                            $benefit = $prod['benefits_vs_risks']['benefits'][$i] ?? '';
                            $risk = $prod['benefits_vs_risks']['risks'][$i] ?? '';
                        ?>
                            <tr>
                                <td><?php if ($benefit): ?><i class="fa-solid fa-circle-check" style="color: #22c55e; margin-right: 8px;"></i> <?php echo $benefit; ?><?php endif; ?></td>
                                <td><?php if ($risk): ?><i class="fa-solid fa-triangle-exclamation" style="color: var(--wf-accent); margin-right: 8px;"></i> <?php echo $risk; ?><?php endif; ?></td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Callout Grid: Common Mistakes to Avoid -->
    <?php if (isset($prod['common_mistakes'])): ?>
    <section class="wf-local-section">
        <div class="container">
            <h2>Common Mistakes to Avoid</h2>
            <p class="wf-local-sub">Avoid these common missteps that can turn borrowing into a financial burden.</p>
            
            <div class="wf-mistakes-grid">
                <?php foreach ($prod['common_mistakes'] as $index => $mistake): ?>
                    <div class="wf-mistake-card">
                        <h4><?php echo ($index + 1) . ". " . $mistake['q']; ?></h4>
                        <p><?php echo $mistake['a']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Tabbed Eligibility and Documents Section -->
    <section class="wf-local-section wf-local-band">
        <div class="container">
            <h2>Eligibility & Documents Required</h2>
            <p class="wf-local-sub">Ensure you meet these criteria and have these documents handy to guarantee a fast approval cycle.</p>
            
            <div class="wf-tabs-container">
                <div class="wf-tabs-nav">
                    <button class="wf-tab-btn active" onclick="switchLocalTab(event, 'tab-eligibility')">Eligibility Criteria</button>
                    <button class="wf-tab-btn" onclick="switchLocalTab(event, 'tab-docs')">Required Documents</button>
                </div>
                
                <div class="wf-tabs-content">
                    <div class="wf-tab-pane active" id="tab-eligibility">
                        <ul class="wf-checklist">
                            <?php foreach ($prod['eligibility'] as $item): ?>
                                <li><i class="fa-solid fa-circle-check"></i> <?php echo $item; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="wf-tab-pane" id="tab-docs">
                        <ul class="wf-checklist">
                            <?php foreach ($prod['documents'] as $item): ?>
                                <li><i class="fa-solid fa-file-invoice"></i> <?php echo $item; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Timeline Stepper Section -->
    <section class="wf-local-section wf-apply-stepper-section">
        <div class="container">
            <h2>How to Apply for a <?php echo $prod['name']; ?> Online – Step-by-Step Guide</h2>
            
            <div class="wf-apply-card">
                <div class="wf-apply-timeline">
                    <div class="wf-apply-line"></div>
                    
                    <div class="wf-apply-step">
                        <div class="wf-apply-badge"><i class="fa-solid fa-check"></i></div>
                        <div class="wf-apply-text">
                            <strong>Step 1</strong> – Click 'Apply Now' and fill out the basic registration form
                        </div>
                    </div>
                    
                    <div class="wf-apply-step">
                        <div class="wf-apply-badge"><i class="fa-solid fa-check"></i></div>
                        <div class="wf-apply-text">
                            <strong>Step 2</strong> – Verify your mobile number instantly via a secure OTP code
                        </div>
                    </div>
                    
                    <div class="wf-apply-step">
                        <div class="wf-apply-badge"><i class="fa-solid fa-check"></i></div>
                        <div class="wf-apply-text">
                            <strong>Step 3</strong> – Complete secure PAN and Aadhaar identity verification
                        </div>
                    </div>
                    
                    <div class="wf-apply-step">
                        <div class="wf-apply-badge"><i class="fa-solid fa-check"></i></div>
                        <div class="wf-apply-text">
                            <strong>Step 4</strong> – Provide employment details and upload salary slips or business proof
                        </div>
                    </div>
                    
                    <div class="wf-apply-step">
                        <div class="wf-apply-badge"><i class="fa-solid fa-check"></i></div>
                        <div class="wf-apply-text">
                            <strong>Step 5</strong> – Link your active bank account for secure verification and instant disbursal reference ID
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="wf-apply-btn-wrapper">
                <a href="<?php echo $prefix; ?>apply" class="wf-apply-now-btn">Apply Now</a>
            </div>
        </div>
    </section>

    <!-- City Serving Description Block -->
    <section class="wf-local-section wf-local-band">
        <div class="container">
            <h2>Waqt Finance Services in <?php echo $city['name']; ?></h2>
            <p class="wf-local-sub">Professional NBFC lending options tailored for the <?php echo $city['name']; ?> region.</p>
            
            <div class="wf-city-services-grid">
                <div class="wf-city-services-left">
                    <p>Our digital process makes it simple to apply for <?php echo strtolower($prod['name']); ?> options from any corner of <?php echo $city['name']; ?>. Whether you are situated in <?php echo implode(', ', array_slice($city['areas'], 0, 4)); ?>, or surrounding sectors, you can upload documents and complete verification online after the required consent. Our local office footprint in Noida Sector 63 ensures backend stability and responsive customer support for applicants.</p>
                    <p>We keep the process transparent with eligibility-led loan limits, document-based processing, and clear agreement terms before acceptance. Final approval, amount, tenure, charges and repayment terms are communicated after verification and policy review.</p>
                </div>
                <div class="wf-city-services-right">
                    <div class="wf-localities-serviced">
                        <h4>Localities Serviced in <?php echo $city['name']; ?>:</h4>
                        <div class="wf-localities-grid">
                            <?php foreach ($city['areas'] as $area): ?>
                                <span><?php echo $area; ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Dynamic FAQ Accordion -->
    <section class="wf-local-section">
        <div class="container">
            <h2>Frequently Asked Questions</h2>
            <p class="wf-local-sub">Find quick answers to common questions about <?php echo strtolower($prod['name']); ?> options in <?php echo $city['name']; ?>.</p>
            
            <div class="wf-faq-wrap">
                <?php foreach ($prod['faqs'] as $index => $faq): ?>
                    <div class="wf-faq-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <button class="wf-faq-trigger" onclick="toggleLocalFaq(this)">
                            <?php echo $faq['q']; ?>
                            <span class="fa-solid fa-chevron-down"></span>
                        </button>
                        <div class="wf-faq-content" style="<?php echo $index === 0 ? 'max-height: 200px;' : ''; ?>">
                            <p><?php echo $faq['a']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="wf-faq-item">
                    <button class="wf-faq-trigger" onclick="toggleLocalFaq(this)">
                        Where is the nearest support center for Waqt Finance in <?php echo $city['name']; ?>?
                        <span class="fa-solid fa-chevron-down"></span>
                    </button>
                    <div class="wf-faq-content">
                        <p>Our corporate head office is located in Noida Sector 63. This central office serves as the processing hub for all digital loan operations in Delhi NCR, Delhi, Noida, and Gurugram. You can reach out directly via customercare@waqtfinance.com or by calling us at +91 95602 60240.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
function switchLocalTab(evt, tabId) {
    const container = evt.currentTarget.closest('.wf-tabs-container');
    const buttons = container.querySelectorAll('.wf-tab-btn');
    const panes = container.querySelectorAll('.wf-tab-pane');
    
    buttons.forEach(btn => btn.classList.remove('active'));
    panes.forEach(pane => pane.classList.remove('active'));
    
    evt.currentTarget.classList.add('active');
    container.querySelector('#' + tabId).classList.add('active');
}

function toggleLocalFaq(btn) {
    const faqItem = btn.closest('.wf-faq-item');
    const isAlreadyActive = faqItem.classList.contains('active');
    
    const container = btn.closest('.wf-faq-wrap');
    container.querySelectorAll('.wf-faq-item').forEach(item => {
        item.classList.remove('active');
        item.querySelector('.wf-faq-content').style.maxHeight = null;
    });
    
    if (!isAlreadyActive) {
        faqItem.classList.add('active');
        const content = faqItem.querySelector('.wf-faq-content');
        content.style.maxHeight = content.scrollHeight + "px";
    }
}

// Live EMI Calculation logic
document.addEventListener("DOMContentLoaded", function() {
    const loanSlider = document.getElementById("loanAmtSlider");
    const rateSlider = document.getElementById("interestSlider");
    const tenureSlider = document.getElementById("tenureSlider");
    
    const loanAmtVal = document.getElementById("loanAmtVal");
    const interestVal = document.getElementById("interestVal");
    const tenureVal = document.getElementById("tenureVal");
    
    const emiResult = document.getElementById("emiResult");
    const totalRepayResult = document.getElementById("totalRepayResult");
    
    function calculateEmi() {
        const principal = parseFloat(loanSlider.value);
        const annualRate = parseFloat(rateSlider.value);
        const months = parseInt(tenureSlider.value);
        
        loanAmtVal.innerText = "₹" + principal.toLocaleString("en-IN");
        interestVal.innerText = annualRate.toFixed(2) + "%";
        tenureVal.innerText = months + " months";
        
        // monthly rate
        const monthlyRate = annualRate / 12 / 100;
        
        let emi = 0;
        let totalRepay = 0;
        
        if (months === 1) {
            // Payday loan flat single payment
            totalRepay = principal + (principal * monthlyRate);
            emi = totalRepay;
        } else {
            // Standard compound EMI
            emi = (principal * monthlyRate * Math.pow(1 + monthlyRate, months)) / (Math.pow(1 + monthlyRate, months) - 1);
            totalRepay = emi * months;
        }
        
        if (isNaN(emi) || !isFinite(emi)) {
            emi = 0;
            totalRepay = principal;
        }
        
        emiResult.innerText = "₹" + Math.round(emi).toLocaleString("en-IN");
        totalRepayResult.innerText = "₹" + Math.round(totalRepay).toLocaleString("en-IN");
    }
    
    if (loanSlider && rateSlider && tenureSlider) {
        loanSlider.addEventListener("input", calculateEmi);
        rateSlider.addEventListener("input", calculateEmi);
        tenureSlider.addEventListener("input", calculateEmi);
        
        // initial run
        calculateEmi();
    }
});
</script>

<?php 
$footer_cta_title = "Ready to Apply for a " . $prod['name'] . " in " . $city['name'] . "?";
$footer_cta_desc = "Get started with your secure online application today and receive quick approval.";
include 'includes/footer.php'; 
?>
