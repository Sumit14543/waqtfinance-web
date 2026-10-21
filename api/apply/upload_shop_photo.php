<?php
declare(strict_types=1);

require_once '../config.php';
require_once __DIR__ . '/../../includes/loan_flow.php';

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF Verification
$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
$sessionCsrf = $_SESSION['csrf_token'] ?? '';
if (!$csrf || !hash_equals((string)$sessionCsrf, (string)$csrf)) {
    http_response_code(419);
    echo json_encode([
        'status' => 'error',
        'message' => 'Security token expired. Please refresh the page and try again.'
    ]);
    exit;
}

$uploadDir = dirname(__DIR__, 2) . '/uploads/shop_photos';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

// Handle file upload via $_FILES
if (isset($_FILES['photo']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
    $file = $_FILES['photo'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Upload error: ' . $file['error']]);
        exit;
    }

    if ($file['size'] > 12 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'File size must be under 12MB.']);
        exit;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowed[$mime])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Only JPG, PNG or WEBP images are allowed.']);
        exit;
    }

    $ext = $allowed[$mime];
    $filename = 'shop_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $targetPath = $uploadDir . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        @chmod($targetPath, 0644);
        echo json_encode([
            'status' => 'success',
            'url' => 'uploads/shop_photos/' . $filename,
            'filename' => $filename,
            'size' => $file['size']
        ]);
        exit;
    }

    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to save uploaded file.']);
    exit;
}

// Handle base64 image data (e.g. from camera canvas or data URI)
$rawJson = file_get_contents('php://input');
$jsonBody = json_decode((string)$rawJson, true) ?: [];
$imageData = $jsonBody['image_data'] ?? $_POST['image_data'] ?? '';

if (!empty($imageData) && is_string($imageData)) {
    if (preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,(.+)$/i', $imageData, $matches)) {
        $ext = strtolower($matches[1]) === 'png' ? 'png' : (strtolower($matches[1]) === 'webp' ? 'webp' : 'jpg');
        $decoded = base64_decode($matches[2]);
        if ($decoded !== false && strlen($decoded) > 0) {
            if (strlen($decoded) > 12 * 1024 * 1024) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Image size exceeds 12MB.']);
                exit;
            }

            $filename = 'shop_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $targetPath = $uploadDir . '/' . $filename;

            if (@file_put_contents($targetPath, $decoded)) {
                @chmod($targetPath, 0644);
                echo json_encode([
                    'status' => 'success',
                    'url' => 'uploads/shop_photos/' . $filename,
                    'filename' => $filename,
                    'size' => strlen($decoded)
                ]);
                exit;
            }
        }
    }

    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid image base64 data format.']);
    exit;
}

http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'No image file or camera snapshot received.']);
