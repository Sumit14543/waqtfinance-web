<?php
declare(strict_types=1);

if (!function_exists('is_https_request')) {
function is_https_request(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}
}

if (!function_exists('send_security_headers')) {
function send_security_headers(): void
{
    if (headers_sent()) {
        return;
    }

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(self), microphone=(), geolocation=(self)');
    header("Content-Security-Policy: default-src 'self' https:; script-src 'self' 'unsafe-inline' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' data: https:; frame-ancestors 'self';");

    if (is_https_request()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}
}

if (!function_exists('waqt_e')) {
function waqt_e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
}

if (!function_exists('waqt_base64url_encode')) {
function waqt_base64url_encode(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}
}

if (!function_exists('waqt_base64url_decode')) {
function waqt_base64url_decode(string $value): string
{
    $padding = strlen($value) % 4;
    if ($padding > 0) {
        $value .= str_repeat('=', 4 - $padding);
    }

    $decoded = base64_decode(strtr($value, '-_', '+/'), true);
    return $decoded === false ? '' : $decoded;
}
}

if (!function_exists('waqt_sensitive_key')) {
function waqt_sensitive_key(): string
{
    return hash('sha256', required_env('APP_SECRET_KEY'), true);
}
}

if (!function_exists('waqt_encrypt_sensitive_data')) {
function waqt_encrypt_sensitive_data(string $plainText): string
{
    $nonce = random_bytes(12);
    $tag = '';
    $cipherText = openssl_encrypt($plainText, 'aes-256-gcm', waqt_sensitive_key(), OPENSSL_RAW_DATA, $nonce, $tag);

    if ($cipherText === false || $tag === '') {
        throw new RuntimeException('Unable to encrypt sensitive data.');
    }

    return 'enc:v1:' . waqt_base64url_encode($nonce . $tag . $cipherText);
}
}

if (!function_exists('waqt_decrypt_sensitive_data')) {
function waqt_decrypt_sensitive_data(string $encryptedValue): ?string
{
    if (!str_starts_with($encryptedValue, 'enc:v1:')) {
        return null;
    }

    $payload = waqt_base64url_decode(substr($encryptedValue, 7));
    if (strlen($payload) <= 28) {
        return null;
    }

    $nonce = substr($payload, 0, 12);
    $tag = substr($payload, 12, 16);
    $cipherText = substr($payload, 28);
    $plainText = openssl_decrypt($cipherText, 'aes-256-gcm', waqt_sensitive_key(), OPENSSL_RAW_DATA, $nonce, $tag);

    return $plainText === false ? null : $plainText;
}
}

if (!function_exists('waqt_sensitive_plaintext')) {
function waqt_sensitive_plaintext(?string $storedValue): string
{
    $storedValue = trim((string) $storedValue);
    if ($storedValue === '') {
        return '';
    }

    if (str_starts_with($storedValue, 'enc:v1:')) {
        return waqt_decrypt_sensitive_data($storedValue) ?? '';
    }

    return $storedValue;
}
}

if (!function_exists('waqt_mask_string')) {
function waqt_mask_string(string $value, int $visibleStart = 0, int $visibleEnd = 4): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    $length = strlen($value);
    if ($length <= ($visibleStart + $visibleEnd)) {
        return str_repeat('*', max(4, $length));
    }

    return substr($value, 0, $visibleStart)
        . str_repeat('*', max(4, $length - $visibleStart - $visibleEnd))
        . substr($value, -$visibleEnd);
}
}

if (!function_exists('waqt_mask_stored_sensitive_value')) {
function waqt_mask_stored_sensitive_value(?string $storedValue, int $visibleStart = 0, int $visibleEnd = 4): string
{
    return waqt_mask_string(waqt_sensitive_plaintext($storedValue), $visibleStart, $visibleEnd);
}
}

