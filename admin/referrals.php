<?php
// Admin Referrals Management Page
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Referrals Management";
$current_page = "referrals.php";
$message = "";
$error = "";

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 20;
$offset = ($page - 1) * $records_per_page;

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM referral_commissions";
$result = $conn_back->query($count_sql);
$row = $result->fetch_assoc();
$total_records = $row['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get referral commissions
$sql = "
    SELECT 
        r.*,
        CONCAT(u1.first_name, ' ', u1.last_name) as referrer_name,
        u1.email as referrer_email,
        CONCAT(u2.first_name, ' ', u2.last_name) as referred_name,
        u2.email as referred_email
    FROM referral_commissions r
    JOIN users u1 ON r.referrer_id = u1.id
    JOIN users u2 ON r.referred_id = u2.id
    ORDER BY r.created_at DESC
    LIMIT $offset, $records_per_page
";
$result = $conn_back->query($sql);

include_once __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Referrals Management</h1>
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
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Referral Commissions</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Referrer</th>
                            <th>Referred User</th>
                            <th>Amount</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td>
                                        <a href="user_detail.php?id=<?= $row['referrer_id'] ?>">
                                            <?= htmlspecialchars($row['referrer_name']) ?>
                                        </a>
                                        <small class="d-block text-muted"><?= htmlspecialchars($row['referrer_email']) ?></small>
                                    </td>
                                    <td>
                                        <a href="user_detail.php?id=<?= $row['referred_id'] ?>">
                                            <?= htmlspecialchars($row['referred_name']) ?>
                                        </a>
                                        <small class="d-block text-muted"><?= htmlspecialchars($row['referred_email']) ?></small>
                                    </td>
                                    <td>$<?= number_format($row['amount'], 2) ?></td>
                                    <td>
                                        <span class="badge badge-<?php
                                            switch ($row['source_type']) {
                                                case 'investment': echo 'primary'; break;
                                                case 'deposit': echo 'success'; break;
                                                case 'staking': echo 'info'; break;
                                                default: echo 'secondary';
                                            }
                                        ?>">
                                            <?= ucfirst(str_replace('_', ' ', $row['source_type'])) ?>
                                        </span>
                                        <small class="d-block">#<?= $row['source_id'] ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $row['status'] === 'paid' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y H:i', strtotime($row['created_at'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No referral commissions found</td>
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
                                <a class="page-link" href="?page=1" aria-label="First">
                                    <span aria-hidden="true">&laquo;&laquo;</span>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
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
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $total_pages ?>" aria-label="Last">
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

<?php include_once __DIR__ . '/layout/footer.php'; ?> 