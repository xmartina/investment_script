<?php
// Admin Staking Plans Management Page
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Staking Plans";
$current_page = "staking_plans.php";
$message = "";
$error = "";

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new plan
    if (isset($_POST['add_plan'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $min_amount = floatval($_POST['min_amount']);
        $max_amount = floatval($_POST['max_amount']);
        $roi_daily = floatval($_POST['roi_daily']);
        $lockup_period = intval($_POST['lockup_period']);
        $status = isset($_POST['status']) ? 1 : 0;
        $featured = isset($_POST['featured']) ? 1 : 0;
        
        // Validate inputs
        if (empty($name) || $min_amount <= 0 || $roi_daily <= 0 || $lockup_period <= 0) {
            $error = "Please fill all required fields with valid values.";
        } else {
            // Insert new plan
            $stmt = $conn_back->prepare("
                INSERT INTO staking_plans (name, description, min_amount, max_amount, 
                    roi_daily, lockup_period, status, featured, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->bind_param("ssdddiis", $name, $description, $min_amount, $max_amount, 
                $roi_daily, $lockup_period, $status, $featured);
            
            if ($stmt->execute()) {
                // Log admin activity
                $admin_id = $_SESSION['admin_id'];
                $action = "Added new staking plan: $name";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                $log_stmt->execute();
                
                $message = "Staking plan added successfully.";
            } else {
                $error = "Error adding staking plan: " . $stmt->error;
            }
            
            $stmt->close();
        }
    }
    
    // Update plan
    if (isset($_POST['update_plan'])) {
        $plan_id = $_POST['plan_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $min_amount = floatval($_POST['min_amount']);
        $max_amount = floatval($_POST['max_amount']);
        $roi_daily = floatval($_POST['roi_daily']);
        $lockup_period = intval($_POST['lockup_period']);
        $status = isset($_POST['status']) ? 1 : 0;
        $featured = isset($_POST['featured']) ? 1 : 0;
        
        // Validate inputs
        if (empty($name) || $min_amount <= 0 || $roi_daily <= 0 || $lockup_period <= 0) {
            $error = "Please fill all required fields with valid values.";
        } else {
            // Update plan
            $stmt = $conn_back->prepare("
                UPDATE staking_plans 
                SET name = ?, description = ?, min_amount = ?, max_amount = ?, 
                    roi_daily = ?, lockup_period = ?, status = ?, featured = ?, 
                    updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("ssdddiisi", $name, $description, $min_amount, $max_amount, 
                $roi_daily, $lockup_period, $status, $featured, $plan_id);
            
            if ($stmt->execute()) {
                // Log admin activity
                $admin_id = $_SESSION['admin_id'];
                $action = "Updated staking plan #$plan_id: $name";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                $log_stmt->execute();
                
                $message = "Staking plan updated successfully.";
            } else {
                $error = "Error updating staking plan: " . $stmt->error;
            }
            
            $stmt->close();
        }
    }
    
    // Delete plan
    if (isset($_POST['delete_plan'])) {
        $plan_id = $_POST['plan_id'];
        
        // Check if plan is in use
        $stmt = $conn_back->prepare("SELECT COUNT(*) as count FROM staking_positions WHERE plan_id = ? AND status = 'active'");
        $stmt->bind_param("i", $plan_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $active_stakings = $row['count'];
        $stmt->close();
        
        if ($active_stakings > 0) {
            $error = "Cannot delete plan with active staking positions. Deactivate it instead.";
        } else {
            $stmt = $conn_back->prepare("DELETE FROM staking_plans WHERE id = ?");
            $stmt->bind_param("i", $plan_id);
            
            if ($stmt->execute()) {
                // Log admin activity
                $admin_id = $_SESSION['admin_id'];
                $action = "Deleted staking plan #$plan_id";
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                $log_stmt->execute();
                
                $message = "Staking plan deleted successfully.";
            } else {
                $error = "Error deleting staking plan: " . $stmt->error;
            }
            
            $stmt->close();
        }
    }
}

// Check if staking_plans table exists
$table_exists = false;
$result = $conn_back->query("SHOW TABLES LIKE 'staking_plans'");
if ($result && $result->num_rows > 0) {
    $table_exists = true;
    
    // Get all staking plans
    $sql = "SELECT * FROM staking_plans ORDER BY id ASC";
    $result = $conn_back->query($sql);
    $plans = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $plans[] = $row;
        }
    }
} else {
    $error = "The staking_plans table does not exist. Please run the database initialization script.";
}

include_once __DIR__ . '/layout/header.php';
?>

<style>
    /* Ensure equal height cards in each row */
    .card-deck .card, .row .card {
        display: flex;
        flex-direction: column;
        min-height: 350px; /* Set minimum height */
        height: auto;
    }
    .card-body {
        flex: 1 1 auto;
        overflow: visible; /* Change from hidden to visible */
        padding: 1.25rem;
    }
    .card-deck {
        margin-bottom: 15px;
    }
    
    /* Prevent text overflow but ensure visibility */
    .card-body p, .card-body div, .card-header h6 {
        word-break: break-word;
        max-width: 100%;
        white-space: normal;
        color: #ffffff !important; /* Make all text white for visibility */
    }
    
    /* Limit description height with scrolling */
    .card-body .description-container {
        max-height: 80px;
        overflow-y: auto;
        background-color: rgba(0,0,0,0.1); /* Subtle background for scrollable area */
        padding: 5px;
        border-radius: 4px;
    }
    
    /* Lighten text colors */
    .text-gray-800 {
        color: #ffffff !important;
        font-size: 1.25rem;
    }
    .text-xs {
        color: #d1d3e2 !important;
        font-weight: bold;
        font-size: 0.8rem;
    }
    
    /* Improve card header text contrast */
    .card-header h6.text-primary {
        color: #ffffff !important;
    }
    
    /* Add spacing for better readability */
    .card {
        margin-bottom: 1rem;
        background-color: #2c3e50 !important; /* Darker background */
        border: 1px solid rgba(255,255,255,0.125) !important;
    }
    
    /* Make numbers more readable */
    .h5.mb-0 {
        font-size: 1.5rem;
    }
    
    /* Improve visibility of badges */
    .badge {
        font-size: 90%;
        padding: 0.35em 0.65em;
    }
</style>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Staking Plans Management</h1>
        <?php if ($table_exists): ?>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addPlanModal">
            <i class="fas fa-plus mr-2"></i> Add New Plan
        </button>
        <?php endif; ?>
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

    <?php if (!$table_exists): ?>
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Create Staking Tables</h6>
                    </div>
                    <div class="card-body">
                        <p>The staking tables need to be created in the database.</p>
                        <form method="post" action="db_fix.php">
                            <input type="hidden" name="create_staking_tables" value="1">
                            <button type="submit" class="btn btn-primary">Create Staking Tables</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row mb-4">
            <style>
                .plan-card-wrapper {
                    height: 450px !important;
                }
            </style>
            <?php 
            $count = 0;
            foreach ($plans as $plan): 
                // Start a new row for every 3 cards (on large screens)
                if($count % 3 == 0 && $count > 0): ?>
                </div><div class="row mb-4">
                <?php endif; 
                $count++;
            ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card <?= $plan['featured'] ? 'border-left-primary' : '' ?> shadow h-100">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary"><?= htmlspecialchars($plan['name']) ?></h6>
                            <div>
                                <?php if ($plan['featured']): ?>
                                    <span class="badge badge-primary">Featured</span>
                                <?php endif; ?>
                                <?php if (isset($plan['status']) && $plan['status']): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactive</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1">Daily Return Rate</div>
                                    <div class="h5 mb-0 font-weight-bold text-white"><?= $plan['roi_daily'] ?>%</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-chart-line fa-2x text-white"></i>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Lock-up Period</div>
                                <div class="text-white"><?= $plan['lockup_period'] ?> days</div>
                            </div>
                            
                            <div class="row">
                                <div class="col-6">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1">Min Stake</div>
                                    <div class="text-nowrap text-white">$<?= number_format($plan['min_amount'], 2) ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1">Max Stake</div>
                                    <div class="text-nowrap text-white">
                                        <?php if ($plan['max_amount'] > 0): ?>
                                            $<?= number_format($plan['max_amount'], 2) ?>
                                        <?php else: ?>
                                            Unlimited
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($plan['description'])): ?>
                                <hr>
                                <div class="mb-0">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1">Description</div>
                                    <div class="description-container mt-2 mb-2">
                                        <p class="mb-0" style="color: #ffffff;"><?= nl2br(htmlspecialchars($plan['description'])) ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between">
                                <button class="btn btn-primary btn-sm edit-plan" data-toggle="modal" data-target="#editPlanModal" 
                                        data-id="<?= $plan['id'] ?>"
                                        data-name="<?= htmlspecialchars($plan['name']) ?>"
                                        data-description="<?= htmlspecialchars($plan['description']) ?>"
                                        data-min="<?= $plan['min_amount'] ?>"
                                        data-max="<?= $plan['max_amount'] ?>"
                                        data-roi="<?= $plan['roi_daily'] ?>"
                                        data-lockup="<?= $plan['lockup_period'] ?>"
                                        data-status="<?= isset($plan['status']) ? $plan['status'] : '0' ?>"
                                        data-featured="<?= $plan['featured'] ?>">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </button>
                                <button class="btn btn-danger btn-sm delete-plan" data-toggle="modal" data-target="#deletePlanModal" 
                                        data-id="<?= $plan['id'] ?>" 
                                        data-name="<?= htmlspecialchars($plan['name']) ?>">
                                    <i class="fas fa-trash mr-1"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($plans)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        No staking plans found. Click the "Add New Plan" button to create your first plan.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Add Plan Modal -->
<div class="modal fade" id="addPlanModal" tabindex="-1" role="dialog" aria-labelledby="addPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPlanModalLabel">Add New Staking Plan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Plan Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="min_amount">Minimum Staking Amount ($) *</label>
                                <input type="number" class="form-control" id="min_amount" name="min_amount" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="max_amount">Maximum Staking Amount ($)</label>
                                <input type="number" class="form-control" id="max_amount" name="max_amount" step="0.01" min="0">
                                <small class="form-text text-muted">Enter 0 for unlimited</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="roi_daily">Daily Interest Rate (%) *</label>
                                <input type="number" class="form-control" id="roi_daily" name="roi_daily" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lockup_period">Lock-up Period (days) *</label>
                                <input type="number" class="form-control" id="lockup_period" name="lockup_period" min="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="status" name="status" checked>
                                    <label class="custom-control-label" for="status">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="featured" name="featured">
                                    <label class="custom-control-label" for="featured">Featured</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_plan" class="btn btn-primary">Add Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Plan Modal -->
<div class="modal fade" id="editPlanModal" tabindex="-1" role="dialog" aria-labelledby="editPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPlanModalLabel">Edit Staking Plan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" id="edit_plan_id" name="plan_id">
                    <div class="form-group">
                        <label for="edit_name">Plan Name *</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_min_amount">Minimum Staking Amount ($) *</label>
                                <input type="number" class="form-control" id="edit_min_amount" name="min_amount" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_max_amount">Maximum Staking Amount ($)</label>
                                <input type="number" class="form-control" id="edit_max_amount" name="max_amount" step="0.01" min="0">
                                <small class="form-text text-muted">Enter 0 for unlimited</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_roi_daily">Daily Interest Rate (%) *</label>
                                <input type="number" class="form-control" id="edit_roi_daily" name="roi_daily" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_lockup_period">Lock-up Period (days) *</label>
                                <input type="number" class="form-control" id="edit_lockup_period" name="lockup_period" min="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="edit_status" name="status">
                                    <label class="custom-control-label" for="edit_status">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="edit_featured" name="featured">
                                    <label class="custom-control-label" for="edit_featured">Featured</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_plan" class="btn btn-primary">Update Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Plan Modal -->
<div class="modal fade" id="deletePlanModal" tabindex="-1" role="dialog" aria-labelledby="deletePlanModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletePlanModalLabel">Delete Staking Plan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" id="delete_plan_id" name="plan_id">
                    <p>Are you sure you want to delete the staking plan: <strong id="delete_plan_name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone. Plans with active staking positions cannot be deleted.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_plan" class="btn btn-danger">Delete Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Edit plan
    $('.edit-plan').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var description = $(this).data('description');
        var min = $(this).data('min');
        var max = $(this).data('max');
        var roi = $(this).data('roi');
        var lockup = $(this).data('lockup');
        var status = $(this).data('status');
        var featured = $(this).data('featured');
        
        $('#edit_plan_id').val(id);
        $('#edit_name').val(name);
        $('#edit_description').val(description);
        $('#edit_min_amount').val(min);
        $('#edit_max_amount').val(max);
        $('#edit_roi_daily').val(roi);
        $('#edit_lockup_period').val(lockup);
        $('#edit_status').prop('checked', status == 1);
        $('#edit_featured').prop('checked', featured == 1);
    });
    
    // Delete plan
    $('.delete-plan').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        
        $('#delete_plan_id').val(id);
        $('#delete_plan_name').text(name);
    });
});
</script>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 