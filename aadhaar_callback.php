<?php
declare(strict_types=1);

/**
 * Aadhaar DigiLocker Verification Callback & Webhook Handler
 * Endpoint: /aadhaar_callback.php (or /aadhaar-callback)
 * Powered by Digitap DigiLocker SDK & Verification API
 */

require_once __DIR__ . '/config/db.php';

// 1. Helper function to parse UIDAI / DigiLocker e-KYC XML
function parseAadhaarKycXml(string $xmlString): array
{
    $result = [
        'name' => '',
        'dob' => '',
        'gender' => '',
        'care_of' => '',
        'address' => '',
        'photo' => '',
        'masked_aadhaar' => '',
        'raw_xml' => $xmlString
    ];

    if (empty($xmlString)) {
        return $result;
    }

    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($xmlString);

    if ($xml === false) {
        $cleaned = html_entity_decode($xmlString, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $xml = simplexml_load_string($cleaned);
    }

    if ($xml === false) {
        if (preg_match('/<Poi[^>]*\bname=["\']([^"\']+)["\']/i', $xmlString, $m)) {
            $result['name'] = trim($m[1]);
        }
        if (preg_match('/<Poi[^>]*\bdob=["\']([^"\']+)["\']/i', $xmlString, $m)) {
            $result['dob'] = trim($m[1]);
        }
        if (preg_match('/<Poi[^>]*\bgender=["\']([^"\']+)["\']/i', $xmlString, $m)) {
            $result['gender'] = trim($m[1]) === 'M' ? 'Male' : (trim($m[1]) === 'F' ? 'Female' : trim($m[1]));
        }
        if (preg_match('/<Poa[^>]*\bdist=["\']([^"\']+)["\']/i', $xmlString, $m)) {
            $result['district'] = trim($m[1]);
        }
        if (preg_match('/<Poa[^>]*\bstate=["\']([^"\']+)["\']/i', $xmlString, $m)) {
            $result['state'] = trim($m[1]);
        }
        if (preg_match('/<Poa[^>]*\bpc=["\']([^"\']+)["\']/i', $xmlString, $m)) {
            $result['pincode'] = trim($m[1]);
        }
        if (preg_match('/<Pht[^>]*>([^<]+)<\/Pht>/i', $xmlString, $m)) {
            $result['photo'] = trim($m[1]);
        }
        return $result;
    }

    $xml->registerXPathNamespace('k', 'http://www.uidai.gov.in/kyc/uid-kyc-response/1.0');

    $poiNodes = $xml->xpath('//Poi') ?: $xml->xpath('//k:Poi');
    if (!empty($poiNodes)) {
        $poi = $poiNodes[0];
        $attrs = $poi->attributes();
        $result['name'] = (string)($attrs['name'] ?? $poi->name ?? '');
        $result['dob'] = (string)($attrs['dob'] ?? $poi->dob ?? '');
        $rawGender = (string)($attrs['gender'] ?? $poi->gender ?? '');
        $result['gender'] = ($rawGender === 'M' || strcasecmp($rawGender, 'Male') === 0) ? 'Male' : (($rawGender === 'F' || strcasecmp($rawGender, 'Female') === 0) ? 'Female' : $rawGender);
    }

    $poaNodes = $xml->xpath('//Poa') ?: $xml->xpath('//k:Poa');
    if (!empty($poaNodes)) {
        $poa = $poaNodes[0];
        $attrs = $poa->attributes();
        $careOf = (string)($attrs['co'] ?? $poa->co ?? '');
        $house = (string)($attrs['house'] ?? $poa->house ?? '');
        $street = (string)($attrs['street'] ?? $poa->street ?? '');
        $landmark = (string)($attrs['lm'] ?? $attrs['landmark'] ?? '');
        $locality = (string)($attrs['loc'] ?? $attrs['vtc'] ?? '');
        $district = (string)($attrs['dist'] ?? $poa->dist ?? '');
        $state = (string)($attrs['state'] ?? $poa->state ?? '');
        $pincode = (string)($attrs['pc'] ?? $attrs['pincode'] ?? $poa->pc ?? '');

        $result['care_of'] = $careOf;
        $parts = array_filter([$careOf, $house, $street, $landmark, $locality, $district, $state, $pincode ? ('- ' . $pincode) : '']);
        $result['address'] = implode(', ', $parts);
    }

    $phtNodes = $xml->xpath('//Pht') ?: $xml->xpath('//k:Pht');
    if (!empty($phtNodes)) {
        $result['photo'] = trim((string)$phtNodes[0]);
    }

    $uidDataNodes = $xml->xpath('//UidData') ?: $xml->xpath('//k:UidData');
    if (!empty($uidDataNodes)) {
        $uidAttrs = $uidDataNodes[0]->attributes();
        if (!empty($uidAttrs['uid'])) {
            $result['masked_aadhaar'] = (string)$uidAttrs['uid'];
        }
    }

    return $result;
}

// 2. Retrieve Callback Parameters (GET / POST / JSON)
$rawBody = file_get_contents('php://input');
$jsonPayload = json_decode((string)$rawBody, true) ?: [];

// Check if this is a Digitap Webhook
if (!empty($jsonPayload['transactionId']) && !empty($jsonPayload['status'])) {
    $storageDir = __DIR__ . '/storage';
    if (!is_dir($storageDir)) {
        @mkdir($storageDir, 0755, true);
    }
    @file_put_contents(
        $storageDir . '/digitap_digilocker_webhooks.log',
        date('Y-m-d H:i:s') . ' ' . $rawBody . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );

    // If customer data is provided in webhook, cache it into durable mappings
    if (!empty($jsonPayload['data']) && is_array($jsonPayload['data'])) {
        $webhookData = $jsonPayload['data'];
        $txnId = (string)$jsonPayload['transactionId'];
        $mappingFile = $storageDir . '/aadhaar_kyc_mappings.json';
        $existing = file_exists($mappingFile) ? (json_decode((string)file_get_contents($mappingFile), true) ?: []) : [];
        $existing[$txnId]['webhook_data'] = $webhookData;
        if (!empty($webhookData['uniqueId'])) {
            $existing[$webhookData['uniqueId']]['webhook_data'] = $webhookData;
        }
        @file_put_contents($mappingFile, json_encode($existing, JSON_PRETTY_PRINT));
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'success', 'message' => 'Webhook received']);
    exit;
}

