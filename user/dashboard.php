<?php
session_start();

$page_name = 'Dashboard';
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/header.php';

if (!isset($_SESSION['user'])) {
    header('Location: /user/login');
    exit;
}
?>