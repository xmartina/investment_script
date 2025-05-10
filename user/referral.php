<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
$page_name = 'Referrals';
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/header.php';

// Get user's referral information
$user_id = $_SESSION['user_id'];

// Get user's referral code
$stmt = $conn_back->prepare("SELECT referral_code, referral_bonus_earned FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_referral = $result->fetch_assoc();
$stmt->close();

// Generate referral link
$referral_link = $site_link . "/register?ref=" . $user_referral['referral_code'];

// Get list of referred users
$stmt = $conn_back->prepare("
    SELECT u.id, u.first_name, u.last_name, u.email, u.created_at, 
           COALESCE(SUM(rc.amount), 0) as commission_earned
    FROM users u 
    LEFT JOIN referral_commissions rc ON u.id = rc.referred_id AND rc.referrer_id = ?
    WHERE u.referred_by = ?
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$referrals = $stmt->get_result();
$stmt->close();

// Get referral statistics
$stats = [
    'total_referrals' => 0,
    'total_earned' => 0,
    'pending_commission' => 0,
    'paid_commission' => 0
];

// Count total referrals
$stmt = $conn_back->prepare("SELECT COUNT(*) as count FROM users WHERE referred_by = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stats['total_referrals'] = $result->fetch_assoc()['count'];
$stmt->close();

// Get commissions data
$stmt = $conn_back->prepare("
    SELECT 
        SUM(amount) as total,
        SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid
    FROM referral_commissions 
    WHERE referrer_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$commission_data = $result->fetch_assoc();
$stmt->close();

$stats['total_earned'] = $commission_data['total'] ?: 0;
$stats['pending_commission'] = $commission_data['pending'] ?: 0;
$stats['paid_commission'] = $commission_data['paid'] ?: 0;

// Get recent commission transactions
$stmt = $conn_back->prepare("
    SELECT rc.*, u.first_name, u.last_name, u.email
    FROM referral_commissions rc
    JOIN users u ON rc.referred_id = u.id
    WHERE rc.referrer_id = ?
    ORDER BY rc.created_at DESC
    LIMIT 10
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_commissions = $stmt->get_result();
$stmt->close();

// Handle copy link button via AJAX
if (isset($_POST['copy_link']) && $_POST['copy_link'] == 1) {
    echo $referral_link;
    exit;
}
?>

<div class="container mt-4" id="main-content">
    <div class="row">
        <div class="col-12">
            <h1><?= $page_name ?></h1>
            <p class="text-muted">Invite friends and earn 5% commission on their successful investments</p>
        </div>
    </div>

    <!-- Referral Link Box -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card adminuiux-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Your Referral Link</h5>
                </div>
                <div class="card-body">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="referral-link" value="<?= $referral_link ?>" readonly>
                        <button class="btn btn-primary" type="button" id="copy-button" onclick="copyReferralLink()">
                            <i class="bi bi-clipboard"></i> Copy
                        </button>
                    </div>
                    <p class="mb-0 small">Share this link with your friends and earn 5% commission when they make successful investments</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card adminuiux-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-50 bg-theme-1 rounded-circle">
                                <i class="bi bi-people-fill text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Total Referrals</h6>
                            <h3 class="mb-0"><?= $stats['total_referrals'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card adminuiux-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-50 bg-success rounded-circle">
                                <i class="bi bi-cash-stack text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Total Earned</h6>
                            <h3 class="mb-0"><?= $user_currency . number_format($stats['total_earned'], 2) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card adminuiux-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-50 bg-warning rounded-circle">
                                <i class="bi bi-hourglass-split text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Pending Commission</h6>
                            <h3 class="mb-0"><?= $user_currency . number_format($stats['pending_commission'], 2) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card adminuiux-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-50 bg-info rounded-circle">
                                <i class="bi bi-check-circle-fill text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Paid Commission</h6>
                            <h3 class="mb-0"><?= $user_currency . number_format($stats['paid_commission'], 2) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Referral Program Information -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card adminuiux-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">How it Works</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3 mb-md-0">
                            <div class="avatar avatar-60 bg-theme-1 rounded-circle mx-auto mb-3">
                                <i class="bi bi-share text-white"></i>
                            </div>
                            <h5>1. Share Your Link</h5>
                            <p class="mb-0 small">Share your unique referral link with friends and family</p>
                        </div>
                        <div class="col-md-4 text-center mb-3 mb-md-0">
                            <div class="avatar avatar-60 bg-theme-1 rounded-circle mx-auto mb-3">
                                <i class="bi bi-person-plus text-white"></i>
                            </div>
                            <h5>2. They Sign Up</h5>
                            <p class="mb-0 small">When they register using your link, they become your referral</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="avatar avatar-60 bg-theme-1 rounded-circle mx-auto mb-3">
                                <i class="bi bi-cash-coin text-white"></i>
                            </div>
                            <h5>3. Earn Commissions</h5>
                            <p class="mb-0 small">Earn 5% commission when your referrals make successful investments</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs for Referrals and Commissions -->
    <div class="row">
        <div class="col-12">
            <div class="card adminuiux-card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="referralTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="referrals-tab" data-bs-toggle="tab" data-bs-target="#referrals-tab-pane" type="button" role="tab" aria-controls="referrals-tab-pane" aria-selected="true">My Referrals</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="commissions-tab" data-bs-toggle="tab" data-bs-target="#commissions-tab-pane" type="button" role="tab" aria-controls="commissions-tab-pane" aria-selected="false">Commission History</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="referralTabsContent">
                        <!-- Referrals Tab -->
                        <div class="tab-pane fade show active" id="referrals-tab-pane" role="tabpanel" aria-labelledby="referrals-tab" tabindex="0">
                            <?php if ($referrals->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Registration Date</th>
                                                <th>Commission Earned</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($referral = $referrals->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($referral['first_name'] . ' ' . $referral['last_name']) ?></td>
                                                    <td><?= htmlspecialchars($referral['email']) ?></td>
                                                    <td><?= date('M d, Y', strtotime($referral['created_at'])) ?></td>
                                                    <td><?= $user_currency . number_format($referral['commission_earned'], 2) ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <div class="avatar avatar-70 bg-light rounded-circle mx-auto mb-3">
                                        <i class="bi bi-people text-muted"></i>
                                    </div>
                                    <h5>No Referrals Yet</h5>
                                    <p class="text-muted">Share your referral link to invite friends and start earning commissions.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Commissions Tab -->
                        <div class="tab-pane fade" id="commissions-tab-pane" role="tabpanel" aria-labelledby="commissions-tab" tabindex="0">
                            <?php if ($recent_commissions->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Referred User</th>
                                                <th>Amount</th>
                                                <th>Source</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($commission = $recent_commissions->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($commission['first_name'] . ' ' . $commission['last_name']) ?></td>
                                                    <td><?= $user_currency . number_format($commission['amount'], 2) ?></td>
                                                    <td>
                                                        <?php 
                                                            switch($commission['source_type']) {
                                                                case 'investment':
                                                                    echo '<span class="badge bg-primary">Investment</span>';
                                                                    break;
                                                                case 'deposit':
                                                                    echo '<span class="badge bg-success">Deposit</span>';
                                                                    break;
                                                                case 'staking':
                                                                    echo '<span class="badge bg-info">Staking</span>';
                                                                    break;
                                                                default:
                                                                    echo '<span class="badge bg-secondary">Other</span>';
                                                            }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($commission['status'] == 'pending'): ?>
                                                            <span class="badge bg-warning">Pending</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-success">Paid</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= date('M d, Y', strtotime($commission['created_at'])) ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <div class="avatar avatar-70 bg-light rounded-circle mx-auto mb-3">
                                        <i class="bi bi-cash text-muted"></i>
                                    </div>
                                    <h5>No Commission History</h5>
                                    <p class="text-muted">You will see your commission history here once your referrals start investing.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyReferralLink() {
    var copyText = document.getElementById("referral-link");
    copyText.select();
    copyText.setSelectionRange(0, 99999); // For mobile devices
    
    navigator.clipboard.writeText(copyText.value);
    
    var copyButton = document.getElementById("copy-button");
    var originalHTML = copyButton.innerHTML;
    
    copyButton.innerHTML = '<i class="bi bi-check"></i> Copied!';
    copyButton.classList.remove("btn-primary");
    copyButton.classList.add("btn-success");
    
    setTimeout(function() {
        copyButton.innerHTML = originalHTML;
        copyButton.classList.remove("btn-success");
        copyButton.classList.add("btn-primary");
    }, 2000);
}
</script>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/footer.php'; ?> 