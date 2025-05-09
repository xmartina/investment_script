<?php
// Staking rewards page
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';
// require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /user/login");
    exit();
}

$user_id = $_SESSION['user_id'];
$page_name = "Staking Rewards";

// Get user data
$stmt = $conn_back->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Process reward claiming
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_reward'])) {
    $reward_id = $_POST['reward_id'];
    
    // Check if reward exists and belongs to user
    $stmt = $conn_back->prepare("
        SELECT sr.*, s.amount as staking_amount, sp.name as plan_name
        FROM staking_rewards sr
        JOIN staking s ON sr.staking_id = s.id
        JOIN staking_plans sp ON s.plan_id = sp.id
        WHERE sr.id = ? AND sr.user_id = ? AND sr.status = 'pending'
    ");
    $stmt->bind_param("ii", $reward_id, $user_id);
    $stmt->execute();
    $reward = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($reward) {
        // Begin transaction
        $conn_back->begin_transaction();
        
        try {
            // Update reward status
            $now = date('Y-m-d H:i:s');
            $stmt = $conn_back->prepare("
                UPDATE staking_rewards 
                SET status = 'claimed', claimed_at = ?
                WHERE id = ?
            ");
            $stmt->bind_param("si", $now, $reward_id);
            $stmt->execute();
            $stmt->close();
            
            // Add to user balance
            $stmt = $conn_back->prepare("
                UPDATE users 
                SET balance = balance + ?
                WHERE id = ?
            ");
            $stmt->bind_param("di", $reward['reward_amount'], $user_id);
            $stmt->execute();
            $stmt->close();
            
            // Create transaction record
            $reference = 'STKRWD' . time() . $user_id;
            $description = "Staking reward from {$reward['plan_name']} plan";
            
            $stmt = $conn_back->prepare("
                INSERT INTO transactions (
                    user_id, type, amount, roi_percentage, status, reference, description, created_at
                ) VALUES (?, 'earning', ?, ?, 'successful', ?, ?, ?)
            ");
            $stmt->bind_param("iddsss", $user_id, $reward['reward_amount'], $reward['reward_percent'], $reference, $description, $now);
            $stmt->execute();
            $transaction_id = $conn_back->insert_id;
            $stmt->close();
            
            // Update reward with transaction ID
            $stmt = $conn_back->prepare("
                UPDATE staking_rewards 
                SET transaction_id = ?
                WHERE id = ?
            ");
            $stmt->bind_param("ii", $transaction_id, $reward_id);
            $stmt->execute();
            $stmt->close();
            
            // Commit transaction
            $conn_back->commit();
            
            // Redirect to prevent form resubmission
            header("Location: /user/staking/rewards?success=claim");
            exit();
        } catch (Exception $e) {
            // Rollback if an error occurs
            $conn_back->rollback();
            $error = "An error occurred: " . $e->getMessage();
        }
    } else {
        $error = "Invalid reward or reward already claimed.";
    }
}

// Process reinvest
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reinvest_reward'])) {
    $reward_id = $_POST['reward_id'];
    $plan_id = $_POST['plan_id'];
    
    // Check if reward exists and belongs to user
    $stmt = $conn_back->prepare("
        SELECT sr.*, s.amount as staking_amount, sp.name as plan_name
        FROM staking_rewards sr
        JOIN staking s ON sr.staking_id = s.id
        JOIN staking_plans sp ON s.plan_id = sp.id
        WHERE sr.id = ? AND sr.user_id = ? AND sr.status = 'pending'
    ");
    $stmt->bind_param("ii", $reward_id, $user_id);
    $stmt->execute();
    $reward = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Check if plan exists
    $stmt = $conn_back->prepare("SELECT * FROM staking_plans WHERE id = ? AND is_active = 1");
    $stmt->bind_param("i", $plan_id);
    $stmt->execute();
    $plan = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($reward && $plan) {
        // Check if reward amount meets minimum
        if ($reward['reward_amount'] < $plan['min_amount']) {
            $error = "Reward amount ($" . number_format($reward['reward_amount'], 2) . ") is below the minimum requirement for this plan ($" . number_format($plan['min_amount'], 2) . ").";
        } else {
            // Begin transaction
            $conn_back->begin_transaction();
            
            try {
                // Update reward status
                $now = date('Y-m-d H:i:s');
                $stmt = $conn_back->prepare("
                    UPDATE staking_rewards 
                    SET status = 'reinvested', claimed_at = ?
                    WHERE id = ?
                ");
                $stmt->bind_param("si", $now, $reward_id);
                $stmt->execute();
                $stmt->close();
                
                // Create new staking position
                $start_date = $now;
                $end_date = date('Y-m-d H:i:s', strtotime($now . ' + ' . $plan['duration_days'] . ' days'));
                $unstake_date = date('Y-m-d H:i:s', strtotime($now . ' + ' . $plan['lock_period_days'] . ' days'));
                
                $stmt = $conn_back->prepare("
                    INSERT INTO staking (
                        user_id, plan_id, amount, duration_days, reward_percent, 
                        is_compounding, status, started_at, ends_at, unstake_available_at, created_at
                    ) VALUES (?, ?, ?, ?, ?, 1, 'active', ?, ?, ?, ?)
                ");
                $stmt->bind_param(
                    "iididssss", 
                    $user_id, $plan_id, $reward['reward_amount'], $plan['duration_days'], 
                    $plan['reward_percent'], $start_date, $end_date, $unstake_date, $now
                );
                $stmt->execute();
                $staking_id = $conn_back->insert_id;
                $stmt->close();
                
                // Create transaction record
                $reference = 'STKREINV' . time() . $user_id;
                $description = "Reinvested staking reward into {$plan['name']} plan";
                
                $stmt = $conn_back->prepare("
                    INSERT INTO transactions (
                        user_id, type, amount, roi_percentage, status, reference, description, created_at
                    ) VALUES (?, 'staking', ?, ?, 'successful', ?, ?, ?)
                ");
                $stmt->bind_param("iddsss", $user_id, $reward['reward_amount'], $plan['reward_percent'], $reference, $description, $now);
                $stmt->execute();
                $transaction_id = $conn_back->insert_id;
                $stmt->close();
                
                // Update reward with transaction ID
                $stmt = $conn_back->prepare("
                    UPDATE staking_rewards 
                    SET transaction_id = ?
                    WHERE id = ?
                ");
                $stmt->bind_param("ii", $transaction_id, $reward_id);
                $stmt->execute();
                $stmt->close();
                
                // Commit transaction
                $conn_back->commit();
                
                // Redirect to prevent form resubmission
                header("Location: /user/staking/rewards?success=reinvest");
                exit();
            } catch (Exception $e) {
                // Rollback if an error occurs
                $conn_back->rollback();
                $error = "An error occurred: " . $e->getMessage();
            }
        }
    } else {
        $error = "Invalid reward, plan, or reward already claimed.";
    }
}

// Get pending rewards
$stmt = $conn_back->prepare("
    SELECT sr.*, s.amount as staking_amount, sp.name as plan_name, sp.description as plan_description
    FROM staking_rewards sr
    JOIN staking s ON sr.staking_id = s.id
    JOIN staking_plans sp ON s.plan_id = sp.id
    WHERE sr.user_id = ? AND sr.status = 'pending'
    ORDER BY sr.expected_date ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_rewards = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get historical rewards
$stmt = $conn_back->prepare("
    SELECT sr.*, s.amount as staking_amount, sp.name as plan_name
    FROM staking_rewards sr
    JOIN staking s ON sr.staking_id = s.id
    JOIN staking_plans sp ON s.plan_id = sp.id
    WHERE sr.user_id = ? AND sr.status != 'pending'
    ORDER BY sr.claimed_at DESC, sr.created_at DESC
    LIMIT 20
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$historical_rewards = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get staking plans for reinvestment
$query = "SELECT * FROM staking_plans WHERE is_active = 1 ORDER BY min_amount ASC";
$staking_plans = $conn_back->query($query)->fetch_all(MYSQLI_ASSOC);

// Calculate totals
$total_pending = 0;
$total_claimed = 0;
$total_reinvested = 0;

foreach ($pending_rewards as $reward) {
    $total_pending += $reward['reward_amount'];
}

foreach ($historical_rewards as $reward) {
    if ($reward['status'] === 'claimed') {
        $total_claimed += $reward['reward_amount'];
    } elseif ($reward['status'] === 'reinvested') {
        $total_reinvested += $reward['reward_amount'];
    }
}

?>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/header.php'; 
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/breadcumb.php';
?>

<!-- Staking Rewards Page -->
<div class="container-xl px-4 mt-4">
    <nav class="nav nav-borders">
        <a class="nav-link" href="/user/staking">Staking Dashboard</a>
        <a class="nav-link" href="/user/staking/dashboard">My Staking</a>
        <a class="nav-link active ms-0" href="/user/staking/rewards">Rewards</a>
    </nav>
    
    <hr class="mt-0 mb-4">
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php if ($_GET['success'] === 'claim'): ?>
                Your reward has been successfully claimed and added to your balance!
            <?php elseif ($_GET['success'] === 'reinvest'): ?>
                Your reward has been successfully reinvested into a new staking position!
            <?php else: ?>
                Operation completed successfully!
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Rewards Overview -->
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card h-100 border-left-primary shadow py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Pending Rewards
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($total_pending, 2) ?>
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
                                Claimed Rewards
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($total_claimed, 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                Reinvested Rewards
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($total_reinvested, 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-sync fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pending Rewards -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Pending Rewards</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($pending_rewards)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-award fa-3x mb-3 text-gray-300"></i>
                            <p>You don't have any pending rewards.</p>
                            <p>Start staking to earn rewards!</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Staking Plan</th>
                                        <th>Amount</th>
                                        <th>Expected Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pending_rewards as $reward): ?>
                                        <?php 
                                        $expected_date = strtotime($reward['expected_date']);
                                        $now = time();
                                        $is_available = $expected_date <= $now;
                                        ?>
                                        <tr>
                                            <td>
                                                <?= htmlspecialchars($reward['plan_name']) ?>
                                                <small class="d-block text-muted">
                                                    Staked: $<?= number_format($reward['staking_amount'], 2) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="text-success font-weight-bold">
                                                    $<?= number_format($reward['reward_amount'], 2) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= date('M d, Y', $expected_date) ?>
                                                <?php if ($is_available): ?>
                                                    <span class="badge bg-success">Available Now</span>
                                                <?php else: ?>
                                                    <small class="d-block text-muted">
                                                        <?= ceil(($expected_date - $now) / (60 * 60 * 24)) ?> days remaining
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning">Pending</span>
                                            </td>
                                            <td>
                                                <?php if ($is_available): ?>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="reward_id" value="<?= $reward['id'] ?>">
                                                        <button type="submit" name="claim_reward" class="btn btn-sm btn-success">
                                                            <i class="fas fa-money-bill-wave"></i> Claim
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-primary" 
                                                            data-bs-toggle="modal" data-bs-target="#reinvestModal<?= $reward['id'] ?>">
                                                        <i class="fas fa-sync"></i> Reinvest
                                                    </button>
                                                    
                                                    <!-- Reinvest Modal -->
                                                    <div class="modal fade" id="reinvestModal<?= $reward['id'] ?>" tabindex="-1" 
                                                         aria-labelledby="reinvestModalLabel<?= $reward['id'] ?>" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="reinvestModalLabel<?= $reward['id'] ?>">
                                                                        Reinvest Reward
                                                                    </h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <form method="post">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="reward_id" value="<?= $reward['id'] ?>">
                                                                        
                                                                        <div class="alert alert-info">
                                                                            You are about to reinvest your reward of 
                                                                            <strong>$<?= number_format($reward['reward_amount'], 2) ?></strong>
                                                                            into a new staking position.
                                                                        </div>
                                                                        
                                                                        <div class="mb-3">
                                                                            <label for="planSelect<?= $reward['id'] ?>" class="form-label">
                                                                                Select Staking Plan
                                                                            </label>
                                                                            <select class="form-select" id="planSelect<?= $reward['id'] ?>" 
                                                                                    name="plan_id" required>
                                                                                <option value="">Choose a plan...</option>
                                                                                <?php foreach ($staking_plans as $plan): ?>
                                                                                    <?php if ($reward['reward_amount'] >= $plan['min_amount']): ?>
                                                                                        <option value="<?= $plan['id'] ?>">
                                                                                            <?= htmlspecialchars($plan['name']) ?> - 
                                                                                            <?= number_format($plan['reward_percent'], 2) ?>% return over 
                                                                                            <?= $plan['duration_days'] ?> days
                                                                                        </option>
                                                                                    <?php else: ?>
                                                                                        <option value="<?= $plan['id'] ?>" disabled>
                                                                                            <?= htmlspecialchars($plan['name']) ?> 
                                                                                            (Min: $<?= number_format($plan['min_amount'], 2) ?>)
                                                                                        </option>
                                                                                    <?php endif; ?>
                                                                                <?php endforeach; ?>
                                                                            </select>
                                                                        </div>
                                                                        
                                                                        <p class="text-muted small">
                                                                            Note: Reinvesting will create a new staking position with 
                                                                            compounding enabled by default.
                                                                        </p>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                            Cancel
                                                                        </button>
                                                                        <button type="submit" name="reinvest_reward" class="btn btn-primary">
                                                                            Confirm Reinvestment
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-secondary" disabled>
                                                        <i class="fas fa-clock"></i> Not Available Yet
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
    
    <!-- Reward History -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Reward History</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($historical_rewards)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x mb-3 text-gray-300"></i>
                            <p>You don't have any reward history yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Staking Plan</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Transaction ID</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historical_rewards as $reward): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($reward['plan_name']) ?></td>
                                            <td>$<?= number_format($reward['reward_amount'], 2) ?></td>
                                            <td>
                                                <?= date('M d, Y', strtotime($reward['claimed_at'] ?? $reward['created_at'])) ?>
                                            </td>
                                            <td>
                                                <?php if ($reward['status'] === 'claimed'): ?>
                                                    <span class="badge bg-success">Claimed</span>
                                                <?php elseif ($reward['status'] === 'reinvested'): ?>
                                                    <span class="badge bg-primary">Reinvested</span>
                                                <?php elseif ($reward['status'] === 'expired'): ?>
                                                    <span class="badge bg-danger">Expired</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (isset($reward['transaction_id']) && $reward['transaction_id']): ?>
                                                    <small class="text-muted">ID: <?= $reward['transaction_id'] ?></small>
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
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/footer.php'; ?> 