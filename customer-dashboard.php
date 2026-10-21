<?php
require_once 'config/db.php';
start_secure_session();

if (!($_SESSION['is_authenticated'] ?? false)) {
    header("Location: customer-login.php");
    exit;
}

$mobile = $_SESSION['mobile_number'] ?? $_SESSION['user_id'] ?? '';
$conn = db();

// Fetch Applications by mobile number
$appStmt = $conn->prepare('SELECT * FROM loan_applications WHERE mobile = ? ORDER BY updated_at DESC');
$appStmt->bind_param('s', $mobile);
$appStmt->execute();
$applications = $appStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Use latest application for user details fallback
$user = !empty($applications) ? $applications[0] : ['full_name' => 'Customer', 'mobile' => $mobile];

require_once 'includes/auth_middleware.php';

$resumeUrl = null;
if (!empty($applications)) {
    $latestApp = $applications[0];
    if ($latestApp['status'] === 'draft') {
        $resumeUrl = get_current_step_url($latestApp['application_id']);
    }
}

$page_title = "Customer Dashboard | Waqt Finance";
include 'includes/header.php';
include 'includes/main-header.php';
?>

<style>
:root {
    --primary: #18529c;
    --secondary: #f7931e;
    --success: #22c55e;
    --warning: #f59e0b;
    --danger: #ef4444;
    --bg-light: #f8fafc;
    --text-dark: #1e293b;
    --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
}

body {
    background-color: var(--bg-light);
    color: var(--text-dark);
}

.dashboard-wrapper {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.welcome-section {
    margin-bottom: 40px;
    background: linear-gradient(135deg, var(--primary), #2d73cc);
    padding: 40px;
    border-radius: 20px;
    color: white;
    box-shadow: var(--card-shadow);
}

.welcome-section h1 {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 10px;
}

.stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: var(--card-shadow);
    display: flex;
    align-items: center;
    gap: 20px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    background: rgba(24, 82, 156, 0.1);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.app-table-container {
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: var(--card-shadow);
}

.app-table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.status-badge {
    padding: 6px 14px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-draft { background: #e2e8f0; color: #475569; }
.status-submitted { background: #dcfce7; color: #166534; }
.status-approved { background: #dcfce7; color: #16a34a; font-weight: bold; }
.status-rejected { background: #fee2e2; color: #991b1b; }

.btn-apply-new {
    background: var(--secondary);
    color: white;
    padding: 12px 25px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 700;
    transition: transform 0.2s;
}

.btn-apply-new:hover {
    transform: translateY(-2px);
    color: white;
}

@media (max-width: 768px) {
    .welcome-section { padding: 30px 20px; text-align: center; }
    .welcome-section h1 { font-size: 1.8rem; }
}
</style>

<div class="dashboard-wrapper">
    <div class="welcome-section" data-aos="fade-down">
        <h1>Hello, <?php echo htmlspecialchars($user['full_name'] ?? 'User'); ?>!</h1>
        <p>Welcome to your personal loan management portal. Manage your applications and credit health in one place.</p>
        
        <?php if ($resumeUrl): ?>
        <div class="mt-4">
            <a href="<?php echo $resumeUrl; ?>" class="btn btn-light btn-lg rounded-pill px-4 fw-bold text-primary">
                Resume Your Application <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <div class="stat-grid">
        <div class="stat-card" data-aos="zoom-in" data-aos-delay="100">
            <div class="stat-icon"><i class="fas fa-file-contract"></i></div>
            <div>
                <div class="text-muted small">Total Applications</div>
                <div class="h4 mb-0 font-weight-bold"><?php echo count($applications); ?></div>
            </div>
        </div>
        <div class="stat-card" data-aos="zoom-in" data-aos-delay="200">
            <div class="stat-icon" style="color:var(--success); background:rgba(34,197,94,0.1);"><i class="fas fa-wallet"></i></div>
            <div>
                <div class="text-muted small">Available Credit</div>
                <div class="h4 mb-0 font-weight-bold">₹ 5,00,000</div>
            </div>
        </div>
        <div class="stat-card" data-aos="zoom-in" data-aos-delay="300">
            <div class="stat-icon" style="color:var(--secondary); background:rgba(247,147,30,0.1);"><i class="fas fa-chart-line"></i></div>
            <div>
                <div class="text-muted small">Credit Score</div>
                <div class="h4 mb-0 font-weight-bold">750+</div>
            </div>
        </div>
    </div>

    <div class="app-table-container" data-aos="fade-up">
        <div class="app-table-header">
            <h3 class="mb-0">Recent Applications</h3>
            <a href="payday-apply.php" class="btn-apply-new">Apply for New Loan <i class="fas fa-plus ms-2"></i></a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Application ID</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applications)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <img src="assets/images/no-data.svg" alt="No Applications" style="width:150px; opacity:0.5; margin-bottom:15px;">
                            <p class="text-muted">You haven't applied for any loans yet.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars((string) $app['application_id'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                            <td><?php echo htmlspecialchars(ucfirst($app['loan_type'] ?? 'payday'), ENT_QUOTES, 'UTF-8'); ?> Loan</td>
                            <td>₹ <?php echo number_format((float)$app['loan_amount'], 2); ?></td>
                            <td><?php echo date('d M Y', strtotime($app['created_at'])); ?></td>
                            <td><span class="status-badge status-<?php echo htmlspecialchars((string) $app['status'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(ucfirst((string) $app['status']), ENT_QUOTES, 'UTF-8'); ?></span></td>
                            <td>
                                <a href="application-status?id=<?php echo rawurlencode((string) $app['application_id']); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">View Details</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true });
</script>

<?php include 'includes/footer.php'; ?>
