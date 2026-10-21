<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');

require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/PHPMailer/Exception.php';
require __DIR__ . '/../includes/PHPMailer/PHPMailer.php';
require __DIR__ . '/../includes/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

start_secure_session();
require_post();
require_valid_csrf();

try {
    $otpId = null;
    $email = normalize_email((string) ($_POST['email'] ?? ''));
    $mobile = preg_replace('/\D+/', '', (string) ($_POST['mobile'] ?? '')) ?? '';
    $firstName = clean_name((string) ($_POST['first_name'] ?? 'User'));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['status' => 'error', 'message' => 'Invalid email address provided.'], 422);
    }
    if ($mobile !== '' && !preg_match('/^[6-9][0-9]{9}$/', $mobile)) {
        json_response([
            'status' => 'error',
            'message' => 'Invalid mobile number.'
        ], 422);
    }

    if ($firstName === '') {
        $firstName = 'User';
    }

    $smtpHost = required_env('SMTP_HOST');
    $smtpUsername = required_env('SMTP_USERNAME');
    $smtpPassword = required_env('SMTP_PASSWORD');
    $smtpFromEmail = required_env('SMTP_FROM_EMAIL');
    $smtpFromName = env_value('SMTP_FROM_NAME', 'Waqt Finance') ?? 'Waqt Finance';
    $smtpSecure = env_value('SMTP_SECURE', PHPMailer::ENCRYPTION_SMTPS) ?? PHPMailer::ENCRYPTION_SMTPS;
    $smtpPort = (int) (env_value('SMTP_PORT', '465') ?? '465');

    $conn = db();

    try {
        $rateStmt = $conn->prepare(
            'SELECT COUNT(*) AS recent_count
             FROM otp_verifications
             WHERE (email = ? OR mobile = ?)
               AND created_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)'
        );
        $rateStmt->bind_param('ss', $email, $mobile);
        $rateStmt->execute();
        $recentCount = (int) ($rateStmt->get_result()->fetch_assoc()['recent_count'] ?? 0);
        if ($recentCount >= 5) {
            json_response([
                'status' => 'error',
                'message' => 'Too many OTP requests. Please try again later.'
            ], 429);
        }
    } catch (Throwable $rateError) {
        error_log('OTP rate-limit check failed: ' . $rateError->getMessage());
    }

    // Clear orphan rows created by older failed sends before this fixed endpoint version.
    $fixedAt = date('Y-m-d H:i:s', filemtime(__FILE__) ?: time());
    $orphanStmt = $conn->prepare(
        'DELETE FROM otp_verifications
         WHERE (email = ? OR mobile = ?)
           AND is_verified = 0
           AND attempts = 0
           AND created_at < ?
           AND expires_at > NOW()'
    );
    $orphanStmt->bind_param('sss', $email, $mobile, $fixedAt);
    $orphanStmt->execute();

    $rateStmt = $conn->prepare(
        'SELECT TIMESTAMPDIFF(SECOND, created_at, NOW()) AS seconds_since_last_otp
         FROM otp_verifications
         WHERE email = ? OR mobile = ?
         ORDER BY id DESC
         LIMIT 1'
    );
    $rateStmt->bind_param('ss', $email, $mobile);
    $rateStmt->execute();
    $latest = $rateStmt->get_result()->fetch_assoc();

    if ($latest !== null && (int) $latest['seconds_since_last_otp'] < 60) {
        $waitSeconds = max(1, 60 - (int) $latest['seconds_since_last_otp']);
        json_response([
            'status' => 'error',
            'message' => 'Please wait ' . $waitSeconds . ' seconds before requesting another OTP.'
        ], 429);
    }

    $otp = (string) random_int(1000, 9999);
    $otpHash = otp_digest($otp);
    $expiresAt = date('Y-m-d H:i:s', time() + 600);

    $insertStmt = $conn->prepare(
        'INSERT INTO otp_verifications (email, mobile, otp, attempts, is_verified, created_at, expires_at)
         VALUES (?, ?, ?, 0, 0, NOW(), ?)'
    );
    $insertStmt->bind_param('ssss', $email, $mobile, $otpHash, $expiresAt);
    $insertStmt->execute();
    $otpId = $conn->insert_id;

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $smtpHost;
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUsername;
    $mail->Password = $smtpPassword;
    $mail->SMTPSecure = $smtpSecure;
    $mail->Port = $smtpPort;

    $mail->setFrom(
        $smtpFromEmail,
        $smtpFromName
    );
    $mail->addAddress($email, $firstName);
    $mail->isHTML(true);
    $mail->Subject = 'Your Security OTP for CIBIL Check - Waqt Finance';

    $safeName = htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8');
    $safeOtp = htmlspecialchars($otp, ENT_QUOTES, 'UTF-8');

    $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #e1e5ea; border-radius: 12px; overflow: hidden;">
            <div style="background-color: #18529c; padding: 25px; text-align: center;">
                <h1 style="color: #ffffff; margin: 0; font-size: 26px;">Waqt Finance</h1>
            </div>
            <div style="padding: 40px 30px; text-align: center; background-color: #ffffff;">
                <h2 style="color: #18529c; margin-top: 0; font-size: 22px;">Identity Verification</h2>
                <p style="color: #555; font-size: 15px; line-height: 1.6;">
                    Hi ' . $safeName . ',<br><br>
                    Please use this one-time password to securely access your free credit health insights.
                </p>
                <div style="font-size: 38px; font-weight: 800; border: 2px dashed #ef4444; color: #ef4444; padding: 15px 30px; border-radius: 8px; display: inline-block; letter-spacing: 8px;">
                    ' . $safeOtp . '
                </div>
                <p style="color: #888; font-size: 13px; margin-top: 25px;">
                    This code is valid for 10 minutes. Never share it with anyone.
                </p>
            </div>
        </div>';
    $mail->AltBody = "Your Waqt Finance OTP is: {$otp}. Valid for 10 minutes.";

    $emailSent = false;

    try {
        $mail->send();
        $emailSent = true;
    } catch (Throwable $e) {
        error_log($e->getMessage());
    }
    
    $whatsAppSent = false;

    if ($mobile !== '' && function_exists('sendWhatsAppOTP')) {
    
        $waResponse = sendWhatsAppOTP($mobile, $otp);
    
        $waData = is_string($waResponse)
            ? json_decode($waResponse, true)
            : null;
    
        if ($waData && !isset($waData['error'])) {
    
            $whatsAppSent = true;
    
        } else {
    
            error_log(
                'WhatsApp OTP Failed: ' .
                waqt_redact_sensitive_text(is_string($waResponse) ? $waResponse : '')
            );
        }
    }

    unset($_SESSION['verified_user'], $_SESSION['verified_email'], $_SESSION['verified_at']);

$channels = [];
if ($emailSent) {
    $channels[] = 'Email';
}

if ($whatsAppSent) {
    $channels[] = 'WhatsApp';
}

json_response([
    'status' => !empty($channels) ? 'success' : 'error',
    'message' => 'OTP sent via ' . implode(' and ', $channels),
    'channels' => $channels,
    'email' => $email,
    'mobile' => $mobile
]);
} catch (Exception $e) {
    if (isset($conn) && $conn instanceof mysqli && !empty($otpId)) {
        $cleanupStmt = $conn->prepare('DELETE FROM otp_verifications WHERE id = ? AND is_verified = 0');
        $cleanupStmt->bind_param('i', $otpId);
        $cleanupStmt->execute();
    }
    error_log('OTP email failed: ' . $e->getMessage());
    json_response(['status' => 'error', 'message' => 'Unable to send OTP right now. Please try again later.'], 500);
    
} catch (Throwable $e) {
    if (isset($conn) && $conn instanceof mysqli && !empty($otpId)) {
        $cleanupStmt = $conn->prepare('DELETE FROM otp_verifications WHERE id = ? AND is_verified = 0');
        $cleanupStmt->bind_param('i', $otpId);
        $cleanupStmt->execute();
    }
    report_exception($e);
}
