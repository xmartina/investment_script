<?php
// Display all errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Testing database connection directly...\n";

// Include only the database file to test connection
require_once __DIR__ . '/include/db.php';

// Check if database connection variables exist
if (isset($conn_front) && $conn_front instanceof mysqli) {
    echo "Frontend database connection is working! Connected to host: " . $conn_front->host_info . "\n";
} else {
    echo "Frontend database connection failed!\n";
}

if (isset($conn_back) && $conn_back instanceof mysqli) {
    echo "Backend database connection is working! Connected to host: " . $conn_back->host_info . "\n";
} else {
    echo "Backend database connection failed!\n";
}

echo "Connection test completed.\n"; 