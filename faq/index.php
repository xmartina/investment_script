<?php
$page_name = 'About';
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/layout/header.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/layout/breadcrumb.php';
?>

<!-- faq-page-section -->
<section class="faq-page-section sec-pad">
    <div class="auto-container">
        <div class="row clearfix">
            <!-- Sidebar with form -->
            <div class="col-lg-4 col-md-12 col-sm-12 sidebar-side">
                <div class="faq-sidebar">
                    <div class="text-box">
                        <h3>Ask Your Question</h3>
                        <p>Need help with investments or account setup? We’re here to help you every step of the way.</p>
                    </div>
                    <div class="form-inner">
                        <form method="post" action="<?=$site_link?>/faq-submit">
                            <div class="form-group">
                                <input type="text" name="name" placeholder="Full Name *" required>
                            </div>
                            <div class="form-group">
                                <input type="email" name="email" placeholder="Email Address *" required>
                            </div>
                            <div class="form-group">
                                <div class="select-box">
                                    <select class="selectmenu" name="category">
                                        <option>Choose Category</option>
                                        <option>Account Management</option>
                                        <option>Investment Plans</option>
                                        <option>Withdrawals</option>
                                        <option>Security & Verification</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <textarea name="message" placeholder="Type your question..." required></textarea>
                            </div>
                            <div class="form-group message-btn">
                                <button type="submit" class="theme-btn btn-two">Submit Question</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- FAQ Content -->
            <div class="col-lg-8 col-md-12 col-sm-12 content-side">
                <div class="faq-content">
                    <span class="big-text">FAQ’s</span>

                    <!-- Investment Management FAQ -->
                    <div class="accordion-content">
                        <h3>Investment Management</h3>
                        <ul class="accordion-box">
                            <li class="accordion block active-block">
                                <div class="acc-btn active">
                                    <div class="icon-box"><i class="flaticon-right-chevron"></i></div>
                                    <h4>What is Exodus Ai Pro?</h4>
                                </div>
                                <div class="acc-content current">
                                    <p>Exodus Ai Pro is an advanced investment platform that combines AI technology with financial expertise to deliver consistent returns across a variety of asset classes including crypto, stocks, and forex.</p>
                                </div>
                            </li>

                            <li class="accordion block">
                                <div class="acc-btn">
                                    <div class="icon-box"><i class="flaticon-right-chevron"></i></div>
                                    <h4>How do I start investing?</h4>
                                </div>
                                <div class="acc-content">
                                    <p>To start investing, simply create an account, choose your preferred investment plan, and fund your wallet using any of the supported payment methods like BTC, USDT, or ETH.</p>
                                </div>
                            </li>

                            <li class="accordion block">
                                <div class="acc-btn">
                                    <div class="icon-box"><i class="flaticon-right-chevron"></i></div>
                                    <h4>Are my investments safe?</h4>
                                </div>
                                <div class="acc-content">
                                    <p>Yes. We use top-grade encryption and cold wallet storage for assets. Our AI models are also monitored 24/7 by finance experts to ensure risk is minimized.</p>
                                </div>
                            </li>

                            <li class="accordion block">
                                <div class="acc-btn">
                                    <div class="icon-box"><i class="flaticon-right-chevron"></i></div>
                                    <h4>Can I monitor my portfolio performance?</h4>
                                </div>
                                <div class="acc-content">
                                    <p>Absolutely. Our dashboard provides real-time tracking, detailed analytics, and reports to help you monitor your investment growth and manage your portfolio efficiently.</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Services & Support FAQ -->
                    <div class="accordion-content">
                        <h3>Support & Services</h3>
                        <ul class="accordion-box">
                            <li class="accordion block">
                                <div class="acc-btn">
                                    <div class="icon-box"><i class="flaticon-right-chevron"></i></div>
                                    <h4>How long do withdrawals take?</h4>
                                </div>
                                <div class="acc-content">
                                    <p>Withdrawals are processed within 24 hours. However, blockchain network congestion may sometimes cause slight delays. You will receive a notification once your funds are transferred.</p>
                                </div>
                            </li>

                            <li class="accordion block">
                                <div class="acc-btn">
                                    <div class="icon-box"><i class="flaticon-right-chevron"></i></div>
                                    <h4>Do you charge any fees?</h4>
                                </div>
                                <div class="acc-content">
                                    <p>There are no hidden fees. We clearly outline any applicable service charges or withdrawal fees during plan selection and transactions.</p>
                                </div>
                            </li>

                            <li class="accordion block">
                                <div class="acc-btn">
                                    <div class="icon-box"><i class="flaticon-right-chevron"></i></div>
                                    <h4>What is your refund policy?</h4>
                                </div>
                                <div class="acc-content">
                                    <p>Due to the nature of digital asset trading, all investments are final and non-refundable. Please ensure you understand your plan before proceeding. Our support team is always available to guide you.</p>
                                </div>
                            </li>

                            <li class="accordion block">
                                <div class="acc-btn">
                                    <div class="icon-box"><i class="flaticon-right-chevron"></i></div>
                                    <h4>How can I contact support?</h4>
                                </div>
                                <div class="acc-content">
                                    <p>You can reach our 24/7 customer support via the live chat on our website, or email us at <a href="mailto:support@exodusai.pro">support@exodusai.pro</a>.</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
<!-- faq-page-section end -->

<!-- cta-section -->
<section class="cta-section alternat-2">
    <div class="outer-container">
        <div class="pattern-layer" style="background-image: url(<?=$site_link?>/front_assets/images/shape/shape-25.png);"></div>

        <!-- Support Contact Image -->
        <div class="image-box-one">
            <figure class="image">
                <img src="<?=$site_link?>/front_assets/images/resource/cta-1.jpg" alt="Support Image">
            </figure>
            <div class="phone">
                <h4>Call Us: <a href="tel:+<?=$site_phone?>"><?=$site_phone?></a></h4>
            </div>
        </div>

        <!-- Join Us Prompt Image -->
        <div class="image-box-two">
            <figure class="image">
                <img src="<?=$site_link?>/front_assets/images/resource/cta-2.jpg" alt="Join Us Image">
            </figure>
            <div class="text-box">
                <h6>Looking to <br />Invest or Partner <br />With Us?</h6>
            </div>
        </div>

        <!-- Main CTA Content -->
        <div class="auto-container">
            <div class="row clearfix">
                <div class="col-lg-8 col-md-12 col-sm-12 offset-lg-2 content-column">
                    <div class="content-box">
                        <h2>Trust Exodus Ai Pro <br />for Smarter Investments</h2>
                        <div class="inner-box">
                            <figure class="image-box">
                                <img src="<?=$site_link?>/front_assets/images/resource/cart-1.jpg" alt="Investment Illustration">
                            </figure>
                            <p>Let our AI-driven strategies and expert team help you grow your capital with confidence, security, and performance-focused plans.</p>
                            <a href="<?=$register['link']?>" class="theme-btn btn-two">Get Started Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
<!-- cta-section end -->


<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/layout/footer.php'; ?>
