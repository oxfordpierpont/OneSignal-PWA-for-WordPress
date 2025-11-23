<?php
/**
 * Subscribers Template - Complete Management Interface
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap onesignal-pwa-wrap">
    <div class="onesignal-flex onesignal-items-center onesignal-justify-between onesignal-mb-6">
        <div>
            <h1 class="onesignal-h1 onesignal-mb-2"><?php _e('Subscribers', 'onesignal-pwa'); ?></h1>
            <p class="onesignal-p"><?php _e('Manage your push notification subscribers', 'onesignal-pwa'); ?></p>
        </div>
        <div class="onesignal-flex onesignal-gap-2">
            <button class="onesignal-btn onesignal-btn-outline" onclick="exportSubscribers()">
                <span class="dashicons dashicons-download" style="margin-top: 4px;"></span>
                <?php _e('Export CSV', 'onesignal-pwa'); ?>
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="onesignal-grid onesignal-grid-cols-4 onesignal-mb-6">
        <div class="onesignal-stat-card">
            <div class="onesignal-stat-label"><?php _e('Total Subscribers', 'onesignal-pwa'); ?></div>
            <div class="onesignal-stat-value"><?php echo number_format($subscriber_stats['total']); ?></div>
        </div>
        <div class="onesignal-stat-card">
            <div class="onesignal-stat-label"><?php _e('Active', 'onesignal-pwa'); ?></div>
            <div class="onesignal-stat-value"><?php echo number_format($subscriber_stats['active']); ?></div>
        </div>
        <div class="onesignal-stat-card">
            <div class="onesignal-stat-label"><?php _e('New (30d)', 'onesignal-pwa'); ?></div>
            <div class="onesignal-stat-value"><?php echo number_format($subscriber_stats['new']); ?></div>
        </div>
        <div class="onesignal-stat-card">
            <div class="onesignal-stat-label"><?php _e('Unsubscribed', 'onesignal-pwa'); ?></div>
            <div class="onesignal-stat-value"><?php echo number_format($subscriber_stats['unsubscribed']); ?></div>
        </div>
    </div>

    <!-- Filters -->
    <div class="onesignal-card onesignal-mb-6">
        <div class="onesignal-card-content">
            <div class="onesignal-flex onesignal-gap-4">
                <input type="search" class="onesignal-input" style="flex: 1; max-width: 400px;" placeholder="<?php _e('Search subscribers...', 'onesignal-pwa'); ?>">
                <select class="onesignal-select" style="width: 200px;">
                    <option><?php _e('All Devices', 'onesignal-pwa'); ?></option>
                    <option><?php _e('Desktop', 'onesignal-pwa'); ?></option>
                    <option><?php _e('Mobile', 'onesignal-pwa'); ?></option>
                </select>
                <select class="onesignal-select" style="width: 200px;">
                    <option><?php _e('All Browsers', 'onesignal-pwa'); ?></option>
                    <option><?php _e('Chrome', 'onesignal-pwa'); ?></option>
                    <option><?php _e('Firefox', 'onesignal-pwa'); ?></option>
                    <option><?php _e('Safari', 'onesignal-pwa'); ?></option>
                </select>
            </div>
        </div>
    </div>

    <!-- Subscribers Table -->
    <div class="onesignal-card">
        <div class="onesignal-card-content" style="padding: 0;">
            <div class="onesignal-table-wrapper">
                <table class="onesignal-table">
                    <thead>
                        <tr>
                            <th><?php _e('Subscriber', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Device', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Browser', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Location', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Last Seen', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Status', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Actions', 'onesignal-pwa'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($subscribers)): ?>
                            <?php foreach ($subscribers as $sub): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <?php if ($sub->user_id): ?>
                                                <strong><?php echo get_userdata($sub->user_id)->display_name; ?></strong>
                                                <br><span class="onesignal-small onesignal-muted"><?php echo esc_html($sub->email); ?></span>
                                            <?php else: ?>
                                                <span class="onesignal-muted"><?php echo substr($sub->player_id, 0, 16); ?>...</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?php echo esc_html($sub->device_type ?: '—'); ?></td>
                                    <td><?php echo esc_html($sub->browser ?: '—'); ?></td>
                                    <td><?php echo esc_html($sub->country ?: '—'); ?></td>
                                    <td><?php echo $sub->last_session ? human_time_diff(strtotime($sub->last_session)) . ' ago' : '—'; ?></td>
                                    <td>
                                        <span class="onesignal-badge onesignal-badge-<?php echo $sub->subscription_status === 'subscribed' ? 'success' : 'secondary'; ?>">
                                            <?php echo esc_html(ucfirst($sub->subscription_status)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="onesignal-btn onesignal-btn-ghost onesignal-btn-sm" onclick="viewSubscriber('<?php echo esc_js($sub->player_id); ?>')">
                                            <span class="dashicons dashicons-visibility" style="font-size: 16px;"></span>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="onesignal-table-empty"><?php _e('No subscribers yet', 'onesignal-pwa'); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function exportSubscribers() {
    window.location.href = '<?php echo admin_url('admin-ajax.php?action=export_subscribers&nonce='); ?>' + onesignalPWA.nonce;
}

function viewSubscriber(playerId) {
    alert('Subscriber details for: ' + playerId);
}
</script>
