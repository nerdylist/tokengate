<?php
require_once __DIR__ . '/config/session.php';
require_once 'config.php';
require_once 'classes/Auth.php';
require_once 'controllers/BountyController.php';

// Initialize controller
$bountyController = new BountyController();

// Build filters from GET parameters
$filters = [];
if (!empty($_GET['category'])) {
    // Get category ID from slug/name
    $db = Database::getInstance();
    $categoryResult = $db->queryOne("SELECT id FROM categories WHERE slug = ? OR name = ?", [$_GET['category'], $_GET['category']]);
    if ($categoryResult) {
        $filters['category_id'] = $categoryResult['id'];
    }
}

if (!empty($_GET['min_price'])) {
    $filters['budget_min'] = (float)$_GET['min_price'];
}

if (!empty($_GET['max_price'])) {
    $filters['budget_max'] = (float)$_GET['max_price'];
}

// Default to only open bounties
$filters['status'] = 'open';

// Get sorting from tabs
$sort = $_GET['sort'] ?? 'new';

// Fetch bounties from database
try {
    $bounties = $bountyController->index($filters);

    // Apply sorting
    if ($sort === 'top') {
        // Sort by vote count (highest to lowest)
        usort($bounties, function($a, $b) {
            return $b['vote_count'] - $a['vote_count'];
        });
    }
    // 'new' is default (already sorted by created_at DESC)

    // Fetch user's votes for all bounties if logged in
    $userVotes = [];
    if (Auth::check()) {
        $db = Database::getInstance();
        $userId = Auth::id();
        $bountyIds = array_column($bounties, 'id');
        if (!empty($bountyIds)) {
            $placeholders = implode(',', array_fill(0, count($bountyIds), '?'));
            $voteResults = $db->query(
                "SELECT bounty_id, vote_type FROM bounty_votes WHERE user_id = ? AND bounty_id IN ($placeholders)",
                array_merge([$userId], $bountyIds)
            );
            foreach ($voteResults as $vote) {
                $userVotes[$vote['bounty_id']] = (int)$vote['vote_type'];
            }
        }
    }

    // Transform bounties to match task-card.php expected format
    $tasks = array_map(function($bounty) use ($userVotes) {
        // Calculate days until deadline
        $due_days = 'no deadline';
        if (!empty($bounty['deadline'])) {
            $deadline = new DateTime($bounty['deadline']);
            $now = new DateTime();
            $diff = $now->diff($deadline);
            if ($diff->invert) {
                $due_days = 'overdue';
            } else {
                $due_days = $diff->days;
            }
        }

        // Calculate time ago
        $created = new DateTime($bounty['created_at']);
        $now = new DateTime();
        $diff = $now->diff($created);

        if ($diff->days > 0) {
            $posted_time = $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
        } elseif ($diff->h > 0) {
            $posted_time = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        } else {
            $posted_time = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        }

        // Convert skills array to tags
        $tags = array_map(function($skill) {
            return $skill['name'];
        }, $bounty['skills'] ?? []);

        return [
            'id' => $bounty['id'],
            'votes' => $bounty['vote_count'] ?? 0,
            'user_vote' => $userVotes[$bounty['id']] ?? 0,
            'category' => $bounty['category_slug'] ?? $bounty['category_name'] ?? 'other',
            'due_days' => $due_days,
            'spots_filled' => 0, // TODO: Implement from applications table
            'spots_total' => 1, // TODO: Add spots field to bounties table
            'price' => ($bounty['budget_min'] + $bounty['budget_max']) / 2 ?? $bounty['budget_min'] ?? $bounty['budget_max'] ?? 0,
            'title' => $bounty['title'],
            'description' => $bounty['description'],
            'tags' => $tags,
            'location' => 'remote', // TODO: Add location field to bounties table
            'duration' => $due_days !== 'no deadline' && is_numeric($due_days) ? $due_days . ' days' : 'flexible',
            'applications' => $bounty['application_count'] ?? 0,
            'posted_time' => $posted_time
        ];
    }, $bounties);

} catch (Exception $e) {
    error_log("Error fetching bounties: " . $e->getMessage());
    $tasks = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/assets/img/token/icon/up-gold.png">
    <link rel="apple-touch-icon" href="/assets/img/token/icon/up-gold.png">
    <meta property="og:title" content="<?php echo APP_NAME; ?>">
    <meta property="og:description" content="Post tasks, get it done by pros">
    <meta property="og:image" content="https://redot.test/assets/img/token/icon/up-gold.png">
    <meta property="og:url" content="https://redot.test">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?php echo APP_NAME; ?>">
    <meta name="twitter:description" content="Post tasks, get it done by pros">
    <meta name="twitter:image" content="https://redot.test/assets/img/token/icon/up-gold.png">
    <title>Task Bounties - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <section class="page-header">
                <div class="page-header-top">
                    <div class="page-title-wrapper">
                        <h1 class="page-title">
                            task bounties
                            <span class="badge badge-new">new</span>
                        </h1>
                    </div>
                    <a href="<?php echo url('hire'); ?>" class="btn-hire">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        new bounty
                    </a>
                </div>
                <p class="page-subtitle">post tasks, get it done by pros</p>
            </section>

            <?php include 'partials/filters.php'; ?>

            <section class="tabs-section">
                <div class="tabs">
                    <a href="<?php echo url('index', array_merge($_GET, ['sort' => 'new'])); ?>" class="tab <?php echo ($sort === 'new' || empty($sort)) ? 'active' : ''; ?>" data-tab="new">new</a>
                    <a href="<?php echo url('index', array_merge($_GET, ['sort' => 'top'])); ?>" class="tab <?php echo $sort === 'top' ? 'active' : ''; ?>" data-tab="top">top</a>
                </div>
            </section>

            <section class="tasks-section">
                <div class="tasks-list">
                    <?php if (empty($tasks)): ?>
                        <div class="empty-state" style="text-align: center; padding: 60px 20px; color: #71717a;">
                            <p style="font-size: 1.125rem; margin-bottom: 8px;">no bounties found</p>
                            <p style="font-size: 0.9375rem;">try adjusting your filters or <a href="<?php echo url('hire'); ?>" style="color: #4ade80; text-decoration: none;">post a new bounty</a></p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($tasks as $task): ?>
                            <?php include 'partials/task-card.php'; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

    <script src="/assets/js/app.js"></script>
    <?php include 'partials/footer.php'; ?>
</body>
</html>
