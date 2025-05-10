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

// Variable to track if any returns were processed
$returns_processed = false;

try {
    // Capture the output of cron_updates.php to check if returns were processed
    ob_start();
    
    // Run the actual cron script
    require_once __DIR__ . '/cron_updates.php';
    
    $output = ob_get_clean();
    echo $output;
    
    // Check if any returns were processed
    if (strpos($output, 'Found 0 due investment returns') !== false) {
        echo "\nNo investment returns were processed during normal cron run. Trying auto-fix...\n";
        
        // Run the auto_fix_dates script to check and fix dates
        echo "\n--- Starting Auto-Fix Script ---\n";
        include_once __DIR__ . '/auto_fix_dates.php';
        echo "--- Auto-Fix Script Completed ---\n";
    } else {
        $returns_processed = true;
    }
    
    // If we get here, the script completed without fatal errors
    echo "Cron job completed successfully!\n";
} catch (Exception $e) {
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    echo "Error executing cron job: " . $e->getMessage() . "\n";
} catch (Error $e) {
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    echo "PHP Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\n";
}

// Restore the default error handler
restore_error_handler();

// Calculate execution time
$execution_time = round(microtime(true) - $start_time, 2);
echo "Total execution time: $execution_time seconds\n"; 