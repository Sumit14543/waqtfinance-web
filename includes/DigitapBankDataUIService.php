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

    public function __construct()
    {
        $this->clientId = env_value('DIGITAP_CLIENT_ID', '53205998');
        $this->clientSecret = env_value('DIGITAP_CLIENT_SECRET', '4lBQRBHkJms0jLy7oTtnEuhEgofOGnwu');
        $this->clientName = env_value('DIGITAP_AA_CLIENT_NAME', $this->clientId);

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

        // Digitap client_ref_num: Alpha-numeric or dash (-), max 50 chars (NO underscores)
        $cleanRef = preg_replace('/[^A-Za-z0-9\-]/', '-', (string)($params['client_ref_num'] ?? ''));
        if (empty($cleanRef)) {
            $cleanRef = 'WF-AA-' . time() . '-' . substr(md5((string)mt_rand()), 0, 6);
        }
        $clientRefNum = substr($cleanRef, 0, 50);

        $requestedDest = strtolower(trim((string)($params['destination'] ?? 'accountaggregator')));
        $mobileNum = preg_replace('/\D/', '', (string)($params['mobile_num'] ?? ''));

        $defaultCallback = env_value('DIGITAP_AA_WEBHOOK_URL', 'https://waqtfinance.com/api/bank-data/callback.php');
        $callbackUrl = $params['txn_completed_cburl'] ?? $defaultCallback;

        $host = $_SERVER['HTTP_HOST'] ?? 'waqtfinance.com';
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'https://';
        $defaultReturn = $scheme . $host . '/create-lead-form?step=5&aa_txn_id=%s&aa_status=%s';
        $returnUrl = $params['return_url'] ?? $defaultReturn;

        $startMonth = $params['start_month'] ?? date('Y-m', strtotime('-6 months'));
        $endMonth = $params['end_month'] ?? date('Y-m');

        // Build Primary Payload
        $payload = [
            'client_ref_num' => $clientRefNum,
            'destination' => $requestedDest,
            'txn_completed_cburl' => $callbackUrl,
            'return_url' => $returnUrl,
            'start_month' => $startMonth,
            'end_month' => $endMonth
        ];

        if ($requestedDest === 'statementupload') {
            $payload['acceptance_policy'] = $params['acceptance_policy'] ?? 'atLeastOneTransactionInRange';
        }

        if ($requestedDest === 'accountaggregator') {
            // Updated per Digitap Team Official Spec
            $payload['multi_aa'] = isset($params['multi_aa']) ? (int)$params['multi_aa'] : 0;
            $payload['aa_vendor'] = !empty($params['aa_vendor']) ? (string)$params['aa_vendor'] : 'anumati';
            $payload['aa_fetch_type'] = !empty($params['aa_fetch_type']) ? (string)$params['aa_fetch_type'] : 'ONETIME';

            $startDate = $params['start_date'] ?? date('Y-m-d', strtotime('-6 months'));
            $endDate = $params['end_date'] ?? date('Y-m-d');

            $payload['consent_request'] = $params['consent_request'] ?? [
                [
                    'fetch_type' => 'ONETIME',
                    'fi_types' => ['DEPOSIT'],
                    'fi_date_range' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ]
                ]
            ];
        }

        if (!empty($mobileNum)) {
            $payload['mobile_num'] = $mobileNum;
        }

        if (!empty($params['institution_id'])) {
            $payload['institution_id'] = (string)$params['institution_id'];
        }

        $res = $this->sendRequest($url, $payload, 'POST');

        // If accountaggregator returns an error (e.g. InvalidAAConsentRequestError due to missing template on Digitap backend),
        // seamlessly fall back to statementupload which is fully active and verified on this account.
        if (
            $requestedDest === 'accountaggregator' &&
            (!empty($res['code']) && in_array($res['code'], ['InvalidAAConsentRequestError', 'AccessDenied', 'InstitutionDestinationError'], true))
        ) {
            $fallbackPayload = [
                'client_ref_num' => 'WF-STMT-' . time() . '-' . substr(md5((string)mt_rand()), 0, 6),
                'destination' => 'statementupload',
                'txn_completed_cburl' => $callbackUrl,
                'return_url' => $returnUrl,
                'start_month' => $startMonth,
                'end_month' => $endMonth,
                'acceptance_policy' => 'atLeastOneTransactionInRange'
            ];

            if (!empty($params['institution_id'])) {
                $fallbackPayload['institution_id'] = (string)$params['institution_id'];
            }

            $fallbackRes = $this->sendRequest($url, $fallbackPayload, 'POST');
            if (!empty($fallbackRes['status']) && $fallbackRes['status'] === 'success') {
                $fallbackRes['mode'] = 'statementupload';
                $fallbackRes['notice'] = 'Generated via Digitap Bank Statement gateway';
                return $fallbackRes;
            }
        }

        return $res;
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
    public function retrieveReport(string $txnId, string $reportType = 'json', string $reportSubtype = 'type3'): array
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
     * POST /bank-data/institutions
     */
    public function getInstitutions(string $type = 'accountaggregator'): array
    {
        $url = $this->baseUrl . '/institutions';
        return $this->sendRequest($url, ['type' => $type], 'POST');
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