if (!function_exists('waqt_prepare_aadhaar_for_storage')) {
function waqt_prepare_aadhaar_for_storage(?string $inputValue, ?string $existingValue = ''): string
{
    $inputValue = trim((string) $inputValue);
    if ($inputValue === '') {
        return '';
    }

    if (str_starts_with($inputValue, 'enc:v1:')) {
        return $inputValue;
    }

    $hasMaskChars = stripos($inputValue, 'x') !== false || str_contains($inputValue, '*');
    $digits = preg_replace('/\D+/', '', $inputValue) ?? '';

    if (!$hasMaskChars && strlen($digits) === 12) {
        return waqt_encrypt_sensitive_data($digits);
    }

    if ($hasMaskChars) {
        $existingValue = trim((string) $existingValue);
        if ($existingValue === '') {
            return '';
        }

        if (str_starts_with($existingValue, 'enc:v1:')) {
            return $existingValue;
        }

        $existingDigits = preg_replace('/\D+/', '', waqt_sensitive_plaintext($existingValue)) ?? '';
        if (strlen($existingDigits) === 12) {
            return waqt_encrypt_sensitive_data($existingDigits);
        }

        return $existingValue;
    }

    return '';
}
}

if (!function_exists('waqt_redact_sensitive_text')) {
function waqt_redact_sensitive_text(string $value): string
{
    $value = preg_replace('/[A-Z]{5}[0-9]{4}[A-Z]/', 'PAN_REDACTED', $value) ?? $value;
    $value = preg_replace('/\b[2-9][0-9]{11}\b/', 'AADHAAR_REDACTED', $value) ?? $value;
    $value = preg_replace('/\b[6-9][0-9]{9}\b/', 'MOBILE_REDACTED', $value) ?? $value;
    $value = preg_replace('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', 'EMAIL_REDACTED', $value) ?? $value;
    $value = preg_replace('/Bearer\s+[A-Za-z0-9._\-]+/i', 'Bearer TOKEN_REDACTED', $value) ?? $value;
    return $value;
}
}

if (!function_exists('waqt_redact_sensitive_data')) {
function waqt_redact_sensitive_data($value)
{
    static $sensitiveKeys = [
        'aadhaar',
        'aadhaar_number',
        'aadhaarnumber',
        'authorization',
        'authkey',
        'bank_account',
        'bankaccount',
        'ifsc',
        'mobile',
        'mobile_number',
        'mobilenumber',
        'otp',
        'pan',
        'pan_number',
        'pannumber',
        'password',
        'phone',
        'secret',
        'token',
    ];

    if (is_array($value)) {
        $redacted = [];
        foreach ($value as $key => $item) {
            $normalizedKey = strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $key) ?? '');
            if (in_array($normalizedKey, $sensitiveKeys, true) || str_contains($normalizedKey, 'token') || str_contains($normalizedKey, 'secret')) {
                $redacted[$key] = '[REDACTED]';
                continue;
            }

            $redacted[$key] = waqt_redact_sensitive_data($item);
        }

        return $redacted;
    }

    if (is_string($value)) {
        return waqt_redact_sensitive_text($value);
    }

    return $value;
}
}

