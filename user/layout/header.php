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
            <a class="navbar-brand" href="investment-dashboard.html"><img data-bs-img="light" src="<?= $site_logo ?>"
                                                                          alt="">
                <img data-bs-img="dark" src="<?= $site_logo ?>" alt="">
            </a>
            <div class="collapse navbar-collapse right-in-device justify-content-center" id="header-navbar">
                <ul class="navbar-nav mx-lg-3 mb-2 mb-md-0">
                    <li class="nav-item"><a class="nav-link" href="<?= $dashboard['link'] ?>">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $profile['link'] ?>">Portfolio</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $transactions['link'] ?>">Transaction</a></li>
                    <li class="nav-item"><a class="nav-link" href="investment-blogs.html">News</a></li>
                </ul>
            </div>
            <div class="ms-auto">
                <button class="btn btn-link btn-square btn-icon btn-link-header" type="button" onclick="openSearch()"><i
                        data-feather="search"></i></button>
                <button class="btn btn-link btn-square btnsunmoon btn-link-header" id="btn-layout-modes-dark-page"><i
                        class="sun mx-auto" data-feather="sun"></i> <i class="moon mx-auto" data-feather="moon"></i>
                </button>
                <div class="dropdown d-none d-sm-inline-block">
                    <button class="btn btn-link btn-square btn-icon btn-link-header dropdown-toggle no-caret"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="grid"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end width-300 pt-0 px-0">
                        <div class="bg-theme-1-space rounded py-3 mb-2 dropdown-dontclose text-center"><p class="mb-0">
                                Applications</p>
                            <p class="opacity-50 small">Make your app innovative</p></div>
                        <div class="px-2">
                            <div class="row g-0 text-center mb-2">
                                <div class="col-4"><a class="dropdown-item square-item" href="app-finance.html">
                                        <div class="avatar avatar-40 rounded mb-2"><i class="bi bi-bank fs-4 mx-0"></i>
                                        </div>
                                        <p class="mb-0">Finance</p>
                                        <p class="fs-12 opacity-50 mb-2">Accounting</p></a></div>
                                <div class="col-4"><a class="dropdown-item square-item" href="app-network.html">
                                        <div class="avatar avatar-40 rounded mb-2"><i class="bi bi-globe fs-4 mx-0"></i>
                                        </div>
                                        <p class="mb-0">Network</p>
                                        <p class="fs-12 opacity-50 mb-2">Stabilize</p></a></div>
                                <div class="col-4"><a class="dropdown-item square-item" href="app-ecommerce.html">
                                        <div class="avatar avatar-40 rounded mb-2"><i class="bi bi-box fs-4 mx-0"></i>
                                        </div>
                                        <p class="mb-0">Inventory</p>
                                        <p class="fs-12 opacity-50 mb-2">Assuring</p></a></div>
                                <div class="col-4"><a class="dropdown-item square-item" href="app-project.html">
                                        <div class="avatar avatar-40 rounded mb-2"><i
                                                class="bi bi-folder fs-4 mx-0"></i>
                                        </div>
                                        <p class="mb-0">Project</p>
                                        <p class="fs-12 opacity-50 mb-2">Management</p></a></div>
                                <div class="col-4"><a class="dropdown-item square-item" href="app-social.html">
                                        <div class="avatar avatar-40 rounded mb-2"><i
                                                class="bi bi-people fs-4 mx-0"></i>
                                        </div>
                                        <p class="mb-0">Social</p>
                                        <p class="fs-12 opacity-50 mb-2">Tracking</p></a></div>
                                <div class="col-4"><a class="dropdown-item square-item" href="app-learning.html">
                                        <div class="avatar avatar-40 rounded mb-2"><i
                                                class="bi bi-journal-bookmark fs-4 mx-0"></i></div>
                                        <p class="mb-0">Learning</p>
                                        <p class="fs-12 opacity-50 mb-2">Make-easy</p></a></div>
                            </div>
                        </div>
                        <div class="text-center"><a class="btn btn-link text-center" href="app-demo.html">View all <i
                                    class="bi bi-arrow-right fs-14"></i></a></div>
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
                <div class="dropdown d-inline-block">
                    <button class="btn btn-link btn-square btn-icon btn-link-header dropdown-toggle no-caret"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="bell"></i>
                        <span
                            class="position-absolute top-0 end-0 badge rounded-pill bg-danger p-1"><small>9+</small> <span
                                class="visually-hidden">unread messages</span></span></button>
                    <ul class="dropdown-menu dropdown-menu-end notification-dd sm-mi-95px">
                        <li><a class="dropdown-item p-2" href="#">
                                <div class="row gx-3">
                                    <div class="col-auto">
                                        <figure class="avatar avatar-40 rounded-circle bg-pink"><i
                                                class="bi bi-gift text-white"></i></figure>
                                    </div>
                                    <div class="col"><p class="mb-2 small">Congratulation! Your property <span
                                                class="fw-bold">#H10215</span> has reached 1000 views.</p><span
                                            class="row"><span class="col"><span
                                                    class="badge badge-light rounded-pill text-bg-warning small">Directory</span></span> <span
                                                class="col-auto small opacity-75">1:00 am</span></span></div>
                                </div>
                            </a></li>
                        <li><a class="dropdown-item p-2" href="#">
                                <div class="row gx-3">
                                    <div class="col-auto">
                                        <figure class="avatar avatar-40 rounded-circle bg-success"><i
                                                class="bi bi-patch-check text-white"></i></figure>
                                    </div>
                                    <div class="col"><p class="mb-2 small">Your property <span
                                                class="fw-bold">#H10215</span> is published and live now.</p><span
                                            class="row"><span class="col"><span
                                                    class="badge badge-light rounded-pill text-bg-primary small">System</span></span> <span
                                                class="col-auto small opacity-75">1:00 am</span></span></div>
                                </div>
                            </a></li>
                        <li><a class="dropdown-item p-2" href="#">
                                <div class="row gx-3">
                                    <div class="col-auto">
                                        <figure class="avatar avatar-40 rounded-circle bg-info"><i
                                                class="bi bi-clipboard-check text-white"></i></figure>
                                    </div>
                                    <div class="col"><p class="mb-2 small">User <span class="fw-bold">Rahana</span> has
                                            updated <span class="fw-bold">#H10215</span> property.</p><span class="row"><span
                                                class="col"><span
                                                    class="badge badge-light rounded-pill text-bg-success small">team</span></span> <span
                                                class="col-auto small opacity-75">1:00 am</span></span></div>
                                </div>
                            </a></li>
                        <li>
                            <div class="dropdown-item p-2">
                                <div class="row gx-3">
                                    <div class="col-auto">
                                        <figure class="avatar avatar-40 rounded-circle bg-warning"><i
                                                class="bi bi-bell text-white"></i></figure>
                                    </div>
                                    <div class="col"><p class="mb-2 small">Your subscription going to expire soon.
                                            Please <a href="profile-subscription.html">upgrade</a> to get service
                                            interrupt
                                            free.</p>
                                        <p class="opacity-75 small">4 days ago</p></div>
                                </div>
                            </div>
                        </li>
                        <li class="text-center">
                            <button class="btn btn-link text-center" onclick="notifcationAll()">View all <i
                                    class="bi bi-arrow-right fs-14"></i></button>
                        </li>
                    </ul>
                </div>
                <div class="dropdown d-inline-block"><a
                        class="dropdown-toggle btn btn-link btn-square btn-link-header style-none no-caret px-0"
                        id="userprofiledd" data-bs-toggle="dropdown" aria-expanded="false" role="button">
                        <div class="row gx-0 d-inline-flex">
                            <div class="col-auto align-self-center">
                                <figure class="avatar avatar-28 rounded-circle coverimg align-middle"><img
                                        src="<?= $site_link ?>/back_assets/img/modern-ai-image/user-6.jpg" alt=""
                                        id="userphotoonboarding2">
                                </figure>
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end width-300 pt-0 px-0 sm-mi-45px"
                         aria-labelledby="userprofiledd">
                        <div class="bg-theme-1-space rounded py-3 mb-3 dropdown-dontclose">
                            <div class="row gx-0">
                                <div class="col-auto px-3">
                                    <figure class="avatar avatar-50 rounded-circle coverimg align-middle"><img
                                            src="<?= $site_link ?>/back_assets/img/modern-ai-image/user-6.jpg" alt="">
                                    </figure>
                                </div>
                                <div class="col align-self-center"><p class="mb-1">
                                        <span><?= $get_user['fname'] ?></span></p>
                                    <p><i class="bi bi-wallet2 me-2"></i> <?= $user_currency . number_format($get_user['balance']) ?> <small
                                            class="opacity-50">Balance</small></p></div>
                            </div>
                        </div>
                        <div class="px-2">
                            <div><a class="dropdown-item" href="investment-myprofile.html"><i data-feather="user"
                                                                                              class="avatar avatar-18 me-1"></i>
                                    My Profile</a></div>
                            <div><a class="dropdown-item" href="investment-dashboard.html">
                                    <div class="row g-0">
                                        <div class="col align-self-center"><i data-feather="layout"
                                                                              class="avatar avatar-18 me-1"></i> My
                                            Dashboard
                                        </div>
                                        <div class="col-auto">
                                            <figure class="avatar avatar-20 coverimg rounded-circle"><img
                                                    src="<?= $site_link ?>/back_assets/img/modern-ai-image/user-1.jpg"
                                                    alt=""></figure>
                                            <figure class="avatar avatar-20 coverimg rounded-circle"><img
                                                    src="<?= $site_link ?>/back_assets/img/modern-ai-image/user-2.jpg"
                                                    alt=""></figure>
                                            <figure class="avatar avatar-20 coverimg rounded-circle"><img
                                                    src="<?= $site_link ?>/back_assets/img/modern-ai-image/user-4.jpg"
                                                    alt=""></figure>
                                            <div
                                                class="avatar avatar-20 bg-theme-1 rounded-circle text-center align-middle">
                                                <small class="fs-10 align-middle">9+</small></div>
                                        </div>
                                    </div>
                                </a></div>
                            <div><a class="dropdown-item" href="investment-earning.html"><i data-feather="dollar-sign"
                                                                                            class="avatar avatar-18 me-1"></i>
                                    Earning</a></div>
                            <div><a class="dropdown-item" href="investment-mysubscription.html">
                                    <div class="row">
                                        <div class="col"><i data-feather="gift" class="avatar avatar-18 me-1"></i>
                                            Subscription
                                        </div>
                                        <div class="col-auto"><p class="small text-success">Upgrade</p></div>
                                        <div class="col-auto"><span class="arrow bi bi-chevron-right"></span></div>
                                    </div>
                                </a></div>
                            <div class="dropdown open-left dropdown-dontclose"><a class="dropdown-item"
                                                                                  data-bs-toggle="dropdown"
                                                                                  aria-expanded="false" role="button">
                                    <div class="row">
                                        <div class="col"><i class="bi bi-translate avatar avatar-18 me-1"></i> Language
                                        </div>
                                        <div class="col-auto"><small class="vm">EN - English</small> <i
                                                class="bi bi-translate"></i></div>
                                        <div class="col-auto"><span class="arrow bi bi-chevron-right"></span></div>
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
                                <a class="dropdown-item" href="investment-settings.html"><i data-feather="settings" class="avatar avatar-18 me-1"></i>Account Setting</a></div>
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
    <div class="adminuiux-search-full">
        <div class="row gx-2 align-items-center">
            <div class="col-auto">
                <button class="btn btn-link btn-square" type="button" onclick="closeSearch()"><i
                        data-feather="arrow-left"></i></button>
            </div>
            <div class="col"><input class="form-control pe-0 border-0" type="search"
                                    placeholder="Type something here..."></div>
            <div class="col-auto">
                <div class="dropdown input-group-text border-0 p-0">
                    <button class="dropdown-toggle btn btn-link btn-square no-caret" type="button" id="searchfilter2"
                            data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="sliders"></i></button>
                    <div class="dropdown-menu dropdown-menu-end dropdown-dontclose width-300">
                        <ul class="nav adminuiux-nav" id="searchtab2" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="searchall-tab2" data-bs-toggle="tab"
                                        data-bs-target="#searchall2" type="button" role="tab" aria-controls="searchall2"
                                        aria-selected="true">All
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="searchorders-tab2" data-bs-toggle="tab"
                                        data-bs-target="#searchorders2" type="button" role="tab"
                                        aria-controls="searchorders2" aria-selected="false" tabindex="-1">Orders
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="searchcontacts-tab2" data-bs-toggle="tab"
                                        data-bs-target="#searchcontacts2" type="button" role="tab"
                                        aria-controls="searchcontacts2" aria-selected="false" tabindex="-1">Contacts
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content py-3" id="searchtabContent">
                            <div class="tab-pane fade active show" id="searchall2" role="tabpanel"
                                 aria-labelledby="searchall-tab2">
                                <ul class="list-group adminuiux-list-group list-group-flush bg-none show">
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col">Search apps</div>
                                            <div class="col-auto">
                                                <div class="form-check form-switch"><input class="form-check-input"
                                                                                           type="checkbox" role="switch"
                                                                                           id="searchswitch1"> <label
                                                        class="form-check-label" for="searchswitch1"></label></div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col">Include Pages</div>
                                            <div class="col-auto">
                                                <div class="form-check form-switch"><input class="form-check-input"
                                                                                           type="checkbox" role="switch"
                                                                                           id="searchswitch2"
                                                                                           checked=""> <label
                                                        class="form-check-label" for="searchswitch2"></label></div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col">Internet resource</div>
                                            <div class="col-auto">
                                                <div class="form-check form-switch"><input class="form-check-input"
                                                                                           type="checkbox" role="switch"
                                                                                           id="searchswitch3"
                                                                                           checked=""> <label
                                                        class="form-check-label" for="searchswitch3"></label></div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col">News and Blogs</div>
                                            <div class="col-auto">
                                                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="searchswitch4">
                                                    <label class="form-check-label" for="searchswitch4"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="tab-pane fade" id="searchorders2" role="tabpanel"
                                 aria-labelledby="searchorders-tab2">
                                <ul class="list-group adminuiux-list-group list-group-flush bg-none show">
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col">Show order ID</div>
                                            <div class="col-auto">
                                                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="searchswitch5">
                                                    <label class="form-check-label" for="searchswitch5"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col">International Order</div>
                                            <div class="col-auto">
                                                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="searchswitch6" checked="">
                                                    <label class="form-check-label" for="searchswitch6"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col">Taxable Product</div>
                                            <div class="col-auto">
                                                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="searchswitch7" checked="">
                                                    <label class="form-check-label" for="searchswitch7"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col">Published Product</div>
                                            <div class="col-auto">
                                                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="searchswitch8">
                                                    <label class="form-check-label" for="searchswitch8"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="tab-pane fade" id="searchcontacts2" role="tabpanel"
                                 aria-labelledby="searchcontacts-tab2">
                                <ul class="list-group adminuiux-list-group list-group-flush bg-none show">
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col">Have email ID</div>
                                            <div class="col-auto">
                                                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="searchswitch9">
                                                    <label class="form-check-label" for="searchswitch9"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col">Have phone number</div>
                                            <div class="col-auto">
                                                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="searchswitch10" checked="">
                                                    <label class="form-check-label" for="searchswitch10"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col">Photo available</div>
                                            <div class="col-auto">
                                                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="searchswitch11" checked="">
                                                    <label class="form-check-label" for="searchswitch11"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col">Referral</div>
                                            <div class="col-auto">
                                                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="searchswitch12">
                                                    <label class="form-check-label" for="searchswitch12"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="">
                            <div class="row">
                                <div class="col">
                                    <button class="btn btn-link">Reset</button>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-theme">Apply</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                    <figure class="avatar avatar-100 rounded-circle coverimg my-3"><img
                            src="<?= $site_link ?>/back_assets/img/modern-ai-image/user-6.jpg" alt=""></figure>
                    <h5 class="mb-1 fw-medium">AdminUIUX</h5>
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