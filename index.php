<?php
$page_name = 'Home';
include_once 'include/config.php';
include_once 'layout/header.php'; ?>

<!-- banner-section -->
<section class="banner-style-two">
    <div class="banner-carousel owl-theme owl-carousel">
        <div class="slide-item style-one">
            <div class="image-layer" style="background-image:url(<?=$site_link?>/front_assets/images/banner/banner-4.jpg)"></div>
            <div class="auto-container">
                <div class="content-box">
                    <h6>Invest Smarter with Exodus AI Pro</h6>
                    <h2>Harness AI-driven insights to maximize your returns</h2>
                    <p>The moment, so blinded by desire, that they cannot foresee the pain <br />and trouble that are bound to ensue. With Exodus, clarity returns.</p>
                    <div class="btn-box">
                        <a href="<?=$login['link']?>" class="theme-btn btn-two">Start Investing</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="slide-item">
            <div class="image-layer" style="background-image:url(<?=$site_link?>/front_assets/images/banner/banner-5.jpg)"></div>
            <div class="auto-container">
                <div class="content-box">
                    <h6>Real-Time Market Analysis</h6>
                    <h2>Our algorithms scan thousands of markets in seconds.</h2>
                    <p>In every tick of the market lies a story. <br />Exodus deciphers it — giving you the edge you never knew you needed.</p>
                    <div class="btn-box">
                        <a href="<?=$plans['link']?>" class="theme-btn btn-two">See Our Plans</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="slide-item">
            <div class="image-layer" style="background-image:url(<?=$site_link?>/front_assets/images/banner/banner-6.jpg)"></div>
            <div class="auto-container">
                <div class="content-box">
                    <h6>Security You Can Trust</h6>
                    <h2>End-to-end encryption with military-grade security</h2>
                    <p>Lost in the market’s noise, many wander without purpose. <br />Exodus AI filters the chaos, guiding you to clarity and gains.</p>
                    <div class="btn-box">
                        <a href="<?=$register['link']?>" class="theme-btn btn-two">Get Started</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- banner-section end -->


<!-- about-style-two -->
<section class="about-style-two sec-pad">
    <div class="pattern-layer" style="background-image: url(<?=$site_link?>/front_assets/images/shape/shape-13.png);"></div>
    <div class="auto-container">
        <div class="sec-title">
            <span class="sub-title">About Us</span>
            <h2>Offer Expert Advice on <br />AI-Powered Investing, Risk Management & Portfolio Growth</h2>
        </div>
        <div class="row clearfix">
            <div class="col-lg-6 col-md-12 col-sm-12 inner-column">
                <div class="inner-box">
                    <div class="experience-box">
                        <h2>08</h2>
                        <h6>Years <br />Experience in <br />Consulting</h6>
                    </div>
                    <ul class="list-item clearfix">
                        <li>Investment Strategy Development</li>
                        <li>Risk Management</li>
                        <li>Investment Manager Selection</li>
                    </ul>
                    <div class="btn-box">
                        <a href="<?=$about['link']?>" class="theme-btn btn-two">Explore More</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 col-sm-12 content-column">
                <div class="content-box">
                    <h3>UK-Based Investment Platform</h3>
                    <div class="text-box">
                        <p><span>E</span>xodus Ai Pro offers clarity to those who fail their financial goals through doubt or fear — which is the same as shrinking from the discipline of investing.</p>
                        <p>These moments are perfectly clear and easy to overcome.</p>
                    </div>
                    <h5>Explore our journey of data, discipline, and disruption.</h5>
                    <div class="quote-box">
                        <div class="icon-box"><i class="flaticon-quote"></i></div>
                        <h4>We believe wealth isn't just earned — it's engineered with precision.</h4>
                        <h6>Tozi Elanda</h6>
                        <span class="designation">CEO & Founder of Exodus Ai Pro</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- about-style-two end -->


