/**
 * OneSignal PWA Public JavaScript
 *
 * @package OneSignal_PWA
 */

(function($) {
    'use strict';

    let deferredPrompt;
    const installPromptShown = 'onesignal_pwa_install_prompt_shown';
    const installPromptDismissed = 'onesignal_pwa_install_prompt_dismissed';

    /**
     * Initialize
     */
    function init() {
        // Capture beforeinstallprompt event
        window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);

        // Check if already installed
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
            trackEvent('pwa_already_installed');
            return;
        }

        // Show install prompt based on settings
        if (onesignalPWAPublic.enableInstallPrompt) {
            setTimeout(maybeShowInstallPrompt, onesignalPWAPublic.installPromptDelay * 1000);
        }

        // Track app install
        window.addEventListener('appinstalled', handleAppInstalled);

        // Setup install button handlers
        setupInstallHandlers();
    }

    /**
     * Handle beforeinstallprompt event
     */
    function handleBeforeInstallPrompt(e) {
        e.preventDefault();
        deferredPrompt = e;
        trackEvent('pwa_install_prompt_available');
    }

    /**
     * Maybe show install prompt
     */
    function maybeShowInstallPrompt() {
        // Check if dismissed recently
        const dismissed = localStorage.getItem(installPromptDismissed);
        if (dismissed) {
            const dismissedTime = parseInt(dismissed);
            const daysSinceDismissed = (Date.now() - dismissedTime) / (1000 * 60 * 60 * 24);
            if (daysSinceDismissed < 7) {
                return;
            }
        }

        // Check if already shown
        if (localStorage.getItem(installPromptShown)) {
            return;
        }

        if (deferredPrompt) {
            showInstallPrompt();
        }
    }

    /**
     * Show install prompt
     */
    function showInstallPrompt() {
        const $prompt = $('#onesignal-pwa-install-prompt');
        const position = onesignalPWAPublic.installPromptPosition;

        $prompt.addClass('position-' + position);
        $prompt.fadeIn(300);

        localStorage.setItem(installPromptShown, Date.now().toString());
        trackEvent('pwa_install_prompt_shown');
    }

    /**
     * Setup install handlers
     */
    function setupInstallHandlers() {
        // Install button
        $('.onesignal-pwa-install-btn').on('click', function() {
            if (!deferredPrompt) {
                return;
            }

            // Show the install prompt
            deferredPrompt.prompt();

            // Wait for the user to respond to the prompt
            deferredPrompt.userChoice.then(function(choiceResult) {
                if (choiceResult.outcome === 'accepted') {
                    trackEvent('pwa_install_accepted');
                } else {
                    trackEvent('pwa_install_declined');
                }
                deferredPrompt = null;
            });

            $('#onesignal-pwa-install-prompt').fadeOut(300);
        });

        // Later button
        $('.onesignal-pwa-later-btn, .onesignal-pwa-close').on('click', function() {
            $('#onesignal-pwa-install-prompt').fadeOut(300);
            localStorage.setItem(installPromptDismissed, Date.now().toString());
            trackEvent('pwa_install_prompt_dismissed');
        });
    }

    /**
     * Handle app installed
     */
    function handleAppInstalled() {
        trackEvent('pwa_installed');
        $('#onesignal-pwa-install-prompt').remove();
    }

    /**
     * Track event
     */
    function trackEvent(eventType, data) {
        data = data || {};
        data.event_type = eventType;
        data.page_url = window.location.href;
        data.referrer_url = document.referrer;

        $.ajax({
            url: onesignalPWAPublic.restUrl + 'events',
            method: 'POST',
            headers: {
                'X-WP-Nonce': onesignalPWAPublic.nonce
            },
            data: JSON.stringify(data),
            contentType: 'application/json',
        });
    }

    // Initialize on document ready
    $(document).ready(init);

})(jQuery);
