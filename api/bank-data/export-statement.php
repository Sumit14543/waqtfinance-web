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

$viewMode = trim((string)($_GET['view'] ?? 'txns'));
$cleanBank = preg_replace('/[^A-Za-z0-9]/', '_', $bankName);
$cleanAcc = substr(preg_replace('/[^A-Za-z0-9]/', '', $accNum), -4);
$filename = ($viewMode === 'daily') 
    ? "Daily_Credit_Report_{$cleanBank}_{$cleanAcc}_" . date('Ymd') . ".csv"
    : "Statement_{$cleanBank}_{$cleanAcc}_" . date('Ymd') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');

// UTF-8 BOM for proper Excel display
fputs($out, "\xEF\xBB\xBF");

// Header Metadata
fputcsv($out, [($viewMode === 'daily') ? 'WAQT FINANCE - ACTUAL DAILY CREDIT REPORT (PER-DAY BREAKDOWN)' : 'WAQT FINANCE - OFFICIAL BANK STATEMENT REPORT']);
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

if ($viewMode === 'daily') {
    // Group transactions by date
    $daily = [];
    foreach ($transactions as $t) {
        $rawAmt = (float)($t['amount'] ?? 0);
        $d = substr((string)($t['date'] ?? ''), 0, 10);
        if (empty($d)) continue;
        if (!isset($daily[$d])) {
            $daily[$d] = [
                'date' => $d,
                'credit_total' => 0.0,
                'credit_count' => 0,
                'debit_total' => 0.0,
                'debit_count' => 0
            ];
        }
        if ($rawAmt >= 0) {
            $daily[$d]['credit_total'] += $rawAmt;
            $daily[$d]['credit_count']++;
        } else {
            $daily[$d]['debit_total'] += abs($rawAmt);
            $daily[$d]['debit_count']++;
        }
    }
    krsort($daily);

    // Calculate 90-day metrics
    $maxDate = null;
    foreach ($daily as $d => $r) {
        if ($maxDate === null || $d > $maxDate) $maxDate = $d;
    }
    $cutoff90Ts = $maxDate ? (strtotime($maxDate) - (90 * 86400)) : (time() - (90 * 86400));
    $days1000 = 0; $days2000 = 0; $days5000 = 0; $total90CrDays = 0;
    foreach ($daily as $d => $r) {
        if (strtotime($d) >= $cutoff90Ts) {
            if ($r['credit_total'] > 0) $total90CrDays++;
            if ($r['credit_total'] >= 1000) $days1000++;
            if ($r['credit_total'] >= 2000) $days2000++;
            if ($r['credit_total'] >= 5000) $days5000++;
        }
    }
    fputcsv($out, ['--- 90-DAY CREDIT FREQUENCY ANALYSIS ---']);
    fputcsv($out, ['90-Day Period Range', date('Y-m-d', $cutoff90Ts) . ' to ' . $maxDate]);
    fputcsv($out, ['Days with Actual Credit >= INR 1,000 (90 Days)', $days1000 . ' Days']);
    fputcsv($out, ['Days with Actual Credit >= INR 2,000 (90 Days)', $days2000 . ' Days']);
    fputcsv($out, ['Days with Actual Credit >= INR 5,000 (90 Days)', $days5000 . ' Days']);
    fputcsv($out, ['Total Days with Any Credit (90 Days)', $total90CrDays . ' Days']);
    fputcsv($out, []);

    fputcsv($out, ['#', 'Date (Tarikh)', 'Actual Amount Credited (INR)', 'Number of Credit Txns', 'Debited on Day (INR)', 'Net Balance Change (INR)']);
    $idx = 1;
    foreach ($daily as $row) {
        $net = $row['credit_total'] - $row['debit_total'];
        fputcsv($out, [
            $idx++,
            $row['date'],
            number_format($row['credit_total'], 2),
            $row['credit_count'],
            number_format($row['debit_total'], 2),
            ($net >= 0 ? '+' : '-') . number_format(abs($net), 2)
        ]);
    }
} else {
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
}

fclose($out);
exit;
