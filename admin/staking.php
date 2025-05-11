<?php
// Admin Staking Management Page
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Staking Management";
$current_page = "staking.php";
$message = "";
$error = "";

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cancel staking
    if (isset($_POST['cancel_staking'])) {
        $staking_id = intval($_POST['staking_id']);
        
        // Get staking details
        $stmt = $conn_back->prepare("
            SELECT s.*, p.name as plan_name 
            FROM staking_positions s 
            JOIN staking_plans p ON s.plan_id = p.id 
            WHERE s.id = ? AND s.status = 'active'
        ");
        $stmt->bind_param("i", $staking_id);
        $stmt->execute();
        $staking = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($staking) {
            // Begin transaction
            $conn_back->begin_transaction();
            
            try {
                // Update staking status
                $stmt = $conn_back->prepare("
                    UPDATE staking_positions 
                    SET status = 'cancelled', 
                        unstaked_at = NOW(),
                        updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->bind_param("i", $staking_id);
                $result = $stmt->execute();
                $stmt->close();
                
                if (!$result) throw new Exception("Failed to update staking status");
                
                // Return staked amount to user
                $stmt = $conn_back->prepare("
                    UPDATE users 
                    SET main_balance = main_balance + ? 
                    WHERE id = ?
                ");
                $stmt->bind_param("di", $staking['amount'], $staking['user_id']);
                $result = $stmt->execute();
                $stmt->close();
                
                if (!$result) throw new Exception("Failed to update user balance");
                
                // Create transaction record
                $description = "Staking cancelled and funds returned. Plan: " . $staking['plan_name'];
                $stmt = $conn_back->prepare("
                    INSERT INTO transactions (
                        user_id, amount, transaction_type, status, 
                        description, date_time
                    ) VALUES (?, ?, 'staking_return', 'completed', ?, NOW())
                ");
                $stmt->bind_param("ids", $staking['user_id'], $staking['amount'], $description);
                $result = $stmt->execute();
                $stmt->close();
                
                if (!$result) throw new Exception("Failed to create transaction record");
                
                // Log admin activity
                $admin_id = $_SESSION['admin_id'];
                $action = "Cancelled staking position #$staking_id for user #" . $staking['user_id'] . " and returned $" . number_format($staking['amount'], 2);
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $admin_id, $action, $ip);
                $stmt->execute();
                $stmt->close();
                
                // Commit transaction
                $conn_back->commit();
                
                $message = "Staking position #$staking_id has been cancelled and $" . number_format($staking['amount'], 2) . " has been returned to the user.";
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn_back->rollback();
                $error = "Error cancelling staking position: " . $e->getMessage();
            }
        } else {
            $error = "Staking position not found or already cancelled.";
        }
    }
    
    // Toggle compounding
    if (isset($_POST['toggle_compounding'])) {
        $staking_id = intval($_POST['staking_id']);
        $is_compounding = isset($_POST['is_compounding']) ? 1 : 0;
        
        $stmt = $conn_back->prepare("
            UPDATE staking_positions 
            SET is_compounding = ?,
                updated_at = NOW()
            WHERE id = ? AND status = 'active'
        ");
        $stmt->bind_param("ii", $is_compounding, $staking_id);
        
        if ($stmt->execute()) {
            // Log admin activity
            $admin_id = $_SESSION['admin_id'];
            $status_text = $is_compounding ? 'enabled' : 'disabled';
            $action = "Changed compounding to '$status_text' for staking position #$staking_id";
            $ip = $_SERVER['REMOTE_ADDR'];
            
            $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
            $log_stmt->bind_param("iss", $admin_id, $action, $ip);
            $log_stmt->execute();
            
            $message = "Compounding status updated successfully for staking position #$staking_id.";
        } else {
            $error = "Failed to update compounding status: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

// Check if staking_positions table exists
$table_exists = false;
$result = $conn_back->query("SHOW TABLES LIKE 'staking_positions'");
if ($result && $result->num_rows > 0) {
    $table_exists = true;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 20;
$offset = ($page - 1) * $records_per_page;

// Filtering
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$plan_id = isset($_GET['plan_id']) ? (int)$_GET['plan_id'] : 0;

// Build query condition
$condition = "1=1";
$params = [];
$types = "";

if (!empty($status_filter)) {
    $condition .= " AND s.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if ($user_id > 0) {
    $condition .= " AND s.user_id = ?";
    $params[] = $user_id;
    $types .= "i";
}

if ($plan_id > 0) {
    $condition .= " AND s.plan_id = ?";
    $params[] = $plan_id;
    $types .= "i";
}

if ($table_exists) {
    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM staking_positions s WHERE $condition";
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

    // Get staking positions
    $sql = "
        SELECT 
            s.*,
            CONCAT(u.first_name, ' ', u.last_name) as username,
            u.email,
            p.name as plan_name,
            p.reward_percent,
            p.lockup_period
        FROM staking_positions s
        JOIN users u ON s.user_id = u.id
        JOIN staking_plans p ON s.plan_id = p.id
        WHERE $condition
        ORDER BY s.created_at DESC
        LIMIT ? OFFSET ?
    ";
    $types .= "ii";
    $params[] = $records_per_page;
    $params[] = $offset;

    $stmt = $conn_back->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stakings = $stmt->get_result();
    $stmt->close();

    // Get staking plans for filter
    $plans_result = $conn_back->query("SELECT id, name FROM staking_plans ORDER BY name");
    $plans = [];
    if ($plans_result->num_rows > 0) {
        while ($row = $plans_result->fetch_assoc()) {
            $plans[] = $row;
        }
    }
}

include_once __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Staking Management</h1>
        <a href="create_staking.php" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i> Create Staking Position
        </a>
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

    <?php if (!$table_exists): ?>
        <div class="alert alert-warning">
            <p>The staking_positions table does not exist in the database. Please run the database initialization script.</p>
            <form method="post" action="db_fix.php">
                <input type="hidden" name="create_staking_tables" value="1">
                <button type="submit" class="btn btn-primary">Create Staking Tables</button>
            </form>
        </div>
    <?php else: ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">User Staking Positions</h6>
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
                        
                        <?php if (count($plans) > 0): ?>
                            <div class="dropdown-divider"></div>
                            <div class="dropdown-header">Plans Filter:</div>
                            <a class="dropdown-item <?= $plan_id === 0 ? 'active' : '' ?>" href="?plan_id=0<?= !empty($status_filter) ? '&status='.$status_filter : '' ?>">All Plans</a>
                            <?php foreach ($plans as $plan): ?>
                                <a class="dropdown-item <?= $plan_id === $plan['id'] ? 'active' : '' ?>" href="?plan_id=<?= $plan['id'] ?><?= !empty($status_filter) ? '&status='.$status_filter : '' ?>"><?= htmlspecialchars($plan['name']) ?></a>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
                                <th>Daily Reward</th>
                                <th>Total Rewards</th>
                                <th>Created</th>
                                <th>Compounding</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($stakings && $stakings->num_rows > 0): ?>
                                <?php while ($staking = $stakings->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $staking['id'] ?></td>
                                        <td>
                                            <a href="user_detail.php?id=<?= $staking['user_id'] ?>">
                                                <?= htmlspecialchars($staking['username']) ?>
                                            </a>
                                            <small class="d-block text-muted"><?= htmlspecialchars($staking['email']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($staking['plan_name']) ?></td>
                                        <td>$<?= number_format($staking['amount'], 2) ?></td>
                                        <td>$<?= number_format($staking['daily_reward'], 2) ?> (<?= number_format($staking['reward_percent'], 2) ?>%)</td>
                                        <td>$<?= number_format($staking['total_rewards'], 2) ?></td>
                                        <td><?= date('M d, Y', strtotime($staking['created_at'])) ?></td>
                                        <td>
                                            <?php if ($staking['status'] === 'active'): ?>
                                                <form method="post">
                                                    <input type="hidden" name="staking_id" value="<?= $staking['id'] ?>">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="compounding_<?= $staking['id'] ?>" name="is_compounding" <?= $staking['is_compounding'] ? 'checked' : '' ?> onchange="this.form.submit()">
                                                        <label class="custom-control-label" for="compounding_<?= $staking['id'] ?>"></label>
                                                    </div>
                                                    <input type="hidden" name="toggle_compounding" value="1">
                                                </form>
                                            <?php else: ?>
                                                <?= $staking['is_compounding'] ? 'Yes' : 'No' ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php
                                                switch ($staking['status']) {
                                                    case 'active': echo 'success'; break;
                                                    case 'completed': echo 'info'; break;
                                                    case 'cancelled': echo 'danger'; break;
                                                    default: echo 'secondary';
                                                }
                                            ?>">
                                                <?= ucfirst($staking['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary view-details" data-toggle="modal" data-target="#detailsModal" 
                                                    data-id="<?= $staking['id'] ?>" 
                                                    data-user="<?= htmlspecialchars($staking['username']) ?>"
                                                    data-plan="<?= htmlspecialchars($staking['plan_name']) ?>"
                                                    data-amount="<?= $staking['amount'] ?>"
                                                    data-daily="<?= $staking['daily_reward'] ?>"
                                                    data-rate="<?= $staking['reward_percent'] ?>"
                                                    data-lockup="<?= $staking['lockup_period'] ?>"
                                                    data-created="<?= date('M d, Y', strtotime($staking['created_at'])) ?>"
                                                    data-rewards="<?= $staking['total_rewards'] ?>"
                                                    data-compound="<?= $staking['is_compounding'] ? 'Yes' : 'No' ?>"
                                                    data-last-reward="<?= $staking['last_reward_date'] ? date('M d, Y H:i:s', strtotime($staking['last_reward_date'])) : 'N/A' ?>"
                                                    data-status="<?= $staking['status'] ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            <?php if ($staking['status'] === 'active'): ?>
                                                <button class="btn btn-sm btn-danger cancel-staking" data-toggle="modal" data-target="#cancelModal" data-id="<?= $staking['id'] ?>">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center">No staking positions found</td>
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
                                    <a class="page-link" href="?page=1<?= !empty($status_filter) ? '&status='.$status_filter : '' ?><?= $plan_id > 0 ? '&plan_id='.$plan_id : '' ?>" aria-label="First">
                                        <span aria-hidden="true">&laquo;&laquo;</span>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page - 1 ?><?= !empty($status_filter) ? '&status='.$status_filter : '' ?><?= $plan_id > 0 ? '&plan_id='.$plan_id : '' ?>" aria-label="Previous">
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
                                    <a class="page-link" href="?page=<?= $i ?><?= !empty($status_filter) ? '&status='.$status_filter : '' ?><?= $plan_id > 0 ? '&plan_id='.$plan_id : '' ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page + 1 ?><?= !empty($status_filter) ? '&status='.$status_filter : '' ?><?= $plan_id > 0 ? '&plan_id='.$plan_id : '' ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $total_pages ?><?= !empty($status_filter) ? '&status='.$status_filter : '' ?><?= $plan_id > 0 ? '&plan_id='.$plan_id : '' ?>" aria-label="Last">
                                        <span aria-hidden="true">&raquo;&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Staking Position Details</h5>
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
                        <p><strong>Daily Rate:</strong> <span id="detailRate"></span>%</p>
                        <p><strong>Daily Reward:</strong> $<span id="detailDaily"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Created:</strong> <span id="detailCreated"></span></p>
                        <p><strong>Lockup Period:</strong> <span id="detailLockup"></span> days</p>
                        <p><strong>Total Rewards:</strong> $<span id="detailRewards"></span></p>
                        <p><strong>Last Reward:</strong> <span id="detailLastReward"></span></p>
                        <p><strong>Compounding:</strong> <span id="detailCompound"></span></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" id="cancelBtn" class="btn btn-danger d-none" data-toggle="modal" data-target="#cancelModal">Cancel Staking</button>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">Cancel Staking Position</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="staking_id" id="cancelStakingId">
                    <p>Are you sure you want to cancel this staking position? This will:</p>
                    <ul>
                        <li>Stop accruing rewards immediately</li>
                        <li>Return the full staked amount to the user</li>
                        <li>Mark the staking position as cancelled</li>
                    </ul>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" name="cancel_staking" class="btn btn-danger">Cancel Staking</button>
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
        var daily = $(this).data('daily');
        var rate = $(this).data('rate');
        var lockup = $(this).data('lockup');
        var created = $(this).data('created');
        var rewards = $(this).data('rewards');
        var compound = $(this).data('compound');
        var lastReward = $(this).data('last-reward');
        var status = $(this).data('status');
        
        $('#detailId').text(id);
        $('#detailUser').text(user);
        $('#detailPlan').text(plan);
        $('#detailAmount').text('$' + parseFloat(amount).toFixed(2));
        $('#detailDaily').text(parseFloat(daily).toFixed(2));
        $('#detailRate').text(parseFloat(rate).toFixed(2));
        $('#detailLockup').text(lockup);
        $('#detailCreated').text(created);
        $('#detailRewards').text(parseFloat(rewards).toFixed(2));
        $('#detailCompound').text(compound);
        $('#detailLastReward').text(lastReward);
        $('#detailStatus').text(status.charAt(0).toUpperCase() + status.slice(1));
        
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
    
    // Cancel staking
    $('.cancel-staking').on('click', function() {
        var id = $(this).data('id');
        $('#cancelStakingId').val(id);
    });
    
    $('#cancelBtn').on('click', function() {
        var id = $(this).data('id');
        $('#cancelStakingId').val(id);
        $('#detailsModal').modal('hide');
    });
});
</script>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 