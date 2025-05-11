<?php
// Settings page for admin panel
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
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

// Define settings categories
$categories = [
    'general' => 'General Settings',
    'payment' => 'Payment Settings',
    'email' => 'Email Settings',
    'referral' => 'Referral Settings',
    'security' => 'Security Settings'
];

// Get current category
$current_category = isset($_GET['tab']) && array_key_exists($_GET['tab'], $categories) ? $_GET['tab'] : 'general';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_settings'])) {
        $settings = $_POST['settings'] ?? [];
        
        if (!empty($settings)) {
            $success = true;
            $message = "Settings updated successfully.";
            
            foreach ($settings as $key => $value) {
                // Clean and sanitize the key and value
                $key = $conn_back->real_escape_string($key);
                $value = $conn_back->real_escape_string($value);
                
                // Update or insert the setting
                $query = "INSERT INTO admin_settings (setting_key, setting_value) 
                          VALUES ('{$key}', '{$value}') 
                          ON DUPLICATE KEY UPDATE setting_value = '{$value}'";
                
                if (!$conn_back->query($query)) {
                    $success = false;
                    $message = "Error updating settings: " . $conn_back->error;
                    break;
                }
            }
            
            // Log the activity
            if ($success) {
                $admin_id = $_SESSION['admin_id'];
                $action = "Updated settings in {$categories[$current_category]}";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                $log_stmt->execute();
            }
        }
    }
}

