<?php
/**
 * Subscriber Management Class
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OneSignal PWA Subscriber Class
 */
class OneSignal_PWA_Subscriber {

    /**
     * Get subscriber by player ID
     *
     * @param string $player_id
     * @return object|null
     */
    public static function get_by_player_id($player_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_subscribers';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE player_id = %s",
            $player_id
        ));
    }

    /**
     * Get subscriber by user ID
     *
     * @param int $user_id
     * @return object|null
     */
    public static function get_by_user_id($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_subscribers';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE user_id = %d",
            $user_id
        ));
    }

    /**
     * Create or update subscriber
     *
     * @param array $data
     * @return int|false
     */
    public static function save($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_subscribers';

        $existing = self::get_by_player_id($data['player_id']);

        // Prepare data
        $subscriber_data = array(
            'player_id' => $data['player_id'],
            'external_id' => isset($data['external_id']) ? $data['external_id'] : null,
            'user_id' => isset($data['user_id']) ? $data['user_id'] : null,
            'email' => isset($data['email']) ? $data['email'] : null,
            'tags' => isset($data['tags']) ? json_encode($data['tags']) : null,
            'device_type' => isset($data['device_type']) ? $data['device_type'] : null,
            'device_model' => isset($data['device_model']) ? $data['device_model'] : null,
            'browser' => isset($data['browser']) ? $data['browser'] : null,
            'browser_version' => isset($data['browser_version']) ? $data['browser_version'] : null,
            'os' => isset($data['os']) ? $data['os'] : null,
            'os_version' => isset($data['os_version']) ? $data['os_version'] : null,
            'country' => isset($data['country']) ? $data['country'] : null,
            'region' => isset($data['region']) ? $data['region'] : null,
            'city' => isset($data['city']) ? $data['city'] : null,
            'timezone' => isset($data['timezone']) ? $data['timezone'] : null,
            'language' => isset($data['language']) ? $data['language'] : null,
            'last_session' => current_time('mysql'),
        );

        if ($existing) {
            // Update
            $subscriber_data['session_count'] = $existing->session_count + 1;
            $wpdb->update(
                $table,
                $subscriber_data,
                array('player_id' => $data['player_id'])
            );
            return $existing->id;
        } else {
            // Insert
            $subscriber_data['first_session'] = current_time('mysql');
            $subscriber_data['session_count'] = 1;
            $subscriber_data['subscription_status'] = 'subscribed';

            $wpdb->insert($table, $subscriber_data);
            return $wpdb->insert_id;
        }
    }

    /**
     * Update subscriber tags
     *
     * @param int $subscriber_id
     * @param array $tags
     * @return bool
     */
    public static function update_tags($subscriber_id, $tags) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_subscribers';

        return $wpdb->update(
            $table,
            array('tags' => json_encode($tags)),
            array('id' => $subscriber_id)
        );
    }

    /**
     * Update subscription status
     *
     * @param int $subscriber_id
     * @param string $status
     * @return bool
     */
    public static function update_status($subscriber_id, $status) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_subscribers';

        return $wpdb->update(
            $table,
            array('subscription_status' => $status),
            array('id' => $subscriber_id)
        );
    }

    /**
     * Get total subscriber count
     *
     * @return int
     */
    public static function get_total_count() {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_subscribers';

        return (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$table} WHERE subscription_status = 'subscribed'"
        );
    }

    /**
     * Get subscribers by segment
     *
     * @param int $segment_id
     * @return array
     */
    public static function get_by_segment($segment_id) {
        $segment = OneSignal_PWA_Segment::get($segment_id);

        if (!$segment) {
            return array();
        }

        return self::query_by_rules(json_decode($segment->rules, true));
    }

    /**
     * Query subscribers by rules
     *
     * @param array $rules
     * @return array
     */
    public static function query_by_rules($rules) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_subscribers';

        // Whitelist of allowed fields to prevent SQL injection
        $allowed_fields = array(
            'player_id',
            'external_id',
            'user_id',
            'email',
            'device_type',
            'device_model',
            'browser',
            'browser_version',
            'os',
            'os_version',
            'country',
            'region',
            'city',
            'timezone',
            'language',
            'first_session',
            'last_session',
            'session_count',
            'subscription_status',
            'created_at',
            'updated_at'
        );

        // Whitelist of allowed operators
        $allowed_operators = array('equals', 'not_equals', 'contains', 'greater_than', 'less_than');

        $where_clauses = array();
        $where_values = array();

        foreach ($rules as $rule) {
            if (!isset($rule['field']) || !isset($rule['operator']) || !isset($rule['value'])) {
                continue; // Skip invalid rules
            }

            $field = $rule['field'];
            $operator = $rule['operator'];
            $value = $rule['value'];

            // Validate field name against whitelist
            if (!in_array($field, $allowed_fields, true)) {
                continue; // Skip invalid field
            }

            // Validate operator against whitelist
            if (!in_array($operator, $allowed_operators, true)) {
                continue; // Skip invalid operator
            }

            switch ($operator) {
                case 'equals':
                    $where_clauses[] = "`$field` = %s";
                    $where_values[] = $value;
                    break;

                case 'not_equals':
                    $where_clauses[] = "`$field` != %s";
                    $where_values[] = $value;
                    break;

                case 'contains':
                    $where_clauses[] = "`$field` LIKE %s";
                    $where_values[] = '%' . $wpdb->esc_like($value) . '%';
                    break;

                case 'greater_than':
                    if (strpos($value, 'days ago') !== false) {
                        $days = intval($value);
                        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
                        $where_clauses[] = "`$field` > %s";
                        $where_values[] = $date;
                    } else {
                        $where_clauses[] = "`$field` > %s";
                        $where_values[] = $value;
                    }
                    break;

                case 'less_than':
                    if (strpos($value, 'days ago') !== false) {
                        $days = intval($value);
                        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
                        $where_clauses[] = "`$field` < %s";
                        $where_values[] = $date;
                    } else {
                        $where_clauses[] = "`$field` < %s";
                        $where_values[] = $value;
                    }
                    break;
            }
        }

        // Return empty array if no valid rules
        if (empty($where_clauses)) {
            return array();
        }

        $where = implode(' AND ', $where_clauses);
        $query = "SELECT * FROM {$table} WHERE {$where}";

        if (empty($where_values)) {
            return $wpdb->get_results($query);
        }

        return $wpdb->get_results($wpdb->prepare($query, $where_values));
    }

    /**
     * Get all subscribers
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function get_all($limit = 100, $offset = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_subscribers';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE subscription_status = 'subscribed' ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $limit,
            $offset
        ));
    }

    /**
     * Delete subscriber
     *
     * @param int $subscriber_id
     * @return bool
     */
    public static function delete($subscriber_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_subscribers';

        return $wpdb->delete($table, array('id' => $subscriber_id));
    }

    /**
     * Get recent subscribers
     *
     * @param int $days
     * @return array
     */
    public static function get_recent($days = 7) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_subscribers';

        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE created_at > %s ORDER BY created_at DESC",
            $date
        ));
    }

    /**
     * Get subscriber growth stats
     *
     * @param int $days
     * @return array
     */
    public static function get_growth_stats($days = 30) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_subscribers';

        $stats = array();
        for ($i = $days; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE DATE(created_at) = %s",
                $date
            ));

            $stats[] = array(
                'date' => $date,
                'count' => (int) $count
            );
        }

        return $stats;
    }
}