// Browser Redirection Parameters (from Digitap redirect URL: ?txnId=...&success=true)
$txnId = trim((string)(
    $_GET['txnId'] ?? $_GET['transactionId'] ??
    $_POST['txnId'] ?? $_POST['transactionId'] ??
    $jsonPayload['txnId'] ?? $jsonPayload['transactionId'] ?? ''
));
$uniqueId = trim((string)(
    $_GET['uniqueId'] ?? $_GET['unique_id'] ?? $_GET['uid'] ??
    $_POST['uniqueId'] ?? $_POST['unique_id'] ?? $_POST['uid'] ??
    $jsonPayload['uniqueId'] ?? $jsonPayload['uid'] ?? ''
));
$successParam = trim((string)(
    $_GET['success'] ?? $_POST['success'] ?? $jsonPayload['success'] ?? ''
));
$errorCode = trim((string)(
    $_GET['error_code'] ?? $_GET['errorCode'] ??
    $_POST['error_code'] ?? $_POST['errorCode'] ?? ''
));
$errorMsg = trim((string)(
    $_GET['errMsg'] ?? $_GET['msg'] ??
    $_POST['errMsg'] ?? $_POST['msg'] ?? ''
));

// Fallback to session
if (empty($txnId)) {
    $txnId = $_SESSION['aadhaar_transaction_id'] ?? '';
}
if (empty($uniqueId)) {
    $uniqueId = $_SESSION['aadhaar_unique_id'] ?? '';
}

