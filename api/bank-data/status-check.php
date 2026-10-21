<?php
declare(strict_types=1);

/**
 * Digitap Bank Data - Status Check Endpoint
 * Endpoint: /api/bank-data/status-check.php
 * Powered by Digitap Bank Data UI API v1.21
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/DigitapBankDataUIService.php';

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rawInput = file_get_contents('php://input');
$jsonBody = json_decode((string)$rawInput, true) ?: [];
$requestId = trim((string)($_GET['request_id'] ?? $jsonBody['request_id'] ?? $_POST['request_id'] ?? ($_SESSION['digitap_aa_request_id'] ?? '')));

if (empty($requestId)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'request_id is required'
    ]);
    exit;
}

$service = new DigitapBankDataUIService();
$res = $service->checkStatus($requestId);

echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
exit;
