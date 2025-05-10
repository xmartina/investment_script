<?php
// Settings page
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /user/login");
    exit();
}

$user_id = $_SESSION['user_id'];
$page_name = "Account Settings";
$css_files = [];
$js_files = [];

// Initialize messages
$success_message = '';
$error_message = '';

// Get user data
$stmt = $conn_back->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Format profile photo
$profile_photo = !empty($user_data['profile_photo']) ? $user_data['profile_photo'] : '/back_assets/img/users/profile_photo/default_photo.jpg';
if (strpos($profile_photo, 'http') !== 0 && strpos($profile_photo, '/') !== 0) {
    $profile_photo = '/' . $profile_photo;
}

// Handle encoding of spaces in profile photo URL
$profile_photo = preg_replace_callback('/\s/', function($match) {
    return rawurlencode($match[0]);
}, $profile_photo);

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Profile Update
    if (isset($_POST['update_profile'])) {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $phone = trim($_POST['phone']);
        $currency = trim($_POST['currency']);
        
        // Validate inputs
        if (empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
            $error_message = "All fields are required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Invalid email format";
        } else {
            // Check if email already exists for a different user
            $stmt = $conn_back->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $email, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error_message = "Email is already in use by another account";
            } else {
                // Update user profile
                $stmt = $conn_back->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, currency = ? WHERE id = ?");
                $stmt->bind_param("sssssi", $first_name, $last_name, $email, $phone, $currency, $user_id);
                
                if ($stmt->execute()) {
                    $success_message = "Profile updated successfully";
                    
                    // Update session variables if needed
                    $_SESSION['user_email'] = $email;
                    
                    // Refresh user data
                    $stmt = $conn_back->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $user_data = $stmt->get_result()->fetch_assoc();
                } else {
                    $error_message = "Failed to update profile: " . $conn_back->error;
                }
            }
            $stmt->close();
        }
    }
    
    // Profile Photo Update
    elseif (isset($_POST['update_photo'])) {
        // Check if file was uploaded
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['profile_photo']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                $error_message = 'Invalid file type. Only JPG, PNG, and GIF images are allowed.';
            } else {
                // Create uploads directory if it doesn't exist
                $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/profile_photos/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                // Generate unique filename
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', basename($_FILES['profile_photo']['name']));
                $target_file = $upload_dir . $filename;
                
                // Attempt to upload the file
                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_file)) {
                    $profile_photo_path = '/profile_photos/' . $filename;
                    
                    // Update profile photo in database
                    $stmt = $conn_back->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
                    $stmt->bind_param("si", $profile_photo_path, $user_id);
                    
                    if ($stmt->execute()) {
                        $success_message = "Profile photo updated successfully";
                        $profile_photo = $profile_photo_path;
                    } else {
                        $error_message = "Failed to update profile photo record: " . $conn_back->error;
                    }
                    $stmt->close();
                } else {
                    $error_message = 'Failed to upload profile photo. Please try again.';
                }
            }
        } else {
            $error_message = 'Please select a file to upload.';
        }
    }
    
    // PIN Update
    elseif (isset($_POST['update_pin'])) {
        $current_pin = trim($_POST['current_pin']);
        $new_pin = trim($_POST['new_pin']);
        $confirm_pin = trim($_POST['confirm_pin']);
        
        // Validate inputs
        if (empty($current_pin) || empty($new_pin) || empty($confirm_pin)) {
            $error_message = "All PIN fields are required";
        } elseif ($current_pin != $user_data['pin']) {
            $error_message = "Current PIN is incorrect";
        } elseif ($new_pin != $confirm_pin) {
            $error_message = "New PIN and Confirm PIN do not match";
        } elseif (!preg_match('/^\d{4}$/', $new_pin)) {
            $error_message = "PIN must be exactly 4 digits";
        } else {
            // Update PIN
            $stmt = $conn_back->prepare("UPDATE users SET pin = ? WHERE id = ?");
            $stmt->bind_param("si", $new_pin, $user_id);
            
            if ($stmt->execute()) {
                $success_message = "PIN updated successfully";
            } else {
                $error_message = "Failed to update PIN: " . $conn_back->error;
            }
            $stmt->close();
        }
    }
    
    // Password Update
    elseif (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate inputs
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = "All password fields are required";
        } elseif ($new_password != $confirm_password) {
            $error_message = "New password and confirm password do not match";
        } elseif (strlen($new_password) < 8) {
            $error_message = "Password must be at least 8 characters long";
        } else {
            // Verify current password
            if (password_verify($current_password, $user_data['password'])) {
                // Hash the new password
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password
                $stmt = $conn_back->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $password_hash, $user_id);
                
                if ($stmt->execute()) {
                    $success_message = "Password updated successfully";
                } else {
                    $error_message = "Failed to update password: " . $conn_back->error;
                }
                $stmt->close();
            } else {
                $error_message = "Current password is incorrect";
            }
        }
    }
}

