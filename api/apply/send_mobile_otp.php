<?php
declare(strict_types=1);

require_once '../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔐 Helper Response
function api_json_response($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function client_ip(): string
{
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        $value = $_SERVER[$key] ?? '';
        if ($value === '') continue;

        $ip = trim(explode(',', $value)[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }
    return '';
}

header('Content-Type: application/json');
require_post();
require_valid_csrf();

// 🔐 CSRF VALIDATION
$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';

if (!$csrf || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
    api_json_response(['status' => 'error', 'message' => 'Invalid CSRF token'], 403);
}

// 📥 Session Data
$mobile = $_SESSION['mobile_number'] ?? null;
$firstName = $_SESSION['Full_Name'] ?? 'User';
$ipAddress = client_ip();

if (!$mobile) {
    api_json_response(['status' => 'error', 'message' => 'Session expired'], 401);
}

// ✅ Mobile validation
if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
    api_json_response(['status' => 'error', 'message' => 'Invalid mobile number'], 400);
}

try {

    $conn = db();

    try {
        // 1. IP Check: Max 5 requests per hour from same IP
        $ipRateStmt = $conn->prepare("
            SELECT COUNT(*) AS count
            FROM otp_requests
            WHERE ip_address = ?
              AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $ipRateStmt->bind_param("s", $ipAddress);
        $ipRateStmt->execute();
        $ipCount = (int) ($ipRateStmt->get_result()->fetch_assoc()['count'] ?? 0);
        $ipRateStmt->close();

        if ($ipCount >= 5) {
            api_json_response([
                'status' => 'error',
                'message' => 'Too many OTP requests from this connection. Please try again after 1 hour.'
            ], 429);
        }

        // 2. Mobile Check: Max 4 requests per 12 hours from same mobile number
        $mobileRateStmt = $conn->prepare("
            SELECT COUNT(*) AS count
            FROM otp_requests
            WHERE mobile_number = ?
              AND created_at >= DATE_SUB(NOW(), INTERVAL 12 HOUR)
        ");
        $mobileRateStmt->bind_param("s", $mobile);
        $mobileRateStmt->execute();
        $mobileCount = (int) ($mobileRateStmt->get_result()->fetch_assoc()['count'] ?? 0);
        $mobileRateStmt->close();

        if ($mobileCount >= 4) {
            api_json_response([
                'status' => 'error',
                'message' => 'Too many verification attempts on this number. Please try again after 12 hours.'
            ], 429);
        }

        // 3. Log this request in otp_requests
        $logStmt = $conn->prepare("
            INSERT INTO otp_requests (mobile_number, ip_address)
            VALUES (?, ?)
        ");
        $logStmt->bind_param("ss", $mobile, $ipAddress);
        $logStmt->execute();
        $logStmt->close();

    } catch (Throwable $rateError) {
        error_log('OTP rate-limit check failed: ' . $rateError->getMessage());
    }

    // ================= COOLDOWN =================
    $lastSentAt = (int) ($_SESSION['otp_last_sent_at'] ?? 0);
    $expiresAtTs = (int) ($_SESSION['otp_expires_at'] ?? 0);
    $remaining = $expiresAtTs > 0 ? $expiresAtTs - time() : 60 - (time() - $lastSentAt);

    if ($lastSentAt > 0 && $remaining > 0) {
        api_json_response([
            'status' => 'success',
            'message' => 'OTP already sent. Please wait.',
            'retry_after' => $remaining
        ]);
    }

    // ================= GENERATE OTP =================
    $otp = (string) random_int(100000, 999999);
    $otpHash = password_hash($otp, PASSWORD_DEFAULT);

    $expiresAtUnix = time() + 60;
    $expiresAt = date('Y-m-d H:i:s', $expiresAtUnix);

    // 🧹 Delete old OTP
    $del = $conn->prepare("DELETE FROM otp_verifications WHERE mobile = ? AND is_verified = 0");
    $del->bind_param("s", $mobile);
    $del->execute();

    // ================= SAVE =================
    $insert = $conn->prepare("
        INSERT INTO otp_verifications (mobile, otp, attempts, is_verified, expires_at, ip_address) 
        VALUES (?, ?, 0, 0, ?, ?)
    ");
    $insert->bind_param("ssss", $mobile, $otpHash, $expiresAt, $ipAddress);
    $insert->execute();

    // ================= SEND OTP =================
    $waData = null;

    if (function_exists('sendWhatsAppOTP')) {
        $waResponse = sendWhatsAppOTP($mobile, $otp);
        if (is_string($waResponse)) {
            $waData = json_decode($waResponse, true);
        }
    }

    // 👉 fallback SMS (optional)
    if (function_exists('sendSMS')) {
        sendSMS($mobile, "Your OTP is $otp");
    }

    $_SESSION['otp_last_sent_at'] = time();
    $_SESSION['otp_expires_at'] = $expiresAtUnix;

    api_json_response([
        'status' => 'success',
        'message' => 'OTP sent successfully on mobile',
        'retry_after' => 60
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());

    api_json_response([
        'status' => 'error',
        'message' => 'Failed to send OTP'
    ], 500);
}
