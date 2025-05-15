<?php
// Front Page Management Installation Guide
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Install Front Page Management";
$current_page = "front_pages.php";

include_once __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Front Page Management Installation</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Installation Steps</h6>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <p>This guide will help you set up the front page management system. Follow these steps in order:</p>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold">Step 1: Create Database Tables</h6>
                        </div>
                        <div class="card-body">
                            <p>First, create the necessary database tables for the front page management system.</p>
                            <a href="/admin/create_front_page_tables.php" class="btn btn-primary">Create Database Tables</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold">Step 2: Manage Front Pages</h6>
                        </div>
                        <div class="card-body">
                            <p>After creating the database tables, you can manage your website's front pages.</p>
                            <a href="/admin/front_pages.php" class="btn btn-primary">Go to Front Pages Management</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold">Step 3: Configure Site Settings</h6>
                        </div>
                        <div class="card-body">
                            <p>Configure general site settings, contact information, and social media links.</p>
                            <a href="/admin/site_settings.php" class="btn btn-primary">Configure Site Settings</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold">Step 4: Customize Header & Footer</h6>
                        </div>
                        <div class="card-body">
                            <p>Customize your website's header and footer to match your brand.</p>
                            <a href="/admin/header_footer.php" class="btn btn-primary">Customize Header & Footer</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold">Step 5: Set Up Navigation Menu</h6>
                        </div>
                        <div class="card-body">
                            <p>Create and manage your website's navigation menu structure.</p>
                            <a href="/admin/navigation.php" class="btn btn-primary">Manage Navigation Menu</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Important Notes</h6>
        </div>
        <div class="card-body">
            <ul>
                <li>Make sure your <code>/layout</code> and <code>/pages</code> directories are writable by the web server.</li>
                <li>If you modify files directly outside of this admin panel, your changes may be overwritten.</li>
                <li>Always back up your database and files before making major changes.</li>
                <li>For optimal results, use the provided tools in the admin panel instead of editing files manually.</li>
            </ul>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 