<!-- funfact-section -->
<section class="funfact-section">
    <div class="auto-container">
        <div class="inner-container">
            <div class="bg-layer" style="background-image: url(<?=$site_link?>/front_assets/images/background/funfact-bg.jpg);"></div>
            <div class="row clearfix">
                <div class="col-lg-5 col-md-12 col-sm-12 content-column">
                    <div class="content-box">
                        <div class="icon-box"><img src="<?=$site_link?>/front_assets/images/icons/icon-15.png" alt=""></div>
                        <h2>We can handle the toughest investment challenges ever!</h2>
                    </div>
                </div>
                <div class="col-lg-7 col-md-12 col-sm-12 inner-column">
                    <div class="inner-box">
                        <div class="funfact-inner">
                            <div class="row clearfix">
                                <div class="col-lg-6 col-md-6 col-sm-12 funfact-block">
                                    <div class="funfact-block-one">
                                        <div class="inner-box">
                                            <div class="count-outer count-box">
                                                <span class="count-text" data-speed="1500" data-stop="840">0</span><span class="text">Billion</span>
                                            </div>
                                            <p>managed with insight, safeguarded by intelligence</p>
                                            <div class="link"><a href="<?=$about['link']?>"><i class="flaticon-diagonal-arrow"></i></a></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-12 funfact-block">
                                    <div class="funfact-block-one">
                                        <div class="inner-box">
                                            <div class="count-outer count-box">
                                                <span class="count-text" data-speed="1500" data-stop="93">0</span><span class="text">Locations</span>
                                            </div>
                                            <p>Service with Professional Firm</p>
                                            <div class="link"><a href="<?=$faq['link']?>"><i class="flaticon-diagonal-arrow"></i></a></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="video-box">
                <a href="javascript:void(0);" class="lightbox-image video-btn" data-caption=""><i class="flaticon-play-button"></i>Delight in Video</a>
            </div>
        </div>
    </div>
</section>
<!-- funfact-section end -->


<!-- service-style-two -->
<section class="service-style-two sec-pad">
    <div class="pattern-layer" style="background-image: url(<?=$site_link?>/front_assets/images/shape/shape-14.png);"></div>
    <div class="auto-container">
        <div class="sec-title centred light">
            <span class="sub-title">Services</span>
            <h2>Exceptional AI-Driven Investment Solutions</h2>
        </div>
        <div class="inner-container">
            <div class="row clearfix">
                <!-- Service 1 -->
                <div class="col-lg-4 col-md-6 col-sm-12 service-block">
                    <div class="service-block-one">
                        <div class="inner-box">
                            <div class="icon-box">
                                <div class="icon"><i class="flaticon-analytics"></i></div>
                                <span class="count-text">01</span>
                            </div>
                            <h3><a href="<?=$about['link']?>">Private Client <br />Investment Management</a></h3>
                            <div class="link"><a href="<?=$about['link']?>"><span>Explore Service</span></a></div>
                            <p>We offer tailored investment portfolios, powered by AI, to help individuals grow and protect their wealth with confidence and precision.</p>
                        </div>
                    </div>
                </div>

                <!-- Service 2 -->
                <div class="col-lg-4 col-md-6 col-sm-12 service-block">
                    <div class="service-block-one">
                        <div class="inner-box">
                            <div class="icon-box">
                                <div class="icon"><i class="flaticon-office-building"></i></div>
                                <span class="count-text">02</span>
                            </div>
                            <h3><a href="<?=$about['link']?>">Institutional <br />Investment Consulting</a></h3>
                            <div class="link"><a href="<?=$about['link']?>"><span>Explore Service</span></a></div>
                            <p>Empowering institutions with data-driven strategies, compliance support, and portfolio optimization using AI and predictive analytics.</p>
                        </div>
                    </div>
                </div>

                <!-- Service 3 -->
                <div class="col-lg-4 col-md-6 col-sm-12 service-block">
                    <div class="service-block-one">
                        <div class="inner-box">
                            <div class="icon-box">
                                <div class="icon"><i class="flaticon-retirement"></i></div>
                                <span class="count-text">03</span>
                            </div>
                            <h3><a href="<?=$about['link']?>">Retirement Plan <br />Consulting</a></h3>
                            <div class="link"><a href="<?=$about['link']?>"><span>Explore Service</span></a></div>
                            <p>We help you plan a financially secure retirement through strategic asset allocation, risk management, and AI-powered forecasting tools.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- service-style-two end -->


