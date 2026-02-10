<?php
/**
 * Admin Applications Management
 * List, filter, and manage applications
 */

require_once __DIR__ . '/../middleware/admin.php';

// Get filter
$statusFilter = $_GET['status'] ?? 'all';

// Fetch applications
$sql = "SELECT a.id, b.title as bounty_title, a.profile_id, a.status, a.proposed_rate,
        a.cover_letter, a.created_at
        FROM applications a
        LEFT JOIN bounties b ON a.bounty_id = b.id";

if ($statusFilter !== 'all') {
    $sql .= " WHERE a.status = :status";
}

$sql .= " ORDER BY a.created_at DESC";

$stmt = $db->prepare($sql);
if ($statusFilter !== 'all') {
    $stmt->execute(['status' => $statusFilter]);
} else {
    $stmt->execute();
}
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications Management - RentPeople.io Admin</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/partials/admin-sidebar.php'; ?>

        <main class="admin-content">
            <h1>Applications Management</h1>

            <div class="filters">
                <div class="filter-group">
                    <label for="status-filter">Filter by Status</label>
                    <select id="status-filter" onchange="window.location.href='?status=' + this.value">
                        <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                        <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="accepted" <?= $statusFilter === 'accepted' ? 'selected' : '' ?>>Accepted</option>
                        <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h2>All Applications (<?= count($applications) ?>)</h2>
                </div>

                <?php
                require_once __DIR__ . '/partials/data-table.php';

                $columns = [
                    'id' => 'ID',
                    'bounty_title' => 'Bounty',
                    'profile_id' => 'Profile ID',
                    'status' => 'Status',
                    'proposed_rate' => 'Proposed Rate',
                    'cover_letter' => 'Cover Letter',
                    'created_at' => 'Created At'
                ];

                $actions = [
                    [
                        'type' => 'button',
                        'label' => 'Accept',
                        'class' => 'btn-success',
                        'onclick' => 'updateStatus("application", {id}, "accepted")'
                    ],
                    [
                        'type' => 'button',
                        'label' => 'Reject',
                        'class' => 'btn-warning',
                        'onclick' => 'updateStatus("application", {id}, "rejected")'
                    ],
                    [
                        'type' => 'button',
                        'label' => 'Delete',
                        'class' => 'btn-danger',
                        'onclick' => 'confirmDelete("application", {id}, "Application #{id}")'
                    ]
                ];

                renderDataTable($columns, $applications, $actions);
                ?>
            </div>
        </main>

        <?php include __DIR__ . '/partials/admin-footer.php'; ?>
    </div>

    <script src="/assets/js/admin.js"></script>
</body>
</html>
