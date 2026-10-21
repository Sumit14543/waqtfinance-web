<?php
declare(strict_types=1);

require_once '../config.php';
require_once __DIR__ . '/../../includes/loan_flow.php';
require_once __DIR__ . '/../../includes/crm_integration.php';
require_once __DIR__ . '/../../includes/email_helper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_post();
require_valid_csrf();

// ================= RESPONSE =================
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
    api_json_response('error', 'Session expired', 401);
}

// ================= CONFIG =================
$allowedTypes = [
    'application/pdf' => 'pdf',
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];
$maxSize = 2 * 1024 * 1024; // 2MB

// ================= HELPER =================
function uploadFile($file, $prefix, $relativeDir, $allowedTypes, $maxSize)
{
    return ['success' => waqt_save_private_uploaded_file($file, $relativeDir, $prefix, $allowedTypes, $maxSize)];
}

// ================= VALIDATE FILES =================

// Salary Slip
if (!isset($_FILES['current_salary_slip'])) {
    api_json_response('error', 'Salary slip required');
}

$salary = uploadFile(
    $_FILES['current_salary_slip'],
    'salary',
    'loan_docs/' . $app_id,
    $allowedTypes,
    $maxSize
);

if (isset($salary['error'])) {
    api_json_response('error', $salary['error']);
}

// Selfie
if (!isset($_FILES['selfie_image'])) {
    api_json_response('error', 'Selfie required');
}

$selfie = uploadFile(
    $_FILES['selfie_image'],
    'selfie',
    'loan_docs/' . $app_id,
    $allowedTypes,
    $maxSize
);

if (isset($selfie['error'])) {
    api_json_response('error', $selfie['error']);
}

// ================= DB UPDATE =================
$conn = db();

$stmt = $conn->prepare("
    UPDATE loan_applications 
    SET 
        salary_slip_current = ?, 
        selfie_image = ?,
        status = 'submitted'
    WHERE application_id = ?
");

if (!$stmt) {
    api_json_response('error', 'DB prepare failed');
}

$stmt->bind_param(
    "sss",
    $salary['success'],
    $selfie['success'],
    $app_id
);

if (!$stmt->execute()) {
    api_json_response('error', 'Failed to save documents');
}

$_SESSION['loan_app_id_final'] = $app_id;
loan_flow_update_application($conn, $app_id, [
    'current_step' => 'submitted',
    'submitted_at' => date('Y-m-d H:i:s'),
    'last_activity_at' => date('Y-m-d H:i:s'),
]);
loan_flow_clear_resume_cookie();

// Push lead to CRM API
try {
    push_payday_lead_to_crm($conn, $app_id);
} catch (Throwable $pushErr) {
    error_log('CRM Push Fail: ' . $pushErr->getMessage());
}

// Send email notification to care email
try {
    $displayId = 'WAQT-PD-' . $app_id; // For payday, we display it simply
    // Fetch serial increment ID for payday to make a nice displayId if possible
    $idStmt = $conn->prepare("SELECT id FROM loan_applications WHERE application_id = ? LIMIT 1");
    if ($idStmt) {
        $idStmt->bind_param("s", $app_id);
        $idStmt->execute();
        $idRow = $idStmt->get_result()->fetch_assoc();
        $idStmt->close();
        if ($idRow) {
            $displayId = 'WAQT-PD-' . str_pad((string)$idRow['id'], 6, '0', STR_PAD_LEFT);
        }
    }
    send_loan_application_notification($conn, 'payday', $app_id, $displayId);
} catch (Throwable $mailErr) {
    error_log('Email Notification Fail: ' . $mailErr->getMessage());
}

// ================= SUCCESS =================
api_json_response('success', 'Documents uploaded successfully');
