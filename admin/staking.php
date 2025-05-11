<?php
// Staking management page for admin panel
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Include necessary files
try {
    require_once __DIR__ . '/include/config.php';
    
    // Set current page for menu highlighting
    $current_page = 'staking.php';
    
    require_once __DIR__ . '/layout/header.php';
    require_once __DIR__ . '/layout/breadcrumb.php';
} catch (Exception $e) {
    echo "Error loading required files: " . $e->getMessage();
    exit;
}

// Process actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle staking status change
    if (isset($_POST['action']) && $_POST['action'] == 'change_status' && isset($_POST['staking_id'])) {
        $staking_id = (int)$_POST['staking_id'];
        $new_status = $conn_back->real_escape_string($_POST['status']);
        
        if (in_array($new_status, ['active', 'completed', 'cancelled'])) {
            $stmt = $conn_back->prepare("UPDATE staking SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $staking_id);
            
            if ($stmt->execute()) {
                // If cancelled, handle refund
                if ($new_status == 'cancelled') {
                    // Get the staking details
                    $staking_query = $conn_back->prepare("
                        SELECT s.user_id, s.amount, u.username, s.plan_id, s.earned_reward
                        FROM staking s
                        JOIN users u ON s.user_id = u.id
                        WHERE s.id = ?
                    ");
                    $staking_query->bind_param("i", $staking_id);
                    $staking_query->execute();
                    $staking_result = $staking_query->get_result();
                    
                    if ($staking_row = $staking_result->fetch_assoc()) {
                        $user_id = $staking_row['user_id'];
                        $staking_amount = $staking_row['amount'];
                        $username = $staking_row['username'];
                        $plan_id = $staking_row['plan_id'];
                        $earned_reward = $staking_row['earned_reward'];
                        
                        // Get early unstaking penalty from staking plan
                        $penalty_query = $conn_back->prepare("
                            SELECT early_unstake_penalty FROM staking_plans WHERE id = ?
                        ");
                        $penalty_query->bind_param("i", $plan_id);
                        $penalty_query->execute();
                        $penalty_result = $penalty_query->get_result();
                        $penalty_percent = 0;
                        
                        if ($penalty_row = $penalty_result->fetch_assoc()) {
                            $penalty_percent = $penalty_row['early_unstake_penalty'];
                        }
                        
                        // Calculate penalty amount
                        $penalty_amount = ($staking_amount * $penalty_percent) / 100;
                        $refund_amount = $staking_amount - $penalty_amount + $earned_reward;
                        
                        // Begin transaction
                        $conn_back->begin_transaction();
                        
                        try {
                            // Add transaction record for the refund
                            $refund_txn = $conn_back->prepare("
                                INSERT INTO transactions (user_id, transaction_type, amount, status, description, date_time) 
                                VALUES (?, 'refund', ?, 'completed', ?, NOW())
                            ");
                            $refund_desc = "Refund for cancelled staking #{$staking_id}. Applied penalty: {$penalty_percent}%";
                            $refund_txn->bind_param("ids", $user_id, $refund_amount, $refund_desc);
                            $refund_txn->execute();
                            
                            // Update user's balance
                            $update_balance = $conn_back->prepare("
                                UPDATE users SET main_balance = main_balance + ?, staking_balance = staking_balance - ? WHERE id = ?
                            ");
                            $update_balance->bind_param("ddi", $refund_amount, $staking_amount, $user_id);
                            $update_balance->execute();
                            
                            // Commit transaction
                            $conn_back->commit();
                            
                            // Log the action
                            $admin_id = $_SESSION['admin_id'];
                            $action = "Cancelled staking ID {$staking_id} for user {$username} (ID: {$user_id}) and refunded {$refund_amount}";
                            $ip = $_SERVER['REMOTE_ADDR'];
                            
                            $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                            $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                            $log_stmt->execute();
                            
                            $success_message = "Staking cancelled and {$refund_amount} refunded to user account.";
                        } catch (Exception $e) {
                            $conn_back->rollback();
                            $error_message = "Error during refund process: " . $e->getMessage();
                        }
                    } else {
                        $error_message = "Failed to retrieve staking details.";
                    }
                } else {
                    // Log the action
                    $admin_id = $_SESSION['admin_id'];
                    $action = "Changed staking ID {$staking_id} status to {$new_status}";
                    $ip = $_SERVER['REMOTE_ADDR'];
                    
                    $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                    $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                    $log_stmt->execute();
                    
                    $success_message = "Staking status updated successfully.";
                }
            } else {
                $error_message = "Failed to update staking status: " . $conn_back->error;
            }
        } else {
            $error_message = "Invalid status value.";
        }
    }
    
    // Handle compound toggle
    if (isset($_POST['action']) && $_POST['action'] == 'toggle_compound' && isset($_POST['staking_id'])) {
        $staking_id = (int)$_POST['staking_id'];
        $compound_value = isset($_POST['compound']) && $_POST['compound'] == 1 ? 1 : 0;
        
        $stmt = $conn_back->prepare("UPDATE staking SET is_compounding = ?, last_compound_at = NOW() WHERE id = ?");
        $stmt->bind_param("ii", $compound_value, $staking_id);
        
        if ($stmt->execute()) {
            // Log the action
            $admin_id = $_SESSION['admin_id'];
            $action = "Updated staking ID {$staking_id} compounding to " . ($compound_value ? 'enabled' : 'disabled');
            $ip = $_SERVER['REMOTE_ADDR'];
            
            $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
            $log_stmt->bind_param("iss", $admin_id, $action, $ip);
            $log_stmt->execute();
            
            $success_message = "Compounding option updated successfully.";
        } else {
            $error_message = "Failed to update compounding option: " . $conn_back->error;
        }
    }
}

// Handle search and filtering
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$user_id_filter = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$plan_id_filter = isset($_GET['plan_id']) ? (int)$_GET['plan_id'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build query conditions
$where_conditions = [];
$where_clause = "";

if (!empty($search)) {
    $search_term = $conn_back->real_escape_string($search);
    $where_conditions[] = "(u.username LIKE '%{$search_term}%' OR 
                           u.email LIKE '%{$search_term}%' OR 
                           CONCAT(u.first_name, ' ', u.last_name) LIKE '%{$search_term}%')";
}

if (!empty($status_filter)) {
    $status = $conn_back->real_escape_string($status_filter);
    $where_conditions[] = "s.status = '{$status}'";
}

if ($user_id_filter > 0) {
    $where_conditions[] = "s.user_id = {$user_id_filter}";
}

if ($plan_id_filter > 0) {
    $where_conditions[] = "s.plan_id = {$plan_id_filter}";
}

if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM staking s 
                LEFT JOIN users u ON s.user_id = u.id 
                LEFT JOIN staking_plans sp ON s.plan_id = sp.id 
                {$where_clause}";
$count_result = $conn_back->query($count_query);
$total_rows = 0;
if ($count_result && $row = $count_result->fetch_assoc()) {
    $total_rows = $row['total'];
}
$total_pages = ceil($total_rows / $per_page);

// Get staking data
$query = "SELECT s.*, 
          CONCAT(u.first_name, ' ', u.last_name) as full_name,
          u.username, u.email,
          sp.name as plan_name, sp.duration_days as plan_duration, sp.lock_period_days, sp.early_unstake_penalty
          FROM staking s 
          LEFT JOIN users u ON s.user_id = u.id 
          LEFT JOIN staking_plans sp ON s.plan_id = sp.id 
          {$where_clause}
          ORDER BY s.created_at DESC
          LIMIT {$offset}, {$per_page}";

$result = $conn_back->query($query);
$stakings = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $stakings[] = $row;
    }
}

// Get staking plans for filter dropdown
$plans_query = "SELECT id, name FROM staking_plans ORDER BY name";
$plans_result = $conn_back->query($plans_query);
$staking_plans = [];
if ($plans_result) {
    while ($row = $plans_result->fetch_assoc()) {
        $staking_plans[] = $row;
    }
}

// Get summary statistics
$stats_query = "SELECT 
                COUNT(*) as total_stakes,
                SUM(amount) as total_staked,
                SUM(CASE WHEN status = 'active' THEN amount ELSE 0 END) as active_amount,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_count,
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as completed_amount,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
                SUM(earned_reward) as total_rewards
                FROM staking";
$stats_result = $conn_back->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<!-- Main content -->
<section class="content">
    <!-- Stats boxes -->
    <div class="row">
        <div class="col-xl-3 col-md-6 col-12">
            <div class="box bg-primary bg-hover-primary">
                <div class="box-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-white"><?php echo number_format($stats['total_stakes'] ?? 0); ?></h4>
                            <p class="text-white mb-0">Total Stakes</p>
                        </div>
                        <div>
                            <h4 class="text-white">$<?php echo number_format($stats['total_staked'] ?? 0, 2); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="box bg-success bg-hover-success">
                <div class="box-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-white"><?php echo $stats['active_count'] ?? 0; ?></h4>
                            <p class="text-white mb-0">Active Stakes</p>
                        </div>
                        <div>
                            <h4 class="text-white">$<?php echo number_format($stats['active_amount'] ?? 0, 2); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="box bg-info bg-hover-info">
                <div class="box-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-white"><?php echo $stats['completed_count'] ?? 0; ?></h4>
                            <p class="text-white mb-0">Completed Stakes</p>
                        </div>
                        <div>
                            <h4 class="text-white">$<?php echo number_format($stats['completed_amount'] ?? 0, 2); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="box bg-warning bg-hover-warning">
                <div class="box-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-white">$<?php echo number_format($stats['total_rewards'] ?? 0, 2); ?></h4>
                            <p class="text-white mb-0">Total Rewards Earned</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="box">
                <div class="box-header with-border">
                    <h4 class="box-title">Staking Management</h4>
                    <div class="box-controls pull-right d-flex">
                        <form class="me-2" method="GET">
                            <?php if (!empty($search)): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <?php endif; ?>
                            <?php if ($user_id_filter > 0): ?>
                            <input type="hidden" name="user_id" value="<?php echo $user_id_filter; ?>">
                            <?php endif; ?>
                            
                            <div class="input-group">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-filter"></i>
                                </button>
                            </div>
                        </form>
                        
                        <form class="me-2" method="GET">
                            <?php if (!empty($search)): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <?php endif; ?>
                            <?php if (!empty($status_filter)): ?>
                            <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                            <?php endif; ?>
                            
                            <div class="input-group">
                                <select name="plan_id" class="form-select">
                                    <option value="0">All Plans</option>
                                    <?php foreach ($staking_plans as $plan): ?>
                                    <option value="<?php echo $plan['id']; ?>" <?php echo $plan_id_filter == $plan['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($plan['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-filter"></i>
                                </button>
                            </div>
                        </form>
                        
                        <form method="GET">
                            <?php if (!empty($status_filter)): ?>
                            <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                            <?php endif; ?>
                            <?php if ($plan_id_filter > 0): ?>
                            <input type="hidden" name="plan_id" value="<?php echo $plan_id_filter; ?>">
                            <?php endif; ?>
                            
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="box-body p-0">
                    <?php if (isset($success_message)): ?>
                    <div class="alert alert-success m-3"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger m-3"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Plan</th>
                                    <th>Amount</th>
                                    <th>Reward / APY</th>
                                    <th>Duration</th>
                                    <th>Compounding</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($stakings) > 0): ?>
                                    <?php foreach ($stakings as $staking): ?>
                                    <tr>
                                        <td><?php echo $staking['id']; ?></td>
                                        <td>
                                            <a href="user_detail.php?id=<?php echo $staking['user_id']; ?>">
                                                <?php echo htmlspecialchars($staking['full_name']); ?>
                                            </a>
                                            <small class="d-block text-muted"><?php echo htmlspecialchars($staking['username']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($staking['plan_name']); ?></td>
                                        <td>$<?php echo number_format($staking['amount'], 2); ?></td>
                                        <td>
                                            $<?php echo number_format($staking['earned_reward'], 2); ?> / <?php echo $staking['apy']; ?>%
                                        </td>
                                        <td>
                                            <?php echo $staking['duration_days']; ?> days
                                            <small class="d-block text-muted">
                                                <?php if ($staking['status'] == 'active'): ?>
                                                <?php 
                                                    $start_date = new DateTime($staking['started_at']);
                                                    $end_date = new DateTime($staking['ends_at']);
                                                    $now = new DateTime();
                                                    $progress = min(100, ($now->getTimestamp() - $start_date->getTimestamp()) / ($end_date->getTimestamp() - $start_date->getTimestamp()) * 100);
                                                ?>
                                                <div class="progress progress-xs mt-1">
                                                    <div class="progress-bar bg-success" style="width: <?php echo $progress; ?>%"></div>
                                                </div>
                                                <?php endif; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if ($staking['status'] == 'active'): ?>
                                            <form method="post">
                                                <input type="hidden" name="action" value="toggle_compound">
                                                <input type="hidden" name="staking_id" value="<?php echo $staking['id']; ?>">
                                                <input type="hidden" name="compound" value="<?php echo $staking['is_compounding'] ? '0' : '1'; ?>">
                                                <button type="submit" class="btn btn-xs <?php echo $staking['is_compounding'] ? 'btn-success' : 'btn-outline-success'; ?>">
                                                    <?php echo $staking['is_compounding'] ? 'On' : 'Off'; ?>
                                                </button>
                                            </form>
                                            <?php else: ?>
                                            <span class="badge <?php echo $staking['is_compounding'] ? 'badge-success' : 'badge-light'; ?>">
                                                <?php echo $staking['is_compounding'] ? 'Enabled' : 'Disabled'; ?>
                                            </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php 
                                                if ($staking['status'] == 'active') echo 'badge-success';
                                                elseif ($staking['status'] == 'completed') echo 'badge-info';
                                                else echo 'badge-danger';
                                            ?>">
                                                <?php echo ucfirst($staking['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($staking['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Action
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="staking_rewards.php?staking_id=<?php echo $staking['id']; ?>">
                                                        <i class="fa fa-money"></i> View Rewards
                                                    </a>
                                                    
                                                    <?php if ($staking['status'] == 'active'): ?>
                                                    <div class="dropdown-divider"></div>
                                                    
                                                    <form method="post" onsubmit="return confirm('Are you sure you want to mark this staking as completed?');">
                                                        <input type="hidden" name="action" value="change_status">
                                                        <input type="hidden" name="staking_id" value="<?php echo $staking['id']; ?>">
                                                        <input type="hidden" name="status" value="completed">
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fa fa-check text-success"></i> Mark as Completed
                                                        </button>
                                                    </form>
                                                    
                                                    <form method="post" onsubmit="return confirm('Are you sure you want to cancel this staking? Funds will be returned to user with any applicable penalties.');">
                                                        <input type="hidden" name="action" value="change_status">
                                                        <input type="hidden" name="staking_id" value="<?php echo $staking['id']; ?>">
                                                        <input type="hidden" name="status" value="cancelled">
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fa fa-times text-danger"></i> Cancel Staking
                                                        </button>
                                                    </form>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center">No staking records found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="box-footer clearfix">
                    <ul class="pagination pagination-sm m-0 float-right">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status='.$status_filter : ''; ?><?php echo $plan_id_filter > 0 ? '&plan_id='.$plan_id_filter : ''; ?><?php echo $user_id_filter > 0 ? '&user_id='.$user_id_filter : ''; ?>">&laquo;</a>
                        </li>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status='.$status_filter : ''; ?><?php echo $plan_id_filter > 0 ? '&plan_id='.$plan_id_filter : ''; ?><?php echo $user_id_filter > 0 ? '&user_id='.$user_id_filter : ''; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status='.$status_filter : ''; ?><?php echo $plan_id_filter > 0 ? '&plan_id='.$plan_id_filter : ''; ?><?php echo $user_id_filter > 0 ? '&user_id='.$user_id_filter : ''; ?>">&raquo;</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->

<?php
// Include footer
require_once __DIR__ . '/layout/footer.php';
?> 