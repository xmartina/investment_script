<?php
// Staking rewards management page for admin panel
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
    $current_page = 'staking_rewards.php';
    
    require_once __DIR__ . '/layout/header.php';
    require_once __DIR__ . '/layout/breadcrumb.php';
} catch (Exception $e) {
    echo "Error loading required files: " . $e->getMessage();
    exit;
}

$staking_id = isset($_GET['staking_id']) ? (int)$_GET['staking_id'] : 0;
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// Process actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process reward
    if (isset($_POST['action']) && $_POST['action'] == 'process_reward' && isset($_POST['reward_id'])) {
        $reward_id = (int)$_POST['reward_id'];
        $status = $conn_back->real_escape_string($_POST['status']);
        
        if (in_array($status, ['claimed', 'reinvested', 'expired'])) {
            $conn_back->begin_transaction();
            
            try {
                // Get reward details
                $reward_query = $conn_back->prepare("
                    SELECT sr.*, s.plan_id 
                    FROM staking_rewards sr 
                    JOIN staking s ON sr.staking_id = s.id 
                    WHERE sr.id = ?
                ");
                $reward_query->bind_param("i", $reward_id);
                $reward_query->execute();
                $reward_result = $reward_query->get_result();
                
                if ($reward_row = $reward_result->fetch_assoc()) {
                    $user_id = $reward_row['user_id'];
                    $reward_amount = $reward_row['reward_amount'];
                    $staking_id = $reward_row['staking_id'];
                    $plan_id = $reward_row['plan_id'];
                    
                    // Update reward status
                    $status_update = $conn_back->prepare("
                        UPDATE staking_rewards SET status = ?, claimed_at = NOW() WHERE id = ?
                    ");
                    $status_update->bind_param("si", $status, $reward_id);
                    $status_update->execute();
                    
                    if ($status == 'claimed') {
                        // Add transaction record
                        $txn_insert = $conn_back->prepare("
                            INSERT INTO transactions (user_id, transaction_type, amount, status, description, date_time) 
                            VALUES (?, 'staking_reward', ?, 'completed', ?, NOW())
                        ");
                        $desc = "Staking reward from staking ID {$staking_id}";
                        $txn_insert->bind_param("ids", $user_id, $reward_amount, $desc);
                        $txn_insert->execute();
                        $txn_id = $conn_back->insert_id;
                        
                        // Update transaction ID in reward record
                        $txn_update = $conn_back->prepare("
                            UPDATE staking_rewards SET transaction_id = ? WHERE id = ?
                        ");
                        $txn_update->bind_param("ii", $txn_id, $reward_id);
                        $txn_update->execute();
                        
                        // Update user balance
                        $balance_update = $conn_back->prepare("
                            UPDATE users SET main_balance = main_balance + ? WHERE id = ?
                        ");
                        $balance_update->bind_param("di", $reward_amount, $user_id);
                        $balance_update->execute();
                    } elseif ($status == 'reinvested') {
                        // Update staking record with increased amount
                        $staking_update = $conn_back->prepare("
                            UPDATE staking SET amount = amount + ?, earned_reward = earned_reward + ? WHERE id = ?
                        ");
                        $staking_update->bind_param("ddi", $reward_amount, $reward_amount, $staking_id);
                        $staking_update->execute();
                        
                        // Add transaction record
                        $txn_insert = $conn_back->prepare("
                            INSERT INTO transactions (user_id, transaction_type, amount, status, description, date_time) 
                            VALUES (?, 'staking_reinvestment', ?, 'completed', ?, NOW())
                        ");
                        $desc = "Staking reward reinvested into staking ID {$staking_id}";
                        $txn_insert->bind_param("ids", $user_id, $reward_amount, $desc);
                        $txn_insert->execute();
                        $txn_id = $conn_back->insert_id;
                        
                        // Update transaction ID in reward record
                        $txn_update = $conn_back->prepare("
                            UPDATE staking_rewards SET transaction_id = ? WHERE id = ?
                        ");
                        $txn_update->bind_param("ii", $txn_id, $reward_id);
                        $txn_update->execute();
                    }
                    
                    // Log admin action
                    $admin_id = $_SESSION['admin_id'];
                    $action = "Processed staking reward ID {$reward_id} - set status to {$status}";
                    $ip = $_SERVER['REMOTE_ADDR'];
                    
                    $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                    $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                    $log_stmt->execute();
                    
                    // Commit transaction
                    $conn_back->commit();
                    $success_message = "Reward successfully processed as {$status}.";
                } else {
                    throw new Exception("Reward not found.");
                }
            } catch (Exception $e) {
                $conn_back->rollback();
                $error_message = "Error processing reward: " . $e->getMessage();
            }
        } else {
            $error_message = "Invalid status value.";
        }
    }
    
    // Add manual reward
    if (isset($_POST['action']) && $_POST['action'] == 'add_reward') {
        $staking_id = (int)$_POST['staking_id'];
        $reward_amount = (float)$_POST['reward_amount'];
        $expected_date = $_POST['expected_date'];
        
        if ($reward_amount <= 0) {
            $error_message = "Reward amount must be greater than zero.";
        } else {
            // Get staking details
            $staking_query = $conn_back->prepare("SELECT user_id FROM staking WHERE id = ?");
            $staking_query->bind_param("i", $staking_id);
            $staking_query->execute();
            $staking_result = $staking_query->get_result();
            
            if ($staking_row = $staking_result->fetch_assoc()) {
                $user_id = $staking_row['user_id'];
                
                // Insert reward record
                $reward_insert = $conn_back->prepare("
                    INSERT INTO staking_rewards (staking_id, user_id, reward_amount, expected_date, status)
                    VALUES (?, ?, ?, ?, 'pending')
                ");
                $reward_insert->bind_param("iidd", $staking_id, $user_id, $reward_amount, $expected_date);
                
                if ($reward_insert->execute()) {
                    // Log admin action
                    $admin_id = $_SESSION['admin_id'];
                    $action = "Added manual staking reward of {$reward_amount} for staking ID {$staking_id}";
                    $ip = $_SERVER['REMOTE_ADDR'];
                    
                    $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                    $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                    $log_stmt->execute();
                    
                    $success_message = "Manual reward added successfully.";
                } else {
                    $error_message = "Error adding reward: " . $conn_back->error;
                }
            } else {
                $error_message = "Staking record not found.";
            }
        }
    }
}

// Build query conditions based on filters
$where_conditions = [];

if ($staking_id > 0) {
    $where_conditions[] = "sr.staking_id = {$staking_id}";
}

if ($user_id > 0) {
    $where_conditions[] = "sr.user_id = {$user_id}";
}

// Add status filter if provided
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status_filter = $conn_back->real_escape_string($_GET['status']);
    $where_conditions[] = "sr.status = '{$status_filter}'";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get staking details if staking_id is provided
$staking_details = null;
if ($staking_id > 0) {
    $staking_query = $conn_back->prepare("
        SELECT s.*, 
        sp.name as plan_name, 
        CONCAT(u.first_name, ' ', u.last_name) as full_name,
        u.username
        FROM staking s
        JOIN staking_plans sp ON s.plan_id = sp.id
        JOIN users u ON s.user_id = u.id
        WHERE s.id = ?
    ");
    $staking_query->bind_param("i", $staking_id);
    $staking_query->execute();
    $staking_result = $staking_query->get_result();
    if ($staking_result->num_rows > 0) {
        $staking_details = $staking_result->fetch_assoc();
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM staking_rewards sr {$where_clause}";
$count_result = $conn_back->query($count_query);
$total_rows = 0;
if ($count_result && $row = $count_result->fetch_assoc()) {
    $total_rows = $row['total'];
}
$total_pages = ceil($total_rows / $per_page);

// Get rewards data
$query = "SELECT sr.*, 
          s.plan_id, s.amount as staking_amount, s.status as staking_status,
          CONCAT(u.first_name, ' ', u.last_name) as full_name, u.username,
          sp.name as plan_name
          FROM staking_rewards sr 
          LEFT JOIN staking s ON sr.staking_id = s.id
          LEFT JOIN users u ON sr.user_id = u.id
          LEFT JOIN staking_plans sp ON s.plan_id = sp.id
          {$where_clause}
          ORDER BY sr.expected_date DESC
          LIMIT {$offset}, {$per_page}";

$result = $conn_back->query($query);
$rewards = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $rewards[] = $row;
    }
}

// Get staking plans if we need to add a manual reward
$staking_plans_query = "SELECT id, name FROM staking_plans WHERE is_active = 1 ORDER BY name";
$staking_plans_result = $conn_back->query($staking_plans_query);
$staking_plans = [];
if ($staking_plans_result) {
    while ($row = $staking_plans_result->fetch_assoc()) {
        $staking_plans[] = $row;
    }
}
?>

<!-- Main content -->
<section class="content">
    <?php if ($staking_details): ?>
    <div class="row">
        <div class="col-12">
            <div class="box">
                <div class="box-header with-border">
                    <h4 class="box-title">Staking Details</h4>
                    <div class="box-controls pull-right">
                        <a href="staking.php" class="btn btn-info btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to Staking
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>User:</strong> <a href="user_detail.php?id=<?php echo $staking_details['user_id']; ?>"><?php echo htmlspecialchars($staking_details['full_name']); ?></a></p>
                            <p><strong>Username:</strong> <?php echo htmlspecialchars($staking_details['username']); ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Plan:</strong> <?php echo htmlspecialchars($staking_details['plan_name']); ?></p>
                            <p><strong>Amount:</strong> $<?php echo number_format($staking_details['amount'], 2); ?></p>
                            <p><strong>APY:</strong> <?php echo $staking_details['apy']; ?>%</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Status:</strong> <span class="badge <?php echo $staking_details['status'] == 'active' ? 'badge-success' : ($staking_details['status'] == 'completed' ? 'badge-info' : 'badge-danger'); ?>"><?php echo ucfirst($staking_details['status']); ?></span></p>
                            <p><strong>Started:</strong> <?php echo date('M d, Y', strtotime($staking_details['started_at'])); ?></p>
                            <p><strong>Ends:</strong> <?php echo date('M d, Y', strtotime($staking_details['ends_at'])); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRewardModal">
                                <i class="fa fa-plus"></i> Add Manual Reward
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-12">
            <div class="box">
                <div class="box-header with-border">
                    <h4 class="box-title">Staking Rewards</h4>
                    <div class="box-controls pull-right d-flex">
                        <form class="me-2" method="GET">
                            <?php if ($staking_id > 0): ?>
                            <input type="hidden" name="staking_id" value="<?php echo $staking_id; ?>">
                            <?php endif; ?>
                            <?php if ($user_id > 0): ?>
                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                            <?php endif; ?>
                            
                            <div class="input-group">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo isset($_GET['status']) && $_GET['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="claimed" <?php echo isset($_GET['status']) && $_GET['status'] == 'claimed' ? 'selected' : ''; ?>>Claimed</option>
                                    <option value="reinvested" <?php echo isset($_GET['status']) && $_GET['status'] == 'reinvested' ? 'selected' : ''; ?>>Reinvested</option>
                                    <option value="expired" <?php echo isset($_GET['status']) && $_GET['status'] == 'expired' ? 'selected' : ''; ?>>Expired</option>
                                </select>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-filter"></i>
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
                                    <?php if (!$staking_id): ?>
                                    <th>Staking ID</th>
                                    <?php endif; ?>
                                    <?php if (!$user_id && !$staking_id): ?>
                                    <th>User</th>
                                    <?php endif; ?>
                                    <th>Plan</th>
                                    <th>Amount</th>
                                    <th>Expected Date</th>
                                    <th>Status</th>
                                    <th>Claimed Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($rewards) > 0): ?>
                                    <?php foreach ($rewards as $reward): ?>
                                    <tr>
                                        <td><?php echo $reward['id']; ?></td>
                                        <?php if (!$staking_id): ?>
                                        <td>
                                            <a href="staking_rewards.php?staking_id=<?php echo $reward['staking_id']; ?>">
                                                <?php echo $reward['staking_id']; ?>
                                            </a>
                                        </td>
                                        <?php endif; ?>
                                        <?php if (!$user_id && !$staking_id): ?>
                                        <td>
                                            <a href="user_detail.php?id=<?php echo $reward['user_id']; ?>">
                                                <?php echo htmlspecialchars($reward['full_name']); ?>
                                            </a>
                                            <small class="d-block text-muted"><?php echo htmlspecialchars($reward['username']); ?></small>
                                        </td>
                                        <?php endif; ?>
                                        <td><?php echo htmlspecialchars($reward['plan_name']); ?></td>
                                        <td>$<?php echo number_format($reward['reward_amount'], 2); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($reward['expected_date'])); ?></td>
                                        <td>
                                            <span class="badge <?php 
                                                if ($reward['status'] == 'pending') echo 'badge-warning';
                                                elseif ($reward['status'] == 'claimed') echo 'badge-success';
                                                elseif ($reward['status'] == 'reinvested') echo 'badge-info';
                                                else echo 'badge-danger';
                                            ?>">
                                                <?php echo ucfirst($reward['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $reward['claimed_at'] ? date('M d, Y', strtotime($reward['claimed_at'])) : '-'; ?></td>
                                        <td>
                                            <?php if ($reward['status'] == 'pending'): ?>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Action
                                                </button>
                                                <div class="dropdown-menu">
                                                    <form method="post">
                                                        <input type="hidden" name="action" value="process_reward">
                                                        <input type="hidden" name="reward_id" value="<?php echo $reward['id']; ?>">
                                                        <input type="hidden" name="status" value="claimed">
                                                        <button type="submit" class="dropdown-item" onclick="return confirm('Are you sure you want to process this reward as claimed?');">
                                                            <i class="fa fa-check text-success"></i> Process as Claimed
                                                        </button>
                                                    </form>
                                                    
                                                    <form method="post">
                                                        <input type="hidden" name="action" value="process_reward">
                                                        <input type="hidden" name="reward_id" value="<?php echo $reward['id']; ?>">
                                                        <input type="hidden" name="status" value="reinvested">
                                                        <button type="submit" class="dropdown-item" onclick="return confirm('Are you sure you want to process this reward as reinvested?');">
                                                            <i class="fa fa-refresh text-info"></i> Process as Reinvested
                                                        </button>
                                                    </form>
                                                    
                                                    <form method="post">
                                                        <input type="hidden" name="action" value="process_reward">
                                                        <input type="hidden" name="reward_id" value="<?php echo $reward['id']; ?>">
                                                        <input type="hidden" name="status" value="expired">
                                                        <button type="submit" class="dropdown-item" onclick="return confirm('Are you sure you want to mark this reward as expired?');">
                                                            <i class="fa fa-times text-danger"></i> Mark as Expired
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                            <?php else: ?>
                                            <span class="text-muted">No actions available</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="<?php echo (!$staking_id ? (!$user_id ? 9 : 8) : 7); ?>" class="text-center">No rewards found</td>
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
                            <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo $staking_id > 0 ? '&staking_id='.$staking_id : ''; ?><?php echo $user_id > 0 ? '&user_id='.$user_id : ''; ?><?php echo isset($_GET['status']) ? '&status='.$_GET['status'] : ''; ?>">&laquo;</a>
                        </li>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $staking_id > 0 ? '&staking_id='.$staking_id : ''; ?><?php echo $user_id > 0 ? '&user_id='.$user_id : ''; ?><?php echo isset($_GET['status']) ? '&status='.$_GET['status'] : ''; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo $staking_id > 0 ? '&staking_id='.$staking_id : ''; ?><?php echo $user_id > 0 ? '&user_id='.$user_id : ''; ?><?php echo isset($_GET['status']) ? '&status='.$_GET['status'] : ''; ?>">&raquo;</a>
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

<!-- Add Reward Modal -->
<div class="modal fade" id="addRewardModal" tabindex="-1" aria-labelledby="addRewardModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRewardModalLabel">Add Manual Reward</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_reward">
                    
                    <?php if ($staking_id): ?>
                    <input type="hidden" name="staking_id" value="<?php echo $staking_id; ?>">
                    <p>Adding reward for Staking ID: <strong><?php echo $staking_id; ?></strong></p>
                    <?php else: ?>
                    <div class="mb-3">
                        <label for="staking_id" class="form-label">Staking ID</label>
                        <input type="number" class="form-control" id="staking_id" name="staking_id" required>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="reward_amount" class="form-label">Reward Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="reward_amount" name="reward_amount" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="expected_date" class="form-label">Expected Date</label>
                        <input type="date" class="form-control" id="expected_date" name="expected_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Reward</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Include footer
require_once __DIR__ . '/layout/footer.php';
?> 