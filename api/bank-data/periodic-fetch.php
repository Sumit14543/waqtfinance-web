<?php
declare(strict_types=1);

/**
 * Digitap AA Periodic Bank Data Management & Test Controller
 * Endpoint: /api/bank-data/periodic-fetch.php
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/DigitapAAPeriodicService.php';

header('Content-Type: application/json; charset=utf-8');

$rawInput = file_get_contents('php://input');
$jsonBody = json_decode((string)$rawInput, true) ?: [];

$action = trim((string)($_GET['action'] ?? $jsonBody['action'] ?? $_POST['action'] ?? 'initiate'));
$mainTxnId = trim((string)($_GET['main_txn_id'] ?? $jsonBody['main_txn_id'] ?? $_POST['main_txn_id'] ?? ''));
$txnId = trim((string)($_GET['txn_id'] ?? $jsonBody['txn_id'] ?? $_POST['txn_id'] ?? ''));
$requestId = trim((string)($_GET['request_id'] ?? $jsonBody['request_id'] ?? $_POST['request_id'] ?? ''));

$service = new DigitapAAPeriodicService();

switch ($action) {
    case 'initiate':
        if (empty($mainTxnId)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'main_txn_id is required']);
            exit;
        }
        $res = $service->initiatePeriodicFetch($mainTxnId);
        echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        break;

    case 'status':
        if (empty($txnId)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'txn_id is required']);
            exit;
        }
        $res = $service->checkStatus($txnId, $requestId);
        echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        break;

    case 'report':
        if (empty($txnId)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'txn_id is required']);
            exit;
        }
        $res = $service->retrieveReport($txnId);
        if (!empty($res['format']) && $res['format'] === 'xlsx') {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="bank_report_' . $txnId . '.xlsx"');
            echo $res['data'];
            exit;
        }
        echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        break;

    default:
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid action. Supported actions: initiate, status, report',
            'usage' => [
                'initiate' => '/api/bank-data/periodic-fetch.php?action=initiate&main_txn_id=da...',
                'status' => '/api/bank-data/periodic-fetch.php?action=status&txn_id=apf...',
                'report' => '/api/bank-data/periodic-fetch.php?action=report&txn_id=apf...'
            ]
        ]);
        break;
}
exit;
