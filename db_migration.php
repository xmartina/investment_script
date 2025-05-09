<?php
// db_migration.php
// Script to update database structure for investment platform

// Include database configuration
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';

// Set to display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Migration Script</h1>";
echo "<p>Running migration to update database structure...</p>";

// Function to execute SQL queries safely
function executeSql($conn, $sql, $description) {
    echo "<hr><h3>$description</h3>";
    echo "<pre>$sql</pre>";
    
    try {
        if ($conn->multi_query($sql)) {
            do {
                // Store result of first query
                if ($result = $conn->store_result()) {
                    $result->free();
                }
                // Check if there are more results
            } while ($conn->more_results() && $conn->next_result());
            
            echo "<p style='color:green'>✅ Success</p>";
            return true;
        } else {
            echo "<p style='color:red'>❌ Error: " . $conn->error . "</p>";
            return false;
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>❌ Exception: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Create new tables
$transactionsSql = "
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    type ENUM('deposit', 'withdrawal', 'investment', 'earning', 'staking', 'bonus'),
    amount DECIMAL(20, 8),
    status ENUM('pending', 'successful', 'failed') DEFAULT 'pending',
    reference VARCHAR(100) UNIQUE,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);";
executeSql($conn_back, $transactionsSql, "Creating transactions table");

$depositRequestsSql = "
CREATE TABLE IF NOT EXISTS deposit_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    amount DECIMAL(20, 8),
    method VARCHAR(50),
    address TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    proof TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);";
executeSql($conn_back, $depositRequestsSql, "Creating deposit_requests table");

$depositsSql = "
CREATE TABLE IF NOT EXISTS deposits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    amount DECIMAL(20, 8),
    method VARCHAR(50),
    reference VARCHAR(100),
    confirmed_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);";
executeSql($conn_back, $depositsSql, "Creating deposits table");

$withdrawalRequestsSql = "
CREATE TABLE IF NOT EXISTS withdrawal_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    amount DECIMAL(20, 8),
    method VARCHAR(50),
    destination TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);";
executeSql($conn_back, $withdrawalRequestsSql, "Creating withdrawal_requests table");

$withdrawalsSql = "
CREATE TABLE IF NOT EXISTS withdrawals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    amount DECIMAL(20, 8),
    method VARCHAR(50),
    destination TEXT,
    reference VARCHAR(100),
    paid_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);";
executeSql($conn_back, $withdrawalsSql, "Creating withdrawals table");

// Check if investment_plans table exists
$result = $conn_back->query("SHOW TABLES LIKE 'investment_plans'");
if ($result->num_rows > 0) {
    // Table exists, update it
    $updateInvestmentPlansSql = "
    ALTER TABLE investment_plans
    ADD COLUMN min_amount DECIMAL(20, 8) AFTER name,
    ADD COLUMN max_amount DECIMAL(20, 8) AFTER min_amount,
    ADD COLUMN roi_percent DECIMAL(5, 2) AFTER max_amount,
    ADD COLUMN duration_days INT AFTER roi_percent,
    ADD COLUMN is_active BOOLEAN DEFAULT TRUE,
    CHANGE COLUMN status status VARCHAR(20);
    
    -- Convert existing active status to is_active boolean
    UPDATE investment_plans SET is_active = 1 WHERE status = 'active';
    UPDATE investment_plans SET is_active = 0 WHERE status != 'active';
    
    -- Rename min_investment to min_amount if it exists
    -- This requires checking column existence first
    ";
    
    // Check if min_investment column exists
    $result = $conn_back->query("SHOW COLUMNS FROM investment_plans LIKE 'min_investment'");
    if ($result->num_rows > 0) {
        $updateInvestmentPlansSql .= "
        UPDATE investment_plans SET min_amount = min_investment;
        ALTER TABLE investment_plans DROP COLUMN min_investment;
        ";
    }
    
    executeSql($conn_back, $updateInvestmentPlansSql, "Updating investment_plans table");
} else {
    // Create new investment_plans table
    $investmentPlansSql = "
    CREATE TABLE investment_plans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100),
        min_amount DECIMAL(20, 8),
        max_amount DECIMAL(20, 8),
        roi_percent DECIMAL(5, 2),
        duration_days INT,
        is_active BOOLEAN DEFAULT TRUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );";
    executeSql($conn_back, $investmentPlansSql, "Creating investment_plans table");
}

// Check if investments table exists
$result = $conn_back->query("SHOW TABLES LIKE 'investments'");
if ($result->num_rows > 0) {
    // Table exists, update it
    $updateInvestmentsSql = "
    ALTER TABLE investments
    ADD COLUMN roi_expected DECIMAL(20, 8) AFTER amount,
    ADD COLUMN status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    ADD COLUMN started_at DATETIME,
    ADD COLUMN ends_at DATETIME;
    
    -- Rename amount_invested to amount if it exists
    ";
    
    // Check if amount_invested column exists
    $result = $conn_back->query("SHOW COLUMNS FROM investments LIKE 'amount_invested'");
    if ($result->num_rows > 0) {
        $updateInvestmentsSql .= "
        UPDATE investments SET amount = amount_invested;
        ALTER TABLE investments DROP COLUMN amount_invested;
        ALTER TABLE investments DROP COLUMN trans_type;
        ALTER TABLE investments DROP COLUMN returns_amount;
        ALTER TABLE investments DROP COLUMN trans_id;
        ";
    }
    
    executeSql($conn_back, $updateInvestmentsSql, "Updating investments table");
} else {
    // Create new investments table
    $investmentsSql = "
    CREATE TABLE investments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        plan_id INT,
        amount DECIMAL(20, 8),
        roi_expected DECIMAL(20, 8),
        status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
        started_at DATETIME,
        ends_at DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (plan_id) REFERENCES investment_plans(id)
    );";
    executeSql($conn_back, $investmentsSql, "Creating investments table");
}

// Create staking table
$stakingSql = "
CREATE TABLE IF NOT EXISTS staking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    amount DECIMAL(20, 8),
    duration_days INT,
    reward_percent DECIMAL(5,2),
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    started_at DATETIME,
    ends_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);";
executeSql($conn_back, $stakingSql, "Creating staking table");

echo "<hr><h2>Migration completed</h2>";
echo "<p>Please review any errors above and fix them manually if needed.</p>";
echo "<p>If no errors are shown, the migration has been successful.</p>";
?> 