<?php
header('Content-Type: text/plain');
$file = __DIR__ . '/aa_debug.log';
if (file_exists($file)) {
    echo file_get_contents($file);
} else {
    echo "NO_LOG_YET";
}
