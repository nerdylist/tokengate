<?php
/**
 * Replace incorrect ranks with proper merged universal ranks
 *
 * This migration:
 * - Deletes existing incorrect ranks
 * - Merges modern and traditional ranks, removing duplicates
 * - Creates single unified rank progression
 *
 * Usage: php database/migrations/replace_ranks_with_proper_ones.php
 */

require_once __DIR__ . '/../../config.php';

$dbPath = __DIR__ . '/../g8.db';

if (!file_exists($dbPath)) {
    echo "Error: Database file not found at {$dbPath}\n";
    exit(1);
}

// Parse ranks from markdown files
function parseRanks($file, $type) {
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    $ranks = [];
    $inRankSection = false;

    foreach ($lines as $line) {
        $line = trim($line);
        if ($type === 'modern' && strpos($line, 'UNIVERSAL GUILD RANK SYSTEM') !== false) {
            $inRankSection = true;
            continue;
        }
        if ($type === 'traditional' && strpos($line, 'GUILD RANK STRUCTURE') !== false) {
            $inRankSection = true;
            continue;
        }
        if ($inRankSection && strpos($line, '===') !== false) {
            continue;
        }
        if ($inRankSection && strpos($line, 'GUILDS') !== false) {
            break;
        }
        if ($inRankSection && !empty($line) && strpos($line, '===') === false) {
            $ranks[] = $line;
        }
    }
    return $ranks;
}

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Starting rank replacement...\n\n";

    // Check if there are any profile_guilds using these ranks
    $profileGuildCount = $pdo->query("SELECT COUNT(*) as cnt FROM profile_guilds")->fetch(PDO::FETCH_ASSOC)['cnt'];

    if ($profileGuildCount > 0) {
        echo "⚠️  Warning: Found {$profileGuildCount} profile_guild assignments.\n";
        echo "   These will need to be updated to use new rank IDs.\n\n";
    }

    // Delete existing ranks
    echo "Deleting incorrect ranks...\n";
    $deletedCount = $pdo->exec("DELETE FROM ranks");
    echo "  ✓ Deleted {$deletedCount} incorrect ranks\n\n";

    // Parse ranks from both files
    echo "Parsing ranks from data files...\n";
    $modernRanks = parseRanks(__DIR__ . '/../../data/modern.md', 'modern');
    $traditionalRanks = parseRanks(__DIR__ . '/../../data/traditional.md', 'traditional');

    echo "  ✓ Found " . count($modernRanks) . " modern ranks\n";
    echo "  ✓ Found " . count($traditionalRanks) . " traditional ranks\n\n";

    // Merge and remove duplicates (case-insensitive)
    echo "Merging ranks and removing duplicates...\n";
    $mergedRanks = [];
    $seen = [];

    foreach ($modernRanks as $rank) {
        $key = strtolower($rank);
        if (!isset($seen[$key])) {
            $mergedRanks[] = $rank;
            $seen[$key] = true;
        }
    }

    foreach ($traditionalRanks as $rank) {
        $key = strtolower($rank);
        if (!isset($seen[$key])) {
            $mergedRanks[] = $rank;
            $seen[$key] = true;
        }
    }

    echo "  ✓ Merged to " . count($mergedRanks) . " unique ranks\n\n";

    // Assign XP levels based on position
    $xpLevels = [0, 100, 500, 1500, 3500, 7000, 12000, 20000, 35000, 60000, 100000, 150000, 250000, 400000];

    echo "Inserting merged ranks...\n";
    $stmt = $pdo->prepare("INSERT INTO ranks (name, level, type, xp_required) VALUES (?, ?, 'universal', ?)");

    foreach ($mergedRanks as $index => $rankName) {
        $level = $index + 1;
        $xpRequired = $xpLevels[$index] ?? ($xpLevels[count($xpLevels) - 1] * 2);
        $stmt->execute([$rankName, $level, $xpRequired]);
    }
    echo "  ✓ Inserted " . count($mergedRanks) . " ranks\n\n";

    // Show the inserted ranks
    echo "Universal Rank Progression:\n";
    $result = $pdo->query("SELECT id, name, level, xp_required FROM ranks ORDER BY level");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['level']}. {$row['name']} (ID: {$row['id']}, {$row['xp_required']} XP)\n";
    }

    if ($profileGuildCount > 0) {
        echo "\n⚠️  IMPORTANT: You need to reassign profile_guilds to use the new rank IDs.\n";
        echo "   The old rank IDs no longer exist.\n";
    }

    echo "\n✅ Migration completed successfully!\n";
    echo "Total unique ranks: " . count($mergedRanks) . "\n";
    exit(0);

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
