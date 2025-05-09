<?php
// update_investment_plans.php
// Script to clean up and organize the investment_plans table

// Include database configuration
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';

// Set to display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Investment Plans Table Update</h1>";
echo "<p>This script will organize the investment_plans table structure.</p>";

// Function to execute SQL queries safely
function executeSql($conn_back, $sql, $description) {
    echo "<hr><h3>$description</h3>";
    echo "<pre>$sql</pre>";
    
    try {
        if ($conn_back->multi_query($sql)) {
            do {
                // Store result of first query
                if ($result = $conn_back->store_result()) {
                    $result->free();
                }
                // Check if there are more results
            } while ($conn_back->more_results() && $conn_back->next_result());
            
            echo "<p style='color:green'>✅ Success</p>";
            return true;
        } else {
            echo "<p style='color:red'>❌ Error: " . $conn_back->error . "</p>";
            return false;
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>❌ Exception: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Step 1: Back up current data
echo "<h3>Step 1: Creating backup of investment_plans table</h3>";
$backupSql = "CREATE TABLE IF NOT EXISTS investment_plans_backup LIKE investment_plans;
              INSERT INTO investment_plans_backup SELECT * FROM investment_plans;";
if ($conn_back->multi_query($backupSql)) {
    while ($conn_back->more_results() && $conn_back->next_result()) { }
    echo "<p style='color:green'>✅ Backup created successfully</p>";
} else {
    echo "<p style='color:red'>❌ Error creating backup: " . $conn_back->error . "</p>";
    exit;
}

// Step 2: Create a temporary table with the desired structure
$createTempSql = "
CREATE TABLE investment_plans_new (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `plan_type` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `min_amount` decimal(20,8) NOT NULL,
  `max_amount` decimal(20,8) NOT NULL,
  `roi_percent` decimal(5,2) NOT NULL,
  `duration_days` int NOT NULL,
  `risk_level` varchar(50) NOT NULL,
  `return_interval` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
";
executeSql($conn_back, $createTempSql, "Creating new table structure");

// Step 3: Copy data from the original table to the new one
$copyDataSql = "
INSERT INTO investment_plans_new (
  id, name, plan_type, category, min_amount, max_amount, 
  roi_percent, duration_days, risk_level, return_interval, 
  is_active, created_at
)
SELECT 
  id, name, plan_type, category, 
  COALESCE(min_amount, 0) as min_amount, 
  COALESCE(max_amount, 0) as max_amount,
  COALESCE(roi_percent, return_rate) as roi_percent, 
  COALESCE(duration_days, 30) as duration_days, 
  risk_level, return_interval,
  COALESCE(is_active, IF(status = 'active', 1, 0)) as is_active,
  created_at
FROM investment_plans;
";
executeSql($conn_back, $copyDataSql, "Copying data to new table");

// Step 4: Rename tables to swap the new structure in place
$renameSql = "
DROP TABLE IF EXISTS investment_plans;
RENAME TABLE investment_plans_new TO investment_plans;
";
executeSql($conn_back, $renameSql, "Replacing old table with new structure");

// Step 5: Add auto_increment to id field if needed
$autoIncrementSql = "
ALTER TABLE investment_plans MODIFY id int NOT NULL AUTO_INCREMENT;
";
executeSql($conn_back, $autoIncrementSql, "Setting AUTO_INCREMENT for ID field");

echo "<hr><h2>Update completed</h2>";
echo "<p>The investment_plans table has been reorganized with the following changes:</p>";
echo "<ul>";
echo "<li>Removed redundant fields (duration, availability, status)</li>";
echo "<li>Kept important plan attributes (plan_type, category, risk_level, return_interval)</li>";
echo "<li>Ensured proper field types and defaults</li>";
echo "<li>Created a backup of the original table as investment_plans_backup</li>";
echo "</ul>";
echo "<p>If you encounter any issues, you can restore from the backup table.</p>";
?> 