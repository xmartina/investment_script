<?php
// Enable full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Connection Test</h1>";

// Step 1: Test MySQL connection directly
try {
    echo "<h2>Step 1: Direct MySQL Connection Test</h2>";
    
    $host = 'localhost';
    $user = 'summitgu_exodusaipro_back';
    $password = 'exodusaipro_back';
    $database = 'summitgu_exodusaipro_back';
    
    echo "<p>Connecting to MySQL with the following credentials:</p>";
    echo "<ul>";
    echo "<li>Host: $host</li>";
    echo "<li>User: $user</li>";
    echo "<li>Password: " . str_repeat('*', strlen($password)) . "</li>";
    echo "<li>Database: $database</li>";
    echo "</ul>";
    
    $mysqli = new mysqli($host, $user, $password, $database);
    
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }
    
    echo "<p style='color:green;'>✅ MySQL connection successful!</p>";
    
    // Test running a simple query
    $result = $mysqli->query("SELECT 1 as test");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>Test query result: " . $row['test'] . "</p>";
        $result->free();
    } else {
        echo "<p style='color:red;'>❌ Failed to run test query: " . $mysqli->error . "</p>";
    }
    
    // List tables
    echo "<h3>Tables in database:</h3>";
    echo "<ul>";
    $result = $mysqli->query("SHOW TABLES");
    if ($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_row()) {
                echo "<li>" . $row[0] . "</li>";
            }
        } else {
            echo "<li>No tables found</li>";
        }
        $result->free();
    } else {
        echo "<li style='color:red;'>❌ Failed to list tables: " . $mysqli->error . "</li>";
    }
    echo "</ul>";
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ " . $e->getMessage() . "</p>";
}

// Step 2: Test config.php inclusion
try {
    echo "<h2>Step 2: Config File Test</h2>";
    
    echo "<p>Attempting to include config.php...</p>";
    
    include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';
    
    echo "<p style='color:green;'>✅ Main config.php included successfully!</p>";
    
    if (isset($conn_back)) {
        echo "<p style='color:green;'>✅ \$conn_back is available from config.php</p>";
        
        // Test running a simple query
        $result = $conn_back->query("SELECT 1 as test");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "<p>Test query result using \$conn_back: " . $row['test'] . "</p>";
            $result->free();
        } else {
            echo "<p style='color:red;'>❌ Failed to run test query with \$conn_back: " . $conn_back->error . "</p>";
        }
    } else {
        echo "<p style='color:red;'>❌ \$conn_back is not available from config.php</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ " . $e->getMessage() . "</p>";
}

// Step 3: Test admin config.php inclusion
try {
    echo "<h2>Step 3: Admin Config File Test</h2>";
    
    echo "<p>Attempting to include admin config.php...</p>";
    
    include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/include/config.php';
    
    echo "<p style='color:green;'>✅ Admin config.php included successfully!</p>";
    
    // Test if functions are available
    if (function_exists('hasPermission')) {
        echo "<p style='color:green;'>✅ hasPermission() function is available</p>";
    } else {
        echo "<p style='color:red;'>❌ hasPermission() function is not available</p>";
    }
    
    if (function_exists('logAdminActivity')) {
        echo "<p style='color:green;'>✅ logAdminActivity() function is available</p>";
    } else {
        echo "<p style='color:red;'>❌ logAdminActivity() function is not available</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ " . $e->getMessage() . "</p>";
}

// Step 4: Environment Information
echo "<h2>Step 4: Environment Information</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

// Display some of the loaded PHP extensions
echo "<h3>Loaded Extensions:</h3>";
echo "<ul>";
$extensions = get_loaded_extensions();
$important_extensions = ['mysqli', 'pdo_mysql', 'curl', 'gd', 'json', 'session', 'mbstring'];
foreach ($important_extensions as $ext) {
    if (in_array($ext, $extensions)) {
        echo "<li style='color:green;'>✅ $ext</li>";
    } else {
        echo "<li style='color:red;'>❌ $ext</li>";
    }
}
echo "</ul>";
?> 