<?php
// User detail page for admin panel
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Debug output function
function debug_to_console($data) {
    $output = $data;
    if (is_array($output)) {
        $output = implode(',', $output);
    }
    echo "<script>console.log('Debug: " . addslashes($output) . "');</script>";
}

session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$user_id = (int)$_GET['id'];

// Include necessary files
try {
    require_once __DIR__ . '/include/config.php';
    
    // Set current page for menu highlighting
    $current_page = 'users.php';
    
    require_once __DIR__ . '/layout/header.php';
    require_once __DIR__ . '/layout/breadcrumb.php';
} catch (Exception $e) {
    echo "Error loading required files: " . $e->getMessage();
    exit;
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle user status change
    if (isset($_POST['action']) && $_POST['action'] == 'change_status') {
        $new_status = $conn_back->real_escape_string($_POST['status']);
        
        if (in_array($new_status, ['active', 'suspended', 'blocked'])) {
            $stmt = $conn_back->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $user_id);
            
            if ($stmt->execute()) {
                // Log the action
                $admin_id = $_SESSION['admin_id'];
                $action = "Changed user ID {$user_id} status to {$new_status}";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                $log_stmt->execute();
                
                $success_message = "User status updated successfully.";
            } else {
                $error_message = "Failed to update user status: " . $conn_back->error;
            }
        } else {
            $error_message = "Invalid status value.";
        }
    }
    
    // Handle balance adjustment
    if (isset($_POST['action']) && $_POST['action'] == 'adjust_balance') {
        $amount = (float)$_POST['amount'];
        $type = $_POST['type'];
        $reason = $conn_back->real_escape_string($_POST['reason']);
        
        if ($amount <= 0) {
            $error_message = "Amount must be greater than zero.";
        } else {
            // Begin transaction
            $conn_back->begin_transaction();
            
            try {
                // Get current balance
                $balance_query = $conn_back->prepare("SELECT main_balance FROM users WHERE id = ?");
                $balance_query->bind_param("i", $user_id);
                $balance_query->execute();
                $balance_result = $balance_query->get_result();
                $current_balance = $balance_result->fetch_assoc()['main_balance'];
                
                // Calculate new balance
                $new_balance = $current_balance;
                if ($type == 'add') {
                    $new_balance += $amount;
                    $transaction_type = 'admin_credit';
                    $description = "Admin credit: $reason";
                } else {
                    if ($current_balance < $amount) {
                        throw new Exception("Insufficient balance for deduction.");
                    }
                    $new_balance -= $amount;
                    $transaction_type = 'admin_debit';
                    $description = "Admin debit: $reason";
                }
                
                // Update user balance
                $balance_update = $conn_back->prepare("UPDATE users SET main_balance = ? WHERE id = ?");
                $balance_update->bind_param("di", $new_balance, $user_id);
                $balance_update->execute();
                
                // Create transaction record
                $transaction = $conn_back->prepare("
                    INSERT INTO transactions (user_id, transaction_type, amount, status, description, date_time) 
                    VALUES (?, ?, ?, 'completed', ?, NOW())
                ");
                $transaction->bind_param("isds", $user_id, $transaction_type, $amount, $description);
                $transaction->execute();
                
                // Log admin action
                $admin_id = $_SESSION['admin_id'];
                $action = "Adjusted user ID {$user_id} balance: {$type} \${$amount} - {$reason}";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                $log_stmt->execute();
                
                // Commit transaction
                $conn_back->commit();
                $success_message = "User balance adjusted successfully.";
            } catch (Exception $e) {
                // Rollback on error
                $conn_back->rollback();
                $error_message = "Error: " . $e->getMessage();
            }
        }
    }
}

