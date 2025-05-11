<?php
// Admin Withdrawals Management Page
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Withdrawals";
$current_page = "withdrawals.php";
$message = "";
$error = "";

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Approve withdrawal
    if (isset($_POST['approve_withdrawal'])) {
        $withdrawal_id = (int)$_POST['withdrawal_id'];
        $transaction_hash = $_POST['transaction_hash'] ?? '';
        
        // Get withdrawal details
        $stmt = $conn_back->prepare("SELECT * FROM withdrawal WHERE id = ? AND status = 'pending'");
        $stmt->bind_param("i", $withdrawal_id);
        $stmt->execute();
        $withdrawal = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($withdrawal) {
            // Begin transaction
            $conn_back->begin_transaction();
            
            try {
                // Update withdrawal status
                $stmt = $conn_back->prepare("UPDATE withdrawal SET status = 'approved', approved_at = NOW() WHERE id = ?");
                $stmt->bind_param("i", $withdrawal_id);
                $stmt->execute();
                $stmt->close();
                
                // Create transaction record
                $transaction_reference = 'WITHDRAW' . time() . rand(1000, 9999);
                $description = "Withdrawal approved via " . $withdrawal['withdrawal_method_id'];
                
                // Check if transaction exists
                $check_stmt = $conn_back->prepare("SELECT transaction_id FROM transactions WHERE reference_id = ?");
                $check_stmt->bind_param("s", $withdrawal['transaction_id']);
                $check_stmt->execute();
                $transaction_result = $check_stmt->get_result();
                $check_stmt->close();
                
                if ($transaction_result->num_rows == 0) {
                    // Create new transaction record
                    $stmt = $conn_back->prepare("
                        INSERT INTO transactions (
                            transaction_type, reference_id, transaction_proof_id, 
                            amount, currency, status, date_time, 
                            description, user_id
                        ) VALUES (
                            'withdrawal', ?, ?, ?, ?, 'completed', NOW(), ?, ?
                        )
                    ");
                    $stmt->bind_param(
                        "ssdssi", 
                        $withdrawal['transaction_id'], 
                        $withdrawal['transaction_proof_id'], 
                        $withdrawal['amount'], 
                        $withdrawal['currency'], 
                        $description, 
                        $withdrawal['user_id']
                    );
                    $stmt->execute();
                    $stmt->close();
                } else {
                    // Update existing transaction
                    $stmt = $conn_back->prepare("UPDATE transactions SET status = 'completed' WHERE reference_id = ?");
                    $stmt->bind_param("s", $withdrawal['transaction_id']);
                    $stmt->execute();
                    $stmt->close();
                }
                
                // Log admin action
                $admin_id = $_SESSION['admin_id'];
                $action = "Approved withdrawal #{$withdrawal_id} of {$withdrawal['amount']} {$withdrawal['currency']} for user #{$withdrawal['user_id']}";
                if (!empty($transaction_hash)) {
                    $action .= " with transaction hash: {$transaction_hash}";
                }
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $admin_id, $action, $ip);
                $stmt->execute();
                $stmt->close();
                
                // Commit transaction
                $conn_back->commit();
                
                $message = "Withdrawal #{$withdrawal_id} has been approved successfully.";
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn_back->rollback();
                $error = "Error approving withdrawal: " . $e->getMessage();
            }
        } else {
            $error = "Withdrawal not found or already processed.";
        }
    }
    
    // Reject withdrawal
    if (isset($_POST['reject_withdrawal'])) {
        $withdrawal_id = (int)$_POST['withdrawal_id'];
        $rejection_reason = $_POST['rejection_reason'] ?? '';
        
        // Get withdrawal details
        $stmt = $conn_back->prepare("SELECT * FROM withdrawal WHERE id = ? AND status = 'pending'");
        $stmt->bind_param("i", $withdrawal_id);
        $stmt->execute();
        $withdrawal = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($withdrawal) {
            // Begin transaction
            $conn_back->begin_transaction();
            
            try {
                // Update withdrawal status
                $stmt = $conn_back->prepare("UPDATE withdrawal SET status = 'rejected', rejected_at = NOW(), rejection_reason = ? WHERE id = ?");
                $stmt->bind_param("si", $rejection_reason, $withdrawal_id);
                $stmt->execute();
                $stmt->close();
                
                // Refund user's balance
                $stmt = $conn_back->prepare("UPDATE users SET main_balance = main_balance + ? WHERE id = ?");
                $stmt->bind_param("di", $withdrawal['amount'], $withdrawal['user_id']);
                $stmt->execute();
                $stmt->close();
                
                // Update transaction status
                $stmt = $conn_back->prepare("UPDATE transactions SET status = 'rejected' WHERE reference_id = ?");
                $stmt->bind_param("s", $withdrawal['transaction_id']);
                $stmt->execute();
                $stmt->close();
                
                // Log admin action
                $admin_id = $_SESSION['admin_id'];
                $action = "Rejected withdrawal #{$withdrawal_id} of {$withdrawal['amount']} {$withdrawal['currency']} for user #{$withdrawal['user_id']}";
                if (!empty($rejection_reason)) {
                    $action .= ". Reason: {$rejection_reason}";
                }
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $admin_id, $action, $ip);
                $stmt->execute();
                $stmt->close();
                
                // Commit transaction
                $conn_back->commit();
                
                $message = "Withdrawal #{$withdrawal_id} has been rejected and funds returned to the user's balance.";
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn_back->rollback();
                $error = "Error rejecting withdrawal: " . $e->getMessage();
            }
        } else {
            $error = "Withdrawal not found or already processed.";
        }
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$records_per_page = 20;
$offset = ($page - 1) * $records_per_page;

