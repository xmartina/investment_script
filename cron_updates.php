<?php
/**
 * Cron Update Script for Exodus AI Pro Investment Platform
 * 
 * This script should be run regularly via a cron job to:
 * 1. Process investment returns when they are due
 * 2. Process staking rewards
 * 3. Update user balances accordingly
 * 4. Mark completed investments and stakings
 * 
 * Recommended cron schedule: Every hour
 * Example crontab entry: 0 * * * * /usr/bin/php /path/to/cron_updates.php
 */

// Set timezone
date_default_timezone_set('UTC');

// Start time measurement
$start_time = microtime(true);

// Set unlimited execution time for potentially long-running operations
set_time_limit(0);

// Basic security - prevent direct access via browser
if (php_sapi_name() !== 'cli' && !defined('CRON_RUNNING')) {
    die('This script can only be run from the command line or through the run_cron.php wrapper.');
}

// Initialize logging
$log_file = __DIR__ . '/logs/cron_' . date('Y-m-d') . '.log';

// Ensure log directory exists with proper error handling
$log_dir = dirname($log_file);
if (!file_exists($log_dir)) {
    try {
        if (!mkdir($log_dir, 0755, true)) {
            echo "Warning: Failed to create log directory: $log_dir" . PHP_EOL;
            $log_file = null; // Disable file logging if directory can't be created
        } else {
            echo "Created log directory: $log_dir" . PHP_EOL;
        }
    } catch (Exception $e) {
        echo "Error creating log directory: " . $e->getMessage() . PHP_EOL;
        $log_file = null; // Disable file logging on error
    }
} else {
    // Check if the directory is writable
    if (!is_writable($log_dir)) {
        echo "Warning: Log directory exists but is not writable: $log_dir" . PHP_EOL;
        $log_file = null; // Disable file logging if directory isn't writable
    }
}

/**
 * Logs a message to both console and log file
 */
function log_message($message, $level = 'INFO') {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $formatted_message = "[$timestamp] [$level] $message" . PHP_EOL;
    
    // Log to console
    echo $formatted_message;
    
    // Log to file - add safety check to ensure log_file is not empty
    if (!empty($log_file)) {
        file_put_contents($log_file, $formatted_message, FILE_APPEND);
    } else {
        echo "Warning: Log file path is empty, cannot write to log file." . PHP_EOL;
    }
}

// Debug timezone information
$original_timezone = date_default_timezone_get();
log_message("Debug: PHP timezone set to: " . $original_timezone);

// Load configuration - use the simplified cron config
require_once __DIR__ . '/include/cron_config.php';

// Check MySQL timezone
function get_mysql_timezone($conn) {
    $result = $conn->query("SELECT @@time_zone, @@system_time_zone, NOW()");
    if ($result && $row = $result->fetch_assoc()) {
        return $row;
    }
    return null;
}

// Check MySQL timezone after database connection is available
$mysql_timezone = get_mysql_timezone($conn_back);
if ($mysql_timezone) {
    log_message("Debug: MySQL timezone: " . $mysql_timezone['@@time_zone'] . 
                ", System timezone: " . $mysql_timezone['@@system_time_zone'] . 
                ", MySQL NOW(): " . $mysql_timezone['NOW()']);
    
    log_message("Debug: PHP time: " . date('Y-m-d H:i:s'));
    
    // Check if there's a timezone mismatch
    $php_time = strtotime(date('Y-m-d H:i:s'));
    $mysql_time = strtotime($mysql_timezone['NOW()']);
    $time_diff = abs($php_time - $mysql_time);
    
    if ($time_diff > 60) { // If difference is more than a minute
        log_message("WARNING: Timezone mismatch between PHP and MySQL: " . 
                   floor($time_diff/60) . " minutes " . ($time_diff % 60) . " seconds");
    }
}

/**
 * Creates a transaction record
 */
