<?php
/**
 * Analytics Tracking Class
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OneSignal PWA Analytics Class
 */
class OneSignal_PWA_Analytics {

    /**
     * Track event
     *
     * @param string $event_type
     * @param array $data
     * @return int|false
     */
    public static function track_event($event_type, $data = array()) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_analytics';

        $event_data = array(
            'event_type' => sanitize_text_field($event_type),
            'event_data' => json_encode($data),
            'subscriber_id' => isset($data['subscriber_id']) ? $data['subscriber_id'] : null,
            'notification_id' => isset($data['notification_id']) ? $data['notification_id'] : null,
            'workflow_id' => isset($data['workflow_id']) ? $data['workflow_id'] : null,
            'session_id' => isset($data['session_id']) ? $data['session_id'] : null,
            'page_url' => isset($data['page_url']) ? esc_url_raw($data['page_url']) : null,
            'referrer_url' => isset($data['referrer_url']) ? esc_url_raw($data['referrer_url']) : null,
            'device_type' => isset($data['device_type']) ? $data['device_type'] : null,
            'browser' => isset($data['browser']) ? $data['browser'] : null,
            'os' => isset($data['os']) ? $data['os'] : null,
            'country' => isset($data['country']) ? $data['country'] : null,
        );

        $wpdb->insert($table, $event_data);

        return $wpdb->insert_id;
    }

    /**
     * Get overview stats
     *
     * @param int $days
     * @return array
     */
    public static function get_overview_stats($days = 30) {
        global $wpdb;
        $analytics_table = $wpdb->prefix . 'onesignal_pwa_analytics';
        $subscribers_table = $wpdb->prefix . 'onesignal_pwa_subscribers';
        $notifications_table = $wpdb->prefix . 'onesignal_pwa_notifications';

        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        // Total subscribers
        $total_subscribers = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$subscribers_table} WHERE subscription_status = 'subscribed'"
        );

        // New subscribers
        $new_subscribers = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$subscribers_table} WHERE created_at > %s",
            $date
        ));

        // Total notifications sent
        $total_notifications = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$notifications_table} WHERE status = 'sent' AND sent_at > %s",
            $date
        ));

        // Total delivered
        $total_delivered = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(delivered) FROM {$notifications_table} WHERE status = 'sent' AND sent_at > %s",
            $date
        ));

        // Total clicked
        $total_clicked = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(clicked) FROM {$notifications_table} WHERE status = 'sent' AND sent_at > %s",
            $date
        ));

        // Calculate CTR
        $ctr = $total_delivered > 0 ? round(($total_clicked / $total_delivered) * 100, 2) : 0;

        // PWA installations
        $pwa_installs = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$analytics_table} WHERE event_type = 'pwa_install' AND created_at > %s",
            $date
        ));

        return array(
            'total_subscribers' => (int) $total_subscribers,
            'new_subscribers' => (int) $new_subscribers,
            'total_notifications' => (int) $total_notifications,
            'total_delivered' => (int) $total_delivered,
            'total_clicked' => (int) $total_clicked,
            'ctr' => $ctr,
            'pwa_installs' => (int) $pwa_installs,
        );
    }

    /**
     * Get subscriber growth data
     *
     * @param int $days
     * @return array
     */
    public static function get_subscriber_growth($days = 30) {
        return OneSignal_PWA_Subscriber::get_growth_stats($days);
    }

    /**
     * Get notification performance
     *
     * @param int $limit
     * @return array
     */
    public static function get_notification_performance($limit = 10) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_notifications';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT
                id,
                title,
                sent_at,
                delivered,
                clicked,
                CASE WHEN delivered > 0 THEN ROUND((clicked / delivered) * 100, 2) ELSE 0 END as ctr
            FROM {$table}
            WHERE status = 'sent'
            ORDER BY sent_at DESC
            LIMIT %d",
            $limit
        ));
    }

    /**
     * Get event counts by type
     *
     * @param int $days
     * @return array
     */
    public static function get_event_counts($days = 30) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_analytics';

        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        return $wpdb->get_results($wpdb->prepare(
            "SELECT event_type, COUNT(*) as count
            FROM {$table}
            WHERE created_at > %s
            GROUP BY event_type
            ORDER BY count DESC",
            $date
        ), ARRAY_A);
    }

    /**
     * Get device breakdown
     *
     * @param int $days
     * @return array
     */
    public static function get_device_breakdown($days = 30) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_subscribers';

        return $wpdb->get_results(
            "SELECT device_type, COUNT(*) as count
            FROM {$table}
            WHERE subscription_status = 'subscribed'
            GROUP BY device_type
            ORDER BY count DESC",
            ARRAY_A
        );
    }

    /**
     * Get browser breakdown
     *
     * @param int $days
     * @return array
     */
    public static function get_browser_breakdown($days = 30) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_subscribers';

        return $wpdb->get_results(
            "SELECT browser, COUNT(*) as count
            FROM {$table}
            WHERE subscription_status = 'subscribed'
            GROUP BY browser
            ORDER BY count DESC",
            ARRAY_A
        );
    }

    /**
     * Get geographic breakdown
     *
     * @param int $days
     * @return array
     */
    public static function get_geographic_breakdown($days = 30) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_subscribers';

        return $wpdb->get_results(
            "SELECT country, COUNT(*) as count
            FROM {$table}
            WHERE subscription_status = 'subscribed' AND country IS NOT NULL
            GROUP BY country
            ORDER BY count DESC
            LIMIT 20",
            ARRAY_A
        );
    }

    /**
     * Clean old analytics data
     *
     * @param int $days
     * @return int
     */
    public static function clean_old_data($days = 90) {
        return OneSignal_PWA_Database::clean_old_analytics($days);
    }
}
