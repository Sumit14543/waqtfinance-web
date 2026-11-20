<?php
declare(strict_types=1);

/*
 * Single source of truth for location pages.
 *
 *  - Locations (loan-in-{slug}) come from the location_pages table, managed in Admin > Location CMS.
 *  - Product content for product + city pages ({product}-in-{slug}) comes from location_product_templates,
 *    managed in Admin > Location CMS > Product content; config/location-product-templates.php holds defaults.
 *  - If a table does not exist yet (fresh install), the config files are used read-only so the site still works.
 *  - Draft locations are never shown publicly (admins can preview them).
 */

require_once __DIR__ . '/../config/db.php';

if (!function_exists('waqt_location_table_exists')) {
    function waqt_location_table_exists(mysqli $conn, string $table): bool
    {
        static $cache = [];
        if (!array_key_exists($table, $cache)) {
            try {
                $stmt = $conn->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1');
                $stmt->bind_param('s', $table);
                $stmt->execute();
                $cache[$table] = (bool) $stmt->get_result()->fetch_row();
                $stmt->close();
            } catch (Throwable $e) {
                $cache[$table] = false;
            }
        }
        return $cache[$table];
    }

    function waqt_location_column_exists(mysqli $conn, string $table, string $column): bool
    {
        static $cache = [];
        $key = $table . '.' . $column;
        if (!array_key_exists($key, $cache)) {
            try {
                $stmt = $conn->prepare('SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1');
                $stmt->bind_param('ss', $table, $column);
                $stmt->execute();
                $cache[$key] = (bool) $stmt->get_result()->fetch_row();
                $stmt->close();
            } catch (Throwable $e) {
                $cache[$key] = false;
            }
        }
        return $cache[$key];
    }

    /** Company-wide defaults used when a location does not set its own value. */
    function waqt_location_site_defaults(): array
    {
        return [
            'phone' => env_value('SUPPORT_PHONE', '+91 92170 86610') ?? '+91 92170 86610',
            'email' => env_value('SUPPORT_EMAIL', 'customercare@waqtfinance.com') ?? 'customercare@waqtfinance.com',
        ];
    }

    /** Normalises a location row (DB or config) into one shape used by every template. */
    function waqt_location_normalise(array $row, string $slug): array
    {
        $decode = static function ($value): array {
            if (is_array($value)) return $value;
            $decoded = json_decode((string) $value, true);
            return is_array($decoded) ? $decoded : [];
        };
        $defaults = waqt_location_site_defaults();
        $scenarios = [];
        foreach ($decode($row['scenarios'] ?? []) as $scenario) {
            if (is_array($scenario)) {
                $title = (string) ($scenario['title'] ?? $scenario[0] ?? '');
                $text = (string) ($scenario['text'] ?? $scenario[1] ?? '');
                if ($title !== '' || $text !== '') $scenarios[] = [$title, $text];
            }
        }
        $nearby = [];
        foreach ($decode($row['nearby'] ?? []) as $key => $value) {
            // Stored either as {"slug":"Label"} or as [{"slug":"...","label":"..."}].
            if (is_array($value)) {
                $nearbySlug = (string) ($value['slug'] ?? '');
                $label = (string) ($value['label'] ?? $nearbySlug);
            } else {
                $nearbySlug = (string) $key;
                $label = (string) $value;
            }
            if (preg_match('/^[a-z0-9-]+$/', $nearbySlug)) $nearby[$nearbySlug] = $label;
        }
        $faqs = [];
        foreach ($decode($row['faqs'] ?? []) as $faq) {
            if (!empty($faq['q']) && !empty($faq['a'])) $faqs[] = ['q' => (string) $faq['q'], 'a' => (string) $faq['a']];
        }
        $type = in_array($row['type'] ?? '', ['office', 'region', 'service-area'], true) ? $row['type'] : 'service-area';

        return [
            'id' => (int) ($row['id'] ?? 0),
            'slug' => $slug,
            'name' => (string) ($row['name'] ?? ''),
            'title' => (string) ($row['title'] ?? ''),
            'meta_title' => (string) ($row['meta_title'] ?? ''),
            'meta_description' => (string) ($row['meta_description'] ?? ''),
            'type' => $type,
            'intro' => (string) ($row['intro'] ?? ''),
            'local_note' => (string) ($row['local_note'] ?? ''),
            'address' => trim((string) ($row['address'] ?? '')),
            'phone' => trim((string) ($row['phone'] ?? '')) ?: $defaults['phone'],
            'map_url' => trim((string) ($row['map_url'] ?? '')),
            'hero_image' => trim((string) ($row['hero_image'] ?? '')),
            'areas' => array_values(array_filter(array_map('strval', $decode($row['areas'] ?? [])), 'strlen')),
            'scenarios' => $scenarios,
            'nearby' => $nearby,
            'faqs' => $faqs,
            'product_content' => $decode($row['product_content'] ?? []),
            'status' => (string) ($row['status'] ?? 'published'),
            'sort_order' => (int) ($row['sort_order'] ?? 0),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
        ];
    }

    /** One location by slug, or null. Drafts are returned only when $includeDrafts is true (admin preview). */
    function waqt_get_location(string $slug, bool $includeDrafts = false): ?array
    {
        if (!preg_match('/^[a-z0-9-]{1,80}$/', $slug)) return null;
        try {
            $conn = db();
            if (waqt_location_table_exists($conn, 'location_pages')) {
                $sql = 'SELECT * FROM location_pages WHERE slug = ?' . ($includeDrafts ? '' : " AND status = 'published'") . ' LIMIT 1';
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('s', $slug);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                return $row ? waqt_location_normalise($row, $slug) : null;
            }
        } catch (Throwable $e) {
            error_log('Location lookup failed: ' . $e->getMessage());
        }
        $config = require __DIR__ . '/../config/location-pages.php';
        return isset($config[$slug]) ? waqt_location_normalise($config[$slug], $slug) : null;
    }

    /** All published locations, in admin-defined order then name. */
    function waqt_list_locations(): array
    {
        static $list = null;
        if ($list !== null) return $list;
        $list = [];
        try {
            $conn = db();
            if (waqt_location_table_exists($conn, 'location_pages')) {
                $order = waqt_location_column_exists($conn, 'location_pages', 'sort_order') ? 'sort_order ASC, name ASC' : 'name ASC';
                $res = $conn->query("SELECT * FROM location_pages WHERE status = 'published' ORDER BY $order");
                while ($res && ($row = $res->fetch_assoc())) {
                    $list[] = waqt_location_normalise($row, (string) $row['slug']);
                }
                return $list;
            }
        } catch (Throwable $e) {
            error_log('Location list failed: ' . $e->getMessage());
        }
        foreach (require __DIR__ . '/../config/location-pages.php' as $slug => $row) {
            $list[] = waqt_location_normalise($row, (string) $slug);
        }
        return $list;
    }

    /** Product content for product + city pages: config defaults overlaid with admin edits. */
    function waqt_location_product_templates(bool $includeInactive = false): array
    {
        static $cache = [];
        $cacheKey = $includeInactive ? 'all' : 'active';
        if (isset($cache[$cacheKey])) return $cache[$cacheKey];

        $templates = require __DIR__ . '/../config/location-product-templates.php';
        $ranges = [
            'personal-loan' => '₹50,000 to ₹25 Lakh', 'business-loan' => '₹1 Lakh to ₹25 Lakh',
            'pay-day-loan' => '₹3,000 to ₹1.5 Lakh before salary', 'edi-loan' => '₹25,000 to ₹5 Lakh (daily EDI)',
            'vehicle-loan' => 'up to 90% on-road vehicle finance', 'loan-against-property' => '₹5 Lakh to ₹5 Crore',
        ];
        $order = 0;
        foreach ($templates as $key => &$template) {
            $template['key'] = $key;
            $template['range_text'] = $template['range_text'] ?? ($ranges[$key] ?? '');
            $template['is_active'] = true;
            $template['sort_order'] = $order++;
        }
        unset($template);

        try {
            $conn = db();
            if (waqt_location_table_exists($conn, 'location_product_templates')) {
                $res = $conn->query('SELECT product_key, content, is_active, sort_order FROM location_product_templates');
                while ($res && ($row = $res->fetch_assoc())) {
                    $key = (string) $row['product_key'];
                    $content = json_decode((string) $row['content'], true);
                    if (!is_array($content) || !isset($templates[$key])) continue;
                    $templates[$key] = array_replace($templates[$key], $content);
                    $templates[$key]['is_active'] = (bool) $row['is_active'];
                    $templates[$key]['sort_order'] = (int) $row['sort_order'];
                }
            }
        } catch (Throwable $e) {
            error_log('Location product templates failed: ' . $e->getMessage());
        }

        if (!$includeInactive) {
            $templates = array_filter($templates, static fn(array $t): bool => !empty($t['is_active']));
        }
        uasort($templates, static fn(array $a, array $b): int => $a['sort_order'] <=> $b['sort_order']);
        return $cache[$cacheKey] = $templates;
    }
}
