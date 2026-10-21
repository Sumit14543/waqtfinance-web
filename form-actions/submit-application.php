<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

/* =========================
   HELPERS
========================= */
function clean($data)
{
    return preg_replace('/\s+/', ' ', trim((string)$data)) ?? '';
}

function generateApplicationRef()
{
    return 'WF' . date('Ymd') . strtoupper(bin2hex(random_bytes(4)));
}

function money_to_float($value): float
{
    return (float) preg_replace('/[^\d.]/', '', (string)$value);
}

function fail(string $message, int $statusCode = 400): void
{
    http_response_code($statusCode);
    echo json_encode([
        'status' => false,
        'message' => $message
    ]);
    exit;
}

function valid_dob(?string $dob): bool
{
    if (!$dob) {
        return false;
    }

    $date = DateTime::createFromFormat('Y-m-d', $dob);
    if (!$date || $date->format('Y-m-d') !== $dob) {
        return false;
    }

    $today = new DateTimeImmutable('today');
    $age = $today->diff(DateTimeImmutable::createFromMutable($date))->y;

    return $age >= 18 && $age <= 100;
}

function option_allowed(string $value, array $allowed): bool
{
    return in_array($value, $allowed, true);
}

$allowedLoanData = [
    'Personal Loan' => [
        'tenure' => ['3 Months', '6 Months', '12 Months', '24 Months', '36 Months', '60 Months'],
        'purpose' => ['Medical Emergency', 'Education Expenses', 'Wedding', 'Travel', 'Home Renovation', 'Debt Consolidation', 'Other Personal Needs'],
        'employment' => ['Salaried Private', 'Salaried Government', 'Self-Employed', 'Freelancer', 'Retired']
    ],
    'Business Loan' => [
        'tenure' => ['6 Months', '12 Months', '24 Months', '36 Months', '60 Months', '72 Months'],
        'purpose' => ['Working Capital', 'Business Expansion', 'Machinery Purchase', 'Stock Purchase', 'Marketing', 'Cash Flow Management'],
        'employment' => ['Business Owner', 'Proprietor', 'Partner', 'Director', 'MSME Owner', 'Trader']
    ],
    'Loan Against Property' => [
        'tenure' => ['12 Months', '24 Months', '60 Months', '84 Months', '120 Months', '180 Months', '240 Months'],
        'purpose' => ['Business Expansion', 'Working Capital', 'Debt Consolidation', 'Education', 'Medical Expenses', 'Home Renovation'],
        'employment' => ['Salaried', 'Self-Employed', 'Business Owner', 'Proprietor', 'Partner', 'Rental Income']
    ],
    'Gold Loan' => [
        'tenure' => ['1 Month', '3 Months', '6 Months', '9 Months', '12 Months', '24 Months'],
        'purpose' => ['Emergency Funds', 'Business Requirement', 'Medical Expenses', 'Education Expenses', 'Household Expenses'],
        'employment' => ['Salaried', 'Self-Employed', 'Business Owner', 'Farmer', 'Homemaker', 'Retired']
    ],
    'Vehicle Loan' => [
        'tenure' => ['12 Months', '24 Months', '36 Months', '48 Months', '60 Months', '72 Months', '84 Months'],
        'purpose' => ['New Car Purchase', 'Used Car Purchase', 'Two Wheeler Purchase', 'Commercial Vehicle Purchase', 'Vehicle Upgrade'],
        'employment' => ['Salaried', 'Self-Employed', 'Business Owner', 'Commercial Driver', 'Fleet Owner', 'Farmer']
    ],
    'EDI Loan' => [
        'tenure' => ['7 Days', '15 Days', '30 Days', '45 Days', '60 Days', '90 Days'],
        'purpose' => ['Urgent Cash Requirement', 'Bill Payment', 'Medical Emergency', 'Short-term Personal Need', 'Utility Payment'],
        'employment' => ['Salaried', 'Self-Employed', 'Freelancer', 'Gig Worker', 'Student']
    ],
    'PDAY Loan' => [
        'tenure' => ['7 Days', '15 Days', '30 Days'],
        'purpose' => ['Salary Gap Management', 'Emergency Expenses', 'Rent Payment', 'Utility Bills', 'Daily Expenses'],
        'employment' => ['Salaried Monthly Income', 'Salaried Weekly Income', 'Freelancer', 'Gig Worker', 'Contract Employee']
    ]
];

/* =========================
   METHOD CHECK
========================= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Invalid request method', 405);
}

require_valid_csrf();

/* =========================
   COLLECT & SANITIZE INPUT
========================= */
// STEP 1
$loanType     = clean($_POST['loanType'] ?? '');
$loanAmount   = money_to_float($_POST['loanAmount'] ?? 0);
$loanTenure   = clean($_POST['loanTenure'] ?? '');
$loanPurpose  = clean($_POST['loanPurpose'] ?? '');

// STEP 2
$fullName = clean($_POST['fullName'] ?? '');
$email    = strtolower(clean($_POST['email'] ?? ''));
$mobile   = preg_replace('/\D/', '', clean($_POST['mobile'] ?? ''));
$dob      = !empty($_POST['dob']) ? clean($_POST['dob']) : null;
$gender   = clean($_POST['gender'] ?? '');
$pan      = strtoupper(clean($_POST['pan'] ?? ''));

