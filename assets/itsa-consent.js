(function () {
    const config = window.itsaConsentConfig || {
        cookieName: 'itsa_cookie_consent',
        expiryDays: 180
    };

    const banner = document.getElementById('itsa-cookie-banner');
    const modal = document.getElementById('itsa-cc-modal');
    const reopenBtn = document.getElementById('itsa-cc-reopen');

    const acceptBtn = document.getElementById('itsa-cc-accept');
    const rejectBtn = document.getElementById('itsa-cc-reject');
    const customizeBtn = document.getElementById('itsa-cc-customize');
    const closeModalBtn = document.getElementById('itsa-cc-close-modal');
    const savePrefsBtn = document.getElementById('itsa-cc-save-prefs');

    const analyticsCheckbox = document.getElementById('itsa-cat-analytics');
    const marketingCheckbox = document.getElementById('itsa-cat-marketing');

    function getConsentCookie() {
        const match = document.cookie.match(
            new RegExp('(?:^|; )' + config.cookieName.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)')
        );
        if (!match) return null;
        try {
            return JSON.parse(decodeURIComponent(match[1]));
        } catch (e) {
            return null;
        }
    }

    function setConsentCookie(prefs) {
        const date = new Date();
        date.setTime(date.getTime() + config.expiryDays * 24 * 60 * 60 * 1000);
        const secure = window.location.protocol === 'https:' ? '; Secure' : '';
        document.cookie =
            config.cookieName +
            '=' +
            encodeURIComponent(JSON.stringify(prefs)) +
            '; expires=' +
            date.toUTCString() +
            '; path=/; SameSite=Lax' +
            secure;
    }

    /**
     * Unblock and execute <script type="text/plain" data-itsa-category="..."> tags
     * matching granted categories.
     */
    function unblockScripts(prefs) {
        const blockedScripts = document.querySelectorAll('script[type="text/plain"][data-itsa-category]');

        blockedScripts.forEach(function (scriptEl) {
            const category = scriptEl.getAttribute('data-itsa-category');
            if (!prefs[category] || scriptEl.dataset.itsaExecuted === 'true') {
                return;
            }

            const newScript = document.createElement('script');
            const externalSrc = scriptEl.getAttribute('data-itsa-src');

            if (externalSrc) {
                newScript.src = externalSrc;
                newScript.async = true;
            } else {
                newScript.textContent = scriptEl.textContent;
            }

            scriptEl.dataset.itsaExecuted = 'true';
            scriptEl.parentNode.insertBefore(newScript, scriptEl.nextSibling);
        });
    }

    function applyConsent(prefs, persist) {
        if (persist) {
            setConsentCookie(prefs);
        }

        unblockScripts(prefs);

        if (banner) banner.hidden = true;
        if (modal) modal.hidden = true;
        if (reopenBtn) reopenBtn.hidden = false;

        document.dispatchEvent(
            new CustomEvent('itsaCookieConsentUpdated', { detail: prefs })
        );
    }

    // Initialize state on page load
    const existingPrefs = getConsentCookie();
    if (existingPrefs) {
        analyticsCheckbox.checked = !!existingPrefs.analytics;
        marketingCheckbox.checked = !!existingPrefs.marketing;
        applyConsent(existingPrefs, false);
    } else {
        if (banner) banner.hidden = false;
    }

    acceptBtn.addEventListener('click', function () {
        const prefs = { essential: true, analytics: true, marketing: true };
        analyticsCheckbox.checked = true;
        marketingCheckbox.checked = true;
        applyConsent(prefs, true);
    });

    rejectBtn.addEventListener('click', function () {
        const prefs = { essential: true, analytics: false, marketing: false };
        analyticsCheckbox.checked = false;
        marketingCheckbox.checked = false;
        applyConsent(prefs, true);
    });

    customizeBtn.addEventListener('click', function () {
        modal.hidden = false;
    });

    reopenBtn.addEventListener('click', function () {
        modal.hidden = false;
    });

    closeModalBtn.addEventListener('click', function () {
        modal.hidden = true;
    });

    savePrefsBtn.addEventListener('click', function () {
        const prefs = {
            essential: true,
            analytics: analyticsCheckbox.checked,
            marketing: marketingCheckbox.checked
        };
        applyConsent(prefs, true);
    });
})();
