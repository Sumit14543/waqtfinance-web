<?php
declare(strict_types=1);

/**
 * Endpoint to look up or resolve AA statement report by Txn ID, Mobile, Account or Lead ID.
 * Endpoint: /api/bank-data/find-report.php
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/aa_report_helper.php';

header('Content-Type: application/json; charset=utf-8');

$txnId = trim((string)($_GET['txn_id'] ?? $_POST['txn_id'] ?? ''));
$mobile = trim((string)($_GET['mobile'] ?? $_POST['mobile'] ?? ''));
$account = trim((string)($_GET['account'] ?? $_POST['account'] ?? ''));
$name = trim((string)($_GET['name'] ?? $_POST['name'] ?? ''));
$leadId = trim((string)($_GET['lead_id'] ?? $_POST['lead_id'] ?? ''));

// If direct txnId is provided, attempt standard fetch
if (!empty($txnId)) {
    $localReportFile = dirname(__DIR__, 2) . '/storage/reports/report_' . $txnId . '.json';
    if (file_exists($localReportFile)) {
        $data = json_decode((string)file_get_contents($localReportFile), true);
        if (!empty($data) && !empty($data['banks'])) {
            echo json_encode([
                'status' => 'success',
                'txn_id' => $txnId,
                'report' => $data
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    try {
        $service = new DigitapBankDataUIService();
        $data = $service->retrieveReport($txnId, 'json', 'type3');
        if (!empty($data) && !empty($data['banks'])) {
            $storageDir = dirname(__DIR__, 2) . '/storage/reports';
            if (!is_dir($storageDir)) {
                @mkdir($storageDir, 0755, true);
            }
            @file_put_contents($localReportFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            echo json_encode([
                'status' => 'success',
                'txn_id' => $txnId,
                'report' => $data
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        }
    } catch (Throwable $e) {}
}

// Fallback: Resolve via helper
$res = waqt_resolve_aa_report($conn, $mobile, $account, $name, $leadId);

if ($res['status'] !== 'success') {
    http_response_code(404);
}

echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
exit;
