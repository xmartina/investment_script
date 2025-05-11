<?php
// Database Schema Fix Script
// This script will add missing columns or fix column names in tables

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/include/config.php';

echo "<h1>Database Fix Script</h1>";
echo "<p>Checking for database schema issues...</p>";

$fixes_needed = [];
$fixes_applied = [];

// Check if investment_plans table has correct columns
$check_query = "SHOW COLUMNS FROM investment_plans LIKE 'roi_percent'";
$result = $conn_back->query($check_query);
if ($result->num_rows == 0) {
    $fixes_needed[] = "investment_plans table is missing roi_percent column";
    
    // Check if interest_rate column exists
    $check_interest = "SHOW COLUMNS FROM investment_plans LIKE 'interest_rate'";
    $interest_result = $conn_back->query($check_interest);
    
    if ($interest_result->num_rows > 0) {
        // Rename interest_rate to roi_percent
        $sql = "ALTER TABLE investment_plans CHANGE COLUMN interest_rate roi_percent DECIMAL(5,2)";
        if ($conn_back->query($sql)) {
            $fixes_applied[] = "Renamed column interest_rate to roi_percent in investment_plans table";
        } else {
            echo "<p>Error: " . $conn_back->error . "</p>";
        }
    } else {
        // Add roi_percent column
        $sql = "ALTER TABLE investment_plans ADD COLUMN roi_percent DECIMAL(5,2) NOT NULL AFTER max_amount";
        if ($conn_back->query($sql)) {
            $fixes_applied[] = "Added column roi_percent to investment_plans table";
        } else {
            echo "<p>Error: " . $conn_back->error . "</p>";
        }
    }
}

// Check if investments table has correct date columns
$check_query = "SHOW COLUMNS FROM investments LIKE 'started_at'";
$result = $conn_back->query($check_query);
if ($result->num_rows == 0) {
    $fixes_needed[] = "investments table is missing started_at column";
    
    // Check if start_date column exists
    $check_start = "SHOW COLUMNS FROM investments LIKE 'start_date'";
    $start_result = $conn_back->query($check_start);
    
    if ($start_result->num_rows > 0) {
        // Rename start_date to started_at
        $sql = "ALTER TABLE investments CHANGE COLUMN start_date started_at DATETIME DEFAULT NULL";
        if ($conn_back->query($sql)) {
            $fixes_applied[] = "Renamed column start_date to started_at in investments table";
        } else {
            echo "<p>Error: " . $conn_back->error . "</p>";
        }
    } else {
        // Add started_at column
        $sql = "ALTER TABLE investments ADD COLUMN started_at DATETIME DEFAULT NULL AFTER status";
        if ($conn_back->query($sql)) {
            $fixes_applied[] = "Added column started_at to investments table";
        } else {
            echo "<p>Error: " . $conn_back->error . "</p>";
        }
    }
}

// Check for ends_at column
$check_query = "SHOW COLUMNS FROM investments LIKE 'ends_at'";
$result = $conn_back->query($check_query);
if ($result->num_rows == 0) {
    $fixes_needed[] = "investments table is missing ends_at column";
    
    // Check if end_date column exists
    $check_end = "SHOW COLUMNS FROM investments LIKE 'end_date'";
    $end_result = $conn_back->query($check_end);
    
    if ($end_result->num_rows > 0) {
        // Rename end_date to ends_at
        $sql = "ALTER TABLE investments CHANGE COLUMN end_date ends_at DATETIME DEFAULT NULL";
        if ($conn_back->query($sql)) {
            $fixes_applied[] = "Renamed column end_date to ends_at in investments table";
        } else {
            echo "<p>Error: " . $conn_back->error . "</p>";
        }
    } else {
        // Add ends_at column
        $sql = "ALTER TABLE investments ADD COLUMN ends_at DATETIME DEFAULT NULL AFTER started_at";
        if ($conn_back->query($sql)) {
            $fixes_applied[] = "Added column ends_at to investments table";
        } else {
            echo "<p>Error: " . $conn_back->error . "</p>";
        }
    }
}

