<?php
declare(strict_types=1);
$pageTitle = "Global Search Results";
$pageSubtitle = "Search matches across all tables and leads";
include 'header.php';

$query = trim($_GET['q'] ?? '');
$conn = db();
$results = [
    'payday' => [],
    'general' => [],
    'landing' => [],
    'cibil' => [],
    'partners' => []
];

if ($query !== '') {
    $searchTerm = "%" . $query . "%";

    // 1. Payday Loans
    $stmt1 = $conn->prepare("SELECT id, application_id, full_name, mobile, email, loan_amount, status, created_at FROM loan_applications WHERE full_name LIKE ? OR mobile LIKE ? OR email LIKE ? OR application_id LIKE ? ORDER BY id DESC LIMIT 50");
    $stmt1->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt1->execute();
    $results['payday'] = $stmt1->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt1->close();

    // 2. General Wizard Loans
    $stmt2 = $conn->prepare("SELECT id, application_ref, full_name, mobile, email, loan_type, loan_amount, created_at FROM apply_loan_applications WHERE full_name LIKE ? OR mobile LIKE ? OR email LIKE ? OR application_ref LIKE ? ORDER BY id DESC LIMIT 50");
    $stmt2->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt2->execute();
    $results['general'] = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt2->close();

    // 3. Landing Page Leads
    $stmt3 = $conn->prepare("SELECT id, full_name, mobile, loan_type, pincode, created_at FROM loan_leads WHERE full_name LIKE ? OR mobile LIKE ? ORDER BY id DESC LIMIT 50");
    $stmt3->bind_param("ss", $searchTerm, $searchTerm);
    $stmt3->execute();
    $results['landing'] = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt3->close();

    // 4. CIBIL Checks
    $stmt4 = $conn->prepare("SELECT id, full_name, mobile, email, pan, score, created_at FROM cibil_reports WHERE full_name LIKE ? OR mobile LIKE ? OR pan LIKE ? ORDER BY id DESC LIMIT 50");
    $stmt4->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $stmt4->execute();
    $results['cibil'] = $stmt4->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt4->close();

    // 5. Partners
    $stmt5 = $conn->prepare("SELECT id, full_name, email_or_mobile, status, created_at FROM partner_registrations WHERE full_name LIKE ? OR email_or_mobile LIKE ? ORDER BY id DESC LIMIT 50");
    $stmt5->bind_param("ss", $searchTerm, $searchTerm);
    $stmt5->execute();
    $results['partners'] = $stmt5->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt5->close();
}

$totalMatches = count($results['payday']) + count($results['general']) + count($results['landing']) + count($results['cibil']) + count($results['partners']);

// Helper to highlight matching keywords safely
function highlight_match(string $text, string $query): string {
    if ($query === '') {
        return htmlspecialchars($text);
    }
    $escapedText = htmlspecialchars($text);
    $escapedQuery = htmlspecialchars($query);
    return preg_replace('/(' . preg_quote($escapedQuery, '/') . ')/i', '<mark style="background: #fef08a; color: #854d0e; padding: 1px 4px; border-radius: 4px; font-weight: 700;">$1</mark>', $escapedText);
}
?>

<style>
    .btn-action-premium {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        font-size: 13px;
        border: 1px solid #e2e8f0;
    }
    .btn-action-premium:hover {
        transform: scale(1.05);
    }
    .btn-action-view {
        border-color: rgba(24, 82, 156, 0.2);
        color: var(--primary);
    }
    .btn-action-view:hover {
        background: var(--primary);
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(24, 82, 156, 0.25);
    }
</style>

