<?php
// Admin Create Investment Page
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Create Investment";
$current_page = "create_investment.php";
$message = "";
$error = "";

// Get users for dropdown
$users_query = "SELECT id, CONCAT(first_name, ' ', last_name, ' (', email, ')') as user_name FROM users ORDER BY first_name, last_name";
$users_result = $conn_back->query($users_query);

// Get investment plans for dropdown
$plans_query = "SELECT id, name, min_amount, max_amount, roi_percent, duration_days FROM investment_plans WHERE is_active = 1 ORDER BY name";
$plans_result = $conn_back->query($plans_query);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_investment'])) {
    $user_id = (int)$_POST['user_id'];
    $plan_id = (int)$_POST['plan_id'];
    $amount = (float)$_POST['amount'];
    $admin_id = $_SESSION['admin_id'];
    
    // Validate inputs
    if ($user_id <= 0) {
        $error = "Please select a valid user.";
    } elseif ($plan_id <= 0) {
        $error = "Please select a valid investment plan.";
    } elseif ($amount <= 0) {
        $error = "Please enter a valid amount greater than zero.";
    } else {
        // Get plan details to validate amount
        $plan_stmt = $conn_back->prepare("SELECT * FROM investment_plans WHERE id = ?");
        $plan_stmt->bind_param("i", $plan_id);
        $plan_stmt->execute();
        $plan = $plan_stmt->get_result()->fetch_assoc();
        $plan_stmt->close();
        
        if (!$plan) {
            $error = "Selected investment plan does not exist.";
        } elseif ($amount < $plan['min_amount']) {
            $error = "Amount is less than the minimum required for this plan ({$plan['min_amount']}).";
        } elseif ($plan['max_amount'] > 0 && $amount > $plan['max_amount']) {
            $error = "Amount exceeds the maximum allowed for this plan ({$plan['max_amount']}).";
        } else {
            // Calculate expected returns
            $roi_percentage = $plan['roi_percent'];
            $roi_expected = $amount * ($roi_percentage / 100);
            
            // Calculate start and end dates
            $now = new DateTime();
            $started_at = $now->format('Y-m-d H:i:s');
            $now->add(new DateInterval('P' . $plan['duration_days'] . 'D'));
            $ends_at = $now->format('Y-m-d H:i:s');
            
            // Start a transaction
            $conn_back->begin_transaction();
            
            try {
                // Insert investment record
                $stmt = $conn_back->prepare("
                    INSERT INTO investments (
                        user_id, plan_id, amount, roi_expected, roi_percentage,
                        created_at, status, started_at, ends_at
                    ) VALUES (?, ?, ?, ?, ?, NOW(), 'active', ?, ?)
                ");
                $stmt->bind_param("iiddiss", $user_id, $plan_id, $amount, $roi_expected, $roi_percentage, $started_at, $ends_at);
                $stmt->execute();
                $investment_id = $conn_back->insert_id;
                $stmt->close();
                
                // Create a transaction record
                $transaction_reference = 'INV-' . time() . '-' . $user_id;
                $transaction_proof = 'PROOF-' . time() . '-' . $user_id;
                $description = "Investment in {$plan['name']} created by admin";
                
                $stmt = $conn_back->prepare("
                    INSERT INTO transactions (
                        user_id, amount, transaction_type, reference_id, transaction_proof_id,
                        currency, status, date_time, description
                    ) VALUES (?, ?, 'investment', ?, ?, '$', 'active', NOW(), ?)
                ");
                $stmt->bind_param("idsss", $user_id, $amount, $transaction_reference, $transaction_proof, $description);
                $stmt->execute();
                $stmt->close();
                
                // Log admin action
                $admin_action = "Created investment #{$investment_id} of {$amount} for user #{$user_id} in plan #{$plan_id}";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $admin_action, $ip);
                $log_stmt->execute();
                $log_stmt->close();
                
                $conn_back->commit();
                $message = "Investment has been created successfully!";
            } catch (Exception $e) {
                $conn_back->rollback();
                $error = "Error creating investment: " . $e->getMessage();
            }
        }
    }
}

