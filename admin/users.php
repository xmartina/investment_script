<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/include/config.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Process user actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check for user status change
    if (isset($_POST['change_status']) && isset($_POST['user_id'])) {
        $user_id = (int)$_POST['user_id'];
        $new_status = $_POST['status'] == 'active' ? 'inactive' : 'active';
        
        $stmt = $conn_back->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $user_id);
        
        if ($stmt->execute()) {
            logAdminActivity($_SESSION['admin_id'], 'Update User Status', "Changed user #$user_id status to $new_status");
            showAlert("User status updated successfully", "success");
        } else {
            showAlert("Error updating user status", "danger");
        }
        $stmt->close();
    }
    
    // Check for user deletion
    if (isset($_POST['delete_user']) && isset($_POST['user_id'])) {
        $user_id = (int)$_POST['user_id'];
        
        // Only allow deletion if user has no active investments or pending withdrawals
        $stmt = $conn_back->prepare("SELECT 
            (SELECT COUNT(*) FROM investments WHERE user_id = ? AND status = 'active') as active_investments,
            (SELECT COUNT(*) FROM withdrawals WHERE user_id = ? AND status = 'pending') as pending_withdrawals");
        $stmt->bind_param("ii", $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['active_investments'] > 0 || $row['pending_withdrawals'] > 0) {
            showAlert("Cannot delete user with active investments or pending withdrawals", "danger");
        } else {
            $stmt = $conn_back->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            
            if ($stmt->execute()) {
                logAdminActivity($_SESSION['admin_id'], 'Delete User', "Deleted user #$user_id");
                showAlert("User deleted successfully", "success");
            } else {
                showAlert("Error deleting user", "danger");
            }
        }
        $stmt->close();
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';
$search_params = [];

if (!empty($search)) {
    $search_condition = "WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ?";
    $search_params = ["%$search%", "%$search%", "%$search%"];
}

// Get total users count
$total_users = 0;
if (empty($search_condition)) {
    $result = $conn_back->query("SELECT COUNT(*) as total FROM users");
    if ($result && $row = $result->fetch_assoc()) {
        $total_users = $row['total'];
    }
} else {
    $stmt = $conn_back->prepare("SELECT COUNT(*) as total FROM users $search_condition");
    if (count($search_params) > 0) {
        $types = str_repeat("s", count($search_params));
        $stmt->bind_param($types, ...$search_params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $total_users = $row['total'];
    }
    $stmt->close();
}

$total_pages = ceil($total_users / $limit);

// Get users
$users = [];
$sql = "SELECT u.*, 
        (SELECT SUM(amount) FROM deposit_requests WHERE user_id = u.id) as total_deposits,
        (SELECT SUM(amount) FROM withdrawal WHERE user_id = u.id) as total_withdrawals,
        (SELECT COUNT(*) FROM investments WHERE user_id = u.id) as total_investments
        FROM users u $search_condition ORDER BY u.created_at DESC LIMIT ? OFFSET ?";

$stmt = $conn_back->prepare($sql);

if (count($search_params) > 0) {
    $search_params[] = $limit;
    $search_params[] = $offset;
    $types = str_repeat("s", count($search_params) - 2) . "ii";
    $stmt->bind_param($types, ...$search_params);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
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
                    <h4 class="box-title">Users Management</h4>
                    <div class="box-controls pull-right">
                        <form class="d-flex" action="" method="GET">
                            <input class="form-control me-2" type="search" name="search" placeholder="Search" aria-label="Search" value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-primary" type="submit">Search</button>
                            <?php if (!empty($search)): ?>
                                <a href="users.php" class="btn btn-outline-secondary ms-2">Clear</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Deposits</th>
                                    <th>Withdrawals</th>
                                    <th>Investments</th>
                                    <th>Registered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($users) > 0): ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['username'] ? $user['username'] : $user['first_name'] . ' ' . $user['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars(($user['full_name'] ?? '') ? $user['full_name'] : $user['first_name'] . ' ' . $user['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <span class="badge badge-success">
                                                    Active
                                                </span>
                                            </td>
                                            <td>$<?php echo number_format($user['total_deposits'] ?? 0, 2); ?></td>
                                            <td>$<?php echo number_format($user['total_withdrawals'] ?? 0, 2); ?></td>
                                            <td><?php echo $user['total_investments'] ?? 0; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="user_details.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info" title="View Details">
                                                        <i data-feather="eye"></i>
                                                    </a>
                                                    <form action="" method="POST" class="d-inline">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" name="delete_user" class="btn btn-sm btn-danger" title="Delete User" 
                                                                onclick="return confirm('Are you sure you want to delete this user? This cannot be undone!')">
                                                            <i data-feather="trash-2"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center">No users found</td>
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
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
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
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
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

<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layout/footer.php';
?> 