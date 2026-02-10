<?php
require_once __DIR__ . '/config/session.php';
require_once 'config.php';
require_once 'controllers/ProfileController.php';

// Initialize controller
$profileController = new ProfileController();

// Build filters from GET parameters (tabs/filters would be implemented here)
$filters = [];
// Default to available profiles only
$filters['available'] = 1;

// Fetch profiles from database
try {
    $dbProfiles = $profileController->index($filters);

    // Transform profiles to match profile-card.php expected format
    $profiles = array_map(function($profile) {
        // Convert skills array to simple strings
        $skills = array_map(function($skill) {
            return $skill['name'];
        }, $profile['skills'] ?? []);

        return [
            'id' => $profile['profile_id'], // e.g., 'P7A3B2C'
            'name' => $profile['user_name'] ?? 'Anonymous',
            'verified' => true, // TODO: Add verification system
            'rating' => 4.5, // TODO: Add rating system
            'location' => 'Remote', // TODO: Add location field to profiles table
            'remote' => true, // TODO: Add remote field to profiles table
            'bio' => $profile['bio'] ?? '',
            'skills' => $skills,
            'price' => (int)$profile['hourly_rate'] ?? 0
        ];
    }, $dbProfiles);

} catch (Exception $e) {
    error_log("Error fetching profiles: " . $e->getMessage());
    $profiles = [];
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
    <title>Browse Humans - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/browse.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <section class="page-header">
                <div class="page-title-wrapper">
                    <h1 class="page-title">browse humans</h1>
                </div>
                <p class="page-subtitle">find freelance workers for your agent</p>
            </section>

            <section class="filters-tabs">
                <div class="tabs">
                    <button class="tab active" data-filter="all">all</button>
                    <button class="tab" data-filter="tech">tech</button>
                    <button class="tab" data-filter="woman">woman</button>
                    <button class="tab" data-filter="other">other</button>
                </div>
            </section>

            <section class="profiles-section">
                <div class="profiles-grid">
                    <?php if (empty($profiles)): ?>
                        <div class="empty-state" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #71717a;">
                            <p style="font-size: 1.125rem; margin-bottom: 8px;">no profiles found</p>
                            <p style="font-size: 0.9375rem;">check back later for available humans</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($profiles as $profile): ?>
                            <?php include 'partials/profile-card.php'; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

    <?php include 'partials/footer.php'; ?>
</body>
</html>
