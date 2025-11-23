<?php
/**
 * Settings Management Class
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OneSignal PWA Settings Class
 */
class OneSignal_PWA_Settings {

    /**
     * Settings option prefix
     */
    const OPTION_PREFIX = 'onesignal_pwa_';

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // PWA settings
        register_setting('onesignal_pwa_settings', self::OPTION_PREFIX . 'app_name');
        register_setting('onesignal_pwa_settings', self::OPTION_PREFIX . 'app_short_name');
        register_setting('onesignal_pwa_settings', self::OPTION_PREFIX . 'app_description');
        register_setting('onesignal_pwa_settings', self::OPTION_PREFIX . 'theme_color');
        register_setting('onesignal_pwa_settings', self::OPTION_PREFIX . 'background_color');
        register_setting('onesignal_pwa_settings', self::OPTION_PREFIX . 'display_mode');
        register_setting('onesignal_pwa_settings', self::OPTION_PREFIX . 'orientation');
        register_setting('onesignal_pwa_settings', self::OPTION_PREFIX . 'start_url');
        register_setting('onesignal_pwa_settings', self::OPTION_PREFIX . 'scope');

        // OneSignal API settings
        register_setting('onesignal_pwa_api', self::OPTION_PREFIX . 'app_id');
        register_setting('onesignal_pwa_api', self::OPTION_PREFIX . 'rest_api_key');
        register_setting('onesignal_pwa_api', self::OPTION_PREFIX . 'user_auth_key');
        register_setting('onesignal_pwa_api', self::OPTION_PREFIX . 'safari_web_id');

        // Service Worker settings
        register_setting('onesignal_pwa_sw', self::OPTION_PREFIX . 'enable_service_worker');
        register_setting('onesignal_pwa_sw', self::OPTION_PREFIX . 'cache_strategy');
        register_setting('onesignal_pwa_sw', self::OPTION_PREFIX . 'cache_version');
        register_setting('onesignal_pwa_sw', self::OPTION_PREFIX . 'offline_page');

        // Notification settings
        register_setting('onesignal_pwa_notifications', self::OPTION_PREFIX . 'enable_push_notifications');
        register_setting('onesignal_pwa_notifications', self::OPTION_PREFIX . 'auto_subscribe');
        register_setting('onesignal_pwa_notifications', self::OPTION_PREFIX . 'welcome_notification');
        register_setting('onesignal_pwa_notifications', self::OPTION_PREFIX . 'notify_on_new_post');

