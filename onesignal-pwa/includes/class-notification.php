<?php
/**
 * Notification Management Class
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OneSignal PWA Notification Class
 */
class OneSignal_PWA_Notification {

    /**
     * Create notification
     *
     * @param array $data
     * @return int|WP_Error
     */
    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_notifications';

        $notification_data = array(
            'title' => sanitize_text_field($data['title']),
            'message' => sanitize_textarea_field($data['message']),
            'url' => isset($data['url']) ? esc_url_raw($data['url']) : null,
            'icon_url' => isset($data['icon_url']) ? esc_url_raw($data['icon_url']) : null,
            'large_image_url' => isset($data['large_image_url']) ? esc_url_raw($data['large_image_url']) : null,
            'action_buttons' => isset($data['action_buttons']) ? json_encode($data['action_buttons']) : null,
            'segments' => isset($data['segments']) ? json_encode($data['segments']) : null,
            'filters' => isset($data['filters']) ? json_encode($data['filters']) : null,
            'custom_data' => isset($data['custom_data']) ? json_encode($data['custom_data']) : null,
            'status' => isset($data['status']) ? $data['status'] : 'draft',
            'scheduled_at' => isset($data['scheduled_at']) ? $data['scheduled_at'] : null,
            'metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null,
            'created_by' => get_current_user_id(),
        );

        $wpdb->insert($table, $notification_data);

        return $wpdb->insert_id;
    }

    /**
     * Update notification
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public static function update($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_notifications';

        return $wpdb->update($table, $data, array('id' => $id));
    }

    /**
     * Get notification
     *
     * @param int $id
     * @return object|null
     */
    public static function get($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_notifications';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $id
        ));
    }

    /**
     * Delete notification
     *
     * @param int $id
     * @return bool
     */
    public static function delete($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_notifications';

        return $wpdb->delete($table, array('id' => $id));
    }

    /**
     * Send notification
     *
     * @param int $id
     * @return bool|WP_Error
     */
    public static function send($id) {
        $notification = self::get($id);

        if (!$notification) {
            return new WP_Error('not_found', __('Notification not found', 'onesignal-pwa'));
        }

        $api_client = new OneSignal_PWA_API_Client();

        $data = array(
            'title' => $notification->title,
            'message' => $notification->message,
            'url' => $notification->url,
            'icon' => $notification->icon_url,
            'large_image' => $notification->large_image_url,
            'buttons' => json_decode($notification->action_buttons, true),
            'segments' => json_decode($notification->segments, true),
            'filters' => json_decode($notification->filters, true),
            'custom_data' => json_decode($notification->custom_data, true),
        );

        $response = $api_client->create_notification($data);

        if (is_wp_error($response)) {
            self::update($id, array(
                'status' => 'failed',
                'metadata' => json_encode(array('error' => $response->get_error_message()))
            ));
            return $response;
        }

        // Update notification record
        self::update($id, array(
            'onesignal_id' => $response['id'],
            'status' => 'sent',
            'sent_at' => current_time('mysql'),
            'delivered' => isset($response['recipients']) ? $response['recipients'] : 0
        ));

        do_action('onesignal_pwa_notification_sent', $id, $response);

        return true;
    }

    /**
     * Schedule notification
     *
     * @param int $id
     * @param string $datetime
     * @return bool
     */
    public static function schedule($id, $datetime) {
        return self::update($id, array(
            'scheduled_at' => $datetime,
            'status' => 'scheduled'
        ));
    }

    /**
     * Get all notifications
     *
     * @param array $args
     * @return array
     */
    public static function get_all($args = array()) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_notifications';

        $defaults = array(
            'limit' => 20,
            'offset' => 0,
            'status' => null,
            'order_by' => 'created_at',
            'order' => 'DESC'
        );

        $args = wp_parse_args($args, $defaults);

        $where = '1=1';
        $where_values = array();

        if ($args['status']) {
            $where .= ' AND status = %s';
            $where_values[] = $args['status'];
        }

        $query = "SELECT * FROM {$table} WHERE {$where} ORDER BY {$args['order_by']} {$args['order']} LIMIT %d OFFSET %d";
        $where_values[] = $args['limit'];
        $where_values[] = $args['offset'];

        return $wpdb->get_results($wpdb->prepare($query, $where_values));
    }

    /**
     * Get notification stats
     *
     * @param int $id
     * @return array|WP_Error
     */
    public static function get_stats($id) {
        $notification = self::get($id);

        if (!$notification || empty($notification->onesignal_id)) {
            return new WP_Error('not_found', __('Notification not found', 'onesignal-pwa'));
        }

        $api_client = new OneSignal_PWA_API_Client();
        $response = $api_client->get_notification($notification->onesignal_id);

        if (is_wp_error($response)) {
            return $response;
        }

        // Update local stats
        if (isset($response['delivered'])) {
            self::update($id, array(
                'delivered' => $response['delivered'],
                'clicked' => isset($response['converted']) ? $response['converted'] : 0,
                'errored' => isset($response['errored']) ? $response['errored'] : 0
            ));
        }

        return $response;
    }

    /**
     * Process scheduled notifications
     */
    public static function process_scheduled() {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_notifications';

        $notifications = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE status = 'scheduled' AND scheduled_at <= %s",
                current_time('mysql')
            )
        );

        foreach ($notifications as $notification) {
            self::send($notification->id);
        }
    }

    /**
     * Get notification template
     *
     * @param string $template_name
     * @return array|null
     */
    public static function get_template($template_name) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_templates';

        $template = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE name = %s AND type = 'notification'",
            $template_name
        ));

        if ($template) {
            return json_decode($template->template_data, true);
        }

        return null;
    }

    /**
     * Apply template
     *
     * @param array $data
     * @param string $template_name
     * @return array
     */
    public static function apply_template($data, $template_name) {
        $template = self::get_template($template_name);

        if (!$template) {
            return $data;
        }

        // Merge template with data
        return array_merge($template, $data);
    }

    /**
     * Replace template variables
     *
     * @param string $content
     * @param array $variables
     * @return string
     */
    public static function replace_variables($content, $variables = array()) {
        $defaults = array(
            'site_name' => get_bloginfo('name'),
            'site_url' => get_site_url(),
            'home_url' => home_url(),
            'user_name' => wp_get_current_user()->display_name,
        );

        $variables = array_merge($defaults, $variables);

        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }

        return $content;
    }
}
