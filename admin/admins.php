<?php
// Admin users management page
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Check if the admin is logged in and is a super admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || $_SESSION['admin_role'] != 'super_admin') {
    header("Location: login.php");
    exit();
}

// Include necessary files
try {
    require_once __DIR__ . '/include/config.php';
    
    // Set current page for menu highlighting
    $current_page = 'admins.php';
    
    require_once __DIR__ . '/layout/header.php';
    require_once __DIR__ . '/layout/breadcrumb.php';
} catch (Exception $e) {
    echo "Error loading required files: " . $e->getMessage();
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add new admin
    if (isset($_POST['action']) && $_POST['action'] == 'add_admin') {
        $username = $conn_back->real_escape_string($_POST['username']);
        $email = $conn_back->real_escape_string($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $full_name = $conn_back->real_escape_string($_POST['full_name']);
        $role = $conn_back->real_escape_string($_POST['role']);
        
        // Validate inputs
        $errors = [];
        
        if (empty($username)) {
            $errors[] = "Username is required";
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required";
        }
        
        if (empty($password)) {
            $errors[] = "Password is required";
        } elseif (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters";
        } elseif ($password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }
        
        if (empty($full_name)) {
            $errors[] = "Full name is required";
        }
        
        if (!in_array($role, ['admin', 'super_admin'])) {
            $errors[] = "Invalid role selected";
        }
        
        // Check if username or email already exists
        $check_query = $conn_back->prepare("SELECT id FROM admins WHERE username = ? OR email = ?");
        $check_query->bind_param("ss", $username, $email);
        $check_query->execute();
        $check_result = $check_query->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = "Username or email already exists";
        }
        
        if (empty($errors)) {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new admin
            $insert_query = $conn_back->prepare("
                INSERT INTO admins (username, password, email, full_name, role)
                VALUES (?, ?, ?, ?, ?)
            ");
            $insert_query->bind_param("sssss", $username, $hashed_password, $email, $full_name, $role);
            
            if ($insert_query->execute()) {
                // Log admin creation
                $admin_id = $_SESSION['admin_id'];
                $action = "Created new admin account: {$username} with role {$role}";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                $log_stmt->execute();
                
                $success_message = "Admin user created successfully.";
            } else {
                $error_message = "Error creating admin user: " . $conn_back->error;
            }
        } else {
            $error_message = "Validation errors:<br>" . implode("<br>", $errors);
        }
    }
    
    // Change admin status
    if (isset($_POST['action']) && $_POST['action'] == 'change_status') {
        $admin_id = (int)$_POST['admin_id'];
        $status = $conn_back->real_escape_string($_POST['status']);
        
        // Don't allow deactivating your own account
        if ($admin_id == $_SESSION['admin_id']) {
            $error_message = "You cannot change your own account status.";
        } else {
            if (in_array($status, ['active', 'inactive'])) {
                $status_query = $conn_back->prepare("UPDATE admins SET status = ? WHERE id = ?");
                $status_query->bind_param("si", $status, $admin_id);
                
                if ($status_query->execute()) {
                    // Log status change
                    $admin_id_actor = $_SESSION['admin_id'];
                    $action = "Changed admin ID {$admin_id} status to {$status}";
                    $ip = $_SERVER['REMOTE_ADDR'];
                    
                    $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                    $log_stmt->bind_param("iss", $admin_id_actor, $action, $ip);
                    $log_stmt->execute();
                    
                    $success_message = "Admin status updated successfully.";
                } else {
                    $error_message = "Error updating admin status: " . $conn_back->error;
                }
            } else {
                $error_message = "Invalid status value.";
            }
        }
    }
    
    // Reset admin password
    if (isset($_POST['action']) && $_POST['action'] == 'reset_password') {
        $admin_id = (int)$_POST['admin_id'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($new_password)) {
            $error_message = "Password is required";
        } elseif (strlen($new_password) < 8) {
            $error_message = "Password must be at least 8 characters";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "Passwords do not match";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $password_query = $conn_back->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $password_query->bind_param("si", $hashed_password, $admin_id);
            
            if ($password_query->execute()) {
                // Log password reset
                $admin_id_actor = $_SESSION['admin_id'];
                $action = "Reset password for admin ID {$admin_id}";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id_actor, $action, $ip);
                $log_stmt->execute();
                
                $success_message = "Admin password reset successfully.";
            } else {
                $error_message = "Error resetting admin password: " . $conn_back->error;
            }
        }
    }
}

