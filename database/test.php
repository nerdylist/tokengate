<?php
/**
 * Database Test Script
 * Verifies that the database is working correctly
 */

require_once __DIR__ . '/../classes/Database.php';

try {
    echo "Testing database connection and queries...\n\n";

    $db = Database::getInstance();

    // Test 1: Query all users
    echo "Test 1: Querying all users\n";
    $users = $db->query("SELECT id, name, email, is_admin FROM users LIMIT 5");
    echo "Found " . count($users) . " users:\n";
    foreach ($users as $user) {
        echo "  - {$user['name']} ({$user['email']}) " . ($user['is_admin'] ? '[ADMIN]' : '') . "\n";
    }
    echo "\n";

    // Test 2: Query categories
    echo "Test 2: Querying categories\n";
    $categories = $db->query("SELECT name, slug FROM categories");
    echo "Found " . count($categories) . " categories:\n";
    foreach ($categories as $category) {
        echo "  - {$category['name']} ({$category['slug']})\n";
    }
    echo "\n";

    // Test 3: Query bounties with JOIN
    echo "Test 3: Querying bounties with user information\n";
    $bounties = $db->query("
        SELECT b.title, b.budget_min, b.budget_max, u.name as user_name, c.name as category
        FROM bounties b
        JOIN users u ON b.user_id = u.id
        JOIN categories c ON b.category_id = c.id
        LIMIT 5
    ");
    echo "Found " . count($bounties) . " bounties:\n";
    foreach ($bounties as $bounty) {
        echo "  - {$bounty['title']} by {$bounty['user_name']} ({$bounty['category']}) - \${$bounty['budget_min']}-\${$bounty['budget_max']}\n";
    }
    echo "\n";

    // Test 4: Query profiles with skills
    echo "Test 4: Querying profiles with skills\n";
    $profiles = $db->query("
        SELECT p.profile_id, p.bio, p.hourly_rate, COUNT(ps.skill_id) as skill_count
        FROM profiles p
        LEFT JOIN profile_skills ps ON p.id = ps.profile_id
        GROUP BY p.id
        LIMIT 5
    ");
    echo "Found " . count($profiles) . " profiles:\n";
    foreach ($profiles as $profile) {
        echo "  - {$profile['profile_id']} - \${$profile['hourly_rate']}/hr - {$profile['skill_count']} skills\n";
    }
    echo "\n";

    // Test 5: Test single row query
    echo "Test 5: Finding a specific user\n";
    $admin = $db->queryOne("SELECT * FROM users WHERE is_admin = 1 LIMIT 1");
    if ($admin) {
        echo "Admin user found: {$admin['name']} ({$admin['email']})\n";
    }
    echo "\n";

    // Test 6: Test count
    echo "Test 6: Counting records\n";
    $stats = [
        'users' => $db->queryOne("SELECT COUNT(*) as count FROM users")['count'],
        'bounties' => $db->queryOne("SELECT COUNT(*) as count FROM bounties")['count'],
        'profiles' => $db->queryOne("SELECT COUNT(*) as count FROM profiles")['count'],
        'applications' => $db->queryOne("SELECT COUNT(*) as count FROM applications")['count'],
    ];
    foreach ($stats as $table => $count) {
        echo "  - {$table}: {$count} records\n";
    }
    echo "\n";

    echo "All tests passed successfully!\n";
    echo "Database is fully operational.\n";

} catch (Exception $e) {
    die("Test failed: " . $e->getMessage() . "\n");
}
