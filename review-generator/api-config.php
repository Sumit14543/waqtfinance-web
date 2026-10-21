<?php
declare(strict_types=1);

if (!headers_sent()) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: public, max-age=60');
    header('X-Robots-Tag: noindex, nofollow, noarchive');
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if (!headers_sent()) http_response_code(200);
    echo json_encode(['status' => 'ok']);
    exit;
}

$config = require __DIR__ . '/config.php';

echo json_encode([
    'success' => true,
    'businessName' => $config['business_name'] ?? 'WAQT Finance',
    'businessTagline' => $config['business_tagline'] ?? 'Share your genuine experience',
    'googleReviewUrl' => $config['google_review_url'] ?? '',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
