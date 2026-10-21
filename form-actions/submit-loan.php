<?php
ini_set('display_errors', '0');

header('Content-Type: application/json');

require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "msg" => "Invalid request"]);
    exit;
}

require_valid_csrf();

// Check DB connection
if (!$conn) {
    echo json_encode(["status" => "error", "msg" => "Database connection failed"]);
    exit;
}

// Sanitize inputs
$loan_type = trim($_POST["loan_type"] ?? '');
$name = trim($_POST["full_name"] ?? '');
$mobile = trim($_POST["mobile"] ?? '');
$pincode = trim($_POST["pincode"] ?? '');

// Validation
if (empty($loan_type) || empty($name) || empty($mobile) || empty($pincode)) {
    echo json_encode(["status" => "error", "msg" => "All fields required"]);
    exit;
}

// Format validation
if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
    echo json_encode(["status" => "error", "msg" => "Please enter a valid 10-digit mobile number."]);
    exit;
}
if (!preg_match('/^\d{6}$/', $pincode)) {
    echo json_encode(["status" => "error", "msg" => "Please enter a valid 6-digit pincode."]);
    exit;
}

// Prepare query
$stmt = $conn->prepare("
    INSERT INTO loan_leads 
    (loan_type, full_name, mobile, pincode)
    VALUES (?, ?, ?, ?)
");

if (!$stmt) {
    error_log('Loan lead prepare failed: ' . $conn->error);
    echo json_encode([
        "status" => "error",
        "msg" => "Server error. Please try again later."
    ]);
    exit;
}

// Bind & execute
$stmt->bind_param("ssss", $loan_type, $name, $mobile, $pincode);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    error_log('Loan lead execute failed: ' . $stmt->error);
    echo json_encode([
        "status" => "error",
        "msg" => "Server error. Please try again later."
    ]);
}

$stmt->close();
$conn->close();
?>
