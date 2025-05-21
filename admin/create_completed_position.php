<?php
// Admin Create Completed Position Page
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Create Completed Position";
$current_page = "create_completed_position.php";
$message = "";
$error = "";

// Get users for dropdown
$users_query = "SELECT id, CONCAT(first_name, ' ', last_name, ' (', email, ')') as user_name FROM users ORDER BY first_name, last_name";
$users_result = $conn_back->query($users_query);

// Get investment plans for dropdown
$investment_plans_query = "SELECT id, name, min_amount, max_amount, roi_percent, duration_days FROM investment_plans WHERE is_active = 1 ORDER BY name";
$investment_plans_result = $conn_back->query($investment_plans_query);

// Get staking plans for dropdown
$staking_plans_query = "SELECT id, name, min_amount, max_amount, reward_percent, duration_days FROM staking_plans WHERE is_active = 1 ORDER BY name";
$staking_plans_result = $conn_back->query($staking_plans_query);

// Process form submission for completed investment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_completed_investment'])) {
    $user_id = (int)$_POST['user_id'];
    $plan_id = (int)$_POST['plan_id'];
    $amount = (float)$_POST['amount'];
    $roi_amount = (float)$_POST['roi_amount'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $admin_id = $_SESSION['admin_id'];
    
    // Validate inputs
    if ($user_id <= 0) {
        $error = "Please select a valid user.";
    } elseif ($plan_id <= 0) {
        $error = "Please select a valid investment plan.";
    } elseif ($amount <= 0) {
        $error = "Please enter a valid amount greater than zero.";
    } elseif ($roi_amount < 0) {
        $error = "ROI amount cannot be negative.";
    } elseif (empty($start_date) || empty($end_date)) {
        $error = "Please provide start and end dates.";
    } elseif (strtotime($end_date) <= strtotime($start_date)) {
        $error = "End date must be after start date.";
    } else {
        // Get plan details
        $plan_stmt = $conn_back->prepare("SELECT * FROM investment_plans WHERE id = ?");
        $plan_stmt->bind_param("i", $plan_id);
        $plan_stmt->execute();
        $plan = $plan_stmt->get_result()->fetch_assoc();
        $plan_stmt->close();
        
        if (!$plan) {
            $error = "Selected investment plan does not exist.";
        } else {
            // Begin transaction
            $conn_back->begin_transaction();
            
            try {
                // Insert investment record as completed
                $stmt = $conn_back->prepare("
                    INSERT INTO investments (
                        user_id, plan_id, amount, roi_expected, roi_percentage,
                        created_at, status, started_at, ends_at
                    ) VALUES (?, ?, ?, ?, ?, NOW(), 'completed', ?, ?)
                ");
                $roi_percentage = $plan['roi_percent'];
                $stmt->bind_param("iiddiss", $user_id, $plan_id, $amount, $roi_amount, $roi_percentage, $start_date, $end_date);
                $stmt->execute();
                $investment_id = $conn_back->insert_id;
                $stmt->close();
                
                // Add return to investment_returns as paid
                $stmt = $conn_back->prepare("
                    INSERT INTO investment_returns (
                        investment_id, user_id, return_amount, roi_percentage, 
                        expected_date, status, created_at, paid_at
                    ) VALUES (?, ?, ?, ?, ?, 'paid', NOW(), NOW())
                ");
                $stmt->bind_param("iidds", $investment_id, $user_id, $roi_amount, $roi_percentage, $end_date);
                $stmt->execute();
                $stmt->close();
                
                // Create transaction record for the completed investment
                $transaction_reference = 'INVCOMPLETE-' . time() . '-' . $user_id;
                $total_return = $amount + $roi_amount;
                $description = "Completed investment in {$plan['name']} added by admin - Principal: \${$amount}, ROI: \${$roi_amount}";
                
                $stmt = $conn_back->prepare("
                    INSERT INTO transactions (
                        user_id, amount, transaction_type, reference_id,
                        currency, status, date_time, description,
                        roi_percentage
                    ) VALUES (?, ?, 'investment_return', ?, '$', 'completed', NOW(), ?, ?)
                ");
                $stmt->bind_param("idssd", $user_id, $total_return, $transaction_reference, $description, $roi_percentage);
                $stmt->execute();
                $stmt->close();
                
                // Update user balance - Add the total return amount to the investment balance
                $update_balance_stmt = $conn_back->prepare("
                    UPDATE users SET investment_balance = investment_balance + CAST(? AS SIGNED) WHERE id = ?
                ");
                $update_balance_stmt->bind_param("di", $total_return, $user_id);
                $update_balance_stmt->execute();
                $update_balance_stmt->close();
                
                // Log admin action
                $admin_action = "Created completed investment #{$investment_id} for user #{$user_id} in plan #{$plan_id}";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $admin_action, $ip);
                $log_stmt->execute();
                $log_stmt->close();
                
                $conn_back->commit();
                $message = "Completed investment has been created successfully!";
            } catch (Exception $e) {
                $conn_back->rollback();
                $error = "Error creating completed investment: " . $e->getMessage();
            }
        }
    }
}

// Process form submission for completed staking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_completed_staking'])) {
    $user_id = (int)$_POST['user_id'];
    $plan_id = (int)$_POST['staking_plan_id'];
    $amount = (float)$_POST['staking_amount'];
    $earned_reward = (float)$_POST['earned_reward'];
    $start_date = $_POST['staking_start_date'];
    $end_date = $_POST['staking_end_date'];
    $admin_id = $_SESSION['admin_id'];
    
    // Validate inputs
    if ($user_id <= 0) {
        $error = "Please select a valid user.";
    } elseif ($plan_id <= 0) {
        $error = "Please select a valid staking plan.";
    } elseif ($amount <= 0) {
        $error = "Please enter a valid amount greater than zero.";
    } elseif ($earned_reward < 0) {
        $error = "Earned reward amount cannot be negative.";
    } elseif (empty($start_date) || empty($end_date)) {
        $error = "Please provide start and end dates.";
    } elseif (strtotime($end_date) <= strtotime($start_date)) {
        $error = "End date must be after start date.";
    } else {
        // Get plan details
        $plan_stmt = $conn_back->prepare("SELECT * FROM staking_plans WHERE id = ?");
        $plan_stmt->bind_param("i", $plan_id);
        $plan_stmt->execute();
        $plan = $plan_stmt->get_result()->fetch_assoc();
        $plan_stmt->close();
        
        if (!$plan) {
            $error = "Selected staking plan does not exist.";
        } else {
            // Begin transaction
            $conn_back->begin_transaction();
            
            try {
                // Calculate dates
                $unstake_date = date('Y-m-d H:i:s', strtotime($start_date . " + " . ($plan['lock_period_days'] ?? 0) . " days"));
                
                // Insert staking record as completed
                $stmt = $conn_back->prepare("
                    INSERT INTO staking (
                        user_id, plan_id, amount, duration_days, reward_percent,
                        apy, earned_reward, is_compounding, status,
                        started_at, ends_at, unstake_available_at, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 'completed', ?, ?, ?, NOW())
                ");
                
                $reward_percent = $plan['reward_percent'];
                $apy = ($reward_percent * 365) / ($plan['duration_days'] ?? 30);
                $duration_days = $plan['duration_days'] ?? 30;
                
                $stmt->bind_param("iidididsss", 
                    $user_id, 
                    $plan_id, 
                    $amount, 
                    $duration_days, 
                    $reward_percent,
                    $apy,
                    $earned_reward,
                    $start_date,
                    $end_date,
                    $unstake_date
                );
                $stmt->execute();
                $staking_id = $conn_back->insert_id;
                $stmt->close();
                
                // Create transaction record for the completed staking
                $transaction_reference = 'STAKECOMPLETE-' . time() . '-' . $user_id;
                $total_return = $amount + $earned_reward;
                $description = "Completed staking in {$plan['name']} added by admin - Principal: \${$amount}, Rewards: \${$earned_reward}";
                
                $stmt = $conn_back->prepare("
                    INSERT INTO transactions (
                        user_id, amount, transaction_type, reference_id,
                        currency, status, date_time, description
                    ) VALUES (?, ?, 'staking_return', ?, '$', 'completed', NOW(), ?)
                ");
                $stmt->bind_param("idss", $user_id, $total_return, $transaction_reference, $description);
                $stmt->execute();
                $stmt->close();
                
                // Update user balance - Add the total return amount to the staking balance
                $update_balance_stmt = $conn_back->prepare("
                    UPDATE users SET staking_balance = staking_balance + CAST(? AS SIGNED) WHERE id = ?
                ");
                $update_balance_stmt->bind_param("di", $total_return, $user_id);
                $update_balance_stmt->execute();
                $update_balance_stmt->close();
                
                // Log admin action
                $admin_action = "Created completed staking #{$staking_id} for user #{$user_id} in plan #{$plan_id}";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $admin_action, $ip);
                $log_stmt->execute();
                $log_stmt->close();
                
                $conn_back->commit();
                $message = "Completed staking position has been created successfully!";
            } catch (Exception $e) {
                $conn_back->rollback();
                $error = "Error creating completed staking: " . $e->getMessage();
            }
        }
    }
}

include_once __DIR__ . '/layout/header.php';
?>
<style>
    #completedInvestmentForm{
        color: rgba(255, 255, 255, 0.85);
    }
    #completedStakingForm{
        color: rgba(255, 255, 255, 0.85);
    }
</style>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create Completed Position</h1>
        <div>
            <a href="investments.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm mr-2">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Investments
            </a>
            <a href="staking.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Staking
            </a>
        </div>
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

    <div class="row">
        <!-- Completed Investment Form -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Add Completed Investment</h6>
                </div>
                <div class="card-body">
                    <form method="post" id="completedInvestmentForm">
                        <div class="form-group">
                            <label for="user_id">Select User:</label>
                            <select class="form-control user-select" id="user_id" name="user_id" required>
                                <option value="">-- Select User --</option>
                                <?php 
                                if ($users_result) {
                                    $users_result->data_seek(0); // Reset result pointer
                                    while ($user = $users_result->fetch_assoc()): 
                                ?>
                                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['user_name']) ?></option>
                                <?php 
                                    endwhile;
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="plan_id">Investment Plan:</label>
                            <select class="form-control" id="plan_id" name="plan_id" required>
                                <option value="">-- Select Plan --</option>
                                <?php 
                                if ($investment_plans_result) {
                                    while ($plan = $investment_plans_result->fetch_assoc()): 
                                ?>
                                    <option value="<?= $plan['id'] ?>" 
                                            data-roi="<?= $plan['roi_percent'] ?>">
                                        <?= htmlspecialchars($plan['name']) ?> 
                                        (<?= $plan['roi_percent'] ?>% ROI, <?= $plan['duration_days'] ?> days)
                                    </option>
                                <?php 
                                    endwhile;
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="amount">Investment Amount:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="roi_amount">ROI Amount:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control" id="roi_amount" name="roi_amount" step="0.01" min="0" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Start Date:</label>
                                    <input type="datetime-local" class="form-control" id="start_date" name="start_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">End Date:</label>
                                    <input type="datetime-local" class="form-control" id="end_date" name="end_date" required>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Note:</strong> The completed investment will be recorded in the system and the total amount (principal + ROI) will be added to the user's balance.
                        </div>
                        
                        <button type="submit" name="create_completed_investment" class="btn btn-primary">Add Completed Investment</button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Completed Staking Form -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Add Completed Staking</h6>
                </div>
                <div class="card-body">
                    <form method="post" id="completedStakingForm">
                        <div class="form-group">
                            <label for="staking_user_id">Select User:</label>
                            <select class="form-control user-select" id="staking_user_id" name="user_id" required>
                                <option value="">-- Select User --</option>
                                <?php 
                                if ($users_result) {
                                    $users_result->data_seek(0); // Reset result pointer
                                    while ($user = $users_result->fetch_assoc()): 
                                ?>
                                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['user_name']) ?></option>
                                <?php 
                                    endwhile;
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="staking_plan_id">Staking Plan:</label>
                            <select class="form-control" id="staking_plan_id" name="staking_plan_id" required>
                                <option value="">-- Select Plan --</option>
                                <?php 
                                if ($staking_plans_result) {
                                    while ($plan = $staking_plans_result->fetch_assoc()): 
                                ?>
                                    <option value="<?= $plan['id'] ?>"
                                            data-reward="<?= $plan['reward_percent'] ?>">
                                        <?= htmlspecialchars($plan['name']) ?> 
                                        (<?= $plan['reward_percent'] ?>% Reward, <?= $plan['duration_days'] ?> days)
                                    </option>
                                <?php 
                                    endwhile;
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="staking_amount">Staking Amount:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control" id="staking_amount" name="staking_amount" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="earned_reward">Earned Reward:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control" id="earned_reward" name="earned_reward" step="0.01" min="0" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="staking_start_date">Start Date:</label>
                                    <input type="datetime-local" class="form-control" id="staking_start_date" name="staking_start_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="staking_end_date">End Date:</label>
                                    <input type="datetime-local" class="form-control" id="staking_end_date" name="staking_end_date" required>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Note:</strong> The completed staking position will be recorded in the system and the total amount (principal + rewards) will be added to the user's balance.
                        </div>
                        
                        <button type="submit" name="create_completed_staking" class="btn btn-primary">Add Completed Staking</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // If select2 is available, enhance the dropdowns
    if ($.fn.select2) {
        $('.user-select').select2({
            placeholder: "Select a user",
            allowClear: true
        });
        $('#plan_id, #staking_plan_id').select2({
            placeholder: "Select a plan"
        });
    }
    
    // Default date values to today
    var today = new Date();
    var todayFormatted = today.toISOString().slice(0, 16);
    $('#start_date, #staking_start_date').val(todayFormatted);
    
    // Set end date to 30 days later by default
    var thirtyDaysLater = new Date();
    thirtyDaysLater.setDate(thirtyDaysLater.getDate() + 30);
    var thirtyDaysFormatted = thirtyDaysLater.toISOString().slice(0, 16);
    $('#end_date, #staking_end_date').val(thirtyDaysFormatted);
    
    // Calculate ROI when investment amount or plan changes
    $('#amount, #plan_id').change(function() {
        var amount = parseFloat($('#amount').val()) || 0;
        var roiPercent = parseFloat($('#plan_id option:selected').data('roi')) || 0;
        var roiAmount = (amount * roiPercent / 100).toFixed(2);
        $('#roi_amount').val(roiAmount);
    });
    
    // Calculate earned reward when staking amount or plan changes
    $('#staking_amount, #staking_plan_id').change(function() {
        var amount = parseFloat($('#staking_amount').val()) || 0;
        var rewardPercent = parseFloat($('#staking_plan_id option:selected').data('reward')) || 0;
        var earnedReward = (amount * rewardPercent / 100).toFixed(2);
        $('#earned_reward').val(earnedReward);
    });
    
    // Basic form validation
    $('#completedInvestmentForm').submit(function(e) {
        var startDate = new Date($('#start_date').val());
        var endDate = new Date($('#end_date').val());
        
        if (endDate <= startDate) {
            alert('End date must be after start date.');
            e.preventDefault();
            return false;
        }
    });
    
    $('#completedStakingForm').submit(function(e) {
        var startDate = new Date($('#staking_start_date').val());
        var endDate = new Date($('#staking_end_date').val());
        
        if (endDate <= startDate) {
            alert('End date must be after start date.');
            e.preventDefault();
            return false;
        }
    });
});
</script>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 