if (!function_exists('waqt_sanitize_rich_html')) {
function waqt_sanitize_rich_html(string $html): string
{
    $html = preg_replace('#<(script|style|iframe|object|embed|form|input|button|meta|link|base|svg|math)[^>]*>.*?</\1>#is', '', $html) ?? '';
    $html = preg_replace('#</?(script|style|iframe|object|embed|form|input|button|meta|link|base|svg|math)[^>]*>#is', '', $html) ?? '';

    $allowedTags = '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><h4><blockquote><table><thead><tbody><tr><th><td><a><img><span><div>';
    $html = strip_tags($html, $allowedTags);

    $html = preg_replace('/\s+on[a-z0-9_-]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? '';
    $html = preg_replace('/\s+style\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? '';
    $html = preg_replace('/\s+srcset\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? '';
    $html = preg_replace('/\s+(href|src)\s*=\s*([\'"])\s*(javascript|data|vbscript):[^\'"]*\2/i', ' $1="#"', $html) ?? '';
    $html = preg_replace('/\s+(href|src)\s*=\s*(javascript|data|vbscript):[^\s>]+/i', ' $1="#"', $html) ?? '';

    return $html;
}
}

if (!function_exists('waqt_upload_error')) {
function waqt_upload_error(string $message, int $statusCode = 400): void
{
    if (function_exists('json_response')) {
        json_response(['status' => 'error', 'message' => $message], $statusCode);
    }

    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}
}

if (!function_exists('waqt_save_uploaded_file')) {
function waqt_save_uploaded_file(array $file, string $relativeDir, string $prefix, array $allowedMimes, int $maxBytes): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        waqt_upload_error('Unable to upload file', 400);
    }

    if (($file['size'] ?? 0) <= 0 || ($file['size'] ?? 0) > $maxBytes) {
        waqt_upload_error('Uploaded file size is not allowed', 422);
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        waqt_upload_error('Invalid upload request', 400);
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? (string) finfo_file($finfo, $tmpName) : '';
    if ($finfo) {
        finfo_close($finfo);
    }

    if (!isset($allowedMimes[$mime])) {
        waqt_upload_error('Uploaded file type is not allowed', 422);
    }

    if (str_starts_with($mime, 'image/') && @getimagesize($tmpName) === false) {
        waqt_upload_error('Upload a valid image file', 422);
    }

    $safeDir = trim(str_replace(['..', '\\'], ['', '/'], $relativeDir), '/');
    $uploadDir = __DIR__ . '/../uploads' . ($safeDir !== '' ? '/' . $safeDir : '');
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        waqt_upload_error('Upload directory is not available', 500);
    }

    $safePrefix = preg_replace('/[^a-z0-9_-]/i', '_', $prefix) ?: 'file';
    $filename = $safePrefix . '_' . bin2hex(random_bytes(12)) . '.' . $allowedMimes[$mime];
    $destination = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($tmpName, $destination)) {
        waqt_upload_error('Failed to save uploaded file', 500);
    }

    $fileKey = 'uploads/' . ($safeDir !== '' ? $safeDir . '/' : '') . $filename;

    if (file_exists(__DIR__ . '/gcs_helper.php')) {
        require_once __DIR__ . '/gcs_helper.php';
        if (waqt_is_gcs_enabled()) {
            if (!waqt_upload_to_gcs($destination, $fileKey, $mime)) {
                waqt_upload_error('Failed to upload file to Cloud Storage', 500);
            }
        }
    }

    return $fileKey;
}
}

if (!function_exists('waqt_private_storage_root')) {
function waqt_private_storage_root(): string
{
    $configuredPath = function_exists('env_value') ? (env_value('PRIVATE_STORAGE_PATH', '') ?? '') : '';
    $root = $configuredPath !== '' ? $configuredPath : (__DIR__ . '/../storage/private');
    return rtrim(str_replace('\\', '/', $root), '/');
}
}

if (!function_exists('waqt_is_private_file_path')) {
function waqt_is_private_file_path(string $path): bool
{
    return str_starts_with(trim($path), 'private://');
}
}

if (!function_exists('waqt_private_relative_path')) {
function waqt_private_relative_path(string $path): string
{
    if (!waqt_is_private_file_path($path)) {
        waqt_upload_error('Invalid private file path', 400);
    }

    $relative = substr(trim($path), strlen('private://'));
    $relative = trim(str_replace('\\', '/', $relative), '/');

    if ($relative === '' || str_contains($relative, '..') || str_starts_with($relative, '/')) {
        waqt_upload_error('Invalid private file path', 400);
    }

    return $relative;
}
}

if (!function_exists('waqt_private_absolute_path')) {
function waqt_private_absolute_path(string $path): string
{
    return waqt_private_storage_root() . '/' . waqt_private_relative_path($path);
}
}

if (!function_exists('waqt_prepare_storage_dir')) {
function waqt_prepare_storage_dir(string $baseDir, string $relativeDir): string
{
    $safeDir = trim(str_replace(['..', '\\'], ['', '/'], $relativeDir), '/');
    $uploadDir = rtrim($baseDir, '/') . ($safeDir !== '' ? '/' . $safeDir : '');

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        waqt_upload_error('Upload directory is not available', 500);
    }

    return $uploadDir;
}
}

