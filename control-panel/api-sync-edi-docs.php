<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

// Auth check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    json_response(['status' => 'error', 'message' => 'Unauthorized access.'], 401);
}

$appId = trim((string) ($_GET['app_id'] ?? ''));

if ($appId === '') {
    json_response(['status' => 'error', 'message' => 'Application ID is required.'], 400);
}

require_once __DIR__ . '/../includes/crm_integration.php';

try {
    $conn = db();
    
    // Check if the application exists
    $stmt = $conn->prepare("SELECT id FROM edi_loan_applications WHERE application_id = ? LIMIT 1");
    if (!$stmt) {
        throw new Exception("Database prepare failed.");
    }
    $stmt->bind_param("s", $appId);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$exists) {
        json_response(['status' => 'error', 'message' => 'EDI Application not found in database.'], 404);
    }
    
    // Trigger push
    $res = push_common_lead_to_crm($conn, 'edi', $appId);
    
    if (($res['status'] ?? '') === 'success') {
        $uploaded = $res['documents']['uploaded'] ?? 0;
        $failed = $res['documents']['failed'] ?? 0;
        
        if ($failed > 0) {
            json_response([
                'status' => 'warning',
                'message' => "Lead verified. Sync complete: $uploaded files successfully imported, but $failed files failed. Please check GCS/CRM availability."
            ]);
        } else {
            json_response([
                'status' => 'success',
                'message' => "Success! All $uploaded documents successfully synced with the CRM."
            ]);
        }
    } else {
        json_response([
            'status' => 'error',
            'message' => $res['message'] ?? 'CRM API returned an error during sync.'
        ]);
    }
} catch (Throwable $e) {
    error_log("Manual CRM Document Sync Fail ($appId): " . $e->getMessage());
    json_response([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ], 500);
}
?>
