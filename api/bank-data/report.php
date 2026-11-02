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

$clientIp = function_exists('waqt_client_ip') ? waqt_client_ip() : ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
if (function_exists('waqt_enforce_rate_limit')) {
    waqt_enforce_rate_limit('bank_data_report_ip', $clientIp, 30, 600, 'Report retrieval rate limit reached. Please wait a few minutes.');
}

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

if (!empty($res) && !empty($res['banks'])) {
    $storageDir = dirname(__DIR__, 2) . '/storage/reports';
    if (!is_dir($storageDir)) {
        @mkdir($storageDir, 0755, true);
    }
    @file_put_contents($localReportFile, json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

if (!empty($res['format']) && $res['format'] === 'xlsx') {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="bank_report_' . $txnId . '.xlsx"');
    echo $res['data'];
    exit;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
exit;
