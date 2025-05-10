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
<!--    <link rel="stylesheet" href="--><?php //=$siteLink?><!--/admin/css/custom.css">-->
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
                                <span class="pull-right-container">
                      <i class="fa fa-angle-right pull-right"></i>
                    </span>
                            </a>
                            <ul class="treeview-menu">
                                <li class="treeview">
                                    <a href="#">
                                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Options 1 to 5
                                        <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                                    </a>
                                    <ul class="treeview-menu">
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 1</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 2</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 3</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 4</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 5</a></li>
                                    </ul>
                                </li>
                                <li class="treeview">
                                    <a href="#">
                                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Options 6 to 10
                                        <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                                    </a>
                                    <ul class="treeview-menu">
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 6</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 7</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 8</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 9</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 10</a></li>
                                    </ul>
                                </li>
                                <li class="treeview">
                                    <a href="#">
                                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Options 11 to 15
                                        <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                                    </a>
                                    <ul class="treeview-menu">
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 11</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 12</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 13</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 14</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 15</a></li>
                                    </ul>
                                </li>
                                <li class="treeview">
                                    <a href="#">
                                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Options 16 to 20
                                        <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                                    </a>
                                    <ul class="treeview-menu">
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 16</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 17</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 18</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 19</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 20</a></li>
                                    </ul>
                                </li>
                                <li class="treeview">
                                    <a href="#">
                                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Options 21 to 25
                                        <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                                    </a>
                                    <ul class="treeview-menu">
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 21</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 22</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 23</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 24</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 25</a></li>
                                    </ul>
                                </li>
                                <li class="treeview">
                                    <a href="#">
                                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Options 26 to 30
                                        <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                                    </a>
                                    <ul class="treeview-menu">
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 26</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 27</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 28</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 29</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 30</a></li>
                                    </ul>
                                </li>
                                <li class="treeview">
                                    <a href="#">
                                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Options 31 to 35
                                        <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                                    </a>
                                    <ul class="treeview-menu">
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 31</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 32</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 33</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 34</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 35</a></li>
                                    </ul>
                                </li>
                                <li class="treeview">
                                    <a href="#">
                                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Options 36 to 40 <span class="badge text-bg-danger w-auto rounded-2 l-h-14 m-0 p-1 fs-10 top-0 ms-2 pb-0">New</span>
                                        <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                                    </a>
                                    <ul class="treeview-menu">
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 36 <span class="badge text-bg-danger w-auto rounded-2 l-h-14 m-0 p-1 fs-10 top-0 ms-2 pb-0">New</span></a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dash 37 <span class="badge text-bg-danger w-auto rounded-2 l-h-14 m-0 p-1 fs-10 top-0 ms-2 pb-0">New</span></a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i data-feather="bar-chart-2"></i>
                                <span>Reports</span>
                                <span class="pull-right-container">
                      <i class="fa fa-angle-right pull-right"></i>
                    </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Transactions</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Top Gainers/Losers</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Market Capitalizations</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Crypto Stats</a></li>
                            </ul>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i data-feather="pie-chart"></i>
                                <span>Initial Coin Offering</span>
                                <span class="pull-right-container">
                      <i class="fa fa-angle-right pull-right"></i>
                    </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Countdown</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Roadmap/Timeline</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Progress Bar</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Details</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>ICO Listing</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>ICO Listing - Filters</a></li>
                            </ul>
                        </li>
                        <li>
                            <a href="#">
                                <i data-feather="refresh-ccw"></i>
                                <span>Currency Exchange</span>
                            </a>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i data-feather="users"></i>
                                <span>Members</span>
                                <span class="pull-right-container">
                      <i class="fa fa-angle-right pull-right"></i>
                    </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Members Grid</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Members List</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Member Profile</a></li>
                            </ul>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i data-feather="sliders"></i>
                                <span>Tickers</span>
                                <span class="pull-right-container">
                      <i class="fa fa-angle-right pull-right"></i>
                    </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Ticker</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Live Crypto Prices</a></li>
                            </ul>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i data-feather="dollar-sign"></i>
                                <span>Transactions</span>
                                <span class="pull-right-container">
                      <i class="fa fa-angle-right pull-right"></i>
                    </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Transactions Tables</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Transactions Search</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Single Transaction</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Transactions Counter</a></li>
                            </ul>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i data-feather="pie-chart"></i>
                                <span>Charts</span>
                                <span class="pull-right-container">
                      <i class="fa fa-angle-right pull-right"></i>
                    </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>ChartJS</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Flot</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Inline charts</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Morris</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Peity</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Chartist</a></li>

                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Rickshaw Charts</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>NVD3 Charts</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>eChart</a></li>

                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>amCharts Charts</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>amCharts Stock Charts</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>amCharts Maps</a></li>
                            </ul>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i data-feather="grid"></i>
                                <span>Apps</span>
                                <span class="pull-right-container">
                      <i class="fa fa-angle-right pull-right"></i>
                    </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Calendar</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Contact List</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Chat</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Todo</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Mailbox</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Project</a></li>
                            </ul>
                        </li>

                        <li class="treeview">
                            <a href="#">
                                <i data-feather="package"></i>
                                <span>Features</span>
                                <span class="pull-right-container">
                      <i class="fa fa-angle-right pull-right"></i>
                    </span>
                            </a>
                            <ul class="treeview-menu">
                                <li class="treeview">
                                    <a href="#">
                                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Components
                                        <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                                    </a>
                                    <ul class="treeview-menu">
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Bootstrap Switch</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Date Paginator</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Advanced Medias</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Range Slider</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Ratings</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Animations</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Fullscreen</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Pace</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Nestable</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Draggable Portlets</a></li>
                                    </ul>
                                </li>
                                <li class="treeview">
                                    <a href="#">
                                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Card
                                        <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                                    </a>
                                    <ul class="treeview-menu">
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>User Card</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Advanced Card</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Basic Card</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Card Color</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Card Group</a></li>
                                    </ul>
                                </li>
                                <li class="treeview">
                                    <a href="#">
                                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Utility Elements
                                        <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                                    </a>
                                    <ul class="treeview-menu">
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Badges</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Border</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Buttons</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Color</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dropdown</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dropdown Grid</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Progress Bars</a></li>
                                    </ul>
                                </li>
                                <li class="treeview">
                                    <a href="#">
                                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Icons
                                        <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                                    </a>
                                    <ul class="treeview-menu">
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Font Awesome</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Glyphicons</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Material Icons</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Themify Icons</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Simple Line Icons</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Cryptocoins Icons</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Flag Icons</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Weather Icons</a></li>
                                    </ul>
                                </li>
                                <li class="treeview">
                                    <a href="#">
                                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Extra Elements
                                        <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                                    </a>
                                    <ul class="treeview-menu">
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Ribbons</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Sliders</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Typography</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Tabs</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Timeline</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Horizontal Timeline</a></li>
                                    </ul>
                                </li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Grid System</a></li>
                            </ul>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i data-feather="inbox"></i>
                                <span>Forms & Tables</span>
                                <span class="pull-right-container">
                      <i class="fa fa-angle-right pull-right"></i>
                    </span>
                            </a>
                            <ul class="treeview-menu">
                                <li class="treeview">
                                    <a href="#">
                                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Forms
                                        <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                                    </a>
                                    <ul class="treeview-menu">
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Form Elements</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Form Layout</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Form Wizard</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Form Validation</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Formatter</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Xeditable Editor</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Dropzone</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Code Editor</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Editor</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Markdown</a></li>
                                    </ul>
                                </li>
                                <li class="treeview">
                                    <a href="#">
                                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Tables
                                        <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                                    </a>
                                    <ul class="treeview-menu">
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Simple tables</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Data tables</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Editable Tables</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Table Color</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i data-feather="edit"></i>
                                <span>Widgets</span>
                                <span class="pull-right-container">
                      <i class="fa fa-angle-right pull-right"></i>
                    </span>
                            </a>
                            <ul class="treeview-menu">
                                <li class="treeview">
                                    <a href="#">
                                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Core Widgets
                                        <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                                    </a>
                                    <ul class="treeview-menu">
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Blog</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Chart</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>List</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Social</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Statistic</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Weather</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Widgets</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Emails</a></li>
                                    </ul>
                                </li>
                                <li class="treeview">
                                    <a href="#">
                                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Maps
                                        <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                                    </a>
                                    <ul class="treeview-menu">
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Google Map</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Vector Map</a></li>
                                    </ul>
                                </li>
                                <li class="treeview">
                                    <a href="#">
                                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Modals
                                        <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                                    </a>
                                    <ul class="treeview-menu">
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Modals</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Sweet Alert</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Toastr</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i data-feather="cast"></i>
                                <span>Pages</span>
                                <span class="pull-right-container">
                      <i class="fa fa-angle-right pull-right"></i>
                    </span>
                            </a>
                            <ul class="treeview-menu">
                                <li class="treeview">
                                    <a href="#">
                                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Elements Pages
                                        <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                                    </a>
                                    <ul class="treeview-menu">
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>FAQs</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Blank</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Coming Soon</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Custom Scrolls</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Gallery</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Lightbox Popup</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Pricing</a></li>
                                    </ul>
                                </li>
                                <li class="treeview">
                                    <a href="#">
                                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Invoice
                                        <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                                    </a>
                                    <ul class="treeview-menu">
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Invoice</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Invoice List</a></li>
                                    </ul>
                                </li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Support Ticket</a></li>
                                <li class="treeview">
                                    <a href="#">
                                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>User Pages
                                        <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                                    </a>
                                    <ul class="treeview-menu">
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>User Profile</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Userlist Grid</a></li>
                                        <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Userlist</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i data-feather="lock"></i>
                                <span>Authentication</span>
                                <span class="pull-right-container">
                      <i class="fa fa-angle-right pull-right"></i>
                    </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Login</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Register</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Lockscreen</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Recover password</a></li>
                            </ul>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i data-feather="alert-triangle"></i>
                                <span>Miscellaneous</span>
                                <span class="pull-right-container">
                      <i class="fa fa-angle-right pull-right"></i>
                    </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Error 404</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Error 500</a></li>
                                <li><a href="#"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Maintenance</a></li>
                            </ul>
                        </li>
                    </ul>

                    <div class="sidebar-widgets">
                        <div class="mx-25 mb-30 p-30 text-center bg-primary-light rounded5">
                            <img src="../images/trophy.png" alt="">
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