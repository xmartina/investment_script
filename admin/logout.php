<?php
session_start();

// Log the logout activity if admin is logged in
if (isset($_SESSION['admin_id'])) {
    include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/include/config.php';
    logAdminActivity($_SESSION['admin_id'], 'Logout', 'Admin logged out of the system');
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
?> 