<?php
/**
 * Uninstall Script
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly or not uninstalling
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Load plugin file to get constants
require_once plugin_dir_path(__FILE__) . 'onesignal-pwa.php';

/**
 * Delete all plugin data
 */
function onesignal_pwa_uninstall() {
    global $wpdb;

    // Check if user wants to keep data
    $keep_data = get_option('onesignal_pwa_keep_data_on_uninstall', false);

    if ($keep_data) {
        return;
    }

    // Drop all tables
    $table_prefix = $wpdb->prefix . 'onesignal_pwa_';
    $tables = array(
        'subscribers',
        'notifications',
        'segments',
        'workflows',
        'workflow_instances',
        'analytics',
        'cache',
        'templates'
    );

    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS {$table_prefix}{$table}");
    }

    // Delete all options
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            'onesignal_pwa_%'
        )
    );

    // Delete all user meta
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
            '_onesignal_pwa_%'
        )
    );

    // Delete uploaded icons
    $upload_dir = wp_upload_dir();
    $icons_dir = $upload_dir['basedir'] . '/onesignal-pwa-icons';

    if (file_exists($icons_dir)) {
        $files = glob($icons_dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($icons_dir);
    }

    // Clear scheduled events
    wp_clear_scheduled_hook('onesignal_pwa_cleanup');
    wp_clear_scheduled_hook('onesignal_pwa_analytics_sync');
    wp_clear_scheduled_hook('onesignal_pwa_process_workflows');
    wp_clear_scheduled_hook('onesignal_pwa_process_instance_step');
    wp_clear_scheduled_hook('onesignal_pwa_check_cart_abandonment');
    wp_clear_scheduled_hook('onesignal_pwa_send_review_request');

    // Clear transients
    delete_transient('onesignal_pwa_activated');

    // Flush rewrite rules
    flush_rewrite_rules();
}

// Run uninstall
onesignal_pwa_uninstall();
