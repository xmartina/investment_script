<?php
// Get current page title based on the file name
$page_title = '';
$breadcrumbs = [];

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
?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="d-flex align-items-center">
        <div class="me-auto">
            <h3 class="page-title"><?php echo $page_title; ?></h3>
            <div class="d-inline-block align-items-center">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?=$siteLink?>/admin"><i class="mdi mdi-home-outline"></i></a></li>
                        <?php foreach ($breadcrumbs as $index => $item): ?>
                            <?php if ($index === count($breadcrumbs) - 1): ?>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo $item['title']; ?></li>
                            <?php else: ?>
                                <li class="breadcrumb-item"><a href="<?=$siteLink?>/admin/<?php echo $item['url']; ?>"><?php echo $item['title']; ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Alert messages -->
<?php displayAlert(); ?>
