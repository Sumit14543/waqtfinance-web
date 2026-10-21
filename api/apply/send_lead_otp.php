<?php
declare(strict_types=1);

require_once '../config.php';

header('Content-Type: application/json; charset=utf-8');
require_post();
require_valid_csrf();

$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
if (!$csrf || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['status' => 'error', 'message' => 'Security token expired. Please refresh and try again.']);
    http_response_code(419);
    exit;
}

$mobileRaw = trim((string) ($_POST['mobile'] ?? ''));
$mobile = preg_replace('/\D/', '', $mobileRaw);

if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid 10-digit mobile number.']);
    exit;
}

$isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'], true) || (env_value('APP_ENV') === 'local');

try {
    $conn = db();

    // Check cooldown (60 seconds)
    $lastSent = (int) ($_SESSION['lead_otp_sent_' . $mobile] ?? 0);
    $diff = time() - $lastSent;
    if ($lastSent > 0 && $diff < 60) {
        $remaining = 60 - $diff;
        echo json_encode([
            'status' => 'error',
            'message' => "Please wait {$remaining} seconds before requesting a new OTP.",
            'retry_after' => $remaining
        ]);
        exit;
    }

    // Generate 6-digit OTP
    $otp = (string) random_int(100000, 999999);
    $otpHash = password_hash($otp, PASSWORD_DEFAULT);
    $expiresAt = date('Y-m-d H:i:s', time() + 300); // 5 mins validity
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    // Delete unverified OTPs for this number
    try {
        $del = $conn->prepare("DELETE FROM otp_verifications WHERE mobile = ? AND is_verified = 0");
        if ($del) {
            $del->bind_param("s", $mobile);
            $del->execute();
            $del->close();
        }
    } catch (Throwable $e) {}

    // Save OTP record
    try {
        $stmt = $conn->prepare("
            INSERT INTO otp_verifications (mobile, otp, attempts, is_verified, expires_at, ip_address)
            VALUES (?, ?, 0, 0, ?, ?)
        ");
        if ($stmt) {
            $stmt->bind_param("ssss", $mobile, $otpHash, $expiresAt, $clientIp);
            $stmt->execute();
            $stmt->close();
        }
    } catch (Throwable $e) {
        error_log('OTP insert failed: ' . $e->getMessage());
    }

    // Save in session as fallback
    $_SESSION['lead_otp_code_' . $mobile] = $otp;
    $_SESSION['lead_otp_sent_' . $mobile] = time();
    $_SESSION['lead_current_mobile'] = $mobile;

    // Dispatch OTP via WhatsApp / SMS if available
    $sentReal = false;
    if (function_exists('sendWhatsAppOTP')) {
        try {
            $resp = sendWhatsAppOTP($mobile, $otp);
            $sentReal = true;
        } catch (Throwable $e) {
            error_log('WhatsApp OTP send error: ' . $e->getMessage());
        }
    }

    $response = [
        'status' => 'success',
        'message' => 'OTP sent successfully to +91 ' . $mobile,
        'retry_after' => 60
    ];

    // For local testing convenience, expose debug_otp
    if ($isLocal) {
        $response['debug_otp'] = $otp;
    }

    echo json_encode($response);

} catch (Throwable $e) {
    error_log('Send lead OTP error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to send OTP. Please try again.']);
}
