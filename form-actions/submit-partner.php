<?php
header("Content-Type: application/json");
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

require_valid_csrf();

$action = trim($_POST["action"] ?? "");
$clientIp = function_exists('waqt_client_ip') ? waqt_client_ip() : ($_SERVER['REMOTE_ADDR'] ?? '');

if ($action === "register") {
    if (function_exists('waqt_enforce_rate_limit')) {
        waqt_enforce_rate_limit('partner_register_ip', $clientIp, 10, 3600, 'Too many registrations from your network. Please try again later.');
    }
    $fullName = trim(strip_tags((string) ($_POST["full_name"] ?? "")));
    $emailOrMobile = trim((string) ($_POST["email_or_mobile"] ?? ""));
    if (filter_var($emailOrMobile, FILTER_VALIDATE_EMAIL)) {
        $emailOrMobile = strtolower($emailOrMobile);
    }
    $password = (string) ($_POST["password"] ?? "");
    if (($_POST["consent"] ?? "") !== "1") {
        echo json_encode(["success" => false, "message" => "Please accept the partner terms and privacy policy to continue."]);
        exit;
    }
    if (mb_strlen($fullName) > 150) {
        echo json_encode(["success" => false, "message" => "Full Name is too long."]);
        exit;
    }

    if (strlen($fullName) < 3) {
        echo json_encode(["success" => false, "message" => "Full Name must be at least 3 characters."]);
        exit;
    }
    
    $isEmail = filter_var($emailOrMobile, FILTER_VALIDATE_EMAIL);
    $isMobile = preg_match("/^[6-9][0-9]{9}$/", $emailOrMobile);

    if (!$isEmail && !$isMobile) {
        echo json_encode(["success" => false, "message" => "Enter a valid email address or 10-digit mobile number."]);
        exit;
    }

    if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        echo json_encode(["success" => false, "message" => "Use at least 8 characters with a letter and a number."]);
        exit;
    }

    // Check if duplicate exists
    $checkStmt = $conn->prepare("SELECT id FROM partner_registrations WHERE email_or_mobile = ?");
    $checkStmt->bind_param("s", $emailOrMobile);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "This email or mobile number is already registered."]);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();

    // Hash password
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO partner_registrations (full_name, email_or_mobile, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $fullName, $emailOrMobile, $passwordHash);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Thank you. Your partner application is received. Our partner team will contact you to complete KYC and the partner agreement."]);
    } else {
        error_log('Partner registration failed: ' . $stmt->error);
        echo json_encode(["success" => false, "message" => "Failed to save registration. Please try again."]);
    }
    $stmt->close();

} elseif ($action === "login") {
    $emailOrMobile = trim((string) ($_POST["email_or_mobile"] ?? ""));
    if (filter_var($emailOrMobile, FILTER_VALIDATE_EMAIL)) {
        $emailOrMobile = strtolower($emailOrMobile);
    }
    $password = (string) ($_POST["password"] ?? "");
    if (function_exists('waqt_enforce_rate_limit')) {
        waqt_enforce_rate_limit('partner_login_ip', $clientIp, 20, 900, 'Too many login attempts. Please wait 15 minutes and try again.');
        waqt_enforce_rate_limit('partner_login_id', hash('sha256', $emailOrMobile), 8, 900, 'Too many login attempts for this account. Please wait 15 minutes and try again.');
    }

    if ($emailOrMobile === '' || $password === '') {
        echo json_encode(["success" => false, "message" => "Please enter email/mobile and password."]);
        exit;
    }

    // Fetch partner details
    $stmt = $conn->prepare("SELECT id, full_name, password_hash, status FROM partner_registrations WHERE email_or_mobile = ?");
    $stmt->bind_param("s", $emailOrMobile);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Same message for unknown account and wrong password, so accounts cannot be discovered.
    if ($result->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Incorrect email/mobile or password."]);
        $stmt->close();
        exit;
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    if (!password_verify($password, $user['password_hash'])) {
        echo json_encode(["success" => false, "message" => "Incorrect email/mobile or password."]);
        exit;
    }
    $partnerStatus = strtolower(trim((string) ($user['status'] ?? ''))) ?: 'pending';
    if ($partnerStatus === 'rejected') {
        echo json_encode(["success" => false, "status" => "rejected", "message" => "Your partner application was not approved. Please contact customercare@waqtfinance.com for details."]);
        exit;
    }

    // Initialize partner session
    start_secure_session();
    session_regenerate_id(true);
    $_SESSION['partner_logged_in'] = true;
    $_SESSION['partner_status'] = $partnerStatus;
    $_SESSION['partner_id'] = $user['id'];
    $_SESSION['partner_name'] = $user['full_name'];

    echo json_encode([
        "success" => true,
        "status" => $partnerStatus,
        "name" => $user['full_name'],
        "message" => $partnerStatus === 'approved'
            ? "Welcome, " . $user['full_name'] . ". Your partner account is active. Share new leads with your partner manager or call +91 92170 86610."
            : "Welcome, " . $user['full_name'] . ". Your application is under review. Our partner team will contact you to complete KYC and the partner agreement."
    ]);

} else {
    echo json_encode(["success" => false, "message" => "Invalid action specified."]);
}

$conn->close();
?>
