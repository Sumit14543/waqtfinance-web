<?php
declare(strict_types=1);

$pageTitle = 'Dashboard Overview';
$pageSubtitle = 'Real-time metrics and system operations analytics';

include 'header.php';

$conn = db();

// Helper to safely count table rows
function get_table_count(mysqli $conn, string $table, string $condition = ''): int {
    $sql = "SELECT COUNT(*) FROM " . $table;
    if ($condition !== '') {
        $sql .= " WHERE " . $condition;
    }
    $res = $conn->query($sql);
    if ($res) {
        $row = $res->fetch_row();
        return (int) ($row[0] ?? 0);
    }
    return 0;
}

// 🧮 Gather Metrics
$totalApplications = get_table_count($conn, 'loan_applications'); // Payday
$pendingApplications = get_table_count($conn, 'loan_applications', "status = 'submitted'");
$reviewApplications = get_table_count($conn, 'loan_applications', "status = 'review'");
$approvedApplications = get_table_count($conn, 'loan_applications', "status = 'approved'");

$totalMasterLoans = get_table_count($conn, 'loan_applied_master');
$totalEdiLoans = get_table_count($conn, 'edi_loan_applications');
$totalLandingLeads = get_table_count($conn, 'loan_leads');
$totalCibilChecks = get_table_count($conn, 'cibil_reports');
$totalMessages = get_table_count($conn, 'contact_messages');
$totalBlogs = get_table_count($conn, 'blogs');
$totalPartners = get_table_count($conn, 'partner_registrations');
$totalGeneralLoans = get_table_count($conn, 'apply_loan_applications');

// Payday status counts for charts
$statusCounts = [
    'draft' => get_table_count($conn, 'loan_applications', "status = 'draft'"),
    'submitted' => get_table_count($conn, 'loan_applications', "status = 'submitted'"),
    'review' => get_table_count($conn, 'loan_applications', "status = 'review'"),
    'approved' => get_table_count($conn, 'loan_applications', "status = 'approved'"),
    'rejected' => get_table_count($conn, 'loan_applications', "status = 'rejected'"),
    'disbursed' => get_table_count($conn, 'loan_applications', "status = 'disbursed'")
];

// Recent payday applications
$recentAppsQuery = "SELECT application_id, full_name, mobile, loan_amount, status, created_at FROM loan_applications ORDER BY id DESC LIMIT 5";
$recentApps = $conn->query($recentAppsQuery)->fetch_all(MYSQLI_ASSOC);

// Recent general wizard applications
$recentGeneralQuery = "SELECT application_ref, full_name, mobile, loan_amount, loan_type, created_at FROM apply_loan_applications ORDER BY id DESC LIMIT 5";
$recentGeneral = [];
$genRes = $conn->query($recentGeneralQuery);
if ($genRes) {
    $recentGeneral = $genRes->fetch_all(MYSQLI_ASSOC);
}

// Recent partner registrations
$recentPartnersQuery = "SELECT full_name, email_or_mobile, status, created_at FROM partner_registrations ORDER BY id DESC LIMIT 5";
$recentPartners = [];
$partnersRes = $conn->query($recentPartnersQuery);
if ($partnersRes) {
    $recentPartners = $partnersRes->fetch_all(MYSQLI_ASSOC);
}

// Recent messages
$recentMsgsQuery = "SELECT name, phone, subject, created_at FROM contact_messages ORDER BY id DESC LIMIT 5";
$recentMsgs = $conn->query($recentMsgsQuery)->fetch_all(MYSQLI_ASSOC);

// Calculate Today's and Yesterday's Leads across all platforms
$todayLeads = 0;
$yesterdayLeads = 0;
$leadTables = ['loan_applications', 'apply_loan_applications', 'loan_applied_master', 'edi_loan_applications', 'loan_leads'];

foreach ($leadTables as $tbl) {
    // Today
    $tRes = $conn->query("SELECT COUNT(*) FROM $tbl WHERE DATE(created_at) = CURDATE()");
    if ($tRes) {
        $row = $tRes->fetch_row();
        $todayLeads += (int) ($row[0] ?? 0);
    }
    // Yesterday
    $yRes = $conn->query("SELECT COUNT(*) FROM $tbl WHERE DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)");
    if ($yRes) {
        $row = $yRes->fetch_row();
        $yesterdayLeads += (int) ($row[0] ?? 0);
    }
}

