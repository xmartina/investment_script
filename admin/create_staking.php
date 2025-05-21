<?php
// Admin Create Staking Page
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Create Staking";
$current_page = "create_staking.php";
$message = "";
$error = "";

// Get users for dropdown
$users_query = "SELECT id, CONCAT(first_name, ' ', last_name, ' (', email, ')') as user_name FROM users ORDER BY first_name, last_name";
$users_result = $conn_back->query($users_query);

// Get staking plans for dropdown
$plans_query = "SELECT id, name, min_amount, max_amount, reward_percent, duration_days, lock_period_days FROM staking_plans WHERE is_active = 1 ORDER BY name";
$plans_result = $conn_back->query($plans_query);

// Debugging - Check for query errors
if (!$plans_result) {
    $error = "Error fetching staking plans: " . $conn_back->error;
}

// If no plans found, show all plans regardless of active status
if ($plans_result && $plans_result->num_rows == 0) {
    $plans_query = "SELECT id, name, min_amount, max_amount, reward_percent, duration_days, lock_period_days FROM staking_plans ORDER BY name";
    $plans_result = $conn_back->query($plans_query);
    
    if (!$plans_result) {
        $error = "Error fetching all staking plans: " . $conn_back->error;
    }
}

// Debugging - Check if any staking plans were found
$num_plans = ($plans_result) ? $plans_result->num_rows : 0;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_staking'])) {
    $user_id = (int)$_POST['user_id'];
    $plan_id = (int)$_POST['plan_id'];
    $amount = (float)$_POST['amount'];
    $is_compounding = isset($_POST['is_compounding']) ? 1 : 0;
    $admin_id = $_SESSION['admin_id'];
    
    // Validate inputs
    if ($user_id <= 0) {
        $error = "Please select a valid user.";
    } elseif ($plan_id <= 0) {
        $error = "Please select a valid staking plan.";
    } elseif ($amount <= 0) {
        $error = "Please enter a valid amount greater than zero.";
    } else {
        // Get plan details to validate amount
        $plan_stmt = $conn_back->prepare("SELECT * FROM staking_plans WHERE id = ?");
        $plan_stmt->bind_param("i", $plan_id);
        $plan_stmt->execute();
        $plan = $plan_stmt->get_result()->fetch_assoc();
        $plan_stmt->close();
        
        if (!$plan) {
            $error = "Selected staking plan does not exist.";
        } elseif ($amount < $plan['min_amount']) {
            $error = "Amount is less than the minimum required for this plan ({$plan['min_amount']}).";
        } elseif ($plan['max_amount'] > 0 && $amount > $plan['max_amount']) {
            $error = "Amount exceeds the maximum allowed for this plan ({$plan['max_amount']}).";
        } else {
            // Calculate dates
            $now = new DateTime();
            $started_at = $now->format('Y-m-d H:i:s');
            
            $duration = $plan['duration_days'];
            $ends_at = (clone $now)->add(new DateInterval("P{$duration}D"))->format('Y-m-d H:i:s');
            
            $lock_period = $plan['lock_period_days'];
            $unstake_available_at = (clone $now)->add(new DateInterval("P{$lock_period}D"))->format('Y-m-d H:i:s');
            
            // Calculate daily reward
            $annual_reward_percent = $plan['reward_percent'];
            $daily_reward_percent = $annual_reward_percent / 365;
            $daily_reward = $amount * ($daily_reward_percent / 100);
            
            // Start a transaction
            $conn_back->begin_transaction();
            
            try {
                // Insert staking record
                $stmt = $conn_back->prepare("
                    INSERT INTO staking (
                        user_id, plan_id, amount, duration_days, reward_percent,
                        apy, earned_reward, is_compounding, status,
                        started_at, ends_at, unstake_available_at, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, 0, ?, 'active', ?, ?, ?, NOW())
                ");
                
                $stmt->bind_param("iidiidiiss", 
                    $user_id, 
                    $plan_id, 
                    $amount, 
                    $duration, 
                    $annual_reward_percent,
                    $annual_reward_percent,
                    $is_compounding,
                    $started_at,
                    $ends_at,
                    $unstake_available_at
                );
                
                $stmt->execute();
                $staking_id = $conn_back->insert_id;
                $stmt->close();
                
                // Create a transaction record
                $transaction_reference = 'STK-' . time() . '-' . $user_id;
                $transaction_proof = 'PROOF-' . time() . '-' . $user_id;
                $description = "Staking in {$plan['name']} created by admin";
                
                $stmt = $conn_back->prepare("
                    INSERT INTO transactions (
                        user_id, amount, transaction_type, reference_id, transaction_proof_id,
                        currency, status, date_time, description
                    ) VALUES (?, ?, 'staking', ?, ?, '$', 'active', NOW(), ?)
                ");
                $stmt->bind_param("idsss", $user_id, $amount, $transaction_reference, $transaction_proof, $description);
                $stmt->execute();
                $stmt->close();
                
                // Create initial staking reward record for first day
                $nextRewardDate = (clone $now)->add(new DateInterval('P1D'))->format('Y-m-d H:i:s');
                
                $reward_stmt = $conn_back->prepare("
                    INSERT INTO staking_rewards (
                        staking_id, user_id, reward_amount, status, 
                        expected_date, created_at
                    ) VALUES (?, ?, ?, 'pending', ?, NOW())
                ");
                $reward_stmt->bind_param("iids", $staking_id, $user_id, $daily_reward, $nextRewardDate);
                $reward_stmt->execute();
                $reward_stmt->close();
                
                // Log admin action
                $admin_action = "Created staking #{$staking_id} of {$amount} for user #{$user_id} in plan #{$plan_id}";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $admin_action, $ip);
                $log_stmt->execute();
                $log_stmt->close();
                
                $conn_back->commit();
                $message = "Staking has been created successfully!";
            } catch (Exception $e) {
                $conn_back->rollback();
                $error = "Error creating staking: " . $e->getMessage();
            }
        }
    }
}

include_once __DIR__ . '/layout/header.php';
?>
<style>
    #createStakingForm{
        color: rgba(255, 255, 255, 0.85);
    }
    #createStakingForm .card-body p {
        color: rgba(255, 255, 255, 0.85);
    }
