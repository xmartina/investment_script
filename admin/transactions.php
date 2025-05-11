<?php
// Admin Transactions Management Page
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
    $current_page = 'transactions.php';
    
    require_once __DIR__ . '/layout/header.php';
    require_once __DIR__ . '/layout/breadcrumb.php';
} catch (Exception $e) {
    echo "Error loading required files: " . $e->getMessage();
    exit;
}

// Handle actions
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
        $transaction_id = (int)$_POST['transaction_id'];
        $new_status = $conn_back->real_escape_string($_POST['status']);
        
        if (in_array($new_status, ['pending', 'completed', 'cancelled', 'rejected'])) {
            $stmt = $conn_back->prepare("UPDATE transactions SET status = ? WHERE transaction_id = ?");
            $stmt->bind_param("si", $new_status, $transaction_id);
            
            if ($stmt->execute()) {
                // Log admin activity
                $admin_id = $_SESSION['admin_id'];
                $action = "Updated transaction #$transaction_id status to $new_status";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                $log_stmt->execute();
                
                $success_message = "Transaction status updated successfully";
            } else {
                $error_message = "Failed to update transaction status: " . $conn_back->error;
            }
        } else {
            $error_message = "Invalid status value";
        }
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 20;
$offset = ($page - 1) * $records_per_page;

