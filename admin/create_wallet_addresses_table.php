<?php
// Script to create wallet_addresses table
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Create Wallet Addresses Table";
$message = "";
$error = "";

// Check if the wallet_addresses table exists
$table_exists = false;
$table_check = $conn_back->query("SHOW TABLES LIKE 'wallet_addresses'");
if ($table_check && $table_check->num_rows > 0) {
    $table_exists = true;
    $message = "The wallet_addresses table already exists.";
} else {
    // If the table doesn't exist, create it
    $sql = "CREATE TABLE IF NOT EXISTS `wallet_addresses` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `currency` VARCHAR(20) NOT NULL,
        `wallet_type` VARCHAR(50) NOT NULL,
        `address` TEXT NOT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `currency` (`currency`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";
    
    if ($conn_back->query($sql) === TRUE) {
        $message = "Wallet addresses table created successfully!";
        
        // Insert default wallet addresses
        $default_wallets = [
            ['BTC', 'Bitcoin', 'bc1qkyqlvaed0zxdjync4udlyk290sy333jhv8qlxc'],
            ['USDT', 'USDT BEP-20', '0x81Dc8cEe8fda0Ee57D2c9E0808218e781dC9Da8A'],
            ['ETH', 'Ethereum', '0x81Dc8cEe8fda0Ee57D2c9E0808218e781dC9Da8A'],
            ['XRP', 'XRP', 'rNET5KoxdU4YRGoLjmxAijYXNmMth8mXgT'],
            ['XLM', 'XLM', 'GCS76CYBB6IQUEROYHVTBAVDCKZY65LMENX3ZR23I6F5TI7DAD43NUA2'],
            ['DOGE', 'Dogecoin', 'DGFq4VZUzMiN2R9sCr74CMiiLiGfaDJjLf'],
            ['SOL', 'Solana', '995UT8C4AaTZvQcZ8vZ6tA1tbLVXnn9wA7Do7Y7X6nfc']
        ];
        
        $stmt = $conn_back->prepare("INSERT INTO wallet_addresses (currency, wallet_type, address) VALUES (?, ?, ?)");
        
        foreach ($default_wallets as $wallet) {
            $stmt->bind_param("sss", $wallet[0], $wallet[1], $wallet[2]);
            $stmt->execute();
        }
        
        $stmt->close();
        $message .= " Default wallet addresses have been added.";
        
        // Log admin activity
        $admin_id = $_SESSION['admin_id'];
        $action = "Created wallet_addresses table and inserted default wallet addresses";
        $ip = $_SERVER['REMOTE_ADDR'];
        
        $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
        $log_stmt->bind_param("iss", $admin_id, $action, $ip);
        $log_stmt->execute();
    } else {
        $error = "Error creating wallet addresses table: " . $conn_back->error;
    }
}

include_once __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create Wallet Addresses Table</h1>
        <a href="wallet_addresses.php" class="btn btn-primary">
            <i class="fas fa-wallet mr-2"></i> Go to Wallet Addresses
        </a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success" role="alert">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" role="alert">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Database Setup</h6>
        </div>
        <div class="card-body">
            <?php if ($table_exists): ?>
                <p>The wallet addresses table is already set up in your database.</p>
                <p>You can now <a href="wallet_addresses.php">manage your wallet addresses</a>.</p>
            <?php else: ?>
                <p>This page has attempted to create the wallet_addresses table in your database.</p>
                <p>If you see a success message above, the table was created successfully.</p>
                <p>If you see an error message, please check your database configuration and try again.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 