<?php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/BountyController.php';

header('Content-Type: application/json');

$controller = new BountyController();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    // GET requests - list or show
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            // Get single bounty
            $bounty = $controller->show($_GET['id']);

            if (!$bounty) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Bounty not found']);
                exit;
            }

            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $bounty]);
        } elseif (isset($_GET['user_id'])) {
            // Get bounties by user
            $bounties = $controller->getUserBounties($_GET['user_id']);

            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $bounties]);
        } else {
            // List bounties with optional filters
            $filters = [];

            if (isset($_GET['category_id'])) {
                $filters['category_id'] = $_GET['category_id'];
            }

            if (isset($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }

            if (isset($_GET['budget_min'])) {
                $filters['budget_min'] = $_GET['budget_min'];
            }

            if (isset($_GET['budget_max'])) {
                $filters['budget_max'] = $_GET['budget_max'];
            }

            if (isset($_GET['skills'])) {
                // Skills can be comma-separated IDs
                $filters['skills'] = is_array($_GET['skills'])
                    ? $_GET['skills']
                    : explode(',', $_GET['skills']);
            }

            $bounties = $controller->index($filters);

            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $bounties]);
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
                    'title' => $_POST['title'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'category_id' => $_POST['category_id'] ?? null,
                    'budget_min' => $_POST['budget_min'] ?? null,
                    'budget_max' => $_POST['budget_max'] ?? null,
                    'deadline' => $_POST['deadline'] ?? null,
                    'skills' => $_POST['skills'] ?? []
                ];

                $bountyId = $controller->create($data);

                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'message' => 'Bounty created successfully',
                    'data' => ['id' => $bountyId]
                ]);
                break;

            case 'update':
                if (empty($_POST['id'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Bounty ID is required']);
                    exit;
                }

                $data = [];
                $allowedFields = ['title', 'description', 'category_id', 'budget_min', 'budget_max', 'deadline', 'status', 'skills'];

                foreach ($allowedFields as $field) {
                    if (isset($_POST[$field])) {
                        $data[$field] = $_POST[$field];
                    }
                }

                $affectedRows = $controller->update($_POST['id'], $data);

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Bounty updated successfully',
                    'data' => ['affected_rows' => $affectedRows]
                ]);
                break;

            case 'delete':
                if (empty($_POST['id'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Bounty ID is required']);
                    exit;
                }

                $affectedRows = $controller->delete($_POST['id']);

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Bounty deleted successfully',
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
    } elseif (strpos($errorMessage, 'Missing required') !== false || strpos($errorMessage, 'Invalid') !== false) {
        http_response_code(400);
    } else {
        http_response_code(500);
    }

    echo json_encode(['success' => false, 'error' => $errorMessage]);
}
