<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/db.php';
header('Content-Type: application/json; charset=utf-8');

$clientId = env_value('DIGITAP_CLIENT_ID', '53205998');
$clientSecret = env_value('DIGITAP_CLIENT_SECRET', '4lBQRBHkJms0jLy7oTtnEuhEgofOGnwu');
$authHeader = 'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret);
$apiUrl = 'https://svc.digitap.ai/bank-data/generateurl';

$tests = [];

// Base minimum
$cb = 'https://waqtfinance.com/api/bank-data/callback.php';
$ret = 'https://waqtfinance.com/create-lead-form.php?step=5&aa_txn_id=%s&aa_status=%s';

// T1: Purpose 101 (Wealth Management / PFM)
$tests['T1_Purpose_101'] = [
    'client_ref_num' => 'WF-T1-' . time(),
    'destination' => 'accountaggregator',
    'txn_completed_cburl' => $cb,
    'return_url' => $ret,
    'consent_request' => [
        [
            'consent' => [
                'expiry' => date('Y-m-d', strtotime('+30 days')),
                'mode' => 'STORE',
                'types' => ['TRANSACTIONS']
            ],
            'fetch_type' => 'ONETIME',
            'purpose' => ['code' => '101'],
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

// T2: Purpose 102 (Personal Finance)
$tests['T2_Purpose_102'] = [
    'client_ref_num' => 'WF-T2-' . time(),
    'destination' => 'accountaggregator',
    'txn_completed_cburl' => $cb,
    'return_url' => $ret,
    'consent_request' => [
        [
            'consent' => [
                'expiry' => date('Y-m-d', strtotime('+30 days')),
                'mode' => 'STORE',
                'types' => ['TRANSACTIONS']
            ],
            'fetch_type' => 'ONETIME',
            'purpose' => ['code' => '102'],
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

// T3: Purpose 105 (Bank Account Verification)
$tests['T3_Purpose_105'] = [
    'client_ref_num' => 'WF-T3-' . time(),
    'destination' => 'accountaggregator',
    'txn_completed_cburl' => $cb,
    'return_url' => $ret,
    'consent_request' => [
        [
            'consent' => [
                'expiry' => date('Y-m-d', strtotime('+1 day')),
                'mode' => 'VIEW',
                'types' => ['SUMMARY', 'PROFILE']
            ],
            'fetch_type' => 'ONETIME',
            'purpose' => ['code' => '105'],
            'fi_types' => ['DEPOSIT'],
            'fi_date_range' => [
                'start_date' => date('Y-m-d', strtotime('-1 day')),
                'end_date' => date('Y-m-d')
            ],
            'data_life' => ['unit' => 'DAY', 'value' => 0],
            'frequency' => ['unit' => 'DAY', 'value' => 1]
        ]
    ]
];

// T4: Without purpose object at all
$tests['T4_No_Purpose'] = [
    'client_ref_num' => 'WF-T4-' . time(),
    'destination' => 'accountaggregator',
    'txn_completed_cburl' => $cb,
    'return_url' => $ret,
    'consent_request' => [
        [
            'consent' => [
                'expiry' => date('Y-m-d', strtotime('+30 days')),
                'mode' => 'STORE',
                'types' => ['TRANSACTIONS']
            ],
            'fetch_type' => 'ONETIME',
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

// T5: Exactly as Page 60 Table:
// Consent Initialization Type: ONETIME
// Parameters Required: [ { "fi_types": ["DEPOSIT"], "fetch_type": "ONETIME" } ]
$tests['T5_Page60_Table_Clean'] = [
    'client_ref_num' => 'WF-T5-' . time(),
    'destination' => 'accountaggregator',
    'txn_completed_cburl' => $cb,
    'return_url' => $ret,
    'consent_request' => [
        [
            'fi_types' => ['DEPOSIT'],
            'fetch_type' => 'ONETIME'
        ]
    ]
];

// T6: Page 60 Table with PERIODIC
$tests['T6_Page61_Periodic'] = [
    'client_ref_num' => 'WF-T6-' . time(),
    'destination' => 'accountaggregator',
    'txn_completed_cburl' => $cb,
    'return_url' => $ret,
    'consent_request' => [
        [
            'fi_types' => ['DEPOSIT'],
            'fetch_type' => 'PERIODIC'
        ]
    ]
];

// T7: Page 61 BOTH (Onetime and Periodic)
$tests['T7_Page61_Both'] = [
    'client_ref_num' => 'WF-T7-' . time(),
    'destination' => 'accountaggregator',
    'txn_completed_cburl' => $cb,
    'return_url' => $ret,
    'consent_request' => [
        [
            'fi_types' => ['DEPOSIT'],
            'fetch_type' => 'ONETIME'
        ],
        [
            'fi_types' => ['DEPOSIT'],
            'fetch_type' => 'PERIODIC'
        ]
    ]
];

// T8: What if client_name is sent? (Page 10 has client_name: client_xxx)
$tests['T8_With_ClientName'] = [
    'client_name' => '53205998',
    'client_ref_num' => 'WF-T8-' . time(),
    'destination' => 'accountaggregator',
    'txn_completed_cburl' => $cb,
    'return_url' => $ret,
    'consent_request' => [
        [
            'fi_types' => ['DEPOSIT'],
            'fetch_type' => 'ONETIME'
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
