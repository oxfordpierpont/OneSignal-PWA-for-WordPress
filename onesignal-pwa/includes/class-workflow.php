<?php
/**
 * Workflow Automation Class
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OneSignal PWA Workflow Class
 */
class OneSignal_PWA_Workflow {

    /**
     * Constructor
     */
    public function __construct() {
        // Register hooks for workflow triggers
        add_action('user_register', array($this, 'trigger_user_register'), 10, 1);
        add_action('wp_login', array($this, 'trigger_user_login'), 10, 2);
        add_action('publish_post', array($this, 'trigger_new_post'), 10, 2);

        // WooCommerce triggers
        add_action('woocommerce_new_order', array($this, 'trigger_new_order'), 10, 1);
        add_action('woocommerce_order_status_changed', array($this, 'trigger_order_status_change'), 10, 3);
        add_action('woocommerce_cart_updated', array($this, 'trigger_cart_updated'));

        // Scheduled workflow processing
        add_action('onesignal_pwa_process_workflows', array($this, 'process_workflows'));
    }

    /**
     * Create workflow
     *
     * @param array $data
     * @return int|false
     */
    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_workflows';

        $workflow_data = array(
            'name' => sanitize_text_field($data['name']),
            'description' => isset($data['description']) ? sanitize_textarea_field($data['description']) : null,
            'trigger_type' => sanitize_text_field($data['trigger_type']),
            'trigger_config' => isset($data['trigger_config']) ? json_encode($data['trigger_config']) : null,
            'steps' => json_encode($data['steps']),
            'status' => isset($data['status']) ? $data['status'] : 'draft',
            'created_by' => get_current_user_id(),
        );

        $wpdb->insert($table, $workflow_data);