if (!function_exists('waqt_save_private_uploaded_file')) {
function waqt_save_private_uploaded_file(array $file, string $relativeDir, string $prefix, array $allowedMimes, int $maxBytes): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        waqt_upload_error('Unable to upload file', 400);
    }

    if (($file['size'] ?? 0) <= 0 || ($file['size'] ?? 0) > $maxBytes) {
        waqt_upload_error('Uploaded file size is not allowed', 422);
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        waqt_upload_error('Invalid upload request', 400);
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? (string) finfo_file($finfo, $tmpName) : '';
    if ($finfo) {
        finfo_close($finfo);
    }

    if (!isset($allowedMimes[$mime])) {
        waqt_upload_error('Uploaded file type is not allowed', 422);
    }

    if (str_starts_with($mime, 'image/') && @getimagesize($tmpName) === false) {
        waqt_upload_error('Upload a valid image file', 422);
    }

    $safeDir = trim(str_replace(['..', '\\'], ['', '/'], $relativeDir), '/');
    $uploadDir = waqt_prepare_storage_dir(waqt_private_storage_root(), $safeDir);
    $safePrefix = preg_replace('/[^a-z0-9_-]/i', '_', $prefix) ?: 'file';
    $filename = $safePrefix . '_' . bin2hex(random_bytes(12)) . '.' . $allowedMimes[$mime];
    $destination = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($tmpName, $destination)) {
        waqt_upload_error('Failed to save uploaded file', 500);
    }

    $fileKey = 'private://' . ($safeDir !== '' ? $safeDir . '/' : '') . $filename;

    if (file_exists(__DIR__ . '/gcs_helper.php')) {
        require_once __DIR__ . '/gcs_helper.php';
        if (waqt_is_gcs_enabled()) {
            $gcsKey = 'private/' . ($safeDir !== '' ? $safeDir . '/' : '') . $filename;
            try {
                $uploadedToGcs = waqt_upload_to_gcs($destination, $gcsKey, $mime);
            } catch (Throwable $error) {
                @unlink($destination);
                throw $error;
            }
            if (!$uploadedToGcs) {
                @unlink($destination);
                waqt_upload_error('Failed to upload file to Cloud Storage', 500);
            }
            @unlink($destination);
        }
    }

    return $fileKey;
}
}

if (!function_exists('waqt_save_base64_image')) {
function waqt_save_base64_image(string $dataUri, string $relativeDir, string $prefix, int $maxBytes): string
{
    if (!preg_match('#^data:image/(png|jpeg|webp);base64,#i', $dataUri, $matches)) {
        waqt_upload_error('Invalid selfie image', 422);
    }

    $encoded = substr($dataUri, strpos($dataUri, ',') + 1);
    $binary = base64_decode($encoded, true);
    if ($binary === false || strlen($binary) === 0 || strlen($binary) > $maxBytes) {
        waqt_upload_error('Selfie image size is not allowed', 422);
    }

    $info = @getimagesizefromstring($binary);
    if ($info === false || empty($info['mime']) || !in_array($info['mime'], ['image/png', 'image/jpeg', 'image/webp'], true)) {
        waqt_upload_error('Invalid selfie image', 422);
    }

    $extMap = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp'];
    $safeDir = trim(str_replace(['..', '\\'], ['', '/'], $relativeDir), '/');
    $uploadDir = __DIR__ . '/../uploads' . ($safeDir !== '' ? '/' . $safeDir : '');
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        waqt_upload_error('Upload directory is not available', 500);
    }

    $safePrefix = preg_replace('/[^a-z0-9_-]/i', '_', $prefix) ?: 'image';
    $filename = $safePrefix . '_' . bin2hex(random_bytes(12)) . '.' . $extMap[$info['mime']];
    $destination = $uploadDir . '/' . $filename;

    if (file_put_contents($destination, $binary, LOCK_EX) === false) {
        waqt_upload_error('Failed to save selfie image', 500);
    }

    $fileKey = 'uploads/' . ($safeDir !== '' ? $safeDir . '/' : '') . $filename;

    if (file_exists(__DIR__ . '/gcs_helper.php')) {
        require_once __DIR__ . '/gcs_helper.php';
        if (waqt_is_gcs_enabled()) {
            waqt_upload_binary_to_gcs($binary, $fileKey, $info['mime']);
        }
    }

    return $fileKey;
}
}

