<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/db.php';
header('Content-Type: application/json; charset=utf-8');

$clientId = env_value('DIGITAP_CLIENT_ID', '53205998');
$clientSecret = env_value('DIGITAP_CLIENT_SECRET', '4lBQRBHkJms0jLy7oTtnEuhEgofOGnwu');
$authHeader = 'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret);

$results = [];

// Test DEMO Env with accountaggregator
$ch = curl_init('https://svcdemo.digitap.work/bank-data/generateurl');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'client_ref_num' => 'WF-DEMO-AA-' . time(),
    'destination' => 'accountaggregator',
    'mobile_num' => '9876543210',
    'aa_vendor' => 'anumati',
    'aa_fetch_type' => 'onetime',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&txn=%s&st=%s'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [$authHeader, 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$res = curl_exec($ch);
$results['Demo_Env_AA'] = [
    'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
    'response' => json_decode((string)$res, true) ?: $res
];
curl_close($ch);

// Test DEMO Env with statementupload
$ch = curl_init('https://svcdemo.digitap.work/bank-data/generateurl');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'client_ref_num' => 'WF-DEMO-STMT-' . time(),
    'destination' => 'statementupload',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&txn=%s&st=%s'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [$authHeader, 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$res = curl_exec($ch);
$results['Demo_Env_Statement'] = [
    'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
    'response' => json_decode((string)$res, true) ?: $res
];
curl_close($ch);

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
