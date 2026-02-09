<?php
require_once __DIR__ . '/../config.php';

/**
 * Profile Card Partial
 *
 * Expected variables:
 * - $profile (array): Profile data containing:
 *   - id: Profile ID (e.g., 'KEA-2847')
 *   - name: Human's full name
 *   - verified: Boolean indicating verification status
 *   - rating: Float rating value (e.g., 4.9)
 *   - location: Geographic location string
 *   - remote: Boolean indicating remote availability
 *   - bio: Biography/description text
 *   - skills: Array of skill strings
 *   - price: Hourly rate as integer
 */
?>
<div class="profile-card">
    <div class="profile-header">
        <div class="profile-avatar">
            <div class="avatar-circle">
                <?php echo strtoupper(substr(htmlspecialchars($profile['name']), 0, 1)); ?>
            </div>
        </div>
        <div class="profile-info">
            <div class="profile-name">
                <span><?php echo htmlspecialchars($profile['name']); ?></span>
                <?php if ($profile['verified']): ?>
                    <svg class="verified-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                <?php endif; ?>
            </div>
            <div class="profile-rating">
                <svg class="star-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                </svg>
                <span><?php echo number_format($profile['rating'], 1); ?></span>
                <span class="profile-id"><?php echo htmlspecialchars($profile['id']); ?></span>
            </div>
        </div>
    </div>

    <div class="profile-location">
        <svg class="pin-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 13C13.6569 13 15 11.6569 15 10C15 8.34315 13.6569 7 12 7C10.3431 7 9 8.34315 9 10C9 11.6569 10.3431 13 12 13Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 22C12 22 20 16 20 10C20 5.58172 16.4183 2 12 2C7.58172 2 4 5.58172 4 10C4 16 12 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span><?php echo htmlspecialchars($profile['location']); ?></span>
    </div>

    <?php if ($profile['remote']): ?>
        <span class="badge-remote">remote</span>
    <?php endif; ?>

    <p class="profile-bio"><?php echo htmlspecialchars($profile['bio']); ?></p>

    <div class="profile-skills">
        <?php foreach ($profile['skills'] as $skill): ?>
            <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
        <?php endforeach; ?>
    </div>

    <div class="profile-footer">
        <div class="profile-price">$<?php echo number_format($profile['price']); ?>/hr</div>
        <button class="btn-rent">rent</button>
    </div>
</div>
