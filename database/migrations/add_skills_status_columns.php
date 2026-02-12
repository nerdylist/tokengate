<?php
/**
 * Add status and submitted_by_profile_id columns to skills table
 *
 * Fixes ERROR 500 on admin/skills.php page
 * Usage: php database/migrations/add_skills_status_columns.php
 */

require_once __DIR__ . '/../../config.php';

$dbPath = __DIR__ . '/../g8.db';

if (!file_exists($dbPath)) {
    echo "Error: Database file not found at {$dbPath}\n";
    exit(1);
}

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Adding columns to skills table...\n\n";

    // Check existing columns
    $columns = $pdo->query("PRAGMA table_info(skills)")->fetchAll(PDO::FETCH_ASSOC);
    $existingColumns = array_column($columns, 'name');

    $hasStatus = in_array('status', $existingColumns);
    $hasSubmittedBy = in_array('submitted_by_profile_id', $existingColumns);

    // Add status column
    if ($hasStatus) {
        echo "✓ Column 'status' already exists in skills table\n";
    } else {
        $pdo->exec("ALTER TABLE skills ADD COLUMN status VARCHAR(50) DEFAULT 'approved'");
        echo "✓ Successfully added 'status' column to skills table\n";
    }

    // Add submitted_by_profile_id column
    if ($hasSubmittedBy) {
        echo "✓ Column 'submitted_by_profile_id' already exists in skills table\n";
    } else {
        $pdo->exec("ALTER TABLE skills ADD COLUMN submitted_by_profile_id INTEGER");
        echo "✓ Successfully added 'submitted_by_profile_id' column to skills table\n";
    }

    // Update existing skills to have 'approved' status
    if (!$hasStatus) {
        $pdo->exec("UPDATE skills SET status = 'approved' WHERE status IS NULL");
        echo "✓ Set all existing skills to 'approved' status\n";
    }

    echo "\n";

    // Verify columns were added
    $columns = $pdo->query("PRAGMA table_info(skills)")->fetchAll(PDO::FETCH_ASSOC);
    $verifiedColumns = array_column($columns, 'name');

    $statusExists = in_array('status', $verifiedColumns);
    $submittedByExists = in_array('submitted_by_profile_id', $verifiedColumns);

    if ($statusExists && $submittedByExists) {
        echo "✅ Migration completed successfully!\n";
        echo "\nSkills table now has:\n";
        foreach ($columns as $col) {
            if ($col['name'] === 'status' || $col['name'] === 'submitted_by_profile_id') {
                echo "  - {$col['name']}: {$col['type']} (default: {$col['dflt_value']})\n";
            }
        }
        exit(0);
    } else {
        echo "✗ Error: Failed to add columns\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
