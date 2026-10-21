<?php
declare(strict_types=1);

// Set CORS and headers
if (!headers_sent()) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('X-Robots-Tag: noindex, nofollow, noarchive');
}

// Handle preflight OPTIONS requests gracefully
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if (!headers_sent()) http_response_code(200);
    echo json_encode(['status' => 'ok']);
    exit;
}

$config = require __DIR__ . '/config.php';
$apiKey = $config['groq_api_key'] ?? '';

if (empty($apiKey) || str_contains($apiKey, 'REPLACE_')) {
    if (!headers_sent()) http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'GROQ_API_KEY is missing or invalid. Please configure it in config.php or .env'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Read input from php://input (JSON) or $_POST or $_GET (for maximum server compatibility)
$raw = file_get_contents('php://input');
$input = null;

if (!empty($raw)) {
    $input = json_decode($raw, true);
}

if (!is_array($input) && !empty($_POST)) {
    $input = $_POST;
}

if (!is_array($input) && !empty($_GET['data'])) {
    $input = json_decode((string) $_GET['data'], true);
}

// If accessed directly via browser GET without data, return API health check
if (!is_array($input) || empty($input)) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        echo json_encode([
            'success' => true,
            'status' => 'active',
            'message' => 'Review Generator API is online and operational.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (!headers_sent()) http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid or empty request payload.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Simple IP-based Rate Limiter (Temporary file storage)
$clientIp = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$clientIp = trim(explode(',', $clientIp)[0]);
$rateLimitMax = (int) ($config['rate_limit_per_minute'] ?? 30);

if ($rateLimitMax > 0) {
    $ipHash = md5($clientIp . '_review_gen');
    $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "rate_limit_{$ipHash}.json";
    $now = time();
    $rateData = ['count' => 0, 'reset_at' => $now + 60];

    if (file_exists($tempFile)) {
        $saved = json_decode((string) @file_get_contents($tempFile), true);
        if (is_array($saved) && isset($saved['reset_at']) && $saved['reset_at'] > $now) {
            $rateData = $saved;
        }
    }

    if ($rateData['count'] >= $rateLimitMax) {
        if (!headers_sent()) http_response_code(429);
        echo json_encode([
            'success' => false,
            'error' => 'Too many requests. Please wait a minute before generating again.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $rateData['count']++;
    @file_put_contents($tempFile, json_encode($rateData), LOCK_EX);
}

// Helper sanitization function
function clean_input(mixed $val, int $maxLen = 250): string {
    if (!is_string($val)) return '';
    $val = trim(strip_tags($val));
    $val = preg_replace('/[\x00-\x1F\x7F]/u', '', $val) ?? $val;
    if (function_exists('mb_substr')) {
        return mb_substr($val, 0, $maxLen, 'UTF-8');
    }
    return substr($val, 0, $maxLen);
}

// Validate language
$lang = strtolower(clean_input($input['lang'] ?? 'en', 15));
if (!in_array($lang, ['hi', 'en', 'hinglish'], true)) {
    $lang = 'en';
}

$rating = (int) ($input['rating'] ?? 5);
if ($rating < 1 || $rating > 5) {
    $rating = 5;
}

$businessName = $config['business_name'] ?? 'WAQT Finance';
$service = clean_input($input['service'] ?? '', 100);
$process = clean_input($input['process'] ?? '', 100);
$support = clean_input($input['support'] ?? '', 100);
$comment = clean_input($input['comment'] ?? '', 500);

$mentions = [];
if (isset($input['mentions']) && is_array($input['mentions'])) {
    foreach (array_slice($input['mentions'], 0, 10) as $m) {
        $cleaned = clean_input($m, 60);
        if ($cleaned !== '') {
            $mentions[] = $cleaned;
        }
    }
}

// Craft specialized language directives
$languageDirectives = match ($lang) {
    'hi' => "Language: Hindi (शुद्ध और स्वाभाविक हिन्दी - देवनागरी लिपि). Write like a real Indian customer.",
    'hinglish' => "Language: Hinglish (Hindi written in English alphabet, e.g. 'Process kaafi smooth tha, team helpful thi...'). Write natural casual Indian review.",
    default => "Language: Natural English. Clear, authentic, human-sounding customer review."
};

$sentimentDirective = match ($rating) {
    5 => "Sentiment: Highly satisfied and appreciative (5-star rating).",
    4 => "Sentiment: Very good experience with minor constructive note (4-star rating).",
    3 => "Sentiment: Average / neutral experience (3-star rating).",
    default => "Sentiment: Critical / dissatisfied (1-2 star rating). Factual and polite."
};

$systemPrompt = <<<PROMPT
You are a fast customer review writer for {$businessName}.
Generate 3 unique customer review drafts in this exact tagged format:

[SHORT]
Short, punchy review (20-35 words)

[BALANCED]
Natural, balanced review (35-55 words)

[DETAILED]
Detailed, descriptive review (55-80 words)

RULES:
1. {$languageDirectives}
2. {$sentimentDirective}
3. Base strictly on user's supplied details. Never invent fake numbers or interest rates.
4. Output ONLY the 3 tagged sections [SHORT], [BALANCED], and [DETAILED]. No extra intro, no markdown fences.
PROMPT;

$userContent = json_encode([
    'business_name' => $businessName,
    'star_rating' => "{$rating} out of 5 stars",
    'service_availed' => $service ?: 'General Service',
    'experience_process' => $process ?: 'Smooth',
    'experience_support' => $support ?: 'Helpful',
    'highlighted_topics' => $mentions,
    'customer_personal_note' => $comment ?: 'None'
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// Call Groq API
function call_groq_api(string $apiKey, string $model, string $systemPrompt, string $userContent, float $temperature = 0.85): array {
    $payload = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userContent]
        ],
        'temperature' => $temperature,
        'max_tokens' => 3500,
        'top_p' => 0.95
    ];

    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        CURLOPT_CONNECTTIMEOUT => 6,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    return [
        'response' => $response,
        'httpCode' => $httpCode,
        'curlError' => $curlError
    ];
}

$randomTemp = 0.80 + (mt_rand(0, 15) / 100);
$primaryModel = $config['primary_model'] ?? 'openai/gpt-oss-20b';
$fallbackModel = $config['fallback_model'] ?? 'openai/gpt-oss-120b';

$apiResult = call_groq_api($apiKey, $primaryModel, $systemPrompt, $userContent, $randomTemp);

if ($apiResult['httpCode'] !== 200 && !empty($fallbackModel)) {
    $fallbackResult = call_groq_api($apiKey, $fallbackModel, $systemPrompt, $userContent, $randomTemp);
    if ($fallbackResult['httpCode'] === 200) {
        $apiResult = $fallbackResult;
        $primaryModel = $fallbackModel;
    }
}

if ($apiResult['response'] === false || empty($apiResult['response'])) {
    if (!headers_sent()) http_response_code(502);
    echo json_encode([
        'success' => false,
        'error' => 'Unable to connect to AI server. Please check your connection or try again.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$httpCode = $apiResult['httpCode'];
$data = json_decode($apiResult['response'], true);

if ($httpCode !== 200 || !is_array($data)) {
    if (!headers_sent()) http_response_code($httpCode === 429 ? 429 : 502);
    $apiErrorMessage = $data['error']['message'] ?? 'AI service temporarily unavailable.';
    if ($httpCode === 429) {
        $apiErrorMessage = 'Rate limit exceeded on AI provider. Please wait a few moments and try again.';
    }
    echo json_encode(['success' => false, 'error' => $apiErrorMessage], JSON_UNESCAPED_UNICODE);
    exit;
}

$rawContent = (string)($data['choices'][0]['message']['content'] ?? '');

// Strip reasoning/think tags if present
$rawContent = preg_replace('/<think>.*?<\/think>/is', '', $rawContent) ?? $rawContent;
$rawContent = trim($rawContent);

// Parse tagged sections: [SHORT], [BALANCED], [DETAILED]
$drafts = ['short' => '', 'balanced' => '', 'detailed' => ''];

if (preg_match('/\[SHORT\]\s*([\s\S]*?)(?=\[BALANCED\]|\[DETAILED\]|$)/i', $rawContent, $m)) {
    $drafts['short'] = trim($m[1]);
}
if (preg_match('/\[BALANCED\]\s*([\s\S]*?)(?=\[SHORT\]|\[DETAILED\]|$)/i', $rawContent, $m)) {
    $drafts['balanced'] = trim($m[1]);
}
if (preg_match('/\[DETAILED\]\s*([\s\S]*?)(?=\[SHORT\]|\[BALANCED\]|$)/i', $rawContent, $m)) {
    $drafts['detailed'] = trim($m[1]);
}

// Clean any leftover quotes or brackets
foreach ($drafts as $k => $v) {
    $drafts[$k] = trim($v, "\"'`“” \t\n\r\0\x0B");
}

// Fallback logic if parsing missed any tag
$primaryReview = $drafts['balanced'] ?: $drafts['short'] ?: $drafts['detailed'] ?: $rawContent;
$primaryReview = trim($primaryReview, "\"'`“” \t\n\r\0\x0B");

if (empty($drafts['balanced'])) $drafts['balanced'] = $primaryReview;
if (empty($drafts['short'])) $drafts['short'] = $primaryReview;
if (empty($drafts['detailed'])) $drafts['detailed'] = $primaryReview;

if ($primaryReview === '') {
    if (!headers_sent()) http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'AI returned an empty draft. Please retry.'], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'success' => true,
    'review' => $primaryReview,
    'drafts' => $drafts,
    'model' => $primaryModel
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
