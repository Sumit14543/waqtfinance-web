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

$mobile = preg_replace('/\D/', '', trim((string) ($_POST['mobile'] ?? '')));
$otp = trim((string) ($_POST['otp'] ?? ''));

if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid mobile number.']);
    exit;
}

if (!preg_match('/^\d{6}$/', $otp)) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid 6-digit OTP.']);
    exit;
}

$isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'], true) || (env_value('APP_ENV') === 'local');

try {
    $conn = db();
    $verified = false;

    // Check DB record first
    $stmt = $conn->prepare("
        SELECT id, otp, attempts, is_verified, expires_at 
        FROM otp_verifications 
        WHERE mobile = ?
        ORDER BY id DESC 
        LIMIT 1
    ");

    if ($stmt) {
        $stmt->bind_param("s", $mobile);
        $stmt->execute();
        $record = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($record) {
            if (strtotime($record['expires_at']) < time()) {
                echo json_encode(['status' => 'error', 'message' => 'OTP has expired. Please request a new one.']);
                exit;
            }

            if ((int)$record['attempts'] >= 5) {
                echo json_encode(['status' => 'error', 'message' => 'Too many invalid attempts. Please request a new OTP.']);
                exit;
            }

            if (password_verify($otp, $record['otp'])) {
                $verified = true;
                $up = $conn->prepare("UPDATE otp_verifications SET is_verified = 1 WHERE id = ?");
                if ($up) {
                    $up->bind_param("i", $record['id']);
                    $up->execute();
                    $up->close();
                }
            } else {
                // Increment attempts
                $att = (int)$record['attempts'] + 1;
                $up = $conn->prepare("UPDATE otp_verifications SET attempts = ? WHERE id = ?");
                if ($up) {
                    $up->bind_param("ii", $att, $record['id']);
                    $up->execute();
                    $up->close();
                }
            }
        }
    }

    // Session fallback check if DB record wasn't verified
    if (!$verified) {
        $sessionOtp = $_SESSION['lead_otp_code_' . $mobile] ?? '';
        if ($sessionOtp !== '' && $sessionOtp === $otp) {
            $verified = true;
        }
    }

    if ($verified) {
        $_SESSION['lead_mobile_verified_' . $mobile] = true;
        $_SESSION['lead_verified_mobile'] = $mobile;
        unset($_SESSION['lead_otp_code_' . $mobile]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Mobile number verified successfully!',
            'mobile' => $mobile
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid OTP. Please check and try again.'
        ]);
    }

} catch (Throwable $e) {
    error_log('Verify lead OTP error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Verification failed. Please try again.']);
}
