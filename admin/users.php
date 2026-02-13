<?php
/**
 * Admin Users Management
 * List, edit, and delete users
 */

require_once __DIR__ . '/../middleware/admin.php';

// Fetch all users with profile information
$sql = "SELECT u.id, u.email, u.name, u.is_admin, u.created_at,
       COALESCE(p.profile_id, u.profile_id) as profile_id, p.id as profile_db_id
FROM users u
LEFT JOIN profiles p ON u.id = p.user_id
ORDER BY u.created_at DESC";
$stmt = $db->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - TokenG8.com Admin</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/partials/admin-sidebar.php'; ?>

        <main class="admin-content">
            <h1>Users Management</h1>

            <div class="table-container">
                <div class="table-header">
                    <h2>All Users (<?= count($users) ?>)</h2>
                </div>

                <?php
                require_once __DIR__ . '/partials/data-table.php';

                $columns = [
                    'id' => 'ID',
                    'email' => 'Email',
                    'name' => 'Name',
                    'is_admin' => 'Admin',
                    'profile_id' => 'Profile',
                    'created_at' => 'Created At'
                ];

                // Store raw profile_id before formatting
                foreach ($users as &$user) {
                    $user['profile_id_raw'] = $user['profile_id'] ?? '';
                }
                unset($user);

                // Format profile_id display
                foreach ($users as &$user) {
                    if (empty($user['profile_id'])) {
                        $user['profile_id'] = '<span style="color: #71717a;">No Profile</span>';
                    } else {
                        $user['profile_id'] = '<span style="color: #22c55e;">' . htmlspecialchars($user['profile_id_raw']) . '</span>';
                    }
                }
                unset($user);

                $actions = [
                    [
                        'type' => 'button',
                        'label' => 'Toggle Admin',
                        'class' => 'btn-warning',
                        'onclick' => 'toggleUserAdmin({id}, {is_admin})'
                    ],
                    [
                        'type' => 'conditional',
                        'condition' => 'profile_id_raw',
                        'label' => 'View Profile',
                        'class' => 'btn-secondary',
                        'onclick' => 'window.open("/profile.php?id={profile_id_raw}", "_blank")'
                    ],
                    [
                        'type' => 'button',
                        'label' => 'Delete',
                        'class' => 'btn-danger',
                        'onclick' => 'confirmDelete("user", {id}, "{email}")'
                    ]
                ];

                renderDataTable($columns, $users, $actions);
                ?>
            </div>
        </main>

        <?php include __DIR__ . '/partials/admin-footer.php'; ?>
    </div>

    <script src="/assets/js/admin.js"></script>
</body>
</html>
