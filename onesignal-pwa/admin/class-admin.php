<?php
/**
 * Admin Interface Class
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OneSignal PWA Admin Class
 */
class OneSignal_PWA_Admin {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_notices', array($this, 'admin_notices'));
        add_filter('plugin_action_links_' . ONESIGNAL_PWA_PLUGIN_BASENAME, array($this, 'plugin_action_links'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('OneSignal PWA', 'onesignal-pwa'),
            __('OneSignal PWA', 'onesignal-pwa'),
            'manage_options',
            'onesignal-pwa',
            array($this, 'dashboard_page'),
            'dashicons-smartphone',
            30
        );

        add_submenu_page(
            'onesignal-pwa',
            __('Dashboard', 'onesignal-pwa'),
            __('Dashboard', 'onesignal-pwa'),
            'manage_options',
            'onesignal-pwa',
            array($this, 'dashboard_page')
        );

        add_submenu_page(
            'onesignal-pwa',
            __('Send Notification', 'onesignal-pwa'),
            __('Send Notification', 'onesignal-pwa'),
            'manage_options',
            'onesignal-pwa-send',
            array($this, 'send_notification_page')
        );

        add_submenu_page(
            'onesignal-pwa',
            __('Notifications', 'onesignal-pwa'),
            __('Notifications', 'onesignal-pwa'),
            'manage_options',
            'onesignal-pwa-notifications',
            array($this, 'notifications_page')
        );

        add_submenu_page(
            'onesignal-pwa',
            __('Subscribers', 'onesignal-pwa'),
            __('Subscribers', 'onesignal-pwa'),
            'manage_options',
            'onesignal-pwa-subscribers',
            array($this, 'subscribers_page')
        );

        add_submenu_page(
            'onesignal-pwa',
            __('Segments', 'onesignal-pwa'),
            __('Segments', 'onesignal-pwa'),
            'manage_options',
            'onesignal-pwa-segments',
            array($this, 'segments_page')
        );

        add_submenu_page(
            'onesignal-pwa',
            __('Workflows', 'onesignal-pwa'),
            __('Workflows', 'onesignal-pwa'),
            'manage_options',
            'onesignal-pwa-workflows',
            array($this, 'workflows_page')
        );

        add_submenu_page(
            'onesignal-pwa',
            __('Analytics', 'onesignal-pwa'),
            __('Analytics', 'onesignal-pwa'),
            'manage_options',
            'onesignal-pwa-analytics',
            array($this, 'analytics_page')
        );

        add_submenu_page(
            'onesignal-pwa',
            __('PWA Settings', 'onesignal-pwa'),
            __('PWA Settings', 'onesignal-pwa'),
            'manage_options',
            'onesignal-pwa-pwa-settings',
            array($this, 'pwa_settings_page')
        );

        add_submenu_page(
            'onesignal-pwa',
            __('Settings', 'onesignal-pwa'),
            __('Settings', 'onesignal-pwa'),
            'manage_options',
            'onesignal-pwa-settings',
            array($this, 'settings_page')
        );
    }

