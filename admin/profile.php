<?php
// Admin profile page
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
    $current_page = 'profile.php';
    
    require_once __DIR__ . '/layout/header.php';
    require_once __DIR__ . '/layout/breadcrumb.php';
} catch (Exception $e) {
    echo "Error loading required files: " . $e->getMessage();
    exit;
}

$admin_id = $_SESSION['admin_id'];

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update profile
    if (isset($_POST['action']) && $_POST['action'] == 'update_profile') {
        $full_name = $conn_back->real_escape_string($_POST['full_name']);
        $email = $conn_back->real_escape_string($_POST['email']);
        
        // Validate inputs
        $errors = [];
        
        if (empty($full_name)) {
            $errors[] = "Full name is required";
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required";
        }
        
        // Check if email already exists for another admin
        $email_check = $conn_back->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
        $email_check->bind_param("si", $email, $admin_id);
        $email_check->execute();
        $email_result = $email_check->get_result();
        
        if ($email_result->num_rows > 0) {
            $errors[] = "Email is already used by another admin";
        }
        
        if (empty($errors)) {
            $update_query = $conn_back->prepare("UPDATE admins SET full_name = ?, email = ? WHERE id = ?");
            $update_query->bind_param("ssi", $full_name, $email, $admin_id);
            
            if ($update_query->execute()) {
                // Log the action
                $action = "Updated profile information";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                $log_stmt->execute();
                
                $success_message = "Profile updated successfully.";
            } else {
                $error_message = "Error updating profile: " . $conn_back->error;
            }
        } else {
            $error_message = "Validation errors:<br>" . implode("<br>", $errors);
        }
    }
    
    // Change password
    if (isset($_POST['action']) && $_POST['action'] == 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate inputs
        $errors = [];
        
        if (empty($current_password)) {
            $errors[] = "Current password is required";
        }
        
        if (empty($new_password)) {
            $errors[] = "New password is required";
        } elseif (strlen($new_password) < 8) {
            $errors[] = "New password must be at least 8 characters";
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }
        
        if (empty($errors)) {
            // Verify current password
            $password_query = $conn_back->prepare("SELECT password FROM admins WHERE id = ?");
            $password_query->bind_param("i", $admin_id);
            $password_query->execute();
            $password_result = $password_query->get_result();
            $admin_data = $password_result->fetch_assoc();
            
            if (!password_verify($current_password, $admin_data['password'])) {
                $error_message = "Current password is incorrect";
            } else {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_query = $conn_back->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $update_query->bind_param("si", $hashed_password, $admin_id);
                
                if ($update_query->execute()) {
                    // Log the action
                    $action = "Changed account password";
                    $ip = $_SERVER['REMOTE_ADDR'];
                    
                    $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                    $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                    $log_stmt->execute();
                    
                    $success_message = "Password changed successfully.";
                } else {
                    $error_message = "Error changing password: " . $conn_back->error;
                }
            }
        } else {
            $error_message = "Validation errors:<br>" . implode("<br>", $errors);
        }
    }
}

// Get admin information
$admin_query = $conn_back->prepare("SELECT username, email, full_name, role, status, last_login, created_at FROM admins WHERE id = ?");
$admin_query->bind_param("i", $admin_id);
$admin_query->execute();
$admin_result = $admin_query->get_result();
$admin = $admin_result->fetch_assoc();

// Get recent activity
$activity_query = $conn_back->prepare("
    SELECT action, ip_address, created_at 
    FROM admin_logs 
    WHERE admin_id = ? 
    ORDER BY created_at DESC 
    LIMIT 10
");
$activity_query->bind_param("i", $admin_id);
$activity_query->execute();
$activity_result = $activity_query->get_result();
$activities = [];
while ($activity = $activity_result->fetch_assoc()) {
    $activities[] = $activity;
}
?>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-4">
            <!-- Profile Information -->
            <div class="box">
                <div class="box-header with-border">
                    <h4 class="box-title">Profile Information</h4>
                </div>
                <div class="box-body">
                    <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?php echo $success_message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?php echo $error_message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="text-center mb-4">
                        <div class="avatar avatar-xxl mb-3">
                            <span class="avatar-initial rounded-circle bg-primary">
                                <?php echo strtoupper(substr($admin['username'], 0, 1)); ?>
                            </span>
                        </div>
                        <h4 class="mb-0"><?php echo htmlspecialchars($admin['full_name']); ?></h4>
                        <p class="text-muted">
                            <span class="badge <?php echo $admin['role'] == 'super_admin' ? 'badge-danger' : 'badge-info'; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $admin['role'])); ?>
                            </span>
                        </p>
                    </div>
                    
                    <div class="list-group mb-4">
                        <div class="list-group-item">
                            <small class="text-muted d-block">Username</small>
                            <span class="h6"><?php echo htmlspecialchars($admin['username']); ?></span>
                        </div>
                        <div class="list-group-item">
                            <small class="text-muted d-block">Email</small>
                            <span class="h6"><?php echo htmlspecialchars($admin['email']); ?></span>
                        </div>
                        <div class="list-group-item">
                            <small class="text-muted d-block">Status</small>
                            <span class="badge <?php echo $admin['status'] == 'active' ? 'badge-success' : 'badge-warning'; ?>">
                                <?php echo ucfirst($admin['status']); ?>
                            </span>
                        </div>
                        <div class="list-group-item">
                            <small class="text-muted d-block">Last Login</small>
                            <span class="h6"><?php echo $admin['last_login'] ? date('M d, Y H:i', strtotime($admin['last_login'])) : 'Never'; ?></span>
                        </div>
                        <div class="list-group-item">
                            <small class="text-muted d-block">Account Created</small>
                            <span class="h6"><?php echo date('M d, Y', strtotime($admin['created_at'])); ?></span>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="fa fa-edit me-1"></i> Edit Profile
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Change Password -->
            <div class="box">
                <div class="box-header with-border">
                    <h4 class="box-title">Security</h4>
                </div>
                <div class="box-body">
                    <div class="d-grid">
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <i class="fa fa-key me-1"></i> Change Password
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <!-- Recent Activity -->
            <div class="box">
                <div class="box-header with-border">
                    <h4 class="box-title">Recent Activity</h4>
                </div>
                <div class="box-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>IP Address</th>
                                    <th>Date & Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($activities) > 0): ?>
                                    <?php foreach ($activities as $activity): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($activity['action']); ?></td>
                                        <td><?php echo htmlspecialchars($activity['ip_address']); ?></td>
                                        <td><?php echo date('M d, Y H:i:s', strtotime($activity['created_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">No recent activity found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if (count($activities) > 0): ?>
                <div class="box-footer text-center">
                    <a href="logs.php?admin_id=<?php echo $admin_id; ?>" class="btn btn-link">View All Activity</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($admin['username']); ?>" disabled>
                        <small class="form-text text-muted">Username cannot be changed</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="passwordForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <small class="form-text text-muted">Password must be at least 8 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password validation
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters');
                return false;
            }
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return false;
            }
            
            return true;
        });
    }
});
</script>

<?php
// Include footer
require_once __DIR__ . '/layout/footer.php';
?> 