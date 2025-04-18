<?php
session_start();
$page_name = 'Login';
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/auth/header.php';

$error = '';
if (isset($_SESSION['user_id'])) {
    header('Location: /user/dashboard');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate email
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address format');
        }

        // Validate password
        $password = $_POST['password'] ?? '';
        if (empty($password)) {
            throw new Exception('Password is required');
        }

        // Get user from database without prepared statement
        $escaped_email = mysqli_real_escape_string($conn_back, $email);
        $query = "SELECT id, password FROM users WHERE email = '$escaped_email'";
        $result = mysqli_query($conn_back, $query);
        $user = mysqli_fetch_assoc($result);

        // Verify user exists and password matches
        if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception('Invalid email or password');
        }

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $email;

        // Handle "Remember me" functionality
        if (isset($_POST['rememberme'])) {
            $token = bin2hex(random_bytes(32));
            $expire = time() + 60 * 60 * 24 * 30; // 30 days

            // Store token in database without prepared statement
            $update_query = "UPDATE users SET remember_token = '" . addslashes($token) . "' WHERE id = " . intval($user['id']);
            $pdo->exec($update_query);

            setcookie('remember', $token, $expire, '/', '', true, true);
        }

        // Redirect to secure area
        header('Location: /user/dashboard');
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>


?>
    <div class="h-100 py-4 px-3">
        <div class="row h-100 align-items-center justify-content-center mt-md-4">
            <div class="col-11 col-sm-8 col-md-11 col-xl-11 col-xxl-10 login-box">
                <div class="text-center mb-4">
                    <h1 class="mb-2">Welcome&#9996;</h1>
                    <p class="text-secondary">Enter your credential to login</p>
                </div>

                <form action="" method="post">
                    <?php if (!empty($error)) : ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <div class="form-floating mb-3">
                        <input type="email" name="email" class="form-control" id="emailadd"
                               placeholder="Enter email address" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        <label for="emailadd">Email Address</label>
                    </div>

                    <div class="position-relative">
                        <div class="form-floating mb-3">
                            <input type="password" name="password" class="form-control" id="passwd"
                                   placeholder="Enter your password" required>
                            <label for="passwd">Password</label>
                        </div>
                        <button type="button" class="btn btn-square btn-link text-theme-1 position-absolute end-0 top-0 mt-2 me-2">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="rememberme" id="rememberme">
                                <label class="form-check-label" for="rememberme">Remember me</label>
                            </div>
                        </div>
                        <div class="col-auto">
                            <a href="<?= htmlspecialchars($forgot_password['link']) ?>" class="">Forget Password?</a>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-lg btn-theme w-100 mb-4">Login</button>
                </form>
                <div class="text-center mb-3">
                    Don't have account? <a href="<?= $register['link'] ?>" class=" ">Create Account</a>
                </div>

            </div>
        </div>
    </div>
    <script>
        // Password visibility toggle
        document.querySelector('.bi-eye').parentElement.addEventListener('click', function(e) {
            const passwordField = document.querySelector('#passwd');
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            e.currentTarget.querySelector('i').classList.toggle('bi-eye-slash');
        });
    </script>
<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/auth/footer.php';
?>