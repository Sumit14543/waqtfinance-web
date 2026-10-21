<?php
declare(strict_types=1);

/**
 * Digitap AA Periodic Bank Data Webhook Receiver
 * Endpoint: /api/bank-data/webhook.php
 * Powered by Digitap.ai Bank Data AA Periodic API v1.0
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/DigitapAAPeriodicService.php';

header('Content-Type: application/json; charset=utf-8');

$rawInput = file_get_contents('php://input');
$json = json_decode((string)$rawInput, true);

// Header check: x-digitap-callback-type: TRANSACTION_COMPLETE
$callbackType = $_SERVER['HTTP_X_DIGITAP_CALLBACK_TYPE'] ?? '';

// Ensure storage log directory
$storageDir = dirname(__DIR__, 2) . '/storage';
if (!is_dir($storageDir . '/logs')) {
    @mkdir($storageDir . '/logs', 0755, true);
}
if (!is_dir($storageDir . '/reports')) {
    @mkdir($storageDir . '/reports', 0755, true);
}

// Log incoming webhook
@file_put_contents(
    $storageDir . '/logs/aa_periodic_webhooks.log',
    date('Y-m-d H:i:s') . ' [' . ($callbackType ?: 'NO_HEADER') . '] ' . $rawInput . PHP_EOL,
    FILE_APPEND | LOCK_EX
);

if (!is_array($json) || empty($json['txn_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid webhook payload']);
    exit;
}

$txnId = (string)$json['txn_id'];
$mainTxnId = (string)($json['main_txn_id'] ?? '');
$status = (string)($json['status'] ?? '');
$code = (string)($json['code'] ?? '');

// If report is generated, automatically retrieve and archive it
if ($status === 'Success' && $code === 'ReportGenerated') {
    try {
        $service = new DigitapAAPeriodicService();
        $report = $service->retrieveReport($txnId);

        if (!empty($report['format']) && $report['format'] === 'xlsx') {
            $filePath = $storageDir . '/reports/bank_aa_' . $txnId . '.xlsx';
            @file_put_contents($filePath, $report['data']);
        } else {
            $filePath = $storageDir . '/reports/bank_aa_' . $txnId . '.json';
            @file_put_contents($filePath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        // Update database if loan application exists with this main_txn_id
        if (!empty($mainTxnId)) {
            try {
                $conn = db();
                if (loan_flow_column_exists($conn, 'loan_applications', 'aa_main_txn_id')) {
                    $stmt = $conn->prepare("UPDATE loan_applications SET aa_periodic_status = 'ReportGenerated', aa_last_report_path = ?, aa_last_pulled_at = NOW() WHERE aa_main_txn_id = ?");
                    if ($stmt) {
                        $stmt->bind_param('ss', $filePath, $mainTxnId);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            } catch (Throwable $dbEx) {
                error_log("AA Webhook DB notice: " . $dbEx->getMessage());
            }
        }
    } catch (Throwable $e) {
        error_log("AA Webhook report retrieval error: " . $e->getMessage());
    }
}

// Return HTTP 200 acknowledge
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'message' => 'Webhook received and processed successfully',
    'txn_id' => $txnId
]);
exit;
