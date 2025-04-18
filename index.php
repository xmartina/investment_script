<?php
require 'routes.php';

get('/', function() {
    include 'pages/homepage.php';
});

get('/about', function() {
    include 'pages/about.php';
});

get('/user/{id}', function($id) {
    include 'pages/user.php'; // Pass ID using $_GET or pass directly
});

dispatch();
