<?php
/**
 * Replace incorrect ranks with proper universal ranks
 *
 * This migration:
 * - Deletes existing incorrect ranks
 * - Inserts proper 20 ranks (10 modern + 10 traditional) from data files
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
    $xpLevels = [0, 100, 500, 1500, 3500, 7000, 12000, 20000, 35000, 60000];
    $inRankSection = false;
    $rankIndex = 0;

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
            $ranks[] = [
                'name' => $line,
                'level' => $rankIndex + 1,
                'type' => $type,
                'xp_required' => $xpLevels[$rankIndex] ?? 0
            ];
            $rankIndex++;
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

    // Parse and insert correct ranks
    echo "Parsing ranks from data files...\n";
    $modernRanks = parseRanks(__DIR__ . '/../../data/modern.md', 'modern');
    $traditionalRanks = parseRanks(__DIR__ . '/../../data/traditional.md', 'traditional');
    $ranks = array_merge($modernRanks, $traditionalRanks);

    echo "  ✓ Found " . count($modernRanks) . " modern ranks\n";
    echo "  ✓ Found " . count($traditionalRanks) . " traditional ranks\n\n";

    echo "Inserting correct ranks...\n";
    $stmt = $pdo->prepare("INSERT INTO ranks (name, level, type, xp_required) VALUES (?, ?, ?, ?)");

    foreach ($ranks as $rank) {
        $stmt->execute([$rank['name'], $rank['level'], $rank['type'], $rank['xp_required']]);
    }
    echo "  ✓ Inserted " . count($ranks) . " ranks\n\n";

    // Show the inserted ranks
    echo "Modern Ranks:\n";
    $result = $pdo->query("SELECT id, name, level, xp_required FROM ranks WHERE type = 'modern' ORDER BY level");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['id']}. {$row['name']} (Level {$row['level']}, {$row['xp_required']} XP)\n";
    }

    echo "\nTraditional Ranks:\n";
    $result = $pdo->query("SELECT id, name, level, xp_required FROM ranks WHERE type = 'traditional' ORDER BY level");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['id']}. {$row['name']} (Level {$row['level']}, {$row['xp_required']} XP)\n";
    }

    if ($profileGuildCount > 0) {
        echo "\n⚠️  IMPORTANT: You need to reassign profile_guilds to use the new rank IDs.\n";
        echo "   The old rank IDs no longer exist.\n";
    }

    echo "\n✅ Migration completed successfully!\n";
    exit(0);

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
