<?php
// Staking plans management page for admin panel
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
    $current_page = 'staking_plans.php';
    
    require_once __DIR__ . '/layout/header.php';
    require_once __DIR__ . '/layout/breadcrumb.php';
} catch (Exception $e) {
    echo "Error loading required files: " . $e->getMessage();
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add/Edit plan
    if (isset($_POST['action']) && ($_POST['action'] == 'add_plan' || $_POST['action'] == 'edit_plan')) {
        $is_edit = ($_POST['action'] == 'edit_plan');
        $plan_id = $is_edit ? (int)$_POST['plan_id'] : 0;
        
        // Get form data
        $name = $conn_back->real_escape_string($_POST['name']);
        $description = $conn_back->real_escape_string($_POST['description']);
        $min_amount = (float)$_POST['min_amount'];
        $max_amount = !empty($_POST['max_amount']) ? (float)$_POST['max_amount'] : null;
        $reward_percent = (float)$_POST['reward_percent'];
        $duration_days = (int)$_POST['duration_days'];
        $lock_period_days = (int)$_POST['lock_period_days'];
        $early_unstake_penalty = (float)$_POST['early_unstake_penalty'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Validate inputs
        $errors = [];
        
        if (empty($name)) {
            $errors[] = "Plan name is required";
        }
        
        if ($min_amount <= 0) {
            $errors[] = "Minimum amount must be greater than zero";
        }
        
        if (!is_null($max_amount) && $max_amount <= $min_amount) {
            $errors[] = "Maximum amount must be greater than minimum amount";
        }
        
        if ($reward_percent <= 0) {
            $errors[] = "Reward percentage must be greater than zero";
        }
        
        if ($duration_days <= 0) {
            $errors[] = "Duration must be greater than zero days";
        }
        
        if ($lock_period_days < 0 || $lock_period_days > $duration_days) {
            $errors[] = "Lock period must be between 0 and duration days";
        }
        
        if ($early_unstake_penalty < 0 || $early_unstake_penalty > 100) {
            $errors[] = "Early unstake penalty must be between 0 and 100 percent";
        }
        
        if (empty($errors)) {
            if ($is_edit) {
                // Update existing plan
                $query = $conn_back->prepare("
                    UPDATE staking_plans 
                    SET name = ?, description = ?, min_amount = ?, max_amount = ?, 
                        reward_percent = ?, duration_days = ?, lock_period_days = ?, 
                        early_unstake_penalty = ?, is_active = ?
                    WHERE id = ?
                ");
                $query->bind_param("ssddiiidii", $name, $description, $min_amount, $max_amount, 
                                $reward_percent, $duration_days, $lock_period_days, 
                                $early_unstake_penalty, $is_active, $plan_id);
            } else {
                // Add new plan
                $query = $conn_back->prepare("
                    INSERT INTO staking_plans (name, description, min_amount, max_amount, 
                                            reward_percent, duration_days, lock_period_days, 
                                            early_unstake_penalty, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $query->bind_param("ssddiiidi", $name, $description, $min_amount, $max_amount, 
                                $reward_percent, $duration_days, $lock_period_days, 
                                $early_unstake_penalty, $is_active);
            }
            
            if ($query->execute()) {
                $plan_id = $is_edit ? $plan_id : $conn_back->insert_id;
                
                // Log the action
                $admin_id = $_SESSION['admin_id'];
                $action = $is_edit ? "Updated staking plan ID {$plan_id}" : "Created new staking plan: {$name}";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                $log_stmt->execute();
                
                $success_message = $is_edit ? "Staking plan updated successfully" : "New staking plan added successfully";
            } else {
                $error_message = "Error: " . $query->error;
            }
        } else {
            $error_message = "Validation errors:<br>" . implode("<br>", $errors);
        }
    }
    
    // Toggle plan status
    if (isset($_POST['action']) && $_POST['action'] == 'toggle_status') {
        $plan_id = (int)$_POST['plan_id'];
        $new_status = (int)$_POST['status'];
        
        $stmt = $conn_back->prepare("UPDATE staking_plans SET is_active = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_status, $plan_id);
        
        if ($stmt->execute()) {
            // Log the action
            $admin_id = $_SESSION['admin_id'];
            $status_text = $new_status ? 'active' : 'inactive';
            $action = "Changed staking plan ID {$plan_id} status to {$status_text}";
            $ip = $_SERVER['REMOTE_ADDR'];
            
            $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
            $log_stmt->bind_param("iss", $admin_id, $action, $ip);
            $log_stmt->execute();
            
            $success_message = "Plan status updated successfully.";
        } else {
            $error_message = "Failed to update plan status: " . $conn_back->error;
        }
    }
    
    // Delete plan
    if (isset($_POST['action']) && $_POST['action'] == 'delete_plan') {
        $plan_id = (int)$_POST['plan_id'];
        
        // Check if plan is in use
        $check_query = $conn_back->prepare("SELECT COUNT(*) as count FROM staking WHERE plan_id = ?");
        $check_query->bind_param("i", $plan_id);
        $check_query->execute();
        $check_result = $check_query->get_result();
        $plan_usage = $check_result->fetch_assoc()['count'];
        
        if ($plan_usage > 0) {
            $error_message = "Cannot delete this plan as it is currently in use by {$plan_usage} staking records.";
        } else {
            $stmt = $conn_back->prepare("DELETE FROM staking_plans WHERE id = ?");
            $stmt->bind_param("i", $plan_id);
            
            if ($stmt->execute()) {
                // Log the action
                $admin_id = $_SESSION['admin_id'];
                $action = "Deleted staking plan ID {$plan_id}";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                $log_stmt->execute();
                
                $success_message = "Staking plan deleted successfully.";
            } else {
                $error_message = "Failed to delete staking plan: " . $conn_back->error;
            }
        }
    }
}

// Get all staking plans
$query = "SELECT * FROM staking_plans ORDER BY name";
$result = $conn_back->query($query);
$plans = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $plans[] = $row;
    }
}
?>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-12">
            <div class="box">
                <div class="box-header with-border">
                    <h4 class="box-title">Staking Plans</h4>
                    <div class="box-controls pull-right">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPlanModal">
                            <i class="fa fa-plus"></i> Add New Plan
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
                                    <th>Name</th>
                                    <th>Min/Max Amount</th>
                                    <th>APY (%)</th>
                                    <th>Duration</th>
                                    <th>Lock Period</th>
                                    <th>Early Unstake<br>Penalty (%)</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($plans) > 0): ?>
                                    <?php foreach ($plans as $plan): ?>
                                    <tr>
                                        <td><?php echo $plan['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($plan['name']); ?></strong>
                                            <?php if (!empty($plan['description'])): ?>
                                            <small class="d-block text-muted"><?php echo htmlspecialchars($plan['description']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            $<?php echo number_format($plan['min_amount'], 2); ?>
                                            <?php if (!empty($plan['max_amount'])): ?>
                                            <small class="d-block text-muted">Max: $<?php echo number_format($plan['max_amount'], 2); ?></small>
                                            <?php else: ?>
                                            <small class="d-block text-muted">No maximum</small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo number_format($plan['reward_percent'], 2); ?>%</td>
                                        <td><?php echo $plan['duration_days']; ?> days</td>
                                        <td><?php echo $plan['lock_period_days']; ?> days</td>
                                        <td><?php echo number_format($plan['early_unstake_penalty'], 2); ?>%</td>
                                        <td>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                                                <input type="hidden" name="status" value="<?php echo $plan['is_active'] ? '0' : '1'; ?>">
                                                <button type="submit" class="btn btn-sm <?php echo $plan['is_active'] ? 'btn-success' : 'btn-warning'; ?>">
                                                    <?php echo $plan['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($plan['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-info btn-sm edit-plan" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editPlanModal"
                                                        data-id="<?php echo $plan['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($plan['name']); ?>"
                                                        data-description="<?php echo htmlspecialchars($plan['description']); ?>"
                                                        data-min-amount="<?php echo $plan['min_amount']; ?>"
                                                        data-max-amount="<?php echo $plan['max_amount']; ?>"
                                                        data-reward-percent="<?php echo $plan['reward_percent']; ?>"
                                                        data-duration-days="<?php echo $plan['duration_days']; ?>"
                                                        data-lock-period-days="<?php echo $plan['lock_period_days']; ?>"
                                                        data-early-unstake-penalty="<?php echo $plan['early_unstake_penalty']; ?>"
                                                        data-is-active="<?php echo $plan['is_active']; ?>">
                                                    <i class="fa fa-edit"></i> Edit
                                                </button>
                                                
                                                <button type="button" class="btn btn-danger btn-sm delete-plan"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deletePlanModal"
                                                        data-id="<?php echo $plan['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($plan['name']); ?>">
                                                    <i class="fa fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center">No staking plans found</td>
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

<!-- Add Plan Modal -->
<div class="modal fade" id="addPlanModal" tabindex="-1" aria-labelledby="addPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPlanModalLabel">Add New Staking Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_plan">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Plan Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="is_active" class="form-label">Active Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
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
                                <label for="reward_percent" class="form-label">APY Reward Percentage</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="reward_percent" name="reward_percent" step="0.01" min="0" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="duration_days" class="form-label">Duration (Days)</label>
                                <input type="number" class="form-control" id="duration_days" name="duration_days" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="lock_period_days" class="form-label">Lock Period (Days)</label>
                                <input type="number" class="form-control" id="lock_period_days" name="lock_period_days" min="0" required>
                                <small class="form-text text-muted">Period during which unstaking is not allowed (0 for no lock)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="early_unstake_penalty" class="form-label">Early Unstake Penalty</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="early_unstake_penalty" name="early_unstake_penalty" step="0.01" min="0" max="100" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="form-text text-muted">Percentage penalty applied if unstaking before the lock period ends</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Plan Modal -->
<div class="modal fade" id="editPlanModal" tabindex="-1" aria-labelledby="editPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPlanModalLabel">Edit Staking Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_plan">
                    <input type="hidden" name="plan_id" id="edit_plan_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_name" class="form-label">Plan Name</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_is_active" class="form-label">Active Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                                    <label class="form-check-label" for="edit_is_active">Active</label>
                                </div>
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
                                <label for="edit_reward_percent" class="form-label">APY Reward Percentage</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="edit_reward_percent" name="reward_percent" step="0.01" min="0" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_duration_days" class="form-label">Duration (Days)</label>
                                <input type="number" class="form-control" id="edit_duration_days" name="duration_days" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_lock_period_days" class="form-label">Lock Period (Days)</label>
                                <input type="number" class="form-control" id="edit_lock_period_days" name="lock_period_days" min="0" required>
                                <small class="form-text text-muted">Period during which unstaking is not allowed (0 for no lock)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_early_unstake_penalty" class="form-label">Early Unstake Penalty</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="edit_early_unstake_penalty" name="early_unstake_penalty" step="0.01" min="0" max="100" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="form-text text-muted">Percentage penalty applied if unstaking before the lock period ends</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Plan Modal -->
<div class="modal fade" id="deletePlanModal" tabindex="-1" aria-labelledby="deletePlanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletePlanModalLabel">Delete Staking Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete_plan">
                    <input type="hidden" name="plan_id" id="delete_plan_id">
                    
                    <p>Are you sure you want to delete the staking plan <strong id="delete_plan_name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone. The plan will be permanently deleted if it's not in use.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit plan button
    const editButtons = document.querySelectorAll('.edit-plan');
    if (editButtons) {
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const description = this.getAttribute('data-description');
                const minAmount = this.getAttribute('data-min-amount');
                const maxAmount = this.getAttribute('data-max-amount');
                const rewardPercent = this.getAttribute('data-reward-percent');
                const durationDays = this.getAttribute('data-duration-days');
                const lockPeriodDays = this.getAttribute('data-lock-period-days');
                const earlyUnstakePenalty = this.getAttribute('data-early-unstake-penalty');
                const isActive = this.getAttribute('data-is-active') === '1';
                
                document.getElementById('edit_plan_id').value = id;
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_description').value = description;
                document.getElementById('edit_min_amount').value = minAmount;
                document.getElementById('edit_max_amount').value = maxAmount || '';
                document.getElementById('edit_reward_percent').value = rewardPercent;
                document.getElementById('edit_duration_days').value = durationDays;
                document.getElementById('edit_lock_period_days').value = lockPeriodDays;
                document.getElementById('edit_early_unstake_penalty').value = earlyUnstakePenalty;
                document.getElementById('edit_is_active').checked = isActive;
            });
        });
    }
    
    // Handle delete plan button
    const deleteButtons = document.querySelectorAll('.delete-plan');
    if (deleteButtons) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                
                document.getElementById('delete_plan_id').value = id;
                document.getElementById('delete_plan_name').textContent = name;
            });
        });
    }
});
</script>

<?php
// Include footer
require_once __DIR__ . '/layout/footer.php';
?> 