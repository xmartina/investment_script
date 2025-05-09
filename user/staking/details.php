<?php
// Staking details page
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';
// require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /user/login");
    exit();
}

$user_id = $_SESSION['user_id'];
$page_name = "Staking Details";

// Check if staking ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: /user/staking/dashboard");
    exit();
}

$staking_id = $_GET['id'];

// Get staking details
$stmt = $conn_back->prepare("
    SELECT s.*, sp.name as plan_name, sp.description as plan_description,
           sp.lock_period_days, sp.early_unstake_penalty
    FROM staking s
    JOIN staking_plans sp ON s.plan_id = sp.id
    WHERE s.id = ? AND s.user_id = ?
");
$stmt->bind_param("ii", $staking_id, $user_id);
$stmt->execute();
$staking = $stmt->get_result()->fetch_assoc();
$stmt->close();

// If staking not found or doesn't belong to user, redirect
if (!$staking) {
    header("Location: /user/staking/dashboard");
    exit();
}

// Get staking rewards
$stmt = $conn_back->prepare("
    SELECT *
    FROM staking_rewards
    WHERE staking_id = ?
    ORDER BY expected_date ASC
");
$stmt->bind_param("i", $staking_id);
$stmt->execute();
$rewards = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate statistics
$start = strtotime($staking['started_at']);
$end = strtotime($staking['ends_at']);
$now = time();
$progress = ($now - $start) / ($end - $start) * 100;
$progress = min(100, max(0, $progress)); // Ensure it's between 0 and 100

// Calculate time left
$time_left = $end - $now;
$days_left = floor($time_left / (60 * 60 * 24));
$hours_left = floor(($time_left % (60 * 60 * 24)) / (60 * 60));

// Check if can unstake
$can_unstake = strtotime($staking['unstake_available_at']) <= time();

// Calculate expected reward
$expected_reward = ($staking['amount'] * $staking['reward_percent']) / 100;

// Get related transactions
$stmt = $conn_back->prepare("
    SELECT *
    FROM transactions
    WHERE user_id = ? AND description LIKE ?
    ORDER BY date_time DESC
    LIMIT 5
");
$search_term = "%staking%" . $staking_id . "%";
$stmt->bind_param("is", $user_id, $search_term);
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/header.php'; 
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/breadcumb.php';
?>

<!-- Staking Details Page -->
<div class="container-xl px-4 mt-4">
    <nav class="nav nav-borders">
        <a class="nav-link" href="/user/staking">Staking Dashboard</a>
        <a class="nav-link" href="/user/staking/dashboard">My Staking</a>
        <a class="nav-link" href="/user/staking/rewards">Rewards</a>
    </nav>
    
    <hr class="mt-0 mb-4">
    
    <div class="row mb-4">
        <div class="col-12 mb-3">
            <a href="/user/staking/dashboard" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        
        <!-- Staking Details Card -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?= htmlspecialchars($staking['plan_name']) ?> Details
                    </h6>
                    <div>
                        <?php if ($staking['status'] === 'active'): ?>
                            <span class="badge bg-success">Active</span>
                        <?php elseif ($staking['status'] === 'completed'): ?>
                            <span class="badge bg-info">Completed</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Cancelled</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Staking Amount</h5>
                            <h2 class="text-primary">$<?= number_format($staking['amount'], 2) ?></h2>
                        </div>
                        <div class="col-md-6">
                            <h5>Expected Return</h5>
                            <h2 class="text-success">$<?= number_format($expected_reward, 2) ?></h2>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="small mb-1">Return Rate</label>
                                <div class="h5"><?= number_format($staking['reward_percent'], 2) ?>%</div>
                            </div>
                            <div class="mb-3">
                                <label class="small mb-1">Annual Percentage Yield (APY)</label>
                                <div class="h5"><?= number_format($staking['apy'], 2) ?>%</div>
                            </div>
                            <div class="mb-3">
                                <label class="small mb-1">Duration</label>
                                <div class="h5"><?= $staking['duration_days'] ?> days</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="small mb-1">Start Date</label>
                                <div class="h5"><?= date('M d, Y', strtotime($staking['started_at'])) ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="small mb-1">End Date</label>
                                <div class="h5"><?= date('M d, Y', strtotime($staking['ends_at'])) ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="small mb-1">Compounding</label>
                                <div class="h5">
                                    <?= $staking['is_compounding'] ? 
                                        '<span class="badge bg-success">Enabled</span>' : 
                                        '<span class="badge bg-secondary">Disabled</span>' 
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($staking['status'] === 'active'): ?>
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="small mb-1">Progress</label>
                                <div class="progress mb-1" style="height: 20px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: <?= $progress ?>%;" 
                                         aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?= round($progress) ?>%
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between small text-muted">
                                    <span><?= date('M d', strtotime($staking['started_at'])) ?></span>
                                    <span><?= $days_left ?> days, <?= $hours_left ?> hours left</span>
                                    <span><?= date('M d', strtotime($staking['ends_at'])) ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="small mb-1">Unlock Status</label>
                                <?php if ($can_unstake): ?>
                                    <div class="alert alert-success mb-0">
                                        <i class="fas fa-unlock"></i> This staking position is now unlocked and can be unstaked at any time.
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-lock"></i> This staking position is locked until 
                                        <strong><?= date('M d, Y', strtotime($staking['unstake_available_at'])) ?></strong>.
                                        Early unstaking will incur a penalty of 
                                        <strong><?= number_format($staking['early_unstake_penalty'], 2) ?>%</strong>.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12 d-flex justify-content-between">
                                <div>
                                    <?php if ($staking['is_compounding']): ?>
                                        <a href="/user/staking/toggle_compound?id=<?= $staking['id'] ?>&enable=0" 
                                            class="btn btn-danger">
                                                <i class="fas fa-toggle-off"></i> Disable Compounding
                                        </a>
                                    <?php else: ?>
                                        <a href="/user/staking/toggle_compound?id=<?= $staking['id'] ?>&enable=1" 
                                            class="btn btn-success">
                                                <i class="fas fa-toggle-on"></i> Enable Compounding
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <?php if ($can_unstake): ?>
                                        <a href="/user/staking/unstake?id=<?= $staking['id'] ?>" 
                                            class="btn btn-warning">
                                                <i class="fas fa-undo"></i> Unstake Now
                                        </a>
                                    <?php else: ?>
                                        <a href="/user/staking/unstake?id=<?= $staking['id'] ?>&early=1" 
                                            class="btn btn-outline-danger" 
                                            onclick="return confirm('Are you sure you want to unstake early? This will incur a penalty of <?= number_format($staking['early_unstake_penalty'], 2) ?>%.')">
                                                <i class="fas fa-exclamation-triangle"></i> Unstake Early (With Penalty)
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Rewards Table -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Rewards</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($rewards)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-coins fa-3x mb-3 text-gray-300"></i>
                            <p>No rewards found for this staking position.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Amount</th>
                                        <th>Expected Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rewards as $reward): ?>
                                        <?php 
                                        $expected_date = strtotime($reward['expected_date']);
                                        $now = time();
                                        $is_available = $expected_date <= $now && $reward['status'] === 'pending';
                                        ?>
                                        <tr>
                                            <td>$<?= number_format($reward['reward_amount'], 2) ?></td>
                                            <td>
                                                <?= date('M d, Y', $expected_date) ?>
                                                <?php if ($reward['status'] === 'pending'): ?>
                                                    <?php if ($is_available): ?>
                                                        <span class="badge bg-success">Available Now</span>
                                                    <?php else: ?>
                                                        <small class="d-block text-muted">
                                                            <?= ceil(($expected_date - $now) / (60 * 60 * 24)) ?> days remaining
                                                        </small>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($reward['status'] === 'pending'): ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php elseif ($reward['status'] === 'claimed'): ?>
                                                    <span class="badge bg-success">Claimed</span>
                                                    <small class="d-block text-muted">
                                                        on <?= date('M d, Y', strtotime($reward['claimed_at'])) ?>
                                                    </small>
                                                <?php elseif ($reward['status'] === 'reinvested'): ?>
                                                    <span class="badge bg-primary">Reinvested</span>
                                                    <small class="d-block text-muted">
                                                        on <?= date('M d, Y', strtotime($reward['claimed_at'])) ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Failed</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($is_available): ?>
                                                    <a href="/user/staking/rewards" class="btn btn-sm btn-success">
                                                        <i class="fas fa-money-bill-wave"></i> Claim
                                                    </a>
                                                <?php elseif ($reward['status'] === 'pending'): ?>
                                                    <button class="btn btn-sm btn-secondary" disabled>
                                                        <i class="fas fa-clock"></i> Not Available Yet
                                                    </button>
                                                <?php else: ?>
                                                    -
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
        
        <!-- Sidebar Cards -->
        <div class="col-lg-4">
            <!-- Plan Information -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Plan Information</h6>
                </div>
                <div class="card-body">
                    <h5><?= htmlspecialchars($staking['plan_name']) ?></h5>
                    <p><?= htmlspecialchars($staking['plan_description']) ?></p>
                    
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Lock Period:</span>
                            <strong><?= $staking['lock_period_days'] ?> days</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Duration:</span>
                            <strong><?= $staking['duration_days'] ?> days</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Early Unstake Fee:</span>
                            <strong><?= number_format($staking['early_unstake_penalty'], 2) ?>%</strong>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Related Transactions -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Related Transactions</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($transactions)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-exchange-alt fa-3x mb-3 text-gray-300"></i>
                            <p>No transactions found for this staking position.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($transactions as $transaction): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">
                                            <?php if ($transaction['type'] === 'staking'): ?>
                                                <i class="fas fa-arrow-right text-danger"></i> Staking
                                            <?php elseif ($transaction['type'] === 'earning'): ?>
                                                <i class="fas fa-arrow-left text-success"></i> Reward
                                            <?php else: ?>
                                                <i class="fas fa-exchange-alt"></i> <?= ucfirst($transaction['type']) ?>
                                            <?php endif; ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?= date('M d, Y', strtotime($transaction['created_at'])) ?>
                                        </small>
                                    </div>
                                    <p class="mb-1">$<?= number_format($transaction['amount'], 2) ?></p>
                                    <small class="text-muted">
                                        Ref: <?= $transaction['reference'] ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/footer.php'; ?> 