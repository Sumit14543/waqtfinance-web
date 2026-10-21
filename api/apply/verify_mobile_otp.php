<?php
declare(strict_types=1);

require_once '../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_post();
require_valid_csrf();

function api_json_response($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// 🔐 CSRF
$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
if (!$csrf || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
    api_json_response(['status' => 'error', 'message' => 'Invalid CSRF token'], 403);
}

// 📥 Input
$otpInput = $_POST['otp'] ?? '';
$mobile = $_SESSION['mobile_number'] ?? null;

if (!$otpInput || !preg_match('/^\d{6}$/', $otpInput)) {
    api_json_response(['status' => 'error', 'message' => 'Invalid OTP format'], 400);
}

if (!$mobile) {
    api_json_response(['status' => 'error', 'message' => 'Session expired'], 401);
}

try {
    $conn = db();

    // 🔎 Fetch latest OTP (ONLY MOBILE)
    $stmt = $conn->prepare("
        SELECT id, otp, attempts, is_verified, expires_at 
        FROM otp_verifications 
        WHERE mobile = ?
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->bind_param("s", $mobile);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if (!$result) {
        api_json_response(['status' => 'error', 'message' => 'OTP not found'], 404);
    }

    if ((int)$result['is_verified'] === 1) {
        api_json_response(['status' => 'error', 'message' => 'Already verified'], 409);
    }

    if (strtotime($result['expires_at']) < time()) {
        api_json_response(['status' => 'error', 'message' => 'OTP expired'], 410);
    }

    if ((int)$result['attempts'] >= 5) {
        api_json_response(['status' => 'error', 'message' => 'Too many attempts'], 429);
    }

    // 🔐 Verify
    if (!password_verify($otpInput, $result['otp'])) {

        $attempts = (int)$result['attempts'] + 1;

        $update = $conn->prepare("
            UPDATE otp_verifications 
            SET attempts = ? 
            WHERE id = ?
        ");
        $update->bind_param("ii", $attempts, $result['id']);
        $update->execute();

        api_json_response([
            'status' => 'error',
            'message' => 'Invalid OTP. Attempts left: ' . (5 - $attempts)
        ], 401);
    }

    // ✅ Mark verified
    $verify = $conn->prepare("
        UPDATE otp_verifications 
        SET is_verified = 1 
        WHERE id = ?
    ");
    $verify->bind_param("i", $result['id']);
    $verify->execute();

    session_regenerate_id(true);

    $_SESSION['otp_verified'] = true;
    unset($_SESSION['otp_last_sent_at'], $_SESSION['otp_expires_at']);

    api_json_response([
        'status' => 'success',
        'message' => 'OTP verified successfully'
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());

    api_json_response([
        'status' => 'error',
        'message' => 'Server error'
    ], 500);
}
