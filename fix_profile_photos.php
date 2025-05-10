<?php
// This script fixes profile photo paths in the database that have unencoded spaces
header('Content-Type: text/html; charset=utf-8');
echo "<h2>Profile Photo Path Fix Utility</h2>";

// Include config
if (file_exists(__DIR__ . '/include/config.php')) {
    include_once __DIR__ . '/include/config.php';
    
    if (isset($conn_back)) {
        echo "<p>Database connection successful!</p>";
        
        // Find profiles with spaces in their paths
        $sql = "SELECT id, first_name, last_name, profile_photo FROM users WHERE profile_photo LIKE '% %'";
        $result = $conn_back->query($sql);
        
        if ($result && $result->num_rows > 0) {
            echo "<p>Found " . $result->num_rows . " user(s) with spaces in profile photo paths.</p>";
            echo "<table border='1' cellpadding='5' style='border-collapse:collapse'>";
            echo "<tr><th>ID</th><th>Name</th><th>Original Path</th><th>Fixed Path</th><th>Status</th></tr>";
            
            while ($row = $result->fetch_assoc()) {
                $user_id = $row['id'];
                $original_path = $row['profile_photo'];
                $status = '';
                
                // Process based on path type
                if (strpos($original_path, '/profile_photos/') === 0) {
                    // Local file - check if we need to modify the filename
                    $path_parts = pathinfo($original_path);
                    $directory = $path_parts['dirname'];
                    $filename = $path_parts['basename'];
                    
                    // First check if the file exists with spaces
                    $full_path = __DIR__ . $original_path;
                    
                    if (file_exists($full_path)) {
                        // File exists with spaces, let's fix the filename in the filesystem
                        $sanitized_filename = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $filename);
                        $new_full_path = __DIR__ . $directory . '/' . $sanitized_filename;
                        $new_path = $directory . '/' . $sanitized_filename;
                        
                        // Copy the file (don't rename to avoid breaking any existing references)
                        if (copy($full_path, $new_full_path)) {
                            // Update the database with the new path
                            $update_sql = "UPDATE users SET profile_photo = ? WHERE id = ?";
                            $stmt = $conn_back->prepare($update_sql);
                            $stmt->bind_param("si", $new_path, $user_id);
                            
                            if ($stmt->execute()) {
                                $status = "<span style='color:green'>Fixed (copied file and updated DB)</span>";
                            } else {
                                $status = "<span style='color:red'>Error updating database</span>";
                            }
                        } else {
                            $status = "<span style='color:red'>Error copying file</span>";
                        }
                    } else {
                        // File doesn't exist with spaces, just update the database with encoded path
                        $sanitized_filename = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $filename);
                        $new_path = $directory . '/' . $sanitized_filename;
                        
                        $update_sql = "UPDATE users SET profile_photo = ? WHERE id = ?";
                        $stmt = $conn_back->prepare($update_sql);
                        $stmt->bind_param("si", $new_path, $user_id);
                        
                        if ($stmt->execute()) {
                            $status = "<span style='color:blue'>Updated DB only (file not found)</span>";
                        } else {
                            $status = "<span style='color:red'>Error updating database</span>";
                        }
                    }
                } else if (preg_match('/^https?:\/\//', $original_path)) {
                    // External URL - just ensure it's encoded in the database
                    $encoded_path = preg_replace_callback('/\s/', function($match) {
                        return rawurlencode($match[0]);
                    }, $original_path);
                    
                    $update_sql = "UPDATE users SET profile_photo = ? WHERE id = ?";
                    $stmt = $conn_back->prepare($update_sql);
                    $stmt->bind_param("si", $encoded_path, $user_id);
                    
                    if ($stmt->execute()) {
                        $status = "<span style='color:green'>URL encoded in database</span>";
                    } else {
                        $status = "<span style='color:red'>Error updating database</span>";
                    }
                } else {
                    // Legacy or other format - just sanitize
                    $sanitized_path = preg_replace('/\s+/', '_', $original_path);
                    
                    $update_sql = "UPDATE users SET profile_photo = ? WHERE id = ?";
                    $stmt = $conn_back->prepare($update_sql);
                    $stmt->bind_param("si", $sanitized_path, $user_id);
                    
                    if ($stmt->execute()) {
                        $status = "<span style='color:blue'>Sanitized legacy path</span>";
                    } else {
                        $status = "<span style='color:red'>Error updating database</span>";
                    }
                }
                
                // Output the results
                echo "<tr>";
                echo "<td>" . $user_id . "</td>";
                echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
                echo "<td>" . htmlspecialchars($original_path) . "</td>";
                echo "<td>" . htmlspecialchars($new_path ?? $encoded_path ?? $sanitized_path) . "</td>";
                echo "<td>" . $status . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p>No users found with spaces in their profile photo paths. Everything looks good!</p>";
        }
    } else {
        echo "<p style='color:red'>Database connection not available.</p>";
    }
} else {
    echo "<p style='color:red'>Config file not found.</p>";
}

echo "<p><a href='/check_profile_photos.php'>Run full diagnostic</a></p>";
echo "<p><a href='/'>Return to home page</a></p>";
?> 