<?php
// Staking dashboard page
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';
// require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /user/login");
    exit();
}

$user_id = $_SESSION['user_id'];
$page_name = "My Staking Dashboard";

// Get user data
$stmt = $conn_back->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get user's active staking positions
$stmt = $conn_back->prepare("
    SELECT s.*, sp.name as plan_name, sp.description as plan_description, 
           sp.lock_period_days, sp.early_unstake_penalty
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
    SELECT s.*, sp.name as plan_name, sp.description as plan_description
    FROM staking s
    JOIN staking_plans sp ON s.plan_id = sp.id
    WHERE s.user_id = ? AND s.status != 'active'
    ORDER BY s.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$past_stakings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get staking statistics
$stmt = $conn_back->prepare("
    SELECT 
        COUNT(*) as total_positions,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_positions,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_positions,
        SUM(CASE WHEN status = 'active' THEN amount ELSE 0 END) as total_active_amount,
        SUM(CASE WHEN status = 'active' THEN earned_reward ELSE 0 END) as total_earned_rewards
    FROM staking
    WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get pending rewards
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

// Get claimed rewards
$stmt = $conn_back->prepare("
    SELECT SUM(reward_amount) as total_claimed
    FROM staking_rewards
    WHERE user_id = ? AND status = 'claimed'
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$claimed_rewards = $result['total_claimed'] ?? 0;
$stmt->close();

?>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/header.php'; 
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/breadcumb.php';
?>

<!-- Staking Dashboard -->
<div class="container-xl px-4 mt-4">
    <nav class="nav nav-borders">
        <a class="nav-link" href="/user/staking">Staking Dashboard</a>
        <a class="nav-link active ms-0" href="/user/staking/dashboard">My Staking</a>
        <a class="nav-link" href="/user/staking/rewards">Rewards</a>
    </nav>
    
    <hr class="mt-0 mb-4">
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php if ($_GET['success'] == 'unstake'): ?>
                Your staking position has been successfully unstaked!
            <?php elseif ($_GET['success'] == 'claim'): ?>
                Your rewards have been successfully claimed!
            <?php else: ?>
                Operation completed successfully!
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Staking Overview -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100 border-left-primary shadow py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Staked
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($stats['total_active_amount'] ?? 0, 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
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
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100 border-left-info shadow py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Active Positions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['active_positions'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100 border-left-warning shadow py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Earned
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format(($stats['total_earned_rewards'] ?? 0) + $claimed_rewards, 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
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
                    <h6 class="m-0 font-weight-bold text-primary">Active Staking Positions</h6>
                    <a href="/user/staking" class="btn btn-sm btn-primary">Add New Staking</a>
                </div>
                <div class="card-body">
                    <?php if (empty($active_stakings)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-coins fa-3x mb-3 text-gray-300"></i>
                            <p class="mb-0">You don't have any active staking positions.</p>
                            <p>Start staking by <a href="/user/staking">selecting a plan</a>!</p>
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
                                        <th>Earned</th>
                                        <th>Compounding</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($active_stakings as $staking): ?>
                                        <?php 
                                        // Calculate progress
                                        $start = strtotime($staking['started_at']);
                                        $end = strtotime($staking['ends_at']);
                                        $now = time();
                                        $progress = ($now - $start) / ($end - $start) * 100;
                                        $progress = min(100, max(0, $progress)); // Ensure it's between 0 and 100
                                        
                                        // Calculate time left
                                        $time_left = $end - $now;
                                        $days_left = floor($time_left / (60 * 60 * 24));
                                        $hours_left = floor(($time_left % (60 * 60 * 24)) / (60 * 60));
                                        
                                        // Calculate expected end reward
                                        $expected_reward = ($staking['amount'] * $staking['reward_percent']) / 100;
                                        
                                        // Check if can unstake
                                        $can_unstake = strtotime($staking['unstake_available_at']) <= time();
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($staking['plan_name']) ?></td>
                                            <td>$<?= number_format($staking['amount'], 2) ?></td>
                                            <td><?= number_format($staking['reward_percent'], 2) ?>%</td>
                                            <td><?= number_format($staking['apy'], 2) ?>%</td>
                                            <td>$<?= number_format($staking['earned_reward'], 2) ?></td>
                                            <td>
                                                <?= $staking['is_compounding'] ? 
                                                    '<span class="badge bg-success">Enabled</span>' : 
                                                    '<span class="badge bg-secondary">Disabled</span>' 
                                                ?>
                                            </td>
                                            <td>
                                                <?= date('M d, Y', strtotime($staking['started_at'])) ?>
                                            </td>
                                            <td>
                                                <?= date('M d, Y', strtotime($staking['ends_at'])) ?>
                                                <div class="progress mt-2" style="height: 5px;">
                                                    <div class="progress-bar bg-success" role="progressbar" 
                                                         style="width: <?= $progress ?>%;" 
                                                         aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <small class="text-muted">
                                                    <?= $days_left ?> days, <?= $hours_left ?> hours left
                                                </small>
                                            </td>
                                            <td>
                                                <a href="/user/staking/details?id=<?= $staking['id'] ?>" 
                                                   class="btn btn-sm btn-info mb-1">
                                                    <i class="fas fa-eye"></i> Details
                                                </a>
                                                <?php if ($can_unstake): ?>
                                                    <a href="/user/staking/unstake?id=<?= $staking['id'] ?>" 
                                                       class="btn btn-sm btn-warning mb-1">
                                                        <i class="fas fa-undo"></i> Unstake
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-warning mb-1" disabled 
                                                            title="Locked until <?= date('M d, Y', strtotime($staking['unstake_available_at'])) ?>">
                                                        <i class="fas fa-lock"></i> Locked
                                                    </button>
                                                <?php endif; ?>
                                                <?php if ($staking['is_compounding']): ?>
                                                    <a href="/user/staking/toggle_compound?id=<?= $staking['id'] ?>&enable=0" 
                                                       class="btn btn-sm btn-danger mb-1">
                                                        <i class="fas fa-toggle-off"></i> Disable Compounding
                                                    </a>
                                                <?php else: ?>
                                                    <a href="/user/staking/toggle_compound?id=<?= $staking['id'] ?>&enable=1" 
                                                       class="btn btn-sm btn-success mb-1">
                                                        <i class="fas fa-toggle-on"></i> Enable Compounding
                                                    </a>
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
    
    <!-- Past Staking Positions -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Past Staking Positions</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($past_stakings)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x mb-3 text-gray-300"></i>
                            <p>You don't have any past staking positions.</p>
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
                                        <th>Earned</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($past_stakings as $staking): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($staking['plan_name']) ?></td>
                                            <td>$<?= number_format($staking['amount'], 2) ?></td>
                                            <td><?= number_format($staking['reward_percent'], 2) ?>%</td>
                                            <td><?= number_format($staking['apy'], 2) ?>%</td>
                                            <td>$<?= number_format($staking['earned_reward'], 2) ?></td>
                                            <td><?= date('M d, Y', strtotime($staking['started_at'])) ?></td>
                                            <td><?= date('M d, Y', strtotime($staking['ends_at'])) ?></td>
                                            <td>
                                                <?php if ($staking['status'] == 'completed'): ?>
                                                    <span class="badge bg-success">Completed</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Cancelled</span>
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