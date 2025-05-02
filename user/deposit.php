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

if ($_SERVER['REQUEST_METHOD']==='POST') {

    $pm      = mysqli_real_escape_string($conn_back,$_POST['payment_method']??'');
    $amount  = floatval($_POST['deposit_amount']??0);
    $addr    = mysqli_real_escape_string($conn_back,$_POST['wallet_address']??'');
    $txid    = mysqli_real_escape_string($conn_back,$_POST['tx_id']??'');

    /* upload */
    $proof_path='';
    if(!empty($_FILES['paymentProof']['name'])){
        $dir='uploads/proofs/';
        if(!is_dir($dir)) mkdir($dir,0777,true);
        $name=uniqid('proof_').basename($_FILES['paymentProof']['name']);
        move_uploaded_file($_FILES['paymentProof']['tmp_name'],$dir.$name);
        $proof_path=$dir.$name;
    }

    /* insert deposit_request */
    mysqli_query($conn_back,"INSERT INTO deposit_request
        (user_id,payment_method,amount,currency,wallet_address,tx_id,proof_file,status,date_time)
        VALUES
        ($user_id,'$pm',$amount,'$pm','$addr','$txid','$proof_path','pending',NOW())");

    if(mysqli_affected_rows($conn_back)){
        $trx_id=uniqid('trx_');
        $now=date('Y-m-d H:i:s');
        mysqli_query($conn_back,"INSERT INTO transactions
            (transaction_id,transaction_type,reference_id,amount,currency,status,date_time,description,from_address,to_address,fee,user_id)
            VALUES
            ('$trx_id','deposit','$txid',$amount,'$pm','pending','$now',
             'User deposit via $pm','$addr','Platform Wallet',0,$user_id)");
        header('Location: success.php?msg=deposit_pending'); exit;
    }
    header('Location: error.php?msg=deposit_failed'); exit;
}
?>


    <form id="depositForm" action="" method="post" enctype="multipart/form-data">
        <div class="container mt-4" id="main-content">
            <div class="card adminuiux-card overflow-hidden mb-4" id="smartwizard">
                <ul class="nav">
                    <li class="nav-item"><a class="nav-link" href="#step-1"><div class="num">1</div><div><p class="h5 mb-0">Deposit Setup</p><p class="small">Deposit Information</p></div></a></li>
                    <li class="nav-item"><a class="nav-link" href="#step-2"><div class="num">2</div><div><p class="h5 mb-0">Payment Instructions</p><p class="small">Make Payment</p></div></a></li>
                    <li class="nav-item"><a class="nav-link" href="#step-3"><div class="num">3</div><div><p class="h5 mb-0">Confirmation</p><p class="small">Confirm Deposit</p></div></a></li>
                </ul><div class="card-body pb-0"><div class="tab-content">

                        <!-- STEP 1 -->
                        <div id="step-1" class="tab-pane px-0" role="tabpanel">
                            <div class="row my-2">
                                <div class="col-12 col-md-6 mb-4">
                                    <div class="card text-center bg-theme-1-subtle theme-green h-100 selectable">
                                        <div class="card-body">
                                            <label class="form-label">Select Payment Method</label>
                                            <select name="payment_method" class="form-select">
                                                <option selected>Select</option><option value="USDT">USDT</option><option value="BTC">BTC</option><option value="ETH">ETH</option>
                                            </select>
                                        </div></div></div>
                                <div class="col-12 col-md-6 mb-4">
                                    <div class="card text-center bg-theme-1-subtle theme-green h-100 selectable">
                                        <div class="card-body">
                                            <label class="form-label">Enter Deposit Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><?=$user_currency?></span>
                                                <input id="deposit_amount_input" name="deposit_amount" type="number" class="form-control">
                                                <span class="input-group-text">.00</span>
                                            </div></div></div></div></div></div>

                        <!-- STEP 2 -->
                        <div id="step-2" class="tab-pane px-0 pb-0" role="tabpanel">
                            <div class="row my-2">
                                <div class="col-12 mb-4">
                                    <div class="card h-100 bg-theme-1-subtle theme-green selectable">
                                        <div class="card-body">
                                            <div class="alert alert-warning">Use the wallet address below to make payment</div>
                                            <div class="row">
                                                <div class="col-md-3"><span class="list-group-item">Wallet Address</span></div>
                                                <div class="col-md-6"><input type="text" id="wallet_address" class="form-control" readonly></div>
                                                <div class="col-md-3"><span id="walletType" class="list-group-item">Wallet Type</span></div>
                                            </div></div></div></div>

                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 selectable"><div class="card-body">
                                            <label class="form-label">Deposit Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><?=$user_currency?></span>
                                                <input id="deposit_amount" type="number" class="form-control" readonly>
                                                <span class="input-group-text">.00</span>
                                            </div><div class="alert alert-warning mt-3">Make exactly the amount above to the address provided</div>
                                        </div></div></div>

                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 selectable"><div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Enter Transaction ID/Reference</label>
                                                <input type="text" class="form-control" name="transactionId" placeholder="ID/REF1235">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Upload Proof of Payment</label>
                                                <input class="form-control" type="file" name="paymentProof">
                                            </div>
                                        </div></div></div>
                            </div></div>

                        <!-- STEP 3 -->
                        <div id="step-3" class="tab-pane px-0 pb-0" role="tabpanel">
                            <div class="row my-2">
                                <div class="col-md-6 mb-4">
                                    <div class="card bg-theme-1-subtle theme-green h-100">
                                        <div class="card-body">
                                            <h5 class="mb-4">Confirm Deposit Details</h5>
                                            <ul class="list-group">
                                                <li class="list-group-item"><strong>Payment Method:</strong> <span id="confirm_payment_method"></span>
                                                    <input type="hidden" name="payment_method" id="hidden_payment_method"></li>
                                                <li class="list-group-item"><strong>Deposit Amount:</strong> <span id="confirm_deposit_amount"></span>
                                                    <input type="hidden" name="deposit_amount" id="hidden_deposit_amount"></li>
                                                <li class="list-group-item"><strong>Wallet Address:</strong> <span id="confirm_wallet_address"></span>
                                                    <input type="hidden" name="wallet_address" id="hidden_wallet_address"></li>
                                                <li class="list-group-item"><strong>Tx ID/Ref:</strong> <span id="confirm_tx_id"></span>
                                                    <input type="hidden" name="tx_id" id="hidden_tx_id"></li>
                                            </ul>
                                        </div></div></div>

                                <div class="col-md-6 mb-4">
                                    <div class="card h-100"><div class="card-body text-center">
                                            <h5 class="mb-4">Proof of Payment</h5>
                                            <img id="proof_preview" class="img-fluid border mb-3" style="max-height:240px">
                                            <button type="submit" class="btn btn-primary w-100">Submit Deposit</button>
                                        </div></div></div>
                            </div></div>

                    </div></div><div class="progress rounded-0"><div class="progress-bar bg-theme-1 h-100" style="width:0%"></div></div>
            </div></div></form>

    <script>
const walletAddresses={USDT:"TXX123USDTWalletExample",BTC:"1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa",ETH:"0x1234567890abcdef1234567890abcdef12345678"};
const walletTypes={USDT:"USDT TRC20",BTC:"Bitcoin",ETH:"Ethereum"};

document.querySelector('select[name="payment_method"]').addEventListener('change',e=>{
    const v=e.target.value;
    document.getElementById('wallet_address').value=walletAddresses[v]||"";
    document.getElementById('walletType').textContent=walletTypes[v]||"";
});

document.getElementById('deposit_amount_input').addEventListener('input',e=>{
    document.getElementById('deposit_amount').value=e.target.value;
});

function syncConfirm(){
    const pm=document.querySelector('select[name="payment_method"]').value;
    const amt=document.getElementById('deposit_amount_input').value;
    const addr=document.getElementById('wallet_address').value;
    const tx=document.querySelector('[name="transactionId"]').value;

    document.getElementById('confirm_payment_method').textContent=pm;
    document.getElementById('confirm_deposit_amount').textContent=amt;
    document.getElementById('confirm_wallet_address').textContent=addr;
    document.getElementById('confirm_tx_id').textContent=tx;

    document.getElementById('hidden_payment_method').value=pm;
    document.getElementById('hidden_deposit_amount').value=amt;
    document.getElementById('hidden_wallet_address').value=addr;
    document.getElementById('hidden_tx_id').value=tx;
}

document.querySelector('[name="paymentProof"]').addEventListener('change',e=>{
    const f=e.target.files[0];
    if(f) document.getElementById('proof_preview').src=URL.createObjectURL(f);
});

/* Simple step switcher (no plugin) */
const navLinks=document.querySelectorAll('.nav-link');
const panes=document.querySelectorAll('.tab-pane');
navLinks.forEach(link=>link.addEventListener('click',e=>{
    e.preventDefault();
    navLinks.forEach(l=>l.classList.remove('active'));
    panes.forEach(p=>p.classList.remove('active','show'));
    link.classList.add('active');
    document.querySelector(link.getAttribute('href')).classList.add('active','show');
    if(link.getAttribute('href')==='#step-3') syncConfirm();
}));
navLinks[0].click(); // init
</script>


<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/footer.php';
?>