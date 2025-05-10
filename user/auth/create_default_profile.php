<?php
// This script creates the profile_photos directory if it doesn't exist
// and copies a default profile image into it

// Define paths
$root_dir = $_SERVER['DOCUMENT_ROOT'];
$profile_photos_dir = $root_dir . '/profile_photos';
$default_image_path = $profile_photos_dir . '/default.png';

// Create the directory if it doesn't exist
if (!file_exists($profile_photos_dir)) {
    mkdir($profile_photos_dir, 0755, true);
    echo "Created profile_photos directory.<br>";
} else {
    echo "Profile photos directory already exists.<br>";
}

// Create a simple default profile image if it doesn't exist
if (!file_exists($default_image_path)) {
    // Try to use GD library to create a default image
    if (extension_loaded('gd')) {
        $width = 250;
        $height = 250;
        $image = imagecreatetruecolor($width, $height);
        
        // Set background color (blue)
        $bg_color = imagecolorallocate($image, 100, 149, 237);
        imagefill($image, 0, 0, $bg_color);
        
        // Draw circle for avatar
        $circle_color = imagecolorallocate($image, 255, 255, 255);
        imagefilledellipse($image, $width/2, $height/2, $width*0.8, $height*0.8, $circle_color);
        
        // Draw silhouette
        $silhouette_color = imagecolorallocate($image, 100, 100, 100);
        // Head
        imagefilledellipse($image, $width/2, $height/2 - 20, $width*0.3, $height*0.3, $silhouette_color);
        // Body
        imagefilledrectangle($image, $width/2 - 30, $height/2 + 20, $width/2 + 30, $height/2 + 80, $silhouette_color);
        
        // Save the image
        imagepng($image, $default_image_path);
        imagedestroy($image);
        
        echo "Created default profile image using GD library.<br>";
    } else {
        // If GD is not available, create a simple text file as a placeholder
        file_put_contents($default_image_path, "Default profile image placeholder");
        echo "Created default profile image placeholder.<br>";
    }
} else {
    echo "Default profile image already exists.<br>";
}

echo "Setup complete!";
?> 