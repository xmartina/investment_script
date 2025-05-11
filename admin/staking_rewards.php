<?php
// Admin Staking Rewards Management Page
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Staking Rewards";
$current_page = "staking_rewards.php";
$message = "";
$error = "";

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process manual reward
    if (isset($_POST['process_reward'])) {
        $staking_id = intval($_POST['staking_id']);
        $reward_amount = floatval($_POST['reward_amount']);
        $user_id = intval($_POST['user_id']);
        
        if ($staking_id <= 0 || $reward_amount <= 0 || $user_id <= 0) {
            $error = "Invalid input parameters.";
        } else {
            // Begin transaction
            $conn_back->begin_transaction();
            
            try {
                // Insert reward record
                $stmt = $conn_back->prepare("
                    INSERT INTO staking_rewards (
                        staking_id, user_id, amount, created_at
                    ) VALUES (?, ?, ?, NOW())
                ");
                $stmt->bind_param("iid", $staking_id, $user_id, $reward_amount);
                $stmt->execute();
                $reward_id = $conn_back->insert_id;
                $stmt->close();
                
                // Update staking position total rewards
                $stmt = $conn_back->prepare("
                    UPDATE staking_positions 
                    SET total_rewards = total_rewards + ?,
                        last_reward_date = NOW(),
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->bind_param("di", $reward_amount, $staking_id);
                $stmt->execute();
                $stmt->close();
                
                // Create transaction record
                $description = "Staking reward #$reward_id for staking position #$staking_id";
                $stmt = $conn_back->prepare("
                    INSERT INTO transactions (
                        user_id, amount, transaction_type, status, 
                        description, date_time
                    ) VALUES (?, ?, 'staking_reward', 'completed', ?, NOW())
                ");
                $stmt->bind_param("ids", $user_id, $reward_amount, $description);
                $stmt->execute();
                $stmt->close();
                
                // Add to user balance
                $stmt = $conn_back->prepare("
                    UPDATE users
                    SET main_balance = main_balance + ?
                    WHERE id = ?
                ");
                $stmt->bind_param("di", $reward_amount, $user_id);
                $stmt->execute();
                $stmt->close();
                
                // Log admin activity
                $admin_id = $_SESSION['admin_id'];
                $action = "Manually processed staking reward of $" . number_format($reward_amount, 2) . " for staking position #$staking_id";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $admin_id, $action, $ip);
                $stmt->execute();
                $stmt->close();
                
                // Commit transaction
                $conn_back->commit();
                
                $message = "Staking reward of $" . number_format($reward_amount, 2) . " has been processed successfully.";
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn_back->rollback();
                $error = "Error processing reward: " . $e->getMessage();
            }
        }
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
$staking_id = isset($_GET['staking_id']) ? (int)$_GET['staking_id'] : 0;
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// Build query condition
$condition = "1=1";
$params = [];
$types = "";

if ($staking_id > 0) {
    $condition .= " AND r.staking_id = ?";
    $params[] = $staking_id;
    $types .= "i";
}

if ($user_id > 0) {
    $condition .= " AND r.user_id = ?";
    $params[] = $user_id;
    $types .= "i";
}

if ($table_exists) {
    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM staking_rewards r WHERE $condition";
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

    // Get rewards
    $sql = "
        SELECT 
            r.*,
            CONCAT(u.first_name, ' ', u.last_name) as username,
            u.email,
            p.name as plan_name,
            s.amount as staked_amount,
            s.status as staking_status
        FROM staking_rewards r
        JOIN users u ON r.user_id = u.id
        JOIN staking_positions s ON r.staking_id = s.id
        JOIN staking_plans p ON s.plan_id = p.id
        WHERE $condition
        ORDER BY r.created_at DESC
        LIMIT ? OFFSET ?
    ";
    $types .= "ii";
    $params[] = $records_per_page;
    $params[] = $offset;

    $stmt = $conn_back->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rewards = $stmt->get_result();
    $stmt->close();

    // Get active staking positions for reward processing
    $active_stakings_sql = "
        SELECT 
            s.*,
            CONCAT(u.first_name, ' ', u.last_name) as username,
            u.email,
            p.name as plan_name,
            p.roi_daily
        FROM staking_positions s
        JOIN users u ON s.user_id = u.id
        JOIN staking_plans p ON s.plan_id = p.id
        WHERE s.status = 'active'
        ORDER BY s.created_at DESC
        LIMIT 100
    ";
    $active_stakings = $conn_back->query($active_stakings_sql);
}

include_once __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Staking Rewards Management</h1>
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
        <div class="row">
            <!-- Active Staking Positions Card -->
            <div class="col-xl-4 col-md-12 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Process Manual Reward</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($active_stakings && $active_stakings->num_rows > 0): ?>
                            <form method="post">
                                <div class="form-group">
                                    <label for="staking_position">Select Staking Position</label>
                                    <select class="form-control" id="staking_position" name="staking_id" required>
                                        <option value="">Select Staking Position</option>
                                        <?php while ($staking = $active_stakings->fetch_assoc()): ?>
                                            <option value="<?= $staking['id'] ?>" 
                                                    data-user-id="<?= $staking['user_id'] ?>"
                                                    data-user="<?= htmlspecialchars($staking['username']) ?>"
                                                    data-amount="<?= $staking['amount'] ?>"
                                                    data-daily="<?= $staking['daily_reward'] ?>"
                                                    data-roi="<?= $staking['roi_daily'] ?>">
                                                #<?= $staking['id'] ?> - <?= htmlspecialchars($staking['username']) ?> (<?= htmlspecialchars($staking['plan_name']) ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="staking_details">Staking Details</label>
                                    <div id="staking_details" class="bg-light p-2 rounded">
                                        <p class="mb-0">Select a staking position to view details</p>
                                    </div>
                                </div>
                                
                                <input type="hidden" id="user_id" name="user_id">
                                
                                <div class="form-group">
                                    <label for="reward_amount">Reward Amount ($)</label>
                                    <input type="number" class="form-control" id="reward_amount" name="reward_amount" step="0.01" min="0.01" required>
                                    <small id="reward_suggestion" class="text-muted"></small>
                                </div>
                                
                                <button type="submit" name="process_reward" class="btn btn-primary">Process Reward</button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info">
                                No active staking positions found.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Rewards History -->
            <div class="col-xl-8 col-md-12 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Rewards History</h6>
                        <?php if ($staking_id > 0 || $user_id > 0): ?>
                            <a href="staking_rewards.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-times"></i> Clear Filters
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Staking ID</th>
                                        <th>Amount</th>
                                        <th>Compounded</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($rewards && $rewards->num_rows > 0): ?>
                                        <?php while ($reward = $rewards->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= $reward['id'] ?></td>
                                                <td>
                                                    <a href="user_detail.php?id=<?= $reward['user_id'] ?>">
                                                        <?= htmlspecialchars($reward['username']) ?>
                                                    </a>
                                                    <small class="d-block text-muted"><?= htmlspecialchars($reward['email']) ?></small>
                                                </td>
                                                <td>
                                                    <a href="?staking_id=<?= $reward['staking_id'] ?>">
                                                        #<?= $reward['staking_id'] ?>
                                                    </a>
                                                    <small class="d-block text-muted"><?= htmlspecialchars($reward['plan_name']) ?></small>
                                                </td>
                                                <td>$<?= number_format($reward['amount'], 2) ?></td>
                                                <td>
                                                    <?php if (isset($reward['is_compounded']) && $reward['is_compounded']): ?>
                                                        <span class="badge badge-info">Yes</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">No</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= date('M d, Y H:i', strtotime($reward['created_at'])) ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No rewards found</td>
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
                                            <a class="page-link" href="?page=1<?= $staking_id > 0 ? '&staking_id='.$staking_id : '' ?><?= $user_id > 0 ? '&user_id='.$user_id : '' ?>" aria-label="First">
                                                <span aria-hidden="true">&laquo;&laquo;</span>
                                            </a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page - 1 ?><?= $staking_id > 0 ? '&staking_id='.$staking_id : '' ?><?= $user_id > 0 ? '&user_id='.$user_id : '' ?>" aria-label="Previous">
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
                                            <a class="page-link" href="?page=<?= $i ?><?= $staking_id > 0 ? '&staking_id='.$staking_id : '' ?><?= $user_id > 0 ? '&user_id='.$user_id : '' ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page + 1 ?><?= $staking_id > 0 ? '&staking_id='.$staking_id : '' ?><?= $user_id > 0 ? '&user_id='.$user_id : '' ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $total_pages ?><?= $staking_id > 0 ? '&staking_id='.$staking_id : '' ?><?= $user_id > 0 ? '&user_id='.$user_id : '' ?>" aria-label="Last">
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
        </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Populate staking details on selection
    $('#staking_position').on('change', function() {
        var selected = $(this).find('option:selected');
        
        if (selected.val()) {
            var userId = selected.data('user-id');
            var user = selected.data('user');
            var amount = selected.data('amount');
            var daily = selected.data('daily');
            var roi = selected.data('roi');
            
            $('#user_id').val(userId);
            
            var detailsHtml = `
                <p><strong>User:</strong> ${user}</p>
                <p><strong>Staked Amount:</strong> $${parseFloat(amount).toFixed(2)}</p>
                <p><strong>Daily Reward:</strong> $${parseFloat(daily).toFixed(2)} (${parseFloat(roi).toFixed(2)}%)</p>
            `;
            
            $('#staking_details').html(detailsHtml);
            $('#reward_amount').val(parseFloat(daily).toFixed(2));
            $('#reward_suggestion').text(`Suggested daily reward: $${parseFloat(daily).toFixed(2)}`);
        } else {
            $('#staking_details').html('<p class="mb-0">Select a staking position to view details</p>');
            $('#user_id').val('');
            $('#reward_amount').val('');
            $('#reward_suggestion').text('');
        }
    });
});
</script>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 