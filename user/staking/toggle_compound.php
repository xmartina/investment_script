<?php
// Toggle compounding for staking positions
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: /user/login");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if required parameters are provided
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['enable'])) {
    header("Location: /user/staking/dashboard");
    exit();
}

$staking_id = $_GET['id'];
$enable = ($_GET['enable'] == 1) ? 1 : 0;

// Get staking details to verify ownership
$stmt = $conn_back->prepare("
    SELECT * FROM staking 
    WHERE id = ? AND user_id = ? AND status = 'active'
");
$stmt->bind_param("ii", $staking_id, $user_id);
$stmt->execute();
$staking = $stmt->get_result()->fetch_assoc();
$stmt->close();

// If staking not found, doesn't belong to user, or is not active, redirect
if (!$staking) {
    header("Location: /user/staking/dashboard");
    exit();
}

// Update compounding status
$stmt = $conn_back->prepare("
    UPDATE staking 
    SET is_compounding = ?, last_compound_at = IF(? = 1, NOW(), last_compound_at)
    WHERE id = ?
");
$stmt->bind_param("iii", $enable, $enable, $staking_id);
$stmt->execute();
$stmt->close();

// Redirect back to details page or dashboard
if (isset($_GET['redirect']) && $_GET['redirect'] == 'dashboard') {
    header("Location: /user/staking/dashboard");
} else {
    header("Location: /user/staking/details?id=" . $staking_id);
}
exit();
?> 