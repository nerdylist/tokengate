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

    // Fetch comments
    $sql = "SELECT gc.*, p.profile_id, u.name as author_name, p.avatar_url
            FROM guild_comments gc
            INNER JOIN profiles p ON gc.profile_id = p.id
            INNER JOIN users u ON p.user_id = u.id
            WHERE gc.thread_id = ?
            ORDER BY gc.created_at ASC";
    $comments = $db->query($sql, [$thread_id]);
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
    <style>
        /* Guild Forum Styles */
        .guild-forum-page {
            background-color: #0a0a0a;
            min-height: 100vh;
            padding: 40px 0;
        }

        .guild-header-section {
            background-color: #151515;
            border: 1px solid #1a1a1a;
            border-radius: 12px;
            padding: 32px;
            margin-bottom: 32px;
        }

        .guild-header-content {
            display: flex;
            align-items: center;
            gap: 24px;
            margin-bottom: 16px;
        }

        .guild-icon-display {
            font-size: 3rem;
            line-height: 1;
        }

        .guild-header-info h1 {
            font-family: 'Share Tech Mono', monospace;
            font-size: 2rem;
            font-weight: 700;
            color: #ffffff;
            margin: 0 0 8px 0;
        }

        .guild-header-meta {
            display: flex;
            gap: 20px;
            font-size: 0.9375rem;
            color: #9ca3af;
        }

        .guild-header-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .guild-description-text {
            color: #9ca3af;
            font-size: 0.9375rem;
            line-height: 1.6;
            margin: 0;
        }

        /* Thread List */
        .forum-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .forum-title {
            font-family: 'Share Tech Mono', monospace;
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
        }

        .btn-new-thread {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: #ffffff;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 0.9375rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-new-thread:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        }

        .threads-table {
            background-color: #151515;
            border: 1px solid #1a1a1a;
            border-radius: 12px;
            overflow: hidden;
        }

        .threads-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .threads-table thead {
            background-color: #1a1a1a;
        }

        .threads-table th {
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.8125rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #9ca3af;
            padding: 16px 20px;
            text-align: left;
        }

        .threads-table tbody tr {
            border-top: 1px solid #1a1a1a;
            transition: background-color 0.2s ease;
        }

        .threads-table tbody tr:hover {
            background-color: #171717;
        }

        .threads-table td {
            padding: 20px;
            color: #e5e5e5;
            font-size: 0.9375rem;
        }

        .thread-title-cell {
            max-width: 500px;
        }

        .thread-title-link {
            font-weight: 600;
            color: #ffffff;
            text-decoration: none;
            transition: color 0.2s ease;
            display: block;
            margin-bottom: 4px;
        }

        .thread-title-link:hover {
            color: #ff6b35;
        }

        .thread-pinned {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.75rem;
            color: #ff6b35;
            font-weight: 700;
            text-transform: uppercase;
            margin-right: 8px;
        }

        .thread-locked {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.75rem;
            color: #9ca3af;
            font-weight: 700;
            text-transform: uppercase;
            margin-right: 8px;
        }

        .thread-author {
            color: #9ca3af;
            font-size: 0.875rem;
        }

        .thread-stat {
            text-align: center;
            color: #9ca3af;
        }

        .thread-last-activity {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .empty-threads {
            padding: 60px 20px;
            text-align: center;
            color: #71717a;
        }

        .empty-threads p {
            font-size: 1.125rem;
            margin-bottom: 8px;
        }

        /* Thread Detail */
        .thread-detail-section {
            background-color: #151515;
            border: 1px solid #1a1a1a;
            border-radius: 12px;
            padding: 32px;
            margin-bottom: 32px;
        }

        .thread-detail-header {
            margin-bottom: 24px;
            padding-bottom: 24px;
            border-bottom: 1px solid #1a1a1a;
        }

        .thread-detail-title {
            font-family: 'Share Tech Mono', monospace;
            font-size: 1.75rem;
            font-weight: 700;
            color: #ffffff;
            margin: 0 0 12px 0;
        }

        .thread-detail-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 0.9375rem;
            color: #9ca3af;
        }

        .thread-detail-author {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .author-avatar-small {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #2d2d2d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            font-weight: 600;
            color: #ffffff;
        }

        .author-avatar-small img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .thread-detail-content {
            color: #e5e5e5;
            font-size: 1rem;
            line-height: 1.8;
            white-space: pre-wrap;
        }

        /* Comments */
        .comments-section {
            background-color: #151515;
            border: 1px solid #1a1a1a;
            border-radius: 12px;
            padding: 32px;
            margin-bottom: 32px;
        }

        .comments-title {
            font-family: 'Share Tech Mono', monospace;
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
            margin: 0 0 24px 0;
        }

        .comment-item {
            padding: 24px 0;
            border-bottom: 1px solid #1a1a1a;
        }

        .comment-item:last-child {
            border-bottom: none;
        }

        .comment-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .comment-author-name {
            font-weight: 600;
            color: #ffffff;
        }

        .comment-timestamp {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .comment-content {
            color: #e5e5e5;
            font-size: 0.9375rem;
            line-height: 1.7;
            white-space: pre-wrap;
        }

        .no-comments {
            text-align: center;
            color: #71717a;
            padding: 40px 20px;
        }

        /* Comment Form */
        .comment-form-section {
            background-color: #151515;
            border: 1px solid #1a1a1a;
            border-radius: 12px;
            padding: 32px;
        }

        .comment-form-title {
            font-family: 'Share Tech Mono', monospace;
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
            margin: 0 0 20px 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.9375rem;
            font-weight: 600;
            color: #e5e5e5;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            background-color: #0a0a0a;
            border: 1px solid #2d2d2d;
            border-radius: 8px;
            padding: 12px 16px;
            color: #ffffff;
            font-size: 0.9375rem;
            font-family: inherit;
            transition: border-color 0.2s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ff6b35;
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .btn-submit {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: #ffffff;
            border: none;
            padding: 12px 32px;
            border-radius: 8px;
            font-size: 0.9375rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #9ca3af;
            text-decoration: none;
            font-size: 0.9375rem;
            margin-bottom: 24px;
            transition: color 0.2s ease;
        }

        .btn-back:hover {
            color: #ffffff;
        }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-container {
            background-color: #151515;
            border: 1px solid #2d2d2d;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px 32px;
            border-bottom: 1px solid #1a1a1a;
        }

        .modal-title {
            font-family: 'Share Tech Mono', monospace;
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            color: #9ca3af;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
        }

        .modal-close:hover {
            color: #ffffff;
        }

        .modal-body {
            padding: 32px;
        }

        @media (max-width: 768px) {
            .threads-table {
                overflow-x: auto;
            }

            .guild-header-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .forum-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .btn-new-thread {
                width: 100%;
            }
        }
    </style>
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
                    back to threads
                </a>

                <section class="thread-detail-section">
                    <div class="thread-detail-header">
                        <h2 class="thread-detail-title">
                            <?php if ($thread['is_pinned']): ?>
                                <span class="thread-pinned">📌 pinned</span>
                            <?php endif; ?>
                            <?php if ($thread['is_locked']): ?>
                                <span class="thread-locked">🔒 locked</span>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($thread['title']); ?>
                        </h2>
                        <div class="thread-detail-meta">
                            <div class="thread-detail-author">
                                <div class="author-avatar-small">
                                    <?php if (!empty($thread['avatar_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($thread['avatar_url']); ?>" alt="<?php echo htmlspecialchars($thread['author_name']); ?>">
                                    <?php else: ?>
                                        <?php echo strtoupper(substr(htmlspecialchars($thread['author_name']), 0, 1)); ?>
                                    <?php endif; ?>
                                </div>
                                <span><?php echo htmlspecialchars($thread['author_name']); ?></span>
                            </div>
                            <span><?php echo date('M j, Y g:i A', strtotime($thread['created_at'])); ?></span>
                            <span><?php echo number_format($thread['view_count']); ?> views</span>
                        </div>
                    </div>
                    <div class="thread-detail-content">
                        <?php echo htmlspecialchars($thread['content']); ?>
                    </div>
                </section>

                <!-- Comments Section -->
                <section class="comments-section">
                    <h3 class="comments-title">replies (<?php echo count($comments); ?>)</h3>
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-item">
                                <div class="comment-header">
                                    <div class="author-avatar-small">
                                        <?php if (!empty($comment['avatar_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($comment['avatar_url']); ?>" alt="<?php echo htmlspecialchars($comment['author_name']); ?>">
                                        <?php else: ?>
                                            <?php echo strtoupper(substr(htmlspecialchars($comment['author_name']), 0, 1)); ?>
                                        <?php endif; ?>
                                    </div>
                                    <span class="comment-author-name"><?php echo htmlspecialchars($comment['author_name']); ?></span>
                                    <span class="comment-timestamp"><?php echo date('M j, Y g:i A', strtotime($comment['created_at'])); ?></span>
                                </div>
                                <div class="comment-content">
                                    <?php echo htmlspecialchars($comment['content']); ?>
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

            <?php else: ?>
                <!-- Thread List View -->
                <div class="forum-header">
                    <h2 class="forum-title">forum threads</h2>
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
        document.getElementById('newThreadForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('api/guild_threads.php?action=create', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'guild.php?id=<?php echo $guild_id; ?>&thread_id=' + data.thread_id;
                } else {
                    alert(data.error || 'Failed to create thread');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while creating the thread');
            });
        });

        <?php if ($thread_id): ?>
        // Handle comment form submission
        document.getElementById('commentForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('api/guild_threads.php?action=add_comment', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to post comment');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while posting the comment');
            });
        });
        <?php endif; ?>
    </script>
</body>
</html>
