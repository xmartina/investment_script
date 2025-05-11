<?php
// User search AJAX endpoint for admin panel
header('Content-Type: application/json');

// Security checks
ini_set('display_errors', 0);
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Include database connection
require_once __DIR__ . '/../include/config.php';

// Get search query
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query)) {
    echo json_encode([]);
    exit();
}

// Search for users
$search_term = "%$query%";

$stmt = $conn_back->prepare("
    SELECT 
        id, 
        username, 
        CONCAT(first_name, ' ', last_name) as full_name,
        email,
        main_balance as balance
    FROM users 
    WHERE 
        username LIKE ? OR 
        email LIKE ? OR 
        CONCAT(first_name, ' ', last_name) LIKE ? 
    ORDER BY 
        CASE 
            WHEN username = ? THEN 0
            WHEN username LIKE ? THEN 1
            WHEN email = ? THEN 2
            WHEN email LIKE ? THEN 3
            ELSE 4
        END,
        username ASC
    LIMIT 20
");

$stmt->bind_param("sssssss", $search_term, $search_term, $search_term, $query, $search_term, $query, $search_term);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($user = $result->fetch_assoc()) {
    $users[] = [
        'id' => $user['id'],
        'text' => $user['username'] . ' (' . $user['full_name'] . ')',
        'email' => $user['email'],
        'balance' => $user['balance']
    ];
}

echo json_encode($users);
?> 