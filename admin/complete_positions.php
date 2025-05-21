<?php
// Admin Complete Positions Page
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Complete Positions";
$current_page = "complete_positions.php";
$message = "";
$error = "";

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_SESSION['admin_id'];
    
    // Complete investment
    if (isset($_POST['complete_investment'])) {
        $investment_id = (int)$_POST['investment_id'];
        
        // Get investment details
        $stmt = $conn_back->prepare("
            SELECT i.*, p.name as plan_name, u.main_balance, u.investment_balance 
            FROM investments i 
            JOIN investment_plans p ON i.plan_id = p.id
            JOIN users u ON i.user_id = u.id
            WHERE i.id = ? AND i.status = 'active'
        ");
        $stmt->bind_param("i", $investment_id);
        $stmt->execute();
        $investment = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$investment) {
            $error = "Investment not found or already completed.";
        } else {
            // Begin transaction
            $conn_back->begin_transaction();
            
            try {
                // Update investment status
                $stmt = $conn_back->prepare("UPDATE investments SET status = 'completed', completed_at = NOW() WHERE id = ?");
                $stmt->bind_param("i", $investment_id);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update investment status: " . $stmt->error);
                }
                
                $stmt->close();
                
                // Calculate return amount
                $principal = (float)$investment['amount'];
                $roi_expected = (float)$investment['roi_expected'];
                $total_return = $principal + $roi_expected;
                
                // Update user's main balance
                $new_main_balance = (float)$investment['main_balance'] + $total_return;
                
                $stmt = $conn_back->prepare("UPDATE users SET main_balance = ? WHERE id = ?");
                $stmt->bind_param("di", $new_main_balance, $investment['user_id']);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update user balance: " . $stmt->error);
                }
                
                $stmt->close();
                
                // Create a transaction record
                $reference_id = 'INV-COMPLETE-' . time();
                $description = "Investment #{$investment_id} in {$investment['plan_name']} completed by admin. Principal: \${$principal}, ROI: \${$roi_expected}";
                
                $stmt = $conn_back->prepare("
                    INSERT INTO transactions (
                        user_id, amount, transaction_type, reference_id, 
                        currency, status, date_time, description
                    ) VALUES (?, ?, 'investment_return', ?, '$', 'completed', NOW(), ?)
                ");
                $stmt->bind_param("idss", $investment['user_id'], $total_return, $reference_id, $description);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to create transaction record: " . $stmt->error);
                }
                
                $stmt->close();
                
                // Add investment return record
                $stmt = $conn_back->prepare("
                    INSERT INTO investment_returns (
                        investment_id, user_id, return_amount, roi_percentage, 
                        expected_date, status, created_at, paid_at
                    ) VALUES (?, ?, ?, ?, NOW(), 'paid', NOW(), NOW())
                ");
                $stmt->bind_param("iidd", $investment_id, $investment['user_id'], $roi_expected, $investment['roi_percentage']);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to create investment return record: " . $stmt->error);
                }
                
                $stmt->close();
                
                // Log admin action
                $action = "Admin #{$admin_id} manually completed investment #{$investment_id} for user #{$investment['user_id']}";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                $log_stmt->execute();
                $log_stmt->close();
                
                $conn_back->commit();
                $message = "Investment #{$investment_id} has been successfully completed and \${$total_return} has been credited to the user's account.";
                
            } catch (Exception $e) {
                $conn_back->rollback();
                $error = "Error completing investment: " . $e->getMessage();
            }
        }
    }
    
    // Complete staking
    if (isset($_POST['complete_staking'])) {
        $staking_id = (int)$_POST['staking_id'];
        
        // Get staking details
        $stmt = $conn_back->prepare("
            SELECT s.*, p.name as plan_name, u.main_balance, u.staking_balance 
            FROM staking s 
            JOIN staking_plans p ON s.plan_id = p.id
            JOIN users u ON s.user_id = u.id
            WHERE s.id = ? AND s.status = 'active'
        ");
        $stmt->bind_param("i", $staking_id);
        $stmt->execute();
        $staking = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$staking) {
            $error = "Staking position not found or already completed.";
        } else {
            // Begin transaction
            $conn_back->begin_transaction();
            
            try {
                // Update staking status
                $stmt = $conn_back->prepare("UPDATE staking SET status = 'completed', completed_at = NOW() WHERE id = ?");
                $stmt->bind_param("i", $staking_id);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update staking status: " . $stmt->error);
                }
                
                $stmt->close();
                
                // Calculate return amount
                $principal = (float)$staking['amount'];
                $earned_reward = (float)$staking['earned_reward'];
                $total_return = $principal + $earned_reward;
                
                // Update user's main balance
                $new_main_balance = (float)$staking['main_balance'] + $total_return;
                
                $stmt = $conn_back->prepare("UPDATE users SET main_balance = ? WHERE id = ?");
                $stmt->bind_param("di", $new_main_balance, $staking['user_id']);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update user balance: " . $stmt->error);
                }
                
                $stmt->close();
                
                // Create a transaction record
                $reference_id = 'STAKE-COMPLETE-' . time();
                $description = "Staking #{$staking_id} in {$staking['plan_name']} completed by admin. Principal: \${$principal}, Rewards: \${$earned_reward}";
                
                $stmt = $conn_back->prepare("
                    INSERT INTO transactions (
                        user_id, amount, transaction_type, reference_id, 
                        currency, status, date_time, description
                    ) VALUES (?, ?, 'staking_return', ?, '$', 'completed', NOW(), ?)
                ");
                $stmt->bind_param("idss", $staking['user_id'], $total_return, $reference_id, $description);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to create transaction record: " . $stmt->error);
                }
                
                $stmt->close();
                
                // Log admin action
                $action = "Admin #{$admin_id} manually completed staking #{$staking_id} for user #{$staking['user_id']}";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                $log_stmt->execute();
                $log_stmt->close();
                
                $conn_back->commit();
                $message = "Staking position #{$staking_id} has been successfully completed and \${$total_return} has been credited to the user's account.";
                
            } catch (Exception $e) {
                $conn_back->rollback();
                $error = "Error completing staking: " . $e->getMessage();
            }
        }
    }
}

