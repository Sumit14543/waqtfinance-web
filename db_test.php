<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h2>Waqt Finance DB Connection Diagnostic Tool</h2>";

$user = "u612504555_waqt_admin";
$password = "Waqt@#@#12345";
$database = "u612504555_waqt_db";

// Test 1: localhost
echo "<strong>1. Connecting via 'localhost'...</strong><br>";
try {
    $conn1 = @new mysqli("localhost", $user, $password, $database);
    if ($conn1->connect_error) {
        echo "<span style='color:red;'>FAIL: " . $conn1->connect_error . "</span><br><br>";
    } else {
        echo "<span style='color:green;'>SUCCESS! (Recommended for live server)</span><br><br>";
        $conn1->close();
    }
} catch (Throwable $e) {
    echo "<span style='color:red;'>FAIL: " . $e->getMessage() . "</span><br><br>";
}

// Test 2: 127.0.0.1
echo "<strong>2. Connecting via '127.0.0.1'...</strong><br>";
try {
    $conn2 = @new mysqli("127.0.0.1", $user, $password, $database);
    if ($conn2->connect_error) {
        echo "<span style='color:red;'>FAIL: " . $conn2->connect_error . "</span><br><br>";
    } else {
        echo "<span style='color:green;'>SUCCESS!</span><br><br>";
        $conn2->close();
    }
} catch (Throwable $e) {
    echo "<span style='color:red;'>FAIL: " . $e->getMessage() . "</span><br><br>";
}

// Test 3: srv1877.hstgr.io
echo "<strong>3. Connecting via 'srv1877.hstgr.io'...</strong><br>";
try {
    $conn3 = @new mysqli("srv1877.hstgr.io", $user, $password, $database);
    if ($conn3->connect_error) {
        echo "<span style='color:red;'>FAIL: " . $conn3->connect_error . "</span><br><br>";
    } else {
        echo "<span style='color:green;'>SUCCESS! (Usually blocked by Hostinger loopback/firewall from within the server)</span><br><br>";
        $conn3->close();
    }
} catch (Throwable $e) {
    echo "<span style='color:red;'>FAIL: " . $e->getMessage() . "</span><br><br>";
}
?>