// Calculate Trend
$leadTrendHtml = '';
if ($yesterdayLeads > 0) {
    $diff = $todayLeads - $yesterdayLeads;
    $pct = round(($diff / $yesterdayLeads) * 100, 1);
    if ($pct > 0) {
        $leadTrendHtml = '<span style="color: #22c55e; font-weight: 600; font-size: 13px; margin-left: 8px;"><i class="fa-solid fa-arrow-trend-up"></i> +' . $pct . '%</span>';
    } elseif ($pct < 0) {
        $leadTrendHtml = '<span style="color: #ef4444; font-weight: 600; font-size: 13px; margin-left: 8px;"><i class="fa-solid fa-arrow-trend-down"></i> ' . $pct . '%</span>';
    } else {
        $leadTrendHtml = '<span style="color: #64748b; font-weight: 600; font-size: 13px; margin-left: 8px;">0% change</span>';
    }
} else {
    if ($todayLeads > 0) {
        $leadTrendHtml = '<span style="color: #22c55e; font-weight: 600; font-size: 13px; margin-left: 8px;"><i class="fa-solid fa-arrow-trend-up"></i> +100%</span>';
    } else {
        $leadTrendHtml = '<span style="color: #64748b; font-weight: 600; font-size: 13px; margin-left: 8px;">No change</span>';
    }
}
?>

<!-- Main Leads Intake Overview -->
<div class="stat-grid" style="grid-template-columns: repeat(2, 1fr); margin-bottom: 25px;" data-aos="fade-up">
    <div class="stat-card" style="border-left: 4px solid #22c55e;">
        <div>
            <div class="stat-label">Today's Total Leads</div>
            <div class="stat-val"><?php echo $todayLeads; ?><?php echo $leadTrendHtml; ?></div>
            <small class="text-muted">Intake on <?php echo date('d M Y'); ?></small>
        </div>
        <div class="stat-icon-wrapper" style="background: rgba(34, 197, 94, 0.1); color: #22c55e; width: 48px; height: 48px; font-size: 20px;">
            <i class="fa-solid fa-calendar-day"></i>
        </div>
    </div>
    
    <div class="stat-card" style="border-left: 4px solid #6366f1;">
        <div>
            <div class="stat-label">Yesterday's Total Leads</div>
            <div class="stat-val"><?php echo $yesterdayLeads; ?></div>
            <small class="text-muted">Intake on <?php echo date('d M Y', strtotime('yesterday')); ?></small>
        </div>
        <div class="stat-icon-wrapper" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; width: 48px; height: 48px; font-size: 20px;">
            <i class="fa-solid fa-calendar-minus"></i>
        </div>
    </div>
</div>

<!-- Statistics Overview Row 1 -->
<div class="stat-grid" data-aos="fade-up">
    <div class="stat-card">
        <div>
            <div class="stat-label">Payday Loans</div>
            <div class="stat-val"><?php echo $totalApplications; ?></div>
            <small class="text-muted"><?php echo $pendingApplications; ?> pending review</small>
        </div>
        <div class="stat-icon-wrapper" style="background: rgba(255, 184, 0, 0.1); color: var(--primary);">
            <i class="fa-solid fa-file-invoice-dollar"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div>
            <div class="stat-label">General Loans</div>
            <div class="stat-val"><?php echo $totalGeneralLoans; ?></div>
            <small class="text-muted">General wizard entries</small>
        </div>
        <div class="stat-icon-wrapper" style="background: rgba(168, 85, 247, 0.1); color: #a855f7;">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div>
            <div class="stat-label">Quick Loan Inquiries</div>
            <div class="stat-val"><?php echo $totalMasterLoans; ?></div>
            <small class="text-muted">Personal, Business, LAP, Vehicle</small>
        </div>
        <div class="stat-icon-wrapper" style="background: rgba(34, 197, 94, 0.1); color: var(--success);">
            <i class="fa-solid fa-layer-group"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div>
            <div class="stat-label">Shopkeeper EDI Loans</div>
            <div class="stat-val"><?php echo $totalEdiLoans; ?></div>
            <small class="text-muted">Small business & shopkeepers</small>
        </div>
        <div class="stat-icon-wrapper" style="background: rgba(6, 182, 212, 0.1); color: var(--info);">
            <i class="fa-solid fa-shop"></i>
        </div>
    </div>
</div>

