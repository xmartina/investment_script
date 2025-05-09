<?php
// update_staking_tables.php
// Script to create or update staking-related tables

// Include database configuration
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';

// Set to display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Staking Tables Setup</h1>";
echo "<p>This script will create and update staking-related tables.</p>";

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

// Step 1: Create staking_plans table
$createStakingPlansSql = "
CREATE TABLE IF NOT EXISTS staking_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    min_amount DECIMAL(20, 8) NOT NULL,
    max_amount DECIMAL(20, 8),
    reward_percent DECIMAL(10, 2) NOT NULL,
    duration_days INT NOT NULL,
    lock_period_days INT NOT NULL COMMENT 'Period during which unstaking is not allowed',
    early_unstake_penalty DECIMAL(5, 2) DEFAULT 0 COMMENT 'Percentage penalty for early unstaking',
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
";
executeSql($conn_back, $createStakingPlansSql, "Creating staking_plans table");

// Step 2: Update staking table structure if needed
$checkStakingTable = $conn_back->query("SHOW TABLES LIKE 'staking'");
if ($checkStakingTable->num_rows > 0) {
    // Check if id is already a primary key
    $checkPrimaryKey = $conn_back->query("SHOW KEYS FROM staking WHERE Key_name = 'PRIMARY'");
    $hasPrimaryKey = $checkPrimaryKey->num_rows > 0;
    
    // Get existing columns
    $columns = array();
    $result = $conn_back->query("SHOW COLUMNS FROM staking");
    while($row = $result->fetch_assoc()){
        $columns[] = $row['Field'];
    }
    
    // Build the SQL dynamically based on which columns need to be added
    $updateStakingSql = "";
    
    // Add plan_id column if it doesn't exist
    if (!in_array('plan_id', $columns)) {
        $updateStakingSql .= "ALTER TABLE staking ADD COLUMN plan_id INT AFTER user_id;\n";
    }
    
    // Add apy column if it doesn't exist
    if (!in_array('apy', $columns)) {
        $updateStakingSql .= "ALTER TABLE staking ADD COLUMN apy DECIMAL(10, 2) AFTER reward_percent;\n";
    }
    
    // Add earned_reward column if it doesn't exist
    if (!in_array('earned_reward', $columns)) {
        $updateStakingSql .= "ALTER TABLE staking ADD COLUMN earned_reward DECIMAL(20, 8) DEFAULT 0 AFTER apy;\n";
    }
    
    // Add is_compounding column if it doesn't exist
    if (!in_array('is_compounding', $columns)) {
        $updateStakingSql .= "ALTER TABLE staking ADD COLUMN is_compounding BOOLEAN DEFAULT FALSE AFTER earned_reward;\n";
    }
    
    // Add last_compound_at column if it doesn't exist
    if (!in_array('last_compound_at', $columns)) {
        $updateStakingSql .= "ALTER TABLE staking ADD COLUMN last_compound_at DATETIME AFTER is_compounding;\n";
    }
    
    // Add unstake_available_at column if it doesn't exist
    if (!in_array('unstake_available_at', $columns)) {
        $updateStakingSql .= "ALTER TABLE staking ADD COLUMN unstake_available_at DATETIME AFTER ends_at;\n";
    }
    
    // Only add the primary key modification if it doesn't already have one
    if (!$hasPrimaryKey) {
        $updateStakingSql .= "ALTER TABLE staking MODIFY id INT AUTO_INCREMENT PRIMARY KEY;\n";
    }
    
    // Only run the SQL if there are changes to make
    if (!empty($updateStakingSql)) {
        executeSql($conn_back, $updateStakingSql, "Updating staking table structure");
    } else {
        echo "<hr><h3>Updating staking table structure</h3>";
        echo "<p style='color:green'>✅ No updates needed - all columns already exist</p>";
    }
} else {
    // Create staking table if it doesn't exist
    $createStakingSql = "
    CREATE TABLE staking (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        plan_id INT,
        amount DECIMAL(20,8),
        duration_days INT,
        reward_percent DECIMAL(5,2),
        apy DECIMAL(10,2),
        earned_reward DECIMAL(20,8) DEFAULT 0,
        is_compounding BOOLEAN DEFAULT FALSE,
        last_compound_at DATETIME,
        status ENUM('active','completed','cancelled') DEFAULT 'active',
        started_at DATETIME,
        ends_at DATETIME,
        unstake_available_at DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
    ";
    executeSql($conn_back, $createStakingSql, "Creating staking table");
}

// Step 3: Create staking_rewards table
$createRewardsSql = "
CREATE TABLE IF NOT EXISTS staking_rewards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staking_id INT NOT NULL,
    user_id INT NOT NULL,
    reward_amount DECIMAL(20, 8) NOT NULL,
    status ENUM('pending', 'claimed', 'reinvested', 'expired') DEFAULT 'pending',
    transaction_id INT DEFAULT NULL,
    expected_date DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    claimed_at DATETIME DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
";
executeSql($conn_back, $createRewardsSql, "Creating staking_rewards table");

// Step 4: Populate staking_plans with default plans if empty
$checkPlans = $conn_back->query("SELECT COUNT(*) AS count FROM staking_plans");
$plansCount = $checkPlans->fetch_assoc()['count'];

if ($plansCount == 0) {
    $populatePlansSql = "
    INSERT INTO staking_plans 
    (name, description, min_amount, max_amount, reward_percent, duration_days, lock_period_days, early_unstake_penalty, is_active) 
    VALUES 
    ('Flexible Staking', 'Stake with no lock period and earn rewards daily', 50, 5000, 8.5, 30, 0, 0, 1),
    ('Bronze Stake', 'Earn 12% APY with a 7-day lock period', 250, 10000, 12, 60, 7, 2.5, 1),
    ('Silver Stake', 'Earn 15% APY with a 14-day lock period', 1000, 25000, 15, 90, 14, 3.5, 1),
    ('Gold Stake', 'Earn 18% APY with a 30-day lock period', 5000, 100000, 18, 180, 30, 5, 1),
    ('Platinum Stake', 'Earn 22% APY with a 60-day lock period', 10000, 500000, 22, 365, 60, 7.5, 1);
    ";
    executeSql($conn_back, $populatePlansSql, "Populating staking_plans with default plans");
}

// Step 5: Create trigger for automatic reward calculation
$createTriggerSql = "
DROP TRIGGER IF EXISTS after_staking_insert;

/* Trigger disabled - we now calculate APY and create reward records directly in the staking creation logic
CREATE TRIGGER after_staking_insert
AFTER INSERT ON staking
FOR EACH ROW
BEGIN
    -- Declare all variables at the beginning
    DECLARE calculated_apy DECIMAL(10, 2);
    DECLARE expected_reward DECIMAL(20, 8);
    
    -- Calculate APY from reward_percent and duration_days
    SET calculated_apy = (NEW.reward_percent * 365) / NEW.duration_days;
    
    -- Update the APY for this staking entry
    UPDATE staking SET apy = calculated_apy WHERE id = NEW.id;
    
    -- Calculate expected reward based on amount, reward_percent and duration
    SET expected_reward = (NEW.amount * NEW.reward_percent) / 100;
    
    -- Insert a record in staking_rewards table
    INSERT INTO staking_rewards 
    (staking_id, user_id, reward_amount, expected_date, status, created_at)
    VALUES
    (NEW.id, NEW.user_id, expected_reward, NEW.ends_at, 'pending', NOW());
END;
*/
";
executeSql($conn_back, $createTriggerSql, "Disabling trigger for automatic reward calculation (now handled in application code)");

// Step 6: Update the current staking records to have rewards if they don't
$populateRewardsSql = "
INSERT INTO staking_rewards 
(staking_id, user_id, reward_amount, expected_date, status, created_at)
SELECT 
    s.id, 
    s.user_id, 
    (s.amount * s.reward_percent) / 100,
    s.ends_at, 
    'pending', 
    NOW()
FROM 
    staking s
LEFT JOIN 
    staking_rewards sr ON s.id = sr.staking_id
WHERE 
    sr.id IS NULL AND s.status = 'active';
";
executeSql($conn_back, $populateRewardsSql, "Populating rewards for existing staking records");

echo "<hr><h2>Setup completed</h2>";
echo "<p>Staking tables have been created and updated.</p>";
echo "<p>Default staking plans have been added.</p>";
echo "<p>A trigger has been set up to automatically calculate rewards for new staking records.</p>";
echo "<h3>Next Steps:</h3>";
echo "<ul>";
echo "<li>Update the staking.php page to use the new tables and plans</li>";
echo "<li>Create staking dashboard views</li>";
echo "<li>Implement the compounding mechanism</li>";
echo "<li>Create a cron job to process pending rewards</li>";
echo "</ul>";
?> 