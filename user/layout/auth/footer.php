<!-- standard footer -->
<!-- standard index footer -->
<footer class="adminuiux-footer mt-auto">
    <div class="container-fluid text-center">
        <span class="small">Copyright @2025, <a href="<?=$site_link?>" target="_blank"><?=$site_name?></a>
        </span>
    </div>
</footer>

<!-- theming action-->
<div class="position-fixed bottom-0 end-0 m-3 z-index-5">
    <button class="btn btn-square btn-theme shadow" type="button" data-bs-toggle="offcanvas" data-bs-target="#theming" aria-controls="theming"><i class="bi bi-palette"></i></button>
    <br>
    <button class="btn btn-theme btn-square rounded-circle shadow mt-2 d-none" id="backtotop"><i class="bi bi-arrow-up"></i></button>
</div>
</div>
<div class="col-12 col-md-6 col-xl-8 p-4 d-none d-md-block">
    <div class="card adminuiux-card bg-theme-1-space position-relative overflow-hidden h-100">
        <div class="position-absolute start-0 top-0 h-100 w-100 coverimg opacity-75 z-index-0">
            <img src="<?=$site_link?>/back_assets/img/background-image/background-image-8.jpg" alt="">
        </div>
        <div class="card-body position-relative z-index-1">
            <div class="row h-100 d-flex flex-column justify-content-center align-items-center gx-0 text-center">
                <div class="col-10 col-md-11 col-xl-8 mb-4 mx-auto">

                    <!-- Slider container -->
                    <div class="swiper swipernavpagination pb-5">
                        <div class="swiper-wrapper">
                            <!-- Slides -->
                            <div class="swiper-slide">
                                <img src="<?=$site_link?>/back_assets/img/investment/slider.png" alt="Investment Dashboard" class="mw-100 mb-3">
                                <h2 class="text-white mb-3">Create and manage your investment goals with ease — all in one secure platform.</h2>
                                <p class="lead opacity-75">Welcome to <?=$site_name?>, your intelligent investment partner for smarter financial decisions.</p>
                            </div>

                            <div class="swiper-slide">
                                <img src="<?=$site_link?>/back_assets/img/investment/slider.png" alt="Secure Investments" class="mw-100 mb-3">
                                <h2 class="text-white mb-3">Track your assets, monitor returns, and stay in control of your portfolio 24/7.</h2>
                                <p class="lead opacity-75">At <?=$site_name?>, we're redefining the way you invest — safely and transparently.</p>
                            </div>

                            <div class="swiper-slide">
                                <img src="<?=$site_link?>/back_assets/img/investment/slider.png" alt="Automated Investment Tools" class="mw-100 mb-3">
                                <h2 class="text-white mb-3">Grow your wealth with automation, expert tools, and a user-first dashboard.</h2>
                                <p class="lead opacity-75">Experience next-level investing with <?=$site_name?> — built for beginners and pros alike.</p>
                            </div>
                        </div>
                        <!-- pagination -->
                        <div class="swiper-pagination white bottom-0"></div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>
</div>

</div>
</div>
</main>

<!-- Page Level js -->
<script src="<?=$site_link?>/back_assets/js/investment/investment-auth.js"></script>

</body>

</html>