function create_transaction($conn, $user_id, $transaction_type, $amount, $currency, $status, $description = null, $roi_percentage = null) {
    $reference_id = strtoupper($transaction_type) . '-' . time() . '-' . $user_id;
    $transaction_proof_id = 'PROOF-' . time() . '-' . $user_id;
    $date_time = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO transactions (
                user_id, transaction_type, reference_id, transaction_proof_id, 
                amount, currency, status, date_time, description, roi_percentage
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssdssssd", 
        $user_id, 
        $transaction_type, 
        $reference_id, 
        $transaction_proof_id, 
        $amount, 
        $currency, 
        $status, 
        $date_time, 
        $description,
        $roi_percentage
    );
    
    if ($stmt->execute()) {
        return $conn->insert_id;
    } else {
        log_message("Error creating transaction: " . $stmt->error, "ERROR");
        return false;
    }
}

/**
 * Update user balance
 */
function update_user_balance($conn, $user_id, $balance_field, $amount) {
    // Validate balance field
    $valid_fields = ['main_balance', 'investment_balance', 'staking_balance'];
    if (!in_array($balance_field, $valid_fields)) {
        log_message("Invalid balance field: $balance_field", "ERROR");
        return false;
    }
    
    // Update user balance
    $sql = "UPDATE users SET $balance_field = $balance_field + ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $amount, $user_id);
    
    if ($stmt->execute()) {
        return true;
    } else {
        log_message("Error updating user balance: " . $stmt->error, "ERROR");
        return false;
    }
}

/**
 * Process investment returns that are due
 */
function process_investment_returns($conn) {
    log_message("Processing investment returns...");
    
    // Get all pending investment returns that are due
    $sql = "SELECT ir.*, i.plan_id, u.currency
            FROM investment_returns ir
            JOIN investments i ON ir.investment_id = i.id
            JOIN users u ON ir.user_id = u.id
            WHERE ir.status = 'pending' 
            AND ir.expected_date <= NOW()";
    
    // Debug logging
    log_message("SQL Query for investment returns: " . $sql);
    log_message("Current datetime: " . date('Y-m-d H:i:s'));
    
    $result = $conn->query($sql);
    
    if (!$result) {
        log_message("Error fetching due investment returns: " . $conn->error, "ERROR");
        return;
    }
    
    // Debug: Show total number of investment returns regardless of status/date
    $debug_sql = "SELECT COUNT(*) as total FROM investment_returns";
    $debug_result = $conn->query($debug_sql);
    $debug_row = $debug_result->fetch_assoc();
    log_message("Debug: Total investment returns in system: " . $debug_row['total']);
    
    // Debug: Show pending investment returns
    $debug_sql = "SELECT COUNT(*) as total FROM investment_returns WHERE status = 'pending'";
    $debug_result = $conn->query($debug_sql);
    $debug_row = $debug_result->fetch_assoc();
    log_message("Debug: Total pending investment returns: " . $debug_row['total']);
    
    // Debug: Show future investment returns
    $debug_sql = "SELECT COUNT(*) as total FROM investment_returns WHERE expected_date > NOW()";
    $debug_result = $conn->query($debug_sql);
    $debug_row = $debug_result->fetch_assoc();
    log_message("Debug: Investment returns with future dates: " . $debug_row['total']);
    
    // Debug: Check for a specific investment return that we know should be due
    // Check the most imminent pending investment return
    $debug_sql = "SELECT ir.id, ir.investment_id, ir.expected_date, ir.status, i.status as investment_status 
                  FROM investment_returns ir 
                  JOIN investments i ON ir.investment_id = i.id 
                  WHERE ir.status = 'pending' 
                  ORDER BY ir.expected_date ASC 
                  LIMIT 1";
    $debug_result = $conn->query($debug_sql);
    
    if ($debug_result && $debug_result->num_rows > 0) {
        $debug_row = $debug_result->fetch_assoc();
        log_message("Debug: Next pending investment return: ID=" . $debug_row['id'] . 
                    ", Investment ID=" . $debug_row['investment_id'] . 
                    ", Expected Date=" . $debug_row['expected_date'] . 
                    ", Status=" . $debug_row['status'] . 
                    ", Investment Status=" . $debug_row['investment_status']);
        
        // Compare the expected date with current date
        $expected_timestamp = strtotime($debug_row['expected_date']);
        $current_timestamp = time();
        $time_diff = $expected_timestamp - $current_timestamp;
        
        log_message("Debug: Time difference (seconds): " . $time_diff . 
                   " (" . floor($time_diff/86400) . " days, " . 
                   floor(($time_diff % 86400)/3600) . " hours)");
    } else {
        log_message("Debug: No pending investment returns found");
    }
    
    $returns_count = $result->num_rows;
    log_message("Found $returns_count due investment returns");
    
    if ($returns_count === 0) {
        return;
    }
    
    // Process each return
    while ($return = $result->fetch_assoc()) {
        $conn->begin_transaction();
        
        try {
            $user_id = $return['user_id'];
            $investment_id = $return['investment_id'];
            $return_amount = $return['return_amount'];
            $roi_percentage = $return['roi_percentage'];
            $currency = $return['currency'] ?: 'USD';
            
            // Create transaction record
            $description = "Return on investment #$investment_id, ROI: $roi_percentage%";
            $transaction_id = create_transaction(
                $conn, 
                $user_id, 
                'investment_return', 
                $return_amount, 
                $currency, 
                'completed', 
                $description,
                $roi_percentage
            );
            
            if (!$transaction_id) {
                throw new Exception("Failed to create transaction record for investment return ID: " . $return['id']);
            }
            
            // Update user's main balance
            if (!update_user_balance($conn, $user_id, 'main_balance', $return_amount)) {
                throw new Exception("Failed to update main balance for user ID: $user_id");
            }
            
            // Update investment return status to paid
            $update_sql = "UPDATE investment_returns SET 
                           status = 'paid', 
                           transaction_id = ?, 
                           paid_at = NOW() 
                           WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ii", $transaction_id, $return['id']);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update investment return status: " . $stmt->error);
            }
            
            // Mark investment as completed if this is the final return
            $update_investment_sql = "UPDATE investments SET 
                                     status = IF(ends_at <= NOW(), 'completed', status) 
                                     WHERE id = ?";
            $stmt = $conn->prepare($update_investment_sql);
            $stmt->bind_param("i", $investment_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update investment status: " . $stmt->error);
            }
            
            // Commit transaction
            $conn->commit();
            
            log_message("Processed investment return ID: " . $return['id'] . " for user ID: $user_id, amount: $return_amount $currency");
            
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            log_message("Error processing investment return ID: " . $return['id'] . " - " . $e->getMessage(), "ERROR");
        }
    }
    
    log_message("Completed processing investment returns");
}

