<?php
session_start();
$page_name = 'Register';
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/auth/header.php';

// Initialize variables
$errors = [];
$success = '';
$formData = [
    'fname' => '',
    'lname' => '',
    'email' => '',
    'phone_code' => '',
    'phone' => ''
];

// Get referral code from URL if present
$referral_code = '';
if (isset($_GET['ref']) && !empty($_GET['ref'])) {
    $referral_code = trim($_GET['ref']);
}

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /user/dashboard');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Collect and sanitize data
        $formData = [
            'fname' => trim($_POST['fname'] ?? ''),
            'lname' => trim($_POST['lname'] ?? ''),
            'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
            'phone_code' => $_POST['phone_code'] ?? '',
            'phone' => trim($_POST['phone'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'referral_code' => trim($_POST['referral_code'] ?? '')
        ];

        // Validate inputs
        if (empty($formData['fname'])) {
            $errors[] = 'First name is required';
        }

        if (empty($formData['lname'])) {
            $errors[] = 'Last name is required';
        }

        if (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address';
        }

        if (empty($formData['phone'])) {
            $errors[] = 'Phone number is required';
        }

        if (strlen($formData['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        } elseif (!preg_match('/[A-Z]/', $formData['password'])) {
            $errors[] = 'Password must contain at least one uppercase letter';
        } elseif (!preg_match('/[0-9]/', $formData['password'])) {
            $errors[] = 'Password must contain at least one number';
        }

        if ($formData['password'] !== $formData['confirm_password']) {
            $errors[] = 'Passwords do not match';
        }

        // Check if email exists
        $stmt = $conn_back->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $formData['email']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Email already registered';
        }

        // Validate referral code if provided
        $referrer_id = null;
        if (!empty($formData['referral_code'])) {
            $stmt = $conn_back->prepare("SELECT id FROM users WHERE referral_code = ?");
            $stmt->bind_param("s", $formData['referral_code']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $referrer = $result->fetch_assoc();
                $referrer_id = $referrer['id'];
            } else {
                $errors[] = 'Invalid referral code';
            }
        }

        // If no errors, create user
        if (empty($errors)) {
            // Hash password
            $passwordHash = password_hash($formData['password'], PASSWORD_DEFAULT);

            // Create full phone number
            $fullPhone = $formData['phone_code'] . $formData['phone'];
            
            // Generate a unique referral code for new user
            $new_referral_code = strtoupper(substr(md5(uniqid($formData['email'] . time(), true)), 0, 8));
            
            // Begin transaction
            $conn_back->begin_transaction();
            
            try {
                // Insert user with referral information
                $stmt = $conn_back->prepare("INSERT INTO users 
                    (first_name, last_name, email, phone, password, referral_code, referred_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssi",
                    $formData['fname'],
                    $formData['lname'],
                    $formData['email'],
                    $fullPhone,
                    $passwordHash,
                    $new_referral_code,
                    $referrer_id
                );
                
                if ($stmt->execute()) {
                    // Commit transaction
                    $conn_back->commit();
                    
                    $success = 'Registration successful! Redirecting to login...';
                    if ($referrer_id) {
                        $success .= ' You were referred by a friend!';
                    }
                    header("Refresh: 3; url=/login");
                } else {
                    throw new Exception('Registration failed. Please try again.');
                }
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn_back->rollback();
                throw $e;
            }
        }

    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}
?>

    <div class="h-100 py-3 px-3">
        <div class="row h-100 align-items-center justify-content-center">
            <div class="col-11 col-sm-8 col-md-11 col-xl-11 col-xxl-10 login-box">
                <div class="text-center mb-4">
                    <h1 class="mb-3">Let's get started&#128077;</h1>
                    <p class="text-secondary">Provide your few details</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <div><?= htmlspecialchars($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form action="/register" method="post">
                    <div class="row">
                        <div class="col">
                            <div class="form-floating mb-3">
                                <input class="form-control" id="namef" name="fname"
                                       placeholder="Enter first name" required
                                       value="<?= htmlspecialchars($formData['fname']) ?>">
                                <label for="namef">First Name</label>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-floating mb-3">
                                <input class="form-control" id="namel" name="lname"
                                       placeholder="Enter last name" required
                                       value="<?= htmlspecialchars($formData['lname']) ?>">
                                <label for="namel">Last Name</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="emailadd" name="email"
                               placeholder="Enter email address" required
                               value="<?= htmlspecialchars($formData['email']) ?>">
                        <label for="emailadd">Email Address</label>
                    </div>

                    <div class="input-group mb-3">
                        <div class="form-floating maxwidth-100">
                            <select class="form-select" id="code" name="phone_code" aria-label="Country code" required>
                                <option value="+1" <?= $formData['phone_code'] === '+1' ? 'selected' : '' ?>>+1</option>
                                <option value="+44" <?= $formData['phone_code'] === '+44' ? 'selected' : 'selected' ?>>
                                    +44
                                </option>
                                <option value="+66" <?= $formData['phone_code'] === '+66' ? 'selected' : '' ?>>+66
                                </option>
                                <option value="+91" <?= $formData['phone_code'] === '+91' ? 'selected' : '' ?>>+91
                                </option>
                            </select>
                            <label for="code">Code</label>
                        </div>
                        <div class="form-floating">
                            <input class="form-control" id="phonen" name="phone"
                                   placeholder="Enter your phone number" required
                                   value="<?= htmlspecialchars($formData['phone']) ?>">
                            <label for="phonen">Phone Number</label>
                        </div>
                    </div>

                    <div class="position-relative">
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="checkstrength"
                                   name="password" placeholder="Enter your password" required>
                            <label for="checkstrength">Password</label>
                        </div>
                        <button type="button"
                                class="btn btn-square btn-link text-theme-1 position-absolute end-0 top-0 mt-2 me-2">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>

                    <div class="position-relative">
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="passwd"
                                   name="confirm_password" placeholder="Confirm your password" required>
                            <label for="passwd">Confirm Password</label>
                        </div>
                        <button type="button"
                                class="btn btn-square btn-link text-theme-1 position-absolute end-0 top-0 mt-2 me-2">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    
                    <!-- Referral Code Field -->
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="referral_code" name="referral_code"
                               placeholder="Referral code (if any)" value="<?= htmlspecialchars($referral_code) ?>">
                        <label for="referral_code">Referral Code (Optional)</label>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button class="btn btn-lg btn-theme-1" type="submit">Register</button>
                    </div>

                    <div class="text-center">
                        <p class="small text-secondary">By clicking Register, you agree to our
                            <a href="#">Terms</a> and <a href="#">Privacy Policy</a>
                        </p>
                        <p class="mt-2">
                            Already have an account? <a href="/login">Login</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButtons = document.querySelectorAll('.btn-square.btn-link.text-theme-1');
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const input = this.previousElementSibling.querySelector('input');
                    const icon = this.querySelector('i');
                    
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.replace('bi-eye', 'bi-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.replace('bi-eye-slash', 'bi-eye');
                    }
                });
            });
        });
    </script>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/auth/footer.php'; ?>