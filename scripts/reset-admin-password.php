#!/usr/bin/env php
<?php
/**
 * Reset Admin Password Script
 *
 * This script resets the admin user password from the .env file.
 * It reads ADMIN_EMAIL and ADMIN_PASSWORD from .env, hashes the password,
 * and updates the users table.
 *
 * Usage: php scripts/reset-admin-password.php
 */

// Define the root directory
define('ROOT_DIR', dirname(__DIR__));

// Check if running from command line
if (php_sapi_name() !== 'cli') {
    die("Error: This script can only be run from the command line.\n");
}

echo "===========================================\n";
echo "  Admin Password Reset Script\n";
echo "===========================================\n\n";

// Load configuration
require_once ROOT_DIR . '/config.php';

// Validate environment variables
if (!defined('ADMIN_EMAIL') || empty(ADMIN_EMAIL)) {
    die("Error: ADMIN_EMAIL is not set in .env file.\n");
}

if (!defined('ADMIN_PASSWORD') || empty(ADMIN_PASSWORD)) {
    die("Error: ADMIN_PASSWORD is not set in .env file.\n");
}

echo "Admin Email: " . ADMIN_EMAIL . "\n";
echo "Password: " . str_repeat('*', strlen(ADMIN_PASSWORD)) . "\n\n";

// Validate email format
if (!filter_var(ADMIN_EMAIL, FILTER_VALIDATE_EMAIL)) {
    die("Error: ADMIN_EMAIL is not a valid email address.\n");
}

// Validate password strength (minimum requirements)
if (strlen(ADMIN_PASSWORD) < 8) {
    die("Error: ADMIN_PASSWORD must be at least 8 characters long.\n");
}

// Connect to database
try {
    $dbPath = ROOT_DIR . '/database/' . DB_NAME;

    // Check if database file exists
    if (!file_exists($dbPath)) {
        die("Error: Database file not found at /database/" . DB_NAME . "\n");
    }

    echo "Connecting to database: " . basename($dbPath) . "\n";

    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Database connection successful.\n\n";

} catch (PDOException $e) {
    die("Error: Could not connect to database: " . $e->getMessage() . "\n");
}

// Check if user exists
try {
    $stmt = $db->prepare("SELECT id, email, name, is_admin FROM users WHERE email = :email");
    $stmt->execute([':email' => ADMIN_EMAIL]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "Warning: User with email '" . ADMIN_EMAIL . "' does not exist.\n";
        echo "Would you like to create a new admin user? (yes/no): ";

        $handle = fopen("php://stdin", "r");
        $line = trim(fgets($handle));
        fclose($handle);

        if (strtolower($line) !== 'yes' && strtolower($line) !== 'y') {
            die("Operation cancelled.\n");
        }

        // Create new admin user
        $passwordHash = password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT);
        $name = explode('@', ADMIN_EMAIL)[0];

        $stmt = $db->prepare("
            INSERT INTO users (email, password_hash, name, is_admin, created_at, updated_at)
            VALUES (:email, :password_hash, :name, 1, datetime('now'), datetime('now'))
        ");

        $stmt->execute([
            ':email' => ADMIN_EMAIL,
            ':password_hash' => $passwordHash,
            ':name' => $name
        ]);

        echo "\nSuccess: New admin user created!\n";
        echo "  Email: " . ADMIN_EMAIL . "\n";
        echo "  Name: " . $name . "\n";
        echo "  Admin: Yes\n";

    } else {
        echo "Found existing user:\n";
        echo "  ID: " . $user['id'] . "\n";
        echo "  Email: " . $user['email'] . "\n";
        echo "  Name: " . $user['name'] . "\n";
        echo "  Admin: " . ($user['is_admin'] ? 'Yes' : 'No') . "\n\n";

        // Hash the new password
        $passwordHash = password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT);

        // Update user password and ensure admin status
        $stmt = $db->prepare("
            UPDATE users
            SET password_hash = :password_hash,
                is_admin = 1,
                updated_at = datetime('now')
            WHERE email = :email
        ");

        $stmt->execute([
            ':password_hash' => $passwordHash,
            ':email' => ADMIN_EMAIL
        ]);

        echo "Success: Admin password updated!\n";

        // Show admin status change if needed
        if (!$user['is_admin']) {
            echo "Note: User has been granted admin privileges.\n";
        }
    }

    echo "\n===========================================\n";
    echo "  Password reset complete!\n";
    echo "===========================================\n";

} catch (PDOException $e) {
    die("Error: Database operation failed: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}

// Close database connection
$db = null;

exit(0);
