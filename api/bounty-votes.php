<?php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Database.php';

header('Content-Type: application/json');

// Require authentication
if (!Auth::check()) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

$userId = Auth::id();
$bountyId = isset($_POST['bounty_id']) ? (int)$_POST['bounty_id'] : 0;
$voteType = isset($_POST['vote_type']) ? (int)$_POST['vote_type'] : 0;

// Validate inputs
if (!$bountyId || !in_array($voteType, [1, -1])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

try {
    $db = Database::getInstance();

    // Check if user already voted on this bounty
    $existing = $db->queryOne(
        "SELECT id, vote_type FROM bounty_votes WHERE bounty_id = ? AND user_id = ?",
        [$bountyId, $userId]
    );

    if ($existing) {
        // If same vote type, remove vote (toggle off)
        if ($existing['vote_type'] == $voteType) {
            $db->execute(
                "DELETE FROM bounty_votes WHERE id = ?",
                [$existing['id']]
            );
            $action = 'removed';
        } else {
            // Change vote type (upvote -> downvote or vice versa)
            $db->execute(
                "UPDATE bounty_votes SET vote_type = ?, updated_at = datetime('now') WHERE id = ?",
                [$voteType, $existing['id']]
            );
            $action = 'updated';
        }
    } else {
        // Create new vote
        $db->execute(
            "INSERT INTO bounty_votes (bounty_id, user_id, vote_type, created_at, updated_at)
             VALUES (?, ?, ?, datetime('now'), datetime('now'))",
            [$bountyId, $userId, $voteType]
        );
        $action = 'created';
    }

    // Get updated vote count
    $result = $db->queryOne(
        "SELECT COALESCE(SUM(vote_type), 0) as vote_count FROM bounty_votes WHERE bounty_id = ?",
        [$bountyId]
    );

    $voteCount = (int)$result['vote_count'];

    // Check user's current vote status
    $userVote = $db->queryOne(
        "SELECT vote_type FROM bounty_votes WHERE bounty_id = ? AND user_id = ?",
        [$bountyId, $userId]
    );

    echo json_encode([
        'success' => true,
        'action' => $action,
        'vote_count' => $voteCount,
        'user_vote' => $userVote ? (int)$userVote['vote_type'] : 0
    ]);

} catch (Exception $e) {
    error_log("Bounty vote error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to save vote']);
}
