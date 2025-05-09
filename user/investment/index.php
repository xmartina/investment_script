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
    
    // Get plan details for ROI calculation
    $plan_query = "SELECT * FROM investment_plans WHERE id = '$plan_id'";
    $plan_result = $conn_back->query($plan_query);
    
    if ($plan_result && $plan_result->num_rows > 0) {
        $plan = $plan_result->fetch_assoc();
        
        // Calculate expected ROI and end date
        $roi_expected = $amount * ($plan['roi_percent'] / 100);
        $start_date = date('Y-m-d H:i:s');
        $end_date = date('Y-m-d H:i:s', strtotime($start_date . ' + ' . $plan['duration_days'] . ' days'));
        
        // Insert into investments table
        $invest_query = "INSERT INTO investments (user_id, plan_id, amount, roi_expected, status, started_at, ends_at, created_at) 
                        VALUES ('$user_id', '$plan_id', '$amount', '$roi_expected', 'active', '$start_date', '$end_date', NOW())";
        
        if ($conn_back->query($invest_query)) {
            // Generate unique reference
            $reference = 'INV-' . time() . '-' . $user_id;
            
            // Insert into transactions table
            $trans_query = "INSERT INTO transactions (user_id, type, amount, status, reference, description, created_at) 
                            VALUES ('$user_id', 'investment', '$amount', 'successful', '$reference', 
                            'Investment in " . $plan['name'] . "', NOW())";
            
            if ($conn_back->query($trans_query)) {
                echo '<script>alert("Investment successful!"); window.location.href="/user/investment";</script>';
            } else {
                echo '<script>alert("Error recording transaction. Please contact support.");</script>';
            }
        } else {
            echo '<script>alert("Error processing investment. Please try again.");</script>';
        }
    } else {
        echo '<script>alert("Invalid investment plan selected.");</script>';
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