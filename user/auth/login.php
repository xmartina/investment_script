<?php
$page_name = 'Login';
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/auth/header.php';
?>
    <div class="h-100 py-4 px-3">
        <div class="row h-100 align-items-center justify-content-center mt-md-4">
            <div class="col-11 col-sm-8 col-md-11 col-xl-11 col-xxl-10 login-box">
                <div class="text-center mb-4">
                    <h1 class="mb-2">Welcome&#9996;</h1>
                    <p class="text-secondary">Enter your credential to login</p>
                </div>

                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="emailadd" placeholder="Enter email address">
                    <label for="emailadd">Email Address</label>
                </div>

                <div class="position-relative">
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="passwd" placeholder="Enter your password">
                        <label for="passwd">Password</label>
                    </div>
                    <button class="btn btn-square btn-link text-theme-1 position-absolute end-0 top-0 mt-2 me-2 ">
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
                        <a href="<?=$forgot_password['link']?>" class=" ">Forget Password?</a>
                    </div>
                </div>
                <a href="<?=$dashboard['link']?>" class="btn btn-lg btn-theme w-100 mb-4">Login</a>
                <!-- <button class="btn btn-lg btn-theme w-100 mb-4">Login</button> -->
                <div class="text-center mb-3">
                    Don't have account? <a href="<?=$register['link']?>" class=" ">Create Account</a>
                </div>

            </div>
        </div>
    </div>
<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/auth/footer.php';
?>