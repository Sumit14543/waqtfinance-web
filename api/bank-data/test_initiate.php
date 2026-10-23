<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json');

$txnId = $_GET['txn_id'] ?? 'da580fbe5';
$vua = $_GET['vua'] ?? '9810123456@anumati';

$auth = base64_encode('53205998:4lBQRBHkJms0jLy7oTtnEuhEgofOGnwu');

$url = 'https://svcprod.digitap.work/bank-data/initiate_aa_consent';
$payload = [
    'aa_id' => 'anumati',
    'vua' => $vua,
    'txn_id' => $txnId,
    'institution_id' => ''
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . $auth,
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$resp = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo json_encode([
    'http_code' => $httpCode,
    'sent' => $payload,
    'response' => json_decode($resp, true) ?: $resp
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
