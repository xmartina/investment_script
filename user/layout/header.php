<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?= $page_name ?> | <?= $site_name ?></title>
    <link rel="icon" type="image/png" href="<?= $site_favicon ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="../../css2?family=Lexend:wght@100..900&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap"
          rel="stylesheet">
    <style>:root {
            --adminuiux-content-font: "Open Sans", sans-serif;
            --adminuiux-content-font-weight: 400;
            --adminuiux-title-font: "Lexend", sans-serif;
            --adminuiux-title-font-weight: 600
        }</style>
    <script defer="defer" src="<?= $site_link ?>/back_assets/js/app.js?ff1e8ee7ca91d18f44ea"></script>
    <link href="<?= $site_link ?>/back_assets/css/app.css?ff1e8ee7ca91d18f44ea" rel="stylesheet">
</head>
<body
    class="main-bg main-bg-opac main-bg-blur adminuiux-sidebar-fill-white adminuiux-sidebar-boxed theme-blue roundedui"
    data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-bs-spy="scroll"
    data-bs-target="#list-example" data-bs-smooth-scroll="true" tabindex="0">
</body>
</html>
<div class="pageloader">
    <div class="container h-100">
        <div class="row justify-content-center align-items-center text-center h-100">
            <div class="col-12 mb-auto pt-4"></div>
            <div class="col-auto"><img src="<?= $site_logo ?>" alt="" class="height-60 mb-3">
            </div>
            <div class="col-12 mt-auto pb-4"><p class="text-secondary">Please wait we are preparing awesome things to
                    preview...</p></div>
        </div>
    </div>
</div>
<header class="adminuiux-header">
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid">
            <button class="btn btn-link btn-square sidebar-toggler" type="button" onclick="initSidebar()"><i
                    class="sidebar-svg" data-feather="menu"></i></button>
            <a class="navbar-brand" href="<?= $site_link ?>"><img data-bs-img="light" src="<?= $site_logo ?>"
                                                                          alt="">
                <img data-bs-img="dark" src="<?= $site_logo ?>" alt="">
            </a>
            <div class="collapse navbar-collapse right-in-device justify-content-center" id="header-navbar">
                <ul class="navbar-nav mx-lg-3 mb-2 mb-md-0">
                    <li class="nav-item"><a class="nav-link" href="<?= $dashboard['link'] ?>">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $profile['link'] ?>">Portfolio</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $transactions['link'] ?>">Transaction</a></li>
                </ul>
            </div>
            <div class="ms-auto">
