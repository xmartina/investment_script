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
            FROM staking_positions s 
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
                    UPDATE staking_positions 
                    SET status = 'cancelled', 
                        unstaked_at = NOW(),
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
    
    // Approve pending staking
    if (isset($_POST['approve_staking'])) {
        $staking_id = intval($_POST['staking_id']);
        
        // Get staking details
        $stmt = $conn_back->prepare("
            SELECT s.*, p.name as plan_name 
            FROM staking_positions s 
            JOIN staking_plans p ON s.plan_id = p.id 
            WHERE s.id = ? AND s.status = 'pending'
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
                    UPDATE staking_positions 
                    SET status = 'active', 
                        updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->bind_param("i", $staking_id);
                $result = $stmt->execute();
                $stmt->close();
                
                if (!$result) throw new Exception("Failed to update staking status");
                
                // Create transaction record
                $description = "Staking position approved. Plan: " . $staking['plan_name'];
                $stmt = $conn_back->prepare("
                    INSERT INTO transactions (
                        user_id, amount, transaction_type, status, 
                        description, date_time
                    ) VALUES (?, ?, 'staking_approval', 'completed', ?, NOW())
                ");
                $stmt->bind_param("ids", $staking['user_id'], $staking['amount'], $description);
                $result = $stmt->execute();
                $stmt->close();
                
                if (!$result) throw new Exception("Failed to create transaction record");
                
                // Log admin activity
                $admin_id = $_SESSION['admin_id'];
                $action = "Approved staking position #$staking_id for user #" . $staking['user_id'] . " with amount $" . number_format($staking['amount'], 2);
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $admin_id, $action, $ip);
                $stmt->execute();
                $stmt->close();
                
                // Commit transaction
                $conn_back->commit();
                
                $message = "Staking position #$staking_id has been approved successfully.";
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn_back->rollback();
                $error = "Error approving staking position: " . $e->getMessage();
            }
        } else {
            $error = "Staking position not found or not in pending status.";
        }
    }
    
    // Reject pending staking
    if (isset($_POST['reject_staking'])) {
        $staking_id = intval($_POST['staking_id']);
        
        // Get staking details
        $stmt = $conn_back->prepare("
            SELECT s.*, p.name as plan_name 
            FROM staking_positions s 
            JOIN staking_plans p ON s.plan_id = p.id 
            WHERE s.id = ? AND s.status = 'pending'
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
                    UPDATE staking_positions 
                    SET status = 'rejected', 
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
                $description = "Staking position rejected. Funds returned. Plan: " . $staking['plan_name'];
                $stmt = $conn_back->prepare("
                    INSERT INTO transactions (
                        user_id, amount, transaction_type, status, 
                        description, date_time
                    ) VALUES (?, ?, 'staking_rejected', 'completed', ?, NOW())
                ");
                $stmt->bind_param("ids", $staking['user_id'], $staking['amount'], $description);
                $result = $stmt->execute();
                $stmt->close();
                
                if (!$result) throw new Exception("Failed to create transaction record");
                
                // Log admin activity
                $admin_id = $_SESSION['admin_id'];
                $action = "Rejected staking position #$staking_id for user #" . $staking['user_id'] . " and returned $" . number_format($staking['amount'], 2);
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $admin_id, $action, $ip);
                $stmt->execute();
                $stmt->close();
                
                // Commit transaction
                $conn_back->commit();
                
                $message = "Staking position #$staking_id has been rejected and $" . number_format($staking['amount'], 2) . " has been returned to the user.";
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn_back->rollback();
                $error = "Error rejecting staking position: " . $e->getMessage();
            }
        } else {
            $error = "Staking position not found or not in pending status.";
        }
    }
    
    // Toggle compounding
    if (isset($_POST['toggle_compounding'])) {
        $staking_id = intval($_POST['staking_id']);
        $is_compounding = isset($_POST['is_compounding']) ? 1 : 0;
        
        $stmt = $conn_back->prepare("
            UPDATE staking_positions 
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
$result = $conn_back->query("SHOW TABLES LIKE 'staking_positions'");
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
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
            IFNULL(SUM(total_rewards), 0) as total_rewards
        FROM staking_positions
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
    $count_sql = "SELECT COUNT(*) as total FROM staking_positions s WHERE $condition";
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
            p.lockup_period
        FROM staking_positions s
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
            <p>The staking_positions table does not exist in the database. Please run the database initialization script.</p>
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
                            <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="active" <?= $status_filter == 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="completed" <?= $status_filter == 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= $status_filter == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            <option value="rejected" <?= $status_filter == 'rejected' ? 'selected' : '' ?>>Rejected</option>
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
                                <th>Daily ROI</th>
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
                                            <?php elseif ($staking['status'] == 'pending'): ?>
                                                <span class="badge badge-warning">Pending</span>
                                            <?php elseif ($staking['status'] == 'completed'): ?>
                                                <span class="badge badge-primary">Completed</span>
                                            <?php elseif ($staking['status'] == 'cancelled'): ?>
                                                <span class="badge badge-danger">Cancelled</span>
                                            <?php elseif ($staking['status'] == 'rejected'): ?>
                                                <span class="badge badge-dark">Rejected</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('Y-m-d H:i', strtotime($staking['created_at'])) ?></td>
                                        <td>
                                            <strong>$<?= number_format($staking['total_rewards'], 2) ?></strong>
                                            <?php if ($staking['status'] == 'active'): ?>
                                                <br>
                                                <small class="text-muted">
                                                    Last: <?= $staking['last_reward_date'] ? date('Y-m-d', strtotime($staking['last_reward_date'])) : 'None' ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($staking['status'] == 'active'): ?>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="staking_id" value="<?= $staking['id'] ?>">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="compounding_<?= $staking['id'] ?>" name="is_compounding" <?= $staking['is_compounding'] ? 'checked' : '' ?> onchange="this.form.submit()">
                                                        <label class="custom-control-label" for="compounding_<?= $staking['id'] ?>"></label>
                                                    </div>
                                                    <input type="hidden" name="toggle_compounding" value="1">
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="create_staking.php?edit=<?= $staking['id'] ?>">
                                                        <i class="fas fa-edit fa-sm fa-fw mr-2 text-gray-400"></i> Edit
                                                    </a>
                                                    <a class="dropdown-item" href="staking_rewards.php?staking_id=<?= $staking['id'] ?>">
                                                        <i class="fas fa-trophy fa-sm fa-fw mr-2 text-gray-400"></i> View Rewards
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    
                                                    <?php if ($staking['status'] == 'pending'): ?>
                                                        <button class="dropdown-item text-success approve-staking" data-toggle="modal" data-target="#approveModal" data-id="<?= $staking['id'] ?>" data-user="<?= htmlspecialchars($staking['username']) ?>" data-amount="<?= number_format($staking['amount'], 2) ?>">
                                                            <i class="fas fa-check fa-sm fa-fw mr-2 text-success"></i> Approve
                                                        </button>
                                                        <button class="dropdown-item text-danger reject-staking" data-toggle="modal" data-target="#rejectModal" data-id="<?= $staking['id'] ?>" data-user="<?= htmlspecialchars($staking['username']) ?>" data-amount="<?= number_format($staking['amount'], 2) ?>">
                                                            <i class="fas fa-times fa-sm fa-fw mr-2 text-danger"></i> Reject
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($staking['status'] == 'active'): ?>
                                                        <button class="dropdown-item text-danger cancel-staking" data-toggle="modal" data-target="#cancelModal" data-id="<?= $staking['id'] ?>" data-user="<?= htmlspecialchars($staking['username']) ?>" data-amount="<?= number_format($staking['amount'], 2) ?>">
                                                            <i class="fas fa-ban fa-sm fa-fw mr-2 text-danger"></i> Cancel
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="staking_id" id="cancel_staking_id">
                    <p>Are you sure you want to cancel the staking position for <strong id="cancel_user"></strong> with an amount of <strong id="cancel_amount"></strong>?</p>
                    <p class="text-danger">This will cancel the active position and return the staked amount to the user. Any accumulated rewards will remain with the user.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" name="cancel_staking" class="btn btn-danger">Cancel Staking</button>
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
    
    // Set up modal data
    $('.approve-staking').on('click', function() {
        var id = $(this).data('id');
        var user = $(this).data('user');
        var amount = $(this).data('amount');
        
        $('#approve_staking_id').val(id);
        $('#approve_user').text(user);
        $('#approve_amount').text('$' + amount);
    });
    
    $('.reject-staking').on('click', function() {
        var id = $(this).data('id');
        var user = $(this).data('user');
        var amount = $(this).data('amount');
        
        $('#reject_staking_id').val(id);
        $('#reject_user').text(user);
        $('#reject_amount').text('$' + amount);
    });
    
    $('.cancel-staking').on('click', function() {
        var id = $(this).data('id');
        var user = $(this).data('user');
        var amount = $(this).data('amount');
        
        $('#cancel_staking_id').val(id);
        $('#cancel_user').text(user);
        $('#cancel_amount').text('$' + amount);
    });
});
</script>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 