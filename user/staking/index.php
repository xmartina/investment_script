<?php
// Main staking page
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /user/login");
    exit();
}

$user_id = $_SESSION['user_id'];
$page_name = "Staking Dashboard";

// Get user data
$stmt = $conn_back->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get staking plans
$query = "SELECT * FROM staking_plans WHERE is_active = 1 ORDER BY min_amount ASC";
$staking_plans = $conn_back->query($query)->fetch_all(MYSQLI_ASSOC);

// Get user's active staking positions
$stmt = $conn_back->prepare("
    SELECT s.*, sp.name as plan_name, sp.description as plan_description 
    FROM staking s
    JOIN staking_plans sp ON s.plan_id = sp.id
    WHERE s.user_id = ? AND s.status = 'active'
    ORDER BY s.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$active_stakings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get user's completed/cancelled staking positions
$stmt = $conn_back->prepare("
    SELECT s.*, sp.name as plan_name 
    FROM staking s
    JOIN staking_plans sp ON s.plan_id = sp.id
    WHERE s.user_id = ? AND s.status != 'active'
    ORDER BY s.created_at DESC
    LIMIT 10
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$past_stakings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get user's pending rewards
$stmt = $conn_back->prepare("
    SELECT SUM(reward_amount) as total_pending
    FROM staking_rewards
    WHERE user_id = ? AND status = 'pending'
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$pending_rewards = $result['total_pending'] ?? 0;
$stmt->close();

// Process form submission for new staking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_staking'])) {
    $plan_id = $_POST['plan_id'];
    $amount = $_POST['amount'];
    $is_compounding = isset($_POST['is_compounding']) ? 1 : 0;
    $balance_source = $_POST['balance_source'];
    $user_pin = $_POST['pin'];
    
    // Validate inputs
    $errors = [];
    
    // Verify PIN first
    $pin_query = "SELECT pin FROM users WHERE id = ?";
    $stmt = $conn_back->prepare($pin_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $pin_result = $stmt->get_result();
    $pin_data = $pin_result->fetch_assoc();
    $stmt->close();
    
    if (!$pin_data || $pin_data['pin'] != $user_pin) {
        $errors[] = "Incorrect PIN. Please try again.";
    }
    
    // Check if plan exists and is active
    $stmt = $conn_back->prepare("SELECT * FROM staking_plans WHERE id = ? AND is_active = 1");
    $stmt->bind_param("i", $plan_id);
    $stmt->execute();
    $plan = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$plan) {
        $errors[] = "Invalid staking plan selected.";
    } else {
        // Check amount limits
        if ($amount < $plan['min_amount']) {
            $errors[] = "Amount is below the minimum requirement of " . $plan['min_amount'];
        }
        if ($plan['max_amount'] > 0 && $amount > $plan['max_amount']) {
            $errors[] = "Amount exceeds the maximum limit of " . $plan['max_amount'];
        }
        
        // Get user's current balances
        $user_query = "SELECT main_balance, investment_balance, staking_balance FROM users WHERE id = ?";
        $stmt = $conn_back->prepare($user_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user_balances = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        // Check if selected balance has enough funds
        $sufficient_funds = false;
        switch ($balance_source) {
            case 'main_balance':
                $sufficient_funds = (float)$user_balances['main_balance'] >= $amount;
                $balance_field = 'main_balance';
                $balance_name = 'Main Balance';
                break;
            case 'investment_balance':
                $sufficient_funds = (float)$user_balances['investment_balance'] >= $amount;
                $balance_field = 'investment_balance';
                $balance_name = 'Investment Balance';
                break;
            case 'staking_balance':
                $sufficient_funds = (float)$user_balances['staking_balance'] >= $amount;
                $balance_field = 'staking_balance';
                $balance_name = 'Staking Balance';
                break;
            default:
                $errors[] = "Invalid balance source selected.";
                break;
        }
        
        if (isset($balance_field) && !$sufficient_funds) {
            $errors[] = "Insufficient funds in your {$balance_name}. Your current {$balance_name} is " . $user_balances[$balance_field];
        }
    }
    
    if (empty($errors)) {
        // Begin transaction
        $conn_back->begin_transaction();
        
        try {
            // Create staking record
            $now = date('Y-m-d H:i:s');
            $start_date = $now;
            $end_date = date('Y-m-d H:i:s', strtotime($now . ' + ' . $plan['duration_days'] . ' days'));
            $unstake_date = date('Y-m-d H:i:s', strtotime($now . ' + ' . $plan['lock_period_days'] . ' days'));
            
            $stmt = $conn_back->prepare("
                INSERT INTO staking (
                    user_id, plan_id, amount, duration_days, reward_percent, 
                    is_compounding, status, started_at, ends_at, unstake_available_at, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                "iidiissss", 
                $user_id, $plan_id, $amount, $plan['duration_days'], 
                $plan['reward_percent'], $is_compounding, $start_date, $end_date, $unstake_date, $now
            );
            $stmt->execute();
            $staking_id = $conn_back->insert_id;
            $stmt->close();
            
            // Deduct amount from the selected balance
            $stmt = $conn_back->prepare("UPDATE users SET {$balance_field} = {$balance_field} - ? WHERE id = ?");
            $stmt->bind_param("di", $amount, $user_id);
            $stmt->execute();
            $stmt->close();
            
            // Add transaction record
            $reference = 'STAKE' . time() . $user_id;
            $description = "Staking {$amount} in {$plan['name']} plan from {$balance_name}";
            
            $stmt = $conn_back->prepare("
                INSERT INTO transactions (user_id, type, amount, status, reference, description, created_at)
                VALUES (?, 'staking', ?, 'successful', ?, ?, ?)
            ");
            $stmt->bind_param("idsss", $user_id, $amount, $reference, $description, $now);
            $stmt->execute();
            $stmt->close();
            
            // Commit transaction
            $conn_back->commit();
            
            // Redirect to prevent form resubmission
            header("Location: /user/staking/dashboard?success=1");
            exit();
        } catch (Exception $e) {
            // Rollback if an error occurs
            $conn_back->rollback();
            $errors[] = "An error occurred: " . $e->getMessage();
        }
    }
}

?>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/header.php'; 
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/breadcumb.php';
    ?>

<!-- Staking Dashboard -->
<div class="container-xl px-4 mt-4">
    <nav class="nav nav-borders">
        <a class="nav-link active ms-0" href="/user/staking">Staking Dashboard</a>
        <a class="nav-link" href="/user/staking/dashboard">My Staking</a>
        <a class="nav-link" href="/user/staking/rewards">Rewards</a>
    </nav>
    
    <hr class="mt-0 mb-4">
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            Your staking position has been created successfully!
        </div>
    <?php endif; ?>
    
    <!-- Staking Overview -->
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card h-100 border-left-primary shadow py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Staked
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                $total_staked = 0;
                                foreach ($active_stakings as $staking) {
                                    $total_staked += $staking['amount'];
                                }
                                echo '$' . number_format($total_staked, 2);
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card h-100 border-left-success shadow py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Pending Rewards
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($pending_rewards, 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-award fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card h-100 border-left-info shadow py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Active Positions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count($active_stakings) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Staking Plans -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Available Staking Plans</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($staking_plans as $plan): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card border-0 shadow h-100">
                                    <div class="card-body">
                                        <div class="text-center">
                                            <h5 class="fw-bolder"><?= htmlspecialchars($plan['name']) ?></h5>
                                            <span class="badge bg-primary mb-2">
                                                <?= number_format($plan['reward_percent'], 2) ?>% return
                                            </span>
                                            <div class="d-flex justify-content-center small text-warning mb-2">
                                                <?php for ($i = 0; $i < ceil($plan['reward_percent'] / 5); $i++): ?>
                                                    <div class="bi-star-fill"></div>
                                                <?php endfor; ?>
                                            </div>
                                            <p class="card-text">
                                                <?= htmlspecialchars($plan['description']) ?>
                                            </p>
                                        </div>
                                        <ul class="list-group list-group-flush mb-3">
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Lock Period:</span>
                                                <strong><?= $plan['lock_period_days'] ?> days</strong>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Duration:</span>
                                                <strong><?= $plan['duration_days'] ?> days</strong>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Min Amount:</span>
                                                <strong>$<?= number_format($plan['min_amount'], 2) ?></strong>
                                            </li>
                                            <?php if ($plan['max_amount'] > 0): ?>
                                                <li class="list-group-item d-flex justify-content-between">
                                                    <span>Max Amount:</span>
                                                    <strong>$<?= number_format($plan['max_amount'], 2) ?></strong>
                                                </li>
                                            <?php endif; ?>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Early Unstake Fee:</span>
                                                <strong><?= number_format($plan['early_unstake_penalty'], 2) ?>%</strong>
                                            </li>
                                        </ul>
                                        <div class="text-center">
                                            <button type="button" class="btn btn-primary" 
                                                    data-bs-toggle="modal" data-bs-target="#stakingModal<?= $plan['id'] ?>">
                                                Stake Now
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Staking Modal -->
                            <div class="modal fade" id="stakingModal<?= $plan['id'] ?>" tabindex="-1" 
                                 aria-labelledby="stakingModalLabel<?= $plan['id'] ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="stakingModalLabel<?= $plan['id'] ?>">
                                                Stake in <?= htmlspecialchars($plan['name']) ?>
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="post">
                                            <div class="modal-body">
                                                <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                                                
                                                <div class="mb-3">
                                                    <label for="amount<?= $plan['id'] ?>" class="form-label">Amount to Stake</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">$</span>
                                                        <input type="number" class="form-control" id="amount<?= $plan['id'] ?>" 
                                                               name="amount" min="<?= $plan['min_amount'] ?>" 
                                                               <?php if ($plan['max_amount'] > 0): ?>
                                                               max="<?= $plan['max_amount'] ?>"
                                                               <?php endif; ?>
                                                               step="0.01" required>
                                                    </div>
                                                    <div class="form-text">
                                                        Min: $<?= number_format($plan['min_amount'], 2) ?>
                                                        <?php if ($plan['max_amount'] > 0): ?>
                                                            | Max: $<?= number_format($plan['max_amount'], 2) ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="calculatedReward<?= $plan['id'] ?>" class="form-label">
                                                        Estimated Reward
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">$</span>
                                                        <input type="text" class="form-control" 
                                                               id="calculatedReward<?= $plan['id'] ?>" readonly>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="balanceSource<?= $plan['id'] ?>" class="form-label">Select Balance Source</label>
                                                    <select class="form-select" id="balanceSource<?= $plan['id'] ?>" name="balance_source" required>
                                                        <option value="main_balance">Main Balance
                                                            (<?=$user_currency?><?=number_format($main_balance, 2)?>)</option>
                                                        <option value="investment_balance">Investment Balance
                                                            (<?=$user_currency?><?=number_format($investment_balance, 2)?>)</option>
                                                        <option value="staking_balance">Staking Balance
                                                            (<?=$user_currency?><?=number_format($staking_balance, 2)?>)</option>
                                                    </select>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="1" 
                                                               id="compounding<?= $plan['id'] ?>" name="is_compounding">
                                                        <label class="form-check-label" for="compounding<?= $plan['id'] ?>">
                                                            Enable Compounding (Reinvest rewards automatically)
                                                        </label>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="userPin<?= $plan['id'] ?>" class="form-label">Enter Your PIN</label>
                                                    <input type="password" class="w-75 form-control" id="userPin<?= $plan['id'] ?>" name="pin" 
                                                           inputmode="numeric" pattern="[0-9]*" maxlength="6" required>
                                                    <small class="text-muted">Please enter your account PIN to confirm this staking operation</small>
                                                </div>
                                                
                                                <div class="alert alert-info">
                                                    <ul class="mb-0">
                                                        <li>Lock period: <?= $plan['lock_period_days'] ?> days</li>
                                                        <li>Early unstaking penalty: <?= number_format($plan['early_unstake_penalty'], 2) ?>%</li>
                                                        <li>Your current balances:
                                                            <ul>
                                                                <li>Main: <?= $user_currency . number_format($main_balance, 2) ?></li>
                                                                <li>Investment: <?= $user_currency . number_format($investment_balance, 2) ?></li>
                                                                <li>Staking: <?= $user_currency . number_format($staking_balance, 2) ?></li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" name="create_staking" class="btn btn-primary">Confirm Staking</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <script>
                                document.getElementById('amount<?= $plan['id'] ?>').addEventListener('input', function() {
                                    const amount = parseFloat(this.value) || 0;
                                    const reward = amount * <?= $plan['reward_percent'] / 100 ?>;
                                    document.getElementById('calculatedReward<?= $plan['id'] ?>').value = reward.toFixed(2);
                                });
                            </script>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Active Staking Positions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Your Active Staking Positions</h6>
                    <a href="/user/staking/dashboard" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($active_stakings)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-coins fa-3x mb-3 text-gray-300"></i>
                            <p class="mb-0">You don't have any active staking positions.</p>
                            <p>Start staking by selecting one of the plans above!</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Plan</th>
                                        <th>Amount</th>
                                        <th>Reward %</th>
                                        <th>APY</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($active_stakings as $staking): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($staking['plan_name']) ?></td>
                                            <td>$<?= number_format($staking['amount'], 2) ?></td>
                                            <td><?= number_format($staking['reward_percent'], 2) ?>%</td>
                                            <td><?= number_format($staking['apy'], 2) ?>%</td>
                                            <td><?= date('M d, Y', strtotime($staking['started_at'])) ?></td>
                                            <td><?= date('M d, Y', strtotime($staking['ends_at'])) ?></td>
                                            <td>
                                                <span class="badge bg-success">Active</span>
                                            </td>
                                            <td>
                                                <a href="/user/staking/details?id=<?= $staking['id'] ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if (strtotime($staking['unstake_available_at']) <= time()): ?>
                                                    <a href="/user/staking/unstake?id=<?= $staking['id'] ?>" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-undo"></i> Unstake
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-warning" disabled title="Locked until <?= date('M d, Y', strtotime($staking['unstake_available_at'])) ?>">
                                                        <i class="fas fa-lock"></i> Locked
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/footer.php'; ?> 