<!--                <button class="btn btn-link btn-square btn-icon btn-link-header" type="button" onclick="openSearch()"><i-->
<!--                        data-feather="search"></i></button>-->
                <button class="btn btn-link btn-square btnsunmoon btn-link-header" id="btn-layout-modes-dark-page"><i
                        class="sun mx-auto" data-feather="sun"></i> <i class="moon mx-auto" data-feather="moon"></i>
                </button>
                <div class="dropdown d-none d-sm-inline-block">
                    <button class="btn btn-link btn-square btn-icon btn-link-header dropdown-toggle no-caret"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="grid"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end width-300 pt-0 px-0">
                        <div class="bg-theme-1-space rounded py-3 mb-2 dropdown-dontclose text-center"><p class="mb-0">
                                Quick Links</p>
                            <p class="opacity-50 small">Fast Navigation</p></div>
                        <div class="px-2">
                            <?php
                            $apps = [
                                [
                                    'href' => '/user/investment',
                                    'icon' => 'bi-bank',
                                    'title' => 'Investment',
                                    'subtitle' => 'invest'
                                ],
                                [
                                    'href' => '/user/transactions',
                                    'icon' => 'bi-globe',
                                    'title' => 'Transactions',
                                    'subtitle' => 'Inflow & Outflow'
                                ],
                                [
                                    'href' => '/user/staking',
                                    'icon' => 'bi-box',
                                    'title' => 'Stacking',
                                    'subtitle' => 'Invest'
                                ],
//                                [
//                                    'href' => 'app-project.html',
//                                    'icon' => 'bi-folder',
//                                    'title' => 'Project',
//                                    'subtitle' => 'Management'
//                                ],
//                                [
//                                    'href' => 'app-social.html',
//                                    'icon' => 'bi-people',
//                                    'title' => 'Social',
//                                    'subtitle' => 'Tracking'
//                                ],
//                                [
//                                    'href' => 'app-learning.html',
//                                    'icon' => 'bi-journal-bookmark',
//                                    'title' => 'Learning',
//                                    'subtitle' => 'Make-easy'
//                                ]
                            ];
                            ?>
                            <div class="row g-0 text-center mb-2">
                                <?php foreach ($apps as $app): ?>
                                    <div class="col-4">
                                        <a class="dropdown-item square-item" href="<?= $app['href']; ?>">
                                            <div class="avatar avatar-40 rounded mb-2">
                                                <i class="bi <?= $app['icon']; ?> fs-4 mx-0"></i>
                                            </div>
                                            <p class="mb-0"><?= $app['title']; ?></p>
                                            <p class="fs-12 opacity-50 mb-2"><?= $app['subtitle']; ?></p>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="dropdown d-none d-sm-inline-block">
                    <button class="btn btn-link btn-square btn-icon btn-link-header dropdown-toggle no-caret"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false"><i
                            class="bi bi-translate"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item active" data-value="EN">EN - English</a></li>
                        <li><a class="dropdown-item" data-value="FR">FR - French</a></li>
                        <li><a class="dropdown-item" data-value="CH">CH - Chinese</a></li>
                        <li><a class="dropdown-item" data-value="HI">HI - Hindi</a></li>
                    </ul>
                </div>
                <?php
                $notifications = [
//                    [
//                        'icon' => 'bi-gift',
//                        'bg' => 'bg-pink',
//                        'message' => 'Congratulation! Your property <span class="fw-bold">#H10215</span> has reached 1000 views.',
//                        'tag' => 'Directory',
//                        'tag_class' => 'text-bg-warning',
//                        'time' => '1:00 am',
//                        'link' => '#'
//                    ],
//                    [
//                        'icon' => 'bi-patch-check',
//                        'bg' => 'bg-success',
//                        'message' => 'Your property <span class="fw-bold">#H10215</span> is published and live now.',
//                        'tag' => 'System',
//                        'tag_class' => 'text-bg-primary',
//                        'time' => '1:00 am',
//                        'link' => '#'
//                    ],
                ];
                ?>
                <div class="dropdown d-inline-block">
                    <button class="btn btn-link btn-square btn-icon btn-link-header dropdown-toggle no-caret"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i data-feather="bell"></i>
                        <span class="position-absolute top-0 end-0 badge rounded-pill bg-danger p-1">
            <small><?= count($notifications) > 0 ? '9+' : '0'; ?></small>
            <span class="visually-hidden">unread messages</span>
        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end notification-dd sm-mi-95px">
                        <?php if (empty($notifications)): ?>
                            <li class="text-center p-3">
                                <span class="small text-muted">No notifications</span>
                            </li>
                        <?php else: ?>
                        <?php foreach ($notifications as $notif): ?>
                        <li>
                            <?php if ($notif['link']): ?>
                            <a class="dropdown-item p-2" href="<?= $notif['link']; ?>">
                                <?php else: ?>
                                <div class="dropdown-item p-2">
                                    <?php endif; ?>
                                    <div class="row gx-3">
                                        <div class="col-auto">
                                            <figure class="avatar avatar-40 rounded-circle <?= $notif['bg']; ?>">
                                                <i class="bi <?= $notif['icon']; ?> text-white"></i>
                                            </figure>
                                        </div>
                                        <div class="col">
                                            <p class="mb-2 small"><?= $notif['message']; ?></p>
                                            <span class="row">
                                        <?php if ($notif['tag']): ?>
                                            <span class="col">
                                                <span class="badge badge-light rounded-pill <?= $notif['tag_class']; ?> small">
                                                    <?= $notif['tag']; ?>
                                                </span>
                                            </span>
                                        <?php endif; ?>
                                        <span class="col-auto small opacity-75"><?= $notif['time']; ?></span>
                                    </span>
                                        </div>
                                    </div>
                                    <?php if ($notif['link']): ?>
                            </a>
                            <?php else: ?>
                </div>
                <?php endif; ?>
                </li>
                <?php endforeach; ?>
                <?php endif; ?>
                </ul>
            </div>

            <div class="dropdown d-inline-block">
                    <a class="dropdown-toggle btn btn-link btn-square btn-link-header style-none no-caret px-0" id="userprofiledd" data-bs-toggle="dropdown" aria-expanded="false" role="button">
                        <div class="row gx-0 d-inline-flex">
                            <div class="col-auto align-self-center">
                                <figure class="avatar avatar-28 rounded-circle coverimg align-middle">
                                    <img src="<?= $profile_photo ?>" alt="" id="userphotoonboarding2">
                                </figure>
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end width-300 pt-0 px-0 sm-mi-45px"
                         aria-labelledby="userprofiledd">
                        <div class="bg-theme-1-space rounded py-3 mb-3 dropdown-dontclose">
                            <div class="row gx-0">
                                <div class="col-auto px-3">
                                    <figure class="avatar avatar-50 rounded-circle coverimg align-middle">
                                        <img src="<?= $profile_photo ?>" alt="">
                                    </figure>
                                </div>
                                <div class="col align-self-center">
                                    <p class="mb-1">
                                        <span><?= $get_user['fname'] ?></span>
                                    </p>
                                    <p><i class="bi bi-wallet2 me-2"></i> <?= $user_currency . number_format($get_user['balance']) ?> <small class="opacity-50">Balance</small>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="px-2">
                            <div>
                                <a class="dropdown-item" href="<?= $profile['link'] ?>"><i data-feather="user" class="avatar avatar-18 me-1"></i> My Profile</a>
                            </div>
                            <div>
                                <a class="dropdown-item" href="<?= $transactions['link'] ?>">
                                    <i data-feather="dollar-sign" class="avatar avatar-18 me-1"></i>Earning
                                </a>
                            </div>
                            <div class="dropdown open-left dropdown-dontclose">
                                <a class="dropdown-item" data-bs-toggle="dropdown" aria-expanded="false" role="button">
                                    <div class="row">
                                        <div class="col">
                                            <i class="bi bi-translate avatar avatar-18 me-1"></i> Language
                                        </div>
                                        <div class="col-auto">
                                            <small class="vm">EN - English</small> <i class="bi bi-translate"></i>
                                        </div>
                                        <div class="col-auto">
                                            <span class="arrow bi bi-chevron-right"></span>
                                        </div>
                                    </div>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <div><a class="dropdown-item active" data-value="EN">EN - English</a></div>
                                    <div><a class="dropdown-item" data-value="FR">FR - French</a></div>
                                    <div><a class="dropdown-item" data-value="CH">CH - Chinese</a></div>
                                    <div><a class="dropdown-item" data-value="HI">HI - Hindi</a></div>
                                </div>
                            </div>
                            <div>
                                <a class="dropdown-item" href="<?= $settings['link'] ?>"><i data-feather="settings" class="avatar avatar-18 me-1"></i>Account Setting</a></div>
                            <div>
                                <a id="logout-btn" class="dropdown-item theme-red" href="javascript:void(0)"><i data-feather="power" class="avatar avatar-18 me-1"></i>Logout</a>
                            </div>
                            <script>
                                document.getElementById("logout-btn").addEventListener("click", function () {
                                    // Create a fake form and submit it as POST
                                    const form = document.createElement("form");
                                    form.method = "POST";
                                    form.style.display = "none";

                                    const logoutInput = document.createElement("input");
                                    logoutInput.type = "hidden";
                                    logoutInput.name = "logout";
                                    logoutInput.value = "1";

                                    form.appendChild(logoutInput);
                                    document.body.appendChild(form);
                                    form.submit();
                                });
                            </script>
                        </div>
                    </div>
                </div>
                <button class="navbar-toggler btn btn-link btn-link-header btn-square btn-icon collapsed" type="button"
                        data-bs-toggle="collapse" data-bs-target="#header-navbar" aria-controls="header-navbar"
                        aria-expanded="false" aria-label="Toggle navigation"><i data-feather="more-vertical"
                                                                                class="openbtn"></i> <i data-feather="x"
                                                                                                        class="closebtn"></i>
                </button>
            </div>
        </div>
    </nav>
