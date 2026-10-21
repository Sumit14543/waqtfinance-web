<?php
declare(strict_types=1);

require_once '../config.php';
require_once __DIR__ . '/../../includes/loan_flow.php';

header('Content-Type: application/json; charset=utf-8');
require_post();
require_valid_csrf();

function update_contact_response(array $payload, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
if (!$csrf || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
    update_contact_response(['status' => 'error', 'message' => 'Invalid CSRF token'], 403);
}

$application = loan_flow_current_application($conn);
if (!$application) {
    update_contact_response(['status' => 'error', 'message' => 'Session expired. Restart application.'], 401);
}

$appId = (string) ($application['application_id'] ?? '');
$currentStep = strtolower((string) ($application['current_step'] ?? ''));
if ($appId === '' || $currentStep !== 'mobile_otp') {
    update_contact_response(['status' => 'error', 'message' => 'Contact details can be changed only before OTP verification.'], 409);
}

$mobile = preg_replace('/\D/', '', (string) ($_POST['mobile'] ?? ''));
$email = normalize_email((string) ($_POST['email'] ?? ''));

if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
    update_contact_response(['status' => 'error', 'message' => 'Enter a valid 10-digit mobile number.'], 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    update_contact_response(['status' => 'error', 'message' => 'Enter a valid email address.'], 422);
}

try {
    $oldMobile = (string) ($application['mobile'] ?? '');
    $oldEmail = (string) ($application['email'] ?? '');

    loan_flow_update_application($conn, $appId, [
        'mobile' => $mobile,
        'email' => $email,
        'current_step' => 'mobile_otp',
        'last_activity_at' => date('Y-m-d H:i:s'),
    ]);

    if (loan_flow_column_exists($conn, 'otp_verifications', 'application_id')) {
        $delete = $conn->prepare("
            DELETE FROM otp_verifications
            WHERE application_id = ?
              AND is_verified = 0
        ");
        $delete->bind_param('s', $appId);
    } else {
        $delete = $conn->prepare("
            DELETE FROM otp_verifications
            WHERE is_verified = 0
              AND (
                (mobile IS NOT NULL AND mobile IN (?, ?))
                OR (email IS NOT NULL AND email IN (?, ?))
              )
        ");
        $delete->bind_param('ssss', $oldMobile, $mobile, $oldEmail, $email);
    }
    $delete->execute();

    $_SESSION['app_id'] = $appId;
    $_SESSION['mobile_number'] = $mobile;
    $_SESSION['user_email'] = $email;
    unset(
        $_SESSION['otp_verified'],
        $_SESSION['otp_last_sent_at'],
        $_SESSION['otp_expires_at'],
        $_SESSION['otp_channels']
    );

    loan_flow_set_resume_cookie($appId);

    update_contact_response([
        'status' => 'success',
        'message' => 'Contact details updated. Please request a new OTP.',
        'mobile' => $mobile,
        'email' => $email,
    ]);
} catch (Throwable $e) {
    error_log('OTP contact update failed: ' . $e->getMessage());
    update_contact_response(['status' => 'error', 'message' => 'Unable to update contact details. Please try again.'], 500);
}
