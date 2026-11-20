<?php
declare(strict_types=1);

/*
 * Default content for product + city landing pages (e.g. /personal-loan-in-delhi).
 * These are only defaults: the admin panel (Location CMS > Product content) stores edits in the
 * location_product_templates table, which take precedence. Per-city overrides live in location_pages.product_content.
 */

return [
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
