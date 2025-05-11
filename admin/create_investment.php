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

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_investment'])) {
        $user_id = intval($_POST['user_id']);
        $plan_id = intval($_POST['plan_id']);
        $amount = floatval($_POST['amount']);
        
        // Validate inputs
        if ($user_id <= 0) {
            $error = "Please select a valid user.";
        } elseif ($plan_id <= 0) {
            $error = "Please select a valid investment plan.";
        } elseif ($amount <= 0) {
            $error = "Please enter a valid amount greater than zero.";
        } else {
            // Get plan details
            $stmt = $conn_back->prepare("SELECT * FROM investment_plans WHERE id = ?");
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
                $error = "Selected investment plan does not exist.";
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
                    // Calculate investment details
                    $roi_percent = $plan['roi_percent'];
                    $duration_days = $plan['duration_days'];
                    $expected_returns = $amount * (1 + ($roi_percent / 100));
                    
                    // Set dates
                    $started_at = date('Y-m-d H:i:s');
                    $ends_at = date('Y-m-d H:i:s', strtotime($started_at . " + $duration_days days"));
                    
                    // Create investment record
                    $stmt = $conn_back->prepare("
                        INSERT INTO investments (
                            user_id, plan_id, amount, expected_returns, 
                            status, started_at, ends_at, created_at
                        ) VALUES (?, ?, ?, ?, 'active', ?, ?, NOW())
                    ");
                    $stmt->bind_param("iiddss", $user_id, $plan_id, $amount, $expected_returns, $started_at, $ends_at);
                    $stmt->execute();
                    $investment_id = $conn_back->insert_id;
                    $stmt->close();
                    
                    // Deduct from user balance if not bypassed
                    if (!isset($_POST['bypass_balance_check'])) {
                        $stmt = $conn_back->prepare("UPDATE users SET main_balance = main_balance - ? WHERE id = ?");
                        $stmt->bind_param("di", $amount, $user_id);
                        $stmt->execute();
                        $stmt->close();
                    }
                    
                    // Create transaction record
                    $description = "Investment in " . $plan['name'] . " plan";
                    $stmt = $conn_back->prepare("
                        INSERT INTO transactions (
                            user_id, amount, transaction_type, status, 
                            description, date_time
                        ) VALUES (?, ?, 'investment', 'completed', ?, NOW())
                    ");
                    $stmt->bind_param("ids", $user_id, $amount, $description);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Log admin activity
                    $admin_id = $_SESSION['admin_id'];
                    $action = "Created investment #$investment_id for user #$user_id in plan " . $plan['name'] . " for $" . number_format($amount, 2);
                    $ip = $_SERVER['REMOTE_ADDR'];
                    
                    $stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $admin_id, $action, $ip);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Commit transaction
                    $conn_back->commit();
                    
                    $message = "Investment created successfully for " . $user['username'] . " in " . $plan['name'] . " plan.";
                } catch (Exception $e) {
                    // Rollback transaction on error
                    $conn_back->rollback();
                    $error = "Error creating investment: " . $e->getMessage();
                }
            }
        }
    }
}

// Get investment plans
$plans_result = $conn_back->query("SELECT * FROM investment_plans WHERE status = 1 ORDER BY name");
$plans = [];
if ($plans_result->num_rows > 0) {
    while ($row = $plans_result->fetch_assoc()) {
        $plans[] = $row;
    }
}

include_once __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create Investment</h1>
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
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Create New Investment</h6>
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
                                        <label class="custom-control-label" for="bypass_balance_check">Bypass balance check (create investment without deducting from balance)</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="plan_id">Select Investment Plan *</label>
                                    <select class="form-control" id="plan_id" name="plan_id" required>
                                        <option value="">Select Plan</option>
                                        <?php foreach ($plans as $plan): ?>
                                        <option value="<?= $plan['id'] ?>" data-min="<?= $plan['min_amount'] ?>" data-max="<?= $plan['max_amount'] ?>" data-roi="<?= $plan['roi_percent'] ?>" data-duration="<?= $plan['duration_days'] ?>">
                                            <?= htmlspecialchars($plan['name']) ?> (<?= $plan['roi_percent'] ?>% / <?= $plan['duration_days'] ?> days)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group mt-3">
                                    <label for="amount">Investment Amount ($) *</label>
                                    <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" required>
                                    <small class="form-text text-muted" id="amount_range"></small>
                                </div>
                                
                                <div class="form-group mt-3">
                                    <label for="expected_return">Expected Return</label>
                                    <input type="text" class="form-control" id="expected_return" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" name="create_investment" class="btn btn-primary">Create Investment</button>
                            <a href="investments" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
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
        
        calculateReturn();
    });
    
    // Calculate expected return
    $('#amount').on('input', calculateReturn);
    
    function calculateReturn() {
        var amount = parseFloat($('#amount').val());
        var roi = parseFloat($('#plan_id option:selected').data('roi'));
        
        if (!isNaN(amount) && !isNaN(roi)) {
            var expectedReturn = amount * (1 + (roi / 100));
            $('#expected_return').val('$' + expectedReturn.toFixed(2));
        } else {
            $('#expected_return').val('');
        }
    }
});
</script>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 