<?php
require_once __DIR__ . '/middleware/guild_member.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Guild.php';
require_once __DIR__ . '/classes/User.php';

// Get guild_id and thread_id from request
$guild_id = $_GET['id'] ?? null;
$thread_id = $_GET['thread_id'] ?? null;

// Fetch guild data
$guildModel = new Guild();
$guild = $guildModel->find($guild_id);

if (!$guild) {
    header('Location: ' . url('my-guilds'));
    exit;
}

// Get member count
$members = $guildModel->members($guild_id);
$memberCount = count($members);

// Fetch threads or thread detail
$db = Database::getInstance();

if ($thread_id) {
    // Thread detail view
    $sql = "SELECT gt.*, p.profile_id, u.name as author_name, p.avatar_url
            FROM guild_threads gt
            INNER JOIN profiles p ON gt.profile_id = p.id
            INNER JOIN users u ON p.user_id = u.id
            WHERE gt.id = ? AND gt.guild_id = ?";
    $thread = $db->queryOne($sql, [$thread_id, $guild_id]);

    if (!$thread) {
        header('Location: ' . url('guild', ['id' => $guild_id]));
        exit;
    }

    // Increment view count
    $db->query("UPDATE guild_threads SET view_count = view_count + 1 WHERE id = ?", [$thread_id]);

    // Get current user's profile ID for vote checking
    $currentProfileId = null;
    if (Auth::check()) {
        $currentUserProfile = $db->queryOne("SELECT id FROM profiles WHERE user_id = ?", [Auth::id()]);
        $currentProfileId = $currentUserProfile['id'] ?? null;
    }

    // Fetch comments with vote information
    if ($currentProfileId) {
        $sql = "SELECT gc.*, p.profile_id, u.name as author_name, p.avatar_url,
                       gcv.vote_type as user_vote
                FROM guild_comments gc
                INNER JOIN profiles p ON gc.profile_id = p.id
                INNER JOIN users u ON p.user_id = u.id
                LEFT JOIN guild_comment_votes gcv ON gc.id = gcv.comment_id AND gcv.profile_id = ?
                WHERE gc.thread_id = ?
                ORDER BY gc.votes DESC, gc.created_at ASC";
        $comments = $db->query($sql, [$currentProfileId, $thread_id]);
    } else {
        $sql = "SELECT gc.*, p.profile_id, u.name as author_name, p.avatar_url
                FROM guild_comments gc
                INNER JOIN profiles p ON gc.profile_id = p.id
                INNER JOIN users u ON p.user_id = u.id
                WHERE gc.thread_id = ?
                ORDER BY gc.votes DESC, gc.created_at ASC";
        $comments = $db->query($sql, [$thread_id]);
    }
} else {
    // Thread list view
    $sql = "SELECT gt.*, p.profile_id, u.name as author_name, p.avatar_url,
                   (SELECT COUNT(*) FROM guild_comments WHERE thread_id = gt.id) as reply_count,
                   (SELECT MAX(created_at) FROM guild_comments WHERE thread_id = gt.id) as last_activity
            FROM guild_threads gt
            INNER JOIN profiles p ON gt.profile_id = p.id
            INNER JOIN users u ON p.user_id = u.id
            WHERE gt.guild_id = ?
            ORDER BY gt.is_pinned DESC,
                     COALESCE(last_activity, gt.created_at) DESC";
    $threads = $db->query($sql, [$guild_id]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/assets/img/token/icon/up-gold.png">
    <link rel="apple-touch-icon" href="/assets/img/token/icon/up-gold.png">
    <meta property="og:title" content="<?php echo htmlspecialchars($guild['name']); ?> - <?php echo APP_NAME; ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($guild['description'] ?? 'Guild forum'); ?>">
    <meta property="og:image" content="https://redot.test/assets/img/token/icon/up-gold.png">
    <meta property="og:type" content="website">
    <title><?php echo htmlspecialchars($guild['name']); ?> Forum - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/guilds.css">
    <link rel="stylesheet" href="<?php echo asset('assets/css/guild.css'); ?>">
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <main class="guild-forum-page">
        <div class="container">
            <!-- Guild Header -->
            <section class="guild-header-section">
                <div class="guild-header-content">
                    <div class="guild-icon-display"><?php echo htmlspecialchars($guild['icon'] ?? '⚔️'); ?></div>
                    <div class="guild-header-info">
                        <h1><?php echo htmlspecialchars($guild['name']); ?></h1>
                        <div class="guild-header-meta">
                            <span><?php echo $memberCount; ?> members</span>
                            <span><?php echo ucfirst(htmlspecialchars($guild['type'])); ?> Guild</span>
                        </div>
                    </div>
                </div>
                <?php if (!empty($guild['description'])): ?>
                    <p class="guild-description-text"><?php echo htmlspecialchars($guild['description']); ?></p>
                <?php endif; ?>
            </section>

            <?php if ($thread_id): ?>
                <!-- Thread Detail View -->
                <a href="<?php echo url('guild', ['id' => $guild_id]); ?>" class="btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    back to guild hall
                </a>

                <div class="thread-content">
                    <section class="thread-detail-section">
                        <div class="thread-post">
                            <div style="display: flex; gap: 20px;">
                                <div class="post-author-column">
                                    <a href="<?php echo url('profile', ['id' => $thread['profile_id']]); ?>" class="author-avatar">
                                        <?php if (!empty($thread['avatar_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($thread['avatar_url']); ?>" alt="<?php echo htmlspecialchars($thread['author_name']); ?>">
                                        <?php else: ?>
                                            <?php echo strtoupper(substr(htmlspecialchars($thread['author_name']), 0, 1)); ?>
                                        <?php endif; ?>
                                    </a>
                                    <div class="author-username"><?php echo htmlspecialchars($thread['author_name']); ?></div>
                                </div>
                                <div class="post-content-column">
                                    <h2 class="thread-detail-title">
                                        <?php if ($thread['is_pinned']): ?>
                                            <span class="thread-pinned">📌 pinned</span>
                                        <?php endif; ?>
                                        <?php if ($thread['is_locked']): ?>
                                            <span class="thread-locked">🔒 locked</span>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($thread['title']); ?>
                                    </h2>
                                    <div class="post-content"><?php echo htmlspecialchars($thread['content']); ?></div>
                                    <div class="post-footer">
                                        <span class="post-timestamp"><?php echo date('M j, Y g:i A', strtotime($thread['created_at'])); ?></span>
                                        <span class="post-views"><?php echo number_format($thread['view_count']); ?> views</span>
                                    </div>
                                </div>
                                <div class="post-vote-column">
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Comments Section -->
                    <section class="comments-section">
                        <h3 class="comments-title">replies (<?php echo count($comments); ?>)</h3>
                        <?php if (!empty($comments)): ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment-item">
                                    <div class="post-author-column">
                                        <a href="<?php echo url('profile', ['id' => $comment['profile_id']]); ?>" class="author-avatar">
                                            <?php if (!empty($comment['avatar_url'])): ?>
                                                <img src="<?php echo htmlspecialchars($comment['avatar_url']); ?>" alt="<?php echo htmlspecialchars($comment['author_name']); ?>">
                                            <?php else: ?>
                                                <?php echo strtoupper(substr(htmlspecialchars($comment['author_name']), 0, 1)); ?>
                                            <?php endif; ?>
                                        </a>
                                        <div class="author-username"><?php echo htmlspecialchars($comment['author_name']); ?></div>
                                    </div>
                                    <div class="post-content-column">
                                        <div class="post-content"><?php echo htmlspecialchars($comment['content']); ?></div>
                                        <div class="post-footer">
                                            <span class="post-timestamp"><?php echo date('M j, Y g:i A', strtotime($comment['created_at'])); ?></span>
                                        </div>
                                    </div>
                                    <div class="post-vote-column">
                                        <div class="comment-vote">
                                            <?php
                                            $userVote = $comment['user_vote'] ?? null;
                                            $upVoted = ($userVote === 'up');
                                            $downVoted = ($userVote === 'down');
                                            ?>
                                            <button class="vote-btn vote-up <?php echo $upVoted ? 'voted' : ''; ?>" aria-label="Upvote" data-comment-id="<?php echo htmlspecialchars($comment['id']); ?>">
                                                <img src="/assets/img/token/icon/<?php echo $upVoted ? 'up-gold' : 'up-silver'; ?>.png" alt="Upvote" class="vote-icon vote-up-icon" data-default="/assets/img/token/icon/up-silver.png" data-hover="/assets/img/token/icon/up-green.png" data-selected="/assets/img/token/icon/up-gold.png">
                                            </button>
                                            <span class="vote-count"><?php echo htmlspecialchars($comment['votes'] ?? 0); ?></span>
                                            <button class="vote-btn vote-down <?php echo $downVoted ? 'voted' : ''; ?>" aria-label="Downvote" data-comment-id="<?php echo htmlspecialchars($comment['id']); ?>">
                                                <img src="/assets/img/token/icon/<?php echo $downVoted ? 'down-gold' : 'down-silver'; ?>.png" alt="Downvote" class="vote-icon vote-down-icon" data-default="/assets/img/token/icon/down-silver.png" data-hover="/assets/img/token/icon/down-red.png" data-selected="/assets/img/token/icon/down-gold.png">
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-comments">
                                <p>no replies yet. be the first to comment!</p>
                            </div>
                        <?php endif; ?>
                    </section>

                    <!-- Add Comment Form -->
                    <?php if (!$thread['is_locked']): ?>
                    <section class="comment-form-section">
                        <h3 class="comment-form-title">add a reply</h3>
                        <form id="commentForm">
                            <input type="hidden" name="thread_id" value="<?php echo $thread_id; ?>">
                            <input type="hidden" name="guild_id" value="<?php echo $guild_id; ?>">
                            <div class="form-group">
                                <label for="content">your reply</label>
                                <textarea id="content" name="content" required placeholder="share your thoughts..."></textarea>
                            </div>
                            <button type="submit" class="btn-submit">post reply</button>
                        </form>
                    </section>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <!-- Thread List View -->
                <div class="forum-header">
                    <h2 class="forum-title">guild hall</h2>
                    <button class="btn-new-thread" onclick="openNewThreadModal()">new thread</button>
                </div>

                <?php if (!empty($threads)): ?>
                    <div class="threads-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>title</th>
                                    <th style="width: 150px; text-align: center;">replies</th>
                                    <th style="width: 150px; text-align: center;">views</th>
                                    <th style="width: 200px;">last activity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($threads as $t): ?>
                                    <tr>
                                        <td class="thread-title-cell">
                                            <a href="<?php echo url('guild', ['id' => $guild_id, 'thread_id' => $t['id']]); ?>" class="thread-title-link">
                                                <?php if ($t['is_pinned']): ?>
                                                    <span class="thread-pinned">📌 pinned</span>
                                                <?php endif; ?>
                                                <?php if ($t['is_locked']): ?>
                                                    <span class="thread-locked">🔒 locked</span>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($t['title']); ?>
                                            </a>
                                            <div class="thread-author">by <?php echo htmlspecialchars($t['author_name']); ?></div>
                                        </td>
                                        <td class="thread-stat"><?php echo number_format($t['reply_count']); ?></td>
                                        <td class="thread-stat"><?php echo number_format($t['view_count']); ?></td>
                                        <td class="thread-last-activity">
                                            <?php
                                            $lastActivity = $t['last_activity'] ?? $t['created_at'];
                                            echo date('M j, Y', strtotime($lastActivity));
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="threads-table">
                        <div class="empty-threads">
                            <p>no threads yet</p>
                            <p style="font-size: 0.9375rem;">be the first to start a discussion!</p>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- New Thread Modal -->
    <div id="newThreadModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h2 class="modal-title">create new thread</h2>
                <button class="modal-close" onclick="closeNewThreadModal()" aria-label="Close modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <form id="newThreadForm">
                    <input type="hidden" name="guild_id" value="<?php echo $guild_id; ?>">
                    <div class="form-group">
                        <label for="thread_title">title</label>
                        <input type="text" id="thread_title" name="title" required placeholder="enter thread title">
                    </div>
                    <div class="form-group">
                        <label for="thread_content">content</label>
                        <textarea id="thread_content" name="content" required placeholder="write your post..."></textarea>
                    </div>
                    <button type="submit" class="btn-submit">create thread</button>
                </form>
            </div>
        </div>
    </div>

    <?php include 'partials/footer.php'; ?>

    <script>
        function openNewThreadModal() {
            document.getElementById('newThreadModal').classList.add('active');
        }

        function closeNewThreadModal() {
            document.getElementById('newThreadModal').classList.remove('active');
        }

        // Close modal on outside click
        document.getElementById('newThreadModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeNewThreadModal();
            }
        });

        // Handle new thread form submission
        document.getElementById('newThreadForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = {
                guild_id: <?php echo $guild_id; ?>,
                title: formData.get('title'),
                content: formData.get('content')
            };

            try {
                const response = await fetch('/api/guild_forum.php?action=create_thread', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    window.location.reload();
                } else {
                    console.error('API Error:', result);
                    alert('Error: ' + result.message + (result.debug ? '\n\nCheck console for details.' : ''));
                    if (result.debug) {
                        console.error('Debug trace:', result.debug);
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while creating the thread. Check console for details.');
            }
        });

        <?php if ($thread_id): ?>
        // Handle comment form submission
        document.getElementById('commentForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = {
                thread_id: <?php echo $thread_id; ?>,
                content: formData.get('content')
            };

            try {
                const response = await fetch('/api/guild_forum.php?action=create_comment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || 'Failed to post comment');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while posting the comment');
            }
        });

        // Handle comment voting
        const voteButtons = document.querySelectorAll('.comment-vote .vote-btn');

        voteButtons.forEach(button => {
            const voteIcon = button.querySelector('.vote-icon');
            const defaultSrc = voteIcon.dataset.default;
            const hoverSrc = voteIcon.dataset.hover;
            const selectedSrc = voteIcon.dataset.selected;

            // Hover effects
            button.addEventListener('mouseenter', function() {
                if (!this.classList.contains('voted')) {
                    voteIcon.src = hoverSrc;
                }
            });

            button.addEventListener('mouseleave', function() {
                if (!this.classList.contains('voted')) {
                    voteIcon.src = defaultSrc;
                } else {
                    voteIcon.src = selectedSrc;
                }
            });

            // Click handler
            button.addEventListener('click', async function(e) {
                e.preventDefault();

                const commentId = this.dataset.commentId;
                const voteType = this.classList.contains('vote-up') ? 'up' : 'down';
                const voteCountSpan = this.parentElement.querySelector('.vote-count');
                const otherButton = this.classList.contains('vote-up')
                    ? this.parentElement.querySelector('.vote-down')
                    : this.parentElement.querySelector('.vote-up');
                const otherIcon = otherButton.querySelector('.vote-icon');

                try {
                    const response = await fetch('/api/guild_forum.php?action=vote_comment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            comment_id: commentId,
                            vote_type: voteType
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        // Update vote count
                        voteCountSpan.textContent = result.data.votes;

                        // Update button states
                        if (result.data.user_vote === voteType) {
                            // User just voted
                            this.classList.add('voted');
                            voteIcon.src = selectedSrc;

                            // Remove voted state from other button
                            otherButton.classList.remove('voted');
                            otherIcon.src = otherIcon.dataset.default;
                        } else {
                            // User removed their vote
                            this.classList.remove('voted');
                            voteIcon.src = defaultSrc;
                        }
                    } else {
                        alert(result.message || 'Failed to vote on comment');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while voting');
                }
            });
        });
        <?php endif; ?>
    </script>
</body>
</html>