// Get admin users
$query = "SELECT id, username, email, full_name, role, status, last_login, created_at FROM admins ORDER BY id";
$result = $conn_back->query($query);
$admin_users = [];
if ($result && $result->num_rows > 0) {
while ($row = $result->fetch_assoc()) {
        $admin_users[] = $row;
    }
}
?>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-12">
            <div class="box">
                <div class="box-header with-border">
                    <h4 class="box-title">Admin Users</h4>
                    <div class="box-controls pull-right">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                            <i class="fa fa-plus"></i> Add New Admin
                        </button>
                    </div>
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
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Created On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($admin_users) > 0): ?>
                                    <?php foreach ($admin_users as $admin): ?>
                                        <tr>
                                            <td><?php echo $admin['id']; ?></td>
                                            <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                            <td><?php echo htmlspecialchars($admin['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                            <td>
                                            <span class="badge <?php echo $admin['role'] == 'super_admin' ? 'badge-danger' : 'badge-info'; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $admin['role'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                            <span class="badge <?php echo $admin['status'] == 'active' ? 'badge-success' : 'badge-warning'; ?>">
                                                    <?php echo ucfirst($admin['status']); ?>
                                                </span>
                                            </td>
                                        <td><?php echo $admin['last_login'] ? date('M d, Y H:i', strtotime($admin['last_login'])) : 'Never'; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($admin['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Action
                                                    </button>
                                                <div class="dropdown-menu">
                                                    <?php if ($_SESSION['admin_id'] != $admin['id']): ?>
                                                        <?php if ($admin['status'] == 'active'): ?>
                                                        <form method="post">
                                                            <input type="hidden" name="action" value="change_status">
                                                            <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                                            <input type="hidden" name="status" value="inactive">
                                                            <button type="submit" class="dropdown-item" onclick="return confirm('Are you sure you want to deactivate this admin?')">
                                                                <i class="fa fa-ban text-warning"></i> Deactivate
                                                    </button>
                                                        </form>
                                                        <?php else: ?>
                                                        <form method="post">
                                                            <input type="hidden" name="action" value="change_status">
                                                            <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                                            <input type="hidden" name="status" value="active">
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="fa fa-check text-success"></i> Activate
                                                        </button>
                                                        </form>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    
                                                    <button type="button" class="dropdown-item" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#resetPasswordModal" 
                                                            data-admin-id="<?php echo $admin['id']; ?>"
                                                            data-admin-name="<?php echo htmlspecialchars($admin['full_name']); ?>">
                                                        <i class="fa fa-key text-primary"></i> Reset Password
                                                    </button>
                                                        </div>
                                                    </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                    <td colspan="9" class="text-center">No admin users found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->

<!-- Add Admin Modal -->
<div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAdminModalLabel">Add New Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_admin">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="form-text text-muted">Password must be at least 8 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                        <small class="form-text text-muted">
                            <strong>Admin:</strong> Can manage users, deposits, withdrawals, investments<br>
                            <strong>Super Admin:</strong> Has full access including admin management, settings
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetPasswordModalLabel">Reset Admin Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="reset_password">
                    <input type="hidden" name="admin_id" id="reset_admin_id">
                    
                    <p>You are about to reset the password for: <strong id="reset_admin_name"></strong></p>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <small class="form-text text-muted">Password must be at least 8 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_new_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_new_password" name="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle password reset modal
    const resetPasswordModal = document.getElementById('resetPasswordModal');
    if (resetPasswordModal) {
        resetPasswordModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const adminId = button.getAttribute('data-admin-id');
            const adminName = button.getAttribute('data-admin-name');
            
            document.getElementById('reset_admin_id').value = adminId;
            document.getElementById('reset_admin_name').textContent = adminName;
        });
    }
    
    // Password validation for add admin form
    const addForm = document.querySelector('#addAdminModal form');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters');
                return false;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return false;
            }
            
            return true;
        });
    }
    
    // Password validation for reset password form
    const resetForm = document.querySelector('#resetPasswordModal form');
    if (resetForm) {
        resetForm.addEventListener('submit', function(e) {
            const password = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_new_password').value;
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters');
                return false;
            }
            
            if (password !== confirmPassword) {
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