<?php
/**
 * OneSignal API Client Class
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OneSignal PWA API Client Class
 */
class OneSignal_PWA_API_Client {

    /**
     * API base URL
     */
    const API_URL = 'https://onesignal.com/api/v1';

    /**
     * App ID
     *
     * @var string
     */
    private $app_id;

    /**
     * REST API key
     *
     * @var string
     */
    private $rest_api_key;

    /**
     * User Auth key
     *
     * @var string
     */
    private $user_auth_key;

    /**
     * Constructor
     */
    public function __construct() {
        $credentials = OneSignal_PWA_Settings::get_api_credentials();
        $this->app_id = $credentials['app_id'];
        $this->rest_api_key = $credentials['rest_api_key'];
        $this->user_auth_key = $credentials['user_auth_key'];
    }

    /**
     * Test API connection
     *
     * @return bool|WP_Error
     */
    public function test_connection() {
        if (empty($this->app_id) || empty($this->rest_api_key)) {
            return new WP_Error('missing_credentials', __('Missing API credentials', 'onesignal-pwa'));
        }

        $response = $this->request('apps/' . $this->app_id, 'GET');

        if (is_wp_error($response)) {
            return $response;
        }

        return true;
    }

    /**
     * Create notification
     *
     * @param array $data Notification data
     * @return array|WP_Error
     */
    public function create_notification($data) {
        $notification_data = array(
            'app_id' => $this->app_id,
            'headings' => array('en' => $data['title']),
            'contents' => array('en' => $data['message']),
        );

        // Add URL
        if (!empty($data['url'])) {
            $notification_data['url'] = $data['url'];
        }

        // Add icon
        if (!empty($data['icon'])) {
            $notification_data['chrome_web_icon'] = $data['icon'];
            $notification_data['firefox_icon'] = $data['icon'];
        }

        // Add large image
        if (!empty($data['large_image'])) {
            $notification_data['chrome_web_image'] = $data['large_image'];
        }

        // Add action buttons
        if (!empty($data['buttons'])) {
            $notification_data['buttons'] = $data['buttons'];
        }

        // Add custom data
        if (!empty($data['custom_data'])) {
            $notification_data['data'] = $data['custom_data'];
        }

        // Add targeting
        if (!empty($data['segments'])) {
            $notification_data['included_segments'] = $data['segments'];
        } elseif (!empty($data['player_ids'])) {
            $notification_data['include_player_ids'] = $data['player_ids'];
        } else {
            $notification_data['included_segments'] = array('All');
        }

        // Add filters
        if (!empty($data['filters'])) {
            $notification_data['filters'] = $data['filters'];
        }

        // Add scheduling
        if (!empty($data['send_after'])) {
            $notification_data['send_after'] = $data['send_after'];
        }

        // Add TTL
        if (isset($data['ttl'])) {
            $notification_data['ttl'] = $data['ttl'];
        }

        // Add priority
        if (isset($data['priority'])) {
            $notification_data['priority'] = $data['priority'];
        }

        return $this->request('notifications', 'POST', $notification_data);
    }

    /**
     * Get notification
     *
     * @param string $notification_id
     * @return array|WP_Error
     */
    public function get_notification($notification_id) {
        return $this->request('notifications/' . $notification_id . '?app_id=' . $this->app_id, 'GET');
    }

    /**
     * Cancel notification
     *
     * @param string $notification_id
     * @return array|WP_Error
     */
    public function cancel_notification($notification_id) {
        return $this->request('notifications/' . $notification_id . '?app_id=' . $this->app_id, 'DELETE');
    }

    /**
     * Get players (subscribers)
     *
     * @param int $limit
     * @param int $offset
     * @return array|WP_Error
     */
    public function get_players($limit = 300, $offset = 0) {
        $endpoint = sprintf('players?app_id=%s&limit=%d&offset=%d', $this->app_id, $limit, $offset);
        return $this->request($endpoint, 'GET');
    }

    /**
     * Get player
     *
     * @param string $player_id
     * @return array|WP_Error
     */
    public function get_player($player_id) {
        return $this->request('players/' . $player_id . '?app_id=' . $this->app_id, 'GET');
    }

    /**
     * Update player
     *
     * @param string $player_id
     * @param array $data
     * @return array|WP_Error
     */
    public function update_player($player_id, $data) {
        return $this->request('players/' . $player_id, 'PUT', $data);
    }

    /**
     * Add device
     *
     * @param array $data
     * @return array|WP_Error
     */
    public function add_device($data) {
        $device_data = array_merge(
            array('app_id' => $this->app_id),
            $data
        );

        return $this->request('players', 'POST', $device_data);
    }

    /**
     * Create segment
     *
     * @param array $data
     * @return array|WP_Error
     */
    public function create_segment($data) {
        $segment_data = array_merge(
            array('app_id' => $this->app_id),
            $data
        );

        return $this->request('apps/' . $this->app_id . '/segments', 'POST', $segment_data);
    }

    /**
     * Get app stats
     *
     * @return array|WP_Error
     */
    public function get_app_stats() {
        return $this->request('apps/' . $this->app_id, 'GET');
    }

    /**
     * Get notification history
     *
     * @param int $limit
     * @param int $offset
     * @return array|WP_Error
     */
    public function get_notification_history($limit = 50, $offset = 0) {
        $endpoint = sprintf('notifications?app_id=%s&limit=%d&offset=%d', $this->app_id, $limit, $offset);
        return $this->request($endpoint, 'GET');
    }

    /**
     * View outcomes
     *
     * @param string $notification_id
     * @return array|WP_Error
     */
    public function view_outcomes($notification_id) {
        return $this->request('notifications/' . $notification_id . '/history', 'GET');
    }

    /**
     * Make API request
     *
     * @param string $endpoint
     * @param string $method
     * @param array $data
     * @return array|WP_Error
     */
    private function request($endpoint, $method = 'GET', $data = array()) {
        $url = self::API_URL . '/' . $endpoint;

        $args = array(
            'method' => $method,
            'headers' => array(
                'Content-Type' => 'application/json; charset=utf-8',
                'Authorization' => 'Basic ' . $this->rest_api_key,
            ),
            'timeout' => 30,
        );

        if (!empty($data) && in_array($method, array('POST', 'PUT', 'PATCH'))) {
            $args['body'] = json_encode($data);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $code = wp_remote_retrieve_response_code($response);

        if ($code >= 400) {
            $error_data = json_decode($body, true);
            $error_message = isset($error_data['errors']) ? implode(', ', $error_data['errors']) : __('API request failed', 'onesignal-pwa');

            return new WP_Error('api_error', $error_message, array('status' => $code));
        }

        return json_decode($body, true);
    }

    /**
     * Export notification
     *
     * @param string $notification_id
     * @return array|WP_Error
     */
    public function export_notification($notification_id) {
        $endpoint = 'notifications/' . $notification_id . '?app_id=' . $this->app_id;
        return $this->request($endpoint, 'GET');
    }

    /**
     * Get outcomes
     *
     * @param string $notification_id
     * @return array|WP_Error
     */
    public function get_outcomes($notification_id) {
        $endpoint = 'apps/' . $this->app_id . '/outcomes?notification_ids=' . $notification_id;
        return $this->request($endpoint, 'GET');
    }
}
