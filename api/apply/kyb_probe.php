<?php
// Temporary probe to test if Hostinger production IP is allowed by Digitap for GST & Udyam
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$clientId = env_value('DIGITAP_CLIENT_ID', '53205998');
$clientSecret = env_value('DIGITAP_CLIENT_SECRET', '');
$authVal = base64_encode($clientId . ':' . $clientSecret);

$serverIp = $_SERVER['SERVER_ADDR'] ?? file_get_contents('https://api.ipify.org');

// Test GST
$chGst = curl_init('https://svc.digitap.ai/validation/kyb/v1/gst');
curl_setopt_array($chGst, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'client_ref_num' => 'PROD_TEST_' . time(),
        'gstin' => '07AAAAA0000A1Z5'
    ]),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Basic ' . $authVal
    ],
    CURLOPT_TIMEOUT => 15
]);
$gstRes = curl_exec($chGst);
$gstCode = curl_getinfo($chGst, CURLINFO_HTTP_CODE);
$gstErr = curl_error($chGst);
curl_close($chGst);

// Test Udyam
$chUdyam = curl_init('https://svc.digitap.ai/validation/kyb/v1/udyam_authentication/basic');
curl_setopt_array($chUdyam, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'client_ref_num' => 'PROD_TEST_' . time(),
        'udyam_reg_no' => 'UDYAM-DL-01-0000001'
    ]),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Basic ' . $authVal
    ],
    CURLOPT_TIMEOUT => 15
]);
$udyamRes = curl_exec($chUdyam);
$udyamCode = curl_getinfo($chUdyam, CURLINFO_HTTP_CODE);
$udyamErr = curl_error($chUdyam);
curl_close($chUdyam);

echo json_encode([
    'server_ip' => $serverIp,
    'gst' => [
        'http_code' => $gstCode,
        'response' => json_decode((string)$gstRes, true) ?: $gstRes,
        'curl_error' => $gstErr
    ],
    'udyam' => [
        'http_code' => $udyamCode,
        'response' => json_decode((string)$udyamRes, true) ?: $udyamRes,
        'curl_error' => $udyamErr
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
