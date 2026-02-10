<?php
/**
 * Admin Guilds Management
 * List, add, edit, and delete guilds
 */

require_once __DIR__ . '/../middleware/admin.php';

// Fetch guilds
$sql = "SELECT id, name, slug, type, created_at
        FROM guilds
        ORDER BY name ASC";
$stmt = $db->query($sql);
$guilds = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guilds Management - RentPeople.io Admin</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/partials/admin-sidebar.php'; ?>

        <main class="admin-content">
            <h1>Guilds Management</h1>

            <div class="table-container">
                <div class="table-header">
                    <h2>All Guilds (<?= count($guilds) ?>)</h2>
                </div>

                <?php
                require_once __DIR__ . '/partials/data-table.php';

                $columns = [
                    'id' => 'ID',
                    'name' => 'Name',
                    'slug' => 'Slug',
                    'type' => 'Type',
                    'created_at' => 'Created At'
                ];

                $actions = [
                    [
                        'type' => 'button',
                        'label' => 'Delete',
                        'class' => 'btn-danger',
                        'onclick' => 'confirmDelete("guild", {id}, "{name}")'
                    ]
                ];

                renderDataTable($columns, $guilds, $actions);
                ?>
            </div>
        </main>

        <?php include __DIR__ . '/partials/admin-footer.php'; ?>
    </div>

    <script src="/assets/js/admin.js"></script>
</body>
</html>
