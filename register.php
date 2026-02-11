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
    <link rel="icon" type="image/png" href="/assets/img/token/icon/up-gold.png">
    <link rel="apple-touch-icon" href="/assets/img/token/icon/up-gold.png">
    <meta property="og:title" content="<?php echo APP_NAME; ?>">
    <meta property="og:description" content="Post tasks, get it done by pros">
    <meta property="og:image" content="https://redot.test/assets/img/token/icon/up-gold.png">
    <meta property="og:url" content="https://redot.test">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?php echo APP_NAME; ?>">
    <meta name="twitter:description" content="Post tasks, get it done by pros">
    <meta name="twitter:image" content="https://redot.test/assets/img/token/icon/up-gold.png">
    <title>Register - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/register.css">
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <a href="<?php echo url('index'); ?>">
                <img src="/assets/img/token/logo/default.png" alt="<?php echo APP_NAME; ?>" class="register-logo">
            </a>
        </div>

        <div class="register-card">
            <div class="register-title-section">
                <h1 class="register-title">create account</h1>
                <p class="register-subtitle">join the community and start working</p>
            </div>

            <form id="registration-form" class="registration-form">
                <!-- Email -->
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

                <!-- Display Name -->
                <div class="form-group">
                    <label for="display_name">display name *</label>
                    <input
                        type="text"
                        id="display_name"
                        name="display_name"
                        class="form-input"
                        placeholder="How should we call you?"
                        required
                    >
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">password *</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        placeholder="at least 8 characters"
                        required
                        minlength="8"
                    >
                    <div class="password-strength" id="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strength-fill"></div>
                        </div>
                        <span class="strength-text" id="strength-text"></span>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="confirm_password">confirm password *</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="form-input"
                        placeholder="re-enter your password"
                        required
                        minlength="8"
                    >
                </div>

                <!-- Bio (Optional) -->
                <div class="form-group">
                    <label for="bio">bio <span class="optional-label">(optional)</span></label>
                    <textarea
                        id="bio"
                        name="bio"
                        class="form-textarea"
                        placeholder="Tell us about yourself, your experience, and what makes you unique..."
                        rows="4"
                    ></textarea>
                </div>

                <!-- Hourly Rate (Optional) -->
                <div class="form-group">
                    <label for="hourly_rate">hourly rate (USD) <span class="optional-label">(optional)</span></label>
                    <input
                        type="number"
                        id="hourly_rate"
                        name="hourly_rate"
                        class="form-input"
                        placeholder="e.g., 50"
                        min="0"
                        step="0.01"
                    >
                </div>

                <!-- Skills (Optional) -->
                <div class="form-group">
                    <label for="skills_input">skills <span class="optional-label">(optional)</span></label>
                    <div class="skills-input-wrapper">
                        <input
                            type="text"
                            id="skills_input"
                            class="form-input"
                            placeholder="Start typing to search skills..."
                            autocomplete="off"
                        >
                        <div class="skills-dropdown" id="skills-dropdown"></div>
                    </div>
                    <div class="selected-skills" id="selected-skills"></div>
                </div>

                <button type="submit" class="btn-register" id="register-btn">create account</button>
            </form>

            <div class="register-footer">
                <span class="footer-text">already have an account?</span>
                <a href="<?php echo url('connect'); ?>" class="footer-link">login here</a>
            </div>
        </div>

        <a href="<?php echo url('hire'); ?>" class="back-link">← back to site</a>
    </div>

    <div id="toast-container" class="toast-container"></div>

    <script src="/assets/js/register.js"></script>
</body>
</html>
