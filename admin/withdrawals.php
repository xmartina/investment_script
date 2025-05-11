<?php
// Withdrawals management page
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
    $current_page = 'withdrawals.php';
    
    require_once __DIR__ . '/layout/header.php';
    require_once __DIR__ . '/layout/breadcrumb.php';
} catch (Exception $e) {
    echo "Error loading required files: " . $e->getMessage();
    exit;
}

// Process withdrawal approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && isset($_POST['withdrawal_id'])) {
        $withdrawal_id = (int)$_POST['withdrawal_id'];
        $action = $_POST['action'];
        $status = '';
        $msg = '';
        
        if ($action == 'approve') {
            $status = 'completed';
            $msg = 'Withdrawal approved successfully';
        } elseif ($action == 'reject') {
            $status = 'rejected';
            $msg = 'Withdrawal rejected successfully';
            
            // Get the withdrawal amount and user to refund if rejected
            $refund_query = $conn_back->prepare("
                SELECT w.amount, w.user_id, u.username 
                FROM withdrawal w 
                JOIN users u ON w.user_id = u.id 
                WHERE w.id = ?
            ");
            $refund_query->bind_param("i", $withdrawal_id);
            $refund_query->execute();
            $refund_result = $refund_query->get_result();
            
            if ($refund_row = $refund_result->fetch_assoc()) {
                // Refund the amount to user's balance
                $refund_amount = $refund_row['amount'];
                $user_id = $refund_row['user_id'];
                $username = $refund_row['username'];
                
                // Add transaction record for the refund
                $refund_txn = $conn_back->prepare("
                    INSERT INTO transactions (user_id, transaction_type, amount, status, description, date_time) 
                    VALUES (?, 'refund', ?, 'completed', ?, NOW())
                ");
                $refund_desc = "Refund for rejected withdrawal #{$withdrawal_id}";
                $refund_txn->bind_param("ids", $user_id, $refund_amount, $refund_desc);
                $refund_txn->execute();
                
                // Update user's balance
                $update_balance = $conn_back->prepare("
                    UPDATE users SET balance = balance + ? WHERE id = ?
                ");
                $update_balance->bind_param("di", $refund_amount, $user_id);
                $update_balance->execute();
                
                $msg .= " and {$refund_amount} has been refunded to {$username}'s account";
            }
        }
        
        if (!empty($status)) {
            // Update withdrawal status
            $stmt = $conn_back->prepare("UPDATE withdrawal SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("si", $status, $withdrawal_id);
            
            if ($stmt->execute()) {
                // Log admin action
                $admin_id = $_SESSION['admin_id'];
                $log_action = "Updated withdrawal #{$withdrawal_id} status to {$status}";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $log_action, $ip);
                $log_stmt->execute();
                
                $success_message = $msg;
            } else {
                $error_message = "Error updating withdrawal: " . $conn_back->error;
            }
        }
    }
}

// Handle search and filtering
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build query conditions
$where_conditions = [];
$where_clause = "";

if (!empty($search)) {
    $search_term = $conn_back->real_escape_string($search);
    $where_conditions[] = "(w.id LIKE '%{$search_term}%' OR 
                          u.username LIKE '%{$search_term}%' OR 
                          u.email LIKE '%{$search_term}%' OR 
                          CONCAT(u.first_name, ' ', u.last_name) LIKE '%{$search_term}%')";
}

if (!empty($status_filter)) {
    $status = $conn_back->real_escape_string($status_filter);
    $where_conditions[] = "w.status = '{$status}'";
}

if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM withdrawal w 
                LEFT JOIN users u ON w.user_id = u.id 
                {$where_clause}";
$count_result = $conn_back->query($count_query);
$total_rows = 0;
if ($count_result && $row = $count_result->fetch_assoc()) {
    $total_rows = $row['total'];
}
$total_pages = ceil($total_rows / $per_page);

// Get withdrawals data
$query = "SELECT w.*, 
          CONCAT(u.first_name, ' ', u.last_name) as full_name,
          u.username, u.email
    FROM withdrawal w
          LEFT JOIN users u ON w.user_id = u.id 
          {$where_clause}
    ORDER BY w.created_at DESC
          LIMIT {$offset}, {$per_page}";

$result = $conn_back->query($query);
$withdrawals = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $withdrawals[] = $row;
    }
}

