<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">

    <title><?=$page_name?> | <?=$site_name?></title>

    <!-- Fav Icon -->
    <link rel="icon" href="<?=$site_link?>/front_assets/images/<?=$site_favicon?>" type="image/x-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;0,1000;1,300;1,400;1,500;1,600;1,700;1,800;1,900;1,1000&amp;display=swap" rel="stylesheet">

    <!-- Stylesheets -->
    <link href="<?=$site_link?>/front_assets/css/font-awesome-all.css" rel="stylesheet">
    <link href="<?=$site_link?>/front_assets/css/flaticon.css" rel="stylesheet">
    <link href="<?=$site_link?>/front_assets/css/owl.css" rel="stylesheet">
    <link href="<?=$site_link?>/front_assets/css/bootstrap.css" rel="stylesheet">
    <link href="<?=$site_link?>/front_assets/css/jquery.fancybox.min.css" rel="stylesheet">
    <link href="<?=$site_link?>/front_assets/css/animate.css" rel="stylesheet">
    <link href="<?=$site_link?>/front_assets/css/nice-select.css" rel="stylesheet">
    <link href="<?=$site_link?>/front_assets/css/color.css" rel="stylesheet">
    <link href="<?=$site_link?>/front_assets/css/style.css" rel="stylesheet">
    <link href="<?=$site_link?>/front_assets/css/responsive.css" rel="stylesheet">

</head>


<!-- page wrapper -->
<body>

