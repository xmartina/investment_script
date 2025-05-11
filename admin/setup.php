<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';

// Check if admins table exists
$tableExists = false;
$result = $conn_back->query("SHOW TABLES LIKE 'admins'");
if ($result->num_rows > 0) {
    $tableExists = true;
}

// Create admins table if it doesn't exist
if (!$tableExists) {
    $sql = "CREATE TABLE admins (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        full_name VARCHAR(100) NOT NULL,
        role ENUM('admin', 'super_admin', 'editor') NOT NULL DEFAULT 'admin',
        status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
        last_login DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if ($conn_back->query($sql) === TRUE) {
        echo "Admins table created successfully<br>";
        
        // Create default admin account with password: admin123
        $username = 'admin';
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $email = 'admin@example.com';
        $fullName = 'System Administrator';
        $role = 'super_admin';
        
        $stmt = $conn_back->prepare("INSERT INTO admins (username, password, email, full_name, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $password, $email, $fullName, $role);
        
        if ($stmt->execute()) {
            echo "Default admin account created successfully<br>";
            echo "Username: admin<br>";
            echo "Password: admin123<br>";
            echo "<strong>Please change this password immediately after first login!</strong><br>";
        } else {
            echo "Error creating default admin account: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        echo "Error creating admins table: " . $conn_back->error;
    }
} else {
    echo "Admins table already exists<br>";
}

echo "<br><a href='/admin/login.php'>Go to Admin Login</a>";
?> 