/**
 * Debug function to force process a specific investment return
 */
function debug_force_process_return($conn, $return_id) {
    log_message("DEBUG: Attempting to force process investment return ID: " . $return_id);
    
    // Get the return details
    $sql = "SELECT ir.*, i.plan_id, u.currency
            FROM investment_returns ir
            JOIN investments i ON ir.investment_id = i.id
            JOIN users u ON ir.user_id = u.id
            WHERE ir.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $return_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        log_message("DEBUG: Investment return ID " . $return_id . " not found", "ERROR");
        return;
    }
    
    $return = $result->fetch_assoc();
    log_message("DEBUG: Found return - User ID: " . $return['user_id'] . 
                ", Investment ID: " . $return['investment_id'] . 
                ", Amount: " . $return['return_amount'] . 
                ", Status: " . $return['status'] . 
                ", Expected date: " . $return['expected_date']);
    
    // Process the return if it's pending
    if ($return['status'] === 'pending') {
        $conn->begin_transaction();
        
        try {
            $user_id = $return['user_id'];
            $investment_id = $return['investment_id'];
            $return_amount = $return['return_amount'];
            $roi_percentage = $return['roi_percentage'];
            $currency = $return['currency'] ?: 'USD';
            
            // Create transaction record
            $description = "Return on investment #$investment_id, ROI: $roi_percentage%";
            $transaction_id = create_transaction(
                $conn, 
                $user_id, 
                'investment_return', 
                $return_amount, 
                $currency, 
                'completed', 
                $description,
                $roi_percentage
            );
            
            if (!$transaction_id) {
                throw new Exception("Failed to create transaction record for investment return ID: " . $return['id']);
            }
            
            // Update user's main balance
            if (!update_user_balance($conn, $user_id, 'main_balance', $return_amount)) {
                throw new Exception("Failed to update main balance for user ID: $user_id");
            }
            
            // Update investment return status to paid
            $update_sql = "UPDATE investment_returns SET 
                           status = 'paid', 
                           transaction_id = ?, 
                           paid_at = NOW() 
                           WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ii", $transaction_id, $return['id']);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update investment return status: " . $stmt->error);
            }
            
            // Mark investment as completed if this is the final return
            $update_investment_sql = "UPDATE investments SET 
                                     status = IF(ends_at <= NOW(), 'completed', status) 
                                     WHERE id = ?";
            $stmt = $conn->prepare($update_investment_sql);
            $stmt->bind_param("i", $investment_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update investment status: " . $stmt->error);
            }
            
            // Commit transaction
            $conn->commit();
            
            log_message("DEBUG: Successfully processed investment return ID: " . $return['id']);
            
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            log_message("DEBUG ERROR: " . $e->getMessage(), "ERROR");
        }
    } else {
        log_message("DEBUG: Return ID " . $return_id . " is not pending (status: " . $return['status'] . ")", "ERROR");
    }
}

