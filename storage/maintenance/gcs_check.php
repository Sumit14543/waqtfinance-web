<?php
declare(strict_types=1);
// Checks the Cloud Storage setup used for customer documents.
//   php storage/maintenance/gcs_check.php
// Required .env settings (values are never printed):
//   FILE_STORAGE_PROVIDER=GCS
//   GCS_PROJECT_ID=<project id>
//   FILE_UPLOAD_BUCKET=<private bucket name>   (uniform bucket-level access, no public access)
//   GOOGLE_APPLICATION_CREDENTIALS=<path to service-account JSON, kept OUTSIDE the public web folder>
// The service account needs only "Storage Object Creator" + "Storage Object Viewer" on that bucket.
// The check writes one small object: private/healthcheck/gcs_check_<time>.txt
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/gcs_helper.php';

$cfg = waqt_get_gcs_config();
$ok = true;
$line = static function (string $label, bool $pass, string $note = '') use (&$ok): void {
    echo ($pass ? '[ OK ] ' : '[FAIL] ') . $label . ($note !== '' ? " - $note" : '') . "\n";
    if (!$pass) $ok = false;
};

$line('FILE_STORAGE_PROVIDER is GCS', waqt_is_gcs_enabled(), 'currently: ' . ($cfg['provider'] ?: 'LOCAL'));
$line('FILE_UPLOAD_BUCKET set', ($cfg['bucket'] ?? '') !== '');
$cred = (string) ($cfg['credentials_path'] ?? '');
$line('GOOGLE_APPLICATION_CREDENTIALS set', $cred !== '');
if ($cred !== '') {
    $abs = (str_starts_with($cred, '/') || str_contains($cred, ':')) ? $cred : dirname(__DIR__, 2) . '/' . $cred;
    $line('credentials file readable', is_file($abs) && is_readable($abs));
    $webRoot = str_replace('\\', '/', (string) realpath(dirname(__DIR__, 2)));
    $realCred = str_replace('\\', '/', (string) realpath($abs));
    $inWebRoot = $realCred !== '' && str_starts_with($realCred, $webRoot . '/') && !str_starts_with($realCred, $webRoot . '/storage/');
    $line('credentials file is outside the public web folder', !$inWebRoot, $inWebRoot ? 'move it outside public_html or into storage/' : '');
}

if ($ok) {
    try {
        $key = 'private/healthcheck/gcs_check_' . time() . '.txt';
        $body = 'waqt-finance gcs check ' . date('c');
        $line('upload test object', waqt_upload_binary_to_gcs($body, $key, 'text/plain'));
        $line('download test object', waqt_download_from_gcs($key) === $body);
    } catch (Throwable $e) {
        $line('Cloud Storage request', false, $e->getMessage());
    }
}

echo $ok ? "Cloud Storage is ready. New customer documents will be stored in the bucket.\n" : "Fix the failed items above. Until then documents are stored on the server disk.\n";
exit($ok ? 0 : 1);
