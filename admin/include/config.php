<?php
// Enable error reporting for this file
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the main configuration file using relative path
try {
    include_once __DIR__ . '/../../include/config.php';
} catch (Exception $e) {
    die("Error including main config file: " . $e->getMessage());
}

// Check if database connection exists
if (!isset($conn_back) || !$conn_back) {
    // Try to re-establish the database connection
    try {
        // Include database connection file
        include_once __DIR__ . '/../../include/db.php';
        
        // If still no connection, try to create one directly
        if (!isset($conn_back) || !$conn_back) {
            // Database credentials
            $back_host = 'localhost';
            $back_user = 'summitgu_exodusaipro_back';
            $back_password = 'exodusaipro_back';
            $back_database = 'summitgu_exodusaipro_back';
            
            // Create connection
            $conn_back = new mysqli($back_host, $back_user, $back_password, $back_database);
            
            // Check connection
            if ($conn_back->connect_error) {
                die("Database connection failed: " . $conn_back->connect_error);
            }
        }
    } catch (Exception $e) {
        die("Error establishing database connection: " . $e->getMessage());
    }
}

// Admin-specific settings
define('ADMIN_TITLE', 'Investment Platform Admin Panel');
define('ADMIN_VERSION', '1.0.0');

// Check for active page to highlight in menu
$current_page = basename($_SERVER['PHP_SELF']);

// Function to check if a user has required permission
function hasPermission($required_role) {
    if (!isset($_SESSION['admin_role'])) {
        return false;
    }
    
    if ($_SESSION['admin_role'] == 'super_admin') {
        return true; // Super admin has all permissions
    }
    
    if ($required_role == 'admin' && $_SESSION['admin_role'] == 'admin') {
        return true;
    }
    
    if ($required_role == 'editor' && 
        ($_SESSION['admin_role'] == 'admin' || $_SESSION['admin_role'] == 'editor')) {
        return true;
    }
    
    return false;
}

// Function to log admin activity
function logAdminActivity($admin_id, $action, $details = '') {
    global $conn_back;
    
    if (!$conn_back) {
        error_log("Cannot log admin activity: Database connection not available");
        return;
    }
    
    try {
        // Check if admin_activity_logs table exists
        $result = $conn_back->query("SHOW TABLES LIKE 'admin_activity_logs'");
        if (!$result) {
            error_log("Error checking for admin_activity_logs table: " . $conn_back->error);
            return;
        }
        
        if ($result->num_rows == 0) {
            // Create table if it doesn't exist
            $sql = "CREATE TABLE admin_activity_logs (
                id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                admin_id INT(11) UNSIGNED NOT NULL,
                action VARCHAR(255) NOT NULL,
                details TEXT,
                ip_address VARCHAR(45) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            if (!$conn_back->query($sql)) {
                error_log("Error creating admin_activity_logs table: " . $conn_back->error);
                return;
            }
        }
        
        $ip = $_SERVER['REMOTE_ADDR'];
        $stmt = $conn_back->prepare("INSERT INTO admin_activity_logs (admin_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Error preparing admin activity log statement: " . $conn_back->error);
            return;
        }
        
        $stmt->bind_param("isss", $admin_id, $action, $details, $ip);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error logging admin activity: " . $e->getMessage());
    }
}

// Helper function to display alert messages
function showAlert($message, $type = 'success') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

// Helper function to display alert messages
function displayAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        echo '<div class="alert alert-' . $alert['type'] . ' alert-dismissible fade show" role="alert">';
        echo $alert['message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['alert']);
    }
}

// Check if admin_activity_logs table exists
try {
    if ($conn_back) {
        $result = $conn_back->query("SHOW TABLES LIKE 'admin_activity_logs'");
        if ($result && $result->num_rows == 0) {
            $sql = "CREATE TABLE admin_activity_logs (
                id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                admin_id INT(11) UNSIGNED NOT NULL,
                action VARCHAR(255) NOT NULL,
                details TEXT,
                ip_address VARCHAR(45) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $conn_back->query($sql);
        }
    }
} catch (Exception $e) {
    error_log("Error checking admin_activity_logs table: " . $e->getMessage());
}
?>