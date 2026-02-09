<?php require_once __DIR__ . '/../config.php'; ?>
<?php
// Detect current page
$currentPage = basename($_SERVER['PHP_SELF']);

// Determine which nav item should be active
$isBrowse = ($currentPage === 'browse.php');
$isBounties = ($currentPage === 'index.php' || $currentPage === 'detail.php');
$isHire = ($currentPage === 'hire.php');
$isConnect = ($currentPage === 'connect.php');
?>
<header class="site-header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="<?php echo url('index'); ?>"><?php echo APP_NAME; ?></a>
            </div>
            <nav class="main-nav">
                <a href="<?php echo url('browse'); ?>" <?php echo $isBrowse ? 'class="active"' : ''; ?>>browse</a>
                <a href="<?php echo url('index'); ?>" <?php echo $isBounties ? 'class="active"' : ''; ?>>bounties</a>
                <a href="<?php echo url('hire'); ?>" <?php echo $isHire ? 'class="active"' : ''; ?>>hire</a>
            </nav>
            <div class="header-actions">
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
                <a href="connect.php" class="btn-join<?php echo $isConnect ? ' active' : ''; ?>">connect</a>
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
</script>
<div id="google_translate_element" style="display:none;"></div>
<script src="js/language-selector.js"></script>