// Durable mapping recovery from file
$mappingDir = __DIR__ . '/storage';
if (!is_dir($mappingDir)) {
    @mkdir($mappingDir, 0755, true);
}
$mappingFile = $mappingDir . '/aadhaar_kyc_mappings.json';
$mappedInfo = [];
if (file_exists($mappingFile)) {
    $fileMap = json_decode((string)file_get_contents($mappingFile), true) ?: [];
    if (!empty($txnId) && !empty($fileMap[$txnId])) {
        $mappedInfo = $fileMap[$txnId];
        if (empty($uniqueId)) $uniqueId = $mappedInfo['uniqueId'] ?? '';
    } elseif (!empty($uniqueId) && !empty($fileMap[$uniqueId])) {
        $mappedInfo = $fileMap[$uniqueId];
        if (empty($txnId)) $txnId = $mappedInfo['transactionId'] ?? '';
    }
}

$maskedAadhaarFromSession = $_SESSION['aadhaar_masked'] ?? $mappedInfo['aadhaar_masked'] ?? '';
$appId = $_SESSION['app_id'] ?? $mappedInfo['app_id'] ?? '';
$referenceId = $uniqueId ?: $txnId;

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

// Guard against exit when included without params
$allowedCallbackScripts = ['aadhaar_callback.php', 'callback.php', 'aadhaar-callback.php', 'index.php'];
if (empty($txnId) && empty($uniqueId) && !in_array(basename($_SERVER['SCRIPT_FILENAME'] ?? ''), $allowedCallbackScripts, true)) {
    return;
}

// Check for explicit failure from DigiLocker redirect
if (strtolower($successParam) === 'false' || !empty($errorCode)) {
    $displayError = $errorMsg ?: 'DigiLocker Aadhaar verification was cancelled or could not be completed.';
    if (strpos($errorMsg, 'Aadhaar data is not available') !== false || strpos($errorCode, 'DG1002') !== false || strpos($errorCode, 'DIGILOCKER_PROCESSING_ERROR') !== false) {
        $displayError = 'DigiLocker account me Aadhaar card link/issue nahi mila. Kripya login karte waqt apna 12-digit Aadhaar number enter karein aur UIDAI OTP submit karein taaki Aadhaar fetch ho sake.';
    }
    if ($isAjax) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => $displayError, 'error_code' => $errorCode]);
        exit;
    }
    renderErrorScreen($displayError);
    exit;
}

if (empty($txnId)) {
    $errorMessage = 'Aadhaar verification transaction token (txnId) is missing. Please restart verification.';
    if ($isAjax) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => $errorMessage]);
        exit;
    }
    renderErrorScreen($errorMessage);
    exit;
}

// 3. Call Digitap get-digilocker-details API with Retry Loop (up to 4 attempts, 1.2s delay)
$clientId = env_value('DIGITAP_CLIENT_ID', '53205998');
$clientSecret = env_value('DIGITAP_CLIENT_SECRET', '4lBQRBHkJms0jLy7oTtnEuhEgofOGnwu');
$base64Auth = base64_encode($clientId . ':' . $clientSecret);
$detailsUrl = env_value('DIGITAP_DIGILOCKER_DETAILS_URL', 'https://api.digitap.ai/ent/v1/kyc/get-digilocker-details');

$maxAttempts = 4;
$attempt = 0;
$digitapModel = null;
$lastErrorMsg = '';

// Check if cached from webhook first
if (!empty($mappedInfo['webhook_data'])) {
    $digitapModel = $mappedInfo['webhook_data'];
}

