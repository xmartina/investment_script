<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
$page_name = 'Deposit';
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/header.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/breadcumb.php';
?>


    <div class="container mt-4" id="main-content">
        <div class="card adminuiux-card overflow-hidden mb-4" id="smartwizard">
            <ul class="nav">
                <li class="nav-item"><a class="nav-link" href="#step-1">
                        <div class="num">1</div>
                        <div><p class="h5 mb-0">Deposit Setup</p>
                            <p class="small">Deposit Information</p></div>
                    </a></li>
                <li class="nav-item"><a class="nav-link" href="#step-2">
                        <div class="num">2</div>
                        <div><p class="h5 mb-0">Payment Instructions</p>
                            <p class="small">Make Payment</p></div>
                    </a></li>
                <li class="nav-item"><a class="nav-link" href="#step-3">
                        <div class="num">3</div>
                        <div><p class="h5 mb-0">Confirmation</p>
                            <p class="small">Confirm Deposit</p></div>
                    </a></li>
            </ul>
            <div class="card-body pb-0">
                <div class="tab-content">
                    <div id="step-1" class="tab-pane px-0" role="tabpanel" aria-labelledby="step-1">
                        <div class="row my-2">
                            <div class="col-6 col-md-4 col-lg-4 col-xl-4 mb-4">
                                <div class="card text-center bg-theme-1-subtle theme-green h-100 selectable">
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="exampleFormControlInput1" class="form-label">Select Payment Method</label>
                                            <select name="payment_method" class="form-select" aria-label="Select Payment Method">
                                                <option selected>Select</option>
                                                <option value="USDT">USDT</option>
                                                <option value="BTC">BTC</option>
                                                <option value="ETH">ETH</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-lg-4 col-xl-4 mb-4">
                                <div class="card text-center bg-theme-1-subtle theme-green h-100 selectable">
                                    <div class="card-body">
                                        <div class="input-group mb-3">
                                            <label for="exampleFormControlInput1" class="form-label">Enter Deposit Amount</label>
                                            <span class="input-group-text"><?=$user_currency?></span>
                                            <input name="deposit_amount" type="text" class="form-control" aria-label="Amount (to the nearest dollar)">
                                            <span class="input-group-text">.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="step-2" class="tab-pane px-0 pb-0" role="tabpanel" aria-labelledby="step-2">
                        <div class="row my-2">
                            <div class="col-6 col-md-3 col-lg-3 col-xl-3 mb-4">
                                <div class="card h-100 bg-theme-1-subtle theme-blue selectable anyone">
                                    <div class="card-body">
                                        <div class="avatar avatar-60 rounded bg-theme-1 mb-3"><i
                                                class="bi bi-house h4"></i></div>
                                        <h6 class="text-theme-1">Home</h6>
                                        <p class="opacity-50">I want to buy a own home</p></div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-lg-3 col-xl-3 mb-4">
                                <div class="card h-100 bg-theme-1-subtle theme-purple selectable anyone">
                                    <div class="card-body">
                                        <div class="avatar avatar-60 rounded bg-theme-1 mb-3"><i
                                                class="bi bi-people h4"></i></div>
                                        <h6 class="text-theme-1">Family</h6>
                                        <p class="opacity-50">I need regular saving for family</p></div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-lg-3 col-xl-3 mb-4">
                                <div class="card h-100 bg-theme-1-subtle theme-red selectable anyone">
                                    <div class="card-body">
                                        <div class="avatar avatar-60 rounded bg-theme-1 mb-3"><i
                                                class="bi bi-mortarboard h4"></i></div>
                                        <h6 class="text-theme-1">Education</h6>
                                        <p class="opacity-50">Creating planning for kids</p></div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-lg-3 col-xl-3 mb-4">
                                <div class="card h-100 bg-theme-1-subtle theme-orange selectable anyone">
                                    <div class="card-body">
                                        <div class="avatar avatar-60 rounded bg-theme-1 mb-3"><i
                                                class="bi bi-luggage h4"></i></div>
                                        <h6 class="text-theme-1">Vacation</h6>
                                        <p class="opacity-50">Planning for trip</p></div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-lg-3 col-xl-3 mb-4">
                                <div class="card h-100 bg-theme-1-subtle theme-brown selectable anyone">
                                    <div class="card-body">
                                        <div class="avatar avatar-60 rounded bg-theme-1 mb-3"><i
                                                class="bi bi-person-walking h4"></i></div>
                                        <h6 class="text-theme-1">Retirement</h6>
                                        <p class="opacity-50">Planning for better future</p></div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-lg-3 col-xl-3 mb-4">
                                <div class="card h-100 bg-theme-1-subtle theme-teal selectable anyone">
                                    <div class="card-body">
                                        <div class="avatar avatar-60 rounded bg-theme-1 mb-3"><i
                                                class="bi bi-puzzle h4"></i></div>
                                        <h6 class="text-theme-1">Other</h6>
                                        <p class="opacity-50">I have independent self plan</p></div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3 col-lg-3 col-xl-3 mb-4">
                                <div class="card h-100">
                                    <div class="card-body z-index-1"><h4 class="fw-normal mb-3">What is your goal
                                            name?</h4>
                                        <div class="form-floating text-dark"><input class="form-control" id="name"
                                                                                    placeholder="Enter name"> <label
                                                for="name">Name</label></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3 col-lg-3 col-xl-3 mb-4">
                                <div class="card adminuiux-card position-relative overflow-hidden bg-theme-1 h-100">
                                    <div class="position-absolute top-0 start-0 h-100 w-100 z-index-0 coverimg opacity-50">
                                        <img src="assets/img/modern-ai-image/flamingo-4.jpg" alt=""></div>
                                    <div class="card-body z-index-1"><h4 class="fw-normal mb-3">How much amount need
                                            for your goal?</h4>
                                        <div class="form-floating text-dark"><input type="number"
                                                                                    class="form-control" id="amount"
                                                                                    placeholder="Enter amount">
                                            <label for="amount">Amount</label></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="step-3" class="tab-pane px-0 pb-0" role="tabpanel" aria-labelledby="step-3">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="card text-center mb-4">
                                    <div class="card-header"><h5>Goal: Sweet Home</h5>
                                        <p class="text-secondary">Choose your investment plan</p></div>
                                    <div class="card-body"><h4>$ 22500.00</h4>
                                        <p class="text-secondary mb-4">Targeted Goal Amount</p>
                                        <div class="card adminuiux-card bg-theme-1-subtle theme-green">
                                            <div class="card-body"><h4>$ 750.00</h4>
                                                <p class="opacity-75">You will need to save per month</p></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="card adminuiux-card mb-4">
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <div class="row align-items-center">
                                                <div class="col-12 col-sm mb-3"><p>Targeted Amount</p></div>
                                                <div class="col-12 col-sm-5 mb-3">
                                                    <div class="input-group"><span class="input-group-text bg-none">$</span>
                                                        <input class="form-control text-end rangevalues"
                                                               value="22500.00" id="value1"></div>
                                                </div>
                                            </div>
                                            <input type="range" id="range1" class="range1 rangevalue" min="100"
                                                   max="150000" value="22500.00" data-value="value1"></div>
                                        <div class="mb-3">
                                            <div class="row align-items-center">
                                                <div class="col-12 col-sm mb-3"><p>Expected goal duration</p></div>
                                                <div class="col-12 col-sm-5 mb-3">
                                                    <div class="input-group"><span class="input-group-text bg-none">Months</span>
                                                        <input class="form-control text-end rangevalues" value="24"
                                                               id="value2"></div>
                                                </div>
                                            </div>
                                            <input type="range" id="range2" class="range1 rangevalue" min="1"
                                                   max="60" step="1" value="24" data-value="value2"></div>
                                        <div>
                                            <div class="row align-items-center">
                                                <div class="col-12 col-sm mb-3"><p>Time period in Year</p></div>
                                                <div class="col-12 col-sm-5 mb-3">
                                                    <div class="input-group"><span class="input-group-text bg-none">$</span>
                                                        <input class="form-control text-end rangevalues"
                                                               value="1000" id="value3"></div>
                                                </div>
                                            </div>
                                            <input type="range" id="range3" class="range1 rangevalue" min="500"
                                                   max="20000" step="500" value="1000" data-value="value3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br><h5 class="text-center">Choose funds for your plan from suggestion</h5>
                        <p class="text-secondary text-center mb-4">You can select any one or multiple and in each
                            total amount should be of $ 750.00/month.</p>
                        <div class="row">
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card selectable active mb-4">
                                    <div class="card-body"><h5 class="fw-medium mb-1">JACKY New age EV and
                                            automotive Fund</h5>
                                        <p class="text-secondary mb-4">Direct <i class="bi bi-chevron-right"></i>
                                            Growth <i class="bi bi-chevron-right"></i> Thematic</p>
                                        <div class="row align-items-center mb-4">
                                            <div class="col-6 text-start mb-3"><h6 class="fw-medium">$150.1250</h6>
                                                <p class="text-secondary small">Current NAV <span>10 Aug 2025</span>
                                                </p></div>
                                            <div class="col-6 text-end mb-3"><h6 class="fw-medium">$2426.50 cr</h6>
                                                <p class="text-secondary small">AUM</p></div>
                                            <div class="col-6 text-start"><h6 class="fw-medium text-success">
                                                    +32.5%</h6>
                                                <p class="text-secondary small">CAGR <span>5 Years</span></p></div>
                                            <div class="col-6 text-end"><h6 class="fw-medium">0.79%</h6>
                                                <p class="text-secondary small">Expanse Ratio</p></div>
                                        </div>
                                        <hr>
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <div class="input-group"><span class="input-group-text bg-none">Invest <b
                                                            class="mx-1">$</b></span> <input
                                                        class="form-control text-end rangevalues" value="375.00">
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <button class="btn btn-square btn-outline-theme theme-red"><i
                                                        class="bi bi-heart"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card selectable active mb-4">
                                    <div class="card-body"><h5 class="fw-medium mb-1">OrganicX Agriculture and
                                            innovation Fund</h5>
                                        <p class="text-secondary mb-4">Direct <i class="bi bi-chevron-right"></i>
                                            Growth <i class="bi bi-chevron-right"></i> FoF</p>
                                        <div class="row align-items-center mb-4">
                                            <div class="col-6 text-start mb-3"><h6 class="fw-medium">$205.6530</h6>
                                                <p class="text-secondary small">Current NAV <span>10 Aug 2025</span>
                                                </p></div>
                                            <div class="col-6 text-end mb-3"><h6 class="fw-medium">$9586.50 cr</h6>
                                                <p class="text-secondary small">AUM</p></div>
                                            <div class="col-6 text-start"><h6 class="fw-medium text-success">
                                                    +15.5%</h6>
                                                <p class="text-secondary small">CAGR <span>5 Years</span></p></div>
                                            <div class="col-6 text-end"><h6 class="fw-medium">0.65%</h6>
                                                <p class="text-secondary small">Expanse Ratio</p></div>
                                        </div>
                                        <hr>
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <div class="input-group"><span class="input-group-text bg-none">Invest <b
                                                            class="mx-1">$</b></span> <input
                                                        class="form-control text-end rangevalues" value="375.00">
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <button class="btn btn-square btn-outline-theme theme-red"><i
                                                        class="bi bi-heart"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card selectable mb-4">
                                    <div class="card-body"><h5 class="fw-medium mb-1">Energy and New Smart
                                            Technology Fund</h5>
                                        <p class="text-secondary mb-4">Direct <i class="bi bi-chevron-right"></i>
                                            Growth <i class="bi bi-chevron-right"></i> ELSS</p>
                                        <div class="row align-items-center mb-4">
                                            <div class="col-6 text-start mb-3"><h6 class="fw-medium">$156.1250</h6>
                                                <p class="text-secondary small">Current NAV <span>10 Aug 2025</span>
                                                </p></div>
                                            <div class="col-6 text-end mb-3"><h6 class="fw-medium">$3265.50 cr</h6>
                                                <p class="text-secondary small">AUM</p></div>
                                            <div class="col-6 text-start"><h6 class="fw-medium text-success">
                                                    +25.5%</h6>
                                                <p class="text-secondary small">CAGR <span>5 Years</span></p></div>
                                            <div class="col-6 text-end"><h6 class="fw-medium">0.65%</h6>
                                                <p class="text-secondary small">Expanse Ratio</p></div>
                                        </div>
                                        <hr>
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <div class="input-group"><span class="input-group-text bg-none">Invest <b
                                                            class="mx-1">$</b></span> <input
                                                        class="form-control text-end rangevalues" value="00.00">
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <button class="btn btn-square btn-outline-theme theme-red"><i
                                                        class="bi bi-heart"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="progress bg-theme-1-subtle rounded-0">
                <div class="progress-bar bg-theme-1 h-100 rounded-0" role="progressbar" style="width: 0%"
                     aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>

<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/footer.php';
?>