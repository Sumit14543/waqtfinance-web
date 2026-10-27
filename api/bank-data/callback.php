<?php
declare(strict_types=1);

/**
 * Digitap Bank Data - Webhook Callback Receiver
 * Endpoint: /api/bank-data/callback.php
 * Powered by Digitap Bank Data UI API v1.21 (Appendix A)
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/DigitapBankDataUIService.php';

header('Content-Type: application/json; charset=utf-8');

$rawInput = (string)file_get_contents('php://input');
$json = json_decode($rawInput, true);

// Header check per spec: x-digitap-callback-type
$callbackType = $_SERVER['HTTP_X_DIGITAP_CALLBACK_TYPE'] ?? 'TRANSACTION_COMPLETE';

// Ensure storage directories
$storageDir = dirname(__DIR__, 2) . '/storage';
if (!is_dir($storageDir . '/logs')) {
    @mkdir($storageDir . '/logs', 0755, true);
}
if (!is_dir($storageDir . '/reports')) {
    @mkdir($storageDir . '/reports', 0755, true);
}

// Log callback
@file_put_contents(
    $storageDir . '/logs/bank_data_callbacks.log',
    date('Y-m-d H:i:s') . ' [' . $callbackType . '] ' . $rawInput . PHP_EOL,
    FILE_APPEND | LOCK_EX
);

if (!is_array($json) || empty($json['txn_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid callback payload']);
    exit;
}

$txnId = (string)$json['txn_id'];
$status = (string)($json['status'] ?? '');
$code = (string)($json['code'] ?? '');
$clientRef = (string)($json['client_ref_num'] ?? '');

// Auto-retrieve report if successfully generated
if ($status === 'Success') {
    try {
        $service = new DigitapBankDataUIService();
        $report = $service->retrieveReport($txnId, 'json', 'type3');

        $reportPath = $storageDir . '/reports/report_' . $txnId . '.json';
        @file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // Attempt to update database if application exists
        if (!empty($clientRef)) {
            try {
                $conn = db();
                $stmt = $conn->prepare("UPDATE loan_applications SET aa_status = 'Success', aa_txn_id = ?, aa_report_file = ? WHERE application_id = ? OR client_ref_num = ?");
                if ($stmt) {
                    $stmt->bind_param('ssss', $txnId, $reportPath, $clientRef, $clientRef);
                    $stmt->execute();
                    $stmt->close();
                }
            } catch (Throwable $dbEx) {
                error_log("DB update notice: " . $dbEx->getMessage());
            }
        }
    } catch (Throwable $e) {
        error_log("Report auto-retrieval error: " . $e->getMessage());
    }
}

// Return HTTP 200 OK required by Digitap
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'message' => 'Callback acknowledged',
    'txn_id' => $txnId
]);
exit;
