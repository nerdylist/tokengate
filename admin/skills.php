<?php
/**
 * Admin Skills Management
 * List, add, edit, and delete skills
 */

require_once __DIR__ . '/../middleware/admin.php';

// Get filter from query parameter
$statusFilter = $_GET['status'] ?? 'all';

// Build SQL query based on filter
$sql = "SELECT s.id, s.name, s.slug, c.name as category, s.status,
               s.submitted_by_profile_id, p.profile_id as submitter_profile_id,
               s.created_at
        FROM skills s
        LEFT JOIN categories c ON s.category_id = c.id
        LEFT JOIN profiles p ON s.submitted_by_profile_id = p.id";

if ($statusFilter === 'pending') {
    $sql .= " WHERE s.status = 'pending'";
} elseif ($statusFilter === 'approved') {
    $sql .= " WHERE s.status = 'approved'";
} elseif ($statusFilter === 'rejected') {
    $sql .= " WHERE s.status = 'rejected'";
}

$sql .= " ORDER BY
          CASE s.status
            WHEN 'pending' THEN 1
            WHEN 'approved' THEN 2
            WHEN 'rejected' THEN 3
          END,
          c.name ASC, s.name ASC";

$stmt = $db->query($sql);
$skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skills Management - TokenG8.com Admin</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/partials/admin-sidebar.php'; ?>

        <main class="admin-content">
            <h1>Skills Management</h1>

            <div class="table-container">
                <div class="table-header">
                    <h2>
                        <?php
                        $statusLabel = $statusFilter === 'all' ? 'All' : ucfirst($statusFilter);
                        echo $statusLabel . ' Skills (' . count($skills) . ')';
                        ?>
                    </h2>
                    <div style="margin-left: auto;">
                        <label for="status-filter" style="margin-right: 8px; color: #a0a0a0;">Filter:</label>
                        <select id="status-filter" onchange="window.location.href='?status='+this.value" style="padding: 8px 12px; background: #1a1a1a; border: 1px solid #333; color: #fff; border-radius: 4px;">
                            <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Skills</option>
                            <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table id="data-table">
                        <thead>
                            <tr>
                                <th class="sortable">ID</th>
                                <th class="sortable">Name</th>
                                <th class="sortable">Slug</th>
                                <th class="sortable">Category</th>
                                <th class="sortable">Submitted By</th>
                                <th class="sortable">Status</th>
                                <th class="sortable">Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($skills)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        No skills found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($skills as $skill): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($skill['id']) ?></td>
                                        <td><?= htmlspecialchars($skill['name']) ?></td>
                                        <td><?= htmlspecialchars($skill['slug']) ?></td>
                                        <td><?= htmlspecialchars($skill['category'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($skill['submitter_profile_id'] ?? 'System') ?></td>
                                        <td>
                                            <?php
                                            $statusColors = [
                                                'approved' => '#10b981',
                                                'pending' => '#f59e0b',
                                                'rejected' => '#ef4444'
                                            ];
                                            $statusColor = $statusColors[$skill['status']] ?? '#6b7280';
                                            ?>
                                            <span style="display: inline-block; padding: 4px 12px; background: <?= $statusColor ?>22; color: <?= $statusColor ?>; border-radius: 12px; font-size: 0.875rem; font-weight: 600;">
                                                <?= ucfirst($skill['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($skill['created_at'])) ?></td>
                                        <td class="actions">
                                            <div class="btn-group">
                                                <?php if ($skill['status'] === 'pending'): ?>
                                                    <button type="button" class="btn btn-small btn-success" onclick="approveSkill(<?= $skill['id'] ?>, '<?= htmlspecialchars($skill['name'], ENT_QUOTES) ?>')">
                                                        Approve
                                                    </button>
                                                    <button type="button" class="btn btn-small btn-danger" onclick="rejectSkill(<?= $skill['id'] ?>, '<?= htmlspecialchars($skill['name'], ENT_QUOTES) ?>')">
                                                        Reject
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-small btn-danger" onclick="confirmDelete('skill', <?= $skill['id'] ?>, '<?= htmlspecialchars($skill['name'], ENT_QUOTES) ?>')">
                                                        Delete
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <?php include __DIR__ . '/partials/admin-footer.php'; ?>
    </div>

    <script src="/assets/js/admin.js"></script>
</body>
</html>
