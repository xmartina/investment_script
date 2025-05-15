<?php
$page_name = 'Plans';
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/layout/header.php'; 
?>

<!-- Page Banner -->
<section class="page-banner">
    <div class="image-layer" style="background-image:url(<?=$site_link?>/front_assets/images/background/banner-bg.jpg)"></div>
    <div class="banner-inner">
        <div class="auto-container">
            <div class="inner-container clearfix">
                <h1>Investment & Staking Plans</h1>
                <div class="page-nav">
                    <ul class="bread-crumb clearfix">
                        <li><a href="<?=$site_link?>">Home</a></li>
                        <li>Plans</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
<!--End Banner Section -->

<!-- Investment Plans Section -->
<section class="pricing-section sec-pad">
    <div class="auto-container">
        <div class="sec-title centred">
            <span class="sub-title">Investment Plans</span>
            <h2>Grow Your Capital with AI-Powered Investment Plans</h2>
            <p>Choose from a variety of investment strategies tailored to your risk tolerance and financial goals</p>
        </div>
        <div class="row clearfix">
            <?php
            // Get site currency from admin_settings if not already defined
            if (!isset($site_currency)) {
                $currency_query = "SELECT setting_value FROM admin_settings WHERE setting_key = 'site_currency'";
                $currency_result = $conn_back->query($currency_query);
                if ($currency_result && $currency_result->num_rows > 0) {
                    $site_currency = $currency_result->fetch_assoc()['setting_value'];
                } else {
                    $site_currency = 'USD'; // Default fallback
                }
            }
            
            // Fetch all investment plans from the database
            $query = "SELECT * FROM investment_plans WHERE is_active = 1 ORDER BY min_amount ASC";
            $result = $conn_back->query($query);
            
            if ($result && $result->num_rows > 0) {
                while($plan = $result->fetch_assoc()) {
                    $isActive = ($plan['featured'] == 1) ? 'active-block' : '';
            ?>
            <div class="col-lg-4 col-md-6 col-sm-12 pricing-block mb-4">
                <div class="pricing-block-one <?=$isActive?>">
                    <div class="pricing-table">
                        <?php if($plan['featured'] == 1) { ?>
                        <span class="discount-text">Featured Plan</span>
                        <?php } ?>
                        <div class="table-header">
                            <div class="icon-box">
                                <i class="flaticon-<?= ($plan['risk_level'] == 'Low') ? 'idea' : (($plan['risk_level'] == 'Moderate') ? 'star' : 'diamond') ?>"></i>
                            </div>
                            <h3><?=htmlspecialchars($plan['name'])?></h3>
                            <p><?=htmlspecialchars($plan['plan_type'])?> - <?=htmlspecialchars($plan['category'])?></p>
                        </div>
                        <div class="table-content">
                            <ul class="feature-list clearfix">
                                <li>Risk Level: <?=htmlspecialchars($plan['risk_level'])?></li>
                                <li>Duration: <?=htmlspecialchars($plan['duration_days'])?> days</li>
                                <li>Return Interval: <?=htmlspecialchars($plan['return_interval'])?></li>
                                <li>Min Investment: <?=$plan['min_amount']?> <?=$site_currency?></li>
                                <?php if($plan['max_amount'] > 0) { ?>
                                <li>Max Investment: <?=$plan['max_amount']?> <?=$site_currency?></li>
                                <?php } else { ?>
                                <li>No Maximum Limit</li>
                                <?php } ?>
                            </ul>
                            <h2><?=$plan['roi_percent']?><span class="symble">%</span><span class="text">Return</span></h2>
                            <a href="<?=$login['link']?>" class="theme-btn btn-two">Start Investing</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
                }
            }
            ?>
        </div>
    </div>
</section>
<!-- End Investment Plans Section -->

