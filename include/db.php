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

