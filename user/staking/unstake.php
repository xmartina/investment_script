<?php
// Staking unstake page
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';
// require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /user/login");
    exit();
}

$user_id = $_SESSION['user_id'];
$page_title = "Unstake Funds";

// Check if staking ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: /user/staking/dashboard");
    exit();
}

$staking_id = $_GET['id'];
$early_unstake = isset($_GET['early']) && $_GET['early'] == 1;

// Get staking details
$stmt = $conn_back->prepare("
    SELECT s.*, sp.name as plan_name, sp.early_unstake_penalty
    FROM staking s
    JOIN staking_plans sp ON s.plan_id = sp.id
    WHERE s.id = ? AND s.user_id = ? AND s.status = 'active'
");
$stmt->bind_param("ii", $staking_id, $user_id);
$stmt->execute();
$staking = $stmt->get_result()->fetch_assoc();
$stmt->close();

// If staking not found, doesn't belong to user, or is not active, redirect
if (!$staking) {
    header("Location: /user/staking/dashboard");
    exit();
}

// Check if it's allowed to unstake
$can_unstake = strtotime($staking['unstake_available_at']) <= time();

// If early unstake is not explicitly requested and staking is still locked, redirect
if (!$early_unstake && !$can_unstake) {
    header("Location: /user/staking/details?id=" . $staking_id);
    exit();
}

// Calculate penalty if applicable
$penalty_amount = 0;
$return_amount = $staking['amount'];

if (!$can_unstake) {
    $penalty_percentage = $staking['early_unstake_penalty'];
    $penalty_amount = ($staking['amount'] * $penalty_percentage) / 100;
    $return_amount = $staking['amount'] - $penalty_amount;
}

// Process unstaking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_unstake'])) {
    // Begin transaction
    $conn_back->begin_transaction();
    
    try {
        // Update staking status
        $now = date('Y-m-d H:i:s');
        $status = 'cancelled';
        
        $stmt = $conn_back->prepare("
            UPDATE staking 
            SET status = ?, ends_at = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssi", $status, $now, $staking_id);
        $stmt->execute();
        $stmt->close();
        
        // Cancel any pending rewards
        $stmt = $conn_back->prepare("
            UPDATE staking_rewards
            SET status = 'expired'
            WHERE staking_id = ? AND status = 'pending'
        ");
        $stmt->bind_param("i", $staking_id);
        $stmt->execute();
        $stmt->close();
        
        // Add funds back to user balance
        $stmt = $conn_back->prepare("
            UPDATE users 
            SET balance = balance + ?
            WHERE id = ?
        ");
        $stmt->bind_param("di", $return_amount, $user_id);
        $stmt->execute();
        $stmt->close();
        
        // Create transaction record
        $reference = 'UNSTAKE' . time() . $user_id;
        $description = "Unstaked funds from {$staking['plan_name']} plan";
        if ($penalty_amount > 0) {
            $description .= " (Early unstake with " . number_format($staking['early_unstake_penalty'], 2) . "% penalty)";
        }
        
        $stmt = $conn_back->prepare("
            INSERT INTO transactions (
                user_id, type, amount, status, reference, description, created_at
            ) VALUES (?, 'staking', ?, 'successful', ?, ?, ?)
        ");
        $stmt->bind_param("idsss", $user_id, $return_amount, $reference, $description, $now);
        $stmt->execute();
        $stmt->close();
        
        // If there was a penalty, record it as a separate transaction
        if ($penalty_amount > 0) {
            $penalty_reference = 'PENALTY' . time() . $user_id;
            $penalty_description = "Early unstaking penalty for {$staking['plan_name']} plan";
            
            $stmt = $conn_back->prepare("
                INSERT INTO transactions (
                    user_id, type, amount, status, reference, description, created_at
                ) VALUES (?, 'staking', ?, 'successful', ?, ?, ?)
            ");
            $stmt->bind_param("idsss", $user_id, $penalty_amount, $penalty_reference, $penalty_description, $now);
            $stmt->execute();
            $stmt->close();
        }
        
        // Commit transaction
        $conn_back->commit();
        
        // Redirect to dashboard with success message
        header("Location: /user/staking/dashboard?success=unstake");
        exit();
    } catch (Exception $e) {
        // Rollback if an error occurs
        $conn_back->rollback();
        $error = "An error occurred: " . $e->getMessage();
    }
}

?>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/header.php'; 
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/breadcumb.php';
?>

<!-- Unstake Page -->
<div class="container-xl px-4 mt-4">
    <nav class="nav nav-borders">
        <a class="nav-link" href="/user/staking">Staking Dashboard</a>
        <a class="nav-link" href="/user/staking/dashboard">My Staking</a>
        <a class="nav-link" href="/user/staking/rewards">Rewards</a>
    </nav>
    
    <hr class="mt-0 mb-4">
    
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Unstake Confirmation</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4 text-center">
                        <i class="fas fa-exclamation-circle fa-3x text-warning mb-3"></i>
                        <h4>You are about to unstake your funds</h4>
                        <p class="mb-0">Please review the details below and confirm</p>
                    </div>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="small mb-1">Staking Plan</label>
                                <div class="h5"><?= htmlspecialchars($staking['plan_name']) ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="small mb-1">Staked Amount</label>
                                <div class="h5">$<?= number_format($staking['amount'], 2) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="small mb-1">Start Date</label>
                                <div class="h5"><?= date('M d, Y', strtotime($staking['started_at'])) ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="small mb-1">Original End Date</label>
                                <div class="h5"><?= date('M d, Y', strtotime($staking['ends_at'])) ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!$can_unstake): ?>
                        <div class="alert alert-warning mb-4">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>Early Unstaking Warning</strong>
                            <p class="mb-0">
                                You are unstaking before the lock period ends (<?= date('M d, Y', strtotime($staking['unstake_available_at'])) ?>).
                                An early unstaking penalty of <?= number_format($staking['early_unstake_penalty'], 2) ?>% will be applied.
                            </p>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card bg-light mb-3">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Staked Amount</h6>
                                        <h4 class="text-primary">$<?= number_format($staking['amount'], 2) ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light mb-3">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Penalty (<?= number_format($staking['early_unstake_penalty'], 2) ?>%)</h6>
                                        <h4 class="text-danger">-$<?= number_format($penalty_amount, 2) ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white mb-3">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">You Will Receive</h6>
                                        <h4>$<?= number_format($return_amount, 2) ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success mb-4">
                            <i class="fas fa-check-circle"></i> 
                            <strong>Lock Period Ended</strong>
                            <p class="mb-0">
                                The lock period for this staking position has ended.
                                You can now unstake your full amount without any penalties.
                            </p>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-success text-white mb-3">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">You Will Receive</h6>
                                        <h4>$<?= number_format($return_amount, 2) ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Important Information</strong>
                        <p class="mb-0">
                            Unstaking will cancel this staking position and return the funds to your account balance.
                            Any pending rewards will be cancelled. This action cannot be undone.
                        </p>
                    </div>
                    
                    <form method="post" class="text-center">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            <a href="/user/staking/details?id=<?= $staking_id ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" name="confirm_unstake" class="btn btn-primary">
                                <i class="fas fa-check"></i> Confirm Unstaking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/footer.php'; ?> 