<?php
// Admin Adjust User Balance Page
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Adjust User Balance";
$current_page = "adjust_balance.php";
$message = "";
$error = "";

// Get users for dropdown
$users_query = "SELECT id, CONCAT(first_name, ' ', last_name, ' (', email, ')') as user_name, 
                main_balance, investment_balance, staking_balance 
                FROM users 
                ORDER BY first_name, last_name";
$users_result = $conn_back->query($users_query);
$users = [];
while ($users_result && $user = $users_result->fetch_assoc()) {
    $users[] = $user;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adjust_balance'])) {
    $user_id = (int)$_POST['user_id'];
    $balance_type = $_POST['balance_type']; // main_balance, investment_balance, staking_balance
    $adjustment_type = $_POST['adjustment_type']; // add, subtract
    $amount = (float)$_POST['amount'];
    $reason = trim($_POST['reason']);
    $admin_id = $_SESSION['admin_id'];
    
    // Validate inputs
    if ($user_id <= 0) {
        $error = "Please select a valid user.";
    } elseif (!in_array($balance_type, ['main_balance', 'investment_balance', 'staking_balance'])) {
        $error = "Invalid balance type selected.";
    } elseif (!in_array($adjustment_type, ['add', 'subtract'])) {
        $error = "Invalid adjustment type selected.";
    } elseif ($amount <= 0) {
        $error = "Amount must be greater than zero.";
    } elseif (empty($reason)) {
        $error = "Please provide a reason for this adjustment.";
    } else {
        // Begin transaction
        $conn_back->begin_transaction();
        
        try {
            // Get current balances
            $stmt = $conn_back->prepare("SELECT main_balance, investment_balance, staking_balance FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user_data = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if (!$user_data) {
                throw new Exception("User not found.");
            }
            
            $current_balance = $user_data[$balance_type];
            
            // Calculate new balance
            if ($adjustment_type === 'add') {
                $new_balance = $current_balance + $amount;
                $transaction_type = 'admin_credit';
            } else { // subtract
                if ($current_balance < $amount) {
                    throw new Exception("Insufficient balance for deduction.");
                }
                $new_balance = $current_balance - $amount;
                $transaction_type = 'admin_debit';
            }
            
            // Update user balance
            $stmt = $conn_back->prepare("UPDATE users SET $balance_type = ? WHERE id = ?");
            $stmt->bind_param("di", $new_balance, $user_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update user balance: " . $stmt->error);
            }
            
            $stmt->close();
            
            // Format balance type name for display
            $balance_name = str_replace('_', ' ', $balance_type);
            $balance_name = ucwords($balance_name);
            
            // Create transaction record
            $reference_id = strtoupper($transaction_type) . time();
            $description = "Admin {$adjustment_type}ed \${$amount} to {$balance_name}: {$reason}";
            
            $stmt = $conn_back->prepare("
                INSERT INTO transactions (
                    user_id, amount, transaction_type, reference_id, 
                    currency, status, date_time, description
                ) VALUES (?, ?, ?, ?, '$', 'completed', NOW(), ?)
            ");
            $stmt->bind_param("idsss", $user_id, $amount, $transaction_type, $reference_id, $description);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create transaction record: " . $stmt->error);
            }
            
            $stmt->close();
            
            // Log admin action
            $action = "Admin {$adjustment_type}ed \${$amount} to user #{$user_id}'s {$balance_name}: {$reason}";
            $ip = $_SERVER['REMOTE_ADDR'];
            
            $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
            $log_stmt->bind_param("iss", $admin_id, $action, $ip);
            $log_stmt->execute();
            $log_stmt->close();
            
            $conn_back->commit();
            $message = "User balance adjusted successfully!";
            
        } catch (Exception $e) {
            $conn_back->rollback();
            $error = "Error adjusting balance: " . $e->getMessage();
        }
    }
}

include_once __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Adjust User Balance</h1>
        <a href="users.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Users
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
            <h6 class="m-0 font-weight-bold text-primary">Adjust User Balance</h6>
        </div>
        <div class="card-body">
            <form method="post" id="adjustBalanceForm">
                <div class="form-group">
                    <label for="user_id">Select User:</label>
                    <select class="form-control user-select" id="user_id" name="user_id" required>
                        <option value="">-- Select User --</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>" 
                                    data-main="<?= $user['main_balance'] ?>" 
                                    data-investment="<?= $user['investment_balance'] ?>"
                                    data-staking="<?= $user['staking_balance'] ?>">
                                <?= htmlspecialchars($user['user_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Main Balance</h5>
                                <p class="card-text h3 text-primary" id="main_balance_display">$0.00</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Investment Balance</h5>
                                <p class="card-text h3 text-info" id="investment_balance_display">$0.00</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Staking Balance</h5>
                                <p class="card-text h3 text-success" id="staking_balance_display">$0.00</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Balance Type:</label>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="main_balance" name="balance_type" value="main_balance" class="custom-control-input" checked required>
                                <label class="custom-control-label" for="main_balance">Main Balance</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="investment_balance" name="balance_type" value="investment_balance" class="custom-control-input" required>
                                <label class="custom-control-label" for="investment_balance">Investment Balance</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="staking_balance" name="balance_type" value="staking_balance" class="custom-control-input" required>
                                <label class="custom-control-label" for="staking_balance">Staking Balance</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Adjustment Type:</label>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="add_balance" name="adjustment_type" value="add" class="custom-control-input" checked required>
                                <label class="custom-control-label" for="add_balance">Add Funds</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="subtract_balance" name="adjustment_type" value="subtract" class="custom-control-input" required>
                                <label class="custom-control-label" for="subtract_balance">Subtract Funds</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="amount">Amount:</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="amount" name="amount" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="reason">Reason for Adjustment:</label>
                    <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                    <small class="form-text text-muted">Provide a clear explanation for this balance adjustment.</small>
                </div>
                
                <hr>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Important:</strong> Balance adjustments are logged and cannot be reversed. Please verify all information before submitting.
                </div>
                
                <button type="submit" name="adjust_balance" class="btn btn-primary">Submit Adjustment</button>
                <a href="users.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // If select2 is available, enhance the dropdown
    if ($.fn.select2) {
        $('.user-select').select2({
            placeholder: "Select a user",
            allowClear: true
        });
    }
    
    // Update balance displays when user changes
    $('#user_id').change(function() {
        var option = $(this).find('option:selected');
        var mainBalance = option.data('main') || 0;
        var investmentBalance = option.data('investment') || 0;
        var stakingBalance = option.data('staking') || 0;
        
        $('#main_balance_display').text('$' + parseFloat(mainBalance).toFixed(2));
        $('#investment_balance_display').text('$' + parseFloat(investmentBalance).toFixed(2));
        $('#staking_balance_display').text('$' + parseFloat(stakingBalance).toFixed(2));
    });
    
    // Form validation
    $('#adjustBalanceForm').submit(function(e) {
        var userId = $('#user_id').val();
        var amount = parseFloat($('#amount').val());
        var reason = $('#reason').val().trim();
        
        if (!userId) {
            alert('Please select a user.');
            e.preventDefault();
            return false;
        }
        
        if (isNaN(amount) || amount <= 0) {
            alert('Please enter a valid amount greater than zero.');
            e.preventDefault();
            return false;
        }
        
        if (!reason) {
            alert('Please provide a reason for this adjustment.');
            e.preventDefault();
            return false;
        }
        
        // Confirm the action
        if (!confirm('Are you sure you want to proceed with this balance adjustment?')) {
            e.preventDefault();
            return false;
        }
    });
});
</script>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 