// Get summary statistics
$stats_query = "SELECT 
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as completed_amount,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
                SUM(CASE WHEN status = 'rejected' THEN amount ELSE 0 END) as rejected_amount,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_count
                FROM withdrawal";
$stats_result = $conn_back->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<!-- Main content -->
<section class="content">
    <!-- Stats boxes -->
    <div class="row">
        <div class="col-xl-4 col-md-6 col-12">
            <div class="box bg-warning bg-hover-info">
                <div class="box-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-white"><?php echo $stats['pending_count'] ?? 0; ?></h4>
                            <p class="text-white mb-0">Pending Withdrawals</p>
                        </div>
                        <div>
                            <h4 class="text-white">$<?php echo number_format($stats['pending_amount'] ?? 0, 2); ?></h4>
                        </div>
                    </div>
                </div>
    </div>
        </div>
        <div class="col-xl-4 col-md-6 col-12">
            <div class="box bg-success bg-hover-success">
                <div class="box-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-white"><?php echo $stats['completed_count'] ?? 0; ?></h4>
                            <p class="text-white mb-0">Completed Withdrawals</p>
                        </div>
                        <div>
                            <h4 class="text-white">$<?php echo number_format($stats['completed_amount'] ?? 0, 2); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 col-12">
            <div class="box bg-danger bg-hover-danger">
                <div class="box-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-white"><?php echo $stats['rejected_count'] ?? 0; ?></h4>
                            <p class="text-white mb-0">Rejected Withdrawals</p>
                        </div>
                        <div>
                            <h4 class="text-white">$<?php echo number_format($stats['rejected_amount'] ?? 0, 2); ?></h4>
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
                    <h4 class="box-title">Withdrawal Requests</h4>
                    <div class="box-controls pull-right d-flex">
                        <form class="me-2" method="GET">
                            <div class="input-group">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-filter"></i>
                                </button>
                            </div>
                        </form>
                        
                        <form method="GET">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
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
                            <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Wallet/Account</th>
                                    <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                                <?php if (count($withdrawals) > 0): ?>
                                    <?php foreach ($withdrawals as $withdrawal): ?>
                                    <tr>
                                        <td><?php echo $withdrawal['id']; ?></td>
                                        <td>
                                            <a href="user_detail.php?id=<?php echo $withdrawal['user_id']; ?>">
                                                <?php echo htmlspecialchars($withdrawal['full_name']); ?>
                                            </a>
                                            <small class="d-block text-muted"><?php echo htmlspecialchars($withdrawal['username']); ?></small>
                                        </td>
                                        <td>$<?php echo number_format($withdrawal['amount'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($withdrawal['method'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($withdrawal['wallet_address'] ?: 'N/A'); ?></td>
                                        <td>
                                            <span class="badge 
                                                <?php 
                                                if ($withdrawal['status'] == 'completed') echo 'badge-success';
                                                elseif ($withdrawal['status'] == 'pending') echo 'badge-warning';
                                                else echo 'badge-danger';
                                                ?>">
                                                <?php echo ucfirst($withdrawal['status']); ?>
                                        </span>
                                    </td>
                                        <td><?php echo date('M d, Y H:i', strtotime($withdrawal['created_at'])); ?></td>
                                        <td>
                                            <?php if ($withdrawal['status'] == 'pending'): ?>
                                            <div class="btn-group">
                                                <form method="post" class="me-1">
                                                    <input type="hidden" name="withdrawal_id" value="<?php echo $withdrawal['id']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to approve this withdrawal?')">
                                                        <i class="fa fa-check"></i> Approve
                                        </button>
                                                </form>
                                                <form method="post">
                                                    <input type="hidden" name="withdrawal_id" value="<?php echo $withdrawal['id']; ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to reject this withdrawal? The amount will be refunded to the user\'s account.')">
                                                        <i class="fa fa-times"></i> Reject
                                            </button>
                                                </form>
                                            </div>
                                            <?php else: ?>
                                            <span class="text-muted">No actions available</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                    <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                    <td colspan="8" class="text-center">No withdrawals found</td>
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
                            <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status='.$status_filter : ''; ?>">&laquo;</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status='.$status_filter : ''; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status='.$status_filter : ''; ?>">&raquo;</a>
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