// Add expected_returns column if missing
$check_query = "SHOW COLUMNS FROM investments LIKE 'expected_returns'";
$result = $conn_back->query($check_query);
if ($result->num_rows == 0) {
    $fixes_needed[] = "investments table is missing expected_returns column";
    
    // Add expected_returns column
    $sql = "ALTER TABLE investments ADD COLUMN expected_returns DECIMAL(20,8) DEFAULT NULL AFTER amount";
    if ($conn_back->query($sql)) {
        $fixes_applied[] = "Added column expected_returns to investments table";
    } else {
        echo "<p>Error: " . $conn_back->error . "</p>";
    }
}

// Check for admin_logs table (instead of admin_activity_logs)
$check_query = "SHOW TABLES LIKE 'admin_logs'";
$result = $conn_back->query($check_query);
if ($result->num_rows == 0) {
    $fixes_needed[] = "admin_logs table is missing";
    
    // Create admin_logs table
    $sql = "CREATE TABLE IF NOT EXISTS `admin_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `admin_id` int(11) NOT NULL,
        `action` varchar(255) NOT NULL,
        `ip_address` varchar(45) NOT NULL,
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn_back->query($sql)) {
        $fixes_applied[] = "Created admin_logs table";
    } else {
        echo "<p>Error: " . $conn_back->error . "</p>";
    }
}

// Add code to create staking tables when requested
if (isset($_POST['create_staking_tables'])) {
    // Create staking_plans table
    $staking_plans_sql = "CREATE TABLE IF NOT EXISTS `staking_plans` (
        `id` int NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `description` text,
        `min_amount` decimal(20,8) NOT NULL,
        `max_amount` decimal(20,8) DEFAULT NULL,
        `roi_daily` decimal(10,4) NOT NULL,
        `lockup_period` int NOT NULL,
        `status` tinyint(1) NOT NULL DEFAULT '1',
        `featured` tinyint(1) NOT NULL DEFAULT '0',
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn_back->query($staking_plans_sql)) {
        $fixes_applied[] = "Created staking_plans table";
    } else {
        echo "<p>Error creating staking_plans table: " . $conn_back->error . "</p>";
    }
    
    // Create staking_positions table
    $staking_positions_sql = "CREATE TABLE IF NOT EXISTS `staking_positions` (
        `id` int NOT NULL AUTO_INCREMENT,
        `user_id` int NOT NULL,
        `plan_id` int NOT NULL,
        `amount` decimal(20,8) NOT NULL,
        `daily_reward` decimal(20,8) NOT NULL,
        `last_reward_date` datetime DEFAULT NULL,
        `total_rewards` decimal(20,8) NOT NULL DEFAULT '0.00000000',
        `status` enum('active','completed','cancelled') NOT NULL DEFAULT 'active',
        `is_compounding` tinyint(1) NOT NULL DEFAULT '0',
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        `unstaked_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        KEY `plan_id` (`plan_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn_back->query($staking_positions_sql)) {
        $fixes_applied[] = "Created staking_positions table";
    } else {
        echo "<p>Error creating staking_positions table: " . $conn_back->error . "</p>";
    }
    
    // Create staking_rewards table
    $staking_rewards_sql = "CREATE TABLE IF NOT EXISTS `staking_rewards` (
        `id` int NOT NULL AUTO_INCREMENT,
        `staking_id` int NOT NULL,
        `user_id` int NOT NULL,
        `amount` decimal(20,8) NOT NULL,
        `is_compounded` tinyint(1) NOT NULL DEFAULT '0',
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `staking_id` (`staking_id`),
        KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn_back->query($staking_rewards_sql)) {
        $fixes_applied[] = "Created staking_rewards table";
    } else {
        echo "<p>Error creating staking_rewards table: " . $conn_back->error . "</p>";
    }
}

// Display results
if (empty($fixes_needed)) {
    echo "<p style='color: green;'>No database schema issues found!</p>";
} else {
    echo "<h2>Issues Found:</h2>";
    echo "<ul>";
    foreach ($fixes_needed as $issue) {
        echo "<li>" . htmlspecialchars($issue) . "</li>";
    }
    echo "</ul>";
    
    echo "<h2>Fixes Applied:</h2>";
    
    if (empty($fixes_applied)) {
        echo "<p style='color: red;'>No fixes were applied. Check database permissions.</p>";
    } else {
        echo "<ul>";
        foreach ($fixes_applied as $fix) {
            echo "<li style='color: green;'>" . htmlspecialchars($fix) . "</li>";
        }
        echo "</ul>";
    }
}

echo "<p><a href='/admin/'>Return to Admin Dashboard</a></p>"; 