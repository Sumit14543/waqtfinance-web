<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';

// Auth check
start_secure_session();
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo "Unauthorized access";
    exit;
}

$type = $_GET['type'] ?? 'payday';
$conn = db();

$filename = $type . "_export_" . date("Ymd_His") . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

if ($type === 'payday') {
    fputcsv($output, ['ID', 'Application ID', 'Full Name', 'Mobile', 'Email', 'Loan Amount', 'Status', 'Created At']);
    $query = "SELECT id, application_id, full_name, mobile, email, loan_amount, status, created_at FROM loan_applications ORDER BY id DESC";
    $res = $conn->query($query);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            fputcsv($output, $row);
        }
    }
} elseif ($type === 'general') {
    fputcsv($output, ['ID', 'Reference', 'Full Name', 'Mobile', 'Email', 'Loan Type', 'Loan Amount', 'Created At']);
    $query = "SELECT id, application_ref, full_name, mobile, email, loan_type, loan_amount, created_at FROM apply_loan_applications ORDER BY id DESC";
    $res = $conn->query($query);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            fputcsv($output, $row);
        }
    }
} elseif ($type === 'landing') {
    fputcsv($output, ['ID', 'Full Name', 'Mobile', 'Loan Type', 'Pincode', 'Created At']);
    $query = "SELECT id, full_name, mobile, loan_type, pincode, created_at FROM loan_leads ORDER BY id DESC";
    $res = $conn->query($query);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            fputcsv($output, $row);
        }
    }
} elseif ($type === 'cibil') {
    fputcsv($output, ['ID', 'Full Name', 'Mobile', 'Email', 'PAN', 'Score', 'Checked At']);
    $query = "SELECT id, full_name, mobile, email, pan, score, created_at FROM cibil_reports ORDER BY id DESC";
    $res = $conn->query($query);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            fputcsv($output, $row);
        }
    }
} elseif ($type === 'partners') {
    fputcsv($output, ['ID', 'Full Name', 'Email / Mobile', 'Status', 'Registered At']);
    $query = "SELECT id, full_name, email_or_mobile, status, created_at FROM partner_registrations ORDER BY id DESC";
    $res = $conn->query($query);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            fputcsv($output, $row);
        }
    }
}

fclose($output);
exit;
