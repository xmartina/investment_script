<?php
// Admin Site Settings Management
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Site Settings";
$current_page = "site_settings.php";
$message = "";
$error = "";

// Load settings from database
$settings = [];
$stmt = $conn_back->prepare("SELECT * FROM site_settings ORDER BY setting_group, id");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_group']][$row['setting_key']] = $row['setting_value'];
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_settings'])) {
        $group = $_POST['group'];
        $updated_settings = $_POST['settings'];
        
        // Begin transaction
        $conn_back->begin_transaction();
        
        try {
            foreach ($updated_settings as $key => $value) {
                // Check if setting exists
                $check_stmt = $conn_back->prepare("SELECT id FROM site_settings WHERE setting_key = ?");
                $check_stmt->bind_param("s", $key);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    // Update existing setting
                    $update_stmt = $conn_back->prepare("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
                    $update_stmt->bind_param("ss", $value, $key);
                    $update_stmt->execute();
                } else {
                    // Insert new setting
                    $insert_stmt = $conn_back->prepare("INSERT INTO site_settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?)");
                    $insert_stmt->bind_param("sss", $key, $value, $group);
                    $insert_stmt->execute();
                }
            }
            
            // Commit the transaction
            $conn_back->commit();
            
            // Log admin activity
            $admin_id = $_SESSION['admin_id'];
            $action = "Updated " . ucfirst($group) . " settings";
            $ip = $_SERVER['REMOTE_ADDR'];
            
            $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
            $log_stmt->bind_param("iss", $admin_id, $action, $ip);
            $log_stmt->execute();
            
            $message = ucfirst($group) . " settings updated successfully!";
            
            // Reload settings
            $settings = [];
            $stmt = $conn_back->prepare("SELECT * FROM site_settings ORDER BY setting_group, id");
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $settings[$row['setting_group']][$row['setting_key']] = $row['setting_value'];
                }
            }
        } catch (Exception $e) {
            // Rollback on error
            $conn_back->rollback();
            $error = "Error updating settings: " . $e->getMessage();
        }
    }
}

include_once __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Site Settings</h1>
        <a href="/admin/front_pages.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back to Pages
        </a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <!-- Card Header - Tabs -->
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <ul class="nav nav-tabs card-header-tabs" id="settingsTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-controls="general" aria-selected="true">General</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab" aria-controls="contact" aria-selected="false">Contact</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="social-tab" data-toggle="tab" href="#social" role="tab" aria-controls="social" aria-selected="false">Social Media</a>
                        </li>
                    </ul>
                </div>
                <!-- Card Body -->
                <div class="card-body">
                    <div class="tab-content" id="settingsTabContent">
                        <!-- General Settings Tab -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                            <form method="post" action="">
                                <input type="hidden" name="group" value="general">
                                <div class="form-group row">
                                    <label for="site_name" class="col-sm-2 col-form-label">Site Name</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="site_name" name="settings[site_name]" value="<?= htmlspecialchars($settings['general']['site_name'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="site_description" class="col-sm-2 col-form-label">Site Description</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control" id="site_description" name="settings[site_description]" rows="2"><?= htmlspecialchars($settings['general']['site_description'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="site_keywords" class="col-sm-2 col-form-label">Keywords</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="site_keywords" name="settings[site_keywords]" value="<?= htmlspecialchars($settings['general']['site_keywords'] ?? '') ?>">
                                        <small class="form-text text-muted">Comma-separated keywords for SEO</small>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-10 offset-sm-2">
                                        <button type="submit" name="save_settings" class="btn btn-primary">Save General Settings</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Contact Information Tab -->
                        <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                            <form method="post" action="">
                                <input type="hidden" name="group" value="contact">
                                <div class="form-group row">
                                    <label for="contact_email" class="col-sm-2 col-form-label">Contact Email</label>
                                    <div class="col-sm-10">
                                        <input type="email" class="form-control" id="contact_email" name="settings[contact_email]" value="<?= htmlspecialchars($settings['contact']['contact_email'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="contact_phone" class="col-sm-2 col-form-label">Contact Phone</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="contact_phone" name="settings[contact_phone]" value="<?= htmlspecialchars($settings['contact']['contact_phone'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="contact_address" class="col-sm-2 col-form-label">Address</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control" id="contact_address" name="settings[contact_address]" rows="3"><?= htmlspecialchars($settings['contact']['contact_address'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-10 offset-sm-2">
                                        <button type="submit" name="save_settings" class="btn btn-primary">Save Contact Settings</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Social Media Tab -->
                        <div class="tab-pane fade" id="social" role="tabpanel" aria-labelledby="social-tab">
                            <form method="post" action="">
                                <input type="hidden" name="group" value="social">
                                <div class="form-group row">
                                    <label for="facebook_url" class="col-sm-2 col-form-label">Facebook</label>
                                    <div class="col-sm-10">
                                        <input type="url" class="form-control" id="facebook_url" name="settings[facebook_url]" value="<?= htmlspecialchars($settings['social']['facebook_url'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="twitter_url" class="col-sm-2 col-form-label">Twitter</label>
                                    <div class="col-sm-10">
                                        <input type="url" class="form-control" id="twitter_url" name="settings[twitter_url]" value="<?= htmlspecialchars($settings['social']['twitter_url'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="instagram_url" class="col-sm-2 col-form-label">Instagram</label>
                                    <div class="col-sm-10">
                                        <input type="url" class="form-control" id="instagram_url" name="settings[instagram_url]" value="<?= htmlspecialchars($settings['social']['instagram_url'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="linkedin_url" class="col-sm-2 col-form-label">LinkedIn</label>
                                    <div class="col-sm-10">
                                        <input type="url" class="form-control" id="linkedin_url" name="settings[linkedin_url]" value="<?= htmlspecialchars($settings['social']['linkedin_url'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-10 offset-sm-2">
                                        <button type="submit" name="save_settings" class="btn btn-primary">Save Social Media Settings</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 