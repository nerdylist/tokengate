<?php
require_once __DIR__ . '/config/session.php';
require_once 'config.php';
require_once 'controllers/SearchController.php';

// Get search term from URL
$searchKey = $_GET['key'] ?? '';

// Initialize controller
$searchController = new SearchController();

// Perform search
$bounties = [];
$profiles = [];
$searchResults = [];

try {
    if (!empty($searchKey)) {
        $searchResults = $searchController->search($searchKey);
        $bounties = $searchResults['bounties'] ?? [];
        $profiles = $searchResults['profiles'] ?? [];
    }
} catch (Exception $e) {
    error_log("Search error: " . $e->getMessage());
    $bounties = [];
    $profiles = [];
}

// Transform bounties to match task-card.php expected format
$tasks = array_map(function($bounty) {
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
        'votes' => 0,
        'category' => $bounty['category_slug'] ?? $bounty['category_name'] ?? 'other',
        'due_days' => $due_days,
        'spots_filled' => 0,
        'spots_total' => 1,
        'price' => ($bounty['budget_min'] + $bounty['budget_max']) / 2 ?? $bounty['budget_min'] ?? $bounty['budget_max'] ?? 0,
        'title' => $bounty['title'],
        'description' => $bounty['description'],
        'tags' => $tags,
        'location' => 'remote',
        'duration' => $due_days !== 'no deadline' && is_numeric($due_days) ? $due_days . ' days' : 'flexible',
        'applications' => $bounty['application_count'] ?? 0,
        'posted_time' => $posted_time
    ];
}, $bounties);

// Transform profiles to match profile-card.php expected format
$transformedProfiles = array_map(function($profile) {
    // Convert skills array to simple strings
    $skills = array_map(function($skill) {
        return $skill['name'];
    }, $profile['skills'] ?? []);

    return [
        'id' => $profile['profile_id'],
        'profile_db_id' => $profile['id'],
        'name' => $profile['user_name'] ?? 'Anonymous',
        'verified' => true,
        'rating' => 4.5,
        'location' => 'Remote',
        'remote' => true,
        'bio' => $profile['bio'] ?? '',
        'skills' => $skills,
        'price' => (int)$profile['hourly_rate'] ?? 0
    ];
}, $profiles);

$totalResults = count($bounties) + count($profiles);
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
    <title>search results - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/browse.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <section class="page-header">
                <div class="page-title-wrapper">
                    <h1 class="page-title">search results</h1>
                </div>
                <?php if (!empty($searchKey)): ?>
                    <p class="page-subtitle">results for: "<?php echo htmlspecialchars($searchKey); ?>"</p>
                <?php else: ?>
                    <p class="page-subtitle">enter a search term to find bounties and profiles</p>
                <?php endif; ?>
            </section>

            <section class="filters-section" style="margin-bottom: 48px; margin-top: 32px;">
                <form method="get" action="search" class="search-form" style="display: flex; gap: 12px; max-width: 600px;">
                    <input
                        type="text"
                        name="key"
                        class="filter-input"
                        placeholder="search by skill, keyword, or name..."
                        value="<?php echo htmlspecialchars($searchKey); ?>"
                        style="flex: 1;"
                    >
                    <button type="submit" class="btn-search" style="width: auto; padding: 12px 32px;">search</button>
                </form>
            </section>

            <?php if (!empty($searchKey)): ?>
                <?php if ($totalResults === 0): ?>
                    <div class="empty-state" style="text-align: center; padding: 80px 20px; color: #71717a;">
                        <p style="font-size: 1.25rem; margin-bottom: 12px; color: #e5e5e5;">no results found for "<?php echo htmlspecialchars($searchKey); ?>"</p>
                        <p style="font-size: 1rem; color: #9ca3af;">try searching with different keywords or skills</p>
                    </div>
                <?php else: ?>
                    <?php if (!empty($tasks)): ?>
                        <section class="tasks-section" style="margin-bottom: 64px;">
                            <h2 style="font-family: 'Silkscreen', monospace; font-size: 1.5rem; font-weight: 700; color: #ffffff; margin-bottom: 24px; letter-spacing: 0.02em;">
                                bounties <span style="color: #9ca3af; font-size: 1.125rem; font-weight: 400;">(<?php echo count($tasks); ?>)</span>
                            </h2>
                            <div class="tasks-list">
                                <?php foreach ($tasks as $task): ?>
                                    <?php include 'partials/task-card.php'; ?>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <?php if (!empty($transformedProfiles)): ?>
                        <section class="profiles-section" style="margin-bottom: 64px;">
                            <h2 style="font-family: 'Silkscreen', monospace; font-size: 1.5rem; font-weight: 700; color: #ffffff; margin-bottom: 24px; letter-spacing: 0.02em;">
                                profiles <span style="color: #9ca3af; font-size: 1.125rem; font-weight: 400;">(<?php echo count($transformedProfiles); ?>)</span>
                            </h2>
                            <div class="profiles-grid">
                                <?php foreach ($transformedProfiles as $profile): ?>
                                    <?php include 'partials/profile-card.php'; ?>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'partials/footer.php'; ?>
    <script src="/assets/js/app.js"></script>
</body>
</html>
