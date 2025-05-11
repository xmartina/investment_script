<?php
// Simple admin dashboard without complex error handling
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Include necessary files - using try/catch for minimal error handling
try {
    require_once __DIR__ . '/include/config.php';
    require_once __DIR__ . '/layout/header.php';
    require_once __DIR__ . '/layout/breadcrumb.php';
} catch (Exception $e) {
    echo "Error loading required files: " . $e->getMessage();
    exit;
}

// Initialize variables with default values
$total_users = 0;
$total_investments = 0;
$total_deposits = 0;
$pending_withdrawals = 0;
$total_profit = 0;
$recent_users = [];
$recent_transactions = [];

// Basic database queries without complex error handling
if (isset($conn_back) && $conn_back) {
    // Count users
    $result = $conn_back->query("SELECT COUNT(*) as total FROM users");
    if ($result && $row = $result->fetch_assoc()) {
        $total_users = $row['total'];
    }

    // Count investments
    $result = $conn_back->query("SELECT COUNT(*) as total FROM investments");
    if ($result && $row = $result->fetch_assoc()) {
        $total_investments = $row['total'];
    }

    // Sum deposits
    // Using deposit_requests table since deposits table doesn't have much data
    $result = $conn_back->query("SELECT SUM(amount) as total FROM deposit_requests");
    if ($result && $row = $result->fetch_assoc()) {
        $total_deposits = $row['total'] ?: 0;
    }

    // Count pending withdrawals
    $result = $conn_back->query("SELECT COUNT(*) as total FROM withdrawal WHERE status = 'pending'");
    if ($result && $row = $result->fetch_assoc()) {
        $pending_withdrawals = $row['total'];
    }

    // Sum profit from investments
    $result = $conn_back->query("SELECT SUM(roi_expected) as total FROM investments");
    if ($result && $row = $result->fetch_assoc()) {
        $total_profit = $row['total'] ?: 0;
    }

    // Get recent users
    $result = $conn_back->query("SELECT id, CONCAT(first_name, ' ', last_name) as username, email, created_at FROM users ORDER BY created_at DESC LIMIT 5");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $recent_users[] = $row;
        }
    }

    // Get recent transactions
    try {
        $result = $conn_back->query("
            SELECT 
                transaction_type as type, 
                amount, 
                status, 
                date_time as created_at, 
                (SELECT CONCAT(first_name, ' ', last_name) FROM users WHERE users.id = transactions.user_id) as username 
            FROM transactions 
            ORDER BY date_time DESC 
            LIMIT 5
        ");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Ensure all required fields have values
                $row['type'] = $row['type'] ?: 'unknown';
                $row['amount'] = $row['amount'] ?: 0;
                $row['status'] = $row['status'] ?: 'unknown';
                $row['created_at'] = $row['created_at'] ?: date('Y-m-d H:i:s');
                $row['username'] = $row['username'] ?: 'Unknown User';
                
                $recent_transactions[] = $row;
            }
        }
    } catch (Exception $e) {
        // Log the error but don't break the page
        error_log("Error fetching transactions: " . $e->getMessage());
    }
}
?>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-xl-3 col-md-6 col-12">
            <div class="box">
                <div class="box-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-fade"><?php echo $total_users; ?></h4>
                            <p class="mb-0">Total Users</p>
                        </div>
                        <div>
                            <i class="fa fa-users text-primary fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="box">
                <div class="box-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-fade"><?php echo $total_investments; ?></h4>
                            <p class="mb-0">Total Investments</p>
                        </div>
                        <div>
                            <i class="fa fa-line-chart text-success fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="box">
                <div class="box-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-fade">$<?php echo number_format($total_deposits, 2); ?></h4>
                            <p class="mb-0">Total Deposits</p>
                        </div>
                        <div>
                            <i class="fa fa-money text-info fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="box">
                <div class="box-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-fade"><?php echo $pending_withdrawals; ?></h4>
                            <p class="mb-0">Pending Withdrawals</p>
                        </div>
                        <div>
                            <i class="fa fa-credit-card text-warning fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xl-8 col-12">
            <div class="box">
                <div class="box-header with-border">
                    <h4 class="box-title">Recent Transactions</h4>
                </div>
                <div class="box-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>User</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($recent_transactions) > 0): ?>
                                    <?php foreach ($recent_transactions as $tx): ?>
                                    <tr>
                                        <td>
                                            <span class="badge <?php echo $tx['type'] == 'deposit' || $tx['type'] == 'investment_return' ? 'badge-success' : 'badge-info'; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $tx['type'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($tx['username']); ?></td>
                                        <td>$<?php echo number_format($tx['amount'], 2); ?></td>
                                        <td>
                                            <span class="badge 
                                                <?php 
                                                if ($tx['status'] == 'completed' || $tx['status'] == 'active') echo 'badge-success';
                                                elseif ($tx['status'] == 'pending') echo 'badge-warning';
                                                else echo 'badge-danger';
                                                ?>">
                                                <?php echo ucfirst($tx['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($tx['created_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No transactions found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="box-footer text-center">
                    <a href="transactions" class="text-uppercase d-none d-md-inline-block">View All Transactions</a>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-12">
            <div class="box">
                <div class="box-header with-border">
                    <h4 class="box-title">Recent Users</h4>
                </div>
                <div class="box-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($recent_users) > 0): ?>
                                    <?php foreach ($recent_users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">No users found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="box-footer text-center">
                    <a href="users" class="text-uppercase d-none d-md-inline-block">View All Users</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Database Maintenance</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Fix Database Issues</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-database fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <a href="db_fix.php" class="btn btn-warning btn-sm mt-3">Run Database Fix</a>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->

<?php
// Include footer
require_once __DIR__ . '/layout/footer.php';
?>
