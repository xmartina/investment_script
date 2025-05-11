<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/include/config.php';

// Check if the admin is logged in and has super_admin role
if (!isset($_SESSION['admin_id']) || !hasPermission('super_admin')) {
    header("Location: login.php");
    exit();
}

// Clear logs if requested
if (isset($_POST['clear_logs']) && hasPermission('super_admin')) {
    $stmt = $conn_back->prepare("DELETE FROM admin_activity_logs");
    
    if ($stmt->execute()) {
        logAdminActivity($_SESSION['admin_id'], 'Clear Logs', "Cleared all admin activity logs");
        showAlert("Activity logs have been cleared", "success");
    } else {
        showAlert("Error clearing activity logs", "danger");
    }
    
    $stmt->close();
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

// Filters
$admin_filter = isset($_GET['admin_id']) ? (int)$_GET['admin_id'] : 0;
$action_filter = isset($_GET['action']) ? $_GET['action'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build where clause
$where_conditions = [];
$where_params = [];
$param_types = '';

if ($admin_filter > 0) {
    $where_conditions[] = "l.admin_id = ?";
    $where_params[] = $admin_filter;
    $param_types .= 'i';
}

if (!empty($action_filter)) {
    $where_conditions[] = "l.action = ?";
    $where_params[] = $action_filter;
    $param_types .= 's';
}

if (!empty($date_from)) {
    $where_conditions[] = "l.created_at >= ?";
    $where_params[] = $date_from . ' 00:00:00';
    $param_types .= 's';
}

if (!empty($date_to)) {
    $where_conditions[] = "l.created_at <= ?";
    $where_params[] = $date_to . ' 23:59:59';
    $param_types .= 's';
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(' AND ', $where_conditions);
}

// Get total logs count
$total_logs = 0;
if (empty($where_clause)) {
    $result = $conn_back->query("SELECT COUNT(*) as total FROM admin_activity_logs");
    if ($result && $row = $result->fetch_assoc()) {
        $total_logs = $row['total'];
    }
} else {
    $sql = "SELECT COUNT(*) as total FROM admin_activity_logs l $where_clause";
    $stmt = $conn_back->prepare($sql);
    
    if (!empty($param_types)) {
        $stmt->bind_param($param_types, ...$where_params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $total_logs = $row['total'];
    }
    $stmt->close();
}

$total_pages = ceil($total_logs / $limit);

// Get logs with admin info
$logs = [];
$sql = "SELECT l.*, a.username, a.full_name 
        FROM admin_activity_logs l 
        LEFT JOIN admins a ON l.admin_id = a.id 
        $where_clause 
        ORDER BY l.created_at DESC 
        LIMIT ? OFFSET ?";

// Add limit and offset to params
$where_params[] = $limit;
$where_params[] = $offset;
$param_types .= 'ii';

$stmt = $conn_back->prepare($sql);

if (!empty($param_types)) {
    $stmt->bind_param($param_types, ...$where_params);
}

$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $logs[] = $row;
}
$stmt->close();

// Get all admins for filter dropdown
$admins = [];
$stmt = $conn_back->prepare("SELECT id, username, full_name FROM admins ORDER BY username");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $admins[] = $row;
}
$stmt->close();

// Get all unique actions for filter dropdown
$actions = [];
$stmt = $conn_back->prepare("SELECT DISTINCT action FROM admin_activity_logs ORDER BY action");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $actions[] = $row['action'];
}
$stmt->close();

include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layout/header.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layout/breadcrumb.php';
?>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-12">
            <div class="box">
                <div class="box-header with-border">
                    <h4 class="box-title">Admin Activity Logs</h4>
                    <div class="box-controls pull-right">
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                            <i data-feather="trash-2"></i> Clear All Logs
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <!-- Filters -->
                    <div class="mb-4">
                        <form action="" method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="admin_id" class="form-label">Admin User</label>
                                <select name="admin_id" id="admin_id" class="form-select">
                                    <option value="">All Admins</option>
                                    <?php foreach ($admins as $admin): ?>
                                        <option value="<?php echo $admin['id']; ?>" <?php echo $admin_filter == $admin['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($admin['username']); ?> (<?php echo htmlspecialchars($admin['full_name']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="action" class="form-label">Action</label>
                                <select name="action" id="action" class="form-select">
                                    <option value="">All Actions</option>
                                    <?php foreach ($actions as $action): ?>
                                        <option value="<?php echo $action; ?>" <?php echo $action_filter == $action ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($action); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="date_from" class="form-label">Date From</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="date_to" class="form-label">Date To</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div>
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <?php if (!empty($admin_filter) || !empty($action_filter) || !empty($date_from) || !empty($date_to)): ?>
                                        <a href="logs.php" class="btn btn-outline-secondary">Clear</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Admin</th>
                                    <th>Action</th>
                                    <th>Details</th>
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
                                                <?php if ($log['username']): ?>
                                                    <?php echo htmlspecialchars($log['username']); ?><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($log['full_name']); ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">Admin ID: <?php echo $log['admin_id']; ?> (deleted)</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                                            <td><?php echo htmlspecialchars($log['details']); ?></td>
                                            <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                            <td><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No logs found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($total_pages > 1): ?>
                    <div class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($admin_filter) ? '&admin_id=' . $admin_filter : ''; ?><?php echo !empty($action_filter) ? '&action=' . urlencode($action_filter) : ''; ?><?php echo !empty($date_from) ? '&date_from=' . urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to=' . urlencode($date_to) : ''; ?>">
                                        Previous
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php 
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            for ($i = $start_page; $i <= $end_page; $i++): 
                            ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($admin_filter) ? '&admin_id=' . $admin_filter : ''; ?><?php echo !empty($action_filter) ? '&action=' . urlencode($action_filter) : ''; ?><?php echo !empty($date_from) ? '&date_from=' . urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to=' . urlencode($date_to) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($admin_filter) ? '&admin_id=' . $admin_filter : ''; ?><?php echo !empty($action_filter) ? '&action=' . urlencode($action_filter) : ''; ?><?php echo !empty($date_from) ? '&date_from=' . urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to=' . urlencode($date_to) : ''; ?>">
                                        Next
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->

<!-- Clear Logs Modal -->
<div class="modal fade" id="clearLogsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Clear All Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to clear all activity logs?</p>
                <p class="text-danger">This action cannot be undone. All admin activity history will be permanently deleted.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="" method="POST">
                    <button type="submit" name="clear_logs" class="btn btn-danger">Clear All Logs</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layout/footer.php';
?> 