<?php
// Admin Deposits Management Page
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Deposits";
$current_page = "deposits.php";
$message = "";
$error = "";

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Approve deposit
    if (isset($_POST['approve_deposit'])) {
        $deposit_id = (int)$_POST['deposit_id'];
        
        // Get deposit details
        $stmt = $conn_back->prepare("SELECT * FROM deposit_requests WHERE id = ? AND status = 'pending'");
        $stmt->bind_param("i", $deposit_id);
        $stmt->execute();
        $deposit = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($deposit) {
            // Begin transaction
            $conn_back->begin_transaction();
            
            try {
                // Update deposit status
                $stmt = $conn_back->prepare("UPDATE deposit_requests SET status = 'approved' WHERE id = ?");
                $stmt->bind_param("i", $deposit_id);
                $stmt->execute();
                $stmt->close();
                
                // Add to user's balance
                $stmt = $conn_back->prepare("UPDATE users SET main_balance = main_balance + ? WHERE id = ?");
                $stmt->bind_param("di", $deposit['amount'], $deposit['user_id']);
                $stmt->execute();
                $stmt->close();
                
                // Create transaction record if not exists
                $stmt = $conn_back->prepare("SELECT transaction_id FROM transactions WHERE deposit_request_id = ?");
                $stmt->bind_param("i", $deposit_id);
                $stmt->execute();
                $transaction_result = $stmt->get_result();
                $stmt->close();
                
                if ($transaction_result->num_rows == 0) {
                    // Insert new transaction
                    $transaction_reference = 'DEP' . time() . rand(1000, 9999);
                    $transaction_proof = 'DEPPROOF' . time();
                    $description = 'Deposit via ' . $deposit['payment_method'];
                    
                    $stmt = $conn_back->prepare("
                        INSERT INTO transactions (
                            user_id, amount, transaction_type, reference_id, 
                            transaction_proof_id, currency, status, 
                            date_time, description, deposit_request_id
                        ) VALUES (?, ?, 'deposit', ?, ?, ?, 'completed', NOW(), ?, ?)
                    ");
                    $stmt->bind_param("idsssssi", 
                        $deposit['user_id'], 
                        $deposit['amount'], 
                        $transaction_reference, 
                        $transaction_proof, 
                        $deposit['currency'], 
                        $description,
                        $deposit_id
                    );
                    $stmt->execute();
                    $stmt->close();
                } else {
                    // Update existing transaction
                    $stmt = $conn_back->prepare("UPDATE transactions SET status = 'completed' WHERE deposit_request_id = ?");
                    $stmt->bind_param("i", $deposit_id);
                    $stmt->execute();
                    $stmt->close();
                }
                
                // Check for referral bonus
                $stmt = $conn_back->prepare("SELECT referred_by FROM users WHERE id = ? AND referred_by IS NOT NULL");
                $stmt->bind_param("i", $deposit['user_id']);
                $stmt->execute();
                $referral_result = $stmt->get_result();
                $stmt->close();
                
                if ($referral_result->num_rows > 0) {
                    $referrer = $referral_result->fetch_assoc();
                    $referrer_id = $referrer['referred_by'];
                    
                    // Calculate referral bonus (example: 5% of deposit)
                    $bonus_percentage = 0.05; // 5%
                    $bonus_amount = $deposit['amount'] * $bonus_percentage;
                    
                    // Add referral bonus to referrer's balance
                    $stmt = $conn_back->prepare("UPDATE users SET main_balance = main_balance + ?, referral_bonus_earned = referral_bonus_earned + ? WHERE id = ?");
                    $stmt->bind_param("ddi", $bonus_amount, $bonus_amount, $referrer_id);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Create referral commission record
                    $stmt = $conn_back->prepare("
                        INSERT INTO referral_commissions (
                            referrer_id, referred_id, amount, source_type, 
                            source_id, status, created_at, paid_at
                        ) VALUES (?, ?, ?, 'deposit', ?, 'paid', NOW(), NOW())
                    ");
                    $stmt->bind_param("iidi", $referrer_id, $deposit['user_id'], $bonus_amount, $deposit_id);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Create transaction for referral bonus
                    $ref_transaction_reference = 'REF' . time() . rand(1000, 9999);
                    $ref_transaction_proof = 'REFPROOF' . time();
                    $ref_description = 'Referral bonus for deposit #' . $deposit_id;
                    
                    $stmt = $conn_back->prepare("
                        INSERT INTO transactions (
                            user_id, amount, transaction_type, reference_id, 
                            transaction_proof_id, currency, status, 
                            date_time, description
                        ) VALUES (?, ?, 'referral_bonus', ?, ?, ?, 'completed', NOW(), ?)
                    ");
                    $stmt->bind_param("idssss", 
                        $referrer_id, 
                        $bonus_amount, 
                        $ref_transaction_reference, 
                        $ref_transaction_proof, 
                        $deposit['currency'], 
                        $ref_description
                    );
                    $stmt->execute();
                    $stmt->close();
                }
                
                // Log admin action
                $admin_id = $_SESSION['admin_id'];
                $action = "Approved deposit #$deposit_id of {$deposit['amount']} {$deposit['currency']} for user #{$deposit['user_id']}";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $admin_id, $action, $ip);
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
            $error = "Deposit not found or already processed.";
        }
    }
    
    // Reject deposit
    if (isset($_POST['reject_deposit'])) {
        $deposit_id = (int)$_POST['deposit_id'];
        $rejection_reason = $_POST['rejection_reason'];
        
        // Get deposit details
        $stmt = $conn_back->prepare("SELECT * FROM deposit_requests WHERE id = ? AND status = 'pending'");
        $stmt->bind_param("i", $deposit_id);
        $stmt->execute();
        $deposit = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($deposit) {
            // Update deposit status
            $stmt = $conn_back->prepare("UPDATE deposit_requests SET status = 'rejected' WHERE id = ?");
            $stmt->bind_param("i", $deposit_id);
            
            if ($stmt->execute()) {
                // Log admin action
                $admin_id = $_SESSION['admin_id'];
                $action = "Rejected deposit #$deposit_id of {$deposit['amount']} {$deposit['currency']} for user #{$deposit['user_id']}. Reason: $rejection_reason";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                $log_stmt->execute();
                
                $message = "Deposit #$deposit_id has been rejected successfully.";
            } else {
                $error = "Error rejecting deposit: " . $stmt->error;
            }
            
            $stmt->close();
        } else {
            $error = "Deposit not found or already processed.";
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
    $condition = " WHERE d.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM deposit_requests d" . $condition;
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

// Get deposits
$params_paged = $params;
$params_paged[] = $records_per_page;
$params_paged[] = $offset;
$types .= "ii";

$sql = "
    SELECT 
        d.*,
        CONCAT(u.first_name, ' ', u.last_name) as username,
        u.email
    FROM deposit_requests d
    JOIN users u ON d.user_id = u.id
    $condition
    ORDER BY d.created_at DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn_back->prepare($sql);
$stmt->bind_param($types, ...$params_paged);
$stmt->execute();
$deposits = $stmt->get_result();
$stmt->close();

include_once __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Deposits Management</h1>
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
                <a class="dropdown-toggle" href="#" role="button" id="statusDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-filter fa-sm fa-fw text-gray-400"></i> Filter by Status
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="statusDropdown">
                    <a class="dropdown-item <?= $status_filter === '' ? 'active' : '' ?>" href="?status=">All</a>
                    <a class="dropdown-item <?= $status_filter === 'pending' ? 'active' : '' ?>" href="?status=pending">Pending</a>
                    <a class="dropdown-item <?= $status_filter === 'approved' ? 'active' : '' ?>" href="?status=approved">Approved</a>
                    <a class="dropdown-item <?= $status_filter === 'rejected' ? 'active' : '' ?>" href="?status=rejected">Rejected</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="depositsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Proof</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($deposits->num_rows > 0): ?>
                            <?php while ($deposit = $deposits->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $deposit['id'] ?></td>
                                    <td>
                                        <a href="user_detail.php?id=<?= $deposit['user_id'] ?>">
                                            <?= htmlspecialchars($deposit['username']) ?>
                                        </a>
                                        <small class="d-block text-muted"><?= htmlspecialchars($deposit['email']) ?></small>
                                    </td>
                                    <td><?= number_format($deposit['amount'], 2) ?> <?= htmlspecialchars($deposit['currency']) ?></td>
                                    <td><?= htmlspecialchars($deposit['payment_method']) ?></td>
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
                                        <?php if (!empty($deposit['payment_proof'])): ?>
                                            <a href="<?= $deposit['payment_proof'] ?>" target="_blank" class="btn btn-sm btn-info">
                                                <i class="fas fa-image"></i> View
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No proof</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($deposit['status'] === 'pending'): ?>
                                            <button class="btn btn-sm btn-success approve-btn" data-toggle="modal" data-target="#approveModal" data-id="<?= $deposit['id'] ?>" data-amount="<?= $deposit['amount'] ?>" data-user="<?= htmlspecialchars($deposit['username']) ?>">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button class="btn btn-sm btn-danger reject-btn" data-toggle="modal" data-target="#rejectModal" data-id="<?= $deposit['id'] ?>" data-amount="<?= $deposit['amount'] ?>" data-user="<?= htmlspecialchars($deposit['username']) ?>">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">Processed</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No deposit requests found</td>
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
                    <input type="hidden" name="deposit_id" id="approve_deposit_id">
                    <p>Are you sure you want to approve this deposit?</p>
                    <p><strong>User:</strong> <span id="approve_user"></span></p>
                    <p><strong>Amount:</strong> <span id="approve_amount"></span></p>
                    <p class="text-info">This will add the funds to the user's balance and mark the deposit as approved.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="approve_deposit" class="btn btn-success">Approve Deposit</button>
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
                    <input type="hidden" name="deposit_id" id="reject_deposit_id">
                    <p>Are you sure you want to reject this deposit?</p>
                    <p><strong>User:</strong> <span id="reject_user"></span></p>
                    <p><strong>Amount:</strong> <span id="reject_amount"></span></p>
                    
                    <div class="form-group">
                        <label for="rejection_reason">Rejection Reason:</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
                    </div>
                    
                    <p class="text-danger">This will mark the deposit as rejected, and the user will not receive the funds.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="reject_deposit" class="btn btn-danger">Reject Deposit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Approve modal
    $('.approve-btn').click(function() {
        var id = $(this).data('id');
        var amount = $(this).data('amount');
        var user = $(this).data('user');
        
        $('#approve_deposit_id').val(id);
        $('#approve_user').text(user);
        $('#approve_amount').text(amount);
    });
    
    // Reject modal
    $('.reject-btn').click(function() {
        var id = $(this).data('id');
        var amount = $(this).data('amount');
        var user = $(this).data('user');
        
        $('#reject_deposit_id').val(id);
        $('#reject_user').text(user);
        $('#reject_amount').text(amount);
    });
});
</script>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 