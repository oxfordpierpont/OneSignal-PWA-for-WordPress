<?php
/**
 * WooCommerce Integration Class
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OneSignal PWA WooCommerce Integration Class
 */
class OneSignal_PWA_WooCommerce {

    /**
     * Constructor
     */
    public function __construct() {
        // Order notifications
        add_action('woocommerce_new_order', array($this, 'notify_new_order'), 10, 1);
        add_action('woocommerce_order_status_processing', array($this, 'notify_order_processing'), 10, 1);
        add_action('woocommerce_order_status_completed', array($this, 'notify_order_completed'), 10, 1);
        add_action('woocommerce_order_status_cancelled', array($this, 'notify_order_cancelled'), 10, 1);

        // Cart abandonment
        add_action('woocommerce_add_to_cart', array($this, 'track_cart_activity'), 10);
        add_action('woocommerce_cart_updated', array($this, 'track_cart_activity'), 10);
        add_action('onesignal_pwa_check_cart_abandonment', array($this, 'check_cart_abandonment'), 10, 1);

        // Product notifications
        add_action('woocommerce_product_set_stock', array($this, 'notify_back_in_stock'), 10, 1);
        add_action('woocommerce_product_on_sale', array($this, 'notify_price_drop'), 10, 1);

        // Review requests
        add_action('woocommerce_order_status_completed', array($this, 'schedule_review_request'), 10, 1);
    }

    /**
     * Notify new order
     *
     * @param int $order_id
     */
    public function notify_new_order($order_id) {
        if (!OneSignal_PWA_Settings::get('woocommerce_notify_new_order', true)) {
            return;
        }

        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();

        if (!$user_id) {
            return;
        }

        $subscriber = OneSignal_PWA_Subscriber::get_by_user_id($user_id);

        if (!$subscriber) {
            return;
        }

        $api_client = new OneSignal_PWA_API_Client();

        $notification = array(
            'title' => sprintf(__('Order #%s Received!', 'onesignal-pwa'), $order->get_order_number()),
            'message' => __('Thank you for your order. We\'ve received it and will process it soon.', 'onesignal-pwa'),
            'url' => $order->get_view_order_url(),
            'player_ids' => array($subscriber->player_id)
        );

        $api_client->create_notification($notification);
    }

    /**
     * Notify order processing
     *
     * @param int $order_id
     */
    public function notify_order_processing($order_id) {
        if (!OneSignal_PWA_Settings::get('woocommerce_notify_order_processing', true)) {
            return;
        }

        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();

        if (!$user_id) {
            return;
        }

        $subscriber = OneSignal_PWA_Subscriber::get_by_user_id($user_id);

        if (!$subscriber) {
            return;
        }

        $api_client = new OneSignal_PWA_API_Client();

        $notification = array(
            'title' => sprintf(__('Order #%s is Being Processed', 'onesignal-pwa'), $order->get_order_number()),
            'message' => __('We\'re now processing your order.', 'onesignal-pwa'),
            'url' => $order->get_view_order_url(),
            'player_ids' => array($subscriber->player_id)
        );

        $api_client->create_notification($notification);
    }

    /**
     * Notify order completed
     *
     * @param int $order_id
     */
    public function notify_order_completed($order_id) {
        if (!OneSignal_PWA_Settings::get('woocommerce_notify_order_completed', true)) {
            return;
        }

        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();

        if (!$user_id) {
            return;
        }

        $subscriber = OneSignal_PWA_Subscriber::get_by_user_id($user_id);

        if (!$subscriber) {
            return;
        }

        $api_client = new OneSignal_PWA_API_Client();

        $notification = array(
            'title' => sprintf(__('Order #%s Completed!', 'onesignal-pwa'), $order->get_order_number()),
            'message' => __('Your order has been completed. Thank you for shopping with us!', 'onesignal-pwa'),
            'url' => $order->get_view_order_url(),
            'player_ids' => array($subscriber->player_id)
        );

        $api_client->create_notification($notification);
    }

    /**
     * Notify order cancelled
     *
     * @param int $order_id
     */
    public function notify_order_cancelled($order_id) {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();

        if (!$user_id) {
            return;
        }

        $subscriber = OneSignal_PWA_Subscriber::get_by_user_id($user_id);

        if (!$subscriber) {
            return;
        }

        $api_client = new OneSignal_PWA_API_Client();

        $notification = array(
            'title' => sprintf(__('Order #%s Cancelled', 'onesignal-pwa'), $order->get_order_number()),
            'message' => __('Your order has been cancelled. If you have any questions, please contact us.', 'onesignal-pwa'),
            'url' => home_url('/shop'),
            'player_ids' => array($subscriber->player_id)
        );

        $api_client->create_notification($notification);
    }

