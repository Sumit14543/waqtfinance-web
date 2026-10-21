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
$bank = trim($_POST['bank_name'] ?? '');
$branch = trim($_POST['branch_name'] ?? '');
$holder = trim($_POST['account_holder'] ?? '');
$account = preg_replace('/\D/', '', $_POST['account_number'] ?? '');
$ifsc = strtoupper(trim($_POST['ifsc_code'] ?? ''));

// ================= VALIDATION =================

// Bank name
if ($bank === '' || strlen($bank) < 2) {
    api_json_response('error', 'Invalid bank name');
}
if ($branch !== '' && strlen($branch) < 2) {
    api_json_response('error', 'Invalid branch name');
}

// Account holder
if ($holder === '' || strlen($holder) < 3) {
    api_json_response('error', 'Invalid account holder name');
}

// Account number
if (!ctype_digit($account) || strlen($account) < 9 || strlen($account) > 18) {
    api_json_response('error', 'Invalid account number');
}

// IFSC validation
if (!preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $ifsc)) {
    api_json_response('error', 'Invalid IFSC code');
}

$conn = db();
loan_flow_update_application($conn, $app_id, [
    'bank_name' => $bank,
    'branch_name' => $branch,
    'account_holder' => $holder,
    'account_number' => $account,
    'ifsc_code' => $ifsc,
    'current_step' => 'references',
    'last_activity_at' => date('Y-m-d H:i:s'),
]);

// ================= SUCCESS =================
api_json_response('success', 'Bank details saved successfully');
