<?php
declare(strict_types=1);

require_once '../config.php';
require_once __DIR__ . '/../../includes/loan_flow.php';

if (session_status() === PHP_SESSION_NONE) {
    start_secure_session();
}

$uniqueId = $_GET['uniqueId'] ?? null;
$app_id = $_SESSION['app_id'] ?? null;

if (!$app_id && $uniqueId && loan_flow_column_exists($conn, 'loan_applications', 'aadhaar_unique_id')) {
    $stmt = $conn->prepare('SELECT application_id, mobile, email FROM loan_applications WHERE aadhaar_unique_id = ? LIMIT 1');
    $stmt->bind_param('s', $uniqueId);
    $stmt->execute();
    $application = $stmt->get_result()->fetch_assoc();
    if ($application) {
        $app_id = $application['application_id'];
        $_SESSION['app_id'] = $app_id;
        $_SESSION['mobile_number'] = $application['mobile'] ?? '';
        if (!empty($application['email'])) {
            $_SESSION['user_email'] = $application['email'];
        }
        loan_flow_set_resume_cookie((string) $app_id);
    }
}

if (!$app_id || !$uniqueId) {
    $_SESSION['aadhaar_error'] = 'Aadhaar verification session expired. Please try again.';
    header('Location: /aadhar-varification.php');
    exit;
}

// ================= SAVE uniqueId =================
loan_flow_update_application($conn, (string) $app_id, [
    'aadhaar_unique_id' => (string) $uniqueId,
]);

// ================= FETCH FINAL KYC =================

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => "https://bifrost.unifers.ai/enrich/get-aadhaar-data",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        "uniqueId" => $uniqueId
    ]),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        'Authorization: ' . required_env('BIFROST_API_TOKEN')
    ]
]);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    error_log('Aadhaar data CURL error: ' . curl_error($ch));
    curl_close($ch);
    $_SESSION['aadhaar_error'] = 'Aadhaar verification service is temporarily unavailable.';
    header('Location: /aadhar-varification.php');
    exit;
}

curl_close($ch);

$data = json_decode($response, true);

if (!$data || ($data['error'] ?? true)) {
    $_SESSION['aadhaar_error'] = 'Aadhaar verification failed. Please try again.';
    header('Location: /aadhar-varification.php');
    exit;
}

// ================= SAVE VERIFIED STATUS =================

loan_flow_update_application($conn, (string) $app_id, [
    'aadhaar_verified' => 1,
    'current_step' => 'work_details',
    'last_activity_at' => date('Y-m-d H:i:s'),
]);

// ================= CLEANUP =================
unset($_SESSION['aadhaar_unique_id']);

// ================= REDIRECT =================
header("Location: /company-details.php");
exit;
