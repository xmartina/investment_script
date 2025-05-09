<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
$page_name = 'My Investments';
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';

include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/header.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/breadcumb.php';

// Fetch user's investments
$user_id = $_SESSION['user_id'];
$investments_query = "
    SELECT i.*, p.name as plan_name, p.roi_percent, p.duration_days, p.plan_type, p.category, p.risk_level,
           r.id as return_id, r.return_amount, r.expected_date, r.status as return_status
    FROM investments i 
    JOIN investment_plans p ON i.plan_id = p.id 
    LEFT JOIN investment_returns r ON i.id = r.investment_id
    WHERE i.user_id = '$user_id' 
    ORDER BY i.created_at DESC";
$investments_result = $conn_back->query($investments_query);

// Calculate statistics
$total_invested = 0;
$total_roi_amount = 0;
$pending_returns = 0;
$paid_returns = 0;
$active_investments = 0;
$completed_investments = 0;

if ($investments_result && $investments_result->num_rows > 0) {
    while ($row = $investments_result->fetch_assoc()) {
        $total_invested += $row['amount'];
        $total_roi_amount += $row['return_amount'] ?? ($row['amount'] * $row['roi_percent'] / 100);
        
        if ($row['status'] == 'active') {
            $active_investments++;
        } else if ($row['status'] == 'completed') {
            $completed_investments++;
        }
        
        if (isset($row['return_status'])) {
            if ($row['return_status'] == 'pending') {
                $pending_returns++;
            } else if ($row['return_status'] == 'paid') {
                $paid_returns++;
            }
        }
    }
    
    // Reset result pointer
    $investments_result->data_seek(0);
}
?>

<div class="container mt-4" id="main-content">
    <!-- Investment Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card adminuiux-card h-100">
                <div class="card-body">
                    <h6 class="card-title text-secondary">Total Invested</h6>
                    <h3 class="fw-bold"><?=$user_currency?><?=number_format($total_invested, 2)?></h3>
                    <p class="small text-muted">Across all investments</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card adminuiux-card h-100">
                <div class="card-body">
                    <h6 class="card-title text-secondary">Expected Returns</h6>
                    <h3 class="fw-bold"><?=$user_currency?><?=number_format($total_roi_amount, 2)?></h3>
                    <p class="small text-muted">Total expected earnings</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card adminuiux-card h-100">
                <div class="card-body">
                    <h6 class="card-title text-secondary">Active/Pending</h6>
                    <h3 class="fw-bold"><?=$active_investments?> / <?=$pending_returns?></h3>
                    <p class="small text-muted">Active investments / pending returns</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card adminuiux-card h-100">
                <div class="card-body">
                    <h6 class="card-title text-secondary">Completed/Paid</h6>
                    <h3 class="fw-bold"><?=$completed_investments?> / <?=$paid_returns?></h3>
                    <p class="small text-muted">Completed investments / paid returns</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Investment List -->
    <div class="card adminuiux-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">My Investments</h5>
            <a href="/user/investment" class="btn btn-sm btn-primary">New Investment</a>
        </div>
        <div class="card-body">
            <?php if ($investments_result && $investments_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Plan</th>
                                <th>Amount</th>
                                <th>ROI %</th>
                                <th>Expected Returns</th>
                                <th>Start Date</th>
                                <th>Maturity Date</th>
                                <th>Status</th>
                                <th>Return Status</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($investment = $investments_result->fetch_assoc()): 
                                // Calculate progress percentage
                                $start_date = strtotime($investment['started_at']);
                                $end_date = strtotime($investment['ends_at']);
                                $current_date = time();
                                
                                $total_duration = $end_date - $start_date;
                                $elapsed_duration = $current_date - $start_date;
                                
                                $progress = 0;
                                if ($total_duration > 0) {
                                    $progress = min(100, max(0, ($elapsed_duration / $total_duration) * 100));
                                }
                                
                                // Calculate expected returns amount
                                $expected_returns = isset($investment['return_amount']) ? 
                                    $investment['return_amount'] : 
                                    ($investment['amount'] * $investment['roi_percent'] / 100);
                                
                                // Determine investment status class
                                $status_class = '';
                                switch ($investment['status']) {
                                    case 'active':
                                        $status_class = 'bg-success';
                                        break;
                                    case 'completed':
                                        $status_class = 'bg-info';
                                        break;
                                    case 'cancelled':
                                        $status_class = 'bg-danger';
                                        break;
                                    default:
                                        $status_class = 'bg-secondary';
                                }
                                
                                // Determine return status class
                                $return_status_class = '';
                                $return_status = isset($investment['return_status']) ? $investment['return_status'] : 'pending';
                                switch ($return_status) {
                                    case 'pending':
                                        $return_status_class = 'bg-warning';
                                        break;
                                    case 'paid':
                                        $return_status_class = 'bg-success';
                                        break;
                                    case 'failed':
                                        $return_status_class = 'bg-danger';
                                        break;
                                    default:
                                        $return_status_class = 'bg-secondary';
                                }
                            ?>
                            <tr>
                                <td>
                                    <strong><?= $investment['plan_name'] ?></strong><br>
                                    <small class="text-muted"><?= $investment['plan_type'] ?> / <?= $investment['category'] ?></small>
                                </td>
                                <td><?= $user_currency ?><?= number_format($investment['amount'], 2) ?></td>
                                <td><?= $investment['roi_percent'] ?>%</td>
                                <td class="text-success"><?= $user_currency ?><?= number_format($expected_returns, 2) ?></td>
                                <td><?= date('M d, Y', strtotime($investment['started_at'])) ?></td>
                                <td><?= date('M d, Y', strtotime($investment['ends_at'])) ?></td>
                                <td><span class="badge <?= $status_class ?>"><?= ucfirst($investment['status']) ?></span></td>
                                <td><span class="badge <?= $return_status_class ?>"><?= ucfirst($return_status) ?></span></td>
                                <td>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-theme-1" role="progressbar" style="width: <?= $progress ?>%;" 
                                            aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted"><?= round($progress) ?>% complete</small>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-wallet2 text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <h5>No investments yet</h5>
                    <p class="text-muted">You haven't made any investments yet. Start investing to grow your wealth.</p>
                    <a href="/user/investment" class="btn btn-primary">Explore Investment Plans</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/footer.php';
?>