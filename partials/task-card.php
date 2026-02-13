<?php
require_once __DIR__ . '/../config.php';

/**
 * Task Card Partial
 *
 * Expected variables:
 * - $task (array): Task data containing:
 *   - id: Task ID
 *   - votes: Number of votes
 *   - user_vote: User's vote status (1, -1, or 0)
 *   - category: Task category
 *   - due_days: Days until due
 *   - spots_filled: Number of spots filled
 *   - spots_total: Total spots available
 *   - price: Task price
 *   - title: Task title
 *   - description: Task description
 *   - tags: Array of tags
 *   - location: Location (e.g., 'remote')
 *   - duration: Duration (e.g., '3-5 days')
 *   - applications: Number of applications
 *   - posted_time: Time posted (e.g., '3 hours ago')
 */

// Determine user vote status
$userVote = $task['user_vote'] ?? 0;
$upvoteClass = ($userVote === 1) ? ' voted' : '';
$downvoteClass = ($userVote === -1) ? ' voted' : '';
$upvoteIcon = ($userVote === 1) ? '/assets/img/token/icon/up-gold.png' : '/assets/img/token/icon/up-silver.png';
$downvoteIcon = ($userVote === -1) ? '/assets/img/token/icon/down-gold.png' : '/assets/img/token/icon/down-silver.png';
?>
<article class="task-card">
    <div class="task-vote">
        <button class="vote-btn vote-up<?php echo $upvoteClass; ?>" aria-label="Upvote" data-index="<?php echo htmlspecialchars($task['id']); ?>">
            <img src="<?php echo $upvoteIcon; ?>" alt="Upvote" class="vote-icon vote-up-icon" data-default="/assets/img/token/icon/up-silver.png" data-hover="/assets/img/token/icon/up-green.png" data-selected="/assets/img/token/icon/up-gold.png">
        </button>
        <span class="vote-count"><?php echo htmlspecialchars($task['votes']); ?></span>
        <button class="vote-btn vote-down<?php echo $downvoteClass; ?>" aria-label="Downvote" data-index="<?php echo htmlspecialchars($task['id']); ?>">
            <img src="<?php echo $downvoteIcon; ?>" alt="Downvote" class="vote-icon vote-down-icon" data-default="/assets/img/token/icon/down-silver.png" data-hover="/assets/img/token/icon/down-red.png" data-selected="/assets/img/token/icon/down-gold.png">
        </button>
    </div>
    <div class="task-content">
        <div class="task-header">
            <div class="task-badges">
                <span class="badge badge-category"><?php echo strtoupper(htmlspecialchars($task['category'])); ?></span>
                <span class="badge badge-due">due <?php echo htmlspecialchars($task['due_days']); ?> days</span>
                <span class="badge badge-spots"><?php echo htmlspecialchars($task['spots_filled']); ?>/<?php echo htmlspecialchars($task['spots_total']); ?> spots</span>
            </div>
            <div class="task-price">
                <span class="price">$<?php echo number_format($task['price']); ?></span>
            </div>
        </div>
        <h2 class="task-title">
            <a href="<?php echo url('bounty', ['id' => $task['id']]); ?>"><?php echo htmlspecialchars($task['title']); ?></a>
        </h2>
        <p class="task-description">
            <?php echo htmlspecialchars($task['description']); ?>
        </p>
        <div class="task-tags">
            <?php foreach ($task['tags'] as $tag): ?>
                <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
            <?php endforeach; ?>
        </div>
        <div class="task-meta">
            <span class="meta-item">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                    <path d="M7 0C3.13 0 0 3.13 0 7C0 10.87 3.13 14 7 14C10.87 14 14 10.87 14 7C14 3.13 10.87 0 7 0ZM7 12.6C3.9 12.6 1.4 10.1 1.4 7C1.4 3.9 3.9 1.4 7 1.4C10.1 1.4 12.6 3.9 12.6 7C12.6 10.1 10.1 12.6 7 12.6Z" fill="currentColor"/>
                    <path d="M7.35 3.5H6.3V7.7L9.8 9.73L10.325 8.89L7.35 7.175V3.5Z" fill="currentColor"/>
                </svg>
                <?php echo htmlspecialchars($task['location']); ?>
            </span>
            <span class="meta-item">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                    <path d="M11.667 2.333H10.5V1.167C10.5 0.523 9.977 0 9.333 0C8.689 0 8.167 0.523 8.167 1.167V2.333H5.833V1.167C5.833 0.523 5.311 0 4.667 0C4.023 0 3.5 0.523 3.5 1.167V2.333H2.333C1.050 2.333 0.012 3.383 0.012 4.667L0 11.667C0 12.950 1.050 14 2.333 14H11.667C12.950 14 14 12.950 14 11.667V4.667C14 3.383 12.950 2.333 11.667 2.333ZM11.667 11.667H2.333V5.833H11.667V11.667Z" fill="currentColor"/>
                </svg>
                <?php echo htmlspecialchars($task['duration']); ?>
            </span>
            <span class="meta-item">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                    <path d="M10 6C11.1046 6 12 5.10457 12 4C12 2.89543 11.1046 2 10 2C8.89543 2 8 2.89543 8 4C8 5.10457 8.89543 6 10 6Z" fill="currentColor"/>
                    <path d="M4 6C5.10457 6 6 5.10457 6 4C6 2.89543 5.10457 2 4 2C2.89543 2 2 2.89543 2 4C2 5.10457 2.89543 6 4 6Z" fill="currentColor"/>
                    <path d="M4 7C2.34 7 0 7.84 0 9.5V11H8V9.5C8 7.84 5.66 7 4 7Z" fill="currentColor"/>
                    <path d="M10 7C9.71 7 9.38 7.02 9.03 7.05C9.05 7.06 9.06 7.08 9.07 7.09C9.86 7.78 10.5 8.7 10.5 9.5V11H14V9.5C14 7.84 11.66 7 10 7Z" fill="currentColor"/>
                </svg>
                <?php echo htmlspecialchars($task['applications']); ?> applications
            </span>
            <span class="meta-item meta-time"><?php echo htmlspecialchars($task['posted_time']); ?></span>
        </div>
    </div>
</article>
