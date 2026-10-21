<?php

ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once '../config.php';

header('Content-Type: application/json');

$conn = db();


// ======================================
// API SECURITY
// ======================================

$api_key = (string) ($_SERVER['HTTP_X_API_KEY'] ?? '');
$expectedApiKey = defined('SYNC_API_KEY') ? (string) SYNC_API_KEY : '';

if ($expectedApiKey === '' || !hash_equals($expectedApiKey, $api_key)) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized'
    ]);

    exit;
}


// ======================================
// GET JSON DATA
// ======================================

$data = json_decode(
    file_get_contents("php://input"),
    true
);

if (!$data) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON'
    ]);

    exit;
}


// ======================================
// REQUIRED FIELD
// ======================================

$application_id =
    $data['application_id'] ?? '';

if (!$application_id) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Application ID Missing'
    ]);

    exit;
}


// ======================================
// DUPLICATE CHECK
// ======================================

$check = $conn->prepare("
    SELECT id
    FROM loan_applications
    WHERE external_application_id = ?
");

$check->bind_param(
    "s",
    $application_id
);

$check->execute();

$existing = $check
    ->get_result()
    ->fetch_assoc();

if ($existing) {

    echo json_encode([
        'status' => 'success',
        'message' => 'Already Synced'
    ]);

    exit;
}


// ======================================
// FIELD MAPPING
// ======================================

$source = "waqtmoney";

$selfie_image =
    $data['selfie_photo'] ?? null;

$submitted_at =
    $data['submit_at'] ?? null;


// ======================================
// SAFE DATA
// ======================================

function value($key, $default = null)
{
    global $data;

    return $data[$key] ?? $default;
}

$incomingAadhaar = trim((string) value('aadhaar_number', ''));
$incomingAadhaarPlain = waqt_sensitive_plaintext($incomingAadhaar);
$incomingAadhaarDigits = preg_replace('/\D+/', '', $incomingAadhaarPlain) ?? '';
$storedAadhaarNumber = strlen($incomingAadhaarDigits) === 12
    ? waqt_encrypt_sensitive_data($incomingAadhaarDigits)
    : (str_starts_with($incomingAadhaar, 'enc:v1:') ? $incomingAadhaar : '');
$storedAadhaarMasked = (string) (value('aadhaar_masked') ?? '');
if ($storedAadhaarMasked === '' && strlen($incomingAadhaarDigits) === 12) {
    $storedAadhaarMasked = 'XXXXXXXX' . substr($incomingAadhaarDigits, -4);
}


// ======================================
// INSERT QUERY
// ======================================

$query = "
INSERT INTO loan_applications
(
    external_application_id,
    application_id,
    loan_type,
    full_name,
    mobile,
    email,
    dob,
    pan_number,
    uan_number,
    aadhaar_number,
    aadhaar_verified,
    aadhaar_unique_id,
    aadhaar_masked,
    cibil_report_url,
    employment_status,
    monthly_income,
    income_received_in,
    city,
    pincode,
    loan_amount,
    loan_purpose,
    has_running_loan,
    company_name,
    designation,
    office_email,
    salary_day,
    office_address,
    education,
    experience_years,
    bank_name,
    branch_name,
    account_holder,
    account_number,
    ifsc_code,
    salary_slip_current,
    status,
    current_step,
    last_activity_at,

    reference1_name,
    reference1_mobile,
    reference1_relation,

    reference2_name,
    reference2_mobile,
    reference2_relation,

    video_kyc,
    office_pincode,

    selfie_image,
    submitted_at,

    sync_status,
    synced_at
)
VALUES
(
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
    ?, ?, ?, ?, ?, ?, ?, ?,
    ?, ?, ?,
    ?, ?, ?,
    ?, ?,
    ?, ?,
    'synced',
    NOW()
)
";


$stmt = $conn->prepare($query);

if (!$stmt) {
    error_log('WaqtMoney sync prepare failed: ' . $conn->error);

    echo json_encode([
        'status' => 'error',
        'message' => 'Server error'
    ]);

    exit;
}


// ======================================
// BIND PARAMS
// ======================================

$stmt->bind_param(

    "ssssssssssssssssssssssssssssssssssssssssssssssss",

    $application_id,
    value('application_id'),
    value('loan_type'),
    value('full_name'),
    value('mobile'),
    value('email'),
    value('dob'),
    value('pan_number'),
    value('uan_number'),
    $storedAadhaarNumber,
    value('aadhaar_verified'),
    value('aadhaar_unique_id'),
    $storedAadhaarMasked,
    value('cibil_report_url'),
    value('employment_status'),
    value('monthly_income'),
    value('income_received_in'),
    value('city'),
    value('pincode'),
    value('loan_amount'),

    value('loan_purpose'),
    value('has_running_loan'),
    value('company_name'),
    value('designation'),
    value('office_email'),
    value('salary_day'),
    value('office_address'),
    value('education'),
    value('experience_years'),
    value('bank_name'),

    value('branch_name'),
    value('account_holder'),
    value('account_number'),
    value('ifsc_code'),
    value('salary_slip_current'),
    value('status', 'submitted'),
    value('current_step'),
    value('last_activity_at'),

    value('reference1_name'),
    value('reference1_mobile'),
    value('reference1_relation'),

    value('reference2_name'),
    value('reference2_mobile'),
    value('reference2_relation'),

    value('video_kyc'),
    value('office_pincode'),

    $selfie_image,
    $submitted_at,
);


// ======================================
// EXECUTE
// ======================================

if ($stmt->execute()) {

    echo json_encode([
        'status' => 'success',
        'message' => 'Lead Synced Successfully'
    ]);

} else {
    error_log('WaqtMoney sync execute failed: ' . $stmt->error);

    echo json_encode([
        'status' => 'error',
        'message' => 'Server error'
    ]);
}
?>
