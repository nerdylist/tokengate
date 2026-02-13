<?php

// Aggressive error suppression to prevent any warnings/notices from corrupting JSON
error_reporting(0);
ini_set('display_errors', '0');

// Start output buffering to ensure clean JSON response
ob_start();

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Profile.php';
require_once __DIR__ . '/../classes/Database.php';

// Clean any output that may have been generated
ob_clean();

// Set Content-Type header early
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// GET endpoints
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'get_statuses') {
        try {
            $profileModel = new Profile();
            $statuses = $profileModel->getAllActiveStatuses();
            echo json_encode([
                'success' => true,
                'statuses' => $statuses
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Failed to fetch statuses']);
        }
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Check if user is logged in
if (!Auth::check()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized. Please log in.']);
    exit;
}

$userId = Auth::id();
$profileModel = new Profile();
$profile = $profileModel->where('user_id', '=', $userId)->first();

if (!$profile) {
    echo json_encode(['success' => false, 'error' => 'Profile not found']);
    exit;
}

switch ($action) {
    case 'update_bio':
        $bio = trim($_POST['bio'] ?? '');

        if (strlen($bio) > 5000) {
            echo json_encode(['success' => false, 'error' => 'Bio is too long (max 5000 characters)']);
            exit;
        }

        try {
            $db = Database::getInstance();
            $sql = "UPDATE profiles SET bio = ?, updated_at = ? WHERE id = ?";
            $db->execute($sql, [$bio, date('Y-m-d H:i:s'), $profile['id']]);

            echo json_encode([
                'success' => true,
                'message' => 'Bio updated successfully',
                'bio' => $bio
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Failed to update bio']);
        }
        break;

    case 'update_rate':
        $rate = trim($_POST['rate'] ?? '');

        if (!is_numeric($rate) || $rate < 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid rate. Please enter a valid number.']);
            exit;
        }

        if ($rate > 9999) {
            echo json_encode(['success' => false, 'error' => 'Rate is too high (max $9,999)']);
            exit;
        }

        try {
            $db = Database::getInstance();
            $sql = "UPDATE profiles SET hourly_rate = ?, updated_at = ? WHERE id = ?";
            $db->execute($sql, [$rate, date('Y-m-d H:i:s'), $profile['id']]);

            echo json_encode([
                'success' => true,
                'message' => 'Hourly rate updated successfully',
                'rate' => number_format($rate)
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Failed to update rate']);
        }
        break;

    case 'update_status':
        $statusId = trim($_POST['status_id'] ?? '');

        // Validate status_id is numeric
        if (!is_numeric($statusId)) {
            echo json_encode(['success' => false, 'error' => 'Invalid status ID']);
            exit;
        }

        // Verify the status exists in profile_statuses table and is active
        $db = Database::getInstance();
        $statusCheck = $db->queryOne("SELECT * FROM profile_statuses WHERE id = ? AND is_active = 1", [$statusId]);

        if (!$statusCheck) {
            echo json_encode(['success' => false, 'error' => 'Invalid or inactive status']);
            exit;
        }

        try {
            $sql = "UPDATE profiles SET status_id = ?, updated_at = ? WHERE id = ?";
            $db->execute($sql, [$statusId, date('Y-m-d H:i:s'), $profile['id']]);

            echo json_encode([
                'success' => true,
                'message' => 'Status updated successfully',
                'statusId' => $statusCheck['id'],
                'statusName' => strtolower($statusCheck['name']),
                'statusSlug' => $statusCheck['slug'],
                'statusColor' => $statusCheck['color'],
                'statusIcon' => $statusCheck['icon']
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Failed to update status']);
        }
        break;

    case 'upload_avatar':
        if (!isset($_FILES['avatar'])) {
            echo json_encode(['success' => false, 'error' => 'No file was selected']);
            exit;
        }

        // Check for upload errors with specific messages
        $uploadError = $_FILES['avatar']['error'];
        if ($uploadError !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File is too large. Maximum size is 2MB.',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds the maximum allowed size.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded. Please try again.',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Server error: Missing temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Server error: Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'Upload was stopped by a server extension.'
            ];

            $errorMessage = $errorMessages[$uploadError] ?? 'An unknown error occurred during upload.';
            echo json_encode(['success' => false, 'error' => $errorMessage]);
            exit;
        }

        $file = $_FILES['avatar'];
        $maxFileSize = 2 * 1024 * 1024; // 2MB (matches PHP upload_max_filesize)

        // Validate file size
        if ($file['size'] > $maxFileSize) {
            echo json_encode(['success' => false, 'error' => 'File is too large (max 2MB)']);
            exit;
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            echo json_encode(['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.']);
            exit;
        }

        // Get file extension
        $extension = '';
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                $extension = 'jpg';
                break;
            case 'image/png':
                $extension = 'png';
                break;
            case 'image/gif':
                $extension = 'gif';
                break;
            case 'image/webp':
                $extension = 'webp';
                break;
        }

        // Hash the file contents
        $fileContents = file_get_contents($file['tmp_name']);
        $hash = md5($fileContents);
        $filename = $hash . '.' . $extension;
        $uploadPath = __DIR__ . '/../uploads/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            echo json_encode(['success' => false, 'error' => 'Failed to save file']);
            exit;
        }

        // Update database with avatar URL
        $avatarUrl = '/uploads/' . $filename;

        try {
            $db = Database::getInstance();
            $sql = "UPDATE profiles SET avatar_url = ?, updated_at = ? WHERE id = ?";
            $db->execute($sql, [$avatarUrl, date('Y-m-d H:i:s'), $profile['id']]);

            // Clear any buffered output to ensure clean JSON
            ob_end_clean();
            ob_start();

            echo json_encode([
                'success' => true,
                'message' => 'Avatar uploaded successfully',
                'avatar_url' => $avatarUrl
            ]);
        } catch (Exception $e) {
            // Clean up uploaded file if database update fails
            if (file_exists($uploadPath)) {
                unlink($uploadPath);
            }

            // Clear any buffered output to ensure clean JSON
            ob_end_clean();
            ob_start();

            echo json_encode(['success' => false, 'error' => 'Failed to update avatar']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}
