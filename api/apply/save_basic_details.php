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
$pincode = $_POST['pincode'] ?? '';
$city = trim($_POST['city'] ?? '');
$income = $_POST['income'] ?? '';
$employment = $_POST['employment_type'] ?? '';
$incomeType = $_POST['income_received_in'] ?? '';

// 🔥 CLEAN DATA
$pincode = preg_replace('/[^0-9]/', '', $pincode);
$income = str_replace(',', '', $income);

// ================= VALIDATION =================

// Pincode
if (strlen($pincode) !== 6) {
    api_json_response('error', 'Invalid pincode');
}

// City
if ($city === '' || strlen($city) < 2) {
    api_json_response('error', 'Invalid city');
}

// Income
if (!is_numeric($income) || $income <= 0) {
    api_json_response('error', 'Invalid monthly income');
}

// Employment
if (!in_array($employment, ['salaried', 'self'])) {
    api_json_response('error', 'Invalid employment type');
}

// Income Type
if (!in_array($incomeType, ['account', 'cash', 'cheque'])) {
    api_json_response('error', 'Invalid income type');
}

// ================= DB UPDATE =================
$conn = db();

$stmt = $conn->prepare("
    UPDATE loan_applications 
    SET 
        pincode = ?, 
        city = ?, 
        monthly_income = ?, 
        employment_status = ?, 
        income_received_in = ?
    WHERE application_id = ?
");

if (!$stmt) {
    api_json_response('error', 'DB prepare failed');
}

$income = (float)$income;

$stmt->bind_param("ssdsss", $pincode, $city, $income, $employment, $incomeType, $app_id);

if (!$stmt->execute()) {
    api_json_response('error', 'Failed to save details');
}

loan_flow_mark_step($conn, $app_id, 'pan_verification');

// ================= SUCCESS =================
api_json_response('success', 'Basic details saved successfully');
