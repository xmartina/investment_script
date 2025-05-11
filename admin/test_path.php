<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Path Debugging Tool</h1>";

// Document root information
echo "<h2>Server Paths</h2>";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "PHP_SELF: " . $_SERVER['PHP_SELF'] . "<br>";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . "<br>";
echo "Current file path: " . __FILE__ . "<br>";
echo "Current directory: " . __DIR__ . "<br>";

// Test file existence
echo "<h2>File Existence Tests</h2>";
$files_to_check = [
    $_SERVER['DOCUMENT_ROOT'] . '/admin/include/config.php',
    __DIR__ . '/include/config.php',
    $_SERVER['DOCUMENT_ROOT'] . '/include/config.php',
    __DIR__ . '/../include/config.php'
];

foreach ($files_to_check as $file) {
    echo "Testing: $file - " . (file_exists($file) ? "EXISTS" : "MISSING") . "<br>";
}

// Test database connection
echo "<h2>Database Connection Test</h2>";
try {
    include_once($_SERVER['DOCUMENT_ROOT'] . '/include/db.php');
    echo "Include db.php: SUCCESS<br>";
    
    if (isset($conn_back) && $conn_back) {
        echo "Backend DB Connection: SUCCESS<br>";
    } else {
        echo "Backend DB Connection: FAILED<br>";
    }
} catch (Exception $e) {
    echo "Database connection error: " . $e->getMessage();
}

// Test session
echo "<h2>Session Test</h2>";
echo "Session active: " . (session_status() === PHP_SESSION_ACTIVE ? "YES" : "NO") . "<br>";
echo "Session ID: " . session_id() . "<br>";
echo "<pre>SESSION: " . print_r($_SESSION, true) . "</pre>";

// Test site config variables
echo "<h2>Site Configuration Variables</h2>";
try {
    include_once($_SERVER['DOCUMENT_ROOT'] . '/include/config.php');
    echo "Include config.php: SUCCESS<br>";
    echo "Site Name: " . (isset($site_name) ? $site_name : "NOT SET") . "<br>";
    echo "Site Link: " . (isset($siteLink) ? $siteLink : "NOT SET") . "<br>";
} catch (Exception $e) {
    echo "Config error: " . $e->getMessage();
}

echo "<hr><p>End of path debug information</p>";
?> 