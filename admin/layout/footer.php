<!-- /.content-wrapper -->

<footer class="main-footer">
    <div class="pull-right d-none d-sm-inline-block">
        <ul class="nav nav-primary nav-dotted nav-dot-separated justify-content-center justify-content-md-end">
            <li class="nav-item">
                <a class="nav-link" href="javascript:void(0)">FAQ</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" target="_blank">Support</a>
            </li>
        </ul>
    </div>
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
    &copy; <?php echo date('Y'); ?> <a href="<?=$siteLink?>"><?=$site_name?></a>. All Rights Reserved.
</footer>

<!-- Control Sidebar -->

<aside class="control-sidebar">

    <div class="rpanel-title"><span class="pull-right btn btn-circle btn-danger" data-toggle="control-sidebar"><i class="ion ion-close text-white" ></i></span> </div>  <!-- Create the tabs -->
    <ul class="nav nav-tabs control-sidebar-tabs">
        <li class="nav-item"><a href="#control-sidebar-home-tab" data-bs-toggle="tab" ><i class="mdi mdi-message-text"></i></a></li>
        <li class="nav-item"><a href="#control-sidebar-settings-tab" data-bs-toggle="tab"><i class="mdi mdi-playlist-check"></i></a></li>
    </ul>
    <!-- Tab panes -->
    <div class="tab-content">
        <!-- Home tab content -->
        <div class="tab-pane" id="control-sidebar-home-tab">
            <div class="flexbox">
                <a href="javascript:void(0)" class="text-grey">
                    <i class="ti-more"></i>
                </a>
                <p>Users</p>
                <a href="javascript:void(0)" class="text-end text-grey"><i class="ti-plus"></i></a>
            </div>
            <div class="lookup lookup-sm lookup-right d-none d-lg-block">
                <input type="text" name="s" placeholder="Search" class="w-p100">
            </div>
            <div class="media-list media-list-hover mt-20">
                <div class="media py-10 px-0">
                    <a class="avatar avatar-lg status-success" href="#">
                        <img src="<?=$siteLink?>/admin/images/avatar/1.jpg" alt="...">
                    </a>
                    <div class="media-body">
                        <p class="fs-16">
                            <a class="hover-primary" href="#"><strong>Tyler</strong></a>
                        </p>
                        <p>Praesent tristique diam...</p>
                        <span>Just now</span>
                    </div>
                </div>

                <div class="media py-10 px-0">
                    <a class="avatar avatar-lg status-danger" href="#">
                        <img src="<?=$siteLink?>/admin/images/avatar/2.jpg" alt="...">
                    </a>
                    <div class="media-body">
                        <p class="fs-16">
                            <a class="hover-primary" href="#"><strong>Luke</strong></a>
                        </p>
                        <p>Cras tempor diam ...</p>
                        <span>33 min ago</span>
                    </div>
                </div>

                <div class="media py-10 px-0">
                    <a class="avatar avatar-lg status-warning" href="#">
                        <img src="<?=$siteLink?>/admin/images/avatar/3.jpg" alt="...">
                    </a>
                    <div class="media-body">
                        <p class="fs-16">
                            <a class="hover-primary" href="#"><strong>Evan</strong></a>
                        </p>
                        <p>In posuere tortor vel...</p>
                        <span>42 min ago</span>
                    </div>
                </div>

                <div class="media py-10 px-0">
                    <a class="avatar avatar-lg status-primary" href="#">
                        <img src="<?=$siteLink?>/admin/images/avatar/4.jpg" alt="...">
                    </a>
                    <div class="media-body">
                        <p class="fs-16">
                            <a class="hover-primary" href="#"><strong>Evan</strong></a>
                        </p>
                        <p>In posuere tortor vel...</p>
                        <span>42 min ago</span>
                    </div>
                </div>

                <div class="media py-10 px-0">
                    <a class="avatar avatar-lg status-success" href="#">
                        <img src="<?=$siteLink?>/admin/images/avatar/1.jpg" alt="...">
                    </a>
                    <div class="media-body">
                        <p class="fs-16">
                            <a class="hover-primary" href="#"><strong>Tyler</strong></a>
                        </p>
                        <p>Praesent tristique diam...</p>
                        <span>Just now</span>
                    </div>
                </div>

                <div class="media py-10 px-0">
                    <a class="avatar avatar-lg status-danger" href="#">
                        <img src="<?=$siteLink?>/admin/images/avatar/2.jpg" alt="...">
                    </a>
                    <div class="media-body">
                        <p class="fs-16">
                            <a class="hover-primary" href="#"><strong>Luke</strong></a>
                        </p>
                        <p>Cras tempor diam ...</p>
                        <span>33 min ago</span>
                    </div>
                </div>

                <div class="media py-10 px-0">
                    <a class="avatar avatar-lg status-warning" href="#">
                        <img src="<?=$siteLink?>/admin/images/avatar/3.jpg" alt="...">
                    </a>
                    <div class="media-body">
                        <p class="fs-16">
                            <a class="hover-primary" href="#"><strong>Evan</strong></a>
                        </p>
                        <p>In posuere tortor vel...</p>
                        <span>42 min ago</span>
                    </div>
                </div>

            </div>

        </div>
        <!-- /.tab-pane -->
        <!-- Settings tab content -->
        <div class="tab-pane" id="control-sidebar-settings-tab">
            <!-- (Settings tab content remains unchanged) -->
        </div>
    </div>
