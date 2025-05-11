<?php
// Withdrawal methods management page for admin panel
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Include necessary files
try {
    require_once __DIR__ . '/include/config.php';
    
    // Set current page for menu highlighting
    $current_page = 'withdrawal_methods.php';
    
    require_once __DIR__ . '/layout/header.php';
    require_once __DIR__ . '/layout/breadcrumb.php';
} catch (Exception $e) {
    echo "Error loading required files: " . $e->getMessage();
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add/Edit method
    if (isset($_POST['action']) && ($_POST['action'] == 'add_method' || $_POST['action'] == 'edit_method')) {
        $is_edit = ($_POST['action'] == 'edit_method');
        $method_id = $is_edit ? (int)$_POST['method_id'] : 0;
        
        // Get form data
        $method_name = $conn_back->real_escape_string($_POST['method_name']);
        $description = $conn_back->real_escape_string($_POST['description']);
        $status = $conn_back->real_escape_string($_POST['status']);
        $min_amount = (float)$_POST['min_amount'];
        $max_amount = !empty($_POST['max_amount']) ? (float)$_POST['max_amount'] : null;
        $processing_time = $conn_back->real_escape_string($_POST['processing_time']);
        $fee_percentage = (float)$_POST['fee_percentage'];
        $fee_fixed = (float)$_POST['fee_fixed'];
        
        // Validate inputs
        $errors = [];
        
        if (empty($method_name)) {
            $errors[] = "Method name is required";
        }
        
        if (!in_array($status, ['active', 'inactive'])) {
            $errors[] = "Invalid status value";
        }
        
        if ($min_amount <= 0) {
            $errors[] = "Minimum amount must be greater than zero";
        }
        
        if (!is_null($max_amount) && $max_amount <= $min_amount) {
            $errors[] = "Maximum amount must be greater than minimum amount";
        }
        
        if ($fee_percentage < 0 || $fee_percentage > 100) {
            $errors[] = "Fee percentage must be between 0 and 100";
        }
        
        if ($fee_fixed < 0) {
            $errors[] = "Fixed fee must be greater than or equal to zero";
        }
        
        if (empty($errors)) {
            if ($is_edit) {
                // Update existing method
                $query = $conn_back->prepare("
                    UPDATE withdrawal_methods 
                    SET method_name = ?, description = ?, status = ?, 
                        min_amount = ?, max_amount = ?, processing_time = ?, 
                        fee_percentage = ?, fee_fixed = ?
                    WHERE id = ?
                ");
                $query->bind_param("sssddsddi", $method_name, $description, $status, 
                                $min_amount, $max_amount, $processing_time, 
                                $fee_percentage, $fee_fixed, $method_id);
            } else {
                // Add new method
                $query = $conn_back->prepare("
                    INSERT INTO withdrawal_methods (method_name, description, status, 
                                                min_amount, max_amount, processing_time, 
                                                fee_percentage, fee_fixed)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $query->bind_param("sssddsddi", $method_name, $description, $status, 
                                $min_amount, $max_amount, $processing_time, 
                                $fee_percentage, $fee_fixed);
            }
            
            if ($query->execute()) {
                $method_id = $is_edit ? $method_id : $conn_back->insert_id;
                
                // Log the action
                $admin_id = $_SESSION['admin_id'];
                $action = $is_edit ? "Updated withdrawal method ID {$method_id}" : "Created new withdrawal method: {$method_name}";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                $log_stmt->execute();
                
                $success_message = $is_edit ? "Withdrawal method updated successfully" : "New withdrawal method added successfully";
            } else {
                $error_message = "Error: " . $query->error;
            }
        } else {
            $error_message = "Validation errors:<br>" . implode("<br>", $errors);
        }
    }
    
    // Toggle method status
    if (isset($_POST['action']) && $_POST['action'] == 'toggle_status') {
        $method_id = (int)$_POST['method_id'];
        $new_status = $conn_back->real_escape_string($_POST['status']);
        
        if (in_array($new_status, ['active', 'inactive'])) {
            $stmt = $conn_back->prepare("UPDATE withdrawal_methods SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $method_id);
            
            if ($stmt->execute()) {
                // Log the action
                $admin_id = $_SESSION['admin_id'];
                $action = "Changed withdrawal method ID {$method_id} status to {$new_status}";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                $log_stmt->execute();
                
                $success_message = "Method status updated successfully.";
            } else {
                $error_message = "Failed to update method status: " . $conn_back->error;
            }
        } else {
            $error_message = "Invalid status value.";
        }
    }
    
    // Delete method
    if (isset($_POST['action']) && $_POST['action'] == 'delete_method') {
        $method_id = (int)$_POST['method_id'];
        
        // Check if method is in use
        $check_query = $conn_back->prepare("
            SELECT COUNT(*) as count 
            FROM user_withdrawal_methods 
            WHERE withdrawal_method_id = ?
        ");
        $check_query->bind_param("i", $method_id);
        $check_query->execute();
        $check_result = $check_query->get_result();
        $method_usage = $check_result->fetch_assoc()['count'];
        
        if ($method_usage > 0) {
            $error_message = "Cannot delete this method as it is currently in use by {$method_usage} users.";
        } else {
            $stmt = $conn_back->prepare("DELETE FROM withdrawal_methods WHERE id = ?");
            $stmt->bind_param("i", $method_id);
            
            if ($stmt->execute()) {
                // Log the action
                $admin_id = $_SESSION['admin_id'];
                $action = "Deleted withdrawal method ID {$method_id}";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                $log_stmt->execute();
                
                $success_message = "Withdrawal method deleted successfully.";
            } else {
                $error_message = "Failed to delete withdrawal method: " . $conn_back->error;
            }
        }
    }
}

// Get all withdrawal methods
$query = "SELECT * FROM withdrawal_methods ORDER BY method_name";
$result = $conn_back->query($query);
$methods = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $methods[] = $row;
    }
}
?>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-12">
            <div class="box">
                <div class="box-header with-border">
                    <h4 class="box-title">Withdrawal Methods</h4>
                    <div class="box-controls pull-right">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMethodModal">
                            <i class="fa fa-plus"></i> Add New Method
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?php echo $success_message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?php echo $error_message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Method Name</th>
                                    <th>Min/Max Amount</th>
                                    <th>Fee</th>
                                    <th>Processing Time</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($methods) > 0): ?>
                                    <?php foreach ($methods as $method): ?>
                                    <tr>
                                        <td><?php echo $method['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($method['method_name']); ?></strong>
                                            <?php if (!empty($method['description'])): ?>
                                            <small class="d-block text-muted"><?php echo htmlspecialchars($method['description']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            $<?php echo number_format($method['min_amount'], 2); ?>
                                            <?php if (!empty($method['max_amount'])): ?>
                                            <small class="d-block text-muted">Max: $<?php echo number_format($method['max_amount'], 2); ?></small>
                                            <?php else: ?>
                                            <small class="d-block text-muted">No maximum</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($method['fee_percentage'] > 0): ?>
                                            <?php echo number_format($method['fee_percentage'], 2); ?>%
                                            <?php endif; ?>
                                            <?php if ($method['fee_fixed'] > 0): ?>
                                            <small class="d-block text-muted">+$<?php echo number_format($method['fee_fixed'], 2); ?> fixed</small>
                                            <?php endif; ?>
                                            <?php if ($method['fee_percentage'] == 0 && $method['fee_fixed'] == 0): ?>
                                            Free
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($method['processing_time']); ?></td>
                                        <td>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="method_id" value="<?php echo $method['id']; ?>">
                                                <input type="hidden" name="status" value="<?php echo $method['status'] == 'active' ? 'inactive' : 'active'; ?>">
                                                <button type="submit" class="btn btn-sm <?php echo $method['status'] == 'active' ? 'btn-success' : 'btn-warning'; ?>">
                                                    <?php echo ucfirst($method['status']); ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($method['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-info btn-sm edit-method" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editMethodModal"
                                                        data-id="<?php echo $method['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($method['method_name']); ?>"
                                                        data-description="<?php echo htmlspecialchars($method['description']); ?>"
                                                        data-status="<?php echo $method['status']; ?>"
                                                        data-min-amount="<?php echo $method['min_amount']; ?>"
                                                        data-max-amount="<?php echo $method['max_amount']; ?>"
                                                        data-processing-time="<?php echo htmlspecialchars($method['processing_time']); ?>"
                                                        data-fee-percentage="<?php echo $method['fee_percentage']; ?>"
                                                        data-fee-fixed="<?php echo $method['fee_fixed']; ?>">
                                                    <i class="fa fa-edit"></i> Edit
                                                </button>
                                                
                                                <button type="button" class="btn btn-danger btn-sm delete-method"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteMethodModal"
                                                        data-id="<?php echo $method['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($method['method_name']); ?>">
                                                    <i class="fa fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No withdrawal methods found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->

<!-- Add Method Modal -->
<div class="modal fade" id="addMethodModal" tabindex="-1" aria-labelledby="addMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMethodModalLabel">Add New Withdrawal Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_method">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="method_name" class="form-label">Method Name</label>
                                <input type="text" class="form-control" id="method_name" name="method_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="min_amount" class="form-label">Minimum Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="min_amount" name="min_amount" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_amount" class="form-label">Maximum Amount (Optional)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="max_amount" name="max_amount" step="0.01" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="fee_percentage" class="form-label">Fee Percentage</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="fee_percentage" name="fee_percentage" step="0.01" min="0" max="100" value="0">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="fee_fixed" class="form-label">Fixed Fee</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="fee_fixed" name="fee_fixed" step="0.01" min="0" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="processing_time" class="form-label">Processing Time</label>
                                <input type="text" class="form-control" id="processing_time" name="processing_time" value="1-3 business days">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Method</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Method Modal -->
<div class="modal fade" id="editMethodModal" tabindex="-1" aria-labelledby="editMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMethodModalLabel">Edit Withdrawal Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_method">
                    <input type="hidden" name="method_id" id="edit_method_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_method_name" class="form-label">Method Name</label>
                                <input type="text" class="form-control" id="edit_method_name" name="method_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_status" class="form-label">Status</label>
                                <select class="form-select" id="edit_status" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_min_amount" class="form-label">Minimum Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="edit_min_amount" name="min_amount" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_max_amount" class="form-label">Maximum Amount (Optional)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="edit_max_amount" name="max_amount" step="0.01" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_fee_percentage" class="form-label">Fee Percentage</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="edit_fee_percentage" name="fee_percentage" step="0.01" min="0" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_fee_fixed" class="form-label">Fixed Fee</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="edit_fee_fixed" name="fee_fixed" step="0.01" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_processing_time" class="form-label">Processing Time</label>
                                <input type="text" class="form-control" id="edit_processing_time" name="processing_time">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Method</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Method Modal -->
<div class="modal fade" id="deleteMethodModal" tabindex="-1" aria-labelledby="deleteMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteMethodModalLabel">Delete Withdrawal Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete_method">
                    <input type="hidden" name="method_id" id="delete_method_id">
                    
                    <p>Are you sure you want to delete the withdrawal method <strong id="delete_method_name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone. The method will be permanently deleted if it's not in use.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Method</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit method button
    const editButtons = document.querySelectorAll('.edit-method');
    if (editButtons) {
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const description = this.getAttribute('data-description');
                const status = this.getAttribute('data-status');
                const minAmount = this.getAttribute('data-min-amount');
                const maxAmount = this.getAttribute('data-max-amount');
                const processingTime = this.getAttribute('data-processing-time');
                const feePercentage = this.getAttribute('data-fee-percentage');
                const feeFixed = this.getAttribute('data-fee-fixed');
                
                document.getElementById('edit_method_id').value = id;
                document.getElementById('edit_method_name').value = name;
                document.getElementById('edit_description').value = description;
                document.getElementById('edit_status').value = status;
                document.getElementById('edit_min_amount').value = minAmount;
                document.getElementById('edit_max_amount').value = maxAmount || '';
                document.getElementById('edit_processing_time').value = processingTime;
                document.getElementById('edit_fee_percentage').value = feePercentage;
                document.getElementById('edit_fee_fixed').value = feeFixed;
            });
        });
    }
    
    // Handle delete method button
    const deleteButtons = document.querySelectorAll('.delete-method');
    if (deleteButtons) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                
                document.getElementById('delete_method_id').value = id;
                document.getElementById('delete_method_name').textContent = name;
            });
        });
    }
});
</script>

<?php
// Include footer
require_once __DIR__ . '/layout/footer.php';
?> 