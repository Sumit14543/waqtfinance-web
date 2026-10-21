<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/db.php';
header('Content-Type: application/json; charset=utf-8');

$clientId = env_value('DIGITAP_CLIENT_ID', '53205998');
$clientSecret = env_value('DIGITAP_CLIENT_SECRET', '4lBQRBHkJms0jLy7oTtnEuhEgofOGnwu');
$authHeader = 'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret);
$apiUrl = 'https://svc.digitap.ai/bank-data/generateurl';

$tests = [];

// 1. No destination specified
$tests['No_Destination'] = [
    'client_ref_num' => 'WF-ND-' . time(),
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&txn=%s&st=%s'
];

// 2. destination: statementupload
$tests['StatementUpload'] = [
    'client_ref_num' => 'WF-SU-' . time(),
    'destination' => 'statementupload',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&txn=%s&st=%s'
];

// 3. AA with multi_aa: "true" (page 12: ii) For Multi AA Feature One more key is added in the payload i.e "multi_aa":"true")
$tests['AA_With_Multi_AA'] = [
    'client_ref_num' => 'WF-MAA-' . time(),
    'destination' => 'accountaggregator',
    'multi_aa' => 'true',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&txn=%s&st=%s'
];

// 4. AA with institution_id specified (e.g. HDFC = 1, SBI = 2)
$tests['AA_With_Institution_1'] = [
    'client_ref_num' => 'WF-AAI-' . time(),
    'destination' => 'accountaggregator',
    'institution_id' => '1',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&txn=%s&st=%s'
];

// 5. AA with aa_vendor: dashboard-aa-preprod (Page 12: i) For SAAFE environment access, set the value of aa_vendor to "dashboard-aa-preprod")
$tests['AA_Saafe_Preprod'] = [
    'client_ref_num' => 'WF-SAF-' . time(),
    'destination' => 'accountaggregator',
    'aa_vendor' => 'dashboard-aa-preprod',
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