<div class="content-wrapper" style="padding: 30px 24px;">
    <div style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
        <h2 style="font-size: 20px; font-weight: 700; color: var(--text-main); margin: 0;">
            Search Query: <span style="color: var(--primary);">"<?php echo htmlspecialchars((string) $query); ?>"</span>
            <span style="font-size: 14px; font-weight: 500; color: var(--text-muted); margin-left: 10px;">(<?php echo $totalMatches; ?> matches found)</span>
        </h2>
        <a href="dashboard.php" class="btn-primary-adm" style="text-decoration: none; padding: 8px 16px;">Back to Dashboard</a>
    </div>

    <?php if ($totalMatches === 0): ?>
        <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius); padding: 50px; text-align: center; box-shadow: var(--shadow);">
            <div style="font-size: 48px; color: var(--text-muted); margin-bottom: 16px;"><i class="fa-solid fa-folder-open"></i></div>
            <h3 style="font-size: 18px; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">No Results Found</h3>
            <p style="color: var(--text-muted); font-size: 14px; margin: 0;">We couldn't find any records matching your search query. Try checking for typos or searching a different term.</p>
        </div>
    <?php else: ?>

        <!-- 1. Payday Loan Matches -->
        <?php if (!empty($results['payday'])): ?>
            <div class="data-card" style="margin-bottom: 30px;">
                <div class="card-header-flex">
                    <h2 class="card-title-text"><i class="fa-solid fa-file-invoice-dollar" style="color: var(--primary); margin-right: 8px;"></i> Payday & Legacy Loans (<?php echo count($results['payday']); ?>)</h2>
                </div>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>App ID</th>
                                <th>Applicant</th>
                                <th>Contact</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results['payday'] as $row): ?>
                                <tr>
                                    <td><strong><?php echo highlight_match((string)($row['application_id'] ?? ''), $query); ?></strong></td>
                                    <td><?php echo highlight_match((string)($row['full_name'] ?? ''), $query); ?></td>
                                    <td>
                                        <?php echo highlight_match((string)($row['mobile'] ?? ''), $query); ?>
                                        <?php if (!empty($row['email'])): ?>
                                            <br><small style="color: var(--text-muted);"><?php echo highlight_match((string)$row['email'], $query); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>₹<?php echo number_format((float) ($row['loan_amount'] ?? 0)); ?></td>
                                    <td><span class="status-badge" style="background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; text-transform: capitalize;"><?php echo htmlspecialchars((string) ($row['status'] ?? '')); ?></span></td>
                                    <td><?php echo date('d M Y', strtotime((string) ($row['created_at'] ?? 'now'))); ?></td>
                                    <td style="text-align: right;">
                                        <a href="view-application.php?id=<?php echo (int) $row['id']; ?>&type=payday" class="btn-action-premium btn-action-view" title="View Application Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- 2. General Wizard Matches -->
        <?php if (!empty($results['general'])): ?>
            <div class="data-card" style="margin-bottom: 30px;">
                <div class="card-header-flex">
                    <h2 class="card-title-text"><i class="fa-solid fa-wand-magic-sparkles" style="color: #a855f7; margin-right: 8px;"></i> General Loans (<?php echo count($results['general']); ?>)</h2>
                </div>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Ref ID</th>
                                <th>Applicant</th>
                                <th>Contact</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results['general'] as $row): ?>
                                <tr>
                                    <td><strong><?php echo highlight_match((string)($row['application_ref'] ?? ''), $query); ?></strong></td>
                                    <td><?php echo highlight_match((string)($row['full_name'] ?? ''), $query); ?></td>
                                    <td>
                                        <?php echo highlight_match((string)($row['mobile'] ?? ''), $query); ?>
                                        <?php if (!empty($row['email'])): ?>
                                            <br><small style="color: var(--text-muted);"><?php echo highlight_match((string)$row['email'], $query); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><span style="font-weight: 600; color: #a855f7;"><?php echo htmlspecialchars((string) ($row['loan_type'] ?? '')); ?></span></td>
                                    <td>₹<?php echo number_format((float) ($row['loan_amount'] ?? 0)); ?></td>
                                    <td><?php echo date('d M Y', strtotime((string) ($row['created_at'] ?? 'now'))); ?></td>
                                    <td style="text-align: right;">
                                        <a href="view-application.php?id=<?php echo (int) $row['id']; ?>&type=wizard" class="btn-action-premium btn-action-view" title="View Application Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- 3. Landing Leads Matches -->
        <?php if (!empty($results['landing'])): ?>
            <div class="data-card" style="margin-bottom: 30px;">
                <div class="card-header-flex">
                    <h2 class="card-title-text"><i class="fa-solid fa-user-tag" style="color: #60a5fa; margin-right: 8px;"></i> Website Lead Captures (<?php echo count($results['landing']); ?>)</h2>
                </div>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Applicant</th>
                                <th>Mobile</th>
                                <th>Loan Type</th>
                                <th>Pincode</th>
                                <th>Date</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results['landing'] as $row): ?>
                                <tr>
                                    <td><strong><?php echo highlight_match((string)($row['full_name'] ?? ''), $query); ?></strong></td>
                                    <td><?php echo highlight_match((string)($row['mobile'] ?? ''), $query); ?></td>
                                    <td><?php echo htmlspecialchars((string) ($row['loan_type'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars((string) ($row['pincode'] ?? '')); ?></td>
                                    <td><?php echo date('d M Y', strtotime((string) ($row['created_at'] ?? 'now'))); ?></td>
                                    <td style="text-align: right;">
                                        <a href="leads.php?tab=landing&search=<?php echo urlencode((string)($row['mobile'] ?? '')); ?>" class="btn-action-premium btn-action-view" title="View in leads panel">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- 4. CIBIL Checks Matches -->
        <?php if (!empty($results['cibil'])): ?>
            <div class="data-card" style="margin-bottom: 30px;">
                <div class="card-header-flex">
                    <h2 class="card-title-text"><i class="fa-solid fa-gauge-high" style="color: var(--warning); margin-right: 8px;"></i> CIBIL Reports (<?php echo count($results['cibil']); ?>)</h2>
                </div>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Applicant</th>
                                <th>Contact</th>
                                <th>PAN Card</th>
                                <th>CIBIL Score</th>
                                <th>Date</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results['cibil'] as $row): ?>
                                <tr>
                                    <td><strong><?php echo highlight_match((string)($row['full_name'] ?? ''), $query); ?></strong></td>
                                    <td>
                                        <?php echo highlight_match((string)($row['mobile'] ?? ''), $query); ?>
                                        <?php if (!empty($row['email'])): ?>
                                            <br><small style="color: var(--text-muted);"><?php echo highlight_match((string)$row['email'], $query); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><span style="font-family: monospace; font-size: 13.5px; font-weight: 700; color: var(--text-main);"><?php echo highlight_match((string)($row['pan'] ?? ''), $query); ?></span></td>
                                    <td>
                                        <span style="font-weight: 700; color: <?php echo (int)($row['score'] ?? 0) >= 750 ? '#16a34a' : ((int)($row['score'] ?? 0) >= 650 ? '#ca8a04' : '#dc2626'); ?>;">
                                            <?php echo htmlspecialchars((string)($row['score'] ?? '')); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime((string) ($row['created_at'] ?? 'now'))); ?></td>
                                    <td style="text-align: right;">
                                        <a href="leads.php?tab=cibil&search=<?php echo urlencode((string)($row['pan'] ?? '')); ?>" class="btn-action-premium btn-action-view" title="View in CIBIL leads panel">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- 5. Partner Matches -->
        <?php if (!empty($results['partners'])): ?>
            <div class="data-card" style="margin-bottom: 30px;">
                <div class="card-header-flex">
                    <h2 class="card-title-text"><i class="fa-solid fa-handshake" style="color: #10b981; margin-right: 8px;"></i> DSA Partners (<?php echo count($results['partners']); ?>)</h2>
                </div>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Partner Name</th>
                                <th>Contact / Mobile</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results['partners'] as $row): ?>
                                <tr>
                                    <td><strong><?php echo highlight_match((string)($row['full_name'] ?? ''), $query); ?></strong></td>
                                    <td><?php echo highlight_match((string)($row['email_or_mobile'] ?? ''), $query); ?></td>
                                    <td><span class="status-badge" style="background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; text-transform: capitalize;"><?php echo htmlspecialchars((string) ($row['status'] ?: 'Pending')); ?></span></td>
                                    <td><?php echo date('d M Y', strtotime((string) ($row['created_at'] ?? 'now'))); ?></td>
                                    <td style="text-align: right;">
                                        <a href="leads.php?tab=partners&search=<?php echo urlencode((string)($row['full_name'] ?? '')); ?>" class="btn-action-premium btn-action-view" title="View in DSA partners panel">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
