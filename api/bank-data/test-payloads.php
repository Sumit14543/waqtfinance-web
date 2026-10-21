<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/db.php';

header('Content-Type: application/json; charset=utf-8');

$clientId = env_value('DIGITAP_CLIENT_ID', '53205998');
$clientSecret = env_value('DIGITAP_CLIENT_SECRET', '4lBQRBHkJms0jLy7oTtnEuhEgofOGnwu');
$authHeader = 'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret);
$apiUrl = 'https://svc.digitap.ai/bank-data/generateurl';

$tests = [];

// Test 1: Exact Page 59 Consent Request with dash-only client_ref_num
$tests['Page59_Exact'] = [
    'client_ref_num' => 'WF-AA-' . time() . '-101',
    'destination' => 'accountaggregator',
    'mobile_num' => '9876543210',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&aa_txn_id=%s&aa_status=%s',
    'consent_request' => [
        [
            'consent' => [
                'expiry' => date('Y-m-d\TH:i:s', strtotime('+30 days')),
                'mode' => 'VIEW',
                'types' => ['TRANSACTIONS', 'PROFILE', 'SUMMARY']
            ],
            'fetch_type' => 'ONETIME',
            'purpose' => [
                'code' => '103',
                'text' => 'Aggregated statement'
            ],
            'fi_date_range' => [
                'start_date' => date('Y-m-d', strtotime('-6 months')),
                'end_date' => date('Y-m-d')
            ],
            'data_life' => [
                'unit' => 'MONTH',
                'value' => 4
            ],
            'frequency' => [
                'unit' => 'DAY',
                'value' => 1
            ]
        ]
    ]
];

// Test 2: Destination statementupload with clean client_ref_num
$tests['StatementUpload_Clean'] = [
    'client_ref_num' => 'WF-ST-' . time() . '-102',
    'destination' => 'statementupload',
    'start_month' => date('Y-m', strtotime('-6 months')),
    'end_month' => date('Y-m'),
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&aa_txn_id=%s&aa_status=%s',
    'acceptance_policy' => 'atLeastOneTransactionInRange'
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $results[$key] = [
        'http_code' => $code,
        'response' => json_decode((string)$res, true) ?: $res
    ];
}

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
