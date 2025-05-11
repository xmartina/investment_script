<?php
// Admin Create Staking Position Page
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Create Staking Position";
$current_page = "create_staking.php";
$message = "";
$error = "";

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_staking'])) {
        $user_id = intval($_POST['user_id']);
        $plan_id = intval($_POST['plan_id']);
        $amount = floatval($_POST['amount']);
        $is_compounding = isset($_POST['is_compounding']) ? 1 : 0;
        
        // Validate inputs
        if ($user_id <= 0) {
            $error = "Please select a valid user.";
        } elseif ($plan_id <= 0) {
            $error = "Please select a valid staking plan.";
        } elseif ($amount <= 0) {
            $error = "Please enter a valid amount greater than zero.";
        } else {
            // Get plan details
            $stmt = $conn_back->prepare("SELECT * FROM staking_plans WHERE id = ?");
            $stmt->bind_param("i", $plan_id);
            $stmt->execute();
            $plan = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            // Get user details
            $stmt = $conn_back->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if (!$plan) {
                $error = "Selected staking plan does not exist.";
            } elseif (!$user) {
                $error = "Selected user does not exist.";
            } elseif ($plan['min_amount'] > $amount) {
                $error = "Amount is below the minimum required for this plan ($" . number_format($plan['min_amount'], 2) . ").";
            } elseif ($plan['max_amount'] > 0 && $plan['max_amount'] < $amount) {
                $error = "Amount exceeds the maximum allowed for this plan ($" . number_format($plan['max_amount'], 2) . ").";
            } elseif ($user['main_balance'] < $amount && !isset($_POST['bypass_balance_check'])) {
                $error = "User does not have sufficient balance. Current balance: $" . number_format($user['main_balance'], 2);
            } else {
                // Begin transaction
                $conn_back->begin_transaction();
                
                try {
                    // Calculate daily reward
                    $roi_daily = isset($plan['roi_daily']) ? $plan['roi_daily'] : $plan['reward_percent'];
                    $daily_reward = $amount * ($roi_daily / 100);
                    
                    // Create staking record
                    $stmt = $conn_back->prepare("
                        INSERT INTO staking_positions (
                            user_id, plan_id, amount, daily_reward, 
                            is_compounding, status, created_at
                        ) VALUES (?, ?, ?, ?, ?, 'active', NOW())
                    ");
                    $stmt->bind_param("iiddi", $user_id, $plan_id, $amount, $daily_reward, $is_compounding);
                    $stmt->execute();
                    $staking_id = $conn_back->insert_id;
                    $stmt->close();
                    
                    // Deduct from user balance if not bypassed
                    if (!isset($_POST['bypass_balance_check'])) {
                        $stmt = $conn_back->prepare("UPDATE users SET main_balance = main_balance - ? WHERE id = ?");
                        $stmt->bind_param("di", $amount, $user_id);
                        $stmt->execute();
                        $stmt->close();
                    }
                    
                    // Create transaction record
                    $description = "Staking in " . $plan['name'] . " plan";
                    $stmt = $conn_back->prepare("
                        INSERT INTO transactions (
                            user_id, amount, transaction_type, status, 
                            description, date_time
                        ) VALUES (?, ?, 'staking', 'completed', ?, NOW())
                    ");
                    $stmt->bind_param("ids", $user_id, $amount, $description);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Log admin activity
                    $admin_id = $_SESSION['admin_id'];
                    $action = "Created staking position #$staking_id for user #$user_id in plan " . $plan['name'] . " for $" . number_format($amount, 2);
                    $ip = $_SERVER['REMOTE_ADDR'];
                    
                    $stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $admin_id, $action, $ip);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Commit transaction
                    $conn_back->commit();
                    
                    $message = "Staking position created successfully for " . $user['first_name'] . " " . $user['last_name'] . " in " . $plan['name'] . " plan.";
                } catch (Exception $e) {
                    // Rollback transaction on error
                    $conn_back->rollback();
                    $error = "Error creating staking position: " . $e->getMessage();
                }
            }
        }
    }
}

// Check if staking_plans table exists
$table_exists = false;
$result = $conn_back->query("SHOW TABLES LIKE 'staking_plans'");
if ($result && $result->num_rows > 0) {
    $table_exists = true;
    
    // Get staking plans
    $plans_result = $conn_back->query("SELECT * FROM staking_plans WHERE is_active = 1 ORDER BY name");
    $plans = [];
    if ($plans_result && $plans_result->num_rows > 0) {
        while ($row = $plans_result->fetch_assoc()) {
            $plans[] = $row;
        }
    }
}

