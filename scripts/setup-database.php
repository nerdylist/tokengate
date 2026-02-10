#!/usr/bin/env php
<?php
/**
 * Database Setup Script
 *
 * This script initializes a fresh database for production deployment.
 * It creates the database file, runs the schema, seeds default data,
 * and creates the admin user from .env credentials.
 *
 * Usage: php scripts/setup-database.php
 */

// Define the root directory
define('ROOT_DIR', dirname(__DIR__));

// Check if running from command line
if (php_sapi_name() !== 'cli') {
    die("Error: This script can only be run from the command line.\n");
}

echo "===========================================\n";
echo "  Database Setup Script\n";
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

// Validate email format
if (!filter_var(ADMIN_EMAIL, FILTER_VALIDATE_EMAIL)) {
    die("Error: ADMIN_EMAIL is not a valid email address.\n");
}

// Validate password strength
if (strlen(ADMIN_PASSWORD) < 8) {
    die("Error: ADMIN_PASSWORD must be at least 8 characters long.\n");
}

// Setup database connection
try {
    $dbPath = ROOT_DIR . '/database/redot.db';
    $schemaPath = ROOT_DIR . '/database/schema.sql';

    // Check if schema file exists
    if (!file_exists($schemaPath)) {
        die("Error: Schema file not found at /database/schema.sql\n");
    }

    // Check if database directory exists, create if not
    $dbDir = dirname($dbPath);
    if (!is_dir($dbDir)) {
        echo "Creating database directory...\n";
        mkdir($dbDir, 0755, true);
    }

    // Check if database already exists
    $dbExists = file_exists($dbPath);
    if ($dbExists) {
        echo "Database file already exists at: " . basename($dbPath) . "\n";
    } else {
        echo "Creating new database file: " . basename($dbPath) . "\n";
    }

    // Connect to database
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('PRAGMA foreign_keys = ON');

    echo "Database connection successful.\n\n";

} catch (PDOException $e) {
    die("Error: Could not connect to database: " . $e->getMessage() . "\n");
}

// Run schema.sql to create tables
try {
    echo "Step 1: Creating database schema...\n";

    $schema = file_get_contents($schemaPath);
    if ($schema === false) {
        die("Error: Could not read schema file.\n");
    }

    // Temporarily disable foreign keys while creating schema
    $db->exec('PRAGMA foreign_keys = OFF');

    // Split schema into individual statements and execute them
    // This allows us to continue even if some indexes already exist
    $statements = explode(';', $schema);

    $createdCount = 0;
    $skippedCount = 0;

    foreach ($statements as $statement) {
        $statement = trim($statement);

        // Skip empty statements
        if (empty($statement)) {
            continue;
        }

        // Remove comment lines from the beginning of multi-line statements
        $lines = explode("\n", $statement);
        $cleanedLines = [];
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            // Skip comment-only lines
            if (empty($trimmedLine) || strpos($trimmedLine, '--') === 0) {
                continue;
            }
            $cleanedLines[] = $line;
        }

        // If nothing left after removing comments, skip this statement
        if (empty($cleanedLines)) {
            continue;
        }

        // Rebuild the statement without comment lines
        $statement = implode("\n", $cleanedLines);
        $statement = trim($statement);

        // Skip PRAGMA statements from schema (we handle them separately)
        if (stripos($statement, 'PRAGMA') === 0) {
            continue;
        }

        try {
            $db->exec($statement);
            $createdCount++;
        } catch (PDOException $e) {
            // Ignore errors for objects that already exist
            if (strpos($e->getMessage(), 'already exists') !== false) {
                $skippedCount++;
            } else {
                // Re-throw other errors
                throw $e;
            }
        }
    }

    // Re-enable foreign keys
    $db->exec('PRAGMA foreign_keys = ON');

    if ($createdCount > 0) {
        echo "  ✓ Database schema created successfully ($createdCount objects created";
        if ($skippedCount > 0) {
            echo ", $skippedCount already existed";
        }
        echo ").\n\n";
    } else {
        echo "  ⓘ Database schema already exists (all objects already created).\n\n";
    }

} catch (PDOException $e) {
    // Make sure to re-enable foreign keys even on error
    $db->exec('PRAGMA foreign_keys = ON');
    die("Error: Failed to create schema: " . $e->getMessage() . "\n");
}

