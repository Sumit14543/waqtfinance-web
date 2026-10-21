<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$pincode = preg_replace('/\D/', '', (string) ($_GET['pincode'] ?? '')) ?? '';

function fallback_pincode_payload(string $pincode): ?string
{
    $exact = [
        '201301' => ['city' => 'Noida', 'state' => 'Uttar Pradesh', 'region' => 'Lucknow HQ'],
        '201303' => ['city' => 'Noida', 'state' => 'Uttar Pradesh', 'region' => 'Lucknow HQ'],
        '201304' => ['city' => 'Noida', 'state' => 'Uttar Pradesh', 'region' => 'Lucknow HQ'],
        '201305' => ['city' => 'Noida', 'state' => 'Uttar Pradesh', 'region' => 'Lucknow HQ'],
        '201306' => ['city' => 'Noida', 'state' => 'Uttar Pradesh', 'region' => 'Lucknow HQ'],
        '201307' => ['city' => 'Noida', 'state' => 'Uttar Pradesh', 'region' => 'Lucknow HQ'],
        '201308' => ['city' => 'Noida', 'state' => 'Uttar Pradesh', 'region' => 'Lucknow HQ'],
        '201309' => ['city' => 'Noida', 'state' => 'Uttar Pradesh', 'region' => 'Lucknow HQ'],
        '201310' => ['city' => 'Greater Noida', 'state' => 'Uttar Pradesh', 'region' => 'Lucknow HQ'],
        '201011' => ['city' => 'Ghaziabad', 'state' => 'Uttar Pradesh', 'region' => 'Lucknow HQ'],
    ];

    if (isset($exact[$pincode])) {
        return json_encode(['status' => 'success'] + $exact[$pincode] + ['pincode' => $pincode], JSON_UNESCAPED_SLASHES);
    }

    $prefixes = [
        '110' => ['city' => 'Delhi', 'state' => 'Delhi', 'region' => 'Delhi'],
        '122' => ['city' => 'Gurugram', 'state' => 'Haryana', 'region' => 'Ambala HQ'],
        '2010' => ['city' => 'Ghaziabad', 'state' => 'Uttar Pradesh', 'region' => 'Lucknow HQ'],
        '203' => ['city' => 'Bulandshahr', 'state' => 'Uttar Pradesh', 'region' => 'Agra'],
        '302' => ['city' => 'Jaipur', 'state' => 'Rajasthan', 'region' => 'Jaipur HQ'],
        '400' => ['city' => 'Mumbai', 'state' => 'Maharashtra', 'region' => 'Mumbai'],
        '411' => ['city' => 'Pune', 'state' => 'Maharashtra', 'region' => 'Pune'],
        '500' => ['city' => 'Hyderabad', 'state' => 'Telangana', 'region' => 'Hyderabad'],
        '560' => ['city' => 'Bangalore', 'state' => 'Karnataka', 'region' => 'Bangalore HQ'],
    ];

    foreach ($prefixes as $prefix => $details) {
        $prefix = (string) $prefix;
        if (strncmp($pincode, $prefix, strlen($prefix)) === 0) {
            return json_encode(['status' => 'success'] + $details + ['pincode' => $pincode, 'source' => 'fallback'], JSON_UNESCAPED_SLASHES);
        }
    }

    return null;
}

if (!preg_match('/^\d{6}$/', $pincode)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid pincode']);
    exit;
}

$fallback = fallback_pincode_payload($pincode);

$cacheTtl = 30 * 24 * 60 * 60;
$cacheDir = __DIR__ . '/../cache/pincode';
$cacheFile = $cacheDir . '/' . $pincode . '.json';

if (is_readable($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
    $cached = file_get_contents($cacheFile);
    if (is_string($cached) && $cached !== '') {
        echo $cached;
        exit;
    }
}

if (!function_exists('curl_init')) {
    echo $fallback ?: json_encode(['status' => 'error', 'message' => 'Pincode service unavailable']);
    exit;
}

$url = 'https://api.postalpincode.in/pincode/' . $pincode;
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 8,
]);

$response = curl_exec($ch);

if ($response === false) {
    if (is_readable($cacheFile)) {
        $cached = file_get_contents($cacheFile);
        if (is_string($cached) && $cached !== '') {
            echo $cached;
            curl_close($ch);
            exit;
        }
    }

    curl_close($ch);
    echo $fallback ?: json_encode(['status' => 'error', 'message' => 'Pincode service unavailable']);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo $fallback ?: json_encode(['status' => 'error', 'message' => 'Pincode lookup failed']);
    exit;
}

$data = json_decode($response, true);
$postOffices = $data[0]['PostOffice'] ?? [];

if (($data[0]['Status'] ?? '') !== 'Success' || empty($postOffices[0])) {
    echo $fallback ?: json_encode(['status' => 'error', 'message' => 'Invalid pincode']);
    exit;
}

$postOffice = $postOffices[0];
$payload = json_encode([
    'status' => 'success',
    'city' => $postOffice['District'] ?? '',
    'state' => $postOffice['State'] ?? '',
    'region' => $postOffice['Region'] ?? '',
    'pincode' => $pincode,
], JSON_UNESCAPED_SLASHES);

if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

if (is_dir($cacheDir) && is_writable($cacheDir)) {
    file_put_contents($cacheFile, $payload, LOCK_EX);
}

echo $payload;