        // Installation prompt settings
        register_setting('onesignal_pwa_install', self::OPTION_PREFIX . 'enable_install_prompt');
        register_setting('onesignal_pwa_install', self::OPTION_PREFIX . 'install_prompt_delay');
        register_setting('onesignal_pwa_install', self::OPTION_PREFIX . 'install_prompt_position');
    }

    /**
     * Get setting value
     *
     * @param string $key Setting key
     * @param mixed $default Default value
     * @return mixed
     */
    public static function get($key, $default = null) {
        return get_option(self::OPTION_PREFIX . $key, $default);
    }

    /**
     * Set setting value
     *
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool
     */
    public static function set($key, $value) {
        return update_option(self::OPTION_PREFIX . $key, $value);
    }

    /**
     * Delete setting
     *
     * @param string $key Setting key
     * @return bool
     */
    public static function delete($key) {
        return delete_option(self::OPTION_PREFIX . $key);
    }

    /**
     * Get all PWA settings
     *
     * @return array
     */
    public static function get_pwa_settings() {
        return array(
            'app_name' => self::get('app_name', get_bloginfo('name')),
            'app_short_name' => self::get('app_short_name', substr(get_bloginfo('name'), 0, 12)),
            'app_description' => self::get('app_description', get_bloginfo('description')),
            'theme_color' => self::get('theme_color', '#000000'),
            'background_color' => self::get('background_color', '#ffffff'),
            'display_mode' => self::get('display_mode', 'standalone'),
            'orientation' => self::get('orientation', 'any'),
            'start_url' => self::get('start_url', '/'),
            'scope' => self::get('scope', '/'),
        );
    }

    /**
     * Get OneSignal API credentials
     *
     * @return array
     */
    public static function get_api_credentials() {
        $encryption = new OneSignal_PWA_Encryption();

        return array(
            'app_id' => self::get('app_id', ''),
            'rest_api_key' => $encryption->decrypt(self::get('rest_api_key', '')),
            'user_auth_key' => $encryption->decrypt(self::get('user_auth_key', '')),
            'safari_web_id' => self::get('safari_web_id', ''),
        );
    }

    /**
     * Set OneSignal API credentials (with encryption)
     *
     * @param array $credentials
     * @return bool
     */
    public static function set_api_credentials($credentials) {
        $encryption = new OneSignal_PWA_Encryption();

        if (isset($credentials['app_id'])) {
            self::set('app_id', sanitize_text_field($credentials['app_id']));
        }

        if (isset($credentials['rest_api_key'])) {
            $encrypted = $encryption->encrypt($credentials['rest_api_key']);
            self::set('rest_api_key', $encrypted);
        }

        if (isset($credentials['user_auth_key'])) {
            $encrypted = $encryption->encrypt($credentials['user_auth_key']);
            self::set('user_auth_key', $encrypted);
        }

        if (isset($credentials['safari_web_id'])) {
            self::set('safari_web_id', sanitize_text_field($credentials['safari_web_id']));
        }

        return true;
    }

    /**
     * Validate API credentials
     *
     * @return bool|WP_Error
     */
    public static function validate_api_credentials() {
        $credentials = self::get_api_credentials();

        if (empty($credentials['app_id']) || empty($credentials['rest_api_key'])) {
            return new WP_Error('missing_credentials', __('App ID and REST API Key are required.', 'onesignal-pwa'));
        }

        // Test API connection
        $api_client = new OneSignal_PWA_API_Client();
        $test = $api_client->test_connection();

        if (is_wp_error($test)) {
            return $test;
        }

        return true;
    }

    /**
     * Check if PWA is properly configured
     *
     * @return bool
     */
    public static function is_pwa_configured() {
        $settings = self::get_pwa_settings();

        return !empty($settings['app_name']) &&
               !empty($settings['theme_color']) &&
               self::get('icon_512');
    }

    /**
     * Check if OneSignal is properly configured
     *
     * @return bool
     */
    public static function is_onesignal_configured() {
        $credentials = self::get_api_credentials();

        return !empty($credentials['app_id']) &&
               !empty($credentials['rest_api_key']);
    }

    /**
     * Get setup completion percentage
     *
     * @return int
     */
    public static function get_setup_completion() {
        $steps = array(
            'app_name' => !empty(self::get('app_name')),
            'theme_color' => !empty(self::get('theme_color')),
            'icon' => !empty(self::get('icon_512')),
            'app_id' => !empty(self::get('app_id')),
            'rest_api_key' => !empty(self::get('rest_api_key')),
            'service_worker' => self::get('enable_service_worker', true),
        );

        $completed = count(array_filter($steps));
        $total = count($steps);

        return intval(($completed / $total) * 100);
    }

    /**
     * Export settings
     *
     * @return array
     */
    public static function export_settings() {
        global $wpdb;

        $options = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
                self::OPTION_PREFIX . '%'
            ),
            ARRAY_A
        );

        $settings = array();
        foreach ($options as $option) {
            $key = str_replace(self::OPTION_PREFIX, '', $option['option_name']);
            $settings[$key] = maybe_unserialize($option['option_value']);
        }

        // Remove sensitive data
        unset($settings['rest_api_key']);
        unset($settings['user_auth_key']);

        return $settings;
    }

    /**
     * Import settings
     *
     * @param array $settings
     * @return bool
     */
    public static function import_settings($settings) {
        if (!is_array($settings)) {
            return false;
        }

        foreach ($settings as $key => $value) {
            // Skip sensitive keys
            if (in_array($key, array('rest_api_key', 'user_auth_key'))) {
                continue;
            }

            self::set($key, $value);
        }

        return true;
    }
}