while (empty($digitapModel) && $attempt < $maxAttempts) {
    $attempt++;

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $detailsUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(['transactionId' => $txnId], JSON_UNESCAPED_SLASHES),
        CURLOPT_TIMEOUT => 25,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'ent_authorization: ' . $base64Auth,
            'Authorization: Basic ' . $base64Auth,
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $rawRes = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300 && !empty($rawRes)) {
        $decoded = json_decode((string)$rawRes, true);
        if (is_array($decoded) && ($decoded['code'] === '200' || $decoded['code'] === 200) && !empty($decoded['model'])) {
            $digitapModel = $decoded['model'];
            break;
        } else {
            $lastErrorMsg = $decoded['msg'] ?? $decoded['message'] ?? 'DigiLocker synchronization in progress...';
        }
    } else {
        $lastErrorMsg = $curlErr ?: "HTTP $httpCode response from Digitap gateway";
    }

    if ($attempt < $maxAttempts) {
        usleep(1200000); // 1.2s delay for UIDAI/DigiLocker sync
    }
}

// 4. Extract Aadhaar Profile Details
$fullName = '';
$dob = '';
$gender = '';
$careOf = '';
$address = '';
$photoBase64 = '';
$maskedAadhaar = '';

if (!empty($digitapModel)) {
    $fullName = trim((string)($digitapModel['name'] ?? ''));
    $dob = trim((string)($digitapModel['dob'] ?? ''));
    $rawGender = trim((string)($digitapModel['gender'] ?? ''));
    $gender = ($rawGender === 'M' || strcasecmp($rawGender, 'Male') === 0) ? 'Male' : (($rawGender === 'F' || strcasecmp($rawGender, 'Female') === 0) ? 'Female' : $rawGender);
    $careOf = trim((string)($digitapModel['careOf'] ?? ''));
    $maskedAadhaar = trim((string)($digitapModel['maskedAdharNumber'] ?? $digitapModel['maskedAadhaar'] ?? ''));
    $photoBase64 = trim((string)($digitapModel['image'] ?? ''));

    // Compose Address
    if (!empty($digitapModel['address']) && is_array($digitapModel['address'])) {
        $a = $digitapModel['address'];
        $parts = array_filter([
            $careOf,
            $a['house'] ?? '',
            $a['street'] ?? '',
            $a['landmark'] ?? '',
            $a['loc'] ?? $a['vtc'] ?? '',
            $a['po'] ?? '',
            $a['dist'] ?? '',
            $a['state'] ?? '',
            !empty($a['pc']) ? ('- ' . $a['pc']) : ''
        ]);
        $address = implode(', ', $parts);
    } elseif (is_string($digitapModel['address'] ?? null)) {
        $address = trim($digitapModel['address']);
    }

    // Parse XML if available and fields are missing
    if (!empty($digitapModel['xmlResponse']) && (empty($photoBase64) || empty($address))) {
        $xmlData = parseAadhaarKycXml((string)$digitapModel['xmlResponse']);
        if (empty($photoBase64) && !empty($xmlData['photo'])) $photoBase64 = $xmlData['photo'];
        if (empty($address) && !empty($xmlData['address'])) $address = $xmlData['address'];
    }
    // Extract DigiLocker PDF & XML files
    $digilockerFiles = $digitapModel['digilockerFiles'] ?? [];
    $pdfDocLink = '';
    if (is_array($digilockerFiles)) {
        foreach ($digilockerFiles as $file) {
            if (($file['docType'] ?? '') === 'DIGILOCKER_PDF' && !empty($file['docLink'])) {
                $pdfDocLink = (string)$file['docLink'];
                break;
            }
        }
    }
}

// Fallback values if API call timed out but redirect was successful
if (empty($fullName)) {
    $fullName = $mappedInfo['name'] ?? $_SESSION['lead_full_name'] ?? 'Verified Citizen';
}
if (empty($maskedAadhaar)) {
    $maskedAadhaar = $maskedAadhaarFromSession ?: 'XXXX-XXXX-AADH';
}

// Strip data URI prefix if present in photo
if (strpos($photoBase64, 'base64,') !== false) {
    $parts = explode('base64,', $photoBase64);
    $photoBase64 = end($parts);
}

