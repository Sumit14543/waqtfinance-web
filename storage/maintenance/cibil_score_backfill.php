<?php
// Backfills cibil_reports.score from the stored raw_response (TransUnion TrueLink riskScore).
// Usage:  php cibil_backfill.php            -> dry run (no writes), prints counts only
//         php cibil_backfill.php --apply    -> writes the scores
// Only rows whose score is NULL/0 are touched. No personal data is printed.
require __DIR__ . '/../../config/db.php';
$apply = in_array('--apply', $argv, true);
$conn = db();
$res = $conn->query("SELECT id, raw_response FROM cibil_reports WHERE (score IS NULL OR score = 0) AND raw_response IS NOT NULL AND raw_response <> ''");
$found = 0; $missing = 0; $updated = 0; $dist = ['300-599' => 0, '600-699' => 0, '700-749' => 0, '750-900' => 0];
$upd = $conn->prepare("UPDATE cibil_reports SET score = ? WHERE id = ? AND (score IS NULL OR score = 0)");
while ($row = $res->fetch_assoc()) {
    $score = waqt_truelink_credit_score(json_decode((string) $row['raw_response'], true));
    if ($score === null) { $missing++; continue; }
    $found++;
    $band = $score < 600 ? '300-599' : ($score < 700 ? '600-699' : ($score < 750 ? '700-749' : '750-900'));
    $dist[$band]++;
    if ($apply) { $id = (int) $row['id']; $upd->bind_param('ii', $score, $id); $upd->execute(); $updated += $upd->affected_rows; }
}
echo ($apply ? "APPLIED" : "DRY RUN") . ": score found for $found rows, no score in $missing rows, updated $updated\n";
echo "score bands: " . json_encode($dist) . "\n";
