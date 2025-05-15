<?php
// Admin Navigation Menu Management
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Navigation Menu";
$current_page = "front_pages.php";
$message = "";
$error = "";

// Define the navigation file
$nav_file = $_SERVER['DOCUMENT_ROOT'] . '/layout/navigation.php';

// Initialize menu items array
$menu_items = [];

// Load menu items from database
$stmt = $conn_back->prepare("SELECT * FROM navigation_menu ORDER BY menu_order ASC");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $menu_items[] = $row;
    }
} else {
    // Default menu items if none in database
    $menu_items = [
        ['id' => 1, 'label' => 'Home', 'url' => '/', 'parent_id' => 0, 'menu_order' => 1, 'status' => 1],
        ['id' => 2, 'label' => 'About', 'url' => '/about', 'parent_id' => 0, 'menu_order' => 2, 'status' => 1],
        ['id' => 3, 'label' => 'FAQ', 'url' => '/faq', 'parent_id' => 0, 'menu_order' => 3, 'status' => 1],
        ['id' => 4, 'label' => 'Contact', 'url' => '/contact', 'parent_id' => 0, 'menu_order' => 4, 'status' => 1]
    ];
}

// Handle menu item operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new menu item
    if (isset($_POST['add_menu_item'])) {
        $label = trim($_POST['menu_label']);
        $url = trim($_POST['menu_url']);
        $parent_id = (int)$_POST['parent_id'];
        $menu_order = (int)$_POST['menu_order'];
        $status = isset($_POST['menu_status']) ? 1 : 0;
        
        if (empty($label) || empty($url)) {
            $error = "Menu label and URL are required fields.";
        } else {
            $insert_stmt = $conn_back->prepare("INSERT INTO navigation_menu (label, url, parent_id, menu_order, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            $insert_stmt->bind_param("ssiii", $label, $url, $parent_id, $menu_order, $status);
            
            if ($insert_stmt->execute()) {
                $message = "Menu item added successfully!";
                
                // Log admin activity
                $admin_id = $_SESSION['admin_id'];
                $action = "Added navigation menu item: " . $label;
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                $log_stmt->execute();
                
                // Refresh the page to show updated menu
                header("Location: /admin/navigation.php?success=added");
                exit();
            } else {
                $error = "Error adding menu item: " . $insert_stmt->error;
            }
        }
    }
    
    // Edit menu item
    if (isset($_POST['edit_menu_item'])) {
        $id = (int)$_POST['menu_id'];
        $label = trim($_POST['menu_label']);
        $url = trim($_POST['menu_url']);
        $parent_id = (int)$_POST['parent_id'];
        $menu_order = (int)$_POST['menu_order'];
        $status = isset($_POST['menu_status']) ? 1 : 0;
        
        if (empty($label) || empty($url)) {
            $error = "Menu label and URL are required fields.";
        } else {
            $update_stmt = $conn_back->prepare("UPDATE navigation_menu SET label = ?, url = ?, parent_id = ?, menu_order = ?, status = ?, updated_at = NOW() WHERE id = ?");
            $update_stmt->bind_param("ssiiii", $label, $url, $parent_id, $menu_order, $status, $id);
            
            if ($update_stmt->execute()) {
                $message = "Menu item updated successfully!";
                
                // Log admin activity
                $admin_id = $_SESSION['admin_id'];
                $action = "Updated navigation menu item: " . $label;
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                $log_stmt->execute();
                
                // Refresh the page to show updated menu
                header("Location: /admin/navigation.php?success=updated");
                exit();
            } else {
                $error = "Error updating menu item: " . $update_stmt->error;
            }
        }
    }
    
    // Delete menu item
    if (isset($_POST['delete_menu_item'])) {
        $id = (int)$_POST['menu_id'];
        
        // First, get the menu item to log
        $get_stmt = $conn_back->prepare("SELECT label FROM navigation_menu WHERE id = ?");
        $get_stmt->bind_param("i", $id);
        $get_stmt->execute();
        $get_result = $get_stmt->get_result();
        $menu_item = $get_result->fetch_assoc();
        
        $delete_stmt = $conn_back->prepare("DELETE FROM navigation_menu WHERE id = ?");
        $delete_stmt->bind_param("i", $id);
        
        if ($delete_stmt->execute()) {
            $message = "Menu item deleted successfully!";
            
            // Log admin activity
            $admin_id = $_SESSION['admin_id'];
            $action = "Deleted navigation menu item: " . ($menu_item ? $menu_item['label'] : "ID: ".$id);
            $ip = $_SERVER['REMOTE_ADDR'];
            
            $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
            $log_stmt->bind_param("iss", $admin_id, $action, $ip);
            $log_stmt->execute();
            
            // Refresh the page to show updated menu
            header("Location: /admin/navigation.php?success=deleted");
            exit();
        } else {
            $error = "Error deleting menu item: " . $delete_stmt->error;
        }
    }
    
    // Regenerate navigation file
    if (isset($_POST['regenerate_file'])) {
        // First fetch updated menu items
        $updated_stmt = $conn_back->prepare("SELECT * FROM navigation_menu ORDER BY menu_order ASC");
        $updated_stmt->execute();
        $updated_result = $updated_stmt->get_result();
        
        $updated_menu_items = [];
        if ($updated_result->num_rows > 0) {
            while ($row = $updated_result->fetch_assoc()) {
                $updated_menu_items[] = $row;
            }
        }
        
        // Create navigation file content
        $nav_content = "<?php\n// Navigation Menu - Auto-generated\n\n";
        $nav_content .= "// This file is auto-generated by the admin panel. Do not edit manually.\n\n";
        $nav_content .= "function get_navigation_menu() {\n";
        $nav_content .= "    return [\n";
        
        foreach ($updated_menu_items as $item) {
            if ($item['status'] == 1) {
                $nav_content .= "        [\n";
                $nav_content .= "            'label' => '" . addslashes($item['label']) . "',\n";
                $nav_content .= "            'url' => '" . addslashes($item['url']) . "',\n";
                $nav_content .= "            'parent_id' => " . $item['parent_id'] . ",\n";
                $nav_content .= "            'menu_order' => " . $item['menu_order'] . "\n";
                $nav_content .= "        ],\n";
            }
        }
        
        $nav_content .= "    ];\n";
        $nav_content .= "}\n";
        
        // Save file
        if (file_put_contents($nav_file, $nav_content)) {
            $message = "Navigation file regenerated successfully!";
            
            // Log admin activity
            $admin_id = $_SESSION['admin_id'];
            $action = "Regenerated navigation menu file";
            $ip = $_SERVER['REMOTE_ADDR'];
            
            $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
            $log_stmt->bind_param("iss", $admin_id, $action, $ip);
            $log_stmt->execute();
        } else {
            $error = "Error regenerating navigation file. Check file permissions.";
        }
    }
}

