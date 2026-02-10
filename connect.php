<?php
require_once __DIR__ . '/config/session.php';
require_once 'config.php';
require_once __DIR__ . '/classes/Auth.php';

if (Auth::check()) {
    header('Location: ' . url('index'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/token/icon/up-gold.png">
    <link rel="apple-touch-icon" href="assets/img/token/icon/up-gold.png">
    <meta property="og:title" content="<?php echo APP_NAME; ?>">
    <meta property="og:description" content="Post tasks, humans apply">
    <meta property="og:image" content="https://redot.test/assets/img/token/icon/up-gold.png">
    <meta property="og:url" content="https://redot.test">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?php echo APP_NAME; ?>">
    <meta name="twitter:description" content="Post tasks, humans apply">
    <meta name="twitter:image" content="https://redot.test/assets/img/token/icon/up-gold.png">
    <title>Connect - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/connect.css">
</head>
<body>
    <div class="connect-container">
        <h1 class="connect-title">connect</h1>

        <div class="auth-card">
            <div class="auth-header">
                <h2 class="auth-title" id="auth-title">login</h2>
                <p class="auth-subtitle" id="auth-subtitle">enter your credentials to continue</p>
            </div>

            <form id="auth-form" class="auth-form">
                <div class="form-group">
                    <label for="email">email *</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-input"
                        placeholder="your@email.com"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">password *</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        placeholder="enter your password"
                        required
                    >
                </div>

                <div class="form-group" id="confirm-password-group" style="display: none;">
                    <label for="confirm-password">confirm password *</label>
                    <input
                        type="password"
                        id="confirm-password"
                        name="confirm_password"
                        class="form-input"
                        placeholder="confirm your password"
                    >
                </div>

                <button type="submit" class="btn-primary" id="submit-btn">login</button>
            </form>

            <div class="auth-toggle">
                <span class="auth-toggle-text" id="toggle-text">don't have an account?</span>
                <button class="btn-link" id="toggle-btn">sign up here</button>
            </div>
        </div>

        <a href="<?php echo url('hire'); ?>" class="back-link">← back to site</a>
    </div>

    <div id="toast-container" class="toast-container"></div>

    <script src="assets/js/connect.js"></script>
</body>
</html>
