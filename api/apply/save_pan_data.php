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

function fetch_uan_safely(mysqli $conn, string $appId, string $mobile): void
{
    if ($mobile === '' || !preg_match('/^[6-9]\d{9}$/', $mobile)) {
        return;
    }

    try {
        $payload = [
            'Mobile_Number' => $mobile,
            'Concent' => 'Y',
            'Concent_Text' => 'We confirm and undertake that valid end-user consent has been obtained for fetching PHONE TO UAN using MOBILE NUMBER, and that such consent remains active and unrevoked at the time of this request.',
        ];

        $curl = curl_init();
        if ($curl === false) {
            return;
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://bifrost.unifers.ai/enrich/get-phone-to-uan',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: ' . required_env('BIFROST_API_TOKEN'),
            ],
            CURLOPT_TIMEOUT => 8,
        ]);

        $response = curl_exec($curl);
        if ($response === false) {
            error_log('UAN fetch CURL error: ' . curl_error($curl));
            curl_close($curl);
            return;
        }
        curl_close($curl);

        $data = json_decode((string) $response, true);
        $uan = $data['data']['result']['uanNumber'] ?? null;
        if (!is_string($uan) || $uan === '') {
            return;
        }

        $stmt = $conn->prepare('UPDATE loan_applications SET uan_number = ? WHERE application_id = ?');
        $stmt->bind_param('ss', $uan, $appId);
        $stmt->execute();
    } catch (Throwable $e) {
        error_log('UAN fetch failed: ' . $e->getMessage());
    }
}
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

if (empty($_SESSION['pan_verified'])) {
    api_json_response('error', 'PAN not verified');
}

// ================= INPUT =================
$pan = strtoupper($_POST['pan'] ?? '');
$full_name = trim($_POST['full_name'] ?? '');
$dob = $_POST['dob'] ?? '';

$verified = $_SESSION['pan_data'] ?? [];

if ($pan !== ($verified['pan'] ?? '')) {
    api_json_response('error', 'PAN mismatch');
}

// ================= VALIDATION =================

// PAN format
if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $pan)) {
    api_json_response('error', 'Invalid PAN format');
}

// Name required (from API ideally)
if ($full_name === '' || strlen($full_name) < 3) {
    api_json_response('error', 'Invalid name');
}

// DOB required
if (!$dob || !strtotime($dob)) {
    api_json_response('error', 'Invalid date of birth');
}

// ================= DB UPDATE =================
$conn = db();

$stmt = $conn->prepare("
    UPDATE loan_applications 
    SET 
        pan_number = ?, 
        full_name = ?, 
        dob = ?
    WHERE application_id = ?
");

if (!$stmt) {
    api_json_response('error', 'DB prepare failed');
}

$stmt->bind_param("ssss", $pan, $full_name, $dob, $app_id);

if (!$stmt->execute()) {
    api_json_response('error', 'Failed to save PAN data');
}

fetch_uan_safely($conn, (string) $app_id, (string) ($_SESSION['mobile_number'] ?? ''));
loan_flow_mark_step($conn, $app_id, 'aadhaar_verification');

// ================= SUCCESS =================
api_json_response('success', 'PAN details saved successfully');
