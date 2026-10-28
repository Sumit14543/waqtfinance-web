<?php
declare(strict_types=1);

require_once '../config.php';
require_once __DIR__ . '/../../includes/loan_flow.php';
require_once __DIR__ . '/../../includes/crm_integration.php';

header('Content-Type: application/json; charset=utf-8');
require_post();
require_valid_csrf();

$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
if (!$csrf || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['status' => 'error', 'message' => 'Security token expired. Please refresh and try again.']);
    http_response_code(419);
    exit;
}

// 1. Sanitize input fields
function lead_clean(?string $val): string {
    return trim((string) ($val ?? ''));
}

$rawJson = file_get_contents('php://input');
$jsonBody = json_decode($rawJson, true) ?: [];
$data = !empty($jsonBody) ? $jsonBody : $_POST;

$fullName = lead_clean($data['full_name'] ?? '');
$mobile = preg_replace('/\D/', '', lead_clean($data['mobile'] ?? ''));
$email = strtolower(lead_clean($data['email'] ?? ''));
$pan = strtoupper(lead_clean($data['pan'] ?? ''));
$aadhaarRaw = preg_replace('/\D/', '', lead_clean($data['aadhaar'] ?? ''));
$gst = strtoupper(lead_clean($data['gst'] ?? ''));
$udyam = strtoupper(lead_clean($data['udyam'] ?? ''));
$companyName = lead_clean($data['company_name'] ?? '');
$businessType = lead_clean($data['business_type'] ?? '');
$monthlyIncome = (float) preg_replace('/[^\d.]/', '', lead_clean((string)($data['monthly_income'] ?? '0')));
$loanType = lead_clean($data['loan_type'] ?? 'Business Loan');
$loanAmount = (float) preg_replace('/[^\d.]/', '', lead_clean((string)($data['loan_amount'] ?? '0')));
$loanTenure = lead_clean($data['loan_tenure'] ?? '24 Months');
$loanPurpose = lead_clean($data['loan_purpose'] ?? 'Working Capital');
$pincode = preg_replace('/\D/', '', lead_clean($data['pincode'] ?? ''));
$city = lead_clean($data['city'] ?? '');
$state = lead_clean($data['state'] ?? '');
$address = lead_clean($data['address'] ?? '');

// Bank & Account Aggregator Data
$bankName = lead_clean($data['bank_name'] ?? '');
$accountNumber = lead_clean($data['account_number'] ?? '');
$ifscCode = strtoupper(lead_clean($data['ifsc_code'] ?? ''));
$accountType = lead_clean($data['account_type'] ?? 'Current');
$aaVerified = !empty($data['aa_verified']) && (string)$data['aa_verified'] === '1';
$aaTxnId = lead_clean($data['aa_txn_id'] ?? '');
$aaInflow = (float)($data['aa_inflow'] ?? 0);
$aaAmb = (float)($data['aa_amb'] ?? 0);
$approvedLimit = (float)($data['approved_limit'] ?? 0);
$cibilScore = (int)($data['cibil_score'] ?? 0);
$cibilPdfUrl = lead_clean($data['cibil_pdf_url'] ?? '');

// Business Rule Engine (BRE) Credit & Banking Assessment Fields
$breStatus = lead_clean($data['bre_status'] ?? 'PENDING');
$breApprovedLimit = (float)($data['bre_approved_limit'] ?? $approvedLimit);
$breTenureDays = (int)($data['bre_tenure_days'] ?? 0);
$breEodBalance = (float)($data['bre_eod_balance'] ?? 0);
$breUpiCredits = (float)($data['bre_upi_credits'] ?? 0);
$breDeclineReasons = is_array($data['bre_decline_reasons'] ?? null) ? $data['bre_decline_reasons'] : [];
$breCrifData = is_array($data['bre_crif_data'] ?? null) ? $data['bre_crif_data'] : [];

$mobileVerified = !empty($data['mobile_verified']) && (string)$data['mobile_verified'] === '1';
$panVerified = !empty($data['pan_verified']) && (string)$data['pan_verified'] === '1';
$aadhaarVerified = !empty($data['aadhaar_verified']) && (string)$data['aadhaar_verified'] === '1';
$gstVerified = !empty($data['gst_verified']) && (string)$data['gst_verified'] === '1';
$udyamVerified = !empty($data['udyam_verified']) && (string)$data['udyam_verified'] === '1';

