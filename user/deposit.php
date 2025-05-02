<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
$page_name = 'Deposit';
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the form data
    $payment_method = mysqli_real_escape_string($conn_back, $_POST['payment_method']);
    $deposit_amount = mysqli_real_escape_string($conn_back, $_POST['deposit_amount']);
    $transaction_id = mysqli_real_escape_string($conn_back, $_POST['transactionId']);
    $wallet_address = mysqli_real_escape_string($conn_back, $_POST['wallet_address']);

    // Handle file upload (proof of payment)
    $payment_proof = '';
    if (isset($_FILES['paymentProof']) && $_FILES['paymentProof']['error'] == 0) {
        $payment_proof = '../payment_proof/' . basename($_FILES['paymentProof']['name']);
        move_uploaded_file($_FILES['paymentProof']['tmp_name'], $payment_proof);
    }

    // Insert into the deposit_requests table
    $insert_deposit_query = "INSERT INTO deposit_requests (user_id, payment_method, amount, currency, reference_id, transaction_proof_id, payment_proof, status) 
                             VALUES ('$user_id', '$payment_method', '$deposit_amount', 'USD', '$transaction_id', '$payment_proof', '$payment_proof', 'pending')";

    if (mysqli_query($conn_back, $insert_deposit_query)) {
        // Get the last insert ID from deposit_requests table to link in transactions table
        $deposit_request_id = mysqli_insert_id($conn_back);

        // Insert into transactions table
        $insert_transaction_query = "INSERT INTO transactions (transaction_id, transaction_type, reference_id, transaction_proof_id, amount, currency, status, date_time, from_address, to_address, user_id, deposit_request_id) 
                                      VALUES ('$transaction_id', 'deposit', '$transaction_id', '$payment_proof', '$deposit_amount', 'USD', 'pending', NOW(), NULL, '$wallet_address', '$user_id', '$deposit_request_id')";

        if (mysqli_query($conn_back, $insert_transaction_query)) {
            echo "Deposit submitted successfully!";
        } else {
            echo "Error inserting transaction: " . mysqli_error($conn_back);
        }
    } else {
        echo "Error inserting deposit request: " . mysqli_error($conn_back);
    }
}
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/header.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/breadcumb.php';

?>

    <form id="depositForm" action="" method="post" enctype="multipart/form-data">
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
                            <div class="col-12 col-md-6 col-lg-6 col-xl-6 mb-4">
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
                            <div class="col-12 col-md-6 col-lg-6 col-xl-6 mb-4">
                                <div class="card text-center bg-theme-1-subtle theme-green h-100 selectable">
                                    <div class="card-body">
                                        <label for="exampleFormControlInput1" class="form-label">Enter Deposit Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><?=$user_currency?></span>
                                            <input id="deposit_amount_input" name="deposit_amount" type="number" class="form-control" aria-label="Amount (to the nearest dollar)">
                                            <span class="input-group-text">.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="step-2" class="tab-pane px-0 pb-0" role="tabpanel" aria-labelledby="step-2">
                        <div class="row my-2">
                            <div class="col-12 col-md-12 col-lg-12 col-xl-12 mb-4">
                                <div class="card h-100 bg-theme-1-subtle theme-green selectable anyone">
                                    <div class="card-body">
                                        <div class="list-group">
                                            <div class="alert alert-warning" role="alert">Use the wallet address below to make payment</div>
                                            <div class="row">
                                                <div class="col-12 col-md-3">
                                                    <span class="list-group-item list-group-item-action list-group-item-light">Wallet Address</span>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <input type="text" id="wallet_address" class="form-control" readonly>
                                                </div>
                                                <div class="col-12 col-md-3">
                                                    <span id="walletType" class="list-group-item list-group-item-action list-group-item-light">Wallet Type</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-6 col-xl-6 mb-4">
                                <div class="card h-100 selectable anyone">
                                    <div class="card-body">
                                        <div class="list-group">
                                            <div class="row">
                                                <div class="col-12 col-md-5">
                                                    <span class="list-group-item list-group-item-action list-group-item-light">Deposit Amount</span>
                                                </div>
                                                <div class="col-12 col-md-7">
                                                    <div class="input-group">
                                                        <span class="input-group-text"><?=$user_currency?></span>
                                                        <input id="deposit_amount"  type="number" class="form-control" aria-label="Amount (to the nearest dollar)" readonly>
                                                        <span class="input-group-text">.00</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="alert alert-warning" role="alert">make exactly the amount above to the address provided</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-6 col-xl-6 mb-4">
                                <div class="card h-100 selectable anyone">
                                    <div class="card-body">
                                        <div class="list-group">
                                            <div class="row">
                                                <div class="col-12 col-md-6">
                                                    <div class="mb-3">
                                                        <label for="exampleFormControlInput1" class="form-label">Enter Transaction ID/Reference</label>
                                                        <input type="text" class="form-control" name="transactionId" id="exampleFormControlInput1" placeholder="ID/REF1235">
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <div class="mb-3">
                                                        <label for="formFile" class="form-label">Upload Proof of Payment</label>
                                                        <input class="form-control" type="file" name="paymentProof" id="formFile">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="step-3" class="tab-pane px-0 pb-0" role="tabpanel" aria-labelledby="step-3">
                        <div class="row my-2">

                            <!-- summary -->
                            <div class="col-12 col-md-6 mb-4">
                                <div class="card bg-theme-1-subtle theme-green h-100 selectable">
                                    <div class="card-body">
                                        <h5 class="mb-4">Confirm Deposit Details</h5>
                                        <ul class="list-group">
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Payment&nbsp;Method</span><span id="c_pm"></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Amount</span><span id="c_amt"></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Wallet&nbsp;Address</span><span id="c_addr" class="text-break"></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Tx&nbsp;ID/Ref</span><span id="c_tx"></span>
                                            </li>
                                        </ul>

                                        <!-- hidden fields posted to PHP -->
                                        <input type="hidden" name="payment_method"  id="h_pm">
                                        <input type="hidden" name="deposit_amount"  id="h_amt">
                                        <input type="hidden" name="wallet_address"  id="h_addr">
