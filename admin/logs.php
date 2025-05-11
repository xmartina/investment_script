<?php
// Admin logs page
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Check if the admin is logged in and is a super admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || $_SESSION['admin_role'] != 'super_admin') {
    header("Location: login.php");
    exit();
}

// Include necessary files
try {
    require_once __DIR__ . '/include/config.php';
    
    // Set current page for menu highlighting
    $current_page = 'logs.php';
    
    require_once __DIR__ . '/layout/header.php';
    require_once __DIR__ . '/layout/breadcrumb.php';
} catch (Exception $e) {
    echo "Error loading required files: " . $e->getMessage();
    exit;
}

// Handle search and pagination
$search = isset($_GET['search']) ? $_GET['search'] : '';
$admin_filter = isset($_GET['admin_id']) ? (int)$_GET['admin_id'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build query conditions
$where_conditions = [];
$where_clause = "";

if (!empty($search)) {
    $search_term = $conn_back->real_escape_string($search);
    $where_conditions[] = "l.action LIKE '%{$search_term}%' OR l.details LIKE '%{$search_term}%'";
}

if ($admin_filter > 0) {
    $where_conditions[] = "l.admin_id = {$admin_filter}";
}

if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

// Get total logs count for pagination
$count_query = "SELECT COUNT(*) as total FROM admin_logs l {$where_clause}";
$count_result = $conn_back->query($count_query);
$total_rows = 0;
if ($count_result && $row = $count_result->fetch_assoc()) {
    $total_rows = $row['total'];
}
$total_pages = ceil($total_rows / $per_page);

// Get logs data
$query = "SELECT l.*, a.username 
          FROM admin_logs l 
          LEFT JOIN admins a ON l.admin_id = a.id 
          {$where_clause}
          ORDER BY l.created_at DESC
          LIMIT {$offset}, {$per_page}";

$result = $conn_back->query($query);
$logs = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
}

// Get admins for filter dropdown
$admins_query = "SELECT id, username, full_name FROM admins ORDER BY username";
$admins_result = $conn_back->query($admins_query);
$admins = [];
if ($admins_result) {
    while ($row = $admins_result->fetch_assoc()) {
        $admins[] = $row;
    }
}
?>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-12">
            <div class="box">
                <div class="box-header with-border">
                    <h4 class="box-title">Admin Activity Logs</h4>
                    <div class="box-controls pull-right d-flex">
                        <form class="me-2" method="GET">
                            <?php if (!empty($search)): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <?php endif; ?>
                            
                            <div class="input-group">
                                <select name="admin_id" class="form-select">
                                    <option value="0">All Admins</option>
                                    <?php foreach ($admins as $admin): ?>
                                    <option value="<?php echo $admin['id']; ?>" <?php echo $admin_filter == $admin['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($admin['username'] . ' (' . $admin['full_name'] . ')'); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-filter"></i>
                                </button>
                            </div>
                        </form>
                        
                        <form method="GET">
                            <?php if ($admin_filter > 0): ?>
                            <input type="hidden" name="admin_id" value="<?php echo $admin_filter; ?>">
                            <?php endif; ?>
                            
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
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Admin</th>
                                    <th>Action</th>
                                    <th>IP Address</th>
                                    <th>Date & Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($logs) > 0): ?>
                                    <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?php echo $log['id']; ?></td>
                                        <td>
                                            <?php if ($log['admin_id']): ?>
                                                <?php echo htmlspecialchars($log['username'] ?? 'Unknown'); ?>
                                                <small class="d-block text-muted">ID: <?php echo $log['admin_id']; ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">System</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($log['action']); ?></td>
                                        <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                        <td><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No logs found</td>
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
                            <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo $admin_filter > 0 ? '&admin_id='.$admin_filter : ''; ?>">&laquo;</a>
                        </li>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo $admin_filter > 0 ? '&admin_id='.$admin_filter : ''; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo $admin_filter > 0 ? '&admin_id='.$admin_filter : ''; ?>">&raquo;</a>
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