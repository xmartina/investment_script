<?php
// Admin Page Content Editor
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Edit Page Content";
$current_page = "front_pages.php";
$message = "";
$error = "";

// Get list of front pages
$pages = [
    'home' => ['name' => 'Home Page', 'file' => '/pages/homepage.php'],
    'about' => ['name' => 'About Us', 'file' => '/pages/aboutpage.php'],
    'faq' => ['name' => 'FAQ Page', 'file' => '/pages/faqpage.php'],
    'contact' => ['name' => 'Contact Us', 'file' => '/pages/contactpage.php'],
    'terms' => ['name' => 'Terms and Conditions', 'file' => '/pages/termspage.php'],
    'privacy' => ['name' => 'Privacy Policy', 'file' => '/pages/privacypage.php']
];

// Validate page ID
if (!isset($_GET['id']) || empty($_GET['id']) || !array_key_exists($_GET['id'], $pages)) {
    header("Location: /admin/front_pages.php");
    exit();
}

$page_id = $_GET['id'];
$page_info = $pages[$page_id];
$file_path = $_SERVER['DOCUMENT_ROOT'] . $page_info['file'];

// Load content from database or file
$page_content = '';
$page_title = '';
$page_meta_description = '';
$page_meta_keywords = '';

// Check if content exists in the database
$stmt = $conn_back->prepare("SELECT * FROM front_pages WHERE page_id = ?");
$stmt->bind_param("s", $page_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $page_data = $result->fetch_assoc();
    $page_content = $page_data['content'];
    $page_title = $page_data['title'];
    $page_meta_description = $page_data['meta_description'];
    $page_meta_keywords = $page_data['meta_keywords'];
} else {
    // If not in database, try to get from file
    if (file_exists($file_path)) {
        $page_content = file_get_contents($file_path);
        
        // Extract the title from the content if possible
        preg_match('/<title>(.*?)<\/title>/i', $page_content, $title_matches);
        if (!empty($title_matches[1])) {
            $page_title = $title_matches[1];
        } else {
            $page_title = $page_info['name'];
        }
        
        // Extract meta description if possible
        preg_match('/<meta\s+name=["\']description["\']\s+content=["\'](.*?)["\']/i', $page_content, $desc_matches);
        if (!empty($desc_matches[1])) {
            $page_meta_description = $desc_matches[1];
        }
        
        // Extract meta keywords if possible
        preg_match('/<meta\s+name=["\']keywords["\']\s+content=["\'](.*?)["\']/i', $page_content, $keyword_matches);
        if (!empty($keyword_matches[1])) {
            $page_meta_keywords = $keyword_matches[1];
        }
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_page'])) {
    $new_content = $_POST['page_content'];
    $new_title = $_POST['page_title'];
    $new_meta_description = $_POST['meta_description'];
    $new_meta_keywords = $_POST['meta_keywords'];
    
    // Save to database
    if ($result->num_rows > 0) {
        // Update existing record
        $update_stmt = $conn_back->prepare("UPDATE front_pages SET content = ?, title = ?, meta_description = ?, meta_keywords = ?, updated_at = NOW() WHERE page_id = ?");
        $update_stmt->bind_param("sssss", $new_content, $new_title, $new_meta_description, $new_meta_keywords, $page_id);
        
        if ($update_stmt->execute()) {
            $message = "Page content updated successfully!";
            
            // Log admin activity
            $admin_id = $_SESSION['admin_id'];
            $action = "Updated " . $page_info['name'] . " content";
            $ip = $_SERVER['REMOTE_ADDR'];
            
            $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
            $log_stmt->bind_param("iss", $admin_id, $action, $ip);
            $log_stmt->execute();
        } else {
            $error = "Error updating page content: " . $update_stmt->error;
        }
    } else {
        // Insert new record
        $insert_stmt = $conn_back->prepare("INSERT INTO front_pages (page_id, title, content, meta_description, meta_keywords, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $insert_stmt->bind_param("sssss", $page_id, $new_title, $new_content, $new_meta_description, $new_meta_keywords);
        
        if ($insert_stmt->execute()) {
            $message = "Page content saved successfully!";
            
            // Log admin activity
            $admin_id = $_SESSION['admin_id'];
            $action = "Created " . $page_info['name'] . " content";
            $ip = $_SERVER['REMOTE_ADDR'];
            
            $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
            $log_stmt->bind_param("iss", $admin_id, $action, $ip);
            $log_stmt->execute();
        } else {
            $error = "Error saving page content: " . $insert_stmt->error;
        }
    }
    
    // Also update the file for compatibility with direct file access
    if (empty($error)) {
        // Only update file if database update was successful
        file_put_contents($file_path, $new_content);
        
        // Reload page data
        $page_content = $new_content;
        $page_title = $new_title;
        $page_meta_description = $new_meta_description;
        $page_meta_keywords = $new_meta_keywords;
    }
}

include_once __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Page: <?= htmlspecialchars($page_info['name']) ?></h1>
        <div>
            <a href="/admin/front_pages.php" class="btn btn-secondary mr-2">
                <i class="fas fa-arrow-left mr-1"></i> Back to Pages
            </a>
            <a href="/<?= $page_id == 'home' ? '' : $page_id ?>" target="_blank" class="btn btn-info">
                <i class="fas fa-eye mr-1"></i> View Page
            </a>
        </div>
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

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Page Content</h6>
        </div>
        <div class="card-body">
            <form method="post" action="">
                <div class="form-group">
                    <label for="page_title"><strong>Page Title</strong></label>
                    <input type="text" class="form-control" id="page_title" name="page_title" value="<?= htmlspecialchars($page_title) ?>" required>
                    <small class="text-muted">This appears in the browser tab title and search results.</small>
                </div>
                
                <div class="form-group">
                    <label for="meta_description"><strong>Meta Description</strong></label>
                    <textarea class="form-control" id="meta_description" name="meta_description" rows="2"><?= htmlspecialchars($page_meta_description) ?></textarea>
                    <small class="text-muted">Brief description for search engines (recommended 150-160 characters).</small>
                </div>
                
                <div class="form-group">
                    <label for="meta_keywords"><strong>Meta Keywords</strong></label>
                    <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" value="<?= htmlspecialchars($page_meta_keywords) ?>">
                    <small class="text-muted">Comma-separated keywords related to this page.</small>
                </div>
                
                <div class="form-group">
                    <label for="page_content"><strong>Page Content (HTML)</strong></label>
                    <textarea class="form-control" id="page_content" name="page_content" rows="25"><?= htmlspecialchars($page_content) ?></textarea>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i> This editor allows you to edit the full HTML of the page. Be careful with your changes to avoid breaking the page structure.
                </div>
                
                <div class="form-group text-right">
                    <button type="submit" name="save_page" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Initialize WYSIWYG Editor -->
<script src="https://cdn.ckeditor.com/4.16.2/standard-all/ckeditor.js"></script>
<script>
$(document).ready(function() {
    CKEDITOR.replace('page_content', {
        height: 500,
        filebrowserUploadUrl: '/admin/upload_image.php',
        allowedContent: true,
        extraPlugins: 'codesnippet,sourcedialog'
    });
});
</script>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 