if (!function_exists('waqt_save_private_base64_image')) {
function waqt_save_private_base64_image(string $dataUri, string $relativeDir, string $prefix, int $maxBytes): string
{
    if (!preg_match('#^data:image/(png|jpeg|webp);base64,#i', $dataUri, $matches)) {
        waqt_upload_error('Invalid selfie image', 422);
    }

    $encoded = substr($dataUri, strpos($dataUri, ',') + 1);
    $binary = base64_decode($encoded, true);
    if ($binary === false || strlen($binary) === 0 || strlen($binary) > $maxBytes) {
        waqt_upload_error('Selfie image size is not allowed', 422);
    }

    $info = @getimagesizefromstring($binary);
    if ($info === false || empty($info['mime']) || !in_array($info['mime'], ['image/png', 'image/jpeg', 'image/webp'], true)) {
        waqt_upload_error('Invalid selfie image', 422);
    }

    $extMap = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp'];
    $safeDir = trim(str_replace(['..', '\\'], ['', '/'], $relativeDir), '/');
    $uploadDir = waqt_prepare_storage_dir(waqt_private_storage_root(), $safeDir);
    $safePrefix = preg_replace('/[^a-z0-9_-]/i', '_', $prefix) ?: 'image';
    $filename = $safePrefix . '_' . bin2hex(random_bytes(12)) . '.' . $extMap[$info['mime']];
    $destination = $uploadDir . '/' . $filename;

    if (file_put_contents($destination, $binary, LOCK_EX) === false) {
        waqt_upload_error('Failed to save selfie image', 500);
    }

    $fileKey = 'private://' . ($safeDir !== '' ? $safeDir . '/' : '') . $filename;

    if (file_exists(__DIR__ . '/gcs_helper.php')) {
        require_once __DIR__ . '/gcs_helper.php';
        if (waqt_is_gcs_enabled()) {
            $gcsKey = 'private/' . ($safeDir !== '' ? $safeDir . '/' : '') . $filename;
            try {
                $uploadedToGcs = waqt_upload_binary_to_gcs($binary, $gcsKey, $info['mime']);
            } catch (Throwable $error) {
                @unlink($destination);
                throw $error;
            }
            if (!$uploadedToGcs) {
                @unlink($destination);
                waqt_upload_error('Failed to upload file to Cloud Storage', 500);
            }
            @unlink($destination);
        }
    }

    return $fileKey;
}
}

if (!function_exists('waqt_document_url')) {
function waqt_document_url(string $path, string $prefix = ''): string
{
    $path = trim($path);
    if ($path === '') {
        return '';
    }

    if (waqt_is_private_file_path($path)) {
        return $prefix . 'download-file.php?file=' . rawurlencode($path);
    }

    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    return $prefix . ltrim($path, '/');
}
}

if (!function_exists('waqt_base_url')) {
function waqt_base_url(): string
{
    $scheme = is_https_request() ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'waqtfinance.com';
    if ($host === 'localhost') {
        return $scheme . $host . '/waqt-finance/';
    }

    return $scheme . $host . '/';
}
}

if (!function_exists('waqt_signed_private_file_url')) {
function waqt_signed_private_file_url(string $path, int $ttlSeconds = 604800): string
{
    if (!waqt_is_private_file_path($path)) {
        return waqt_document_url($path);
    }

    $expiresAt = time() + $ttlSeconds;
    $signature = hash_hmac('sha256', $path . '|' . $expiresAt, required_env('APP_SECRET_KEY'));

    return waqt_base_url()
        . 'download-file.php?file=' . rawurlencode($path)
        . '&expires=' . $expiresAt
        . '&sig=' . rawurlencode($signature);
}
}

if (!function_exists('start_secure_session')) {
function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => is_https_request(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}
}

if (!function_exists('csrf_token')) {
function csrf_token(): string
{
    start_secure_session();

    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}
}

if (!function_exists('csrf_field')) {
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8')
        . '">';
}
}

if (!function_exists('request_csrf_token')) {
function request_csrf_token(): string
{
    $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (is_string($headerToken) && $headerToken !== '') {
        return $headerToken;
    }

    $postedToken = $_POST['csrf_token'] ?? '';
    return is_string($postedToken) ? $postedToken : '';
}
}

if (!function_exists('verify_csrf_token')) {
function verify_csrf_token(): bool
{
    start_secure_session();

    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $requestToken = request_csrf_token();

    return is_string($sessionToken)
        && $sessionToken !== ''
        && $requestToken !== ''
        && hash_equals($sessionToken, $requestToken);
}
}

if (!function_exists('require_valid_csrf')) {
function require_valid_csrf(): void
{
    if (verify_csrf_token()) {
        return;
    }

    if (function_exists('json_response')) {
        json_response(['status' => 'error', 'message' => 'Security token expired. Please refresh the page and try again.'], 419);
    }

    http_response_code(419);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'Security token expired. Please refresh the page and try again.']);
    exit;
}
}
