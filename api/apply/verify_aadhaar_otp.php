<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');
require_post();
require_valid_csrf();

$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
if (!$csrf || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['status' => 'error', 'message' => 'Security token expired. Please refresh and try again.']);
    http_response_code(419);
    exit;
}

$otpSubmitted = trim((string) ($_POST['otp'] ?? ''));

if (!preg_match('/^\d{6}$/', $otpSubmitted)) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid 6-digit Aadhaar OTP.']);
    exit;
}

$storedHash = $_SESSION['aadhaar_otp_hash'] ?? '';
$storedTime = (int) ($_SESSION['aadhaar_otp_time'] ?? 0);
$aadhaar = $_SESSION['aadhaar_otp_number'] ?? '';
$last4 = $_SESSION['aadhaar_otp_last4'] ?? '';

if (empty($storedHash) || (time() - $storedTime > 600)) {
    echo json_encode(['status' => 'error', 'message' => 'Aadhaar OTP expired. Please request a new OTP.']);
    exit;
}

$submittedHash = hash('sha256', $otpSubmitted);
if (!hash_equals($storedHash, $submittedHash)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Aadhaar OTP code. Please enter the correct code.']);
    exit;
}

// Mark verified in session
$_SESSION['lead_aadhaar_verified'] = true;
$_SESSION['lead_aadhaar_masked'] = 'XXXXXXXX' . $last4;
$_SESSION['lead_aadhaar_last4'] = $last4;

// Cleanup OTP
unset($_SESSION['aadhaar_otp_hash'], $_SESSION['aadhaar_otp_time']);

echo json_encode([
    'status' => 'success',
    'message' => 'Aadhaar verified successfully via UIDAI OTP',
    'masked_aadhaar' => 'XXXX XXXX ' . $last4,
    'last4' => $last4,
    'verified_at' => date('Y-m-d H:i:s')
]);
