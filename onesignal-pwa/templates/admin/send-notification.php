<?php
/**
 * Send Notification Template - Complete Shadcn UI
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get default icon
$default_icon = OneSignal_PWA_Settings::get('app_icon_url', '');
?>

<div class="wrap onesignal-pwa-wrap">
    <div class="onesignal-flex onesignal-items-center onesignal-justify-between onesignal-mb-6">
        <div>
            <h1 class="onesignal-h1 onesignal-mb-2"><?php _e('Send Notification', 'onesignal-pwa'); ?></h1>
            <p class="onesignal-p"><?php _e('Create and send push notifications to your subscribers', 'onesignal-pwa'); ?></p>
        </div>
        <div class="onesignal-flex onesignal-gap-2">
            <a href="<?php echo admin_url('admin.php?page=onesignal-pwa-templates'); ?>" class="onesignal-btn onesignal-btn-outline">
                <span class="dashicons dashicons-feedback" style="margin-top: 4px;"></span>
                <?php _e('Use Template', 'onesignal-pwa'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=onesignal-pwa-notifications'); ?>" class="onesignal-btn onesignal-btn-outline">
                <span class="dashicons dashicons-list-view" style="margin-top: 4px;"></span>
                <?php _e('View History', 'onesignal-pwa'); ?>
            </a>
        </div>
    </div>

    <div class="onesignal-grid onesignal-grid-cols-3 onesignal-gap-6">
        <!-- Main Form Column (2/3 width) -->
        <div style="grid-column: span 2 / span 2;">
            <form method="post" id="send-notification-form" enctype="multipart/form-data">
                <?php wp_nonce_field('send_notification'); ?>

                <!-- Notification Content Card -->
                <div class="onesignal-card onesignal-mb-6">
                    <div class="onesignal-card-header">
                        <h3 class="onesignal-card-title"><?php _e('Notification Content', 'onesignal-pwa'); ?></h3>
                        <p class="onesignal-card-description"><?php _e('Craft your notification message', 'onesignal-pwa'); ?></p>
                    </div>
                    <div class="onesignal-card-content">
                        <div class="onesignal-form-row">
                            <label for="notification-title" class="onesignal-label">
                                <?php _e('Title', 'onesignal-pwa'); ?> *
                            </label>
                            <input
                                type="text"
                                id="notification-title"
                                name="title"
                                class="onesignal-input"
                                required
                                maxlength="255"
                                placeholder="<?php _e('Enter notification title...', 'onesignal-pwa'); ?>"
                            >
                            <p class="onesignal-form-description">
                                <span id="title-counter">0</span>/255 <?php _e('characters', 'onesignal-pwa'); ?>
                            </p>
                        </div>

                        <div class="onesignal-form-row">
                            <label for="notification-message" class="onesignal-label">
                                <?php _e('Message', 'onesignal-pwa'); ?> *
                            </label>
                            <textarea
                                id="notification-message"
                                name="message"
                                class="onesignal-textarea"
                                required
                                maxlength="2048"
                                rows="4"
                                placeholder="<?php _e('Enter your notification message...', 'onesignal-pwa'); ?>"
                            ></textarea>
                            <p class="onesignal-form-description">
                                <span id="message-counter">0</span>/2048 <?php _e('characters', 'onesignal-pwa'); ?>
                            </p>
                        </div>

                        <div class="onesignal-form-row">
                            <label for="notification-url" class="onesignal-label">
                                <?php _e('Click Action URL', 'onesignal-pwa'); ?>
                            </label>
                            <input
                                type="url"
                                id="notification-url"
                                name="url"
                                class="onesignal-input"
                                placeholder="https://<?php echo $_SERVER['HTTP_HOST']; ?>"
                            >
                            <p class="onesignal-form-description">
                                <?php _e('Where users will be taken when they click the notification', 'onesignal-pwa'); ?>
                            </p>
                        </div>

                        <div class="onesignal-form-row">
                            <label for="notification-icon" class="onesignal-label">
                                <?php _e('Notification Icon', 'onesignal-pwa'); ?>
                            </label>
                            <div class="onesignal-flex onesignal-gap-3 onesignal-items-center">
                                <div id="icon-preview-container">
                                    <img
                                        id="icon-preview"
                                        src="<?php echo esc_url($default_icon); ?>"
                                        alt="<?php _e('Icon preview', 'onesignal-pwa'); ?>"
                                        style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 2px solid hsl(var(--border));"
                                    >
                                </div>
                                <div class="onesignal-flex onesignal-gap-2">
                                    <input
                                        type="file"
                                        id="notification-icon"
                                        name="icon"
                                        accept="image/png,image/jpeg"
                                        style="display: none;"
                                    >
                                    <button type="button" class="onesignal-btn onesignal-btn-outline onesignal-btn-sm" onclick="document.getElementById('notification-icon').click();">
                                        <?php _e('Upload Image', 'onesignal-pwa'); ?>
                                    </button>
                                    <button type="button" class="onesignal-btn onesignal-btn-ghost onesignal-btn-sm" id="reset-icon">
                                        <?php _e('Reset to Default', 'onesignal-pwa'); ?>
                                    </button>
                                </div>
                            </div>
                            <p class="onesignal-form-description">
                                <?php _e('Recommended: 192x192 PNG or JPG, max 5MB', 'onesignal-pwa'); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Targeting Card -->
                <div class="onesignal-card onesignal-mb-6">
                    <div class="onesignal-card-header">
                        <h3 class="onesignal-card-title"><?php _e('Targeting', 'onesignal-pwa'); ?></h3>
                        <p class="onesignal-card-description"><?php _e('Choose who should receive this notification', 'onesignal-pwa'); ?></p>
                    </div>
                    <div class="onesignal-card-content">
                        <div class="onesignal-form-row">
                            <label class="onesignal-label">
                                <?php _e('Target Audience', 'onesignal-pwa'); ?>
                            </label>
                            <div class="onesignal-tabs">
                                <div class="onesignal-tabs-list">
                                    <button type="button" class="onesignal-tabs-trigger active" data-target="all">
                                        <?php _e('All Subscribers', 'onesignal-pwa'); ?>
                                    </button>
                                    <button type="button" class="onesignal-tabs-trigger" data-target="segments">
                                        <?php _e('Segments', 'onesignal-pwa'); ?>
                                    </button>
                                    <button type="button" class="onesignal-tabs-trigger" data-target="users">
                                        <?php _e('Specific Users', 'onesignal-pwa'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div id="targeting-all" class="targeting-panel">
                            <input type="hidden" name="target_type" value="all">
                            <div class="onesignal-alert onesignal-alert-default onesignal-mt-4">
                                <div class="onesignal-alert-description">
                                    <?php
                                    $total_subscribers = OneSignal_PWA_Subscriber::get_total_count();
                                    printf(
                                        __('This notification will be sent to all %s subscribers.', 'onesignal-pwa'),
                                        '<strong>' . number_format($total_subscribers) . '</strong>'
                                    );
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div id="targeting-segments" class="targeting-panel" style="display: none;">
                            <div class="onesignal-form-row onesignal-mt-4">
                                <label for="segment-select" class="onesignal-label">
                                    <?php _e('Select Segments', 'onesignal-pwa'); ?>
                                </label>
                                <select id="segment-select" name="segments[]" class="onesignal-select" multiple size="5">
                                    <?php if (!empty($segments)): ?>
                                        <?php foreach ($segments as $segment): ?>
                                            <option value="<?php echo esc_attr($segment->id); ?>">
                                                <?php echo esc_html($segment->name); ?> (<?php echo number_format($segment->estimated_size); ?> <?php _e('users', 'onesignal-pwa'); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option disabled><?php _e('No segments created yet', 'onesignal-pwa'); ?></option>
                                    <?php endif; ?>
                                </select>
                                <p class="onesignal-form-description">
                                    <?php _e('Hold Ctrl (Cmd on Mac) to select multiple segments', 'onesignal-pwa'); ?>
                                </p>
                            </div>
                        </div>

                        <div id="targeting-users" class="targeting-panel" style="display: none;">
                            <div class="onesignal-form-row onesignal-mt-4">
                                <label for="user-ids" class="onesignal-label">
                                    <?php _e('Player IDs', 'onesignal-pwa'); ?>
                                </label>
                                <textarea
                                    id="user-ids"
                                    name="user_ids"
                                    class="onesignal-textarea"
                                    rows="4"
                                    placeholder="<?php _e('Enter player IDs, one per line', 'onesignal-pwa'); ?>"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scheduling Card -->
                <div class="onesignal-card onesignal-mb-6">
                    <div class="onesignal-card-header">
                        <h3 class="onesignal-card-title"><?php _e('Scheduling', 'onesignal-pwa'); ?></h3>
                        <p class="onesignal-card-description"><?php _e('When should this notification be sent?', 'onesignal-pwa'); ?></p>
                    </div>
                    <div class="onesignal-card-content">
                        <div class="onesignal-flex onesignal-gap-4">
                            <label class="onesignal-flex onesignal-items-center onesignal-gap-2" style="cursor: pointer;">
                                <input type="radio" name="schedule_type" value="now" class="onesignal-radio" checked>
                                <span><?php _e('Send Immediately', 'onesignal-pwa'); ?></span>
                            </label>
                            <label class="onesignal-flex onesignal-items-center onesignal-gap-2" style="cursor: pointer;">
                                <input type="radio" name="schedule_type" value="scheduled" class="onesignal-radio">
                                <span><?php _e('Schedule for Later', 'onesignal-pwa'); ?></span>
                            </label>
                        </div>

                        <div id="schedule-datetime" style="display: none; margin-top: 1rem;">
                            <div class="onesignal-grid onesignal-grid-cols-2 onesignal-gap-4">
                                <div class="onesignal-form-row">
                                    <label for="schedule-date" class="onesignal-label">
                                        <?php _e('Date', 'onesignal-pwa'); ?>
                                    </label>
                                    <input
                                        type="date"
                                        id="schedule-date"
                                        name="schedule_date"
                                        class="onesignal-input"
                                        min="<?php echo date('Y-m-d'); ?>"
                                    >
                                </div>
                                <div class="onesignal-form-row">
                                    <label for="schedule-time" class="onesignal-label">
                                        <?php _e('Time', 'onesignal-pwa'); ?>
                                    </label>
                                    <input
                                        type="time"
                                        id="schedule-time"
                                        name="schedule_time"
                                        class="onesignal-input"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Options Card -->
                <div class="onesignal-card onesignal-mb-6">
                    <div class="onesignal-card-header">
                        <h3 class="onesignal-card-title"><?php _e('Advanced Options', 'onesignal-pwa'); ?></h3>
                        <p class="onesignal-card-description"><?php _e('Optional advanced settings', 'onesignal-pwa'); ?></p>
                    </div>
                    <div class="onesignal-card-content">
                        <div class="onesignal-form-row">
                            <label for="notification-ttl" class="onesignal-label">
                                <?php _e('Time to Live (TTL)', 'onesignal-pwa'); ?>
                            </label>
                            <select id="notification-ttl" name="ttl" class="onesignal-select">
                                <option value="0"><?php _e('No expiration', 'onesignal-pwa'); ?></option>
                                <option value="3600"><?php _e('1 hour', 'onesignal-pwa'); ?></option>
                                <option value="86400" selected><?php _e('1 day', 'onesignal-pwa'); ?></option>
                                <option value="259200"><?php _e('3 days', 'onesignal-pwa'); ?></option>
                                <option value="604800"><?php _e('1 week', 'onesignal-pwa'); ?></option>
                            </select>
                            <p class="onesignal-form-description">
                                <?php _e('How long OneSignal will continue trying to deliver the notification', 'onesignal-pwa'); ?>
                            </p>
                        </div>

                        <div class="onesignal-form-row">
                            <label class="onesignal-flex onesignal-items-center onesignal-gap-2" style="cursor: pointer;">
                                <input type="checkbox" name="require_interaction" value="1" class="onesignal-checkbox">
                                <span><?php _e('Require user interaction', 'onesignal-pwa'); ?></span>
                            </label>
                            <p class="onesignal-form-description">
                                <?php _e('Notification will remain visible until the user interacts with it', 'onesignal-pwa'); ?>
                            </p>
                        </div>

                        <div class="onesignal-form-row">
                            <label class="onesignal-flex onesignal-items-center onesignal-gap-2" style="cursor: pointer;">
                                <input type="checkbox" name="enable_ab_test" value="1" id="enable-ab-test" class="onesignal-checkbox">
                                <span><?php _e('Enable A/B Testing', 'onesignal-pwa'); ?></span>
                            </label>
                            <p class="onesignal-form-description">
                                <?php _e('Test different variations of this notification', 'onesignal-pwa'); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="onesignal-flex onesignal-gap-2">
                    <button type="submit" name="send_now" class="onesignal-btn onesignal-btn-primary">
                        <span class="dashicons dashicons-email-alt" style="margin-top: 4px;"></span>
                        <?php _e('Send Notification', 'onesignal-pwa'); ?>
                    </button>
                    <button type="submit" name="save_draft" class="onesignal-btn onesignal-btn-secondary">
                        <span class="dashicons dashicons-saved" style="margin-top: 4px;"></span>
                        <?php _e('Save as Draft', 'onesignal-pwa'); ?>
                    </button>
                    <button type="button" id="test-notification" class="onesignal-btn onesignal-btn-outline">
                        <span class="dashicons dashicons-yes-alt" style="margin-top: 4px;"></span>
                        <?php _e('Send Test', 'onesignal-pwa'); ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Preview Column (1/3 width) -->
        <div>
            <div class="onesignal-card" style="position: sticky; top: 32px;">
                <div class="onesignal-card-header">
                    <h3 class="onesignal-card-title"><?php _e('Live Preview', 'onesignal-pwa'); ?></h3>
                    <p class="onesignal-card-description"><?php _e('How your notification will appear', 'onesignal-pwa'); ?></p>
                </div>
                <div class="onesignal-card-content">
                    <div class="onesignal-notification-preview">
                        <div class="onesignal-notification-preview-icon">
                            <img
                                id="preview-icon"
                                src="<?php echo esc_url($default_icon); ?>"
                                alt="<?php _e('Icon', 'onesignal-pwa'); ?>"
                            >
                        </div>
                        <div class="onesignal-notification-preview-content">
                            <div class="onesignal-notification-preview-title" id="preview-title">
                                <?php _e('Your notification title', 'onesignal-pwa'); ?>
                            </div>
                            <div class="onesignal-notification-preview-message" id="preview-message">
                                <?php _e('Your notification message will appear here', 'onesignal-pwa'); ?>
                            </div>
                            <div class="onesignal-notification-preview-url" id="preview-url" style="display: none;"></div>
                        </div>
                    </div>

                    <div class="onesignal-mt-6">
                        <div class="onesignal-flex onesignal-justify-between onesignal-mb-2">
                            <span class="onesignal-small onesignal-font-medium"><?php _e('Estimated Reach', 'onesignal-pwa'); ?></span>
                            <span class="onesignal-small onesignal-font-bold" id="estimated-reach">
                                <?php echo number_format(OneSignal_PWA_Subscriber::get_total_count()); ?>
                            </span>
                        </div>
                        <div class="onesignal-flex onesignal-justify-between onesignal-mb-2">
                            <span class="onesignal-small onesignal-font-medium"><?php _e('Expected CTR', 'onesignal-pwa'); ?></span>
                            <span class="onesignal-small onesignal-font-bold">3-5%</span>
                        </div>
                        <div class="onesignal-flex onesignal-justify-between">
                            <span class="onesignal-small onesignal-font-medium"><?php _e('Delivery Status', 'onesignal-pwa'); ?></span>
                            <span class="onesignal-badge onesignal-badge-secondary onesignal-badge-sm">
                                <?php _e('Draft', 'onesignal-pwa'); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tips Card -->
            <div class="onesignal-card onesignal-mt-4">
                <div class="onesignal-card-header">
                    <h3 class="onesignal-card-title"><?php _e('Best Practices', 'onesignal-pwa'); ?></h3>
                </div>
                <div class="onesignal-card-content">
                    <ul style="margin: 0; padding-left: 1.25rem; line-height: 1.8;">
                        <li class="onesignal-small"><?php _e('Keep titles under 40 characters', 'onesignal-pwa'); ?></li>
                        <li class="onesignal-small"><?php _e('Messages should be clear and actionable', 'onesignal-pwa'); ?></li>
                        <li class="onesignal-small"><?php _e('Include a specific call-to-action', 'onesignal-pwa'); ?></li>
                        <li class="onesignal-small"><?php _e('Test with a small segment first', 'onesignal-pwa'); ?></li>
                        <li class="onesignal-small"><?php _e('Avoid sending too frequently', 'onesignal-pwa'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const defaultIcon = '<?php echo esc_js($default_icon); ?>';

    // Live preview updates
    $('#notification-title').on('input', function() {
        const value = $(this).val() || '<?php _e('Your notification title', 'onesignal-pwa'); ?>';
        $('#preview-title').text(value);
        $('#title-counter').text(value.length);
    });

    $('#notification-message').on('input', function() {
        const value = $(this).val() || '<?php _e('Your notification message will appear here', 'onesignal-pwa'); ?>';
        $('#preview-message').text(value);
        $('#message-counter').text(value.length);
    });

    $('#notification-url').on('input', function() {
        const value = $(this).val();
        if (value) {
            $('#preview-url').text(value).show();
        } else {
            $('#preview-url').hide();
        }
    });

    // Icon upload preview
    $('#notification-icon').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#icon-preview, #preview-icon').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // Reset icon
    $('#reset-icon').on('click', function() {
        $('#notification-icon').val('');
        $('#icon-preview, #preview-icon').attr('src', defaultIcon);
    });

    // Tab switching
    $('.onesignal-tabs-trigger').on('click', function() {
        $('.onesignal-tabs-trigger').removeClass('active');
        $(this).addClass('active');

        const target = $(this).data('target');
        $('.targeting-panel').hide();
        $('#targeting-' + target).show();
        $('input[name="target_type"]').val(target);
    });

    // Schedule toggle
    $('input[name="schedule_type"]').on('change', function() {
        if ($(this).val() === 'scheduled') {
            $('#schedule-datetime').slideDown();
        } else {
            $('#schedule-datetime').slideUp();
        }
    });
});
</script>
