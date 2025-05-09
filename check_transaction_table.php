<?php
// Simple script to check the structure of the transactions table
require_once 'include/config.php';

echo "<h1>Transactions Table Structure</h1>";

$result = $conn_back->query("DESCRIBE transactions");
if ($result) {
    echo "<pre>";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . "\n";
    }
    echo "</pre>";
} else {
    echo "Error: " . $conn_back->error;
}
?> 