<?php
declare(strict_types=1);

/**
 * Digitap Bank Data - Statement CSV / Excel Exporter
 * Endpoint: /api/bank-data/export-statement.php?txn_id=...
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/DigitapBankDataUIService.php';

$txnId = trim((string)($_GET['txn_id'] ?? ''));
if (empty($txnId)) {
    http_response_code(400);
    echo "Transaction ID (txn_id) is required.";
    exit;
}

$localReportFile = dirname(__DIR__, 2) . '/storage/reports/report_' . $txnId . '.json';
$data = null;
if (file_exists($localReportFile)) {
    $data = json_decode((string)file_get_contents($localReportFile), true);
}

if (empty($data)) {
    $service = new DigitapBankDataUIService();
    $data = $service->retrieveReport($txnId, 'json', 'type3');
    if (!empty($data) && !empty($data['banks'])) {
        $storageDir = dirname(__DIR__, 2) . '/storage/reports';
        if (!is_dir($storageDir)) {
            @mkdir($storageDir, 0755, true);
        }
        @file_put_contents($localReportFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}

if (empty($data) || empty($data['banks'])) {
    http_response_code(404);
    echo "Bank statement data not found or still processing for transaction " . htmlspecialchars($txnId);
    exit;
}

$bankObj = $data['banks'][0] ?? [];
$bankName = $bankObj['bank'] ?? 'Bank';
$accObj = $bankObj['accounts'][0] ?? [];
$accNum = $accObj['account_number'] ?? 'Account';
$accType = $accObj['account_type'] ?? 'SAVINGS';
$ifsc = $accObj['ifsc_code'] ?? '';
$balance = $accObj['current_balance'] ?? '';
$holders = $accObj['customer_info']['holders'] ?? [];
$holderName = !empty($holders[0]['name']) ? $holders[0]['name'] : 'Customer';
$pan = !empty($holders[0]['pan']) ? $holders[0]['pan'] : '';
$contact = !empty($holders[0]['contact_number']) ? $holders[0]['contact_number'] : '';
$transactions = $accObj['transactions'] ?? [];

$cleanBank = preg_replace('/[^A-Za-z0-9]/', '_', $bankName);
$cleanAcc = substr(preg_replace('/[^A-Za-z0-9]/', '', $accNum), -4);
$filename = "Statement_{$cleanBank}_{$cleanAcc}_" . date('Ymd') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');

// UTF-8 BOM for proper Excel display
fputs($out, "\xEF\xBB\xBF");

// Header Metadata
fputcsv($out, ['WAQT FINANCE - OFFICIAL BANK STATEMENT REPORT']);
fputcsv($out, ['Generated via RBI NBFC-AA (Account Aggregator)']);
fputcsv($out, []);
fputcsv($out, ['Account Holder Name', $holderName]);
fputcsv($out, ['Bank Name', $bankName]);
fputcsv($out, ['Account Number', $accNum]);
fputcsv($out, ['IFSC Code', $ifsc]);
fputcsv($out, ['Account Type', $accType]);
fputcsv($out, ['Current Balance', 'INR ' . number_format((float)$balance, 2)]);
if (!empty($pan)) fputcsv($out, ['PAN Number', $pan]);
if (!empty($contact)) fputcsv($out, ['Registered Mobile', $contact]);
fputcsv($out, ['Transaction ID', $txnId]);
fputcsv($out, ['Report Date', date('d-M-Y H:i:s')]);
fputcsv($out, []);

// Table Header
fputcsv($out, ['#', 'Date', 'Narration / Description', 'Reference / Txn ID', 'Type', 'Amount (INR)', 'Balance (INR)', 'Payment Mode']);

$idx = 1;
foreach ($transactions as $t) {
    $rawAmt = (float)($t['amount'] ?? 0);
    $type = $rawAmt >= 0 ? 'CREDIT' : 'DEBIT';
    $amt = abs($rawAmt);
    $bal = isset($t['balance']) ? number_format((float)$t['balance'], 2) : '';

    fputcsv($out, [
        $idx++,
        $t['date'] ?? '',
        $t['narration'] ?? '',
        $t['reference'] ?? $t['transaction_id'] ?? '',
        $type,
        ($type === 'CREDIT' ? '+' : '-') . number_format($amt, 2),
        $bal,
        $t['mode_from_source'] ?? 'ELECTRONIC'
    ]);
}

fclose($out);
exit;
