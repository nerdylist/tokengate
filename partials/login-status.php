<?php
require_once __DIR__ . '/../classes/Auth.php';
$isLoggedIn = Auth::check();
$currentUser = $isLoggedIn ? Auth::user() : null;
?>

<!-- Login Status Bar -->
<div id="login-bar" class="login-bar">
    <?php if (!$isLoggedIn): ?>
    <!-- Not Logged In State -->
    <div id="login-bar-guest" class="login-bar-content">
        <span class="login-bar-text">login to post bounties:</span>
        <input type="email" id="quick-email" class="login-bar-input" placeholder="email" />
        <input type="password" id="quick-password" class="login-bar-input" placeholder="password" />
        <button id="quick-login-btn" class="login-bar-btn">login</button>
        <span class="login-bar-divider">|</span>
        <a href="<?php echo url('connect'); ?>" class="login-bar-link">need an account? sign up here</a>
    </div>
    <?php else: ?>
    <!-- Logged In State -->
    <div id="login-bar-user" class="login-bar-content">
        <span class="login-bar-greeting">Hi <span id="username-display"><?php echo htmlspecialchars($currentUser['name'] ?? $currentUser['email']); ?></span>! Welcome back!</span>
        <nav class="login-bar-nav">
            <a href="profile.php" class="login-bar-nav-link">PROFILE</a>
            <span class="login-bar-nav-divider">|</span>
            <a href="settings.php" class="login-bar-nav-link">SETTINGS</a>
            <span class="login-bar-nav-divider">|</span>
            <button id="logout-btn" class="login-bar-nav-btn">LOGOUT</button>
        </nav>
    </div>
    <?php endif; ?>
</div>