// Filtering
$transaction_type = isset($_GET['type']) ? $_GET['type'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build the query
$where = [];
$params = [];
$types = "";

if (!empty($transaction_type)) {
    $where[] = "t.transaction_type = ?";
    $params[] = $transaction_type;
    $types .= "s";
}

if (!empty($status)) {
    $where[] = "t.status = ?";
    $params[] = $status;
    $types .= "s";
}

if ($user_id > 0) {
    $where[] = "t.user_id = ?";
    $params[] = $user_id;
    $types .= "i";
}

if (!empty($search)) {
    $search_term = "%$search%";
    $where[] = "(t.reference_id LIKE ? OR u.username LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Count total records
$count_sql = "SELECT COUNT(*) as total 
              FROM transactions t 
              LEFT JOIN users u ON t.user_id = u.id 
              $where_clause";

if (!empty($params)) {
    $stmt = $conn_back->prepare($count_sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $count_result = $stmt->get_result();
} else {
    $count_result = $conn_back->query($count_sql);
}

$row = $count_result->fetch_assoc();
$total_records = $row['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get transactions
$sql = "SELECT t.*, 
        CONCAT(u.first_name, ' ', u.last_name) as user_name,
        u.username,
        u.email
        FROM transactions t
        LEFT JOIN users u ON t.user_id = u.id
        $where_clause
        ORDER BY t.date_time DESC
        LIMIT ?, ?";

$params[] = $offset;
$params[] = $records_per_page;
$types .= "ii";

$stmt = $conn_back->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$transactions = [];

while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}

// Get transaction types for filtering
$types_sql = "SELECT DISTINCT transaction_type FROM transactions ORDER BY transaction_type";
$types_result = $conn_back->query($types_sql);
$transaction_types = [];

if ($types_result) {
    while ($row = $types_result->fetch_assoc()) {
        $transaction_types[] = $row['transaction_type'];
    }
}

// Get statuses for filtering
$statuses_sql = "SELECT DISTINCT status FROM transactions ORDER BY status";
$statuses_result = $conn_back->query($statuses_sql);
$transaction_statuses = [];

if ($statuses_result) {
    while ($row = $statuses_result->fetch_assoc()) {
        $transaction_statuses[] = $row['status'];
    }
}
?>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-12">
            <div class="box">
                <div class="box-header with-border">
                    <h4 class="box-title">Transactions Management</h4>
                    <div class="box-controls pull-right d-flex">
                        <form class="me-2" method="GET" action="">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search..." name="search" value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </form>
                        
                        <div class="dropdown me-2">
                            <button class="btn btn-secondary dropdown-toggle" type="button" id="typeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo !empty($transaction_type) ? 'Type: ' . $transaction_type : 'All Types'; ?>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="typeDropdown">
                                <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($_GET, ['type' => '', 'page' => 1])); ?>">All Types</a></li>
                                <?php foreach ($transaction_types as $type): ?>
                                <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($_GET, ['type' => $type, 'page' => 1])); ?>"><?php echo $type; ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" id="statusDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo !empty($status) ? 'Status: ' . $status : 'All Statuses'; ?>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="statusDropdown">
                                <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($_GET, ['status' => '', 'page' => 1])); ?>">All Statuses</a></li>
                                <?php foreach ($transaction_statuses as $stat): ?>
                                <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($_GET, ['status' => $stat, 'page' => 1])); ?>"><?php echo $stat; ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="box-body">
                    <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?php echo $success_message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?php echo $error_message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date/Time</th>
                                    <th>Reference</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($transactions) > 0): ?>
                                    <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><?php echo $transaction['transaction_id']; ?></td>
                                        <td>
                                            <a href="user_detail.php?id=<?php echo $transaction['user_id']; ?>">
                                                <?php echo htmlspecialchars($transaction['user_name']); ?>
                                            </a>
                                            <small class="d-block text-muted"><?php echo htmlspecialchars($transaction['username']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge <?php 
                                                switch ($transaction['transaction_type']) {
                                                    case 'deposit':
                                                    case 'investment_return':
                                                    case 'staking_reward':
                                                    case 'referral_bonus':
                                                        echo 'badge-success';
                                                        break;
                                                    case 'withdrawal':
                                                        echo 'badge-warning';
                                                        break;
                                                    case 'investment':
                                                    case 'staking':
                                                        echo 'badge-info';
                                                        break;
                                                    default:
                                                        echo 'badge-secondary';
                                                }
                                            ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $transaction['transaction_type'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            $<?php echo number_format($transaction['amount'], 2); ?>
                                            <?php if (!empty($transaction['fee']) && $transaction['fee'] > 0): ?>
                                            <small class="d-block text-muted">Fee: $<?php echo number_format($transaction['fee'], 2); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php 
                                                switch ($transaction['status']) {
                                                    case 'completed':
                                                    case 'active':
                                                        echo 'badge-success';
                                                        break;
                                                    case 'pending':
                                                        echo 'badge-warning';
                                                        break;
                                                    case 'cancelled':
                                                    case 'rejected':
                                                    case 'failed':
                                                        echo 'badge-danger';
                                                        break;
                                                    default:
                                                        echo 'badge-secondary';
                                                }
                                            ?>">
                                                <?php echo ucfirst($transaction['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y H:i', strtotime($transaction['date_time'])); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['reference_id'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['description'] ?? 'N/A'); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <button class="dropdown-item view-details" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#transactionDetailsModal"
                                                                data-id="<?php echo $transaction['transaction_id']; ?>"
                                                                data-type="<?php echo $transaction['transaction_type']; ?>"
                                                                data-amount="<?php echo $transaction['amount']; ?>"
                                                                data-status="<?php echo $transaction['status']; ?>"
                                                                data-reference="<?php echo htmlspecialchars($transaction['reference_id'] ?? 'N/A'); ?>"
                                                                data-description="<?php echo htmlspecialchars($transaction['description'] ?? 'N/A'); ?>"
                                                                data-date="<?php echo date('M d, Y H:i', strtotime($transaction['date_time'])); ?>"
                                                                data-user="<?php echo htmlspecialchars($transaction['user_name']); ?>"
                                                                data-username="<?php echo htmlspecialchars($transaction['username']); ?>"
                                                                data-email="<?php echo htmlspecialchars($transaction['email']); ?>"
                                                                data-fee="<?php echo $transaction['fee'] ?? '0'; ?>"
                                                        >
                                                            <i class="fa fa-eye"></i> View Details
                                                        </button>
                                                    </li>
                                                    <?php if ($transaction['status'] == 'pending'): ?>
                                                    <li>
                                                        <button class="dropdown-item change-status" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#changeStatusModal"
                                                                data-id="<?php echo $transaction['transaction_id']; ?>"
                                                                data-current-status="<?php echo $transaction['status']; ?>">
                                                            <i class="fa fa-edit"></i> Change Status
                                                        </button>
                                                    </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">No transactions found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="box-footer clearfix">
                    <ul class="pagination pagination-sm m-0 float-end">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">&laquo;</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">&lt;</a>
                        </li>
                        <?php endif; ?>
                        
                        <?php 
                        $start_page = max(1, $page - 2);
                        $end_page = min($start_page + 4, $total_pages);
                        
                        if ($end_page - $start_page < 4) {
                            $start_page = max(1, $end_page - 4);
                        }
                        
                        for ($i = $start_page; $i <= $end_page; $i++): 
                        ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">&gt;</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>">&raquo;</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Transaction Details Modal -->
<div class="modal fade" id="transactionDetailsModal" tabindex="-1" aria-labelledby="transactionDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionDetailsModalLabel">Transaction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div id="detailStatus" class="badge badge-success mb-2">Completed</div>
                    <h3 id="detailAmount">$0.00</h3>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Transaction ID:</strong> <span id="detailId"></span></p>
                        <p><strong>Type:</strong> <span id="detailType"></span></p>
                        <p><strong>Reference ID:</strong> <span id="detailReference"></span></p>
                        <p><strong>Date/Time:</strong> <span id="detailDate"></span></p>
                        <p><strong>Fee:</strong> $<span id="detailFee"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>User:</strong> <span id="detailUser"></span></p>
                        <p><strong>Username:</strong> <span id="detailUsername"></span></p>
                        <p><strong>Email:</strong> <span id="detailEmail"></span></p>
                        <p><strong>Status:</strong> <span id="detailStatusText"></span></p>
                    </div>
                </div>
                
                <div class="mt-3">
                    <p><strong>Description:</strong></p>
                    <p id="detailDescription" class="bg-light p-2 rounded"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="changeStatusBtn" class="btn btn-primary d-none" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#changeStatusModal">Change Status</button>
            </div>
        </div>
    </div>
</div>

<!-- Change Status Modal -->
<div class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeStatusModalLabel">Change Transaction Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="transaction_id" id="statusTransactionId">
                    
                    <div class="form-group mb-3">
                        <label for="statusSelect" class="form-label">New Status</label>
                        <select class="form-select" id="statusSelect" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle me-2"></i>
                        Changing the transaction status may affect user balances or investment states. Please be careful.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Transaction Details Modal
    const viewButtons = document.querySelectorAll('.view-details');
    if (viewButtons) {
        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const type = this.getAttribute('data-type');
                const amount = this.getAttribute('data-amount');
                const status = this.getAttribute('data-status');
                const reference = this.getAttribute('data-reference');
                const description = this.getAttribute('data-description');
                const date = this.getAttribute('data-date');
                const user = this.getAttribute('data-user');
                const username = this.getAttribute('data-username');
                const email = this.getAttribute('data-email');
                const fee = this.getAttribute('data-fee');
                
                document.getElementById('detailId').textContent = id;
                document.getElementById('detailType').textContent = type.replace(/_/g, ' ');
                document.getElementById('detailAmount').textContent = '$' + parseFloat(amount).toFixed(2);
                document.getElementById('detailReference').textContent = reference;
                document.getElementById('detailDescription').textContent = description;
                document.getElementById('detailDate').textContent = date;
                document.getElementById('detailUser').textContent = user;
                document.getElementById('detailUsername').textContent = username;
                document.getElementById('detailEmail').textContent = email;
                document.getElementById('detailFee').textContent = parseFloat(fee).toFixed(2);
                document.getElementById('detailStatusText').textContent = status.charAt(0).toUpperCase() + status.slice(1);
                
                // Set status badge class
                const statusBadge = document.getElementById('detailStatus');
                statusBadge.className = 'badge mb-2';
                
                if (status === 'completed' || status === 'active') {
                    statusBadge.classList.add('badge-success');
                } else if (status === 'pending') {
                    statusBadge.classList.add('badge-warning');
                } else {
                    statusBadge.classList.add('badge-danger');
                }
                
                statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                
                // Show/hide change status button
                const changeStatusBtn = document.getElementById('changeStatusBtn');
                if (status === 'pending') {
                    changeStatusBtn.classList.remove('d-none');
                    changeStatusBtn.setAttribute('data-id', id);
                } else {
                    changeStatusBtn.classList.add('d-none');
                }
            });
        });
    }
    
    // Change Status Modal
    const changeStatusButtons = document.querySelectorAll('.change-status');
    if (changeStatusButtons) {
        changeStatusButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const currentStatus = this.getAttribute('data-current-status');
                
                document.getElementById('statusTransactionId').value = id;
                document.getElementById('statusSelect').value = currentStatus;
            });
        });
    }
    
    document.getElementById('changeStatusBtn').addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        document.getElementById('statusTransactionId').value = id;
    });
});
</script>

<?php
require_once __DIR__ . '/layout/footer.php';
?> 