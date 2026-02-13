<?php
require_once __DIR__ . '/config/session.php';
require_once 'config.php';
require_once 'classes/Database.php';
require_once 'classes/Bounty.php';
require_once 'classes/Category.php';

// Fetch featured bounties (sorted by vote count)
$featuredBounties = [];
try {
    $db = Database::getInstance();
    $sql = "SELECT b.*,
                   (SELECT COUNT(*) FROM votes v WHERE v.bounty_id = b.id) as vote_count
            FROM bounties b
            WHERE b.status = 'open'
            ORDER BY vote_count DESC, b.created_at DESC
            LIMIT 6";
    $featuredBounties = $db->query($sql, []);
} catch (Exception $e) {
    error_log("Error fetching featured bounties: " . $e->getMessage());
    $featuredBounties = [];
}

// Fetch popular categories with skill counts
$popularCategories = [];
try {
    $db = Database::getInstance();
    $sql = "SELECT c.id, c.name, c.description,
                   (SELECT COUNT(*) FROM skills WHERE category_id = c.id) as skill_count
            FROM categories c
            ORDER BY skill_count DESC
            LIMIT 6";
    $popularCategories = $db->query($sql, []);
} catch (Exception $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    $popularCategories = [];
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
    <title><?php echo APP_NAME; ?> - Post Tasks, Get It Done by Pros</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/home.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <main class="main-content">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container">
                <div class="hero-content">
                    <h1 class="hero-title">post tasks, get it done by pros</h1>
                    <p class="hero-subtitle">connect with skilled professionals to get your work done</p>
                    <div class="hero-actions">
                        <a href="<?php echo url('hire'); ?>" class="btn-hero-primary">post a bounty</a>
                        <a href="<?php echo url('browse'); ?>" class="btn-hero-secondary">browse talent</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Bounties Section -->
        <section class="featured-bounties-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">featured bounties</h2>
                    <a href="<?php echo url('bounties'); ?>" class="section-link">view all</a>
                </div>
                <div class="placeholder-grid">
                    <?php if (count($featuredBounties) === 0): ?>
                        <div class="empty-state">
                            <p class="empty-state-text">no bounties posted yet</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($featuredBounties as $bounty): ?>
                            <a href="<?php echo url('bounty', ['id' => $bounty['id']]); ?>" class="bounty-card">
                                <h3 class="bounty-card-title"><?php echo htmlspecialchars($bounty['title']); ?></h3>
                                <p class="bounty-card-description">
                                    <?php
                                    $description = htmlspecialchars($bounty['description']);
                                    echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                                    ?>
                                </p>
                                <div class="bounty-card-meta">
                                    <span class="bounty-card-budget">
                                        $<?php echo number_format($bounty['budget_min']); ?> - $<?php echo number_format($bounty['budget_max']); ?>
                                    </span>
                                    <?php if (!empty($bounty['deadline'])): ?>
                                        <span class="bounty-card-deadline">
                                            due: <?php echo date('M j, Y', strtotime($bounty['deadline'])); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="bounty-card-deadline">no deadline</span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="how-it-works-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">how it works</h2>
                </div>
                <div class="steps-grid">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h3 class="step-title">post your task</h3>
                        <p class="step-description">describe what you need done and set your budget</p>
                    </div>
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h3 class="step-title">review applications</h3>
                        <p class="step-description">skilled professionals apply to work on your task</p>
                    </div>
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h3 class="step-title">get it done</h3>
                        <p class="step-description">collaborate with your chosen professional and complete the task</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Popular Skills Section -->
        <section class="popular-skills-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">popular skills</h2>
                </div>
                <div class="skills-grid">
                    <?php if (count($popularCategories) === 0): ?>
                        <div class="empty-state">
                            <p class="empty-state-text">no categories available</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($popularCategories as $category): ?>
                            <a href="<?php echo url('browse', ['category' => $category['name']]); ?>" class="skill-card">
                                <div class="skill-icon-placeholder"></div>
                                <h3 class="skill-name"><?php echo htmlspecialchars(strtolower($category['name'])); ?></h3>
                                <p class="skill-count">
                                    <?php echo (int)$category['skill_count']; ?> skill<?php echo (int)$category['skill_count'] !== 1 ? 's' : ''; ?> available
                                </p>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <script src="/assets/js/app.js"></script>
    <?php include 'partials/footer.php'; ?>
</body>
</html>
