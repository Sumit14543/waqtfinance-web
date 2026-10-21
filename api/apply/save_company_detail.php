<?php
declare(strict_types=1);

require_once '../config.php';
require_once __DIR__ . '/../../includes/loan_flow.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_post();
require_valid_csrf();

// 🔐 Helper
function api_json_response($status, $message, $code = 200)
{
    http_response_code($code);
    echo json_encode([
        'status' => $status,
        'message' => $message
    ]);
    exit;
}

// ================= CSRF =================
$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

if (!$csrf || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
    api_json_response('error', 'Invalid CSRF token', 403);
}

// ================= SESSION =================
$app_id = $_SESSION['app_id'] ?? null;

if (!$app_id) {
    api_json_response('error', 'Session expired. Restart application.', 401);
}

// ================= INPUT =================
$company = trim($_POST['company_name'] ?? '');
$designation = trim($_POST['designation'] ?? '');
$email = trim($_POST['office_email'] ?? '');
$salaryDay = $_POST['salary_day'] ?? '';
$address = trim($_POST['office_address'] ?? '');
$education = $_POST['education'] ?? '';
$experience = $_POST['experience'] ?? '';

// ================= VALIDATION =================

// Company
if ($company === '' || strlen($company) < 2) {
    api_json_response('error', 'Invalid company name');
}

// Designation
if ($designation === '' || strlen($designation) < 2) {
    api_json_response('error', 'Invalid designation');
}

// Email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    api_json_response('error', 'Invalid email');
}

// Salary day
if (!$salaryDay || !is_numeric($salaryDay) || $salaryDay < 1 || $salaryDay > 31) {
    api_json_response('error', 'Invalid salary day');
}

// Address
if ($address === '' || strlen($address) < 5) {
    api_json_response('error', 'Invalid office address');
}

// Education
$allowedEducation = ['10th', '12th', 'Graduate', 'Post Graduate'];
if (!in_array($education, $allowedEducation)) {
    api_json_response('error', 'Invalid education');
}

// Experience
if (!is_numeric($experience) || $experience < 0 || $experience > 50) {
    api_json_response('error', 'Invalid experience');
}



// ================= DB UPDATE =================
$conn = db();

$stmt = $conn->prepare("
    UPDATE loan_applications 
    SET 
        company_name = ?, 
        designation = ?, 
        office_email = ?, 
        salary_day = ?, 
        office_address = ?, 
        education = ?, 
        experience_years = ?
    WHERE application_id = ?
");

if (!$stmt) {
    api_json_response('error', 'DB prepare failed');
}

$experience = (int) $experience;

$stmt->bind_param(
    "sssissis",
    $company,
    $designation,
    $email,
    $salaryDay,
    $address,
    $education,
    $experience,
    $app_id
);

if (!$stmt->execute()) {
    api_json_response('error', 'Failed to save company details');
}

loan_flow_mark_step($conn, $app_id, 'bank_details');

// ================= SUCCESS =================
api_json_response('success', 'Company details saved successfully');
