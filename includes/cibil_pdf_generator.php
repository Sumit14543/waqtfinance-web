<?php
declare(strict_types=1);

/**
 * Pure PHP Minimal Multi-Page PDF Generator for CRIF High Mark & CIBIL Bureau Reports
 * Generates standards-compliant, multi-page PDF 1.4 documents without external dependencies.
 * Matches exact high-detail bureau report design with Executive Credit Summary & all Tradeline Cards.
 */
class CibilPdfGenerator
{
    public function generate(array $data, string $outputPath): bool
    {
        $fullName = htmlspecialchars((string)($data['full_name'] ?? 'Applicant'));
        $pan = strtoupper((string)($data['pan'] ?? ''));
        $mobile = (string)($data['mobile'] ?? '');
        $dob = (string)($data['dob'] ?? 'N/A');
        $score = (int)($data['score'] ?? 0);
        $scoreDesc = (string)($data['score_description'] ?? '');
        $scoreDisplay = (string)($data['score_display'] ?? ($score > 0 ? (string)$score : 'NTC'));
        if ($score > 0 && !empty($scoreDesc)) {
            $scoreDisplay .= " ({$scoreDesc})";
        }
        $reportDate = (string)($data['report_date'] ?? date('d/m/Y, g:i:s a'));
        $provider = (string)($data['provider'] ?? 'CRIF High Mark (via Digitap)');

        $totalAccounts = (int)($data['total_accounts'] ?? 0);
        $activeAccounts = (int)($data['active_accounts'] ?? 0);
        $overdueAccounts = (int)($data['overdue_accounts'] ?? 0);
        $totalOutstanding = (string)($data['total_outstanding'] ?? 'Rs. 0');
        $unsecuredBal = (string)($data['unsecured_balance'] ?? 'Rs. 0.0');
        $securedBal = (string)($data['secured_balance'] ?? 'Rs. 0.0');
        $monthlyEmi = (string)($data['monthly_emi'] ?? 'Rs. 0');
        $inquiries6m = (string)($data['inquiries_6m'] ?? '0');
        $inquiries12m = (string)($data['inquiries_12m'] ?? '0');

        $tradelines = is_array($data['tradelines'] ?? null) ? $data['tradelines'] : [];

        // Distribute tradelines across pages
        // Page 1 has header (Navy bar), Applicant & Bureau info, Summary Box, and Section Heading.
        // On Page 1, space allows ~8 cards.
        // Subsequent pages have a small top header, allowing ~12 cards per page.
        $page1Count = 8;
        $subsequentPageCount = 12;

        $pagesTradelines = [];
        if (empty($tradelines)) {
            $pagesTradelines[] = [];
        } else {
            $remaining = $tradelines;
            $pagesTradelines[] = array_splice($remaining, 0, $page1Count);
            while (!empty($remaining)) {
                $pagesTradelines[] = array_splice($remaining, 0, $subsequentPageCount);
            }
        }

        $totalPages = count($pagesTradelines);
        $pagesContent = [];

        foreach ($pagesTradelines as $pageIndex => $pageCards) {
            $pageNum = $pageIndex + 1;
            $ops = [];

            if ($pageNum === 1) {
                // PAGE 1: Full Header & Profile & Executive Summary
                // Navy Top Header Bar
                $ops[] = "0.106 0.165 0.278 rg"; // Dark Navy #1b2a47
                $ops[] = "36 788 523 38 re f";
                $ops[] = "BT /F2 13.5 Tf 1 1 1 rg 48 802 Td (CRIF HIGH MARK CREDIT INFORMATION REPORT) Tj ET";
                $ops[] = "BT /F1 8 Tf 0.85 0.88 0.92 rg 368 804 Td (CONFIDENTIAL CREDIT ASSESSMENT) Tj ET";

                // Applicant Information (Left Column)
                $ops[] = "BT /F2 10.5 Tf 0.12 0.16 0.22 rg 48 765 Td (Applicant Information) Tj ET";
                $ops[] = "BT /F1 9 Tf 0.25 0.3 0.35 rg 48 748 Td (Full Name: {$this->escape($fullName)}) Tj ET";
                $ops[] = "BT /F1 9 Tf 0.25 0.3 0.35 rg 48 733 Td (Mobile: {$this->escape($mobile)}) Tj ET";
                $ops[] = "BT /F1 9 Tf 0.25 0.3 0.35 rg 48 718 Td (PAN: {$this->escape($pan)}) Tj ET";

                // Bureau Assessment (Right Column)
                $ops[] = "BT /F2 10.5 Tf 0.12 0.16 0.22 rg 330 765 Td (Bureau Assessment) Tj ET";
                $ops[] = "BT /F1 9 Tf 0.25 0.3 0.35 rg 330 748 Td (Bureau Score: {$this->escape($scoreDisplay)}) Tj ET";
                $ops[] = "BT /F1 9 Tf 0.25 0.3 0.35 rg 330 733 Td (Date of Report: {$this->escape($reportDate)}) Tj ET";
                $ops[] = "BT /F1 9 Tf 0.25 0.3 0.35 rg 330 718 Td (Provider: {$this->escape($provider)}) Tj ET";

                // Executive Credit Summary
                $ops[] = "BT /F2 10.5 Tf 0.12 0.16 0.22 rg 48 695 Td (Executive Credit Summary) Tj ET";

                // Summary Box
                $ops[] = "0.95 0.965 0.98 rg 36 618 523 68 re f";
                $ops[] = "0.85 0.88 0.92 RG 0.8 w 36 618 523 68 re S";

                // 3x3 Grid inside Summary Box
                // Row 1
                $ops[] = "BT /F2 8.5 Tf 0.2 0.25 0.32 rg 48 668 Td (Total Accounts: ) Tj /F1 8.5 Tf ({$totalAccounts}) Tj ET";
                $ops[] = "BT /F2 8.5 Tf 0.2 0.25 0.32 rg 205 668 Td (Total Outstanding: ) Tj /F1 8.5 Tf ({$this->escape($totalOutstanding)}) Tj ET";
                $ops[] = "BT /F2 8.5 Tf 0.2 0.25 0.32 rg 375 668 Td (Active Monthly EMI: ) Tj /F1 8.5 Tf ({$this->escape($monthlyEmi)}) Tj ET";

                // Row 2
                $ops[] = "BT /F2 8.5 Tf 0.2 0.25 0.32 rg 48 648 Td (Active Accounts: ) Tj /F1 8.5 Tf ({$activeAccounts}) Tj ET";
                $ops[] = "BT /F2 8.5 Tf 0.2 0.25 0.32 rg 205 648 Td (Unsecured Bal: ) Tj /F1 8.5 Tf ({$this->escape($unsecuredBal)}) Tj ET";
                $ops[] = "BT /F2 8.5 Tf 0.2 0.25 0.32 rg 375 648 Td (Inquiries \\(Last 6M\\): ) Tj /F1 8.5 Tf ({$inquiries6m}) Tj ET";

                // Row 3
                $overdueColor = $overdueAccounts > 0 ? "0.85 0.15 0.15" : "0.2 0.25 0.32";
                $ops[] = "BT /F2 8.5 Tf 0.2 0.25 0.32 rg 48 628 Td (Overdue Accounts: ) Tj /F2 8.5 Tf {$overdueColor} rg ({$overdueAccounts}) Tj ET";
                $ops[] = "BT /F2 8.5 Tf 0.2 0.25 0.32 rg 205 628 Td (Secured Bal: ) Tj /F1 8.5 Tf ({$this->escape($securedBal)}) Tj ET";
                $ops[] = "BT /F2 8.5 Tf 0.2 0.25 0.32 rg 375 628 Td (Inquiries \\(Last 12M\\): ) Tj /F1 8.5 Tf ({$inquiries12m}) Tj ET";

                // Trade Lines Section Heading
                $ops[] = "BT /F2 10.5 Tf 0.12 0.16 0.22 rg 48 595 Td (Trade Lines & Credit Facilities \\({$totalAccounts} Accounts\\)) Tj ET";

                $startY = 540;
            } else {
                // PAGE 2+: Running Header
                $ops[] = "0.106 0.165 0.278 rg 36 795 523 30 re f";
                $ops[] = "BT /F2 10.5 Tf 1 1 1 rg 48 806 Td (CRIF HIGH MARK CREDIT INFORMATION REPORT \\(Continued\\)) Tj ET";
                $ops[] = "BT /F1 8.5 Tf 0.85 0.88 0.92 rg 410 806 Td (Trade Lines & Facilities \\(Page {$pageNum} of {$totalPages}\\)) Tj ET";

                $startY = 735;
            }

            // Render Card for each Tradeline on this page
            $currY = $startY;
            if (empty($pageCards)) {
                // NTC or No Tradelines Box
                $ops[] = "1 1 1 rg 36 {$currY} 523 48 re f";
                $ops[] = "0.85 0.88 0.92 RG 0.7 w 36 {$currY} 523 48 re S";
                $ops[] = "BT /F2 9 Tf 0.15 0.45 0.75 rg 48 " . ($currY + 28) . " Td ([ NEW TO CREDIT ] No Prior Credit Tradelines Recorded in Bureau Database) Tj ET";
                $ops[] = "BT /F1 8 Tf 0.4 0.45 0.5 rg 48 " . ($currY + 14) . " Td (Applicant has no historical loan or credit card facilities on record with CRIF High Mark / CIBIL.) Tj ET";
            } else {
                foreach ($pageCards as $card) {
                    $idx = (int)($card['index'] ?? 1);
                    $status = strtoupper((string)($card['status'] ?? 'ACTIVE'));
                    $isClosed = (strcasecmp($status, 'CLOSED') === 0);
                    $lender = $this->escape((string)($card['lender'] ?? 'Commercial Institution'));
                    $type = $this->escape((string)($card['type'] ?? 'Loan Account'));
                    $acct = $this->escape((string)($card['account_number'] ?? ''));
                    $acctStr = !empty($acct) ? " \\(A/C: {$acct}\\)" : "";

                    $disbAmt = $this->escape((string)($card['disbursed_amount'] ?? '0'));
                    $disbDt = $this->escape((string)($card['disbursed_date'] ?? ''));
                    $bal = $this->escape((string)($card['balance'] ?? '0'));
                    $overdue = $this->escape((string)($card['overdue'] ?? '0'));
                    $emi = $this->escape((string)($card['emi'] ?? ''));
                    $closedDt = $this->escape((string)($card['closed_date'] ?? ''));

                    // Card background and border
                    $ops[] = "1 1 1 rg 36 {$currY} 523 48 re f";
                    $ops[] = "0.85 0.88 0.92 RG 0.7 w 36 {$currY} 523 48 re S";

                    // Line 1: [ STATUS ] Index. LENDER - TYPE (A/C: ...)
                    $statusColor = $isClosed ? "0.45 0.5 0.55" : "0.08 0.55 0.25";
                    $statusTag = "[ " . ($isClosed ? "CLOSED" : "ACTIVE") . " ]";
                    $titleLine = "{$idx}. {$lender} - {$type}{$acctStr}";

                    $ops[] = "BT /F2 8.5 Tf {$statusColor} rg 46 " . ($currY + 34) . " Td ({$statusTag}) Tj ET";
                    $ops[] = "BT /F2 8.5 Tf 0.15 0.2 0.3 rg 102 " . ($currY + 34) . " Td ({$titleLine}) Tj ET";

                    // Line 2: Disbursed: Rs. X (Date) | Balance: Rs. Y | Overdue: Rs. Z | EMI: Rs. ...
                    $line2 = "Disbursed: Rs. {$disbAmt}";
                    if (!empty($disbDt)) {
                        $line2 .= " \\({$disbDt}\\)";
                    }
                    $line2 .= " | Balance: Rs. {$bal} | Overdue: Rs. {$overdue}";
                    if (!empty($emi) && $emi !== '0') {
                        $line2 .= " | EMI: Rs. {$emi}";
                    }
                    $ops[] = "BT /F1 7.8 Tf 0.35 0.4 0.45 rg 46 " . ($currY + 20) . " Td ({$line2}) Tj ET";

                    // Line 3: Status: Closed | Closed Date: ...
                    $line3 = "Status: " . ($isClosed ? "Closed" : "Active");
                    if ($isClosed && !empty($closedDt)) {
                        $line3 .= " | Closed Date: {$closedDt}";
                    } elseif (!empty($card['reported_date'])) {
                        $line3 .= " | Last Reported: " . $this->escape((string)$card['reported_date']);
                    }
                    $ops[] = "BT /F1 7.8 Tf 0.45 0.5 0.55 rg 46 " . ($currY + 7) . " Td ({$line3}) Tj ET";

                    $currY -= 55; // Move down for next card
                }
            }

            // Running Footer at bottom of each page
            $ops[] = "0.85 0.88 0.92 RG 0.5 w 36 40 523 0.5 re S";
            $ops[] = "BT /F1 7.5 Tf 0.45 0.5 0.55 rg 36 28 Td (Notice: Confidential electronic bureau assessment record via Digitap Bureau Services for Waqt Finance under end-user consent.) Tj ET";
            $ops[] = "BT /F2 7.5 Tf 0.3 0.35 0.4 rg 490 28 Td (Page {$pageNum} of {$totalPages}) Tj ET";

            $pagesContent[] = implode("\n", $ops);
        }

        // Construct valid Multi-Page PDF 1.4 Binary
        $pdf = "%PDF-1.4\n";
        $offsets = [];

        $pageObjIds = [];
        for ($i = 0; $i < $totalPages; $i++) {
            $pageObjIds[] = 3 + $i;
        }

        $contentObjIds = [];
        for ($i = 0; $i < $totalPages; $i++) {
            $contentObjIds[] = 3 + $totalPages + $i;
        }

        $font1Id = 3 + 2 * $totalPages;
        $font2Id = $font1Id + 1;
        $totalObjs = $font2Id;

        // Obj 1: Catalog
        $offsets[1] = strlen($pdf);
        $pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";

        // Obj 2: Pages
        $kidsStr = implode(' 0 R ', $pageObjIds) . ' 0 R';
        $offsets[2] = strlen($pdf);
        $pdf .= "2 0 obj\n<< /Type /Pages /Kids [{$kidsStr}] /Count {$totalPages} >>\nendobj\n";

        // Obj 3 .. Page objects
        for ($i = 0; $i < $totalPages; $i++) {
            $pId = $pageObjIds[$i];
            $cId = $contentObjIds[$i];
            $offsets[$pId] = strlen($pdf);
            $pdf .= "{$pId} 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595.28 841.89] /Contents {$cId} 0 R /Resources << /Font << /F1 {$font1Id} 0 R /F2 {$font2Id} 0 R >> >> >>\nendobj\n";
        }

        // Obj Content streams
        for ($i = 0; $i < $totalPages; $i++) {
            $cId = $contentObjIds[$i];
            $content = $pagesContent[$i];
            $len = strlen($content);
            $offsets[$cId] = strlen($pdf);
            $pdf .= "{$cId} 0 obj\n<< /Length {$len} >>\nstream\n{$content}\nendstream\nendobj\n";
        }

        // Font 1: Helvetica
        $offsets[$font1Id] = strlen($pdf);
        $pdf .= "{$font1Id} 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";

        // Font 2: Helvetica-Bold
        $offsets[$font2Id] = strlen($pdf);
        $pdf .= "{$font2Id} 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>\nendobj\n";

        // Cross-reference table
        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . ($totalObjs + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $totalObjs; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        // Trailer
        $pdf .= "trailer\n<< /Size " . ($totalObjs + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";

        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        return file_put_contents($outputPath, $pdf) !== false;
    }

    private function escape(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
