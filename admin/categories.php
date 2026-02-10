<?php
/**
 * Admin Categories Management
 * List, add, edit, and delete categories
 */

require_once __DIR__ . '/../middleware/admin.php';

// Fetch categories with bounty count
$sql = "SELECT c.id, c.name, c.slug, COUNT(b.id) as bounty_count, c.created_at
        FROM categories c
        LEFT JOIN bounties b ON c.id = b.category_id
        GROUP BY c.id
        ORDER BY c.name ASC";
$stmt = $db->query($sql);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories Management - RentPeople.io Admin</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <?php include __DIR__ . '/partials/admin-header.php'; ?>

    <div class="admin-layout">
        <?php include __DIR__ . '/partials/admin-sidebar.php'; ?>

        <main class="admin-content">
            <h1>Categories Management</h1>

            <div class="table-container">
                <div class="table-header">
                    <h2>All Categories (<?= count($categories) ?>)</h2>
                </div>

                <?php
                require_once __DIR__ . '/partials/data-table.php';

                $columns = [
                    'id' => 'ID',
                    'name' => 'Name',
                    'slug' => 'Slug',
                    'bounty_count' => 'Bounties',
                    'created_at' => 'Created At'
                ];

                $actions = [
                    [
                        'type' => 'button',
                        'label' => 'Delete',
                        'class' => 'btn-danger',
                        'onclick' => 'confirmDelete("category", {id}, "{name}")'
                    ]
                ];

                renderDataTable($columns, $categories, $actions);
                ?>
            </div>
        </main>

        <?php include __DIR__ . '/partials/admin-footer.php'; ?>
    </div>

    <script src="/assets/js/admin.js"></script>
</body>
</html>