/**
 * Process staking rewards that are due
 */
function process_staking_rewards($conn) {
    log_message("Processing staking rewards...");
    
    // Get all pending staking rewards that are due
    $sql = "SELECT sr.*, s.plan_id, s.is_compounding, u.currency
            FROM staking_rewards sr
            JOIN staking s ON sr.staking_id = s.id
            JOIN users u ON sr.user_id = u.id
            WHERE sr.status = 'pending' 
            AND sr.expected_date <= NOW()";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        log_message("Error fetching due staking rewards: " . $conn->error, "ERROR");
        return;
    }
    
    $rewards_count = $result->num_rows;
    log_message("Found $rewards_count due staking rewards");
    
    if ($rewards_count === 0) {
        return;
    }
    
    // Process each reward
    while ($reward = $result->fetch_assoc()) {
        $conn->begin_transaction();
        
        try {
            $user_id = $reward['user_id'];
            $staking_id = $reward['staking_id'];
            $reward_amount = $reward['reward_amount'];
            $is_compounding = $reward['is_compounding'];
            $currency = $reward['currency'] ?: 'USD';
            
            if ($is_compounding) {
                // For compounding stakes, add the reward to the staking amount
                // and create a new pending reward
                
                // Update staking record to increase amount and update earned_reward
                $update_sql = "UPDATE staking SET 
                               amount = amount + ?, 
                               earned_reward = earned_reward + ?,
                               last_compound_at = NOW() 
                               WHERE id = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("ddi", $reward_amount, $reward_amount, $staking_id);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update staking for compounding: " . $stmt->error);
                }
                
                // Update reward status to reinvested
                $update_reward_sql = "UPDATE staking_rewards SET 
                                     status = 'reinvested', 
                                     claimed_at = NOW() 
                                     WHERE id = ?";
                $stmt = $conn->prepare($update_reward_sql);
                $stmt->bind_param("i", $reward['id']);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update staking reward status: " . $stmt->error);
                }
                
                // Create transaction record
                $description = "Reinvested staking reward for stake #$staking_id";
                $transaction_id = create_transaction(
                    $conn, 
                    $user_id, 
                    'staking_compound', 
                    $reward_amount, 
                    $currency, 
                    'completed', 
                    $description
                );
                
                if (!$transaction_id) {
                    throw new Exception("Failed to create transaction record for compounding staking reward ID: " . $reward['id']);
                }
                
                log_message("Processed compounding staking reward ID: " . $reward['id'] . " for user ID: $user_id, amount: $reward_amount $currency");
            } else {
                // For non-compounding stakes, add the reward to main balance
                
                // Create transaction record
                $description = "Staking reward for stake #$staking_id";
                $transaction_id = create_transaction(
                    $conn, 
                    $user_id, 
                    'staking_reward', 
                    $reward_amount, 
                    $currency, 
                    'completed', 
                    $description
                );
                
                if (!$transaction_id) {
                    throw new Exception("Failed to create transaction record for staking reward ID: " . $reward['id']);
                }
                
                // Update user's main balance
                if (!update_user_balance($conn, $user_id, 'main_balance', $reward_amount)) {
                    throw new Exception("Failed to update main balance for user ID: $user_id");
                }
                
                // Update staking record to increase earned_reward
                $update_sql = "UPDATE staking SET 
                               earned_reward = earned_reward + ? 
                               WHERE id = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("di", $reward_amount, $staking_id);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update staking earned reward: " . $stmt->error);
                }
                
                // Update reward status to claimed
                $update_reward_sql = "UPDATE staking_rewards SET 
                                     status = 'claimed', 
                                     transaction_id = ?,
                                     claimed_at = NOW() 
                                     WHERE id = ?";
                $stmt = $conn->prepare($update_reward_sql);
                $stmt->bind_param("ii", $transaction_id, $reward['id']);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update staking reward status: " . $stmt->error);
                }
                
                log_message("Processed staking reward ID: " . $reward['id'] . " for user ID: $user_id, amount: $reward_amount $currency");
            }
            
            // Commit transaction
            $conn->commit();
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            log_message("Error processing staking reward ID: " . $reward['id'] . " - " . $e->getMessage(), "ERROR");
        }
    }
    
    log_message("Completed processing staking rewards");
}

