<?php
declare(strict_types=1);

/**
 * Aadhaar DigiLocker Verification Initiation Handler
 * Endpoint: /aadhaar_initiate.php
 * Powered by Digitap DigiLocker SDK & Verification API (Production)
 */

require_once __DIR__ . '/config/db.php';

if (!headers_sent() && basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'aadhaar_initiate.php') {
    header('Content-Type: application/json; charset=utf-8');
}

// 1. Verhoeff Checksum Validation for UIDAI 12-digit Aadhaar
function isValidVerhoeff(string $num): bool
{
    $d = [
        [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
        [1, 2, 3, 4, 0, 6, 7, 8, 9, 5],
        [2, 3, 4, 0, 1, 7, 8, 9, 5, 6],
        [3, 4, 0, 1, 2, 8, 9, 5, 6, 7],
        [4, 0, 1, 2, 3, 9, 5, 6, 7, 8],
        [5, 9, 8, 7, 6, 0, 4, 3, 2, 1],
        [6, 5, 9, 8, 7, 1, 0, 4, 3, 2],
        [7, 6, 5, 9, 8, 2, 1, 0, 4, 3],
        [8, 7, 6, 5, 9, 3, 2, 1, 0, 4],
        [9, 8, 7, 6, 5, 4, 3, 2, 1, 0]
    ];
    $p = [
        [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
        [1, 5, 7, 6, 2, 8, 3, 0, 9, 4],
        [5, 8, 0, 3, 7, 9, 6, 1, 4, 2],
        [8, 9, 1, 6, 0, 4, 3, 5, 2, 7],
        [9, 4, 5, 3, 1, 2, 6, 8, 7, 0],
        [4, 2, 8, 6, 5, 7, 3, 9, 0, 1],
        [2, 7, 9, 3, 8, 0, 6, 4, 1, 5],
        [7, 0, 4, 6, 9, 1, 3, 2, 5, 8]
    ];
    $c = 0;
    $digits = array_map('intval', str_split(strrev($num)));
    foreach ($digits as $i => $digit) {
        $c = $d[$c][$p[$i % 8][$digit]];
    }
    return $c === 0;
}

// 2. Read Request Input (Supports JSON body, Form POST, or GET)
$rawInput = file_get_contents('php://input');
$jsonBody = json_decode((string)$rawInput, true);
$inputAadhaar = '';

if (is_array($jsonBody) && !empty($jsonBody['aadhaar'])) {
    $inputAadhaar = (string)$jsonBody['aadhaar'];
} elseif (!empty($_POST['aadhaar'])) {
    $inputAadhaar = (string)$_POST['aadhaar'];
} elseif (!empty($_GET['aadhaar'])) {
    $inputAadhaar = (string)$_GET['aadhaar'];
}

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false)
    || (is_array($jsonBody) && !empty($jsonBody));

// If included by another script and no aadhaar input provided, do not auto-exit
if (empty($inputAadhaar) && basename($_SERVER['SCRIPT_FILENAME'] ?? '') !== 'aadhaar_initiate.php') {
    return;
}

// Sanitize Aadhaar Number (if provided)
$cleanAadhaar = preg_replace('/\D/', '', $inputAadhaar);
$maskedAadhaar = 'XXXX-XXXX-AADH';
$last4 = '';

if (!empty($cleanAadhaar)) {
    if (strlen($cleanAadhaar) !== 12) {
        http_response_code(400);
        $errMsg = 'Please enter a valid 12-digit Aadhaar number.';
        if ($isAjax) {
            echo json_encode(['status' => 'error', 'message' => $errMsg]);
        } else {
            $_SESSION['aadhaar_error'] = $errMsg;
            header('Location: /aadhaar-verification.php');
        }
        exit;
    }
    $last4 = substr($cleanAadhaar, -4);
    $maskedAadhaar = 'XXXX-XXXX-' . $last4;
}

// 3. Extract Applicant Metadata (Name, Mobile, Email) for Digitap DigiLocker Request
$fullName = trim((string)(
    $jsonBody['fullName'] ?? $jsonBody['name'] ??
    $_POST['fullName'] ?? $_POST['name'] ??
    $_SESSION['lead_full_name'] ?? $_SESSION['lead_name'] ?? $_SESSION['name'] ?? ''
));

if (empty($fullName)) {
    $fullName = 'Applicant User';
}

$nameParts = preg_split('/\s+/', $fullName);
$firstName = !empty($nameParts[0]) ? preg_replace('/[^a-zA-Z]/', '', $nameParts[0]) : 'Applicant';
if (empty($firstName)) $firstName = 'Applicant';

$lastName = count($nameParts) > 1 ? preg_replace('/[^a-zA-Z]/', '', implode('', array_slice($nameParts, 1))) : 'User';
if (empty($lastName)) $lastName = 'User';

// Sanitize Mobile (must match ^[6-9]\d{9}$)
$rawMobile = (string)(
    $jsonBody['mobile'] ?? $_POST['mobile'] ??
    $_SESSION['lead_mobile'] ?? $_SESSION['mobile'] ?? ''
);
$cleanMobile = preg_replace('/\D/', '', $rawMobile);
if (strlen($cleanMobile) > 10) {
    $cleanMobile = substr($cleanMobile, -10);
}
if (!preg_match('/^[6-9]\d{9}$/', $cleanMobile)) {
    $cleanMobile = '9876543210';
}

// Sanitize Email
$rawEmail = trim((string)(
    $jsonBody['email'] ?? $_POST['email'] ??
    $_SESSION['lead_email'] ?? $_SESSION['email'] ?? ''
));
$cleanEmail = filter_var($rawEmail, FILTER_VALIDATE_EMAIL) ? $rawEmail : 'lead@waqtfinance.com';

// Generate Unique ID conformant with Digitap: 1-80 chars, word characters, dots, @, hyphens
$uniqueId = 'uid-' . time() . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
$referenceId = $uniqueId;

// Optional application context
$appId = $_SESSION['app_id'] ?? (string)($jsonBody['app_id'] ?? $_POST['app_id'] ?? '');

// 4. Retrieve Digitap Credentials & Configuration
$clientId = env_value('DIGITAP_CLIENT_ID', '53205998');
$clientSecret = env_value('DIGITAP_CLIENT_SECRET', '4lBQRBHkJms0jLy7oTtnEuhEgofOGnwu');
$generateUrl = env_value('DIGITAP_DIGILOCKER_GENERATE_URL', 'https://api.digitap.ai/ent/v1/kyc/generate-url');

$isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1'], true)
    || strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost:') === 0;

if ($isLocal) {
    $uriPrefix = '';
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    if ($scriptDir !== '/' && $scriptDir !== '\\' && !empty($scriptDir)) {
        $uriPrefix = rtrim(str_replace('\\', '/', $scriptDir), '/');
    }
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $redirectionUrl = $proto . '://' . $_SERVER['HTTP_HOST'] . $uriPrefix . '/aadhaar-callback';
} else {
    $redirectionUrl = env_value('DIGITAP_DIGILOCKER_REDIRECT_URL', 'https://waqtfinance.com/aadhaar-callback');
}

if (empty($clientId) || empty($clientSecret)) {
    http_response_code(500);
    $errMsg = 'Digitap API credentials missing in server configuration.';
    if ($isAjax) {
        echo json_encode(['status' => 'error', 'message' => $errMsg]);
    } else {
        $_SESSION['aadhaar_error'] = $errMsg;
        header('Location: /aadhaar-verification.php');
    }
    exit;
}

// 5. Call Digitap DigiLocker Generate URL API (/ent/v1/kyc/generate-url)
$base64Auth = base64_encode($clientId . ':' . $clientSecret);
$payload = [
    'serviceId' => '4',
    'firstName' => $firstName,
    'lastName' => $lastName,
    'uid' => $uniqueId,
    'mobile' => $cleanMobile,
    'emailId' => $cleanEmail,
    'isSendOtp' => false,
    'isHideExplanationScreen' => false,
    'redirectionUrl' => $redirectionUrl,
    'webhookUrl' => $redirectionUrl
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $generateUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_HTTPHEADER => [
        'ent_authorization: ' . $base64Auth,
        'Authorization: Basic ' . $base64Auth,
        'Content-Type: application/json',
        'Accept: application/json'
    ],
    CURLOPT_SSL_VERIFYPEER => true
]);

