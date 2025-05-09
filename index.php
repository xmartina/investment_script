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
    include $_SERVER['DOCUMENT_ROOT'] . '/user/withdraw.php';
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



dispatch();