<?php
/**
 * Database Connection Setup
 * PDO SQLite Connection Configuration
 */

// Database file path
$dbPath = __DIR__ . '/rentpeople.db';

try {
    // Create PDO instance for SQLite
    $pdo = new PDO('sqlite:' . $dbPath);

    // Set error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Enable foreign key constraints
    $pdo->exec('PRAGMA foreign_keys = ON');

    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Return PDO instance
    return $pdo;

} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
