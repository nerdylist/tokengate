<?php
/**
 * Admin Skills Management
 * List, add, edit, and delete skills
 */

require_once __DIR__ . '/../middleware/admin.php';

// Fetch skills with category name
$sql = "SELECT s.id, s.name, s.slug, c.name as category, s.created_at
        FROM skills s
        LEFT JOIN categories c ON s.category_id = c.id
        ORDER BY c.name ASC, s.name ASC";
$stmt = $db->query($sql);
$skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skills Management - RentPeople.io Admin</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <?php include __DIR__ . '/partials/admin-header.php'; ?>

    <div class="admin-layout">
        <?php include __DIR__ . '/partials/admin-sidebar.php'; ?>

        <main class="admin-content">
            <h1>Skills Management</h1>

            <div class="table-container">
                <div class="table-header">
                    <h2>All Skills (<?= count($skills) ?>)</h2>
                </div>

                <?php
                require_once __DIR__ . '/partials/data-table.php';

                $columns = [
                    'id' => 'ID',
                    'name' => 'Name',
                    'slug' => 'Slug',
                    'category' => 'Category',
                    'created_at' => 'Created At'
                ];

                $actions = [
                    [
                        'type' => 'button',
                        'label' => 'Delete',
                        'class' => 'btn-danger',
                        'onclick' => 'confirmDelete("skill", {id}, "{name}")'
                    ]
                ];

                renderDataTable($columns, $skills, $actions);
                ?>
            </div>
        </main>

        <?php include __DIR__ . '/partials/admin-footer.php'; ?>
    </div>

    <script src="/assets/js/admin.js"></script>
</body>
</html>