// Get active investments
$investments_query = "
    SELECT i.*, 
           p.name as plan_name, 
           p.roi_percent,
           u.first_name, 
           u.last_name, 
           u.email
    FROM investments i
    JOIN users u ON i.user_id = u.id
    JOIN investment_plans p ON i.plan_id = p.id
    WHERE i.status = 'active'
    ORDER BY i.created_at DESC
";
$investments_result = $conn_back->query($investments_query);

// Get active staking positions
$staking_query = "
    SELECT s.*, 
           p.name as plan_name, 
           p.reward_percent,
           u.first_name, 
           u.last_name, 
           u.email
    FROM staking s
    JOIN users u ON s.user_id = u.id
    JOIN staking_plans p ON s.plan_id = p.id
    WHERE s.status = 'active'
    ORDER BY s.created_at DESC
";
$staking_result = $conn_back->query($staking_query);

include_once __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Complete Positions</h1>
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
        <!-- Active Investments -->
        <div class="col-xl-12 col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Active Investments</h6>
                    <a href="create_completed_position.php" class="btn btn-sm btn-success">
                        <i class="fas fa-plus fa-sm"></i> Add Completed Position
                    </a>
                </div>
                <div class="card-body">
                    <?php if ($investments_result && $investments_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="investmentsTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Plan</th>
                                        <th>Amount</th>
                                        <th>ROI</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($investment = $investments_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $investment['id'] ?></td>
                                            <td>
                                                <?= htmlspecialchars($investment['first_name'] . ' ' . $investment['last_name']) ?>
                                                <br>
                                                <small><?= htmlspecialchars($investment['email']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($investment['plan_name']) ?></td>
                                            <td>$<?= number_format($investment['amount'], 2) ?></td>
                                            <td>
                                                $<?= number_format($investment['roi_expected'], 2) ?>
                                                <br>
                                                <small><?= $investment['roi_percentage'] ?>%</small>
                                            </td>
                                            <td><?= date('Y-m-d', strtotime($investment['started_at'])) ?></td>
                                            <td><?= date('Y-m-d', strtotime($investment['ends_at'])) ?></td>
                                            <td>
                                                <span class="badge badge-success">Active</span>
                                            </td>
                                            <td>
                                                <form method="post" onsubmit="return confirmComplete('investment', <?= $investment['id'] ?>);">
                                                    <input type="hidden" name="investment_id" value="<?= $investment['id'] ?>">
                                                    <button type="submit" name="complete_investment" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-check fa-sm"></i> Complete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            No active investments found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Active Staking Positions -->
        <div class="col-xl-12 col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Active Staking Positions</h6>
                </div>
                <div class="card-body">
                    <?php if ($staking_result && $staking_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="stakingTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Plan</th>
                                        <th>Amount</th>
                                        <th>Earned Reward</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($staking = $staking_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $staking['id'] ?></td>
                                            <td>
                                                <?= htmlspecialchars($staking['first_name'] . ' ' . $staking['last_name']) ?>
                                                <br>
                                                <small><?= htmlspecialchars($staking['email']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($staking['plan_name']) ?></td>
                                            <td>$<?= number_format($staking['amount'], 2) ?></td>
                                            <td>
                                                $<?= number_format($staking['earned_reward'], 2) ?>
                                                <br>
                                                <small><?= $staking['reward_percent'] ?>%</small>
                                            </td>
                                            <td><?= date('Y-m-d', strtotime($staking['started_at'])) ?></td>
                                            <td><?= date('Y-m-d', strtotime($staking['ends_at'])) ?></td>
                                            <td>
                                                <span class="badge badge-success">Active</span>
                                            </td>
                                            <td>
                                                <form method="post" onsubmit="return confirmComplete('staking', <?= $staking['id'] ?>);">
                                                    <input type="hidden" name="staking_id" value="<?= $staking['id'] ?>">
                                                    <button type="submit" name="complete_staking" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-check fa-sm"></i> Complete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            No active staking positions found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTables if available
    if ($.fn.DataTable) {
        $('#investmentsTable').DataTable({
            order: [[0, 'desc']]
        });
        $('#stakingTable').DataTable({
            order: [[0, 'desc']]
        });
    }
});

function confirmComplete(type, id) {
    let message = '';
    
    if (type === 'investment') {
        message = 'Are you sure you want to complete this investment?\n\nThis will:\n- Mark the investment as completed\n- Credit the principal and ROI to the user\'s main balance\n- Create a transaction record';
    } else if (type === 'staking') {
        message = 'Are you sure you want to complete this staking position?\n\nThis will:\n- Mark the staking position as completed\n- Credit the principal and earned rewards to the user\'s main balance\n- Create a transaction record';
    }
    
    return confirm(message);
}
</script>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 