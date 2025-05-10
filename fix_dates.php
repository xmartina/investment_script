<?php
/**
 * Date fixing script for investment returns
 * 
 * This script checks and corrects date issues in the investment_returns table
 */

// Set up error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database configuration
require_once __DIR__ . '/include/db.php';

echo "Checking investment return dates...\n";

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
echo "ID | Investment ID | Expected Date | User ID\n";
echo "------------------------------------------------\n";

$found_returns = [];
$now = new DateTime();

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

// Check for a specific return that's due (if any overdue returns found)
$found_due_return = false;
foreach ($found_returns as $return) {
    $expected_date = new DateTime($return['expected_date']);
    if ($now->getTimestamp() > $expected_date->getTimestamp()) {
        $found_due_return = true;
        echo "\nFound overdue return ID: " . $return['id'] . "\n";
        
        // Ask if user wants to force process this return
        echo "Do you want to force process this return? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        if (trim($line) == 'y') {
            echo "Processing return ID: " . $return['id'] . "...\n";
            
            // Process the return (this is a simplified version)
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
                $description = "Manually processed investment return #" . $return['id'];
                $stmt->bind_param("isdss", $return['user_id'], $reference_id, $return['return_amount'], $date_time, $description);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to create transaction: " . $stmt->error);
                }
                
                echo "Created transaction record\n";
                
                // Commit transaction
                $conn_back->commit();
                echo "Successfully processed return ID: " . $return['id'] . "\n";
                
            } catch (Exception $e) {
                $conn_back->rollback();
                echo "Error: " . $e->getMessage() . "\n";
            }
        }
        break;
    }
}

if (!$found_due_return) {
    echo "\nNo overdue returns found.\n";
    
    // Option to fix dates
    echo "Do you want to check and fix date formats? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    if (trim($line) == 'y') {
        echo "Checking date formats...\n";
        
        // Check for the earliest pending return
        if (count($found_returns) > 0) {
            $earliest = $found_returns[0];
            echo "Earliest return: ID " . $earliest['id'] . ", Expected date: " . $earliest['expected_date'] . "\n";
            
            // Ask for a new date
            echo "Enter new date for this return (YYYY-MM-DD HH:MM:SS) or leave blank to skip: ";
            $line = fgets($handle);
            $new_date = trim($line);
            
            if (!empty($new_date)) {
                $conn_back->begin_transaction();
                
                try {
                    $sql = "UPDATE investment_returns SET expected_date = ? WHERE id = ?";
                    $stmt = $conn_back->prepare($sql);
                    $stmt->bind_param("si", $new_date, $earliest['id']);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to update date: " . $stmt->error);
                    }
                    
                    $conn_back->commit();
                    echo "Updated date for return ID " . $earliest['id'] . " to " . $new_date . "\n";
                    
                } catch (Exception $e) {
                    $conn_back->rollback();
                    echo "Error: " . $e->getMessage() . "\n";
                }
            }
        }
    }
}

echo "\nScript completed.\n"; 