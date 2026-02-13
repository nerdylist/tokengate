<?php
/**
 * Admin Password Reset Script
 *
 * Resets the admin user's password using credentials from the .env file.
 * Can be run from command line: php database/reset_admin_password.php
 *
 * This script is production-safe and works on both local dev and CloudWays hosting.
 */

// Include config to load .env variables
require_once __DIR__ . '/../config.php';

// Check if required environment variables are set
if (!defined('ADMIN_EMAIL') || !defined('ADMIN_PASSWORD') || !defined('DB_NAME')) {
    die("Error: Required environment variables not found in .env file.\n" .
        "Please ensure ADMIN_EMAIL, ADMIN_PASSWORD, and DB_NAME are set.\n");
}

try {
    // Build database path using __DIR__ for portability
    $dbPath = __DIR__ . '/' . DB_NAME;

    // Check if database file exists
    if (!file_exists($dbPath)) {
        die("Error: Database file not found at: {$dbPath}\n");
    }

    // Connect to SQLite database using PDO
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Find user by admin email
    $stmt = $pdo->prepare("SELECT id, email, name FROM users WHERE email = :email");
    $stmt->execute(['email' => ADMIN_EMAIL]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user exists
    if (!$user) {
        die("Error: No user found with email: " . ADMIN_EMAIL . "\n");
    }

    // Hash the password using PHP's password_hash with PASSWORD_DEFAULT
    $hashedPassword = password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT);

    // Update the password_hash in the database
    $updateStmt = $pdo->prepare("UPDATE users SET password_hash = :password_hash, updated_at = CURRENT_TIMESTAMP WHERE email = :email");
    $result = $updateStmt->execute([
        'password_hash' => $hashedPassword,
        'email' => ADMIN_EMAIL
    ]);

    // Verify the update succeeded
    if ($result && $updateStmt->rowCount() > 0) {
        echo "Success: Password reset successful for user: " . ADMIN_EMAIL . "\n";
        echo "User ID: " . $user['id'] . "\n";
        echo "Name: " . $user['name'] . "\n";
    } else {
        die("Error: Password update failed. No rows were affected.\n");
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}
