<?php
/**
 * Database Migration Runner
 * Executes the schema.sql file to create the database structure
 */

// Get the database path
$dbPath = __DIR__ . '/rentpeople.db';
$schemaFile = __DIR__ . '/schema.sql';

// Check if schema file exists
if (!file_exists($schemaFile)) {
    die("Error: schema.sql file not found at {$schemaFile}\n");
}

// Read the schema SQL
$sql = file_get_contents($schemaFile);

if ($sql === false) {
    die("Error: Could not read schema.sql file\n");
}

try {
    // Create PDO connection
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Starting database migration...\n";
    echo "Database: {$dbPath}\n\n";

    // Execute the schema
    $pdo->exec($sql);

    echo "Migration completed successfully!\n";
    echo "Database tables created.\n\n";

    // Verify tables were created
    $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);

    echo "Tables created:\n";
    foreach ($tables as $table) {
        echo "  - {$table}\n";
    }

    echo "\nDatabase is ready to use!\n";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage() . "\n");
}
