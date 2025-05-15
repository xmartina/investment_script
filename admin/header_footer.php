<?php
// Admin Header & Footer Management
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Header & Footer Management";
$current_page = "front_pages.php";
$message = "";
$error = "";

// Define the files to edit
$header_file = $_SERVER['DOCUMENT_ROOT'] . '/layout/header.php';
$footer_file = $_SERVER['DOCUMENT_ROOT'] . '/layout/footer.php';

// Load content from database or file
$header_content = '';
$footer_content = '';

// Check if content exists in the database
$stmt = $conn_back->prepare("SELECT * FROM layout_components WHERE component_id IN ('header', 'footer')");
$stmt->execute();
$result = $stmt->get_result();

$db_components = [];
while ($row = $result->fetch_assoc()) {
    $db_components[$row['component_id']] = $row;
}

// Get header content
if (isset($db_components['header'])) {
    $header_content = $db_components['header']['content'];
} else if (file_exists($header_file)) {
    $header_content = file_get_contents($header_file);
}

// Get footer content
if (isset($db_components['footer'])) {
    $footer_content = $db_components['footer']['content'];
} else if (file_exists($footer_file)) {
    $footer_content = file_get_contents($footer_file);
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_header'])) {
        $new_header_content = $_POST['header_content'];
        
        // Save to database
        if (isset($db_components['header'])) {
            // Update existing record
            $update_stmt = $conn_back->prepare("UPDATE layout_components SET content = ?, updated_at = NOW() WHERE component_id = 'header'");
            $update_stmt->bind_param("s", $new_header_content);
            
            if ($update_stmt->execute()) {
                $message = "Header content updated successfully!";
                
                // Log admin activity
                $admin_id = $_SESSION['admin_id'];
                $action = "Updated website header";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                $log_stmt->execute();
            } else {
                $error = "Error updating header content: " . $update_stmt->error;
            }
        } else {
            // Insert new record
            $insert_stmt = $conn_back->prepare("INSERT INTO layout_components (component_id, content, created_at, updated_at) VALUES ('header', ?, NOW(), NOW())");
            $insert_stmt->bind_param("s", $new_header_content);
            
            if ($insert_stmt->execute()) {
                $message = "Header content saved successfully!";
                
                // Log admin activity
                $admin_id = $_SESSION['admin_id'];
                $action = "Created website header";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                $log_stmt->execute();
            } else {
                $error = "Error saving header content: " . $insert_stmt->error;
            }
        }
        
        // Also update the file for compatibility
        if (empty($error)) {
            file_put_contents($header_file, $new_header_content);
            $header_content = $new_header_content;
        }
    } else if (isset($_POST['save_footer'])) {
        $new_footer_content = $_POST['footer_content'];
        
        // Save to database
        if (isset($db_components['footer'])) {
            // Update existing record
            $update_stmt = $conn_back->prepare("UPDATE layout_components SET content = ?, updated_at = NOW() WHERE component_id = 'footer'");
            $update_stmt->bind_param("s", $new_footer_content);
            
            if ($update_stmt->execute()) {
                $message = "Footer content updated successfully!";
                
                // Log admin activity
                $admin_id = $_SESSION['admin_id'];
                $action = "Updated website footer";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                $log_stmt->execute();
            } else {
                $error = "Error updating footer content: " . $update_stmt->error;
            }
        } else {
            // Insert new record
            $insert_stmt = $conn_back->prepare("INSERT INTO layout_components (component_id, content, created_at, updated_at) VALUES ('footer', ?, NOW(), NOW())");
            $insert_stmt->bind_param("s", $new_footer_content);
            
            if ($insert_stmt->execute()) {
                $message = "Footer content saved successfully!";
                
                // Log admin activity
                $admin_id = $_SESSION['admin_id'];
                $action = "Created website footer";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                $log_stmt->execute();
            } else {
                $error = "Error saving footer content: " . $insert_stmt->error;
            }
        }
        
        // Also update the file for compatibility
        if (empty($error)) {
            file_put_contents($footer_file, $new_footer_content);
            $footer_content = $new_footer_content;
        }
    }
}

include_once __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Header & Footer Management</h1>
        <a href="/admin/front_pages.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back to Pages
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

    <!-- Header Tab -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Website Header</h6>
        </div>
        <div class="card-body">
            <form method="post" action="">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i> Edit the website header HTML. Be careful with your changes to avoid breaking the site layout.
                </div>
                
                <div class="form-group">
                    <textarea class="form-control" id="header_content" name="header_content" rows="15"><?= htmlspecialchars($header_content) ?></textarea>
                </div>
                
                <div class="form-group text-right">
                    <button type="submit" name="save_header" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Save Header
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Footer Tab -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Website Footer</h6>
        </div>
        <div class="card-body">
            <form method="post" action="">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i> Edit the website footer HTML. Be careful with your changes to avoid breaking the site layout.
                </div>
                
                <div class="form-group">
                    <textarea class="form-control" id="footer_content" name="footer_content" rows="15"><?= htmlspecialchars($footer_content) ?></textarea>
                </div>
                
                <div class="form-group text-right">
                    <button type="submit" name="save_footer" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Save Footer
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
    CKEDITOR.replace('header_content', {
        height: 300,
        filebrowserUploadUrl: '/admin/upload_image.php',
        allowedContent: true,
        extraPlugins: 'codesnippet,sourcedialog'
    });
    
    CKEDITOR.replace('footer_content', {
        height: 300,
        filebrowserUploadUrl: '/admin/upload_image.php',
        allowedContent: true,
        extraPlugins: 'codesnippet,sourcedialog'
    });
});
</script>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 