<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once 'db.php';

//frontend config
$sql_front = "SELECT * FROM general_settings";
$settings_result = $conn_front->query($sql_front);
$row = $settings_result->fetch_assoc();
$site_link = $row['site_link'];
$site_name = $row['site_name'];
$site_email = $row['site_email'];
$site_address = $row['site_address'];
$site_phone = $row['site_phone'];
$site_logo = $row['site_logo'];
$site_favicon = $row['site_favicon'];
$site_logo = $site_link . '/front_assets/images/' .$site_logo;
$site_favicon = $site_link. '/front_assets/images/' .$site_favicon;

$sql_front = "SELECT * FROM front_menus";
$menus_result = $conn_front->query($sql_front);

$sql_front = "SELECT * FROM social_media";
$social_result = $conn_front->query($sql_front);


const web_url = 'exodusaipro.online';
const link = 'https://exodusaipro.online';
$active_url = $_SERVER['REQUEST_URI'];

//backend config
if (isset($_POST['logout'])) {
    unset($_SESSION['user']); // remove specific session variable
    session_destroy(); // optional: completely destroy session
    header("Location: /user/login"); // optional: redirect after logout
    exit;
}


include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/front_functions.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/back_functions.php';