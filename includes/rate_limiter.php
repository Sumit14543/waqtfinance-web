<?php
declare(strict_types=1);

/**
 * Waqt Finance - Central Production Rate Limiter
 * Provides multi-tier rate limiting (by IP, Mobile, PAN, Action)
 * Supports MySQL backend with automatic failover to local file cache.
 */

if (!function_exists('waqt_client_ip')) {
    function waqt_client_ip(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $raw = (string)$_SERVER[$header];
                if (str_contains($raw, ',')) {
                    $ips = explode(',', $raw);
                    $candidate = trim($ips[0]);
                } else {
                    $candidate = trim($raw);
                }

                if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                    return $candidate;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}

if (!function_exists('waqt_ensure_rate_limit_table')) {
    function waqt_ensure_rate_limit_table(mysqli $db): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }

        try {
            $sql = "CREATE TABLE IF NOT EXISTS `waqt_rate_limits` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `rate_key` VARCHAR(120) NOT NULL,
                `identifier` VARCHAR(120) NOT NULL,
                `hits` INT UNSIGNED NOT NULL DEFAULT 1,
                `reset_at` INT UNSIGNED NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_key_identifier` (`rate_key`, `identifier`),
                KEY `idx_reset_at` (`reset_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $db->query($sql);
            $checked = true;
        } catch (Throwable $e) {
            error_log('waqt_ensure_rate_limit_table error: ' . $e->getMessage());
        }
    }
}

if (!function_exists('waqt_file_rate_limit')) {
    function waqt_file_rate_limit(string $key, string $identifier, int $maxAttempts, int $windowSeconds): array
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'waqt_rate_limits';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $hash = md5($key . ':' . $identifier);
        $file = $dir . DIRECTORY_SEPARATOR . 'rl_' . $hash . '.json';
        $now = time();

        $data = ['hits' => 0, 'reset_at' => $now + $windowSeconds];
        if (file_exists($file)) {
            $content = @file_get_contents($file);
            if ($content) {
                $decoded = json_decode($content, true);
                if (is_array($decoded) && isset($decoded['reset_at']) && $decoded['reset_at'] > $now) {
                    $data = $decoded;
                }
            }
        }

        if ($data['reset_at'] <= $now) {
            $data = ['hits' => 0, 'reset_at' => $now + $windowSeconds];
        }

        $data['hits']++;
        @file_put_contents($file, json_encode($data), LOCK_EX);

        $allowed = $data['hits'] <= $maxAttempts;
        $remaining = max(0, $maxAttempts - $data['hits']);
        $resetIn = max(0, $data['reset_at'] - $now);

        return [
            'allowed' => $allowed,
            'current' => $data['hits'],
            'limit' => $maxAttempts,
            'remaining' => $remaining,
            'reset_in' => $resetIn
        ];
    }
}

