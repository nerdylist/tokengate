<?php
/**
 * Guild and Rank Seeder
 * Populates the guilds and ranks tables from data files
 * Usage: php database/seed_guilds.php
 */

require_once __DIR__ . '/connection.php';

function slugify($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

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

function parseGuilds($file, $type) {
    $content = file_get_contents($file);
    $lines = explode("\n", $content);

    $guilds = [];
    $inGuildSection = false;

    foreach ($lines as $line) {
        $line = trim($line);

        if (strpos($line, 'GUILDS') !== false || strpos($line, 'CRAFT & TRADE') !== false) {
            $inGuildSection = true;
            continue;
        }

        // Stop at church/ministry sections in traditional.md
        if ($type === 'traditional' &&
            (strpos($line, 'CHURCH HIERARCHY') !== false ||
             strpos($line, 'SCRIPTURE') !== false ||
             strpos($line, 'TESTAMENT') !== false ||
             strpos($line, 'MONASTIC') !== false ||
             strpos($line, 'LEADERSHIP TITLES') !== false)) {
            break;
        }

        // Stop at quest system in modern.md
        if ($type === 'modern' && strpos($line, 'GUILD QUEST SYSTEM') !== false) {
            break;
        }

        if ($inGuildSection && !empty($line) &&
            strpos($line, '===') === false &&
            strpos($line, 'METAL') === false &&
            strpos($line, 'WOOD') === false &&
            strpos($line, 'GLASS') === false &&
            strpos($line, 'TEXTILE') === false &&
            strpos($line, 'LEATHER') === false &&
            strpos($line, 'PAPER') === false &&
            strpos($line, 'FOOD') === false &&
            strpos($line, 'MEDICINE') === false &&
            strpos($line, 'MUSIC') === false &&
            strpos($line, 'TRADE SUPPORT') === false &&
            strpos($line, 'DIGITAL') === false &&
            strpos($line, 'DESIGN') === false &&
            strpos($line, 'DATA') === false &&
            strpos($line, 'MARKETING') === false &&
            strpos($line, 'SOCIAL') === false &&
            strpos($line, 'COPY') === false &&
            strpos($line, 'BUSINESS') === false &&
            strpos($line, 'FINANCE') === false &&
            strpos($line, 'FIELD') === false &&
            strpos($line, 'CREATIVE') === false &&
            strpos($line, 'EDUCATION') === false &&
            strpos($line, 'CRAFTING') === false) {

            $guilds[] = [
                'name' => $line,
                'slug' => slugify($line),
                'type' => $type
            ];
        }
    }

    return $guilds;
}

try {
    echo "Starting guild and rank seeding...\n\n";

    // Parse ranks
    echo "Parsing modern ranks...\n";
    $modernRanks = parseRanks(__DIR__ . '/../data/modern.md', 'modern');
    echo "Found " . count($modernRanks) . " modern ranks\n";

    echo "Parsing traditional ranks...\n";
    $traditionalRanks = parseRanks(__DIR__ . '/../data/traditional.md', 'traditional');
    echo "Found " . count($traditionalRanks) . " traditional ranks\n\n";

    // Parse guilds
    echo "Parsing modern guilds...\n";
    $modernGuilds = parseGuilds(__DIR__ . '/../data/modern.md', 'modern');
    echo "Found " . count($modernGuilds) . " modern guilds\n";

    echo "Parsing traditional guilds...\n";
    $traditionalGuilds = parseGuilds(__DIR__ . '/../data/traditional.md', 'traditional');
    echo "Found " . count($traditionalGuilds) . " traditional guilds\n\n";

    // Insert ranks
    echo "Inserting ranks...\n";
    $rankStmt = $pdo->prepare("INSERT INTO ranks (name, level, type, xp_required, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)");

    foreach (array_merge($modernRanks, $traditionalRanks) as $rank) {
        $now = date('Y-m-d H:i:s');
        $rankStmt->execute([
            $rank['name'],
            $rank['level'],
            $rank['type'],
            $rank['xp_required'],
            $now,
            $now
        ]);
    }
    echo "Inserted " . (count($modernRanks) + count($traditionalRanks)) . " ranks\n\n";

    // Insert guilds
    echo "Inserting guilds...\n";
    $guildStmt = $pdo->prepare("INSERT INTO guilds (name, slug, type, created_at, updated_at) VALUES (?, ?, ?, ?, ?)");

    foreach (array_merge($modernGuilds, $traditionalGuilds) as $guild) {
        $now = date('Y-m-d H:i:s');
        $guildStmt->execute([
            $guild['name'],
            $guild['slug'],
            $guild['type'],
            $now,
            $now
        ]);
    }
    echo "Inserted " . (count($modernGuilds) + count($traditionalGuilds)) . " guilds\n\n";

    echo "Seeding complete!\n";
    echo "Total: " . (count($modernRanks) + count($traditionalRanks)) . " ranks, " .
         (count($modernGuilds) + count($traditionalGuilds)) . " guilds\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
