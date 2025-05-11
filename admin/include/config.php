<?php
// Include the main configuration file
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';

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
    
    // Check if admin_activity_logs table exists
    $result = $conn_back->query("SHOW TABLES LIKE 'admin_activity_logs'");
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
        $conn_back->query($sql);
    }
    
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $conn_back->prepare("INSERT INTO admin_activity_logs (admin_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $admin_id, $action, $details, $ip);
    $stmt->execute();
    $stmt->close();
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

// Check if we need to create admin activity logs table
$result = $conn_back->query("SHOW TABLES LIKE 'admin_activity_logs'");
if ($result->num_rows == 0) {
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
?>