/**
 * Generate future staking rewards for active stakes
 */
function generate_staking_rewards($conn) {
    log_message("Generating upcoming staking rewards...");
    
    // Find active stakings that need to have rewards scheduled
    $sql = "SELECT s.*, sp.reward_percent, u.currency
            FROM staking s
            JOIN staking_plans sp ON s.plan_id = sp.id
            JOIN users u ON s.user_id = u.id
            WHERE s.status = 'active'
            AND (
                -- No rewards generated yet
                NOT EXISTS (SELECT 1 FROM staking_rewards sr WHERE sr.staking_id = s.id)
                OR
                -- Last reward was generated more than 24 hours ago
                (SELECT MAX(sr.expected_date) FROM staking_rewards sr WHERE sr.staking_id = s.id) < DATE_ADD(NOW(), INTERVAL 7 DAY)
            )";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        log_message("Error fetching active stakings: " . $conn->error, "ERROR");
        return;
    }
    
    $stakings_count = $result->num_rows;
    log_message("Found $stakings_count stakings needing reward scheduling");
    
    if ($stakings_count === 0) {
        return;
    }
    
    // Process each staking
    while ($staking = $result->fetch_assoc()) {
        $conn->begin_transaction();
        
        try {
            $staking_id = $staking['id'];
            $user_id = $staking['user_id'];
            $staking_amount = $staking['amount'];
            $reward_percent = $staking['reward_percent'];
            $currency = $staking['currency'] ?: 'USD';
            
            // Get most recent expected date for this staking
            $last_date_sql = "SELECT MAX(expected_date) as last_date FROM staking_rewards WHERE staking_id = ?";
            $stmt = $conn->prepare($last_date_sql);
            $stmt->bind_param("i", $staking_id);
            $stmt->execute();
            $last_date_result = $stmt->get_result()->fetch_assoc();
            
            // Start from the day after the last reward or from the started_at date
            if ($last_date_result['last_date']) {
                $next_date = date('Y-m-d H:i:s', strtotime($last_date_result['last_date'] . ' +1 day'));
            } else {
                $next_date = date('Y-m-d H:i:s', strtotime($staking['started_at'] . ' +1 day'));
            }
            
            // If stake has no upcoming rewards, generate them for the next 7 days
            // or until the stake ends, whichever comes first
            $end_date = $staking['ends_at'];
            $days_to_generate = min(7, (strtotime($end_date) - strtotime($next_date)) / 86400);
            
            // Daily reward calculation (annual % / 365)
            $daily_reward_rate = $reward_percent / 365;
            $daily_reward_amount = ($staking_amount * $daily_reward_rate) / 100;
            
            log_message("Scheduling rewards for staking ID: $staking_id, daily amount: $daily_reward_amount $currency");
            
            // Generate reward entries
            for ($i = 0; $i < $days_to_generate; $i++) {
                $reward_date = date('Y-m-d H:i:s', strtotime($next_date . " +$i days"));
                
                // Skip if this would be past the end date
                if (strtotime($reward_date) > strtotime($end_date)) {
                    continue;
                }
                
                // Insert reward record
                $insert_sql = "INSERT INTO staking_rewards 
                               (staking_id, user_id, reward_amount, status, expected_date, created_at) 
                               VALUES (?, ?, ?, 'pending', ?, NOW())";
                $stmt = $conn->prepare($insert_sql);
                $stmt->bind_param("iidd", $staking_id, $user_id, $daily_reward_amount, $reward_date);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to insert staking reward: " . $stmt->error);
                }
                
                log_message("Created staking reward for date: $reward_date, staking ID: $staking_id");
            }
            
            // Commit transaction
            $conn->commit();
            
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            log_message("Error generating staking rewards for staking ID: " . $staking['id'] . " - " . $e->getMessage(), "ERROR");
        }
    }
    
    log_message("Completed generating staking rewards");
}

