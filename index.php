<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require $_SERVER['DOCUMENT_ROOT'] . '/routes.php';

//Landing page
get('/', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/pages/homepage.php';
});
get('/about', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/pages/aboutpage.php';
});
get('/faq', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/pages/faqpage.php';
});
get('/contact', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/pages/contactpage.php';
});

//User Dashboard
get('/user/login', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/auth/login.php';
});
get('/login', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/auth/login.php';
});
get('/user/register', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/auth/register.php';
});
get('/register', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/auth/register.php';
});
get('/user/dashboard', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/dashboard.php';
});
get('/user/staking', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/staking/index.php';
});
get('/user/staking/dashboard', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/staking/dashboard.php';
});
get('/user/staking/rewards', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/staking/rewards.php';
});
get('/user/staking/details', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/staking/details.php';
});
get('/user/staking/unstake', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/staking/unstake.php';
});
get('/user/staking/toggle_compound', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/staking/toggle_compound.php';
});
get('/user/profile', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/profile.php';
});
get('/user/settings', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/settings.php';
});
get('/user/deposit', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/deposit.php';
});
get('/user/transactions', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/transactions.php';
});
get('/user/withdraw', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/withdrawal.php';
});
get('/user/withdrawal', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/withdrawal.php';
});
get('/user/withdrawal_methods', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/withdrawal_methods.php';
});
get('/user/referral', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/referral.php';
});
get('/user/investment_plans', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/investment/index.php';
});
get('/user/investment/create_investment_plans_table', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/investment/create_investment_plans_table.php';
});
get('/user/investment/my_investments', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/investment/investments.php';
});
get('/db_migration', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/db_migration.php';
});
get('/update_investment_plans', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/update_investment_plans.php';
});
get('/update_investment_returns', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/update_investment_returns.php';
});
get('/update_staking_tables', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/update_staking_tables.php';
});
get('/update_withdrawal_tables', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/update_withdrawal_tables.php';
});
get('/update_referral_tables', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/update_referral_tables.php';
});

// Admin routes
get('/admin', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/index.php';
});
get('/admin/', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/index.php';
});
get('/admin/index.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/index.php';
});
get('/admin/login', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/login.php';
});
get('/admin/login.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/login.php';
});
get('/admin/logout', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/logout.php';
});
get('/admin/logout.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/logout.php';
});
get('/admin/setup', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/setup.php';
});
get('/admin/setup.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/setup.php';
});
get('/admin/db_init', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/db_init.php';
});
get('/admin/db_init.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/db_init.php';
});
get('/admin/users', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/users.php';
});
get('/admin/users.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/users.php';
});
get('/admin/user_detail', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/user_detail.php';
});
get('/admin/user_detail.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/user_detail.php';
});
get('/admin/withdrawals', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/withdrawals.php';
});
get('/admin/withdrawals.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/withdrawals.php';
});
get('/admin/deposits', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/deposits.php';
});
get('/admin/deposits.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/deposits.php';
});
get('/admin/investments', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/investments.php';
});
get('/admin/investments.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/investments.php';
});
get('/admin/investment_plans', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/investment_plans.php';
});
get('/admin/investment_plans.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/investment_plans.php';
});
get('/admin/transactions', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/transactions.php';
});
get('/admin/transactions.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/transactions.php';
});
get('/admin/referrals', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/referrals.php';
});
get('/admin/referrals.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/referrals.php';
});
get('/admin/settings', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/settings.php';
});
get('/admin/settings.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/settings.php';
});
get('/admin/admins', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/admins.php';
});
get('/admin/admins.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/admins.php';
});
get('/admin/logs', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/logs.php';
});
get('/admin/logs.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/logs.php';
});
get('/admin/profile', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/profile.php';
});
get('/admin/profile.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/profile.php';
});

