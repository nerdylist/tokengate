/**
 * Language Selector with Google Translate Integration
 * Uses cookie-based translation approach
 */

document.addEventListener('DOMContentLoaded', function() {
    const languageBtn = document.getElementById('languageBtn');
    const languageDropdown = document.getElementById('languageDropdown');
    const currentFlag = document.getElementById('currentFlag');
    const languageOptions = document.querySelectorAll('.language-option');

    if (!languageBtn || !languageDropdown || !currentFlag) {
        return;
    }

    // Check if page was already translated
    const googtransCookie = getCookie('googtrans');
    if (googtransCookie) {
        const langMatch = googtransCookie.match(/\/en\/([a-z-]+)/i);
        if (langMatch && langMatch[1] !== 'en') {
            const langCode = langMatch[1];
            // Find matching option to update flag
            languageOptions.forEach(function(option) {
                if (option.getAttribute('data-lang') === langCode) {
                    const flagCode = option.getAttribute('data-flag');
                    updateButtonFlag(flagCode);
                }
            });
        }
    }

    // Load saved language preference
    const savedLang = localStorage.getItem('selectedLanguage');
    const savedFlag = localStorage.getItem('selectedFlag');

    if (savedLang && savedFlag) {
        updateButtonFlag(savedFlag);
    }

    // Toggle dropdown on button click
    languageBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        languageDropdown.classList.toggle('active');
    });

    // Handle language selection
    languageOptions.forEach(function(option) {
        option.addEventListener('click', function(e) {
            e.stopPropagation();

            const langCode = this.getAttribute('data-lang');
            const flagCode = this.getAttribute('data-flag');

            // Update button flag
            updateButtonFlag(flagCode);

            // Save preference
            localStorage.setItem('selectedLanguage', langCode);
            localStorage.setItem('selectedFlag', flagCode);

            // Translate page
            changeLanguage(langCode);

            // Close dropdown
            languageDropdown.classList.remove('active');
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!languageBtn.contains(e.target) && !languageDropdown.contains(e.target)) {
            languageDropdown.classList.remove('active');
        }
    });

    function updateButtonFlag(flagCode) {
        if (currentFlag) {
            currentFlag.src = 'https://flagcdn.com/w20/' + flagCode + '.png';
            currentFlag.alt = flagCode.toUpperCase() + ' Flag';
        }
    }

    function changeLanguage(langCode) {
        // Save preference
        localStorage.setItem('selectedLanguage', langCode);

        if (langCode === 'en') {
            // Reset to English: clear all Google Translate cookies and reload
            deleteCookie('googtrans');
            deleteCookie('googtrans', '/');
            deleteCookie('googtrans', '/', window.location.hostname);
            // Also clear the specific domain variants
            document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
            document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=' + window.location.hostname + ';';
            // Clear localStorage
            localStorage.removeItem('selectedLanguage');
            localStorage.setItem('selectedLanguage', 'en');
            localStorage.setItem('selectedFlag', 'us');
            // Reload to show original English
            location.reload();
        } else {
            // Set translation cookie for other languages
            const cookieValue = '/en/' + langCode;
            document.cookie = 'googtrans=' + cookieValue + '; path=/';
            document.cookie = 'googtrans=' + cookieValue + '; path=/; domain=' + window.location.hostname + ';';
            // Reload page to apply translation
            location.reload();
        }
    }

    function getCookie(name) {
        const value = '; ' + document.cookie;
        const parts = value.split('; ' + name + '=');
        if (parts.length === 2) {
            return parts.pop().split(';').shift();
        }
        return null;
    }

    function deleteCookie(name, path, domain) {
        let cookieString = name + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC;';
        if (path) {
            cookieString += ' path=' + path + ';';
        }
        if (domain) {
            cookieString += ' domain=' + domain + ';';
        }
        document.cookie = cookieString;
    }
});
