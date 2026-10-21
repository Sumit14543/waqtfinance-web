<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/db.php';
header('Content-Type: application/json; charset=utf-8');

$clientId = env_value('DIGITAP_CLIENT_ID', '53205998');
$clientSecret = env_value('DIGITAP_CLIENT_SECRET', '4lBQRBHkJms0jLy7oTtnEuhEgofOGnwu');
$authHeader = 'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret);
$apiUrl = 'https://svc.digitap.ai/bank-data/generateurl';

$tests = [];

// 1. Exact Page 11-12 section 4.3.3 payload with different aa_vendor values
$vendors = ['anumati', 'finvu', 'setu', 'onemoney', 'dashboard-aa-preprod', 'saafe'];
foreach ($vendors as $v) {
    $tests['P12_Vendor_' . $v] = [
        'client_ref_num' => 'WF-AA-' . time() . '-' . substr($v, 0, 3),
        'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
        'destination' => 'accountaggregator',
        'acceptance_policy' => 'atLeastOneTransactionInRange',
        'return_url' => 'https://waqtfinance.com/create-lead-form.php?step=5&aa_txn_id=%s&aa_status=%s',
        'mobile_num' => '9876543210',
        'aa_vendor' => $v,
        'aa_fetch_type' => 'onetime'
    ];
}

// 2. Section 4.3.3 with multi_aa: "true"
$tests['P12_MultiAA_true'] = [
    'client_ref_num' => 'WF-AA-' . time() . '-maa',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'destination' => 'accountaggregator',
    'multi_aa' => 'true',
    'return_url' => 'https://waqtfinance.com/create-lead-form.php?step=5&aa_txn_id=%s&aa_status=%s',
    'mobile_num' => '9876543210',
    'aa_fetch_type' => 'onetime'
];

// 3. Section 4.3.3 without mobile_num (user enters mobile on AA screen)
foreach (['anumati', 'finvu', 'setu'] as $v) {
    $tests['P12_NoMobile_' . $v] = [
        'client_ref_num' => 'WF-AA-' . time() . '-nm-' . $v,
        'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
        'destination' => 'accountaggregator',
        'return_url' => 'https://waqtfinance.com/create-lead-form.php?step=5&aa_txn_id=%s&aa_status=%s',
        'aa_vendor' => $v,
        'aa_fetch_type' => 'onetime'
    ];
}

// 4. With institution_id: "1" (HDFC)
$tests['P12_With_HDFC_Inst1'] = [
    'client_ref_num' => 'WF-AA-' . time() . '-hdfc',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'destination' => 'accountaggregator',
    'institution_id' => '1',
    'mobile_num' => '9876543210',
    'aa_vendor' => 'anumati',
    'aa_fetch_type' => 'onetime',
    'return_url' => 'https://waqtfinance.com/create-lead-form.php?step=5&aa_txn_id=%s&aa_status=%s'
];

// 5. Page 60 table with different fetch_type (PERIODIC / BOTH)
$tests['P60_Table_PERIODIC'] = [
    'client_ref_num' => 'WF-AA-' . time() . '-per',
    'destination' => 'accountaggregator',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create-lead-form.php?step=5&aa_txn_id=%s&aa_status=%s',
    'consent_request' => [
        [
            'fi_types' => ['DEPOSIT'],
            'fetch_type' => 'PERIODIC'
        ]
    ]
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