// Seed profile statuses
try {
    echo "Step 2: Seeding profile statuses...\n";

    $statuses = [
        ['name' => 'Available', 'slug' => 'available', 'color' => '#22c55e', 'icon' => 'check', 'sort_order' => 1],
        ['name' => 'Busy', 'slug' => 'busy', 'color' => '#eab308', 'icon' => 'clock', 'sort_order' => 2],
        ['name' => 'Unavailable', 'slug' => 'unavailable', 'color' => '#ef4444', 'icon' => 'x', 'sort_order' => 3],
        ['name' => 'Away', 'slug' => 'away', 'color' => '#6b7280', 'icon' => 'moon', 'sort_order' => 4]
    ];

    $stmt = $db->prepare("
        INSERT OR IGNORE INTO profile_statuses (name, slug, color, icon, sort_order, is_active, created_at, updated_at)
        VALUES (:name, :slug, :color, :icon, :sort_order, 1, datetime('now'), datetime('now'))
    ");

    foreach ($statuses as $status) {
        $stmt->execute($status);
    }

    echo "  ✓ Profile statuses seeded successfully.\n\n";

} catch (PDOException $e) {
    die("Error: Failed to seed profile statuses: " . $e->getMessage() . "\n");
}

// Seed categories and skills
try {
    echo "Step 3: Seeding categories and skills...\n";

    $categoriesAndSkills = [
        'Development' => ['PHP', 'JavaScript', 'Python', 'SQL', 'React'],
        'Design' => ['UI/UX Design', 'Graphic Design', 'Web Design', 'Figma'],
        'Writing' => ['Content Writing', 'Copywriting', 'Technical Writing'],
        'Marketing' => ['SEO', 'Social Media', 'Email Marketing', 'PPC'],
        'Business' => ['Project Management', 'Consulting', 'Business Analysis']
    ];

    $categoryStmt = $db->prepare("
        INSERT OR IGNORE INTO categories (name, slug, description, created_at, updated_at)
        VALUES (:name, :slug, :description, datetime('now'), datetime('now'))
    ");

    $skillStmt = $db->prepare("
        INSERT OR IGNORE INTO skills (name, slug, category_id, created_at, updated_at)
        VALUES (:name, :slug, :category_id, datetime('now'), datetime('now'))
    ");

    foreach ($categoriesAndSkills as $categoryName => $skills) {
        // Create category
        $slug = strtolower(str_replace(' ', '-', $categoryName));
        $categoryStmt->execute([
            ':name' => $categoryName,
            ':slug' => $slug,
            ':description' => 'Skills related to ' . $categoryName
        ]);

        // Get category ID
        $categoryId = $db->query("SELECT id FROM categories WHERE slug = " . $db->quote($slug))->fetchColumn();

        // Create skills
        foreach ($skills as $skillName) {
            $skillSlug = strtolower(str_replace(['/', ' '], ['-', '-'], $skillName));
            $skillStmt->execute([
                ':name' => $skillName,
                ':slug' => $skillSlug,
                ':category_id' => $categoryId
            ]);
        }
    }

    echo "  ✓ Categories and skills seeded successfully.\n\n";

} catch (PDOException $e) {
    die("Error: Failed to seed categories and skills: " . $e->getMessage() . "\n");
}

// Create admin user
try {
    echo "Step 4: Creating admin user...\n";

    // Check if admin user already exists
    $stmt = $db->prepare("SELECT id, email, name, is_admin FROM users WHERE email = :email");
    $stmt->execute([':email' => ADMIN_EMAIL]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "  ⓘ Admin user already exists:\n";
        echo "    Email: " . $user['email'] . "\n";
        echo "    Name: " . $user['name'] . "\n";
        echo "    Admin: " . ($user['is_admin'] ? 'Yes' : 'No') . "\n";

        // Ensure user has admin privileges
        if (!$user['is_admin']) {
            $db->prepare("UPDATE users SET is_admin = 1, updated_at = datetime('now') WHERE id = :id")
               ->execute([':id' => $user['id']]);
            echo "  ✓ Admin privileges granted.\n";
        }

        $adminUserId = $user['id'];
    } else {
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

        $adminUserId = $db->lastInsertId();

        echo "  ✓ Admin user created successfully:\n";
        echo "    Email: " . ADMIN_EMAIL . "\n";
        echo "    Name: " . $name . "\n";
        echo "    Admin: Yes\n";
    }

    echo "\n";

} catch (PDOException $e) {
    die("Error: Failed to create admin user: " . $e->getMessage() . "\n");
}

// Create admin profile
try {
    echo "Step 5: Creating admin profile...\n";

    // Check if profile already exists
    $stmt = $db->prepare("SELECT id, profile_id FROM profiles WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $adminUserId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($profile) {
        echo "  ⓘ Profile already exists:\n";
        echo "    Profile ID: " . $profile['profile_id'] . "\n";
    } else {
        // Generate profile_id
        $profileId = 'user-' . $adminUserId . '-' . substr(md5(uniqid(rand(), true)), 0, 8);

        // Get "Available" status ID
        $availableStatusId = $db->query("SELECT id FROM profile_statuses WHERE slug = 'available'")->fetchColumn();
        if (!$availableStatusId) {
            $availableStatusId = 1; // Fallback to ID 1
        }

        $stmt = $db->prepare("
            INSERT INTO profiles (user_id, profile_id, bio, available, status_id, created_at, updated_at)
            VALUES (:user_id, :profile_id, :bio, 1, :status_id, datetime('now'), datetime('now'))
        ");

        $stmt->execute([
            ':user_id' => $adminUserId,
            ':profile_id' => $profileId,
            ':bio' => 'Admin user profile',
            ':status_id' => $availableStatusId
        ]);

        echo "  ✓ Profile created successfully:\n";
        echo "    Profile ID: " . $profileId . "\n";
        echo "    Status: Available\n";
    }

    echo "\n";

} catch (PDOException $e) {
    die("Error: Failed to create admin profile: " . $e->getMessage() . "\n");
}

echo "===========================================\n";
echo "  Database setup complete!\n";
echo "===========================================\n\n";

echo "Summary:\n";
echo "  - Database: " . basename($dbPath) . "\n";
echo "  - Admin Email: " . ADMIN_EMAIL . "\n";
echo "  - Profile Statuses: 4 statuses created\n";
echo "  - Categories: " . count($categoriesAndSkills) . " categories created\n";

// Count skills
$totalSkills = 0;
foreach ($categoriesAndSkills as $skills) {
    $totalSkills += count($skills);
}
echo "  - Skills: " . $totalSkills . " skills created\n\n";

echo "You can now access the application!\n";

// Close database connection
$db = null;

exit(0);
