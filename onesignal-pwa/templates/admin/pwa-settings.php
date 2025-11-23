<?php
/**
 * PWA Settings Template - Complete Configuration
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap onesignal-pwa-wrap">
    <div class="onesignal-mb-6">
        <h1 class="onesignal-h1 onesignal-mb-2"><?php _e('PWA Settings', 'onesignal-pwa'); ?></h1>
        <p class="onesignal-p"><?php _e('Configure your Progressive Web App settings', 'onesignal-pwa'); ?></p>
    </div>

    <form method="post">
        <?php wp_nonce_field('save_pwa_settings'); ?>

        <!-- App Identity -->
        <div class="onesignal-card onesignal-mb-6">
            <div class="onesignal-card-header">
                <h3 class="onesignal-card-title"><?php _e('App Identity', 'onesignal-pwa'); ?></h3>
            </div>
            <div class="onesignal-card-content">
                <div class="onesignal-form-row">
                    <label class="onesignal-label"><?php _e('App Name', 'onesignal-pwa'); ?></label>
                    <input type="text" name="app_name" class="onesignal-input" value="<?php echo esc_attr(OneSignal_PWA_Settings::get('app_name', get_bloginfo('name'))); ?>" required>
                </div>

                <div class="onesignal-form-row">
                    <label class="onesignal-label"><?php _e('Short Name', 'onesignal-pwa'); ?> <span class="onesignal-small onesignal-muted">(<?php _e('Max 12 characters', 'onesignal-pwa'); ?>)</span></label>
                    <input type="text" name="app_short_name" class="onesignal-input" maxlength="12" value="<?php echo esc_attr(OneSignal_PWA_Settings::get('app_short_name', '')); ?>">
                </div>

                <div class="onesignal-form-row">
                    <label class="onesignal-label"><?php _e('Description', 'onesignal-pwa'); ?></label>
                    <textarea name="app_description" class="onesignal-textarea" rows="3"><?php echo esc_textarea(OneSignal_PWA_Settings::get('app_description', get_bloginfo('description'))); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Display Settings -->
        <div class="onesignal-card onesignal-mb-6">
            <div class="onesignal-card-header">
                <h3 class="onesignal-card-title"><?php _e('Display Settings', 'onesignal-pwa'); ?></h3>
            </div>
            <div class="onesignal-card-content">
                <div class="onesignal-grid onesignal-grid-cols-2 onesignal-gap-6">
                    <div class="onesignal-form-row">
                        <label class="onesignal-label"><?php _e('Display Mode', 'onesignal-pwa'); ?></label>
                        <select name="display_mode" class="onesignal-select">
                            <option value="standalone" <?php selected(OneSignal_PWA_Settings::get('display_mode'), 'standalone'); ?>><?php _e('Standalone', 'onesignal-pwa'); ?></option>
                            <option value="fullscreen" <?php selected(OneSignal_PWA_Settings::get('display_mode'), 'fullscreen'); ?>><?php _e('Fullscreen', 'onesignal-pwa'); ?></option>
                            <option value="minimal-ui" <?php selected(OneSignal_PWA_Settings::get('display_mode'), 'minimal-ui'); ?>><?php _e('Minimal UI', 'onesignal-pwa'); ?></option>
                            <option value="browser" <?php selected(OneSignal_PWA_Settings::get('display_mode'), 'browser'); ?>><?php _e('Browser', 'onesignal-pwa'); ?></option>
                        </select>
                    </div>

                    <div class="onesignal-form-row">
                        <label class="onesignal-label"><?php _e('Orientation', 'onesignal-pwa'); ?></label>
                        <select name="orientation" class="onesignal-select">
                            <option value="any" <?php selected(OneSignal_PWA_Settings::get('orientation'), 'any'); ?>><?php _e('Any', 'onesignal-pwa'); ?></option>
                            <option value="portrait" <?php selected(OneSignal_PWA_Settings::get('orientation'), 'portrait'); ?>><?php _e('Portrait', 'onesignal-pwa'); ?></option>
                            <option value="landscape" <?php selected(OneSignal_PWA_Settings::get('orientation'), 'landscape'); ?>><?php _e('Landscape', 'onesignal-pwa'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="onesignal-form-row">
                    <label class="onesignal-label"><?php _e('Theme Color', 'onesignal-pwa'); ?></label>
                    <input type="color" name="theme_color" class="onesignal-input" style="width: 100px;" value="<?php echo esc_attr(OneSignal_PWA_Settings::get('theme_color', '#2271b1')); ?>">
                    <p class="onesignal-form-description"><?php _e('Color for the browser UI', 'onesignal-pwa'); ?></p>
                </div>

                <div class="onesignal-form-row">
                    <label class="onesignal-label"><?php _e('Background Color', 'onesignal-pwa'); ?></label>
                    <input type="color" name="background_color" class="onesignal-input" style="width: 100px;" value="<?php echo esc_attr(OneSignal_PWA_Settings::get('background_color', '#ffffff')); ?>">
                    <p class="onesignal-form-description"><?php _e('Splash screen background color', 'onesignal-pwa'); ?></p>
                </div>
            </div>
        </div>

        <!-- Offline Settings -->
        <div class="onesignal-card onesignal-mb-6">
            <div class="onesignal-card-header">
                <h3 class="onesignal-card-title"><?php _e('Offline Settings', 'onesignal-pwa'); ?></h3>
            </div>
            <div class="onesignal-card-content">
                <div class="onesignal-form-row">
                    <label class="onesignal-flex onesignal-items-center onesignal-gap-2" style="cursor: pointer;">
                        <input type="checkbox" name="enable_offline" value="1" class="onesignal-checkbox" <?php checked(OneSignal_PWA_Settings::get('enable_offline'), '1'); ?>>
                        <span><?php _e('Enable offline support', 'onesignal-pwa'); ?></span>
                    </label>
                </div>

                <div class="onesignal-form-row">
                    <label class="onesignal-label"><?php _e('Cache Strategy', 'onesignal-pwa'); ?></label>
                    <select name="cache_strategy" class="onesignal-select">
                        <option value="cache_first" <?php selected(OneSignal_PWA_Settings::get('cache_strategy'), 'cache_first'); ?>><?php _e('Cache First', 'onesignal-pwa'); ?></option>
                        <option value="network_first" <?php selected(OneSignal_PWA_Settings::get('cache_strategy'), 'network_first'); ?>><?php _e('Network First', 'onesignal-pwa'); ?></option>
                        <option value="stale_while_revalidate" <?php selected(OneSignal_PWA_Settings::get('cache_strategy'), 'stale_while_revalidate'); ?>><?php _e('Stale While Revalidate', 'onesignal-pwa'); ?></option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="onesignal-flex onesignal-gap-2">
            <button type="submit" name="save_pwa_settings" class="onesignal-btn onesignal-btn-primary">
                <span class="dashicons dashicons-saved" style="margin-top: 4px;"></span>
                <?php _e('Save Settings', 'onesignal-pwa'); ?>
            </button>
            <a href="<?php echo home_url('/manifest.json'); ?>" target="_blank" class="onesignal-btn onesignal-btn-outline">
                <span class="dashicons dashicons-visibility" style="margin-top: 4px;"></span>
                <?php _e('View Manifest', 'onesignal-pwa'); ?>
            </a>
        </div>
    </form>
</div>
