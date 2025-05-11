<?php
// Update database structure page for admin panel
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Include necessary files
try {
    require_once __DIR__ . '/include/config.php';
    
    // Set current page for menu highlighting
    $current_page = 'settings.php';
    
    require_once __DIR__ . '/layout/header.php';
    require_once __DIR__ . '/layout/breadcrumb.php';
} catch (Exception $e) {
    echo "Error loading required files: " . $e->getMessage();
    exit;
}

$message = '';
$success = false;

// Process update request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_database'])) {
    try {
        // Create admin_settings table if it doesn't exist
        $conn_back->query("
            CREATE TABLE IF NOT EXISTS admin_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(50) NOT NULL UNIQUE,
                setting_value TEXT,
                setting_description VARCHAR(255),
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        // Insert default settings if they don't exist
        $default_settings = [
            // General Settings
            ['site_name', 'Investment Platform', 'The name of your website'],
            ['site_url', 'https://example.com', 'Full URL of your website'],
            ['admin_email', 'admin@example.com', 'Main admin email address for notifications'],
            ['site_currency', 'USD', 'Default currency code'],
            ['site_currency_symbol', '$', 'Currency symbol'],
            
            // Payment Settings
            ['min_deposit', '50', 'Minimum deposit amount'],
            ['min_withdrawal', '100', 'Minimum withdrawal amount'],
            ['withdrawal_fee_type', 'percentage', 'How withdrawal fee is calculated'],
            ['withdrawal_fee', '2.5', 'Withdrawal fee percentage'],
            ['deposit_fee', '0', 'Deposit fee percentage'],
            
            // Email Settings
            ['email_from', 'no-reply@example.com', 'Email address used to send emails'],
            ['email_name', 'Investment Platform', 'Name displayed as sender'],
            ['email_host', 'smtp.example.com', 'SMTP server address'],
            ['email_port', '587', 'SMTP server port'],
            ['email_encryption', 'tls', 'Email encryption method (tls/ssl)'],
            ['email_username', 'smtp_username', 'SMTP username'],
            ['email_password', 'smtp_password', 'SMTP password'],
            
            // Referral Settings
            ['referral_enabled', '1', 'Enable referral system'],
            ['referral_commission', '5', 'Referral commission percentage'],
            ['referral_minimum_deposit', '100', 'Minimum deposit for referral commission'],
            ['referral_levels', '1', 'Number of referral levels'],
            
            // Security Settings
            ['login_attempts', '5', 'Maximum number of failed login attempts before lockout'],
            ['login_lockout_time', '30', 'Time in minutes to lock account after failed attempts'],
            ['require_email_verification', '1', 'Require email verification for new accounts'],
            ['enable_2fa', '0', 'Enable two-factor authentication option for users'],
            ['maintenance_mode', '0', 'Enable maintenance mode']
        ];
        
        $inserted_count = 0;
        foreach ($default_settings as $setting) {
            $key = $conn_back->real_escape_string($setting[0]);
            $value = $conn_back->real_escape_string($setting[1]);
            $description = $conn_back->real_escape_string($setting[2]);
            
            // Check if setting already exists
            $check_setting = $conn_back->query("SELECT id FROM admin_settings WHERE setting_key = '{$key}'");
            
            if ($check_setting->num_rows == 0) {
                $conn_back->query("
                    INSERT INTO admin_settings (setting_key, setting_value, setting_description) 
                    VALUES ('{$key}', '{$value}', '{$description}')
                ");
                $inserted_count++;
            }
        }
        
        // Log the action
        $admin_id = $_SESSION['admin_id'];
        $action = "Updated database structure and initialized admin settings";
        $ip = $_SERVER['REMOTE_ADDR'];
        
        $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
        $log_stmt->bind_param("iss", $admin_id, $action, $ip);
        $log_stmt->execute();
        
        $success = true;
        $message = "Database structure updated successfully. Admin settings table created and initialized with {$inserted_count} default settings.";
        
    } catch (Exception $e) {
        $message = "Error updating database structure: " . $e->getMessage();
    }
}
?>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-12">
            <div class="box">
                <div class="box-header with-border">
                    <h4 class="box-title">Update Database Structure</h4>
                </div>
                <div class="box-body">
                    <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?> alert-dismissible">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?php echo $message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="callout callout-info">
                        <h5>Database Update Tool</h5>
                        <p>This tool will update your database structure to match the current application requirements. It will:</p>
                        <ul>
                            <li>Create the <code>admin_settings</code> table if it doesn't exist</li>
                            <li>Initialize default settings if they don't exist</li>
                        </ul>
                        <p><strong>Note:</strong> This operation is safe to run multiple times. It won't overwrite existing settings.</p>
                    </div>
                    
                    <form method="post" class="mt-4">
                        <div class="form-group">
                            <button type="submit" name="update_database" class="btn btn-primary">Update Database Structure</button>
                            <a href="settings.php" class="btn btn-outline-secondary ms-2">Back to Settings</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
require_once __DIR__ . '/layout/footer.php';
?> 