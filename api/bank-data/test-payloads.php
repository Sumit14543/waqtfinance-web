<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/db.php';

header('Content-Type: application/json; charset=utf-8');

$clientId = env_value('DIGITAP_CLIENT_ID', '53205998');
$clientSecret = env_value('DIGITAP_CLIENT_SECRET', '4lBQRBHkJms0jLy7oTtnEuhEgofOGnwu');
$authHeader = 'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret);
$apiUrl = 'https://svc.digitap.ai/bank-data/generateurl';

$base = [
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&txn=%s&st=%s',
    'destination' => 'accountaggregator',
    'mobile_num' => '9876543210',
];

$tests = [];

// Variation 1: Page 11-12 section 4.3.3 Payload For Account Aggregator (NO consent_request)
$tests['Page12_4_3_3'] = array_merge($base, [
    'client_ref_num' => 'WF-AA-' . time() . '-1',
    'aa_vendor' => 'anumati',
    'aa_fetch_type' => 'onetime'
]);

// Variation 2: Section 4.3.3 with UPPERCASE ONETIME
$tests['Page12_UpperFetch'] = array_merge($base, [
    'client_ref_num' => 'WF-AA-' . time() . '-2',
    'aa_vendor' => 'anumati',
    'aa_fetch_type' => 'ONETIME'
]);

// Variation 3: Section 4.3.3 without aa_vendor
$tests['Page12_NoVendor'] = array_merge($base, [
    'client_ref_num' => 'WF-AA-' . time() . '-3',
    'aa_fetch_type' => 'ONETIME'
]);

// Variation 4: Page 60 Table Minimal consent_request
$tests['Page60_TableMinimal'] = array_merge($base, [
    'client_ref_num' => 'WF-AA-' . time() . '-4',
    'aa_vendor' => 'anumati',
    'consent_request' => [
        [
            'fi_types' => ['DEPOSIT'],
            'fetch_type' => 'ONETIME'
        ]
    ]
]);

// Variation 5: Page 60 Minimal consent_request without aa_vendor
$tests['Page60_TableMinimal_NoVendor'] = array_merge($base, [
    'client_ref_num' => 'WF-AA-' . time() . '-5',
    'consent_request' => [
        [
            'fi_types' => ['DEPOSIT'],
            'fetch_type' => 'ONETIME'
        ]
    ]
]);

// Variation 6: Page 59 with ISO Expiry DateTime (YYYY-MM-DDTHH:MM:SS)
$tests['Page59_ISO_Expiry'] = array_merge($base, [
    'client_ref_num' => 'WF-AA-' . time() . '-6',
    'aa_vendor' => 'anumati',
    'consent_request' => [
        [
            'consent' => [
                'expiry' => gmdate('Y-m-d\TH:i:s', strtotime('+30 days')),
                'mode' => 'VIEW',
                'types' => ['TRANSACTIONS', 'PROFILE', 'SUMMARY']
            ],
            'fetch_type' => 'ONETIME',
            'purpose' => [
                'code' => '103',
                'text' => 'Aggregated statement'
            ],
            'fi_types' => ['DEPOSIT'],
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
]);

// Variation 7: Page 59 with ISO Expiry + No Vendor
$tests['Page59_ISO_NoVendor'] = array_merge($base, [
    'client_ref_num' => 'WF-AA-' . time() . '-7',
    'consent_request' => [
        [
            'consent' => [
                'expiry' => gmdate('Y-m-d\TH:i:s', strtotime('+30 days')),
                'mode' => 'VIEW',
                'types' => ['TRANSACTIONS', 'PROFILE', 'SUMMARY']
            ],
            'fetch_type' => 'ONETIME',
            'purpose' => [
                'code' => '103',
                'text' => 'Aggregated statement'
            ],
            'fi_types' => ['DEPOSIT'],
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
]);

// Variation 8: Bare Minimum destination=accountaggregator
$tests['Bare_Minimum'] = [
    'client_ref_num' => 'WF-AA-' . time() . '-8',
    'destination' => 'accountaggregator',
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
