<?php
// Withdrawal page
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /user/login");
    exit();
}

$user_id = $_SESSION['user_id'];
$page_name = "Withdraw Funds";
$css_files = [];
$js_files = [];

$success_message = '';
$error_message = '';

// Get user data and balances
$stmt = $conn_back->prepare("SELECT main_balance, investment_balance, staking_balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();
$stmt->close();

$main_balance = $user_data['main_balance'] ?? 0;
$investment_balance = $user_data['investment_balance'] ?? 0;
$staking_balance = $user_data['staking_balance'] ?? 0;
$total_balance = $main_balance + $investment_balance + $staking_balance;

// Get user's withdrawal methods
$stmt = $conn_back->prepare("
    SELECT uwm.*, wm.method_name, wm.description, wm.min_amount, wm.max_amount, wm.processing_time, wm.fee_percentage, wm.fee_fixed
    FROM user_withdrawal_methods uwm
    JOIN withdrawal_methods wm ON uwm.withdrawal_method_id = wm.id
    WHERE uwm.user_id = ? AND wm.status = 'active'
    ORDER BY uwm.is_default DESC, wm.method_name ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$withdrawal_methods = $stmt->get_result();
$has_withdrawal_methods = $withdrawal_methods->num_rows > 0;
$stmt->close();

// Get user's recent withdrawals
$stmt = $conn_back->prepare("
    SELECT w.*, wm.method_name
    FROM withdrawal w
    JOIN withdrawal_methods wm ON w.withdrawal_method_id = wm.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
    LIMIT 10
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_withdrawals = $stmt->get_result();
$stmt->close();

// Process withdrawal request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_withdrawal'])) {
    $withdrawal_method_id = $_POST['withdrawal_method_id'] ?? 0;
    $amount = floatval($_POST['amount'] ?? 0);
    $from_balance = $_POST['from_balance'] ?? '';
    $pin = $_POST['pin'] ?? '';
    
    // Validate PIN
    $stmt = $conn_back->prepare("SELECT pin FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $pin_result = $stmt->get_result();
    $pin_data = $pin_result->fetch_assoc();
    $stmt->close();
    
    $valid_pin = $pin_data && $pin_data['pin'] == $pin;
    
    // Get withdrawal method details
    $stmt = $conn_back->prepare("
        SELECT uwm.*, wm.method_name, wm.min_amount, wm.max_amount, wm.fee_percentage, wm.fee_fixed
        FROM user_withdrawal_methods uwm
        JOIN withdrawal_methods wm ON uwm.withdrawal_method_id = wm.id
        WHERE uwm.id = ? AND uwm.user_id = ? AND wm.status = 'active'
    ");
    $stmt->bind_param("ii", $withdrawal_method_id, $user_id);
    $stmt->execute();
    $method_result = $stmt->get_result();
    $method_data = $method_result->fetch_assoc();
    $stmt->close();
    
    // Validate withdrawal
    $errors = [];
    
    if (!$valid_pin) {
        $errors[] = "Invalid PIN. Please check and try again.";
    }
    
    if (!$method_data) {
        $errors[] = "Invalid withdrawal method selected.";
    }
    
    if ($amount <= 0) {
        $errors[] = "Please enter a valid withdrawal amount.";
    }
    
    if ($method_data && $amount < $method_data['min_amount']) {
        $errors[] = "Minimum withdrawal amount is $" . number_format($method_data['min_amount'], 2) . ".";
    }
    
    if ($method_data && $amount > $method_data['max_amount']) {
        $errors[] = "Maximum withdrawal amount is $" . number_format($method_data['max_amount'], 2) . ".";
    }
    
    // Check selected balance
    $selected_balance = 0;
    $balance_field = '';
    
    if ($from_balance === 'main_balance') {
        $selected_balance = $main_balance;
        $balance_field = 'main_balance';
    } elseif ($from_balance === 'investment_balance') {
        $selected_balance = $investment_balance;
        $balance_field = 'investment_balance';
    } elseif ($from_balance === 'staking_balance') {
        $selected_balance = $staking_balance;
        $balance_field = 'staking_balance';
    } else {
        $errors[] = "Please select a valid balance to withdraw from.";
    }
    
    // Calculate fees
    $fee_percentage = $method_data['fee_percentage'] ?? 0;
    $fee_fixed = $method_data['fee_fixed'] ?? 0;
    $fee_amount = ($amount * $fee_percentage / 100) + $fee_fixed;
    $total_amount = $amount + $fee_amount;
    
    if ($total_amount > $selected_balance) {
        $errors[] = "Insufficient funds in selected balance. Please enter a smaller amount or select a different balance.";
    }
    
    if (empty($errors)) {
        // Begin transaction
        $conn_back->begin_transaction();
        
        try {
            // Generate transaction ID
            $transaction_id = 'WD' . time() . strtoupper(substr(md5(uniqid()), 0, 6));
            
            // Update user balance
            $new_balance = $selected_balance - $total_amount;
            $stmt = $conn_back->prepare("UPDATE users SET $balance_field = ? WHERE id = ?");
            $stmt->bind_param("di", $new_balance, $user_id);
            $stmt->execute();
            $stmt->close();
            
            // Create withdrawal record
            $stmt = $conn_back->prepare("
                INSERT INTO withdrawal (
                    user_id, amount, currency, withdrawal_method_id, transaction_id, 
                    status, withdrawal_address, transaction_proof_id, 
                    user_balance_before_withdrawal, user_balance_after_withdrawal, fee_amount
                ) VALUES (?, ?, 'USD', ?, ?, 'pending', ?, ?, ?, ?, ?)
            ");
            
            $transaction_proof_id = 'WDPROOF' . time();
            
            $stmt->bind_param(
                "idissdddd", 
                $user_id, 
                $amount, 
                $method_data['withdrawal_method_id'],
                $transaction_id, 
                $method_data['account_details'], 
                $transaction_proof_id, 
                $selected_balance, 
                $new_balance,
                $fee_amount
            );
            $stmt->execute();
            $withdrawal_id = $conn_back->insert_id;
            $stmt->close();
            
            // Create transaction record
            $stmt = $conn_back->prepare("
                INSERT INTO transactions (
                    user_id, transaction_type, reference_id, transaction_proof_id, 
                    amount, currency, status, date_time, description
                ) VALUES (?, 'withdrawal', ?, ?, ?, 'USD', 'pending', NOW(), ?)
            ");
            
            $description = "Withdrawal via " . $method_data['method_name'] . " - Fees: $" . number_format($fee_amount, 2);
            
            $stmt->bind_param(
                "issds", 
                $user_id, 
                $transaction_id, 
                $transaction_proof_id, 
                $amount, 
                $description
            );
            $stmt->execute();
            $stmt->close();
            
            // Commit transaction
            $conn_back->commit();
            
            $success_message = "Your withdrawal request for $" . number_format($amount, 2) . " has been submitted successfully and is pending approval.";
            
            // Refresh user data
            $stmt = $conn_back->prepare("SELECT main_balance, investment_balance, staking_balance FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user_result = $stmt->get_result();
            $user_data = $user_result->fetch_assoc();
            $stmt->close();
            
            $main_balance = $user_data['main_balance'] ?? 0;
            $investment_balance = $user_data['investment_balance'] ?? 0;
            $staking_balance = $user_data['staking_balance'] ?? 0;
            $total_balance = $main_balance + $investment_balance + $staking_balance;
            
            // Refresh recent withdrawals
            $stmt = $conn_back->prepare("
                SELECT w.*, wm.method_name
                FROM withdrawal w
                JOIN withdrawal_methods wm ON w.withdrawal_method_id = wm.id
                WHERE w.user_id = ?
                ORDER BY w.created_at DESC
                LIMIT 10
            ");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $recent_withdrawals = $stmt->get_result();
            $stmt->close();
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn_back->rollback();
            $error_message = "An error occurred: " . $e->getMessage();
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/header.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/breadcumb.php';
?>

<!-- Withdrawal Page -->
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-12">
            <div class="card card-body mb-4">
                <div class="row mb-3">
                    <div class="col">
                        <h4 class="fw-medium">Withdraw Funds</h4>
                        <p class="text-muted">Request a withdrawal from your account.</p>
                    </div>
                </div>
                
                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $success_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $error_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <!-- Balances and Withdrawal Form -->
                    <div class="col-lg-8">
                        <!-- Balance Cards -->
                        <div class="row mb-4">
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card shadow-sm h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="avatar avatar-40 rounded-circle bg-primary-subtle me-2">
                                                <i class="bi bi-wallet fs-4 text-primary"></i>
                                            </div>
                                            <h6 class="card-title mb-0">Main Balance</h6>
                                        </div>
                                        <h4 class="mb-0">$<?= number_format($main_balance, 2) ?></h4>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card shadow-sm h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="avatar avatar-40 rounded-circle bg-success-subtle me-2">
                                                <i class="bi bi-graph-up fs-4 text-success"></i>
                                            </div>
                                            <h6 class="card-title mb-0">Investment Balance</h6>
                                        </div>
                                        <h4 class="mb-0">$<?= number_format($investment_balance, 2) ?></h4>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card shadow-sm h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="avatar avatar-40 rounded-circle bg-info-subtle me-2">
                                                <i class="bi bi-lock fs-4 text-info"></i>
                                            </div>
                                            <h6 class="card-title mb-0">Staking Balance</h6>
                                        </div>
                                        <h4 class="mb-0">$<?= number_format($staking_balance, 2) ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Withdrawal Form -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Request Withdrawal</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!$has_withdrawal_methods): ?>
                                    <div class="text-center p-4">
                                        <div class="avatar avatar-60 rounded-circle bg-light mb-3">
                                            <i class="bi bi-wallet2 fs-2"></i>
                                        </div>
                                        <h5>No Withdrawal Methods Available</h5>
                                        <p class="text-muted">You need to add at least one withdrawal method before you can request a withdrawal.</p>
                                        <a href="/user/withdrawal_methods" class="btn btn-primary">
                                            <i class="bi bi-plus-circle me-2"></i> Add Withdrawal Method
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <form method="post" id="withdrawalForm">
                                        <div class="mb-3">
                                            <label for="withdrawal_method_id" class="form-label">Withdrawal Method</label>
                                            <select class="form-select" id="withdrawal_method_id" name="withdrawal_method_id" required>
                                                <option value="">Select a withdrawal method</option>
                                                <?php 
                                                $withdrawal_methods->data_seek(0);
                                                while ($method = $withdrawal_methods->fetch_assoc()): 
                                                ?>
                                                    <option value="<?= $method['id'] ?>" 
                                                            data-min="<?= $method['min_amount'] ?>" 
                                                            data-max="<?= $method['max_amount'] ?>"
                                                            data-fee-percent="<?= $method['fee_percentage'] ?>"
                                                            data-fee-fixed="<?= $method['fee_fixed'] ?>"
                                                            data-details="<?= htmlspecialchars($method['account_details']) ?>">
                                                        <?= htmlspecialchars($method['method_name']) ?> 
                                                        <?= $method['is_default'] ? '(Default)' : '' ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                            <div class="form-text" id="method_details"></div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="from_balance" class="form-label">Withdraw From</label>
                                            <select class="form-select" id="from_balance" name="from_balance" required>
                                                <option value="">Select balance</option>
                                                <option value="main_balance" data-balance="<?= $main_balance ?>">Main Balance ($<?= number_format($main_balance, 2) ?>)</option>
                                                <option value="investment_balance" data-balance="<?= $investment_balance ?>">Investment Balance ($<?= number_format($investment_balance, 2) ?>)</option>
                                                <option value="staking_balance" data-balance="<?= $staking_balance ?>">Staking Balance ($<?= number_format($staking_balance, 2) ?>)</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="amount" class="form-label">Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" placeholder="0.00" required>
                                            </div>
                                            <div class="form-text">
                                                <span id="min_amount"></span>
                                                <span id="max_amount"></span>
                                            </div>
                                        </div>
                                        
                                        <div class="card bg-light mb-3">
                                            <div class="card-body p-3">
                                                <h6 class="card-title">Fee Details</h6>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <p class="mb-1">Amount:</p>
                                                        <h6 id="display_amount">$0.00</h6>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <p class="mb-1">Fee:</p>
                                                        <h6 id="display_fee">$0.00</h6>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <p class="mb-1">Total:</p>
                                                        <h6 id="display_total">$0.00</h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="pin" class="form-label">Enter Your PIN</label>
                                            <input type="password" class="form-control" id="pin" name="pin" inputmode="numeric" pattern="[0-9]*" maxlength="6" required>
                                            <div class="form-text">Please enter your account PIN to confirm this withdrawal.</div>
                                        </div>
                                        
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle me-2"></i>
                                            <strong>Important:</strong> Withdrawal requests are processed within 24-48 hours. Please ensure your withdrawal method details are correct.
                                        </div>
                                        
                                        <div class="d-grid">
                                            <button type="submit" name="submit_withdrawal" class="btn btn-primary">
                                                <i class="bi bi-cash-coin me-2"></i> Request Withdrawal
                                            </button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Withdrawals -->
                    <div class="col-lg-4">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Recent Withdrawals</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if ($recent_withdrawals->num_rows > 0): ?>
                                    <div class="list-group list-group-flush">
                                        <?php while ($withdrawal = $recent_withdrawals->fetch_assoc()): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">$<?= number_format($withdrawal['amount'], 2) ?></h6>
                                                        <p class="small text-muted mb-0">
                                                            <?= htmlspecialchars($withdrawal['method_name']) ?> - 
                                                            <?= date('M d, Y', strtotime($withdrawal['created_at'])) ?>
                                                        </p>
                                                    </div>
                                                    <span class="badge <?php
                                                        switch ($withdrawal['status']) {
                                                            case 'pending': echo 'bg-warning'; break;
                                                            case 'approved': echo 'bg-info'; break;
                                                            case 'completed': echo 'bg-success'; break;
                                                            case 'rejected': echo 'bg-danger'; break;
                                                            default: echo 'bg-secondary';
                                                        }
                                                    ?>">
                                                        <?= ucfirst($withdrawal['status']) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center p-4">
                                        <div class="avatar avatar-50 rounded-circle bg-light mb-3">
                                            <i class="bi bi-clock-history fs-4"></i>
                                        </div>
                                        <h6>No Recent Withdrawals</h6>
                                        <p class="text-muted small">Your withdrawal history will appear here.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const withdrawalMethodSelect = document.getElementById('withdrawal_method_id');
    const fromBalanceSelect = document.getElementById('from_balance');
    const amountInput = document.getElementById('amount');
    const methodDetailsElement = document.getElementById('method_details');
    const minAmountElement = document.getElementById('min_amount');
    const maxAmountElement = document.getElementById('max_amount');
    const displayAmountElement = document.getElementById('display_amount');
    const displayFeeElement = document.getElementById('display_fee');
    const displayTotalElement = document.getElementById('display_total');
    
    // Format number as currency
    function formatCurrency(value) {
        return '$' + parseFloat(value).toFixed(2);
    }
    
    // Calculate and display fee details
    function updateFeeDetails() {
        const selectedMethod = withdrawalMethodSelect.options[withdrawalMethodSelect.selectedIndex];
        const amount = parseFloat(amountInput.value) || 0;
        
        if (selectedMethod && selectedMethod.value) {
            const feePercent = parseFloat(selectedMethod.dataset.feePercent) || 0;
            const feeFixed = parseFloat(selectedMethod.dataset.feeFixed) || 0;
            
            const percentFee = amount * (feePercent / 100);
            const totalFee = percentFee + feeFixed;
            const total = amount + totalFee;
            
            displayAmountElement.textContent = formatCurrency(amount);
            displayFeeElement.textContent = formatCurrency(totalFee);
            displayTotalElement.textContent = formatCurrency(total);
            
            // Check if amount is within balance
            const selectedBalance = fromBalanceSelect.options[fromBalanceSelect.selectedIndex];
            if (selectedBalance && selectedBalance.value) {
                const availableBalance = parseFloat(selectedBalance.dataset.balance) || 0;
                
                if (total > availableBalance) {
                    displayTotalElement.classList.add('text-danger');
                } else {
                    displayTotalElement.classList.remove('text-danger');
                }
            }
        } else {
            displayAmountElement.textContent = '$0.00';
            displayFeeElement.textContent = '$0.00';
            displayTotalElement.textContent = '$0.00';
            displayTotalElement.classList.remove('text-danger');
        }
    }
    
    // Update method details when selection changes
    withdrawalMethodSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            const minAmount = parseFloat(selectedOption.dataset.min) || 0;
            const maxAmount = parseFloat(selectedOption.dataset.max) || 0;
            const accountDetails = selectedOption.dataset.details || '';
            
            methodDetailsElement.textContent = 'Account: ' + accountDetails;
            minAmountElement.textContent = 'Min: $' + minAmount.toFixed(2);
            maxAmountElement.textContent = ' | Max: $' + maxAmount.toFixed(2);
            
            // Update amount input constraints
            amountInput.min = minAmount;
            amountInput.max = maxAmount;
            
            // Set a default amount if empty
            if (!amountInput.value) {
                amountInput.value = minAmount.toFixed(2);
            }
        } else {
            methodDetailsElement.textContent = '';
            minAmountElement.textContent = '';
            maxAmountElement.textContent = '';
        }
        
        updateFeeDetails();
    });
    
    // Update fee details when amount changes
    amountInput.addEventListener('input', updateFeeDetails);
    
    // Update available amount when balance changes
    fromBalanceSelect.addEventListener('change', updateFeeDetails);
    
    // Form validation
    document.getElementById('withdrawalForm')?.addEventListener('submit', function(e) {
        const selectedMethod = withdrawalMethodSelect.options[withdrawalMethodSelect.selectedIndex];
        const selectedBalance = fromBalanceSelect.options[fromBalanceSelect.selectedIndex];
        const amount = parseFloat(amountInput.value) || 0;
        
        if (!selectedMethod || !selectedMethod.value) {
            e.preventDefault();
            alert('Please select a withdrawal method.');
            return;
        }
        
        if (!selectedBalance || !selectedBalance.value) {
            e.preventDefault();
            alert('Please select a balance to withdraw from.');
            return;
        }
        
        if (amount <= 0) {
            e.preventDefault();
            alert('Please enter a valid withdrawal amount.');
            return;
        }
        
        const minAmount = parseFloat(selectedMethod.dataset.min) || 0;
        const maxAmount = parseFloat(selectedMethod.dataset.max) || 0;
        
        if (amount < minAmount) {
            e.preventDefault();
            alert('Minimum withdrawal amount is $' + minAmount.toFixed(2));
            return;
        }
        
        if (amount > maxAmount) {
            e.preventDefault();
            alert('Maximum withdrawal amount is $' + maxAmount.toFixed(2));
            return;
        }
        
        const feePercent = parseFloat(selectedMethod.dataset.feePercent) || 0;
        const feeFixed = parseFloat(selectedMethod.dataset.feeFixed) || 0;
        const totalFee = (amount * (feePercent / 100)) + feeFixed;
        const total = amount + totalFee;
        
        const availableBalance = parseFloat(selectedBalance.dataset.balance) || 0;
        
        if (total > availableBalance) {
            e.preventDefault();
            alert('Insufficient funds. Total amount (including fees) exceeds your available balance.');
            return;
        }
    });
});
</script>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/footer.php'; ?> 