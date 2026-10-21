<?php
declare(strict_types=1);

require_once '../config.php';
require_once __DIR__ . '/../../includes/loan_flow.php';

header('Content-Type: application/json');
require_post();
require_valid_csrf();

function ref_response(string $status, string $message, int $code = 200): void
{
    http_response_code($code);
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

function ensure_reference_table(mysqli $conn): void
{
    $conn->query("
        CREATE TABLE IF NOT EXISTS loan_application_references (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            application_id VARCHAR(30) NOT NULL,
            reference_type ENUM('primary', 'secondary') NOT NULL,
            full_name VARCHAR(255) NOT NULL,
            mobile VARCHAR(15) NOT NULL,
            relation ENUM(
                'Father',
                'Mother',
                'Brother',
                'Sister',
                'Spouse',
                'Son',
                'Daughter',
                'Friend',
                'Relative',
                'Cousin',
                'Colleague',
                'Guarantor'
            ) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_reference_type (application_id, reference_type),
            KEY idx_reference_mobile (mobile)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $column = $conn->query("
        SELECT CHARACTER_MAXIMUM_LENGTH AS max_length
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'loan_application_references'
          AND COLUMN_NAME = 'application_id'
        LIMIT 1
    ")->fetch_assoc();

    if ((int) ($column['max_length'] ?? 0) < 30) {
        $constraint = $conn->query("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'loan_application_references'
              AND COLUMN_NAME = 'application_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ")->fetch_assoc();

        $constraintName = (string) ($constraint['CONSTRAINT_NAME'] ?? '');
        if ($constraintName !== '') {
            $conn->query('ALTER TABLE loan_application_references DROP FOREIGN KEY `' . $conn->real_escape_string($constraintName) . '`');
        }

        $conn->query("ALTER TABLE loan_application_references MODIFY application_id VARCHAR(30) NOT NULL");

        if ($constraintName !== '' && loan_flow_column_exists($conn, 'loan_applications', 'application_id')) {
            $conn->query("
                ALTER TABLE loan_application_references
                ADD CONSTRAINT `fk_reference_application`
                FOREIGN KEY (`application_id`) REFERENCES `loan_applications` (`application_id`) ON DELETE CASCADE
            ");
        }
    }
}

function read_reference(string $prefix, array $allowedRelations): array
{
    $name = clean_name((string) ($_POST[$prefix . '_name'] ?? ''));
    $mobile = preg_replace('/\D/', '', (string) ($_POST[$prefix . '_mobile'] ?? '')) ?? '';
    $relation = trim((string) ($_POST[$prefix . '_relation'] ?? ''));

    if ($name === '' || strlen($name) < 3) {
        ref_response('error', ucfirst($prefix) . ' reference name is invalid.', 400);
    }

    if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
        ref_response('error', ucfirst($prefix) . ' reference mobile number is invalid.', 400);
    }

    if (!in_array($relation, $allowedRelations, true)) {
        ref_response('error', ucfirst($prefix) . ' reference relation is invalid.', 400);
    }

    return [
        'name' => $name,
        'mobile' => $mobile,
        'relation' => $relation,
    ];
}

try {
    $conn = db();
    ensure_reference_table($conn);

    $application = loan_flow_current_application($conn);
    if (!$application) {
        ref_response('error', 'Session expired. Restart application.', 401);
    }

    $appId = (string) $application['application_id'];
    $applicantMobile = preg_replace('/\D/', '', (string) ($application['mobile'] ?? '')) ?? '';

    $allowedRelations = [
        'Father',
        'Mother',
        'Brother',
        'Sister',
        'Spouse',
        'Son',
        'Daughter',
        'Friend',
        'Relative',
        'Cousin',
        'Colleague',
        'Guarantor',
    ];

    $primary = read_reference('primary', $allowedRelations);
    $secondary = read_reference('secondary', $allowedRelations);

    if ($primary['mobile'] === $secondary['mobile']) {
        ref_response('error', 'Reference 1 and Reference 2 mobile numbers must be different.', 400);
    }

    if ($applicantMobile !== '' && in_array($applicantMobile, [$primary['mobile'], $secondary['mobile']], true)) {
        ref_response('error', 'Reference mobile number cannot be same as applicant mobile.', 400);
    }

    $stmt = $conn->prepare("
        INSERT INTO loan_application_references
            (application_id, reference_type, full_name, mobile, relation)
        VALUES
            (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            full_name = VALUES(full_name),
            mobile = VALUES(mobile),
            relation = VALUES(relation),
            updated_at = NOW()
    ");

    foreach (['primary' => $primary, 'secondary' => $secondary] as $type => $reference) {
        $stmt->bind_param(
            'sssss',
            $appId,
            $type,
            $reference['name'],
            $reference['mobile'],
            $reference['relation']
        );
        $stmt->execute();
    }

    loan_flow_mark_step($conn, $appId, 'documents');

    ref_response('success', 'Reference details saved successfully.');
} catch (Throwable $e) {
    error_log('Reference Save Error: ' . $e->getMessage());
    ref_response('error', 'Server error. Please try again later.', 500);
}
