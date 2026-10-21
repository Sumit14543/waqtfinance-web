<?php
declare(strict_types=1);

// This helper manages interactions with Google Cloud Storage via curl REST API.
require_once __DIR__ . '/../config/db.php';

if (!function_exists('waqt_get_gcs_config')) {
    function waqt_get_gcs_config(): array
    {
        return [
            'provider' => env_value('FILE_STORAGE_PROVIDER', 'LOCAL'),
            'project_id' => env_value('GCS_PROJECT_ID', ''),
            'bucket' => env_value('FILE_UPLOAD_BUCKET', ''),
            'credentials_path' => env_value('GOOGLE_APPLICATION_CREDENTIALS', '')
        ];
    }
}

if (!function_exists('waqt_is_gcs_enabled')) {
    function waqt_is_gcs_enabled(): bool
    {
        $config = waqt_get_gcs_config();
        return strtoupper($config['provider'] ?? 'LOCAL') === 'GCS';
    }
}

if (!function_exists('waqt_gcs_curl_request')) {
    /**
     * @return array{status:int,body:string}
     */
    function waqt_gcs_curl_request(string $url, string $method, array $headers, ?string $body = null): array
    {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('Unable to initialise Cloud Storage request.');
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => array_merge($headers, ['Connection: close']),
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Cloud Storage request failed: ' . ($curlError !== '' ? $curlError : 'transport error'));
        }

        return ['status' => $httpCode, 'body' => (string) $response];
    }
}

if (!function_exists('waqt_get_gcs_access_token')) {
    function waqt_get_gcs_access_token(): string
    {
        static $cachedToken = null;
        static $tokenExpiry = 0;

        if ($cachedToken !== null && time() < ($tokenExpiry - 30)) {
            return $cachedToken;
        }

        $config = waqt_get_gcs_config();
        $credPath = $config['credentials_path'];
        if ($credPath === '') {
            throw new RuntimeException('GOOGLE_APPLICATION_CREDENTIALS not configured.');
        }

        $absoluteCredPath = str_starts_with($credPath, '/') || str_contains($credPath, ':')
            ? $credPath
            : (__DIR__ . '/../' . $credPath);

        if (!is_file($absoluteCredPath) || !is_readable($absoluteCredPath)) {
            throw new RuntimeException('Google credentials JSON file not found or not readable: ' . $absoluteCredPath);
        }

        $json = json_decode(file_get_contents($absoluteCredPath), true);
        if (!$json || empty($json['private_key']) || empty($json['client_email'])) {
            throw new RuntimeException('Invalid credentials JSON format.');
        }

        $privateKey = $json['private_key'];
        $clientEmail = $json['client_email'];

        // Build JWT Header & Payload
        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $now = time();
        $payload = json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/devstorage.read_write',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ]);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = '';
        $dataToSign = $base64UrlHeader . '.' . $base64UrlPayload;

        if (!openssl_sign($dataToSign, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Failed to sign JWT assertion.');
        }

        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        $assertion = $dataToSign . '.' . $base64UrlSignature;

        // Post to Google OAuth Token endpoint
        $tokenResponse = waqt_gcs_curl_request(
            'https://oauth2.googleapis.com/token',
            'POST',
            ['Content-Type: application/x-www-form-urlencoded'],
            http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $assertion
            ])
        );

        if ($tokenResponse['status'] !== 200) {
            throw new RuntimeException('OAuth token request failed with status ' . $tokenResponse['status'] . '.');
        }

        $resData = json_decode($tokenResponse['body'], true);
        if (empty($resData['access_token'])) {
            throw new RuntimeException('OAuth token response did not contain an access token.');
        }

        $cachedToken = (string) $resData['access_token'];
        $tokenExpiry = time() + (int) ($resData['expires_in'] ?? 3600);

        return $cachedToken;
    }
}

if (!function_exists('waqt_upload_binary_to_gcs')) {
    function waqt_upload_binary_to_gcs(string $binaryData, string $gcsKey, string $mimeType): bool
    {
        $config = waqt_get_gcs_config();
        $bucket = $config['bucket'];
        if ($bucket === '') {
            throw new RuntimeException('GCS bucket name not configured.');
        }

        $token = waqt_get_gcs_access_token();

        // GCS REST endpoint for uploading raw media
        $url = 'https://storage.googleapis.com/upload/storage/v1/b/' . rawurlencode($bucket) . '/o?uploadType=media&name=' . rawurlencode($gcsKey);

        $uploadResponse = waqt_gcs_curl_request($url, 'POST', [
            'Authorization: Bearer ' . $token,
            'Content-Type: ' . $mimeType,
            'Content-Length: ' . strlen($binaryData)
        ], $binaryData);

        if (!in_array($uploadResponse['status'], [200, 201], true)) {
            error_log('GCS upload failed with status ' . $uploadResponse['status'] . ' for object ' . $gcsKey);
            return false;
        }

        return true;
    }
}

if (!function_exists('waqt_upload_to_gcs')) {
    function waqt_upload_to_gcs(string $localFilePath, string $gcsKey, string $mimeType): bool
    {
        if (!is_file($localFilePath) || !is_readable($localFilePath)) {
            return false;
        }
        $data = file_get_contents($localFilePath);
        return waqt_upload_binary_to_gcs($data, $gcsKey, $mimeType);
    }
}

if (!function_exists('waqt_download_from_gcs')) {
    function waqt_download_from_gcs(string $gcsKey): ?string
    {
        $config = waqt_get_gcs_config();
        $bucket = $config['bucket'];
        if ($bucket === '') {
            throw new RuntimeException('GCS bucket name not configured.');
        }

        $token = waqt_get_gcs_access_token();

        $url = 'https://storage.googleapis.com/storage/v1/b/' . rawurlencode($bucket) . '/o/' . rawurlencode($gcsKey) . '?alt=media';

        $downloadResponse = waqt_gcs_curl_request($url, 'GET', [
            'Authorization: Bearer ' . $token
        ]);

        if ($downloadResponse['status'] !== 200) {
            error_log('GCS download failed for ' . $gcsKey . ' with status ' . $downloadResponse['status']);
            return null;
        }

        return $downloadResponse['body'];
    }
}
