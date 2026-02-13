<?php

/**
 * Safe Production Migration for CloudWays
 *
 * This script creates a new database alongside the existing one,
 * then swaps them atomically to avoid locking issues.
 */

require_once __DIR__ . '/../../config.php';

echo "=== Safe Production Migration ===\n\n";

$dbPath = __DIR__ . '/../' . DB_NAME;
$tempDbPath = __DIR__ . '/../g8_new.db';
$backupDbPath = __DIR__ . '/../g8_backup_' . date('Y-m-d_H-i-s') . '.db';

// Step 1: Check if old database exists
if (file_exists($dbPath)) {
    echo "✓ Found existing database: " . DB_NAME . "\n";
    $hasExistingData = true;
} else {
    echo "○ No existing database found - creating fresh database\n";
    $hasExistingData = false;
}

// Step 2: Create new database with complete schema
echo "\nCreating new database with complete schema...\n";

try {
    $newDb = new PDO('sqlite:' . $tempDbPath);
    $newDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Read and execute schema
    $schemaPath = __DIR__ . '/../schema.sql';
    if (!file_exists($schemaPath)) {
        throw new Exception("Schema file not found: $schemaPath");
    }

    $schema = file_get_contents($schemaPath);
    $newDb->exec($schema);

    echo "✓ Schema created successfully\n";

} catch (Exception $e) {
    echo "✗ Failed to create new database: " . $e->getMessage() . "\n";
    if (file_exists($tempDbPath)) {
        unlink($tempDbPath);
    }
    exit(1);
}

// Step 3: Copy data from old database if it exists
if ($hasExistingData) {
    echo "\nCopying data from existing database...\n";

    try {
        $newDb->exec("ATTACH DATABASE '$dbPath' AS old_db");

        $tables = [
            'users', 'profiles', 'skills', 'categories', 'ranks',
            'profile_skills', 'bounties', 'applications', 'guilds',
            'profile_guilds', 'guild_skills', 'guild_ranks',
            'guild_threads', 'guild_comments', 'profile_statuses',
            'quests', 'quest_bounties', 'bounty_ranks', 'bounty_skills',
            'votes', 'sessions'
        ];

        $newDb->exec("PRAGMA foreign_keys = OFF");

        foreach ($tables as $table) {
            // Check if table exists in old database
            $check = $newDb->query("SELECT name FROM old_db.sqlite_master WHERE type='table' AND name='$table'");
            if ($check->fetch()) {
                // Get column list from new table
                $cols = $newDb->query("PRAGMA table_info($table)")->fetchAll(PDO::FETCH_COLUMN, 1);

                // Get column list from old table
                $oldCols = $newDb->query("PRAGMA old_db.table_info($table)")->fetchAll(PDO::FETCH_COLUMN, 1);

                // Find common columns
                $commonCols = array_intersect($cols, $oldCols);

                if (!empty($commonCols)) {
                    $colList = implode(', ', $commonCols);
                    $newDb->exec("INSERT OR IGNORE INTO $table ($colList) SELECT $colList FROM old_db.$table");
                    $count = $newDb->query("SELECT COUNT(*) FROM $table")->fetchColumn();
                    echo "  ✓ $table: $count rows\n";
                }
            }
        }

        $newDb->exec("PRAGMA foreign_keys = ON");
        $newDb->exec("DETACH DATABASE old_db");

        echo "✓ Data migration complete\n";

    } catch (Exception $e) {
        echo "✗ Data migration error: " . $e->getMessage() . "\n";
        echo "  (Continuing anyway - you may need to re-enter some data)\n";
    }
}

// Step 4: Create admin user from .env
echo "\nCreating admin user from .env...\n";

$adminEmail = getenv('ADMIN_EMAIL');
$adminPassword = getenv('ADMIN_PASSWORD');

if (!$adminEmail || !$adminPassword) {
    echo "⚠ Warning: ADMIN_EMAIL or ADMIN_PASSWORD not set in .env\n";
} else {
    try {
        // Check if admin already exists
        $stmt = $newDb->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$adminEmail]);
        $existing = $stmt->fetch();

        if ($existing) {
            echo "  ○ Admin user already exists (migrated from old database)\n";

            // Update password and ensure admin status
            $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
            $stmt = $newDb->prepare("UPDATE users SET password_hash = ?, is_admin = 1, is_verified = 1 WHERE email = ?");
            $stmt->execute([$passwordHash, $adminEmail]);
            echo "  ✓ Updated admin password and status\n";

        } else {
            echo "  ○ Creating new admin user\n";

            $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
            $name = 'Administrator';

            $stmt = $newDb->prepare("
                INSERT INTO users (email, password_hash, name, is_admin, is_verified, created_at)
                VALUES (?, ?, ?, 1, 1, datetime('now'))
            ");
            $stmt->execute([$adminEmail, $passwordHash, $name]);

            echo "  ✓ Admin user created\n";
        }

    } catch (Exception $e) {
        echo "✗ Admin user error: " . $e->getMessage() . "\n";
    }
}

// Step 5: Close new database
unset($newDb);

// Step 6: Swap databases atomically
echo "\nSwapping databases...\n";

try {
    // Backup old database if it exists
    if ($hasExistingData) {
        if (!copy($dbPath, $backupDbPath)) {
            throw new Exception("Failed to create backup");
        }
        echo "✓ Backup created: " . basename($backupDbPath) . "\n";
    }

    // Remove old database and rename new one
    if (file_exists($dbPath)) {
        if (!unlink($dbPath)) {
            throw new Exception("Failed to remove old database");
        }
    }

    if (!rename($tempDbPath, $dbPath)) {
        throw new Exception("Failed to rename new database");
    }

    // Set permissions
    chmod($dbPath, 0664);

    echo "✓ Database swap complete\n";

} catch (Exception $e) {
    echo "✗ Database swap failed: " . $e->getMessage() . "\n";

    // Attempt recovery
    if (file_exists($backupDbPath) && !file_exists($dbPath)) {
        copy($backupDbPath, $dbPath);
        echo "  ✓ Restored from backup\n";
    }

    if (file_exists($tempDbPath)) {
        unlink($tempDbPath);
    }

    exit(1);
}

echo "\n=== Migration Complete ===\n";
echo "✓ Production database is ready\n";
echo "✓ Admin login: $adminEmail\n";
echo "\nBackup location: " . basename($backupDbPath) . "\n";
