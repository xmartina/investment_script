<?php
// Script to check and fix staking plans is_active issue
require_once __DIR__ . '/include/config.php';

echo "<h1>Staking Plans Status Check</h1>";

// Get all staking plans
$query = "SELECT id, name, is_active FROM staking_plans ORDER BY id ASC";
$result = $conn_back->query($query);

echo "<h2>Current Status</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>is_active Value</th><th>is_active Type</th></tr>";

$hasIssues = false;

if ($result && $result->num_rows > 0) {
    while ($plan = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$plan['id']}</td>";
        echo "<td>{$plan['name']}</td>";
        echo "<td>{$plan['is_active']}</td>";
        echo "<td>" . gettype($plan['is_active']) . "</td>";
        echo "</tr>";
        
        // Check if is_active is not a proper boolean value (0 or 1)
        if ($plan['is_active'] !== '0' && $plan['is_active'] !== '1' && 
            $plan['is_active'] !== 0 && $plan['is_active'] !== 1) {
            $hasIssues = true;
        }
    }
} else {
    echo "<tr><td colspan='4'>No staking plans found</td></tr>";
}

echo "</table>";

// Process fix if requested
if (isset($_GET['fix']) && $_GET['fix'] == 1) {
    // Update all plans to ensure is_active is a proper boolean
    $update_query = "UPDATE staking_plans SET is_active = 1 WHERE is_active != 0";
    if ($conn_back->query($update_query)) {
        echo "<div style='color:green; margin-top:20px;'>Successfully updated staking plans. All active plans now have is_active = 1</div>";
    } else {
        echo "<div style='color:red; margin-top:20px;'>Error updating staking plans: " . $conn_back->error . "</div>";
    }
    
    // Show current status after fix
    echo "<h2>Status After Fix</h2>";
    $result = $conn_back->query("SELECT id, name, is_active FROM staking_plans ORDER BY id ASC");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>is_active Value</th><th>is_active Type</th></tr>";
    
    if ($result && $result->num_rows > 0) {
        while ($plan = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$plan['id']}</td>";
            echo "<td>{$plan['name']}</td>";
            echo "<td>{$plan['is_active']}</td>";
            echo "<td>" . gettype($plan['is_active']) . "</td>";
            echo "</tr>";
        }
    }
    
    echo "</table>";
}

// Show fix button if there are issues
if ($hasIssues) {
    echo "<div style='margin-top:20px;'>";
    echo "<p style='color:red;'>Potential issues detected with is_active values. Click the button below to fix:</p>";
    echo "<a href='?fix=1' style='padding:10px; background-color:blue; color:white; text-decoration:none;'>Fix is_active Values</a>";
    echo "</div>";
} else {
    echo "<div style='margin-top:20px; color:green;'>All is_active values appear to be correctly set.</div>";
}

// Show back link
echo "<div style='margin-top:20px;'>";
echo "<a href='/admin/staking_plans.php'>Back to Staking Plans Admin</a>";
echo "</div>";
?> 