<?php
declare(strict_types=1);

/**
 * Digitap Bank Data - Generate URL Endpoint
 * Endpoint: /api/bank-data/generate-url.php
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
$data = !empty($jsonBody) ? $jsonBody : $_POST;

$mobile = preg_replace('/\D/', '', (string)($data['mobile'] ?? $data['mobile_num'] ?? ''));
$destination = trim((string)($data['destination'] ?? 'accountaggregator'));
$clientRefNum = 'WF-AA-' . time() . '-' . substr(md5(($mobile ?: 'AA') . mt_rand()), 0, 6);

$genParams = [
    'client_ref_num' => $clientRefNum,
    'destination' => $destination
];

// Per Doc Page 16: mobile_num is Optional.
// If explicitly requested or non-empty and pass_mobile is not false:
$passMobile = isset($data['pass_mobile']) ? (bool)$data['pass_mobile'] : (!empty($mobile) && strlen($mobile) === 10);
if ($passMobile && !empty($mobile) && strlen($mobile) === 10) {
    $genParams['mobile_num'] = $mobile;
}

if (!empty($data['aa_vendor'])) {
    $genParams['aa_vendor'] = trim((string)$data['aa_vendor']);
}
if (!empty($data['aa_fetch_type'])) {
    $genParams['aa_fetch_type'] = trim((string)$data['aa_fetch_type']);
}
if (!empty($data['multi_aa'])) {
    $genParams['multi_aa'] = trim((string)$data['multi_aa']);
}
if (!empty($data['consent_request']) && is_array($data['consent_request'])) {
    $genParams['consent_request'] = $data['consent_request'];
}

$service = new DigitapBankDataUIService();
$res = $service->generateUrl($genParams);

// Log for debugging
@file_put_contents(__DIR__ . '/aa_debug.log', date('Y-m-d H:i:s') . ' | ' . json_encode($genParams) . ' | ' . json_encode($res) . "\n", FILE_APPEND);

// If successful, save in session for verification on return
if (!empty($res['status']) && $res['status'] === 'success') {
    $_SESSION['digitap_aa_request_id'] = $res['request_id'] ?? '';
    $_SESSION['digitap_aa_client_ref'] = $clientRefNum;
    $_SESSION['digitap_aa_mobile'] = $mobile;
}

echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
exit;
