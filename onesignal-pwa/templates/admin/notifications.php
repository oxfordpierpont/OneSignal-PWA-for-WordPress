<?php
/**
 * Notifications History Template - Complete Interface
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
?>

<div class="wrap onesignal-pwa-wrap">
    <div class="onesignal-flex onesignal-items-center onesignal-justify-between onesignal-mb-6">
        <div>
            <h1 class="onesignal-h1 onesignal-mb-2"><?php _e('Notification History', 'onesignal-pwa'); ?></h1>
            <p class="onesignal-p"><?php _e('View and manage all sent notifications', 'onesignal-pwa'); ?></p>
        </div>
        <a href="<?php echo admin_url('admin.php?page=onesignal-pwa-send'); ?>" class="onesignal-btn onesignal-btn-primary">
            <span class="dashicons dashicons-email-alt" style="margin-top: 4px;"></span>
            <?php _e('Send New', 'onesignal-pwa'); ?>
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="onesignal-grid onesignal-grid-cols-4 onesignal-mb-6">
        <div class="onesignal-stat-card">
            <div class="onesignal-stat-label"><?php _e('Total Sent', 'onesignal-pwa'); ?></div>
            <div class="onesignal-stat-value"><?php echo number_format($notification_stats['total']); ?></div>
        </div>
        <div class="onesignal-stat-card">
            <div class="onesignal-stat-label"><?php _e('Delivered', 'onesignal-pwa'); ?></div>
            <div class="onesignal-stat-value"><?php echo number_format($notification_stats['delivered']); ?></div>
        </div>
        <div class="onesignal-stat-card">
            <div class="onesignal-stat-label"><?php _e('Clicked', 'onesignal-pwa'); ?></div>
            <div class="onesignal-stat-value"><?php echo number_format($notification_stats['clicked']); ?></div>
        </div>
        <div class="onesignal-stat-card">
            <div class="onesignal-stat-label"><?php _e('Avg CTR', 'onesignal-pwa'); ?></div>
            <div class="onesignal-stat-value"><?php echo $notification_stats['avg_ctr']; ?>%</div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="onesignal-card onesignal-mb-6">
        <div class="onesignal-card-content">
            <div class="onesignal-flex onesignal-items-center onesignal-justify-between onesignal-gap-4">
                <!-- Status Filter -->
                <div class="onesignal-tabs">
                    <div class="onesignal-tabs-list">
                        <a href="<?php echo add_query_arg('status', 'all', remove_query_arg('paged')); ?>"
                           class="onesignal-tabs-trigger <?php echo $status_filter === 'all' ? 'active' : ''; ?>">
                            <?php _e('All', 'onesignal-pwa'); ?>
                        </a>
                        <a href="<?php echo add_query_arg('status', 'sent', remove_query_arg('paged')); ?>"
                           class="onesignal-tabs-trigger <?php echo $status_filter === 'sent' ? 'active' : ''; ?>">
                            <?php _e('Sent', 'onesignal-pwa'); ?>
                        </a>
                        <a href="<?php echo add_query_arg('status', 'scheduled', remove_query_arg('paged')); ?>"
                           class="onesignal-tabs-trigger <?php echo $status_filter === 'scheduled' ? 'active' : ''; ?>">
                            <?php _e('Scheduled', 'onesignal-pwa'); ?>
                        </a>
                        <a href="<?php echo add_query_arg('status', 'draft', remove_query_arg('paged')); ?>"
                           class="onesignal-tabs-trigger <?php echo $status_filter === 'draft' ? 'active' : ''; ?>">
                            <?php _e('Drafts', 'onesignal-pwa'); ?>
                        </a>
                        <a href="<?php echo add_query_arg('status', 'failed', remove_query_arg('paged')); ?>"
                           class="onesignal-tabs-trigger <?php echo $status_filter === 'failed' ? 'active' : ''; ?>">
                            <?php _e('Failed', 'onesignal-pwa'); ?>
                        </a>
                    </div>
                </div>

                <!-- Search -->
                <form method="get" class="onesignal-flex onesignal-gap-2">
                    <input type="hidden" name="page" value="onesignal-pwa-notifications">
                    <input type="hidden" name="status" value="<?php echo esc_attr($status_filter); ?>">
                    <input type="search" name="s" value="<?php echo esc_attr($search); ?>"
                           class="onesignal-input" style="width: 300px;"
                           placeholder="<?php _e('Search notifications...', 'onesignal-pwa'); ?>">
                    <button type="submit" class="onesignal-btn onesignal-btn-outline">
                        <span class="dashicons dashicons-search" style="margin-top: 4px;"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Notifications Table -->
    <div class="onesignal-card">
        <div class="onesignal-card-content" style="padding: 0;">
            <div class="onesignal-table-wrapper">
                <table class="onesignal-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="select-all" class="onesignal-checkbox">
                            </th>
                            <th><?php _e('Notification', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Status', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Sent At', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Recipients', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Delivered', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Clicked', 'onesignal-pwa'); ?></th>
                            <th><?php _e('CTR', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Actions', 'onesignal-pwa'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($notifications)): ?>
                            <?php foreach ($notifications as $notification):
                                $ctr = $notification->delivered > 0 ? round(($notification->clicked / $notification->delivered) * 100, 2) : 0;
                            ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="onesignal-checkbox notification-checkbox" value="<?php echo $notification->id; ?>">
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo esc_html($notification->title); ?></strong>
                                            <?php if ($notification->message): ?>
                                                <br><span class="onesignal-small onesignal-muted">
                                                    <?php echo esc_html(wp_trim_words($notification->message, 15)); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="onesignal-badge onesignal-status-<?php echo esc_attr($notification->status); ?>">
                                            <?php echo esc_html(ucfirst($notification->status)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        if ($notification->sent_at) {
                                            echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($notification->sent_at));
                                        } elseif ($notification->scheduled_at) {
                                            echo '<span class="onesignal-muted">' . __('Scheduled for:', 'onesignal-pwa') . '<br>' .
                                                 date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($notification->scheduled_at)) . '</span>';
                                        } else {
                                            echo '—';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo number_format($notification->recipients); ?></td>
                                    <td><?php echo number_format($notification->delivered); ?></td>
                                    <td><?php echo number_format($notification->clicked); ?></td>
                                    <td>
                                        <span class="onesignal-font-semibold" style="color: <?php echo $ctr > 5 ? 'hsl(var(--success))' : ($ctr > 2 ? 'hsl(var(--warning))' : 'hsl(var(--muted-foreground))'); ?>;">
                                            <?php echo $ctr; ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <div class="onesignal-flex onesignal-gap-1">
                                            <button class="onesignal-btn onesignal-btn-ghost onesignal-btn-sm" onclick="viewNotification(<?php echo $notification->id; ?>)" title="<?php _e('View Details', 'onesignal-pwa'); ?>">
                                                <span class="dashicons dashicons-visibility" style="font-size: 16px;"></span>
                                            </button>
                                            <?php if ($notification->status === 'draft'): ?>
                                                <a href="<?php echo admin_url('admin.php?page=onesignal-pwa-send&edit=' . $notification->id); ?>"
                                                   class="onesignal-btn onesignal-btn-ghost onesignal-btn-sm" title="<?php _e('Edit', 'onesignal-pwa'); ?>">
                                                    <span class="dashicons dashicons-edit" style="font-size: 16px;"></span>
                                                </a>
                                            <?php endif; ?>
                                            <button class="onesignal-btn onesignal-btn-ghost onesignal-btn-sm" onclick="duplicateNotification(<?php echo $notification->id; ?>)" title="<?php _e('Duplicate', 'onesignal-pwa'); ?>">
                                                <span class="dashicons dashicons-admin-page" style="font-size: 16px;"></span>
                                            </button>
                                            <button class="onesignal-btn onesignal-btn-ghost onesignal-btn-sm" onclick="deleteNotification(<?php echo $notification->id; ?>)" title="<?php _e('Delete', 'onesignal-pwa'); ?>">
                                                <span class="dashicons dashicons-trash" style="font-size: 16px;"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="onesignal-table-empty">
                                    <span class="dashicons dashicons-email-alt" style="font-size: 48px; opacity: 0.3;"></span>
                                    <br>
                                    <?php _e('No notifications found.', 'onesignal-pwa'); ?>
                                    <br><br>
                                    <a href="<?php echo admin_url('admin.php?page=onesignal-pwa-send'); ?>" class="onesignal-btn onesignal-btn-primary onesignal-btn-sm">
                                        <?php _e('Send Your First Notification', 'onesignal-pwa'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="onesignal-flex onesignal-justify-center onesignal-mt-6 onesignal-gap-2">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="<?php echo add_query_arg('paged', $i); ?>"
                   class="onesignal-btn onesignal-btn-<?php echo $current_page == $i ? 'primary' : 'outline'; ?> onesignal-btn-sm">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Notification Details Modal (placeholder) -->
<div id="notification-modal" style="display: none;">
    <!-- Modal content would go here -->
</div>

<script>
jQuery(document).ready(function($) {
    // Select all checkbox
    $('#select-all').on('change', function() {
        $('.notification-checkbox').prop('checked', $(this).prop('checked'));
    });

    // Bulk actions (placeholder)
    $('.notification-checkbox').on('change', function() {
        const anyChecked = $('.notification-checkbox:checked').length > 0;
        // Show/hide bulk action bar
    });
});

function viewNotification(id) {
    window.location.href = '<?php echo admin_url('admin.php?page=onesignal-pwa-notifications&view='); ?>' + id;
}

function duplicateNotification(id) {
    if (confirm('<?php _e('Duplicate this notification?', 'onesignal-pwa'); ?>')) {
        jQuery.post(ajaxurl, {
            action: 'duplicate_notification',
            notification_id: id,
            nonce: onesignalPWA.nonce
        }, function(response) {
            if (response.success) {
                location.reload();
            }
        });
    }
}

function deleteNotification(id) {
    if (confirm(onesignalPWA.strings.confirmDelete)) {
        jQuery.post(ajaxurl, {
            action: 'delete_notification',
            notification_id: id,
            nonce: onesignalPWA.nonce
        }, function(response) {
            if (response.success) {
                location.reload();
            }
        });
    }
}
</script>