        return $wpdb->insert_id;
    }

    /**
     * Update workflow
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public static function update($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_workflows';

        return $wpdb->update($table, $data, array('id' => $id));
    }

    /**
     * Get workflow
     *
     * @param int $id
     * @return object|null
     */
    public static function get($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_workflows';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $id
        ));
    }

    /**
     * Delete workflow
     *
     * @param int $id
     * @return bool
     */
    public static function delete($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_workflows';

        return $wpdb->delete($table, array('id' => $id));
    }

    /**
     * Activate workflow
     *
     * @param int $id
     * @return bool
     */
    public static function activate($id) {
        return self::update($id, array(
            'status' => 'active',
            'activated_at' => current_time('mysql')
        ));
    }

    /**
     * Deactivate workflow
     *
     * @param int $id
     * @return bool
     */
    public static function deactivate($id) {
        return self::update($id, array(
            'status' => 'inactive',
            'deactivated_at' => current_time('mysql')
        ));
    }

    /**
     * Get active workflows by trigger
     *
     * @param string $trigger_type
     * @return array
     */
    public static function get_by_trigger($trigger_type) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_workflows';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE trigger_type = %s AND status = 'active'",
            $trigger_type
        ));
    }

    /**
     * Start workflow for subscriber
     *
     * @param int $workflow_id
     * @param int $subscriber_id
     * @param array $metadata
     * @return int|false
     */
    public static function start_instance($workflow_id, $subscriber_id, $metadata = array()) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_workflow_instances';

        $instance_data = array(
            'workflow_id' => $workflow_id,
            'subscriber_id' => $subscriber_id,
            'current_step' => 0,
            'status' => 'active',
            'started_at' => current_time('mysql'),
            'metadata' => json_encode($metadata)
        );

        $wpdb->insert($table, $instance_data);

        $instance_id = $wpdb->insert_id;

        // Process first step
        self::process_instance_step($instance_id);

        return $instance_id;
    }

    /**
     * Process workflow instance step
     *
     * @param int $instance_id
     * @return bool
     */
    public static function process_instance_step($instance_id) {
        global $wpdb;
        $instances_table = $wpdb->prefix . 'onesignal_pwa_workflow_instances';
        $workflows_table = $wpdb->prefix . 'onesignal_pwa_workflows';

        $instance = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$instances_table} WHERE id = %d",
            $instance_id
        ));

        if (!$instance) {
            return false;
        }

        $workflow = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$workflows_table} WHERE id = %d",
            $instance->workflow_id
        ));

        if (!$workflow) {
            return false;
        }

        $steps = json_decode($workflow->steps, true);
        $current_step_index = $instance->current_step;

        if ($current_step_index >= count($steps)) {
            // Workflow complete
            $wpdb->update(
                $instances_table,
                array(
                    'status' => 'completed',
                    'completed_at' => current_time('mysql')
                ),
                array('id' => $instance_id)
            );

            return true;
        }

        $step = $steps[$current_step_index];

        // Execute step action
        self::execute_step_action($instance, $step);

        // Move to next step
        $wpdb->update(
            $instances_table,
            array('current_step' => $current_step_index + 1),
            array('id' => $instance_id)
        );

        return true;
    }

    /**
     * Execute step action
     *
     * @param object $instance
     * @param array $step
     * @return bool
     */
    private static function execute_step_action($instance, $step) {
        switch ($step['type']) {
            case 'send_notification':
                $notification_data = $step['data'];
                $notification_data['player_ids'] = array($instance->subscriber_id);

                $api_client = new OneSignal_PWA_API_Client();
                $api_client->create_notification($notification_data);
                break;

            case 'wait':
                $delay = $step['data']['delay'];
                // Schedule next step
                wp_schedule_single_event(
                    time() + $delay,
                    'onesignal_pwa_process_instance_step',
                    array($instance->id)
                );
                break;

            case 'add_tag':
                $tag = $step['data']['tag'];
                $value = $step['data']['value'];
                // Add tag to subscriber
                break;

            case 'remove_tag':
                $tag = $step['data']['tag'];
                // Remove tag from subscriber
                break;
        }

        return true;
    }

    /**
     * Trigger: User Registration
     *
     * @param int $user_id
     */
    public function trigger_user_register($user_id) {
        $workflows = self::get_by_trigger('user_register');

        foreach ($workflows as $workflow) {
            // Find subscriber for this user
            $subscriber = OneSignal_PWA_Subscriber::get_by_user_id($user_id);

            if ($subscriber) {
                self::start_instance($workflow->id, $subscriber->id);
            }
        }
    }

    /**
     * Trigger: User Login
     *
     * @param string $user_login
     * @param WP_User $user
     */
    public function trigger_user_login($user_login, $user) {
        $workflows = self::get_by_trigger('user_login');

        foreach ($workflows as $workflow) {
            $subscriber = OneSignal_PWA_Subscriber::get_by_user_id($user->ID);

            if ($subscriber) {
                self::start_instance($workflow->id, $subscriber->id);
            }
        }
    }

    /**
     * Trigger: New Post
     *
     * @param int $post_id
     * @param WP_Post $post
     */
    public function trigger_new_post($post_id, $post) {
        $workflows = self::get_by_trigger('new_post');

        foreach ($workflows as $workflow) {
            // Trigger for all subscribers
            $subscribers = OneSignal_PWA_Subscriber::get_all();

            foreach ($subscribers as $subscriber) {
                self::start_instance($workflow->id, $subscriber->id, array(
                    'post_id' => $post_id,
                    'post_title' => $post->post_title
                ));
            }
        }
    }

    /**
     * Trigger: New Order
     *
     * @param int $order_id
     */
    public function trigger_new_order($order_id) {
        $workflows = self::get_by_trigger('new_order');

        foreach ($workflows as $workflow) {
            $order = wc_get_order($order_id);
            $user_id = $order->get_user_id();

            if ($user_id) {
                $subscriber = OneSignal_PWA_Subscriber::get_by_user_id($user_id);

                if ($subscriber) {
                    self::start_instance($workflow->id, $subscriber->id, array(
                        'order_id' => $order_id
                    ));
                }
            }
        }
    }

    /**
     * Trigger: Order Status Change
     *
     * @param int $order_id
     * @param string $old_status
     * @param string $new_status
     */
    public function trigger_order_status_change($order_id, $old_status, $new_status) {
        $workflows = self::get_by_trigger('order_status_' . $new_status);

        foreach ($workflows as $workflow) {
            $order = wc_get_order($order_id);
            $user_id = $order->get_user_id();

            if ($user_id) {
                $subscriber = OneSignal_PWA_Subscriber::get_by_user_id($user_id);

                if ($subscriber) {
                    self::start_instance($workflow->id, $subscriber->id, array(
                        'order_id' => $order_id,
                        'old_status' => $old_status,
                        'new_status' => $new_status
                    ));
                }
            }
        }
    }

    /**
     * Trigger: Cart Updated
     */
    public function trigger_cart_updated() {
        // Implement cart abandonment detection
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $cart = WC()->cart;

            if (!$cart->is_empty()) {
                // Schedule cart abandonment check
                wp_schedule_single_event(
                    time() + (30 * MINUTE_IN_SECONDS),
                    'onesignal_pwa_check_cart_abandonment',
                    array($user_id)
                );
            }
        }
    }

    /**
     * Process all workflows
     */
    public function process_workflows() {
        // Process pending workflow instances
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_workflow_instances';

        $instances = $wpdb->get_results(
            "SELECT * FROM {$table} WHERE status = 'active' LIMIT 100"
        );

        foreach ($instances as $instance) {
            self::process_instance_step($instance->id);
        }
    }
}
