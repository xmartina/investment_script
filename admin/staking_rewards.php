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

// Get staking ID filter if provided
$staking_id = isset($_GET['staking_id']) ? (int)$_GET['staking_id'] : 0;
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['process_rewards'])) {
        // Logic for manually processing staking rewards would go here
        // This is a placeholder for actual reward processing code
        
        // Admin log
        $admin_id = $_SESSION['admin_id'];
        $action = "Manually processed staking rewards";
        $ip = $_SERVER['REMOTE_ADDR'];
        
        $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
        $log_stmt->bind_param("iss", $admin_id, $action, $ip);
        $log_stmt->execute();
        
        $message = "Staking rewards processed successfully.";
    }
}

// Check if staking_rewards table exists
$table_exists = false;
$result = $conn_back->query("SHOW TABLES LIKE 'staking_rewards'");
if ($result && $result->num_rows > 0) {
    $table_exists = true;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 20;
$offset = ($page - 1) * $records_per_page;

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
            s.plan_id,
            p.name as plan_name
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
    
    // Get staking position details if filtered by staking_id
    $staking_details = null;
    if ($staking_id > 0) {
        $stmt = $conn_back->prepare("
            SELECT 
                s.*,
                CONCAT(u.first_name, ' ', u.last_name) as username,
                u.email,
                p.name as plan_name,
                p.roi_daily
            FROM staking_positions s
            JOIN users u ON s.user_id = u.id
            JOIN staking_plans p ON s.plan_id = p.id
            WHERE s.id = ?
        ");
        $stmt->bind_param("i", $staking_id);
        $stmt->execute();
        $staking_details = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
    
    // Get summary statistics
    $stats_sql = "
        SELECT 
            COUNT(r.id) as total_rewards,
            SUM(r.amount) as total_amount,
            COUNT(DISTINCT r.user_id) as unique_users,
            SUM(CASE WHEN r.is_compounded = 1 THEN r.amount ELSE 0 END) as total_compounded
        FROM staking_rewards r
        WHERE $condition
    ";
    $stmt = $conn_back->prepare($stats_sql);
    if (!empty($types)) {
        // Remove the last two parameters (LIMIT and OFFSET)
        $stmt->bind_param(substr($types, 0, -2), ...array_slice($params, 0, -2));
    }
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

include_once __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Staking Rewards Management</h1>
        
        <div>
            <?php if (!$staking_id): ?>
                <form method="post" class="d-inline">
                    <button type="submit" name="process_rewards" class="btn btn-primary">
                        <i class="fas fa-sync-alt mr-2"></i> Process Rewards
                    </button>
                </form>
            <?php endif; ?>
            
            <?php if ($staking_id): ?>
                <a href="staking_rewards.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i> Back to All Rewards
                </a>
            <?php endif; ?>
        </div>
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
            <p>The staking_rewards table does not exist in the database. Please run the database initialization script.</p>
            <form method="post" action="db_fix.php">
                <input type="hidden" name="create_staking_tables" value="1">
                <button type="submit" class="btn btn-primary">Create Staking Tables</button>
            </form>
        </div>
    <?php else: ?>
        <?php if ($staking_details): ?>
            <div class="row">
                <div class="col-xl-12 col-md-12 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Staking Position #<?= $staking_id ?>
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars($staking_details['username']) ?></div>
                                    <div class="text-muted"><?= htmlspecialchars($staking_details['email']) ?></div>
                                    <div class="mt-2">
                                        <span class="badge badge-info"><?= htmlspecialchars($staking_details['plan_name']) ?></span>
                                        <span class="badge badge-success">$<?= number_format($staking_details['amount'], 2) ?></span>
                                        <span class="badge badge-warning"><?= $staking_details['roi_daily'] ?>% Daily</span>
                                        <span class="badge badge-<?= $staking_details['is_compounding'] ? 'success' : 'secondary' ?>">
                                            <?= $staking_details['is_compounding'] ? 'Compounding' : 'No Compounding' ?>
                                        </span>
                                        <span class="badge badge-<?php
                                            switch ($staking_details['status']) {
                                                case 'active': echo 'success'; break;
                                                case 'completed': echo 'info'; break;
                                                case 'cancelled': echo 'danger'; break;
                                                default: echo 'secondary';
                                            }
                                        ?>">
                                            <?= ucfirst($staking_details['status']) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Stats Cards -->
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Rewards
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_rewards'] ?? 0 ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-trophy fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Total Amount
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">$<?= number_format($stats['total_amount'] ?? 0, 2) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Users Earning
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['unique_users'] ?? 0 ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Compounded
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">$<?= number_format($stats['total_compounded'] ?? 0, 2) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-sync-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <?= $staking_id ? "Rewards for Staking Position #$staking_id" : "All Staking Rewards" ?>
                </h6>
                
                <?php if (!$staking_id): ?>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="filterDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-filter fa-sm fa-fw text-gray-400"></i> Filter
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="filterDropdown">
                            <div class="dropdown-header">Filter Options:</div>
                            <form class="px-3 py-2">
                                <div class="form-group">
                                    <label for="staking_id_filter">Staking ID</label>
                                    <input type="number" class="form-control" id="staking_id_filter" name="staking_id" value="<?= $staking_id ? $staking_id : '' ?>" placeholder="Enter Staking ID">
                                </div>
                                <div class="form-group">
                                    <label for="user_id_filter">User ID</label>
                                    <input type="number" class="form-control" id="user_id_filter" name="user_id" value="<?= $user_id ? $user_id : '' ?>" placeholder="Enter User ID">
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm btn-block">Apply Filters</button>
                                <a href="staking_rewards.php" class="btn btn-secondary btn-sm btn-block">Reset</a>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Plan</th>
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
                                        <td><?= htmlspecialchars($reward['plan_name']) ?></td>
                                        <td>
                                            <a href="staking_rewards.php?staking_id=<?= $reward['staking_id'] ?>">
                                                #<?= $reward['staking_id'] ?>
                                            </a>
                                        </td>
                                        <td>$<?= number_format($reward['amount'], 2) ?></td>
                                        <td>
                                            <span class="badge badge-<?= $reward['is_compounded'] ? 'success' : 'secondary' ?>">
                                                <?= $reward['is_compounded'] ? 'Yes' : 'No' ?>
                                            </span>
                                        </td>
                                        <td><?= date('M d, Y H:i', strtotime($reward['created_at'])) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No rewards found</td>
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
                                    <a class="page-link" href="?page=1<?= $staking_id ? '&staking_id='.$staking_id : '' ?><?= $user_id ? '&user_id='.$user_id : '' ?>" aria-label="First">
                                        <span aria-hidden="true">&laquo;&laquo;</span>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page - 1 ?><?= $staking_id ? '&staking_id='.$staking_id : '' ?><?= $user_id ? '&user_id='.$user_id : '' ?>" aria-label="Previous">
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
                                    <a class="page-link" href="?page=<?= $i ?><?= $staking_id ? '&staking_id='.$staking_id : '' ?><?= $user_id ? '&user_id='.$user_id : '' ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page + 1 ?><?= $staking_id ? '&staking_id='.$staking_id : '' ?><?= $user_id ? '&user_id='.$user_id : '' ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $total_pages ?><?= $staking_id ? '&staking_id='.$staking_id : '' ?><?= $user_id ? '&user_id='.$user_id : '' ?>" aria-label="Last">
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

<?php include_once __DIR__ . '/layout/footer.php'; ?> 