include_once __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create Staking Position</h1>
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

    <?php if (!$table_exists): ?>
        <div class="alert alert-warning">
            <p>The staking_plans table does not exist in the database. Please run the database initialization script first.</p>
            <form method="post" action="db_fix.php">
                <input type="hidden" name="create_staking_tables" value="1">
                <button type="submit" class="btn btn-primary">Create Staking Tables</button>
            </form>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Create New Staking Position</h6>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="user_id">Select User *</label>
                                        <select class="form-control" id="user_id" name="user_id" required>
                                            <option value="">Select User</option>
                                        </select>
                                        <small class="form-text text-muted">Search by username, email, or name</small>
                                    </div>
                                    
                                    <div class="form-group mt-3">
                                        <label for="user_balance">User Balance</label>
                                        <input type="text" class="form-control" id="user_balance" readonly>
                                    </div>
                                    
                                    <div class="form-group mt-3">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="bypass_balance_check" name="bypass_balance_check">
                                            <label class="custom-control-label" for="bypass_balance_check">Bypass balance check (create staking position without deducting from balance)</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="plan_id">Select Staking Plan *</label>
                                        <select class="form-control" id="plan_id" name="plan_id" required>
                                            <option value="">Select Plan</option>
                                            <?php foreach ($plans as $plan): ?>
                                            <?php 
                                                $roi = isset($plan['roi_daily']) ? $plan['roi_daily'] : $plan['reward_percent'];
                                                $lockup = isset($plan['lockup_period']) ? $plan['lockup_period'] : $plan['lock_period_days'];
                                            ?>
                                            <option value="<?= $plan['id'] ?>" data-min="<?= $plan['min_amount'] ?>" data-max="<?= $plan['max_amount'] ?>" data-roi="<?= $roi ?>" data-lockup="<?= $lockup ?>">
                                                <?= htmlspecialchars($plan['name']) ?> (<?= $roi ?>% daily / <?= $lockup ?> days lockup)
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group mt-3">
                                        <label for="amount">Staking Amount ($) *</label>
                                        <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" required>
                                        <small class="form-text text-muted" id="amount_range"></small>
                                    </div>
                                    
                                    <div class="form-group mt-3">
                                        <label for="daily_reward">Daily Reward</label>
                                        <input type="text" class="form-control" id="daily_reward" readonly>
                                    </div>
                                    
                                    <div class="form-group mt-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="is_compounding" name="is_compounding">
                                            <label class="custom-control-label" for="is_compounding">Enable compounding (rewards are automatically restaked)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mt-4">
                                <button type="submit" name="create_staking" class="btn btn-primary">Create Staking Position</button>
                                <a href="staking" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Initialize user select2 with AJAX
    $('#user_id').select2({
        ajax: {
            url: 'ajax/search_users.php',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data
                };
            },
            cache: true
        },
        minimumInputLength: 2,
        placeholder: 'Search for a user',
        templateResult: formatUser,
        templateSelection: formatUserSelection
    });
    
    function formatUser(user) {
        if (!user.id) return user.text;
        return $(`
            <div>
                <strong>${user.text}</strong>
                <br>
                <small>${user.email} (Balance: $${parseFloat(user.balance).toFixed(2)})</small>
            </div>
        `);
    }
    
    function formatUserSelection(user) {
        if (!user.id) return user.text;
        $('#user_balance').val('$' + parseFloat(user.balance).toFixed(2));
        return user.text;
    }
    
    // Update plan info on change
    $('#plan_id').change(function() {
        var selectedOption = $(this).find('option:selected');
        var minAmount = selectedOption.data('min');
        var maxAmount = selectedOption.data('max');
        var roi = selectedOption.data('roi');
        
        if (minAmount) {
            var rangeText = 'Min: $' + parseFloat(minAmount).toFixed(2);
            if (maxAmount > 0) {
                rangeText += ' / Max: $' + parseFloat(maxAmount).toFixed(2);
            }
            $('#amount_range').text(rangeText);
            $('#amount').attr('min', minAmount);
            if (maxAmount > 0) {
                $('#amount').attr('max', maxAmount);
            } else {
                $('#amount').removeAttr('max');
            }
        }
        
        calculateReward();
    });
    
    // Calculate daily reward
    $('#amount').on('input', calculateReward);
    
    function calculateReward() {
        var amount = parseFloat($('#amount').val());
        var roi = parseFloat($('#plan_id option:selected').data('roi'));
        
        if (!isNaN(amount) && !isNaN(roi)) {
            var dailyReward = amount * (roi / 100);
            $('#daily_reward').val('$' + dailyReward.toFixed(2) + ' per day');
        } else {
            $('#daily_reward').val('');
        }
    }
});
</script>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 