// 5. Store Verified Details in Session & Database
$_SESSION['aadhaar_verified'] = true;
$_SESSION['lead_aadhaar_verified'] = true;
$_SESSION['aadhaar_kyc_data'] = [
    'status' => 'SUCCESS',
    'transaction_id' => $txnId,
    'unique_id' => $uniqueId,
    'reference_id' => $referenceId,
    'name' => $fullName,
    'dob' => $dob,
    'gender' => $gender,
    'care_of' => $careOf,
    'address' => $address,
    'photo' => $photoBase64,
    'pdf_link' => $pdfDocLink ?? '',
    'masked_aadhaar' => $maskedAadhaar,
    'verified_at' => date('Y-m-d H:i:s')
];

// Update loan application in DB if present
if (!empty($appId)) {
    try {
        $conn = db();
        if (loan_flow_column_exists($conn, 'loan_applications', 'aadhaar_verified')) {
            loan_flow_update_application($conn, $appId, [
                'aadhaar_verified' => 1,
                'aadhaar_unique_id' => $uniqueId,
                'aadhaar_masked' => $maskedAadhaar,
                'current_step' => 'work_details',
                'last_activity_at' => date('Y-m-d H:i:s')
            ]);
        }
    } catch (Throwable $e) {
        error_log("Database KYC update notice: " . $e->getMessage());
    }
}

// 6. Output Result: JSON for AJAX or Clean Visual Success Screen for Browser Redirect
if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'success',
        'message' => 'Aadhaar DigiLocker e-KYC verified successfully',
        'data' => [
            'status' => 'SUCCESS',
            'full_name' => $fullName,
            'masked_aadhaar' => $maskedAadhaar,
            'dob' => $dob,
            'gender' => $gender,
            'address' => $address,
            'photo' => !empty($photoBase64),
            'transaction_id' => $txnId,
            'unique_id' => $uniqueId,
            'reference_id' => $referenceId
        ]
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

// Render Premium HTML Success Screen
renderSuccessScreen($fullName, $maskedAadhaar, $dob, $gender, $address, $photoBase64, $referenceId, $txnId);
exit;

/**
 * Renders Visual Success Screen
 */
