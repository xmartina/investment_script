<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database configuration
try {
    include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';
} catch (Exception $e) {
    die("Error including config file: " . $e->getMessage());
}

// Security check: Only allow access to super admins or if no admins exist yet
$allow_access = false;
$message = "";
$success = false;

try {
    // Check if admins table exists
    $table_exists = false;
    $tables_result = $conn_back->query("SHOW TABLES LIKE 'admins'");
    if ($tables_result) {
        $table_exists = $tables_result->num_rows > 0;
    }
    
    // If admins table doesn't exist, allow access to set up initial admin
    if (!$table_exists) {
        $allow_access = true;
    } 
    // Otherwise check for admin login
    else if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_role']) && $_SESSION['admin_role'] == 'super_admin') {
        $allow_access = true;
    }

    // Process initialization if allowed and form is submitted
    if ($allow_access && isset($_POST['initialize'])) {
        // Create admins table
        $conn_back->query("
            CREATE TABLE IF NOT EXISTS admins (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                full_name VARCHAR(100) NOT NULL,
                role ENUM('super_admin', 'admin') NOT NULL DEFAULT 'admin',
                status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
                last_login DATETIME,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        // Check if we should create default admin
        if (isset($_POST['create_admin']) && $_POST['create_admin'] == 1) {
            $admin_username = $conn_back->real_escape_string($_POST['admin_username'] ?? 'admin');
            $admin_password = password_hash($_POST['admin_password'] ?? 'admin123', PASSWORD_DEFAULT);
            $admin_email = $conn_back->real_escape_string($_POST['admin_email'] ?? 'admin@example.com');
            $admin_name = $conn_back->real_escape_string($_POST['admin_name'] ?? 'Super Admin');
            
            // Check if admin exists
            $check_admin = $conn_back->query("SELECT id FROM admins WHERE username = '{$admin_username}' OR email = '{$admin_email}'");
            
            if ($check_admin->num_rows == 0) {
                $conn_back->query("
                    INSERT INTO admins (username, password, email, full_name, role) 
                    VALUES ('{$admin_username}', '{$admin_password}', '{$admin_email}', '{$admin_name}', 'super_admin')
                ");
                
                $message .= "Default admin account created successfully.<br>";
            } else {
                $message .= "Admin with that username or email already exists.<br>";
            }
        }
        
        // Create admin_settings table
        $conn_back->query("
            CREATE TABLE IF NOT EXISTS admin_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(50) NOT NULL UNIQUE,
                setting_value TEXT,
                setting_description VARCHAR(255),
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        // Create admin_logs table
        $conn_back->query("
            CREATE TABLE IF NOT EXISTS admin_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                admin_id INT,
                action VARCHAR(255) NOT NULL,
                details TEXT,
                ip_address VARCHAR(45),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
            )
        ");
        
        $success = true;
        $message .= "Database initialization completed successfully!";
    }
} catch (Exception $e) {
    $message = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="<?=$siteLink?>/admin/images/favicon.ico">

    <title>Database Initialization - <?=$site_name?></title>
  
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Style-->  
    <link rel="stylesheet" href="<?=$siteLink?>/admin/css/style.css">
    <link rel="stylesheet" href="<?=$siteLink?>/admin/css/skin_color.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="hold-transition theme-primary bg-img" style="background-image: url(<?=$siteLink?>/admin/images/auth-bg/bg-1.jpg)">
    <div class="container h-p100">
        <div class="row align-items-center justify-content-md-center h-p100">    
            <div class="col-12">
                <div class="row justify-content-center g-0">
                    <div class="col-lg-6 col-md-8 col-12">
                        <div class="bg-white rounded10 shadow-lg">
                            <div class="content-top-agile p-20 pb-0">
                                <h2 class="text-primary">Database Initialization</h2>
                                <p class="mb-0">Setup the admin panel database tables</p>                            
                            </div>
                            <div class="p-40">
                                <?php if (!empty($message)): ?>
                                <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>"><?php echo $message; ?></div>
                                <?php endif; ?>
                                
                                <?php if (!$allow_access): ?>
                                <div class="alert alert-warning">
                                    <p>Access to this page is restricted to super administrators.</p>
                                    <p>Please <a href="login.php">login</a> with super admin credentials to continue.</p>
                                </div>
                                <?php elseif (!$success): ?>
                                <form action="" method="post">
                                    <div class="form-group mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="create_admin" name="create_admin" value="1" checked>
                                            <label class="form-check-label" for="create_admin">
                                                Create default super admin account
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div id="admin_details">
                                        <div class="form-group mb-3">
                                            <label for="admin_username">Admin Username</label>
                                            <input type="text" class="form-control" id="admin_username" name="admin_username" value="admin">
                                        </div>
                                        
                                        <div class="form-group mb-3">
                                            <label for="admin_password">Admin Password</label>
                                            <input type="password" class="form-control" id="admin_password" name="admin_password" value="admin123">
                                        </div>
                                        
                                        <div class="form-group mb-3">
                                            <label for="admin_email">Admin Email</label>
                                            <input type="email" class="form-control" id="admin_email" name="admin_email" value="admin@example.com">
                                        </div>
                                        
                                        <div class="form-group mb-3">
                                            <label for="admin_name">Admin Full Name</label>
                                            <input type="text" class="form-control" id="admin_name" name="admin_name" value="Super Admin">
                                        </div>
                                    </div>
                                    
                                    <button type="submit" name="initialize" class="btn btn-primary btn-block mt-3">Initialize Database</button>
                                </form>
                                
                                <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const createAdminCheckbox = document.getElementById('create_admin');
                                    const adminDetailsDiv = document.getElementById('admin_details');
                                    
                                    function toggleAdminDetails() {
                                        adminDetailsDiv.style.display = createAdminCheckbox.checked ? 'block' : 'none';
                                    }
                                    
                                    createAdminCheckbox.addEventListener('change', toggleAdminDetails);
                                    toggleAdminDetails();
                                });
                                </script>
                                <?php else: ?>
                                <div class="text-center">
                                    <p>Database initialization has been completed successfully.</p>
                                    <a href="login.php" class="btn btn-primary">Proceed to Login</a>
                                </div>
                                <?php endif; ?>
                            </div>                        
                        </div>
                        <div class="text-center mt-3">
                            <p class="mt-20 text-white">&copy; <?php echo date('Y'); ?> <?=$site_name?> - All Rights Reserved</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
