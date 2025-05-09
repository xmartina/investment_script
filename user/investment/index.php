<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
$page_name = 'Investment Plans';
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';

// Fetch investment plans from database
$query = "SELECT * FROM investment_plans WHERE is_active = 1";
$result = $conn_back->query($query);
$investment_plans = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $investment_plans[] = $row;
    }
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/header.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/breadcumb.php';

?>

<div class="container mt-4" id="main-content">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card adminuiux-card bg-theme-1-subtle">
                <div class="card-body pb-0">
                    <div class="row justify-content-center gy-4 gx-4">
                        <?php 
                        if (!empty($investment_plans)) {
                            foreach ($investment_plans as $plan) {
                        ?>
                        <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                            <div class="card adminuiux-card mb-3">
                                <div class="card-body">
                                    <p><span class="badge badge-light text-bg-theme-1"><?php echo $plan['name']; ?></span></p>
                                    <h5 class="fw-medium mb-1">ROI: <?php echo $plan['roi_percent']; ?>%</h5>
                                    <p class="text-secondary mb-4">Duration: <?php echo $plan['duration_days']; ?> days</p>
                                    <h6 class="fw-medium"><?=$user_currency?><?php echo number_format($plan['min_amount'], 2); ?></h6>
                                    <p class="text-secondary small mb-4">Min. Investment</p>
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <button class="btn btn-theme invest-btn" data-plan-id="<?php echo $plan['id']; ?>" 
                                                data-plan-name="<?php echo $plan['name']; ?>"
                                                data-min-amount="<?php echo $plan['min_amount']; ?>"
                                                data-max-amount="<?php echo $plan['max_amount']; ?>">Apply</button>
                                        </div>
                                        <div class="col-auto">
                                            <p class="text-success"><?php echo $plan['duration_days']; ?> days duration</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php 
                            }
                        } else {
                        ?>
                        <div class="col-12">
                            <div class="alert alert-info">No investment plans available at the moment.</div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Investment Modal -->
<div class="modal fade" id="investmentModal" tabindex="-1" aria-labelledby="investmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="investmentModalLabel">Invest in <span id="planName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="investmentForm" method="post" action="">
                <div class="modal-body">
                    <input type="hidden" name="plan_id" id="planId">
                    <div class="mb-3">
                        <label for="investmentAmount" class="form-label">Investment Amount</label>
                        <input type="number" class="form-control" id="investmentAmount" name="amount" min="" max="" required>
                        <small class="text-muted">Min: <?=$user_currency?><span id="minAmount"></span> | Max: <?=$user_currency?><span id="maxAmount"></span></small>
                    </div>
                    <div class="mb-3">
                        <label for="balanceSource" class="form-label">Select Balance Source</label>
                        <select class="form-select" id="balanceSource" name="balance_source" required>
                            <option value="main_balance">Main Balance (<?=$user_currency?><?=number_format($main_balance, 2)?>)</option>
                            <option value="investment_balance">Investment Balance (<?=$user_currency?><?=number_format($investment_balance, 2)?>)</option>
                            <option value="staking_balance">Staking Balance (<?=$user_currency?><?=number_format($staking_balance, 2)?>)</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <small>You will be investing from your selected balance. Make sure you have enough funds.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="invest" class="btn btn-primary">Invest Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Process Investment Request -->
<?php
if (isset($_POST['invest'])) {
    $plan_id = $_POST['plan_id'];
    $amount = (float)$_POST['amount'];
    $balance_source = $_POST['balance_source'];
    $user_id = $_SESSION['user_id'];
    
    // Get current user balances
    $user_query = "SELECT main_balance, investment_balance, staking_balance FROM users WHERE id = '$user_id'";
    $user_result = $conn_back->query($user_query);
    
    if (!$user_result) {
        error_log("User query failed: " . $conn_back->error);
        echo '<script>alert("Error retrieving user data. Please try again."); window.location.href="/user/investment";</script>';
        exit;
    }
    
    $user_data = $user_result->fetch_assoc();
    
    // Check if selected balance has enough funds
    $sufficient_funds = false;
    switch ($balance_source) {
        case 'main_balance':
            $sufficient_funds = (float)$user_data['main_balance'] >= $amount;
            $balance_field = 'main_balance';
            $balance_name = 'Main Balance';
            break;
        case 'investment_balance':
            $sufficient_funds = (float)$user_data['investment_balance'] >= $amount;
            $balance_field = 'investment_balance';
            $balance_name = 'Investment Balance';
            break;
        case 'staking_balance':
            $sufficient_funds = (float)$user_data['staking_balance'] >= $amount;
            $balance_field = 'staking_balance';
            $balance_name = 'Staking Balance';
            break;
    }
    
    if (!$sufficient_funds) {
        echo '<script>alert("Insufficient funds in your ' . $balance_name . '. Please add funds or select a different balance source."); window.location.href="/user/investment";</script>';
        exit;
    }
    
    // Get plan details for ROI calculation
    $plan_query = "SELECT * FROM investment_plans WHERE id = '$plan_id'";
    $plan_result = $conn_back->query($plan_query);
    
    if (!$plan_result) {
        error_log("Plan query failed: " . $conn_back->error);
        echo '<script>alert("Error retrieving plan data. Please try again."); window.location.href="/user/investment";</script>';
        exit;
    }
    
    if ($plan_result && $plan_result->num_rows > 0) {
        $plan = $plan_result->fetch_assoc();
        
        // Get the ROI value directly from the plan
        $roi_expected = (float)$plan['roi_percent'];
        
        $start_date = date('Y-m-d H:i:s');
        $end_date = date('Y-m-d H:i:s', strtotime($start_date . ' + ' . $plan['duration_days'] . ' days'));
        
        // Begin transaction
        $conn_back->begin_transaction();
        
        try {
            // Deduct amount from the selected balance
            $update_balance = "UPDATE users SET $balance_field = $balance_field - $amount WHERE id = '$user_id'";
            $update_result = $conn_back->query($update_balance);
            
            if (!$update_result) {
                throw new Exception("Failed to update user balance: " . $conn_back->error);
            }
            
            // Insert into investments table
            $invest_query = "INSERT INTO investments (user_id, plan_id, amount, roi_expected, status, started_at, ends_at, created_at) 
                            VALUES ('$user_id', '$plan_id', '$amount', '$roi_expected', 'active', '$start_date', '$end_date', NOW())";
            $invest_result = $conn_back->query($invest_query);
            
            if (!$invest_result) {
                throw new Exception("Failed to create investment: " . $conn_back->error);
            }
            
            // Generate unique reference
            $reference = 'INV-' . time() . '-' . $user_id;
            $transaction_proof_id = 'PROOF-' . time() . '-' . $user_id;
            
            // Insert into transactions table - Adapt this to match your actual table structure
            $trans_query = "INSERT INTO transactions (transaction_type, reference_id, transaction_proof_id, 
                           amount, currency, status, date_time, description, user_id) 
                           VALUES ('investment', '$reference', '$transaction_proof_id', 
                           '$amount', 'USD', 'completed', NOW(), 'Investment in " . $plan['name'] . " from " . $balance_name . "', '$user_id')";
            $trans_result = $conn_back->query($trans_query);
            
            if (!$trans_result) {
                throw new Exception("Failed to record transaction: " . $conn_back->error);
            }
            
            // Commit transaction
            $conn_back->commit();
            
            echo '<script>alert("Investment successful!"); window.location.href="/user/investment";</script>';
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn_back->rollback();
            error_log("Investment error: " . $e->getMessage());
            echo '<script>alert("Error processing investment: ' . addslashes($e->getMessage()) . '"); window.location.href="/user/investment";</script>';
        }
    } else {
        echo '<script>alert("Invalid investment plan selected."); window.location.href="/user/investment";</script>';
    }
}
?>

<!-- JavaScript for investment modal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const investButtons = document.querySelectorAll('.invest-btn');
    investButtons.forEach(button => {
        button.addEventListener('click', function() {
            const planId = this.getAttribute('data-plan-id');
            const planName = this.getAttribute('data-plan-name');
            const minAmount = this.getAttribute('data-min-amount');
            const maxAmount = this.getAttribute('data-max-amount');
            
            document.getElementById('planId').value = planId;
            document.getElementById('planName').textContent = planName;
            document.getElementById('minAmount').textContent = minAmount;
            document.getElementById('maxAmount').textContent = maxAmount;
            
            document.getElementById('investmentAmount').min = minAmount;
            document.getElementById('investmentAmount').max = maxAmount;
            
            // Show modal
            const investmentModal = new bootstrap.Modal(document.getElementById('investmentModal'));
            investmentModal.show();
        });
    });
});
</script>

<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/footer.php';
?>