<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Auth.php';

$currentPage = basename($_SERVER['PHP_SELF']);

$isBrowse = ($currentPage === 'browse.php');
$isBounties = ($currentPage === 'index.php' || $currentPage === 'detail.php');
$isHire = ($currentPage === 'hire.php');
$isConnect = ($currentPage === 'connect.php');

$isLoggedIn = Auth::check();
$currentUser = $isLoggedIn ? Auth::user() : null;
$isAdmin = $isLoggedIn && Auth::isAdmin();
?>
<header class="site-header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="<?php echo url('index'); ?>"><img src="assets/img/token/logo/default.png" alt="<?php echo APP_NAME; ?>" class="site-logo"></a>
            </div>
            <nav class="main-nav">
                <a href="<?php echo url('browse'); ?>" <?php echo $isBrowse ? 'class="active"' : ''; ?>>browse</a>
                <a href="<?php echo url('index'); ?>" <?php echo $isBounties ? 'class="active"' : ''; ?>>bounties</a>
                <a href="<?php echo url('hire'); ?>" <?php echo $isHire ? 'class="active"' : ''; ?>>hire</a>
            </nav>
            <div class="header-actions">
                <?php if ($isLoggedIn): ?>
                    <span class="user-email"><?php echo htmlspecialchars($currentUser['email']); ?></span>
                    <div class="language-selector">
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
                    <?php if ($isAdmin): ?>
                        <a href="/admin/" class="btn-admin" aria-label="Admin">
                            <img src="https://nerd.biz/assets/fa/svgs/solid/gear.svg" alt="Admin" style="width: 20px; height: 20px;">
                        </a>
                    <?php endif; ?>
                    <button class="btn-logout" onclick="handleLogout()" aria-label="Logout">
                        <img src="https://nerd.biz/assets/fa/svgs/solid/key.svg" alt="Logout" style="width: 20px; height: 20px;">
                    </button>
                <?php else: ?>
                    <a href="connect.php" class="btn-join<?php echo $isConnect ? ' active' : ''; ?>">connect</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
<script>
function googleTranslateElementInit() {
    new google.translate.TranslateElement({
        pageLanguage: 'en',
        includedLanguages: 'en,es,fr,de,tl,zh-CN,zh-TW',
        autoDisplay: false
    }, 'google_translate_element');
}

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
<div id="google_translate_element" style="display:none;"></div>
<script src="assets/js/language-selector.js"></script>
