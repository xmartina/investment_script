<?php
// Get current page title based on the file name
$page_title = '';
$breadcrumbs = [];

// Make sure siteLink is defined
if (!isset($siteLink) || empty($siteLink)) {
    // Attempt to get site URL from server variables
    $siteLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
}

// Try to determine the current page
try {
    switch (basename($_SERVER['PHP_SELF'])) {
        case 'index.php':
            $page_title = 'Dashboard';
            $breadcrumbs = [
                ['title' => 'Dashboard', 'url' => '#']
            ];
            break;
        case 'users.php':
            $page_title = 'Users Management';
            $breadcrumbs = [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'Users', 'url' => '#']
            ];
            break;
        case 'deposits.php':
            $page_title = 'Deposits Management';
            $breadcrumbs = [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'Deposits', 'url' => '#']
            ];
            break;
        case 'withdrawals.php':
            $page_title = 'Withdrawals Management';
            $breadcrumbs = [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'Withdrawals', 'url' => '#']
            ];
            break;
        case 'investments.php':
            $page_title = 'Investments Management';
            $breadcrumbs = [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'Investments', 'url' => '#']
            ];
            break;
        case 'investment_plans.php':
            $page_title = 'Investment Plans';
            $breadcrumbs = [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'Investment Plans', 'url' => '#']
            ];
            break;
        case 'transactions.php':
            $page_title = 'Transaction History';
            $breadcrumbs = [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'Transactions', 'url' => '#']
            ];
            break;
        case 'referrals.php':
            $page_title = 'Referral Management';
            $breadcrumbs = [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'Referrals', 'url' => '#']
            ];
            break;
        case 'settings.php':
            $page_title = 'System Settings';
            $breadcrumbs = [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'Settings', 'url' => '#']
            ];
            break;
        case 'admins.php':
            $page_title = 'Admin Users';
            $breadcrumbs = [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'Admin Users', 'url' => '#']
            ];
            break;
        case 'logs.php':
            $page_title = 'Activity Logs';
            $breadcrumbs = [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'Activity Logs', 'url' => '#']
            ];
            break;
        case 'profile.php':
            $page_title = 'My Profile';
            $breadcrumbs = [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'My Profile', 'url' => '#']
            ];
            break;
        default:
            $page_title = 'Admin Panel';
            $breadcrumbs = [
                ['title' => 'Dashboard', 'url' => 'index.php']
            ];
    }
} catch (Exception $e) {
    $page_title = 'Admin Panel';
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => 'index.php']
    ];
    error_log("Error in breadcrumb.php: " . $e->getMessage());
}
?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="d-flex align-items-center">
        <div class="me-auto">
            <h3 class="page-title"><?php echo htmlspecialchars($page_title); ?></h3>
            <div class="d-inline-block align-items-center">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?=$siteLink?>/admin"><i class="mdi mdi-home-outline"></i></a></li>
                        <?php foreach ($breadcrumbs as $index => $item): ?>
                            <?php if ($index === count($breadcrumbs) - 1): ?>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($item['title']); ?></li>
                            <?php else: ?>
                                <li class="breadcrumb-item"><a href="<?=$siteLink?>/admin/<?php echo str_replace('.php', '', htmlspecialchars($item['url'])); ?>"><?php echo htmlspecialchars($item['title']); ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Alert messages -->
<?php 
// Check if displayAlert function exists before calling it
if (function_exists('displayAlert')) {
    displayAlert();
}
?>