<!-- skills-section -->
<section class="skills-section">
    <div class="auto-container">
        <div class="row clearfix">
            <div class="col-lg-6 col-md-12 col-sm-12 image-column">
                <div class="image-box">
                    <figure class="image image-1"><img src="<?=$site_link?>/front_assets/images/resource/skills-1.jpg" alt=""></figure>
                    <figure class="image image-2"><img src="<?=$site_link?>/front_assets/images/resource/skills-2.jpg" alt=""></figure>
                    <div class="chart-box">
                        <h3>Total Product</h3>
                        <h5>January-March 2023</h5>
                        <h2>3,456</h2>
                        <h4>+25% per week</h4>
                        <div class="graph"><img src="<?=$site_link?>/front_assets/images/icons/graph-1.png" alt=""></div>
                    </div>
                    <div class="image-shape" style="background-image: url(<?=$site_link?>/front_assets/images/shape/shape-15.png);"></div>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 col-sm-12 content-column">
                <div class="content-box">
                    <div class="sec-title">
                        <span class="sub-title">Our Skills</span>
                        <h2>We stay ahead <br />so your investments never fall behind</h2>
                    </div>
                    <div class="text-box">
                        <p>At Exodus Ai Pro, we combine AI innovation with expert insights to drive smarter financial decisions. Our goal is to maximize gains while minimizing risks for every client.</p>
                        <p>We continuously refine our strategies to stay at the forefront of financial intelligence.</p>
                    </div>
                    <div class="inner-box">
                        <div class="row clearfix">

                            <!-- Personal Consulting Service -->
                            <div class="col-lg-6 col-md-6 col-sm-12 single-column">
                                <div class="single-item">
                                    <div class="icon-box"><i class="flaticon-downloads"></i></div>
                                    <h3>Personal <br />Investment Strategy</h3>
                                    <h5><a href="<?=$about['link']?>">Explore Projects</a></h5>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 skills-column">
                                <div class="progress-box">
                                    <p>Tailored portfolios aligned with your goals and risk tolerance.</p>
                                    <h5>45.2%</h5>
                                    <div class="bar">
                                        <div class="bar-inner count-bar" data-percent="45.2%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Corporate Consulting Service -->
                            <div class="col-lg-6 col-md-6 col-sm-12 single-column">
                                <div class="single-item">
                                    <div class="icon-box"><i class="flaticon-downloads"></i></div>
                                    <h3>Corporate <br />Financial Solutions</h3>
                                    <h5><a href="<?=$about['link']?>">Explore Projects</a></h5>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 skills-column">
                                <div class="progress-box">
                                    <p>Helping businesses scale through AI-powered financial planning.</p>
                                    <h5>68.9%</h5>
                                    <div class="bar">
                                        <div class="bar-inner count-bar" data-percent="68.9%"></div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- skills-section end -->

<!-- chooseus-style-two -->
<section class="chooseus-style-two sec-pad">
    <span class="big-text">benefits</span>
    <div class="auto-container">
        <div class="sec-title">
            <span class="sub-title">Why Choose Us</span>
            <h2>Reasons to Partner with Exodus Ai Pro</h2>
        </div>
        <div class="inner-container">
            <div class="bg-layer" style="background-image: url(<?=$site_link?>/front_assets/images/background/chooseus-bg.jpg);"></div>
            <div class="inner-content clearfix">

                <!-- Reason 01 -->
                <div class="chooseus-block-two">
                    <div class="inner-box">
                        <div class="icon-box"><i class="flaticon-downloads"></i></div>
                        <div class="text">Reason<span>01</span></div>
                        <h3>Client-Centered Approach</h3>
                        <div class="link"><a href="<?=$about['link']?>"><span>Explore More</span></a></div>
                        <p>Your financial goals guide our every move. We build personalized strategies that align with your long-term vision.</p>
                    </div>
                </div>

                <!-- Reason 02 -->
                <div class="chooseus-block-two">
                    <div class="inner-box">
                        <div class="icon-box"><i class="flaticon-downloads"></i></div>
                        <div class="text">Reason<span>02</span></div>
                        <h3>Independent Expertise</h3>
                        <div class="link"><a href="<?=$about['link']?>"><span>Explore More</span></a></div>
                        <p>Free from third-party influence, our advice is transparent, unbiased, and always in your best interest.</p>
                    </div>
                </div>

                <!-- Reason 03 -->
                <div class="chooseus-block-two">
                    <div class="inner-box">
                        <div class="icon-box"><i class="flaticon-downloads"></i></div>
                        <div class="text">Reason<span>03</span></div>
                        <h3>Collaborative Team Strategy</h3>
                        <div class="link"><a href="<?=$about['link']?>"><span>Explore More</span></a></div>
                        <p>Our advisors, analysts, and AI systems work together to deliver integrated and forward-thinking solutions.</p>
                    </div>
                </div>

                <!-- Reason 04 -->
                <div class="chooseus-block-two">
                    <div class="inner-box">
                        <div class="icon-box"><i class="flaticon-downloads"></i></div>
                        <div class="text">Reason<span>04</span></div>
                        <h3>Cutting-Edge AI Technology</h3>
                        <div class="link"><a href="<?=$about['link']?>"><span>Explore More</span></a></div>
                        <p>We leverage machine learning and predictive analytics to anticipate trends and optimize your portfolio.</p>
                    </div>
                </div>

                <!-- Reason 05 -->
                <div class="chooseus-block-two">
                    <div class="inner-box">
                        <div class="icon-box"><i class="flaticon-downloads"></i></div>
                        <div class="text">Reason<span>05</span></div>
                        <h3>Integrated Financial Planning</h3>
                        <div class="link"><a href="<?=$about['link']?>"><span>Explore More</span></a></div>
                        <p>From investment to insurance, we offer comprehensive financial solutions all under one roof.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
