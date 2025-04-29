<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
$page_name = 'Dashboard';
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/header.php';
?>

<div class="container mt-4" id="main-content">
    <div class="row align-items-center">
        <div class="col-12 col-lg mb-4">
            <h3 class="fw-normal mb-0 text-secondary">Good Morning,</h3>
            <h1><?= $get_user['fname'] ?></h1>
        </div>

        <div class="col-6 col-sm-4 col-lg-3 col-xl-2 mb-4">
            <div class="card adminuiux-card">
                <div class="card-body">
                    <p class="text-secondary small mb-2">Total Investment Profit</p>
                    <h4 class="mb-3"><?= $total_returns ?></h4>
                    <span class="badge badge-light text-bg-success">
                        <i class="me-1 bi bi-arrow-up-short"></i>
                        <span class="percent-value">28.50%</span>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-6 col-sm-4 col-lg-3 col-xl-2 mb-4">
            <div class="card adminuiux-card">
                <div class="card-body">
                    <p class="text-secondary small mb-2">Total Staked Profit</p>
                    <h4 class="mb-3"><?= $total_returns_stakes ?></h4>
                    <span class="badge badge-light text-bg-success">
                        <i class="me-1 bi bi-arrow-up-short"></i>
                        <span class="percent-value">54.35%</span>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-4 col-lg-3 col-xl-2 mb-4">
            <div class="card adminuiux-card">
                <div class="card-body">
                    <p class="text-secondary small mb-2">Top Profit</p>
                    <h4 class="mb-3"><?= $total_profit ?></h4>
                    <span class="badge badge-light text-bg-danger">
                        <i class="me-1 bi bi-arrow-down-short"></i>
                        <span class="percent-value">18.25%</span>
                    </span>
                </div>
            </div>
        </div>

        <script>
            function generateRandomPercentage(min = 10, max = 99) {
                return (Math.random() * (max - min) + min).toFixed(2) + '%';
            }

            document.addEventListener('DOMContentLoaded', () => {
                const percentEls = document.getElementsByClassName('percent-value');
                for (let i = 0; i < percentEls.length; i++) {
                    percentEls[i].textContent = generateRandomPercentage();
                }
            });
        </script>
    </div>

    <div class="row">
        <div class="col-12 col-lg-6 col-xl-4 mb-4">
            <div class="card adminuiux-card position-relative overflow-hidden bg-theme-1 h-100">
                <div class="position-absolute top-0 start-0 h-100 w-100 z-index-0 coverimg opacity-50">
                    <img src="<?= $site_link ?>/back_assets/img/modern-ai-image/flamingo-4.jpg" alt="">
                </div>
                <div class="card-body z-index-1">
                    <div class="row align-items-center justify-content-center h-100 py-4">
                        <div class="col-11">
                            <h2 class="fw-normal">Your portfolio value has been grown by</h2>
                            <h1 class="mb-3"><?= $user_currency . $total_returns ?></h1>
                            <p>In last 7 days</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6 col-xl-8 mb-4">
            <div class="card adminuiux-card">
                <div class="row gx-0">
                    <div class="col-12 col-xl-4">
                        <div class="card-header"><h6>Summary</h6></div>
                        <div class="card-body pb-0">
                            <div class="card adminuiux-card bg-theme-1 mb-3">
                                <div class="card-body">
                                    <p class="text-white mb-2">Total Investments</p>
                                    <h4 class="fw-medium"><?= $user_currency . $total_invested ?></h4>
                                </div>
                            </div>
                            <div class="card adminuiux-card bg-theme-1-subtle mb-3">
                                <div class="card-body">
                                    <p class="text-secondary mb-2">Total Stock Purchased</p>
                                    <h4 class="fw-medium">
                                        <?= $user_currency . $total_stakes_invested ?>
                                        <span class="text-success fs-14">
                                            <i class="bi bi-arrow-up-short me-1"></i>11.5%
                                        </span>
                                    </h4>
                                </div>
                            </div>
                            <div class="card adminuiux-card bg-theme-1-subtle mb-3">
                                <div class="card-body">
                                    <p class="text-secondary mb-2">Total Investments</p>
                                    <h4 class="fw-medium"><?= $user_currency . $total_investments ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-xl-8">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <nav aria-label="Page navigation example"></nav>
                                </div>
                            </div>
                        </div>

                        <div class="card-body px-1">
                            <div class="row mb-2">
                                <div class="col-6 col-md-4 col-lg-4 col-xl-4 mb-3">
                                    <a href="<?= $site_link ?>/user/deposit" class="card adminuiux-card style-none text-center h-100">
                                        <div class="card-body">
                                            <i class="avatar avatar-40 text-theme-1 h3 bi bi-bank mb-3"></i>
                                            <p class="text-secondary small">Make Deposit</p>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-6 col-md-4 col-lg-4 col-xl-4 mb-3">
                                    <a href="<?= $site_link ?>/user/staking" class="card adminuiux-card style-none text-center h-100">
                                        <div class="card-body">
                                            <i class="avatar avatar-40 text-theme-1 bi bi-calendar-event h3 mb-3"></i>
                                            <p class="text-secondary small">Stake Asset</p>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-6 col-md-4 col-lg-4 col-xl-4 mb-3">
                                    <a href="<?= $site_link ?>/user/investment" class="card adminuiux-card style-none text-center h-100">
                                        <div class="card-body">
                                            <i class="avatar avatar-40 text-theme-1 bi bi-percent h3 mb-3"></i>
                                            <p class="text-secondary small">Investments</p>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-6 col-md-6 col-lg-6 col-xl-6 mb-3">
                                    <a href="<?= $site_link ?>/user/transactions" class="card adminuiux-card style-none text-center h-100">
                                        <div class="card-body">
                                            <i class="avatar avatar-40 text-theme-1 bi bi-cash-stack h3 mb-3"></i>
                                            <p class="text-secondary small">View transactions</p>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-6 col-md-6 col-lg-6 col-xl-6 mb-3">
                                    <a href="<?= $site_link ?>/user/withdraw" class="card adminuiux-card style-none text-center h-100">
                                        <div class="card-body">
                                            <i class="avatar avatar-40 text-theme-1 bi bi-person-walking h3 mb-3"></i>
                                            <p class="text-secondary small">Withdraw Funds</p>
                                        </div>
                                    </a>
                                </div>
                            </div> <!-- row -->
                        </div> <!-- card-body -->
                    </div> <!-- col-xl-8 -->
                </div> <!-- row gx-0 -->
            </div> <!-- card -->
        </div> <!-- col-xl-8 -->

        <div class="col-12 mb-4">
            <div class="row align-items-center">
                <div class="col"><h6 class="mb-0">Updates:</h6>
                    <p class="small text-secondary">Today <span class="text-danger">Live</span></p>
                </div>
                <div class="col-12 col-sm-10">
                    <!-- TradingView Widget BEGIN -->
                    <div class="tradingview-widget-container">
                        <div class="tradingview-widget-container__widget"></div>
                        <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-ticker-tape.js" async>
                            {
                                "symbols": [
                                {
                                    "proName": "FOREXCOM:SPXUSD",
                                    "title": "S&P 500 Index"
                                },
                                {
                                    "proName": "FOREXCOM:NSXUSD",
                                    "title": "US 100 Cash CFD"
                                },
                                {
                                    "proName": "FX_IDC:EURUSD",
                                    "title": "EUR to USD"
                                },
                                {
                                    "proName": "BITSTAMP:BTCUSD",
                                    "title": "Bitcoin"
                                },
                                {
                                    "proName": "BITSTAMP:ETHUSD",
                                    "title": "Ethereum"
                                }
                            ],
                                "showSymbolLogo": true,
                                "isTransparent": false,
                                "displayMode": "adaptive",
                                "colorTheme": "dark",
                                "locale": "en"
                            }
                        </script>
                    </div>
                    <!-- TradingView Widget END -->
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-square btn-link"><i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card adminuiux-card mb-4">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col"><h6>Market with Technical Trend</h6></div>
                        <div class="col-auto px-0"><select class="form-select form-select-sm">
                                <option selected="selected">All Trend</option>
                                <option>Bullish</option>
                                <option>Bearish</option>
                            </select></div>
                        <div class="col-auto">
                            <button class="btn btn-sm btn-square btn-link"><i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php TransactionsCard() ?>
            </div>
        </div>

    </div> <!-- row -->
</div> <!-- container -->

<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/footer.php';
?>
