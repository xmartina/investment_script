<?php
// Include database configuration
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';

// Set to display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Setting Up Withdrawal Tables</h1>";

// Function to safely create tables with error handling
function createTableSafely($conn, $tableName, $sql) {
    try {
        if ($conn->query($sql) === TRUE) {
            echo "<p>{$tableName} table created successfully.</p>";
            return true;
        } else {
            echo "<p>Error creating {$tableName} table: " . $conn->error . "</p>";
            return false;
        }
    } catch (Exception $e) {
        echo "<p>Exception when creating {$tableName} table: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Function to add foreign key safely
function addForeignKeySafely($conn, $tableName, $fkName, $column, $refTable, $refColumn) {
    try {
        $sql = "ALTER TABLE `{$tableName}` ADD CONSTRAINT `{$fkName}` 
                FOREIGN KEY (`{$column}`) REFERENCES `{$refTable}`(`{$refColumn}`) 
                ON DELETE CASCADE";
                
        if ($conn->query($sql) === TRUE) {
            echo "<p>Foreign key {$fkName} added to {$tableName} successfully.</p>";
            return true;
        } else {
            echo "<p>Warning: Failed to add foreign key {$fkName} to {$tableName}: " . $conn->error . "</p>";
            return false;
        }
    } catch (Exception $e) {
        echo "<p>Exception when adding foreign key {$fkName} to {$tableName}: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Check if withdrawal_methods table exists
$tableExists = $conn_back->query("SHOW TABLES LIKE 'withdrawal_methods'");
if ($tableExists->num_rows == 0) {
    echo "<p>Creating withdrawal_methods table...</p>";
    
    $sql = "CREATE TABLE `withdrawal_methods` (
        `id` int NOT NULL AUTO_INCREMENT,
        `method_name` varchar(50) NOT NULL,
        `description` text DEFAULT NULL,
        `status` enum('active', 'inactive') NOT NULL DEFAULT 'active',
        `min_amount` decimal(15,2) DEFAULT 10.00,
        `max_amount` decimal(15,2) DEFAULT 10000.00,
        `processing_time` varchar(100) DEFAULT '1-3 business days',
        `fee_percentage` decimal(5,2) DEFAULT 0.00,
        `fee_fixed` decimal(15,2) DEFAULT 0.00,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if (createTableSafely($conn_back, "withdrawal_methods", $sql)) {
        echo "<p>Adding default withdrawal methods...</p>";
        
        // Insert default withdrawal methods
        $methods = [
            [
                'method_name' => 'Bank Transfer',
                'description' => 'Direct transfer to your bank account. Please provide your bank details.',
                'min_amount' => 50.00,
                'max_amount' => 10000.00,
                'processing_time' => '2-5 business days',
                'fee_percentage' => 0.00,
                'fee_fixed' => 5.00
            ],
            [
                'method_name' => 'Bitcoin (BTC)',
                'description' => 'Withdraw funds to your Bitcoin wallet.',
                'min_amount' => 20.00,
                'max_amount' => 5000.00,
                'processing_time' => '1-24 hours',
                'fee_percentage' => 1.50,
                'fee_fixed' => 0.00
            ],
            [
                'method_name' => 'Ethereum (ETH)',
                'description' => 'Withdraw funds to your Ethereum wallet.',
                'min_amount' => 20.00,
                'max_amount' => 5000.00,
                'processing_time' => '1-24 hours',
                'fee_percentage' => 1.50,
                'fee_fixed' => 0.00
            ],
            [
                'method_name' => 'PayPal',
                'description' => 'Withdraw funds to your PayPal account.',
                'min_amount' => 10.00,
                'max_amount' => 2000.00,
                'processing_time' => '1-2 business days',
                'fee_percentage' => 2.50,
                'fee_fixed' => 0.00
            ],
            [
                'method_name' => 'Skrill',
                'description' => 'Withdraw funds to your Skrill account.',
                'min_amount' => 10.00,
                'max_amount' => 2000.00,
                'processing_time' => '1-2 business days',
                'fee_percentage' => 2.00,
                'fee_fixed' => 1.00
            ]
        ];
        
        try {
            $stmt = $conn_back->prepare("INSERT INTO withdrawal_methods (method_name, description, min_amount, max_amount, processing_time, fee_percentage, fee_fixed) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($methods as $method) {
                $stmt->bind_param("ssddsdd", 
                    $method['method_name'], 
                    $method['description'], 
                    $method['min_amount'], 
                    $method['max_amount'], 
                    $method['processing_time'], 
                    $method['fee_percentage'], 
                    $method['fee_fixed']
                );
                $stmt->execute();
            }
            
            echo "<p>Default withdrawal methods added.</p>";
            $stmt->close();
        } catch (Exception $e) {
            echo "<p>Error adding default withdrawal methods: " . $e->getMessage() . "</p>";
        }
    }
} else {
    echo "<p>withdrawal_methods table already exists.</p>";
}

// Check if user_withdrawal_methods table exists
$tableExists = $conn_back->query("SHOW TABLES LIKE 'user_withdrawal_methods'");
if ($tableExists->num_rows == 0) {
    echo "<p>Creating user_withdrawal_methods table...</p>";
    
    // Create table first without foreign keys
    $sql = "CREATE TABLE `user_withdrawal_methods` (
        `id` int NOT NULL AUTO_INCREMENT,
        `user_id` int NOT NULL,
        `withdrawal_method_id` int NOT NULL,
        `account_details` text NOT NULL,
        `is_default` tinyint(1) NOT NULL DEFAULT 0,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `user_method_unique` (`user_id`, `withdrawal_method_id`),
        INDEX (`user_id`),
        INDEX (`withdrawal_method_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if (createTableSafely($conn_back, "user_withdrawal_methods", $sql)) {
        echo "<p>user_withdrawal_methods table created successfully, trying to add foreign keys...</p>";
        
        // Try to add foreign keys separately
        addForeignKeySafely($conn_back, "user_withdrawal_methods", "fk_user_withdrawal_methods_user", 
                           "user_id", "users", "id");
                           
        addForeignKeySafely($conn_back, "user_withdrawal_methods", "fk_user_withdrawal_methods_method", 
                           "withdrawal_method_id", "withdrawal_methods", "id");
    }
} else {
    echo "<p>user_withdrawal_methods table already exists.</p>";
}

// Check if withdrawal table exists
$tableExists = $conn_back->query("SHOW TABLES LIKE 'withdrawal'");
if ($tableExists->num_rows == 0) {
    echo "<p>Creating withdrawal table...</p>";
    
    // Create table first without foreign keys
    $sql = "CREATE TABLE `withdrawal` (
        `id` int NOT NULL AUTO_INCREMENT,
        `user_id` int NOT NULL,
        `amount` decimal(15,2) NOT NULL,
        `currency` varchar(10) NOT NULL,
        `withdrawal_method_id` int NOT NULL,
        `transaction_id` varchar(100) NOT NULL,
        `status` enum('pending', 'approved', 'rejected', 'completed') NOT NULL DEFAULT 'pending',
        `withdrawal_address` varchar(255) NOT NULL,
        `transaction_proof_id` varchar(150) DEFAULT NULL,
        `payment_proof` varchar(255) DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `approved_at` datetime DEFAULT NULL,
        `rejected_at` datetime DEFAULT NULL,
        `rejection_reason` text DEFAULT NULL,
        `user_balance_before_withdrawal` decimal(15,2) NOT NULL,
        `user_balance_after_withdrawal` decimal(15,2) NOT NULL,
        `fee_amount` decimal(15,2) DEFAULT 0.00,
        PRIMARY KEY (`id`),
        INDEX (`user_id`),
        INDEX (`withdrawal_method_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if (createTableSafely($conn_back, "withdrawal", $sql)) {
        echo "<p>withdrawal table created successfully, trying to add foreign keys...</p>";
        
        // Try to add foreign keys separately
        addForeignKeySafely($conn_back, "withdrawal", "fk_withdrawal_user", 
                          "user_id", "users", "id");
                          
        addForeignKeySafely($conn_back, "withdrawal", "fk_withdrawal_method", 
                          "withdrawal_method_id", "withdrawal_methods", "id");
    }
} else {
    echo "<p>withdrawal table already exists.</p>";
}

echo "<p>Setup completed successfully. Please make sure the following routes are added to your index.php file:</p>";
echo "<pre>
get('/update_withdrawal_tables', function() {
    include \$_SERVER['DOCUMENT_ROOT'] . '/update_withdrawal_tables.php';
});
post('/update_withdrawal_tables', function() {
    include \$_SERVER['DOCUMENT_ROOT'] . '/update_withdrawal_tables.php';
});
get('/user/withdrawal', function() {
    include \$_SERVER['DOCUMENT_ROOT'] . '/user/withdrawal.php';
});
post('/user/withdrawal', function() {
    include \$_SERVER['DOCUMENT_ROOT'] . '/user/withdrawal.php';
});
get('/user/withdrawal_methods', function() {
    include \$_SERVER['DOCUMENT_ROOT'] . '/user/withdrawal_methods.php';
});
post('/user/withdrawal_methods', function() {
    include \$_SERVER['DOCUMENT_ROOT'] . '/user/withdrawal_methods.php';
});
</pre>";
?> 