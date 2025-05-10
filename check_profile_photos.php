<?php
// This is a diagnostic and repair script for profile photos
header('Content-Type: text/html; charset=utf-8');
echo "<h2>Profile Photos Diagnostic</h2>";

// Check profile_photos directory
$profile_photos_dir = __DIR__ . '/profile_photos';
$webroot_path = __DIR__;
$relative_path = '/profile_photos';

echo "<h3>Checking Directory Structure</h3>";
echo "Web root path: " . $webroot_path . "<br>";
echo "Profile photos directory: " . $profile_photos_dir . "<br>";

if (!file_exists($profile_photos_dir)) {
    echo "<p style='color:red'>⚠️ Profile photos directory doesn't exist! Creating it now...</p>";
    if (mkdir($profile_photos_dir, 0755, true)) {
        echo "<p style='color:green'>✅ Successfully created profile photos directory</p>";
    } else {
        echo "<p style='color:red'>❌ Failed to create profile photos directory. Check permissions.</p>";
    }
} else {
    echo "<p style='color:green'>✅ Profile photos directory exists</p>";
    
    // Check if directory is writable
    if (is_writable($profile_photos_dir)) {
        echo "<p style='color:green'>✅ Profile photos directory is writable</p>";
    } else {
        echo "<p style='color:red'>❌ Profile photos directory is not writable. Attempting to fix permissions...</p>";
        if (chmod($profile_photos_dir, 0755)) {
            echo "<p style='color:green'>✅ Permissions fixed</p>";
        } else {
            echo "<p style='color:red'>❌ Could not fix permissions</p>";
        }
    }
}

// Check default image
$default_image = $profile_photos_dir . '/default.png';
echo "<h3>Checking Default Image</h3>";

if (!file_exists($default_image)) {
    echo "<p style='color:red'>⚠️ Default profile image doesn't exist! Attempting to create...</p>";
    
    if (extension_loaded('gd')) {
        echo "GD library is available. Creating image...<br>";
        $width = 250;
        $height = 250;
        $image = imagecreatetruecolor($width, $height);
        
        // Set background color
        $bg_color = imagecolorallocate($image, 70, 130, 180); // Steel Blue
        imagefill($image, 0, 0, $bg_color);
        
        // Draw circle
        $circle_color = imagecolorallocate($image, 255, 255, 255);
        imagefilledellipse($image, $width/2, $height/2, $width*0.8, $height*0.8, $circle_color);
        
        // Draw user silhouette
        $silhouette_color = imagecolorallocate($image, 100, 100, 100);
        imagefilledellipse($image, $width/2, $height/2 - 20, $width*0.3, $height*0.3, $silhouette_color);
        imagefilledrectangle($image, $width/2 - 30, $height/2 + 20, $width/2 + 30, $height/2 + 80, $silhouette_color);
        
        // Save the image
        if (imagepng($image, $default_image)) {
            echo "<p style='color:green'>✅ Default profile image created successfully</p>";
        } else {
            echo "<p style='color:red'>❌ Failed to create default profile image</p>";
        }
        imagedestroy($image);
    } else {
        echo "<p style='color:orange'>⚠️ GD library is not available. Cannot create image dynamically.</p>";
        // Create a simple placeholder file
        file_put_contents($default_image, "Default profile image placeholder");
        echo "<p style='color:orange'>Created a text file as placeholder.</p>";
    }
} else {
    echo "<p style='color:green'>✅ Default profile image exists</p>";
    echo "<img src='/profile_photos/default.png' style='width:100px;height:100px;border-radius:50%;'><br>";
}

// Check database connection and profile photo paths
echo "<h3>Checking Database Configuration</h3>";
if (file_exists(__DIR__ . '/include/config.php')) {
    echo "<p style='color:green'>✅ Config file exists</p>";
    
    // Include minimal database connection details
    include_once __DIR__ . '/include/config.php';
    
    if (isset($conn_back)) {
        echo "<p style='color:green'>✅ Database connection variable exists</p>";
        
        // Check if any user has an invalid profile photo path
        $sql = "SELECT id, first_name, last_name, profile_photo FROM users WHERE profile_photo != '' LIMIT 10";
        $result = $conn_back->query($sql);
        
        if ($result && $result->num_rows > 0) {
            echo "<table border='1' cellpadding='5' style='border-collapse:collapse'>";
            echo "<tr><th>ID</th><th>Name</th><th>Profile Photo Path</th><th>Status</th></tr>";
            
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
                echo "<td>" . $row['profile_photo'] . "</td>";
                
                $photo_path = $row['profile_photo'];
                $status = "";
                
                if (strpos($photo_path, 'http') === 0) {
                    // External URL
                    $status = "<span style='color:blue'>External URL</span>";
                } 
                else if (strpos($photo_path, '/profile_photos/') === 0) {
                    // Local file
                    $full_path = __DIR__ . $photo_path;
                    if (file_exists($full_path)) {
                        $status = "<span style='color:green'>File exists</span>";
                    } else {
                        $status = "<span style='color:red'>File not found</span>";
                    }
                }
                else {
                    $status = "<span style='color:orange'>Unknown format</span>";
                }
                
                echo "<td>" . $status . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No users with profile photos found or query error</p>";
        }
    } else {
        echo "<p style='color:red'>❌ Database connection variable not found</p>";
    }
} else {
    echo "<p style='color:red'>❌ Config file not found</p>";
}

echo "<h3>Recommendations</h3>";
echo "<ul>";
echo "<li>Make sure the profile_photos directory has the correct permissions (usually 755)</li>";
echo "<li>If using links to external image services, ensure they're accessible and don't have CORS restrictions</li>";
echo "<li>For uploaded images, check that the file paths in the database correctly point to existing files</li>";
echo "<li>Verify that the site URL configuration in your system matches the actual domain being used</li>";
echo "</ul>";

echo "<p><a href='/'>Return to home page</a></p>";
?> 