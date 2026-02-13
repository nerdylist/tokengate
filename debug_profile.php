<?php
/**
 * Debug Profile Page - Shows detailed error information
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Profile Debug</h1>";

try {
    echo "<h2>1. Loading Config</h2>";
    require_once __DIR__ . '/config/session.php';
    echo "✓ Session config loaded<br>";

    require_once __DIR__ . '/config.php';
    echo "✓ Config loaded<br>";
    echo "DB_NAME: " . DB_NAME . "<br>";

    echo "<h2>2. Loading Classes</h2>";
    require_once __DIR__ . '/classes/Profile.php';
    echo "✓ Profile class loaded<br>";

    require_once __DIR__ . '/classes/Skill.php';
    echo "✓ Skill class loaded<br>";

    require_once __DIR__ . '/classes/User.php';
    echo "✓ User class loaded<br>";

    require_once __DIR__ . '/classes/Auth.php';
    echo "✓ Auth class loaded<br>";

    echo "<h2>3. Database Connection</h2>";
    $dbPath = __DIR__ . '/database/' . DB_NAME;
    echo "Database path: $dbPath<br>";
    echo "Database exists: " . (file_exists($dbPath) ? 'YES' : 'NO') . "<br>";

    if (file_exists($dbPath)) {
        echo "Database size: " . number_format(filesize($dbPath)) . " bytes<br>";
        echo "Database readable: " . (is_readable($dbPath) ? 'YES' : 'NO') . "<br>";
    }

    echo "<h2>4. Testing Profile Fetch</h2>";
    $profile_id = $_GET['id'] ?? 'NERD-001';
    echo "Looking for profile_id: $profile_id<br>";

    $profileModel = new Profile();
    echo "✓ Profile model instantiated<br>";

    $profile = $profileModel->where('profile_id', '=', $profile_id)->first();

    if (!$profile) {
        echo "<strong style='color: red;'>✗ Profile not found!</strong><br>";
        echo "This is likely why the page is failing.<br>";

        // Check if ANY profiles exist
        $allProfiles = $profileModel->all();
        echo "<br>Total profiles in database: " . count($allProfiles) . "<br>";

        if (!empty($allProfiles)) {
            echo "<br>Available profile IDs:<br>";
            echo "<ul>";
            foreach ($allProfiles as $p) {
                echo "<li>{$p['profile_id']} (User ID: {$p['user_id']})</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "✓ Profile found!<br>";
        echo "<pre>" . print_r($profile, true) . "</pre>";

        echo "<h2>5. Testing User Fetch</h2>";
        $userModel = new User();
        $user = $userModel->find($profile['user_id']);

        if (!$user) {
            echo "<strong style='color: red;'>✗ User not found!</strong><br>";
        } else {
            echo "✓ User found: {$user['name']} ({$user['email']})<br>";
        }

        echo "<h2>6. Testing Profile Methods</h2>";

        try {
            $skills = $profileModel->skills($profile['id']);
            echo "✓ skills() - " . count($skills) . " skills found<br>";
        } catch (Exception $e) {
            echo "<strong style='color: red;'>✗ skills() failed: {$e->getMessage()}</strong><br>";
        }

        try {
            $guilds = $profileModel->guilds($profile['id']);
            echo "✓ guilds() - " . count($guilds) . " guilds found<br>";
        } catch (Exception $e) {
            echo "<strong style='color: red;'>✗ guilds() failed: {$e->getMessage()}</strong><br>";
        }

        try {
            $primaryGuild = $profileModel->primaryGuild($profile['id']);
            echo "✓ primaryGuild() - " . ($primaryGuild ? "Found: {$primaryGuild['name']}" : "None set") . "<br>";
        } catch (Exception $e) {
            echo "<strong style='color: red;'>✗ primaryGuild() failed: {$e->getMessage()}</strong><br>";
        }

        try {
            $currentStatus = $profileModel->getStatus($profile['id']);
            echo "✓ getStatus() - " . ($currentStatus ? "Found: {$currentStatus['name']}" : "None set") . "<br>";
        } catch (Exception $e) {
            echo "<strong style='color: red;'>✗ getStatus() failed: {$e->getMessage()}</strong><br>";
        }

        try {
            $applications = $profileModel->applications($profile['id']);
            echo "✓ applications() - " . count($applications) . " applications found<br>";
        } catch (Exception $e) {
            echo "<strong style='color: red;'>✗ applications() failed: {$e->getMessage()}</strong><br>";
        }
    }

    echo "<h2>7. Database Tables Check</h2>";
    $db = new PDO('sqlite:' . $dbPath);
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in database:<br><ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";

    echo "<h2>✓ All checks complete</h2>";

} catch (Exception $e) {
    echo "<h2 style='color: red;'>ERROR</h2>";
    echo "<strong>Message:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>File:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Line:</strong> " . $e->getLine() . "<br>";
    echo "<strong>Trace:</strong><pre>" . $e->getTraceAsString() . "</pre>";
}
