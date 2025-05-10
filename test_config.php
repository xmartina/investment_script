<?php
// Display all errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Test if config loads correctly
echo "Testing config loading...\n";
require_once __DIR__ . '/include/config.php';

// Check if database connection variable exists
if (isset($conn_back) && $conn_back) {
    echo "Database connection ($conn_back) is working!\n";
} else {
    echo "Database connection failed!\n";
}

echo "Test completed.\n"; 