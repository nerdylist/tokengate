<?php
/**
 * Admin Sidebar Partial
 * Full-height sidebar with logo, user info, navigation, and logout
 */

require_once __DIR__ . '/../../config/session.php';

$current_page = basename($_SERVER['PHP_SELF']);
$admin_name = $_SESSION['user_name'] ?? 'Admin';
$admin_email = $_SESSION['user_email'] ?? 'admin@redot.com';
?>

<aside class="admin-sidebar">
    <!-- Logo Section -->
    <div class="sidebar-logo">
        <img src="/assets/img/token/logo/default.png" alt="REDOT Admin" />
    </div>

    <!-- User Info Section -->
    <div class="sidebar-user-info">
        <div class="user-name"><?= htmlspecialchars($admin_name) ?></div>
        <div class="user-email"><?= htmlspecialchars($admin_email) ?></div>
    </div>

    <!-- View Site Button -->
    <div class="sidebar-view-site">
        <a href="/" target="_blank" class="btn btn-primary">View Site</a>
    </div>

    <!-- Navigation Menu -->
    <div class="sidebar-nav">
        <nav>
            <ul>
                <li class="<?= ($current_page === 'index.php' || $current_page === '') ? 'active' : '' ?>">
                    <a href="/admin/">Dashboard</a>
                </li>
                <li class="<?= $current_page === 'users.php' ? 'active' : '' ?>">
                    <a href="/admin/users.php">Users</a>
                </li>
                <li class="<?= $current_page === 'bounties.php' ? 'active' : '' ?>">
                    <a href="/admin/bounties.php">Bounties</a>
                </li>
                <li class="<?= $current_page === 'categories.php' ? 'active' : '' ?>">
                    <a href="/admin/categories.php">Categories</a>
                </li>
                <li class="<?= $current_page === 'skills.php' ? 'active' : '' ?>">
                    <a href="/admin/skills.php">Skills</a>
                </li>
                <li class="<?= $current_page === 'profile-statuses.php' ? 'active' : '' ?>">
                    <a href="/admin/profile-statuses.php">Profile Statuses</a>
                </li>
                <li class="<?= $current_page === 'guilds.php' ? 'active' : '' ?>">
                    <a href="/admin/guilds.php">Guilds</a>
                </li>
                <li class="<?= $current_page === 'ranks.php' ? 'active' : '' ?>">
                    <a href="/admin/ranks.php">Ranks</a>
                </li>
                <li class="<?= $current_page === 'applications.php' ? 'active' : '' ?>">
                    <a href="/admin/applications.php">Applications</a>
                </li>
                <li class="<?= $current_page === 'settings.php' ? 'active' : '' ?>">
                    <a href="/admin/settings.php">Settings</a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Logout Button -->
    <div class="sidebar-logout">
        <form method="POST" action="/api/auth.php?action=logout">
            <button type="submit" class="btn btn-danger">Logout</button>
        </form>
    </div>
</aside>
