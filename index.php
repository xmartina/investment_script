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
get('/plans', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/pages/plans.php';
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
get('/admin/staking', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/staking.php';
});
get('/admin/staking.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/staking.php';
});
get('/admin/staking_plans', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/staking_plans.php';
});
get('/admin/staking_plans.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/staking_plans.php';
});
get('/admin/staking_rewards', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/staking_rewards.php';
});
get('/admin/staking_rewards.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/staking_rewards.php';
});
get('/admin/create_staking', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/create_staking.php';
});
get('/admin/create_staking.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/create_staking.php';
});
get('/admin/create_investment', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/create_investment.php';
});
get('/admin/create_investment.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/create_investment.php';
});
get('/admin/withdrawal_methods', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/withdrawal_methods.php';
});
get('/admin/withdrawal_methods.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/withdrawal_methods.php';
});

// New admin pages for managing positions and balances
get('/admin/complete_positions', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/complete_positions.php';
});
get('/admin/complete_positions.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/complete_positions.php';
});
get('/admin/adjust_balance', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/adjust_balance.php';
});
get('/admin/adjust_balance.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/adjust_balance.php';
});
get('/admin/create_completed_position', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/create_completed_position.php';
});
get('/admin/create_completed_position.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/create_completed_position.php';
});

// Wallet Addresses Management
get('/admin/wallet_addresses', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/wallet_addresses.php';
});
get('/admin/wallet_addresses.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/wallet_addresses.php';
});
get('/admin/create_wallet_addresses_table', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/create_wallet_addresses_table.php';
});
get('/admin/create_wallet_addresses_table.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/create_wallet_addresses_table.php';
});

// Front Pages Management
get('/admin/front_pages', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/front_pages.php';
});
get('/admin/front_pages.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/front_pages.php';
});
get('/admin/edit_page.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/edit_page.php';
});
get('/admin/header_footer', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/header_footer.php';
});
get('/admin/header_footer.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/header_footer.php';
});
get('/admin/navigation', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/navigation.php';
});
get('/admin/navigation.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/navigation.php';
});
get('/admin/site_settings', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/site_settings.php';
});
get('/admin/site_settings.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/site_settings.php';
});
get('/admin/create_front_page_tables', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/create_front_page_tables.php';
});
get('/admin/create_front_page_tables.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/create_front_page_tables.php';
});
get('/admin/install_front_pages', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/install_front_pages.php';
});
get('/admin/install_front_pages.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/install_front_pages.php';
});

// Wallet addresses installation helper
get('/install_wallet_addresses', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/install_wallet_addresses.php';
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
post('/admin/staking', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/staking.php';
});
post('/admin/staking.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/staking.php';
});
post('/admin/staking_plans', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/staking_plans.php';
});
post('/admin/staking_plans.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/staking_plans.php';
});
post('/admin/staking_rewards', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/staking_rewards.php';
});
post('/admin/staking_rewards.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/staking_rewards.php';
});
post('/admin/create_staking', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/create_staking.php';
});
post('/admin/create_staking.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/create_staking.php';
});
post('/admin/create_investment', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/create_investment.php';
});
post('/admin/create_investment.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/create_investment.php';
});
post('/admin/withdrawal_methods', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/withdrawal_methods.php';
});
post('/admin/withdrawal_methods.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/withdrawal_methods.php';
});

post('/admin/wallet_addresses', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/wallet_addresses.php';
});
post('/admin/wallet_addresses.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/wallet_addresses.php';
});

// Front Pages Management POST Routes
post('/admin/front_pages', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/front_pages.php';
});
post('/admin/front_pages.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/front_pages.php';
});
post('/admin/edit_page.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/edit_page.php';
});
post('/admin/header_footer', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/header_footer.php';
});
post('/admin/header_footer.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/header_footer.php';
});
post('/admin/navigation', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/navigation.php';
});
post('/admin/navigation.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/navigation.php';
});
post('/admin/site_settings', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/site_settings.php';
});
post('/admin/site_settings.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/site_settings.php';
});
post('/admin/upload_image.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/upload_image.php';
});
post('/plans', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/pages/plans.php';
});

// POST routes for new admin pages
post('/admin/complete_positions', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/complete_positions.php';
});
post('/admin/complete_positions.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/complete_positions.php';
});
post('/admin/adjust_balance', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/adjust_balance.php';
});
post('/admin/adjust_balance.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/adjust_balance.php';
});
post('/admin/create_completed_position', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/create_completed_position.php';
});
post('/admin/create_completed_position.php', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/create_completed_position.php';
});

// Dispatch the routes
dispatch();