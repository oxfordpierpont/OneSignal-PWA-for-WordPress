<?php
/**
 * Admin Dashboard Template
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('OneSignal PWA Dashboard', 'onesignal-pwa'); ?></h1>

    <?php if ($setup_completion < 100): ?>
    <div class="onesignal-pwa-setup-progress">
        <h2><?php _e('Setup Progress', 'onesignal-pwa'); ?></h2>
        <div class="onesignal-pwa-progress-bar">
            <div class="onesignal-pwa-progress-fill" style="width: <?php echo $setup_completion; ?>%;">
                <?php echo $setup_completion; ?>%
            </div>
        </div>
        <p>
            <?php if ($setup_completion < 100): ?>
                <a href="<?php echo admin_url('admin.php?page=onesignal-pwa-settings'); ?>" class="button button-primary">
                    <?php _e('Complete Setup', 'onesignal-pwa'); ?>
                </a>
            <?php endif; ?>
        </p>
    </div>
    <?php endif; ?>

    <div class="onesignal-pwa-stats">
        <div class="onesignal-pwa-stat-card">
            <h3><?php _e('Total Subscribers', 'onesignal-pwa'); ?></h3>
            <div class="onesignal-pwa-stat-value"><?php echo number_format($stats['total_subscribers']); ?></div>
        </div>

        <div class="onesignal-pwa-stat-card">
            <h3><?php _e('New Subscribers (30d)', 'onesignal-pwa'); ?></h3>
            <div class="onesignal-pwa-stat-value"><?php echo number_format($stats['new_subscribers']); ?></div>
        </div>

        <div class="onesignal-pwa-stat-card">
            <h3><?php _e('Notifications Sent', 'onesignal-pwa'); ?></h3>
            <div class="onesignal-pwa-stat-value"><?php echo number_format($stats['total_notifications']); ?></div>
        </div>

        <div class="onesignal-pwa-stat-card">
            <h3><?php _e('Average CTR', 'onesignal-pwa'); ?></h3>
            <div class="onesignal-pwa-stat-value"><?php echo $stats['ctr']; ?>%</div>
        </div>

        <div class="onesignal-pwa-stat-card">
            <h3><?php _e('PWA Installs', 'onesignal-pwa'); ?></h3>
            <div class="onesignal-pwa-stat-value"><?php echo number_format($stats['pwa_installs']); ?></div>
        </div>
    </div>

    <div class="onesignal-pwa-recent-notifications">
        <h2><?php _e('Recent Notifications', 'onesignal-pwa'); ?></h2>
        <table class="onesignal-pwa-table">
            <thead>
                <tr>
                    <th><?php _e('Title', 'onesignal-pwa'); ?></th>
                    <th><?php _e('Sent At', 'onesignal-pwa'); ?></th>
                    <th><?php _e('Delivered', 'onesignal-pwa'); ?></th>
                    <th><?php _e('Clicked', 'onesignal-pwa'); ?></th>
                    <th><?php _e('CTR', 'onesignal-pwa'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recent_notifications)): ?>
                    <?php foreach ($recent_notifications as $notification): ?>
                        <tr>
                            <td><?php echo esc_html($notification->title); ?></td>
                            <td><?php echo $notification->sent_at ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($notification->sent_at)) : '-'; ?></td>
                            <td><?php echo number_format($notification->delivered); ?></td>
                            <td><?php echo number_format($notification->clicked); ?></td>
                            <td><?php echo $notification->delivered > 0 ? round(($notification->clicked / $notification->delivered) * 100, 2) : 0; ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5"><?php _e('No notifications sent yet.', 'onesignal-pwa'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="onesignal-pwa-quick-actions" style="margin-top: 20px;">
        <a href="<?php echo admin_url('admin.php?page=onesignal-pwa-send'); ?>" class="button button-primary button-large">
            <?php _e('Send Notification', 'onesignal-pwa'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=onesignal-pwa-analytics'); ?>" class="button button-large">
            <?php _e('View Analytics', 'onesignal-pwa'); ?>
        </a>
    </div>
</div>
