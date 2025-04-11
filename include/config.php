<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once 'db.php';

$sql_front = "SELECT * FROM general_settings";
$result = $conn_front->query($sql_front);
$row = $result->fetch_assoc();
$site_link = $row['site_link'];
$site_name = $row['site_name'];
$site_email = $row['site_email'];
$site_phone = $row['site_phone'];
$site_logo = $row['site_logo'];
$site_favicon = $row['site_favicon'];

const web_url = 'exodusaipro.online';
const link = 'https://exodusaipro.online';