if (!function_exists('waqt_check_rate_limit')) {
    /**
     * Check if a request exceeds rate limit.
     *
     * @param string $action Action name e.g. 'send_otp', 'verify_pan'
     * @param string $identifier Unique identifier e.g. IP address, mobile, or PAN
     * @param int $maxAttempts Maximum allowed attempts in window
     * @param int $windowSeconds Time window in seconds
     * @return array ['allowed' => bool, 'current' => int, 'limit' => int, 'remaining' => int, 'reset_in' => int]
     */
    function waqt_check_rate_limit(string $action, string $identifier, int $maxAttempts, int $windowSeconds): array
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            $identifier = waqt_client_ip();
        }

        $now = time();
        $db = null;

        try {
            if (function_exists('db')) {
                $db = db();
            }
        } catch (Throwable $e) {
            $db = null;
        }

        if (!$db instanceof mysqli) {
            return waqt_file_rate_limit($action, $identifier, $maxAttempts, $windowSeconds);
        }

        try {
            waqt_ensure_rate_limit_table($db);

            // Fetch current rate limit row
            $stmt = $db->prepare("SELECT id, hits, reset_at FROM `waqt_rate_limits` WHERE `rate_key` = ? AND `identifier` = ? LIMIT 1");
            if (!$stmt) {
                return waqt_file_rate_limit($action, $identifier, $maxAttempts, $windowSeconds);
            }

            $stmt->bind_param('ss', $action, $identifier);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            $stmt->close();

            if (!$row || (int)$row['reset_at'] <= $now) {
                // New window
                $resetAt = $now + $windowSeconds;
                $upsert = $db->prepare("INSERT INTO `waqt_rate_limits` (`rate_key`, `identifier`, `hits`, `reset_at`)
                    VALUES (?, ?, 1, ?)
                    ON DUPLICATE KEY UPDATE `hits` = 1, `reset_at` = VALUES(`reset_at`)");
                if ($upsert) {
                    $upsert->bind_param('ssi', $action, $identifier, $resetAt);
                    $upsert->execute();
                    $upsert->close();
                }

                return [
                    'allowed' => true,
                    'current' => 1,
                    'limit' => $maxAttempts,
                    'remaining' => $maxAttempts - 1,
                    'reset_in' => $windowSeconds
                ];
            }

            // Window is still active - increment hits
            $newHits = (int)$row['hits'] + 1;
            $resetAt = (int)$row['reset_at'];
            $resetIn = max(0, $resetAt - $now);

            $update = $db->prepare("UPDATE `waqt_rate_limits` SET `hits` = `hits` + 1 WHERE `id` = ?");
            if ($update) {
                $id = (int)$row['id'];
                $update->bind_param('i', $id);
                $update->execute();
                $update->close();
            }

            $allowed = $newHits <= $maxAttempts;
            $remaining = max(0, $maxAttempts - $newHits);

            return [
                'allowed' => $allowed,
                'current' => $newHits,
                'limit' => $maxAttempts,
                'remaining' => $remaining,
                'reset_in' => $resetIn
            ];
        } catch (Throwable $e) {
            error_log('DB rate limit error, falling back to file: ' . $e->getMessage());
            return waqt_file_rate_limit($action, $identifier, $maxAttempts, $windowSeconds);
        }
    }
}

if (!function_exists('waqt_enforce_rate_limit')) {
    /**
     * Enforce rate limit. If exceeded, sends 429 response and terminates script.
     *
     * @param string $action Action key e.g. 'send_otp'
     * @param string $identifier Unique ID (IP, mobile, PAN, etc.)
     * @param int $maxAttempts Max attempts allowed
     * @param int $windowSeconds Window in seconds
     * @param string $friendlyMsg Optional custom user-friendly message
     */
    function waqt_enforce_rate_limit(string $action, string $identifier, int $maxAttempts, int $windowSeconds, string $friendlyMsg = ''): void
    {
        $status = waqt_check_rate_limit($action, $identifier, $maxAttempts, $windowSeconds);

        if (headers_sent()) {
            return;
        }

        header('X-RateLimit-Limit: ' . $status['limit']);
        header('X-RateLimit-Remaining: ' . $status['remaining']);
        header('X-RateLimit-Reset: ' . $status['reset_in']);

        if (!$status['allowed']) {
            $resetIn = $status['reset_in'];
            header('HTTP/1.1 429 Too Many Requests');
            http_response_code(429);
            header('Retry-After: ' . $resetIn);
            header('Content-Type: application/json; charset=utf-8');

            $msg = $friendlyMsg ?: "Too many attempts. For security, please wait {$resetIn} seconds before trying again.";

            echo json_encode([
                'status' => 'error',
                'code' => 'RATE_LIMIT_EXCEEDED',
                'message' => $msg,
                'retry_after' => $resetIn,
                'limit' => $status['limit'],
                'current' => $status['current']
            ], JSON_UNESCAPED_SLASHES);
            exit;
        }
    }
}

