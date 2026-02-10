<?php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/ProfileController.php';

header('Content-Type: application/json');

$controller = new ProfileController();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    // GET requests - list or show
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            // Get single profile
            $profile = $controller->show($_GET['id']);

            if (!$profile) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Profile not found']);
                exit;
            }

            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $profile]);
        } elseif (isset($_GET['user_id'])) {
            // Get profile by user ID
            $profile = $controller->getUserProfile($_GET['user_id']);

            if (!$profile) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Profile not found']);
                exit;
            }

            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $profile]);
        } else {
            // List profiles with optional filters
            $filters = [];

            if (isset($_GET['available'])) {
                $filters['available'] = $_GET['available'];
            }

            if (isset($_GET['hourly_rate_min'])) {
                $filters['hourly_rate_min'] = $_GET['hourly_rate_min'];
            }

            if (isset($_GET['hourly_rate_max'])) {
                $filters['hourly_rate_max'] = $_GET['hourly_rate_max'];
            }

            if (isset($_GET['skills'])) {
                // Skills can be comma-separated IDs
                $filters['skills'] = is_array($_GET['skills'])
                    ? $_GET['skills']
                    : explode(',', $_GET['skills']);
            }

            $profiles = $controller->index($filters);

            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $profiles]);
        }
        exit;
    }

    // POST requests - create, update, delete
    if ($method === 'POST') {
        // Parse JSON input if content-type is JSON
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            $_POST = $input ?? $_POST;
        }

        switch ($action) {
            case 'create':
                $data = [
                    'bio' => $_POST['bio'] ?? null,
                    'hourly_rate' => $_POST['hourly_rate'] ?? null,
                    'available' => $_POST['available'] ?? 1,
                    'skills' => $_POST['skills'] ?? []
                ];

                $profileId = $controller->create($data);

                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'message' => 'Profile created successfully',
                    'data' => ['id' => $profileId]
                ]);
                break;

            case 'update':
                if (empty($_POST['id'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Profile ID is required']);
                    exit;
                }

                $data = [];
                $allowedFields = ['bio', 'hourly_rate', 'available', 'skills'];

                foreach ($allowedFields as $field) {
                    if (isset($_POST[$field])) {
                        $data[$field] = $_POST[$field];
                    }
                }

                $affectedRows = $controller->update($_POST['id'], $data);

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Profile updated successfully',
                    'data' => ['affected_rows' => $affectedRows]
                ]);
                break;

            case 'delete':
                if (empty($_POST['id'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Profile ID is required']);
                    exit;
                }

                $affectedRows = $controller->delete($_POST['id']);

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Profile deleted successfully',
                    'data' => ['affected_rows' => $affectedRows]
                ]);
                break;

            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
                break;
        }
        exit;
    }

    // Method not allowed
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);

} catch (Exception $e) {
    // Determine appropriate status code
    $errorMessage = $e->getMessage();

    if (strpos($errorMessage, 'Authentication required') !== false) {
        http_response_code(401);
    } elseif (strpos($errorMessage, 'Permission denied') !== false || strpos($errorMessage, "don't own") !== false) {
        http_response_code(403);
    } elseif (strpos($errorMessage, 'not found') !== false) {
        http_response_code(404);
    } elseif (strpos($errorMessage, 'Missing required') !== false || strpos($errorMessage, 'Invalid') !== false || strpos($errorMessage, 'already has') !== false) {
        http_response_code(400);
    } else {
        http_response_code(500);
    }

    echo json_encode(['success' => false, 'error' => $errorMessage]);
}
