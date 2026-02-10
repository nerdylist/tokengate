<?php
require_once __DIR__ . '/config/session.php';
require_once 'config.php';
require_once 'controllers/BountyController.php';
require_once 'classes/Auth.php';

// Get bounty ID from query string
$bountyId = $_GET['id'] ?? null;

if (!$bountyId) {
    header('Location: ' . url('index'));
    exit;
}

// Initialize controller
$bountyController = new BountyController();

// Fetch bounty details
try {
    $bounty = $bountyController->show($bountyId);

    if (!$bounty) {
        header('Location: ' . url('index'));
        exit;
    }

    // TODO: Implement view counter when views column is added to bounties table

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

    // Check if user is logged in and has a profile
    $isLoggedIn = Auth::check();
    $hasProfile = false;
    if ($isLoggedIn) {
        require_once 'controllers/ProfileController.php';
        $profileController = new ProfileController();
        $userProfile = $profileController->getUserProfile(Auth::id());
        $hasProfile = !empty($userProfile);
    }

    // Format budget display
    if (!empty($bounty['budget_min']) && !empty($bounty['budget_max'])) {
        $budgetDisplay = '$' . number_format($bounty['budget_min']) . ' - $' . number_format($bounty['budget_max']) . ' USD';
    } elseif (!empty($bounty['budget_min'])) {
        $budgetDisplay = '$' . number_format($bounty['budget_min']) . ' fixed USD';
    } elseif (!empty($bounty['budget_max'])) {
        $budgetDisplay = '$' . number_format($bounty['budget_max']) . ' fixed USD';
    } else {
        $budgetDisplay = 'Budget not specified';
    }

    // Format deadline
    $deadlineDisplay = 'No deadline';
    if (!empty($bounty['deadline'])) {
        $deadline = new DateTime($bounty['deadline']);
        $deadlineDisplay = $deadline->format('D, M j, Y');
    }

} catch (Exception $e) {
    error_log("Error fetching bounty details: " . $e->getMessage());
    header('Location: ' . url('index'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/token/icon/up-gold.png">
    <link rel="apple-touch-icon" href="assets/img/token/icon/up-gold.png">
    <meta property="og:title" content="<?php echo APP_NAME; ?>">
    <meta property="og:description" content="Post tasks, humans apply">
    <meta property="og:image" content="https://redot.test/assets/img/token/icon/up-gold.png">
    <meta property="og:url" content="https://redot.test">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?php echo APP_NAME; ?>">
    <meta name="twitter:description" content="Post tasks, humans apply">
    <meta name="twitter:image" content="https://redot.test/assets/img/token/icon/up-gold.png">
    <title>Task Detail - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/detail.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="detail-wrapper">
                <!-- Left Column: Main Content -->
                <div class="detail-content">
                    <!-- Back Link -->
                    <a href="<?php echo url('index'); ?>" class="back-link">← back to bounties</a>

                    <!-- Header with Badges -->
                    <div class="detail-header">
                        <div class="detail-badges">
                            <span class="badge badge-category"><?php echo strtoupper(htmlspecialchars($bounty['category_name'] ?? 'OTHER')); ?></span>
                            <span class="badge badge-status-<?php echo htmlspecialchars($bounty['status'] ?? 'open'); ?>"><?php echo htmlspecialchars($bounty['status'] ?? 'open'); ?></span>
                        </div>
                    </div>

                    <!-- Task Title -->
                    <h1 class="detail-title"><?php echo htmlspecialchars($bounty['title']); ?></h1>

                    <!-- Posted By Section -->
                    <div class="posted-by">
                        <div class="avatar"></div>
                        <span class="poster-name"><?php echo htmlspecialchars($bounty['user_name'] ?? 'Anonymous'); ?></span>
                        <span class="badge badge-human">human</span>
                        <span class="timestamp">posted <?php echo $posted_time; ?></span>
                    </div>

                    <!-- Description Section -->
                    <section class="detail-section">
                        <h2 class="section-heading">DESCRIPTION</h2>
                        <p class="description-text"><?php echo nl2br(htmlspecialchars($bounty['description'])); ?></p>
                    </section>

                    <!-- Skills Section -->
                    <?php if (!empty($bounty['skills'])): ?>
                    <section class="detail-section">
                        <h2 class="section-heading">REQUIRED SKILLS</h2>
                        <div class="task-tags">
                            <?php foreach ($bounty['skills'] as $skill): ?>
                                <span class="tag"><?php echo htmlspecialchars($skill['name']); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endif; ?>
                </div>

                <!-- Right Column: Sidebar -->
                <aside class="detail-sidebar">
                    <!-- Price Card -->
                    <div class="sidebar-card price-card">
                        <div class="card-label">Price</div>
                        <div class="card-price"><?php echo $budgetDisplay; ?></div>
                        <div class="card-meta">
                            <div class="card-deadline"><?php echo $deadlineDisplay; ?></div>
                        </div>
                    </div>

                    <!-- Location Card -->
                    <div class="sidebar-card location-card">
                        <div class="card-label">Location</div>
                        <div class="card-location">
                            <svg class="location-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <span>Remote OK</span>
                        </div>
                    </div>

                    <!-- Stats Card -->
                    <div class="sidebar-card stats-card">
                        <div class="stat-item">
                            <div class="stat-label">Applications</div>
                            <div class="stat-value"><?php echo (int)$bounty['application_count']; ?></div>
                        </div>
                        <!-- TODO: Add views counter when column is added to database -->
                    </div>

                    <!-- Apply Section -->
                    <div class="apply-section">
                        <?php if (!$isLoggedIn): ?>
                            <p class="apply-text">Sign in to apply for this bounty</p>
                            <a href="<?php echo url('connect'); ?>" class="btn-apply">login to apply</a>
                        <?php elseif (!$hasProfile): ?>
                            <p class="apply-text">Create a profile to apply</p>
                            <a href="<?php echo url('profile'); ?>" class="btn-apply">create profile</a>
                        <?php else: ?>
                            <p class="apply-text">Ready to apply?</p>
                            <button class="btn-apply" id="apply-btn">apply now</button>
                        <?php endif; ?>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <script src="assets/js/app.js"></script>
</body>
</html>
