<?php
/**
 * Add last_login column to users table
 *
 * Run this on production to fix login errors
 * Usage: php database/migrations/add_last_login_column.php
 */

require_once __DIR__ . '/../../config.php';

$dbPath = __DIR__ . '/../g8.db';

if (!file_exists($dbPath)) {
    echo "Error: Database file not found at {$dbPath}\n";
    exit(1);
}

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if column already exists
    $columns = $pdo->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC);
    $hasLastLogin = false;

    foreach ($columns as $column) {
        if ($column['name'] === 'last_login') {
            $hasLastLogin = true;
            break;
        }
    }

    if ($hasLastLogin) {
        echo "✓ Column 'last_login' already exists in users table\n";
    } else {
        // Add the column
        $pdo->exec("ALTER TABLE users ADD COLUMN last_login DATETIME");
        echo "✓ Successfully added 'last_login' column to users table\n";
    }

    // Verify it was added
    $columns = $pdo->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC);
    $verified = false;

    foreach ($columns as $column) {
        if ($column['name'] === 'last_login') {
            $verified = true;
            break;
        }
    }

    if ($verified) {
        echo "✓ Verified: last_login column exists\n";
        exit(0);
    } else {
        echo "✗ Error: Failed to add last_login column\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