// Set success message from URL parameter
if (isset($_GET['success'])) {
    switch($_GET['success']) {
        case 'added':
            $message = "Menu item added successfully!";
            break;
        case 'updated':
            $message = "Menu item updated successfully!";
            break;
        case 'deleted':
            $message = "Menu item deleted successfully!";
            break;
    }
}

// Reload menu items from database
$menu_items = [];
$stmt = $conn_back->prepare("SELECT * FROM navigation_menu ORDER BY menu_order ASC");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $menu_items[] = $row;
    }
}

include_once __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Navigation Menu Management</h1>
        <div>
            <button type="button" class="btn btn-primary mr-2" data-toggle="modal" data-target="#addMenuModal">
                <i class="fas fa-plus mr-1"></i> Add Menu Item
            </button>
            <a href="/admin/front_pages.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Pages
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
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Navigation Menu Items</h6>
            <form method="post" action="">
                <button type="submit" name="regenerate_file" class="btn btn-sm btn-info">
                    <i class="fas fa-sync-alt mr-1"></i> Regenerate Menu File
                </button>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Label</th>
                            <th>URL</th>
                            <th>Parent ID</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menu_items as $item): ?>
                            <tr class="<?= $item['status'] == 0 ? 'table-secondary' : '' ?>">
                                <td><?= $item['id'] ?></td>
                                <td><?= htmlspecialchars($item['label']) ?></td>
                                <td><?= htmlspecialchars($item['url']) ?></td>
                                <td><?= $item['parent_id'] ?></td>
                                <td><?= $item['menu_order'] ?></td>
                                <td>
                                    <?php if ($item['status'] == 1): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm edit-menu-btn" 
                                            data-id="<?= $item['id'] ?>"
                                            data-label="<?= htmlspecialchars($item['label']) ?>"
                                            data-url="<?= htmlspecialchars($item['url']) ?>"
                                            data-parent="<?= $item['parent_id'] ?>"
                                            data-order="<?= $item['menu_order'] ?>"
                                            data-status="<?= $item['status'] ?>"
                                            data-toggle="modal" data-target="#editMenuModal">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm delete-menu-btn"
                                            data-id="<?= $item['id'] ?>"
                                            data-label="<?= htmlspecialchars($item['label']) ?>"
                                            data-toggle="modal" data-target="#deleteMenuModal">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($menu_items)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No menu items found. Add your first menu item!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Menu Item Modal -->
