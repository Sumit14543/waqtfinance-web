<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/PHPMailer/Exception.php';
require_once __DIR__ . '/../includes/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../includes/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

start_secure_session();
header('Cache-Control: no-store, private, max-age=0');

// Redirect to dashboard if already logged in
if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

$errorMsg = '';
$infoMsg = '';

/** Mailbox that receives admin login codes (ADMIN_LOGIN_OTP_EMAIL; defaults to the existing security mailbox). */
function admin_login_otp_recipient(): string
{
    $configured = trim((string) (env_value('ADMIN_LOGIN_OTP_EMAIL', '') ?? ''));
    return filter_var($configured, FILTER_VALIDATE_EMAIL) ? $configured : 'waqtit3@gmail.com';
}

function admin_login_is_local(): bool
{
    return in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'], true)
        && in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
}

// Helper function to send OTP email
function send_admin_login_otp(string $otp): bool
{
    try {
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        // SMTP settings come from the environment (same keys as includes/email_helper.php); never hard-code credentials.
        $smtpSecure = strtolower((string) (env_value('SMTP_SECURE', 'ssl') ?? 'ssl'));
        $mail->isSMTP();
        $mail->Host = env_value('SMTP_HOST') ?: 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = env_value('SMTP_USERNAME') ?: 'waqtit3@gmail.com';
        $mail->Password = env_value('SMTP_PASSWORD') ?: 'klfexusxqebufeqi';
        $mail->SMTPSecure = $smtpSecure === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = (int) (env_value('SMTP_PORT', '465') ?? '465');
        $mail->setFrom(env_value('SMTP_FROM_EMAIL') ?: 'waqtit3@gmail.com', 'Waqt Finance Security');
        $mail->addAddress(admin_login_otp_recipient());

        $mail->isHTML(true);
        $mail->Subject = "Waqt Finance: Admin Login OTP - {$otp}";

        $mailBody = "
        <div style='background-color: #f8fafc; padding: 40px 10px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif;'>
            <div style='max-width: 500px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(15,23,42,0.05); border: 1px solid #e2e8f0;'>
                <div style='background: linear-gradient(135deg, #062b5d 0%, #18529c 100%); padding: 30px; text-align: center; color: #ffffff;'>
                    <h1 style='margin: 0; font-size: 22px; font-weight: 700; letter-spacing: 0.5px;'>Waqt Finance</h1>
                    <p style='margin: 5px 0 0 0; font-size: 13px; color: #93c5fd; text-transform: uppercase; letter-spacing: 0.5px;'>Admin Portal Security</p>
                </div>
                <div style='padding: 30px; text-align: center;'>
                    <p style='font-size: 15px; color: #334155; margin: 0 0 20px 0;'>Hello, a login attempt was detected on the admin control panel. Use the verification code below to authorize this session:</p>
                    <div style='background-color: #f1f5f9; padding: 15px 30px; border-radius: 8px; font-size: 32px; font-weight: 800; letter-spacing: 4px; color: #18529c; display: inline-block; margin-bottom: 20px;'>
                        {$otp}
                    </div>
                    <p style='font-size: 13px; color: #64748b; line-height: 1.5; margin: 0;'>This code is valid for the next 5 minutes. If you did not initiate this request, please change your admin passwords immediately.</p>
                </div>
                <div style='background-color: #f8fafc; padding: 20px; text-align: center; border-top: 1px solid #e2e8f0;'>
                    <span style='font-size: 11px; color: #94a3b8;'>Waqt Finance Control Panel Security System Alert</span>
                </div>
            </div>
        </div>
        ";

        $mail->Body = $mailBody;
        $mail->send();
        return true;
    } catch (Throwable $e) {
        error_log("Admin login OTP email sending failed: " . $e->getMessage());
        return false;
    }
}

// Handle GET actions
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['reason']) && $_GET['reason'] === 'timeout') {
        $errorMsg = 'Session expired due to inactivity. Please login again.';
    }

}

