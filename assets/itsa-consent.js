(function () {
    const config = window.itsaConsentConfig || {
        cookieName: 'itsa_cookie_consent',
        expiryDays: 180
    };

    const banner = document.getElementById('itsa-cookie-banner');
    const acceptBtn = document.getElementById('itsa-cc-accept');
    const rejectBtn = document.getElementById('itsa-cc-reject');

    if (!banner || !acceptBtn || !rejectBtn) {
        return;
    }

    function setConsentCookie(value) {
        const date = new Date();
        date.setTime(date.getTime() + config.expiryDays * 24 * 60 * 60 * 1000);
        const secure = window.location.protocol === 'https:' ? '; Secure' : '';
        document.cookie =
            config.cookieName +
            '=' +
            encodeURIComponent(value) +
            '; expires=' +
            date.toUTCString() +
            '; path=/; SameSite=Lax' +
            secure;
    }

    function handleChoice(choice) {
        setConsentCookie(choice);
        banner.remove();
        document.dispatchEvent(
            new CustomEvent('itsaCookieConsentUpdated', { detail: { consent: choice } })
        );
    }

    acceptBtn.addEventListener('click', function () {
        handleChoice('accepted');
    });

    rejectBtn.addEventListener('click', function () {
        handleChoice('essential_only');
    });
})();