if (!function_exists('waqt_get_rate_limit_status')) {
    /**
     * Inspect rate limit status without incrementing hits.
     *
     * @param string $action
     * @param string $identifier
     * @param int $maxAttempts
     * @return array ['blocked' => bool, 'hits' => int, 'limit' => int, 'remaining' => int, 'reset_in' => int]
     */
    function waqt_get_rate_limit_status(string $action, string $identifier, int $maxAttempts): array
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            $identifier = waqt_client_ip();
        }

        $now = time();
        $db = null;
        try {
            if (function_exists('db')) {
                $db = db();
            }
        } catch (Throwable $e) {
            $db = null;
        }

        if ($db instanceof mysqli) {
            try {
                waqt_ensure_rate_limit_table($db);
                $stmt = $db->prepare("SELECT hits, reset_at FROM `waqt_rate_limits` WHERE `rate_key` = ? AND `identifier` = ? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('ss', $action, $identifier);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $row = $res ? $res->fetch_assoc() : null;
                    $stmt->close();

                    if ($row && (int)$row['reset_at'] > $now) {
                        $hits = (int)$row['hits'];
                        $resetIn = (int)$row['reset_at'] - $now;
                        $blocked = ($hits >= $maxAttempts);
                        return [
                            'blocked' => $blocked,
                            'hits' => $hits,
                            'limit' => $maxAttempts,
                            'remaining' => max(0, $maxAttempts - $hits),
                            'reset_in' => $resetIn
                        ];
                    }
                }
            } catch (Throwable $e) {
                // fall through to file
            }
        }

        // File-based fallback
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'waqt_rate_limits';
        $hash = md5($action . ':' . $identifier);
        $file = $dir . DIRECTORY_SEPARATOR . 'rl_' . $hash . '.json';
        if (file_exists($file)) {
            $content = @file_get_contents($file);
            if ($content) {
                $decoded = json_decode($content, true);
                if (is_array($decoded) && isset($decoded['reset_at']) && $decoded['reset_at'] > $now) {
                    $hits = (int)($decoded['hits'] ?? 0);
                    $resetIn = (int)$decoded['reset_at'] - $now;
                    $blocked = ($hits >= $maxAttempts);
                    return [
                        'blocked' => $blocked,
                        'hits' => $hits,
                        'limit' => $maxAttempts,
                        'remaining' => max(0, $maxAttempts - $hits),
                        'reset_in' => $resetIn
                    ];
                }
            }
        }

        return [
            'blocked' => false,
            'hits' => 0,
            'limit' => $maxAttempts,
            'remaining' => $maxAttempts,
            'reset_in' => 0
        ];
    }
}