    /**
     * Enqueue admin scripts and styles
     *
     * @param string $hook
     */
    public function enqueue_scripts($hook) {
        if (strpos($hook, 'onesignal-pwa') === false) {
            return;
        }

        wp_enqueue_style(
            'onesignal-pwa-admin',
            ONESIGNAL_PWA_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            ONESIGNAL_PWA_VERSION
        );

        wp_enqueue_script(
            'onesignal-pwa-admin',
            ONESIGNAL_PWA_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            ONESIGNAL_PWA_VERSION,
            true
        );

        wp_localize_script('onesignal-pwa-admin', 'onesignalPWA', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('onesignal-pwa/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'strings' => array(
                'saving' => __('Saving...', 'onesignal-pwa'),
                'saved' => __('Saved!', 'onesignal-pwa'),
                'error' => __('Error', 'onesignal-pwa'),
            )
        ));
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // Settings are registered in OneSignal_PWA_Settings class
    }

    /**
     * Add plugin action links
     *
     * @param array $links
     * @return array
     */
    public function plugin_action_links($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=onesignal-pwa-settings') . '">' . __('Settings', 'onesignal-pwa') . '</a>';
        array_unshift($links, $settings_link);

        return $links;
    }

    /**
     * Dashboard page
     */
    public function dashboard_page() {
        $stats = OneSignal_PWA_Analytics::get_overview_stats(30);
        $recent_notifications = OneSignal_PWA_Notification::get_all(array('limit' => 5));
        $setup_completion = OneSignal_PWA_Settings::get_setup_completion();

        include ONESIGNAL_PWA_PLUGIN_DIR . 'templates/admin/dashboard.php';
    }

    /**
     * Send notification page
     */
    public function send_notification_page() {
        if (isset($_POST['send_notification']) && check_admin_referer('send_notification')) {
            $this->handle_send_notification();
        }

        $segments = OneSignal_PWA_Segment::get_all();
        $templates = $this->get_templates();

        include ONESIGNAL_PWA_PLUGIN_DIR . 'templates/admin/send-notification.php';
    }

    /**
     * Notifications page
     */
    public function notifications_page() {
        $notifications = OneSignal_PWA_Notification::get_all(array('limit' => 50));

        include ONESIGNAL_PWA_PLUGIN_DIR . 'templates/admin/notifications.php';
    }

    /**
     * Subscribers page
     */
    public function subscribers_page() {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_subscribers';
        $subscribers = $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC LIMIT 100");

        include ONESIGNAL_PWA_PLUGIN_DIR . 'templates/admin/subscribers.php';
    }

    /**
     * Segments page
     */
    public function segments_page() {
        if (isset($_POST['create_segment']) && check_admin_referer('create_segment')) {
            $this->handle_create_segment();
        }

        $segments = OneSignal_PWA_Segment::get_all();

        include ONESIGNAL_PWA_PLUGIN_DIR . 'templates/admin/segments.php';
    }

    /**
     * Workflows page
     */
    public function workflows_page() {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_workflows';
        $workflows = $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC");

        include ONESIGNAL_PWA_PLUGIN_DIR . 'templates/admin/workflows.php';
    }

    /**
     * Analytics page
     */
    public function analytics_page() {
        $stats = OneSignal_PWA_Analytics::get_overview_stats(30);
        $growth = OneSignal_PWA_Analytics::get_subscriber_growth(30);
        $performance = OneSignal_PWA_Analytics::get_notification_performance(10);
        $device_breakdown = OneSignal_PWA_Analytics::get_device_breakdown();
        $browser_breakdown = OneSignal_PWA_Analytics::get_browser_breakdown();

        include ONESIGNAL_PWA_PLUGIN_DIR . 'templates/admin/analytics.php';
    }

    /**
     * PWA settings page
     */
    public function pwa_settings_page() {
        if (isset($_POST['save_pwa_settings']) && check_admin_referer('save_pwa_settings')) {
            $this->handle_save_pwa_settings();
        }

        if (isset($_POST['upload_icon']) && check_admin_referer('upload_icon')) {
            $this->handle_upload_icon();
        }

        $settings = OneSignal_PWA_Settings::get_pwa_settings();
        $icons = OneSignal_PWA_Icon_Processor::get_all_icons();

        include ONESIGNAL_PWA_PLUGIN_DIR . 'templates/admin/pwa-settings.php';
    }

    /**
     * Settings page
     */
    public function settings_page() {
        if (isset($_POST['save_settings']) && check_admin_referer('save_settings')) {
            $this->handle_save_settings();
        }

        $credentials = OneSignal_PWA_Settings::get_api_credentials();

        include ONESIGNAL_PWA_PLUGIN_DIR . 'templates/admin/settings.php';
    }

    /**
     * Handle send notification
     */
    private function handle_send_notification() {
        $data = array(
            'title' => sanitize_text_field($_POST['title']),
            'message' => sanitize_textarea_field($_POST['message']),
            'url' => esc_url_raw($_POST['url']),
            'segments' => isset($_POST['segments']) ? array_map('sanitize_text_field', $_POST['segments']) : array('All'),
        );

        $notification_id = OneSignal_PWA_Notification::create($data);

        if ($notification_id) {
            if (isset($_POST['send_now'])) {
                OneSignal_PWA_Notification::send($notification_id);
                add_settings_error('onesignal_pwa', 'notification_sent', __('Notification sent successfully!', 'onesignal-pwa'), 'success');
            } else {
                add_settings_error('onesignal_pwa', 'notification_saved', __('Notification saved as draft.', 'onesignal-pwa'), 'success');
            }
        }
    }

    /**
     * Handle create segment
     */
    private function handle_create_segment() {
        $data = array(
            'name' => sanitize_text_field($_POST['segment_name']),
            'description' => sanitize_textarea_field($_POST['segment_description']),
            'rules' => json_decode(stripslashes($_POST['segment_rules']), true)
        );

        OneSignal_PWA_Segment::create($data);

        add_settings_error('onesignal_pwa', 'segment_created', __('Segment created successfully!', 'onesignal-pwa'), 'success');
    }

    /**
     * Handle save PWA settings
     */
    private function handle_save_pwa_settings() {
        OneSignal_PWA_Settings::set('app_name', sanitize_text_field($_POST['app_name']));
        OneSignal_PWA_Settings::set('app_short_name', sanitize_text_field($_POST['app_short_name']));
        OneSignal_PWA_Settings::set('app_description', sanitize_textarea_field($_POST['app_description']));
        OneSignal_PWA_Settings::set('theme_color', sanitize_hex_color($_POST['theme_color']));
        OneSignal_PWA_Settings::set('background_color', sanitize_hex_color($_POST['background_color']));
        OneSignal_PWA_Settings::set('display_mode', sanitize_text_field($_POST['display_mode']));
        OneSignal_PWA_Settings::set('orientation', sanitize_text_field($_POST['orientation']));

        add_settings_error('onesignal_pwa', 'settings_saved', __('PWA settings saved successfully!', 'onesignal-pwa'), 'success');
    }

    /**
     * Handle upload icon
     */
    private function handle_upload_icon() {
        if (isset($_FILES['icon']) && $_FILES['icon']['error'] === UPLOAD_ERR_OK) {
            $result = OneSignal_PWA_Icon_Processor::upload_icon($_FILES['icon']);

            if (is_wp_error($result)) {
                add_settings_error('onesignal_pwa', 'icon_error', $result->get_error_message(), 'error');
            } else {
                add_settings_error('onesignal_pwa', 'icon_uploaded', __('Icon uploaded and processed successfully!', 'onesignal-pwa'), 'success');
            }
        }
    }

    /**
     * Handle save settings
     */
    private function handle_save_settings() {
        $credentials = array(
            'app_id' => sanitize_text_field($_POST['app_id']),
            'rest_api_key' => sanitize_text_field($_POST['rest_api_key']),
            'user_auth_key' => sanitize_text_field($_POST['user_auth_key']),
            'safari_web_id' => sanitize_text_field($_POST['safari_web_id']),
        );

        OneSignal_PWA_Settings::set_api_credentials($credentials);

        // Test connection
        $api_client = new OneSignal_PWA_API_Client();
        $test = $api_client->test_connection();

        if (is_wp_error($test)) {
            add_settings_error('onesignal_pwa', 'api_error', __('Settings saved, but API connection failed: ', 'onesignal-pwa') . $test->get_error_message(), 'warning');
        } else {
            add_settings_error('onesignal_pwa', 'settings_saved', __('Settings saved and API connection verified!', 'onesignal-pwa'), 'success');
        }
    }

    /**
     * Get templates
     */
    private function get_templates() {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_templates';

        return $wpdb->get_results("SELECT * FROM {$table} WHERE type = 'notification'");
    }

    /**
     * Admin notices
     */
    public function admin_notices() {
        settings_errors('onesignal_pwa');

        // Show setup notice if not configured
        if (!OneSignal_PWA_Settings::is_onesignal_configured() && isset($_GET['page']) && strpos($_GET['page'], 'onesignal-pwa') !== false) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <?php _e('Please configure your OneSignal API credentials to start using the plugin.', 'onesignal-pwa'); ?>
                    <a href="<?php echo admin_url('admin.php?page=onesignal-pwa-settings'); ?>"><?php _e('Go to Settings', 'onesignal-pwa'); ?></a>
                </p>
            </div>
            <?php
        }
    }
}