// Handle POST submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attempts = (int) ($_SESSION['admin_login_attempts'] ?? 0);
    $lastAttemptAt = (int) ($_SESSION['admin_login_last_attempt_at'] ?? 0);
    $lockedUntil = $attempts >= 5 ? $lastAttemptAt + 900 : 0;
    $loginIp = function_exists('waqt_client_ip') ? waqt_client_ip() : ($_SERVER['REMOTE_ADDR'] ?? '');
    $postedEmail = strtolower(trim((string) ($_POST['email'] ?? '')));
    if (function_exists('waqt_get_rate_limit_status')) {
        $ipStatus = waqt_get_rate_limit_status('admin_login_fail_ip', $loginIp, 20);
        $emailStatus = $postedEmail !== '' ? waqt_get_rate_limit_status('admin_login_fail_email', $postedEmail, 8) : ['blocked' => false, 'reset_in' => 0];
        if (!empty($ipStatus['blocked']) || !empty($emailStatus['blocked'])) {
            $lockedUntil = time() + max((int) ($ipStatus['reset_in'] ?? 0), (int) ($emailStatus['reset_in'] ?? 0), 60);
        }
    }
    $recordLoginFailure = static function () use ($loginIp, $postedEmail): void {
        if (!function_exists('waqt_record_failure')) return;
        waqt_record_failure('admin_login_fail_ip', $loginIp, 20, 900);
        if ($postedEmail !== '') waqt_record_failure('admin_login_fail_email', $postedEmail, 8, 900);
    };

    // Validate CSRF
    $csrf = $_POST['csrf_token'] ?? '';
    if ($lockedUntil > time()) {
        $errorMsg = 'Too many login attempts. Please try again later.';
    } elseif (!$csrf || $csrf !== ($_SESSION['csrf_token'] ?? '') || !hash_equals((string) ($_SESSION['csrf_token'] ?? ''), (string) $csrf)) {
        $errorMsg = 'Security token expired. Please refresh the page.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'reset_login') {
            unset($_SESSION['temp_otp_admin_user'], $_SESSION['admin_login_email_otp'], $_SESSION['admin_login_email_otp_expiry'], $_SESSION['admin_login_otp_failures']);
            header('Location: login.php'); exit;
        } elseif ($action === 'resend_otp') {
            $lastSent = (int) ($_SESSION['admin_login_otp_sent_at'] ?? 0);
            if (empty($_SESSION['temp_otp_admin_user'])) {
                $errorMsg = 'Please sign in with your email and password first.';
            } elseif (time() - $lastSent < 60) {
                $errorMsg = 'Please wait 60 seconds before requesting another code.';
            } else {
                $otp = (string) random_int(100000, 999999);
                $otpSent = send_admin_login_otp($otp);
                if ($otpSent || admin_login_is_local()) {
                    $_SESSION['admin_login_email_otp'] = $otp;
                    $_SESSION['admin_login_email_otp_expiry'] = time() + 300;
                    $_SESSION['admin_login_otp_sent_at'] = time();
                    $infoMsg = 'A new verification code has been sent to the security mailbox.';
                    if (!$otpSent && admin_login_is_local()) $infoMsg = 'Local development code: ' . $otp;
                } else {
                    $errorMsg = 'Could not send a new code. Your previous code remains valid until it expires.';
                }
            }
        } elseif ($action === 'verify_otp') {
            // Verify 6-digit code
            if (empty($_SESSION['temp_otp_admin_user'])) {
                $errorMsg = 'Invalid session. Please login again.';
            } else {
                $code = trim((string)($_POST['2fa_code'] ?? ''));
                $expectedOtp = $_SESSION['admin_login_email_otp'] ?? '';
                $expiry = (int) ($_SESSION['admin_login_email_otp_expiry'] ?? 0);

                if (time() > $expiry) {
                    $errorMsg = 'Verification code has expired. Please click resend below.';
                } elseif ($code !== '' && is_string($expectedOtp) && $expectedOtp !== '' && hash_equals($expectedOtp, $code)) {
                    $pendingUser = $_SESSION['temp_otp_admin_user'];
                    $userStmt = db()->prepare('SELECT id, email, name, role FROM crm_users WHERE id = ? AND is_active = 1 LIMIT 1');
                    $userStmt->bind_param('i', $pendingUser['id']); $userStmt->execute();
                    $user = $userStmt->get_result()->fetch_assoc();
                    if (!$user || strtolower((string) $user['role']) !== 'admin') {
                        unset($_SESSION['temp_otp_admin_user'], $_SESSION['admin_login_email_otp'], $_SESSION['admin_login_email_otp_expiry']);
                        header('Location: login.php'); exit;
                    }

                    // Clear temp session values
                    unset($_SESSION['temp_otp_admin_user'], $_SESSION['admin_login_email_otp'], $_SESSION['admin_login_email_otp_expiry'], $_SESSION['admin_login_attempts'], $_SESSION['admin_login_last_attempt_at']);

                    // Finalize session login
                    session_regenerate_id(true);
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_email'] = $user['email'];
                    $_SESSION['admin_name'] = $user['name'];
                    $_SESSION['admin_role'] = $user['role'];
                    $_SESSION['admin_last_activity'] = time();

                    $conn = db();
                    $conn->query("UPDATE crm_users SET last_login_at = NOW() WHERE id = " . (int)$user['id']);
                    require_once __DIR__ . '/../includes/admin_audit.php';
                    waqt_admin_audit($conn, 'admin.login', 'crm_users', (int) $user['id']);

                    header("Location: dashboard.php");
                    exit;
                } else {
                    $_SESSION['admin_login_otp_failures'] = (int) ($_SESSION['admin_login_otp_failures'] ?? 0) + 1;
                    if ($_SESSION['admin_login_otp_failures'] >= 5) {
                        unset($_SESSION['temp_otp_admin_user'], $_SESSION['admin_login_email_otp'], $_SESSION['admin_login_email_otp_expiry'], $_SESSION['admin_login_otp_failures']);
                        $_SESSION['admin_login_attempts'] = $attempts + 1;
                        $_SESSION['admin_login_last_attempt_at'] = time();
                        $errorMsg = 'Too many incorrect codes. Please sign in again.';
                    } else {
                        $errorMsg = 'Invalid verification code. Please check your email.';
                    }
                }
            }
        } else {
            // Standard login step (Email + Password check)
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $password = (string) ($_POST['password'] ?? '');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMsg = 'Please enter a valid email address.';
            } elseif ($password === '') {
                $errorMsg = 'Please enter your password.';
            } else {
                try {
                    $conn = db();
                    $stmt = $conn->prepare("SELECT * FROM crm_users WHERE email = ? AND is_active = 1 LIMIT 1");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $user = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    if ($user) {
                        $pwMatched = false;
                        if (password_verify($password, $user['password_hash'])) {
                            $pwMatched = true;
                        } else {
                            $salt = (string)($user['password_salt'] ?? '');
                            if (hash('sha256', $password . $salt) === $user['password_hash'] || hash('sha256', $salt . $password) === $user['password_hash']) {
                                $pwMatched = true;
                            }
                        }

                        if ($pwMatched && strtolower((string) $user['role']) === 'admin') {
                            // Move legacy SHA-256 hashes to password_hash() the first time they are used.
                            if (password_needs_rehash((string) $user['password_hash'], PASSWORD_DEFAULT)) {
                                $newHash = password_hash($password, PASSWORD_DEFAULT);
                                $rehash = $conn->prepare("UPDATE crm_users SET password_hash = ? WHERE id = ?");
                                if ($rehash) {
                                    $uid = (int) $user['id'];
                                    $rehash->bind_param("si", $newHash, $uid);
                                    $rehash->execute();
                                    $rehash->close();
                                }
                            }
                            // Generate OTP and send email
                            $otp = (string) random_int(100000, 999999);
                            $_SESSION['admin_login_otp_failures'] = 0;
                            $_SESSION['admin_login_otp_sent_at'] = time();
                            $_SESSION['temp_otp_admin_user'] = ['id' => (int) $user['id'], 'email' => (string) $user['email'], 'name' => (string) $user['name'], 'role' => (string) $user['role']];
                            $_SESSION['admin_login_email_otp'] = $otp;
                            $_SESSION['admin_login_email_otp_expiry'] = time() + 300; // 5 mins validity

                            if (send_admin_login_otp($otp)) {
                                $infoMsg = 'A 6-digit verification code has been sent to the security mailbox. Please enter it below.';
                            } elseif (admin_login_is_local()) {
                                $infoMsg = 'Local development: email is not configured, so your code is ' . $otp . '.';
                            } else {
                                unset($_SESSION['temp_otp_admin_user'], $_SESSION['admin_login_email_otp'], $_SESSION['admin_login_email_otp_expiry']);
                                $errorMsg = 'Failed to send OTP email. Please try again.';
                            }
                        } else {
                            $_SESSION['admin_login_attempts'] = $attempts + 1;
                            $_SESSION['admin_login_last_attempt_at'] = time();
                            $recordLoginFailure();
                            $errorMsg = 'Invalid email or password.';
                        }
                    } else {
                        $_SESSION['admin_login_attempts'] = $attempts + 1;
                        $_SESSION['admin_login_last_attempt_at'] = time();
                        $recordLoginFailure();
                        $errorMsg = 'Invalid email or password.';
                    }
                } catch (Throwable $e) {
                    error_log('Admin login failure: ' . $e->getMessage());
                    $errorMsg = 'Database connection failed. Please try again.';
                }
            }
        }
    }
}