<div class="modal fade" id="addMenuModal" tabindex="-1" role="dialog" aria-labelledby="addMenuModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMenuModalLabel">Add New Menu Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="menu_label">Menu Label *</label>
                        <input type="text" class="form-control" id="menu_label" name="menu_label" required>
                    </div>
                    <div class="form-group">
                        <label for="menu_url">URL *</label>
                        <input type="text" class="form-control" id="menu_url" name="menu_url" required>
                        <small class="form-text text-muted">Use relative URLs (e.g., /about, /contact)</small>
                    </div>
                    <div class="form-group">
                        <label for="parent_id">Parent Menu</label>
                        <select class="form-control" id="parent_id" name="parent_id">
                            <option value="0">None (Top Level)</option>
                            <?php foreach ($menu_items as $item): ?>
                                <?php if ($item['parent_id'] == 0): ?>
                                    <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['label']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="menu_order">Menu Order</label>
                        <input type="number" class="form-control" id="menu_order" name="menu_order" min="1" value="<?= count($menu_items) + 1 ?>">
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="menu_status" name="menu_status" checked>
                            <label class="custom-control-label" for="menu_status">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_menu_item" class="btn btn-primary">Add Menu Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Menu Item Modal -->
<div class="modal fade" id="editMenuModal" tabindex="-1" role="dialog" aria-labelledby="editMenuModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMenuModalLabel">Edit Menu Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <input type="hidden" id="edit_menu_id" name="menu_id">
                    <div class="form-group">
                        <label for="edit_menu_label">Menu Label *</label>
                        <input type="text" class="form-control" id="edit_menu_label" name="menu_label" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_menu_url">URL *</label>
                        <input type="text" class="form-control" id="edit_menu_url" name="menu_url" required>
                        <small class="form-text text-muted">Use relative URLs (e.g., /about, /contact)</small>
                    </div>
                    <div class="form-group">
                        <label for="edit_parent_id">Parent Menu</label>
                        <select class="form-control" id="edit_parent_id" name="parent_id">
                            <option value="0">None (Top Level)</option>
                            <?php foreach ($menu_items as $item): ?>
                                <?php if ($item['parent_id'] == 0): ?>
                                    <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['label']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_menu_order">Menu Order</label>
                        <input type="number" class="form-control" id="edit_menu_order" name="menu_order" min="1" value="1">
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="edit_menu_status" name="menu_status">
                            <label class="custom-control-label" for="edit_menu_status">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_menu_item" class="btn btn-primary">Update Menu Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Menu Item Modal -->
<div class="modal fade" id="deleteMenuModal" tabindex="-1" role="dialog" aria-labelledby="deleteMenuModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteMenuModalLabel">Delete Menu Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <input type="hidden" id="delete_menu_id" name="menu_id">
                    <p>Are you sure you want to delete the menu item: <strong id="delete_menu_label"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_menu_item" class="btn btn-danger">Delete Menu Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle edit button clicks
    $('.edit-menu-btn').click(function() {
        var id = $(this).data('id');
        var label = $(this).data('label');
        var url = $(this).data('url');
        var parent = $(this).data('parent');
        var order = $(this).data('order');
        var status = $(this).data('status');
        
        $('#edit_menu_id').val(id);
        $('#edit_menu_label').val(label);
        $('#edit_menu_url').val(url);
        $('#edit_parent_id').val(parent);
        $('#edit_menu_order').val(order);
        $('#edit_menu_status').prop('checked', status == 1);
    });
    
    // Handle delete button clicks
    $('.delete-menu-btn').click(function() {
        var id = $(this).data('id');
        var label = $(this).data('label');
        
        $('#delete_menu_id').val(id);
        $('#delete_menu_label').text(label);
    });
});
</script>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 