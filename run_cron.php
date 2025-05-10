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

// Define the CRON_RUNNING constant to signal that we're in a cron job
define('CRON_RUNNING', true);

// Start time measurement
$start_time = microtime(true);

echo "Starting cron job wrapper...\n";

// Set up an error handler for warnings
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Only log warnings about file includes
    if (strpos($errstr, 'Failed to open stream') !== false || 
        strpos($errstr, 'Failed opening') !== false) {
        echo "Warning: $errstr in $errfile on line $errline (suppressing further similar warnings)\n";
        // Return true to prevent the standard error handler from running
        return true;
    }
    // For other errors, let the standard error handler run
    return false;
});

try {
    // Run the actual cron script
    require_once __DIR__ . '/cron_updates.php';

    // If we get here, the script completed without fatal errors
    echo "Cron job completed successfully!\n";
} catch (Exception $e) {
    echo "Error executing cron job: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "PHP Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\n";
}

// Restore the default error handler
restore_error_handler();

// Calculate execution time
$execution_time = round(microtime(true) - $start_time, 2);
echo "Total execution time: $execution_time seconds\n"; 