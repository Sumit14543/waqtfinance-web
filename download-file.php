<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';

start_secure_session();

function deny_file_access(int $statusCode = 403): void
{
    http_response_code($statusCode);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'File not available.';
    exit;
}

function valid_signed_file_request(string $file): bool
{
    $expires = (int) ($_GET['expires'] ?? 0);
    $signature = (string) ($_GET['sig'] ?? '');

    if ($expires <= 0 || $signature === '' || $expires < time()) {
        return false;
    }

    $expected = hash_hmac('sha256', $file . '|' . $expires, required_env('APP_SECRET_KEY'));
    return hash_equals($expected, $signature);
}

function private_file_application_id(string $file): string
{
    $relative = waqt_private_relative_path($file);
    foreach (explode('/', $relative) as $segment) {
        if (preg_match('/^WAQT[A-Z0-9-]{4,}$/i', $segment)) {
            return $segment;
        }
    }

    return '';
}

function authenticated_customer_owns_app(string $applicationId): bool
{
    if ($applicationId === '' || empty($_SESSION['is_authenticated'])) {
        return false;
    }

    $mobile = trim((string) ($_SESSION['mobile_number'] ?? $_SESSION['user_id'] ?? ''));
    if ($mobile === '') {
        return false;
    }

    $conn = db();
    $stmt = $conn->prepare('SELECT id FROM loan_applications WHERE application_id = ? AND mobile = ? LIMIT 1');
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ss', $applicationId, $mobile);
    $stmt->execute();

    return (bool) $stmt->get_result()->fetch_assoc();
}

function session_can_access_private_file(string $file): bool
{
    if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        return true;
    }

    $relative = waqt_private_relative_path($file);
    $sessionAppIds = array_filter([
        (string) ($_SESSION['app_id'] ?? ''),
        (string) ($_SESSION['loan_app_id'] ?? ''),
        (string) ($_SESSION['loan_app_id_final'] ?? ''),
    ]);

    foreach ($sessionAppIds as $appId) {
        if ($appId !== '' && str_contains($relative, '/' . $appId . '/')) {
            return true;
        }
    }

    if (authenticated_customer_owns_app(private_file_application_id($file))) {
        return true;
    }

    return false;
}

$file = (string) ($_GET['file'] ?? '');

if ($file === '' || !waqt_is_private_file_path($file)) {
    deny_file_access(404);
}

$authorized = session_can_access_private_file($file) || valid_signed_file_request($file);
if (!$authorized) {
    deny_file_access(403);
}

if (file_exists(__DIR__ . '/includes/gcs_helper.php')) {
    require_once __DIR__ . '/includes/gcs_helper.php';
    if (waqt_is_gcs_enabled()) {
        $relative = waqt_private_relative_path($file);
        $gcsKey = 'private/' . $relative;

        $contents = waqt_download_from_gcs($gcsKey);
        if ($contents !== null) {
            $filename = basename($relative);
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = $finfo ? (string) finfo_buffer($finfo, $contents) : 'application/octet-stream';
            if ($finfo) {
                finfo_close($finfo);
            }

            header('X-Content-Type-Options: nosniff');
            header('Content-Type: ' . ($mime !== '' ? $mime : 'application/octet-stream'));
            header('Content-Length: ' . (string) strlen($contents));
            header('Content-Disposition: inline; filename="' . str_replace('"', '', $filename) . '"');
            header('Cache-Control: private, no-store, max-age=0');

            echo $contents;
            exit;
        }
        // Backward compatibility: documents created before GCS enablement can
        // still be served from private local storage after the same access check.
    }
}

$absolutePath = waqt_private_absolute_path($file);
$root = realpath(waqt_private_storage_root());
$resolved = realpath($absolutePath);

if ($root === false || $resolved === false || !str_starts_with(str_replace('\\', '/', $resolved), str_replace('\\', '/', $root) . '/')) {
    deny_file_access(404);
}

if (!is_file($resolved) || !is_readable($resolved)) {
    deny_file_access(404);
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = $finfo ? (string) finfo_file($finfo, $resolved) : 'application/octet-stream';
if ($finfo) {
    finfo_close($finfo);
}

$filename = basename($resolved);

header('X-Content-Type-Options: nosniff');
header('Content-Type: ' . ($mime !== '' ? $mime : 'application/octet-stream'));
header('Content-Length: ' . (string) filesize($resolved));
header('Content-Disposition: inline; filename="' . str_replace('"', '', $filename) . '"');
header('Cache-Control: private, no-store, max-age=0');

readfile($resolved);
exit;
