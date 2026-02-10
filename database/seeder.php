<?php
/**
 * Database Seeder
 * Populates the database with sample data from seeds.sql
 */

// Get the database path
$dbPath = __DIR__ . '/rentpeople.db';
$seedsFile = __DIR__ . '/seeds.sql';

// Check if seeds file exists
if (!file_exists($seedsFile)) {
    die("Error: seeds.sql file not found at {$seedsFile}\n");
}

// Check if database exists
if (!file_exists($dbPath)) {
    die("Error: Database not found. Please run migrations.php first.\n");
}

// Read the seeds SQL
$sql = file_get_contents($seedsFile);

if ($sql === false) {
    die("Error: Could not read seeds.sql file\n");
}

try {
    // Create PDO connection
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Starting database seeding...\n";
    echo "Database: {$dbPath}\n\n";

    // Execute the seeds
    $pdo->exec($sql);

    echo "Seeding completed successfully!\n\n";

    // Verify data was inserted
    $tables = ['users', 'categories', 'skills', 'bounties', 'profiles', 'applications'];

    echo "Data inserted:\n";
    foreach ($tables as $table) {
        $result = $pdo->query("SELECT COUNT(*) as count FROM {$table}");
        $count = $result->fetch(PDO::FETCH_ASSOC)['count'];
        echo "  - {$table}: {$count} records\n";
    }

    echo "\nDatabase is populated with sample data!\n";

} catch (PDOException $e) {
    die("Seeding failed: " . $e->getMessage() . "\n");
}
