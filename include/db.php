<?php
// Database credentials
$host = 'localhost';
$user = 'summitgu_exodusaipro_front';
$password = 'exodusaipro_front';
$database = 'summitgu_exodusaipro_front';

// Create connection
$conn_front = mysqli_connect($host, $user, $password, $database);

// Check connection
if (!$conn_front) {
    die("Connection failed: " . mysqli_connect_error());
}

// Database credentials
$back_host = 'localhost';
$back_user = 'summitgu_exodusaipro_back';
$back_password = 'exodusaipro_back';
$back_database = 'summitgu_exodusaipro_back';

// Create connection
$conn_back = mysqli_connect($back_host, $back_user, $back_password, $back_database);

// Check connection
if (!$conn_back) {
    die("Connection failed: " . mysqli_connect_error());
}