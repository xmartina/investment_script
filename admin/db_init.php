<?php
// Database Initialization Script
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session to access any session variables
session_start();

// We'll allow running this script without login if no admin exists yet
$admin_check_skipped = false;

try {
    // Include database configuration
    include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';
    
    if (!$conn_back) {
        die("<div class='alert alert-danger'>Database connection not available. Please check configuration.</div>");
    }
    
    // Check if any admin users exist
    $admin_exists = false;
    $result = $conn_back->query("SHOW TABLES LIKE 'admins'");
    if ($result && $result->num_rows > 0) {
        $admin_result = $conn_back->query("SELECT COUNT(*) as total FROM admins");
        if ($admin_result && $admin_row = $admin_result->fetch_assoc()) {
            $admin_exists = ($admin_row['total'] > 0);
        }
    }
    
    // Only require login if admins already exist
    if ($admin_exists && !isset($_SESSION['admin_id'])) {
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1'>
            <title>Database Initialization</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body>
            <div class='container mt-5'>
                <div class='row'>
                    <div class='col-md-6 offset-md-3'>
                        <div class='card'>
                            <div class='card-header bg-danger text-white'>
                                <h4>Authentication Required</h4>
                            </div>
                            <div class='card-body'>
                                <p>You must be logged in as an administrator to run this script since admin users already exist.</p>
                                <a href='login.php' class='btn btn-primary'>Go to Login</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>";
        exit;
    } else if (!$admin_exists) {
        $admin_check_skipped = true;
    }
    
    // Output HTML header
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <title>Database Initialization</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    </head>
    <body>
        <div class='container mt-5'>
            <div class='row'>
                <div class='col-md-8 offset-md-2'>
                    <div class='card'>
                        <div class='card-header bg-primary text-white'>
                            <h4>Database Initialization Script</h4>
                        </div>
                        <div class='card-body'>";
    
    if ($admin_check_skipped) {
        echo "<div class='alert alert-info'>Running in initial setup mode. No admin login required.</div>";
    }
    
    echo "<p class='alert alert-success'>Database connection successful!</p>";
    
    // Tables to check/create
    $tables = [
        'users' => "
            CREATE TABLE IF NOT EXISTS users (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                full_name VARCHAR(100),
                phone VARCHAR(20),
                balance DECIMAL(15, 2) DEFAULT 0.00,
                status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ",
        
        'investments' => "
            CREATE TABLE IF NOT EXISTS investments (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                plan_id INT UNSIGNED NOT NULL,
                amount DECIMAL(15, 2) NOT NULL,
                profit DECIMAL(15, 2) DEFAULT 0.00,
                status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
                start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                end_date TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX (user_id),
                INDEX (plan_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ",
        
        'deposits' => "
            CREATE TABLE IF NOT EXISTS deposits (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                amount DECIMAL(15, 2) NOT NULL,
                fee_amount DECIMAL(15, 2) DEFAULT 0.00,
                payment_method VARCHAR(50) NOT NULL,
                transaction_id VARCHAR(100),
                status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ",
        
        'withdrawals' => "
            CREATE TABLE IF NOT EXISTS withdrawals (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                amount DECIMAL(15, 2) NOT NULL,
                fee_amount DECIMAL(15, 2) DEFAULT 0.00,
                withdrawal_method_id INT UNSIGNED NOT NULL,
                withdrawal_address TEXT NOT NULL,
                transaction_id VARCHAR(100),
                user_balance_before_withdrawal DECIMAL(15, 2) NOT NULL,
                user_balance_after_withdrawal DECIMAL(15, 2) NOT NULL,
                status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
                approved_at TIMESTAMP NULL,
                rejected_at TIMESTAMP NULL,
                rejection_reason TEXT,
                payment_proof VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ",
        
        'admins' => "
            CREATE TABLE IF NOT EXISTS admins (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                full_name VARCHAR(100) NOT NULL,
                role ENUM('admin', 'super_admin', 'editor') NOT NULL DEFAULT 'admin',
                status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
                last_login TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ",
        
        'admin_activity_logs' => "
            CREATE TABLE IF NOT EXISTS admin_activity_logs (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                admin_id INT UNSIGNED NOT NULL,
                action VARCHAR(255) NOT NULL,
                details TEXT,
                ip_address VARCHAR(45) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        "
    ];
    
    // Create tables if they don't exist
    echo "<h5>Checking Tables</h5>";
    echo "<ul class='list-group mb-4'>";
    
    foreach ($tables as $table => $sql) {
        // Check if table exists
        $check_result = $conn_back->query("SHOW TABLES LIKE '$table'");
        
        if ($check_result->num_rows == 0) {
            // Create table
            if ($conn_back->query($sql)) {
                echo "<li class='list-group-item list-group-item-success'>Created table '$table' ✅</li>";
            } else {
                echo "<li class='list-group-item list-group-item-danger'>Failed to create table '$table': " . $conn_back->error . " ❌</li>";
            }
        } else {
            echo "<li class='list-group-item list-group-item-info'>Table '$table' already exists ✓</li>";
        }
    }
    
    echo "</ul>";
    
    // Check if we need to create a default admin user
    $admin_result = $conn_back->query("SELECT COUNT(*) as total FROM admins");
    $admin_row = $admin_result->fetch_assoc();
    
    if ($admin_row['total'] == 0) {
        echo "<h5 class='card-title'>Creating Default Admin User</h5>";
        
        // Create default admin with password 'admin123'
        $username = 'admin';
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $email = 'admin@example.com';
        $full_name = 'System Administrator';
        $role = 'super_admin';
        
        $stmt = $conn_back->prepare("INSERT INTO admins (username, password, email, full_name, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $password, $email, $full_name, $role);
        
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>
                <p>Default admin account created successfully!</p>
                <ul>
                    <li><strong>Username:</strong> admin</li>
                    <li><strong>Password:</strong> admin123</li>
                </ul>
                <p class='text-danger'><strong>Please change this password immediately after login!</strong></p>
            </div>";
        } else {
            echo "<div class='alert alert-danger'>Error creating default admin: " . $stmt->error . "</div>";
        }
        
        $stmt->close();
    } else {
        echo "<div class='alert alert-info'>Admin users already exist in the database.</div>";
    }
    
    echo "<div class='mt-4'>
            <a href='/admin/login.php' class='btn btn-primary'>Go to Admin Login</a>
            <a href='/admin/index.php' class='btn btn-secondary'>Go to Admin Dashboard</a>
        </div>";
    
    // Close HTML
    echo "</div></div></div></div></div></body></html>";
    
} catch (Exception $e) {
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <title>Database Initialization Error</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    </head>
    <body>
        <div class='container mt-5'>
            <div class='row'>
                <div class='col-md-6 offset-md-3'>
                    <div class='card'>
                        <div class='card-header bg-danger text-white'>
                            <h4>Error</h4>
                        </div>
                        <div class='card-body'>
                            <p>" . htmlspecialchars($e->getMessage()) . "</p>
                            <a href='/admin/login.php' class='btn btn-primary'>Return to Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>";
}
?> 