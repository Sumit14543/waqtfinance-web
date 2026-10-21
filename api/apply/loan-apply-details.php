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

// 🔐 Response helper
function api_json_response($status, $message, $code = 200){
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

// ================= SESSION CHECK =================
$app_id = $_SESSION['app_id'] ?? null;

if (!$app_id) {
    api_json_response('error', 'Session expired. Restart application.', 401);
}

// ================= INPUT =================
$amount = $_POST['amount'] ?? '';
$purpose = $_POST['purpose'] ?? '';
$hasLoan = $_POST['has_running_loan'] ?? null;

// 🔥 CLEAN AMOUNT (remove comma)
$amount = str_replace(',', '', $amount);

// ================= VALIDATION =================

// Amount
if (!is_numeric($amount) || $amount <= 0) {
    api_json_response('error', 'Invalid loan amount');
}

// Limit (optional but recommended)
if ($amount < 1000 || $amount > 10000000) {
    api_json_response('error', 'Loan amount must be between ₹1,000 and ₹1 Cr');
}

// Purpose
$allowedPurposes = ['Marriage','Tuition fees','Education','Medical / Health'];

if (!in_array($purpose, $allowedPurposes)) {
    api_json_response('error', 'Invalid loan purpose');
}

// Running loan
if (!in_array($hasLoan, ['0','1',0,1], true)) {
    api_json_response('error', 'Invalid loan selection');
}

// ================= DB UPDATE =================

$conn = db();

$stmt = $conn->prepare("
    UPDATE loan_applications 
    SET loan_amount = ?, 
        loan_purpose = ?, 
        has_running_loan = ?
    WHERE application_id = ?
");

if (!$stmt) {
    api_json_response('error', 'DB prepare failed');
}

$hasLoan = (int)$hasLoan;
$amount = (float)$amount;

$stmt->bind_param("dsis", $amount, $purpose, $hasLoan, $app_id);

if (!$stmt->execute()) {
    api_json_response('error', 'Failed to save loan details');
}

loan_flow_mark_step($conn, $app_id, 'basic_details');

// ================= SUCCESS =================

api_json_response('success', 'Loan details saved successfully');
