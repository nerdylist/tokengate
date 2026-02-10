<?php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/VoteController.php';

header('Content-Type: application/json');

$controller = new VoteController();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    // GET requests - get vote counts and check voting status
    if ($method === 'GET') {
        if (isset($_GET['bounty_id']) && isset($_GET['profile_id'])) {
            // Get vote count and check if current user has voted
            $bountyId = $_GET['bounty_id'];
            $profileId = $_GET['profile_id'];

            $voteCount = $controller->getVoteCount($bountyId, $profileId);
            $hasVoted = $controller->hasVoted($bountyId, $profileId);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => [
                    'vote_count' => $voteCount,
                    'has_voted' => $hasVoted
                ]
            ]);
        } elseif (isset($_GET['bounty_id'])) {
            // Get all votes for a bounty grouped by profile
            $votes = $controller->getBountyVotes($_GET['bounty_id']);

            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $votes]);
        } elseif (isset($_GET['user_id']) || isset($_GET['my_votes'])) {
            // Get votes by user
            $userId = isset($_GET['my_votes']) ? null : $_GET['user_id'];
            $votes = $controller->getUserVotes($userId);

            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $votes]);
        } elseif (isset($_GET['top_voted'])) {
            // Get top voted profiles for a bounty
            if (empty($_GET['bounty_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'bounty_id is required for top_voted']);
                exit;
            }

            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $topProfiles = $controller->getTopVoted($_GET['bounty_id'], $limit);

            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $topProfiles]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'bounty_id and profile_id are required']);
        }
        exit;
    }

    // POST requests - vote and unvote
    if ($method === 'POST') {
        // Parse JSON input if content-type is JSON
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            $_POST = $input ?? $_POST;
        }

        switch ($action) {
            case 'vote':
                if (empty($_POST['bounty_id']) || empty($_POST['profile_id'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'bounty_id and profile_id are required']);
                    exit;
                }

                $voteId = $controller->vote($_POST['bounty_id'], $_POST['profile_id']);

                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'message' => 'Vote cast successfully',
                    'data' => ['id' => $voteId]
                ]);
                break;

            case 'unvote':
                if (empty($_POST['bounty_id']) || empty($_POST['profile_id'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'bounty_id and profile_id are required']);
                    exit;
                }

                $affectedRows = $controller->unvote($_POST['bounty_id'], $_POST['profile_id']);

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Vote removed successfully',
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
    } elseif (strpos($errorMessage, 'Permission denied') !== false) {
        http_response_code(403);
    } elseif (strpos($errorMessage, 'not found') !== false) {
        http_response_code(404);
    } elseif (strpos($errorMessage, 'Missing required') !== false ||
              strpos($errorMessage, 'Invalid') !== false ||
              strpos($errorMessage, 'already voted') !== false) {
        http_response_code(400);
    } else {
        http_response_code(500);
    }

    echo json_encode(['success' => false, 'error' => $errorMessage]);
}
