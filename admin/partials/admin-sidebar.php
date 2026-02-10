<?php
/**
 * Admin Sidebar Partial
 * Fixed left sidebar with navigation links
 */

$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside class="admin-sidebar">
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
            <li class="<?= $current_page === 'profiles.php' ? 'active' : '' ?>">
                <a href="/admin/profiles.php">Profiles</a>
            </li>
            <li class="<?= $current_page === 'categories.php' ? 'active' : '' ?>">
                <a href="/admin/categories.php">Categories</a>
            </li>
            <li class="<?= $current_page === 'skills.php' ? 'active' : '' ?>">
                <a href="/admin/skills.php">Skills</a>
            </li>
            <li class="<?= $current_page === 'applications.php' ? 'active' : '' ?>">
                <a href="/admin/applications.php">Applications</a>
            </li>
        </ul>
    </nav>
</aside>