// Get user data
try {
    $user_query = $conn_back->prepare("
        SELECT u.*, 
        (SELECT COUNT(*) FROM investments WHERE user_id = u.id) as total_investments,
        (SELECT SUM(amount) FROM transactions WHERE user_id = u.id AND transaction_type = 'deposit' AND status = 'completed') as total_deposits,
        (SELECT SUM(amount) FROM transactions WHERE user_id = u.id AND transaction_type = 'withdrawal' AND status = 'completed') as total_withdrawals,
        (SELECT SUM(roi_received) FROM investments WHERE user_id = u.id) as total_earnings
        FROM users u 
        WHERE u.id = ?
    ");
    $user_query->bind_param("i", $user_id);
    $user_query->execute();
    $user_result = $user_query->get_result();

    if ($user_result->num_rows == 0) {
        header("Location: users.php");
        exit();
    }

    $user = $user_result->fetch_assoc();
    
    // Debug output
    debug_to_console("User data loaded successfully");
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error loading user: ' . $e->getMessage() . '</div>';
    debug_to_console("Error: " . $e->getMessage());
    exit;
}

// Get recent transactions
try {
    $transactions_query = $conn_back->prepare("
        SELECT transaction_type, amount, status, description, date_time
        FROM transactions 
        WHERE user_id = ? 
        ORDER BY date_time DESC 
        LIMIT 10
    ");
    $transactions_query->bind_param("i", $user_id);
    $transactions_query->execute();
    $transactions_result = $transactions_query->get_result();
    $transactions = [];
    while ($tx = $transactions_result->fetch_assoc()) {
        $transactions[] = $tx;
    }
    
    debug_to_console("Transactions loaded successfully: " . count($transactions));
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error loading transactions: ' . $e->getMessage() . '</div>';
    debug_to_console("Error loading transactions: " . $e->getMessage());
    $transactions = [];
}

// Get investments
try {
    $investments_query = $conn_back->prepare("
        SELECT i.*, p.name as plan_name
        FROM investments i
        JOIN investment_plans p ON i.plan_id = p.id
        WHERE i.user_id = ?
        ORDER BY i.created_at DESC
        LIMIT 10
    ");
    $investments_query->bind_param("i", $user_id);
    $investments_query->execute();
    $investments_result = $investments_query->get_result();
    $investments = [];
    while ($inv = $investments_result->fetch_assoc()) {
        $investments[] = $inv;
    }
    
    debug_to_console("Investments loaded successfully: " . count($investments));
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error loading investments: ' . $e->getMessage() . '</div>';
    debug_to_console("Error loading investments: " . $e->getMessage());
    $investments = [];
}
?>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-12">
            <div class="box">
                <div class="box-header with-border d-flex justify-content-between align-items-center">
                    <h4 class="box-title">User Details</h4>
                    <a href="users.php" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to Users
                    </a>
                </div>
                <div class="box-body">
                    <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Personal Information</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th>Full Name</th>
                                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Username</th>
                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Email</th>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Phone</th>
                                                <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Status</th>
                                                <td>
                                                    <span class="badge 
                                                        <?php 
                                                        if ($user['status'] == 'active') echo 'bg-success';
                                                        elseif ($user['status'] == 'pending') echo 'bg-warning';
                                                        else echo 'bg-danger';
                                                        ?>">
                                                        <?php echo ucfirst($user['status']); ?>
                                                    </span>
                                                    
                                                    <div class="btn-group dropdown ml-2">
                                                        <button type="button" class="btn btn-xs btn-info dropdown-toggle" id="statusDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                            Change Status
                                                        </button>
                                                        <ul class="dropdown-menu" aria-labelledby="statusDropdown">
                                                            <?php if ($user['status'] != 'active'): ?>
                                                            <li>
                                                                <form method="post">
                                                                    <input type="hidden" name="action" value="change_status">
                                                                    <input type="hidden" name="status" value="active">
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="fa fa-check text-success"></i> Activate
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <?php endif; ?>
                                                            
                                                            <?php if ($user['status'] != 'suspended'): ?>
                                                            <li>
                                                                <form method="post">
                                                                    <input type="hidden" name="action" value="change_status">
                                                                    <input type="hidden" name="status" value="suspended">
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="fa fa-pause text-warning"></i> Suspend
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <?php endif; ?>
                                                            
                                                            <?php if ($user['status'] != 'blocked'): ?>
                                                            <li>
                                                                <form method="post">
                                                                    <input type="hidden" name="action" value="change_status">
                                                                    <input type="hidden" name="status" value="blocked">
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="fa fa-ban text-danger"></i> Block
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Registered On</th>
                                                <td><?php echo date('M d, Y H:i', strtotime($user['created_at'])); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Last Login</th>
                                                <td><?php echo $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Financial Information</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th>Current Balance</th>
                                                <td>
                                                    <strong>$<?php echo isset($user['main_balance']) ? number_format($user['main_balance'], 2) : '0.00'; ?></strong>
                                                    <button type="button" class="btn btn-xs btn-primary ml-2" data-bs-toggle="modal" data-bs-target="#adjustBalanceModal">
                                                        Adjust
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Total Deposits</th>
                                                <td>$<?php echo isset($user['total_deposits']) ? number_format($user['total_deposits'] ?? 0, 2) : '0.00'; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Total Withdrawals</th>
                                                <td>$<?php echo isset($user['total_withdrawals']) ? number_format($user['total_withdrawals'] ?? 0, 2) : '0.00'; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Total Investments</th>
                                                <td><?php echo isset($user['total_investments']) ? ($user['total_investments'] ?? 0) : '0'; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Total Earnings</th>
                                                <td>$<?php echo isset($user['total_earnings']) ? number_format($user['total_earnings'] ?? 0, 2) : '0.00'; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Referrer</th>
                                                <td>
                                                    <?php 
                                                    if (!empty($user['referred_by'])) {
                                                        echo '<a href="user_detail.php?id='.$user['referred_by'].'">';
                                                        echo 'ID: '.$user['referred_by'].' (View)';
                                                        echo '</a>';
                                                    } else {
                                                        echo 'None';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Referral Code</th>
                                                <td><?php echo htmlspecialchars($user['referral_code'] ?? 'N/A'); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="box">
                <div class="box-header with-border">
                    <h4 class="box-title">Recent Transactions</h4>
                    <div class="box-controls pull-right">
                        <a href="transactions.php?user_id=<?php echo $user_id; ?>" class="btn btn-info btn-xs">View All</a>
                    </div>
                </div>
                <div class="box-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($transactions) > 0): ?>
                                    <?php foreach ($transactions as $tx): ?>
                                    <tr>
                                        <td>
                                            <span class="badge 
                                                <?php
                                                if (in_array($tx['transaction_type'], ['deposit', 'investment_return', 'admin_credit', 'referral_bonus'])) {
                                                    echo 'bg-success';
                                                } elseif (in_array($tx['transaction_type'], ['withdrawal', 'investment', 'admin_debit'])) {
                                                    echo 'bg-info';
                                                } else {
                                                    echo 'bg-secondary';
                                                }
                                                ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $tx['transaction_type'])); ?>
                                            </span>
                                        </td>
                                        <td>$<?php echo number_format($tx['amount'], 2); ?></td>
                                        <td>
                                            <span class="badge 
                                                <?php 
                                                if ($tx['status'] == 'completed') echo 'bg-success';
                                                elseif ($tx['status'] == 'pending') echo 'bg-warning';
                                                else echo 'bg-danger';
                                                ?>">
                                                <?php echo ucfirst($tx['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($tx['date_time'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No transactions found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="box">
                <div class="box-header with-border">
                    <h4 class="box-title">Investments</h4>
                    <div class="box-controls pull-right">
                        <a href="investments.php?user_id=<?php echo $user_id; ?>" class="btn btn-info btn-xs">View All</a>
                    </div>
                </div>
                <div class="box-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Plan</th>
                                    <th>Amount</th>
                                    <th>ROI</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($investments) > 0): ?>
                                    <?php foreach ($investments as $inv): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($inv['plan_name']); ?></td>
                                        <td>$<?php echo number_format($inv['amount'], 2); ?></td>
                                        <td>$<?php echo number_format($inv['roi_received'], 2); ?> / $<?php echo number_format($inv['roi_expected'], 2); ?></td>
                                        <td>
                                            <span class="badge 
                                                <?php 
                                                if ($inv['status'] == 'active') echo 'bg-success';
                                                elseif ($inv['status'] == 'completed') echo 'bg-info';
                                                elseif ($inv['status'] == 'pending') echo 'bg-warning';
                                                else echo 'bg-danger';
                                                ?>">
                                                <?php echo ucfirst($inv['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($inv['created_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No investments found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Adjust Balance Modal -->
<div class="modal fade" id="adjustBalanceModal" tabindex="-1" role="dialog" aria-labelledby="adjustBalanceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adjustBalanceModalLabel">Adjust User Balance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="adjust_balance">
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="amount" name="amount" min="0.01" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Action</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type" id="typeAdd" value="add" checked>
                            <label class="form-check-label" for="typeAdd">
                                Add to Balance
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type" id="typeSubtract" value="subtract">
                            <label class="form-check-label" for="typeSubtract">
                                Subtract from Balance
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                        <small class="form-text text-muted">Please provide a reason for this adjustment. This will be recorded in the system.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Adjust Balance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap 5 dropdowns specifically for this page
    var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    dropdownElementList.forEach(function(dropdownToggleEl) {
        try {
            new bootstrap.Dropdown(dropdownToggleEl);
        } catch (e) {
            console.error('Error initializing dropdown:', e);
        }
    });
    
    // Add click event listeners to ensure dropdowns open on click
    document.querySelectorAll('.dropdown-toggle').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var dropdown = bootstrap.Dropdown.getInstance(this);
            if (dropdown) {
                dropdown.toggle();
            } else {
                var newDropdown = new bootstrap.Dropdown(this);
                newDropdown.toggle();
            }
        });
    });
});
</script>

<?php
// Include footer
require_once __DIR__ . '/layout/footer.php';
?> 