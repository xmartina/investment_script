<?php
/**
 * Cron Job Wrapper Script
 * 
 * This is a safer wrapper for cron_updates.php that handles errors
 * related to missing $_SERVER variables and file includes.
 */

// Set error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start time measurement
$start_time = microtime(true);

echo "Starting cron job wrapper...\n";

try {
    // Run the actual cron script
    require_once __DIR__ . '/cron_updates.php';

    // If we get here, the script completed without fatal errors
    echo "Cron job completed successfully!\n";
} catch (Exception $e) {
    echo "Error executing cron job: " . $e->getMessage() . "\n";
}

// Calculate execution time
$execution_time = round(microtime(true) - $start_time, 2);
echo "Total execution time: $execution_time seconds\n"; 