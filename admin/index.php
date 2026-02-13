<?php
/**
 * Admin Dashboard Home
 * Displays stats and recent activity
 */

require_once __DIR__ . '/../middleware/admin.php';
require_once __DIR__ . '/../controllers/AdminController.php';

$controller = new AdminController($db);
$stats = $controller->getDashboardStats();
$activity = $controller->getRecentActivity();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TokenG8.com</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/partials/admin-sidebar.php'; ?>

        <main class="admin-content">
            <h1>Dashboard</h1>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Users</div>
                    <div class="stat-value"><?= $stats['users'] ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Total Bounties</div>
                    <div class="stat-value"><?= $stats['bounties'] ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Total Profiles</div>
                    <div class="stat-value"><?= $stats['profiles'] ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Total Applications</div>
                    <div class="stat-value"><?= $stats['applications'] ?></div>
                </div>
            </div>

            <div class="recent-activity">
                <h2>Recent Activity</h2>

                <div class="activity-grid">
                    <div class="activity-card">
                        <h3>Recent Bounties</h3>
                        <ul class="activity-list">
                            <?php if (empty($activity['bounties'])): ?>
                                <li class="text-muted">No recent bounties</li>
                            <?php else: ?>
                                <?php foreach ($activity['bounties'] as $bounty): ?>
                                    <li>
                                        <div class="activity-title">
                                            <?= htmlspecialchars($bounty['title']) ?>
                                        </div>
                                        <div class="activity-meta">
                                            Posted by <?= htmlspecialchars($bounty['user_email']) ?>
                                            on <?= date('M d, Y', strtotime($bounty['created_at'])) ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="activity-card">
                        <h3>Recent Applications</h3>
                        <ul class="activity-list">
                            <?php if (empty($activity['applications'])): ?>
                                <li class="text-muted">No recent applications</li>
                            <?php else: ?>
                                <?php foreach ($activity['applications'] as $application): ?>
                                    <li>
                                        <div class="activity-title">
                                            Application for: <?= htmlspecialchars($application['bounty_title']) ?>
                                        </div>
                                        <div class="activity-meta">
                                            Profile ID: <?= htmlspecialchars($application['profile_id']) ?>
                                            | Status: <span class="badge badge-<?= $application['status'] === 'accepted' ? 'success' : ($application['status'] === 'rejected' ? 'danger' : 'warning') ?>">
                                                <?= htmlspecialchars($application['status']) ?>
                                            </span>
                                            | <?= date('M d, Y', strtotime($application['created_at'])) ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </main>

        <?php include __DIR__ . '/partials/admin-footer.php'; ?>
    </div>

    <script src="/assets/js/admin.js"></script>
</body>
</html>