/**
 * Update completed stakings
 */
function update_completed_stakings($conn) {
    log_message("Updating completed stakings...");
    
    // Find active stakings that have passed their end date
    $sql = "UPDATE staking 
            SET status = 'completed' 
            WHERE status = 'active' 
            AND ends_at <= NOW()";
    
    if ($conn->query($sql)) {
        $affected_rows = $conn->affected_rows;
        log_message("Updated $affected_rows stakings to completed status");
    } else {
        log_message("Error updating completed stakings: " . $conn->error, "ERROR");
    }
}

/**
 * Check and update when unstaking becomes available
 */
function update_unstake_availability($conn) {
    log_message("Updating unstake availability...");
    
    // Find active stakings where unstaking should now be available
    $sql = "SELECT s.*, sp.lock_period_days
            FROM staking s
            JOIN staking_plans sp ON s.plan_id = sp.id
            WHERE s.status = 'active'
            AND s.unstake_available_at IS NULL
            AND DATE_ADD(s.started_at, INTERVAL sp.lock_period_days DAY) <= NOW()";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        log_message("Error fetching stakings for unstake availability update: " . $conn->error, "ERROR");
        return;
    }
    
    $stakings_count = $result->num_rows;
    log_message("Found $stakings_count stakings to update unstake availability");
    
    if ($stakings_count === 0) {
        return;
    }
    
    // Process each staking
    while ($staking = $result->fetch_assoc()) {
        $staking_id = $staking['id'];
        $lock_period_days = $staking['lock_period_days'];
        
        // Calculate when unstaking becomes available
        $unstake_available_at = date('Y-m-d H:i:s', strtotime($staking['started_at'] . " +$lock_period_days days"));
        
        // Update staking record
        $update_sql = "UPDATE staking 
                       SET unstake_available_at = ? 
                       WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $unstake_available_at, $staking_id);
        
        if ($stmt->execute()) {
            log_message("Updated unstake availability for staking ID: $staking_id to $unstake_available_at");
        } else {
            log_message("Error updating unstake availability for staking ID: $staking_id - " . $stmt->error, "ERROR");
        }
    }
    
    log_message("Completed updating unstake availability");
}

/**
 * Update status of completed investments
 */
function update_completed_investments($conn) {
    log_message("Updating completed investments...");
    
    // Find active investments that have passed their end date
    $sql = "UPDATE investments 
            SET status = 'completed' 
            WHERE status = 'active' 
            AND ends_at <= NOW()";
    
    if ($conn->query($sql)) {
        $affected_rows = $conn->affected_rows;
        log_message("Updated $affected_rows investments to completed status");
    } else {
        log_message("Error updating completed investments: " . $conn->error, "ERROR");
    }
}

/**
 * Process referral commissions
 */
