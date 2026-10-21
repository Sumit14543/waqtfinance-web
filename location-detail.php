<?php
declare(strict_types=1);

$location_key = trim((string) ($_GET['slug'] ?? ''));

if ($location_key === '') {
    header("Location: index.php");
    exit;
}

require __DIR__ . '/includes/location-page-template.php';
