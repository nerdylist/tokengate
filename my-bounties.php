<?php
require_once __DIR__ . '/config/session.php';
require_once 'config.php';
require_once 'classes/Auth.php';
require_once 'classes/Database.php';

// Check authentication
if (!Auth::check()) {
    header('Location: connect.php');
    exit;
}

$userId = Auth::id();
$db = Database::getInstance();

// Fetch user's bounties with application counts
$query = "
    SELECT
        b.id,
        b.title,
        b.status,
        b.budget_min,
        b.budget_max,
        b.deadline,
        b.created_at,
        c.name as category_name,
        c.slug as category_slug,
        COUNT(DISTINCT a.id) as application_count
    FROM bounties b
    LEFT JOIN categories c ON b.category_id = c.id
    LEFT JOIN applications a ON b.id = a.bounty_id
    WHERE b.user_id = ?
    GROUP BY b.id
    ORDER BY b.created_at DESC
";

$bounties = $db->query($query, [$userId]);

// Helper function for time ago
function getTimeAgo($datetime) {
    $created = new DateTime($datetime);
    $now = new DateTime();
    $diff = $now->diff($created);

    if ($diff->days > 0) {
        return $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
    } elseif ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    } else {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/assets/img/token/icon/up-gold.png">
    <link rel="apple-touch-icon" href="/assets/img/token/icon/up-gold.png">
    <meta property="og:title" content="My Bounties - <?php echo APP_NAME; ?>">
    <meta property="og:description" content="Manage your posted bounties">
    <meta property="og:image" content="https://redot.test/assets/img/token/icon/up-gold.png">
    <meta property="og:type" content="website">
    <title>My Bounties - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <section class="page-header">
                <div class="page-header-top">
                    <div class="page-title-wrapper">
                        <h1 class="page-title">my bounties</h1>
                    </div>
                    <a href="<?php echo url('hire'); ?>" class="btn-hire">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        new bounty
                    </a>
                </div>
                <p class="page-subtitle">manage your posted bounties</p>
            </section>

            <section class="tasks-section">
                <div class="tasks-list">
                    <?php if (empty($bounties)): ?>
                        <div class="empty-state">
                            <svg width="64" height="64" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="8" y="8" width="48" height="48" rx="8" />
                                <line x1="20" y1="28" x2="44" y2="28" />
                                <line x1="20" y1="36" x2="36" y2="36" />
                            </svg>
                            <h3>no bounties yet</h3>
                            <p>you haven't posted any bounties yet. start by posting your first bounty.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($bounties as $bounty): ?>
                            <div class="bounty-card">
                                <div class="bounty-status">
                                    <span class="badge badge-status-<?php echo htmlspecialchars($bounty['status']); ?>">
                                        <?php echo htmlspecialchars(str_replace('_', ' ', $bounty['status'])); ?>
                                    </span>
                                </div>

                                <div class="bounty-info">
                                    <h3 class="task-title">
                                        <a href="detail.php?id=<?php echo $bounty['id']; ?>">
                                            <?php echo htmlspecialchars($bounty['title']); ?>
                                        </a>
                                    </h3>
                                    <div class="task-meta">
                                        <span class="meta-item">
                                            <strong>Budget:</strong>
                                            $<?php echo number_format($bounty['budget_min'], 0); ?>
                                            <?php if ($bounty['budget_max'] && $bounty['budget_max'] != $bounty['budget_min']): ?>
                                                - $<?php echo number_format($bounty['budget_max'], 0); ?>
                                            <?php endif; ?>
                                        </span>
                                        <span class="meta-item">
                                            <strong>Deadline:</strong>
                                            <?php
                                            if ($bounty['deadline']) {
                                                echo date('M d, Y', strtotime($bounty['deadline']));
                                            } else {
                                                echo 'No deadline';
                                            }
                                            ?>
                                        </span>
                                        <span class="meta-item">
                                            <strong>Applications:</strong> <?php echo $bounty['application_count']; ?>
                                        </span>
                                        <span class="meta-item meta-time">
                                            Posted <?php echo getTimeAgo($bounty['created_at']); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="bounty-action">
                                    <a href="bounty-applicants.php?id=<?php echo $bounty['id']; ?>" class="btn-search">
                                        review applicants
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

    <?php include 'partials/footer.php'; ?>
</body>
</html>