$panDataJson = is_array($data['pan_details'] ?? null) ? json_encode($data['pan_details']) : lead_clean((string)($data['pan_details'] ?? ''));
$gstDataJson = is_array($data['gst_details'] ?? null) ? json_encode($data['gst_details']) : lead_clean((string)($data['gst_details'] ?? ''));
$udyamDataJson = is_array($data['udyam_details'] ?? null) ? json_encode($data['udyam_details']) : lead_clean((string)($data['udyam_details'] ?? ''));

// Process Shop / Business Premises photos
$shopPhotos = [];
$incomingShopPhotos = $data['shop_photos'] ?? $data['shop_photo'] ?? [];
if (!is_array($incomingShopPhotos) && !empty($incomingShopPhotos)) {
    $incomingShopPhotos = [$incomingShopPhotos];
}
if (is_array($incomingShopPhotos)) {
    $shopDir = dirname(__DIR__, 2) . '/uploads/shop_photos';
    if (!is_dir($shopDir)) {
        @mkdir($shopDir, 0755, true);
    }
    foreach ($incomingShopPhotos as $photoItem) {
        $photoItem = trim((string)$photoItem);
        if (empty($photoItem)) continue;
        if (str_starts_with($photoItem, 'uploads/') || str_starts_with($photoItem, 'http')) {
            $shopPhotos[] = $photoItem;
            continue;
        }
        if (preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,(.+)$/i', $photoItem, $matches)) {
            $ext = strtolower($matches[1]) === 'png' ? 'png' : (strtolower($matches[1]) === 'webp' ? 'webp' : 'jpg');
            $decoded = base64_decode($matches[2]);
            if ($decoded !== false && strlen($decoded) > 0) {
                $filename = 'shop_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $targetPath = $shopDir . '/' . $filename;
                if (@file_put_contents($targetPath, $decoded)) {
                    $shopPhotos[] = 'uploads/shop_photos/' . $filename;
                }
            }
        }
    }
}
$shopPhotoPrimary = !empty($shopPhotos) ? $shopPhotos[0] : '';

// 2. Validation
if (empty($fullName)) {
    echo json_encode(['status' => 'error', 'message' => 'Applicant Full Name is required.']);
    exit;
}

if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid 10-digit mobile number.']);
    exit;
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']);
    exit;
}

if (!empty($pan) && !preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]$/', $pan)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid PAN Card format (e.g. ABCDE1234F).']);
    exit;
}

if (!empty($aadhaarRaw) && strlen($aadhaarRaw) !== 12) {
    echo json_encode(['status' => 'error', 'message' => 'Aadhaar must be 12 digits.']);
    exit;
}

if (!empty($gst) && !preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $gst)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid GSTIN format (e.g. 27AAPFU0939F1ZV).']);
    exit;
}

if (!empty($udyam) && !preg_match('/^UDYAM-[A-Z]{2}-\d{2}-\d{7}$/', $udyam)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Udyam format (e.g. UDYAM-DL-01-1234567).']);
    exit;
}

if (!empty($pincode) && !preg_match('/^\d{6}$/', $pincode)) {
    echo json_encode(['status' => 'error', 'message' => 'Pincode must be 6 digits.']);
    exit;
}

if ($loanAmount <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid loan amount.']);
    exit;
}

$conn = db();

// 3. Generate Application Reference
$applicationId = 'WF-LEAD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
$maskedAadhaar = !empty($aadhaarRaw) ? ('XXXXXXXX' . substr($aadhaarRaw, -4)) : '';
$encryptedAadhaar = !empty($aadhaarRaw) ? waqt_encrypt_sensitive_data($aadhaarRaw) : '';

// 4. Construct Comprehensive Lead Details JSON
$panDetails = json_decode($panDataJson, true) ?: [];
$gstDetails = json_decode($gstDataJson, true) ?: [];
$udyamDetails = json_decode($udyamDataJson, true) ?: [];

