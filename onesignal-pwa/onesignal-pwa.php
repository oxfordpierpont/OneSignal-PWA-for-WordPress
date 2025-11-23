<?php
/**
 * Plugin Name: OneSignal PWA for WordPress
 * Plugin URI: https://oxfordpierpont.com/onesignal-pwa
 * Description: Transform any WordPress website into a full-featured Progressive Web App (PWA) with comprehensive OneSignal push notification integration.
 * Version: 1.0.0
 * Author: Oxford Pierpont
 * Author URI: https://oxfordpierpont.com
 * License: Proprietary
 * License URI: https://oxfordpierpont.com/license
 * Text Domain: onesignal-pwa
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * Copyright (c) 2024 The Oxford Pierpont Corporation
 * All rights reserved.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ONESIGNAL_PWA_VERSION', '1.0.0');
define('ONESIGNAL_PWA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ONESIGNAL_PWA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ONESIGNAL_PWA_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('ONESIGNAL_PWA_MIN_PHP_VERSION', '7.4');
define('ONESIGNAL_PWA_MIN_WP_VERSION', '5.8');

/**
 * Main Plugin Class
 */
final class OneSignal_PWA {

    /**
     * Single instance of the class
     *
     * @var OneSignal_PWA
     */
    private static $instance = null;

    /**
     * Database manager instance
     *
     * @var OneSignal_PWA_Database
     */
    public $database;

    /**
     * Settings manager instance
     *
     * @var OneSignal_PWA_Settings
     */
    public $settings;

    /**
     * Manifest manager instance
     *
     * @var OneSignal_PWA_Manifest
     */
    public $manifest;

    /**
     * Service worker manager instance
     *
     * @var OneSignal_PWA_Service_Worker
     */
    public $service_worker;

    /**
     * OneSignal API client instance
     *
     * @var OneSignal_PWA_API_Client
     */
    public $api_client;

    /**
     * Admin interface instance
     *
     * @var OneSignal_PWA_Admin
     */
    public $admin;

    /**
     * Get main instance
     *
     * @return OneSignal_PWA
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->check_requirements();
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Check plugin requirements
     */
    private function check_requirements() {
        // Check PHP version
        if (version_compare(PHP_VERSION, ONESIGNAL_PWA_MIN_PHP_VERSION, '<')) {
            add_action('admin_notices', array($this, 'php_version_notice'));
            return;
        }

        // Check WordPress version
        global $wp_version;
        if (version_compare($wp_version, ONESIGNAL_PWA_MIN_WP_VERSION, '<')) {
            add_action('admin_notices', array($this, 'wp_version_notice'));
            return;
        }

        // Check HTTPS requirement
        if (!is_ssl() && !$this->is_local_environment()) {
            add_action('admin_notices', array($this, 'https_notice'));
        }
    }

    /**
     * Check if running in local environment
     */
    private function is_local_environment() {
        $url = get_site_url();
        return (strpos($url, 'localhost') !== false ||
                strpos($url, '127.0.0.1') !== false ||
                strpos($url, '.local') !== false);
    }

    /**
     * Include required files
     */
    private function includes() {
        // Core includes
        require_once ONESIGNAL_PWA_PLUGIN_DIR . 'includes/class-database.php';
        require_once ONESIGNAL_PWA_PLUGIN_DIR . 'includes/class-settings.php';
        require_once ONESIGNAL_PWA_PLUGIN_DIR . 'includes/class-manifest.php';
        require_once ONESIGNAL_PWA_PLUGIN_DIR . 'includes/class-service-worker.php';
        require_once ONESIGNAL_PWA_PLUGIN_DIR . 'includes/class-api-client.php';
        require_once ONESIGNAL_PWA_PLUGIN_DIR . 'includes/class-encryption.php';
        require_once ONESIGNAL_PWA_PLUGIN_DIR . 'includes/class-icon-processor.php';
        require_once ONESIGNAL_PWA_PLUGIN_DIR . 'includes/class-subscriber.php';
        require_once ONESIGNAL_PWA_PLUGIN_DIR . 'includes/class-notification.php';
        require_once ONESIGNAL_PWA_PLUGIN_DIR . 'includes/class-segment.php';
        require_once ONESIGNAL_PWA_PLUGIN_DIR . 'includes/class-workflow.php';
        require_once ONESIGNAL_PWA_PLUGIN_DIR . 'includes/class-analytics.php';
        require_once ONESIGNAL_PWA_PLUGIN_DIR . 'includes/class-rest-api.php';

        // Integration includes
        require_once ONESIGNAL_PWA_PLUGIN_DIR . 'includes/integrations/class-woocommerce.php';
        require_once ONESIGNAL_PWA_PLUGIN_DIR . 'includes/integrations/class-integration-manager.php';

        // Admin includes
        if (is_admin()) {
            require_once ONESIGNAL_PWA_PLUGIN_DIR . 'admin/class-admin.php';
        }

        // Public includes
        require_once ONESIGNAL_PWA_PLUGIN_DIR . 'public/class-public.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Init action
        add_action('init', array($this, 'init'), 0);
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        // Initialize components
        add_action('plugins_loaded', array($this, 'init_components'));
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        require_once ONESIGNAL_PWA_PLUGIN_DIR . 'includes/class-database.php';
        OneSignal_PWA_Database::create_tables();

        // Set default settings
        $this->set_default_settings();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Set activation flag
        set_transient('onesignal_pwa_activated', true, 30);
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();

        // Clear scheduled events
        wp_clear_scheduled_hook('onesignal_pwa_cleanup');
        wp_clear_scheduled_hook('onesignal_pwa_analytics_sync');
    }

