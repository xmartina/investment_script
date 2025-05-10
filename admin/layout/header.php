<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="https://crypto-admin-templates.multipurposethemes.com/bs5/images/favicon.ico">

    <title>Crypto Admin - Responsive Bootstrap Admin HTML Templates + Bitcoin Dashboards + ICO </title>

    <!-- Vendors Style-->
    <link rel="stylesheet" href="<?=$siteLink?>/admin/css/vendors_css.css">
    <!--amcharts -->
    <link href="https://www.amcharts.com/lib/3/plugins/export/export.css" rel="stylesheet" type="text/css" />

    <!-- Style-->
    <link rel="stylesheet" href="<?=$siteLink?>/admin/css/style.css">
    <link rel="stylesheet" href="<?=$siteLink?>/admin/css/skin_color.css">
    <link rel="stylesheet" href="<?=$siteLink?>/admin/css/custom.css">
    <link rel="stylesheet" href="<?=$siteLink?>/admin/css/custom.css">
</head>

<body class="dark-skin sidebar-mini theme-primary  fixed sidebar-collapse">

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
                    <li class="btn-group nav-item d-none d-xl-inline-block">
                        <a href="<?=$siteLink?>/admin/contact_app_chat.html" class="waves-effect waves-light nav-link svg-bt-icon btn-primary-light" title="Chat">
                            <i data-feather="message-circle"></i>
                        </a>
                    </li>
                    <li class="btn-group nav-item d-none d-xl-inline-block">
                        <a href="<?=$siteLink?>/admin/mailbox.html" class="waves-effect waves-light nav-link svg-bt-icon btn-primary-light" title="Mailbox">
                            <i data-feather="at-sign"></i>
                        </a>
                    </li>
                    <li class="btn-group nav-item d-none d-xl-inline-block">
                        <a href="<?=$siteLink?>/admin/extra_taskboard.html" class="waves-effect waves-light nav-link svg-bt-icon btn-primary-light" title="Taskboard">
                            <i data-feather="clipboard"></i>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="navbar-custom-menu r-side">
                <ul class="nav navbar-nav">
                    <li class="btn-group d-lg-inline-flex d-none">
                        <div class="app-menu">
                            <div class="search-bx mx-5">
                                <form>
                                    <div class="input-group">
                                        <input type="search" class="form-control" placeholder="Search" aria-label="Search" aria-describedby="button-addon2">
                                        <div class="input-group-append">
                                            <button class="btn" type="submit" id="button-addon3"><i data-feather="search"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </li>
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
                                    <li><a href="#"><i class="fa fa-users text-info"></i> Curabitur id eros quis nunc suscipit blandit.</a></li>
                                    <li><a href="#"><i class="fa fa-warning text-warning"></i> Duis malesuada justo eu sapien elementum, in semper diam posuere.</a></li>
                                    <li><a href="#"><i class="fa fa-users text-danger"></i> Donec at nisi sit amet tortor commodo porttitor pretium a erat.</a></li>
                                    <li><a href="#"><i class="fa fa-shopping-cart text-success"></i> In gravida mauris et nisi</a></li>
                                    <li><a href="#"><i class="fa fa-user text-danger"></i> Praesent eu lacus in libero dictum fermentum.</a></li>
                                    <li><a href="#"><i class="fa fa-user text-primary"></i> Nunc fringilla lorem</a></li>
                                    <li><a href="#"><i class="fa fa-user text-success"></i> Nullam euismod dolor ut quam interdum, at scelerisque ipsum imperdiet.</a></li>
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
                                <a class="dropdown-item" href="#"><i class="ti-user text-muted me-2"></i> Profile</a>
                                <a class="dropdown-item" href="#"><i class="ti-wallet text-muted me-2"></i> My Wallet</a>
                                <a class="dropdown-item" href="#"><i class="ti-settings text-muted me-2"></i> Settings</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#"><i class="ti-lock text-muted me-2"></i> Logout</a>
                            </li>
                        </ul>
                    </li>

                    <!-- Control Sidebar Toggle Button -->
                    <li>
                        <a href="#" data-toggle="control-sidebar" title="Setting" class="waves-effect waves-light btn-primary-light">
                            <i data-feather="settings"></i>
                        </a>
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
                        <li class="treeview">
                            <a href="#">
                                <i data-feather="monitor"></i>
                                <span>Dashboard <span class="badge badge-sm badge-dot badge-danger" style="width: 5px; height: 5px; margin: 0px 0px 10px 2px;"></span></span>
                                <span class="pull-right-container"><i class="fa fa-angle-right pull-right"></i></span>
                            </a>
                            <ul class="treeview-menu">
                                <!-- Dashboard menu items updated similarly -->
                                <li class="treeview">
                                    <a href="#">
                                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Options 1 to 5
                                        <span class="pull-right-container"><i class="fa fa-angle-right pull-right"></i></span>
                                    </a>
                                    <ul class="treeview-menu">
                                        <li><a href="<?=$siteLink?>/admin/index.html"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 1</a></li>
                                        <!-- Repeat for other dashboard links -->
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <!-- Other menu items updated similarly -->
                    </ul>

                    <div class="sidebar-widgets">
                        <div class="mx-25 mb-30 p-30 text-center bg-primary-light rounded5">
                            <img src="<?=$siteLink?>/admin/images/trophy.png" alt="">
                            <h4 class="my-3 fw-500 text-uppercase text-primary">Start Trading</h4>
                            <span class="fs-12 d-block mb-3 text-black-50">Offering discounts for better online a store can loyalty weapon into driving</span>
                            <button type="button" class="waves-effect waves-light btn btn-sm btn-primary mb-5">Invest Now</button>
                        </div>
                        <div class="copyright text-center m-25">
                            <p><strong class="d-block">Crypto Admin Dashboard</strong> © 2024 All Rights Reserved</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </aside>