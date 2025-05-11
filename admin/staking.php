<?php
// Admin Staking Management Page
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Staking Management";
$current_page = "staking.php";
$message = "";
$error = "";

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cancel staking
    if (isset($_POST['cancel_staking'])) {
        $staking_id = intval($_POST['staking_id']);
        
        // Get staking details
        $stmt = $conn_back->prepare("
            SELECT s.*, p.name as plan_name 
            FROM staking s 
            JOIN staking_plans p ON s.plan_id = p.id 
            WHERE s.id = ? AND s.status = 'active'
        ");
        $stmt->bind_param("i", $staking_id);
        $stmt->execute();
        $staking = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($staking) {
            // Begin transaction
            $conn_back->begin_transaction();
            
            try {
                // Update staking status
                $stmt = $conn_back->prepare("
                    UPDATE staking 
                    SET status = 'cancelled',
                        updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->bind_param("i", $staking_id);
                $result = $stmt->execute();
                $stmt->close();
                
                if (!$result) throw new Exception("Failed to update staking status");
                
                // Return staked amount to user
                $stmt = $conn_back->prepare("
                    UPDATE users 
                    SET main_balance = main_balance + ? 
                    WHERE id = ?
                ");
                $stmt->bind_param("di", $staking['amount'], $staking['user_id']);
                $result = $stmt->execute();
                $stmt->close();
                
                if (!$result) throw new Exception("Failed to update user balance");
                
                // Create transaction record
                $description = "Staking cancelled and funds returned. Plan: " . $staking['plan_name'];
                $stmt = $conn_back->prepare("
                    INSERT INTO transactions (
                        user_id, amount, transaction_type, status, 
                        description, date_time
                    ) VALUES (?, ?, 'staking_return', 'completed', ?, NOW())
                ");
                $stmt->bind_param("ids", $staking['user_id'], $staking['amount'], $description);
                $result = $stmt->execute();
                $stmt->close();
                
                if (!$result) throw new Exception("Failed to create transaction record");
                
                // Log admin activity
                $admin_id = $_SESSION['admin_id'];
                $action = "Cancelled staking position #$staking_id for user #" . $staking['user_id'] . " and returned $" . number_format($staking['amount'], 2);
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $admin_id, $action, $ip);
                $stmt->execute();
                $stmt->close();
                
                // Commit transaction
                $conn_back->commit();
                
                $message = "Staking position #$staking_id has been cancelled and $" . number_format($staking['amount'], 2) . " has been returned to the user.";
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn_back->rollback();
                $error = "Error cancelling staking position: " . $e->getMessage();
            }
        } else {
            $error = "Staking position not found or already cancelled.";
        }
    }
    
    // Toggle compounding
    if (isset($_POST['toggle_compounding'])) {
        $staking_id = intval($_POST['staking_id']);
        $is_compounding = isset($_POST['is_compounding']) ? 1 : 0;
        
        $stmt = $conn_back->prepare("
            UPDATE staking 
            SET is_compounding = ?,
                updated_at = NOW()
            WHERE id = ? AND status = 'active'
        ");
        $stmt->bind_param("ii", $is_compounding, $staking_id);
        
        if ($stmt->execute()) {
            // Log admin activity
            $admin_id = $_SESSION['admin_id'];
            $status_text = $is_compounding ? 'enabled' : 'disabled';
            $action = "Changed compounding to '$status_text' for staking position #$staking_id";
            $ip = $_SERVER['REMOTE_ADDR'];
            
            $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
            $log_stmt->bind_param("iss", $admin_id, $action, $ip);
            $log_stmt->execute();
            
            $message = "Compounding status updated successfully for staking position #$staking_id.";
        } else {
            $error = "Failed to update compounding status: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

// Check if staking_positions table exists
$table_exists = false;
$result = $conn_back->query("SHOW TABLES LIKE 'staking'");
if ($result && $result->num_rows > 0) {
    $table_exists = true;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 20;
$offset = ($page - 1) * $records_per_page;

// Filtering
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$plan_id = isset($_GET['plan_id']) ? (int)$_GET['plan_id'] : 0;

// Build query condition
$condition = "1=1";
$params = [];
$types = "";

if (!empty($status_filter)) {
    $condition .= " AND s.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if ($user_id > 0) {
    $condition .= " AND s.user_id = ?";
    $params[] = $user_id;
    $types .= "i";
}

if ($plan_id > 0) {
    $condition .= " AND s.plan_id = ?";
    $params[] = $plan_id;
    $types .= "i";
}

// Get staking statistics
$stats = [
    'total_active' => 0,
    'total_amount' => 0,
    'total_pending' => 0,
    'total_completed' => 0,
    'total_rewards' => 0
];

if ($table_exists) {
    // Get statistics
    $stats_sql = "
        SELECT 
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active_count,
            IFNULL(SUM(CASE WHEN status = 'active' THEN amount ELSE 0 END), 0) as active_amount,
            0 as pending_count,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
            IFNULL(SUM(earned_reward), 0) as total_rewards
        FROM staking
    ";
    $stats_result = $conn_back->query($stats_sql);
    if ($stats_result && $row = $stats_result->fetch_assoc()) {
        $stats['total_active'] = $row['active_count'];
        $stats['total_amount'] = $row['active_amount'];
        $stats['total_pending'] = $row['pending_count'];
        $stats['total_completed'] = $row['completed_count'];
        $stats['total_rewards'] = $row['total_rewards'];
    }
    
    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) as total FROM staking s WHERE $condition";
    $stmt = $conn_back->prepare($count_sql);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_records = $row['total'];
    $total_pages = ceil($total_records / $records_per_page);
    $stmt->close();

    // Get staking positions
    $sql = "
        SELECT 
            s.*,
            CONCAT(u.first_name, ' ', u.last_name) as username,
            u.email,
            p.name as plan_name,
            p.roi_daily,
            p.lock_period_days as lockup_period
        FROM staking s
        JOIN users u ON s.user_id = u.id
        JOIN staking_plans p ON s.plan_id = p.id
        WHERE $condition
        ORDER BY s.created_at DESC
        LIMIT ? OFFSET ?
    ";
    $types .= "ii";
    $params[] = $records_per_page;
    $params[] = $offset;

    $stmt = $conn_back->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stakings = $stmt->get_result();
    $stmt->close();

    // Get staking plans for filter
    $plans_result = $conn_back->query("SELECT id, name FROM staking_plans ORDER BY name");
    $plans = [];
    if ($plans_result->num_rows > 0) {
        while ($row = $plans_result->fetch_assoc()) {
            $plans[] = $row;
        }
    }
}

include_once __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Staking Management</h1>
        <a href="create_staking.php" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i> Create Staking Position
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

    <?php if (!$table_exists): ?>
        <div class="alert alert-warning">
            <h4 class="alert-heading">Staking tables not found!</h4>
            <p>The staking table does not exist in the database. Please run the database initialization script.</p>
            <hr>
            <a href="db_init.php" class="btn btn-primary">Initialize Database</a>
        </div>
    <?php else: ?>
        <!-- Staking Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Active Staking Positions</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['total_active'] ?? 0) ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-coins fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Staked Amount</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">$<?= number_format($stats['total_amount'] ?? 0, 2) ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Pending Requests</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['total_pending'] ?? 0) ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Rewards Paid</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">$<?= number_format($stats['total_rewards'] ?? 0, 2) ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-trophy fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
            </div>
            <div class="card-body">
                <form method="get" class="form-inline">
                    <div class="form-group mb-2 mr-3">
                        <label for="status" class="mr-2">Status:</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="active" <?= $status_filter == 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="completed" <?= $status_filter == 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= $status_filter == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group mb-2 mr-3">
                        <label for="plan_id" class="mr-2">Plan:</label>
                        <select class="form-control" id="plan_id" name="plan_id">
                            <option value="0">All Plans</option>
                            <?php foreach ($plans as $plan): ?>
                                <option value="<?= $plan['id'] ?>" <?= $plan_id == $plan['id'] ? 'selected' : '' ?>><?= htmlspecialchars($plan['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-2 mr-3">
                        <label for="user_id" class="mr-2">User ID:</label>
                        <input type="number" class="form-control" id="user_id" name="user_id" value="<?= $user_id > 0 ? $user_id : '' ?>" placeholder="Enter User ID">
                    </div>
                    <button type="submit" class="btn btn-primary mb-2 mr-2">Apply Filters</button>
                    <a href="staking.php" class="btn btn-secondary mb-2">Clear Filters</a>
                </form>
            </div>
        </div>

        <!-- Staking Positions Table -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Staking Positions</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="stakingTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Plan</th>
                                <th>Amount</th>
                                <th>APY %</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Rewards</th>
                                <th>Compounding</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($stakings->num_rows > 0): ?>
                                <?php while ($staking = $stakings->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $staking['id'] ?></td>
                                        <td>
                                            <a href="user_detail.php?id=<?= $staking['user_id'] ?>" title="View User Details">
                                                <?= htmlspecialchars($staking['username']) ?>
                                            </a>
                                            <br>
                                            <small class="text-muted"><?= $staking['email'] ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($staking['plan_name']) ?></td>
                                        <td>$<?= number_format($staking['amount'], 2) ?></td>
                                        <td><?= $staking['roi_daily'] ?>%</td>
                                        <td>
                                            <?php if ($staking['status'] == 'active'): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php elseif ($staking['status'] == 'completed'): ?>
                                                <span class="badge badge-primary">Completed</span>
                                            <?php elseif ($staking['status'] == 'cancelled'): ?>
                                                <span class="badge badge-danger">Cancelled</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('Y-m-d H:i', strtotime($staking['created_at'])) ?></td>
                                        <td>
                                            <strong>$<?= number_format($staking['earned_reward'], 2) ?></strong>
                                            <?php if ($staking['status'] == 'active'): ?>
                                                <br>
                                                <small class="text-muted">
                                                    Last: <?= $staking['last_compound_at'] ? date('Y-m-d', strtotime($staking['last_compound_at'])) : 'None' ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($staking['is_compounding'] == 1): ?>
                                                <span class="badge badge-success">Yes</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="dropdown no-arrow">
                                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                                                    <div class="dropdown-header">Actions:</div>
                                                    <a class="dropdown-item" href="user_detail.php?id=<?= $staking['user_id'] ?>">
                                                        <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i> View User
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    
                                                    <?php if ($staking['status'] == 'active'): ?>
                                                        <button class="dropdown-item text-danger cancel-staking" data-toggle="modal" data-target="#cancelModal" data-id="<?= $staking['id'] ?>" data-user="<?= htmlspecialchars($staking['username']) ?>" data-amount="<?= number_format($staking['amount'], 2) ?>">
                                                            <i class="fas fa-ban fa-sm fa-fw mr-2 text-danger"></i> Cancel
                                                        </button>
                                                        <button class="dropdown-item toggle-compounding" data-toggle="modal" data-target="#compoundingModal" data-id="<?= $staking['id'] ?>" data-compounding="<?= $staking['is_compounding'] ?>">
                                                            <i class="fas fa-sync fa-sm fa-fw mr-2 text-info"></i> Toggle Compounding
                                                        </button>
                                                        <a class="dropdown-item" href="staking_rewards.php?process=<?= $staking['id'] ?>">
                                                            <i class="fas fa-coins fa-sm fa-fw mr-2 text-warning"></i> Process Reward
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center">No staking positions found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&status=<?= $status_filter ?>&plan_id=<?= $plan_id ?>&user_id=<?= $user_id ?>" tabindex="-1">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&status=<?= $status_filter ?>&plan_id=<?= $plan_id ?>&user_id=<?= $user_id ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&status=<?= $status_filter ?>&plan_id=<?= $plan_id ?>&user_id=<?= $user_id ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Approve Staking Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">Approve Staking Position</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="staking_id" id="approve_staking_id">
                    <p>Are you sure you want to approve the staking position for <strong id="approve_user"></strong> with an amount of <strong id="approve_amount"></strong>?</p>
                    <p>This will activate the staking position and start generating rewards.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="approve_staking" class="btn btn-success">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Staking Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Reject Staking Position</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="staking_id" id="reject_staking_id">
                    <p>Are you sure you want to reject the staking position for <strong id="reject_user"></strong> with an amount of <strong id="reject_amount"></strong>?</p>
                    <p>This will mark the position as rejected and refund the amount to the user.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="reject_staking" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Staking Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">Cancel Staking Position</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <p>Are you sure you want to cancel the staking position for <span id="cancelUserName"></span>?</p>
                    <p>This will return <span id="cancelAmount"></span> to the user's balance.</p>
                    <input type="hidden" name="staking_id" id="cancelStakingId" value="">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="cancel_staking" class="btn btn-danger">Yes, Cancel Staking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Compounding Modal -->
<div class="modal fade" id="compoundingModal" tabindex="-1" role="dialog" aria-labelledby="compoundingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="compoundingModalLabel">Change Compounding Setting</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <p>Update compounding setting for staking position #<span id="compoundingStakingIdDisplay"></span>:</p>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="compoundingSwitch" name="is_compounding">
                            <label class="custom-control-label" for="compoundingSwitch">Enable Compounding</label>
                        </div>
                    </div>
                    <input type="hidden" name="staking_id" id="compoundingStakingId" value="">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="toggle_compounding" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#stakingTable').DataTable({
        "paging": false,
        "ordering": true,
        "info": false,
        "searching": false
    });
    
    // Cancel staking modal
    $('.cancel-staking').click(function() {
        var id = $(this).data('id');
        var user = $(this).data('user');
        var amount = $(this).data('amount');
        
        $('#cancelStakingId').val(id);
        $('#cancelUserName').text(user);
        $('#cancelAmount').text('$' + amount);
    });
    
    // Toggle compounding modal
    $('.toggle-compounding').click(function() {
        var id = $(this).data('id');
        var is_compounding = $(this).data('compounding');
        
        $('#compoundingStakingId').val(id);
        $('#compoundingStakingIdDisplay').text(id);
        $('#compoundingSwitch').prop('checked', is_compounding == 1);
    });
});
</script>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 