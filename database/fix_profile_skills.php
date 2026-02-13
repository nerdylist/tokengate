<?php

/**
 * Quick Fix: Add missing xp column to profile_skills table
 */

require_once __DIR__ . '/../config.php';

echo "=== Adding XP Column to profile_skills ===\n\n";

$dbPath = __DIR__ . '/' . DB_NAME;

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if xp column exists
    $columns = $db->query("PRAGMA table_info(profile_skills)")->fetchAll(PDO::FETCH_ASSOC);
    $hasXp = false;

    foreach ($columns as $col) {
        if ($col['name'] === 'xp') {
            $hasXp = true;
            break;
        }
    }

    if ($hasXp) {
        echo "✓ Column 'xp' already exists in profile_skills table\n";
    } else {
        echo "Adding 'xp' column to profile_skills table...\n";
        $db->exec("ALTER TABLE profile_skills ADD COLUMN xp INTEGER DEFAULT 0");
        echo "✓ Column 'xp' added successfully\n";
    }

    // Create index if it doesn't exist
    echo "\nCreating index on xp column...\n";
    $db->exec("CREATE INDEX IF NOT EXISTS idx_profile_skills_xp ON profile_skills(xp DESC)");
    echo "✓ Index created\n";

    echo "\n=== Fix Complete ===\n";
    echo "You can now access profile pages without errors.\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
