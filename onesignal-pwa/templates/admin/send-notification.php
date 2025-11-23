<?php
/**
 * Send Notification Template
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Send Notification', 'onesignal-pwa'); ?></h1>

    <form method="post" class="onesignal-pwa-notification-form">
        <?php wp_nonce_field('send_notification'); ?>

        <div class="onesignal-pwa-form-row">
            <label for="title"><?php _e('Title', 'onesignal-pwa'); ?> *</label>
            <input type="text" id="title" name="title" required maxlength="255">
            <p class="description"><?php _e('Maximum 255 characters', 'onesignal-pwa'); ?></p>
        </div>

        <div class="onesignal-pwa-form-row">
            <label for="message"><?php _e('Message', 'onesignal-pwa'); ?> *</label>
            <textarea id="message" name="message" required maxlength="2048"></textarea>
            <p class="description"><?php _e('Maximum 2048 characters', 'onesignal-pwa'); ?></p>
        </div>

        <div class="onesignal-pwa-form-row">
            <label for="url"><?php _e('URL', 'onesignal-pwa'); ?></label>
            <input type="url" id="url" name="url" placeholder="https://">
            <p class="description"><?php _e('Where users will be taken when they click the notification', 'onesignal-pwa'); ?></p>
        </div>

        <div class="onesignal-pwa-form-row">
            <label for="segments"><?php _e('Send To', 'onesignal-pwa'); ?></label>
            <select id="segments" name="segments[]" multiple>
                <option value="All" selected><?php _e('All Subscribers', 'onesignal-pwa'); ?></option>
                <?php foreach ($segments as $segment): ?>
                    <option value="<?php echo esc_attr($segment->name); ?>">
                        <?php echo esc_html($segment->name); ?> (<?php echo number_format($segment->estimated_size); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="onesignal-pwa-form-row">
            <button type="submit" name="send_now" class="button button-primary">
                <?php _e('Send Now', 'onesignal-pwa'); ?>
            </button>
            <button type="submit" name="save_draft" class="button">
                <?php _e('Save as Draft', 'onesignal-pwa'); ?>
            </button>
            <button type="button" id="preview-notification" class="button">
                <?php _e('Preview', 'onesignal-pwa'); ?>
            </button>
        </div>
    </form>

    <div id="notification-preview-container" style="margin-top: 30px;"></div>
</div>
