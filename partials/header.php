<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Profile.php';

$currentPage = basename($_SERVER['PHP_SELF']);

$isHome = ($currentPage === 'index.php');
$isBrowse = ($currentPage === 'browse.php');
$isBounties = ($currentPage === 'bounties.php');

$isLoggedIn = Auth::check();
$currentUser = $isLoggedIn ? Auth::user() : null;
$isAdmin = $isLoggedIn && Auth::isAdmin();

// Get user's profile for the Profile link
$userProfile = null;
if ($isLoggedIn) {
    $profileModel = new Profile();
    $userProfile = $profileModel->where('user_id', '=', Auth::id())->first();
}
?>
<header class="site-header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="<?php echo url('index'); ?>"><img src="/assets/img/token/logo/default.png" alt="<?php echo APP_NAME; ?>" class="site-logo"></a>
            </div>
            <nav class="main-nav">
                <a href="<?php echo url('index'); ?>" <?php echo $isHome ? 'class="active"' : ''; ?>>home</a>
                <a href="<?php echo url('browse'); ?>" <?php echo $isBrowse ? 'class="active"' : ''; ?>>browse</a>
                <a href="<?php echo url('bounties'); ?>" <?php echo $isBounties ? 'class="active"' : ''; ?>>bounties</a>
            </nav>
            <div class="header-actions">
                <button id="search-icon-btn" class="search-icon-btn" aria-label="Search">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
                <?php if ($isLoggedIn): ?>
                    <div class="user-menu-wrapper">
                        <button id="user-menu-btn" class="user-icon-btn" aria-label="User menu">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </button>
                        <div id="user-dropdown" class="user-dropdown">
                            <?php if ($userProfile): ?>
                                <a href="<?php echo url('profile', ['id' => $userProfile['profile_id']]); ?>">Profile</a>
                            <?php else: ?>
                                <a href="/profile.php">Profile</a>
                            <?php endif; ?>
                            <a href="/my-bounties.php">My Bounties</a>
                            <a href="/my-guilds.php">My Guilds</a>
                            <a href="/settings.php">Settings</a>
                            <?php if ($isAdmin): ?>
                                <a href="/admin/">Admin</a>
                            <?php endif; ?>
                            <a href="#" onclick="handleLogout(); return false;">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="connect.php" class="btn-join">login</a>
                    <a href="register.php" class="btn-join">register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- Search Modal -->
<div id="search-modal" class="search-modal">
    <div class="search-modal-content">
        <button class="search-modal-close" id="search-modal-close" aria-label="Close search">&times;</button>
        <form action="<?php echo url('search'); ?>" method="GET">
            <input type="text" name="key" id="search-input" placeholder="Search..." autocomplete="off" />
            <button type="submit" class="btn-search">Search</button>
        </form>
    </div>
</div>

<script>
function handleLogout() {
    fetch('api/auth.php?action=logout', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'connect.php';
        }
    })
    .catch(error => {
        console.error('Logout error:', error);
        window.location.href = 'connect.php';
    });
}
</script>
<script src="/assets/js/header.js" defer></script>
