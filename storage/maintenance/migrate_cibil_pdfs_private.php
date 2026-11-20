<?php
declare(strict_types=1);
// Moves old CIBIL report PDFs from the public folder uploads/cibil/ to private storage
// (Cloud Storage when FILE_STORAGE_PROVIDER=GCS) and updates cibil_reports.pdf_url to the new private:// path.
//
// Usage (take a database backup first):
//   php storage/maintenance/migrate_cibil_pdfs_private.php            -> dry run, changes nothing
//   php storage/maintenance/migrate_cibil_pdfs_private.php --apply    -> copies each file, updates the row, then deletes the public file
//
// A public file is deleted only after its private copy is stored and the database row is updated.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

require_once __DIR__ . '/../../config/db.php';
$apply = in_array('--apply', $argv, true);
$conn = db();
$root = dirname(__DIR__, 2);

$rows = $conn->query("SELECT id, pdf_url FROM cibil_reports WHERE pdf_url LIKE 'uploads/cibil/%'");
if (!$rows) {
    fwrite(STDERR, "Could not read cibil_reports.\n");
    exit(1);
}

$moved = 0; $missing = 0; $failed = 0; $total = 0;
while ($row = $rows->fetch_assoc()) {
    $total++;
    $relative = (string) $row['pdf_url'];
    if (!preg_match('#^uploads/cibil/[A-Za-z0-9_.-]+\.pdf$#', $relative)) {
        echo "row {$row['id']}: unexpected path, skipped\n";
        $failed++;
        continue;
    }
    $file = $root . '/' . $relative;
    if (!is_file($file)) {
        echo "row {$row['id']}: file not found on this server, left as is\n";
        $missing++;
        continue;
    }
    if (!$apply) {
        echo "row {$row['id']}: would move " . basename($file) . "\n";
        continue;
    }
    $private = waqt_save_private_binary((string) file_get_contents($file), 'cibil', pathinfo($file, PATHINFO_FILENAME), 'pdf', 'application/pdf');
    if ($private === '') {
        echo "row {$row['id']}: could not store private copy, public file kept\n";
        $failed++;
        continue;
    }
    $stmt = $conn->prepare('UPDATE cibil_reports SET pdf_url = ? WHERE id = ?');
    $id = (int) $row['id'];
    $stmt->bind_param('si', $private, $id);
    if (!$stmt->execute()) {
        echo "row {$row['id']}: database update failed, public file kept\n";
        $failed++;
        continue;
    }
    @unlink($file);
    $moved++;
    echo "row {$row['id']}: moved\n";
}

echo ($apply ? 'APPLIED' : 'DRY RUN') . ": $total rows, $moved moved, $missing files missing, $failed failed\n";