// Add these to your index.php
post('/user/login', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/auth/login.php';
});
post('/login', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/auth/login.php';
});
post('/user/login', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/auth/login.php';
});
post('/login', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/auth/login.php';
});
post('/user/staking', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/staking/index.php';
});
post('/user/staking/dashboard', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/staking/dashboard.php';
});
post('/user/staking/rewards', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/staking/rewards.php';
});
post('/user/staking/details', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/staking/details.php';
});
post('/user/staking/unstake', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/staking/unstake.php';
});
post('/user/register', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/auth/register.php';
});
post('/register', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/auth/register.php';
});
post('/user/dashboard', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/dashboard.php';
});
post('/user/deposit', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/deposit.php';
});
post('/user/settings', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/settings.php';
});
post('/user/withdraw', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/withdrawal.php';
});
post('/user/withdrawal', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/withdrawal.php';
});
post('/user/withdrawal_methods', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/withdrawal_methods.php';
});
post('/user/referral', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/referral.php';
});
post('/user/investment_plans', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/investment/index.php';
});
post('/user/investment/create_investment_plans_table', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/investment/create_investment_plans_table.php';
});
post('/user/investment/my_investments', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/investment/investments.php';
});
post('/db_migration', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/db_migration.php';
});
post('/update_investment_plans', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/update_investment_plans.php';
});
post('/update_investment_returns', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/update_investment_returns.php';
});
post('/update_staking_tables', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/update_staking_tables.php';
});
post('/update_withdrawal_tables', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/update_withdrawal_tables.php';
});
post('/update_referral_tables', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/update_referral_tables.php';
});

// Admin POST routes
post('/admin/login', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/login.php';
});
post('/admin/login.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/login.php';
});
post('/admin/users', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/users.php';
});
post('/admin/users.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/users.php';
});
post('/admin/withdrawals', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/withdrawals.php';
});
post('/admin/withdrawals.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/withdrawals.php';
});
post('/admin/deposits', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/deposits.php';
});
post('/admin/deposits.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/deposits.php';
});
post('/admin/investments', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/investments.php';
});
post('/admin/investments.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/investments.php';
});
post('/admin/investment_plans', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/investment_plans.php';
});
post('/admin/investment_plans.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/investment_plans.php';
});
post('/admin/transactions', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/transactions.php';
});
post('/admin/transactions.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/transactions.php';
});
post('/admin/referrals', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/referrals.php';
});
post('/admin/referrals.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/referrals.php';
});
post('/admin/settings', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/settings.php';
});
post('/admin/settings.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/settings.php';
});
post('/admin/admins', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/admins.php';
});
post('/admin/admins.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/admins.php';
});
post('/admin/profile', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/profile.php';
});
post('/admin/profile.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/profile.php';
});

// Get parts of URL
$parsed_url = parse_url($_SERVER['REQUEST_URI']);

// Get path from URL, or root as a fallback
$path = isset($parsed_url['path']) ? $parsed_url['path'] : '/';

// Remove trailing slashes from path and convert to lowercase
$path = strtolower(rtrim($path, '/'));

// Add root back if path is empty
if ($path == '') {
    $path = '/';
}

// Get array of URL parameters
$parameters = [];
if (isset($parsed_url['query'])) {
    parse_str($parsed_url['query'], $parameters);
}

// Check if the requested URL has a valid route
$route_found = false;
foreach (Route::$routes as $route) {
    // Check if the request method matches the route's method
    if ($route['method'] === $_SERVER['REQUEST_METHOD'] || $route['method'] === 'ANY') {
        // Convert route path to regex pattern for matching
        $pattern = Route::convertPatternToRegex($route['path']);
        
        // Check if the requested path matches the route pattern
        if (preg_match($pattern, $path, $matches)) {
            // Remove the first match (the full path)
            array_shift($matches);
            
            // Store matched route parameters
            $route_parameters = $matches;
            
            // Call the route's callback function
            call_user_func_array($route['callback'], $route_parameters);
            
            $route_found = true;
            break;
        }
    }
}

// Return 404 if no route matches
if (!$route_found) {
    http_response_code(404);
    echo '404 - Page Not Found';
}