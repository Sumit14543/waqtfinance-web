<?php
declare(strict_types=1);

/*
 * Admin accountability: who did what, and a copy of anything deleted.
 * Loan, KYC and credit records must be retrievable for years (PMLA / RBI KYC directions), so a delete
 * from the admin panel first copies the full row into deleted_records_archive.
 * Tables are created on first use, like waqt_rate_limits.
 */

if (!function_exists('waqt_admin_audit_ensure_tables')) {
    function waqt_admin_audit_ensure_tables(mysqli $conn): void
    {
        static $ready = false;
        if ($ready) return;
        $conn->query("CREATE TABLE IF NOT EXISTS admin_audit_log (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NULL,
            admin_email VARCHAR(191) NULL,
            admin_role VARCHAR(40) NULL,
            action VARCHAR(60) NOT NULL,
            entity_table VARCHAR(64) NULL,
            entity_id VARCHAR(64) NULL,
            details TEXT NULL,
            ip_address VARCHAR(64) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_audit_created (created_at),
            KEY idx_audit_entity (entity_table, entity_id),
            KEY idx_audit_admin (admin_id, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $conn->query("CREATE TABLE IF NOT EXISTS deleted_records_archive (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            source_table VARCHAR(64) NOT NULL,
            source_id VARCHAR(64) NOT NULL,
            row_json LONGTEXT NOT NULL,
            deleted_by_admin_id INT NULL,
            deleted_by_email VARCHAR(191) NULL,
            deleted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_archive_source (source_table, source_id),
            KEY idx_archive_deleted (deleted_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $ready = true;
    }
}

if (!function_exists('waqt_admin_audit')) {
    /** Records an admin action. Never throws: an audit failure is logged, not shown to the user. */
    function waqt_admin_audit(mysqli $conn, string $action, ?string $table = null, $entityId = null, array $details = []): void
    {
        try {
            waqt_admin_audit_ensure_tables($conn);
            $stmt = $conn->prepare("INSERT INTO admin_audit_log (admin_id, admin_email, admin_role, action, entity_table, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) return;
            $adminId = isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null;
            $email = isset($_SESSION['admin_email']) ? (string) $_SESSION['admin_email'] : null;
            $role = isset($_SESSION['admin_role']) ? (string) $_SESSION['admin_role'] : null;
            $id = $entityId === null ? null : (string) $entityId;
            $json = $details ? json_encode($details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;
            $ip = function_exists('waqt_client_ip') ? waqt_client_ip() : ($_SERVER['REMOTE_ADDR'] ?? null);
            $stmt->bind_param('isssssss', $adminId, $email, $role, $action, $table, $id, $json, $ip);
            $stmt->execute();
            $stmt->close();
        } catch (Throwable $e) {
            error_log('Admin audit log failed: ' . $e->getMessage());
        }
    }
}

if (!function_exists('waqt_archive_and_delete')) {
    /**
     * Copies the row into deleted_records_archive, deletes it, and writes an audit entry, in one transaction.
     * $table must already be whitelisted by the caller. Returns true when a row was deleted.
     */
    function waqt_archive_and_delete(mysqli $conn, string $table, int $id): bool
    {
        if (!preg_match('/^[a-z0-9_]+$/', $table) || $id <= 0) return false;
        waqt_admin_audit_ensure_tables($conn);
        $conn->begin_transaction();
        try {
            $select = $conn->prepare("SELECT * FROM `$table` WHERE id = ? FOR UPDATE");
            $select->bind_param('i', $id);
            $select->execute();
            $row = $select->get_result()->fetch_assoc();
            $select->close();
            if (!$row) {
                $conn->rollback();
                return false;
            }

            $rowJson = json_encode($row, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            $adminId = isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null;
            $email = isset($_SESSION['admin_email']) ? (string) $_SESSION['admin_email'] : null;
            $sourceId = (string) $id;
            $archive = $conn->prepare("INSERT INTO deleted_records_archive (source_table, source_id, row_json, deleted_by_admin_id, deleted_by_email) VALUES (?, ?, ?, ?, ?)");
            $archive->bind_param('sssis', $table, $sourceId, $rowJson, $adminId, $email);
            $archive->execute();
            $archive->close();

            $delete = $conn->prepare("DELETE FROM `$table` WHERE id = ?");
            $delete->bind_param('i', $id);
            $delete->execute();
            $deleted = $delete->affected_rows > 0;
            $delete->close();

            $conn->commit();
        } catch (Throwable $e) {
            $conn->rollback();
            error_log("Archive-and-delete failed for $table #$id: " . $e->getMessage());
            return false;
        }

        $reference = (string) ($row['application_id'] ?? $row['application_ref'] ?? $row['slug'] ?? '');
        waqt_admin_audit($conn, 'record.delete', $table, $id, array_filter(['reference' => $reference]));
        return $deleted;
    }
}
