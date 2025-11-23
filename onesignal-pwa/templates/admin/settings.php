<?php
/**
 * Settings Template
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('OneSignal PWA Settings', 'onesignal-pwa'); ?></h1>

    <form method="post" class="onesignal-pwa-settings-section">
        <?php wp_nonce_field('save_settings'); ?>

        <h2><?php _e('OneSignal API Credentials', 'onesignal-pwa'); ?></h2>

        <table class="form-table">
            <tr>
                <th><label for="app_id"><?php _e('App ID', 'onesignal-pwa'); ?></label></th>
                <td>
                    <input type="text" id="app_id" name="app_id" value="<?php echo esc_attr($credentials['app_id']); ?>" class="regular-text">
                    <p class="description"><?php _e('Your OneSignal App ID', 'onesignal-pwa'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="rest_api_key"><?php _e('REST API Key', 'onesignal-pwa'); ?></label></th>
                <td>
                    <input type="password" id="rest_api_key" name="rest_api_key" value="<?php echo esc_attr($credentials['rest_api_key']); ?>" class="regular-text">
                    <p class="description"><?php _e('Your OneSignal REST API Key', 'onesignal-pwa'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="user_auth_key"><?php _e('User Auth Key', 'onesignal-pwa'); ?></label></th>
                <td>
                    <input type="password" id="user_auth_key" name="user_auth_key" value="<?php echo esc_attr($credentials['user_auth_key']); ?>" class="regular-text">
                    <p class="description"><?php _e('Your OneSignal User Auth Key (optional, for advanced features)', 'onesignal-pwa'); ?></p>
                </td>
            </tr>
        </table>

        <p>
            <button type="submit" name="save_settings" class="button button-primary"><?php _e('Save Settings', 'onesignal-pwa'); ?></button>
            <button type="button" id="test-api-connection" class="button"><?php _e('Test Connection', 'onesignal-pwa'); ?></button>
        </p>
    </form>
</div>
