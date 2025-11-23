<?php
/**
 * Public-Facing Functionality
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OneSignal PWA Public Class
 */
class OneSignal_PWA_Public {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'install_prompt'));
        add_action('wp_head', array($this, 'add_meta_tags'), 1);
    }

    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'onesignal-pwa-public',
            ONESIGNAL_PWA_PLUGIN_URL . 'assets/css/public.css',
            array(),
            ONESIGNAL_PWA_VERSION
        );

        wp_enqueue_script(
            'onesignal-pwa-public',
            ONESIGNAL_PWA_PLUGIN_URL . 'assets/js/public.js',
            array('jquery'),
            ONESIGNAL_PWA_VERSION,
            true
        );

        wp_localize_script('onesignal-pwa-public', 'onesignalPWAPublic', array(
            'restUrl' => rest_url('onesignal-pwa/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'appId' => OneSignal_PWA_Settings::get('app_id'),
            'enableInstallPrompt' => OneSignal_PWA_Settings::get('enable_install_prompt', true),
            'installPromptDelay' => OneSignal_PWA_Settings::get('install_prompt_delay', 30),
            'installPromptPosition' => OneSignal_PWA_Settings::get('install_prompt_position', 'bottom'),
            'enableIOSAddToHome' => OneSignal_PWA_Settings::get('enable_ios_a2hs', true),
            'iosAddToHomeDelay' => OneSignal_PWA_Settings::get('ios_a2hs_delay', 15),
            'strings' => array(
                'installTitle' => __('Install App', 'onesignal-pwa'),
                'installMessage' => __('Install our app for a better experience!', 'onesignal-pwa'),
                'installButton' => __('Install', 'onesignal-pwa'),
                'laterButton' => __('Later', 'onesignal-pwa'),
                'iosHintTitle' => OneSignal_PWA_Settings::get('ios_a2hs_title', __('Add to Home Screen', 'onesignal-pwa')),
                'iosHintMessage' => OneSignal_PWA_Settings::get('ios_a2hs_message', __('Tap the share icon and select “Add to Home Screen” to install this app on your iPhone.', 'onesignal-pwa')),
            )
        ));
    }

    /**
     * Add meta tags
     */
    public function add_meta_tags() {
        // Meta tags are added by the Manifest class
    }

    /**
     * Display install prompt
     */
    public function install_prompt() {
        $install_prompt_enabled = OneSignal_PWA_Settings::get('enable_install_prompt', true);
        $ios_hint_enabled = OneSignal_PWA_Settings::get('enable_ios_a2hs', true);

        if (!$install_prompt_enabled && !$ios_hint_enabled) {
            return;
        }

        ?>
        <?php if ($install_prompt_enabled) : ?>
            <div id="onesignal-pwa-install-prompt" class="onesignal-pwa-install-prompt" style="display:none;">
                <div class="onesignal-pwa-install-content">
                    <button class="onesignal-pwa-close" aria-label="<?php _e('Close', 'onesignal-pwa'); ?>">&times;</button>
                    <div class="onesignal-pwa-icon">
                        <?php
                        $icon = OneSignal_PWA_Settings::get('icon_192');
                        if ($icon): ?>
                            <img src="<?php echo esc_url($icon); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
                        <?php endif; ?>
                    </div>
                    <div class="onesignal-pwa-text">
                        <h3><?php echo esc_html(OneSignal_PWA_Settings::get('install_prompt_title', __('Install App', 'onesignal-pwa'))); ?></h3>
                        <p><?php echo esc_html(OneSignal_PWA_Settings::get('install_prompt_message', __('Install our app for a better experience!', 'onesignal-pwa'))); ?></p>
                    </div>
                    <div class="onesignal-pwa-actions">
                        <button class="onesignal-pwa-install-btn"><?php _e('Install', 'onesignal-pwa'); ?></button>
                        <button class="onesignal-pwa-later-btn"><?php _e('Later', 'onesignal-pwa'); ?></button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($ios_hint_enabled) : ?>
            <div id="onesignal-pwa-ios-hint" class="onesignal-pwa-ios-hint" style="display:none;">
                <button class="onesignal-pwa-close" aria-label="<?php _e('Close', 'onesignal-pwa'); ?>">&times;</button>
                <p class="onesignal-pwa-ios-title"><?php echo esc_html(OneSignal_PWA_Settings::get('ios_a2hs_title', __('Add to Home Screen', 'onesignal-pwa'))); ?></p>
                <p class="onesignal-pwa-ios-message">
                    <?php echo esc_html(OneSignal_PWA_Settings::get('ios_a2hs_message', __('Tap the share icon and select “Add to Home Screen” to install this app on your iPhone.', 'onesignal-pwa'))); ?>
                </p>
                <div class="onesignal-pwa-ios-icons" aria-hidden="true">
                    <span class="share-icon">⬆️</span>
                    <span class="onesignal-pwa-ios-label"><?php _e('Add to Home Screen', 'onesignal-pwa'); ?></span>
                </div>
            </div>
        <?php endif; ?>
        <?php
    }
}
