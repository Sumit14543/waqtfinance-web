<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/db.php';
header('Content-Type: application/json; charset=utf-8');

$clientId = env_value('DIGITAP_CLIENT_ID', '53205998');
$clientSecret = env_value('DIGITAP_CLIENT_SECRET', '4lBQRBHkJms0jLy7oTtnEuhEgofOGnwu');
$authHeader = 'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret);
$apiUrl = 'https://svc.digitap.ai/bank-data/generateurl';

$tests = [];

$today = date('Y-m-d');
$expiry15 = date('Y-m-d', strtotime('+15 days'));
$start6m = date('Y-m-d', strtotime('-6 months'));

// Test A1: STORE mode, purpose 103, data_life 1 month, no filters
$tests['A1_Store_103'] = [
    'client_ref_num' => 'WF-A1-' . time(),
    'destination' => 'accountaggregator',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&txn=%s&st=%s',
    'consent_request' => [
        [
            'consent' => [
                'expiry' => $expiry15,
                'mode' => 'STORE',
                'types' => ['TRANSACTIONS', 'PROFILE', 'SUMMARY']
            ],
            'fetch_type' => 'ONETIME',
            'purpose' => [
                'code' => '103',
                'text' => "To process borrower's (loan / credit card / credit line) application"
            ],
            'fi_types' => ['DEPOSIT'],
            'fi_date_range' => [
                'start_date' => $start6m,
                'end_date' => $today
            ],
            'data_life' => ['unit' => 'MONTH', 'value' => 1],
            'frequency' => ['unit' => 'DAY', 'value' => 1]
        ]
    ]
];

// Test A2: VIEW mode, data_life INF/0
$tests['A2_View_103'] = [
    'client_ref_num' => 'WF-A2-' . time(),
    'destination' => 'accountaggregator',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&txn=%s&st=%s',
    'consent_request' => [
        [
            'consent' => [
                'expiry' => $expiry15,
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
                'start_date' => $start6m,
                'end_date' => $today
            ],
            'data_life' => ['unit' => 'DAY', 'value' => 0],
            'frequency' => ['unit' => 'DAY', 'value' => 1]
        ]
    ]
];

// Test A3: purpose code ONLY (no text)
$tests['A3_PurposeCodeOnly'] = [
    'client_ref_num' => 'WF-A3-' . time(),
    'destination' => 'accountaggregator',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&txn=%s&st=%s',
    'consent_request' => [
        [
            'consent' => [
                'expiry' => $expiry15,
                'mode' => 'STORE',
                'types' => ['TRANSACTIONS', 'PROFILE', 'SUMMARY']
            ],
            'fetch_type' => 'ONETIME',
            'purpose' => ['code' => '103'],
            'fi_types' => ['DEPOSIT'],
            'fi_date_range' => [
                'start_date' => $start6m,
                'end_date' => $today
            ],
            'data_life' => ['unit' => 'MONTH', 'value' => 1],
            'frequency' => ['unit' => 'DAY', 'value' => 1]
        ]
    ]
];

// Test A4: Using start_month and end_month inside fi_date_range
$tests['A4_MonthRange'] = [
    'client_ref_num' => 'WF-A4-' . time(),
    'destination' => 'accountaggregator',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&txn=%s&st=%s',
    'consent_request' => [
        [
            'consent' => [
                'expiry' => $expiry15,
                'mode' => 'STORE',
                'types' => ['TRANSACTIONS', 'PROFILE', 'SUMMARY']
            ],
            'fetch_type' => 'ONETIME',
            'purpose' => ['code' => '103'],
            'fi_types' => ['DEPOSIT'],
            'fi_date_range' => [
                'start_month' => date('Y-m', strtotime('-6 months')),
                'end_month' => date('Y-m')
            ],
            'data_life' => ['unit' => 'MONTH', 'value' => 1],
            'frequency' => ['unit' => 'DAY', 'value' => 1]
        ]
    ]
];

// Test A5: Sahamati CT001 specification format
$tests['A5_Sahamati_103'] = [
    'client_ref_num' => 'WF-A5-' . time(),
    'destination' => 'accountaggregator',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&txn=%s&st=%s',
    'consent_request' => [
        [
            'consent' => [
                'expiry' => date('Y-m-d\TH:i:s\Z', strtotime('+30 days')),
                'mode' => 'STORE',
                'types' => ['TRANSACTIONS', 'PROFILE', 'SUMMARY']
            ],
            'fetch_type' => 'ONETIME',
            'purpose' => [
                'code' => '103',
                'text' => "To process borrower's (loan / credit card / credit line) application"
            ],
            'fi_types' => ['DEPOSIT'],
            'fi_date_range' => [
                'start_date' => date('Y-m-d\TH:i:s\Z', strtotime('-6 months')),
                'end_date' => date('Y-m-d\TH:i:s\Z')
            ],
            'data_life' => ['unit' => 'MONTH', 'value' => 1],
            'frequency' => ['unit' => 'DAY', 'value' => 1]
        ]
    ]
];

// Test A6: Without 'consent' wrapper (flat fields)
$tests['A6_Flat_Fields'] = [
    'client_ref_num' => 'WF-A6-' . time(),
    'destination' => 'accountaggregator',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&txn=%s&st=%s',
    'consent_request' => [
        [
            'expiry' => $expiry15,
            'mode' => 'STORE',
            'types' => ['TRANSACTIONS', 'PROFILE', 'SUMMARY'],
            'fetch_type' => 'ONETIME',
            'purpose_code' => '103',
            'fi_types' => ['DEPOSIT'],
            'start_date' => $start6m,
            'end_date' => $today
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