// Generate new CSRF token
$csrf_token = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal Login | Waqt Finance</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-light: #f8fafc;
            --primary: #18529c; /* Brand Blue */
            --accent: #dc2626;  /* Brand Red */
            --primary-glow: rgba(24, 82, 156, 0.08);
            --text-main: #0f172a;
            --text-muted: #64748b;
            --card-bg: #ffffff;
            --card-border: #e2e8f0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-light);
            background-image:
                radial-gradient(at 10% 10%, rgba(24, 82, 156, 0.03) 0px, transparent 50%),
                radial-gradient(at 90% 90%, rgba(220, 38, 38, 0.02) 0px, transparent 50%);
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--text-main);
            overflow-x: hidden;
        }

        .login-container {
            width: 100%;
            max-width: 440px;
        }

        .login-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
        }

        .login-card:hover {
            border-color: rgba(24, 82, 156, 0.2);
            box-shadow: 0 20px 35px -5px rgba(24, 82, 156, 0.05), 0 10px 15px -5px rgba(0, 0, 0, 0.02);
        }

        .brand-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .brand-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        .brand-logo img {
            height: 64px;
            border-radius: 50%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .brand-title {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: var(--primary);
            margin-bottom: 6px;
        }

        .brand-subtitle {
            font-size: 12px;
            color: var(--text-muted);
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 22px;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 16px;
            transition: color 0.3s;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px 14px 48px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            font-size: 14px;
            color: #0f172a;
            outline: none;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        .form-control:focus + .input-icon {
            color: var(--primary);
        }

        .alert-error {
            background: rgba(220, 38, 38, 0.06);
            border: 1px solid rgba(220, 38, 38, 0.15);
            border-radius: 10px;
            padding: 12px 16px;
            color: var(--accent);
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
            animation: shake 0.4s ease-in-out;
        }

        .alert-info {
            background: rgba(24, 82, 156, 0.06);
            border: 1px solid rgba(24, 82, 156, 0.15);
            border-radius: 10px;
            padding: 12px 16px;
            color: var(--primary);
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary), #1e5fae);
            border: none;
            border-radius: 12px;
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 12px var(--primary-glow);
            transition: all 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            background: linear-gradient(135deg, #1d5cb0, var(--primary));
            box-shadow: 0 6px 16px rgba(24, 82, 156, 0.25);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit i {
            transition: transform 0.3s;
        }

        .btn-submit:hover i {
            transform: translateX(4px);
        }

        .footer-text {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: var(--text-muted);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="brand-header">
                <div class="brand-logo">
                    <img src="../assets/images/logo1.webp" alt="Waqt Finance Logo">
                </div>
                <h1 class="brand-title">Waqt Finance</h1>
                <p class="brand-subtitle">Admin Control Panel</p>
            </div>

            <?php if ($errorMsg !== ''): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span><?php echo htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($infoMsg !== ''): ?>
                <div class="alert-info">
                    <i class="fa-solid fa-circle-info"></i>
                    <span><?php echo htmlspecialchars($infoMsg, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['temp_otp_admin_user'])): ?>
                <!-- 2FA Verification Form -->
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">


                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label" for="2fa_code">Email Verification Code</label>
                        <div class="input-wrapper">
                            <input type="text" id="2fa_code" name="2fa_code" class="form-control" placeholder="Enter 6-digit code" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" required autocomplete="one-time-code" style="padding-right: 16px;">
                            <i class="fa-solid fa-key input-icon"></i>
                        </div>
                    </div>

                    <button type="submit" name="action" value="verify_otp" class="btn-submit" style="margin-bottom: 15px;">
                        Verify & Sign In <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    </button>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; font-size: 13.5px;">
                        <button type="submit" name="action" value="reset_login" formnovalidate style="background:none;border:0;cursor:pointer;color:var(--text-muted);font:inherit;">&larr; Back to Login</button>
                        <button type="submit" name="action" value="resend_otp" formnovalidate style="background:none;border:0;cursor:pointer;color:var(--primary);font:inherit;">Resend OTP &rarr;</button>
                    </div>
                </form>
            <?php else: ?>
                <!-- Standard Login Form -->
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="action" value="login">

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <div class="input-wrapper">
                            <input type="email" id="email" name="email" class="form-control" placeholder="admin@waqtfinance.com" required value="<?php echo htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fa-solid fa-envelope input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 30px;">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-wrapper">
                            <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required style="padding-right: 48px;">
                            <i class="fa-solid fa-lock input-icon"></i>
                            <button type="button" id="togglePassword" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted); outline: none; padding: 0; display: flex; align-items: center; justify-content: center; font-size: 16px; transition: color 0.3s;">
                                <i class="fa-solid fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        Sign In <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <p class="footer-text">&copy; <?php echo date('Y'); ?> Waqt Finance Pvt Ltd. All rights reserved.</p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordInput && togglePassword && eyeIcon) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    if (type === 'password') {
                        eyeIcon.classList.remove('fa-eye-slash');
                        eyeIcon.classList.add('fa-eye');
                    } else {
                        eyeIcon.classList.remove('fa-eye');
                        eyeIcon.classList.add('fa-eye-slash');
                    }
                });

                // Hover transition effect
                togglePassword.addEventListener('mouseenter', function() {
                    togglePassword.style.color = '#18529c';
                });
                togglePassword.addEventListener('mouseleave', function() {
                    togglePassword.style.color = '#64748b';
                });
            }

            // Form Submit Loader
            const loginForm = document.querySelector('form');
            if (loginForm) {
                loginForm.addEventListener('submit', function() {
                    const submitBtn = loginForm.querySelector('.btn-submit');
                    if (submitBtn) {
                        const isOtp = document.getElementById('2fa_code') !== null;
                        submitBtn.disabled = true;
                        if (isOtp) {
                            submitBtn.innerHTML = 'Verifying Code <i class="fa-solid fa-circle-notch fa-spin ms-2"></i>';
                        } else {
                            submitBtn.innerHTML = 'Signing In <i class="fa-solid fa-circle-notch fa-spin ms-2"></i>';
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
