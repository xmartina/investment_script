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

// Check if investment_plans table has featured column
$check_query = "SHOW COLUMNS FROM investment_plans LIKE 'featured'";
$result = $conn_back->query($check_query);
if ($result->num_rows == 0) {
    $fixes_needed[] = "investment_plans table is missing featured column";
    
    // Add featured column
    $sql = "ALTER TABLE investment_plans ADD COLUMN featured TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active";
    if ($conn_back->query($sql)) {
        $fixes_applied[] = "Added column featured to investment_plans table";
    } else {
        echo "<p>Error: " . $conn_back->error . "</p>";
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

// Check if payment_methods table exists
$check_query = "SHOW TABLES LIKE 'payment_methods'";
$result = $conn_back->query($check_query);
if ($result->num_rows == 0) {
    $fixes_needed[] = "payment_methods table is missing";
    
    // Create payment_methods table
    $sql = "CREATE TABLE IF NOT EXISTS `payment_methods` (
        `id` int NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `description` text,
        `instructions` text,
        `min_amount` decimal(15,2) DEFAULT '10.00',
        `max_amount` decimal(15,2) DEFAULT '10000.00',
        `fixed_fee` decimal(15,2) DEFAULT '0.00',
        `percentage_fee` decimal(5,2) DEFAULT '0.00',
        `is_active` tinyint(1) DEFAULT '1',
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn_back->query($sql)) {
        $fixes_applied[] = "Created payment_methods table";
        
        // Add default payment methods
        $methods = [
            ['Bank Transfer', 'Direct bank transfer payment method', 'Please transfer to our bank account.', 50, 10000, 5, 0],
            ['Bitcoin', 'Bitcoin cryptocurrency payment', 'Send BTC to our wallet address.', 20, 5000, 0, 1.5],
            ['Ethereum', 'Ethereum cryptocurrency payment', 'Send ETH to our wallet address.', 20, 5000, 0, 1.5],
            ['USDT (TRC20)', 'USDT on Tron network', 'Send USDT to our TRC20 wallet address.', 10, 10000, 0, 1],
            ['USDT (ERC20)', 'USDT on Ethereum network', 'Send USDT to our ERC20 wallet address.', 10, 10000, 0, 2]
        ];
        
        $insert_sql = "INSERT INTO payment_methods (name, description, instructions, min_amount, max_amount, fixed_fee, percentage_fee) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn_back->prepare($insert_sql);
        
        foreach ($methods as $method) {
            $stmt->bind_param("sssdddd", $method[0], $method[1], $method[2], $method[3], $method[4], $method[5], $method[6]);
            $stmt->execute();
        }
        
        $fixes_applied[] = "Added default payment methods";
    } else {
        echo "<p>Error: " . $conn_back->error . "</p>";
    }
}

// Check staking_plans table for featured column
$check_query = "SHOW COLUMNS FROM staking_plans LIKE 'featured'";
$result = $conn_back->query($check_query);
if ($result->num_rows == 0) {
    $fixes_needed[] = "staking_plans table is missing featured column";
    
    // Add featured column
    $sql = "ALTER TABLE staking_plans ADD COLUMN featured TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active";
    if ($conn_back->query($sql)) {
        $fixes_applied[] = "Added column featured to staking_plans table";
    } else {
        echo "<p>Error: " . $conn_back->error . "</p>";
    }
}

// Check staking_plans for roi_daily column
$check_query = "SHOW COLUMNS FROM staking_plans LIKE 'roi_daily'";
$result = $conn_back->query($check_query);
if ($result->num_rows == 0) {
    $fixes_needed[] = "staking_plans table is missing roi_daily column";
    
    // Check if reward_percent column exists
    $check_reward = "SHOW COLUMNS FROM staking_plans LIKE 'reward_percent'";
    $reward_result = $conn_back->query($check_reward);
    
    if ($reward_result->num_rows > 0) {
        // Add roi_daily column as a copy of reward_percent
        $sql = "ALTER TABLE staking_plans ADD COLUMN roi_daily DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER max_amount;
                UPDATE staking_plans SET roi_daily = reward_percent";
        if ($conn_back->multi_query($sql)) {
            $conn_back->next_result(); // move past the first result set
            $fixes_applied[] = "Added column roi_daily to staking_plans table and copied data from reward_percent";
        } else {
            echo "<p>Error: " . $conn_back->error . "</p>";
        }
    } else {
        // Add roi_daily column
        $sql = "ALTER TABLE staking_plans ADD COLUMN roi_daily DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER max_amount";
        if ($conn_back->query($sql)) {
            $fixes_applied[] = "Added column roi_daily to staking_plans table";
        } else {
            echo "<p>Error: " . $conn_back->error . "</p>";
        }
    }
}

// Check staking_plans for lockup_period column
$check_query = "SHOW COLUMNS FROM staking_plans LIKE 'lockup_period'";
$result = $conn_back->query($check_query);
if ($result->num_rows == 0) {
    $fixes_needed[] = "staking_plans table is missing lockup_period column";
    
    // Check if lock_period_days column exists
    $check_lockperiod = "SHOW COLUMNS FROM staking_plans LIKE 'lock_period_days'";
    $lockperiod_result = $conn_back->query($check_lockperiod);
    
    if ($lockperiod_result->num_rows > 0) {
        // Add lockup_period column as a copy of lock_period_days
        $sql = "ALTER TABLE staking_plans ADD COLUMN lockup_period INT NOT NULL DEFAULT 0 AFTER duration_days;
                UPDATE staking_plans SET lockup_period = lock_period_days";
        if ($conn_back->multi_query($sql)) {
            $conn_back->next_result(); // move past the first result set
            $fixes_applied[] = "Added column lockup_period to staking_plans table and copied data from lock_period_days";
        } else {
            echo "<p>Error: " . $conn_back->error . "</p>";
        }
    } else {
        // Add lockup_period column
        $sql = "ALTER TABLE staking_plans ADD COLUMN lockup_period INT NOT NULL DEFAULT 0 AFTER duration_days";
        if ($conn_back->query($sql)) {
            $fixes_applied[] = "Added column lockup_period to staking_plans table";
        } else {
            echo "<p>Error: " . $conn_back->error . "</p>";
        }
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
        `duration_days` int NOT NULL,
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