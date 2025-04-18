<?php
$page_name = 'Contact';
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

    <!-- contact-style-two -->
    <section class="contact-style-two">
        <div class="outer-container sec-pad">
            <div class="pattern-layer">
                <div class="pattern-1"></div>
                <div class="pattern-2"></div>
            </div>
            <figure class="image-layer">
                <img src="<?=$site_link?>/front_assets/images/resource/contact-1.png" alt="Contact Illustration">
            </figure>
            <div class="auto-container">
                <div class="row clearfix">

                    <!-- Section Title -->
                    <div class="col-lg-6 col-md-12 col-sm-12 title-column">
                        <div class="sec-title light">
                            <span class="sub-title">Get in Touch</span>
                            <h2>Let’s Talk! <br />We’d Love to Hear From You</h2>
                            <p>Fill out the form below and our team will respond shortly.</p>
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <div class="col-lg-6 col-md-12 col-sm-12 content-column">
                        <div class="content-box">
                            <div class="form-inner">
                                <form method="post" action="" id="contact-form" class="default-form">
                                    <div class="row clearfix">

                                        <!-- First Name -->
                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <label>First Name</label>
                                            <input type="text" name="fname" placeholder="Enter your first name" required>
                                        </div>

                                        <!-- Last Name -->
                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <label>Last Name</label>
                                            <input type="text" name="lname" placeholder="Enter your last name" required>
                                        </div>

                                        <!-- Company or Project -->
                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <label>Project / Company</label>
                                            <input type="text" name="subject" placeholder="E.g., Exodus Holdings Ltd." required>
                                        </div>

                                        <!-- Phone -->
                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <label>Phone Number</label>
                                            <input type="text" name="phone" placeholder="+1 234 567 890" required>
                                        </div>

                                        <!-- Email -->
                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                            <label>Email Address</label>
                                            <input type="email" name="email" placeholder="Enter your email address" required>
                                        </div>

                                        <!-- Services -->
                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                            <label>Interested In</label>
                                            <div class="select-box">
                                                <select class="selectmenu" name="interest">
                                                    <option value="Traditional Consulting">Traditional Consulting</option>
                                                    <option value="Portfolio Management">Portfolio Management</option>
                                                    <option value="Asset Allocation">Asset Allocation</option>
                                                    <option value="Risk Management">Risk Management</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Message -->
                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                            <label>Your Message</label>
                                            <textarea name="message" placeholder="Type your message here..."></textarea>
                                        </div>

                                        <!-- Privacy Checkbox -->
                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                            <div class="check-box">
                                                <input class="check" type="checkbox" id="privacyCheck" required>
                                                <label for="privacyCheck">I confirm that I have read and agree to the <a href="<?=$privacy['link']?> target="_blank">Privacy Policy</a>.</label>
                                            </div>
                                        </div>

                                        <!-- Submit Button -->
                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group message-btn">
                                            <button class="theme-btn btn-two" type="submit" name="submit-form"><span>Send Message</span></button>
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div> <!-- .row -->
            </div>
        </div>
    </section>
    <!-- contact-style-two end -->


<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/layout/footer.php';
?>