<?php
// Include database configuration
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';

// Set to display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Setting Up Referral System</h1>";

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

// Function to safely add columns to existing tables
function addColumnSafely($conn, $tableName, $columnName, $columnDefinition) {
    try {
        // Check if column exists
        $result = $conn->query("SHOW COLUMNS FROM `{$tableName}` LIKE '{$columnName}'");
        if ($result->num_rows == 0) {
            // Column doesn't exist, add it
            $sql = "ALTER TABLE `{$tableName}` ADD COLUMN `{$columnName}` {$columnDefinition}";
            if ($conn->query($sql) === TRUE) {
                echo "<p>Column {$columnName} added to {$tableName} successfully.</p>";
                return true;
            } else {
                echo "<p>Error adding column {$columnName} to {$tableName}: " . $conn->error . "</p>";
                return false;
            }
        } else {
            echo "<p>Column {$columnName} already exists in {$tableName}.</p>";
            return true;
        }
    } catch (Exception $e) {
        echo "<p>Exception when adding column {$columnName} to {$tableName}: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Step 1: Add referral_code and referred_by columns to users table
echo "<h2>Updating Users Table for Referrals</h2>";

// Add referral_code column (unique identifier for each user)
addColumnSafely($conn_back, "users", "referral_code", "VARCHAR(20) UNIQUE DEFAULT NULL");

// Add referred_by column (stores the referrer's user ID)
addColumnSafely($conn_back, "users", "referred_by", "INT DEFAULT NULL");

// Add referral_bonus_earned column (total earnings from referrals)
addColumnSafely($conn_back, "users", "referral_bonus_earned", "DECIMAL(15,2) DEFAULT 0.00");

// Step 2: Create referral_commissions table to track all referral earnings
echo "<h2>Creating Referral Commissions Table</h2>";

// Check if referral_commissions table exists
$tableExists = $conn_back->query("SHOW TABLES LIKE 'referral_commissions'");
if ($tableExists->num_rows == 0) {
    echo "<p>Creating referral_commissions table...</p>";
    
    $sql = "CREATE TABLE `referral_commissions` (
        `id` int NOT NULL AUTO_INCREMENT,
        `referrer_id` int NOT NULL,
        `referred_id` int NOT NULL,
        `amount` decimal(15,2) NOT NULL,
        `source_type` enum('investment', 'deposit', 'staking') NOT NULL,
        `source_id` int NOT NULL,
        `status` enum('pending', 'paid') NOT NULL DEFAULT 'pending',
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `paid_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        INDEX (`referrer_id`),
        INDEX (`referred_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    createTableSafely($conn_back, "referral_commissions", $sql);
    
    // Try to add foreign keys separately
    try {
        $sql = "ALTER TABLE `referral_commissions` 
                ADD CONSTRAINT `fk_referral_referrer` 
                FOREIGN KEY (`referrer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE";
        
        if ($conn_back->query($sql) === TRUE) {
            echo "<p>Foreign key fk_referral_referrer added successfully.</p>";
        } else {
            echo "<p>Warning: Failed to add foreign key fk_referral_referrer: " . $conn_back->error . "</p>";
        }
        
        $sql = "ALTER TABLE `referral_commissions` 
                ADD CONSTRAINT `fk_referral_referred` 
                FOREIGN KEY (`referred_id`) REFERENCES `users`(`id`) ON DELETE CASCADE";
        
        if ($conn_back->query($sql) === TRUE) {
            echo "<p>Foreign key fk_referral_referred added successfully.</p>";
        } else {
            echo "<p>Warning: Failed to add foreign key fk_referral_referred: " . $conn_back->error . "</p>";
        }
    } catch (Exception $e) {
        echo "<p>Exception when adding foreign keys to referral_commissions: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>referral_commissions table already exists.</p>";
}

// Step 3: Generate referral codes for existing users that don't have one
echo "<h2>Generating Referral Codes for Existing Users</h2>";

try {
    // First check if there are users without referral codes
    $result = $conn_back->query("SELECT id FROM users WHERE referral_code IS NULL");
    
    if ($result->num_rows > 0) {
        echo "<p>Found " . $result->num_rows . " users without referral codes. Generating codes...</p>";
        
        while ($row = $result->fetch_assoc()) {
            $user_id = $row['id'];
            
            // Generate a unique referral code
            $referral_code = strtoupper(substr(md5(uniqid($user_id, true)), 0, 8));
            
            // Update the user's record
            $stmt = $conn_back->prepare("UPDATE users SET referral_code = ? WHERE id = ?");
            $stmt->bind_param("si", $referral_code, $user_id);
            
            if ($stmt->execute()) {
                echo "<p>Generated referral code {$referral_code} for user ID {$user_id}.</p>";
            } else {
                echo "<p>Error generating referral code for user ID {$user_id}: " . $stmt->error . "</p>";
            }
            
            $stmt->close();
        }
    } else {
        echo "<p>All existing users already have referral codes.</p>";
    }
} catch (Exception $e) {
    echo "<p>Exception when generating referral codes: " . $e->getMessage() . "</p>";
}

// Step 4: Update transactions table to include referral bonus type if needed
echo "<h2>Updating Transactions Table</h2>";

try {
    // Check the current enum values for the type column in the transactions table
    $result = $conn_back->query("SHOW COLUMNS FROM transactions WHERE Field = 'type'");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $type_enum = $row['Type'];
        
        // Check if 'referral' is already in the enum values
        if (strpos($type_enum, "'referral'") === false) {
            // Extract current enum values
            preg_match('/enum\((.*)\)/', $type_enum, $matches);
            $values = $matches[1];
            
            // Add 'referral' to the enum values
            $new_values = str_replace(')', ",'referral')", str_replace('enum(', 'enum(', $type_enum));
            
            $sql = "ALTER TABLE transactions MODIFY COLUMN type " . $new_values;
            if ($conn_back->query($sql) === TRUE) {
                echo "<p>Added 'referral' to transaction types successfully.</p>";
            } else {
                echo "<p>Error adding 'referral' to transaction types: " . $conn_back->error . "</p>";
            }
        } else {
            echo "<p>'referral' transaction type already exists.</p>";
        }
    } else {
        echo "<p>Could not find 'type' column in transactions table.</p>";
    }
} catch (Exception $e) {
    echo "<p>Exception when updating transactions table: " . $e->getMessage() . "</p>";
}

echo "<hr><h2>Referral System Setup Completed</h2>";
echo "<p>Please make sure the following routes are added to your index.php file:</p>";
echo "<pre>
get('/update_referral_tables', function() {
    include \$_SERVER['DOCUMENT_ROOT'] . '/update_referral_tables.php';
});
post('/update_referral_tables', function() {
    include \$_SERVER['DOCUMENT_ROOT'] . '/update_referral_tables.php';
});
get('/user/referral', function() {
    include \$_SERVER['DOCUMENT_ROOT'] . '/user/referral.php';
});
post('/user/referral', function() {
    include \$_SERVER['DOCUMENT_ROOT'] . '/user/referral.php';
});
</pre>";
?> 