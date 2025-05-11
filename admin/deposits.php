<?php
// Admin Deposits Management Page
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Deposit Management";
$message = "";
$error = "";

// Process deposit approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_deposit'])) {
        $deposit_id = $_POST['deposit_id'];
        
        // Get deposit details
        $stmt = $conn_back->prepare("SELECT user_id, amount, status FROM deposit_requests WHERE id = ?");
        $stmt->bind_param("i", $deposit_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $deposit = $result->fetch_assoc();
        $stmt->close();
        
        if ($deposit && $deposit['status'] == 'pending') {
            // Begin transaction
            $conn_back->begin_transaction();
            
            try {
                // Update user balance
                $stmt = $conn_back->prepare("UPDATE users SET main_balance = main_balance + ? WHERE id = ?");
                $stmt->bind_param("di", $deposit['amount'], $deposit['user_id']);
                $stmt->execute();
                $stmt->close();
                
                // Update deposit status
                $stmt = $conn_back->prepare("UPDATE deposit_requests SET status = 'approved', approved_at = NOW() WHERE id = ?");
                $stmt->bind_param("i", $deposit_id);
                $stmt->execute();
                $stmt->close();
                
                // Record transaction
                $description = "Deposit #$deposit_id approved";
                $stmt = $conn_back->prepare("INSERT INTO transactions (user_id, amount, type, status, description, created_at) VALUES (?, ?, 'deposit', 'completed', ?, NOW())");
                $stmt->bind_param("ids", $deposit['user_id'], $deposit['amount'], $description);
                $stmt->execute();
                $stmt->close();
                
                // Commit transaction
                $conn_back->commit();
                
                $message = "Deposit #$deposit_id has been approved successfully.";
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn_back->rollback();
                $error = "Error approving deposit: " . $e->getMessage();
            }
        } else {
            $error = "Invalid deposit or deposit already processed.";
        }
    } elseif (isset($_POST['reject_deposit'])) {
        $deposit_id = $_POST['deposit_id'];
        $rejection_reason = $_POST['rejection_reason'];
        
        // Update deposit status to rejected
        $stmt = $conn_back->prepare("UPDATE deposit_requests SET status = 'rejected', rejection_reason = ?, rejected_at = NOW() WHERE id = ? AND status = 'pending'");
        $stmt->bind_param("si", $rejection_reason, $deposit_id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $message = "Deposit #$deposit_id has been rejected.";
        } else {
            $error = "Error rejecting deposit or deposit already processed.";
        }
        
        $stmt->close();
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Filtering
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$valid_statuses = ['pending', 'approved', 'rejected', ''];

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
$count_sql = "SELECT COUNT(*) as total FROM deposit_requests WHERE $condition";
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

// Get deposits
$sql = "
    SELECT 
        d.*, 
        CONCAT(u.first_name, ' ', u.last_name) AS username, 
        p.name AS payment_method
    FROM deposit_requests d
    JOIN users u ON d.user_id = u.id
    LEFT JOIN payment_methods p ON d.payment_method_id = p.id
    WHERE $condition
    ORDER BY d.created_at DESC
    LIMIT ? OFFSET ?
";
$types .= "ii";
$params[] = $records_per_page;
$params[] = $offset;

$stmt = $conn_back->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$deposits = $stmt->get_result();
$stmt->close();

include_once __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Deposit Management</h1>
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
            <h6 class="m-0 font-weight-bold text-primary">Deposit Requests</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="filterDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-filter fa-sm fa-fw text-gray-400"></i> Filter
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="filterDropdown">
                    <div class="dropdown-header">Status Filter:</div>
                    <a class="dropdown-item <?= $status_filter === '' ? 'active' : '' ?>" href="?status=">All</a>
                    <a class="dropdown-item <?= $status_filter === 'pending' ? 'active' : '' ?>" href="?status=pending">Pending</a>
                    <a class="dropdown-item <?= $status_filter === 'approved' ? 'active' : '' ?>" href="?status=approved">Approved</a>
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
                        <?php if ($deposits->num_rows > 0): ?>
                            <?php while ($deposit = $deposits->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $deposit['id'] ?></td>
                                    <td><?= htmlspecialchars($deposit['username']) ?></td>
                                    <td>$<?= number_format($deposit['amount'], 2) ?></td>
                                    <td><?= htmlspecialchars($deposit['payment_method'] ?? 'N/A') ?></td>
                                    <td><?= date('M d, Y H:i', strtotime($deposit['created_at'])) ?></td>
                                    <td>
                                        <span class="badge badge-<?php
                                            switch ($deposit['status']) {
                                                case 'pending': echo 'warning'; break;
                                                case 'approved': echo 'success'; break;
                                                case 'rejected': echo 'danger'; break;
                                                default: echo 'secondary';
                                            }
                                        ?>">
                                            <?= ucfirst($deposit['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary view-details" data-toggle="modal" data-target="#detailsModal" 
                                                data-id="<?= $deposit['id'] ?>" 
                                                data-user="<?= htmlspecialchars($deposit['username']) ?>"
                                                data-amount="<?= $deposit['amount'] ?>"
                                                data-method="<?= htmlspecialchars($deposit['payment_method'] ?? 'N/A') ?>"
                                                data-reference="<?= htmlspecialchars($deposit['transaction_reference'] ?? 'N/A') ?>"
                                                data-date="<?= date('M d, Y H:i', strtotime($deposit['created_at'])) ?>"
                                                data-status="<?= $deposit['status'] ?>"
                                                data-proof="<?= $deposit['payment_proof'] ?? '' ?>"
                                                data-rejection="<?= htmlspecialchars($deposit['rejection_reason'] ?? '') ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php if ($deposit['status'] === 'pending'): ?>
                                            <button class="btn btn-sm btn-success approve-deposit" data-toggle="modal" data-target="#approveModal" data-id="<?= $deposit['id'] ?>">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger reject-deposit" data-toggle="modal" data-target="#rejectModal" data-id="<?= $deposit['id'] ?>">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No deposit requests found</td>
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
                <h5 class="modal-title" id="detailsModalLabel">Deposit Details</h5>
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
                    </div>
                    <div class="col-md-6">
                        <p><strong>Reference:</strong> <span id="detailReference"></span></p>
                        <p><strong>Date:</strong> <span id="detailDate"></span></p>
                        <p><strong>Status:</strong> <span id="detailStatus"></span></p>
                    </div>
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
                
                <div id="rejectionReasonDiv" class="form-group d-none">
                    <label><strong>Rejection Reason:</strong></label>
                    <div id="detailRejection" class="p-2 bg-light border rounded"></div>
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
                <h5 class="modal-title" id="approveModalLabel">Approve Deposit</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="deposit_id" id="approveDepositId">
                    <p>Are you sure you want to approve this deposit request? This will add the funds to the user's account.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="approve_deposit" class="btn btn-success">Approve</button>
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
                <h5 class="modal-title" id="rejectModalLabel">Reject Deposit</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="deposit_id" id="rejectDepositId">
                    <div class="form-group">
                        <label for="rejection_reason">Rejection Reason</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
                        <small class="form-text text-muted">Please provide a reason for rejecting this deposit request. This will be visible to the user.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="reject_deposit" class="btn btn-danger">Reject</button>
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
        var method = $(this).data('method');
        var reference = $(this).data('reference');
        var date = $(this).data('date');
        var status = $(this).data('status');
        var proof = $(this).data('proof');
        var rejection = $(this).data('rejection');
        
        $('#detailId').text(id);
        $('#detailUser').text(user);
        $('#detailAmount').text('$' + parseFloat(amount).toFixed(2));
        $('#detailMethod').text(method);
        $('#detailReference').text(reference);
        $('#detailDate').text(date);
        $('#detailStatus').text(status.charAt(0).toUpperCase() + status.slice(1));
        
        // Update status badge
        var badgeClass = 'badge-secondary';
        switch (status) {
            case 'pending': badgeClass = 'badge-warning'; break;
            case 'approved': badgeClass = 'badge-success'; break;
            case 'rejected': badgeClass = 'badge-danger'; break;
        }
        $('#statusBadge').attr('class', 'badge ' + badgeClass + ' mb-2').text(status.charAt(0).toUpperCase() + status.slice(1));
        
        // Show/hide payment proof
        if (proof) {
            $('#paymentProofDiv').removeClass('d-none');
            $('#paymentProofImage').attr('src', proof);
            $('#paymentProofLink').attr('href', proof);
        } else {
            $('#paymentProofDiv').addClass('d-none');
        }
        
        // Show/hide rejection reason
        if (status === 'rejected' && rejection) {
            $('#rejectionReasonDiv').removeClass('d-none');
            $('#detailRejection').text(rejection);
        } else {
            $('#rejectionReasonDiv').addClass('d-none');
        }
    });
    
    // Approve deposit
    $('.approve-deposit').on('click', function() {
        var id = $(this).data('id');
        $('#approveDepositId').val(id);
    });
    
    // Reject deposit
    $('.reject-deposit').on('click', function() {
        var id = $(this).data('id');
        $('#rejectDepositId').val(id);
    });
});
</script>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 