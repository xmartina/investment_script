<?php
// Admin Investments Management Page
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Investments Management";
$message = "";
$error = "";

// Process investment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cancel_investment'])) {
        $investment_id = $_POST['investment_id'];
        
        // Get investment details
        $stmt = $conn_back->prepare("
            SELECT i.*, p.name as plan_name 
            FROM investments i 
            JOIN investment_plans p ON i.plan_id = p.id 
            WHERE i.id = ? AND i.status = 'active'
        ");
        $stmt->bind_param("i", $investment_id);
        $stmt->execute();
        $investment = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($investment) {
            // Begin transaction
            $conn_back->begin_transaction();
            
            try {
                // Update investment status
                $stmt = $conn_back->prepare("UPDATE investments SET status = 'cancelled', updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("i", $investment_id);
                $stmt->execute();
                $stmt->close();
                
                // Return remaining principal to user
                $days_passed = (time() - strtotime($investment['created_at'])) / (60 * 60 * 24);
                $total_days = (strtotime($investment['end_date']) - strtotime($investment['start_date'])) / (60 * 60 * 24);
                $remaining_percentage = max(0, 1 - ($days_passed / $total_days));
                $refund_amount = $investment['amount'] * $remaining_percentage;
                
                if ($refund_amount > 0) {
                    $stmt = $conn_back->prepare("UPDATE users SET main_balance = main_balance + ? WHERE id = ?");
                    $stmt->bind_param("di", $refund_amount, $investment['user_id']);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Record transaction
                    $description = "Refund from cancelled investment #$investment_id";
                    $stmt = $conn_back->prepare("INSERT INTO transactions (user_id, amount, type, status, description, created_at) VALUES (?, ?, 'refund', 'completed', ?, NOW())");
                    $stmt->bind_param("ids", $investment['user_id'], $refund_amount, $description);
                    $stmt->execute();
                    $stmt->close();
                }
                
                // Commit transaction
                $conn_back->commit();
                
                logAdminActivity($_SESSION['admin_id'], 'Cancel Investment', "Cancelled investment #$investment_id, refunded $".number_format($refund_amount, 2));
                $message = "Investment #$investment_id has been cancelled and $".number_format($refund_amount, 2)." has been refunded to the user.";
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn_back->rollback();
                $error = "Error cancelling investment: " . $e->getMessage();
            }
        } else {
            $error = "Investment not found or already cancelled/expired.";
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
$valid_statuses = ['active', 'completed', 'cancelled', ''];

if (!in_array($status_filter, $valid_statuses)) {
    $status_filter = '';
}

// Build query condition
$condition = "1=1";
$params = [];
$types = "";

if (!empty($status_filter)) {
    $condition .= " AND i.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM investments i WHERE $condition";
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

// Get investments
$sql = "
    SELECT 
        i.*,
        CONCAT(u.first_name, ' ', u.last_name) as username,
        p.name as plan_name,
        p.interest_rate,
        p.duration
    FROM investments i
    JOIN users u ON i.user_id = u.id
    JOIN investment_plans p ON i.plan_id = p.id
    WHERE $condition
    ORDER BY i.created_at DESC
    LIMIT ? OFFSET ?
";
$types .= "ii";
$params[] = $records_per_page;
$params[] = $offset;

$stmt = $conn_back->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$investments = $stmt->get_result();
$stmt->close();

include_once __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Investments Management</h1>
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
            <h6 class="m-0 font-weight-bold text-primary">User Investments</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="filterDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-filter fa-sm fa-fw text-gray-400"></i> Filter
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="filterDropdown">
                    <div class="dropdown-header">Status Filter:</div>
                    <a class="dropdown-item <?= $status_filter === '' ? 'active' : '' ?>" href="?status=">All</a>
                    <a class="dropdown-item <?= $status_filter === 'active' ? 'active' : '' ?>" href="?status=active">Active</a>
                    <a class="dropdown-item <?= $status_filter === 'completed' ? 'active' : '' ?>" href="?status=completed">Completed</a>
                    <a class="dropdown-item <?= $status_filter === 'cancelled' ? 'active' : '' ?>" href="?status=cancelled">Cancelled</a>
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
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Interest Rate</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($investments->num_rows > 0): ?>
                            <?php while ($investment = $investments->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $investment['id'] ?></td>
                                    <td><?= htmlspecialchars($investment['username']) ?></td>
                                    <td><?= htmlspecialchars($investment['plan_name']) ?></td>
                                    <td>$<?= number_format($investment['amount'], 2) ?></td>
                                    <td><?= $investment['interest_rate'] ?>%</td>
                                    <td><?= date('M d, Y', strtotime($investment['start_date'])) ?></td>
                                    <td><?= date('M d, Y', strtotime($investment['end_date'])) ?></td>
                                    <td>
                                        <span class="badge badge-<?php
                                            switch ($investment['status']) {
                                                case 'active': echo 'success'; break;
                                                case 'completed': echo 'info'; break;
                                                case 'cancelled': echo 'danger'; break;
                                                default: echo 'secondary';
                                            }
                                        ?>">
                                            <?= ucfirst($investment['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary view-details" data-toggle="modal" data-target="#detailsModal" 
                                                data-id="<?= $investment['id'] ?>" 
                                                data-user="<?= htmlspecialchars($investment['username']) ?>"
                                                data-plan="<?= htmlspecialchars($investment['plan_name']) ?>"
                                                data-amount="<?= $investment['amount'] ?>"
                                                data-rate="<?= $investment['interest_rate'] ?>"
                                                data-start="<?= date('M d, Y', strtotime($investment['start_date'])) ?>"
                                                data-end="<?= date('M d, Y', strtotime($investment['end_date'])) ?>"
                                                data-status="<?= $investment['status'] ?>"
                                                data-returns="<?= $investment['expected_returns'] ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php if ($investment['status'] === 'active'): ?>
                                            <button class="btn btn-sm btn-danger cancel-investment" data-toggle="modal" data-target="#cancelModal" data-id="<?= $investment['id'] ?>">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No investments found</td>
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
                <h5 class="modal-title" id="detailsModalLabel">Investment Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div id="statusBadge" class="badge badge-success mb-2">Active</div>
                    <h4 id="detailAmount">$0.00</h4>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>ID:</strong> <span id="detailId"></span></p>
                        <p><strong>User:</strong> <span id="detailUser"></span></p>
                        <p><strong>Plan:</strong> <span id="detailPlan"></span></p>
                        <p><strong>Interest Rate:</strong> <span id="detailRate"></span>%</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Start Date:</strong> <span id="detailStart"></span></p>
                        <p><strong>End Date:</strong> <span id="detailEnd"></span></p>
                        <p><strong>Status:</strong> <span id="detailStatus"></span></p>
                        <p><strong>Expected Returns:</strong> $<span id="detailReturns"></span></p>
                    </div>
                </div>
                
                <div id="progressContainer" class="form-group">
                    <label><strong>Progress:</strong></label>
                    <div class="progress">
                        <div id="progressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" id="cancelBtn" class="btn btn-danger d-none" data-toggle="modal" data-target="#cancelModal">Cancel Investment</button>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">Cancel Investment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="investment_id" id="cancelInvestmentId">
                    <p>Are you sure you want to cancel this investment? This will:</p>
                    <ul>
                        <li>Stop accruing interest immediately</li>
                        <li>Refund a prorated portion of the principal to the user</li>
                        <li>Mark the investment as cancelled</li>
                    </ul>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" name="cancel_investment" class="btn btn-danger">Cancel Investment</button>
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
        var plan = $(this).data('plan');
        var amount = $(this).data('amount');
        var rate = $(this).data('rate');
        var start = $(this).data('start');
        var end = $(this).data('end');
        var status = $(this).data('status');
        var returns = $(this).data('returns');
        
        $('#detailId').text(id);
        $('#detailUser').text(user);
        $('#detailPlan').text(plan);
        $('#detailAmount').text('$' + parseFloat(amount).toFixed(2));
        $('#detailRate').text(rate);
        $('#detailStart').text(start);
        $('#detailEnd').text(end);
        $('#detailStatus').text(status.charAt(0).toUpperCase() + status.slice(1));
        $('#detailReturns').text(parseFloat(returns).toFixed(2));
        
        // Calculate progress
        var startDate = new Date(start);
        var endDate = new Date(end);
        var now = new Date();
        
        var totalDuration = endDate - startDate;
        var elapsed = now - startDate;
        var percentComplete = Math.min(100, Math.max(0, Math.round((elapsed / totalDuration) * 100)));
        
        $('#progressBar').css('width', percentComplete + '%').attr('aria-valuenow', percentComplete).text(percentComplete + '%');
        
        // Show/hide cancel button
        if (status === 'active') {
            $('#cancelBtn').removeClass('d-none').data('id', id);
        } else {
            $('#cancelBtn').addClass('d-none');
        }
        
        // Update status badge
        var badgeClass = 'badge-secondary';
        switch (status) {
            case 'active': badgeClass = 'badge-success'; break;
            case 'completed': badgeClass = 'badge-info'; break;
            case 'cancelled': badgeClass = 'badge-danger'; break;
        }
        $('#statusBadge').attr('class', 'badge ' + badgeClass + ' mb-2').text(status.charAt(0).toUpperCase() + status.slice(1));
    });
    
    // Cancel investment
    $('.cancel-investment').on('click', function() {
        var id = $(this).data('id');
        $('#cancelInvestmentId').val(id);
    });
    
    $('#cancelBtn').on('click', function() {
        var id = $(this).data('id');
        $('#cancelInvestmentId').val(id);
        $('#detailsModal').modal('hide');
    });
});
</script>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 