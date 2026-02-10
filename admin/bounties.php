<?php
/**
 * Admin Bounties Management
 * List, filter, edit, and delete bounties
 */

require_once __DIR__ . '/../middleware/admin.php';

// Get filter
$statusFilter = $_GET['status'] ?? 'all';

// Fetch bounties
$sql = "SELECT b.id, b.title, c.name as category, b.budget_min, b.budget_max, b.status, u.email as user_email, b.created_at
        FROM bounties b
        LEFT JOIN categories c ON b.category_id = c.id
        LEFT JOIN users u ON b.user_id = u.id";

if ($statusFilter !== 'all') {
    $sql .= " WHERE b.status = :status";
}

$sql .= " ORDER BY b.created_at DESC";

$stmt = $db->prepare($sql);
if ($statusFilter !== 'all') {
    $stmt->execute(['status' => $statusFilter]);
} else {
    $stmt->execute();
}
$bounties = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bounties Management - RentPeople.io Admin</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/partials/admin-sidebar.php'; ?>

        <main class="admin-content">
            <h1>Bounties Management</h1>

            <div class="filters">
                <div class="filter-group">
                    <label for="status-filter">Filter by Status</label>
                    <select id="status-filter" onchange="window.location.href='?status=' + this.value">
                        <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                        <option value="open" <?= $statusFilter === 'open' ? 'selected' : '' ?>>Open</option>
                        <option value="in_progress" <?= $statusFilter === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h2>All Bounties (<?= count($bounties) ?>)</h2>
                </div>

                <?php
                require_once __DIR__ . '/partials/data-table.php';

                $columns = [
                    'id' => 'ID',
                    'title' => 'Title',
                    'category' => 'Category',
                    'budget_min' => 'Min Budget',
                    'budget_max' => 'Max Budget',
                    'status' => 'Status',
                    'user_email' => 'Posted By',
                    'created_at' => 'Created At'
                ];

                $actions = [
                    [
                        'type' => 'button',
                        'label' => 'Open',
                        'class' => 'btn-success',
                        'onclick' => 'updateStatus("bounty", {id}, "open")'
                    ],
                    [
                        'type' => 'button',
                        'label' => 'Complete',
                        'class' => 'btn-success',
                        'onclick' => 'updateStatus("bounty", {id}, "completed")'
                    ],
                    [
                        'type' => 'button',
                        'label' => 'Cancel',
                        'class' => 'btn-warning',
                        'onclick' => 'updateStatus("bounty", {id}, "cancelled")'
                    ],
                    [
                        'type' => 'button',
                        'label' => 'Delete',
                        'class' => 'btn-danger',
                        'onclick' => 'confirmDelete("bounty", {id}, "{title}")'
                    ]
                ];

                renderDataTable($columns, $bounties, $actions);
                ?>
            </div>
        </main>

        <?php include __DIR__ . '/partials/admin-footer.php'; ?>
    </div>

    <script src="/assets/js/admin.js"></script>
</body>
</html>