// Build query condition
$condition = "";
$params = [];
$types = "";

if (!empty($status_filter)) {
    $condition = " WHERE w.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM withdrawal w" . $condition;
if (!empty($types)) {
    $stmt = $conn_back->prepare($count_sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_records = $row['total'];
    $stmt->close();
} else {
    $result = $conn_back->query($count_sql);
    $row = $result->fetch_assoc();
    $total_records = $row['total'];
}

$total_pages = ceil($total_records / $records_per_page);

// Get withdrawals
$params_paged = $params;
$params_paged[] = $records_per_page;
$params_paged[] = $offset;
$types .= "ii";

$sql = "
    SELECT 
        w.*,
        CONCAT(u.first_name, ' ', u.last_name) as username,
        u.email,
        wm.method_name
    FROM withdrawal w
    JOIN users u ON w.user_id = u.id
    JOIN withdrawal_methods wm ON w.withdrawal_method_id = wm.id
    $condition
    ORDER BY w.created_at DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn_back->prepare($sql);
if (!$stmt) {
    die("Error in SQL query: " . $conn_back->error);
}

$stmt->bind_param($types, ...$params_paged);
$stmt->execute();
$withdrawals = $stmt->get_result();
$stmt->close();

include_once __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Withdrawals Management</h1>
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

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Withdrawal Requests</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="statusDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-filter fa-sm fa-fw text-gray-400"></i> Filter by Status
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="statusDropdown">
                    <a class="dropdown-item <?= $status_filter === '' ? 'active' : '' ?>" href="?status=">All</a>
                    <a class="dropdown-item <?= $status_filter === 'pending' ? 'active' : '' ?>" href="?status=pending">Pending</a>
                    <a class="dropdown-item <?= $status_filter === 'approved' ? 'active' : '' ?>" href="?status=approved">Approved</a>
                    <a class="dropdown-item <?= $status_filter === 'rejected' ? 'active' : '' ?>" href="?status=rejected">Rejected</a>
                    <a class="dropdown-item <?= $status_filter === 'completed' ? 'active' : '' ?>" href="?status=completed">Completed</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="withdrawalsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($withdrawals && $withdrawals->num_rows > 0): ?>
                            <?php while ($withdrawal = $withdrawals->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $withdrawal['id'] ?></td>
                                    <td>
                                        <a href="user_detail.php?id=<?= $withdrawal['user_id'] ?>">
                                            <?= htmlspecialchars($withdrawal['username']) ?>
                                        </a>
                                        <small class="d-block text-muted"><?= htmlspecialchars($withdrawal['email']) ?></small>
                                    </td>
                                    <td>
                                        <?= number_format($withdrawal['amount'], 2) ?> <?= htmlspecialchars($withdrawal['currency']) ?>
                                        <?php if ($withdrawal['fee_amount'] > 0): ?>
                                            <small class="d-block text-muted">Fee: <?= number_format($withdrawal['fee_amount'], 2) ?> <?= htmlspecialchars($withdrawal['currency']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($withdrawal['method_name']) ?>
                                        <small class="d-block text-muted">
                                            <?php 
                                            // Truncate address if too long
                                            $address = $withdrawal['withdrawal_address'];
                                            echo strlen($address) > 15 ? substr($address, 0, 15) . '...' : $address;
                                            ?>
                                        </small>
                                    </td>
                                    <td><?= date('M d, Y H:i', strtotime($withdrawal['created_at'])) ?></td>
                                    <td>
                                        <span class="badge badge-<?php
                                            switch ($withdrawal['status']) {
                                                case 'pending': echo 'warning'; break;
                                                case 'approved': echo 'primary'; break;
                                                case 'completed': echo 'success'; break;
                                                case 'rejected': echo 'danger'; break;
                                                default: echo 'secondary';
                                            }
                                        ?>">
                                            <?= ucfirst($withdrawal['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info view-details-btn" data-toggle="modal" data-target="#detailsModal" 
                                                data-id="<?= $withdrawal['id'] ?>"
                                                data-user="<?= htmlspecialchars($withdrawal['username']) ?>" 
                                                data-amount="<?= $withdrawal['amount'] ?>"
                                                data-currency="<?= htmlspecialchars($withdrawal['currency']) ?>"
                                                data-fee="<?= $withdrawal['fee_amount'] ?>"
                                                data-method="<?= htmlspecialchars($withdrawal['method_name']) ?>"
                                                data-address="<?= htmlspecialchars($withdrawal['withdrawal_address']) ?>"
                                                data-date="<?= date('M d, Y H:i', strtotime($withdrawal['created_at'])) ?>"
                                                data-status="<?= $withdrawal['status'] ?>"
                                                data-reason="<?= htmlspecialchars($withdrawal['rejection_reason'] ?? '') ?>">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        
                                        <?php if ($withdrawal['status'] === 'pending'): ?>
                                            <button class="btn btn-sm btn-success approve-btn" data-toggle="modal" data-target="#approveModal" 
                                                    data-id="<?= $withdrawal['id'] ?>" 
                                                    data-amount="<?= $withdrawal['amount'] ?>" 
                                                    data-currency="<?= htmlspecialchars($withdrawal['currency']) ?>" 
                                                    data-user="<?= htmlspecialchars($withdrawal['username']) ?>"
                                                    data-method="<?= htmlspecialchars($withdrawal['method_name']) ?>"
                                                    data-address="<?= htmlspecialchars($withdrawal['withdrawal_address']) ?>">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button class="btn btn-sm btn-danger reject-btn" data-toggle="modal" data-target="#rejectModal" 
                                                    data-id="<?= $withdrawal['id'] ?>" 
                                                    data-amount="<?= $withdrawal['amount'] ?>" 
                                                    data-currency="<?= htmlspecialchars($withdrawal['currency']) ?>" 
                                                    data-user="<?= htmlspecialchars($withdrawal['username']) ?>">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No withdrawal requests found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1<?= !empty($status_filter) ? '&status=' . $status_filter : '' ?>" aria-label="First">
                                    <span aria-hidden="true">&laquo;&laquo;</span>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?><?= !empty($status_filter) ? '&status=' . $status_filter : '' ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        if ($end_page - $start_page + 1 < 5 && $total_pages >= 5) {
                            if ($start_page == 1) {
                                $end_page = min($total_pages, 5);
                            } elseif ($end_page == $total_pages) {
                                $start_page = max(1, $total_pages - 4);
                            }
                        }
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= !empty($status_filter) ? '&status=' . $status_filter : '' ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?><?= !empty($status_filter) ? '&status=' . $status_filter : '' ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $total_pages ?><?= !empty($status_filter) ? '&status=' . $status_filter : '' ?>" aria-label="Last">
                                    <span aria-hidden="true">&raquo;&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Withdrawal Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div id="statusBadge" class="badge badge-warning mb-2">Pending</div>
                    <h4 id="detailAmount">$0.00</h4>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>User:</strong> <span id="detailUser"></span></p>
                        <p><strong>Method:</strong> <span id="detailMethod"></span></p>
                        <p><strong>Address:</strong> <span id="detailAddress"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Date:</strong> <span id="detailDate"></span></p>
                        <p><strong>Status:</strong> <span id="detailStatus"></span></p>
                        <p id="detailFeeContainer"><strong>Fee:</strong> <span id="detailFee"></span></p>
                    </div>
                </div>
                
                <div id="rejectionReasonContainer" class="form-group d-none">
                    <label><strong>Rejection Reason:</strong></label>
                    <div id="detailReason" class="p-2 bg-light border rounded"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">Approve Withdrawal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="withdrawal_id" id="approve_withdrawal_id">
                    <p>Are you sure you want to approve this withdrawal?</p>
                    <p><strong>User:</strong> <span id="approve_user"></span></p>
                    <p><strong>Amount:</strong> <span id="approve_amount"></span></p>
                    <p><strong>Method:</strong> <span id="approve_method"></span></p>
                    <p><strong>Address:</strong> <span id="approve_address"></span></p>
                    
                    <div class="form-group">
                        <label for="transaction_hash">Transaction Hash (optional):</label>
                        <input type="text" class="form-control" id="transaction_hash" name="transaction_hash" placeholder="Enter blockchain transaction hash if available">
                    </div>
                    
                    <p class="text-info">This will mark the withdrawal as approved. Make sure you have sent the funds to the user's address.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="approve_withdrawal" class="btn btn-success">Approve Withdrawal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Reject Withdrawal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="withdrawal_id" id="reject_withdrawal_id">
                    <p>Are you sure you want to reject this withdrawal?</p>
                    <p><strong>User:</strong> <span id="reject_user"></span></p>
                    <p><strong>Amount:</strong> <span id="reject_amount"></span></p>
                    
                    <div class="form-group">
                        <label for="rejection_reason">Rejection Reason:</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
                    </div>
                    
                    <p class="text-danger">This will mark the withdrawal as rejected and refund the amount to the user's balance.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="reject_withdrawal" class="btn btn-danger">Reject Withdrawal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // View details modal
    $('.view-details-btn').click(function() {
        var id = $(this).data('id');
        var user = $(this).data('user');
        var amount = $(this).data('amount');
        var currency = $(this).data('currency');
        var fee = $(this).data('fee');
        var method = $(this).data('method');
        var address = $(this).data('address');
        var date = $(this).data('date');
        var status = $(this).data('status');
        var reason = $(this).data('reason');
        
        // Update modal content
        $('#detailUser').text(user);
        $('#detailAmount').text(amount + ' ' + currency);
        $('#detailMethod').text(method);
        $('#detailAddress').text(address);
        $('#detailDate').text(date);
        $('#detailStatus').text(status.charAt(0).toUpperCase() + status.slice(1));
        
        // Show/hide fee if available
        if (fee && fee > 0) {
            $('#detailFeeContainer').show();
            $('#detailFee').text(fee + ' ' + currency);
        } else {
            $('#detailFeeContainer').hide();
        }
        
        // Update status badge
        var badgeClass = 'badge-secondary';
        switch (status) {
            case 'pending': badgeClass = 'badge-warning'; break;
            case 'approved': badgeClass = 'badge-primary'; break;
            case 'completed': badgeClass = 'badge-success'; break;
            case 'rejected': badgeClass = 'badge-danger'; break;
        }
        $('#statusBadge').attr('class', 'badge ' + badgeClass + ' mb-2').text(status.charAt(0).toUpperCase() + status.slice(1));
        
        // Show/hide rejection reason
        if (status === 'rejected' && reason) {
            $('#rejectionReasonContainer').removeClass('d-none');
            $('#detailReason').text(reason);
        } else {
            $('#rejectionReasonContainer').addClass('d-none');
        }
    });
    
    // Approve modal
    $('.approve-btn').click(function() {
        var id = $(this).data('id');
        var user = $(this).data('user');
        var amount = $(this).data('amount');
        var currency = $(this).data('currency');
        var method = $(this).data('method');
        var address = $(this).data('address');
        
        $('#approve_withdrawal_id').val(id);
        $('#approve_user').text(user);
        $('#approve_amount').text(amount + ' ' + currency);
        $('#approve_method').text(method);
        $('#approve_address').text(address);
    });
    
    // Reject modal
    $('.reject-btn').click(function() {
        var id = $(this).data('id');
        var user = $(this).data('user');
        var amount = $(this).data('amount');
        var currency = $(this).data('currency');
        
        $('#reject_withdrawal_id').val(id);
        $('#reject_user').text(user);
        $('#reject_amount').text(amount + ' ' + currency);
    });
});
</script>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 