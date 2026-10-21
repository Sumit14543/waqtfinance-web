<?php
declare(strict_types=1);

/**
 * Review Generator Configuration
 */

// Simple .env parser if .env file exists
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (strpos($line, '=') !== false) {
                [$key, $val] = explode('=', $line, 2);
                $key = trim($key);
                $val = trim($val);
                if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                    (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                    $val = substr($val, 1, -1);
                }
                if (!empty($key) && getenv($key) === false) {
                    putenv("{$key}={$val}");
                    $_ENV[$key] = $val;
                }
            }
        }
    }
}

function get_config(string $key, mixed $default = null): mixed {
    $val = getenv($key);
    if ($val !== false && $val !== '') {
        return $val;
    }
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }
    return $default;
}

return [
    // Business Information
    'business_name' => get_config('BUSINESS_NAME', 'WAQT Finance'),
    'business_tagline' => get_config('BUSINESS_TAGLINE', 'Share your genuine experience'),

    // Google My Business / Google Maps Review Direct URL
    // e.g. https://g.page/r/YOUR_ID/review or https://search.google.com/local/writereview?placeid=...
    'google_review_url' => get_config('GOOGLE_REVIEW_URL', 'https://search.google.com/local/writereview?placeid=ChIJ6wMIyo7vDDkRaSo4sN2-h5o'),

    // Groq API Key
    'groq_api_key' => get_config('GROQ_API_KEY', ''),

    // AI Models
    'primary_model' => get_config('AI_MODEL', 'openai/gpt-oss-20b'),
    'fallback_model' => 'openai/gpt-oss-120b',

    // Rate limiting: Max review generations per IP per minute
    'rate_limit_per_minute' => (int) get_config('RATE_LIMIT_PER_MINUTE', 20),

    // Enable debug messages in development
    'debug' => (bool) get_config('APP_DEBUG', false),
];