<!-- chooseus-style-two end -->

<!-- working-style-two -->
<section class="working-style-two sec-pad">
    <div class="auto-container">
        <div class="sec-title centred">
            <span class="sub-title">How We Work</span>
            <h2>Our Strategic Process for Your Growth</h2>
        </div>
        <div class="inner-container">
            <div class="row clearfix">

                <!-- Step 01 -->
                <div class="col-lg-3 col-md-6 col-sm-12 working-block">
                    <div class="working-block-two">
                        <div class="inner-box">
                            <div class="upper-box centred">
                                <span class="count-text">01</span>
                                <div class="icon-box"><i class="flaticon-meeting"></i></div>
                                <h6>First Step</h6>
                                <p>We begin with a discovery session to understand your goals, risk tolerance, and financial aspirations.</p>
                            </div>
                            <div class="lower-box">
                                <h3>Initial Consultation</h3>
                                <a href="<?=$about['link']?>"><span>Explore More</span></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 02 -->
                <div class="col-lg-3 col-md-6 col-sm-12 working-block">
                    <div class="working-block-two">
                        <div class="inner-box">
                            <div class="upper-box centred">
                                <span class="count-text">02</span>
                                <div class="icon-box"><i class="flaticon-paper"></i></div>
                                <h6>Second Step</h6>
                                <p>We gather insights and analyze data to identify market opportunities tailored to your needs.</p>
                            </div>
                            <div class="lower-box">
                                <h3>Needs Assessment</h3>
                                <a href="<?=$about['link']?>"><span>Explore More</span></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 03 -->
                <div class="col-lg-3 col-md-6 col-sm-12 working-block">
                    <div class="working-block-two">
                        <div class="inner-box">
                            <div class="upper-box centred">
                                <span class="count-text">03</span>
                                <div class="icon-box"><i class="flaticon-analysis"></i></div>
                                <h6>Third Step</h6>
                                <p>Our experts and AI systems collaborate to design a personalized strategy based on deep market research.</p>
                            </div>
                            <div class="lower-box">
                                <h3>Data-Driven Planning</h3>
                                <a href="<?=$about['link']?>"><span>Explore More</span></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 04 -->
                <div class="col-lg-3 col-md-6 col-sm-12 working-block">
                    <div class="working-block-two">
                        <div class="inner-box">
                            <div class="upper-box centred">
                                <span class="count-text">04</span>
                                <div class="icon-box"><i class="flaticon-submit"></i></div>
                                <h6>Fourth Step</h6>
                                <p>We deliver detailed reports and begin implementation, ensuring transparency and ongoing support.</p>
                            </div>
                            <div class="lower-box">
                                <h3>Execution & Review</h3>
                                <a href="<?=$about['link']?>"><span>Explore More</span></a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
<!-- working-style-two end -->

