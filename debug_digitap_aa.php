<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/DigitapBankDataUIService.php';

header('Content-Type: text/plain');

$url = 'https://svcprod.digitap.work/bank-data/generateurl';
$service = new DigitapBankDataUIService();
$reflector = new ReflectionClass($service);
$method = $reflector->getMethod('getBasicAuthHeader');
$method->setAccessible(true);
$authHeader = $method->invoke($service);

$tests = [
    'Test A: Doc Section 4.3.3 Exact Payload' => [
        'client_ref_num' => 'test-' . time(),
        'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
        'destination' => 'accountaggregator',
        'acceptance_policy' => 'atLeastOneTransactionInRange',
        'return_url' => 'https://waqtfinance.com/create-lead-form?step=5&aa_txn_id=%s&aa_status=%s',
        'mobile_num' => '9876543210',
        'aa_vendor' => 'anumati',
        'aa_fetch_type' => 'onetime'
    ],
    'Test B: Minimal (no aa_vendor, no aa_fetch_type)' => [
        'client_ref_num' => 'test-' . time(),
        'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
        'destination' => 'accountaggregator',
        'return_url' => 'https://waqtfinance.com/create-lead-form?step=5&aa_txn_id=%s&aa_status=%s'
    ],
    'Test C: Destination = choice' => [
        'client_ref_num' => 'test-' . time(),
        'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
        'destination' => 'choice',
        'return_url' => 'https://waqtfinance.com/create-lead-form?step=5&aa_txn_id=%s&aa_status=%s'
    ],
    'Test D: Destination = statementupload' => [
        'client_ref_num' => 'test-' . time(),
        'txn_completed_cburl' => 'https://waqtfinance.com/api/bank-data/callback.php',
        'destination' => 'statementupload',
        'return_url' => 'https://waqtfinance.com/create-lead-form?step=5&aa_txn_id=%s&aa_status=%s',
        'start_month' => '2024-04',
        'end_month' => '2024-10',
        'acceptance_policy' => 'atLeastOneTransactionInRange'
    ]
];

foreach ($tests as $name => $payload) {
    echo "========================================\n$name\n";
    echo "Payload: " . json_encode($payload) . "\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        $authHeader,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: $httpCode\n";
    echo "Response: $resp\n\n";
}
