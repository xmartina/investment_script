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

// Check if investment_plans table has roi_percent column
$checkRoiPercentColumnSql = "SHOW COLUMNS FROM investment_plans LIKE 'roi_percent'";
$roiPercentColumnExists = $conn_back->query($checkRoiPercentColumnSql)->num_rows > 0;

if (!$roiPercentColumnExists) {
    echo "<p style='color:red'>❌ Warning: The investment_plans table does not have a roi_percent column. Please add this column first.</p>";
}

// Step 1: Create investment_returns table (without foreign key constraints since investments table is MyISAM)
$createTableSql = "
DROP TABLE IF EXISTS investment_returns;

CREATE TABLE investment_returns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    investment_id INT NOT NULL,
    user_id INT NOT NULL,
    return_amount DECIMAL(20, 8) NOT NULL,
    roi_percentage DECIMAL(10, 2) NOT NULL,
    expected_date DATETIME NOT NULL,
    status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    transaction_id INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    paid_at DATETIME DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
";
executeSql($conn_back, $createTableSql, "Recreating investment_returns table");

// Step 2: Add a trigger to automatically create return records when an investment is created
$createTriggerSql = "
DROP TRIGGER IF EXISTS after_investment_insert;

CREATE TRIGGER after_investment_insert
AFTER INSERT ON investments
FOR EACH ROW
BEGIN
    -- Get ROI percentage from investment_plans
    DECLARE plan_roi_percent DECIMAL(10, 2);
    
    SELECT roi_percent INTO plan_roi_percent
    FROM investment_plans
    WHERE id = NEW.plan_id
    LIMIT 1;
    
    -- Insert a record in investment_returns table
    INSERT INTO investment_returns 
    (investment_id, user_id, return_amount, roi_percentage, expected_date, status, created_at)
    VALUES
    (NEW.id, NEW.user_id, NEW.roi_expected, IFNULL(plan_roi_percent, 0), NEW.ends_at, 'pending', NOW());
END;
";
executeSql($conn_back, $createTriggerSql, "Creating trigger for automatic return record creation");

// First, check if investments table already has ROI percentage column
$checkRoiColumnSql = "SHOW COLUMNS FROM investments LIKE 'roi_percentage'";
$roiColumnExists = $conn_back->query($checkRoiColumnSql)->num_rows > 0;

// If roi_percentage column doesn't exist in investments table, add it
if (!$roiColumnExists) {
    $addRoiColumnSql = "
    ALTER TABLE investments 
    ADD COLUMN roi_percentage DECIMAL(10, 2) AFTER roi_expected;
    ";
    
    if ($roiPercentColumnExists) {
        $addRoiColumnSql .= "
        -- Update existing records to set roi_percentage from investment_plans
        UPDATE investments i
        JOIN investment_plans p ON i.plan_id = p.id
        SET i.roi_percentage = p.roi_percent;
        ";
    }
    
    executeSql($conn_back, $addRoiColumnSql, "Adding roi_percentage column to investments table");
}

// Step 3: Populate returns for existing investments
// Check if we have investment_plans table with roi_percent column
if ($roiPercentColumnExists) {
    // Populate with roi_percent from investment_plans
    $populateReturnsSQL = "
    INSERT INTO investment_returns 
    (investment_id, user_id, return_amount, roi_percentage, expected_date, status, created_at)
    SELECT 
        i.id, 
        i.user_id, 
        i.roi_expected,
        p.roi_percent,
        i.ends_at, 
        'pending', 
        NOW()
    FROM 
        investments i
    LEFT JOIN 
        investment_returns ir ON i.id = ir.investment_id
    JOIN
        investment_plans p ON i.plan_id = p.id
    WHERE 
        ir.id IS NULL AND i.status = 'active';
    ";
} else {
    // Populate with default value for roi_percentage
    $populateReturnsSQL = "
    INSERT INTO investment_returns 
    (investment_id, user_id, return_amount, roi_percentage, expected_date, status, created_at)
    SELECT 
        i.id, 
        i.user_id, 
        i.roi_expected,
        0,
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
}
executeSql($conn_back, $populateReturnsSQL, "Populating returns for existing investments");

// Check if the transactions table exists
$checkTransactionsTable = $conn_back->query("SHOW TABLES LIKE 'transactions'");
if ($checkTransactionsTable->num_rows > 0) {
    // Check if roi_percentage column exists in transactions table
    $checkTransRoiColumnSql = "SHOW COLUMNS FROM transactions LIKE 'roi_percentage'";
    $transRoiColumnExists = $conn_back->query($checkTransRoiColumnSql)->num_rows > 0;
    
    // If roi_percentage column doesn't exist in transactions table, add it
    if (!$transRoiColumnExists) {
        $addTransRoiColumnSql = "
        ALTER TABLE transactions 
        ADD COLUMN roi_percentage DECIMAL(10, 2) AFTER amount;
        ";
        executeSql($conn_back, $addTransRoiColumnSql, "Adding roi_percentage column to transactions table");
    }
}

// Step 4: Update the investments page to display returns
echo "<hr><h2>Setup completed</h2>";
echo "<p>The investment_returns table has been created and populated with data from existing investments.</p>";
echo "<p>A trigger has been set up to automatically create return records when new investments are made.</p>";
echo "<p>ROI percentage field has been added to the relevant tables.</p>";
echo "<h3>Next Steps:</h3>";
echo "<ul>";
echo "<li>Update the investments.php page to display returns information</li>";
echo "<li>Create a cron job to process pending returns when they reach their expected date</li>";
echo "</ul>";
?> 