</style>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create Staking</h1>
        <a href="staking.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Staking
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

    <?php // Debugging message ?>
    <div class="alert alert-info">
        <?= $num_plans ?> staking plans found in database.
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">New Staking</h6>
        </div>
        <div class="card-body">
            <form method="post" id="createStakingForm">
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
                    <label for="plan_id">Staking Plan:</label>
                    <select class="form-control" id="plan_id" name="plan_id" required>
                        <option value="">-- Select Plan --</option>
                        <?php while ($plan = $plans_result->fetch_assoc()): ?>
                            <option value="<?= $plan['id'] ?>" 
                                    data-min="<?= $plan['min_amount'] ?>" 
                                    data-max="<?= $plan['max_amount'] ?>"
                                    data-reward="<?= $plan['reward_percent'] ?>"
                                    data-duration="<?= $plan['duration_days'] ?>"
                                    data-lock="<?= $plan['lock_period_days'] ?>">
                                <?= htmlspecialchars($plan['name']) ?> 
                                (<?= $plan['reward_percent'] ?>% APY, <?= $plan['duration_days'] ?> days)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="amount">Staking Amount:</label>
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
                
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="is_compounding" name="is_compounding">
                        <label class="custom-control-label" for="is_compounding">Enable Compounding (automatically reinvest rewards)</label>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Staking Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>APY:</strong> <span id="reward_percent">0.00</span>%</p>
                                <p><strong>Daily Reward:</strong> $<span id="daily_reward">0.00</span></p>
                                <p><strong>Lock Period:</strong> <span id="lock_period">0</span> days</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Duration:</strong> <span id="duration_days">0</span> days</p>
                                <p><strong>Total Potential Reward:</strong> $<span id="total_reward">0.00</span></p>
                                <p><strong>Available for Unstaking:</strong> <span id="unstake_date">-</span></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="create_staking" class="btn btn-primary">Create Staking</button>
                <a href="staking.php" class="btn btn-secondary">Cancel</a>
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
        var rewardPercent = parseFloat(selectedOption.data('reward')) || 0;
        var durationDays = parseInt(selectedOption.data('duration')) || 0;
        var lockPeriod = parseInt(selectedOption.data('lock')) || 0;
        
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
        $('#reward_percent').text(rewardPercent.toFixed(2));
        $('#duration_days').text(durationDays);
        $('#lock_period').text(lockPeriod);
        
        // Calculate unstake date
        var now = new Date();
        var unstakeDate = new Date(now);
        unstakeDate.setDate(unstakeDate.getDate() + lockPeriod);
        $('#unstake_date').text(unstakeDate.toLocaleDateString());
        
        // Calculate rewards if amount is entered
        calculateRewards();
    });
    
    // Calculate rewards when amount changes
    $('#amount').on('input', function() {
        calculateRewards();
    });
    
    // Function to calculate and display rewards
    function calculateRewards() {
        var amount = parseFloat($('#amount').val()) || 0;
        var rewardPercent = parseFloat($('#reward_percent').text()) || 0;
        var durationDays = parseInt($('#duration_days').text()) || 0;
        
        // Daily reward calculation
        var dailyRewardPercent = rewardPercent / 365;
        var dailyReward = amount * (dailyRewardPercent / 100);
        
        // Total potential reward (simple interest for now)
        var totalReward = dailyReward * durationDays;
        
        $('#daily_reward').text(dailyReward.toFixed(2));
        $('#total_reward').text(totalReward.toFixed(2));
    }
    
    // Form validation
    $('#createStakingForm').submit(function(e) {
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
            alert('Please select a staking plan');
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