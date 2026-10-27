<?php
declare(strict_types=1);

/**
 * Digitap Bank Data - Retrieve Report Endpoint
 * Endpoint: /api/bank-data/report.php
 * Powered by Digitap Bank Data UI API v1.21
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/DigitapBankDataUIService.php';

$rawInput = file_get_contents('php://input');
$jsonBody = json_decode((string)$rawInput, true) ?: [];

$txnId = trim((string)($_GET['txn_id'] ?? $jsonBody['txn_id'] ?? $_POST['txn_id'] ?? ''));
$reportType = strtolower(trim((string)($_GET['format'] ?? $jsonBody['report_type'] ?? 'json')));
$reportSubtype = trim((string)($_GET['subtype'] ?? $jsonBody['report_subtype'] ?? 'type3'));

if (empty($txnId)) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'txn_id is required'
    ]);
    exit;
}

$localReportFile = dirname(__DIR__, 2) . '/storage/reports/report_' . $txnId . '.json';
if (file_exists($localReportFile) && empty($_GET['force_refresh'])) {
    $cached = json_decode((string)file_get_contents($localReportFile), true);
    if (!empty($cached)) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($cached, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

$service = new DigitapBankDataUIService();
$res = $service->retrieveReport($txnId, $reportType, $reportSubtype);

if (!empty($res['format']) && $res['format'] === 'xlsx') {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="bank_report_' . $txnId . '.xlsx"');
    echo $res['data'];
    exit;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
exit;
