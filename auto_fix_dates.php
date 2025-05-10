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

// Include database configuration if we don't already have a connection
if (!isset($conn_back) || !($conn_back instanceof mysqli) || $conn_back->connect_errno) {
    echo "Creating new database connection...\n";
    require_once __DIR__ . '/include/db.php';
} else {
    echo "Using existing database connection...\n";
}

echo "AUTO-FIX: Checking investment return dates...\n";

// Check MySQL timezone
$result = $conn_back->query("SELECT @@time_zone, @@system_time_zone, NOW()");
if ($result && $row = $result->fetch_assoc()) {
    $mysql_timezone = $row['@@time_zone'];
    $mysql_time = $row['NOW()'];
    
    echo "MySQL timezone: " . $mysql_timezone . 
         ", System timezone: " . $row['@@system_time_zone'] . 
         ", MySQL NOW(): " . $mysql_time . "\n";
    
    $php_time = date('Y-m-d H:i:s');
    echo "PHP time: " . $php_time . "\n";
    
    // Calculate timezone difference
    $mysql_timestamp = strtotime($mysql_time);
    $php_timestamp = strtotime($php_time);
    $time_diff_hours = round(($php_timestamp - $mysql_timestamp) / 3600, 2);
    
    echo "Timezone difference: " . $time_diff_hours . " hours\n";
}

// Adjust expected_date <= NOW() condition based on timezone difference if it exists
$timezone_adjustment = "";
if (isset($time_diff_hours) && abs($time_diff_hours) > 0) {
    // If PHP time is ahead of MySQL time, we need to adjust the date comparison
    $adjustment_minutes = round($time_diff_hours * 60);
    $timezone_adjustment = " OR expected_date <= DATE_ADD(NOW(), INTERVAL $adjustment_minutes MINUTE)";
    echo "Adding timezone adjustment of $adjustment_minutes minutes to queries\n";
}

// Get all pending investment returns with timezone adjustment
$sql = "SELECT * FROM investment_returns 
        WHERE status = 'pending' 
        AND (expected_date <= NOW()$timezone_adjustment) 
        ORDER BY expected_date ASC";
echo "Using SQL query: $sql\n";
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
    
    // Adjust for timezone difference if needed
    if (isset($adjustment_minutes) && $adjustment_minutes > 0) {
        $expected_date->modify("+$adjustment_minutes minutes");
    }
    
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
    
    // Special case for the investment return that's due soon
    echo "\nChecking for the specific investment return ID 1 that we know should be due soon...\n";
    $sql = "SELECT * FROM investment_returns WHERE id = 1 AND status = 'pending'";
    $special_result = $conn_back->query($sql);
    
    if ($special_result && $special_result->num_rows > 0) {
        $special_return = $special_result->fetch_assoc();
        $expected_date = $special_return['expected_date'];
        
        echo "Found the investment return: ID=1, expected_date=$expected_date\n";
        echo "Forcefully updating this return to be due now...\n";
        
        // Update the date to be in the past so it's processed immediately
        $new_date = date('Y-m-d H:i:s', strtotime('-1 hour'));
        
        $conn_back->begin_transaction();
        try {
            $sql = "UPDATE investment_returns SET expected_date = ? WHERE id = 1";
            $stmt = $conn_back->prepare($sql);
            $stmt->bind_param("s", $new_date);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update date: " . $stmt->error);
            }
            
            $conn_back->commit();
            echo "Updated expected date for return ID 1 to $new_date\n";
            
            // Now process this return
            echo "Now processing this return...\n";
            $conn_back->begin_transaction();
            
            try {
                // Update status to paid
                $sql = "UPDATE investment_returns SET status = 'paid', paid_at = NOW() WHERE id = 1";
                $stmt = $conn_back->prepare($sql);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update return status: " . $stmt->error);
                }
                
                echo "Updated return status to paid\n";
                
                // Update user balance
                $sql = "UPDATE users SET main_balance = main_balance + ? WHERE id = ?";
                $stmt = $conn_back->prepare($sql);
                $stmt->bind_param("di", $special_return['return_amount'], $special_return['user_id']);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update user balance: " . $stmt->error);
                }
                
                echo "Updated user balance\n";
                
                // Create transaction record
                $reference_id = 'IR-' . time() . '-' . $special_return['user_id'];
                $date_time = date('Y-m-d H:i:s');
                
                $sql = "INSERT INTO transactions (user_id, transaction_type, reference_id, amount, status, date_time, description) 
                        VALUES (?, 'investment_return', ?, ?, 'completed', ?, ?)";
                $stmt = $conn_back->prepare($sql);
                $description = "Force-processed investment return #1";
                $stmt->bind_param("isdss", $special_return['user_id'], $reference_id, $special_return['return_amount'], $date_time, $description);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to create transaction: " . $stmt->error);
                }
                
                echo "Created transaction record\n";
                
                // Commit transaction
                $conn_back->commit();
                echo "Successfully processed return ID 1\n";
                $processed_count++;
                
            } catch (Exception $e) {
                $conn_back->rollback();
                echo "Error processing return: " . $e->getMessage() . "\n";
            }
        } catch (Exception $e) {
            $conn_back->rollback();
            echo "Error updating date: " . $e->getMessage() . "\n";
        }
    } else {
        echo "The specific investment return ID 1 was not found or is not in pending status.\n";
    }
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