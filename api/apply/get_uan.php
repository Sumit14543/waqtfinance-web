<?php
require_once '../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$app_id = $_SESSION['app_id'] ?? null;

if (!$app_id) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired']);
    exit;
}

$conn = db();

$stmt = $conn->prepare("
    SELECT uan_number 
    FROM loan_applications 
    WHERE application_id = ?
");

$stmt->bind_param("s", $app_id);
$stmt->execute();

$result = $stmt->get_result()->fetch_assoc();

$uan = $result['uan_number'] ?? null;

echo json_encode([
    'status' => 'success',
    'uan_number' => $uan
]);