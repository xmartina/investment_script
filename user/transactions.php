<?php
// Transactions page
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /user/login");
    exit();
}

$user_id = $_SESSION['user_id'];
$page_name = "Transaction History";
$css_files = [];
$js_files = [];

// Default sorting
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'date_time';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Validate sort parameters to prevent SQL injection
$valid_sort_fields = ['date_time', 'amount', 'transaction_type', 'status', 'reference_id'];
if (!in_array($sort_by, $valid_sort_fields)) {
    $sort_by = 'date_time';
}

$valid_sort_orders = ['ASC', 'DESC'];
if (!in_array(strtoupper($sort_order), $valid_sort_orders)) {
    $sort_order = 'DESC';
}

// Pagination
$records_per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $records_per_page;

// Filter by transaction type
$transaction_type = isset($_GET['type']) ? $_GET['type'] : '';
$valid_types = ['deposit', 'withdrawal', 'investment', 'staking', 'unstaking', 'reward', 'penalty', 'referral', 'bonus', ''];

if (!in_array($transaction_type, $valid_types)) {
    $transaction_type = '';
}

// Build query condition
$condition = "user_id = ?";
$params = [$user_id];
$types = "i";

if (!empty($transaction_type)) {
    $condition .= " AND transaction_type = ?";
    $params[] = $transaction_type;
    $types .= "s";
}

