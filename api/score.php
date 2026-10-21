<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');

require __DIR__ . '/../config/db.php';

start_secure_session();
require_post();
require_valid_csrf();

try {
    if (empty($_SESSION['verified_user']) || $_SESSION['verified_user'] !== true) {
        json_response(['status' => 'error', 'message' => 'Please verify OTP before fetching your credit score.'], 401);
    }

    if (!empty($_SESSION['verified_at']) && time() - (int) $_SESSION['verified_at'] > 600) {
        unset($_SESSION['verified_user'], $_SESSION['verified_email'], $_SESSION['verified_at']);
        json_response(['status' => 'error', 'message' => 'OTP verification expired. Please verify again.'], 401);
    }

    if (!function_exists('curl_init')) {
        json_response(['status' => 'error', 'message' => 'Server cURL extension is not enabled. Please contact support.'], 500);
    }

    $firstName = clean_name((string) ($_POST['first_name'] ?? ''));
    $lastName = clean_name((string) ($_POST['last_name'] ?? ''));
    $mobile = preg_replace('/\D+/', '', (string) ($_POST['mobile'] ?? '')) ?? '';
    $pan = strtoupper(trim((string) ($_POST['pan'] ?? '')));
    $consent = isset($_POST['consent']) && $_POST['consent'] !== '';

    if ($firstName === '') {
        json_response(['status' => 'error', 'message' => 'Please enter your full name.'], 422);
    }

    if (!preg_match('/^[6-9][0-9]{9}$/', $mobile)) {
        json_response(['status' => 'error', 'message' => 'Please enter a valid 10-digit mobile number.'], 422);
    }

    if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]$/', $pan)) {
        json_response(['status' => 'error', 'message' => 'Please enter a valid PAN number.'], 422);
    }

    if (!$consent) {
        json_response(['status' => 'error', 'message' => 'Consent is required to fetch the credit report.'], 422);
    }

    $payload = [
        'Mobile_Number' => $mobile,
        'Full_Name' => trim($firstName . ' ' . $lastName),
        'PAN_Number' => $pan,
        'Callback_Url' => env_value('BIFROST_CALLBACK_URL', 'https://www.waqtfinance.com/api/score-callback') ?? 'https://www.waqtfinance.com/api/score-callback',
        'Concent' => 'Y',
        'Concent_Text' => 'We confirm and undertake that valid end-user consent has been obtained for fetching CIBIL REPORT using MOBILE NUMBER, and that such consent remains active and unrevoked at the time of this request.',
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => env_value('BIFROST_API_URL', 'https://bifrost.unifers.ai/enrich/get-cibil-report'),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
        CURLOPT_HTTPHEADER => [
            'Authorization: ' . required_env('BIFROST_API_TOKEN'),
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);

    $response = curl_exec($curl);
    $curlError = curl_error($curl);
    $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    $verifiedEmail = $_SESSION['verified_email'] ?? 'unknown';
    unset($_SESSION['verified_user'], $_SESSION['verified_email'], $_SESSION['verified_at']);

    if ($curlError !== '') {
        json_response(['status' => 'error', 'message' => 'Unable to reach credit bureau server. Please try again.'], 502);
    }

    $responseData = json_decode((string) $response, true);
    if (!is_array($responseData)) {
        json_response(['status' => 'error', 'message' => 'Invalid response received from credit bureau.'], 502);
    }

    if ($httpCode < 200 || $httpCode >= 300 || !empty($responseData['error'])) {
        $apiMessage = isset($responseData['message']) && is_string($responseData['message'])
            ? $responseData['message']
            : 'Credit bureau rejected the request.';
        json_response(['status' => 'error', 'message' => $apiMessage], 502);
    }

    if (isset($responseData['data']['errorType'])) {
        $apiMessage = isset($responseData['data']['errorMessage']) && is_string($responseData['data']['errorMessage'])
            ? $responseData['data']['errorMessage']
            : 'Credit bureau service error.';
        json_response(['status' => 'error', 'message' => $apiMessage], 502);
    }

    $pdfUrl = extract_report_url($responseData);
    $score = extract_score($responseData);

    if ($score === null && $pdfUrl === null) {
        json_response(['status' => 'error', 'message' => 'Credit report URL was not available in the bureau response. Please try again later.'], 502);
    }

    $rating = $score === null ? 'Report Ready' : score_rating($score);
    $message = $score === null
        ? 'Your CIBIL report is ready. Download the PDF to view your score and full bureau details.'
        : 'Your CIBIL score has been retrieved successfully.';

    // Log report to database for lead tracking
    try {
        $logStmt = $conn->prepare(
            'INSERT INTO cibil_reports (full_name, email, mobile, pan, score, pdf_url, ref_id, raw_response)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $fullName = trim($firstName . ' ' . $lastName);
        $refId = $responseData['data']['refId'] ?? null;
        $redactedResponse = json_encode(waqt_redact_sensitive_data($responseData), JSON_UNESCAPED_SLASHES);
        if ($redactedResponse === false) {
            $redactedResponse = null;
        }
        $logStmt->bind_param('ssssisss', $fullName, $verifiedEmail, $mobile, $pan, $score, $pdfUrl, $refId, $redactedResponse);
        $logStmt->execute();
    } catch (Throwable $logError) {
        error_log('CIBIL Logging Error: ' . $logError->getMessage());
    }

    json_response([
        'status' => 'success',
        'score' => $score,
        'rating' => $rating,
        'message' => $message,
        'pdf_url' => $pdfUrl,
        'ref_id' => isset($responseData['data']['refId']) && is_string($responseData['data']['refId'])
            ? $responseData['data']['refId']
            : null,
    ]);
} catch (Throwable $e) {
    report_exception($e);
}

function extract_score(array $responseData): ?int
{
    $paths = [
        ['data', 'score'],
        ['data', 'cibil_score'],
        ['data', 'credit_score'],
        ['data', 'result', 'score'],
        ['data', 'result', 'cibil_score'],
        ['data', 'report', 'score'],
        ['data', 'result', 'SCORES', 'SCORE', 'SCORE-VALUE'],
    ];

    foreach ($paths as $path) {
        $value = nested_value($responseData, $path);
        if (is_numeric($value)) {
            $score = (int) $value;
            if ($score >= 300 && $score <= 900) {
                return $score;
            }
        }
    }

    return null;
}

function extract_report_url(array $responseData): ?string
{
    $paths = [
        ['data', 'reportUrl'],
        ['data', 'result', 'reportUrl'],
        ['data', 'report_url'],
        ['data', 'pdf_url'],
        ['data', 'report', 'pdf_url'],
    ];

    foreach ($paths as $path) {
        $value = nested_value($responseData, $path);
        if (is_string($value) && filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
    }

    return null;
}

function nested_value(array $source, array $path)
{
    $current = $source;
    foreach ($path as $key) {
        if (!is_array($current) || !array_key_exists($key, $current)) {
            return null;
        }
        $current = $current[$key];
    }

    return $current;
}

function score_rating(int $score): string
{
    if ($score >= 750) {
        return 'Excellent';
    }

    if ($score >= 700) {
        return 'Good';
    }

    if ($score >= 650) {
        return 'Fair';
    }

    return 'Poor';
}
