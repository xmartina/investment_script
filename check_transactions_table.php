<?php
// Include database configuration
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';

// Set to display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Check Transactions Table Structure</h1>";

// Check if transactions table exists
$tableExists = $conn_back->query("SHOW TABLES LIKE 'transactions'");
if ($tableExists->num_rows == 0) {
    echo "<p>The transactions table does not exist yet.</p>";
    exit;
}

// Get table structure
$tableStructure = $conn_back->query("DESCRIBE transactions");
echo "<h2>Transactions Table Structure</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

while ($field = $tableStructure->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $field['Field'] . "</td>";
    echo "<td>" . $field['Type'] . "</td>";
    echo "<td>" . $field['Null'] . "</td>";
    echo "<td>" . $field['Key'] . "</td>";
    echo "<td>" . $field['Default'] . "</td>";
    echo "<td>" . $field['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr><h2>Transaction Data (Last 5 records)</h2>";
// Show sample data if any
$sampleData = $conn_back->query("SELECT * FROM transactions ORDER BY id DESC LIMIT 5");
if ($sampleData->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr>";
    
    // Get column names
    $fields = $sampleData->fetch_fields();
    foreach ($fields as $field) {
        echo "<th>" . $field->name . "</th>";
    }
    echo "</tr>";
    
    // Reset result pointer
    $sampleData->data_seek(0);
    
    // Get data
    while ($row = $sampleData->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . $value . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No data in the transactions table yet.</p>";
}
?> 