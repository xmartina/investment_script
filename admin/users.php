<?php
// User management page
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
    $current_page = 'users.php';
    
    require_once __DIR__ . '/layout/header.php';
    require_once __DIR__ . '/layout/breadcrumb.php';
} catch (Exception $e) {
    echo "Error loading required files: " . $e->getMessage();
    exit;
}

// Process user status change if requested
if (isset($_POST['action']) && $_POST['action'] == 'change_status' && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    $new_status = $conn_back->real_escape_string($_POST['status']);
    
    if (in_array($new_status, ['active', 'suspended', 'blocked'])) {
        $stmt = $conn_back->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $user_id);
        
        if ($stmt->execute()) {
            // Log the action
            $admin_id = $_SESSION['admin_id'];
            $action = "Changed user ID {$user_id} status to {$new_status}";
            $ip = $_SERVER['REMOTE_ADDR'];
            
            $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
            $log_stmt->bind_param("iss", $admin_id, $action, $ip);
            $log_stmt->execute();
            
            $success_message = "User status updated successfully.";
        } else {
            $error_message = "Failed to update user status: " . $conn_back->error;
        }
    } else {
        $error_message = "Invalid status value.";
    }
}

// Handle search and pagination
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build query with search condition
$where_clause = "";
if (!empty($search)) {
    $search_term = $conn_back->real_escape_string($search);
    $where_clause = "WHERE 
                    first_name LIKE '%{$search_term}%' OR 
                    last_name LIKE '%{$search_term}%' OR 
                    email LIKE '%{$search_term}%' OR 
                    username LIKE '%{$search_term}%'";
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM users {$where_clause}";
$count_result = $conn_back->query($count_query);
$total_rows = 0;
if ($count_result && $row = $count_result->fetch_assoc()) {
    $total_rows = $row['total'];
}
$total_pages = ceil($total_rows / $per_page);

// Get users data with pagination
$query = "SELECT id, username, email, CONCAT(first_name, ' ', last_name) as full_name, 
          phone, status, created_at, (SELECT SUM(amount) FROM transactions 
          WHERE user_id = users.id AND transaction_type = 'deposit' AND status = 'completed') as total_deposit
          FROM users {$where_clause}
          ORDER BY created_at DESC
          LIMIT {$offset}, {$per_page}";

$result = $conn_back->query($query);
$users = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Format data
        $row['total_deposit'] = $row['total_deposit'] ? $row['total_deposit'] : 0;
        $users[] = $row;
    }
}
?>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-12">
            <div class="box">
                <div class="box-header with-border">
                    <h4 class="box-title">User Management</h4>
                    <div class="box-controls pull-right">
                        <form class="form-inline" method="GET">
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
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Total Deposit</th>
                                    <th>Registered On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($users) > 0): ?>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td>
                                            <a href="user_detail.php?id=<?php echo $user['id']; ?>">
                                                <?php echo htmlspecialchars($user['full_name']); ?>
                                            </a>
                                            <small class="d-block text-muted"><?php echo htmlspecialchars($user['username']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge 
                                                <?php 
                                                if ($user['status'] == 'active') echo 'badge-success';
                                                elseif ($user['status'] == 'pending') echo 'badge-warning';
                                                else echo 'badge-danger';
                                                ?>">
                                                <?php echo ucfirst($user['status']); ?>
                                            </span>
                                        </td>
                                        <td>$<?php echo number_format($user['total_deposit'], 2); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Action
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="user_detail.php?id=<?php echo $user['id']; ?>">
                                                        <i class="fa fa-eye"></i> View Details
                                                    </a>
                                                    <?php if ($user['status'] != 'active'): ?>
                                                    <form method="post" style="display:inline;">
                                                        <input type="hidden" name="action" value="change_status">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <input type="hidden" name="status" value="active">
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fa fa-check"></i> Activate
                                                        </button>
                                                    </form>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($user['status'] != 'suspended'): ?>
                                                    <form method="post" style="display:inline;">
                                                        <input type="hidden" name="action" value="change_status">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <input type="hidden" name="status" value="suspended">
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fa fa-pause"></i> Suspend
                                                        </button>
                                                    </form>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($user['status'] != 'blocked'): ?>
                                                    <form method="post" style="display:inline;">
                                                        <input type="hidden" name="action" value="change_status">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <input type="hidden" name="status" value="blocked">
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fa fa-ban"></i> Block
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
                                    <td colspan="8" class="text-center">No users found</td>
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
                            <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>">&laquo;</a>
                        </li>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>">&raquo;</a>
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