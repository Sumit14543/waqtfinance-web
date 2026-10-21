<?php
declare(strict_types=1);

require_once '../config.php';

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

// UIDAI Verhoeff Checksum Validation
function verhoeff_validate(string $num): bool {
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

if (!verhoeff_validate($aadhaar)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid Aadhaar number checksum. Please re-check the 12 digits.'
    ]);
    exit;
}

$last4 = substr($aadhaar, -4);
$masked = 'XXXXXXXX' . $last4;

// PAN Match Check if PAN was already verified in session
$panMatched = null;
$panLinkedAadhaar = $_SESSION['pan_data']['aadhaar'] ?? '';
if ($panLinkedAadhaar !== '') {
    $panDigits = preg_replace('/\D/', '', $panLinkedAadhaar);
    if (strlen($panDigits) >= 4) {
        $panLast4 = substr($panDigits, -4);
        $panMatched = ($panLast4 === $last4);
    }
}

// Store verified aadhaar in session
$_SESSION['lead_aadhaar_verified'] = true;
$_SESSION['lead_aadhaar_masked'] = $masked;
$_SESSION['lead_aadhaar_last4'] = $last4;

echo json_encode([
    'status' => 'success',
    'message' => 'Aadhaar verified successfully',
    'masked_aadhaar' => $masked,
    'formatted_masked' => 'XXXX XXXX ' . $last4,
    'last4' => $last4,
    'pan_matched' => $panMatched,
    'pan_linked_status' => $_SESSION['pan_data']['aadhaar_linked'] ?? 'N/A'
]);
