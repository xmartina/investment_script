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
    include $_SERVER['DOCUMENT_ROOT'] . '/user/staking.php';
});
get('/user/profile', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/profile.php';
});
get('/user/settings', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/settings.php';
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
    include $_SERVER['DOCUMENT_ROOT'] . '/user/staking.php';
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
post('/user/settings', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/settings.php';
});
dispatch();