// Get all settings
$settings = [];
try {
    $settings_query = "SELECT setting_key, setting_value FROM admin_settings";
    $settings_result = $conn_back->query($settings_query);
    
    if ($settings_result && $settings_result->num_rows > 0) {
        while ($row = $settings_result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
} catch (Exception $e) {
    // Table might not exist or there's a database error
    $error_message = "There was an issue loading settings: " . $e->getMessage();
    $error_message .= "<br>Please run the <a href='update_database.php' class='alert-link'>Database Update Tool</a> to fix this issue.";
}

// Default settings if not set
$default_settings = [
    // General settings
    'site_name' => 'Investment Platform',
    'site_url' => 'https://example.com',
    'admin_email' => 'admin@example.com',
    'site_currency' => 'USD',
    'site_currency_symbol' => '$',
    
    // Payment settings
    'min_deposit' => '50',
    'min_withdrawal' => '100',
    'withdrawal_fee_type' => 'percentage',
    'withdrawal_fee' => '2.5',
    
    // Email settings
    'email_from' => 'noreply@example.com',
    'email_name' => 'Investment Platform',
    'email_host' => 'smtp.example.com',
    'email_port' => '587',
    'email_encryption' => 'tls',
    'email_username' => '',
    'email_password' => '',
    
    // Referral settings
    'referral_enabled' => '1',
    'referral_commission' => '5',
    'referral_levels' => '1',
    
    // Security settings
    'login_attempts' => '5',
    'login_lockout_time' => '30',
    'require_email_verification' => '1',
    'enable_2fa' => '0',
    'maintenance_mode' => '0'
];

// Merge with defaults
foreach ($default_settings as $key => $value) {
    if (!isset($settings[$key])) {
        $settings[$key] = $value;
    }
}
?>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-12">
            <div class="box">
                <div class="box-header with-border">
                    <h4 class="box-title">System Settings</h4>
                </div>
                <div class="box-body">
                    <?php if (isset($message)): ?>
                    <div class="alert alert-<?php echo isset($success) && $success ? 'success' : 'danger'; ?> alert-dismissible">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?php echo $message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?php echo $error_message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Settings Tab Navigation -->
                    <ul class="nav nav-tabs" role="tablist">
                        <?php foreach ($categories as $tab => $name): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_category == $tab ? 'active' : ''; ?>" 
                               href="settings.php?tab=<?php echo $tab; ?>" role="tab">
                                <?php echo $name; ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <!-- Settings Form -->
                    <div class="tab-content pt-3">
                        <div class="tab-pane active">
                            <form method="post" class="form-horizontal">
                                <?php if ($current_category == 'general'): ?>
                                <!-- General Settings -->
                                <div class="form-group row mb-3">
                                    <label class="col-form-label col-md-3">Site Name</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" name="settings[site_name]" value="<?php echo htmlspecialchars($settings['site_name']); ?>">
                                        <small class="form-text text-muted">The name of your website</small>
                                    </div>
                                </div>
                                
                                <div class="form-group row mb-3">
                                    <label class="col-form-label col-md-3">Site URL</label>
                                    <div class="col-md-9">
                                        <input type="url" class="form-control" name="settings[site_url]" value="<?php echo htmlspecialchars($settings['site_url']); ?>">
                                        <small class="form-text text-muted">Full URL of your website (with https://)</small>
                                    </div>
                                </div>
                                
                                <div class="form-group row mb-3">
                                    <label class="col-form-label col-md-3">Admin Email</label>
                                    <div class="col-md-9">
                                        <input type="email" class="form-control" name="settings[admin_email]" value="<?php echo htmlspecialchars($settings['admin_email']); ?>">
                                        <small class="form-text text-muted">Main admin email address for notifications</small>
                                    </div>
                                </div>
                                
                                <div class="form-group row mb-3">
                                    <label class="col-form-label col-md-3">Currency</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" name="settings[site_currency]" value="<?php echo htmlspecialchars($settings['site_currency']); ?>">
                                        <small class="form-text text-muted">Default currency code (e.g., USD)</small>
                                    </div>
                                </div>
                                
                                <div class="form-group row mb-3">
                                    <label class="col-form-label col-md-3">Currency Symbol</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" name="settings[site_currency_symbol]" value="<?php echo htmlspecialchars($settings['site_currency_symbol']); ?>">
                                        <small class="form-text text-muted">Currency symbol (e.g., $)</small>
                                    </div>
                                </div>
                                
                                <?php elseif ($current_category == 'payment'): ?>
                                <!-- Payment Settings -->
                                <div class="form-group row mb-3">
                                    <label class="col-form-label col-md-3">Minimum Deposit</label>
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <span class="input-group-text"><?php echo htmlspecialchars($settings['site_currency_symbol']); ?></span>
                                            <input type="number" class="form-control" name="settings[min_deposit]" value="<?php echo htmlspecialchars($settings['min_deposit']); ?>" min="0" step="0.01">
                                        </div>
                                        <small class="form-text text-muted">Minimum amount users can deposit</small>
                                    </div>
                                </div>
                                
                                <div class="form-group row mb-3">
                                    <label class="col-form-label col-md-3">Minimum Withdrawal</label>
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <span class="input-group-text"><?php echo htmlspecialchars($settings['site_currency_symbol']); ?></span>
                                            <input type="number" class="form-control" name="settings[min_withdrawal]" value="<?php echo htmlspecialchars($settings['min_withdrawal']); ?>" min="0" step="0.01">
                                        </div>
                                        <small class="form-text text-muted">Minimum amount users can withdraw</small>
                                    </div>
                                </div>
                                
                                <div class="form-group row mb-3">
                                    <label class="col-form-label col-md-3">Withdrawal Fee Type</label>
                                    <div class="col-md-9">
                                        <select class="form-select" name="settings[withdrawal_fee_type]">
                                            <option value="percentage" <?php echo $settings['withdrawal_fee_type'] == 'percentage' ? 'selected' : ''; ?>>Percentage</option>
                                            <option value="fixed" <?php echo $settings['withdrawal_fee_type'] == 'fixed' ? 'selected' : ''; ?>>Fixed Amount</option>
                                        </select>
                                        <small class="form-text text-muted">How withdrawal fee is calculated</small>
                                    </div>
                                </div>
                                
                                <div class="form-group row mb-3">
                                    <label class="col-form-label col-md-3">Withdrawal Fee</label>
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="settings[withdrawal_fee]" value="<?php echo htmlspecialchars($settings['withdrawal_fee']); ?>" min="0" step="0.01">
                                            <span class="input-group-text"><?php echo $settings['withdrawal_fee_type'] == 'percentage' ? '%' : htmlspecialchars($settings['site_currency_symbol']); ?></span>
                                        </div>
                                        <small class="form-text text-muted">Fee applied to withdrawals</small>
                                    </div>
                                </div>
                                
                                <?php elseif ($current_category == 'email'): ?>
                                <!-- Email Settings -->
                                <div class="form-group row mb-3">
                                    <label class="col-form-label col-md-3">From Email</label>
                                    <div class="col-md-9">
                                        <input type="email" class="form-control" name="settings[email_from]" value="<?php echo htmlspecialchars($settings['email_from']); ?>">
                                        <small class="form-text text-muted">Email address used to send emails</small>
                                    </div>
                                </div>
                                
                                <div class="form-group row mb-3">
                                    <label class="col-form-label col-md-3">From Name</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" name="settings[email_name]" value="<?php echo htmlspecialchars($settings['email_name']); ?>">
                                        <small class="form-text text-muted">Name displayed as sender</small>
                                    </div>
                                </div>
                                
                                <div class="form-group row mb-3">
                                    <label class="col-form-label col-md-3">SMTP Host</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" name="settings[email_host]" value="<?php echo htmlspecialchars($settings['email_host']); ?>">
                                        <small class="form-text text-muted">SMTP server address</small>
                                    </div>
                                </div>
                                
                                <div class="form-group row mb-3">
                                    <label class="col-form-label col-md-3">SMTP Port</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" name="settings[email_port]" value="<?php echo htmlspecialchars($settings['email_port']); ?>">
                                        <small class="form-text text-muted">SMTP server port (usually 587 for TLS, 465 for SSL)</small>
                                    </div>
                                </div>
                                
                                <div class="form-group row mb-3">
                                    <label class="col-form-label col-md-3">Encryption</label>
                                    <div class="col-md-9">
                                        <select class="form-select" name="settings[email_encryption]">
                                            <option value="tls" <?php echo $settings['email_encryption'] == 'tls' ? 'selected' : ''; ?>>TLS</option>
                                            <option value="ssl" <?php echo $settings['email_encryption'] == 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                            <option value="none" <?php echo $settings['email_encryption'] == 'none' ? 'selected' : ''; ?>>None</option>
                                        </select>
                                        <small class="form-text text-muted">Encryption protocol</small>
                                    </div>
                                </div>
                                
                                <div class="form-group row mb-3">
                                    <label class="col-form-label col-md-3">SMTP Username</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" name="settings[email_username]" value="<?php echo htmlspecialchars($settings['email_username']); ?>">
                                        <small class="form-text text-muted">Username for SMTP authentication</small>
                                    </div>
                                </div>
                                
                                <div class="form-group row mb-3">
                                    <label class="col-form-label col-md-3">SMTP Password</label>
                                    <div class="col-md-9">
                                        <input type="password" class="form-control" name="settings[email_password]" value="<?php echo !empty($settings['email_password']) ? '********' : ''; ?>">
                                        <small class="form-text text-muted">Password for SMTP authentication (leave blank to keep current)</small>
                                    </div>
                                </div>
                                
                                <?php elseif ($current_category == 'referral'): ?>
                                <!-- Referral Settings -->
                                <div class="form-group row mb-3">
                                    <label class="col-form-label col-md-3">Enable Referrals</label>
                                    <div class="col-md-9">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="referral_enabled" name="settings[referral_enabled]" value="1" <?php echo $settings['referral_enabled'] == '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="referral_enabled">Enable referral system</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group row mb-3">
                                    <label class="col-form-label col-md-3">Referral Commission</label>
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="settings[referral_commission]" value="<?php echo htmlspecialchars($settings['referral_commission']); ?>" min="0" step="0.01">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        <small class="form-text text-muted">Percentage commission for referrals</small>
                                    </div>
                                </div>
                                
                                <div class="form-group row mb-3">
                                    <label class="col-form-label col-md-3">Referral Levels</label>
                                    <div class="col-md-9">
                                        <input type="number" class="form-control" name="settings[referral_levels]" value="<?php echo htmlspecialchars($settings['referral_levels']); ?>" min="1" max="10">
                                        <small class="form-text text-muted">Number of referral levels (1-10)</small>
                                    </div>
                                </div>
                                
                                <?php elseif ($current_category == 'security'): ?>
                                <!-- Security Settings -->
                                <div class="form-group row mb-3">
                                    <label class="col-form-label col-md-3">Login Attempts</label>
                                    <div class="col-md-9">
                                        <input type="number" class="form-control" name="settings[login_attempts]" value="<?php echo htmlspecialchars($settings['login_attempts']); ?>" min="1">
                                        <small class="form-text text-muted">Maximum number of failed login attempts before lockout</small>
                                    </div>
                                </div>
                                
                                <div class="form-group row mb-3">
                                    <label class="col-form-label col-md-3">Lockout Time</label>
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="settings[login_lockout_time]" value="<?php echo htmlspecialchars($settings['login_lockout_time']); ?>" min="1">
                                            <span class="input-group-text">minutes</span>
                                        </div>
                                        <small class="form-text text-muted">Time in minutes to lock account after failed attempts</small>
                                    </div>
                                </div>
                                
                                <div class="form-group row mb-3">
                                    <label class="col-form-label col-md-3">Email Verification</label>
                                    <div class="col-md-9">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="require_email_verification" name="settings[require_email_verification]" value="1" <?php echo $settings['require_email_verification'] == '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="require_email_verification">Require email verification for new accounts</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group row mb-3">
                                    <label class="col-form-label col-md-3">Two-Factor Authentication</label>
                                    <div class="col-md-9">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="enable_2fa" name="settings[enable_2fa]" value="1" <?php echo $settings['enable_2fa'] == '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="enable_2fa">Enable two-factor authentication option for users</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group row mb-3">
                                    <label class="col-form-label col-md-3">Maintenance Mode</label>
                                    <div class="col-md-9">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="maintenance_mode" name="settings[maintenance_mode]" value="1" <?php echo $settings['maintenance_mode'] == '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="maintenance_mode">Enable maintenance mode (only admins can access the site)</label>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="form-group row mt-4">
                                    <div class="col-md-9 offset-md-3">
                                        <button type="submit" name="update_settings" class="btn btn-primary">Save Settings</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->

<?php
// Include footer
require_once __DIR__ . '/layout/footer.php';
?> 