include_once __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create Investment</h1>
        <a href="investments.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Investments
        </a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">New Investment</h6>
        </div>
        <div class="card-body">
            <form method="post" id="createInvestmentForm">
                <div class="form-group">
                    <label for="user_id">Select User:</label>
                    <select class="form-control" id="user_id" name="user_id" required>
                        <option value="">-- Select User --</option>
                        <?php while ($user = $users_result->fetch_assoc()): ?>
                            <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['user_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="plan_id">Investment Plan:</label>
                    <select class="form-control" id="plan_id" name="plan_id" required>
                        <option value="">-- Select Plan --</option>
                        <?php while ($plan = $plans_result->fetch_assoc()): ?>
                            <option value="<?= $plan['id'] ?>" 
                                    data-min="<?= $plan['min_amount'] ?>" 
                                    data-max="<?= $plan['max_amount'] ?>"
                                    data-roi="<?= $plan['roi_percent'] ?>"
                                    data-duration="<?= $plan['duration_days'] ?>">
                                <?= htmlspecialchars($plan['name']) ?> 
                                (<?= $plan['roi_percent'] ?>% ROI, <?= $plan['duration_days'] ?> days)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="amount">Investment Amount:</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" required>
                    </div>
                    <small id="amountHelp" class="form-text text-muted">
                        Min: $<span id="min_amount">0.00</span> 
                        <?php /* Only show max if it exists */ ?>
                        <span id="max_amount_container" style="display:none;">
                            | Max: $<span id="max_amount">0.00</span>
                        </span>
                    </small>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Investment Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>ROI Percentage:</strong> <span id="roi_percent">0.00</span>%</p>
                                <p><strong>ROI Amount:</strong> $<span id="roi_amount">0.00</span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Duration:</strong> <span id="duration_days">0</span> days</p>
                                <p><strong>Total Return:</strong> $<span id="total_return">0.00</span></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="create_investment" class="btn btn-primary">Create Investment</button>
                <a href="investments.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize select2 for better dropdowns
    if ($.fn.select2) {
        $('#user_id, #plan_id').select2({
            placeholder: "Select an option"
        });
    }
    
    // Update amount limits and calculations when plan changes
    $('#plan_id').change(function() {
        var selectedOption = $(this).find('option:selected');
        var minAmount = parseFloat(selectedOption.data('min')) || 0;
        var maxAmount = parseFloat(selectedOption.data('max')) || 0;
        var roiPercent = parseFloat(selectedOption.data('roi')) || 0;
        var durationDays = parseInt(selectedOption.data('duration')) || 0;
        
        // Update min/max display
        $('#min_amount').text(minAmount.toFixed(2));
        $('#max_amount').text(maxAmount.toFixed(2));
        
        if (maxAmount > 0) {
            $('#max_amount_container').show();
        } else {
            $('#max_amount_container').hide();
        }
        
        // Update amount input constraints
        $('#amount').attr('min', minAmount);
        if (maxAmount > 0) {
            $('#amount').attr('max', maxAmount);
        } else {
            $('#amount').removeAttr('max');
        }
        
        // Update displayed values
        $('#roi_percent').text(roiPercent.toFixed(2));
        $('#duration_days').text(durationDays);
        
        // Calculate ROI if amount is entered
        calculateROI();
    });
    
    // Calculate ROI when amount changes
    $('#amount').on('input', function() {
        calculateROI();
    });
    
    // Function to calculate and display ROI
    function calculateROI() {
        var amount = parseFloat($('#amount').val()) || 0;
        var roiPercent = parseFloat($('#roi_percent').text()) || 0;
        
        var roiAmount = amount * (roiPercent / 100);
        var totalReturn = amount + roiAmount;
        
        $('#roi_amount').text(roiAmount.toFixed(2));
        $('#total_return').text(totalReturn.toFixed(2));
    }
    
    // Form validation
    $('#createInvestmentForm').submit(function(e) {
        var userId = $('#user_id').val();
        var planId = $('#plan_id').val();
        var amount = parseFloat($('#amount').val()) || 0;
        var minAmount = parseFloat($('#min_amount').text()) || 0;
        var maxAmount = parseFloat($('#max_amount').text()) || 0;
        
        if (!userId) {
            alert('Please select a user');
            e.preventDefault();
            return false;
        }
        
        if (!planId) {
            alert('Please select an investment plan');
            e.preventDefault();
            return false;
        }
        
        if (amount <= 0) {
            alert('Please enter a valid amount greater than zero');
            e.preventDefault();
            return false;
        }
        
        if (amount < minAmount) {
            alert('Amount is less than the minimum required for this plan ($' + minAmount.toFixed(2) + ')');
            e.preventDefault();
            return false;
        }
        
        if (maxAmount > 0 && amount > maxAmount) {
            alert('Amount exceeds the maximum allowed for this plan ($' + maxAmount.toFixed(2) + ')');
            e.preventDefault();
            return false;
        }
        
        return true;
    });
});
</script>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 