$responseBody = curl_exec($ch);
$curlErr = curl_error($ch);
$httpStatus = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if (!empty($curlErr) || $httpStatus < 200 || $httpStatus >= 300) {
    error_log("Digitap DigiLocker Init Error [HTTP $httpStatus]: $curlErr | Response: $responseBody");
    http_response_code(502);
    $errMsg = 'Unable to connect to Aadhaar DigiLocker verification gateway. Please try again.';
    if ($isAjax) {
        echo json_encode(['status' => 'error', 'message' => $errMsg, 'detail' => $curlErr]);
    } else {
        $_SESSION['aadhaar_error'] = $errMsg;
        header('Location: /aadhaar-verification.php');
    }
    exit;
}

$data = json_decode((string)$responseBody, true);

if (!is_array($data) || empty($data['model']['kycUrl']) && empty($data['model']['url'])) {
    $apiMsg = $data['msg'] ?? $data['message'] ?? 'Aadhaar DigiLocker initiation failed. Please try again.';
    error_log("Digitap DigiLocker Init Failure: " . json_encode($data));
    http_response_code(400);
    if ($isAjax) {
        echo json_encode(['status' => 'error', 'message' => $apiMsg]);
    } else {
        $_SESSION['aadhaar_error'] = $apiMsg;
        header('Location: /aadhaar-verification.php');
    }
    exit;
}

