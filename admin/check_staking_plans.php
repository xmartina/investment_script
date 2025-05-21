<?php
// Debugging script to check staking plans
require_once __DIR__ . '/include/config.php';

echo "<h1>Debugging Staking Plans</h1>";

// Check if database connection is working
if (!$conn_back) {
    echo "<p>Error: Database connection is not working</p>";
    exit;
}

echo "<p>Database connection successful!</p>";

// Check if staking_plans table exists
$table_check = $conn_back->query("SHOW TABLES LIKE 'staking_plans'");
if ($table_check->num_rows == 0) {
    echo "<p>Error: staking_plans table doesn't exist!</p>";
    exit;
}

echo "<p>staking_plans table exists!</p>";

// Check table structure
echo "<h2>Table Structure:</h2>";
$structure = $conn_back->query("DESCRIBE staking_plans");
if (!$structure) {
    echo "<p>Error getting table structure: " . $conn_back->error . "</p>";
    exit;
}

echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = $structure->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Check data in the table
echo "<h2>Table Data:</h2>";
$data = $conn_back->query("SELECT * FROM staking_plans");
if (!$data) {
    echo "<p>Error getting table data: " . $conn_back->error . "</p>";
    exit;
}

if ($data->num_rows == 0) {
    echo "<p>No data found in staking_plans table!</p>";
    exit;
}

echo "<table border='1'>";
echo "<tr>";
$fields = $data->fetch_fields();
foreach ($fields as $field) {
    echo "<th>" . $field->name . "</th>";
}
echo "</tr>";

$data->data_seek(0); // Reset pointer to beginning
while ($row = $data->fetch_assoc()) {
    echo "<tr>";
    foreach ($row as $value) {
        echo "<td>" . htmlspecialchars($value) . "</td>";
    }
    echo "</tr>";
}
echo "</table>";
?> 