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
function api_json_response($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// 🔐 CSRF check
$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';

if (!$csrf || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
    api_json_response(['status' => 'error', 'message' => 'Invalid CSRF token'], 403);
}

// 📥 Input
$otpInput = $_POST['otp'] ?? '';
$email = $_SESSION['user_email'] ?? null;
$mobile = $_SESSION['mobile_number'] ?? null;
$appId = (string) ($_SESSION['app_id'] ?? '');

if (!$otpInput || !preg_match('/^\d{6}$/', $otpInput)) {
    api_json_response(['status' => 'error', 'message' => 'Invalid OTP format'], 400);
}

if (!$email && !$mobile) {
    api_json_response(['status' => 'error', 'message' => 'Session expired'], 401);
}

try {

    $conn = db();

    // 🔎 Fetch latest OTP
    if ($appId !== '' && loan_flow_column_exists($conn, 'otp_verifications', 'application_id')) {
        $stmt = $conn->prepare("
            SELECT id, otp, attempts, is_verified, expires_at
            FROM otp_verifications
            WHERE application_id = ?
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->bind_param("s", $appId);
    } else {
        $stmt = $conn->prepare("
            SELECT id, otp, attempts, is_verified, expires_at
            FROM otp_verifications
            WHERE email = ? OR mobile = ?
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->bind_param("ss", $email, $mobile);
    }
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if (!$result) {
        api_json_response(['status' => 'error', 'message' => 'OTP not found'], 404);
    }

    if ((int) $result['is_verified'] === 1) {
        api_json_response(['status' => 'error', 'message' => 'OTP already verified. Please continue.'], 409);
    }

    // ⏳ Expiry check
    if (strtotime($result['expires_at']) < time()) {
        api_json_response(['status' => 'error', 'message' => 'OTP expired'], 410);
    }

    // 🚫 Attempt limit
    if ((int)$result['attempts'] >= 5) {
        api_json_response(['status' => 'error', 'message' => 'Too many attempts'], 429);
    }

    // 🔐 Verify OTP (hashed)
    if (!password_verify($otpInput, $result['otp'])) {

        // ❌ Increment attempts
        $attempts = (int) $result['attempts'] + 1;
        $update = $conn->prepare("
            UPDATE otp_verifications 
            SET attempts = ? 
            WHERE id = ?
        ");
        $update->bind_param("ii", $attempts, $result['id']);
        $update->execute();

        $remaining = max(0, 5 - $attempts);

        api_json_response([
            'status' => 'error',
            'message' => $remaining > 0
                ? 'Invalid OTP. Attempts remaining: ' . $remaining
                : 'Too many attempts. Please request a new OTP.'
        ], $remaining > 0 ? 401 : 429);
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

    // 🧹 Cleanup (optional but recommended)
    // 🔐 Session update (login success)
    $_SESSION['otp_verified'] = true;

    if (!empty($_SESSION['login_flow']) && $_SESSION['login_flow'] === true) {
        $_SESSION['is_authenticated'] = true;
        $_SESSION['user_id'] = $_SESSION['mobile_number'];
        unset($_SESSION['login_flow']);
        $redirect = 'customer-dashboard.php';
    } else {
        $redirect = 'apply-for-loan.php';
        if (!empty($_SESSION['app_id'])) {
            loan_flow_set_resume_cookie((string) $_SESSION['app_id']);
            loan_flow_mark_step($conn, (string) $_SESSION['app_id'], 'loan_requirement');
            $application = loan_flow_find_application($conn, (string) $_SESSION['app_id']);
            if ($application) {
                $redirect = loan_flow_next_url($conn, $application);
            }
        }
    }
    unset($_SESSION['otp_last_sent_at'], $_SESSION['otp_expires_at']);

    // ✅ SUCCESS
    api_json_response([
        'status' => 'success',
        'message' => 'OTP verified successfully',
        'redirect' => $redirect
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());
    api_json_response([
        'status' => 'error',
        'message' => 'Server error'
    ], 500);
}
