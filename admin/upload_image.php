<?php
// Admin - Image Upload Handler for CKEditor
session_start();
require_once __DIR__ . '/include/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => ['message' => 'Unauthorized access']]);
    exit();
}

// Function to sanitize file name
function sanitizeFileName($filename) {
    // Remove any characters that aren't alphanumeric, underscore, dash, or dot
    $filename = preg_replace('/[^\w\-\.]/', '_', $filename);
    // Ensure filename doesn't contain multiple consecutive dots
    $filename = preg_replace('/\.{2,}/', '.', $filename);
    return $filename;
}

// Function to generate a unique filename
function generateUniqueFilename($filename) {
    $pathInfo = pathinfo($filename);
    $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';
    $baseName = isset($pathInfo['filename']) ? $pathInfo['filename'] : 'upload';
    $timestamp = time();
    $randomString = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
    
    return $baseName . '_' . $timestamp . '_' . $randomString . $extension;
}

// Set up response
$response = ['uploaded' => 0, 'error' => ['message' => 'An error occurred during upload']];

// Check if file was uploaded
if (isset($_FILES['upload']) && $_FILES['upload']['error'] === UPLOAD_ERR_OK) {
    // Get file information
    $file = $_FILES['upload'];
    $fileName = $file['name'];
    $fileTmpPath = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    
    // Get file extension
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Define allowed extensions
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // Check if extension is allowed
    if (in_array($fileExtension, $allowedExtensions)) {
        // Check file size (limit to 5MB)
        if ($fileSize <= 5 * 1024 * 1024) {
            // Create upload directory if it doesn't exist
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/images/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Sanitize and make filename unique
            $sanitizedFileName = sanitizeFileName($fileName);
            $uniqueFileName = generateUniqueFilename($sanitizedFileName);
            $uploadPath = $uploadDir . $uniqueFileName;
            
            // Move uploaded file to destination
            if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                // Success - set response
                $response = [
                    'uploaded' => 1,
                    'fileName' => $uniqueFileName,
                    'url' => '/uploads/images/' . $uniqueFileName
                ];
                
                // Log admin activity
                $admin_id = $_SESSION['admin_id'];
                $action = "Uploaded image: " . $uniqueFileName;
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $log_stmt = $conn_back->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $admin_id, $action, $ip);
                $log_stmt->execute();
                
                // Insert into media library
                $fileUrl = '/uploads/images/' . $uniqueFileName;
                $insert_stmt = $conn_back->prepare("INSERT INTO media_library (file_name, file_path, file_type, file_size, uploaded_by, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $insert_stmt->bind_param("sssii", $uniqueFileName, $fileUrl, $fileExtension, $fileSize, $admin_id);
                $insert_stmt->execute();
            } else {
                $response['error']['message'] = 'Error moving uploaded file to destination.';
            }
        } else {
            $response['error']['message'] = 'File size exceeds the 5MB limit.';
        }
    } else {
        $response['error']['message'] = 'Invalid file type. Allowed types: ' . implode(', ', $allowedExtensions);
    }
} else {
    // Set specific error message based on upload error code
    switch ($_FILES['upload']['error'] ?? UPLOAD_ERR_NO_FILE) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $response['error']['message'] = 'File size exceeds the maximum allowed limit.';
            break;
        case UPLOAD_ERR_PARTIAL:
            $response['error']['message'] = 'The file was only partially uploaded.';
            break;
        case UPLOAD_ERR_NO_FILE:
            $response['error']['message'] = 'No file was uploaded.';
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $response['error']['message'] = 'Missing temporary folder.';
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $response['error']['message'] = 'Failed to write file to disk.';
            break;
        case UPLOAD_ERR_EXTENSION:
            $response['error']['message'] = 'A PHP extension stopped the file upload.';
            break;
    }
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit; 