<!-- Statistics Overview Row 2 -->
<div class="stat-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); margin-top: 25px;" data-aos="fade-up" data-aos-delay="50">
    <div class="stat-card">
        <div>
            <div class="stat-label">CIBIL Inquiries</div>
            <div class="stat-val"><?php echo $totalCibilChecks; ?></div>
            <small class="text-muted">Credit score inspections</small>
        </div>
        <div class="stat-icon-wrapper" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
            <i class="fa-solid fa-gauge-high"></i>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Website Lead Captures</div>
            <div class="stat-val"><?php echo $totalLandingLeads; ?></div>
            <small class="text-muted">Quick landing pages leads</small>
        </div>
        <div class="stat-icon-wrapper" style="background: rgba(59, 130, 246, 0.1); color: #60a5fa;">
            <i class="fa-solid fa-user-tag"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div>
            <div class="stat-label">DSA Partners</div>
            <div class="stat-val"><?php echo $totalPartners; ?></div>
            <small class="text-muted">Registered DSA agents</small>
        </div>
        <div class="stat-icon-wrapper" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
            <i class="fa-solid fa-handshake"></i>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Contact Inquiries</div>
            <div class="stat-val"><?php echo $totalMessages; ?></div>
            <small class="text-muted">Inbox inquiries</small>
        </div>
        <div class="stat-icon-wrapper" style="background: rgba(239, 68, 68, 0.1); color: var(--danger);">
            <i class="fa-solid fa-envelope-open-text"></i>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap: 30px; margin-top: 30px;" data-aos="fade-up" data-aos-delay="100">
    <div class="data-card">
        <div class="card-header-flex">
            <h2 class="card-title-text"><i class="fa-solid fa-chart-pie" style="color: var(--primary); margin-right: 8px;"></i> Payday Loan Status</h2>
        </div>
        <div style="height: 250px; position: relative; display: flex; align-items: center; justify-content: center; padding: 10px;">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
    
    <div class="data-card">
        <div class="card-header-flex">
            <h2 class="card-title-text"><i class="fa-solid fa-chart-bar" style="color: var(--success); margin-right: 8px;"></i> volume by category</h2>
        </div>
        <div style="height: 250px; position: relative; display: flex; align-items: center; justify-content: center; padding: 10px;">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>

    <!-- Geographic Distribution Widget -->
    <div class="data-card">
        <div class="card-header-flex">
            <h2 class="card-title-text"><i class="fa-solid fa-map-location-dot" style="color: #6366f1; margin-right: 8px;"></i> Geographic Distribution</h2>
        </div>
        <div style="padding: 20px; display: flex; flex-direction: column; gap: 14px;">
            <?php
            $geoQuery = "
                SELECT COALESCE(NULLIF(TRIM(city), ''), 'Unknown') as city_name, COUNT(*) AS count 
                FROM (
                    SELECT city FROM loan_applications WHERE city IS NOT NULL
                    UNION ALL
                    SELECT city FROM apply_loan_applications WHERE city IS NOT NULL
                ) as combined 
                GROUP BY city_name 
                ORDER BY count DESC 
                LIMIT 5
            ";
            $geoRes = $conn->query($geoQuery);
            $geoData = $geoRes ? $geoRes->fetch_all(MYSQLI_ASSOC) : [];
            $maxCount = !empty($geoData) ? max(array_column($geoData, 'count')) : 1;
            
            if (empty($geoData)):
            ?>
                <div style="text-align: center; color: var(--text-muted); padding: 40px 0;">No geographic data available.</div>
            <?php
            else:
                foreach ($geoData as $geo):
                    $percentage = round(($geo['count'] / $maxCount) * 100);
            ?>
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 13.5px; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">
                        <span><?php echo htmlspecialchars(ucwords(strtolower($geo['city_name']))); ?></span>
                        <span style="color: var(--primary);"><?php echo $geo['count']; ?> Leads</span>
                    </div>
                    <div style="width: 100%; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                        <div style="width: <?php echo $percentage; ?>%; height: 100%; background: linear-gradient(90deg, #6366f1, #4f46e5); border-radius: 4px;"></div>
                    </div>
                </div>
            <?php
                endforeach;
            endif;
            ?>
        </div>
    </div>
</div>

<!-- Activity Feeds Row 1: Recent Payday Applications & General Wizard Applications -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 30px; margin-top: 30px;" data-aos="fade-up" data-aos-delay="150">
    
    <!-- Recent Payday Applications -->
    <div class="data-card">
        <div class="card-header-flex">
            <h2 class="card-title-text"><i class="fa-solid fa-clock-rotate-left" style="color: var(--primary); margin-right: 8px;"></i> Recent Payday Loan Applications</h2>
            <a href="applications.php?tab=payday" class="btn-primary-adm" style="padding: 6px 12px; font-size: 11px; box-shadow: none; text-decoration: none;">View All</a>
        </div>
        
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>App ID</th>
                        <th>Applicant</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentApps)): ?>
                        <tr>
                            <td colspan="4" class="text-center" style="color: var(--text-muted); padding: 30px 0;">No applications found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentApps as $app): 
                            $appId = htmlspecialchars((string)($app['application_id'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $fullName = htmlspecialchars((string)($app['full_name'] ?? 'N/A'), ENT_QUOTES, 'UTF-8');
                            $mobile = htmlspecialchars((string)($app['mobile'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $appAmount = is_numeric($app['loan_amount']) ? number_format((float)$app['loan_amount'], 2) : '0.00';
                            $status = htmlspecialchars((string)($app['status'] ?? 'draft'), ENT_QUOTES, 'UTF-8');
                        ?>
                            <tr>
                                <td><span style="font-weight: 700; color: var(--primary);"><?php echo $appId; ?></span></td>
                                <td>
                                    <div><?php echo $fullName; ?></div>
                                    <small class="text-muted"><?php echo $mobile; ?></small>
                                </td>
                                <td>₹<?php echo $appAmount; ?></td>
                                <td><span class="badge-status badge-<?php echo $status; ?>"><?php echo $status; ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Recent General Wizard Applications -->
    <div class="data-card">
        <div class="card-header-flex">
            <h2 class="card-title-text"><i class="fa-solid fa-wand-magic-sparkles" style="color: #a855f7; margin-right: 8px;"></i> Recent General Loan Applications</h2>
            <a href="applications.php?tab=wizard" class="btn-primary-adm" style="padding: 6px 12px; font-size: 11px; box-shadow: none; text-decoration: none;">View All</a>
        </div>
        
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>App ID</th>
                        <th>Applicant</th>
                        <th>Amount</th>
                        <th>Loan Category</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentGeneral)): ?>
                        <tr>
                            <td colspan="4" class="text-center" style="color: var(--text-muted); padding: 30px 0;">No general applications found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentGeneral as $app): 
                            $appRef = htmlspecialchars((string)($app['application_ref'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $fullName = htmlspecialchars((string)($app['full_name'] ?? 'N/A'), ENT_QUOTES, 'UTF-8');
                            $mobile = htmlspecialchars((string)($app['mobile'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $appAmount = is_numeric($app['loan_amount']) ? number_format((float)$app['loan_amount'], 2) : htmlspecialchars((string)$app['loan_amount'], ENT_QUOTES, 'UTF-8');
                            $loanType = htmlspecialchars((string)($app['loan_type'] ?? 'N/A'), ENT_QUOTES, 'UTF-8');
                        ?>
                            <tr>
                                <td><span style="font-weight: 700; color: #a855f7;"><?php echo $appRef; ?></span></td>
                                <td>
                                    <div><?php echo $fullName; ?></div>
                                    <small class="text-muted"><?php echo $mobile; ?></small>
                                </td>
                                <td>₹<?php echo $appAmount; ?></td>
                                <td><span style="background: rgba(168, 85, 247, 0.08); padding: 3px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; color: #a855f7;"><?php echo $loanType; ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Activity Feeds Row 2: Recent DSA Registrations & Support Inquiries -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 30px; margin-top: 30px; margin-bottom: 30px;" data-aos="fade-up" data-aos-delay="200">
    
    <!-- Recent DSA Registrations -->
    <div class="data-card">
        <div class="card-header-flex">
            <h2 class="card-title-text"><i class="fa-solid fa-handshake" style="color: #10b981; margin-right: 8px;"></i> Newly Registered DSA Partners</h2>
            <a href="leads.php?tab=dsa" class="btn-primary-adm" style="padding: 6px 12px; font-size: 11px; box-shadow: none; text-decoration: none;">View All</a>
        </div>
        
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Partner Name</th>
                        <th>Mobile / Email</th>
                        <th>Status</th>
                        <th>Joined Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentPartners)): ?>
                        <tr>
                            <td colspan="4" class="text-center" style="color: var(--text-muted); padding: 30px 0;">No registered partners found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentPartners as $partner): 
                            $fullName = htmlspecialchars((string)($partner['full_name'] ?? 'N/A'), ENT_QUOTES, 'UTF-8');
                            $contact = htmlspecialchars((string)($partner['email_or_mobile'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $status = htmlspecialchars((string)($partner['status'] ?? 'pending'), ENT_QUOTES, 'UTF-8');
                            $dateStr = date('d M, h:i A', strtotime($partner['created_at']));
                        ?>
                            <tr>
                                <td><span style="font-weight: 700; color: #10b981;"><?php echo $fullName; ?></span></td>
                                <td><?php echo $contact; ?></td>
                                <td><span class="badge-status badge-<?php echo ($status === 'approved') ? 'approved' : (($status === 'rejected') ? 'rejected' : 'review'); ?>"><?php echo $status; ?></span></td>
                                <td><?php echo $dateStr; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Support Inquiries -->
    <div class="data-card">
        <div class="card-header-flex">
            <h2 class="card-title-text"><i class="fa-solid fa-comments" style="color: var(--danger); margin-right: 8px;"></i> Recent Support Messages</h2>
            <a href="leads.php?tab=messages" class="btn-primary-adm" style="padding: 6px 12px; font-size: 11px; box-shadow: none; text-decoration: none;">View All</a>
        </div>
        
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Contact</th>
                        <th>Subject</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentMsgs)): ?>
                        <tr>
                            <td colspan="3" class="text-center" style="color: var(--text-muted); padding: 30px 0;">No messages received.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentMsgs as $msg): 
                            $name = htmlspecialchars((string)($msg['name'] ?? 'N/A'), ENT_QUOTES, 'UTF-8');
                            $phone = htmlspecialchars((string)($msg['phone'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $subject = htmlspecialchars((string)($msg['subject'] ?? 'N/A'), ENT_QUOTES, 'UTF-8');
                            $dateStr = date('d M, h:i A', strtotime($msg['created_at']));
                        ?>
                            <tr>
                                <td>
                                    <div><?php echo $name; ?></div>
                                    <small class="text-muted"><?php echo $phone; ?></small>
                                </td>
                                <td><span style="color: var(--text-main);"><?php echo $subject; ?></span></td>
                                <td><?php echo $dateStr; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    AOS.init({ duration: 800, once: true });

    // 📊 Status distribution Doughnut Chart
    const statusData = {
        labels: ['Draft', 'Submitted', 'Review', 'Approved', 'Rejected', 'Disbursed'],
        datasets: [{
            data: [
                <?php echo $statusCounts['draft']; ?>,
                <?php echo $statusCounts['submitted']; ?>,
                <?php echo $statusCounts['review']; ?>,
                <?php echo $statusCounts['approved']; ?>,
                <?php echo $statusCounts['rejected']; ?>,
                <?php echo $statusCounts['disbursed']; ?>
            ],
            backgroundColor: [
                '#94a3b8', // Draft - Slate
                '#3b82f6', // Submitted - Blue
                '#ca8a04', // Review - Yellow
                '#16a34a', // Approved - Green
                '#dc2626', // Rejected - Red
                '#0d9488'  // Disbursed - Teal
            ],
            borderWidth: 0
        }]
    };

    const statusConfig = {
        type: 'doughnut',
        data: statusData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        font: { family: 'Plus Jakarta Sans', size: 12, weight: '600' },
                        color: '#64748b'
                    }
                }
            },
            cutout: '65%'
        }
    };

    new Chart(document.getElementById('statusChart'), statusConfig);

    // 📊 Category volume Bar Chart
    const categoryData = {
        labels: ['Payday', 'General', 'Quick Inq', 'EDI Loans', 'DSA Partners'],
        datasets: [{
            label: 'Total Records',
            data: [
                <?php echo $totalApplications; ?>,
                <?php echo $totalGeneralLoans; ?>,
                <?php echo $totalMasterLoans; ?>,
                <?php echo $totalEdiLoans; ?>,
                <?php echo $totalPartners; ?>
            ],
            backgroundColor: 'rgba(24, 82, 156, 0.85)',
            hoverBackgroundColor: '#18529c',
            borderRadius: 6,
            borderWidth: 0
        }]
    };

    const categoryConfig = {
        type: 'bar',
        data: categoryData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        precision: 0,
                        font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' },
                        color: '#64748b'
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' },
                        color: '#64748b'
                    }
                }
            }
        }
    };

    new Chart(document.getElementById('categoryChart'), categoryConfig);
</script>

<?php include 'footer.php'; ?>
