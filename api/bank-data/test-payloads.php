<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/db.php';
header('Content-Type: application/json; charset=utf-8');

$clientId = env_value('DIGITAP_CLIENT_ID', '53205998');
$clientSecret = env_value('DIGITAP_CLIENT_SECRET', '4lBQRBHkJms0jLy7oTtnEuhEgofOGnwu');
$authHeader = 'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret);
$apiUrl = 'https://svc.digitap.ai/bank-data/generateurl';

$tests = [];

// 1. consent_request as associative OBJECT instead of array list
$tests['Consent_As_Object'] = [
    'client_ref_num' => 'WF-OBJ-' . time(),
    'destination' => 'accountaggregator',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&txn=%s&st=%s',
    'consent_request' => [
        'consent' => [
            'expiry' => date('Y-m-d', strtotime('+30 days')),
            'mode' => 'STORE',
            'types' => ['TRANSACTIONS', 'PROFILE', 'SUMMARY']
        ],
        'fetch_type' => 'ONETIME',
        'purpose' => ['code' => '103'],
        'fi_types' => ['DEPOSIT'],
        'fi_date_range' => [
            'start_date' => date('Y-m-d', strtotime('-6 months')),
            'end_date' => date('Y-m-d')
        ],
        'data_life' => ['unit' => 'MONTH', 'value' => 1],
        'frequency' => ['unit' => 'DAY', 'value' => 1]
    ]
];

// 2. purpose with "type" and "text"
$tests['Purpose_WithType'] = [
    'client_ref_num' => 'WF-PT-' . time(),
    'destination' => 'accountaggregator',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&txn=%s&st=%s',
    'consent_request' => [
        [
            'consent' => [
                'expiry' => date('Y-m-d', strtotime('+30 days')),
                'mode' => 'STORE',
                'types' => ['TRANSACTIONS', 'PROFILE', 'SUMMARY']
            ],
            'fetch_type' => 'ONETIME',
            'purpose' => [
                'code' => '103',
                'type' => 'Loan Underwriting',
                'text' => "To process borrower's (loan / credit card / credit line) application"
            ],
            'fi_types' => ['DEPOSIT'],
            'fi_date_range' => [
                'start_date' => date('Y-m-d', strtotime('-6 months')),
                'end_date' => date('Y-m-d')
            ],
            'data_life' => ['unit' => 'MONTH', 'value' => 1],
            'frequency' => ['unit' => 'DAY', 'value' => 1]
        ]
    ]
];

// 3. fi_types at root payload
$tests['FiTypes_At_Root'] = [
    'client_ref_num' => 'WF-FTR-' . time(),
    'destination' => 'accountaggregator',
    'fi_types' => ['DEPOSIT'],
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&txn=%s&st=%s',
    'consent_request' => [
        [
            'consent' => [
                'expiry' => date('Y-m-d', strtotime('+30 days')),
                'mode' => 'STORE',
                'types' => ['TRANSACTIONS']
            ],
            'fetch_type' => 'ONETIME',
            'purpose' => ['code' => '103']
        ]
    ]
];

// 4. Date format with %Y-%m-%d and minimal fields
$tests['Minimal_Valid_Consent'] = [
    'client_ref_num' => 'WF-MIN-' . time(),
    'destination' => 'accountaggregator',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&txn=%s&st=%s',
    'consent_request' => [
        [
            'consent' => [
                'expiry' => date('Y-m-d', strtotime('+15 days')),
                'mode' => 'VIEW',
                'types' => ['TRANSACTIONS']
            ],
            'fetch_type' => 'ONETIME',
            'purpose' => [
                'code' => '105',
                'type' => 'Bank Account Verification',
                'text' => 'To verify account details for'
            ],
            'fi_types' => ['DEPOSIT'],
            'fi_date_range' => [
                'start_date' => date('Y-m-d', strtotime('-1 month')),
                'end_date' => date('Y-m-d')
            ],
            'data_life' => ['unit' => 'DAY', 'value' => 0],
            'frequency' => ['unit' => 'DAY', 'value' => 1]
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