</aside>

<!-- Add the sidebar's background. This div must be placed immediately after the control sidebar -->
<div class="control-sidebar-bg"></div>
</div>

<!-- ./wrapper -->
<!-- ./side demo panel -->

<div class="sticky-toolbar">
    <a href="https://themeforest.net/item/crypto-admin-responsive-bootstrap-4-admin-html-templates/21604673" data-bs-toggle="tooltip" data-bs-placement="left" title="Buy Now" class="waves-effect waves-light btn btn-primary-light btn-flat mb-5 btn-sm" target="_blank">
        <span class="icon-Money"><span class="path1"></span><span class="path2"></span></span>
    </a>
    <a href="https://themeforest.net/user/multipurposethemes/portfolio" data-bs-toggle="tooltip" data-bs-placement="left" title="Portfolio" class="waves-effect waves-light btn btn-primary-light btn-flat mb-5 btn-sm" target="_blank">
        <span class="icon-Image"></span>
    </a>
    <a id="chat-popup" href="#" data-bs-toggle="tooltip" data-bs-placement="left" title="Live Chat" class="waves-effect waves-light btn btn-primary-light btn-flat btn-sm">
        <span class="icon-Group-chat"><span class="path1"></span><span class="path2"></span></span>
    </a>
</div>

<!-- Chatbox and remaining content -->
<div id="chat-box-body">
    <!-- (Chatbox content with corrected image paths) -->
    <img src="<?=$siteLink?>/admin/images/avatar/2.jpg" class="avatar avatar-lg">
    <img src="<?=$siteLink?>/admin/images/avatar/3.jpg" class="avatar avatar-lg">
</div>

<!-- Vendor JS -->
<script src="<?=$siteLink?>/admin/js/vendors.min.js"></script>
<script src="<?=$siteLink?>/admin/js/pages/chat-popup.js"></script>
<script src="<?=$siteLink?>/admin/assets/icons/feather-icons/feather.min.js"></script>

<!-- Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Crypto Admin App -->
<script src="<?=$siteLink?>/admin/js/template.js"></script>
<script src="<?=$siteLink?>/admin/js/pages/dashboard.js"></script>

<!-- Custom JS -->
<script>
    // Initialize feather icons
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
    
    // Disable form resubmission on page refresh
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert-dismissible').fadeOut('slow');
    }, 5000);
</script>

</body>
</html>