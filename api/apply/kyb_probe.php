<?php
require_once dirname(__DIR__, 2) . '/config/db.php';

header('Content-Type: application/json');

$clientId = env_value('DIGITAP_CLIENT_ID', '53205998');
$clientSecret = env_value('DIGITAP_CLIENT_SECRET', '');
$authVal = base64_encode($clientId . ':' . $clientSecret);

$gstCandidates = [
    'https://svc.digitap.ai/validation/kyb/v1/gst',
    'https://svc.digitap.ai/validation/kyb/v1/gst_authentication',
    'https://svc.digitap.ai/validation/kyb/v1/gst_advanced',
    'https://svc.digitap.ai/validation/kyb/v1/gstin',
    'https://api.digitap.ai/validation/kyb/v1/gst',
    'https://svc.digitap.ai/ent/v1/gst',
    'https://svc.digitap.ai/validation/kyb/v1/gst_search'
];

$results = [];

foreach ($gstCandidates as $url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'client_ref_num' => 'TEST_' . time(),
            'gstin' => '07AAAAA0000A1Z5'
        ]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Basic ' . $authVal
        ],
        CURLOPT_TIMEOUT => 8
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    $results[$url] = [
        'http_code' => $code,
        'response' => json_decode((string)$res, true) ?: substr((string)$res, 0, 200),
        'error' => $err
    ];
}

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
