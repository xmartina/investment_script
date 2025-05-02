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


    <form id="depositForm" action="deposit_submit.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="currency" value="<?php echo htmlspecialchars($user_currency); ?>">
        <div class="container mt-4" id="main-content">
            <div class="card overflow-hidden mb-4" id="smartwizard">
                <ul class="nav">
                    <li class="nav-item"><a class="nav-link" href="#step-1"><div class="num">1</div><div><p class="h5 mb-0">Deposit Setup</p><p class="small">Deposit Information</p></div></a></li>
                    <li class="nav-item"><a class="nav-link" href="#step-2"><div class="num">2</div><div><p class="h5 mb-0">Payment Instructions</p><p class="small">Make Payment</p></div></a></li>
                    <li class="nav-item"><a class="nav-link" href="#step-3"><div class="num">3</div><div><p class="h5 mb-0">Confirmation</p><p class="small">Confirm Deposit</p></div></a></li>
                </ul>
                <div class="card-body pb-0">
                    <div class="tab-content">

                        <!-- STEP 1 -->
                        <div id="step-1" class="tab-pane px-0" role="tabpanel">
                            <div class="row my-2">
                                <div class="col-md-6 mb-4">
                                    <div class="card text-center bg-light h-100 selectable">
                                        <div class="card-body">
                                            <label class="form-label">Select Payment Method</label>
                                            <select name="payment_method" class="form-select">
                                                <option selected>Select</option>
                                                <option value="USDT">USDT</option>
                                                <option value="BTC">BTC</option>
                                                <option value="ETH">ETH</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="card text-center bg-light h-100 selectable">
                                        <div class="card-body">
                                            <label class="form-label">Enter Deposit Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><?php echo htmlspecialchars($user_currency); ?></span>
                                                <input id="deposit_amount_input" name="deposit_amount" type="number" class="form-control">
                                                <span class="input-group-text">.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- STEP 2 -->
                        <div id="step-2" class="tab-pane px-0 pb-0" role="tabpanel">
                            <div class="row my-2">
                                <div class="col-12 mb-4">
                                    <div class="card bg-light h-100 selectable">
                                        <div class="card-body">
                                            <div class="alert alert-warning">Use the wallet address below to make payment</div>
                                            <div class="row">
                                                <div class="col-md-3"><span class="list-group-item-light">Wallet Address</span></div>
                                                <div class="col-md-6"><input type="text" id="wallet_address" name="wallet_address" class="form-control" readonly></div>
                                                <div class="col-md-3"><span id="walletType" class="list-group-item-light">Wallet Type</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 selectable">
                                        <div class="card-body">
                                            <div class="list-group">
                                                <div class="row">
                                                    <div class="col-md-5"><span class="list-group-item-light">Deposit Amount</span></div>
                                                    <div class="col-md-7">
                                                        <div class="input-group">
                                                            <span class="input-group-text"><?php echo htmlspecialchars($user_currency); ?></span>
                                                            <input id="deposit_amount" class="form-control" readonly>
                                                            <span class="input-group-text">.00</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="alert alert-warning mt-2">Make exactly the amount above to the address provided</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 selectable">
                                        <div class="card-body">
                                            <label class="form-label">Enter Transaction ID/Reference</label>
                                            <input type="text" class="form-control" name="transactionId" placeholder="ID/REF1235">
                                            <label class="form-label mt-3">Upload Proof of Payment</label>
                                            <input class="form-control" type="file" name="paymentProof">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- STEP 3 -->
                        <div id="step-3" class="tab-pane px-0 pb-0" role="tabpanel">
                            <div class="row my-2">
                                <div class="col-md-6 mb-4">
                                    <div class="card text-center">
                                        <div class="card-header"><h5>Payment Method</h5></div>
                                        <div class="card-body"><p class="fw-bold" id="confirm_payment_method"></p></div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="card text-center">
                                        <div class="card-header"><h5>Deposit Amount</h5></div>
                                        <div class="card-body"><p class="fw-bold"><?php echo htmlspecialchars($user_currency); ?> <span id="confirm_deposit_amount"></span></p></div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="card text-center">
                                        <div class="card-header"><h5>Transaction Reference</h5></div>
                                        <div class="card-body"><p class="fw-bold" id="confirm_reference_id"></p></div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="card text-center">
                                        <div class="card-header"><h5>Proof of Payment</h5></div>
                                        <div class="card-body"><p class="fw-bold" id="confirm_proof_filename"></p></div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-success">Confirm Deposit</button>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="progress bg-light rounded-0">
                    <div class="progress-bar bg-success h-100" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </form>

    <script>
$(function(){
  $('#smartwizard').smartWizard();
  const walletAddresses = {
    USDT: "TXX123USDTWalletExample",
    BTC: "1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa",
    ETH: "0x1234567890abcdef1234567890abcdef12345678"
  };
  const walletTypes = {
    USDT: "USDT TRC20",
    BTC: "Bitcoin",
    ETH: "Ethereum"
  };
  $('select[name="payment_method"]').on('change', function(){
    const m = this.value;
    $('#wallet_address').val(walletAddresses[m]||"");
    $('#walletType').text(walletTypes[m]||"");
  });
  $('#deposit_amount_input').on('input', function(){
    $('#deposit_amount').val(this.value);
  });
  $("#smartwizard").on("showStep", function(e, anchorObject, stepIndex){
    if(stepIndex===2){
      $('#confirm_payment_method').text($('select[name="payment_method"]').val());
      $('#confirm_deposit_amount').text($('#deposit_amount_input').val()+".00");
      $('#confirm_reference_id').text($('input[name="transactionId"]').val());
      const f = $('input[name="paymentProof"]')[0].files[0];
      $('#confirm_proof_filename').text(f?f.name:"No file selected");
    }
  });
});
</script>


<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/footer.php';
?>