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

// 🔐 CSRF VALIDATION
$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';

if (!$csrf || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}

$mobile = preg_replace('/\D/', '', (string) ($_POST['mobile'] ?? ''));

// ✅ Validate Mobile format
if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid 10-digit mobile number.']);
    exit;
}

try {
    $conn = db();

    // 🔎 Check if there is an application for this mobile number
    $stmt = $conn->prepare("SELECT application_id FROM loan_applications WHERE mobile = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("s", $mobile);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if (!$result) {
        echo json_encode(['status' => 'error', 'message' => 'We could not start verification for this request. Please check the details or apply first.']);
        exit;
    }

    // 💾 Set login flow sessions
    $_SESSION['mobile_number'] = $mobile;
    $_SESSION['app_id'] = $result['application_id'];
    $_SESSION['login_flow'] = true;
    
    // Clear old OTP session data to force a new send
    unset($_SESSION['otp_last_sent_at'], $_SESSION['otp_expires_at'], $_SESSION['otp_verified']);

    echo json_encode(['status' => 'success', 'message' => 'Identity verified. Redirecting to OTP page.']);
} catch (Throwable $e) {
    error_log('Login Identity Check Error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server error. Please try again later.']);
}
