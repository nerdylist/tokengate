<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Profile.php';
require_once __DIR__ . '/classes/Skill.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Auth.php';

// Get profile_id from URL
$profile_id = $_GET['id'] ?? null;

if (!$profile_id) {
    header('Location: ' . url('browse'));
    exit;
}

// Fetch profile data
$profileModel = new Profile();
$profile = $profileModel->where('profile_id', '=', $profile_id)->first();

if (!$profile) {
    // Profile not found, redirect to browse
    header('Location: ' . url('browse'));
    exit;
}

// Get user details
$userModel = new User();
$user = $userModel->find($profile['user_id']);

if (!$user) {
    // User not found, redirect to browse
    header('Location: ' . url('browse'));
    exit;
}

// Check if viewing own profile
$isOwnProfile = Auth::check() && Auth::id() == $profile['user_id'];

// Get skills
$skills = $profileModel->skills($profile['id']);

// Get guild memberships
$guilds = $profileModel->guilds($profile['id']);
$primaryGuild = $profileModel->primaryGuild($profile['id']);

// Get current status from profile_statuses table
$currentStatus = $profileModel->getStatus($profile['id']);
if (!$currentStatus) {
    // Fallback to available column if status_id is null
    $currentStatus = [
        'id' => $profile['available'] ? 1 : 2,
        'slug' => $profile['available'] ? 'available' : 'busy',
        'name' => $profile['available'] ? 'Available' : 'Busy',
        'color' => $profile['available'] ? '#10b981' : '#f59e0b'
    ];
}

// Get application count
$applications = $profileModel->applications($profile['id']);
$applicationCount = count($applications);

