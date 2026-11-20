<?php
declare(strict_types=1);
// Seeds the Location CMS tables from the config defaults. Run after storage/migrations/2026_11_19_location_cms.sql.
// Usage:  php storage/maintenance/seed_location_data.php           -> dry run, prints what would be inserted
//         php storage/maintenance/seed_location_data.php --apply   -> inserts missing rows only (never overwrites)
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

require __DIR__ . '/../../includes/location_data.php';
$apply = in_array('--apply', $argv, true);
$conn = db();

foreach (['location_pages', 'location_product_templates'] as $table) {
    if (!waqt_location_table_exists($conn, $table)) {
        fwrite(STDERR, "Table $table does not exist. Run storage/migrations/2026_11_19_location_cms.sql first.\n");
        exit(1);
    }
}

$hasSort = waqt_location_column_exists($conn, 'location_pages', 'sort_order');
$locations = require __DIR__ . '/../../config/location-pages.php';
$order = 0; $newLocations = 0;
foreach ($locations as $slug => $loc) {
    $order += 10;
    $exists = $conn->prepare('SELECT 1 FROM location_pages WHERE slug = ?');
    $exists->bind_param('s', $slug);
    $exists->execute();
    if ($exists->get_result()->fetch_row()) { echo "location $slug: already present, kept\n"; continue; }
    $newLocations++;
    echo "location $slug: " . ($apply ? 'inserted' : 'would insert') . "\n";
    if (!$apply) continue;
    $scenarios = json_encode(array_map(static fn($s) => [$s[0], $s[1]], $loc['scenarios'] ?? []), JSON_UNESCAPED_UNICODE);
    $areas = json_encode($loc['areas'] ?? [], JSON_UNESCAPED_UNICODE);
    $nearby = json_encode($loc['nearby'] ?? new stdClass(), JSON_UNESCAPED_UNICODE);
    $address = $loc['address'] ?? null;
    $sql = 'INSERT INTO location_pages (slug, name, title, meta_title, meta_description, type, intro, local_note, address, areas, scenarios, nearby, faqs, product_content, status'
        . ($hasSort ? ', sort_order' : '') . ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '[]', '{}', 'published'" . ($hasSort ? ', ?' : '') . ')';
    $stmt = $conn->prepare($sql);
    if ($hasSort) {
        $stmt->bind_param('ssssssssssssi', $slug, $loc['name'], $loc['title'], $loc['meta_title'], $loc['meta_description'], $loc['type'], $loc['intro'], $loc['local_note'], $address, $areas, $scenarios, $nearby, $order);
    } else {
        $stmt->bind_param('ssssssssssss', $slug, $loc['name'], $loc['title'], $loc['meta_title'], $loc['meta_description'], $loc['type'], $loc['intro'], $loc['local_note'], $address, $areas, $scenarios, $nearby);
    }
    $stmt->execute();
}

$templates = require __DIR__ . '/../../config/location-product-templates.php';
$order = 0; $newTemplates = 0;
foreach ($templates as $key => $content) {
    $order += 10;
    $exists = $conn->prepare('SELECT 1 FROM location_product_templates WHERE product_key = ?');
    $exists->bind_param('s', $key);
    $exists->execute();
    if ($exists->get_result()->fetch_row()) { echo "product $key: already present, kept\n"; continue; }
    $newTemplates++;
    echo "product $key: " . ($apply ? 'inserted' : 'would insert') . "\n";
    if (!$apply) continue;
    $json = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $stmt = $conn->prepare('INSERT INTO location_product_templates (product_key, content, is_active, sort_order, updated_by) VALUES (?, ?, 1, ?, ?)');
    $by = 'seed';
    $stmt->bind_param('ssis', $key, $json, $order, $by);
    $stmt->execute();
}

echo ($apply ? 'APPLIED' : 'DRY RUN') . ": $newLocations locations, $newTemplates product templates\n";
