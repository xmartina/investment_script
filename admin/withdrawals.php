<?php
// Admin Withdrawals Management Page
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Withdrawal Management";
$message = "";
$error = "";

// Process withdrawal approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_withdrawal'])) {
        $withdrawal_id = $_POST['withdrawal_id'];
        
        // Update withdrawal status to approved
        $stmt = $conn_back->prepare("UPDATE withdrawal SET status = 'approved', approved_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $withdrawal_id);
        
        if ($stmt->execute()) {
            // Update the transaction status
            $stmt = $conn_back->prepare("
                UPDATE transactions 
                SET status = 'approved' 
                WHERE reference_id = (SELECT transaction_id FROM withdrawal WHERE id = ?)
            ");
            $stmt->bind_param("i", $withdrawal_id);
            $stmt->execute();
            
            $message = "Withdrawal #$withdrawal_id has been approved successfully.";
        } else {
            $error = "Error approving withdrawal: " . $stmt->error;
        }
        
        $stmt->close();
    } elseif (isset($_POST['reject_withdrawal'])) {
        $withdrawal_id = $_POST['withdrawal_id'];
        $rejection_reason = $_POST['rejection_reason'];
        
        // Begin transaction
        $conn_back->begin_transaction();
        
        try {
            // Get withdrawal details
            $stmt = $conn_back->prepare("
                SELECT user_id, amount, fee_amount, user_balance_before_withdrawal, user_balance_after_withdrawal
                FROM withdrawal 
                WHERE id = ?
            ");
            $stmt->bind_param("i", $withdrawal_id);
            $stmt->execute();
            $withdrawal = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            // Get balance field
            $stmt = $conn_back->prepare("SELECT transaction_type FROM transactions WHERE reference_id = (SELECT transaction_id FROM withdrawal WHERE id = ?)");
            $stmt->bind_param("i", $withdrawal_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $transaction = $result->fetch_assoc();
            $stmt->close();
            
            // Refund the user's balance
            $balance_field = 'main_balance'; // Default
            
            // Update user balance
            $new_balance = $withdrawal['user_balance_before_withdrawal'];
            $stmt = $conn_back->prepare("UPDATE users SET $balance_field = ? WHERE id = ?");
            $stmt->bind_param("di", $new_balance, $withdrawal['user_id']);
            $stmt->execute();
            $stmt->close();
            
            // Update withdrawal status
            $stmt = $conn_back->prepare("
                UPDATE withdrawal 
                SET status = 'rejected', rejected_at = NOW(), rejection_reason = ? 
                WHERE id = ?
            ");
            $stmt->bind_param("si", $rejection_reason, $withdrawal_id);
            $stmt->execute();
            $stmt->close();
            
            // Update transaction status
            $stmt = $conn_back->prepare("
                UPDATE transactions 
                SET status = 'rejected', description = CONCAT(description, ' - Rejected: ', ?) 
                WHERE reference_id = (SELECT transaction_id FROM withdrawal WHERE id = ?)
            ");
            $stmt->bind_param("si", $rejection_reason, $withdrawal_id);
            $stmt->execute();
            $stmt->close();
            
            // Commit transaction
            $conn_back->commit();
            
            $message = "Withdrawal #$withdrawal_id has been rejected and funds returned to the user's account.";
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn_back->rollback();
            $error = "Error rejecting withdrawal: " . $e->getMessage();
        }
    } elseif (isset($_POST['complete_withdrawal'])) {
        $withdrawal_id = $_POST['withdrawal_id'];
        $payment_proof = '';
        
        // Upload payment proof if provided
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/payment_proofs/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_name = time() . '_' . basename($_FILES['payment_proof']['name']);
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $target_path)) {
                $payment_proof = '/uploads/payment_proofs/' . $file_name;
            } else {
                $error = "Error uploading payment proof.";
            }
        }
        
        if (empty($error)) {
            // Update withdrawal status to completed
            $stmt = $conn_back->prepare("
                UPDATE withdrawal 
                SET status = 'completed', payment_proof = ? 
                WHERE id = ?
            ");
            $stmt->bind_param("si", $payment_proof, $withdrawal_id);
            
            if ($stmt->execute()) {
                // Update transaction status
                $stmt = $conn_back->prepare("
                    UPDATE transactions 
                    SET status = 'completed' 
                    WHERE reference_id = (SELECT transaction_id FROM withdrawal WHERE id = ?)
                ");
                $stmt->bind_param("i", $withdrawal_id);
                $stmt->execute();
                
                $message = "Withdrawal #$withdrawal_id has been marked as completed.";
            } else {
                $error = "Error completing withdrawal: " . $stmt->error;
            }
            
            $stmt->close();
        }
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Filtering
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$valid_statuses = ['pending', 'approved', 'rejected', 'completed', ''];

if (!in_array($status_filter, $valid_statuses)) {
    $status_filter = '';
}

// Build query condition
$condition = "1=1";
$params = [];
$types = "";

if (!empty($status_filter)) {
    $condition .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM withdrawal WHERE $condition";
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

// Get withdrawals
$sql = "
    SELECT w.*, u.username, wm.method_name 
    FROM withdrawal w
    JOIN users u ON w.user_id = u.id
    JOIN withdrawal_methods wm ON w.withdrawal_method_id = wm.id
    WHERE $condition
    ORDER BY w.created_at DESC
    LIMIT ? OFFSET ?
";
$types .= "ii";
$params[] = $records_per_page;
$params[] = $offset;

$stmt = $conn_back->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$withdrawals = $stmt->get_result();
$stmt->close();

include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layout/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Withdrawal Management</h1>
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
                <a class="dropdown-toggle" href="#" role="button" id="filterDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-filter fa-sm fa-fw text-gray-400"></i> Filter
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="filterDropdown">
                    <div class="dropdown-header">Status Filter:</div>
                    <a class="dropdown-item <?= $status_filter === '' ? 'active' : '' ?>" href="?status=">All</a>
                    <a class="dropdown-item <?= $status_filter === 'pending' ? 'active' : '' ?>" href="?status=pending">Pending</a>
                    <a class="dropdown-item <?= $status_filter === 'approved' ? 'active' : '' ?>" href="?status=approved">Approved</a>
                    <a class="dropdown-item <?= $status_filter === 'completed' ? 'active' : '' ?>" href="?status=completed">Completed</a>
                    <a class="dropdown-item <?= $status_filter === 'rejected' ? 'active' : '' ?>" href="?status=rejected">Rejected</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
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
                        <?php if ($withdrawals->num_rows > 0): ?>
                            <?php while ($withdrawal = $withdrawals->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $withdrawal['id'] ?></td>
                                    <td><?= htmlspecialchars($withdrawal['username']) ?></td>
                                    <td>$<?= number_format($withdrawal['amount'], 2) ?></td>
                                    <td><?= htmlspecialchars($withdrawal['method_name']) ?></td>
                                    <td><?= date('M d, Y H:i', strtotime($withdrawal['created_at'])) ?></td>
                                    <td>
                                        <span class="badge badge-<?php
                                            switch ($withdrawal['status']) {
                                                case 'pending': echo 'warning'; break;
                                                case 'approved': echo 'info'; break;
                                                case 'completed': echo 'success'; break;
                                                case 'rejected': echo 'danger'; break;
                                                default: echo 'secondary';
                                            }
                                        ?>">
                                            <?= ucfirst($withdrawal['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary view-details" data-toggle="modal" data-target="#detailsModal" 
                                                data-id="<?= $withdrawal['id'] ?>" 
                                                data-user="<?= htmlspecialchars($withdrawal['username']) ?>"
                                                data-amount="<?= $withdrawal['amount'] ?>"
                                                data-fee="<?= $withdrawal['fee_amount'] ?>"
                                                data-method="<?= htmlspecialchars($withdrawal['method_name']) ?>"
                                                data-address="<?= htmlspecialchars($withdrawal['withdrawal_address']) ?>"
                                                data-date="<?= date('M d, Y H:i', strtotime($withdrawal['created_at'])) ?>"
                                                data-status="<?= $withdrawal['status'] ?>"
                                                data-transaction="<?= $withdrawal['transaction_id'] ?>"
                                                data-proof="<?= $withdrawal['payment_proof'] ?? '' ?>"
                                                data-rejection="<?= htmlspecialchars($withdrawal['rejection_reason'] ?? '') ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php if ($withdrawal['status'] === 'pending'): ?>
                                            <button class="btn btn-sm btn-success approve-withdrawal" data-toggle="modal" data-target="#approveModal" data-id="<?= $withdrawal['id'] ?>">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger reject-withdrawal" data-toggle="modal" data-target="#rejectModal" data-id="<?= $withdrawal['id'] ?>">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($withdrawal['status'] === 'approved'): ?>
                                            <button class="btn btn-sm btn-success complete-withdrawal" data-toggle="modal" data-target="#completeModal" data-id="<?= $withdrawal['id'] ?>">
                                                <i class="fas fa-check-double"></i>
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
                                <a class="page-link" href="?page=1<?= !empty($status_filter) ? '&status='.$status_filter : '' ?>" aria-label="First">
                                    <span aria-hidden="true">&laquo;&laquo;</span>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?><?= !empty($status_filter) ? '&status='.$status_filter : '' ?>" aria-label="Previous">
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
                                <a class="page-link" href="?page=<?= $i ?><?= !empty($status_filter) ? '&status='.$status_filter : '' ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?><?= !empty($status_filter) ? '&status='.$status_filter : '' ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $total_pages ?><?= !empty($status_filter) ? '&status='.$status_filter : '' ?>" aria-label="Last">
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
                        <p><strong>ID:</strong> <span id="detailId"></span></p>
                        <p><strong>User:</strong> <span id="detailUser"></span></p>
                        <p><strong>Method:</strong> <span id="detailMethod"></span></p>
                        <p><strong>Fee:</strong> $<span id="detailFee"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Transaction ID:</strong> <span id="detailTransaction"></span></p>
                        <p><strong>Date:</strong> <span id="detailDate"></span></p>
                        <p><strong>Status:</strong> <span id="detailStatus"></span></p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><strong>Withdrawal Address/Details:</strong></label>
                    <textarea class="form-control" id="detailAddress" rows="3" readonly></textarea>
                </div>
                
                <div id="rejectionReasonDiv" class="form-group d-none">
                    <label><strong>Rejection Reason:</strong></label>
                    <div id="detailRejection" class="p-2 bg-light border rounded"></div>
                </div>
                
                <div id="paymentProofDiv" class="form-group d-none">
                    <label><strong>Payment Proof:</strong></label>
                    <div class="text-center">
                        <a id="paymentProofLink" href="#" target="_blank">
                            <img id="paymentProofImage" src="" alt="Payment Proof" class="img-fluid mb-2">
                            <div>View Full Image</div>
                        </a>
                    </div>
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
                    <input type="hidden" name="withdrawal_id" id="approveWithdrawalId">
                    <p>Are you sure you want to approve this withdrawal request? This action indicates that you are processing the withdrawal.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="approve_withdrawal" class="btn btn-success">Approve</button>
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
                    <input type="hidden" name="withdrawal_id" id="rejectWithdrawalId">
                    <div class="form-group">
                        <label for="rejection_reason">Rejection Reason</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
                        <small class="form-text text-muted">Please provide a reason for rejecting this withdrawal request. This will be visible to the user.</small>
                    </div>
                    <p class="text-danger">Warning: This action will cancel the withdrawal and refund the amount to the user's account.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="reject_withdrawal" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Complete Modal -->
<div class="modal fade" id="completeModal" tabindex="-1" role="dialog" aria-labelledby="completeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="completeModalLabel">Complete Withdrawal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="withdrawal_id" id="completeWithdrawalId">
                    <p>Are you sure you want to mark this withdrawal as completed? This indicates that you have processed the payment to the user.</p>
                    <div class="form-group">
                        <label for="payment_proof">Payment Proof (Optional)</label>
                        <input type="file" class="form-control-file" id="payment_proof" name="payment_proof">
                        <small class="form-text text-muted">Upload a screenshot or receipt of the payment as proof.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="complete_withdrawal" class="btn btn-success">Mark as Completed</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // View details
    $('.view-details').on('click', function() {
        var id = $(this).data('id');
        var user = $(this).data('user');
        var amount = $(this).data('amount');
        var fee = $(this).data('fee');
        var method = $(this).data('method');
        var address = $(this).data('address');
        var date = $(this).data('date');
        var status = $(this).data('status');
        var transaction = $(this).data('transaction');
        var proof = $(this).data('proof');
        var rejection = $(this).data('rejection');
        
        $('#detailId').text(id);
        $('#detailUser').text(user);
        $('#detailAmount').text('$' + parseFloat(amount).toFixed(2));
        $('#detailFee').text(parseFloat(fee).toFixed(2));
        $('#detailMethod').text(method);
        $('#detailAddress').val(address);
        $('#detailDate').text(date);
        $('#detailStatus').text(status.charAt(0).toUpperCase() + status.slice(1));
        $('#detailTransaction').text(transaction);
        
        // Update status badge
        var badgeClass = 'badge-secondary';
        switch (status) {
            case 'pending': badgeClass = 'badge-warning'; break;
            case 'approved': badgeClass = 'badge-info'; break;
            case 'completed': badgeClass = 'badge-success'; break;
            case 'rejected': badgeClass = 'badge-danger'; break;
        }
        $('#statusBadge').attr('class', 'badge ' + badgeClass + ' mb-2').text(status.charAt(0).toUpperCase() + status.slice(1));
        
        // Show/hide rejection reason
        if (status === 'rejected' && rejection) {
            $('#rejectionReasonDiv').removeClass('d-none');
            $('#detailRejection').text(rejection);
        } else {
            $('#rejectionReasonDiv').addClass('d-none');
        }
        
        // Show/hide payment proof
        if (status === 'completed' && proof) {
            $('#paymentProofDiv').removeClass('d-none');
            $('#paymentProofImage').attr('src', proof);
            $('#paymentProofLink').attr('href', proof);
        } else {
            $('#paymentProofDiv').addClass('d-none');
        }
    });
    
    // Approve withdrawal
    $('.approve-withdrawal').on('click', function() {
        var id = $(this).data('id');
        $('#approveWithdrawalId').val(id);
    });
    
    // Reject withdrawal
    $('.reject-withdrawal').on('click', function() {
        var id = $(this).data('id');
        $('#rejectWithdrawalId').val(id);
    });
    
    // Complete withdrawal
    $('.complete-withdrawal').on('click', function() {
        var id = $(this).data('id');
        $('#completeWithdrawalId').val(id);
    });
});
</script>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layout/footer.php'; ?> 