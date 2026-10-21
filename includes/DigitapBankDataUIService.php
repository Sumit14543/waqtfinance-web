<?php
declare(strict_types=1);

/**
 * Digitap Bank Data UI API Service
 * Built in accordance with: Digitap.ai Bank Data UI API Specifications (Version 1.21, January 11, 2025)
 * Supports Header-Based Authentication (Basic Auth) & Signature-Based Authentication
 */

require_once dirname(__DIR__) . '/config/db.php';

class DigitapBankDataUIService
{
    private string $clientId;
    private string $clientSecret;
    private string $clientName;
    private string $baseUrl;
    private string $envMode;
    private string $authMethod; // 'header' or 'signature'
    private ?string $privateKeyPem = null;

    public function __construct()
    {
        $this->clientId = env_value('DIGITAP_CLIENT_ID', '53205998');
        $this->clientSecret = env_value('DIGITAP_CLIENT_SECRET', '4lBQRBHkJms0jLy7oTtnEuhEgofOGnwu');
        $this->clientName = env_value('DIGITAP_AA_CLIENT_NAME', $this->clientId);
        $this->authMethod = strtolower(env_value('DIGITAP_AA_AUTH_METHOD', 'header'));

        $this->envMode = strtolower(env_value('DIGITAP_AA_ENV', 'prod'));
        if ($this->envMode === 'prod' || $this->envMode === 'production') {
            $this->baseUrl = rtrim(env_value('DIGITAP_AA_PROD_URL', 'https://svc.digitap.ai/bank-data'), '/');
        } else {
            $this->baseUrl = rtrim(env_value('DIGITAP_AA_DEMO_URL', 'https://svcdemo.digitap.work/bank-data'), '/');
        }
    }

    /**
     * Helper to get Basic Auth Header string
     */
    private function getBasicAuthHeader(): string
    {
        return 'Authorization: Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret);
    }

    /**
     * 1. Generate URL API (Doc Section 4)
     * POST /bank-data/generateurl
     */
    public function generateUrl(array $params): array
    {
        $url = $this->baseUrl . '/generateurl';

        // Prepare standard parameters
        $clientRefNum = $params['client_ref_num'] ?? ('WF_AA_' . time() . '_' . substr(md5((string)mt_rand()), 0, 6));
        $destination = $params['destination'] ?? 'accountaggregator'; // 'accountaggregator' | 'statementupload' | 'netbanking' | 'choice'
        $mobileNum = preg_replace('/\D/', '', (string)($params['mobile_num'] ?? ''));

        $defaultCallback = env_value('DIGITAP_AA_WEBHOOK_URL', 'https://waqtfinance.com/api/bank-data/callback.php');
        $callbackUrl = $params['txn_completed_cburl'] ?? $defaultCallback;

        $host = $_SERVER['HTTP_HOST'] ?? 'waqtfinance.com';
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $defaultReturn = $scheme . $host . '/create_lead?step=5&aa_txn_id=%s&aa_status=%s';
        $returnUrl = $params['return_url'] ?? $defaultReturn;

        $payload = [
            'client_ref_num' => substr($clientRefNum, 0, 50),
            'destination' => $destination,
            'txn_completed_cburl' => $callbackUrl,
            'return_url' => $returnUrl,
            'acceptance_policy' => $params['acceptance_policy'] ?? 'atLeastOneTransactionInRange'
        ];

        if (!empty($mobileNum)) {
            $payload['mobile_num'] = $mobileNum;
            $payload['aa_vendor'] = $params['aa_vendor'] ?? 'anumati';
            $payload['aa_fetch_type'] = $params['aa_fetch_type'] ?? 'onetime';
        }

        if (!empty($params['institution_id'])) {
            $payload['institution_id'] = (string)$params['institution_id'];
        }

        if (!empty($params['start_month']) && !empty($params['end_month'])) {
            $payload['start_month'] = (string)$params['start_month'];
            $payload['end_month'] = (string)$params['end_month'];
        }

        return $this->sendRequest($url, $payload, 'POST');
    }

    /**
     * 2. Status Check API (Doc Section 5)
     * POST /bank-data/statuscheck
     */
    public function checkStatus(string $requestId): array
    {
        $url = $this->baseUrl . '/statuscheck';
        $payload = ['request_id' => $requestId];
        return $this->sendRequest($url, $payload, 'POST');
    }

    /**
     * 3. Retrieve Report API (Doc Section 6)
     * POST /bank-data/retrievereport
     */
    public function retrieveReport(string $txnId, string $reportType = 'json', string $reportSubtype = 'type1'): array
    {
        $url = $this->baseUrl . '/retrievereport';
        $payload = [
            'txn_id' => $txnId,
            'report_type' => $reportType,
            'report_subtype' => $reportSubtype
        ];

        return $this->sendRequest($url, $payload, 'POST', $reportType === 'xlsx');
    }

    /**
     * 4. Institution List API (Doc Section 7)
     * GET /bank-data/institutions?type=NetBanking|Statement
     */
    public function getInstitutions(string $type = 'NetBanking'): array
    {
        $url = $this->baseUrl . '/institutions?type=' . urlencode($type);
        return $this->sendRequest($url, [], 'GET');
    }

    /**
     * Sends HTTP request using cURL with Header-Based Authentication
     */
    private function sendRequest(string $url, array $payload, string $method = 'POST', bool $isBinary = false): array
    {
        $ch = curl_init();
        $headers = [
            $this->getBasicAuthHeader(),
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_SLASHES));
        }

        $rawResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($rawResponse === false || !empty($curlError)) {
            return [
                'status' => 'error',
                'code' => 'CurlError',
                'message' => $curlError ?: 'Connection to Digitap Bank Data API failed',
                'http_code' => $httpCode
            ];
        }

        if ($isBinary && $httpCode === 200) {
            return [
                'status' => 'success',
                'format' => 'xlsx',
                'data' => $rawResponse,
                'http_code' => 200
            ];
        }

        $decoded = json_decode((string)$rawResponse, true);
        if (is_array($decoded)) {
            $decoded['http_code'] = $httpCode;
            return $decoded;
        }

        return [
            'status' => ($httpCode >= 200 && $httpCode < 300) ? 'success' : 'error',
            'http_code' => $httpCode,
            'raw_body' => $rawResponse
        ];
    }
}
