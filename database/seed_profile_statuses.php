<?php
/**
 * Profile Status Seeder
 * Populates the profile_statuses table with default statuses
 * Usage: php database/seed_profile_statuses.php
 */

require_once __DIR__ . '/connection.php';

$statuses = [
    [
        'name' => 'Available',
        'slug' => 'available',
        'color' => '#10b981',
        'sort_order' => 1,
        'is_active' => 1
    ],
    [
        'name' => 'Busy',
        'slug' => 'busy',
        'color' => '#f59e0b',
        'sort_order' => 2,
        'is_active' => 1
    ],
    [
        'name' => 'Unavailable',
        'slug' => 'unavailable',
        'color' => '#ef4444',
        'sort_order' => 3,
        'is_active' => 1
    ],
    [
        'name' => 'Away',
        'slug' => 'away',
        'color' => '#6b7280',
        'sort_order' => 4,
        'is_active' => 1
    ]
];

try {
    echo "Starting profile status seeding...\n\n";

    // Prepare insert statement
    $stmt = $pdo->prepare("INSERT INTO profile_statuses (name, slug, color, sort_order, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)");

    $count = 0;
    foreach ($statuses as $status) {
        $now = date('Y-m-d H:i:s');
        $stmt->execute([
            $status['name'],
            $status['slug'],
            $status['color'],
            $status['sort_order'],
            $status['is_active'],
            $now,
            $now
        ]);
        $count++;
        echo "Inserted: " . $status['name'] . " (" . $status['color'] . ")\n";
    }

    echo "\nSeeding complete!\n";
    echo "Total: " . $count . " profile statuses inserted\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
