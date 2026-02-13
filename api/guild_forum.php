<?php
/**
 * Guild Forum API Endpoints
 * Handles AJAX requests for guild forum operations (threads and comments)
 */

// Start output buffering and suppress error output
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Profile.php';
require_once __DIR__ . '/../classes/ProfileGuild.php';

// Clear any output that might have occurred
ob_clean();
header('Content-Type: application/json');

$db = Database::getInstance();
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

        case 'vote_comment':
            $commentId = $input['comment_id'] ?? null;
            $voteType = $input['vote_type'] ?? null;

            if ($commentId === null || $voteType === null) {
                throw new Exception('Missing required parameters: comment_id, vote_type');
            }

            if (!in_array($voteType, ['up', 'down'])) {
                throw new Exception('Invalid vote_type. Must be "up" or "down"');
            }

            // Get user's profile
            $profile = getUserProfile($db);

            // Get comment and verify guild membership
            $sql = "SELECT gc.id, gc.votes, gt.guild_id
                    FROM guild_comments gc
                    INNER JOIN guild_threads gt ON gc.thread_id = gt.id
                    WHERE gc.id = ?";
            $comment = $db->queryOne($sql, [$commentId]);

            if (!$comment) {
                throw new Exception('Comment not found');
            }

            // Verify guild membership
            verifyGuildMembership($db, $profile['id'], $comment['guild_id']);

            // Check if user has already voted
            $sql = "SELECT id, vote_type FROM guild_comment_votes
                    WHERE comment_id = ? AND profile_id = ?";
            $existingVote = $db->queryOne($sql, [$commentId, $profile['id']]);

            $newVotes = $comment['votes'];
            $userVote = null;

            if ($existingVote) {
                if ($existingVote['vote_type'] === $voteType) {
                    // User is removing their vote
                    $sql = "DELETE FROM guild_comment_votes WHERE id = ?";
                    $db->execute($sql, [$existingVote['id']]);

                    // Adjust vote count
                    if ($voteType === 'up') {
                        $newVotes--;
                    } else {
                        $newVotes++;
                    }
                } else {
                    // User is changing their vote
                    $sql = "UPDATE guild_comment_votes
                            SET vote_type = ?, updated_at = ?
                            WHERE id = ?";
                    $db->execute($sql, [$voteType, date('Y-m-d H:i:s'), $existingVote['id']]);

                    // Adjust vote count (reverse old vote and apply new vote)
                    if ($voteType === 'up') {
                        $newVotes += 2; // Remove -1 from down and add +1 for up
                    } else {
                        $newVotes -= 2; // Remove +1 from up and add -1 for down
                    }

                    $userVote = $voteType;
                }
            } else {
                // User is casting a new vote
                $sql = "INSERT INTO guild_comment_votes (comment_id, profile_id, vote_type, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?)";
                $db->execute($sql, [
                    $commentId,
                    $profile['id'],
                    $voteType,
                    date('Y-m-d H:i:s'),
                    date('Y-m-d H:i:s')
                ]);

                // Adjust vote count
                if ($voteType === 'up') {
                    $newVotes++;
                } else {
                    $newVotes--;
                }

                $userVote = $voteType;
            }

            // Update comment votes
            $sql = "UPDATE guild_comments SET votes = ? WHERE id = ?";
            $db->execute($sql, [$newVotes, $commentId]);

            echo json_encode([
                'success' => true,
                'message' => 'Vote recorded successfully',
                'data' => [
                    'votes' => $newVotes,
                    'user_vote' => $userVote
                ]
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    // Determine appropriate status code
    $errorMessage = $e->getMessage();
    $errorTrace = $e->getTraceAsString();

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

    // Log error for debugging
    error_log("Guild Forum API Error: " . $errorMessage);
    error_log("Stack trace: " . $errorTrace);

    echo json_encode([
        'success' => false,
        'message' => $errorMessage,
        'debug' => IS_DEV ? $errorTrace : null
    ]);
}
