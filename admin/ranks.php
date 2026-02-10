<?php
/**
 * Admin Ranks Management
 * List, add, edit, and delete ranks
 */

require_once __DIR__ . '/../middleware/admin.php';

// Fetch ranks
$sql = "SELECT id, name, level, type, xp_required, description, created_at
        FROM ranks
        ORDER BY type ASC, level ASC";
$stmt = $db->query($sql);
$ranks = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranks Management - RentPeople.io Admin</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/partials/admin-sidebar.php'; ?>

        <main class="admin-content">
            <h1>Ranks Management</h1>

            <div class="table-container">
                <div class="table-header">
                    <h2>All Ranks (<?= count($ranks) ?>)</h2>
                </div>

                <?php
                require_once __DIR__ . '/partials/data-table.php';

                $columns = [
                    'id' => 'ID',
                    'name' => 'Name',
                    'level' => 'Level',
                    'type' => 'Type',
                    'xp_required' => 'XP Required',
                    'description' => 'Description',
                    'created_at' => 'Created At'
                ];

                $actions = [
                    [
                        'type' => 'button',
                        'label' => 'Delete',
                        'class' => 'btn-danger',
                        'onclick' => 'confirmDelete("rank", {id}, "{name}")'
                    ]
                ];

                renderDataTable($columns, $ranks, $actions);
                ?>
            </div>
        </main>

        <?php include __DIR__ . '/partials/admin-footer.php'; ?>
    </div>

    <script src="/assets/js/admin.js"></script>
</body>
</html>
