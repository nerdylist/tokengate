<?php
/**
 * Guild Forum API Endpoints
 * Handles AJAX requests for guild forum operations (threads and comments)
 */

require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Profile.php';
require_once __DIR__ . '/../classes/ProfileGuild.php';

header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();
$action = $_GET['action'] ?? '';

try {
    // Get request body for POST requests
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Fallback to POST if JSON parsing fails
        $input = $_POST;
    }

    // Helper function to get user's profile
    function getUserProfile($db) {
        $sql = "SELECT id FROM profiles WHERE user_id = ?";
        $profile = $db->queryOne($sql, [Auth::id()]);
        if (!$profile) {
            throw new Exception('Profile not found. Please create a profile first.');
        }
        return $profile;
    }

    // Helper function to verify guild membership
    function verifyGuildMembership($db, $profileId, $guildId) {
        // Admin bypass
        if (Auth::isAdmin()) {
            return true;
        }

        $sql = "SELECT id FROM profile_guilds WHERE profile_id = ? AND guild_id = ?";
        $membership = $db->queryOne($sql, [$profileId, $guildId]);
        if (!$membership) {
            throw new Exception('Guild membership required');
        }
        return true;
    }

    switch ($action) {
        case 'create_thread':
            $guildId = $input['guild_id'] ?? null;
            $title = $input['title'] ?? null;
            $content = $input['content'] ?? null;

            if ($guildId === null || $title === null || $content === null) {
                throw new Exception('Missing required parameters: guild_id, title, content');
            }

            // Get user's profile
            $profile = getUserProfile($db);

            // Verify guild membership
            verifyGuildMembership($db, $profile['id'], $guildId);

            // Create thread
            $sql = "INSERT INTO guild_threads (guild_id, profile_id, title, content, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $db->execute($sql, [
                $guildId,
                $profile['id'],
                $title,
                $content,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);

            $threadId = $db->lastInsertId();

            echo json_encode([
                'success' => true,
                'message' => 'Thread created successfully',
                'data' => ['id' => $threadId]
            ]);
            break;

        case 'create_comment':
            $threadId = $input['thread_id'] ?? null;
            $content = $input['content'] ?? null;

            if ($threadId === null || $content === null) {
                throw new Exception('Missing required parameters: thread_id, content');
            }

            // Get user's profile
            $profile = getUserProfile($db);

            // Get guild_id from thread
            $sql = "SELECT guild_id FROM guild_threads WHERE id = ?";
            $thread = $db->queryOne($sql, [$threadId]);
            if (!$thread) {
                throw new Exception('Thread not found');
            }

            // Verify guild membership
            verifyGuildMembership($db, $profile['id'], $thread['guild_id']);

            // Create comment
            $sql = "INSERT INTO guild_comments (thread_id, profile_id, content, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?)";
            $db->execute($sql, [
                $threadId,
                $profile['id'],
                $content,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);

            $commentId = $db->lastInsertId();

            echo json_encode([
                'success' => true,
                'message' => 'Comment posted successfully',
                'data' => ['id' => $commentId]
            ]);
            break;

        case 'get_threads':
            $guildId = $_GET['guild_id'] ?? null;

            if ($guildId === null) {
                throw new Exception('Missing required parameter: guild_id');
            }

            // Get user's profile
            $profile = getUserProfile($db);

            // Verify guild membership
            verifyGuildMembership($db, $profile['id'], $guildId);

            // Get threads with author info and comment count
            $sql = "SELECT
                        gt.id,
                        gt.guild_id,
                        gt.profile_id,
                        gt.title,
                        gt.content,
                        gt.created_at,
                        gt.updated_at,
                        p.profile_id as author_profile_id,
                        u.name as author_name,
                        (SELECT COUNT(*) FROM guild_comments WHERE thread_id = gt.id) as comment_count
                    FROM guild_threads gt
                    INNER JOIN profiles p ON gt.profile_id = p.id
                    INNER JOIN users u ON p.user_id = u.id
                    WHERE gt.guild_id = ?
                    ORDER BY gt.created_at DESC";

            $threads = $db->query($sql, [$guildId]);

            echo json_encode([
                'success' => true,
                'data' => $threads
            ]);
            break;

        case 'get_thread_detail':
            $threadId = $_GET['thread_id'] ?? null;

            if ($threadId === null) {
                throw new Exception('Missing required parameter: thread_id');
            }

            // Get thread with author info
            $sql = "SELECT
                        gt.id,
                        gt.guild_id,
                        gt.profile_id,
                        gt.title,
                        gt.content,
                        gt.created_at,
                        gt.updated_at,
                        p.profile_id as author_profile_id,
                        u.name as author_name
                    FROM guild_threads gt
                    INNER JOIN profiles p ON gt.profile_id = p.id
                    INNER JOIN users u ON p.user_id = u.id
                    WHERE gt.id = ?";

            $thread = $db->queryOne($sql, [$threadId]);

            if (!$thread) {
                throw new Exception('Thread not found');
            }

            // Get user's profile
            $profile = getUserProfile($db);

            // Verify guild membership for this thread's guild
            verifyGuildMembership($db, $profile['id'], $thread['guild_id']);

            // Get comments with author info
            $sql = "SELECT
                        gc.id,
                        gc.thread_id,
                        gc.profile_id,
                        gc.content,
                        gc.created_at,
                        gc.updated_at,
                        p.profile_id as author_profile_id,
                        u.name as author_name
                    FROM guild_comments gc
                    INNER JOIN profiles p ON gc.profile_id = p.id
                    INNER JOIN users u ON p.user_id = u.id
                    WHERE gc.thread_id = ?
                    ORDER BY gc.created_at ASC";

            $comments = $db->query($sql, [$threadId]);

            echo json_encode([
                'success' => true,
                'data' => [
                    'thread' => $thread,
                    'comments' => $comments
                ]
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    // Determine appropriate status code
    $errorMessage = $e->getMessage();

    if (strpos($errorMessage, 'Authentication required') !== false) {
        http_response_code(401);
    } elseif (strpos($errorMessage, 'Guild membership required') !== false || strpos($errorMessage, 'Permission denied') !== false) {
        http_response_code(403);
    } elseif (strpos($errorMessage, 'not found') !== false) {
        http_response_code(404);
    } elseif (strpos($errorMessage, 'Missing required') !== false || strpos($errorMessage, 'Invalid') !== false) {
        http_response_code(400);
    } else {
        http_response_code(500);
    }

    echo json_encode([
        'success' => false,
        'message' => $errorMessage
    ]);
}