$leadDetailsArray = [
    'application_ref' => $applicationId,
    'applicant_name' => $fullName,
    'mobile_number' => $mobile,
    'mobile_verified' => $mobileVerified,
    'email' => $email,
    'pan_number' => $pan,
    'pan_verified' => $panVerified,
    'pan_verification_details' => $panDetails,
    'aadhaar_masked' => $maskedAadhaar,
    'aadhaar_verified' => $aadhaarVerified,
    'gst_number' => $gst,
    'gst_verified' => $gstVerified,
    'gst_verification_details' => $gstDetails,
    'udyam_number' => $udyam,
    'udyam_verified' => $udyamVerified,
    'udyam_verification_details' => $udyamDetails,
    'company_name' => $companyName,
    'business_type' => $businessType,
    'monthly_income' => $monthlyIncome,
    'shop_photo_url' => $shopPhotoPrimary,
    'shop_photos' => $shopPhotos,
    'loan_type' => $loanType,
    'loan_amount' => $loanAmount,
    'approved_limit' => $approvedLimit,
    'bre_status' => $breStatus,
    'bre_approved_limit' => $breApprovedLimit,
    'bre_tenure_days' => $breTenureDays,
    'bre_eod_balance' => $breEodBalance,
    'bre_upi_credits' => $breUpiCredits,
    'bre_decline_reasons' => $breDeclineReasons,
    'bre_crif_check' => $breCrifData,
    'cibil_score' => $cibilScore,
    'cibil_status' => $cibilScore >= 750 ? 'Excellent' : ($cibilScore >= 680 ? 'Good' : ($cibilScore >= 600 ? 'Fair' : ($cibilScore > 0 ? 'High Risk' : ($cibilScore === -1 ? 'New to Credit (NTC)' : 'Not Checked')))),
    'cibil_pdf_url' => $cibilPdfUrl,
    'loan_tenure' => $loanTenure,
    'loan_purpose' => $loanPurpose,
    'bank_name' => $bankName,
    'account_number' => $accountNumber,
    'ifsc_code' => $ifscCode,
    'account_type' => $accountType,
    'account_aggregator_verified' => $aaVerified,
    'account_aggregator_txn_id' => $aaTxnId,
    'aa_txn_id' => $aaTxnId,
    'monthly_inflow' => $aaInflow,
    'average_monthly_balance' => $aaAmb,
    'pincode' => $pincode,
    'city' => $city,
    'state' => $state,
    'address' => $address,
    'source' => 'create_lead_form',
    'created_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
    'created_at' => date('Y-m-d H:i:s')
];

$leadDetailsJson = json_encode($leadDetailsArray, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

// 5. Insert into loan_leads (for landing quick leads panel)
try {
    $leadStmt = $conn->prepare("
        INSERT INTO loan_leads (loan_type, full_name, mobile, pincode)
        VALUES (?, ?, ?, ?)
    ");
    if ($leadStmt) {
        $leadStmt->bind_param("ssss", $loanType, $fullName, $mobile, $pincode);
        $leadStmt->execute();
        $leadStmt->close();
    }
} catch (Throwable $e) {
    error_log('Lead quick record insert error: ' . $e->getMessage());
}

// 6. Insert into loan_applied_master (for full loan application master panel)
$savedMaster = false;
try {
    $masterStmt = $conn->prepare("
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

    if ($masterStmt) {
        $masterStmt->bind_param(
            "sssssdss",
            $applicationId,
            $fullName,
            $mobile,
            $pan,
            $city,
            $loanAmount,
            $loanType,
            $leadDetailsJson
        );
        $savedMaster = $masterStmt->execute();
        $masterStmt->close();
    }
} catch (Throwable $e) {
    error_log('Loan master insert error: ' . $e->getMessage());
}

// 7. Optional CRM Intake Push
try {
    if (function_exists('push_common_lead_to_crm')) {
        // Will safely run if configured
        push_common_lead_to_crm($conn, strtolower(str_replace(' ', '_', $loanType)), $applicationId);
    }
} catch (Throwable $e) {
    error_log('CRM push error: ' . $e->getMessage());
}

echo json_encode([
    'status' => 'success',
    'message' => 'Lead created and verified successfully!',
    'reference_id' => $applicationId,
    'applicant_name' => $fullName,
    'mobile_verified' => $mobileVerified,
    'pan_verified' => $panVerified,
    'aadhaar_verified' => $aadhaarVerified,
    'gst_verified' => $gstVerified,
    'udyam_verified' => $udyamVerified
]);
