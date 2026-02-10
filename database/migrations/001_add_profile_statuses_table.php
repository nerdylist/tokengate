<?php
/**
 * Migration: Add profile_statuses table
 * Usage: php database/migrations/001_add_profile_statuses_table.php
 */

require_once __DIR__ . '/../connection.php';

try {
    echo "Running migration: Add profile_statuses table...\n\n";

    // Check if table already exists
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='profile_statuses'");
    $tableExists = $stmt->fetch();

    if ($tableExists) {
        echo "Table 'profile_statuses' already exists. Skipping migration.\n";
        exit(0);
    }

    // Create table
    echo "Creating profile_statuses table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS profile_statuses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            color VARCHAR(7) NOT NULL,
            sort_order INTEGER DEFAULT 0,
            is_active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Create indexes
    echo "Creating indexes...\n";
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_profile_statuses_slug ON profile_statuses(slug)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_profile_statuses_active ON profile_statuses(is_active)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_profile_statuses_sort ON profile_statuses(sort_order)");

    echo "\nMigration completed successfully!\n";
    echo "Run 'php database/seed_profile_statuses.php' to seed default data.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
