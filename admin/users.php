<?php
/**
 * Admin Users Management
 * List, edit, and delete users
 */

require_once __DIR__ . '/../middleware/admin.php';

// Fetch all users
$stmt = $db->query("SELECT id, email, COALESCE(first_name, '') || ' ' || COALESCE(last_name, '') as name, is_admin, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - RentPeople.io Admin</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <?php include __DIR__ . '/partials/admin-header.php'; ?>

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
                    'created_at' => 'Created At'
                ];

                $actions = [
                    [
                        'type' => 'button',
                        'label' => 'Toggle Admin',
                        'class' => 'btn-warning',
                        'onclick' => 'toggleUserAdmin({id}, {is_admin})'
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
