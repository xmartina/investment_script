<?php
// Edit user page for admin panel
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$user_id = (int)$_GET['id'];

// Include necessary files
require_once __DIR__ . '/include/config.php';

// Set current page for menu highlighting
$current_page = 'users.php';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $first_name = $conn_back->real_escape_string($_POST['first_name']);
    $last_name = $conn_back->real_escape_string($_POST['last_name']);
    $email = $conn_back->real_escape_string($_POST['email']);
    $phone = $conn_back->real_escape_string($_POST['phone']);
    $status = isset($_POST['status']) ? $conn_back->real_escape_string($_POST['status']) : null;
    
    // Check if password is being updated
    $password_update = '';
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        $password_update = ", password = '$password'";
    }
    
    // Check if PIN is being updated
    $pin_update = '';
    if (!empty($_POST['pin'])) {
        $pin = (int)$_POST['pin'];
        $pin_update = ", pin = '$pin'";
    }
    
    // Prepare status update if available
    $status_update = '';
    if ($status !== null) {
        // First check if status column exists
        $check_status = $conn_back->query("SHOW COLUMNS FROM users LIKE 'status'");
        if ($check_status->num_rows > 0) {
            $status_update = ", status = '$status'";
        }
    }
    
    // Update query
    $query = "UPDATE users SET 
              first_name = '$first_name', 
              last_name = '$last_name', 
              email = '$email', 
              phone = '$phone'
              $password_update
              $pin_update
              $status_update
              WHERE id = $user_id";
    
    if ($conn_back->query($query)) {
        // Log the action
        $admin_id = $_SESSION['admin_id'];
        $action = "Updated user ID {$user_id} details";
        $ip = $_SERVER['REMOTE_ADDR'];
        
        $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
        $log_stmt->bind_param("iss", $admin_id, $action, $ip);
        $log_stmt->execute();
        
        $success_message = "User information updated successfully.";
    } else {
        $error_message = "Error updating user information: " . $conn_back->error;
    }
}

// Get user data
try {
    $user_query = $conn_back->prepare("
        SELECT u.id, u.first_name, u.last_name, u.email, u.phone, 
               u.profile_photo, u.pin, u.main_balance, u.investment_balance, 
               u.staking_balance, u.currency, u.created_at, u.referral_code, u.referred_by
        FROM users u 
        WHERE u.id = ?
    ");
    
    if (!$user_query) {
        throw new Exception("Database error: " . $conn_back->error);
    }
    
    $user_query->bind_param("i", $user_id);
    
    if (!$user_query->execute()) {
        throw new Exception("Query execution failed: " . $user_query->error);
    }
    
    $user_result = $user_query->get_result();

    if ($user_result->num_rows == 0) {
        // Include header and breadcrumb
        require_once __DIR__ . '/layout/header.php';
        require_once __DIR__ . '/layout/breadcrumb.php';
        
        echo '<div class="alert alert-danger">User with ID ' . $user_id . ' not found.</div>';
        require_once __DIR__ . '/layout/footer.php';
        exit();
    }

    $user = $user_result->fetch_assoc();
    
} catch (Exception $e) {
    // Include header and breadcrumb
    require_once __DIR__ . '/layout/header.php';
    require_once __DIR__ . '/layout/breadcrumb.php';
    
    echo '<div class="alert alert-danger">Error loading user: ' . $e->getMessage() . '</div>';
    require_once __DIR__ . '/layout/footer.php';
    exit;
}

// Check if status column exists in the users table 
$status_feature_available = false;
$user_status = '';

try {
    $check_column = $conn_back->query("SHOW COLUMNS FROM users LIKE 'status'");
    $status_feature_available = ($check_column->num_rows > 0);
    
    if ($status_feature_available) {
        // Get the user's status
        $status_query = $conn_back->prepare("SELECT status FROM users WHERE id = ?");
        $status_query->bind_param("i", $user_id);
        $status_query->execute();
        $status_result = $status_query->get_result();
        
        if ($status_result->num_rows > 0) {
            $status_row = $status_result->fetch_assoc();
            $user_status = $status_row['status'];
        }
    }
} catch (Exception $e) {
    $status_feature_available = false;
}

// Include header and breadcrumb
require_once __DIR__ . '/layout/header.php';
require_once __DIR__ . '/layout/breadcrumb.php';
?>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-12">
            <div class="box">
                <div class="box-header with-border d-flex justify-content-between align-items-center">
                    <h4 class="box-title">Edit User</h4>
                    <a href="user_detail.php?id=<?php echo $user_id; ?>" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to User Details
                    </a>
                </div>
                <div class="box-body">
                    <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <form method="post" class="form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Personal Information</h5>
                                        
                                        <div class="mb-3">
                                            <label for="first_name" class="form-label">First Name</label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="last_name" class="form-label">Last Name</label>
                                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone</label>
                                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                                        </div>
                                        
                                        <?php if ($status_feature_available): ?>
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="active" <?php echo ($user_status == 'active') ? 'selected' : ''; ?>>Active</option>
                                                <option value="pending" <?php echo ($user_status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                <option value="suspended" <?php echo ($user_status == 'suspended') ? 'selected' : ''; ?>>Suspended</option>
                                                <option value="blocked" <?php echo ($user_status == 'blocked') ? 'selected' : ''; ?>>Blocked</option>
                                            </select>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Security Information</h5>
                                        
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
                                            <small class="form-text text-muted">Only fill this if you want to change the user's password.</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="pin" class="form-label">PIN</label>
                                            <input type="text" class="form-control" id="pin" name="pin" placeholder="Leave blank to keep current PIN" 
                                                   pattern="\d{4}" title="PIN must be 4 digits" maxlength="4">
                                            <small class="form-text text-muted">Only fill this if you want to change the user's PIN. Must be 4 digits.</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="referral_code" class="form-label">Referral Code</label>
                                            <input type="text" class="form-control" id="referral_code" value="<?php echo htmlspecialchars($user['referral_code'] ?? ''); ?>" readonly>
                                            <small class="form-text text-muted">Referral code cannot be changed.</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="created_at" class="form-label">Registered On</label>
                                            <input type="text" class="form-control" id="created_at" value="<?php echo date('M d, Y H:i', strtotime($user['created_at'])); ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <button type="submit" class="btn btn-primary px-5">Update User</button>
                            <a href="user_detail.php?id=<?php echo $user_id; ?>" class="btn btn-outline-secondary px-5 ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add form validation
    const form = document.querySelector('form');
    
    form.addEventListener('submit', function(e) {
        let valid = true;
        
        // Basic email validation
        const email = document.getElementById('email').value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            valid = false;
            alert('Please enter a valid email address.');
        }
        
        // PIN validation if entered
        const pin = document.getElementById('pin').value;
        if (pin && (pin.length !== 4 || !/^\d{4}$/.test(pin))) {
            valid = false;
            alert('PIN must be exactly 4 digits.');
        }
        
        if (!valid) {
            e.preventDefault();
        }
    });
});
</script>

<?php
// Include footer
require_once __DIR__ . '/layout/footer.php';
?> 