// Available currencies
$currencies = [
    'USD' => 'US Dollar ($)',
    'EUR' => 'Euro (€)',
    'GBP' => 'British Pound (£)',
    'JPY' => 'Japanese Yen (¥)',
    'CAD' => 'Canadian Dollar (C$)',
    'AUD' => 'Australian Dollar (A$)',
    'NGN' => 'Nigerian Naira (₦)',
    'CHF' => 'Swiss Franc (CHF)',
    'CNY' => 'Chinese Yuan (¥)',
    'INR' => 'Indian Rupee (₹)'
];

// Include header
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/header.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/breadcumb.php';
?>

<div class="container-fluid px-4 py-4">
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $success_message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error_message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Left Sidebar - Navigation -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Account Settings</h5>
                    <div class="list-group list-group-flush" id="settings-nav">
                        <a href="#profile-settings" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                            <i class="bi bi-person me-2"></i> Profile Information
                        </a>
                        <a href="#photo-settings" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="bi bi-image me-2"></i> Profile Photo
                        </a>
                        <a href="#pin-settings" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="bi bi-shield-lock me-2"></i> Change PIN
                        </a>
                        <a href="#password-settings" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="bi bi-key me-2"></i> Change Password
                        </a>
                        <a href="/user/profile" class="list-group-item list-group-item-action">
                            <i class="bi bi-arrow-left me-2"></i> Back to Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Content - Forms -->
        <div class="col-lg-9">
            <div class="tab-content">
                <!-- Profile Information -->
                <div class="tab-pane fade show active" id="profile-settings">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Profile Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($user_data['first_name']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($user_data['last_name']) ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user_data['email']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user_data['phone']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="currency" class="form-label">Preferred Currency</label>
                                    <select class="form-select" id="currency" name="currency">
                                        <?php foreach ($currencies as $code => $name): ?>
                                            <option value="<?= $code ?>" <?= ($user_data['currency'] == $code) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Photo -->
                <div class="tab-pane fade" id="photo-settings">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Update Profile Photo</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <div class="avatar avatar-100 rounded-circle coverimg mx-auto" style="width: 150px; height: 150px;">
                                    <img src="<?= htmlspecialchars($profile_photo) ?>" alt="Profile Photo" 
                                         onerror="this.onerror=null; this.src='<?= $default_photo ?>';" 
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <p class="text-muted mt-2">Current Profile Photo</p>
                            </div>
                            
                            <form method="post" action="" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="profile_photo" class="form-label">Select New Photo</label>
                                    <input class="form-control" type="file" id="profile_photo" name="profile_photo" accept="image/jpeg, image/png, image/gif">
                                    <small class="text-muted">Accepted formats: JPG, PNG, GIF. Max size: 2MB.</small>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" name="update_photo" class="btn btn-primary">Upload New Photo</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- PIN Settings -->
                <div class="tab-pane fade" id="pin-settings">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Change 4-Digit PIN</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Your 4-digit PIN is used to authorize sensitive transactions on your account.
                            </div>
                            
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="current_pin" class="form-label">Current PIN</label>
                                    <input type="password" class="form-control" id="current_pin" name="current_pin" maxlength="4" inputmode="numeric" pattern="[0-9]*" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_pin" class="form-label">New PIN</label>
                                    <input type="password" class="form-control" id="new_pin" name="new_pin" maxlength="4" inputmode="numeric" pattern="[0-9]*" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_pin" class="form-label">Confirm New PIN</label>
                                    <input type="password" class="form-control" id="confirm_pin" name="confirm_pin" maxlength="4" inputmode="numeric" pattern="[0-9]*" required>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" name="update_pin" class="btn btn-primary">Update PIN</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Password Settings -->
                <div class="tab-pane fade" id="password-settings">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" minlength="8" required>
                                    <small class="text-muted">Password must be at least 8 characters long</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="8" required>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" name="update_password" class="btn btn-primary">Change Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Enable Bootstrap tabs functionality
document.addEventListener('DOMContentLoaded', function() {
    // Handle URL hash for tabs
    let hash = window.location.hash;
    if (hash) {
        const tab = document.querySelector(`#settings-nav a[href="${hash}"]`);
        if (tab) {
            const bsTab = new bootstrap.Tab(tab);
            bsTab.show();
        }
    }
    
    // Force numeric input for PIN fields
    const pinFields = document.querySelectorAll('input[pattern="[0-9]*"]');
    pinFields.forEach(field => {
        field.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 4) {
                this.value = this.value.slice(0, 4);
            }
        });
    });
});
</script>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/footer.php'; ?>
