<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/db.php';
header('Content-Type: application/json; charset=utf-8');

$clientId = env_value('DIGITAP_CLIENT_ID', '53205998');
$clientSecret = env_value('DIGITAP_CLIENT_SECRET', '4lBQRBHkJms0jLy7oTtnEuhEgofOGnwu');
$authHeader = 'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret);
$apiUrl = 'https://svc.digitap.ai/bank-data/generateurl';

$tests = [];

// 1. Choice without institution_id
$tests['Choice'] = [
    'client_ref_num' => 'WF-CH-' . time(),
    'destination' => 'choice',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&txn=%s&st=%s'
];

// 2. What about AA with different vendors: "finvu", "setu", "onemoney", "nadl", "cookie"?
// What if aa_vendor is specified WITH mobile_num and aa_fetch_type, but with start_month & end_month?
$tests['AA_With_Months'] = [
    'client_ref_num' => 'WF-AAM-' . time(),
    'destination' => 'accountaggregator',
    'mobile_num' => '9876543210',
    'aa_vendor' => 'finvu',
    'aa_fetch_type' => 'onetime',
    'start_month' => date('Y-m', strtotime('-6 months')),
    'end_month' => date('Y-m'),
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&txn=%s&st=%s'
];

// 3. What if aa_fetch_type is UPPERCASE ONETIME?
$tests['AA_With_Months_Upper'] = [
    'client_ref_num' => 'WF-AAMU-' . time(),
    'destination' => 'accountaggregator',
    'mobile_num' => '9876543210',
    'aa_vendor' => 'finvu',
    'aa_fetch_type' => 'ONETIME',
    'start_month' => date('Y-m', strtotime('-6 months')),
    'end_month' => date('Y-m'),
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&txn=%s&st=%s'
];

$results = [];
foreach ($tests as $key => $payload) {
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_SLASHES));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        $authHeader,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $results[$key] = [
        'http_code' => $code,
        'response' => json_decode((string)$res, true) ?: $res
    ];
}

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
