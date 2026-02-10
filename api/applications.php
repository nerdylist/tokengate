<?php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/ApplicationController.php';

header('Content-Type: application/json');

$controller = new ApplicationController();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    // GET requests - list applications
    if ($method === 'GET') {
        if (isset($_GET['bounty_id'])) {
            // Get applications for a bounty
            $applications = $controller->getForBounty($_GET['bounty_id']);

            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $applications]);
        } elseif (isset($_GET['profile_id'])) {
            // Get applications by a profile
            $applications = $controller->getForProfile($_GET['profile_id']);

            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $applications]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'bounty_id or profile_id is required']);
        }
        exit;
    }

    // POST requests - create, update, accept, reject, withdraw
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
                    'bounty_id' => $_POST['bounty_id'] ?? null,
                    'cover_letter' => $_POST['cover_letter'] ?? '',
                    'proposed_rate' => $_POST['proposed_rate'] ?? null
                ];

                $applicationId = $controller->create($data);

                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'message' => 'Application submitted successfully',
                    'data' => ['id' => $applicationId]
                ]);
                break;

            case 'update':
                if (empty($_POST['id'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Application ID is required']);
                    exit;
                }

                $data = [];
                $allowedFields = ['cover_letter', 'proposed_rate'];

                foreach ($allowedFields as $field) {
                    if (isset($_POST[$field])) {
                        $data[$field] = $_POST[$field];
                    }
                }

                $affectedRows = $controller->update($_POST['id'], $data);

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Application updated successfully',
                    'data' => ['affected_rows' => $affectedRows]
                ]);
                break;

            case 'accept':
                if (empty($_POST['id'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Application ID is required']);
                    exit;
                }

                $affectedRows = $controller->accept($_POST['id']);

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Application accepted successfully',
                    'data' => ['affected_rows' => $affectedRows]
                ]);
                break;

            case 'reject':
                if (empty($_POST['id'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Application ID is required']);
                    exit;
                }

                $affectedRows = $controller->reject($_POST['id']);

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Application rejected successfully',
                    'data' => ['affected_rows' => $affectedRows]
                ]);
                break;

            case 'withdraw':
                if (empty($_POST['id'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Application ID is required']);
                    exit;
                }

                $affectedRows = $controller->withdraw($_POST['id']);

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Application withdrawn successfully',
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
    } elseif (strpos($errorMessage, 'Missing required') !== false ||
              strpos($errorMessage, 'Invalid') !== false ||
              strpos($errorMessage, 'already applied') !== false ||
              strpos($errorMessage, 'not accepting') !== false ||
              strpos($errorMessage, 'cannot apply') !== false ||
              strpos($errorMessage, 'Cannot update') !== false ||
              strpos($errorMessage, 'Can only') !== false ||
              strpos($errorMessage, 'is not') !== false ||
              strpos($errorMessage, 'must create') !== false) {
        http_response_code(400);
    } else {
        http_response_code(500);
    }

    echo json_encode(['success' => false, 'error' => $errorMessage]);
}
