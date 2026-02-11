<?php
require_once __DIR__ . '/config/session.php';
require_once 'config.php';
require_once 'classes/Auth.php';
require_once 'controllers/ApplicationController.php';

// Check authentication
if (!Auth::check()) {
    header('Location: connect.php');
    exit;
}

// Get bounty ID from query string
$bountyId = $_GET['bounty_id'] ?? null;

if (!$bountyId) {
    $error = "Bounty ID is required";
    $applications = [];
    $bountyTitle = "Review Applicants";
} else {
    // Initialize controller and fetch applications
    $controller = new ApplicationController();
    $db = Database::getInstance();

    try {
        // Get bounty details
        $bounty = $db->queryOne("SELECT id, title, user_id FROM bounties WHERE id = ?", [$bountyId]);

        if (!$bounty) {
            $error = "Bounty not found";
            $applications = [];
            $bountyTitle = "Review Applicants";
        } else {
            $bountyTitle = $bounty['title'];

            // Fetch applications (this will verify ownership)
            $applications = $controller->getForBounty($bountyId);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        $applications = [];
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
    <title>Review Applicants - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/review-applicants.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <section class="page-header">
                <div class="page-header-top">
                    <div class="page-title-wrapper">
                        <h1 class="page-title">review applicants</h1>
                    </div>
                    <a href="<?php echo url('bounties'); ?>" class="btn-back">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        back to bounties
                    </a>
                </div>
                <p class="page-subtitle"><?php echo htmlspecialchars($bountyTitle); ?></p>
            </section>

            <?php if (isset($error)): ?>
                <div class="error-message" style="background-color: #1a1a1a; border: 1px solid #ef4444; padding: 20px; border-radius: 8px; margin: 20px 0; color: #ef4444;">
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php elseif (empty($applications)): ?>
                <div class="empty-state" style="text-align: center; padding: 60px 20px; color: #71717a;">
                    <p style="font-size: 1.125rem; margin-bottom: 8px;">no applications yet</p>
                    <p style="font-size: 0.9375rem;">check back later for applicants</p>
                </div>
            <?php else: ?>
                <section class="applicants-container">
                    <?php foreach ($applications as $application): ?>
                        <div class="applicant-card" data-application-id="<?php echo $application['id']; ?>">
                            <div class="applicant-header">
                                <div class="applicant-avatar">
                                    <?php if (!empty($application['avatar_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($application['avatar_url']); ?>" alt="<?php echo htmlspecialchars($application['applicant_name']); ?>">
                                    <?php else: ?>
                                        <div class="avatar-placeholder">
                                            <?php echo strtoupper(substr($application['applicant_name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="applicant-info">
                                    <h3 class="applicant-name"><?php echo htmlspecialchars($application['applicant_name']); ?></h3>
                                    <p class="applicant-profile-id"><?php echo htmlspecialchars($application['profile_id']); ?></p>
                                    <a href="profile.php?id=<?php echo $application['profile_id']; ?>" class="view-profile-link">view profile →</a>
                                </div>
                                <div class="applicant-status">
                                    <span class="status-badge status-<?php echo $application['status']; ?>">
                                        <?php echo $application['status']; ?>
                                    </span>
                                </div>
                            </div>

                            <?php if (!empty($application['skills'])): ?>
                                <div class="applicant-skills">
                                    <?php foreach ($application['skills'] as $skill): ?>
                                        <span class="skill-tag"><?php echo htmlspecialchars($skill['name']); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="applicant-rates">
                                <div class="rate-item">
                                    <span class="rate-label">hourly rate:</span>
                                    <span class="rate-value">$<?php echo number_format($application['hourly_rate'], 2); ?>/hr</span>
                                </div>
                                <?php if ($application['proposed_rate']): ?>
                                    <div class="rate-item">
                                        <span class="rate-label">proposed rate:</span>
                                        <span class="rate-value">$<?php echo number_format($application['proposed_rate'], 2); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="applicant-cover-letter">
                                <h4>cover letter</h4>
                                <p><?php echo nl2br(htmlspecialchars($application['cover_letter'])); ?></p>
                            </div>

                            <div class="applicant-meta">
                                <span class="applied-date">Applied <?php
                                    $createdDate = new DateTime($application['created_at']);
                                    $now = new DateTime();
                                    $diff = $now->diff($createdDate);
                                    if ($diff->days > 0) {
                                        echo $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
                                    } elseif ($diff->h > 0) {
                                        echo $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
                                    } else {
                                        echo $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
                                    }
                                ?></span>
                            </div>

                            <?php if ($application['status'] === 'pending'): ?>
                                <div class="applicant-actions">
                                    <button class="btn-accept" data-application-id="<?php echo $application['id']; ?>">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        accept
                                    </button>
                                    <button class="btn-decline" data-application-id="<?php echo $application['id']; ?>">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                        decline
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>
        </div>
    </main>

    <script src="/assets/js/review-applicants.js"></script>
    <?php include 'partials/footer.php'; ?>
</body>
</html>
