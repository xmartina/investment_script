<?php
// This script creates a default profile image
$width = 250;
$height = 250;
$image = imagecreatetruecolor($width, $height);

// Set background color (light blue)
$bg_color = imagecolorallocate($image, 100, 149, 237);
imagefill($image, 0, 0, $bg_color);

// Draw the circle for the avatar
$circle_color = imagecolorallocate($image, 255, 255, 255);
imagefilledellipse($image, $width/2, $height/2, $width*0.8, $height*0.8, $circle_color);

// Draw a simple user silhouette
$silhouette_color = imagecolorallocate($image, 100, 100, 100);
// Head
imagefilledellipse($image, $width/2, $height/2 - 20, $width*0.3, $height*0.3, $silhouette_color);
// Body
imagefilledrectangle($image, $width/2 - 30, $height/2 + 20, $width/2 + 30, $height/2 + 80, $silhouette_color);

// Save the image
imagepng($image, 'default.png');
imagedestroy($image);

echo "Default profile image has been created.";
?> 