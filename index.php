<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require $_SERVER['DOCUMENT_ROOT'] . '/routes.php';

get('/', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/pages/homepage.php';
});

get('/about', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/about/index.php';
});

get('/user/login', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/login.php';
});

get('/login', function() {
    include $_SERVER['DOCUMENT_ROOT'] . '/user/login.php';
});

dispatch();