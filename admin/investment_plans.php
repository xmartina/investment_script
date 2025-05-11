<?php
// Admin Investment Plans Management Page
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Investment Plans";
$message = "";
$error = "";

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new plan
    if (isset($_POST['add_plan'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $plan_type = $_POST['plan_type'];
        $category = $_POST['category'];
        $min_amount = floatval($_POST['min_amount']);
        $max_amount = floatval($_POST['max_amount']);
        $roi_percent = floatval($_POST['roi_percent']);
        $duration_days = intval($_POST['duration_days']);
        $risk_level = $_POST['risk_level'];
        $return_interval = $_POST['return_interval'];
        $is_active = isset($_POST['status']) ? 1 : 0;
        $featured = isset($_POST['featured']) ? 1 : 0;
        
        // Validate inputs
        if (empty($name) || empty($plan_type) || empty($category) || $min_amount <= 0 || $roi_percent <= 0 || $duration_days <= 0) {
            $error = "Please fill all required fields with valid values.";
        } else {
            // Insert new plan
            $stmt = $conn_back->prepare("
                INSERT INTO investment_plans (
                    name, description, plan_type, category, min_amount, max_amount, 
                    roi_percent, duration_days, risk_level, return_interval, 
                    is_active, featured, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->bind_param("ssssdddssii", $name, $description, $plan_type, $category, $min_amount, $max_amount, $roi_percent, $duration_days, $risk_level, $return_interval, $is_active, $featured);
            
            if ($stmt->execute()) {
                logAdminActivity($_SESSION['admin_id'], 'Add Investment Plan', "Added new plan: $name");
                $message = "Investment plan added successfully.";
            } else {
                $error = "Error adding investment plan: " . $stmt->error;
            }
            
            $stmt->close();
        }
    }
    
    // Update plan
    if (isset($_POST['update_plan'])) {
        $plan_id = $_POST['plan_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $plan_type = $_POST['plan_type'];
        $category = $_POST['category'];
        $min_amount = floatval($_POST['min_amount']);
        $max_amount = floatval($_POST['max_amount']);
        $roi_percent = floatval($_POST['roi_percent']);
        $duration_days = intval($_POST['duration_days']);
        $risk_level = $_POST['risk_level'];
        $return_interval = $_POST['return_interval'];
        $is_active = isset($_POST['status']) ? 1 : 0;
        $featured = isset($_POST['featured']) ? 1 : 0;
        
        // Validate inputs
        if (empty($name) || empty($plan_type) || empty($category) || $min_amount <= 0 || $roi_percent <= 0 || $duration_days <= 0) {
            $error = "Please fill all required fields with valid values.";
        } else {
            // Update plan
            $stmt = $conn_back->prepare("
                UPDATE investment_plans 
                SET name = ?, description = ?, plan_type = ?, category = ?,
                    min_amount = ?, max_amount = ?, roi_percent = ?, duration_days = ?,
                    risk_level = ?, return_interval = ?, is_active = ?, featured = ?,
                    updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("ssssdddssiii", $name, $description, $plan_type, $category, $min_amount, $max_amount, $roi_percent, $duration_days, $risk_level, $return_interval, $is_active, $featured, $plan_id);
            
            if ($stmt->execute()) {
                logAdminActivity($_SESSION['admin_id'], 'Update Investment Plan', "Updated plan #$plan_id: $name");
                $message = "Investment plan updated successfully.";
            } else {
                $error = "Error updating investment plan: " . $stmt->error;
            }
            
            $stmt->close();
        }
    }
    
    // Delete plan
    if (isset($_POST['delete_plan'])) {
        $plan_id = $_POST['plan_id'];
        
        // Check if plan is in use
        $stmt = $conn_back->prepare("SELECT COUNT(*) as count FROM investments WHERE plan_id = ? AND status = 'active'");
        $stmt->bind_param("i", $plan_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $active_investments = $row['count'];
        $stmt->close();
        
        if ($active_investments > 0) {
            $error = "Cannot delete plan with active investments. Deactivate it instead.";
        } else {
            $stmt = $conn_back->prepare("DELETE FROM investment_plans WHERE id = ?");
            $stmt->bind_param("i", $plan_id);
            
            if ($stmt->execute()) {
                logAdminActivity($_SESSION['admin_id'], 'Delete Investment Plan', "Deleted plan #$plan_id");
                $message = "Investment plan deleted successfully.";
            } else {
                $error = "Error deleting investment plan: " . $stmt->error;
            }
            
            $stmt->close();
        }
    }
}

// Get all investment plans
$sql = "SELECT * FROM investment_plans ORDER BY id ASC";
$result = $conn_back->query($sql);
$plans = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $plans[] = $row;
    }
}

include_once __DIR__ . '/layout/header.php';
?>

<style>
.plan-card-wrapper {
    position: relative;
    margin-bottom: 30px;
    height: 100%;
}
.plan-card {
    position: relative;
    z-index: 1;
    transition: transform 0.2s;
}
.plan-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Investment Plans Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPlanModal">
            <i class="fas fa-plus mr-2"></i> Add New Plan
        </button>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row gy-4">
    <style>
                .plan-card-wrapper {
                    height: 450px !important;
                }
            </style>
        <?php foreach ($plans as $plan): ?>
            <div class="col-lg-4 col-md-6">
                <div class="plan-card-wrapper">
                    <div class="card plan-card <?= $plan['featured'] ? 'border-left-primary' : '' ?> shadow h-100">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary text-truncate" style="max-width: 70%;" title="<?= htmlspecialchars($plan['name']) ?>"><?= htmlspecialchars($plan['name']) ?></h6>
                        <div>
                            <?php if ($plan['featured']): ?>
                                <span class="badge badge-primary">Featured</span>
                            <?php endif; ?>
                                <?php if ($plan['is_active']): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Inactive</span>
                            <?php endif; ?>
                        </div>
                    </div>
                        <div class="card-body" style="min-height: 350px; overflow: hidden; background-color: #4e73df; color: #ffffff;">
                            <!-- Plan Type and Category Info -->
                            <div class="d-flex justify-content-between mb-3">
                                <span class="badge badge-info"><?= htmlspecialchars($plan['plan_type']) ?></span>
                                <span class="badge badge-secondary"><?= htmlspecialchars($plan['category']) ?></span>
                            </div>

                            <!-- ROI Info with Icon -->
                            <div class="row no-gutters align-items-center mb-3">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Return Rate</div>
                                    <div class="h4 mb-0 font-weight-bold text-white"><?= $plan['roi_percent'] ?>%</div>
                            </div>
                            <div class="col-auto">
                                    <i class="fas fa-chart-line fa-2x text-white"></i>
                            </div>
                        </div>
                        
                            <hr class="my-2" style="border-color: rgba(255,255,255,0.2);">
                        
                            <!-- Duration and Risk Level -->
                            <div class="row mb-3">
                                <div class="col-6">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Duration</div>
                                    <div class="font-weight-bold text-white"><?= $plan['duration_days'] ?? 0 ?> Days</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1">Risk Level</div>
                                    <div class="font-weight-bold text-white text-truncate" title="<?= htmlspecialchars($plan['risk_level']) ?>"><?= htmlspecialchars($plan['risk_level']) ?></div>
                                </div>
                        </div>
                        
                            <!-- Investment Range -->
                            <div class="row mb-3">
                            <div class="col-6">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Min Investment</div>
                                    <div class="font-weight-bold text-white">$<?= number_format($plan['min_amount'], 2) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Max Investment</div>
                                    <div class="font-weight-bold text-white">
                                    <?php if ($plan['max_amount'] > 0): ?>
                                        $<?= number_format($plan['max_amount'], 2) ?>
                                    <?php else: ?>
                                        Unlimited
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                            
                            <!-- Return Interval -->
                            <div class="mb-3">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Return Interval</div>
                                <div class="font-weight-bold text-white"><?= htmlspecialchars($plan['return_interval']) ?></div>
                            </div>
                        
                        <?php if (!empty($plan['description'])): ?>
                                <hr class="my-2" style="border-color: rgba(255,255,255,0.2);">
                            <div class="mb-0">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Description</div>
                                    <div class="description-container mt-2 mb-2">
                                        <p class="mb-0" style="color: #ffffff; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;"><?= nl2br(htmlspecialchars($plan['description'] ?? '')) ?></p>
                                    </div>
                            </div>
                        <?php endif; ?>
                    </div>
                        <div class="card-footer py-2">
                        <div class="d-flex justify-content-between">
                                <button class="btn btn-primary btn-sm edit-plan" data-bs-toggle="modal" data-bs-target="#editPlanModal" 
                                    data-id="<?= $plan['id'] ?>"
                                    data-name="<?= htmlspecialchars($plan['name']) ?>"
                                        data-description="<?= htmlspecialchars($plan['description'] ?? '') ?>"
                                    data-min="<?= $plan['min_amount'] ?>"
                                    data-max="<?= $plan['max_amount'] ?>"
                                        data-rate="<?= $plan['roi_percent'] ?>"
                                        data-duration="<?= $plan['duration_days'] ?>"
                                        data-status="<?= $plan['is_active'] ?? 0 ?>"
                                        data-featured="<?= $plan['featured'] ?>"
                                        data-plan-type="<?= htmlspecialchars($plan['plan_type']) ?>"
                                        data-category="<?= htmlspecialchars($plan['category']) ?>"
                                        data-risk-level="<?= htmlspecialchars($plan['risk_level']) ?>"
                                        data-return-interval="<?= htmlspecialchars($plan['return_interval']) ?>">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                                <button class="btn btn-danger btn-sm delete-plan" data-bs-toggle="modal" data-bs-target="#deletePlanModal" 
                                    data-id="<?= $plan['id'] ?>" 
                                    data-name="<?= htmlspecialchars($plan['name']) ?>">
                                <i class="fas fa-trash mr-1"></i> Delete
                            </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($plans)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    No investment plans found. Click the "Add New Plan" button to create your first plan.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Plan Modal -->
<div class="modal fade" id="addPlanModal" tabindex="-1" role="dialog" aria-labelledby="addPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addPlanModalLabel">Add New Investment Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Plan Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="plan_type">Plan Type *</label>
                                <select class="form-control" id="plan_type" name="plan_type" required>
                                    <option value="MF">Mutual Fund (MF)</option>
                                    <option value="ETF">Exchange Traded Fund (ETF)</option>
                                    <option value="SIP">Systematic Investment Plan (SIP)</option>
                                    <option value="NFO">New Fund Offer (NFO)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category">Category *</label>
                                <select class="form-control" id="category" name="category" required>
                                    <option value="Direct">Direct</option>
                                    <option value="Regular">Regular</option>
                                    <option value="Thematic">Thematic</option>
                                    <option value="Index">Index</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="min_amount">Minimum Investment Amount ($) *</label>
                                <input type="number" class="form-control" id="min_amount" name="min_amount" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="max_amount">Maximum Investment Amount ($)</label>
                                <input type="number" class="form-control" id="max_amount" name="max_amount" step="0.01" min="0">
                                <small class="form-text text-muted">Enter 0 for unlimited</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="roi_percent">Interest Rate (%) *</label>
                                <input type="number" class="form-control" id="roi_percent" name="roi_percent" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="duration_days">Duration (Days) *</label>
                                <input type="number" class="form-control" id="duration_days" name="duration_days" min="1" required>
                                <small class="form-text text-muted">Number of days for the investment period</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="risk_level">Risk Level *</label>
                                <select class="form-control" id="risk_level" name="risk_level" required>
                                    <option value="Low">Low</option>
                                    <option value="Moderate">Moderate</option>
                                    <option value="High">High</option>
                                    <option value="Thematic">Thematic</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="return_interval">Return Interval *</label>
                                <select class="form-control" id="return_interval" name="return_interval" required>
                                    <option value="Daily">Daily</option>
                                    <option value="Weekly">Weekly</option>
                                    <option value="Monthly">Monthly</option>
                                    <option value="Quarterly">Quarterly</option>
                                    <option value="Annually">Annually</option>
                                </select>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editPlanModalLabel">Edit Investment Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" id="edit_plan_id" name="plan_id">
                    <div class="form-group">
                        <label for="edit_name">Plan Name *</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_plan_type">Plan Type *</label>
                                <select class="form-control" id="edit_plan_type" name="plan_type" required>
                                    <option value="MF">Mutual Fund (MF)</option>
                                    <option value="ETF">Exchange Traded Fund (ETF)</option>
                                    <option value="SIP">Systematic Investment Plan (SIP)</option>
                                    <option value="NFO">New Fund Offer (NFO)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_category">Category *</label>
                                <select class="form-control" id="edit_category" name="category" required>
                                    <option value="Direct">Direct</option>
                                    <option value="Regular">Regular</option>
                                    <option value="Thematic">Thematic</option>
                                    <option value="Index">Index</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_min_amount">Minimum Investment Amount ($) *</label>
                                <input type="number" class="form-control" id="edit_min_amount" name="min_amount" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_max_amount">Maximum Investment Amount ($)</label>
                                <input type="number" class="form-control" id="edit_max_amount" name="max_amount" step="0.01" min="0">
                                <small class="form-text text-muted">Enter 0 for unlimited</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_roi_percent">Interest Rate (%) *</label>
                                <input type="number" class="form-control" id="edit_roi_percent" name="roi_percent" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_duration_days">Duration (Days) *</label>
                                <input type="number" class="form-control" id="edit_duration_days" name="duration_days" min="1" required>
                                <small class="form-text text-muted">Number of days for the investment period</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_risk_level">Risk Level *</label>
                                <select class="form-control" id="edit_risk_level" name="risk_level" required>
                                    <option value="Low">Low</option>
                                    <option value="Moderate">Moderate</option>
                                    <option value="High">High</option>
                                    <option value="Thematic">Thematic</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_return_interval">Return Interval *</label>
                                <select class="form-control" id="edit_return_interval" name="return_interval" required>
                                    <option value="Daily">Daily</option>
                                    <option value="Weekly">Weekly</option>
                                    <option value="Monthly">Monthly</option>
                                    <option value="Quarterly">Quarterly</option>
                                    <option value="Annually">Annually</option>
                                </select>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
                <h5 class="modal-title" id="deletePlanModalLabel">Delete Investment Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" id="delete_plan_id" name="plan_id">
                    <p>Are you sure you want to delete the investment plan: <strong id="delete_plan_name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone. Plans with active investments cannot be deleted.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_plan" class="btn btn-danger">Delete Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit plan
    $('.edit-plan').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var description = $(this).data('description');
        var min = $(this).data('min');
        var max = $(this).data('max');
        var rate = $(this).data('rate');
        var duration = $(this).data('duration');
        var status = $(this).data('status');
        var featured = $(this).data('featured');
        var plan_type = $(this).data('plan-type');
        var category = $(this).data('category');
        var risk_level = $(this).data('risk-level');
        var return_interval = $(this).data('return-interval');
        
        $('#edit_plan_id').val(id);
        $('#edit_name').val(name);
        $('#edit_description').val(description);
        $('#edit_min_amount').val(min);
        $('#edit_max_amount').val(max);
        $('#edit_roi_percent').val(rate);
        $('#edit_duration_days').val(duration);
        $('#edit_plan_type').val(plan_type);
        $('#edit_category').val(category);
        $('#edit_risk_level').val(risk_level);
        $('#edit_return_interval').val(return_interval);
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
    
    // Initialize Bootstrap 5 modals
    var modals = document.querySelectorAll('.modal');
    if (modals.length > 0 && typeof bootstrap !== 'undefined') {
        modals.forEach(function(modal) {
            new bootstrap.Modal(modal);
        });
    }
});
</script>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 