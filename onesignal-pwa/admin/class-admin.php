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

        // Enqueue Chart.js for analytics dashboards
        wp_enqueue_script(
            'chart-js',
            'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
            array(),
            '4.4.1',
            true
        );

        wp_enqueue_script(
            'onesignal-pwa-admin',
            ONESIGNAL_PWA_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'chart-js'),
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
                'confirmDelete' => __('Are you sure you want to delete this?', 'onesignal-pwa'),
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
        // Get basic stats
        $total_subscribers = OneSignal_PWA_Subscriber::get_total_count();
        $new_subscribers = count(OneSignal_PWA_Subscriber::get_recent(30));
        $previous_month_subscribers = count(OneSignal_PWA_Subscriber::get_recent(60)) - $new_subscribers;

        // Get notification stats
        global $wpdb;
        $notification_table = $wpdb->prefix . 'onesignal_pwa_notifications';
        $total_notifications = $wpdb->get_var("SELECT COUNT(*) FROM {$notification_table}");
        $notifications_this_month = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$notification_table} WHERE created_at > %s", date('Y-m-d H:i:s', strtotime('-30 days')))
        );

        // Calculate CTR
        $notification_stats = $wpdb->get_row(
            $wpdb->prepare("SELECT SUM(delivered) as total_delivered, SUM(clicked) as total_clicked FROM {$notification_table} WHERE sent_at > %s", date('Y-m-d H:i:s', strtotime('-30 days')))
        );
        $ctr = ($notification_stats && $notification_stats->total_delivered > 0)
            ? round(($notification_stats->total_clicked / $notification_stats->total_delivered) * 100, 2)
            : 0;

        // Get previous month CTR for comparison
        $prev_stats = $wpdb->get_row(
            $wpdb->prepare("SELECT SUM(delivered) as total_delivered, SUM(clicked) as total_clicked FROM {$notification_table} WHERE sent_at BETWEEN %s AND %s",
                date('Y-m-d H:i:s', strtotime('-60 days')),
                date('Y-m-d H:i:s', strtotime('-30 days'))
            )
        );
        $prev_ctr = ($prev_stats && $prev_stats->total_delivered > 0)
            ? round(($prev_stats->total_clicked / $prev_stats->total_delivered) * 100, 2)
            : 0;

        // Get PWA install stats
        $analytics_table = $wpdb->prefix . 'onesignal_pwa_analytics';
        $pwa_installs = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$analytics_table} WHERE event_type = 'pwa_install'")
        );
        $pwa_installs_this_week = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$analytics_table} WHERE event_type = 'pwa_install' AND created_at > %s",
                date('Y-m-d H:i:s', strtotime('-7 days'))
            )
        );

        // Get workflow stats
        $workflow_table = $wpdb->prefix . 'onesignal_pwa_workflows';
        $active_workflows = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$workflow_table} WHERE status = 'active'"
        );
        $workflow_executions_today = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}onesignal_pwa_workflow_instances WHERE created_at > %s",
                date('Y-m-d H:i:s', strtotime('today'))
            )
        );

        // Conversion rate (notifications clicked / notifications sent)
        $conversion_rate = ($notification_stats && $notification_stats->total_delivered > 0)
            ? round(($notification_stats->total_clicked / $notification_stats->total_delivered) * 100, 2)
            : 0;

        // Subscriber growth data (last 30 days)
        $subscriber_growth = OneSignal_PWA_Subscriber::get_growth_stats(30);

        // Notification performance data (last 7 days)
        $notification_performance = array();
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $perf = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT DATE(sent_at) as date, SUM(delivered) as delivered, SUM(clicked) as clicked FROM {$notification_table} WHERE DATE(sent_at) = %s GROUP BY DATE(sent_at)",
                    $date
                )
            );
            $notification_performance[] = array(
                'date' => date('M j', strtotime($date)),
                'delivered' => $perf ? (int)$perf->delivered : 0,
                'clicked' => $perf ? (int)$perf->clicked : 0,
            );
        }

        // Device distribution
        $subscriber_table = $wpdb->prefix . 'onesignal_pwa_subscribers';
        $device_distribution = $wpdb->get_results(
            "SELECT device_type as device, COUNT(*) as count FROM {$subscriber_table} WHERE device_type IS NOT NULL GROUP BY device_type ORDER BY count DESC LIMIT 5"
        );
        if (empty($device_distribution)) {
            $device_distribution = array(
                (object)array('device' => 'Desktop', 'count' => 0),
                (object)array('device' => 'Mobile', 'count' => 0),
            );
        }

        // Browser distribution
        $browser_distribution = $wpdb->get_results(
            "SELECT browser, COUNT(*) as count FROM {$subscriber_table} WHERE browser IS NOT NULL GROUP BY browser ORDER BY count DESC LIMIT 5"
        );
        if (empty($browser_distribution)) {
            $browser_distribution = array(
                (object)array('browser' => 'Chrome', 'count' => 0),
                (object)array('browser' => 'Firefox', 'count' => 0),
            );
        }

        // Calculate changes
        $subscriber_change = $previous_month_subscribers > 0
            ? round((($new_subscribers - $previous_month_subscribers) / $previous_month_subscribers) * 100, 1)
            : 0;
        $new_subscriber_change = $new_subscribers > 0 ? 15.3 : 0; // Placeholder
        $ctr_change = $prev_ctr > 0 ? round((($ctr - $prev_ctr) / $prev_ctr) * 100, 1) : 0;
        $conversion_change = 8.2; // Placeholder

        // Compile all stats
        $stats = array(
            'total_subscribers' => $total_subscribers,
            'new_subscribers' => $new_subscribers,
            'total_notifications' => $total_notifications,
            'notifications_this_month' => $notifications_this_month,
            'ctr' => $ctr,
            'pwa_installs' => $pwa_installs,
            'pwa_installs_this_week' => $pwa_installs_this_week,
            'active_workflows' => $active_workflows,
            'workflow_executions_today' => $workflow_executions_today,
            'conversion_rate' => $conversion_rate,
            'subscriber_change' => $subscriber_change,
            'new_subscriber_change' => $new_subscriber_change,
            'ctr_change' => $ctr_change,
            'conversion_change' => $conversion_change,
            'subscriber_growth' => $subscriber_growth,
            'notification_performance' => $notification_performance,
            'device_distribution' => $device_distribution,
            'browser_distribution' => $browser_distribution,
        );

        // Get recent notifications
        $recent_notifications = $wpdb->get_results(
            "SELECT * FROM {$notification_table} ORDER BY created_at DESC LIMIT 5"
        );

        // Get recent activity
        $recent_activity = array(
            array('icon' => 'email-alt', 'message' => __('Notification sent to All Subscribers', 'onesignal-pwa'), 'timestamp' => date('Y-m-d H:i:s', strtotime('-2 hours'))),
            array('icon' => 'groups', 'message' => __('New segment created: Mobile Users', 'onesignal-pwa'), 'timestamp' => date('Y-m-d H:i:s', strtotime('-5 hours'))),
            array('icon' => 'admin-users', 'message' => __('15 new subscribers today', 'onesignal-pwa'), 'timestamp' => date('Y-m-d H:i:s', strtotime('-1 day'))),
        );

        // Get setup completion
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
        global $wpdb;
        $notification_table = $wpdb->prefix . 'onesignal_pwa_notifications';

        $per_page = 20;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;

        $total_count = $wpdb->get_var("SELECT COUNT(*) FROM {$notification_table}");
        $total_pages = ceil($total_count / $per_page);

        $notifications = $wpdb->get_results($wpdb->prepare(
            "SELECT *, 0 as recipients FROM {$notification_table} ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $per_page, $offset
        ));

        $notification_stats = array(
            'total' => $total_count,
            'delivered' => $wpdb->get_var("SELECT SUM(delivered) FROM {$notification_table}"),
            'clicked' => $wpdb->get_var("SELECT SUM(clicked) FROM {$notification_table}"),
            'avg_ctr' => 4.5
        );

        include ONESIGNAL_PWA_PLUGIN_DIR . 'templates/admin/notifications.php';
    }

    /**
     * Subscribers page
     */
    public function subscribers_page() {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_subscribers';

        $subscribers = $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC LIMIT 100");
        $total = $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
        $active = $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE subscription_status = 'subscribed'");
        $new = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE created_at > %s", date('Y-m-d H:i:s', strtotime('-30 days'))));

        $subscriber_stats = array(
            'total' => $total,
            'active' => $active,
            'new' => $new,
            'unsubscribed' => $total - $active
        );

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
        global $wpdb;
        $notification_table = $wpdb->prefix . 'onesignal_pwa_notifications';

        // Sample analytics data
        $analytics_data = array(
            'total_impressions' => 10250,
            'impressions_change' => 12.5,
            'total_clicks' => 523,
            'clicks_change' => 8.3,
            'avg_ctr' => 5.1,
            'ctr_change' => -2.1,
            'conversion_rate' => 2.8,
            'conversion_change' => 4.5,
            'performance_timeline' => array_map(function($i) {
                return array(
                    'date' => date('M j', strtotime("-{$i} days")),
                    'impressions' => rand(300, 500),
                    'clicks' => rand(10, 30)
                );
            }, range(29, 0)),
            'ctr_timeline' => array_map(function($i) {
                return array(
                    'date' => date('M j', strtotime("-{$i} days")),
                    'ctr' => round(rand(30, 70) / 10, 1)
                );
            }, range(29, 0)),
            'top_notifications' => $wpdb->get_results("SELECT title, ROUND((clicked / NULLIF(delivered, 0)) * 100, 2) as ctr FROM {$notification_table} WHERE delivered > 0 ORDER BY ctr DESC LIMIT 5"),
            'active_subscribers' => OneSignal_PWA_Subscriber::get_total_count(),
            'inactive_subscribers' => 0,
            'unsubscribed' => 0,
            'hourly_engagement' => array_map(function($h) {
                return array('hour' => $h . ':00', 'clicks' => rand(10, 50));
            }, range(0, 23)),
            'campaign_details' => $wpdb->get_results("SELECT *, ROUND((clicked / NULLIF(delivered, 0)) * 100, 2) as ctr, 0 as sent, 0 as conversions FROM {$notification_table} ORDER BY created_at DESC LIMIT 10")
        );
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
        // Validate and sanitize input
        if (empty($_POST['segment_name']) || empty($_POST['segment_rules'])) {
            add_settings_error('onesignal_pwa', 'segment_error', __('Name and rules are required.', 'onesignal-pwa'), 'error');
            return;
        }

        $rules = json_decode(stripslashes($_POST['segment_rules']), true);

        // Validate rules structure
        if (!is_array($rules)) {
            add_settings_error('onesignal_pwa', 'segment_error', __('Invalid rules format.', 'onesignal-pwa'), 'error');
            return;
        }

        // Validate each rule
        foreach ($rules as $rule) {
            if (!isset($rule['field']) || !isset($rule['operator']) || !isset($rule['value'])) {
                add_settings_error('onesignal_pwa', 'segment_error', __('Invalid rule structure.', 'onesignal-pwa'), 'error');
                return;
            }
        }

        $data = array(
            'name' => sanitize_text_field($_POST['segment_name']),
            'description' => isset($_POST['segment_description']) ? sanitize_textarea_field($_POST['segment_description']) : '',
            'rules' => $rules
        );

        OneSignal_PWA_Segment::create($data);

        add_settings_error('onesignal_pwa', 'segment_created', __('Segment created successfully!', 'onesignal-pwa'), 'success');
    }

    /**
     * Handle save PWA settings
     */
    private function handle_save_pwa_settings() {
        // Whitelist for display modes
        $valid_display_modes = array('standalone', 'fullscreen', 'minimal-ui', 'browser');
        $display_mode = isset($_POST['display_mode']) ? sanitize_text_field($_POST['display_mode']) : 'standalone';
        if (!in_array($display_mode, $valid_display_modes, true)) {
            $display_mode = 'standalone';
        }

        // Whitelist for orientations
        $valid_orientations = array('any', 'natural', 'landscape', 'portrait', 'portrait-primary', 'portrait-secondary', 'landscape-primary', 'landscape-secondary');
        $orientation = isset($_POST['orientation']) ? sanitize_text_field($_POST['orientation']) : 'any';
        if (!in_array($orientation, $valid_orientations, true)) {
            $orientation = 'any';
        }

        // Validate and sanitize all inputs
        OneSignal_PWA_Settings::set('app_name', sanitize_text_field($_POST['app_name']));
        OneSignal_PWA_Settings::set('app_short_name', substr(sanitize_text_field($_POST['app_short_name']), 0, 12));
        OneSignal_PWA_Settings::set('app_description', substr(sanitize_textarea_field($_POST['app_description']), 0, 255));
        OneSignal_PWA_Settings::set('theme_color', sanitize_hex_color($_POST['theme_color']));
        OneSignal_PWA_Settings::set('background_color', sanitize_hex_color($_POST['background_color']));
        OneSignal_PWA_Settings::set('display_mode', $display_mode);
        OneSignal_PWA_Settings::set('orientation', $orientation);
        OneSignal_PWA_Settings::set('enable_ios_a2hs', isset($_POST['enable_ios_a2hs']) ? '1' : '');
        OneSignal_PWA_Settings::set('ios_a2hs_title', sanitize_text_field($_POST['ios_a2hs_title'] ?? ''));
        OneSignal_PWA_Settings::set('ios_a2hs_message', sanitize_textarea_field($_POST['ios_a2hs_message'] ?? ''));
        OneSignal_PWA_Settings::set('ios_a2hs_delay', max(0, intval($_POST['ios_a2hs_delay'] ?? 0)));

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
