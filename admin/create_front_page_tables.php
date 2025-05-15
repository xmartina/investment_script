<?php
// Script to create front page management tables
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Create Front Page Tables";
$current_page = "front_pages.php";
$message = "";
$error = "";

// Create front_pages table
$sql_front_pages = "CREATE TABLE IF NOT EXISTS `front_pages` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `page_id` VARCHAR(50) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `content` LONGTEXT NOT NULL,
    `meta_description` TEXT NULL,
    `meta_keywords` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `page_id` (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

// Create layout_components table
$sql_layout_components = "CREATE TABLE IF NOT EXISTS `layout_components` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `component_id` VARCHAR(50) NOT NULL,
    `content` LONGTEXT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `component_id` (`component_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

// Create navigation_menu table
$sql_navigation_menu = "CREATE TABLE IF NOT EXISTS `navigation_menu` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `label` VARCHAR(100) NOT NULL,
    `url` VARCHAR(255) NOT NULL,
    `parent_id` INT NOT NULL DEFAULT 0,
    `menu_order` INT NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

// Create sliders table
$sql_sliders = "CREATE TABLE IF NOT EXISTS `sliders` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `subtitle` TEXT NULL,
    `button_text` VARCHAR(100) NULL,
    `button_url` VARCHAR(255) NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `slide_order` INT NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

// Create site_settings table
$sql_site_settings = "CREATE TABLE IF NOT EXISTS `site_settings` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT NOT NULL,
    `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

// Create media_library table
$sql_media_library = "CREATE TABLE IF NOT EXISTS `media_library` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_type` VARCHAR(50) NOT NULL,
    `file_size` INT NOT NULL,
    `uploaded_by` INT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

// Execute the SQL statements
$tables = [
    'front_pages' => $sql_front_pages,
    'layout_components' => $sql_layout_components,
    'navigation_menu' => $sql_navigation_menu,
    'sliders' => $sql_sliders,
    'site_settings' => $sql_site_settings,
    'media_library' => $sql_media_library
];

$success_tables = [];
$error_tables = [];

foreach ($tables as $table_name => $sql) {
    if ($conn_back->query($sql) === TRUE) {
        $success_tables[] = $table_name;
    } else {
        $error_tables[$table_name] = $conn_back->error;
    }
}

// Insert default navigation menu items if table is empty
$check_navigation = $conn_back->query("SELECT COUNT(*) as count FROM navigation_menu");
$nav_count = $check_navigation->fetch_assoc()['count'];

if ($nav_count == 0 && in_array('navigation_menu', $success_tables)) {
    $default_menu_items = [
        ['Home', '/', 0, 1, 1],
        ['About', '/about', 0, 2, 1],
        ['FAQ', '/faq', 0, 3, 1],
        ['Contact', '/contact', 0, 4, 1]
    ];
    
    $insert_nav_stmt = $conn_back->prepare("INSERT INTO navigation_menu (label, url, parent_id, menu_order, status) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($default_menu_items as $item) {
        $insert_nav_stmt->bind_param("ssiii", $item[0], $item[1], $item[2], $item[3], $item[4]);
        $insert_nav_stmt->execute();
    }
    
    // Create navigation.php file
    $nav_file = $_SERVER['DOCUMENT_ROOT'] . '/layout/navigation.php';
    $nav_content = "<?php\n// Navigation Menu - Auto-generated\n\n";
    $nav_content .= "// This file is auto-generated by the admin panel. Do not edit manually.\n\n";
    $nav_content .= "function get_navigation_menu() {\n";
    $nav_content .= "    return [\n";
    
    foreach ($default_menu_items as $item) {
        $nav_content .= "        [\n";
        $nav_content .= "            'label' => '" . $item[0] . "',\n";
        $nav_content .= "            'url' => '" . $item[1] . "',\n";
        $nav_content .= "            'parent_id' => " . $item[2] . ",\n";
        $nav_content .= "            'menu_order' => " . $item[3] . "\n";
        $nav_content .= "        ],\n";
    }
    
    $nav_content .= "    ];\n";
    $nav_content .= "}\n";
    
    // Create directory if it doesn't exist
    $nav_dir = $_SERVER['DOCUMENT_ROOT'] . '/layout';
    if (!file_exists($nav_dir)) {
        mkdir($nav_dir, 0755, true);
    }
    
    // Save navigation file
    file_put_contents($nav_file, $nav_content);
}

// Insert default site settings
$check_settings = $conn_back->query("SELECT COUNT(*) as count FROM site_settings");
$settings_count = $check_settings->fetch_assoc()['count'];

if ($settings_count == 0 && in_array('site_settings', $success_tables)) {
    $default_settings = [
        ['site_name', 'Investment Platform', 'general'],
        ['site_description', 'Your Trusted Investment Platform', 'general'],
        ['site_keywords', 'investment, crypto, bitcoin, ethereum, finance', 'general'],
        ['contact_email', 'support@example.com', 'contact'],
        ['contact_phone', '+1 234 567 8900', 'contact'],
        ['contact_address', '123 Main St, New York, NY 10001', 'contact'],
        ['facebook_url', 'https://facebook.com/', 'social'],
        ['twitter_url', 'https://twitter.com/', 'social'],
        ['instagram_url', 'https://instagram.com/', 'social'],
        ['linkedin_url', 'https://linkedin.com/', 'social']
    ];
    
    $insert_setting_stmt = $conn_back->prepare("INSERT INTO site_settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?)");
    
    foreach ($default_settings as $setting) {
        $insert_setting_stmt->bind_param("sss", $setting[0], $setting[1], $setting[2]);
        $insert_setting_stmt->execute();
    }
}

// Log admin activity
$admin_id = $_SESSION['admin_id'];
$action = "Created front page management tables";
$ip = $_SERVER['REMOTE_ADDR'];

$log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
$log_stmt->bind_param("iss", $admin_id, $action, $ip);
$log_stmt->execute();

include_once __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create Front Page Management Tables</h1>
        <a href="/admin/front_pages.php" class="btn btn-primary">
            <i class="fas fa-arrow-right mr-2"></i> Go to Front Pages
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Database Setup Results</h6>
        </div>
        <div class="card-body">
            <?php if (count($success_tables) > 0): ?>
                <div class="alert alert-success">
                    <h5>Tables Created Successfully:</h5>
                    <ul>
                        <?php foreach ($success_tables as $table): ?>
                            <li><?= $table ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (count($error_tables) > 0): ?>
                <div class="alert alert-danger">
                    <h5>Tables With Errors:</h5>
                    <ul>
                        <?php foreach ($error_tables as $table => $error): ?>
                            <li><?= $table ?>: <?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <p>To manage the front pages of your website, go to <a href="/admin/front_pages.php">Front Pages Management</a>.</p>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 