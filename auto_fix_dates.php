<?php
/**
 * Automatic Date Fixing Script for Investment Returns
 * 
 * This script automatically checks for pending investment returns with dates
 * that are in the past and processes them without user interaction
 */

// Set up error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database configuration
require_once __DIR__ . '/include/db.php';

echo "AUTO-FIX: Checking investment return dates...\n";

// Check MySQL timezone
$result = $conn_back->query("SELECT @@time_zone, @@system_time_zone, NOW()");
if ($result && $row = $result->fetch_assoc()) {
    echo "MySQL timezone: " . $row['@@time_zone'] . 
         ", System timezone: " . $row['@@system_time_zone'] . 
         ", MySQL NOW(): " . $row['NOW()'] . "\n";
    
    echo "PHP time: " . date('Y-m-d H:i:s') . "\n";
}

// Get all pending investment returns
$sql = "SELECT * FROM investment_returns WHERE status = 'pending' ORDER BY expected_date ASC";
$result = $conn_back->query($sql);

if (!$result) {
    echo "Error fetching investment returns: " . $conn_back->error . "\n";
    exit;
}

echo "Found " . $result->num_rows . " pending investment returns\n";
echo "ID | Investment ID | Expected Date | User ID | Status\n";
echo "------------------------------------------------\n";

$found_returns = [];
$now = new DateTime();
$processed_count = 0;

while ($row = $result->fetch_assoc()) {
    $expected_date = new DateTime($row['expected_date']);
    $time_diff = $now->getTimestamp() - $expected_date->getTimestamp();
    $time_diff_days = floor($time_diff / 86400);
    
    $status = "";
    if ($time_diff > 0) {
        $status = "OVERDUE by " . $time_diff_days . " days";
    } else {
        $status = "Due in " . abs($time_diff_days) . " days";
    }
    
    echo $row['id'] . " | " . $row['investment_id'] . " | " . $row['expected_date'] . " | " . $row['user_id'] . " | " . $status . "\n";
    $found_returns[] = $row;
}

// Auto-process overdue returns
foreach ($found_returns as $return) {
    $expected_date = new DateTime($return['expected_date']);
    if ($now->getTimestamp() > $expected_date->getTimestamp()) {
        echo "\nAUTO-PROCESSING: Overdue return ID: " . $return['id'] . "\n";
        
        // Process the return
        $conn_back->begin_transaction();
        
        try {
            // Update status to paid
            $sql = "UPDATE investment_returns SET status = 'paid', paid_at = NOW() WHERE id = ?";
            $stmt = $conn_back->prepare($sql);
            $stmt->bind_param("i", $return['id']);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update return status: " . $stmt->error);
            }
            
            echo "Updated return status to paid\n";
            
            // Update user balance
            $sql = "UPDATE users SET main_balance = main_balance + ? WHERE id = ?";
            $stmt = $conn_back->prepare($sql);
            $stmt->bind_param("di", $return['return_amount'], $return['user_id']);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update user balance: " . $stmt->error);
            }
            
            echo "Updated user balance\n";
            
            // Create transaction record
            $reference_id = 'IR-' . time() . '-' . $return['user_id'];
            $date_time = date('Y-m-d H:i:s');
            
            $sql = "INSERT INTO transactions (user_id, transaction_type, reference_id, amount, status, date_time, description) 
                    VALUES (?, 'investment_return', ?, ?, 'completed', ?, ?)";
            $stmt = $conn_back->prepare($sql);
            $description = "Auto-processed investment return #" . $return['id'];
            $stmt->bind_param("isdss", $return['user_id'], $reference_id, $return['return_amount'], $date_time, $description);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create transaction: " . $stmt->error);
            }
            
            echo "Created transaction record\n";
            
            // Commit transaction
            $conn_back->commit();
            echo "Successfully processed return ID: " . $return['id'] . "\n";
            $processed_count++;
            
        } catch (Exception $e) {
            $conn_back->rollback();
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}

if ($processed_count > 0) {
    echo "\nAuto-processed $processed_count overdue returns.\n";
} else {
    echo "\nNo overdue returns found to process.\n";
}

// Update future returns with correct dates if needed
// This would fix any potential timezone issues
$future_returns_fixed = 0;

// Find any returns that might have incorrect dates
$sql = "SELECT * FROM investment_returns 
        WHERE status = 'pending' 
        AND expected_date > NOW() 
        AND DATE(expected_date) = CURDATE()";
$result = $conn_back->query($sql);

if ($result && $result->num_rows > 0) {
    echo "\nFound " . $result->num_rows . " returns with potentially incorrect timestamps (same day but future time)\n";
    
    while ($row = $result->fetch_assoc()) {
        // Get just the date part
        $date_part = date('Y-m-d', strtotime($row['expected_date']));
        // Set the time to current hour minus 1 to make it due
        $new_date = $date_part . ' ' . date('H:i:s', strtotime('-1 hour'));
        
        $conn_back->begin_transaction();
        try {
            $sql = "UPDATE investment_returns SET expected_date = ? WHERE id = ?";
            $stmt = $conn_back->prepare($sql);
            $stmt->bind_param("si", $new_date, $row['id']);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update date: " . $stmt->error);
            }
            
            $conn_back->commit();
            echo "AUTO-FIX: Updated date for return ID " . $row['id'] . " to " . $new_date . "\n";
            $future_returns_fixed++;
            
        } catch (Exception $e) {
            $conn_back->rollback();
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}

if ($future_returns_fixed > 0) {
    echo "Auto-fixed $future_returns_fixed return dates.\n";
}

echo "\nScript completed.\n"; 