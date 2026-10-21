<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/db.php';

header('Content-Type: application/json; charset=utf-8');

$clientId = env_value('DIGITAP_CLIENT_ID', '53205998');
$clientSecret = env_value('DIGITAP_CLIENT_SECRET', '4lBQRBHkJms0jLy7oTtnEuhEgofOGnwu');
$authHeader = 'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret);
$apiUrl = 'https://svc.digitap.ai/bank-data/generateurl';

$tests = [];

// Exact Specification Match from Page 18, 58, 59, 62
$tests['Spec_Compliant_V1'] = [
    'client_ref_num' => 'WF-AA-' . time() . '-001',
    'destination' => 'accountaggregator',
    'mobile_num' => '9876543210',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&aa_txn_id=%s&aa_status=%s',
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
                'text' => "To process borrower's (loan / credit card / credit line) application"
            ],
            'fi_types' => ['DEPOSIT'],
            'fi_date_range' => [
                'start_date' => date('Y-m-d', strtotime('-6 months')),
                'end_date' => date('Y-m-d')
            ],
            'data_life' => [
                'unit' => 'DAY',
                'value' => 0
            ],
            'frequency' => [
                'unit' => 'DAY',
                'value' => 1
            ]
        ]
    ]
];

// Test with aa_vendor anumati
$tests['Spec_With_Anumati'] = array_merge($tests['Spec_Compliant_V1'], [
    'client_ref_num' => 'WF-AA-' . time() . '-002',
    'aa_vendor' => 'anumati'
]);

// Test with aa_vendor finvu
$tests['Spec_With_Finvu'] = array_merge($tests['Spec_Compliant_V1'], [
    'client_ref_num' => 'WF-AA-' . time() . '-003',
    'aa_vendor' => 'finvu'
]);

// Test with aa_vendor setu
$tests['Spec_With_Setu'] = array_merge($tests['Spec_Compliant_V1'], [
    'client_ref_num' => 'WF-AA-' . time() . '-004',
    'aa_vendor' => 'setu'
]);

// Test with aa_vendor onemoney
$tests['Spec_With_OneMoney'] = array_merge($tests['Spec_Compliant_V1'], [
    'client_ref_num' => 'WF-AA-' . time() . '-005',
    'aa_vendor' => 'onemoney'
]);

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
