<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/db.php';
header('Content-Type: application/json; charset=utf-8');

$clientId = env_value('DIGITAP_CLIENT_ID', '53205998');
$clientSecret = env_value('DIGITAP_CLIENT_SECRET', '4lBQRBHkJms0jLy7oTtnEuhEgofOGnwu');
$authHeader = 'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret);

$results = [];

// 1. Institution list for Statement
$ch = curl_init('https://svc.digitap.ai/bank-data/institutions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['type' => 'Statement']));
curl_setopt($ch, CURLOPT_HTTPHEADER, [$authHeader, 'Content-Type: application/json']);
$res = curl_exec($ch);
$results['Institutions_Statement'] = [
    'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
    'response' => json_decode((string)$res, true) ?: $res
];
curl_close($ch);

// 2. Institution list for NetBanking
$ch = curl_init('https://svc.digitap.ai/bank-data/institutions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['type' => 'NetBanking']));
curl_setopt($ch, CURLOPT_HTTPHEADER, [$authHeader, 'Content-Type: application/json']);
$res = curl_exec($ch);
$results['Institutions_NetBanking'] = [
    'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
    'response' => json_decode((string)$res, true) ?: $res
];
curl_close($ch);

// 3. Test statementupload generateurl
$ch = curl_init('https://svc.digitap.ai/bank-data/generateurl');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'client_ref_num' => 'WF-STMT-' . time(),
    'destination' => 'statementupload',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&txn=%s&st=%s',
    'start_month' => date('Y-m', strtotime('-3 months')),
    'end_month' => date('Y-m')
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [$authHeader, 'Content-Type: application/json']);
$res = curl_exec($ch);
$results['GenerateUrl_StatementUpload'] = [
    'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
    'response' => json_decode((string)$res, true) ?: $res
];
curl_close($ch);

// 4. Exact Page 59 consent_request structure with NO extra keys
$now = time();
$exactPage59 = [
    'client_ref_num' => 'WF-AA-' . $now,
    'destination' => 'accountaggregator',
    'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
    'return_url' => 'https://waqtfinance.com/create_lead?step=5&txn=%s&st=%s',
    'consent_request' => [
        [
            'consent' => [
                'expiry' => date('Y-m-d\TH:i:s', $now + 86400 * 30),
                'mode' => 'VIEW',
                'types' => ['TRANSACTIONS', 'PROFILE', 'SUMMARY']
            ],
            'fetch_type' => 'ONETIME',
            'purpose' => [
                'code' => '103',
                'text' => 'Aggregated statement'
            ],
            'fi_date_range' => [
                'start_date' => date('Y-m-d', $now - 86400 * 180),
                'end_date' => date('Y-m-d', $now)
            ],
            'data_life' => [
                'unit' => 'MONTH',
                'value' => 4
            ],
            'frequency' => [
                'unit' => 'DAY',
                'value' => 1
            ],
            'data_filters' => [
                [
                    'operator' => '>',
                    'type' => 'TRANSACTIONAMOUNT',
                    'value' => '1'
                ]
            ]
        ]
    ]
];

$ch = curl_init('https://svc.digitap.ai/bank-data/generateurl');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($exactPage59));
curl_setopt($ch, CURLOPT_HTTPHEADER, [$authHeader, 'Content-Type: application/json']);
$res = curl_exec($ch);
$results['Exact_Page_59'] = [
    'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
    'response' => json_decode((string)$res, true) ?: $res
];
curl_close($ch);

// 5. What if consent_request is a JSON string instead of an array?
$page59String = $exactPage59;
$page59String['consent_request'] = json_encode($exactPage59['consent_request']);
$ch = curl_init('https://svc.digitap.ai/bank-data/generateurl');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($page59String));
curl_setopt($ch, CURLOPT_HTTPHEADER, [$authHeader, 'Content-Type: application/json']);
$res = curl_exec($ch);
$results['Consent_Request_As_JSON_String'] = [
    'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
    'response' => json_decode((string)$res, true) ?: $res
];
curl_close($ch);

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
