<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

start_secure_session();

require_post();
require_valid_csrf();
header('Cache-Control: no-store, private, max-age=0');

// Clear admin session variables
$_SESSION = [];

// Destroy session cookie if set
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

header("Location: login.php");
exit;
