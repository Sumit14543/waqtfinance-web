<?php
declare(strict_types=1);

/**
 * Helper to discover and resolve Account Aggregator reports for leads
 * whose aa_txn_id might be unlinked or missing in database records.
 */

require_once __DIR__ . '/DigitapBankDataUIService.php';

function waqt_resolve_aa_report(?mysqli $conn, string $mobile = '', string $account = '', string $name = '', string $leadId = ''): array
{
    $cleanMobile = preg_replace('/\D/', '', $mobile);
    $cleanMobile10 = strlen($cleanMobile) >= 10 ? substr($cleanMobile, -10) : $cleanMobile;
    $cleanAcc = preg_replace('/\D/', '', $account);
    $cleanAcc4 = strlen($cleanAcc) >= 4 ? substr($cleanAcc, -4) : $cleanAcc;
    $cleanName = strtolower(trim($name));

    $reportsDir = dirname(__DIR__) . '/storage/reports';
    $matchedTxnId = '';
    $matchedData = null;

    // 1. Scan storage/reports directory
    if (is_dir($reportsDir)) {
        $files = glob($reportsDir . '/report_*.json');
        if ($files) {
            // Sort newest first
            usort($files, function ($a, $b) {
                return filemtime($b) <=> filemtime($a);
            });

            foreach ($files as $filePath) {
                $raw = @file_get_contents($filePath);
                if (!$raw) continue;
                $decoded = json_decode($raw, true);
                if (!is_array($decoded) || empty($decoded['banks'])) continue;

                $filename = basename($filePath);
                $fileTxnId = preg_replace('/^report_|\.json$/', '', $filename);

                $accObj = $decoded['banks'][0]['accounts'][0] ?? [];
                $fileAccNum = preg_replace('/\D/', '', (string)($accObj['account_number'] ?? ''));
                $holders = $accObj['customer_info']['holders'] ?? [];
                $fileHolderName = strtolower((string)($holders[0]['name'] ?? ''));
                $filePhone = preg_replace('/\D/', '', (string)($holders[0]['contact_number'] ?? $holders[0]['mobile'] ?? ''));

                $isMatch = false;

                // Match by phone
                if (!empty($cleanMobile10) && !empty($filePhone) && str_ends_with($filePhone, $cleanMobile10)) {
                    $isMatch = true;
                }
                // Match by account number last 4 digits
                elseif (!empty($cleanAcc4) && !empty($fileAccNum) && str_ends_with($fileAccNum, $cleanAcc4)) {
                    $isMatch = true;
                }
                // Match by applicant name
                elseif (!empty($cleanName) && !empty($fileHolderName) && (strpos($fileHolderName, $cleanName) !== false || strpos($cleanName, $fileHolderName) !== false)) {
                    $isMatch = true;
                }

                if ($isMatch) {
                    $matchedTxnId = $fileTxnId;
                    $matchedData = $decoded;
                    break;
                }
            }
        }
    }

    // 2. Check loan_applications table if available
    if (empty($matchedTxnId) && $conn instanceof mysqli && !empty($cleanMobile10)) {
        $stmt = $conn->prepare("SELECT aa_txn_id, aa_report_file FROM loan_applications WHERE mobile = ? OR mobile = ? ORDER BY id DESC LIMIT 1");
        if ($stmt) {
            $fullMob = '91' . $cleanMobile10;
            $stmt->bind_param("ss", $cleanMobile10, $fullMob);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                if (!empty($row['aa_txn_id'])) {
                    $matchedTxnId = (string)$row['aa_txn_id'];
                }
            }
            $stmt->close();
        }
    }

    // 3. If matched, optionally patch loan_applied_master to link permanently
    if (!empty($matchedTxnId) && $conn instanceof mysqli) {
        try {
            $searchField = !empty($leadId) ? (is_numeric($leadId) ? 'id' : 'application_id') : (!empty($cleanMobile10) ? 'mobile_number' : '');
            $searchValue = !empty($leadId) ? $leadId : $cleanMobile10;

            if (!empty($searchField) && !empty($searchValue)) {
                $query = "SELECT id, loan_details FROM loan_applied_master WHERE {$searchField} = ? ORDER BY id DESC LIMIT 1";
                $mStmt = $conn->prepare($query);
                if ($mStmt) {
                    $mStmt->bind_param("s", $searchValue);
                    $mStmt->execute();
                    $mRes = $mStmt->get_result();
                    if ($mRow = $mRes->fetch_assoc()) {
                        $details = json_decode((string)($mRow['loan_details'] ?? '{}'), true) ?: [];
                        if (empty($details['account_aggregator_txn_id']) || empty($details['aa_txn_id'])) {
                            $details['account_aggregator_txn_id'] = $matchedTxnId;
                            $details['aa_txn_id'] = $matchedTxnId;
                            $details['account_aggregator_verified'] = true;

                            $updatedJson = json_encode($details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                            $upStmt = $conn->prepare("UPDATE loan_applied_master SET loan_details = ? WHERE id = ?");
                            if ($upStmt) {
                                $upStmt->bind_param("si", $updatedJson, $mRow['id']);
                                $upStmt->execute();
                                $upStmt->close();
                            }
                        }
                    }
                    $mStmt->close();
                }
            }
        } catch (Throwable $e) {
            error_log('Error auto-linking AA txn_id to lead: ' . $e->getMessage());
        }
    }

    // 4. If matchedTxnId found but matchedData was not cached, retrieve via service
    if (!empty($matchedTxnId) && empty($matchedData)) {
        try {
            $service = new DigitapBankDataUIService();
            $matchedData = $service->retrieveReport($matchedTxnId);
        } catch (Throwable $e) {}
    }

    if (!empty($matchedTxnId) && !empty($matchedData)) {
        return [
            'status' => 'success',
            'txn_id' => $matchedTxnId,
            'report' => $matchedData
        ];
    }

    return [
        'status' => 'error',
        'message' => 'No matching bank statement report found.'
    ];
}
