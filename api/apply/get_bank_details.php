<?php
header('Content-Type: application/json');

// input lo
$ifsc = strtoupper(trim($_GET['ifsc'] ?? ''));

// validation
if (!preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $ifsc)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid IFSC code'
    ]);
    exit;
}

$cacheTtl = 30 * 24 * 60 * 60;
$cacheDir = __DIR__ . '/../cache/ifsc';
$cacheFile = $cacheDir . '/' . $ifsc . '.json';

if (is_readable($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
    $cached = file_get_contents($cacheFile);
    if (is_string($cached) && $cached !== '') {
        echo $cached;
        exit;
    }
}

// API URL
$url = "https://ifsc.razorpay.com/" . $ifsc;

// cURL init
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($ch);

// error handling
if (curl_errno($ch)) {
    if (is_readable($cacheFile)) {
        $cached = file_get_contents($cacheFile);
        if (is_string($cached) && $cached !== '') {
            echo $cached;
            curl_close($ch);
            exit;
        }
    }

    echo json_encode([
        'status' => 'error',
        'message' => 'API connection failed'
    ]);
    curl_close($ch);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// IFSC not found
if ($httpCode !== 200) {
    echo json_encode([
        'status' => 'error',
        'message' => 'IFSC not found'
    ]);
    exit;
}

$data = json_decode($response, true);

if (!is_array($data) || empty($data['BANK'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid IFSC response'
    ]);
    exit;
}

// response return
$payload = json_encode([
    'status' => 'success',
    'bank' => $data['BANK'] ?? '',
    'branch' => $data['BRANCH'] ?? '',
    'city' => $data['CITY'] ?? '',
    'state' => $data['STATE'] ?? '',
    'address' => $data['ADDRESS'] ?? ''
]);

if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

if (is_dir($cacheDir) && is_writable($cacheDir)) {
    file_put_contents($cacheFile, $payload, LOCK_EX);
}

echo $payload;
