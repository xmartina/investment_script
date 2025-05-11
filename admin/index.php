<?php
// Enable full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering to capture errors
ob_start();

try {
    session_start();
    
    // Check if the admin is logged in
    if (!isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit();
    }
    
    // Debug info
    $debug_info = [];
    $debug_info[] = "Session admin_id: " . (isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 'Not set');
    
    try {
        include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/include/config.php';
        $debug_info[] = "Admin config file included successfully";
    } catch (Exception $e) {
        $debug_info[] = "Error including admin config: " . $e->getMessage();
    }
    
    try {
        include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layout/header.php';
        $debug_info[] = "Header included successfully";
    } catch (Exception $e) {
        $debug_info[] = "Error including header: " . $e->getMessage();
    }
    
    try {
        include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layout/breadcrumb.php';
        $debug_info[] = "Breadcrumb included successfully";
    } catch (Exception $e) {
        $debug_info[] = "Error including breadcrumb: " . $e->getMessage();
    }
    
    // Fetch counts for dashboard - wrapped in try/catch blocks
    $total_users = 0;
    $total_investments = 0;
    $total_deposits = 0;
    $pending_withdrawals = 0;
    $total_profit = 0;
    
    try {
        // Count users
        $result = $conn_back->query("SELECT COUNT(*) as total FROM users");
        if ($result && $row = $result->fetch_assoc()) {
            $total_users = $row['total'];
        }
        $debug_info[] = "Users query executed successfully";
    } catch (Exception $e) {
        $debug_info[] = "Error querying users: " . $e->getMessage();
    }
    
    try {
        // Count investments
        $result = $conn_back->query("SELECT COUNT(*) as total FROM investments");
        if ($result && $row = $result->fetch_assoc()) {
            $total_investments = $row['total'];
        }
        $debug_info[] = "Investments query executed successfully";
    } catch (Exception $e) {
        $debug_info[] = "Error querying investments: " . $e->getMessage();
    }
    
    try {
        // Sum deposits
        $result = $conn_back->query("SELECT SUM(amount) as total FROM deposits WHERE status = 'completed'");
        if ($result && $row = $result->fetch_assoc()) {
            $total_deposits = $row['total'] ?: 0;
        }
        $debug_info[] = "Deposits query executed successfully";
    } catch (Exception $e) {
        $debug_info[] = "Error querying deposits: " . $e->getMessage();
    }
    
    try {
        // Count pending withdrawals
        $result = $conn_back->query("SELECT COUNT(*) as total FROM withdrawals WHERE status = 'pending'");
        if ($result && $row = $result->fetch_assoc()) {
            $pending_withdrawals = $row['total'];
        }
        $debug_info[] = "Withdrawals query executed successfully";
    } catch (Exception $e) {
        $debug_info[] = "Error querying withdrawals: " . $e->getMessage();
    }
    
    try {
        // Sum profit
        $result = $conn_back->query("SELECT SUM(profit) as total FROM investments");
        if ($result && $row = $result->fetch_assoc()) {
            $total_profit = $row['total'] ?: 0;
        }
        $debug_info[] = "Profit query executed successfully";
    } catch (Exception $e) {
        $debug_info[] = "Error querying profit: " . $e->getMessage();
    }
    
    // Recent users
    $recent_users = [];
    try {
        $result = $conn_back->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC LIMIT 5");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $recent_users[] = $row;
            }
        }
        $debug_info[] = "Recent users query executed successfully";
    } catch (Exception $e) {
        $debug_info[] = "Error querying recent users: " . $e->getMessage();
    }
    
    // Recent transactions
    $recent_transactions = [];
    try {
        $result = $conn_back->query("
            SELECT 'deposit' as type, d.amount, d.status, d.created_at, u.username 
            FROM deposits d 
            JOIN users u ON d.user_id = u.id 
            UNION ALL 
            SELECT 'withdrawal' as type, w.amount, w.status, w.created_at, u.username 
            FROM withdrawals w 
            JOIN users u ON w.user_id = u.id 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $recent_transactions[] = $row;
            }
        }
        $debug_info[] = "Recent transactions query executed successfully";
    } catch (Exception $e) {
        $debug_info[] = "Error querying recent transactions: " . $e->getMessage();
    }
    
    // Debug section - will only be visible when there's a problem
    if (false) {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px; border: 1px solid #f5c6cb;'>";
        echo "<h3>Debug Information</h3>";
        echo "<ul>";
        foreach ($debug_info as $info) {
            echo "<li>" . htmlspecialchars($info) . "</li>";
        }
        echo "</ul>";
        echo "</div>";
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
                                <?php foreach ($recent_transactions as $tx): ?>
                                <tr>
                                    <td>
                                        <span class="badge <?php echo $tx['type'] == 'deposit' ? 'badge-success' : 'badge-info'; ?>">
                                            <?php echo ucfirst($tx['type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($tx['username']); ?></td>
                                    <td>$<?php echo number_format($tx['amount'], 2); ?></td>
                                    <td>
                                        <span class="badge 
                                            <?php 
                                            if ($tx['status'] == 'completed') echo 'badge-success';
                                            elseif ($tx['status'] == 'pending') echo 'badge-warning';
                                            else echo 'badge-danger';
                                            ?>">
                                            <?php echo ucfirst($tx['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($tx['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (count($recent_transactions) == 0): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No transactions found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="box-footer text-center">
                    <a href="transactions.php" class="text-uppercase d-none d-md-inline-block">View All Transactions</a>
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
                                <?php foreach ($recent_users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (count($recent_users) == 0): ?>
                                <tr>
                                    <td colspan="3" class="text-center">No users found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="box-footer text-center">
                    <a href="users.php" class="text-uppercase d-none d-md-inline-block">View All Users</a>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->

<?php
try {
    include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layout/footer.php';
    $debug_info[] = "Footer included successfully";
} catch (Exception $e) {
    $debug_info[] = "Error including footer: " . $e->getMessage();
}

// If there were any errors, let's display the debug information
$error_occurred = false;
foreach ($debug_info as $info) {
    if (strpos($info, 'Error') !== false) {
        $error_occurred = true;
        break;
    }
}

if ($error_occurred) {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px; border: 1px solid #f5c6cb;'>";
    echo "<h3>Debug Information</h3>";
    echo "<ul>";
    foreach ($debug_info as $info) {
        echo "<li>" . htmlspecialchars($info) . "</li>";
    }
    echo "</ul>";
    echo "</div>";
}

// Get any errors that occurred during script execution
$errors = ob_get_clean();
if (!empty($errors)) {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px; border: 1px solid #f5c6cb;'>";
    echo "<h3>PHP Errors</h3>";
    echo "<pre>";
    echo htmlspecialchars($errors);
    echo "</pre>";
    echo "</div>";
} else {
    echo ob_get_clean(); // Output the normal page if no errors
}

} catch (Exception $e) {
    // Catch any uncaught exceptions
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px; border: 1px solid #f5c6cb;'>";
    echo "<h3>Fatal Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>
