<?php
require_once __DIR__ . '/config/session.php';
require_once 'config.php';
require_once 'classes/Auth.php';
require_once 'classes/Database.php';

// Check authentication
if (!Auth::check()) {
    header('Location: connect.php');
    exit;
}

$userId = Auth::id();
$db = Database::getInstance();

// Fetch user's profile
$profileQuery = "SELECT id FROM profiles WHERE user_id = ?";
$profile = $db->queryOne($profileQuery, [$userId]);

$guilds = [];
if ($profile) {
    // Fetch user's guild memberships with guild details
    $query = "
        SELECT
            pg.id as membership_id,
            pg.xp,
            pg.is_primary,
            pg.joined_at,
            g.id as guild_id,
            g.name,
            g.description,
            g.icon,
            r.name as rank_name,
            r.level as rank_level,
            r.xp_required
        FROM profile_guilds pg
        INNER JOIN guilds g ON pg.guild_id = g.id
        LEFT JOIN ranks r ON pg.rank_id = r.id
        WHERE pg.profile_id = ?
        ORDER BY pg.is_primary DESC, pg.joined_at DESC
    ";

    $guilds = $db->query($query, [$profile['id']]);
}

// Helper function for time ago
function getTimeAgo($datetime) {
    $joined = new DateTime($datetime);
    $now = new DateTime();
    $diff = $now->diff($joined);

    if ($diff->days > 30) {
        $months = floor($diff->days / 30);
        return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
    } elseif ($diff->days > 0) {
        return $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
    } elseif ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    } else {
        return 'just now';
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
    <meta property="og:title" content="My Guilds - <?php echo APP_NAME; ?>">
    <meta property="og:description" content="Manage your guild memberships">
    <meta property="og:image" content="https://redot.test/assets/img/token/icon/up-gold.png">
    <meta property="og:type" content="website">
    <title>My Guilds - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/guilds.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <section class="page-header">
                <div class="page-header-top">
                    <div class="page-title-wrapper">
                        <h1 class="page-title">my guilds</h1>
                    </div>
                </div>
                <p class="page-subtitle">manage your guild memberships</p>
            </section>

            <section class="tasks-section">
                <div class="tasks-list">
                    <?php if (empty($guilds)): ?>
                        <div class="empty-state">
                            <svg width="64" height="64" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="32" cy="32" r="24" />
                                <line x1="20" y1="32" x2="44" y2="32" />
                                <line x1="32" y1="20" x2="32" y2="44" />
                            </svg>
                            <h3>no guild memberships yet</h3>
                            <p>you haven't joined any guilds yet. join a guild to collaborate with others.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($guilds as $guild): ?>
                            <div class="guild-membership-card">
                                <div class="guild-icon-wrapper">
                                    <span class="guild-icon-large"><?php echo htmlspecialchars($guild['icon'] ?? '⚔️'); ?></span>
                                </div>

                                <div class="guild-info">
                                    <div class="guild-header">
                                        <h3 class="guild-name">
                                            <?php echo htmlspecialchars($guild['name']); ?>
                                            <?php if ($guild['is_primary']): ?>
                                                <span class="badge-primary">primary</span>
                                            <?php endif; ?>
                                        </h3>
                                        <div class="guild-rank">
                                            <span class="rank-badge" style="background: #ff6b3520; color: #ff6b35; border-color: #ff6b35">
                                                <?php echo htmlspecialchars($guild['rank_name'] ?? 'Member'); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <?php if ($guild['description']): ?>
                                        <p class="guild-description"><?php echo htmlspecialchars($guild['description']); ?></p>
                                    <?php endif; ?>

                                    <div class="guild-meta">
                                        <span class="meta-item">
                                            <strong>XP:</strong> <?php echo number_format($guild['xp']); ?>
                                        </span>
                                        <span class="meta-item meta-time">
                                            Joined <?php echo getTimeAgo($guild['joined_at']); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="guild-actions">
                                    <?php if (!$guild['is_primary']): ?>
                                        <button class="btn-secondary" onclick="setPrimaryGuild(<?php echo $guild['membership_id']; ?>)">
                                            set as primary
                                        </button>
                                    <?php endif; ?>
                                    <a href="/guild.php?id=<?php echo $guild['guild_id']; ?>" class="btn-search">
                                        view guild
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

    <?php include 'partials/footer.php'; ?>

    <script>
    function setPrimaryGuild(membershipId) {
        if (!confirm('Set this guild as your primary guild?')) {
            return;
        }

        fetch('/api/guilds.php?action=set_primary', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ membership_id: membershipId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Could not set primary guild'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
    </script>
</body>
</html>
