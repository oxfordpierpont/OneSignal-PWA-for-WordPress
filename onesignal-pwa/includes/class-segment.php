<?php
/**
 * Segment Management Class
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OneSignal PWA Segment Class
 */
class OneSignal_PWA_Segment {

    /**
     * Create segment
     *
     * @param array $data
     * @return int|false
     */
    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_segments';

        $segment_data = array(
            'name' => sanitize_text_field($data['name']),
            'description' => isset($data['description']) ? sanitize_textarea_field($data['description']) : null,
            'rules' => json_encode($data['rules']),
            'is_dynamic' => isset($data['is_dynamic']) ? (bool) $data['is_dynamic'] : true,
            'created_by' => get_current_user_id(),
        );

        $wpdb->insert($table, $segment_data);

        $segment_id = $wpdb->insert_id;

        // Calculate initial size
        self::calculate_size($segment_id);

        return $segment_id;
    }

    /**
     * Update segment
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public static function update($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_segments';

        $result = $wpdb->update($table, $data, array('id' => $id));

        // Recalculate size
        self::calculate_size($id);

        return $result;
    }

    /**
     * Get segment
     *
     * @param int $id
     * @return object|null
     */
    public static function get($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_segments';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $id
        ));
    }

    /**
     * Delete segment
     *
     * @param int $id
     * @return bool
     */
    public static function delete($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_segments';

        return $wpdb->delete($table, array('id' => $id));
    }

    /**
     * Get all segments
     *
     * @return array
     */
    public static function get_all() {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_segments';

        return $wpdb->get_results("SELECT * FROM {$table} ORDER BY name ASC");
    }

    /**
     * Calculate segment size
     *
     * @param int $segment_id
     * @return int
     */
    public static function calculate_size($segment_id) {
        $segment = self::get($segment_id);

        if (!$segment) {
            return 0;
        }

        $rules = json_decode($segment->rules, true);
        $subscribers = OneSignal_PWA_Subscriber::query_by_rules($rules);
        $size = count($subscribers);

        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_segments';

        $wpdb->update(
            $table,
            array(
                'estimated_size' => $size,
                'last_calculated' => current_time('mysql')
            ),
            array('id' => $segment_id)
        );

        return $size;
    }

    /**
     * Get subscribers in segment
     *
     * @param int $segment_id
     * @return array
     */
    public static function get_subscribers($segment_id) {
        return OneSignal_PWA_Subscriber::get_by_segment($segment_id);
    }

    /**
     * Check if subscriber matches segment
     *
     * @param int $subscriber_id
     * @param int $segment_id
     * @return bool
     */
    public static function subscriber_matches($subscriber_id, $segment_id) {
        $subscribers = self::get_subscribers($segment_id);

        foreach ($subscribers as $subscriber) {
            if ($subscriber->id == $subscriber_id) {
                return true;
            }
        }

        return false;
    }
}
