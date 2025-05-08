<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
$page_name = 'Deposit';
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';

// Fetch investment plans from database
$query = "SELECT * FROM investment_plans WHERE status = 'active'";
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
                    <div class="row">
                        <?php 
                        if (!empty($investment_plans)) {
                            foreach ($investment_plans as $plan) {
                        ?>
                        <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                            <div class="card adminuiux-card mb-3">
                                <div class="card-body">
                                    <p><span class="badge badge-light text-bg-theme-1"><?php echo $plan['plan_type']; ?></span></p>
                                    <h5 class="fw-medium mb-1"><?php echo $plan['name']; ?></h5>
                                    <p class="text-secondary mb-4"><?php echo $plan['category']; ?> <i class="bi bi-chevron-right"></i>
                                        <?php echo $plan['duration']; ?> <i class="bi bi-chevron-right"></i> <?php echo $plan['risk_level']; ?></p>
                                    <h6 class="fw-medium">$<?php echo number_format($plan['min_investment'], 2); ?></h6>
                                    <p class="text-secondary small mb-4">Min. Investment</p>
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <button class="btn btn-theme invest-btn" data-plan-id="<?php echo $plan['id']; ?>" 
                                                data-plan-name="<?php echo $plan['name']; ?>"
                                                data-min-amount="<?php echo $plan['min_investment']; ?>">Apply</button>
                                        </div>
                                        <div class="col-auto">
                                            <p class="text-success"><?php echo $plan['availability']; ?> days available</p>
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
                        <input type="number" class="form-control" id="investmentAmount" name="amount" min="" required>
                        <small class="text-muted">Minimum investment: $<span id="minAmount"></span></small>
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
    $amount = $_POST['amount'];
    $user_id = $_SESSION['user_id'];
    
    // Insert into investments table
    $invest_query = "INSERT INTO investments (user_id, plan_id, trans_type, amount_invested, returns_amount, created_at) 
                     VALUES ('$user_id', '$plan_id', 'investment', '$amount', '0', NOW())";
    
    if ($conn_back->query($invest_query)) {
        $transaction_proof_id = 'INV-' . time() . '-' . $user_id;
        $reference_id = 'REF-' . time() . '-' . $user_id;
        
        // Insert into transactions table
        $trans_query = "INSERT INTO transactions (transaction_type, reference_id, transaction_proof_id, 
                        amount, currency, status, date_time, description, user_id) 
                        VALUES ('investment', '$reference_id', '$transaction_proof_id', 
                        '$amount', 'USD', 'completed', NOW(), 'Investment Purchase', '$user_id')";
        
        if ($conn_back->query($trans_query)) {
            // Update investment record with transaction ID
            $trans_id = $conn_back->insert_id;
            $update_query = "UPDATE investments SET trans_id = '$trans_id' WHERE id = '" . $conn_back->insert_id . "'";
            $conn_back->query($update_query);
            
            echo '<script>alert("Investment successful!"); window.location.href="/user/investment";</script>';
        } else {
            echo '<script>alert("Error processing transaction. Please try again.");</script>';
        }
    } else {
        echo '<script>alert("Error processing investment. Please try again.");</script>';
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
            
            document.getElementById('planId').value = planId;
            document.getElementById('planName').textContent = planName;
            document.getElementById('minAmount').textContent = minAmount;
            document.getElementById('investmentAmount').min = minAmount;
            
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