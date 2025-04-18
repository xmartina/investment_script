<?php
$page_name = 'FAQ';
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/layout/header.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/layout/breadcrumb.php';
?>

<!-- contact-section -->
<section class="contact-section sec-pad">
    <div class="auto-container">
        <div class="row clearfix">

            <!-- Section Title Column -->
            <div class="col-lg-4 col-md-12 col-sm-12 title-column">
                <div class="sec-title">
                    <span class="sub-title">Contact</span>
                    <h2>Reach Out to <br />Exodus Ai Pro Today</h2>
                    <a href="<?=$site_link?>/locations" class="theme-btn btn-two">Our Locations</a>
                </div>
            </div>

            <!-- Contact Content Column -->
            <div class="col-lg-8 col-md-12 col-sm-12 content-column">
                <div class="content-box">
                    <div class="row clearfix">

                        <!-- Support Info -->
                        <div class="col-lg-6 col-md-6 col-sm-12 info-column">
                            <div class="info-block-one">
                                <div class="inner-box">
                                    <div class="upper-box">
                                        <div class="light-icon">
                                            <i class="flaticon-customer-service-1"></i>
                                        </div>
                                        <h3>Support</h3>
                                        <p>Talk with our expert support team</p>
                                    </div>
                                    <div class="lower-content">
                                        <div class="single-item">
                                            <div class="icon-box">
                                                <i class="flaticon-chat-2"></i>
                                            </div>
                                            <h6>Phone</h6>
                                            <p>
                                                General: <a href="tel:<?=$site_phone?>"><?=$site_phone?></a><br />
                                                WhatsApp: <a href="https://wa.me/<?=$site_phone?>" target="_blank"><?=$site_phone?></a>
                                            </p>
                                        </div>
                                        <div class="single-item">
                                            <div class="icon-box">
                                                <i class="flaticon-mail"></i>
                                            </div>
                                            <h6>Email</h6>
                                            <p><a href="mailto:<?=$site_email?>"><?=$site_email?></a></p>
                                        </div>
                                        <div class="link">
                                            <a href="<?=$contact['link']?>"><span>Request a Callback</span></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Address Info -->
                        <div class="col-lg-6 col-md-6 col-sm-12 info-column">
                            <div class="info-block-one">
                                <div class="inner-box">
                                    <div class="upper-box">
                                        <div class="light-icon">
                                            <i class="flaticon-cityscape"></i>
                                        </div>
                                        <h3>Head Office</h3>
                                        <p>Visit our corporate location</p>
                                    </div>
                                    <div class="lower-content">
                                        <div class="single-item">
                                            <div class="icon-box">
                                                <i class="flaticon-location-1"></i>
                                            </div>
                                            <h6>Address</h6>
                                            <p><?=$site_address?></p>
                                        </div>
                                        <div class="single-item">
                                            <div class="icon-box">
                                                <i class="flaticon-time-management"></i>
                                            </div>
                                            <h6>Office Hours</h6>
                                            <p>Mon - Sat: 9am to 6pm (GMT-5)</p>
                                        </div>
                                        <div class="link">
                                            <a href="https://www.google.com/maps?q=280+Granite+Run+Drive,+LA" target="_blank"><span>View on Map</span></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div> <!-- .row -->
                </div> <!-- .content-box -->
            </div>

        </div>
    </div>
</section>
<!-- contact-section end -->

<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/layout/footer_cta.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/layout/footer.php';
?>