    /**
     * Track cart activity
     */
    public function track_cart_activity() {
        if (!is_user_logged_in()) {
            return;
        }

        $user_id = get_current_user_id();
        $cart = WC()->cart;

        if ($cart->is_empty()) {
            delete_user_meta($user_id, '_onesignal_pwa_cart_data');
            return;
        }

        $cart_data = array(
            'cart_total' => $cart->get_cart_contents_total(),
            'cart_count' => $cart->get_cart_contents_count(),
            'cart_url' => wc_get_cart_url(),
            'timestamp' => time()
        );

        update_user_meta($user_id, '_onesignal_pwa_cart_data', $cart_data);

        // Clear any existing scheduled abandonment check
        wp_clear_scheduled_hook('onesignal_pwa_check_cart_abandonment', array($user_id));

        // Schedule abandonment check in 30 minutes
        wp_schedule_single_event(
            time() + (30 * MINUTE_IN_SECONDS),
            'onesignal_pwa_check_cart_abandonment',
            array($user_id)
        );
    }

    /**
     * Check cart abandonment
     *
     * @param int $user_id
     */
    public function check_cart_abandonment($user_id) {
        if (!OneSignal_PWA_Settings::get('woocommerce_cart_abandonment', true)) {
            return;
        }

        $cart_data = get_user_meta($user_id, '_onesignal_pwa_cart_data', true);

        if (empty($cart_data)) {
            return;
        }

        // Check if cart is still abandoned (more than 30 minutes old)
        $time_elapsed = time() - $cart_data['timestamp'];

        if ($time_elapsed < (30 * MINUTE_IN_SECONDS)) {
            return;
        }

        // Check if user has completed an order since cart was last updated
        $recent_orders = wc_get_orders(array(
            'customer_id' => $user_id,
            'date_created' => '>' . $cart_data['timestamp'],
            'limit' => 1
        ));

        if (!empty($recent_orders)) {
            delete_user_meta($user_id, '_onesignal_pwa_cart_data');
            return;
        }

        // Send abandonment notification
        $subscriber = OneSignal_PWA_Subscriber::get_by_user_id($user_id);

        if (!$subscriber) {
            return;
        }

        $api_client = new OneSignal_PWA_API_Client();

        $notification = array(
            'title' => __('You left items in your cart!', 'onesignal-pwa'),
            'message' => sprintf(__('Complete your purchase of %d items worth %s', 'onesignal-pwa'), $cart_data['cart_count'], wc_price($cart_data['cart_total'])),
            'url' => $cart_data['cart_url'],
            'player_ids' => array($subscriber->player_id)
        );

        $api_client->create_notification($notification);

        // Track abandonment
        OneSignal_PWA_Analytics::track_event('cart_abandonment', array(
            'user_id' => $user_id,
            'cart_total' => $cart_data['cart_total'],
            'cart_count' => $cart_data['cart_count']
        ));
    }

    /**
     * Notify back in stock
     *
     * @param WC_Product $product
     */
    public function notify_back_in_stock($product) {
        if (!$product->is_in_stock()) {
            return;
        }

        // Get users who have this product in wishlist or have previously purchased
        // This is a simplified version - you'd need wishlist plugin integration

        // Send notification to interested subscribers
        $api_client = new OneSignal_PWA_API_Client();

        $notification = array(
            'title' => sprintf(__('%s is back in stock!', 'onesignal-pwa'), $product->get_name()),
            'message' => __('Get it before it sells out again.', 'onesignal-pwa'),
            'url' => $product->get_permalink(),
            'segments' => array('All') // You would segment this to interested users
        );

        $api_client->create_notification($notification);
    }

    /**
     * Notify price drop
     *
     * @param WC_Product $product
     */
    public function notify_price_drop($product) {
        $api_client = new OneSignal_PWA_API_Client();

        $notification = array(
            'title' => sprintf(__('%s is on sale!', 'onesignal-pwa'), $product->get_name()),
            'message' => sprintf(__('Now only %s - Save %s%%', 'onesignal-pwa'), wc_price($product->get_sale_price()), $product->get_percentage_discount()),
            'url' => $product->get_permalink(),
            'large_image' => wp_get_attachment_url($product->get_image_id()),
            'segments' => array('All')
        );

        $api_client->create_notification($notification);
    }

    /**
     * Schedule review request
     *
     * @param int $order_id
     */
    public function schedule_review_request($order_id) {
        if (!OneSignal_PWA_Settings::get('woocommerce_review_request', true)) {
            return;
        }

        // Schedule review request for 7 days after order completion
        wp_schedule_single_event(
            time() + (7 * DAY_IN_SECONDS),
            'onesignal_pwa_send_review_request',
            array($order_id)
        );
    }

    /**
     * Send review request
     *
     * @param int $order_id
     */
    public function send_review_request($order_id) {
        $order = wc_get_order($order_id);

        if (!$order) {
            return;
        }

        $user_id = $order->get_user_id();

        if (!$user_id) {
            return;
        }

        $subscriber = OneSignal_PWA_Subscriber::get_by_user_id($user_id);

        if (!$subscriber) {
            return;
        }

        $api_client = new OneSignal_PWA_API_Client();

        $notification = array(
            'title' => __('How was your recent purchase?', 'onesignal-pwa'),
            'message' => __('We\'d love to hear your feedback! Leave a review.', 'onesignal-pwa'),
            'url' => $order->get_view_order_url(),
            'player_ids' => array($subscriber->player_id)
        );

        $api_client->create_notification($notification);
    }
}
