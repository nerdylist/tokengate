<?php

require_once __DIR__ . '/../config.php';

echo "=== Seeding Bounty Votes ===\n\n";

try {
    $db = new PDO('sqlite:' . __DIR__ . '/g8.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Clear existing votes
    $db->exec("DELETE FROM bounty_votes");
    echo "✓ Cleared existing votes\n";

    // Get admin user ID (or use first user)
    $stmt = $db->query("SELECT id FROM users WHERE is_admin = 1 LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $baseUserId = $user ? $user['id'] : 1;

    // Seed votes for different bounties with realistic distributions
    $votes = [
        // Bounty 1: +43 votes (43 upvotes, 0 downvotes)
        ['bounty_id' => 1, 'vote_type' => 1, 'count' => 43],

        // Bounty 2: -1 vote (0 upvotes, 1 downvote)
        ['bounty_id' => 2, 'vote_type' => -1, 'count' => 1],

        // Bounty 3: 0 votes (leave empty)

        // Bounty 4: +15 votes
        ['bounty_id' => 4, 'vote_type' => 1, 'count' => 15],

        // Bounty 5: +8 votes
        ['bounty_id' => 5, 'vote_type' => 1, 'count' => 8],

        // Bounty 6: +22 votes
        ['bounty_id' => 6, 'vote_type' => 1, 'count' => 22],
    ];

    $stmt = $db->prepare("INSERT INTO bounty_votes (bounty_id, user_id, vote_type, created_at, updated_at) VALUES (?, ?, ?, datetime('now'), datetime('now'))");

    $totalVotes = 0;
    foreach ($votes as $voteData) {
        for ($i = 0; $i < $voteData['count']; $i++) {
            // Use different user IDs by incrementing (simulating multiple users)
            $simulatedUserId = $baseUserId + $i;
            $stmt->execute([
                $voteData['bounty_id'],
                $simulatedUserId,
                $voteData['vote_type']
            ]);
            $totalVotes++;
        }
    }

    echo "✓ Created $totalVotes test votes\n\n";

    // Show vote counts
    echo "Vote counts by bounty:\n";
    $results = $db->query("
        SELECT b.id, b.title,
               (SELECT COALESCE(SUM(vote_type), 0) FROM bounty_votes WHERE bounty_id = b.id) as vote_count
        FROM bounties b
        ORDER BY vote_count DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        $prefix = $row['vote_count'] > 0 ? '+' : '';
        echo "  {$prefix}{$row['vote_count']} - {$row['title']}\n";
    }

    echo "\n=== Seeding Complete ===\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