<!-- project-section -->
<section class="project-section style-two sec-pad">
    <div class="auto-container">
        <div class="sec-title">
            <span class="sub-title">Our Work</span>
            <h2>Driving Impact Through Innovation</h2>
        </div>
    </div>
    <div class="outer-container">
        <div class="three-item-carousel owl-carousel owl-theme">
            <?php
            $projects = [
                ['image' => 'project-5.jpg', 'category' => 'Business Strategy', 'title' => 'Expanding into New Markets'],
                ['image' => 'project-6.jpg', 'category' => 'Financial Planning', 'title' => 'Optimizing Investment Portfolios'],
                ['image' => 'project-7.jpg', 'category' => 'Client Solutions', 'title' => 'Custom Growth Strategy'],
                ['image' => 'project-5.jpg', 'category' => 'Business Strategy', 'title' => 'Expanding into New Markets'],
                ['image' => 'project-6.jpg', 'category' => 'Financial Planning', 'title' => 'Optimizing Investment Portfolios'],
                ['image' => 'project-7.jpg', 'category' => 'Client Solutions', 'title' => 'Custom Growth Strategy'],
                ['image' => 'project-5.jpg', 'category' => 'Business Strategy', 'title' => 'Expanding into New Markets'],
                ['image' => 'project-6.jpg', 'category' => 'Financial Planning', 'title' => 'Optimizing Investment Portfolios'],
                ['image' => 'project-7.jpg', 'category' => 'Client Solutions', 'title' => 'Custom Growth Strategy'],
                ['image' => 'project-5.jpg', 'category' => 'Business Strategy', 'title' => 'Expanding into New Markets'],
                ['image' => 'project-6.jpg', 'category' => 'Financial Planning', 'title' => 'Optimizing Investment Portfolios'],
                ['image' => 'project-7.jpg', 'category' => 'Client Solutions', 'title' => 'Custom Growth Strategy']
            ];

            foreach ($projects as $project) {
                $imagePath = $site_link . "/front_assets/images/project/" . $project['image'];
                $link = $about['link'];
                ?>
                <div class="project-block-one">
                    <div class="inner-box">
                        <figure class="image-box"><img src="<?=$imagePath?>" alt=""></figure>
                        <div class="content-inner">
                            <div class="text-box">
                                <h6><?=htmlspecialchars($project['category'])?></h6>
                                <h3><a href="<?=$link?>"><?=htmlspecialchars($project['title'])?></a></h3>
                            </div>
                            <div class="link">
                                <a href="<?=$link?>"><i class="flaticon-diagonal-arrow"></i></a>
                            </div>
                        </div>
                        <div class="view-btn">
                            <a href="<?=$imagePath?>" class="lightbox-image" data-fancybox="gallery"><i class="flaticon-zoom-in"></i></a>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</section>


<!-- pricing-section -->
<section class="pricing-section sec-pad">
    <div class="auto-container">
        <div class="sec-title centred">
            <span class="sub-title">Plan & pricing</span>
            <h2>Effective & Flexible Pricing</h2>
        </div>
        <div class="row clearfix">
            <div class="col-lg-4 col-md-6 col-sm-12 pricing-block">
                <div class="pricing-block-one">
                    <div class="pricing-table">
                        <div class="table-header">
                            <div class="icon-box"><i class="flaticon-idea"></i></div>
                            <h3>Basic <br />Package</h3>
                            <p>Pricing plan for small business</p>
                        </div>
                        <div class="table-content">
                            <ul class="feature-list clearfix">
                                <li>Traditional Consulting</li>
                                <li>Investment Management</li>
                                <li>Data Aggregation</li>
                                <li class="light">Tax Planning & Preparation</li>
                            </ul>
                            <h2>49 <span class="symble">$</span><span class="fraction">.99</span><span class="text">Billed Monthly</span></h2>
                            <a href="index-2.html" class="theme-btn btn-two">Get Started Now</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 pricing-block">
                <div class="pricing-block-one active-block">
                    <div class="pricing-table">
                        <span class="discount-text">10% Discount, Start Today</span>
                        <div class="table-header">
                            <div class="icon-box"><i class="flaticon-star"></i></div>
                            <h3>Basic <br />Package</h3>
                            <p>Pricing plan for small business</p>
                        </div>
                        <div class="table-content">
                            <ul class="feature-list clearfix">
                                <li>Traditional Consulting</li>
                                <li>Investment Management</li>
                                <li>Data Aggregation</li>
                                <li>Tax Planning & Preparation</li>
                            </ul>
                            <h2>129 <span class="symble">$</span><span class="fraction">.99</span><span class="text">Billed Monthly</span></h2>
                            <a href="index-2.html" class="theme-btn btn-two">Get Started Now</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 pricing-block">
                <div class="pricing-block-one">
                    <div class="pricing-table">
                        <div class="table-header">
                            <div class="icon-box"><i class="flaticon-diamond"></i></div>
                            <h3>Pro <br />Package</h3>
                            <p>Pricing plan for small business</p>
                        </div>
                        <div class="table-content">
                            <ul class="feature-list clearfix">
                                <li>Traditional Consulting</li>
                                <li>Investment Management</li>
                                <li>Data Aggregation</li>
                                <li>Tax Planning & Preparation</li>
                            </ul>
                            <h2>189 <span class="symble">$</span><span class="fraction">.99</span><span class="text">Billed Monthly</span></h2>
                            <a href="index-2.html" class="theme-btn btn-two">Get Started Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- pricing-section -->

<section class="testimonial-style-two">
    <div class="outer-container">
        <div class="pattern-layer" style="background-image: url(<?=$site_link?>/front_assets/images/shape/shape-16.png);"></div>
        <span class="big-text">Praise</span>
        <div class="auto-container">
            <div class="single-item-carousel owl-carousel owl-theme">
                <div class="testimonial-content">
                    <div class="author-box">
                        <div class="thumb-box">
                            <figure class="thumb"><img src="<?=$site_link?>/front_assets/images/resource/testimonial-1.jpg" alt=""></figure>
                            <div class="icon-box"><img src="<?=$site_link?>/front_assets/images/icons/icon-16.png" alt=""></div>
                        </div>
                        <h3>Jessica Mathews</h3>
                        <h5>Senior Financial Analyst - <br /> Pinnacle Investment Solutions</h5>
                    </div>
                    <div class="quote-box"><i class="flaticon-quote"></i></div>
                    <p>Exodus Ai Pro has provided invaluable financial advice that helped me improve my investment strategy significantly. Their tailored approach has allowed me to achieve financial milestones I never thought possible.</p>
                </div>
                <div class="testimonial-content">
                    <div class="author-box">
                        <div class="thumb-box">
                            <figure class="thumb"><img src="<?=$site_link?>/front_assets/images/resource/testimonial-1.jpg" alt=""></figure>
                            <div class="icon-box"><img src="<?=$site_link?>/front_assets/images/icons/icon-16.png" alt=""></div>
                        </div>
                        <h3>David Stewart</h3>
                        <h5>CEO - Capital Growth Strategies</h5>
                    </div>
                    <div class="quote-box"><i class="flaticon-quote"></i></div>
                    <p>Since working with Exodus Ai Pro, my investment portfolio has grown exponentially. Their expertise in market trends and forecasting has been a game-changer for our business growth.</p>
                </div>
                <div class="testimonial-content">
                    <div class="author-box">
                        <div class="thumb-box">
                            <figure class="thumb"><img src="<?=$site_link?>/front_assets/images/resource/testimonial-1.jpg" alt=""></figure>
                            <div class="icon-box"><img src="<?=$site_link?>/front_assets/images/icons/icon-16.png" alt=""></div>
                        </div>
                        <h3>Emily Carter</h3>
                        <h5>Managing Director - <br /> WealthMax Advisors</h5>
                    </div>
                    <div class="quote-box"><i class="flaticon-quote"></i></div>
                    <p>Exodus Ai Pro’s team has demonstrated deep knowledge in investment strategies. Their insights have helped us make smarter financial decisions, and I couldn’t be more satisfied with the results.</p>
                </div>
                <div class="testimonial-content">
                    <div class="author-box">
                        <div class="thumb-box">
                            <figure class="thumb"><img src="<?=$site_link?>/front_assets/images/resource/testimonial-1.jpg" alt=""></figure>
                            <div class="icon-box"><img src="<?=$site_link?>/front_assets/images/icons/icon-16.png" alt=""></div>
                        </div>
                        <h3>John Harrison</h3>
                        <h5>Portfolio Manager - <br /> Global Investment Partners</h5>
                    </div>
                    <div class="quote-box"><i class="flaticon-quote"></i></div>
                    <p>The level of professionalism and understanding at Exodus Ai Pro is exceptional. Thanks to their team, I was able to maximize my investment returns in just a few months.</p>
                </div>
                <div class="testimonial-content">
                    <div class="author-box">
                        <div class="thumb-box">
                            <figure class="thumb"><img src="<?=$site_link?>/front_assets/images/resource/testimonial-1.jpg" alt=""></figure>
                            <div class="icon-box"><img src="<?=$site_link?>/front_assets/images/icons/icon-16.png" alt=""></div>
                        </div>
                        <h3>Sarah Williams</h3>
                        <h5>Lead Investment Strategist <br /> - Quantum Wealth Group</h5>
                    </div>
                    <div class="quote-box"><i class="flaticon-quote"></i></div>
                    <p>I have been extremely impressed with Exodus Ai Pro’s ability to understand and cater to our specific investment goals. Their strategies have helped us grow our capital effectively and consistently.</p>
                </div>
            </div>
        </div>
    </div>
    <div class="image-thumb">
        <ul class="thumb-list clearfix">
            <li>
                <div class="single-item">
                    <figure class="image"><img src="<?=$site_link?>/front_assets/images/resource/thumb-1.jpg" alt=""></figure>
                </div>
            </li>
            <li>
                <div class="single-item">
                    <div class="icon-box"><i class="flaticon-feedback"></i></div>
                    <h5>2.5k Clients Rated</h5>
                </div>
            </li>
            <li>
                <div class="single-item">
                    <figure class="image"><img src="<?=$site_link?>/front_assets/images/resource/thumb-2.jpg" alt=""></figure>
                </div>
            </li>
            <li>
                <div class="single-item">
                    <div class="icon-box"><i class="flaticon-cartoon"></i></div>
                    <h5>Avg. Rating 4.8/5</h5>
                </div>
            </li>
            <li>
                <div class="single-item">
                    <figure class="image"><img src="<?=$site_link?>/front_assets/images/resource/thumb-3.jpg" alt=""></figure>
                </div>
            </li>
            <li>
                <div class="single-item">
                    <figure class="image"><img src="<?=$site_link?>/front_assets/images/resource/thumb-4.jpg" alt=""></figure>
                </div>
            </li>
        </ul>
    </div>
</section>


<!-- news-style-two -->
<!-- news-style-two -->
<section class="news-style-two sec-pad">
    <div class="auto-container">
        <div class="sec-title">
            <span class="sub-title">Insights & Tips</span>
            <h2>Latest from Exodus Ai Pro</h2>
        </div>
        <div class="single-item-carousel owl-carousel owl-theme owl-dots-none nav-style-one">
            <div class="row clearfix">
                <div class="col-lg-4 col-md-6 col-sm-12 news-block">
                    <div class="news-block-one wow fadeInUp animated" data-wow-delay="00ms" data-wow-duration="1500ms">
                        <div class="inner-box">
                            <div class="upper-box">
                                <span class="category">Smart Investing</span>
                                <ul class="post-info clearfix">
                                    <li><span>On</span> Mar 14, 2023</li>
                                    <li><span>By</span> <a href="javascript:void(0);">Justin Langer</a></li>
                                </ul>
                            </div>
                            <div class="image-box">
                                <figure class="image"><a href="javascript:void(0);"><img src="<?=$site_link?>/front_assets/images/news/news-1.jpg" alt=""></a></figure>
                                <div class="view-btn"><a href="<?=$site_link?>/front_assets/images/news/news-1.jpg" class="lightbox-image" data-fancybox="gallery"><i class="flaticon-zoom-in"></i></a></div>
                            </div>
                            <div class="lower-box">
                                <h3><a href="javascript:void(0);">How to Realign Your Investment Goals in 2025</a></h3>
                                <div class="link"><a href="javascript:void(0);"><span>Explore More</span></a></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 col-sm-12 news-block">
                    <div class="news-block-one wow fadeInUp animated" data-wow-delay="300ms" data-wow-duration="1500ms">
                        <div class="inner-box">
                            <div class="upper-box">
                                <span class="category">Wealth Growth</span>
                                <ul class="post-info clearfix">
                                    <li><span>On</span> Feb 26, 2023</li>
                                    <li><span>By</span> <a href="javascript:void(0);">Colmin Neil</a></li>
                                </ul>
                            </div>
                            <div class="image-box">
                                <figure class="image"><a href="javascript:void(0);"><img src="<?=$site_link?>/front_assets/images/news/news-2.jpg" alt=""></a></figure>
                                <div class="view-btn"><a href="<?=$site_link?>/front_assets/images/news/news-2.jpg" class="lightbox-image" data-fancybox="gallery"><i class="flaticon-zoom-in"></i></a></div>
                            </div>
                            <div class="lower-box">
                                <h3><a href="javascript:void(0);">Interview: AI in Wealth Management</a></h3>
                                <div class="link"><a href="javascript:void(0);"><span>Explore More</span></a></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 col-sm-12 post-column">
                    <div class="post-block-one">
                        <div class="inner-box">
                            <figure class="post-thumb"><a href="javascript:void(0);"><img src="<?=$site_link?>/front_assets/images/news/post-1.jpg" alt=""></a></figure>
                            <span class="category">Financial Strategy</span>
                            <h4><a href="javascript:void(0);">Want to Give Back in 2025? Here's How to Do It Smartly</a></h4>
                        </div>
                    </div>
                    <div class="post-block-one">
                        <div class="inner-box">
                            <figure class="post-thumb"><a href="javascript:void(0);"><img src="<?=$site_link?>/front_assets/images/news/post-2.jpg" alt=""></a></figure>
                            <span class="category">Market Outlook</span>
                            <h4><a href="javascript:void(0);">Q1 Investment Report: Key Trends & Projections</a></h4>
                        </div>
                    </div>
                    <div class="post-block-one">
                        <div class="inner-box">
                            <figure class="post-thumb"><a href="javascript:void(0);"><img src="<?=$site_link?>/front_assets/images/news/post-3.jpg" alt=""></a></figure>
                            <span class="category">Retirement</span>
                            <h4><a href="javascript:void(0);">Recovering from Market Dips: What Retirees Should Know</a></h4>
                        </div>
                    </div>
                    <div class="btn-box">
                        <a href="javascript:void(0);" class="theme-btn btn-two">Read All Posts</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- news-style-two end -->


<!-- clients-style-two -->
<section class="clients-style-two">
    <div class="auto-container">
        <div class="sec-title centred">
            <span class="sub-title">Our Clients</span>
            <h2>People Who Trusted Us</h2>
        </div>
        <div class="inner-container">
            <ul class="clients-list clearfix">
                <li>
                    <figure class="clients-logo"><a href="index-2.html"><img src="assets/images/clients/clients-1.png" alt=""></a></figure>
                </li>
                <li>
                    <figure class="clients-logo"><a href="index-2.html"><img src="assets/images/clients/clients-2.png" alt=""></a></figure>
                </li>
                <li>
                    <figure class="clients-logo"><a href="index-2.html"><img src="assets/images/clients/clients-3.png" alt=""></a></figure>
                </li>
                <li>
                    <figure class="clients-logo"><a href="index-2.html"><img src="assets/images/clients/clients-4.png" alt=""></a></figure>
                </li>
                <li>
                    <figure class="clients-logo"><a href="index-2.html"><img src="assets/images/clients/clients-5.png" alt=""></a></figure>
                </li>
            </ul>
            <ul class="clients-list clearfix">
                <li>
                    <figure class="clients-logo"><a href="index-2.html"><img src="assets/images/clients/clients-6.png" alt=""></a></figure>
                </li>
                <li>
                    <figure class="clients-logo"><a href="index-2.html"><img src="assets/images/clients/clients-7.png" alt=""></a></figure>
                </li>
                <li>
                    <figure class="clients-logo"><a href="index-2.html"><img src="assets/images/clients/clients-8.png" alt=""></a></figure>
                </li>
                <li>
                    <figure class="clients-logo"><a href="index-2.html"><img src="assets/images/clients/clients-9.png" alt=""></a></figure>
                </li>
                <li>
                    <figure class="clients-logo"><a href="index-2.html"><img src="assets/images/clients/clients-10.png" alt=""></a></figure>
                </li>
            </ul>
        </div>
        <div class="more-text centred">
            <h5>2.6k Companies & Individuals Trusted  Us. <a href="index-2.html"><i class="flaticon-right-chevron"></i>View All Clients</a></h5>
        </div>
    </div>
</section>
<!-- clients-style-two end -->


<?php include_once 'layout/footer.php'; ?>
