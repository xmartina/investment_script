<?php
// Admin Front Pages Management
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Front Pages";
$current_page = "front_pages.php";
$message = "";
$error = "";

// Get list of front pages
$pages = [
    ['id' => 'home', 'name' => 'Home Page', 'file' => '/pages/homepage.php'],
    ['id' => 'about', 'name' => 'About Us', 'file' => '/pages/aboutpage.php'],
    ['id' => 'faq', 'name' => 'FAQ Page', 'file' => '/pages/faqpage.php'],
    ['id' => 'contact', 'name' => 'Contact Us', 'file' => '/pages/contactpage.php'],
    ['id' => 'terms', 'name' => 'Terms and Conditions', 'file' => '/pages/termspage.php'],
    ['id' => 'privacy', 'name' => 'Privacy Policy', 'file' => '/pages/privacypage.php']
];

// Handle page edit redirection
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    foreach ($pages as $page) {
        if ($page['id'] == $edit_id) {
            header("Location: /admin/edit_page.php?id=" . $edit_id);
            exit();
        }
    }
    $error = "Invalid page specified.";
}

include_once __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Front Pages Management</h1>
        <a href="/admin/site_settings" class="btn btn-primary">
            <i class="fas fa-cog mr-2"></i> Site Settings
        </a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Main Pages</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Page</th>
                                    <th>File Path</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pages as $page): ?>
                                    <?php
                                    $file_path = $_SERVER['DOCUMENT_ROOT'] . $page['file'];
                                    $last_updated = file_exists($file_path) ? date("F d, Y H:i:s", filemtime($file_path)) : 'N/A';
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($page['name']) ?></td>
                                        <td><?= htmlspecialchars($page['file']) ?></td>
                                        <td><?= $last_updated ?></td>
                                        <td>
                                            <a href="/admin/edit_page.php?id=<?= $page['id'] ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit Content
                                            </a>
                                            <a href="/<?= $page['id'] == 'home' ? '' : $page['id'] ?>" target="_blank" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Layout Components</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="/admin/header_footer.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Header & Footer
                            <span class="badge badge-primary badge-pill"><i class="fas fa-chevron-right"></i></span>
                        </a>
                        <a href="/admin/navigation.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Navigation Menu
                            <span class="badge badge-primary badge-pill"><i class="fas fa-chevron-right"></i></span>
                        </a>
                        <a href="/admin/slider.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Home Slider
                            <span class="badge badge-primary badge-pill"><i class="fas fa-chevron-right"></i></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Site Information</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="/admin/site_settings.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            General Settings
                            <span class="badge badge-primary badge-pill"><i class="fas fa-chevron-right"></i></span>
                        </a>
                        <a href="/admin/social_media.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Social Media Links
                            <span class="badge badge-primary badge-pill"><i class="fas fa-chevron-right"></i></span>
                        </a>
                        <a href="/admin/contact_info.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Contact Information
                            <span class="badge badge-primary badge-pill"><i class="fas fa-chevron-right"></i></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 