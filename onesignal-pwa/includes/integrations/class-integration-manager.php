<?php
/**
 * Integration Manager Class
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OneSignal PWA Integration Manager Class
 */
class OneSignal_PWA_Integration_Manager {

    /**
     * Constructor
     */
    public function __construct() {
        // Initialize integrations
        add_action('plugins_loaded', array($this, 'init_integrations'), 20);
    }

    /**
     * Initialize integrations
     */
    public function init_integrations() {
        // WooCommerce
        if (class_exists('WooCommerce')) {
            new OneSignal_PWA_WooCommerce();
        }

        do_action('onesignal_pwa_integrations_loaded');
    }

    /**
     * Get available integrations
     *
     * @return array
     */
    public static function get_available_integrations() {
        $integrations = array(
            'woocommerce' => array(
                'name' => 'WooCommerce',
                'description' => __('E-commerce integration for cart abandonment, order notifications, and more', 'onesignal-pwa'),
                'available' => class_exists('WooCommerce'),
                'active' => class_exists('WooCommerce') && OneSignal_PWA_Settings::get('woocommerce_enabled', true)
            ),
            'edd' => array(
                'name' => 'Easy Digital Downloads',
                'description' => __('Digital products integration', 'onesignal-pwa'),
                'available' => class_exists('Easy_Digital_Downloads'),
                'active' => false
            ),
            'memberpress' => array(
                'name' => 'MemberPress',
                'description' => __('Membership site integration', 'onesignal-pwa'),
                'available' => class_exists('MeprAppCtrl'),
                'active' => false
            ),
            'learndash' => array(
                'name' => 'LearnDash',
                'description' => __('Learning management system integration', 'onesignal-pwa'),
                'available' => class_exists('SFWD_LMS'),
                'active' => false
            ),
            'buddypress' => array(
                'name' => 'BuddyPress',
                'description' => __('Community platform integration', 'onesignal-pwa'),
                'available' => function_exists('buddypress'),
                'active' => false
            ),
        );

        return apply_filters('onesignal_pwa_available_integrations', $integrations);
    }

    /**
     * Check if integration is active
     *
     * @param string $integration
     * @return bool
     */
    public static function is_integration_active($integration) {
        $integrations = self::get_available_integrations();

        return isset($integrations[$integration]) && $integrations[$integration]['active'];
    }
}