function process_referral_commissions($conn) {
    log_message("Processing pending referral commissions...");
    
    // Get all pending referral commissions
    $sql = "SELECT rc.*, u.currency
            FROM referral_commissions rc
            JOIN users u ON rc.referrer_id = u.id
            WHERE rc.status = 'pending'";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        log_message("Error fetching pending referral commissions: " . $conn->error, "ERROR");
        return;
    }
    
    $commissions_count = $result->num_rows;
    log_message("Found $commissions_count pending referral commissions");
    
    if ($commissions_count === 0) {
        return;
    }
    
    // Process each commission
    while ($commission = $result->fetch_assoc()) {
        $conn->begin_transaction();
        
        try {
            $referrer_id = $commission['referrer_id'];
            $referred_id = $commission['referred_id'];
            $amount = $commission['amount'];
            $source_type = $commission['source_type'];
            $source_id = $commission['source_id'];
            $currency = $commission['currency'] ?: 'USD';
            
            // Create transaction record
            $description = "Referral commission from User #$referred_id for $source_type #$source_id";
            $transaction_id = create_transaction(
                $conn, 
                $referrer_id, 
                'referral', 
                $amount, 
                $currency, 
                'completed', 
                $description
            );
            
            if (!$transaction_id) {
                throw new Exception("Failed to create transaction record for referral commission ID: " . $commission['id']);
            }
            
            // Update user's main balance
            if (!update_user_balance($conn, $referrer_id, 'main_balance', $amount)) {
                throw new Exception("Failed to update main balance for referrer ID: $referrer_id");
            }
            
            // Update user's referral bonus earned
            $update_bonus_sql = "UPDATE users 
                                SET referral_bonus_earned = referral_bonus_earned + ? 
                                WHERE id = ?";
            $stmt = $conn->prepare($update_bonus_sql);
            $stmt->bind_param("di", $amount, $referrer_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update referral bonus earned: " . $stmt->error);
            }
            
            // Update commission status to paid
            $update_sql = "UPDATE referral_commissions SET 
                           status = 'paid', 
                           paid_at = NOW() 
                           WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("i", $commission['id']);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update referral commission status: " . $stmt->error);
            }
            
            // Commit transaction
            $conn->commit();
            
            log_message("Processed referral commission ID: " . $commission['id'] . " for referrer ID: $referrer_id, amount: $amount $currency");
            
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            log_message("Error processing referral commission ID: " . $commission['id'] . " - " . $e->getMessage(), "ERROR");
        }
    }
    
    log_message("Completed processing referral commissions");
}

// Main execution
try {
    log_message("Starting cron updates...");
    
    // Find the first pending investment return for debugging
    $debug_sql = "SELECT id FROM investment_returns WHERE status = 'pending' ORDER BY expected_date ASC LIMIT 1";
    $debug_result = $conn_back->query($debug_sql);
    
    if ($debug_result && $debug_result->num_rows > 0) {
        $debug_row = $debug_result->fetch_assoc();
        $return_id = $debug_row['id'];
        log_message("Debug: Found pending investment return with ID: " . $return_id);
        
        // Uncomment the following line to force process this specific return for debugging
        // debug_force_process_return($conn_back, $return_id);
    }
    
    // Process various update tasks
    process_investment_returns($conn_back);
    process_staking_rewards($conn_back);
    generate_staking_rewards($conn_back);
    update_completed_stakings($conn_back);
    update_unstake_availability($conn_back);
    update_completed_investments($conn_back);
    process_referral_commissions($conn_back);
    
    // Calculate execution time
    $execution_time = round(microtime(true) - $start_time, 2);
    log_message("Cron updates completed in $execution_time seconds");
    
} catch (Exception $e) {
    log_message("Critical error in cron updates: " . $e->getMessage(), "ERROR");
}

// Don't close the database connection if KEEP_DB_OPEN constant is defined
// This allows the auto_fix_dates.php script to reuse the connection
if (!defined('KEEP_DB_OPEN')) {
    // Close database connection
    $conn_back->close();
    log_message("Database connection closed");
} 