    /**
     * Set default settings
     */
    private function set_default_settings() {
        $defaults = array(
            'app_name' => get_bloginfo('name'),
            'app_short_name' => substr(get_bloginfo('name'), 0, 12),
            'app_description' => get_bloginfo('description'),
            'theme_color' => '#000000',
            'background_color' => '#ffffff',
            'display_mode' => 'standalone',
            'orientation' => 'any',
            'start_url' => '/',
            'scope' => '/',
            'cache_strategy' => 'network_first',
            'cache_version' => '1.0',
            'offline_page' => '',
            'enable_service_worker' => true,
            'enable_push_notifications' => true,
            'enable_install_prompt' => true,
            'install_prompt_delay' => 30,
            'install_prompt_position' => 'bottom',
            'enable_ios_a2hs' => true,
            'ios_a2hs_title' => __('Add to Home Screen', 'onesignal-pwa'),
            'ios_a2hs_message' => __('Tap the share icon and select “Add to Home Screen” to install this app on your iPhone.', 'onesignal-pwa'),
            'ios_a2hs_delay' => 15,
            'version' => ONESIGNAL_PWA_VERSION,
        );

        foreach ($defaults as $key => $value) {
            if (get_option('onesignal_pwa_' . $key) === false) {
                add_option('onesignal_pwa_' . $key, $value);
            }
        }
    }

    /**
     * Initialize plugin
     */
    public function init() {
        do_action('onesignal_pwa_before_init');

        // Register custom post types and taxonomies if needed

        do_action('onesignal_pwa_after_init');
    }

    /**
     * Initialize components
     */
    public function init_components() {
        // Initialize database
        $this->database = new OneSignal_PWA_Database();

        // Initialize settings
        $this->settings = new OneSignal_PWA_Settings();

        // Initialize manifest
        $this->manifest = new OneSignal_PWA_Manifest();

        // Initialize service worker
        $this->service_worker = new OneSignal_PWA_Service_Worker();

        // Initialize API client
        $this->api_client = new OneSignal_PWA_API_Client();

        // Initialize REST API
        new OneSignal_PWA_REST_API();

        // Initialize integrations
        new OneSignal_PWA_Integration_Manager();

        // Initialize admin
        if (is_admin()) {
            $this->admin = new OneSignal_PWA_Admin();
        }

        // Initialize public
        if (!is_admin()) {
            new OneSignal_PWA_Public();
        }

        do_action('onesignal_pwa_components_loaded');
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'onesignal-pwa',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }

    /**
     * PHP version notice
     */
    public function php_version_notice() {
        echo '<div class="error"><p>';
        printf(
            __('OneSignal PWA requires PHP version %s or higher. You are running version %s.', 'onesignal-pwa'),
            ONESIGNAL_PWA_MIN_PHP_VERSION,
            PHP_VERSION
        );
        echo '</p></div>';
    }

    /**
     * WordPress version notice
     */
    public function wp_version_notice() {
        echo '<div class="error"><p>';
        printf(
            __('OneSignal PWA requires WordPress version %s or higher.', 'onesignal-pwa'),
            ONESIGNAL_PWA_MIN_WP_VERSION
        );
        echo '</p></div>';
    }

    /**
     * HTTPS notice
     */
    public function https_notice() {
        echo '<div class="notice notice-warning"><p>';
        _e('OneSignal PWA requires HTTPS to function properly. Please enable SSL on your website.', 'onesignal-pwa');
        echo '</p></div>';
    }
}

/**
 * Get main plugin instance
 *
 * @return OneSignal_PWA
 */
function onesignal_pwa() {
    return OneSignal_PWA::instance();
}

// Initialize plugin
onesignal_pwa();