// Count total records for pagination
$count_sql = "SELECT COUNT(*) as total FROM transactions WHERE $condition";
$stmt = $conn_back->prepare($count_sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_records = $row['total'];
$total_pages = ceil($total_records / $records_per_page);
$stmt->close();

// Get transactions
$sql = "SELECT * FROM transactions WHERE $condition ORDER BY $sort_by $sort_order LIMIT ? OFFSET ?";
$stmt = $conn_back->prepare($sql);
$types .= "ii";
$params[] = $records_per_page;
$params[] = $offset;
$stmt->bind_param($types, ...$params);
$stmt->execute();
$transactions = $stmt->get_result();
$stmt->close();

// Get transaction type counts for filter
$type_counts = [];
$counts_sql = "SELECT transaction_type, COUNT(*) as count FROM transactions WHERE user_id = ? GROUP BY transaction_type";
$stmt = $conn_back->prepare($counts_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$counts_result = $stmt->get_result();
while ($count_row = $counts_result->fetch_assoc()) {
    $type_counts[$count_row['transaction_type']] = $count_row['count'];
}
$stmt->close();

// Function to generate sort URL
function getSortUrl($field, $current_sort, $current_order) {
    $new_order = ($current_sort === $field && $current_order === 'DESC') ? 'ASC' : 'DESC';
    $query_params = $_GET;
    $query_params['sort'] = $field;
    $query_params['order'] = $new_order;
    return '?' . http_build_query($query_params);
}

// Function to get filter URL
function getFilterUrl($type) {
    $query_params = $_GET;
    if ($type === '' && isset($query_params['type'])) {
        unset($query_params['type']);
    } else {
        $query_params['type'] = $type;
    }
    // Reset to first page when filtering
    $query_params['page'] = 1;
    return '?' . http_build_query($query_params);
}

// Function to generate pagination URL
function getPaginationUrl($page_num) {
    $query_params = $_GET;
    $query_params['page'] = $page_num;
    return '?' . http_build_query($query_params);
}

// Function to get status badge class
function getStatusBadgeClass($status) {
    switch (strtolower($status)) {
        case 'successful':
        case 'completed':
        case 'success':
            return 'badge-success';
        case 'pending':
        case 'processing':
            return 'badge-warning';
        case 'failed':
        case 'rejected':
        case 'expired':
            return 'badge-danger';
        default:
            return 'badge-secondary';
    }
}

// Function to get transaction type badge class
function getTypeBadgeClass($type) {
    switch (strtolower($type)) {
        case 'deposit':
            return 'badge-primary';
        case 'withdrawal':
            return 'badge-danger';
        case 'investment':
            return 'badge-info';
        case 'staking':
            return 'badge-success';
        case 'unstaking':
            return 'badge-warning';
        case 'reward':
            return 'badge-success';
        case 'penalty':
            return 'badge-danger';
        case 'referral':
        case 'bonus':
            return 'badge-primary';
        default:
            return 'badge-secondary';
    }
}

// Function to get transaction icon
function getTransactionIcon($type) {
    switch (strtolower($type)) {
        case 'deposit':
            return 'fa-arrow-circle-down';
        case 'withdrawal':
            return 'fa-arrow-circle-up';
        case 'investment':
            return 'fa-chart-line';
        case 'staking':
            return 'fa-lock';
        case 'unstaking':
            return 'fa-unlock';
        case 'reward':
            return 'fa-gift';
        case 'penalty':
            return 'fa-exclamation-circle';
        case 'referral':
            return 'fa-user-plus';
        case 'bonus':
            return 'fa-award';
        default:
            return 'fa-exchange-alt';
    }
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/header.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/breadcumb.php';
?>

<!-- Transactions Page -->
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-12">
            <div class="card card-body mb-4">
                <div class="row mb-3">
                    <div class="col">
                        <h4 class="fw-medium">Transaction History</h4>
                        <p class="text-muted">View and track all of your transactions across the platform.</p>
                    </div>
                </div>
                
                <!-- Filter Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-3">
                        <a href="<?= getFilterUrl('') ?>" class="text-decoration-none">
                            <div class="card shadow-sm h-100 <?= $transaction_type === '' ? 'border-primary' : '' ?>">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-auto">
                                            <div class="avatar avatar-50 rounded-circle bg-primary-subtle">
                                                <i class="bi bi-clipboard-check fs-4 text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <p class="mb-0 small text-primary">All Transactions</p>
                                            <h5 class="mb-0 fw-bold"><?= $total_records ?></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-3">
                        <a href="<?= getFilterUrl('deposit') ?>" class="text-decoration-none">
                            <div class="card shadow-sm h-100 <?= $transaction_type === 'deposit' ? 'border-success' : '' ?>">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-auto">
                                            <div class="avatar avatar-50 rounded-circle bg-success-subtle">
                                                <i class="bi bi-arrow-down-circle fs-4 text-success"></i>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <p class="mb-0 small text-success">Deposits</p>
                                            <h5 class="mb-0 fw-bold"><?= isset($type_counts['deposit']) ? $type_counts['deposit'] : 0 ?></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-3">
                        <a href="<?= getFilterUrl('withdrawal') ?>" class="text-decoration-none">
                            <div class="card shadow-sm h-100 <?= $transaction_type === 'withdrawal' ? 'border-danger' : '' ?>">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-auto">
                                            <div class="avatar avatar-50 rounded-circle bg-danger-subtle">
                                                <i class="bi bi-arrow-up-circle fs-4 text-danger"></i>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <p class="mb-0 small text-danger">Withdrawals</p>
                                            <h5 class="mb-0 fw-bold"><?= isset($type_counts['withdrawal']) ? $type_counts['withdrawal'] : 0 ?></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-3">
                        <a href="<?= getFilterUrl('staking') ?>" class="text-decoration-none">
                            <div class="card shadow-sm h-100 <?= $transaction_type === 'staking' ? 'border-info' : '' ?>">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-auto">
                                            <div class="avatar avatar-50 rounded-circle bg-info-subtle">
                                                <i class="bi bi-lock fs-4 text-info"></i>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <p class="mb-0 small text-info">Staking</p>
                                            <h5 class="mb-0 fw-bold">
                                                <?= (isset($type_counts['staking']) ? $type_counts['staking'] : 0) + 
                                                    (isset($type_counts['unstaking']) ? $type_counts['unstaking'] : 0) + 
                                                    (isset($type_counts['reward']) ? $type_counts['reward'] : 0) ?>
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                
                <!-- Transactions Table Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Transaction History</h5>
                        <div class="dropdown">
                            <button class="btn btn-link dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-filter me-1"></i> Filter
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">Transaction Types:</h6></li>
                                <li><a class="dropdown-item <?= $transaction_type === '' ? 'active' : '' ?>" href="<?= getFilterUrl('') ?>">All Transactions</a></li>
                                <li><a class="dropdown-item <?= $transaction_type === 'deposit' ? 'active' : '' ?>" href="<?= getFilterUrl('deposit') ?>">Deposits</a></li>
                                <li><a class="dropdown-item <?= $transaction_type === 'withdrawal' ? 'active' : '' ?>" href="<?= getFilterUrl('withdrawal') ?>">Withdrawals</a></li>
                                <li><a class="dropdown-item <?= $transaction_type === 'investment' ? 'active' : '' ?>" href="<?= getFilterUrl('investment') ?>">Investments</a></li>
                                <li><a class="dropdown-item <?= $transaction_type === 'staking' ? 'active' : '' ?>" href="<?= getFilterUrl('staking') ?>">Staking</a></li>
                                <li><a class="dropdown-item <?= $transaction_type === 'unstaking' ? 'active' : '' ?>" href="<?= getFilterUrl('unstaking') ?>">Unstaking</a></li>
                                <li><a class="dropdown-item <?= $transaction_type === 'reward' ? 'active' : '' ?>" href="<?= getFilterUrl('reward') ?>">Rewards</a></li>
                                <li><a class="dropdown-item <?= $transaction_type === 'referral' ? 'active' : '' ?>" href="<?= getFilterUrl('referral') ?>">Referrals</a></li>
                                <li><a class="dropdown-item <?= $transaction_type === 'bonus' ? 'active' : '' ?>" href="<?= getFilterUrl('bonus') ?>">Bonuses</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>
                                            <a href="<?= getSortUrl('transaction_type', $sort_by, $sort_order) ?>" class="text-decoration-none">
                                                Type 
                                                <?php if ($sort_by === 'transaction_type'): ?>
                                                    <i class="bi bi-sort-<?= $sort_order === 'ASC' ? 'up' : 'down' ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>Reference</th>
                                        <th>
                                            <a href="<?= getSortUrl('amount', $sort_by, $sort_order) ?>" class="text-decoration-none">
                                                Amount 
                                                <?php if ($sort_by === 'amount'): ?>
                                                    <i class="bi bi-sort-<?= $sort_order === 'ASC' ? 'up' : 'down' ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="<?= getSortUrl('status', $sort_by, $sort_order) ?>" class="text-decoration-none">
                                                Status
                                                <?php if ($sort_by === 'status'): ?>
                                                    <i class="bi bi-sort-<?= $sort_order === 'ASC' ? 'up' : 'down' ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="<?= getSortUrl('date_time', $sort_by, $sort_order) ?>" class="text-decoration-none">
                                                Date
                                                <?php if ($sort_by === 'date_time'): ?>
                                                    <i class="bi bi-sort-<?= $sort_order === 'ASC' ? 'up' : 'down' ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($transactions->num_rows > 0): ?>
                                        <?php while ($transaction = $transactions->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= $transaction['id'] ?></td>
                                                <td>
                                                    <span class="badge bg-<?= str_replace('badge-', '', getTypeBadgeClass($transaction['transaction_type'])) ?>">
                                                        <i class="bi <?= str_replace('fa-', 'bi-', getTransactionIcon($transaction['transaction_type'])) ?> me-1"></i>
                                                        <?= ucfirst($transaction['transaction_type']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted"><?= $transaction['reference_id'] ?></small>
                                                </td>
                                                <td>
                                                    <span class="<?= in_array(strtolower($transaction['transaction_type']), ['deposit', 'reward', 'referral', 'bonus']) ? 'text-success' : 'text-danger' ?>">
                                                        <?= in_array(strtolower($transaction['transaction_type']), ['deposit', 'reward', 'referral', 'bonus']) ? '+' : '-' ?>
                                                        $<?= number_format($transaction['amount'], 2) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= str_replace(['badge-success', 'badge-warning', 'badge-danger', 'badge-secondary'], ['success', 'warning', 'danger', 'secondary'], getStatusBadgeClass($transaction['status'])) ?>">
                                                        <?= ucfirst($transaction['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div><?= date('M d, Y', strtotime($transaction['date_time'])) ?></div>
                                                    <small class="text-muted"><?= date('h:i A', strtotime($transaction['date_time'])) ?></small>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#transactionModal<?= $transaction['id'] ?>">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    
                                                    <!-- Transaction Details Modal -->
                                                    <div class="modal fade" id="transactionModal<?= $transaction['id'] ?>" tabindex="-1" aria-labelledby="transactionModalLabel<?= $transaction['id'] ?>" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="transactionModalLabel<?= $transaction['id'] ?>">Transaction Details</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="row mb-3">
                                                                        <div class="col-12 text-center mb-3">
                                                                            <div class="d-inline-block p-3 rounded-circle 
                                                                                bg-<?= in_array(strtolower($transaction['transaction_type']), ['deposit', 'reward', 'referral', 'bonus']) ? 'success' : 'danger' ?> text-white">
                                                                                <i class="bi <?= str_replace('fa-', 'bi-', getTransactionIcon($transaction['transaction_type'])) ?> fs-3"></i>
                                                                            </div>
                                                                            <h4 class="mt-2"><?= ucfirst($transaction['transaction_type']) ?></h4>
                                                                            <h2 class="<?= in_array(strtolower($transaction['transaction_type']), ['deposit', 'reward', 'referral', 'bonus']) ? 'text-success' : 'text-danger' ?>">
                                                                                <?= in_array(strtolower($transaction['transaction_type']), ['deposit', 'reward', 'referral', 'bonus']) ? '+' : '-' ?>
                                                                                $<?= number_format($transaction['amount'], 2) ?>
                                                                            </h2>
                                                                            <span class="badge bg-<?= str_replace(['badge-success', 'badge-warning', 'badge-danger', 'badge-secondary'], ['success', 'warning', 'danger', 'secondary'], getStatusBadgeClass($transaction['status'])) ?> p-2">
                                                                                <?= ucfirst($transaction['status']) ?>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="row">
                                                                        <div class="col-6">
                                                                            <p class="mb-1"><strong>Transaction ID:</strong></p>
                                                                            <p class="text-muted"><?= $transaction['id'] ?></p>
                                                                        </div>
                                                                        <div class="col-6">
                                                                            <p class="mb-1"><strong>Reference ID:</strong></p>
                                                                            <p class="text-muted"><?= $transaction['reference_id'] ?></p>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="row">
                                                                        <div class="col-6">
                                                                            <p class="mb-1"><strong>Date:</strong></p>
                                                                            <p class="text-muted"><?= date('M d, Y', strtotime($transaction['date_time'])) ?></p>
                                                                        </div>
                                                                        <div class="col-6">
                                                                            <p class="mb-1"><strong>Time:</strong></p>
                                                                            <p class="text-muted"><?= date('h:i:s A', strtotime($transaction['date_time'])) ?></p>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="row">
                                                                        <div class="col-6">
                                                                            <p class="mb-1"><strong>Amount:</strong></p>
                                                                            <p class="<?= in_array(strtolower($transaction['transaction_type']), ['deposit', 'reward', 'referral', 'bonus']) ? 'text-success' : 'text-danger' ?>">
                                                                                $<?= number_format($transaction['amount'], 2) ?>
                                                                            </p>
                                                                        </div>
                                                                        <div class="col-6">
                                                                            <p class="mb-1"><strong>Currency:</strong></p>
                                                                            <p class="text-muted"><?= strtoupper($transaction['currency']) ?></p>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <?php if (!empty($transaction['description'])): ?>
                                                                    <div class="row">
                                                                        <div class="col-12">
                                                                            <p class="mb-1"><strong>Description:</strong></p>
                                                                            <p class="text-muted"><?= $transaction['description'] ?></p>
                                                                        </div>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                    
                                                                    <?php if (!empty($transaction['transaction_proof_id'])): ?>
                                                                    <div class="row">
                                                                        <div class="col-12">
                                                                            <p class="mb-1"><strong>Transaction Proof ID:</strong></p>
                                                                            <p class="text-muted"><?= $transaction['transaction_proof_id'] ?></p>
                                                                        </div>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">No transactions found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <div class="d-flex justify-content-center p-3">
                            <nav aria-label="Transaction pagination">
                                <ul class="pagination">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= getPaginationUrl(1) ?>" aria-label="First">
                                                <span aria-hidden="true">&laquo;&laquo;</span>
                                            </a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= getPaginationUrl($page - 1) ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php
                                    // Determine pagination range
                                    $start_page = max(1, $page - 2);
                                    $end_page = min($total_pages, $page + 2);
                                    
                                    // Ensure we always show at least 5 pages if available
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
                                            <a class="page-link" href="<?= getPaginationUrl($i) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= getPaginationUrl($page + 1) ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= getPaginationUrl($total_pages) ?>" aria-label="Last">
                                                <span aria-hidden="true">&raquo;&raquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                        <?php endif; ?>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/footer.php'; ?>
