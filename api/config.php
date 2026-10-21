<?php
require __DIR__ . '/../config/db.php';

set_exception_handler(static function (Throwable $e): void {
    report_exception($e);
});
