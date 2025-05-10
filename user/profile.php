<?php
// Profile page
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /user/login");
    exit();
}

$user_id = $_SESSION['user_id'];
$page_name = "My Profile";
$css_files = [];
$js_files = [];

// Get full user data
$stmt = $conn_back->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Format profile photo
$profile_photo = !empty($user_data['profile_photo']) ? $user_data['profile_photo'] : '/back_assets/img/users/profile_photo/default_photo.jpg';
if (strpos($profile_photo, 'http') !== 0 && strpos($profile_photo, '/') !== 0) {
    $profile_photo = '/' . $profile_photo;
}

// Handle encoding of spaces in profile photo URL
$profile_photo = preg_replace_callback('/\s/', function($match) {
    return rawurlencode($match[0]);
}, $profile_photo);

// Get user stats
// 1. Total investments
$stmt = $conn_back->prepare("SELECT COUNT(*) as count, SUM(amount) as total FROM investments WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$investments = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 2. Total stakings
$stmt = $conn_back->prepare("SELECT COUNT(*) as count, SUM(amount) as total FROM staking WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stakings = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 3. Total transactions
$stmt = $conn_back->prepare("SELECT COUNT(*) as count FROM transactions WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transactions_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// 4. Recent activities (last 5 transactions)
$stmt = $conn_back->prepare("
    SELECT * FROM transactions 
    WHERE user_id = ? 
    ORDER BY date_time DESC 
    LIMIT 5
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_activities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Include header
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/header.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/breadcumb.php';
?>

<div class="container-fluid px-4 py-4">
    <div class="row">
        <!-- Profile Card -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="position-relative mx-auto mb-4">
                        <div class="avatar avatar-100 rounded-circle coverimg mx-auto" style="width: 150px; height: 150px;">
                            <img src="<?= htmlspecialchars($profile_photo) ?>" alt="Profile Photo" 
                                 onerror="this.onerror=null; this.src='<?= $default_photo ?>';" 
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <a href="/user/settings" class="position-absolute bottom-0 end-0 btn btn-sm btn-primary rounded-circle">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>
                    
                    <h4 class="fw-bold mb-1"><?= htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']) ?></h4>
                    <p class="text-muted mb-3"><?= htmlspecialchars($user_data['email']) ?></p>
                    
                    <div class="d-flex justify-content-center">
                        <a href="/user/settings" class="btn btn-primary">
                            <i class="bi bi-gear me-2"></i> Edit Profile
                        </a>
                    </div>
                </div>
                
                <div class="card-footer bg-light">
                    <div class="row text-center">
                        <div class="col-4 border-end">
                            <h5 class="mb-0"><?= number_format($investments['count'] ?? 0) ?></h5>
                            <small class="text-muted">Investments</small>
                        </div>
                        <div class="col-4 border-end">
                            <h5 class="mb-0"><?= number_format($stakings['count'] ?? 0) ?></h5>
                            <small class="text-muted">Stakings</small>
                        </div>
                        <div class="col-4">
                            <h5 class="mb-0"><?= number_format($transactions_count ?? 0) ?></h5>
                            <small class="text-muted">Transactions</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Account Details Card -->
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Account Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="small text-muted d-block">Full Name</label>
                        <div class="fw-medium"><?= htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']) ?></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="small text-muted d-block">Email Address</label>
                        <div class="fw-medium"><?= htmlspecialchars($user_data['email']) ?></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="small text-muted d-block">Phone Number</label>
                        <div class="fw-medium"><?= htmlspecialchars($user_data['phone']) ?></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="small text-muted d-block">Preferred Currency</label>
                        <div class="fw-medium"><?= htmlspecialchars($user_data['currency']) ?></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="small text-muted d-block">Account Created</label>
                        <div class="fw-medium"><?= date('F j, Y', strtotime($user_data['created_at'])) ?></div>
                    </div>
                    
                    <?php if(!empty($user_data['referral_code'])): ?>
                    <div class="mb-3">
                        <label class="small text-muted d-block">Referral Code</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user_data['referral_code']) ?>" id="referralCode" readonly>
                            <button class="btn btn-outline-primary" type="button" onclick="copyReferralCode()">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Account Balance Card -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Account Balance</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Main Balance</h6>
                                    <h3 class="mb-0">
                                        <?php 
                                        $currency_symbol = [
                                            'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥',
                                            'CAD' => 'C$', 'AUD' => 'A$', 'NGN' => '₦', 'CHF' => 'CHF'
                                        ][$user_data['currency']] ?? '$';
                                        echo $currency_symbol . number_format($user_data['main_balance'], 2);
                                        ?>
                                    </h3>
                                    <a href="/user/deposit" class="btn btn-light btn-sm mt-3">Deposit Funds</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Investment Balance</h6>
                                    <h3 class="mb-0">
                                        <?= $currency_symbol . number_format($user_data['investment_balance'], 2) ?>
                                    </h3>
                                    <a href="/user/investment_plans" class="btn btn-light btn-sm mt-3">Invest More</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Staking Balance</h6>
                                    <h3 class="mb-0">
                                        <?= $currency_symbol . number_format($user_data['staking_balance'], 2) ?>
                                    </h3>
                                    <a href="/user/staking" class="btn btn-light btn-sm mt-3">Stake More</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity Card -->
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Activities</h5>
                    <a href="/user/transactions" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if(empty($recent_activities)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-clock-history fs-1 text-muted"></i>
                        <p class="mt-3">No recent activities found</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Transaction ID</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_activities as $activity): ?>
                                <tr>
                                    <td><?= htmlspecialchars($activity['reference_id']) ?></td>
                                    <td>
                                        <?php
                                        $type_classes = [
                                            'deposit' => 'text-success',
                                            'withdraw' => 'text-danger',
                                            'investment' => 'text-primary',
                                            'stake' => 'text-info'
                                        ];
                                        $type_class = $type_classes[$activity['transaction_type']] ?? 'text-secondary';
                                        ?>
                                        <span class="<?= $type_class ?>"><?= ucfirst(htmlspecialchars($activity['transaction_type'])) ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $currency_symbol = [
                                            'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥',
                                            'CAD' => 'C$', 'AUD' => 'A$', 'NGN' => '₦', 'CHF' => 'CHF'
                                        ][$activity['currency']] ?? '$';
                                        echo $currency_symbol . number_format($activity['amount'], 2);
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status_classes = [
                                            'pending' => 'badge bg-warning',
                                            'approved' => 'badge bg-success',
                                            'declined' => 'badge bg-danger',
                                            'completed' => 'badge bg-success',
                                            'running' => 'badge bg-primary'
                                        ];
                                        $status_class = $status_classes[$activity['status']] ?? 'badge bg-secondary';
                                        ?>
                                        <span class="<?= $status_class ?>"><?= ucfirst(htmlspecialchars($activity['status'])) ?></span>
                                    </td>
                                    <td><?= date('M d, Y H:i', strtotime($activity['date_time'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyReferralCode() {
    const referralCode = document.getElementById('referralCode');
    referralCode.select();
    document.execCommand('copy');
    
    // Show tooltip or notification
    alert('Referral code copied to clipboard');
}
</script>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/footer.php'; ?>
