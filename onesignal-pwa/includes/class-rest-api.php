<?php
/**
 * REST API Class
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OneSignal PWA REST API Class
 */
class OneSignal_PWA_REST_API {

    /**
     * API namespace
     */
    const NAMESPACE = 'onesignal-pwa/v1';

    /**
     * Constructor
     */
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Status endpoint
        register_rest_route(self::NAMESPACE, '/status', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_status'),
            'permission_callback' => '__return_true'
        ));

        // Settings endpoints
        register_rest_route(self::NAMESPACE, '/settings', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_settings'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));

        register_rest_route(self::NAMESPACE, '/settings', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_settings'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));

        // Manifest endpoint
        register_rest_route(self::NAMESPACE, '/manifest', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_manifest'),
            'permission_callback' => '__return_true'
        ));

        // Subscribers endpoints
        register_rest_route(self::NAMESPACE, '/subscribers', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_subscribers'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));

        register_rest_route(self::NAMESPACE, '/subscribers', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_subscriber'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route(self::NAMESPACE, '/subscribers/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_subscriber'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));

        register_rest_route(self::NAMESPACE, '/subscribers/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_subscriber'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));

        register_rest_route(self::NAMESPACE, '/subscribers/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_subscriber'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));

        // Notifications endpoints
        register_rest_route(self::NAMESPACE, '/notifications', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_notifications'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));

        register_rest_route(self::NAMESPACE, '/notifications', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_notification'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));

        register_rest_route(self::NAMESPACE, '/notifications/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_notification'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));

        register_rest_route(self::NAMESPACE, '/notifications/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_notification'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));

        register_rest_route(self::NAMESPACE, '/notifications/(?P<id>\d+)/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_notification_stats'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));

        register_rest_route(self::NAMESPACE, '/notifications/(?P<id>\d+)/send', array(
            'methods' => 'POST',
            'callback' => array($this, 'send_notification'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));

        // Segments endpoints
        register_rest_route(self::NAMESPACE, '/segments', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_segments'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));

        register_rest_route(self::NAMESPACE, '/segments', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_segment'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));

        register_rest_route(self::NAMESPACE, '/segments/(?P<id>\d+)/size', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_segment_size'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));

        // Analytics endpoints
        register_rest_route(self::NAMESPACE, '/analytics/overview', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_analytics_overview'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));

        register_rest_route(self::NAMESPACE, '/analytics/subscribers', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_analytics_subscribers'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));

        // Events endpoint
        register_rest_route(self::NAMESPACE, '/events', array(
            'methods' => 'POST',
            'callback' => array($this, 'track_event'),
            'permission_callback' => '__return_true'
        ));
    }

    /**
     * Check admin permission
     *
     * @return bool
     */
    public function check_admin_permission() {
        return current_user_can('manage_options');
    }

    /**
     * Get status
     *
     * @return WP_REST_Response
     */
    public function get_status() {
        return new WP_REST_Response(array(
            'success' => true,
            'version' => ONESIGNAL_PWA_VERSION,
            'pwa_configured' => OneSignal_PWA_Settings::is_pwa_configured(),
            'onesignal_configured' => OneSignal_PWA_Settings::is_onesignal_configured()
        ));
    }

    /**
     * Get settings
     *
     * @return WP_REST_Response
     */
    public function get_settings() {
        $settings = OneSignal_PWA_Settings::export_settings();

        return new WP_REST_Response($settings);
    }

    /**
     * Update settings
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update_settings($request) {
        $settings = $request->get_json_params();

        OneSignal_PWA_Settings::import_settings($settings);

        return new WP_REST_Response(array(
            'success' => true,
            'message' => __('Settings updated successfully', 'onesignal-pwa')
        ));
    }

    /**
     * Get manifest
     *
     * @return WP_REST_Response
     */
    public function get_manifest() {
        $manifest = new OneSignal_PWA_Manifest();

        return new WP_REST_Response($manifest->generate_manifest());
    }

    /**
     * Get subscribers
     *
     * @return WP_REST_Response
     */
    public function get_subscribers() {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_subscribers';

        $subscribers = $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC LIMIT 100");

        return new WP_REST_Response($subscribers);
    }

    /**
     * Create subscriber
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function create_subscriber($request) {
        $data = $request->get_json_params();

        $subscriber_id = OneSignal_PWA_Subscriber::save($data);

        if ($subscriber_id) {
            return new WP_REST_Response(array(
                'success' => true,
                'id' => $subscriber_id
            ), 201);
        }

        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Failed to create subscriber', 'onesignal-pwa')
        ), 400);
    }

    /**
     * Get notifications
     *
     * @return WP_REST_Response
     */
    public function get_notifications() {
        $notifications = OneSignal_PWA_Notification::get_all();

        return new WP_REST_Response($notifications);
    }

    /**
     * Create notification
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function create_notification($request) {
        $data = $request->get_json_params();

        $notification_id = OneSignal_PWA_Notification::create($data);

        if ($notification_id) {
            // Send immediately if requested
            if (isset($data['send_now']) && $data['send_now']) {
                OneSignal_PWA_Notification::send($notification_id);
            }

            return new WP_REST_Response(array(
                'success' => true,
                'id' => $notification_id
            ), 201);
        }

        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Failed to create notification', 'onesignal-pwa')
        ), 400);
    }

    /**
     * Send notification
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function send_notification($request) {
        $id = $request->get_param('id');

        $result = OneSignal_PWA_Notification::send($id);

        if (is_wp_error($result)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $result->get_error_message()
            ), 400);
        }

        return new WP_REST_Response(array(
            'success' => true,
            'message' => __('Notification sent successfully', 'onesignal-pwa')
        ));
    }

    /**
     * Get notification stats
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_notification_stats($request) {
        $id = $request->get_param('id');

        $stats = OneSignal_PWA_Notification::get_stats($id);

        if (is_wp_error($stats)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $stats->get_error_message()
            ), 404);
        }

        return new WP_REST_Response($stats);
    }

    /**
     * Get segments
     *
     * @return WP_REST_Response
     */
    public function get_segments() {
        $segments = OneSignal_PWA_Segment::get_all();

        return new WP_REST_Response($segments);
    }

    /**
     * Create segment
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function create_segment($request) {
        $data = $request->get_json_params();

        $segment_id = OneSignal_PWA_Segment::create($data);

        if ($segment_id) {
            return new WP_REST_Response(array(
                'success' => true,
                'id' => $segment_id
            ), 201);
        }

        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Failed to create segment', 'onesignal-pwa')
        ), 400);
    }

    /**
     * Get segment size
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_segment_size($request) {
        $id = $request->get_param('id');

        $size = OneSignal_PWA_Segment::calculate_size($id);

        return new WP_REST_Response(array(
            'size' => $size
        ));
    }

    /**
     * Get analytics overview
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_analytics_overview($request) {
        $days = $request->get_param('days') ?: 30;

        $stats = OneSignal_PWA_Analytics::get_overview_stats($days);

        return new WP_REST_Response($stats);
    }

    /**
     * Get analytics subscribers
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_analytics_subscribers($request) {
        $days = $request->get_param('days') ?: 30;

        $stats = OneSignal_PWA_Analytics::get_subscriber_growth($days);

        return new WP_REST_Response($stats);
    }

    /**
     * Track event
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function track_event($request) {
        $data = $request->get_json_params();

        $event_id = OneSignal_PWA_Analytics::track_event($data['event_type'], $data);

        if ($event_id) {
            return new WP_REST_Response(array(
                'success' => true,
                'id' => $event_id
            ), 201);
        }

        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Failed to track event', 'onesignal-pwa')
        ), 400);
    }

    /**
     * Get subscriber
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_subscriber($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_subscribers';
        $id = $request->get_param('id');

        $subscriber = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $id
        ));

        if ($subscriber) {
            return new WP_REST_Response($subscriber);
        }

        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Subscriber not found', 'onesignal-pwa')
        ), 404);
    }

    /**
     * Update subscriber
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update_subscriber($request) {
        $id = $request->get_param('id');
        $data = $request->get_json_params();

        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_subscribers';

        $result = $wpdb->update($table, $data, array('id' => $id));

        if ($result !== false) {
            return new WP_REST_Response(array(
                'success' => true,
                'message' => __('Subscriber updated successfully', 'onesignal-pwa')
            ));
        }

        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Failed to update subscriber', 'onesignal-pwa')
        ), 400);
    }

    /**
     * Delete subscriber
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function delete_subscriber($request) {
        $id = $request->get_param('id');

        $result = OneSignal_PWA_Subscriber::delete($id);

        if ($result) {
            return new WP_REST_Response(array(
                'success' => true,
                'message' => __('Subscriber deleted successfully', 'onesignal-pwa')
            ));
        }

        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Failed to delete subscriber', 'onesignal-pwa')
        ), 400);
    }

    /**
     * Get notification
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_notification($request) {
        $id = $request->get_param('id');

        $notification = OneSignal_PWA_Notification::get($id);

        if ($notification) {
            return new WP_REST_Response($notification);
        }

        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Notification not found', 'onesignal-pwa')
        ), 404);
    }

    /**
     * Delete notification
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function delete_notification($request) {
        $id = $request->get_param('id');

        $result = OneSignal_PWA_Notification::delete($id);

        if ($result) {
            return new WP_REST_Response(array(
                'success' => true,
                'message' => __('Notification deleted successfully', 'onesignal-pwa')
            ));
        }

        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Failed to delete notification', 'onesignal-pwa')
        ), 400);
    }
}
