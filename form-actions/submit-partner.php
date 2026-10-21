<?php
header("Content-Type: application/json");
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

require_valid_csrf();

$action = trim($_POST["action"] ?? "");

if ($action === "register") {
    $fullName = trim($_POST["full_name"] ?? "");
    $emailOrMobile = trim($_POST["email_or_mobile"] ?? "");
    $password = $_POST["password"] ?? "";

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

    if (strlen($password) < 6) {
        echo json_encode(["success" => false, "message" => "Password must be at least 6 characters."]);
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
        echo json_encode(["success" => true, "message" => "Partner account registered successfully!"]);
    } else {
        error_log('Partner registration failed: ' . $stmt->error);
        echo json_encode(["success" => false, "message" => "Failed to save registration. Please try again."]);
    }
    $stmt->close();

} elseif ($action === "login") {
    $emailOrMobile = trim($_POST["email_or_mobile"] ?? "");
    $password = $_POST["password"] ?? "";

    if (empty($emailOrMobile) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Please enter email/mobile and password."]);
        exit;
    }

    // Fetch partner details
    $stmt = $conn->prepare("SELECT id, full_name, password_hash, status FROM partner_registrations WHERE email_or_mobile = ?");
    $stmt->bind_param("s", $emailOrMobile);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Account not found."]);
        $stmt->close();
        exit;
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    if (!password_verify($password, $user['password_hash'])) {
        echo json_encode(["success" => false, "message" => "Incorrect password."]);
        exit;
    }

    // Initialize partner session
    start_secure_session();
    $_SESSION['partner_logged_in'] = true;
    $_SESSION['partner_id'] = $user['id'];
    $_SESSION['partner_name'] = $user['full_name'];

    echo json_encode([
        "success" => true,
        "message" => "Logged in successfully! Welcome, " . $user['full_name']
    ]);

} else {
    echo json_encode(["success" => false, "message" => "Invalid action specified."]);
}

$conn->close();
?>
