<?php
// process_referral_commission.php - Call this script when investments generate profit

// Include database configuration
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';

// Set to display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Function to process referral commission when an investment generates profit
 * 
 * @param int $user_id - The user ID of the person who made the investment
 * @param int $investment_id - The ID of the investment that generated profit
 * @param float $profit_amount - The amount of profit generated
 * @param string $source_type - Type of source (investment, staking, etc.)
 * @return bool - Whether the commission was processed successfully
 */
function processReferralCommission($user_id, $source_id, $profit_amount, $source_type = 'investment') {
    global $conn_back;
    
    // Referral commission percentage (5%)
    $commission_percentage = 0.05;
    
    // Begin transaction
    $conn_back->begin_transaction();
    
    try {
        // Get referrer ID if exists
        $stmt = $conn_back->prepare("SELECT referred_by FROM users WHERE id = ? AND referred_by IS NOT NULL");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            // User wasn't referred by anyone
            $conn_back->commit();
            return false;
        }
        
        $referrer_id = $result->fetch_assoc()['referred_by'];
        $stmt->close();
        
        // Calculate commission amount (5% of profit)
        $commission_amount = $profit_amount * $commission_percentage;
        
        // Round to 2 decimal places
        $commission_amount = round($commission_amount, 2);
        
        // Skip if commission amount is too small
        if ($commission_amount < 0.01) {
            $conn_back->commit();
            return false;
        }
        
        // Check if a commission record already exists for this investment and referrer
        $stmt = $conn_back->prepare("
            SELECT id FROM referral_commissions 
            WHERE referrer_id = ? AND referred_id = ? AND source_id = ? AND source_type = ?
        ");
        $stmt->bind_param("iiis", $referrer_id, $user_id, $source_id, $source_type);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            // Commission already processed for this investment
            $stmt->close();
            $conn_back->commit();
            return false;
        }
        $stmt->close();
        
        // Insert commission record
        $stmt = $conn_back->prepare("
            INSERT INTO referral_commissions 
            (referrer_id, referred_id, amount, source_type, source_id, status, created_at) 
            VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->bind_param("iidsi", $referrer_id, $user_id, $commission_amount, $source_type, $source_id);
        $stmt->execute();
        $commission_id = $stmt->insert_id;
        $stmt->close();
        
        // Update referrer's referral_bonus_earned amount
        $stmt = $conn_back->prepare("
            UPDATE users 
            SET referral_bonus_earned = referral_bonus_earned + ? 
            WHERE id = ?
        ");
        $stmt->bind_param("di", $commission_amount, $referrer_id);
        $stmt->execute();
        $stmt->close();
        
        // Add to referrer's main balance
        $stmt = $conn_back->prepare("
            UPDATE users 
            SET main_balance = main_balance + ? 
            WHERE id = ?
        ");
        $stmt->bind_param("di", $commission_amount, $referrer_id);
        $stmt->execute();
        $stmt->close();
        
        // Create transaction record
        $transaction_id = generateTransactionId();
        $description = "Referral commission from user ID {$user_id}'s {$source_type} (ID: {$source_id})";
        
        $stmt = $conn_back->prepare("
            INSERT INTO transactions 
            (user_id, type, amount, status, reference, description, created_at) 
            VALUES (?, 'referral', ?, 'successful', ?, ?, NOW())
        ");
        $stmt->bind_param("idss", $referrer_id, $commission_amount, $transaction_id, $description);
        $stmt->execute();
        $stmt->close();
        
        // Update commission status to paid
        $stmt = $conn_back->prepare("
            UPDATE referral_commissions 
            SET status = 'paid', paid_at = NOW() 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $commission_id);
        $stmt->execute();
        $stmt->close();
        
        // Commit transaction
        $conn_back->commit();
        return true;
        
    } catch (Exception $e) {
        // Rollback on error
        $conn_back->rollback();
        error_log("Error processing referral commission: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate a unique transaction ID
 * 
 * @return string - Unique transaction ID
 */
function generateTransactionId() {
    $prefix = 'REF';
    $uniqueId = uniqid($prefix, true);
    return strtoupper(substr($uniqueId, 0, 16));
}

// Example usage:
// When an investment generates profit, call:
// processReferralCommission($user_id, $investment_id, $profit_amount, 'investment');

// When a staking generates profit, call:
// processReferralCommission($user_id, $staking_id, $profit_amount, 'staking');
?> 