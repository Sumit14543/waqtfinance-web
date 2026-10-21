<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');

require __DIR__ . '/../config/db.php';

start_secure_session();
require_post();
require_valid_csrf();

try {
    $email = normalize_email((string) ($_POST['email'] ?? ''));
    $submittedOtp = preg_replace('/\D+/', '', (string) ($_POST['otp'] ?? '')) ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['status' => 'error', 'message' => 'Invalid email address provided.'], 422);
    }

    if (!preg_match('/^[0-9]{4}$/', $submittedOtp)) {
        json_response(['status' => 'error', 'message' => 'Please enter a valid 4-digit OTP.'], 422);
    }

    $conn = db();
    $stmt = $conn->prepare(
        'SELECT id, otp, attempts, is_verified, expires_at
         FROM otp_verifications
         WHERE email = ?
         ORDER BY id DESC
         LIMIT 1'
    );
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $otpRow = $stmt->get_result()->fetch_assoc();

    if ($otpRow === null) {
        json_response(['status' => 'error', 'message' => 'OTP session expired. Please request a new one.'], 404);
    }

    $otpId = (int) $otpRow['id'];
    $attempts = (int) $otpRow['attempts'];

    if ((int) $otpRow['is_verified'] === 1) {
        json_response(['status' => 'error', 'message' => 'This OTP has already been used. Please request a new one.'], 409);
    }

    if (strtotime((string) $otpRow['expires_at']) < time()) {
        json_response(['status' => 'error', 'message' => 'OTP has expired. Please request a new one.'], 410);
    }

    if ($attempts >= 5) {
        json_response(['status' => 'error', 'message' => 'Too many invalid attempts. Please request a new OTP.'], 429);
    }

    if (!hash_equals((string) $otpRow['otp'], otp_digest($submittedOtp))) {
        $attempts++;
        $updateStmt = $conn->prepare('UPDATE otp_verifications SET attempts = ? WHERE id = ?');
        $updateStmt->bind_param('ii', $attempts, $otpId);
        $updateStmt->execute();

        $remaining = max(0, 5 - $attempts);
        json_response([
            'status' => 'error',
            'message' => $remaining > 0
                ? 'Invalid OTP. Attempts remaining: ' . $remaining
                : 'Too many invalid attempts. Please request a new OTP.'
        ], $remaining > 0 ? 401 : 429);
    }

    $verifyStmt = $conn->prepare('UPDATE otp_verifications SET is_verified = 1 WHERE id = ?');
    $verifyStmt->bind_param('i', $otpId);
    $verifyStmt->execute();

    session_regenerate_id(true);
    $_SESSION['verified_user'] = true;
    $_SESSION['verified_email'] = $email;
    $_SESSION['verified_at'] = time();

    json_response(['status' => 'success', 'message' => 'OTP verified successfully.']);
} catch (Throwable $e) {
    report_exception($e);
}