</header>
<div class="adminuiux-wrap">
    <div class="adminuiux-sidebar">
        <div class="adminuiux-sidebar-inner">
            <div class="px-3 not-iconic mt-3">
                <div class="row">
                    <div class="col align-self-center"><h6 class="fw-medium">Main Menu</h6></div>
                    <div class="col-auto"><a class="btn btn-link btn-square" data-bs-toggle="collapse"
                                             data-bs-target="#usersidebarprofile" aria-expanded="false" role="button"
                                             aria-controls="usersidebarprofile"><i data-feather="user"></i></a></div>
                </div>
                <div class="text-center collapse" id="usersidebarprofile">
                    <figure class="avatar avatar-100 rounded-circle coverimg my-3">
                        <img src="<?= $profile_photo ?>" alt=""></figure>
                    <h5 class="mb-1 fw-medium"><?= $fname ?></h5>
                    <p class="small">The Investment UI Kit</p></div>
            </div>
            <ul class="nav flex-column menu-active-line">
                <li class="nav-item"><a href="investment-dashboard.html" class="nav-link"><i
                            class="menu-icon bi bi-columns-gap"></i> <span class="menu-name">Dashboard</span></a></li>
                <li class="nav-item"><a href="investment-wallet.html" class="nav-link"><i
                            class="menu-icon bi bi-wallet"></i> <span class="menu-name">Wallet</span></a></li>
                <li class="nav-item"><a href="investment-goals.html" class="nav-link"><i
                            class="menu-icon bi bi-bullseye"></i> <span class="menu-name">My Goals</span></a></li>
                <li class="nav-item"><a href="investment-loan-list.html" class="nav-link"><i
                            class="menu-icon bi bi-bank"></i> <span class="menu-name">My Loans</span></a></li>
                <li class="nav-item dropdown"><a href="javascrit:void(0)" class="nav-link dropdown-toggle"
                                                 data-bs-toggle="dropdown"><i class="menu-icon bi bi-piggy-bank"></i>
                        <span class="menu-name">Investment</span></a>
                    <div class="dropdown-menu">
                        <div class="nav-item"><a href="investment-company-shares.html" class="nav-link"><i
                                    class="menu-icon bi bi-bank"></i> <span class="menu-name">Company Share</span></a>
                        </div>
                        <div class="nav-item"><a href="investment-mutual-funds.html" class="nav-link"><i
                                    class="menu-icon bi bi-cash-coin"></i> <span
                                    class="menu-name">Mutual Fund</span></a>
                        </div>
                        <div class="nav-item"><a href="investment-deposit.html" class="nav-link"><i
                                    class="menu-icon bi bi-percent"></i> <span class="menu-name">Deposit</span></a>
                        </div>
                    </div>
                </li>
                <li class="nav-item"><a href="investment-explore.html" class="nav-link"><i
                            class="menu-icon bi bi-search"></i> <span class="menu-name">Explore</span></a></li>
                <li class="nav-item"><a href="investment-statistics.html" class="nav-link"><i
                            class="menu-icon bi bi-bar-chart-line"></i> <span class="menu-name">Statistics</span></a>
                </li>
                <li class="nav-item"><a href="investment-calculator.html" class="nav-link"><i
                            class="menu-icon bi bi-calculator"></i> <span class="menu-name">Calculators</span></a></li>
                <li class="nav-item"><a href="investment-pages.html" class="nav-link"><i
                            class="menu-icon bi bi-layers"></i> <span class="menu-name">Pages</span> <span
                            class="badge text-bg-primary mx-2">55+</span></a></li>
                <li class="nav-item"><a href="investment-personalization.html" class="nav-link"><i
                            class="menu-icon bi bi-palette h4"></i> <span class="menu-name">Personalize ❤️</span></a>
                </li>
                <li class="nav-item"><a class="nav-link" href="components.html"><i class="menu-icon bi bi-cpu"></i>
                        <span class="menu-name">Components</span></a></li>
            </ul>
            <div class="mt-auto"></div>
            <div class="px-3 mb-3 not-iconic"><h6 class="mb-3 fw-medium">Quick Links</h6>
                <div class="card adminuiux-card">
                    <div class="card-body p-2">
                        <div class="row gx-2">
                            <div class="col-12 d-flex justify-content-between"><a
                                    href="investment-search-mutual-funds.html"
                                    class="btn btn-square btn-link theme-red"><span class="position-relative"><i
                                            data-feather="heart"></i> <span
                                            class="position-absolute top-0 start-100 translate-middle p-1 bg-success rounded-circle"><span
                                                class="visually-hidden">New alerts</span> </span></span></a><a
                                    href="investment-schedule.html" class="btn btn-square btn-link"><span
                                        class="position-relative"><i data-feather="calendar"></i> <span
                                            class="position-absolute top-0 start-100 translate-middle p-1 bg-warning rounded-circle"><span
                                                class="visually-hidden">New alerts</span> </span></span></a><a
                                    href="investment-inbox.html" class="btn btn-square btn-link"><i
                                        data-feather="inbox"></i> </a><a href="investment-help-center.html"
                                                                         class="btn btn-square btn-link"><i
                                        data-feather="help-circle"></i></a></div>
                        </div>
                    </div>
                </div>
            </div>
            <ul class="nav flex-column menu-active-line">
                <li class="nav-item"><a href="investment-referral.html" class="nav-link"><i class="menu-icon"
                                                                                            data-feather="users"></i>
                        <span class="menu-name">Referral</span></a></li>
                <li class="nav-item"><a href="investment-settings.html" class="nav-link"><i class="menu-icon"
                                                                                            data-feather="settings"></i>
                        <span class="menu-name">Settings</span></a></li>
            </ul>
        </div>
    </div>
    <main class="adminuiux-content has-sidebar" onclick="contentClick()">