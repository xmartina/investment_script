<?php
session_start();

// Log the logout activity if we have admin info
if (isset($_SESSION['admin_id'])) {
    try {
        require_once __DIR__ . '/include/config.php';
        
        $admin_id = $_SESSION['admin_id'];
        $action = "Admin logged out";
        $ip = $_SERVER['REMOTE_ADDR'];
        
        $stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $admin_id, $action, $ip);
        $stmt->execute();
    } catch (Exception $e) {
        // Just continue with logout even if logging fails
    }
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
?> 