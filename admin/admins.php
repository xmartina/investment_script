<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/include/config.php';

// Check if the admin is logged in and has super_admin role
if (!isset($_SESSION['admin_id']) || !hasPermission('super_admin')) {
    header("Location: login.php");
    exit();
}

// Process admin actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add new admin
    if (isset($_POST['add_admin'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $full_name = trim($_POST['full_name']);
        $role = $_POST['role'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        $errors = [];
        
        // Validate inputs
        if (empty($username)) {
            $errors[] = "Username is required";
        }
        
        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
        
        if (empty($full_name)) {
            $errors[] = "Full name is required";
        }
        
        if (empty($password)) {
            $errors[] = "Password is required";
        } elseif (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters";
        }
        
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }
        
        // Check if username or email already exists
        $stmt = $conn_back->prepare("SELECT COUNT(*) as count FROM admins WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            $errors[] = "Username or email already exists";
        }
        
        if (empty($errors)) {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new admin
            $stmt = $conn_back->prepare("INSERT INTO admins (username, email, full_name, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $email, $full_name, $hashed_password, $role);
            
            if ($stmt->execute()) {
                logAdminActivity($_SESSION['admin_id'], 'Add Admin', "Added new admin: $username ($email) with role: $role");
                showAlert("Admin user added successfully", "success");
            } else {
                showAlert("Error adding admin user: " . $stmt->error, "danger");
            }
        } else {
            // Display errors
            showAlert(implode("<br>", $errors), "danger");
        }
        
        $stmt->close();
    }
    
    // Edit admin
    if (isset($_POST['edit_admin'])) {
        $admin_id = (int)$_POST['admin_id'];
        $email = trim($_POST['email']);
        $full_name = trim($_POST['full_name']);
        $role = $_POST['role'];
        $status = $_POST['status'];
        
        $errors = [];
        
        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
        
        if (empty($full_name)) {
            $errors[] = "Full name is required";
        }
        
        // Check if email already exists for another admin
        $stmt = $conn_back->prepare("SELECT COUNT(*) as count FROM admins WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            $errors[] = "Email already exists for another admin";
        }
        
        if (empty($errors)) {
            // Update admin
            $stmt = $conn_back->prepare("UPDATE admins SET email = ?, full_name = ?, role = ?, status = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $email, $full_name, $role, $status, $admin_id);
            
            if ($stmt->execute()) {
                logAdminActivity($_SESSION['admin_id'], 'Edit Admin', "Updated admin #$admin_id: $full_name ($email) with role: $role, status: $status");
                showAlert("Admin user updated successfully", "success");
            } else {
                showAlert("Error updating admin user: " . $stmt->error, "danger");
            }
        } else {
            // Display errors
            showAlert(implode("<br>", $errors), "danger");
        }
        
        $stmt->close();
    }
    
    // Change admin password
    if (isset($_POST['change_password'])) {
        $admin_id = (int)$_POST['admin_id'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        $errors = [];
        
        if (empty($new_password)) {
            $errors[] = "New password is required";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "Password must be at least 6 characters";
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }
        
        if (empty($errors)) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update admin password
            $stmt = $conn_back->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $admin_id);
            
            if ($stmt->execute()) {
                logAdminActivity($_SESSION['admin_id'], 'Change Admin Password', "Changed password for admin #$admin_id");
                showAlert("Admin password updated successfully", "success");
            } else {
                showAlert("Error updating admin password: " . $stmt->error, "danger");
            }
        } else {
            // Display errors
            showAlert(implode("<br>", $errors), "danger");
        }
        
        $stmt->close();
    }
    
    // Delete admin
    if (isset($_POST['delete_admin']) && isset($_POST['admin_id'])) {
        $admin_id = (int)$_POST['admin_id'];
        
        // Prevent deleting own account
        if ($admin_id == $_SESSION['admin_id']) {
            showAlert("You cannot delete your own account", "danger");
        } else {
            // Get admin info for logging
            $stmt = $conn_back->prepare("SELECT username, email FROM admins WHERE id = ?");
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();
            
            // Delete admin
            $stmt = $conn_back->prepare("DELETE FROM admins WHERE id = ?");
            $stmt->bind_param("i", $admin_id);
            
            if ($stmt->execute()) {
                logAdminActivity($_SESSION['admin_id'], 'Delete Admin', "Deleted admin: {$admin['username']} ({$admin['email']})");
                showAlert("Admin user deleted successfully", "success");
            } else {
                showAlert("Error deleting admin user: " . $stmt->error, "danger");
            }
        }
        
        $stmt->close();
    }
}

// Get admin users
$admins = [];
$stmt = $conn_back->prepare("SELECT * FROM admins ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $admins[] = $row;
}
$stmt->close();

include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layout/header.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layout/breadcrumb.php';
?>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-12">
            <div class="box">
                <div class="box-header with-border">
                    <h4 class="box-title">Admin Users Management</h4>
                    <div class="box-controls pull-right">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                            <i data-feather="user-plus"></i> Add New Admin
                        </button>
                    </div>
                </div>
                <div class="box-body">
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($admins) > 0): ?>
                                    <?php foreach ($admins as $admin): ?>
                                        <tr>
                                            <td><?php echo $admin['id']; ?></td>
                                            <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                            <td><?php echo htmlspecialchars($admin['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                            <td>
                                                <span class="badge badge-<?php 
                                                    echo $admin['role'] == 'super_admin' ? 'danger' : 
                                                        ($admin['role'] == 'admin' ? 'primary' : 'info'); 
                                                    ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $admin['role'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo $admin['status'] == 'active' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($admin['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($admin['last_login']): ?>
                                                    <?php echo date('M d, Y H:i', strtotime($admin['last_login'])); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Never</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editAdminModal<?php echo $admin['id']; ?>">
                                                        <i data-feather="edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#changePasswordModal<?php echo $admin['id']; ?>">
                                                        <i data-feather="key"></i>
                                                    </button>
                                                    <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAdminModal<?php echo $admin['id']; ?>">
                                                            <i data-feather="trash-2"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <!-- Edit Admin Modal -->
                                                <div class="modal fade" id="editAdminModal<?php echo $admin['id']; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edit Admin</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form action="" method="POST">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                                                    
                                                                    <div class="form-group mb-3">
                                                                        <label for="username" class="form-label">Username</label>
                                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($admin['username']); ?>" readonly>
                                                                        <small class="text-muted">Username cannot be changed</small>
                                                                    </div>
                                                                    
                                                                    <div class="form-group mb-3">
                                                                        <label for="email" class="form-label">Email</label>
                                                                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                                                                    </div>
                                                                    
                                                                    <div class="form-group mb-3">
                                                                        <label for="full_name" class="form-label">Full Name</label>
                                                                        <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
                                                                    </div>
                                                                    
                                                                    <div class="form-group mb-3">
                                                                        <label for="role" class="form-label">Role</label>
                                                                        <select class="form-select" name="role" <?php echo $admin['id'] == $_SESSION['admin_id'] ? 'disabled' : ''; ?>>
                                                                            <option value="super_admin" <?php echo $admin['role'] == 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                                                                            <option value="admin" <?php echo $admin['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                                            <option value="editor" <?php echo $admin['role'] == 'editor' ? 'selected' : ''; ?>>Editor</option>
                                                                        </select>
                                                                        <?php if ($admin['id'] == $_SESSION['admin_id']): ?>
                                                                            <input type="hidden" name="role" value="<?php echo $admin['role']; ?>">
                                                                            <small class="text-muted">You cannot change your own role</small>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    
                                                                    <div class="form-group mb-3">
                                                                        <label for="status" class="form-label">Status</label>
                                                                        <select class="form-select" name="status" <?php echo $admin['id'] == $_SESSION['admin_id'] ? 'disabled' : ''; ?>>
                                                                            <option value="active" <?php echo $admin['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                                            <option value="inactive" <?php echo $admin['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                                        </select>
                                                                        <?php if ($admin['id'] == $_SESSION['admin_id']): ?>
                                                                            <input type="hidden" name="status" value="<?php echo $admin['status']; ?>">
                                                                            <small class="text-muted">You cannot change your own status</small>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" name="edit_admin" class="btn btn-primary">Save Changes</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Change Password Modal -->
                                                <div class="modal fade" id="changePasswordModal<?php echo $admin['id']; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Change Password for <?php echo htmlspecialchars($admin['username']); ?></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form action="" method="POST">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                                                    
                                                                    <div class="form-group mb-3">
                                                                        <label for="new_password" class="form-label">New Password</label>
                                                                        <input type="password" class="form-control" name="new_password" required minlength="6">
                                                                    </div>
                                                                    
                                                                    <div class="form-group mb-3">
                                                                        <label for="confirm_password" class="form-label">Confirm Password</label>
                                                                        <input type="password" class="form-control" name="confirm_password" required minlength="6">
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" name="change_password" class="btn btn-warning">Change Password</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Delete Admin Modal -->
                                                <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                                    <div class="modal fade" id="deleteAdminModal<?php echo $admin['id']; ?>" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Delete Admin</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p>Are you sure you want to delete the admin user <strong><?php echo htmlspecialchars($admin['username']); ?></strong>?</p>
                                                                    <p>This action cannot be undone.</p>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <form action="" method="POST">
                                                                        <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                                                        <button type="submit" name="delete_admin" class="btn btn-danger">Delete Admin</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No admin users found</td>
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
<div class="modal fade" id="addAdminModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" name="role">
                            <option value="super_admin">Super Admin</option>
                            <option value="admin" selected>Admin</option>
                            <option value="editor">Editor</option>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required minlength="6">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" name="confirm_password" required minlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_admin" class="btn btn-primary">Add Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layout/footer.php';
?> 