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
$aadhaar = preg_replace('/\D/', '', $_POST['aadhaar'] ?? '');

// ================= VALIDATION =================

// length check
if (strlen($aadhaar) !== 12) {
    api_json_response('error', 'Invalid Aadhaar number');
}

// basic numeric check
if (!ctype_digit($aadhaar)) {
    api_json_response('error', 'Invalid Aadhaar format');
}

// ================= MASKING =================
$masked = 'XXXXXXXX' . substr($aadhaar, -4);

$encryptedAadhaar = waqt_encrypt_sensitive_data($aadhaar);

// ================= DB UPDATE =================
$conn = db();

$stmt = $conn->prepare("
    UPDATE loan_applications 
    SET 
        aadhaar_number = ?, 
        aadhaar_masked = ?
    WHERE application_id = ?
");

if (!$stmt) {
    api_json_response('error', 'DB prepare failed');
}

$stmt->bind_param("sss", $encryptedAadhaar, $masked, $app_id);

if (!$stmt->execute()) {
    api_json_response('error', 'Failed to save Aadhaar');
}


// ================= CALL AADHAAR API =================

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => "https://bifrost.unifers.ai/enrich/get-aadhaar-verification",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        "referenceId" => $app_id
    ]),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: ". required_env('BIFROST_API_TOKEN')
    ]
]);

$response = curl_exec($ch);
if ($response === false) {
    error_log('Aadhaar init CURL error: ' . curl_error($ch));
    curl_close($ch);
    api_json_response('error', 'Aadhaar verification service is temporarily unavailable');
}
curl_close($ch);

$data = json_decode($response, true);

if (!$data || ($data['error'] ?? true) || empty($data['data']['uniqueId']) || empty($data['data']['authorizationUrl'])) {
    api_json_response('error', 'Aadhaar API failed');
}

// 🔥 Save uniqueId in session
$_SESSION['aadhaar_unique_id'] = $data['data']['uniqueId'];
loan_flow_update_application($conn, $app_id, [
    'aadhaar_unique_id' => (string) $data['data']['uniqueId'],
    'current_step' => 'aadhaar_callback',
    'last_activity_at' => date('Y-m-d H:i:s'),
]);

// 🔥 Return redirect URL
api_json_response('success', $data['data']['authorizationUrl']);
