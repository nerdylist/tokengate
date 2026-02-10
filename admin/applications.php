<?php
/**
 * Admin Applications Management
 * List, filter, and manage applications
 */

require_once __DIR__ . '/../middleware/admin.php';

// Get filter
$statusFilter = $_GET['status'] ?? 'all';

// Fetch applications with JOIN to profiles, bounties, and users tables
$sql = "SELECT
        a.id,
        a.bounty_id,
        a.profile_id,
        a.status,
        a.proposed_rate,
        a.cover_letter,
        a.created_at,
        b.title as bounty_title,
        u.name as applicant_name,
        u.email as applicant_email,
        p.profile_id as applicant_profile_id
        FROM applications a
        LEFT JOIN bounties b ON a.bounty_id = b.id
        LEFT JOIN profiles p ON a.profile_id = p.id
        LEFT JOIN users u ON p.user_id = u.id";

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

                // Format applications data for better display
                foreach ($applications as &$app) {
                    // Store raw values for links
                    $app['bounty_id_raw'] = $app['bounty_id'] ?? '';
                    $app['applicant_profile_id_raw'] = $app['applicant_profile_id'] ?? '';

                    // Format bounty title with link
                    if (!empty($app['bounty_title']) && !empty($app['bounty_id'])) {
                        $app['bounty_title_display'] = '<a href="/detail.php?id=' . htmlspecialchars($app['bounty_id']) . '" target="_blank" style="color: #3b82f6; text-decoration: underline;">' . htmlspecialchars($app['bounty_title']) . '</a>';
                    } else {
                        $app['bounty_title_display'] = '<span style="color: #71717a;">Unknown Bounty</span>';
                    }

                    // Format applicant name with link to profile
                    if (!empty($app['applicant_name']) && !empty($app['applicant_profile_id'])) {
                        $app['applicant_name_display'] = '<a href="/profile.php?id=' . htmlspecialchars($app['applicant_profile_id']) . '" target="_blank" style="color: #22c55e; text-decoration: underline;">' . htmlspecialchars($app['applicant_name']) . '</a>';
                    } elseif (!empty($app['applicant_name'])) {
                        $app['applicant_name_display'] = htmlspecialchars($app['applicant_name']);
                    } else {
                        $app['applicant_name_display'] = '<span style="color: #71717a;">Unknown</span>';
                    }

                    // Format cover letter with truncation and expandable view
                    if (!empty($app['cover_letter'])) {
                        $coverLetter = $app['cover_letter'];
                        if (strlen($coverLetter) > 100) {
                            $app['cover_letter_display'] = '<span class="truncate" title="' . htmlspecialchars($coverLetter) . '">' . htmlspecialchars(substr($coverLetter, 0, 100)) . '... <a href="#" onclick="alert(\'' . htmlspecialchars(addslashes($coverLetter)) . '\'); return false;" style="color: #3b82f6; text-decoration: underline;">View More</a></span>';
                        } else {
                            $app['cover_letter_display'] = htmlspecialchars($coverLetter);
                        }
                    } else {
                        $app['cover_letter_display'] = '<span style="color: #71717a;">No cover letter</span>';
                    }
                }
                unset($app);

                $columns = [
                    'id' => 'Application ID',
                    'bounty_title_display' => 'Bounty Title',
                    'applicant_name_display' => 'Applicant Name',
                    'applicant_email' => 'Email',
                    'proposed_rate' => 'Proposed Rate',
                    'cover_letter_display' => 'Cover Letter',
                    'status' => 'Status',
                    'created_at' => 'Date Applied'
                ];

                $actions = [
                    [
                        'type' => 'button',
                        'label' => 'Approve',
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