$authUrl = (string)($data['model']['kycUrl'] ?: $data['model']['url']);
$transactionId = (string)($data['model']['transactionId'] ?? '');

// 6. Save Session & Durable Storage Mapping
$_SESSION['aadhaar_reference_id'] = $referenceId;
$_SESSION['aadhaar_unique_id'] = $uniqueId;
$_SESSION['aadhaar_transaction_id'] = $transactionId;
$_SESSION['aadhaar_masked'] = $maskedAadhaar;
$_SESSION['aadhaar_last4'] = $last4;
$_SESSION['aadhaar_number'] = $cleanAadhaar;
$_SESSION['aadhaar_initiated_at'] = time();

if (!isset($_SESSION['aadhaar_map']) || !is_array($_SESSION['aadhaar_map'])) {
    $_SESSION['aadhaar_map'] = [];
}
$_SESSION['aadhaar_map'][$uniqueId] = [
    'referenceId' => $referenceId,
    'transactionId' => $transactionId,
    'aadhaar' => $cleanAadhaar,
    'masked' => $maskedAadhaar,
    'app_id' => $appId,
    'name' => $fullName,
    'mobile' => $cleanMobile,
    'time' => time()
];

// Persist durable file mapping in storage/ directory
$mappingDir = __DIR__ . '/storage';
if (!is_dir($mappingDir)) {
    @mkdir($mappingDir, 0755, true);
}
$mappingFile = $mappingDir . '/aadhaar_kyc_mappings.json';
try {
    $existing = [];
    if (file_exists($mappingFile)) {
        $existing = json_decode((string)file_get_contents($mappingFile), true) ?: [];
    }
    $mappingData = [
        'referenceId' => $referenceId,
        'uniqueId' => $uniqueId,
        'transactionId' => $transactionId,
        'aadhaar_masked' => $maskedAadhaar,
        'app_id' => $appId,
        'name' => $fullName,
        'mobile' => $cleanMobile,
        'created_at' => date('Y-m-d H:i:s')
    ];
    $existing[$uniqueId] = $mappingData;
    if (!empty($transactionId)) {
        $existing[$transactionId] = $mappingData;
    }
    if (count($existing) > 400) {
        $existing = array_slice($existing, -400, null, true);
    }
    @file_put_contents($mappingFile, json_encode($existing, JSON_PRETTY_PRINT));
} catch (Throwable $e) {
    error_log("Aadhaar mapping storage notice: " . $e->getMessage());
}

// If loan application exists in DB, update aadhaar_unique_id
if (!empty($appId)) {
    try {
        $conn = db();
        if (loan_flow_column_exists($conn, 'loan_applications', 'aadhaar_unique_id')) {
            loan_flow_update_application($conn, $appId, [
                'aadhaar_unique_id' => $uniqueId,
                'aadhaar_masked' => $maskedAadhaar
            ]);
        }
    } catch (Throwable $e) {
        error_log("DB update notice: " . $e->getMessage());
    }
}

// 7. Output Response: JSON for AJAX or HTTP 302 Redirect for Form
if ($isAjax) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Digitap DigiLocker authorization URL generated successfully',
        'authorizationUrl' => $authUrl,
        'transactionId' => $transactionId,
        'uniqueId' => $uniqueId,
        'referenceId' => $referenceId,
        'maskedAadhaar' => $maskedAadhaar
    ], JSON_UNESCAPED_SLASHES);
} else {
    header('Location: ' . $authUrl, true, 302);
}
exit;