<!-- Staking Plans Section -->
<section class="pricing-section sec-pad bg-color-1">
    <div class="auto-container">
        <div class="sec-title centred">
            <span class="sub-title">Staking Plans</span>
            <h2>Earn Passive Income Through Staking</h2>
            <p>Stake your assets and earn consistent returns with our secure staking options</p>
        </div>
        <div class="row clearfix">
            <?php
            // Fetch all staking plans from the database
            $query = "SELECT * FROM staking_plans WHERE is_active = 1 ORDER BY min_amount ASC";
            $result = $conn_back->query($query);
            
            if ($result && $result->num_rows > 0) {
                while($plan = $result->fetch_assoc()) {
                    $isActive = ($plan['featured'] == 1) ? 'active-block' : '';
            ?>
            <div class="col-lg-4 col-md-6 col-sm-12 pricing-block mb-4">
                <div class="pricing-block-one <?=$isActive?>">
                    <div class="pricing-table">
                        <?php if($plan['featured'] == 1) { ?>
                        <span class="discount-text">Featured Plan</span>
                        <?php } ?>
                        <div class="table-header">
                            <div class="icon-box">
                                <i class="flaticon-<?= ($plan['lock_period_days'] == 0) ? 'idea' : (($plan['lock_period_days'] <= 14) ? 'star' : 'diamond') ?>"></i>
                            </div>
                            <h3><?=htmlspecialchars($plan['name'])?></h3>
                            <p><?=htmlspecialchars($plan['description'])?></p>
                        </div>
                        <div class="table-content">
                            <ul class="feature-list clearfix">
                                <li>Duration: <?=htmlspecialchars($plan['duration_days'])?> days</li>
                                <li>Lock Period: <?=htmlspecialchars($plan['lock_period_days'])?> days</li>
                                <li>Daily ROI: <?=$plan['roi_daily']?>%</li>
                                <li>Min Staking: <?=$plan['min_amount']?> <?=$site_currency?></li>
                                <?php if($plan['max_amount'] > 0) { ?>
                                <li>Max Staking: <?=$plan['max_amount']?> <?=$site_currency?></li>
                                <?php } else { ?>
                                <li>No Maximum Limit</li>
                                <?php } ?>
                                <?php if($plan['early_unstake_penalty'] > 0) { ?>
                                <li>Early Unstake Penalty: <?=$plan['early_unstake_penalty']?>%</li>
                                <?php } ?>
                            </ul>
                            <h2><?=$plan['reward_percent']?><span class="symble">%</span><span class="text">APY</span></h2>
                            <a href="<?=$login['link']?>" class="theme-btn btn-two">Start Staking</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
                }
            }
            ?>
        </div>
    </div>
</section>
<!-- End Staking Plans Section -->

<!-- Call To Action Section -->
<section class="call-to-action-two">
    <div class="auto-container">
        <div class="inner-container clearfix">
            <div class="title-box">
                <h3>Ready to Maximize Your Investment Potential?</h3>
                <p>Join Exodus AI Pro today and start your journey to financial growth</p>
            </div>
            <div class="btn-box">
                <a href="<?=$register['link']?>" class="theme-btn btn-style-one"><span class="btn-title">Open an Account</span></a>
            </div>
        </div>
    </div>
</section>
<!-- End Call To Action Section -->

<!-- FAQ Section -->
<section class="faq-section">
    <div class="auto-container">
        <div class="sec-title centred">
            <span class="sub-title">FAQ</span>
            <h2>Common Questions About Our Investment & Staking Plans</h2>
        </div>
        <div class="row clearfix">
            <div class="col-lg-8 offset-lg-2 col-md-12 col-sm-12">
                <div class="accordion-box">
                    <div class="accordion block">
                        <div class="acc-btn active">
                            <div class="icon-outer"><span class="icon icon-plus fa fa-plus"></span> <span class="icon icon-minus fa fa-minus"></span></div>
                            <h5>What is the difference between investing and staking?</h5>
                        </div>
                        <div class="acc-content current">
                            <div class="content">
                                <p>Investing involves placing your capital in various assets with the expectation of growth or income, while staking involves locking up your digital assets to support network operations in exchange for rewards. Both offer ways to grow your capital, but staking typically offers more predictable returns.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion block">
                        <div class="acc-btn">
                            <div class="icon-outer"><span class="icon icon-plus fa fa-plus"></span> <span class="icon icon-minus fa fa-minus"></span></div>
                            <h5>How are returns calculated and distributed?</h5>
                        </div>
                        <div class="acc-content">
                            <div class="content">
                                <p>Returns are calculated based on the percentage rate of the plan you choose and the amount you invest or stake. Investment returns are typically distributed at the end of the investment period, while staking rewards may be distributed daily, can be compounded, or claimed at your convenience.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion block">
                        <div class="acc-btn">
                            <div class="icon-outer"><span class="icon icon-plus fa fa-plus"></span> <span class="icon icon-minus fa fa-minus"></span></div>
                            <h5>Is there a minimum investment or staking amount?</h5>
                        </div>
                        <div class="acc-content">
                            <div class="content">
                                <p>Yes, each plan has a minimum requirement. Investment plans start from as low as $100, while staking plans begin at $50. The minimum amount varies based on the plan's risk level and potential returns.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion block">
                        <div class="acc-btn">
                            <div class="icon-outer"><span class="icon icon-plus fa fa-plus"></span> <span class="icon icon-minus fa fa-minus"></span></div>
                            <h5>Can I withdraw my investment before the term ends?</h5>
                        </div>
                        <div class="acc-content">
                            <div class="content">
                                <p>Investment plans typically run for their full duration. For staking plans, some offer flexibility with early unstaking options, though this may incur a penalty as specified in the plan details. Flexible staking plans allow withdrawal at any time, while others have a lock period.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End FAQ Section -->

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/layout/footer.php'; ?> 