<?php
header("Content-Type: application/json");
require_once '../config/db.php';
require_once __DIR__ . '/../includes/email_helper.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

require_valid_csrf();

// Sanitize inputs
$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$subject = trim($_POST["subject"] ?? "");
$message = trim($_POST["message"] ?? "");
$consent = isset($_POST["consent"]) ? 1 : 0;

// Server-side validation (IMPORTANT)
if (
    strlen($name) < 3 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    !preg_match("/^[0-9]{10}$/", $phone) ||
    empty($subject) ||
    strlen($message) < 10 ||
    !$consent
) {
    echo json_encode(["success" => false, "message" => "Invalid form data"]);
    exit;
}

// Extra security data
$ipAddress = $_SERVER["REMOTE_ADDR"] ?? null;
$userAgent = $_SERVER["HTTP_USER_AGENT"] ?? null;

// Prepared statement (SQL Injection safe)
$stmt = $conn->prepare("
  INSERT INTO contact_messages 
  (name, email, phone, subject, message, consent, ip_address, user_agent)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
  "sssssiss",
  $name,
  $email,
  $phone,
  $subject,
  $message,
  $consent,
  $ipAddress,
  $userAgent
);

if ($stmt->execute()) {
    // Send email notification using premium fintech template
    send_contact_enquiry_notification($name, $email, $phone, $subject, $message, $ipAddress, $userAgent);

    echo json_encode(["success" => true]);
} else {
    error_log('Contact form insert failed: ' . $stmt->error);
    echo json_encode(["success" => false, "message" => "Database error"]);
}

$stmt->close();
$conn->close();