<div class="boxed_wrapper home_2">


    <!-- preloader -->
    <div class="loader-wrap">
        <div class="preloader">
            <div class="preloader-close">x</div>
            <div id="handle-preloader" class="handle-preloader">
                <div class="animation-preloader">
                    <div class="spinner"></div>
                    <div class="txt-loading">
                            <span data-text-preloader="c" class="letters-loading">
                                c
                            </span>
                        <span data-text-preloader="o" class="letters-loading">
                                o
                            </span>
                        <span data-text-preloader="u" class="letters-loading">
                                u
                            </span>
                        <span data-text-preloader="n" class="letters-loading">
                                n
                            </span>
                        <span data-text-preloader="s" class="letters-loading">
                                s
                            </span>
                        <span data-text-preloader="o" class="letters-loading">
                                o
                            </span>
                        <span data-text-preloader="l" class="letters-loading">
                                l
                            </span>
                        <span data-text-preloader="v" class="letters-loading">
                                v
                            </span>
                        <span data-text-preloader="e" class="letters-loading">
                                e
                            </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- preloader end -->


    <!--Search Popup-->
    <div id="search-popup" class="search-popup">
        <div class="popup-inner">
            <div class="upper-box clearfix">
                <figure class="logo-box pull-left"><a href="<?=$site_link?>"><img src="<?=$site_link?>/front_assets/images/<?=$site_logo?>.png" alt="<?=$site_name?> logo"></a></figure>
                <div class="close-search pull-right"><i class="fa-solid fa-xmark"></i></div>
            </div>
            <div class="overlay-layer"></div>
            <div class="auto-container">
                <div class="search-form">
                    <form method="post" action="">
                        <div class="form-group">
                            <fieldset>
                                <input type="search" class="form-control" name="search-input" value="" placeholder="Type your keyword and hit" required >
                                <button type="submit"><i class="flaticon-loupe"></i></button>
                            </fieldset>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- sidebar cart item -->
    <div class="xs-sidebar-group info-group info-sidebar">
        <div class="xs-overlay xs-bg-black"></div>
        <div class="xs-overlay xs-overlay-2 xs-bg-black"></div>
        <div class="xs-overlay xs-overlay-3 xs-bg-black"></div>
        <div class="xs-overlay xs-overlay-4 xs-bg-black"></div>
        <div class="xs-overlay xs-overlay-5 xs-bg-black"></div>
        <div class="xs-sidebar-widget">
            <div class="sidebar-widget-container">
                <div class="widget-heading">
                    <a href="#" class="close-side-widget"><i class="fa fa-times"></i></a>
                </div>
                <div class="sidebar-textwidget">
                    <div class="sidebar-info-contents">
                        <div class="content-inner">
                            <div class="logo">
                                <a href="<?=$site_link?>"><img src="<?=$site_link?>/front_assets/images/logo-3.png" alt=""></a>
                            </div>
                            <div class="content-box">
                                <h4>About Us</h4>
                                <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi</p>
                                <p>Research oriented solutions for Data Science and Machine Learning business needs.</p>
                                <a href="about.html" class="theme-btn btn-two">About Us</a>
                            </div>
                            <div class="contact-info">
                                <h4>Contact Info</h4>
                                <ul>
                                    <li>Chicago 12, Melborne City, USA</li>
                                    <li><a href="<?=$site_phone?>"><?=$site_phone?></a></li>
                                    <li><a href="mailto:<?=$site_email?>"><?=$site_email?></a></li>
                                </ul>
                            </div>
                            <ul class="social-box clearfix">
                                <li><a href="index.html"><i class="fa-brands fa-facebook"></i></a></li>
                                <li><a href="index.html"><i class="fa-brands fa-square-twitter"></i></a></li>
                                <li><a href="index.html"><i class="fa-solid fa-basketball"></i></a></li>
                                <li><a href="index.html"><i class="fa-brands fa-youtube"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END sidebar widget item -->


    <!-- main header -->
    <header class="main-header header-style-two">
        <!-- header-top -->
        <div class="header-top-two">
            <div class="outer-container">
                <ul class="info-list clearfix">
                    <li>
                        <div class="icon-box"><img src="<?=$site_link?>/front_assets/images/icons/icon-9.png" alt=""></div>
                        Talk to Us: <a href="tel:4188822333"><span>(+41) 888.22.333</span></a> / <a href="mailto:mailus@example.com"><span>mailus@example.com</span></a>
                    </li>
                    <li>
                        <div class="icon-box"><img src="<?=$site_link?>/front_assets/images/icons/icon-10.png" alt=""></div>
                        Reach Us: <span>Hobert, LA 90010 /  Mon - Sat: 9am to 6pm</span>
                    </li>
                </ul>
                <div class="language-box">
                    <h5><img src="<?=$site_link?>/front_assets/images/icons/icon-11.png" alt="">Global:</h5>
                    <div class="select-box">
                        <select class="selectmenu">
                            <option>Eng</option>
                            <option>Chines</option>
                            <option>Hindi</option>
                            <option>Turky</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <!-- header-lower -->
        <div class="header-lower">
            <div class="outer-container">
                <div class="outer-box">
                    <figure class="logo-box"><a href="index-2.html"><img src="<?=$site_link?>/front_assets/images/logo-3.png" alt=""></a></figure>
                    <div class="menu-area">
                        <!--Mobile Navigation Toggler-->
                        <div class="mobile-nav-toggler">
                            <i class="icon-bar"></i>
                            <i class="icon-bar"></i>
                            <i class="icon-bar"></i>
                        </div>
                        <nav class="main-menu navbar-expand-md navbar-light">
                            <div class="collapse navbar-collapse show clearfix" id="navbarSupportedContent">
                                <ul class="navigation clearfix">
                                    <li class="current">
                                        <a href="<?=$site_link?>">Home</a>
                                    </li>
                                    <li class="dropdown"><a href="index.html">Services</a>
                                        <ul>
                                            <li><a href="service.html">Services</a></li>
                                            <li class="dropdown"><a href="index.html">Private</a>
                                                <ul>
                                                    <li><a href="service-details.html">Traditional Consulting</a></li>
                                                    <li><a href="service-details-2.html">Portfolio Management</a></li>
                                                    <li><a href="service-details-3.html">Asset Allocation</a></li>
                                                    <li><a href="service-details-4.html">Risk Management</a></li>
                                                    <li><a href="service-details-5.html">Policy Development</a></li>
                                                    <li><a href="service-details-6.html">Fiduciary Consulting</a></li>
                                                </ul>
                                            </li>
                                            <li class="dropdown"><a href="index.html">Institution</a>
                                                <ul>
                                                    <li><a href="service-details-7.html">Traditional Consulting</a></li>
                                                    <li><a href="service-details-8.html">Portfolio Management</a></li>
                                                    <li><a href="service-details-9.html">Asset Allocation</a></li>
                                                    <li><a href="service-details-10.html">Risk Management</a></li>
                                                    <li><a href="service-details-11.html">Policy Development</a></li>
                                                    <li><a href="service-details-12.html">Fiduciary Consulting</a></li>
                                                </ul>
                                            </li>
                                            <li class="dropdown"><a href="index.html">Retirement</a>
                                                <ul>
                                                    <li><a href="service-details-13.html">Traditional Consulting</a></li>
                                                    <li><a href="service-details-14.html">Portfolio Management</a></li>
                                                    <li><a href="service-details-15.html">Asset Allocation</a></li>
                                                    <li><a href="service-details-16.html">Risk Management</a></li>
                                                    <li><a href="service-details-17.html">Policy Development</a></li>
                                                    <li><a href="service-details-18.html">Fiduciary Consulting</a></li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="dropdown"><a href="index.html">Projects</a>
                                        <ul>
                                            <li><a href="project.html">3 Columns</a></li>
                                            <li><a href="project-2.html">2 Columns</a></li>
                                            <li><a href="project-3.html">Masonry</a></li>
                                            <li><a href="project-details.html">Project Details</a></li>
                                        </ul>
                                    </li>
                                    <li class="dropdown"><a href="index.html">Blog</a>
                                        <ul>
                                            <li><a href="blog.html">Grid View</a></li>
                                            <li><a href="blog-2.html">List View 01</a></li>
                                            <li><a href="blog-3.html">List View 02</a></li>
                                            <li><a href="blog-details.html">Blog Details</a></li>
                                        </ul>
                                    </li>
                                    <li class="dropdown"><a href="index.html">Pages</a>
                                        <ul>
                                            <li><a href="about.html">About Us</a></li>
                                            <li><a href="team.html">Our Team</a></li>
                                            <li><a href="faq.html">Faq’s</a></li>
                                            <li><a href="comingsoon.html">Coming Soon</a></li>
                                            <li><a href="error.html">404</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="contact.html">Contact</a></li>
                                </ul>
                            </div>
                        </nav>
                        <div class="menu-right-content">
                            <div class="support-box">
                                <button>
                                    <img src="<?=$site_link?>/front_assets/images/icons/icon-12.png" alt="">
                                    Consult
                                    <span>with our experts</span>
                                </button>
                            </div>
                            <div class="search-box">
                                <div class="search-box-outer search-toggler"><img src="<?=$site_link?>/front_assets/images/icons/icon-2.png" alt="">Search</div>
                            </div>
                            <ul class="social-links clearfix">
                                <li><a href="index.html"><i class="fa-brands fa-facebook"></i></a></li>
                                <li><a href="index.html"><i class="fa-brands fa-square-twitter"></i></a></li>
                                <li><a href="index.html"><i class="fa-brands fa-youtube"></i></a></li>
                            </ul>
                            <div class="nav-btn nav-toggler navSidebar-button clearfix">
                                <img src="<?=$site_link?>/front_assets/images/icons/icon-14.png" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--sticky Header-->
        <div class="sticky-header">
            <div class="outer-container">
                <div class="outer-box">
                    <figure class="logo-box"><a href="<?=$site_link?>"><img src="<?=$site_link?>/front_assets/images/<?=$site_logo?>" alt="<?=$site_name?> logo"></a></figure>
                    <div class="menu-area clearfix">
                        <nav class="main-menu clearfix">
                            <!--Keep This Empty / Menu will come through Javascript-->
                        </nav>
                        <div class="menu-right-content">
                            <div class="support-box">
                                <button>
                                    <img src="<?=$site_link?>/front_assets/images/icons/icon-12.png" alt="">
                                    Consult
                                    <span>with our experts</span>
                                </button>
                            </div>
                            <div class="search-box">
                                <div class="search-box-outer search-toggler"><img src="<?=$site_link?>/front_assets/images/icons/icon-2.png" alt="">Search</div>
                            </div>
                            <ul class="social-links clearfix">
                                <li><a href="index.html"><i class="fa-brands fa-facebook"></i></a></li>
                                <li><a href="index.html"><i class="fa-brands fa-square-twitter"></i></a></li>
                                <li><a href="index.html"><i class="fa-brands fa-youtube"></i></a></li>
                            </ul>
                            <div class="nav-btn nav-toggler navSidebar-button clearfix">
                                <img src="<?=$site_link?>/front_assets/images/icons/icon-14.png" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- main-header end -->

    <!-- Mobile Menu  -->
    <div class="mobile-menu">
        <div class="menu-backdrop"></div>
        <div class="close-btn"><i class="fas fa-times"></i></div>

        <nav class="menu-box">
            <div class="nav-logo"><a href="<?=$site_link?>"><img src="<?=$site_link?>/front_assets/images/<?=$site_logo?>" alt="" title=""></a></div>
            <div class="menu-outer"><!--Here Menu Will Come Automatically Via Javascript / Same Menu as in Header--></div>
            <div class="contact-info">
                <h4>Contact Info</h4>
                <ul>
                    <li>Chicago 12, Melborne City, USA</li>
                    <li><a href="tel:<?=$site_phone?>"><?=$site_phone?></a></li>
                    <li><a href="mailto:<?=$site_email?>"><?=$site_email?></a></li>
                </ul>
            </div>
            <div class="social-links">
                <ul class="clearfix">
                    <li><a href="index.html"><span class="fab fa-twitter"></span></a></li>
                    <li><a href="index.html"><span class="fab fa-facebook-square"></span></a></li>
                    <li><a href="index.html"><span class="fab fa-pinterest-p"></span></a></li>
                    <li><a href="index.html"><span class="fab fa-instagram"></span></a></li>
                    <li><a href="index.html"><span class="fab fa-youtube"></span></a></li>
                </ul>
            </div>
        </nav>
    </div><!-- End Mobile Menu -->

