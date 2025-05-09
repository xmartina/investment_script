<?php
// update_investment_returns.php
// Script to create a new investment_returns table and populate it with data

// Include database configuration
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';

// Set to display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Investment Returns Table Setup</h1>";
echo "<p>This script will create and populate the investment_returns table.</p>";

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

// Check if investment_plans table exists, create it if it doesn't
$checkPlansTable = $conn_back->query("SHOW TABLES LIKE 'investment_plans'");
if ($checkPlansTable->num_rows == 0) {
    $createPlansSql = "
    CREATE TABLE investment_plans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100),
        min_amount DECIMAL(20, 8),
        max_amount DECIMAL(20, 8),
        roi_percent DECIMAL(5, 2),
        duration_days INT,
        is_active BOOLEAN DEFAULT TRUE,
        status VARCHAR(20) DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );";
    executeSql($conn_back, $createPlansSql, "Creating investment_plans table");
}

// Check if investments table exists, create it if it doesn't
$checkInvestmentsTable = $conn_back->query("SHOW TABLES LIKE 'investments'");
if ($checkInvestmentsTable->num_rows == 0) {
    $createInvestmentsSql = "
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
    executeSql($conn_back, $createInvestmentsSql, "Creating investments table");
}

// Step 1: Create investment_returns table (without foreign key constraints since investments table is MyISAM)
$createTableSql = "
CREATE TABLE IF NOT EXISTS investment_returns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    investment_id INT NOT NULL,
    user_id INT NOT NULL,
    return_amount DECIMAL(20, 8) NOT NULL,
    expected_date DATETIME NOT NULL,
    status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    transaction_id INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    paid_at DATETIME DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
";
executeSql($conn_back, $createTableSql, "Creating investment_returns table");

// Step 2: Add a trigger to automatically create return records when an investment is created
$createTriggerSql = "
DROP TRIGGER IF EXISTS after_investment_insert;

CREATE TRIGGER after_investment_insert
AFTER INSERT ON investments
FOR EACH ROW
BEGIN
    -- Insert a record in investment_returns table
    INSERT INTO investment_returns 
    (investment_id, user_id, return_amount, expected_date, status, created_at)
    VALUES
    (NEW.id, NEW.user_id, NEW.roi_expected, NEW.ends_at, 'pending', NOW());
END;
";
executeSql($conn_back, $createTriggerSql, "Creating trigger for automatic return record creation");

// Step 3: Populate returns for existing investments
$populateReturnsSQL = "
INSERT INTO investment_returns 
(investment_id, user_id, return_amount, expected_date, status, created_at)
SELECT 
    i.id, 
    i.user_id, 
    i.roi_expected, 
    i.ends_at, 
    'pending', 
    NOW()
FROM 
    investments i
LEFT JOIN 
    investment_returns ir ON i.id = ir.investment_id
WHERE 
    ir.id IS NULL AND i.status = 'active';
";
executeSql($conn_back, $populateReturnsSQL, "Populating returns for existing investments");

// Step 4: Update the investments page to display returns
echo "<hr><h2>Setup completed</h2>";
echo "<p>The investment_returns table has been created and populated with data from existing investments.</p>";
echo "<p>A trigger has been set up to automatically create return records when new investments are made.</p>";
echo "<h3>Next Steps:</h3>";
echo "<ul>";
echo "<li>Update the investments.php page to display returns information</li>";
echo "<li>Create a cron job to process pending returns when they reach their expected date</li>";
echo "</ul>";
?> 