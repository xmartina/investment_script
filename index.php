<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require $_SERVER['DOCUMENT_ROOT'] . '/routes.php';

get('/', function() {
    include 'pages/homepage.php';
});

get('/about', function() {
    include 'pages/about.php';
});

get('/user/{id}', function($id) {
    echo "User ID: $id";
});

dispatch();