if (!function_exists('waqt_record_failure')) {
    /**
     * Record a failed attempt (e.g., incorrect password).
     *
     * @param string $action
     * @param string $identifier
     * @param int $maxAttempts
     * @param int $lockoutSeconds
     * @return array ['blocked' => bool, 'hits' => int, 'limit' => int, 'remaining' => int, 'reset_in' => int]
     */
    function waqt_record_failure(string $action, string $identifier, int $maxAttempts, int $lockoutSeconds): array
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            $identifier = waqt_client_ip();
        }

        $now = time();
        $db = null;
        try {
            if (function_exists('db')) {
                $db = db();
            }
        } catch (Throwable $e) {
            $db = null;
        }

        if ($db instanceof mysqli) {
            try {
                waqt_ensure_rate_limit_table($db);

                $stmt = $db->prepare("SELECT id, hits, reset_at FROM `waqt_rate_limits` WHERE `rate_key` = ? AND `identifier` = ? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('ss', $action, $identifier);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $row = $res ? $res->fetch_assoc() : null;
                    $stmt->close();

                    if (!$row || (int)$row['reset_at'] <= $now) {
                        $resetAt = $now + $lockoutSeconds;
                        $upsert = $db->prepare("INSERT INTO `waqt_rate_limits` (`rate_key`, `identifier`, `hits`, `reset_at`)
                            VALUES (?, ?, 1, ?)
                            ON DUPLICATE KEY UPDATE `hits` = 1, `reset_at` = VALUES(`reset_at`)");
                        if ($upsert) {
                            $upsert->bind_param('ssi', $action, $identifier, $resetAt);
                            $upsert->execute();
                            $upsert->close();
                        }
                        $blocked = (1 >= $maxAttempts);
                        return [
                            'blocked' => $blocked,
                            'hits' => 1,
                            'limit' => $maxAttempts,
                            'remaining' => max(0, $maxAttempts - 1),
                            'reset_in' => $lockoutSeconds
                        ];
                    }

                    $newHits = (int)$row['hits'] + 1;
                    $resetAt = max((int)$row['reset_at'], $now + ($newHits >= $maxAttempts ? $lockoutSeconds : 0));
                    $resetIn = max(0, $resetAt - $now);

                    $update = $db->prepare("UPDATE `waqt_rate_limits` SET `hits` = ?, `reset_at` = ? WHERE `id` = ?");
                    if ($update) {
                        $id = (int)$row['id'];
                        $update->bind_param('iii', $newHits, $resetAt, $id);
                        $update->execute();
                        $update->close();
                    }

                    $blocked = ($newHits >= $maxAttempts);
                    return [
                        'blocked' => $blocked,
                        'hits' => $newHits,
                        'limit' => $maxAttempts,
                        'remaining' => max(0, $maxAttempts - $newHits),
                        'reset_in' => $resetIn
                    ];
                }
            } catch (Throwable $e) {
                // fall through to file
            }
        }

        // File-based fallback
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'waqt_rate_limits';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $hash = md5($action . ':' . $identifier);
        $file = $dir . DIRECTORY_SEPARATOR . 'rl_' . $hash . '.json';

        $data = ['hits' => 0, 'reset_at' => $now + $lockoutSeconds];
        if (file_exists($file)) {
            $content = @file_get_contents($file);
            if ($content) {
                $decoded = json_decode($content, true);
                if (is_array($decoded) && isset($decoded['reset_at']) && $decoded['reset_at'] > $now) {
                    $data = $decoded;
                }
            }
        }

        if ($data['reset_at'] <= $now) {
            $data = ['hits' => 0, 'reset_at' => $now + $lockoutSeconds];
        }

        $data['hits']++;
        if ($data['hits'] >= $maxAttempts) {
            $data['reset_at'] = max($data['reset_at'], $now + $lockoutSeconds);
        }
        @file_put_contents($file, json_encode($data), LOCK_EX);

        $blocked = ($data['hits'] >= $maxAttempts);
        $resetIn = max(0, $data['reset_at'] - $now);
        return [
            'blocked' => $blocked,
            'hits' => $data['hits'],
            'limit' => $maxAttempts,
            'remaining' => max(0, $maxAttempts - $data['hits']),
            'reset_in' => $resetIn
        ];
    }
}

if (!function_exists('waqt_reset_rate_limit')) {
    /**
     * Clear rate limit / failed attempts for a given action and identifier.
     *
     * @param string $action
     * @param string $identifier
     */
    function waqt_reset_rate_limit(string $action, string $identifier): void
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            $identifier = waqt_client_ip();
        }

        try {
            if (function_exists('db')) {
                $db = db();
                if ($db instanceof mysqli) {
                    $stmt = $db->prepare("DELETE FROM `waqt_rate_limits` WHERE `rate_key` = ? AND `identifier` = ?");
                    if ($stmt) {
                        $stmt->bind_param('ss', $action, $identifier);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            }
        } catch (Throwable $e) {
            // Ignore DB reset errors
        }

        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'waqt_rate_limits';
        $hash = md5($action . ':' . $identifier);
        $file = $dir . DIRECTORY_SEPARATOR . 'rl_' . $hash . '.json';
        if (file_exists($file)) {
            @unlink($file);
        }
    }
}
