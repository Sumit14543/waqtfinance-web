<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/DigitapBankDataUIService.php';

header('Content-Type: application/json; charset=utf-8');

$clientId = env_value('DIGITAP_CLIENT_ID', '53205998');
$clientSecret = env_value('DIGITAP_CLIENT_SECRET', '4lBQRBHkJms0jLy7oTtnEuhEgofOGnwu');
$authHeader = 'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret);
$apiUrl = 'https://svc.digitap.ai/bank-data/generateurl';

$tests = [];

// Variation 1: Strict ONETIME with consent_request (Page 60-61)
$tests['V1_consent_request_short'] = [
    'client_ref_num' => 'WF_T1_' . time(),
    'destination' => 'accountaggregator',
    'mobile_num' => '9876543210',
    'aa_vendor' => 'anumati',
    'consent_request' => [
        [
            'fi_types' => ['DEPOSIT'],
            'fetch_type' => 'ONETIME'
        ]
    ],
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&aa_txn_id=%s&aa_status=%s'
];

// Variation 2: Full consent_request (Page 18 & 59)
$tests['V2_consent_request_full'] = [
    'client_ref_num' => 'WF_T2_' . time(),
    'destination' => 'accountaggregator',
    'mobile_num' => '9876543210',
    'aa_vendor' => 'anumati',
    'consent_request' => [
        [
            'consent' => [
                'expiry' => date('Y-m-d', strtotime('+30 days')),
                'mode' => 'VIEW',
                'types' => ['TRANSACTIONS', 'PROFILE', 'SUMMARY']
            ],
            'fetch_type' => 'ONETIME',
            'purpose' => [
                'code' => '103',
                'text' => 'Loan Underwriting'
            ],
            'fi_types' => ['DEPOSIT']
        ]
    ],
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&aa_txn_id=%s&aa_status=%s'
];

// Variation 3: Destination = 'choice' (Shows NetBanking, Statement Upload, and AA together)
$tests['V3_destination_choice'] = [
    'client_ref_num' => 'WF_T3_' . time(),
    'destination' => 'choice',
    'mobile_num' => '9876543210',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&aa_txn_id=%s&aa_status=%s',
    'acceptance_policy' => 'atLeastOneTransactionInRange'
];

// Variation 4: Destination = 'accountaggregator' without aa_vendor or aa_fetch_type
$tests['V4_aa_minimal'] = [
    'client_ref_num' => 'WF_T4_' . time(),
    'destination' => 'accountaggregator',
    'mobile_num' => '9876543210',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&aa_txn_id=%s&aa_status=%s'
];

// Variation 5: aa_fetch_type = 'ONETIME' (Uppercase)
$tests['V5_aa_fetch_type_ONETIME'] = [
    'client_ref_num' => 'WF_T5_' . time(),
    'destination' => 'accountaggregator',
    'mobile_num' => '9876543210',
    'aa_vendor' => 'anumati',
    'aa_fetch_type' => 'ONETIME',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&aa_txn_id=%s&aa_status=%s'
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
