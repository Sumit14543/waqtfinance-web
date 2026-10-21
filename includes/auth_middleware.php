<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

if (!function_exists('require_auth')) {
    function require_auth(): void
    {
        start_secure_session();
        if (!($_SESSION['is_authenticated'] ?? false)) {
            header("Location: customer-login.php");
            exit;
        }
    }
}

if (!function_exists('get_current_step_url')) {
    function get_current_step_url(string $appId): string
    {
        $conn = db();
        $stmt = $conn->prepare('SELECT * FROM loan_applications WHERE application_id = ? LIMIT 1');
        $stmt->bind_param('s', $appId);
        $stmt->execute();
        $app = $stmt->get_result()->fetch_assoc();

        if (!$app) return 'payday-apply.php';

        if (empty($app['loan_amount'])) return 'apply-for-loan.php';
        if (empty($app['pincode'])) return 'basic-details-form.php';
        if (empty($app['pan_number'])) return 'pan-verification.php';
        if (empty($app['aadhaar_number'])) return 'aadhar-varification.php';
        if (empty($app['company_name'])) return 'company-details.php';
        if (empty($app['bank_name'])) return 'bank-details.php';
        if (empty($app['salary_slip_current'])) return 'salary-slip-upload.php';

        return 'application-status.php';
    }
}
