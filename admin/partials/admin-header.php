<?php
/**
 * Admin Header Partial
 * Fixed header with logo, admin email, view site link, and logout button
 */

require_once __DIR__ . '/../../config/session.php';

$admin_email = $_SESSION['user_email'] ?? 'Admin';
?>

<header class="admin-header">
    <div class="logo"><img src="../assets/img/token/logo/default.png" alt="Admin" class="admin-logo" style="height: 35px; width: auto;"></div>

    <div class="header-right">
        <span class="admin-email"><?= htmlspecialchars($admin_email) ?></span>

        <a href="/" class="view-site">View Site</a>

        <form method="POST" action="/api/auth.php?action=logout" style="margin: 0;">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>
</header>
