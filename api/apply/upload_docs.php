<?php
declare(strict_types=1);

require __DIR__ . '/../../config/db.php';

start_secure_session();
require_post();
require_valid_csrf();

try {
    $appId = (string) ($_SESSION['loan_app_id'] ?? '');
    
    if ($appId === '' || !($_SESSION['loan_app_verified'] ?? false)) {
        json_response(['status' => 'error', 'message' => 'Unauthorized or session expired.'], 401);
    }

    $conn = db();
    $updates = [];
    $types = '';
    $params = [];

    // Helper for file uploads
    $handleFile = function($key, $dbField) use (&$updates, &$types, &$params, $appId) {
        if (isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK) {
            $path = waqt_save_private_uploaded_file($_FILES[$key], 'loan_docs/' . $appId, $key, [
                'application/pdf' => 'pdf',
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            ], 5 * 1024 * 1024);
            $updates[] = "$dbField = ?";
            $types .= "s";
            $params[] = $path;
        }
    };

    $handleFile('current_salary_slip', 'salary_slip_current');
    $handleFile('previous_salary_slip', 'salary_slip_previous');
    $handleFile('old_salary_slip', 'salary_slip_old');
    $handleFile('company_id', 'company_id_card');

    // Handle Selfie (Base64)
    if (isset($_POST['selfie_image']) && strpos($_POST['selfie_image'], 'data:image') === 0) {
        $path = waqt_save_private_base64_image((string) $_POST['selfie_image'], 'loan_docs/' . $appId, 'selfie', 4 * 1024 * 1024);
        $updates[] = "selfie_image = ?";
        $types .= "s";
        $params[] = $path;
    }

    if (!empty($updates)) {
        $updates[] = "status = 'submitted'";
        $updates[] = "updated_at = NOW()";
        $sql = "UPDATE loan_applications SET " . implode(', ', $updates) . " WHERE application_id = ?";
        $types .= "s";
        $params[] = $appId;

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    }

    // Clear session after submission but keep ID for status page
    $_SESSION['loan_app_id_final'] = $appId;
    unset($_SESSION['loan_app_id'], $_SESSION['loan_app_verified'], $_SESSION['mobile_number']);

    json_response(['status' => 'success', 'message' => 'Application submitted successfully.']);

} catch (Throwable $e) {
    report_exception($e);
}
