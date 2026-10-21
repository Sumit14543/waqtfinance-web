<?php
declare(strict_types=1);

if (!function_exists('loan_flow_base64url_encode')) {
function loan_flow_base64url_encode(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}
}

if (!function_exists('loan_flow_base64url_decode')) {
function loan_flow_base64url_decode(string $value): string
{
    $padding = strlen($value) % 4;
    if ($padding > 0) {
        $value .= str_repeat('=', 4 - $padding);
    }

    $decoded = base64_decode(strtr($value, '-_', '+/'), true);
    return $decoded === false ? '' : $decoded;
}
}

if (!function_exists('loan_flow_secret')) {
function loan_flow_secret(): string
{
    if (!function_exists('required_env')) {
        require_once __DIR__ . '/../config/db.php';
    }

    return required_env('APP_SECRET_KEY');
}
}

if (!function_exists('loan_flow_resume_cookie_name')) {
function loan_flow_resume_cookie_name(): string
{
    return 'waqt_payday_resume';
}
}

if (!function_exists('loan_flow_sign_resume_payload')) {
function loan_flow_sign_resume_payload(string $applicationId, int $expiresAt): string
{
    return hash_hmac('sha256', $applicationId . '|' . $expiresAt, loan_flow_secret());
}
}

if (!function_exists('loan_flow_make_resume_token')) {
function loan_flow_make_resume_token(string $applicationId, int $ttlSeconds = 2592000): string
{
    $expiresAt = time() + $ttlSeconds;
    $signature = loan_flow_sign_resume_payload($applicationId, $expiresAt);

    return loan_flow_base64url_encode($applicationId . '|' . $expiresAt . '|' . $signature);
}
}

if (!function_exists('loan_flow_read_resume_token')) {
function loan_flow_read_resume_token(?string $token): ?array
{
    if (!$token) {
        return null;
    }

    $parts = explode('|', loan_flow_base64url_decode($token));
    if (count($parts) !== 3) {
        return null;
    }

    [$applicationId, $expiresAt, $signature] = $parts;
    $expiresAt = (int) $expiresAt;

    if ($applicationId === '' || $expiresAt < time()) {
        return null;
    }

    $expected = loan_flow_sign_resume_payload($applicationId, $expiresAt);
    if (!hash_equals($expected, $signature)) {
        return null;
    }

    return ['application_id' => $applicationId, 'expires_at' => $expiresAt];
}
}

