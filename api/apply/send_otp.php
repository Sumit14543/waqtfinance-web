<?php
declare(strict_types=1);

require_once '../config.php';
require_once '../../includes/loan_flow.php';
require_once '../../includes/PHPMailer/Exception.php';
require_once '../../includes/PHPMailer/PHPMailer.php';
require_once '../../includes/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

if (session_status() === PHP_SESSION_NONE) {
    start_secure_session();
}

header('Content-Type: application/json');
require_post();
require_valid_csrf();

function otp_json_response(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function otp_client_ip(): string
{
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        $value = $_SERVER[$key] ?? '';
        if ($value === '') {
            continue;
        }

        $ip = trim(explode(',', (string) $value)[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }

    return '';
}

$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
if (!$csrf || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
    otp_json_response(['status' => 'error', 'message' => 'Invalid CSRF token'], 403);
}

$application = loan_flow_current_application($conn);
$appId = is_array($application) ? (string) ($application['application_id'] ?? '') : (string) ($_SESSION['app_id'] ?? '');
$mobile = $_SESSION['mobile_number'] ?? null;
$email = $_SESSION['user_email'] ?? null;
$firstName = $_SESSION['Full_Name'] ?? 'User';
$ipAddress = otp_client_ip();

if (!$email && !$mobile) {
    otp_json_response(['status' => 'error', 'message' => 'Session expired'], 401);
}

if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    otp_json_response(['status' => 'error', 'message' => 'Invalid email'], 400);
}

try {
    $conn = db();

    try {
        $rateStmt = $conn->prepare("
            SELECT COUNT(*) AS recent_count
            FROM otp_verifications
            WHERE (email = ? OR mobile = ?)
              AND created_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
        ");
        $rateStmt->bind_param("ss", $email, $mobile);
        $rateStmt->execute();
        $recentCount = (int) ($rateStmt->get_result()->fetch_assoc()['recent_count'] ?? 0);
        if ($recentCount >= 5) {
            otp_json_response([
                'status' => 'error',
                'message' => 'Too many OTP requests. Please try again later.',
            ], 429);
        }
    } catch (Throwable $rateError) {
        error_log('OTP rate-limit check failed: ' . $rateError->getMessage());
    }

    $lastSentAt = (int) ($_SESSION['otp_last_sent_at'] ?? 0);
    $expiresAtTs = (int) ($_SESSION['otp_expires_at'] ?? 0);
    $remaining = $expiresAtTs > 0 ? $expiresAtTs - time() : 60 - (time() - $lastSentAt);

    if ($lastSentAt > 0 && $remaining > 0) {
        otp_json_response([
            'status' => 'success',
            'message' => 'OTP already sent. Please wait before requesting again.',
            'retry_after' => $remaining,
        ]);
    }

    $otp = (string) random_int(100000, 999999);
    $otpHash = password_hash($otp, PASSWORD_DEFAULT);
    $expiresAtUnix = time() + 60;
    $expiresAt = date('Y-m-d H:i:s', $expiresAtUnix);

    $hasApplicationIdColumn = loan_flow_column_exists($conn, 'otp_verifications', 'application_id');
    if ($hasApplicationIdColumn && $appId !== '') {
        $del = $conn->prepare("DELETE FROM otp_verifications WHERE application_id = ? AND is_verified = 0");
        $del->bind_param("s", $appId);
        $del->execute();

        $insert = $conn->prepare("
            INSERT INTO otp_verifications (application_id, email, mobile, otp, attempts, is_verified, expires_at, ip_address)
            VALUES (?, ?, ?, ?, 0, 0, ?, ?)
        ");
        $insert->bind_param("ssssss", $appId, $email, $mobile, $otpHash, $expiresAt, $ipAddress);
        $insert->execute();
    } else {
        $del = $conn->prepare("DELETE FROM otp_verifications WHERE (email = ? OR mobile = ?) AND is_verified = 0");
        $del->bind_param("ss", $email, $mobile);
        $del->execute();

        $insert = $conn->prepare("
            INSERT INTO otp_verifications (email, mobile, otp, attempts, is_verified, expires_at, ip_address)
            VALUES (?, ?, ?, 0, 0, ?, ?)
        ");
        $insert->bind_param("sssss", $email, $mobile, $otpHash, $expiresAt, $ipAddress);
        $insert->execute();
    }

    $emailSent = false;
    if ($email) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = required_env('SMTP_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = required_env('SMTP_USERNAME');
            $mail->Password = required_env('SMTP_PASSWORD');
            $mail->SMTPSecure = required_env('SMTP_SECURE');
            $mail->Port = (int) required_env('SMTP_PORT');
            $mail->setFrom(required_env('SMTP_FROM_EMAIL'), 'Waqt Finance');
            $mail->addAddress((string) $email, (string) $firstName);
            $mail->isHTML(true);
            $mail->Subject = 'Your OTP - Waqt Finance';
            $mail->Body = "
                <div style='max-width:600px;margin:auto;font-family:Arial;background:#f4f6f9;padding:20px'>
                  <div style='background:#fff;padding:30px;border-radius:10px;text-align:center'>
                    <h2 style='color:#1d3f8f;margin-bottom:10px'>Waqt Finance</h2>
                    <p style='font-size:16px'>Your One-Time Password</p>
                    <div style='font-size:34px;font-weight:bold;letter-spacing:8px;margin:20px 0;color:#000'>$otp</div>
                    <p>This OTP is valid for <b>1 minute</b>.</p>
                    <p style='font-size:12px;color:#888;margin-top:20px'>If you did not request this, please ignore this email.</p>
                  </div>
                </div>
            ";
            $mail->send();
            $emailSent = true;
        } catch (Throwable $e) {
            error_log("OTP Email Failed for {$email}: " . $e->getMessage());
        }
    }

    $whatsAppSent = false;
    if ($mobile && function_exists('sendWhatsAppOTP')) {
        $waResponse = sendWhatsAppOTP($mobile, $otp);
        $waData = is_string($waResponse) ? json_decode($waResponse, true) : null;
        if ($waData && !isset($waData['error'])) {
            $whatsAppSent = true;
        } else {
            error_log('WhatsApp Failed: ' . waqt_redact_sensitive_text(is_string($waResponse) ? $waResponse : ''));
        }
    }

    if (!$emailSent && !$whatsAppSent) {
        $_SESSION['otp_channels'] = [];
        otp_json_response([
            'status' => 'error',
            'message' => 'OTP could not be delivered. Please check your email/mobile or try again.',
        ], 502);
    }

    $_SESSION['otp_last_sent_at'] = time();
    $_SESSION['otp_expires_at'] = $expiresAtUnix;

    $channels = [];
    if ($emailSent) {
        $channels[] = 'Email';
    }
    if ($whatsAppSent) {
        $channels[] = 'WhatsApp';
    }
    $_SESSION['otp_channels'] = $channels;

    otp_json_response([
        'status' => count($channels) === 2 ? 'success' : 'partial',
        'message' => 'OTP sent via ' . implode(' and ', $channels),
        'channels' => $channels,
        'retry_after' => 60,
    ]);
} catch (Throwable $e) {
    error_log('OTP Send Error: ' . $e->getMessage());
    otp_json_response([
        'status' => 'error',
        'message' => 'Failed to send OTP. Try again later.',
    ], 500);
}
