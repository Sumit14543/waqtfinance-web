<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/crm_integration.php';
require_once __DIR__ . '/../../includes/email_helper.php';

header('Content-Type: application/json; charset=utf-8');

require_post();
require_valid_csrf();

function clean_input($data)
{
    return trim((string)$data);
}



function save_edi_image(string $fieldName): string
{
    if (empty($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
        json_response(['status' => 'error', 'message' => 'Business photos are required'], 400);
    }

    return waqt_save_private_uploaded_file($_FILES[$fieldName], 'edi', $fieldName, [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ], 5 * 1024 * 1024);
}

function save_edi_document_files(string $fieldName, bool $required = true, bool $allowPdf = true): array
{
    if (empty($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
        if ($required) {
            json_response(['status' => 'error', 'message' => 'Required documents are missing'], 400);
        }
        return [];
    }

    $files = $_FILES[$fieldName];
    $names = is_array($files['name']) ? $files['name'] : [$files['name']];
    $tmpNames = is_array($files['tmp_name']) ? $files['tmp_name'] : [$files['tmp_name']];
    $errors = is_array($files['error']) ? $files['error'] : [$files['error']];
    $sizes = is_array($files['size']) ? $files['size'] : [$files['size']];

    if ($required && count(array_filter($names, static fn($name) => (string) $name !== '')) === 0) {
        json_response(['status' => 'error', 'message' => 'Required documents are missing'], 400);
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if ($allowPdf) {
        $allowed['application/pdf'] = 'pdf';
    }

    $paths = [];
    foreach ($names as $index => $name) {
        if ((string) $name === '') {
            continue;
        }
        $paths[] = waqt_save_private_uploaded_file([
            'name' => $names[$index],
            'tmp_name' => $tmpNames[$index] ?? '',
            'error' => $errors[$index] ?? UPLOAD_ERR_NO_FILE,
            'size' => $sizes[$index] ?? 0,
        ], 'edi-documents', $fieldName, $allowed, 8 * 1024 * 1024);
    }

    return $paths;
}

function edi_first_document_path(array $paths): string
{
    return (string) ($paths[0] ?? '');
}

// Detect loan type
$loanType = clean_input($_POST['loan_type'] ?? '');

if (empty($loanType)) {
    json_response(['status' => 'error', 'message' => 'Loan type is required'], 400);
}

// Common fields
$phone = clean_input($_POST['phoneField'] ?? '');
$pan   = strtoupper(clean_input($_POST['panField'] ?? ''));
$city  = clean_input($_POST['city'] ?? '');
$email = clean_input($_POST['emailField'] ?? '');
$latitude = clean_input($_POST['latitude'] ?? '');
$longitude = clean_input($_POST['longitude'] ?? '');
$gpsAddress = clean_input($_POST['gpsAddress'] ?? '');
$deviceFingerprint = clean_input($_POST['deviceFingerprint'] ?? '');

$ipAddress = '';
foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
    $value = $_SERVER[$key] ?? '';
    if ($value !== '') {
        $ip = trim(explode(',', $value)[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            $ipAddress = $ip;
            break;
        }
    }
}

if ($phone !== '' && !preg_match('/^[6-9]\d{9}$/', $phone)) {
    json_response(['status' => 'error', 'message' => 'Please enter a valid 10-digit mobile number starting with 6-9.'], 400);
}
if ($pan !== '' && !preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]$/', $pan)) {
    json_response(['status' => 'error', 'message' => 'Please enter a valid 10-character PAN number.'], 400);
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['status' => 'error', 'message' => 'Please enter a valid email address.'], 400);
}


$loan_details = [];
$applicant_name = "";
$appPrefixMap = [
    'vehicle'  => 'WAQT-VH',
    'edi'      => 'WAQT-EDI',
    'personal' => 'WAQT-PL',
    'business' => 'WAQT-BL',
    'property' => 'WAQT-PL'
];

// 🔀 Switch based on loan type
switch ($loanType) {

    case 'edi':
        $applicant_name = clean_input($_POST['fullName'] ?? '');
        $employeeCode = clean_input($_POST['employeeCode'] ?? '');
        $email = clean_input($_POST['emailField'] ?? '');
        $address = clean_input($_POST['addressField'] ?? '');
        $landmark = clean_input($_POST['landmarkField'] ?? '');
        $personalCity = clean_input($_POST['personalCity'] ?? '');
        $pincode = clean_input($_POST['pincodeField'] ?? '');
        $aadhaar = preg_replace('/\D/', '', clean_input($_POST['aadhaarField'] ?? ''));
        $businessProof = clean_input($_POST['businessProofField'] ?? '');
        $businessVerifiedDetails = clean_input($_POST['businessVerifiedDetails'] ?? '');
        $latitude = clean_input($_POST['latitude'] ?? '');
        $longitude = clean_input($_POST['longitude'] ?? '');
        $gpsAddress = clean_input($_POST['gpsAddress'] ?? '');
        $deviceFingerprint = clean_input($_POST['deviceFingerprint'] ?? '');
        $shopBusinessName = clean_input($_POST['shopBusinessName'] ?? '');
        $registrationType = clean_input($_POST['registrationType'] ?? '');
        $businessAge = clean_input($_POST['businessAge'] ?? '');
        $monthlyIncome = clean_input($_POST['monthlyIncome'] ?? '');
        $businessType = clean_input($_POST['businessType'] ?? '');
        $dailySales  = clean_input($_POST['dailySales'] ?? '');
        $loanAmount  = clean_input($_POST['loanAmount'] ?? '');
        $purpose     = clean_input($_POST['purpose'] ?? '');
        $ifscCode = strtoupper(clean_input($_POST['ifscCode'] ?? ''));
        $bankName = clean_input($_POST['bankName'] ?? '');
        $branchName = clean_input($_POST['branchName'] ?? '');
        $accountHolder = clean_input($_POST['accountHolder'] ?? '');
        $accountNumber = preg_replace('/\D/', '', clean_input($_POST['accountNumber'] ?? ''));
        $primaryRefName = clean_input($_POST['primaryRefName'] ?? '');
        $primaryRefMobile = preg_replace('/\D/', '', clean_input($_POST['primaryRefMobile'] ?? ''));
        $primaryRefRelation = clean_input($_POST['primaryRefRelation'] ?? '');
        $secondaryRefName = clean_input($_POST['secondaryRefName'] ?? '');
        $secondaryRefMobile = preg_replace('/\D/', '', clean_input($_POST['secondaryRefMobile'] ?? ''));
        $secondaryRefRelation = clean_input($_POST['secondaryRefRelation'] ?? '');

        if (!$applicant_name || !$phone || !$email || !$address || !$landmark || !$personalCity || !$pincode || !$aadhaar || !$pan || !$shopBusinessName || !$registrationType || !$businessAge || !$dailySales || !$monthlyIncome || !$businessType || !$loanAmount || !$purpose || !$city || !$ifscCode || !$bankName || !$accountHolder || !$accountNumber || !$primaryRefName || !$primaryRefMobile || !$primaryRefRelation || !$secondaryRefName || !$secondaryRefMobile || !$secondaryRefRelation) {
            json_response(['status' => 'error', 'message' => 'All EDI fields required'], 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_response(['status' => 'error', 'message' => 'Invalid email address'], 422);
        }
        if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
            json_response(['status' => 'error', 'message' => 'Invalid mobile number'], 422);
        }
        if (!preg_match('/^\d{6}$/', $pincode)) {
            json_response(['status' => 'error', 'message' => 'Invalid pincode'], 422);
        }
        if (!preg_match('/^\d{12}$/', $aadhaar)) {
            json_response(['status' => 'error', 'message' => 'Invalid Aadhaar number'], 422);
        }
        if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]$/', $pan)) {
            json_response(['status' => 'error', 'message' => 'Invalid PAN number'], 422);
        }
        if (!preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $ifscCode)) {
            json_response(['status' => 'error', 'message' => 'Invalid IFSC code'], 422);
        }
        if (!preg_match('/^\d{9,18}$/', $accountNumber)) {
            json_response(['status' => 'error', 'message' => 'Invalid account number'], 422);
        }
        if (!preg_match('/^[6-9]\d{9}$/', $primaryRefMobile) || !preg_match('/^[6-9]\d{9}$/', $secondaryRefMobile)) {
            json_response(['status' => 'error', 'message' => 'Invalid reference mobile number'], 422);
        }
        if ($primaryRefMobile === $secondaryRefMobile || $primaryRefMobile === $phone || $secondaryRefMobile === $phone) {
            json_response(['status' => 'error', 'message' => 'Reference numbers must be unique and different from applicant mobile'], 422);
        }
        // Geolocation IP address detection
        $ipAddress = '';
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            $value = $_SERVER[$key] ?? '';
            if ($value !== '') {
                $ip = trim(explode(',', $value)[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    $ipAddress = $ip;
                    break;
                }
            }
        }

        // 1. PAN 30-Day Duplicate Block (Anti-Fraud)
        $panCheckStmt = $conn->prepare("
            SELECT COUNT(*) AS count
            FROM edi_loan_applications
            WHERE pan_number = ?
              AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $panCheckStmt->bind_param("s", $pan);
        $panCheckStmt->execute();
        $panCount = (int) ($panCheckStmt->get_result()->fetch_assoc()['count'] ?? 0);
        $panCheckStmt->close();

        if ($panCount > 0) {
            json_response([
                'status' => 'error',
                'message' => 'An application with this PAN Card is already in process. You can apply again after 30 days.'
            ], 400);
        }

        // 2. IP Submission Limit: Max 2 submissions per 24 hours per IP
        if ($ipAddress !== '') {
            $ipCheckStmt = $conn->prepare("
                SELECT COUNT(*) AS count
                FROM edi_loan_applications
                WHERE ip_address = ?
                  AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");
            $ipCheckStmt->bind_param("s", $ipAddress);
            $ipCheckStmt->execute();
            $ipCount = (int) ($ipCheckStmt->get_result()->fetch_assoc()['count'] ?? 0);
            $ipCheckStmt->close();

            if ($ipCount >= 2) {
                json_response([
                    'status' => 'error',
                    'message' => 'Too many applications submitted from this connection. Please try again after 24 hours.'
                ], 429);
            }
        }

        $encryptedAadhaar = waqt_encrypt_sensitive_data($aadhaar);
        $shopFrontPhotoPath = save_edi_image('shopFrontPhoto');
        $stockPhotoPath = save_edi_image('stockPhoto');
        $stockPhotoTwoPath = save_edi_image('stockPhotoTwo');
        $uploadedDocuments = [
            'pan_card' => save_edi_document_files('panCardDoc'),
            'aadhaar_card' => save_edi_document_files('aadhaarCardDoc'),
            'bank_statement' => save_edi_document_files('bankStatementDoc'),
            'business_registration' => save_edi_document_files('businessRegistrationDoc'),
            'address_proof' => save_edi_document_files('addressProofDoc'),
            'passport_photo' => save_edi_document_files('passportPhotoDoc', true, false),
            'selfie' => save_edi_document_files('selfieDoc', true, false),
        ];

        $loan_details = [
            'employee_code' => $employeeCode,
            'email' => $email,
            'address' => $address,
            'landmark' => $landmark,
            'personal_city' => $personalCity,
            'pincode' => $pincode,
            'aadhaar_last4' => substr($aadhaar, -4),
            'business_proof' => $businessProof,
            'shop_business_name' => $shopBusinessName,
            'registration_type' => $registrationType,
            'business_age' => $businessAge,
            'business_type' => $businessType,
            'daily_sales' => $dailySales,
            'monthly_income' => $monthlyIncome,
            'shop_front_photo' => $shopFrontPhotoPath,
            'stock_photo' => $stockPhotoPath,
            'stock_photo_two' => $stockPhotoTwoPath,
            'uploaded_documents' => $uploadedDocuments,
            'bank_details' => [
                'ifsc_code' => $ifscCode,
                'bank_name' => $bankName,
                'branch_name' => $branchName,
                'account_holder' => $accountHolder,
                'account_number' => $accountNumber,
            ],
            'references' => [
                'primary' => [
                    'full_name' => $primaryRefName,
                    'mobile' => $primaryRefMobile,
                    'relation' => $primaryRefRelation,
                ],
                'secondary' => [
                    'full_name' => $secondaryRefName,
                    'mobile' => $secondaryRefMobile,
                    'relation' => $secondaryRefRelation,
                ],
            ],
            'loan_amount' => $loanAmount,
            'purpose'     => $purpose
        ];
        break;

    case 'business':
        $applicant_name = clean_input($_POST['ownerName'] ?? '');
        $businessName = clean_input($_POST['businessName'] ?? '');
        $turnover     = clean_input($_POST['turnover'] ?? '');
        $loanAmount   = clean_input($_POST['loanAmount'] ?? '');

        if (!$applicant_name || !$phone || !$pan || !$businessName || !$loanAmount) {
            json_response(['status' => 'error', 'message' => 'All Business fields required'], 400);
        }

        $loan_details = [
            'business_name'   => $businessName,
            'annual_turnover' => $turnover,
            'loan_amount'     => $loanAmount,
            'email'           => $email,
            'latitude'        => $latitude,
            'longitude'       => $longitude,
            'gps_address'     => $gpsAddress,
            'device_fingerprint' => $deviceFingerprint,
            'ip_address'      => $ipAddress,
        ];
        break;

    case 'personal':
        $applicant_name = clean_input($_POST['fullName'] ?? '');
        $emp           = clean_input($_POST['emp'] ?? '');
        $income        = clean_input($_POST['income'] ?? '');
        $loanAmount    = clean_input($_POST['loanAmount'] ?? '');

        if (!$applicant_name || !$phone || !$pan || !$income || !$loanAmount) {
            json_response(['status' => 'error', 'message' => 'All Personal loan fields required'], 400);
        }

        $loan_details = [
            'employment_status' => $emp,
            'monthly_income'    => $income,
            'loan_amount'       => $loanAmount,
            'email'           => $email,
            'latitude'        => $latitude,
            'longitude'       => $longitude,
            'gps_address'     => $gpsAddress,
            'device_fingerprint' => $deviceFingerprint,
            'ip_address'      => $ipAddress,
        ];
        break;

    case 'vehicle':
        $applicant_name = clean_input($_POST['fullName'] ?? '');
        $vehicleType    = clean_input($_POST['vehicleType'] ?? '');
        $vehiclePrice   = clean_input($_POST['vehiclePrice'] ?? '');
        $loanAmount     = clean_input($_POST['loanAmount'] ?? '');
        $income         = clean_input($_POST['income'] ?? '');

        if (!$applicant_name || !$phone || !$pan || !$vehicleType || !$vehiclePrice || !$loanAmount) {
            json_response(['status' => 'error', 'message' => 'All Vehicle loan fields required'], 400);
        }

        $loan_details = [
            'vehicle_type'   => $vehicleType,
            'vehicle_price'  => $vehiclePrice,
            'loan_amount'    => $loanAmount,
            'monthly_income' => $income,
            'email'           => $email,
            'latitude'        => $latitude,
            'longitude'       => $longitude,
            'gps_address'     => $gpsAddress,
            'device_fingerprint' => $deviceFingerprint,
            'ip_address'      => $ipAddress,
        ];
        break;

    case 'property':
        $applicant_name = clean_input($_POST['fullName'] ?? '');
        $propType       = clean_input($_POST['propType'] ?? '');
        $propValue      = clean_input($_POST['propValue'] ?? '');
        $loanAmount = (float) clean_input($_POST['loanAmount'] ?? 0);

        if (!$applicant_name || !$phone || !$pan || !$propType || !$propValue || !$loanAmount) {
            json_response(['status' => 'error', 'message' => 'All Property loan fields required'], 400);
        }

        $loan_details = [
            'property_type'  => $propType,
            'property_value' => $propValue,
            'loan_amount'    => $loanAmount,
            'email'           => $email,
            'latitude'        => $latitude,
            'longitude'       => $longitude,
            'gps_address'     => $gpsAddress,
            'device_fingerprint' => $deviceFingerprint,
            'ip_address'      => $ipAddress,
        ];
        break;

    default:
        json_response(['status' => 'error', 'message' => 'Invalid loan type'], 400);
}
$application_id = ($appPrefixMap[$loanType] ?? 'WAQT-GEN') . date('Ymd') . strtoupper(bin2hex(random_bytes(5)));

// 💰 Extract loan amount for dedicated column and remove from JSON
$finalLoanAmount = (float) ($loan_details['loan_amount'] ?? 0);
unset($loan_details['loan_amount']);

$loan_details_json = json_encode($loan_details);

// 🛠️ Map internal loan type to DB enum if needed
$dbLoanType = $loanType;
if ($loanType === 'property') {
    $dbLoanType = 'lap';
}

if ($loanType === 'edi') {
    try {
        $ediRow = [
            'application_id' => $application_id,
            'loan_type' => 'edi',
            'source' => 'waqtfinance',
            'applicant_name' => $applicant_name,
            'mobile_number' => $phone,
            'employee_code' => $employeeCode,
            'email' => $email,
            'current_address' => $address,
            'landmark' => $landmark,
            'city' => $city,
            'pincode' => $pincode,
            'pan_number' => $pan,
            'aadhaar_number' => $encryptedAadhaar,
            'business_proof' => $businessProof,
            'shop_business_name' => $shopBusinessName,
            'registration_type' => $registrationType,
            'business_age' => $businessAge,
            'business_type' => $businessType,
            'daily_sales' => $dailySales,
            'monthly_income' => $monthlyIncome,
            'shop_front_photo' => $shopFrontPhotoPath,
            'stock_photo' => $stockPhotoPath,
            'stock_photo_two' => $stockPhotoTwoPath,
            'pan_card_doc' => edi_first_document_path($uploadedDocuments['pan_card']),
            'aadhaar_card_doc' => edi_first_document_path($uploadedDocuments['aadhaar_card']),
            'bank_statement_doc' => edi_first_document_path($uploadedDocuments['bank_statement']),
            'business_registration_doc' => edi_first_document_path($uploadedDocuments['business_registration']),
            'address_proof_doc' => edi_first_document_path($uploadedDocuments['address_proof']),
            'passport_photo_doc' => edi_first_document_path($uploadedDocuments['passport_photo']),
            'selfie_doc' => edi_first_document_path($uploadedDocuments['selfie']),
            'loan_amount' => $loanAmount,
            'loan_purpose' => $purpose,
            'ifsc_code' => $ifscCode,
            'bank_name' => $bankName,
            'branch_name' => $branchName,
            'account_holder' => $accountHolder,
            'account_number' => $accountNumber,
            'primary_ref_name' => $primaryRefName,
            'primary_ref_mobile' => $primaryRefMobile,
            'primary_ref_relation' => $primaryRefRelation,
            'secondary_ref_name' => $secondaryRefName,
            'secondary_ref_mobile' => $secondaryRefMobile,
            'secondary_ref_relation' => $secondaryRefRelation,
            'status' => 'submitted',
            'business_verified_details' => $businessVerifiedDetails,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'gps_address' => $gpsAddress,
            'device_fingerprint' => $deviceFingerprint,
            'ip_address' => $ipAddress,
        ];

        $columns = array_keys($ediRow);
        $columnSql = '`' . implode('`, `', $columns) . '`';
        $placeholderSql = implode(', ', array_fill(0, count($columns), '?'));
        $stmt = $conn->prepare("INSERT INTO edi_loan_applications ($columnSql) VALUES ($placeholderSql)");

        $values = array_values($ediRow);
        $types = str_repeat('s', count($values));
        $refs = [$types];
        foreach ($values as $index => &$value) {
            $refs[] = &$value;
        }
        call_user_func_array([$stmt, 'bind_param'], $refs);

        if ($stmt->execute()) {
            $insertedId = $stmt->insert_id;
            $displayId = 'WAQT-EDI-' . str_pad((string)$insertedId, 6, '0', STR_PAD_LEFT);
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['loan_app_id_final'] = $application_id;

            try {
                $notes = "Website EDI Loan ID: " . $displayId . "\n" .
                         "Landmark: " . $landmark . "\n" .
                         "Registration: " . $registrationType . "\n" .
                         "Business Age: " . $businessAge . "\n" .
                         "Daily Sales: " . $dailySales . "\n" .
                         "IFSC: " . $ifscCode . "\n" .
                         "Account Number: " . $accountNumber . "\n" .
                         "References:\n" .
                         "1. " . $primaryRefName . " (" . $primaryRefMobile . " - " . $primaryRefRelation . ")\n" .
                         "2. " . $secondaryRefName . " (" . $secondaryRefMobile . " - " . $secondaryRefRelation . ")";

                push_common_lead_to_crm(db(), 'edi', $application_id);
            } catch (Throwable $pushErr) {
                error_log('CRM Push Fail: ' . $pushErr->getMessage());
            }

            try {
                send_loan_application_notification($conn, 'edi', $application_id, $displayId);
            } catch (Throwable $mailErr) {
                error_log('Email Notification Fail: ' . $mailErr->getMessage());
            }

            json_response([
                'status' => 'success',
                'message' => 'EDI loan application stored successfully.',
                'application_id' => $displayId,
            ]);
        }

        throw new Exception('DB error: ' . $stmt->error);
    } catch (Throwable $e) {
        error_log('EDI Loan Save Error: ' . $e->getMessage());
        json_response([
            'status' => 'error',
            'message' => 'Server error'
        ], 500);
    }
}

// 1. PAN 30-Day Duplicate Block for other loan types
    $panCheckStmt = $conn->prepare("
        SELECT COUNT(*) AS count
        FROM loan_applied_master
        WHERE pancard_number = ?
          AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $panCheckStmt->bind_param("s", $pan);
    $panCheckStmt->execute();
    $panCount = (int) ($panCheckStmt->get_result()->fetch_assoc()['count'] ?? 0);
    $panCheckStmt->close();

    if ($panCount > 0) {
        json_response([
            'status' => 'error',
            'message' => 'An application with this PAN Card is already in process. You can apply again after 30 days.'
        ], 400);
    }

    // 2. IP Submission Limit: Max 2 submissions per 24 hours per IP
    if ($ipAddress !== '') {
        $likeIp = '%"ip_address":"' . $ipAddress . '"%';
        $ipCheckStmt = $conn->prepare("
            SELECT COUNT(*) AS count
            FROM loan_applied_master
            WHERE loan_details LIKE ?
              AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $ipCheckStmt->bind_param("s", $likeIp);
        $ipCheckStmt->execute();
        $ipCount = (int) ($ipCheckStmt->get_result()->fetch_assoc()['count'] ?? 0);
        $ipCheckStmt->close();

        if ($ipCount >= 2) {
            json_response([
                'status' => 'error',
                'message' => 'Too many applications submitted from this connection. Please try again after 24 hours.'
            ], 429);
        }
    }

try {
    $stmt = $conn->prepare("
    INSERT INTO loan_applied_master (
        application_id,
        applicant_name, 
        mobile_number, 
        pancard_number, 
        city, 
        loan_amount,
        loan_type, 
        loan_details
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

   $stmt->bind_param(
    "sssssdss",
    $application_id,
    $applicant_name,
    $phone,
    $pan,
    $city,
    $finalLoanAmount,
    $dbLoanType,
    $loan_details_json
);

    if ($stmt->execute()) {
        $insertedId = $stmt->insert_id;
        $displayId = ($appPrefixMap[$loanType] ?? 'WAQT-GEN') . '-' . str_pad((string)$insertedId, 6, '0', STR_PAD_LEFT);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['loan_app_id_final'] = $application_id;

        try {
            push_common_lead_to_crm(db(), $loanType, $application_id);
        } catch (Throwable $pushErr) {
            error_log('CRM Push Fail: ' . $pushErr->getMessage());
        }

        try {
            send_loan_application_notification($conn, $loanType, $application_id, $displayId);
        } catch (Throwable $mailErr) {
            error_log('Email Notification Fail: ' . $mailErr->getMessage());
        }

        json_response([
            'status' => 'success',
            'message' => 'Application stored successfully.',
            'application_id' => $displayId
        ]);
    } else {
        throw new Exception('DB error: ' . $stmt->error);
    }
} catch (Throwable $e) {
    error_log('Loan Save Error: ' . $e->getMessage());
    json_response([
        'status' => 'error',
        'message' => 'Server error'
    ], 500);
}