if (!function_exists('loan_flow_set_resume_cookie')) {
function loan_flow_set_resume_cookie(string $applicationId): void
{
    $ttl = 2592000;
    $token = loan_flow_make_resume_token($applicationId, $ttl);

    setcookie(loan_flow_resume_cookie_name(), $token, [
        'expires' => time() + $ttl,
        'path' => '/',
        'secure' => function_exists('is_https_request') ? is_https_request() : false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    $_COOKIE[loan_flow_resume_cookie_name()] = $token;
}
}

if (!function_exists('loan_flow_clear_resume_cookie')) {
function loan_flow_clear_resume_cookie(): void
{
    setcookie(loan_flow_resume_cookie_name(), '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => function_exists('is_https_request') ? is_https_request() : false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    unset($_COOKIE[loan_flow_resume_cookie_name()]);
}
}

if (!function_exists('loan_flow_column_exists')) {
function loan_flow_column_exists(mysqli $conn, string $table, string $column): bool
{
    static $cache = [];
    $key = $table . '.' . $column;

    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS count_value
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
    ");
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    $cache[$key] = ((int) ($row['count_value'] ?? 0)) > 0;
    return $cache[$key];
}
}

if (!function_exists('loan_flow_table_exists')) {
function loan_flow_table_exists(mysqli $conn, string $table): bool
{
    static $cache = [];

    if (array_key_exists($table, $cache)) {
        return $cache[$table];
    }

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS count_value
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
    ");
    $stmt->bind_param('s', $table);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    $cache[$table] = ((int) ($row['count_value'] ?? 0)) > 0;
    return $cache[$table];
}
}

if (!function_exists('loan_flow_find_application')) {
function loan_flow_find_application(mysqli $conn, string $applicationId): ?array
{
    if ($applicationId === '') {
        return null;
    }

    $stmt = $conn->prepare('SELECT * FROM loan_applications WHERE application_id = ? LIMIT 1');
    $stmt->bind_param('s', $applicationId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    return is_array($row) ? $row : null;
}
}

if (!function_exists('loan_flow_current_application')) {
function loan_flow_current_application(mysqli $conn): ?array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        start_secure_session();
    }

    $applicationId = (string) ($_SESSION['app_id'] ?? '');
    $application = loan_flow_find_application($conn, $applicationId);

    if ($application) {
        return $application;
    }

    $token = $_COOKIE[loan_flow_resume_cookie_name()] ?? '';
    $payload = loan_flow_read_resume_token(is_string($token) ? $token : '');
    if (!$payload) {
        return null;
    }

    $application = loan_flow_find_application($conn, $payload['application_id']);
    if (!$application) {
        return null;
    }

    $_SESSION['app_id'] = $application['application_id'];
    $_SESSION['mobile_number'] = $application['mobile'] ?? '';
    if (!empty($application['email'])) {
        $_SESSION['user_email'] = $application['email'];
    }

    return $application;
}
}

if (!function_exists('loan_flow_update_application')) {
function loan_flow_update_application(mysqli $conn, string $applicationId, array $fields): void
{
    $updates = [];
    $params = [];
    $types = '';

    foreach ($fields as $column => $value) {
        if (!loan_flow_column_exists($conn, 'loan_applications', (string) $column)) {
            continue;
        }

        $updates[] = $column . ' = ?';
        $params[] = $value;
        $types .= is_int($value) ? 'i' : (is_float($value) ? 'd' : 's');
    }

    if (!$updates) {
        return;
    }

    $params[] = $applicationId;
    $types .= 's';

    $stmt = $conn->prepare('UPDATE loan_applications SET ' . implode(', ', $updates) . ' WHERE application_id = ?');
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
}
}

if (!function_exists('loan_flow_mark_step')) {
function loan_flow_mark_step(mysqli $conn, string $applicationId, string $nextStep): void
{
    loan_flow_update_application($conn, $applicationId, [
        'current_step' => $nextStep,
        'last_activity_at' => date('Y-m-d H:i:s'),
    ]);
}
}

if (!function_exists('loan_flow_is_mobile_verified')) {
function loan_flow_is_mobile_verified(mysqli $conn, array $application): bool
{
    $currentStep = strtolower((string) ($application['current_step'] ?? ''));

    if ($currentStep === 'mobile_otp') {
        return false;
    }

    $applicationId = (string) ($application['application_id'] ?? '');
    if (
        $applicationId !== ''
        && (string) ($_SESSION['app_id'] ?? '') === $applicationId
        && !empty($_SESSION['otp_verified'])
    ) {
        return true;
    }

    if (in_array($currentStep, [
        'loan_requirement',
        'basic_details',
        'pan_verification',
        'aadhaar_verification',
        'aadhaar_callback',
        'company_details',
        'bank_details',
        'references',
        'documents',
        'submitted',
    ], true)) {
        return true;
    }

    $mobile = (string) ($application['mobile'] ?? '');
    $email = (string) ($application['email'] ?? '');

    if ($mobile === '' && $email === '') {
        return false;
    }

    if ($applicationId !== '' && loan_flow_column_exists($conn, 'otp_verifications', 'application_id')) {
        $stmt = $conn->prepare("
            SELECT id
            FROM otp_verifications
            WHERE application_id = ?
              AND is_verified = 1
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->bind_param('s', $applicationId);
    } else {
        $stmt = $conn->prepare("
            SELECT id
            FROM otp_verifications
            WHERE is_verified = 1
              AND ((mobile IS NOT NULL AND mobile = ?) OR (email IS NOT NULL AND email = ?))
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->bind_param('ss', $mobile, $email);
    }
    $stmt->execute();

    return (bool) $stmt->get_result()->fetch_assoc();
}
}

if (!function_exists('loan_flow_reference_count')) {
function loan_flow_reference_count(mysqli $conn, string $applicationId): int
{
    if (!loan_flow_table_exists($conn, 'loan_application_references')) {
        return 0;
    }

    $stmt = $conn->prepare('SELECT COUNT(*) AS count_value FROM loan_application_references WHERE application_id = ?');
    $stmt->bind_param('s', $applicationId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    return (int) ($row['count_value'] ?? 0);
}
}

if (!function_exists('loan_flow_next_url')) {
function loan_flow_next_url(mysqli $conn, array $application): string
{
    $status = strtolower((string) ($application['status'] ?? 'draft'));
    if (in_array($status, ['submitted', 'review', 'approved', 'rejected', 'disbursed'], true)) {
        return 'application-status.php';
    }

    if (!loan_flow_is_mobile_verified($conn, $application)) {
        return 'mobile-otp.php';
    }

    if (empty($application['loan_amount']) || empty($application['loan_purpose'])) {
        return 'apply-for-loan.php';
    }

    if (empty($application['pincode']) || empty($application['city']) || empty($application['income_received_in'])) {
        return 'basic-details-form.php';
    }

    if (empty($application['pan_number']) || empty($application['full_name']) || empty($application['dob'])) {
        return 'pan-verification.php';
    }

    if (empty($application['aadhaar_verified'])) {
        return 'aadhar-varification.php';
    }

    if (empty($application['company_name']) || empty($application['office_email']) || empty($application['office_address'])) {
        return 'company-details.php';
    }

    if (empty($application['bank_name']) || empty($application['account_number']) || empty($application['ifsc_code'])) {
        return 'bank-details.php';
    }

    if (loan_flow_reference_count($conn, (string) $application['application_id']) < 2) {
        return 'reference-details.php';
    }

    if (empty($application['salary_slip_current']) || empty($application['selfie_image'])) {
        return 'salary-slip-upload.php';
    }

    return 'application-status.php';
}
}

if (!function_exists('loan_flow_redirect_to_resume_if_applicable')) {
function loan_flow_redirect_to_resume_if_applicable(mysqli $conn, string $currentPage): void
{
    $application = loan_flow_current_application($conn);
    if (!$application) {
        return;
    }

    $nextUrl = loan_flow_next_url($conn, $application);
    if (basename($nextUrl) !== basename($currentPage)) {
        header('Location: ' . $nextUrl);
        exit;
    }
}
}

if (!function_exists('loan_flow_require_application')) {
function loan_flow_require_application(mysqli $conn): array
{
    $application = loan_flow_current_application($conn);
    if (!$application) {
        header('Location: payday-apply.php');
        exit;
    }

    return $application;
}
}
