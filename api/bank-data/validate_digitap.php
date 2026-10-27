<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/DigitapBankDataUIService.php';

header('Content-Type: application/json');

$url = 'https://svcprod.digitap.work/bank-data/generateurl';
$auth = base64_encode('53205998:4lBQRBHkJms0jLy7oTtnEuhEgofOGnwu');

$startDate = date('Y-m-d', strtotime('-6 months'));
$endDate = date('Y-m-d');

$variations = [
    // Format 1: Exact as Digitap Team sent
    'Team_Format_1' => [
        'client_ref_num' => 'WF-AA-' . time() . '-1',
        'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
        'destination' => 'accountaggregator',
        'return_url' => 'https://waqtfinance.com/create-lead-form?step=5&aa_txn_id=%s&aa_status=%s',
        'mobile_num' => '9810123456',
        'multi_aa' => 0,
        'aa_vendor' => '',
        'aa_fetch_type' => '',
        'consent_request' => [
            [
                'fetch_type' => 'ONETIME',
                'fi_types' => ['DEPOSIT'],
                'fi_date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]
        ]
    ],
    // Format 2: With aa_vendor = anumati, fetch_type = ONETIME
    'Team_Format_2' => [
        'client_ref_num' => 'WF-AA-' . time() . '-2',
        'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
        'destination' => 'accountaggregator',
        'return_url' => 'https://waqtfinance.com/create-lead-form?step=5&aa_txn_id=%s&aa_status=%s',
        'mobile_num' => '9810123456',
        'multi_aa' => 0,
        'aa_vendor' => 'anumati',
        'aa_fetch_type' => 'ONETIME',
        'consent_request' => [
            [
                'fetch_type' => 'ONETIME',
                'fi_types' => ['DEPOSIT'],
                'fi_date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]
        ]
    ],
    // Format 3: Exactly as sent with empty strings for aa_vendor and aa_fetch_type and fetch_type = "ONETIME"
    'Team_Format_3' => [
        'client_ref_num' => 'WF-AA-' . time() . '-3',
        'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
        'destination' => 'accountaggregator',
        'return_url' => 'https://waqtfinance.com/create-lead-form?step=5&aa_txn_id=%s&aa_status=%s',
        'mobile_num' => '9810123456',
        'multi_aa' => 0,
        'aa_vendor' => '',
        'aa_fetch_type' => '',
        'consent_request' => [
            [
                'fetch_type' => 'ONETIME',
                'fi_types' => ['DEPOSIT'],
                'fi_date_range' => [
                    'start_date' => '2026-04-01',
                    'end_date' => '2026-10-27'
                ]
            ]
        ]
    ]
];

$results = [];

foreach ($variations as $k => $p) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . $auth,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($p));
    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $results[$k] = [
        'http_code' => $httpCode,
        'sent' => $p,
        'resp' => json_decode($resp, true) ?: $resp
    ];
}

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
