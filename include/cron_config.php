<?php
/**
 * Simplified configuration file for cron jobs
 * This file only loads the essential components needed for cron jobs
 */

// Load database connection
include_once(__DIR__ . '/db.php');

// Skip loading frontend components that aren't needed for cron jobs
// We only need the database connections

// Define constants that might be needed
const web_url = 'exodusaipro.online';
const link = 'https://exodusaipro.online';

// Define essential variables needed by the functions
$site_link = 'https://exodusaipro.online';

// Skip the session handling, menus, social media, etc.
// Those aren't needed for a cron job

// Include essential functions only
if (file_exists(__DIR__ . '/../functions/back_functions.php')) {
    include_once __DIR__ . '/../functions/back_functions.php';
} 