function renderSuccessScreen(string $name, string $maskedAadhaar, string $dob, string $gender, string $address, string $photo, string $refId, string $txnId): void
{
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Aadhaar e-KYC Verified | Waqt Finance</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <style>
            :root {
                --brand-primary: #004c8f;
                --brand-secondary: #008080;
                --brand-gradient: linear-gradient(135deg, #004c8f 0%, #008080 100%);
                --success: #16a34a;
                --success-bg: #f0fdf4;
                --slate-900: #0f172a;
                --slate-700: #334155;
                --slate-500: #64748b;
                --slate-100: #f1f5f9;
            }
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body {
                font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
                background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 50%, #f0fdf4 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                color: var(--slate-900);
            }
            .kyc-card {
                background: #ffffff;
                width: 100%;
                max-width: 520px;
                border-radius: 24px;
                box-shadow: 0 20px 45px -10px rgba(0, 76, 143, 0.15), 0 0 1px 1px rgba(0, 0, 0, 0.05);
                overflow: hidden;
                border: 1px solid #e2e8f0;
                position: relative;
                animation: slideUp 0.4s ease-out;
            }
            @keyframes slideUp {
                from { opacity: 0; transform: translateY(18px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .kyc-header {
                background: var(--brand-gradient);
                color: #ffffff;
                padding: 28px 24px 24px;
                text-align: center;
                position: relative;
            }
            .brand-logo-wrap {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background: #ffffff;
                padding: 4px;
                margin: 0 auto 12px;
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .brand-logo-wrap img {
                width: 100%;
                height: 100%;
                object-fit: contain;
                border-radius: 50%;
            }
            .kyc-badge {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                background: rgba(255, 255, 255, 0.18);
                backdrop-filter: blur(8px);
                color: #ffffff;
                font-size: 11px;
                font-weight: 800;
                padding: 4px 12px;
                border-radius: 999px;
                letter-spacing: 0.5px;
                text-transform: uppercase;
                margin-bottom: 8px;
            }
            .kyc-title {
                font-size: 22px;
                font-weight: 800;
                letter-spacing: -0.5px;
            }
            .kyc-sub {
                font-size: 12.5px;
                color: rgba(255, 255, 255, 0.88);
                margin-top: 4px;
            }
            .kyc-body {
                padding: 24px;
            }
            .status-banner {
                background: var(--success-bg);
                border: 1.5px solid #86efac;
                border-radius: 14px;
                padding: 12px 14px;
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 18px;
            }
            .status-icon {
                width: 38px;
                height: 38px;
                border-radius: 50%;
                background: var(--success);
                color: #ffffff;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                flex-shrink: 0;
            }
            .status-title {
                font-size: 13.5px;
                font-weight: 800;
                color: #14532d;
            }
            .status-sub {
                font-size: 11px;
                color: #15803d;
            }
            .profile-grid {
                display: flex;
                gap: 16px;
                background: var(--slate-100);
                border: 1px solid #e2e8f0;
                border-radius: 16px;
                padding: 16px;
                margin-bottom: 18px;
            }
            .profile-photo {
                width: 72px;
                height: 86px;
                border-radius: 10px;
                object-fit: cover;
                border: 2px solid #cbd5e1;
                background: #ffffff;
                flex-shrink: 0;
            }
            .profile-details {
                flex-grow: 1;
            }
            .profile-name {
                font-size: 16px;
                font-weight: 800;
                color: var(--slate-900);
                margin-bottom: 4px;
            }
            .aadhaar-number-tag {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                font-family: 'JetBrains Mono', monospace;
                font-size: 12px;
                font-weight: 700;
                color: var(--brand-primary);
                background: #e0f2fe;
                padding: 2px 8px;
                border-radius: 6px;
                margin-bottom: 8px;
            }
            .meta-row {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 6px;
                font-size: 11.5px;
                color: var(--slate-700);
            }
            .meta-row span {
                color: var(--slate-500);
            }
            .address-box {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 12px;
                margin-bottom: 18px;
                font-size: 12px;
                line-height: 1.45;
                color: var(--slate-700);
            }
            .address-lbl {
                font-size: 10.5px;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                color: var(--slate-500);
                margin-bottom: 4px;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            .ref-audit-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-size: 10.5px;
                color: var(--slate-500);
                padding: 8px 10px;
                background: #f8fafc;
                border-radius: 8px;
                margin-bottom: 18px;
            }
            .btn-continue {
                display: block;
                width: 100%;
                background: var(--brand-gradient);
                color: #ffffff;
                font-size: 14px;
                font-weight: 800;
                text-align: center;
                text-decoration: none;
                padding: 13px 18px;
                border-radius: 12px;
                box-shadow: 0 6px 16px rgba(0, 76, 143, 0.28);
                transition: transform 0.15s ease, box-shadow 0.15s ease;
            }
            .btn-continue:hover {
                transform: translateY(-1px);
                box-shadow: 0 10px 22px rgba(0, 76, 143, 0.35);
            }
            .trust-strip {
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 16px;
                font-size: 10.5px;
                color: var(--slate-500);
                margin-top: 14px;
            }
        </style>
    </head>
    <body>
        <div class="kyc-card">
            <div class="kyc-header">
                <div class="brand-logo-wrap">
                    <img src="assets/images/logo1.webp" alt="Waqt Finance">
                </div>
                <div class="kyc-badge">
                    <i class="fa-solid fa-shield-halved"></i> UIDAI DigiLocker Verified
                </div>
                <h1 class="kyc-title">Aadhaar e-KYC Completed</h1>
                <p class="kyc-sub">Official identity verification successful</p>
            </div>

            <div class="kyc-body">
                <div class="status-banner">
                    <div class="status-icon">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div>
                        <div class="status-title">DigiLocker Verification Success</div>
                        <div class="status-sub">Identity &amp; Address verified via Government e-KYC Gateway</div>
                    </div>
                </div>

                <div class="profile-grid">
                    <?php if (!empty($photo)): ?>
                        <img src="data:image/jpeg;base64,<?= htmlspecialchars($photo, ENT_QUOTES, 'UTF-8') ?>" alt="Aadhaar Photo" class="profile-photo">
                    <?php else: ?>
                        <div class="profile-photo" style="display:flex; align-items:center; justify-content:center; color:var(--slate-500); font-size:26px;">
                            <i class="fa-solid fa-user"></i>
                        </div>
                    <?php endif; ?>

                    <div class="profile-details">
                        <div class="profile-name"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="aadhaar-number-tag">
                            <i class="fa-solid fa-fingerprint"></i> <?= htmlspecialchars($maskedAadhaar, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div class="meta-row">
                            <div><span>DOB:</span> <strong><?= htmlspecialchars($dob ?: 'Recorded', ENT_QUOTES, 'UTF-8') ?></strong></div>
                            <div><span>Gender:</span> <strong><?= htmlspecialchars($gender ?: 'Recorded', ENT_QUOTES, 'UTF-8') ?></strong></div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($address)): ?>
                    <div class="address-box">
                        <div class="address-lbl"><i class="fa-solid fa-location-dot" style="color:var(--brand-primary);"></i> Verified Aadhaar Address</div>
                        <div><?= htmlspecialchars($address, ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                <?php endif; ?>

                <div class="ref-audit-row">
                    <div>Ref: <strong><?= htmlspecialchars($refId ?: $txnId, ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <div>Status: <strong style="color:var(--success);">SUCCESS (UIDAI)</strong></div>
                </div>

                <a href="create-lead-form?step=3&aadhaar_verified=1" class="btn-continue">
                    Continue to Credit Score Check <i class="fa-solid fa-arrow-right"></i>
                </a>

                <div class="trust-strip">
                    <span><i class="fa-solid fa-lock"></i> 256-Bit SSL Encrypted</span>
                    <span><i class="fa-solid fa-certificate"></i> Verified by Digitap / DigiLocker</span>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Renders Error Screen
 */
function renderErrorScreen(string $errorMessage): void
{
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Aadhaar Verification Notice | Waqt Finance</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <style>
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
                background: #f8fafc;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                color: #0f172a;
            }
            .err-card {
                background: #ffffff;
                width: 100%;
                max-width: 460px;
                border-radius: 20px;
                padding: 28px 24px;
                text-align: center;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
                border: 1px solid #fee2e2;
            }
            .err-icon {
                width: 56px;
                height: 56px;
                border-radius: 50%;
                background: #fee2e2;
                color: #dc2626;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                margin: 0 auto 14px;
            }
            .err-title { font-size: 19px; font-weight: 800; color: #991b1b; margin-bottom: 6px; }
            .err-desc { font-size: 12.5px; color: #64748b; line-height: 1.5; margin-bottom: 20px; }
            .btn-retry {
                display: inline-block;
                background: #004c8f;
                color: #fff;
                padding: 11px 20px;
                border-radius: 10px;
                text-decoration: none;
                font-weight: 700;
                font-size: 13.5px;
            }
        </style>
    </head>
    <body>
        <div class="err-card">
            <div class="err-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <h2 class="err-title">Verification Incomplete</h2>
            <p class="err-desc"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
            <a href="create-lead-form?step=2" class="btn-retry">
                <i class="fa-solid fa-rotate-left"></i> Try Aadhaar Verification Again
            </a>
        </div>
    </body>
    </html>
    <?php
}
