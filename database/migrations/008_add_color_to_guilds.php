<?php
/**
 * Migration: Add color column to guilds table
 * Date: 2026-02-11
 * Description: Adds a color column to store hex color codes for guilds
 */

$dbPath = __DIR__ . '/../g8.db';

if (!file_exists($dbPath)) {
    die("✗ Database file not found: $dbPath\n");
}

try {
    // Connect to database
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Running migration: Add color column to guilds table\n";
    echo str_repeat('-', 50) . "\n";
    
    // Begin transaction
    $db->exec('BEGIN TRANSACTION');
    
    // Add color column with default value
    $db->exec("
        ALTER TABLE guilds 
        ADD COLUMN color VARCHAR(7) DEFAULT '#FFCC00'
    ");
    
    // Update existing guilds to have the default color
    $stmt = $db->exec("
        UPDATE guilds 
        SET color = '#FFCC00' 
        WHERE color IS NULL
    ");
    
    // Commit transaction
    $db->exec('COMMIT');
    
    echo "✓ Successfully added color column to guilds table\n";
    echo "  - Column type: VARCHAR(7)\n";
    echo "  - Default value: #FFCC00\n";
    echo "  - All existing guilds updated with default color\n";
    echo str_repeat('-', 50) . "\n";
    echo "Migration completed successfully!\n";
    
} catch (Exception $e) {
    // Rollback on error
    if (isset($db)) {
        $db->exec('ROLLBACK');
    }
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
