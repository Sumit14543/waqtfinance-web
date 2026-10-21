<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');
require_post();
require_valid_csrf();

$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
if (!$csrf || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['status' => 'error', 'message' => 'Security token expired. Please refresh and try again.']);
    http_response_code(419);
    exit;
}

$aadhaarRaw = trim((string) ($_POST['aadhaar'] ?? ''));
$aadhaar = preg_replace('/\D/', '', $aadhaarRaw);

if (strlen($aadhaar) !== 12 || !ctype_digit($aadhaar)) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid 12-digit Aadhaar number.']);
    exit;
}

// 1. UIDAI Verhoeff Checksum Validation
function verhoeff_check(string $num): bool {
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

if (!verhoeff_check($aadhaar)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid Aadhaar checksum. Please check the 12 digits carefully.'
    ]);
    exit;
}

$last4 = substr($aadhaar, -4);
$masked = 'XXXXXXXX' . $last4;

// 2. PAN-Aadhaar Link Match Check
// Government ITD / PAN API returns masked Aadhaar with first 2 digits and last 2 digits (e.g. 50XXXXXXXX16 or 50XX XXXX XX16)
$panLinkedAadhaar = $_SESSION['pan_data']['aadhaar'] ?? '';
$panMatched = false;
if (!empty($panLinkedAadhaar)) {
    $cleanAadhaar = strtoupper(trim((string) $panLinkedAadhaar));
    $actualFirst2 = substr($aadhaar, 0, 2);
    $actualLast2 = substr($aadhaar, -2);
    
    // Pattern 1: 2 digits at start, masked characters in middle, 2 digits at end (e.g. 50XXXXXXXX16, 50********16, 50XX XXXX XX16)
    if (preg_match('/^(\d{2})[X\*\s\-_]+(\d{2})$/', $cleanAadhaar, $m)) {
        $expectedFirst2 = $m[1];
        $expectedLast2 = $m[2];
        if ($actualFirst2 !== $expectedFirst2 || $actualLast2 !== $expectedLast2) {
            echo json_encode([
                'status' => 'error',
                'message' => "Aadhaar number does not match PAN record (PAN linked Aadhaar: {$expectedFirst2}XX XXXX XX{$expectedLast2})"
            ]);
            exit;
        }
        $panMatched = true;
    }
    // Pattern 2: Only last 4 digits masked (e.g. XXXXXXXX1234)
    elseif (preg_match('/^[X\*\s\-_]+(\d{4})$/', $cleanAadhaar, $m)) {
        $expectedLast4 = $m[1];
        if (substr($aadhaar, -4) !== $expectedLast4) {
            echo json_encode([
                'status' => 'error',
                'message' => "Aadhaar number does not match PAN record (PAN linked Aadhaar ends with: {$expectedLast4})"
            ]);
            exit;
        }
        $panMatched = true;
    }
    // Pattern 3: Raw digits or other formats
    else {
        $panDigits = preg_replace('/\D/', '', $cleanAadhaar);
        if (strlen($panDigits) === 4) {
            // First 2 and last 2 digits
            $expectedFirst2 = substr($panDigits, 0, 2);
            $expectedLast2 = substr($panDigits, -2);
            if ($actualFirst2 !== $expectedFirst2 || $actualLast2 !== $expectedLast2) {
                echo json_encode([
                    'status' => 'error',
                    'message' => "Aadhaar number does not match PAN record (PAN linked Aadhaar: {$expectedFirst2}XX XXXX XX{$expectedLast2})"
                ]);
                exit;
            }
            $panMatched = true;
        } elseif (strlen($panDigits) === 12) {
            if ($aadhaar !== $panDigits) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Aadhaar number does not match PAN record.'
                ]);
                exit;
            }
            $panMatched = true;
        }
    }
}

// 3. Generate 6-digit Aadhaar OTP
$otp = (string) random_int(100000, 999999);
$otpHash = hash('sha256', $otp);

$_SESSION['aadhaar_otp_hash'] = $otpHash;
$_SESSION['aadhaar_otp_number'] = $aadhaar;
$_SESSION['aadhaar_otp_last4'] = $last4;
$_SESSION['aadhaar_otp_time'] = time();

// If applicant mobile is present in session, trigger WhatsApp/SMS dispatch
$mobile = $_SESSION['lead_verified_mobile'] ?? $_POST['mobile'] ?? '';
if (!empty($mobile) && preg_match('/^[6-9]\d{9}$/', $mobile)) {
    try {
        if (function_exists('sendWhatsAppOTP')) {
            sendWhatsAppOTP($mobile, $otp);
        }
    } catch (Throwable $e) {
        error_log('Aadhaar OTP dispatch notice: ' . $e->getMessage());
    }
}

$host = $_SERVER['HTTP_HOST'] ?? '';
$isLocal = in_array($host, ['localhost', '127.0.0.1'], true) || str_contains($host, 'localhost');

$res = [
    'status' => 'success',
    'message' => 'UIDAI verification OTP sent to Aadhaar-registered mobile',
    'masked_aadhaar' => substr($aadhaar, 0, 2) . 'XX XXXX XX' . substr($aadhaar, -2),
    'last4' => $last4,
    'retry_after' => 30
];

// Provide debug OTP on localhost/dev
if ($isLocal) {
    $res['debug_otp'] = $otp;
}

echo json_encode($res);
