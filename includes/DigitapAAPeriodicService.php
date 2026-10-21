<?php
declare(strict_types=1);

/**
 * Digitap Account Aggregator (AA) Bank Data Periodic API Service
 * Powered by Digitap.ai Bank Data AA Periodic API v1.0
 */

require_once dirname(__DIR__) . '/config/db.php';

class DigitapAAPeriodicService
{
    private string $clientId;
    private string $clientSecret;
    private string $clientName;
    private string $baseUrl;
    private ?string $privateKeyPem = null;

    public function __construct()
    {
        $this->clientId = env_value('DIGITAP_CLIENT_ID', '53205998');
        $this->clientSecret = env_value('DIGITAP_CLIENT_SECRET', '4lBQRBHkJms0jLy7oTtnEuhEgofOGnwu');
        $this->clientName = env_value('DIGITAP_AA_CLIENT_NAME', $this->clientId);

        $envMode = strtolower(env_value('DIGITAP_AA_ENV', 'demo'));
        if ($envMode === 'prod' || $envMode === 'production') {
            $this->baseUrl = rtrim(env_value('DIGITAP_AA_PROD_URL', 'https://svc.digitap.ai/bank-data'), '/');
        } else {
            $this->baseUrl = rtrim(env_value('DIGITAP_AA_DEMO_URL', 'https://svcdemo.digitap.work/bank-data'), '/');
        }

        $this->loadPrivateKey();
    }

    /**
     * Loads the RSA private key (.pem) or auto-generates a fallback key pair
     */
    private function loadPrivateKey(): void
    {
        $keyPath = env_value('DIGITAP_AA_PRIVATE_KEY_PATH', '');
        if (!empty($keyPath)) {
            $fullPath = (strpos($keyPath, '/') === 0 || preg_match('/^[a-zA-Z]:/', $keyPath))
                ? $keyPath
                : dirname(__DIR__) . '/' . ltrim($keyPath, '/\\');
            if (file_exists($fullPath)) {
                $this->privateKeyPem = (string)file_get_contents($fullPath);
                return;
            }
        }

        // Check default storage/keys/digitap_aa_private.pem
        $defaultKeyFile = dirname(__DIR__) . '/storage/keys/digitap_aa_private.pem';
        if (file_exists($defaultKeyFile)) {
            $this->privateKeyPem = (string)file_get_contents($defaultKeyFile);
            return;
        }

        // Auto-generate and persist a standard 2048-bit RSA key pair if none exists
        $keyDir = dirname(__DIR__) . '/storage/keys';
        if (!is_dir($keyDir)) {
            @mkdir($keyDir, 0755, true);
        }

        // Try OpenSSL with Windows config fallback if needed
        $configArgs = [
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA
        ];
        $winOpenSslConf = 'C:/xampp/apache/bin/openssl.cnf';
        if (file_exists($winOpenSslConf)) {
            $configArgs['config'] = $winOpenSslConf;
        }

        $newKey = openssl_pkey_new($configArgs);
        if ($newKey !== false && openssl_pkey_export($newKey, $pem, null, $configArgs)) {
            @file_put_contents($defaultKeyFile, $pem);
            $pub = openssl_pkey_get_details($newKey);
            if (!empty($pub['key'])) {
                @file_put_contents($keyDir . '/digitap_aa_public.pem', $pub['key']);
            }
            $this->privateKeyPem = $pem;
        }
    }

    /**
     * Generates RSA-SHA256 Digital Signature for input payload array
     */
    public function generateSignature(array $payload): string
    {
        if (empty($this->privateKeyPem)) {
            return '';
        }

        $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $signature = '';

        $configArgs = [];
        $winOpenSslConf = 'C:/xampp/apache/bin/openssl.cnf';
        if (file_exists($winOpenSslConf)) {
            $configArgs['config'] = $winOpenSslConf;
        }

        $keyResource = openssl_pkey_get_private($this->privateKeyPem);
        if ($keyResource === false) {
            return '';
        }

        $success = openssl_sign($jsonPayload, $signature, $keyResource, OPENSSL_ALGO_SHA256);
        return $success ? base64_encode($signature) : '';
    }

    /**
     * Step 1: Initiate Periodic Fetch API (/bank-data/initiate_periodic_fetch)
     * Returns array with status, code, txn_id, main_txn_id
     */
    public function initiatePeriodicFetch(string $mainTxnId): array
    {
        $url = $this->baseUrl . '/initiate_periodic_fetch';

        $payload = [
            'client_name' => $this->clientName,
            'main_txn_id' => $mainTxnId
        ];

        $signature = $this->generateSignature($payload);

        $body = [
            'payload' => $payload,
            'signature' => $signature
        ];

        return $this->sendRequest($url, $body);
    }

    /**
     * Step 2: Status Check API (/bank-data/statuscheck)
     */
    public function checkStatus(string $txnId, string $requestId = ''): array
    {
        $url = $this->baseUrl . '/statuscheck';

        $payload = [
            'client_name' => $this->clientName,
            'request_id' => $requestId,
            'txn_id' => $txnId
        ];

        $signature = $this->generateSignature($payload);

        $body = [
            'payload' => $payload,
            'signature' => $signature
        ];

        return $this->sendRequest($url, $body);
    }

    /**
     * Step 3: Retrieve Report API (/bank-data/retrievereport)
     * Returns JSON or raw Excel (.xlsx) file data
     */
    public function retrieveReport(string $txnId): array
    {
        $url = $this->baseUrl . '/retrievereport';

        $payload = [
            'client_name' => $this->clientName,
            'txn_id' => $txnId
        ];

        $signature = $this->generateSignature($payload);

        $body = [
            'payload' => $payload,
            'signature' => $signature
        ];

        return $this->sendRequest($url, $body);
    }

    /**
     * Helper to execute CURL request with Basic Auth & Content-Type
     */
    private function sendRequest(string $url, array $body): array
    {
        $base64Auth = base64_encode($this->clientId . ':' . $this->clientSecret);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT => 45,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . $base64Auth,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if (!empty($err) || $httpCode === 0) {
            return [
                'status' => 'error',
                'code' => 'ConnectionError',
                'msg' => $err ?: 'Connection to Digitap AA service failed',
                'http_code' => $httpCode
            ];
        }

        // If Excel format (.xlsx) returned
        if (strpos($contentType, 'spreadsheetml') !== false || strpos($contentType, 'application/vnd.openxmlformats') !== false) {
            return [
                'status' => 'success',
                'code' => 'FileDownloaded',
                'format' => 'xlsx',
                'data' => $response,
                'http_code' => $httpCode
            ];
        }

        $decoded = json_decode((string)$response, true);
        if (is_array($decoded)) {
            $decoded['http_code'] = $httpCode;
            return $decoded;
        }

        return [
            'status' => 'unknown',
            'raw_response' => $response,
            'http_code' => $httpCode
        ];
    }
}
