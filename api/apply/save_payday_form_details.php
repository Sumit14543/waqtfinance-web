<?php

require_once '../config.php';
require_once __DIR__ . '/../../includes/loan_flow.php';

header('Content-Type: application/json');
require_post();
require_valid_csrf();

// CSRF VALIDATION
$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

if (!$csrf || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid CSRF token'
    ]);
    exit;
}


// 🔐 Helper function
function response($status, $message, $redirect = null)
{
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'redirect' => $redirect
    ]);
    exit;
}


// 📥 Get Data
$mobile = $_POST['mobile'] ?? '';
$email = $_POST['email'] ?? '';
$salary = str_replace(',', '', $_POST['monthly_salary'] ?? '');
$loanAmount = str_replace(',', '', $_POST['loan_amount'] ?? '');
$loanPurpose = trim((string) ($_POST['loan_purpose'] ?? ''));
$hasRunningLoan = $_POST['has_running_loan'] ?? '0';
$emp = $_POST['emp'] ?? '';
$terms = $_POST['terms'] ?? '';
$cityRaw = trim((string) ($_POST['city'] ?? ''));
$city = '';
if ($cityRaw !== '') {
    $city = ucwords(str_replace('-', ' ', $cityRaw));
}

// 🔎 VALIDATION

if (!is_numeric($salary) || $salary <= 0) {
    response('error', 'Invalid salary amount');
}

if (!is_numeric($loanAmount) || $loanAmount < 1000 || $loanAmount > 10000000) {
    response('error', 'Loan amount must be between ₹1,000 and ₹1 Cr');
}

$allowedPurposes = ['Marriage', 'Tuition fees', 'Education', 'Medical / Health'];
if (!in_array($loanPurpose, $allowedPurposes, true)) {
    response('error', 'Invalid loan purpose');
}

if (!in_array($hasRunningLoan, ['0', '1', 0, 1], true)) {
    response('error', 'Invalid running loan selection');
}

// Mobile (intl format)
if (!preg_match('/^\+?[1-9]\d{9,14}$/', $mobile)) {
    response('error', 'Invalid mobile number');
}

// Email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    response('error', 'Invalid email address');
}


// Employment
$employmentMap = [
    'salaried' => 'salaried',
    'self' => 'self',
    'self-employed' => 'self',
];

if (!isset($employmentMap[$emp])) {
    response('error', 'Invalid employment type');
}

$emp = $employmentMap[$emp];

// Terms
if (!$terms) {
    response('error', 'Please accept terms');
}

// 🆔 Generate Application ID
$app_id = 'WAQTFN-PD-' . time() . rand(100, 999);

// 💾 SAVE SESSION
$_SESSION['app_id'] = $app_id;
$_SESSION['mobile_number'] = $mobile;
$_SESSION['user_email'] = $email;
unset($_SESSION['otp_last_sent_at'], $_SESSION['otp_expires_at'], $_SESSION['otp_verified']);


// 🗄️ INSERT INTO DB
$stmt = $conn->prepare("
    INSERT INTO loan_applications
    (application_id, loan_type, mobile, email, monthly_income, employment_status, loan_amount, loan_purpose, has_running_loan, status, current_step, last_activity_at, city)
    VALUES (?, 'payday', ?, ?, ?, ?, ?, ?, ?, 'draft', 'mobile_otp', NOW(), ?)
");

if (!$stmt) {
    response('error', 'DB Prepare Failed');
}

$salary = (float) $salary;
$loanAmount = (float) $loanAmount;
$hasRunningLoan = (int) $hasRunningLoan;

$stmt->bind_param("sssdsdsis", $app_id, $mobile, $email, $salary, $emp, $loanAmount, $loanPurpose, $hasRunningLoan, $city);

if (!$stmt->execute()) {
    response('error', 'DB Insert Failed');
}

loan_flow_set_resume_cookie($app_id);
loan_flow_mark_step($conn, $app_id, 'mobile_otp');

// ✅ SUCCESS
response('success', 'Details saved successfully', 'mobile-otp.php');
