<?php
// Admin Wallet Addresses Management
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login");
    exit();
}

$page_name = "Wallet Addresses";
$current_page = "wallet_addresses.php";
$message = "";
$error = "";

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add or update wallet address
    if (isset($_POST['save_wallet'])) {
        $currency = $_POST['currency'];
        $address = $_POST['address'];
        $wallet_type = $_POST['wallet_type'];
        
        // Validate inputs
        if (empty($currency) || empty($address) || empty($wallet_type)) {
            $error = "Please fill all required fields.";
        } else {
            // Check if the wallet address already exists
            $stmt = $conn_back->prepare("SELECT * FROM wallet_addresses WHERE currency = ?");
            $stmt->bind_param("s", $currency);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Update existing wallet address
                $stmt = $conn_back->prepare("UPDATE wallet_addresses SET address = ?, wallet_type = ? WHERE currency = ?");
                $stmt->bind_param("sss", $address, $wallet_type, $currency);
                
                if ($stmt->execute()) {
                    // Log admin activity
                    $admin_id = $_SESSION['admin_id'];
                    $action = "Updated wallet address for $currency";
                    $ip = $_SERVER['REMOTE_ADDR'];
                    
                    $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                    $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                    $log_stmt->execute();
                    
                    $message = "Wallet address for $currency updated successfully.";
                } else {
                    $error = "Error updating wallet address: " . $stmt->error;
                }
            } else {
                // Insert new wallet address
                $stmt = $conn_back->prepare("INSERT INTO wallet_addresses (currency, address, wallet_type) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $currency, $address, $wallet_type);
                
                if ($stmt->execute()) {
                    // Log admin activity
                    $admin_id = $_SESSION['admin_id'];
                    $action = "Added new wallet address for $currency";
                    $ip = $_SERVER['REMOTE_ADDR'];
                    
                    $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                    $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                    $log_stmt->execute();
                    
                    $message = "Wallet address for $currency added successfully.";
                } else {
                    $error = "Error adding wallet address: " . $stmt->error;
                }
            }
            
            $stmt->close();
        }
    }
    
    // Delete wallet address
    if (isset($_POST['delete_wallet'])) {
        $currency = $_POST['currency'];
        
        $stmt = $conn_back->prepare("DELETE FROM wallet_addresses WHERE currency = ?");
        $stmt->bind_param("s", $currency);
        
        if ($stmt->execute()) {
            // Log admin activity
            $admin_id = $_SESSION['admin_id'];
            $action = "Deleted wallet address for $currency";
            $ip = $_SERVER['REMOTE_ADDR'];
            
            $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
            $log_stmt->bind_param("iss", $admin_id, $action, $ip);
            $log_stmt->execute();
            
            $message = "Wallet address for $currency deleted successfully.";
        } else {
            $error = "Error deleting wallet address: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

// Get all wallet addresses
$sql = "SELECT * FROM wallet_addresses ORDER BY currency ASC";
$result = $conn_back->query($sql);
$wallets = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $wallets[] = $row;
    }
}

include_once __DIR__ . '/layout/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Wallet Addresses Management</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addWalletModal">
            <i class="fas fa-plus mr-2"></i> Add New Wallet Address
        </button>
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
            <h6 class="m-0 font-weight-bold text-primary">Wallet Addresses</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Currency</th>
                            <th>Wallet Type</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($wallets) > 0): ?>
                            <?php foreach ($wallets as $wallet): ?>
                                <tr>
                                    <td><?= htmlspecialchars($wallet['currency']) ?></td>
                                    <td><?= htmlspecialchars($wallet['wallet_type']) ?></td>
                                    <td>
                                        <div class="input-group">
                                            <input type="text" class="form-control wallet-address" value="<?= htmlspecialchars($wallet['address']) ?>" readonly>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary copy-btn" type="button" data-toggle="tooltip" title="Copy to clipboard">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-sm edit-wallet" data-toggle="modal" data-target="#editWalletModal"
                                                data-currency="<?= htmlspecialchars($wallet['currency']) ?>"
                                                data-address="<?= htmlspecialchars($wallet['address']) ?>"
                                                data-wallet-type="<?= htmlspecialchars($wallet['wallet_type']) ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-danger btn-sm delete-wallet" data-toggle="modal" data-target="#deleteWalletModal"
                                                data-currency="<?= htmlspecialchars($wallet['currency']) ?>">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No wallet addresses found. Click the "Add New Wallet Address" button to add one.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Wallet Modal -->
<div class="modal fade" id="addWalletModal" tabindex="-1" role="dialog" aria-labelledby="addWalletModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addWalletModalLabel">Add New Wallet Address</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="currency">Currency Code *</label>
                        <input type="text" class="form-control" id="currency" name="currency" required placeholder="e.g. BTC, ETH, USDT">
                        <small class="form-text text-muted">Enter the currency code (e.g., BTC, ETH, USDT)</small>
                    </div>
                    <div class="form-group">
                        <label for="wallet_type">Wallet Type *</label>
                        <input type="text" class="form-control" id="wallet_type" name="wallet_type" required placeholder="e.g. Bitcoin, Ethereum, USDT BEP-20">
                        <small class="form-text text-muted">Enter the wallet type or network (e.g., Bitcoin, USDT BEP-20)</small>
                    </div>
                    <div class="form-group">
                        <label for="address">Wallet Address *</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_wallet" class="btn btn-primary">Save Wallet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Wallet Modal -->
<div class="modal fade" id="editWalletModal" tabindex="-1" role="dialog" aria-labelledby="editWalletModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editWalletModalLabel">Edit Wallet Address</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_currency">Currency Code *</label>
                        <input type="text" class="form-control" id="edit_currency" name="currency" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit_wallet_type">Wallet Type *</label>
                        <input type="text" class="form-control" id="edit_wallet_type" name="wallet_type" required>
                        <small class="form-text text-muted">Enter the wallet type or network (e.g., Bitcoin, USDT BEP-20)</small>
                    </div>
                    <div class="form-group">
                        <label for="edit_address">Wallet Address *</label>
                        <textarea class="form-control" id="edit_address" name="address" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_wallet" class="btn btn-primary">Update Wallet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Wallet Modal -->
<div class="modal fade" id="deleteWalletModal" tabindex="-1" role="dialog" aria-labelledby="deleteWalletModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteWalletModalLabel">Delete Wallet Address</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" id="delete_currency" name="currency">
                    <p>Are you sure you want to delete this wallet address?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_wallet" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Edit wallet
    $('.edit-wallet').click(function() {
        var currency = $(this).data('currency');
        var address = $(this).data('address');
        var walletType = $(this).data('wallet-type');
        
        $('#edit_currency').val(currency);
        $('#edit_address').val(address);
        $('#edit_wallet_type').val(walletType);
    });
    
    // Delete wallet
    $('.delete-wallet').click(function() {
        var currency = $(this).data('currency');
        $('#delete_currency').val(currency);
    });
    
    // Copy wallet address to clipboard
    $('.copy-btn').click(function() {
        var $input = $(this).closest('.input-group').find('.wallet-address');
        $input.select();
        document.execCommand('copy');
        
        $(this).attr('data-original-title', 'Copied!').tooltip('show');
        
        // Reset tooltip after 2 seconds
        var $button = $(this);
        setTimeout(function() {
            $button.attr('data-original-title', 'Copy to clipboard');
        }, 2000);
    });
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});
</script>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 