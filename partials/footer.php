<?php require_once __DIR__ . '/../config.php'; ?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-content">
            <nav class="footer-links">
                <a href="/about">about</a>
                <a href="/terms">terms</a>
                <a href="/privacy">privacy</a>
                <a href="/contact">contact</a>
            </nav>
            <div class="language-selector footer-language">
                <button class="language-btn" id="languageBtn" aria-label="Select Language">
                    <img src="https://flagcdn.com/w20/us.png" alt="US Flag" class="flag-icon" id="currentFlag">
                </button>
                <div class="language-dropdown" id="languageDropdown">
                    <button class="language-option" data-lang="en" data-flag="us">
                        <img src="https://flagcdn.com/w20/us.png" alt="US Flag" class="flag-icon">
                        <span>English</span>
                    </button>
                    <button class="language-option" data-lang="es" data-flag="es">
                        <img src="https://flagcdn.com/w20/es.png" alt="ES Flag" class="flag-icon">
                        <span>Spanish</span>
                    </button>
                    <button class="language-option" data-lang="fr" data-flag="fr">
                        <img src="https://flagcdn.com/w20/fr.png" alt="FR Flag" class="flag-icon">
                        <span>French</span>
                    </button>
                    <button class="language-option" data-lang="de" data-flag="de">
                        <img src="https://flagcdn.com/w20/de.png" alt="DE Flag" class="flag-icon">
                        <span>German</span>
                    </button>
                    <button class="language-option" data-lang="tl" data-flag="ph">
                        <img src="https://flagcdn.com/w20/ph.png" alt="PH Flag" class="flag-icon">
                        <span>Tagalog</span>
                    </button>
                    <button class="language-option" data-lang="zh-CN" data-flag="cn">
                        <img src="https://flagcdn.com/w20/cn.png" alt="CN Flag" class="flag-icon">
                        <span>Chinese (Simplified)</span>
                    </button>
                    <button class="language-option" data-lang="zh-TW" data-flag="tw">
                        <img src="https://flagcdn.com/w20/tw.png" alt="TW Flag" class="flag-icon">
                        <span>Chinese (Traditional)</span>
                    </button>
                </div>
            </div>
            <p class="footer-copyright">
                &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. all rights reserved.
            </p>
        </div>
    </div>
</footer>
