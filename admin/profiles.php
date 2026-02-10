<?php
/**
 * Admin Profiles Management
 * List, view, and delete rent profiles
 */

require_once __DIR__ . '/../middleware/admin.php';

// Fetch all profiles
$sql = "SELECT rp.id, rp.profile_id, u.email, rp.bio, rp.hourly_rate, rp.is_available, rp.created_at
        FROM rent_profiles rp
        LEFT JOIN users u ON rp.user_id = u.id
        ORDER BY rp.created_at DESC";
$stmt = $db->query($sql);
$profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profiles Management - RentPeople.io Admin</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <?php include __DIR__ . '/partials/admin-header.php'; ?>

    <div class="admin-layout">
        <?php include __DIR__ . '/partials/admin-sidebar.php'; ?>

        <main class="admin-content">
            <h1>Profiles Management</h1>

            <div class="table-container">
                <div class="table-header">
                    <h2>All Profiles (<?= count($profiles) ?>)</h2>
                </div>

                <?php
                require_once __DIR__ . '/partials/data-table.php';

                $columns = [
                    'id' => 'ID',
                    'profile_id' => 'Profile ID',
                    'email' => 'User Email',
                    'bio' => 'Bio',
                    'hourly_rate' => 'Hourly Rate',
                    'is_available' => 'Available',
                    'created_at' => 'Created At'
                ];

                $actions = [
                    [
                        'type' => 'link',
                        'label' => 'View',
                        'class' => 'btn-secondary',
                        'href' => '/detail.php?id={profile_id}'
                    ],
                    [
                        'type' => 'button',
                        'label' => 'Delete',
                        'class' => 'btn-danger',
                        'onclick' => 'confirmDelete("profile", {id}, "{profile_id}")'
                    ]
                ];

                renderDataTable($columns, $profiles, $actions);
                ?>
            </div>
        </main>

        <?php include __DIR__ . '/partials/admin-footer.php'; ?>
    </div>

    <script src="/assets/js/admin.js"></script>
</body>
</html>
