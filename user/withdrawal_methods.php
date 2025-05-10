<?php
// Withdrawal Methods page
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /user/login");
    exit();
}

$user_id = $_SESSION['user_id'];
$page_name = "Withdrawal Methods";
$css_files = [];
$js_files = [];

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_method'])) {
        // Add new withdrawal method
        $method_id = $_POST['method_id'];
        $account_details = $_POST['account_details'];
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        
        // Validate input
        if (empty($method_id) || empty($account_details)) {
            $error_message = "Please select a withdrawal method and provide account details.";
        } else {
            // If setting as default, unset all other defaults first
            if ($is_default) {
                $stmt = $conn_back->prepare("UPDATE user_withdrawal_methods SET is_default = 0 WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $stmt->close();
            }
            
            // Check if method already exists for this user
            $stmt = $conn_back->prepare("SELECT id FROM user_withdrawal_methods WHERE user_id = ? AND withdrawal_method_id = ?");
            $stmt->bind_param("ii", $user_id, $method_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Update existing method
                $row = $result->fetch_assoc();
                $user_method_id = $row['id'];
                
                $stmt = $conn_back->prepare("UPDATE user_withdrawal_methods SET account_details = ?, is_default = ?, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("sii", $account_details, $is_default, $user_method_id);
                
                if ($stmt->execute()) {
                    $success_message = "Withdrawal method updated successfully.";
                } else {
                    $error_message = "Error updating withdrawal method: " . $stmt->error;
                }
            } else {
                // Insert new method
                $stmt = $conn_back->prepare("INSERT INTO user_withdrawal_methods (user_id, withdrawal_method_id, account_details, is_default) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iisi", $user_id, $method_id, $account_details, $is_default);
                
                if ($stmt->execute()) {
                    $success_message = "Withdrawal method added successfully.";
                } else {
                    $error_message = "Error adding withdrawal method: " . $stmt->error;
                }
            }
            $stmt->close();
        }
    } elseif (isset($_POST['delete_method'])) {
        // Delete withdrawal method
        $user_method_id = $_POST['user_method_id'];
        
        $stmt = $conn_back->prepare("DELETE FROM user_withdrawal_methods WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $user_method_id, $user_id);
        
        if ($stmt->execute()) {
            $success_message = "Withdrawal method deleted successfully.";
        } else {
            $error_message = "Error deleting withdrawal method: " . $stmt->error;
        }
        $stmt->close();
    } elseif (isset($_POST['set_default'])) {
        // Set default withdrawal method
        $user_method_id = $_POST['user_method_id'];
        
        // First, unset all defaults
        $stmt = $conn_back->prepare("UPDATE user_withdrawal_methods SET is_default = 0 WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        
        // Then set the new default
        $stmt = $conn_back->prepare("UPDATE user_withdrawal_methods SET is_default = 1 WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $user_method_id, $user_id);
        
        if ($stmt->execute()) {
            $success_message = "Default withdrawal method updated successfully.";
        } else {
            $error_message = "Error updating default withdrawal method: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Get available withdrawal methods
$stmt = $conn_back->prepare("SELECT * FROM withdrawal_methods WHERE status = 'active' ORDER BY method_name");
$stmt->execute();
$withdrawal_methods = $stmt->get_result();
$stmt->close();

// Get user's withdrawal methods
$stmt = $conn_back->prepare("
    SELECT uwm.*, wm.method_name, wm.description, wm.min_amount, wm.max_amount, wm.processing_time, wm.fee_percentage, wm.fee_fixed
    FROM user_withdrawal_methods uwm
    JOIN withdrawal_methods wm ON uwm.withdrawal_method_id = wm.id
    WHERE uwm.user_id = ?
    ORDER BY uwm.is_default DESC, wm.method_name ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_methods = $stmt->get_result();
$stmt->close();

include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/header.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/breadcumb.php';
?>

<!-- Withdrawal Methods Page -->
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-12">
            <div class="card card-body mb-4">
                <div class="row mb-3">
                    <div class="col">
                        <h4 class="fw-medium">Withdrawal Methods</h4>
                        <p class="text-muted">Manage your withdrawal methods for faster and easier withdrawals.</p>
                    </div>
                </div>
                
                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $success_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $error_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <!-- User's Withdrawal Methods -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Your Withdrawal Methods</h5>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addMethodModal">
                            <i class="bi bi-plus-circle me-1"></i> Add New Method
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <?php if ($user_methods->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Method</th>
                                            <th>Account Details</th>
                                            <th>Fee</th>
                                            <th>Processing Time</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($method = $user_methods->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-40 rounded-circle bg-light me-2">
                                                            <i class="bi bi-wallet2 fs-5"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0"><?= htmlspecialchars($method['method_name']) ?></h6>
                                                            <small class="text-muted">
                                                                Min: $<?= number_format($method['min_amount'], 2) ?> | 
                                                                Max: $<?= number_format($method['max_amount'], 2) ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-truncate" style="max-width: 200px;" data-bs-toggle="tooltip" title="<?= htmlspecialchars($method['account_details']) ?>">
                                                        <?= htmlspecialchars($method['account_details']) ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $fee_text = [];
                                                    if ($method['fee_percentage'] > 0) {
                                                        $fee_text[] = number_format($method['fee_percentage'], 2) . '%';
                                                    }
                                                    if ($method['fee_fixed'] > 0) {
                                                        $fee_text[] = '$' . number_format($method['fee_fixed'], 2);
                                                    }
                                                    echo !empty($fee_text) ? implode(' + ', $fee_text) : 'Free';
                                                    ?>
                                                </td>
                                                <td><?= htmlspecialchars($method['processing_time']) ?></td>
                                                <td>
                                                    <?php if ($method['is_default']): ?>
                                                        <span class="badge bg-success">Default</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-dark">Active</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary edit-method" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editMethodModal"
                                                                data-id="<?= $method['id'] ?>"
                                                                data-method-id="<?= $method['withdrawal_method_id'] ?>"
                                                                data-method-name="<?= htmlspecialchars($method['method_name']) ?>"
                                                                data-details="<?= htmlspecialchars($method['account_details']) ?>"
                                                                data-default="<?= $method['is_default'] ?>">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        
                                                        <?php if (!$method['is_default']): ?>
                                                        <form method="post" class="d-inline">
                                                            <input type="hidden" name="user_method_id" value="<?= $method['id'] ?>">
                                                            <button type="submit" name="set_default" class="btn btn-sm btn-outline-success" title="Set as Default">
                                                                <i class="bi bi-star"></i>
                                                            </button>
                                                        </form>
                                                        <?php endif; ?>
                                                        
                                                        <form method="post" class="d-inline delete-method-form">
                                                            <input type="hidden" name="user_method_id" value="<?= $method['id'] ?>">
                                                            <button type="button" class="btn btn-sm btn-outline-danger delete-method-btn" title="Delete">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center p-4">
                                <div class="avatar avatar-60 rounded-circle bg-light mb-3">
                                    <i class="bi bi-wallet2 fs-2"></i>
                                </div>
                                <h5>No Withdrawal Methods Added</h5>
                                <p class="text-muted">You haven't added any withdrawal methods yet. Add a method to make withdrawals easier.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMethodModal">
                                    <i class="bi bi-plus-circle me-2"></i> Add Withdrawal Method
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Available Withdrawal Methods -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Available Withdrawal Methods</h5>
                    </div>
                    <div class="card-body">
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                            <?php 
                            // Reset result pointer
                            $withdrawal_methods->data_seek(0);
                            while ($method = $withdrawal_methods->fetch_assoc()): 
                            ?>
                                <div class="col">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="avatar avatar-50 rounded-circle bg-light me-3">
                                                    <i class="bi bi-wallet2 fs-4"></i>
                                                </div>
                                                <h5 class="card-title mb-0"><?= htmlspecialchars($method['method_name']) ?></h5>
                                            </div>
                                            <p class="card-text small text-muted"><?= htmlspecialchars($method['description']) ?></p>
                                            <ul class="list-unstyled small">
                                                <li class="mb-1">
                                                    <i class="bi bi-cash-coin me-2 text-success"></i>
                                                    <strong>Min:</strong> $<?= number_format($method['min_amount'], 2) ?> | 
                                                    <strong>Max:</strong> $<?= number_format($method['max_amount'], 2) ?>
                                                </li>
                                                <li class="mb-1">
                                                    <i class="bi bi-clock-history me-2 text-primary"></i>
                                                    <strong>Processing:</strong> <?= htmlspecialchars($method['processing_time']) ?>
                                                </li>
                                                <li>
                                                    <i class="bi bi-tag me-2 text-danger"></i>
                                                    <strong>Fee:</strong> 
                                                    <?php 
                                                    $fee_text = [];
                                                    if ($method['fee_percentage'] > 0) {
                                                        $fee_text[] = number_format($method['fee_percentage'], 2) . '%';
                                                    }
                                                    if ($method['fee_fixed'] > 0) {
                                                        $fee_text[] = '$' . number_format($method['fee_fixed'], 2);
                                                    }
                                                    echo !empty($fee_text) ? implode(' + ', $fee_text) : 'Free';
                                                    ?>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="card-footer bg-transparent border-0 pt-0">
                                            <button class="btn btn-sm btn-outline-primary w-100 add-method-btn" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#addMethodModal"
                                                    data-id="<?= $method['id'] ?>"
                                                    data-name="<?= htmlspecialchars($method['method_name']) ?>">
                                                <i class="bi bi-plus-circle me-1"></i> Add Method
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Method Modal -->
<div class="modal fade" id="addMethodModal" tabindex="-1" aria-labelledby="addMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMethodModalLabel">Add Withdrawal Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="method_id" class="form-label">Withdrawal Method</label>
                        <select class="form-select" id="method_id" name="method_id" required>
                            <option value="">Select a withdrawal method</option>
                            <?php 
                            // Reset result pointer
                            $withdrawal_methods->data_seek(0);
                            while ($method = $withdrawal_methods->fetch_assoc()): 
                            ?>
                                <option value="<?= $method['id'] ?>"><?= htmlspecialchars($method['method_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="account_details" class="form-label">Account Details</label>
                        <textarea class="form-control" id="account_details" name="account_details" rows="4" placeholder="Enter your account details (e.g., bank account number, wallet address, etc.)" required></textarea>
                        <div class="form-text" id="account_details_help"></div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="is_default" name="is_default">
                        <label class="form-check-label" for="is_default">
                            Set as default withdrawal method
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_method" class="btn btn-primary">Save Method</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Method Modal -->
<div class="modal fade" id="editMethodModal" tabindex="-1" aria-labelledby="editMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMethodModalLabel">Edit Withdrawal Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" id="edit_method_id" name="method_id">
                    <div class="mb-3">
                        <label class="form-label">Withdrawal Method</label>
                        <input type="text" class="form-control" id="edit_method_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="edit_account_details" class="form-label">Account Details</label>
                        <textarea class="form-control" id="edit_account_details" name="account_details" rows="4" placeholder="Enter your account details (e.g., bank account number, wallet address, etc.)" required></textarea>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="edit_is_default" name="is_default">
                        <label class="form-check-label" for="edit_is_default">
                            Set as default withdrawal method
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_method" class="btn btn-primary">Update Method</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this withdrawal method? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Handle method selection in add modal
    document.getElementById('method_id').addEventListener('change', function() {
        var methodId = this.value;
        var helpText = '';
        
        <?php
        $withdrawal_methods->data_seek(0);
        echo "var methods = {";
        while ($method = $withdrawal_methods->fetch_assoc()) {
            echo "{$method['id']}: {
                name: '" . addslashes($method['method_name']) . "',
                help: '" . addslashes($method['description']) . "'
            },";
        }
        echo "};";
        ?>
        
        if (methodId && methods[methodId]) {
            helpText = methods[methodId].help;
        }
        
        document.getElementById('account_details_help').textContent = helpText;
    });
    
    // Handle edit method button
    document.querySelectorAll('.edit-method').forEach(function(button) {
        button.addEventListener('click', function() {
            var methodId = this.getAttribute('data-method-id');
            var methodName = this.getAttribute('data-method-name');
            var details = this.getAttribute('data-details');
            var isDefault = this.getAttribute('data-default') === '1';
            
            document.getElementById('edit_method_id').value = methodId;
            document.getElementById('edit_method_name').value = methodName;
            document.getElementById('edit_account_details').value = details;
            document.getElementById('edit_is_default').checked = isDefault;
        });
    });
    
    // Handle add method button from available methods
    document.querySelectorAll('.add-method-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            var methodId = this.getAttribute('data-id');
            document.getElementById('method_id').value = methodId;
            document.getElementById('method_id').dispatchEvent(new Event('change'));
        });
    });
    
    // Handle delete method confirmation
    var deleteForm = null;
    document.querySelectorAll('.delete-method-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            deleteForm = this.closest('.delete-method-form');
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            deleteModal.show();
        });
    });
    
    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (deleteForm) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'delete_method';
            input.value = '1';
            deleteForm.appendChild(input);
            deleteForm.submit();
        }
    });
});
</script>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/user/layout/footer.php'; ?> 