// Format availability status
$availabilityStatus = strtolower($currentStatus['name']);
$availabilityClass = 'status-' . $currentStatus['slug'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/token/icon/up-gold.png">
    <link rel="apple-touch-icon" href="assets/img/token/icon/up-gold.png">
    <meta property="og:title" content="<?php echo htmlspecialchars($user['name']); ?> - <?php echo APP_NAME; ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars(substr($profile['bio'], 0, 155)); ?>">
    <meta property="og:image" content="https://redot.test/assets/img/token/icon/up-gold.png">
    <meta property="og:type" content="profile">
    <title><?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($profile_id); ?>) - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/profile.css">
    <link rel="stylesheet" href="assets/css/guilds.css">
    <link rel="stylesheet" href="assets/css/profile-skills.css">
    <?php if ($isOwnProfile): ?>
    <link rel="stylesheet" href="assets/css/profile-edit.css">
    <?php endif; ?>
    <script src="assets/js/profile-skills.js"></script>
    <?php if ($isOwnProfile): ?>
    <script src="assets/js/profile-edit.js" defer></script>
    <?php endif; ?>
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <main class="profile-page">
        <div class="container">
            <!-- Profile Hero Section -->
            <section class="profile-hero">
                <div class="profile-hero-content">
                    <div class="profile-avatar-large" <?php if ($isOwnProfile) echo 'id="avatar-container"'; ?>>
                        <?php if (!empty($profile['avatar_url'])): ?>
                            <img src="<?php echo htmlspecialchars($profile['avatar_url']); ?>" alt="<?php echo htmlspecialchars($user['name']); ?>" class="avatar-image-large">
                        <?php else: ?>
                            <div class="avatar-circle-large">
                                <?php echo strtoupper(substr(htmlspecialchars($user['name']), 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($isOwnProfile): ?>
                            <button class="avatar-edit-btn" onclick="triggerAvatarUpload()" aria-label="Edit avatar">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </button>
                            <input type="file" id="avatar-upload" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" style="display: none;">
                        <?php endif; ?>
                    </div>
                    <div class="profile-hero-info">
                        <div class="profile-badge"><?php echo htmlspecialchars($profile_id); ?></div>
                        <?php if ($primaryGuild): ?>
                        <div class="guild-badge-primary">
                            <span class="guild-icon">⚔️</span>
                            <span class="guild-name"><?php echo htmlspecialchars($primaryGuild['name']); ?></span>
                            <span class="guild-rank" style="color: <?php echo $primaryGuild['rank']['color']; ?>">
                                <?php echo htmlspecialchars($primaryGuild['rank']['name']); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        <h1 class="profile-name-large"><?php echo htmlspecialchars($user['name']); ?></h1>
                        <div class="profile-meta">
                            <div class="profile-rate-large editable-field" id="rate-display" data-original="<?php echo $profile['hourly_rate']; ?>">
                                $<?php echo number_format($profile['hourly_rate']); ?>/hr
                                <?php if ($isOwnProfile): ?>
                                    <button class="edit-btn" onclick="editField('rate')" aria-label="Edit rate">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="profile-status-wrapper editable-field" id="status-display" data-original="<?php echo $currentStatus['id']; ?>">
                                <span class="profile-status <?php echo $availabilityClass; ?>" style="color: <?php echo htmlspecialchars($currentStatus['color']); ?>"><?php echo $availabilityStatus; ?></span>
                                <?php if ($isOwnProfile): ?>
                                    <button class="edit-btn" onclick="editField('status')" aria-label="Edit status">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Profile Content Grid -->
            <div class="profile-content-grid">
                <!-- Left Column: Bio & Skills -->
                <div class="profile-main-content">
                    <!-- About Section -->
                    <section class="profile-section profile-about">
                        <h2 class="section-title">
                            about
                            <?php if ($isOwnProfile): ?>
                                <button class="edit-btn" onclick="editField('bio')" aria-label="Edit bio">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </button>
                            <?php endif; ?>
                        </h2>
                        <div class="section-content">
                            <div class="editable-field" id="bio-display" data-original="<?php echo htmlspecialchars($profile['bio']); ?>">
                                <p class="profile-bio-text"><?php echo nl2br(htmlspecialchars($profile['bio'])); ?></p>
                            </div>
                        </div>
                    </section>

                    <!-- Skills Section -->
                    <?php if (!empty($skills)): ?>
                    <section class="profile-section profile-skills-section">
                        <h2 class="section-title">
                            skills
                            <?php if ($isOwnProfile): ?>
                                <button class="edit-skills-btn" onclick="openSkillsModal()" aria-label="Edit skills">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </button>
                            <?php endif; ?>
                        </h2>
                        <div class="section-content">
                            <div class="skills-grid-profile">
                                <?php foreach ($skills as $skill): ?>
                                    <?php
                                    $skillStatus = $skill['status'] ?? 'approved';
                                    $statusClass = $skillStatus === 'pending' ? 'skill-pending' : 'skill-approved';
                                    ?>
                                    <a href="<?php echo url('search.php?key=' . urlencode($skill['slug'])); ?>" class="skill-badge-profile <?php echo $statusClass; ?>">
                                        <span class="skill-name">#<?php echo htmlspecialchars($skill['name']); ?></span>
                                        <span class="skill-proficiency"><?php echo htmlspecialchars($skill['proficiency_level']); ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- Guild Memberships Section -->
                    <?php if (!empty($guilds)): ?>
                    <section class="profile-section profile-guilds-section">
                        <h2 class="section-title">guild memberships</h2>
                        <div class="section-content">
                            <div class="guilds-list">
                                <?php foreach ($guilds as $guild): ?>
                                    <div class="guild-card <?php echo $guild['is_primary'] ? 'guild-primary' : ''; ?>">
                                        <div class="guild-card-header">
                                            <div class="guild-card-title">
                                                <span class="guild-icon">⚔️</span>
                                                <h3><?php echo htmlspecialchars($guild['name']); ?></h3>
                                                <?php if ($guild['is_primary']): ?>
                                                    <span class="badge-primary">primary</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="guild-card-rank">
                                                <?php
                                                $rank = $profileModel->calculateRank($guild['total_xp']);
                                                ?>
                                                <span class="rank-badge" style="background: <?php echo $rank['color']; ?>20; color: <?php echo $rank['color']; ?>; border-color: <?php echo $rank['color']; ?>">
                                                    <?php echo htmlspecialchars($rank['name']); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="guild-card-xp">
                                            <div class="xp-info">
                                                <span class="xp-current"><?php echo number_format($guild['total_xp']); ?> XP</span>
                                                <span class="xp-next">Next: <?php echo htmlspecialchars($rank['next_rank']); ?> (<?php echo number_format($rank['next_rank_xp']); ?> XP)</span>
                                            </div>
                                            <div class="xp-progress-bar">
                                                <div class="xp-progress-fill" style="width: <?php echo $rank['progress_percent']; ?>%; background: <?php echo $rank['color']; ?>"></div>
                                            </div>
                                        </div>
                                        <div class="guild-card-skills">
                                            <?php foreach ($guild['skills'] as $skill): ?>
                                                <span class="guild-skill-tag">
                                                    <?php echo htmlspecialchars($skill['name']); ?>
                                                    <span class="skill-xp"><?php echo number_format($skill['xp']); ?> XP</span>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>
                </div>

                <!-- Right Column: Stats & Actions -->
                <aside class="profile-sidebar">
                    <!-- Stats Card -->
                    <div class="stats-card">
                        <h3 class="stats-title">statistics</h3>
                        <div class="stats-list">
                            <div class="stat-item">
                                <span class="stat-label">total applications</span>
                                <span class="stat-value"><?php echo $applicationCount; ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">hourly rate</span>
                                <span class="stat-value" id="rate-stat">$<?php echo number_format($profile['hourly_rate']); ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">availability</span>
                                <span class="stat-value <?php echo $availabilityClass; ?>" id="status-stat"><?php echo $availabilityStatus; ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">member since</span>
                                <span class="stat-value"><?php echo date('M Y', strtotime($profile['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Card -->
                    <div class="action-card">
                        <h3 class="action-title">hire this human</h3>
                        <p class="action-description">browse bounties and connect with <?php echo htmlspecialchars($user['name']); ?></p>
                        <a href="<?php echo url('bounties'); ?>" class="btn btn-primary btn-full">view bounties</a>
                        <a href="<?php echo url('browse'); ?>" class="btn btn-secondary btn-full">browse more humans</a>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <!-- Skills Management Modal -->
    <?php if ($isOwnProfile): ?>
    <div id="skillsModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h2 class="modal-title">manage skills</h2>
                <button class="modal-close" onclick="closeSkillsModal()" aria-label="Close modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="skill-search-section">
                    <input type="text" id="skillSearchInput" class="skill-search-input" placeholder="search skills..." autocomplete="off">
                    <div id="skillAutocomplete" class="autocomplete-suggestions"></div>
                </div>
                <div class="current-skills-section">
                    <h3 class="section-subtitle">your skills</h3>
                    <div id="currentSkillsList" class="current-skills-list"></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php include 'partials/footer.php'; ?>

    <script>
        const PROFILE_ID = <?php echo json_encode($profile['id']); ?>;
    </script>
</body>
</html>