$address  = clean($_POST['address'] ?? '');
$city     = clean($_POST['city'] ?? '');
$state    = clean($_POST['state'] ?? '');
$pincode  = clean($_POST['pincode'] ?? '');

// STEP 3
$employmentType = clean($_POST['employmentType'] ?? $_POST['employment_type'] ?? $_POST['emp'] ?? '');
$company        = clean($_POST['company'] ?? '');
$designation    = clean($_POST['designation'] ?? '');
$monthlyIncome  = money_to_float($_POST['monthlyIncome'] ?? 0);
$existingEmi    = money_to_float($_POST['existingEmi'] ?? 0);

// BANK
$bankName = clean($_POST['bankName'] ?? '');
$account  = clean($_POST['accountNumber'] ?? '');
$ifsc     = strtoupper(clean($_POST['ifscCode'] ?? ''));

// CONSENTS
$consents = [
    'terms'         => !empty($_POST['terms']) ? 1 : 0,
    'credit'        => !empty($_POST['credit']) ? 1 : 0,
    'communication' => !empty($_POST['communication']) ? 1 : 0
];
$consentsJson = json_encode($consents);

// DOCUMENTS (Placeholder for checklist or future uploads)
$documentsJson = json_encode(['checklist' => 'completed']);

// Generate Reference
$appRef = generateApplicationRef();

/* =========================
   VALIDATION
========================= */
if (
    !$loanType || !$loanAmount || !$loanTenure ||
    !$fullName || !$email || !$mobile ||
    !$employmentType || $employmentType === '0' || !$monthlyIncome
) {
    fail('Required fields missing');
}

if (!isset($allowedLoanData[$loanType])) {
    fail('Please select a valid loan type');
}

if ($loanAmount < 1000 || $loanAmount > 50000000) {
    fail('Please enter a valid loan amount');
}

if (!option_allowed($loanTenure, $allowedLoanData[$loanType]['tenure'])) {
    fail('Please select a valid loan tenure');
}

if ($loanPurpose !== '' && !option_allowed($loanPurpose, $allowedLoanData[$loanType]['purpose'])) {
    fail('Please select a valid loan purpose');
}

if (!preg_match("/^[A-Za-z][A-Za-z .'-]{1,98}$/", $fullName)) {
    fail('Please enter a valid full name');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
    fail('Please enter a valid email address');
}

if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
    fail('Please enter a valid 10 digit mobile number');
}

if (!valid_dob($dob)) {
    fail('Applicant must be between 18 and 100 years old');
}

if (!option_allowed($gender, ['male', 'female', 'other'])) {
    fail('Please select a valid gender');
}

if ($pan !== '' && !preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]$/', $pan)) {
    fail('Please enter a valid PAN number');
}

if ($pincode !== '' && !preg_match('/^[1-9][0-9]{5}$/', $pincode)) {
    fail('Please enter a valid pincode');
}

if (!option_allowed($employmentType, $allowedLoanData[$loanType]['employment'])) {
    fail('Please select a valid employment type');
}

if ($monthlyIncome < 1000 || $monthlyIncome > 100000000) {
    fail('Please enter a valid monthly income');
}

if ($existingEmi < 0 || $existingEmi > $monthlyIncome) {
    fail('Existing EMI cannot be greater than monthly income');
}

if ($ifsc !== '' && !preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $ifsc)) {
    fail('Please enter a valid IFSC code');
}

if (!$consents['terms'] || !$consents['credit']) {
    fail('Terms and credit bureau consent are required');
}

/* =========================
   INSERT DATA (TRANSACTION)
========================= */
$conn->begin_transaction();

try {
    $stmt = $conn->prepare("
        INSERT INTO apply_loan_applications (
            application_ref, loan_type, loan_amount, loan_tenure, loan_purpose,
            full_name, dob, gender, mobile, email, pan_number,
            address, city, state, pincode,
            employment_type, monthly_income, existing_emi,
            bank_name, account_number, ifsc_code,
            company_name, designation, documents, consents
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");

    if (!$stmt) {
        throw new Exception($conn->error);
    }

    $stmt->bind_param(
        "ssdsssssssssssssddsssssss",
        $appRef,
        $loanType,
        $loanAmount,
        $loanTenure,
        $loanPurpose,
        $fullName,
        $dob,
        $gender,
        $mobile,
        $email,
        $pan,
        $address,
        $city,
        $state,
        $pincode,
        $employmentType,
        $monthlyIncome,
        $existingEmi,
        $bankName,
        $account,
        $ifsc,
        $company,
        $designation,
        $documentsJson,
        $consentsJson
    );

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $conn->commit();

    echo json_encode([
        'status' => true,
        'message' => 'Application submitted successfully',
        'reference' => $appRef
    ]);

} catch (Exception $e) {
    $conn->rollback();
    error_log('Loan submission failed: ' . $e->getMessage());
    http_response_code(500);

    echo json_encode([
        'status' => false,
        'message' => 'Server error. Please try again later.'
    ]);
}