<!--                                        <input type="hidden" name="tx_id"           id="h_tx">-->
                                    </div>
                                </div>
                            </div>

                            <!-- proof + submit -->
                            <div class="col-12 col-md-6 mb-4">
                                <div class="card h-100 selectable">
                                    <div class="card-body text-center">
                                        <h5 class="mb-4">Proof of Payment</h5>
                                        <img id="proof_preview" class="img-fluid border mb-3" style="max-height:240px" alt="">
                                        <button id="submitDepositBtn" type="submit" class="btn btn-theme-1 w-100 finish-btn" style="background-color: #0049e8;color: #fff;">Submit Deposit</button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- /STEP-3 -->
                </div>
            </div>
            <div class="progress bg-theme-1-subtle rounded-0">
                <div class="progress-bar bg-theme-1 h-100 rounded-0" role="progressbar" style="width: 0%"
                     aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>
    </form>

    <!-- ───────────────  SCRIPT  ─────────────── -->
    <script>
/* ====== CONSTANT MAPS (step-1 / step-2) ====== */
const walletAddresses = {
  USDT: "TXX123USDTWalletExample",
  BTC : "1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa",
  ETH : "0x1234567890abcdef1234567890abcdef12345678"
};
const walletTypes = {
  USDT: "USDT TRC-20",
  BTC : "Bitcoin",
  ETH : "Ethereum"
};

/* ====== SYNC FIELDS BETWEEN STEP-1 & STEP-2 ====== */
document.querySelector('select[name="payment_method"]').addEventListener('change', e => {
  const v = e.target.value;
  document.getElementById('wallet_address').value   = walletAddresses[v] || '';
  document.getElementById('walletType').textContent = walletTypes[v]   || '';
});

document.getElementById('deposit_amount_input').addEventListener('input', e => {
  document.getElementById('deposit_amount').value = e.target.value;
});

/* ====== PREVIEW PAYMENT PROOF ====== */
document.querySelector('[name="paymentProof"]').addEventListener('change', e => {
  const f = e.target.files[0];
  if (f) document.getElementById('proof_preview').src = URL.createObjectURL(f);
});

/* ====== COPY DATA INTO STEP-3 (CONFIRMATION) ====== */
function fillConfirm() {
  const pm   = document.querySelector('select[name="payment_method"]').value.trim();
  const amt  = document.getElementById('deposit_amount_input').value.trim();
  const addr = document.getElementById('wallet_address').value.trim();
  const tx   = document.querySelector('[name="transactionId"]').value.trim();

  /* visible text */
  document.getElementById('c_pm').textContent   = pm   || '—';
  document.getElementById('c_amt').textContent  = amt  || '—';
  document.getElementById('c_addr').textContent = addr || '—';
  document.getElementById('c_tx').textContent   = tx   || '—';

  /* hidden inputs (sent to PHP) */
  document.getElementById('h_pm').value   = pm;
  document.getElementById('h_amt').value  = amt;
  document.getElementById('h_addr').value = addr;
  document.getElementById('h_tx').value   = tx;
}

/* ====== TRIGGER fillConfirm WHEN STEP-3 OPENS ====== */
document.addEventListener('DOMContentLoaded', () => {

  /* 1) SmartWizard users — ‘showStep’ fires AFTER pane is visible */
  if (window.$ && $('#smartwizard').length) {
    $('#smartwizard').on('showStep', (e, anchorObj, stepNumber) => {
      if (stepNumber === 2) fillConfirm();      // 0-based index → step-3
    });
  }

  /* 2) Plain Bootstrap-tab / anchor navigation fallback */
  const link = document.querySelector('a[href="#step-3"]');
  if (link) link.addEventListener('click', () => setTimeout(fillConfirm, 10));
});
</script>


<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/footer.php';
?>