<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Admin Panel for Investment Platform">
    <meta name="author" content="">
    
    <?php 
    // Make sure siteLink and site_name are defined
    if (!isset($siteLink) || empty($siteLink)) {
        // Attempt to get site URL from server variables
        $siteLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    }
    
    if (!isset($site_name) || empty($site_name)) {
        $site_name = "Investment Platform";
    }
    ?>
    
    <link rel="icon" href="<?=$siteLink?>/admin/images/favicon.ico">

    <title>Admin Dashboard - <?=$site_name?></title>

    <!-- Vendors Style-->
    <link rel="stylesheet" href="<?=$siteLink?>/admin/css/vendors_css.css">
    

    <!-- Style-->
    <link rel="stylesheet" href="<?=$siteLink?>/admin/css/style.css">
    <link rel="stylesheet" href="<?=$siteLink?>/admin/css/skin_color.css">
    <link rel="stylesheet" href="<?=$siteLink?>/admin/css/custom.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="dark-skin sidebar-mini theme-primary fixed">

<div class="wrapper">
    <div id="loader"></div>

    <header class="main-header">
        <div class="d-flex align-items-center logo-box justify-content-start">
            <!-- Logo -->
            <a href="<?=$siteLink?>/admin" class="logo">
                <!-- logo-->
                <div class="logo-mini w-30">
                    <span class="light-logo"><img src="<?=$siteLink?>/admin/images/logo-letter.png" alt="logo"></span>
                    <span class="dark-logo"><img src="<?=$siteLink?>/admin/images/logo-letter.png" alt="logo"></span>
                </div>
                <div class="logo-lg">
                    <span class="light-logo"><img src="<?=$siteLink?>/admin/images/logo-dark-text.png" alt="logo"></span>
                    <span class="dark-logo"><img src="<?=$siteLink?>/admin/images/logo-light-text.png" alt="logo"></span>
                </div>
            </a>
        </div>
        <!-- Header Navbar -->
        <nav class="navbar navbar-static-top">
            <!-- Sidebar toggle button-->
            <div class="app-menu">
                <ul class="header-megamenu nav">
                    <li class="btn-group nav-item">
                        <a href="#" class="waves-effect waves-light nav-link push-btn btn-primary-light" data-toggle="push-menu" role="button">
                            <i data-feather="align-left"></i>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="navbar-custom-menu r-side">
                <ul class="nav navbar-nav">
                    <li class="btn-group nav-item d-lg-inline-flex d-none">
                        <a href="#" data-provide="fullscreen" class="waves-effect waves-light nav-link full-screen btn-primary-light" title="Full Screen">
                            <i data-feather="maximize"></i>
                        </a>
                    </li>
                    <!-- Notifications -->
                    <li class="dropdown notifications-menu">
                        <a href="#" class="waves-effect waves-light dropdown-toggle btn-primary-light" data-bs-toggle="dropdown" title="Notifications">
                            <i data-feather="bell"></i>
                        </a>
                        <ul class="dropdown-menu animated bounceIn">
                            <li class="header">
                                <div class="p-20">
                                    <div class="flexbox">
                                        <div>
                                            <h4 class="mb-0 mt-0">Notifications</h4>
                                        </div>
                                        <div>
                                            <a href="#" class="text-danger">Clear All</a>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <ul class="menu sm-scrol">
                                    <li><a href="#"><i class="fa fa-users text-info"></i> New user registered</a></li>
                                    <li><a href="#"><i class="fa fa-warning text-warning"></i> New withdrawal request</a></li>
                                    <li><a href="#"><i class="fa fa-shopping-cart text-success"></i> New deposit completed</a></li>
                                </ul>
                            </li>
                            <li class="footer"><a href="#">View all</a></li>
                        </ul>
                    </li>

                    <!-- User Account-->
                    <li class="dropdown user user-menu">
                        <a href="#" class="waves-effect waves-light dropdown-toggle btn-primary-light" data-bs-toggle="dropdown" title="User">
                            <i data-feather="user"></i>
                        </a>
                        <ul class="dropdown-menu animated flipInX">
                            <li class="user-body">
                                <a class="dropdown-item" href="<?=$siteLink?>/admin/profile.php"><i class="ti-user text-muted me-2"></i> Profile</a>
                                <a class="dropdown-item" href="<?=$siteLink?>/admin/settings.php"><i class="ti-settings text-muted me-2"></i> Settings</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="<?=$siteLink?>/admin/logout.php"><i class="ti-lock text-muted me-2"></i> Logout</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <aside class="main-sidebar">
        <!-- sidebar-->
        <section class="sidebar position-relative">
            <div class="multinav">
                <div class="multinav-scroll" style="height: 100%;">
                    <!-- sidebar menu-->
                    <ul class="sidebar-menu" data-widget="tree">
                        <li class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                            <a href="<?=$siteLink?>/admin/index.php">
                                <i data-feather="monitor"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="<?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
                            <a href="<?=$siteLink?>/admin/users.php">
                                <i data-feather="users"></i>
                                <span>Users</span>
                            </a>
                        </li>
                        <li class="<?php echo $current_page == 'deposits.php' ? 'active' : ''; ?>">
                            <a href="<?=$siteLink?>/admin/deposits.php">
                                <i data-feather="credit-card"></i>
                                <span>Deposits</span>
                            </a>
                        </li>
                        <li class="<?php echo $current_page == 'withdrawals.php' ? 'active' : ''; ?>">
                            <a href="<?=$siteLink?>/admin/withdrawals.php">
                                <i data-feather="dollar-sign"></i>
                                <span>Withdrawals</span>
                            </a>
                        </li>
                        <li class="<?php echo $current_page == 'investments.php' ? 'active' : ''; ?>">
                            <a href="<?=$siteLink?>/admin/investments.php">
                                <i data-feather="trending-up"></i>
                                <span>Investments</span>
                            </a>
                        </li>
                        <li class="<?php echo $current_page == 'investment_plans.php' ? 'active' : ''; ?>">
                            <a href="<?=$siteLink?>/admin/investment_plans.php">
                                <i data-feather="list"></i>
                                <span>Investment Plans</span>
                            </a>
                        </li>
                        <li class="<?php echo $current_page == 'transactions.php' ? 'active' : ''; ?>">
                            <a href="<?=$siteLink?>/admin/transactions.php">
                                <i data-feather="refresh-cw"></i>
                                <span>Transactions</span>
                            </a>
                        </li>
                        <li class="<?php echo $current_page == 'referrals.php' ? 'active' : ''; ?>">
                            <a href="<?=$siteLink?>/admin/referrals.php">
                                <i data-feather="share-2"></i>
                                <span>Referrals</span>
                            </a>
                        </li>
                        <li class="<?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
                            <a href="<?=$siteLink?>/admin/settings.php">
                                <i data-feather="settings"></i>
                                <span>Settings</span>
                            </a>
                        </li>
                        <li class="header">ADMINISTRATOR</li>
                        <?php if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] == 'super_admin'): ?>
                        <li class="<?php echo $current_page == 'admins.php' ? 'active' : ''; ?>">
                            <a href="<?=$siteLink?>/admin/admins.php">
                                <i data-feather="shield"></i>
                                <span>Admin Users</span>
                            </a>
                        </li>
                        <li class="<?php echo $current_page == 'logs.php' ? 'active' : ''; ?>">
                            <a href="<?=$siteLink?>/admin/logs.php">
                                <i data-feather="file-text"></i>
                                <span>Activity Logs</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </section>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Main Content -->