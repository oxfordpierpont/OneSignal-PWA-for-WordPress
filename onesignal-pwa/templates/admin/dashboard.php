<?php
/**
 * Admin Dashboard Template - Complete Shadcn UI
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
            <h1 class="onesignal-h1 onesignal-mb-2"><?php _e('OneSignal PWA Dashboard', 'onesignal-pwa'); ?></h1>
            <p class="onesignal-p"><?php _e('Monitor your push notifications and PWA performance', 'onesignal-pwa'); ?></p>
        </div>
        <div class="onesignal-flex onesignal-gap-2">
            <a href="<?php echo admin_url('admin.php?page=onesignal-pwa-send'); ?>" class="onesignal-btn onesignal-btn-primary">
                <span class="dashicons dashicons-email-alt" style="margin-top: 4px;"></span>
                <?php _e('Send Notification', 'onesignal-pwa'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=onesignal-pwa-analytics'); ?>" class="onesignal-btn onesignal-btn-outline">
                <span class="dashicons dashicons-chart-line" style="margin-top: 4px;"></span>
                <?php _e('Analytics', 'onesignal-pwa'); ?>
            </a>
        </div>
    </div>

    <?php if ($setup_completion < 100): ?>
    <div class="onesignal-alert onesignal-alert-warning onesignal-mb-6">
        <div class="onesignal-alert-title"><?php _e('Setup Incomplete', 'onesignal-pwa'); ?></div>
        <div class="onesignal-alert-description">
            <p><?php _e('Complete the setup to start sending push notifications.', 'onesignal-pwa'); ?></p>
            <div class="onesignal-progress onesignal-mt-3">
                <div class="onesignal-progress-bar" style="width: <?php echo $setup_completion; ?>%;">
                    <?php echo $setup_completion; ?>%
                </div>
            </div>
            <a href="<?php echo admin_url('admin.php?page=onesignal-pwa-settings'); ?>" class="onesignal-btn onesignal-btn-warning onesignal-btn-sm onesignal-mt-3">
                <?php _e('Complete Setup', 'onesignal-pwa'); ?>
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Key Performance Indicators -->
    <div class="onesignal-grid onesignal-grid-cols-4 onesignal-mb-6">
        <!-- Total Subscribers -->
        <div class="onesignal-stat-card">
            <div class="onesignal-stat-label"><?php _e('Total Subscribers', 'onesignal-pwa'); ?></div>
            <div class="onesignal-stat-value"><?php echo number_format($stats['total_subscribers']); ?></div>
            <div class="onesignal-stat-change <?php echo $stats['subscriber_change'] >= 0 ? 'positive' : 'negative'; ?>">
                <span class="dashicons dashicons-arrow-<?php echo $stats['subscriber_change'] >= 0 ? 'up' : 'down'; ?>-alt" style="font-size: 12px;"></span>
                <?php echo abs($stats['subscriber_change']); ?>% <?php _e('from last month', 'onesignal-pwa'); ?>
            </div>
        </div>

        <!-- New Subscribers (30d) -->
        <div class="onesignal-stat-card">
            <div class="onesignal-stat-label"><?php _e('New Subscribers (30d)', 'onesignal-pwa'); ?></div>
            <div class="onesignal-stat-value"><?php echo number_format($stats['new_subscribers']); ?></div>
            <div class="onesignal-stat-change <?php echo $stats['new_subscriber_change'] >= 0 ? 'positive' : 'negative'; ?>">
                <span class="dashicons dashicons-arrow-<?php echo $stats['new_subscriber_change'] >= 0 ? 'up' : 'down'; ?>-alt" style="font-size: 12px;"></span>
                <?php echo abs($stats['new_subscriber_change']); ?>% <?php _e('from previous period', 'onesignal-pwa'); ?>
            </div>
        </div>

        <!-- Notifications Sent -->
        <div class="onesignal-stat-card">
            <div class="onesignal-stat-label"><?php _e('Notifications Sent', 'onesignal-pwa'); ?></div>
            <div class="onesignal-stat-value"><?php echo number_format($stats['total_notifications']); ?></div>
            <div class="onesignal-stat-change neutral">
                <span class="dashicons dashicons-calendar-alt" style="font-size: 12px;"></span>
                <?php echo number_format($stats['notifications_this_month']); ?> <?php _e('this month', 'onesignal-pwa'); ?>
            </div>
        </div>

        <!-- Average CTR -->
        <div class="onesignal-stat-card">
            <div class="onesignal-stat-label"><?php _e('Average CTR', 'onesignal-pwa'); ?></div>
            <div class="onesignal-stat-value"><?php echo $stats['ctr']; ?>%</div>
            <div class="onesignal-stat-change <?php echo $stats['ctr_change'] >= 0 ? 'positive' : 'negative'; ?>">
                <span class="dashicons dashicons-arrow-<?php echo $stats['ctr_change'] >= 0 ? 'up' : 'down'; ?>-alt" style="font-size: 12px;"></span>
                <?php echo abs($stats['ctr_change']); ?>% <?php _e('from last month', 'onesignal-pwa'); ?>
            </div>
        </div>
    </div>

    <!-- Secondary Metrics -->
    <div class="onesignal-grid onesignal-grid-cols-3 onesignal-mb-6">
        <!-- PWA Installs -->
        <div class="onesignal-stat-card">
            <div class="onesignal-stat-label"><?php _e('PWA Installs', 'onesignal-pwa'); ?></div>
            <div class="onesignal-stat-value"><?php echo number_format($stats['pwa_installs']); ?></div>
            <div class="onesignal-stat-change positive">
                <span class="dashicons dashicons-smartphone" style="font-size: 12px;"></span>
                <?php echo number_format($stats['pwa_installs_this_week']); ?> <?php _e('this week', 'onesignal-pwa'); ?>
            </div>
        </div>

        <!-- Active Workflows -->
        <div class="onesignal-stat-card">
            <div class="onesignal-stat-label"><?php _e('Active Workflows', 'onesignal-pwa'); ?></div>
            <div class="onesignal-stat-value"><?php echo number_format($stats['active_workflows']); ?></div>
            <div class="onesignal-stat-change neutral">
                <span class="dashicons dashicons-networking" style="font-size: 12px;"></span>
                <?php echo number_format($stats['workflow_executions_today']); ?> <?php _e('executions today', 'onesignal-pwa'); ?>
            </div>
        </div>

        <!-- Conversion Rate -->
        <div class="onesignal-stat-card">
            <div class="onesignal-stat-label"><?php _e('Conversion Rate', 'onesignal-pwa'); ?></div>
            <div class="onesignal-stat-value"><?php echo $stats['conversion_rate']; ?>%</div>
            <div class="onesignal-stat-change <?php echo $stats['conversion_change'] >= 0 ? 'positive' : 'negative'; ?>">
                <span class="dashicons dashicons-arrow-<?php echo $stats['conversion_change'] >= 0 ? 'up' : 'down'; ?>-alt" style="font-size: 12px;"></span>
                <?php echo abs($stats['conversion_change']); ?>% <?php _e('from last month', 'onesignal-pwa'); ?>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="onesignal-grid onesignal-grid-cols-2 onesignal-mb-6">
        <!-- Subscriber Growth Chart -->
        <div class="onesignal-card">
            <div class="onesignal-card-header">
                <h3 class="onesignal-card-title"><?php _e('Subscriber Growth', 'onesignal-pwa'); ?></h3>
                <p class="onesignal-card-description"><?php _e('Last 30 days', 'onesignal-pwa'); ?></p>
            </div>
            <div class="onesignal-card-content">
                <div class="onesignal-chart-container">
                    <canvas id="subscriberGrowthChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Notification Performance Chart -->
        <div class="onesignal-card">
            <div class="onesignal-card-header">
                <h3 class="onesignal-card-title"><?php _e('Notification Performance', 'onesignal-pwa'); ?></h3>
                <p class="onesignal-card-description"><?php _e('Delivery vs Click-through', 'onesignal-pwa'); ?></p>
            </div>
            <div class="onesignal-card-content">
                <div class="onesignal-chart-container">
                    <canvas id="notificationPerformanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Device & Platform Distribution -->
    <div class="onesignal-grid onesignal-grid-cols-2 onesignal-mb-6">
        <!-- Device Type Distribution -->
        <div class="onesignal-card">
            <div class="onesignal-card-header">
                <h3 class="onesignal-card-title"><?php _e('Device Distribution', 'onesignal-pwa'); ?></h3>
                <p class="onesignal-card-description"><?php _e('Subscriber breakdown by device type', 'onesignal-pwa'); ?></p>
            </div>
            <div class="onesignal-card-content">
                <div class="onesignal-chart-container" style="height: 250px;">
                    <canvas id="deviceDistributionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Browser Distribution -->
        <div class="onesignal-card">
            <div class="onesignal-card-header">
                <h3 class="onesignal-card-title"><?php _e('Browser Distribution', 'onesignal-pwa'); ?></h3>
                <p class="onesignal-card-description"><?php _e('Subscriber breakdown by browser', 'onesignal-pwa'); ?></p>
            </div>
            <div class="onesignal-card-content">
                <div class="onesignal-chart-container" style="height: 250px;">
                    <canvas id="browserDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Notifications Table -->
    <div class="onesignal-card onesignal-mb-6">
        <div class="onesignal-card-header">
            <div class="onesignal-flex onesignal-items-center onesignal-justify-between">
                <div>
                    <h3 class="onesignal-card-title"><?php _e('Recent Notifications', 'onesignal-pwa'); ?></h3>
                    <p class="onesignal-card-description"><?php _e('Latest push notification performance', 'onesignal-pwa'); ?></p>
                </div>
                <a href="<?php echo admin_url('admin.php?page=onesignal-pwa-notifications'); ?>" class="onesignal-btn onesignal-btn-outline onesignal-btn-sm">
                    <?php _e('View All', 'onesignal-pwa'); ?>
                </a>
            </div>
        </div>
        <div class="onesignal-card-content" style="padding: 0;">
            <div class="onesignal-table-wrapper">
                <table class="onesignal-table">
                    <thead>
                        <tr>
                            <th><?php _e('Title', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Status', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Sent At', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Delivered', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Clicked', 'onesignal-pwa'); ?></th>
                            <th><?php _e('CTR', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Actions', 'onesignal-pwa'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_notifications)): ?>
                            <?php foreach ($recent_notifications as $notification):
                                $ctr = $notification->delivered > 0 ? round(($notification->clicked / $notification->delivered) * 100, 2) : 0;
                            ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($notification->title); ?></strong>
                                        <?php if ($notification->message): ?>
                                            <br><span class="onesignal-small onesignal-muted"><?php echo esc_html(wp_trim_words($notification->message, 10)); ?></span>
                                        <?php endif; ?>
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
                                        } else {
                                            echo '—';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo number_format($notification->delivered); ?></td>
                                    <td><?php echo number_format($notification->clicked); ?></td>
                                    <td>
                                        <span class="onesignal-font-semibold" style="color: <?php echo $ctr > 5 ? 'hsl(var(--success))' : ($ctr > 2 ? 'hsl(var(--warning))' : 'hsl(var(--destructive))'); ?>;">
                                            <?php echo $ctr; ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <button class="onesignal-btn onesignal-btn-ghost onesignal-btn-sm" onclick="viewNotificationDetails(<?php echo $notification->id; ?>)">
                                            <span class="dashicons dashicons-visibility" style="font-size: 16px;"></span>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="onesignal-table-empty">
                                    <span class="dashicons dashicons-email-alt" style="font-size: 48px; opacity: 0.3;"></span>
                                    <br>
                                    <?php _e('No notifications sent yet.', 'onesignal-pwa'); ?>
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

    <!-- Quick Actions & Info Cards -->
    <div class="onesignal-grid onesignal-grid-cols-3 onesignal-mb-6">
        <!-- Quick Actions -->
        <div class="onesignal-card">
            <div class="onesignal-card-header">
                <h3 class="onesignal-card-title"><?php _e('Quick Actions', 'onesignal-pwa'); ?></h3>
            </div>
            <div class="onesignal-card-content">
                <div class="onesignal-flex onesignal-flex-col onesignal-gap-2">
                    <a href="<?php echo admin_url('admin.php?page=onesignal-pwa-send'); ?>" class="onesignal-btn onesignal-btn-outline">
                        <span class="dashicons dashicons-email-alt" style="margin-top: 4px;"></span>
                        <?php _e('Send Notification', 'onesignal-pwa'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=onesignal-pwa-segments'); ?>" class="onesignal-btn onesignal-btn-outline">
                        <span class="dashicons dashicons-groups" style="margin-top: 4px;"></span>
                        <?php _e('Create Segment', 'onesignal-pwa'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=onesignal-pwa-workflows'); ?>" class="onesignal-btn onesignal-btn-outline">
                        <span class="dashicons dashicons-networking" style="margin-top: 4px;"></span>
                        <?php _e('Create Workflow', 'onesignal-pwa'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=onesignal-pwa-templates'); ?>" class="onesignal-btn onesignal-btn-outline">
                        <span class="dashicons dashicons-feedback" style="margin-top: 4px;"></span>
                        <?php _e('Browse Templates', 'onesignal-pwa'); ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="onesignal-card">
            <div class="onesignal-card-header">
                <h3 class="onesignal-card-title"><?php _e('System Status', 'onesignal-pwa'); ?></h3>
            </div>
            <div class="onesignal-card-content">
                <div class="onesignal-flex onesignal-flex-col onesignal-gap-3">
                    <div class="onesignal-flex onesignal-items-center onesignal-justify-between">
                        <span class="onesignal-small"><?php _e('OneSignal API', 'onesignal-pwa'); ?></span>
                        <span class="onesignal-badge onesignal-badge-success"><?php _e('Connected', 'onesignal-pwa'); ?></span>
                    </div>
                    <div class="onesignal-flex onesignal-items-center onesignal-justify-between">
                        <span class="onesignal-small"><?php _e('Service Worker', 'onesignal-pwa'); ?></span>
                        <span class="onesignal-badge onesignal-badge-success"><?php _e('Active', 'onesignal-pwa'); ?></span>
                    </div>
                    <div class="onesignal-flex onesignal-items-center onesignal-justify-between">
                        <span class="onesignal-small"><?php _e('PWA Manifest', 'onesignal-pwa'); ?></span>
                        <span class="onesignal-badge onesignal-badge-success"><?php _e('Valid', 'onesignal-pwa'); ?></span>
                    </div>
                    <div class="onesignal-flex onesignal-items-center onesignal-justify-between">
                        <span class="onesignal-small"><?php _e('HTTPS', 'onesignal-pwa'); ?></span>
                        <span class="onesignal-badge onesignal-badge-<?php echo is_ssl() ? 'success' : 'destructive'; ?>">
                            <?php echo is_ssl() ? __('Enabled', 'onesignal-pwa') : __('Disabled', 'onesignal-pwa'); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="onesignal-card">
            <div class="onesignal-card-header">
                <h3 class="onesignal-card-title"><?php _e('Recent Activity', 'onesignal-pwa'); ?></h3>
            </div>
            <div class="onesignal-card-content">
                <div class="onesignal-flex onesignal-flex-col onesignal-gap-3">
                    <?php if (!empty($recent_activity)): ?>
                        <?php foreach (array_slice($recent_activity, 0, 4) as $activity): ?>
                            <div class="onesignal-flex onesignal-gap-2">
                                <span class="dashicons dashicons-<?php echo esc_attr($activity['icon']); ?>" style="margin-top: 2px; color: hsl(var(--muted-foreground));"></span>
                                <div>
                                    <div class="onesignal-small"><?php echo esc_html($activity['message']); ?></div>
                                    <div class="onesignal-small onesignal-muted"><?php echo human_time_diff(strtotime($activity['timestamp']), current_time('timestamp')); ?> <?php _e('ago', 'onesignal-pwa'); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="onesignal-small onesignal-muted onesignal-text-center">
                            <?php _e('No recent activity', 'onesignal-pwa'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Integration -->
<script>
jQuery(document).ready(function($) {
    // Subscriber Growth Chart
    const subscriberCtx = document.getElementById('subscriberGrowthChart');
    if (subscriberCtx) {
        new Chart(subscriberCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($stats['subscriber_growth'], 'date')); ?>,
                datasets: [{
                    label: '<?php _e('Subscribers', 'onesignal-pwa'); ?>',
                    data: <?php echo json_encode(array_column($stats['subscriber_growth'], 'count')); ?>,
                    borderColor: 'hsl(var(--chart-1))',
                    backgroundColor: 'hsl(var(--chart-1) / 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Notification Performance Chart
    const performanceCtx = document.getElementById('notificationPerformanceChart');
    if (performanceCtx) {
        new Chart(performanceCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($stats['notification_performance'], 'date')); ?>,
                datasets: [
                    {
                        label: '<?php _e('Delivered', 'onesignal-pwa'); ?>',
                        data: <?php echo json_encode(array_column($stats['notification_performance'], 'delivered')); ?>,
                        backgroundColor: 'hsl(var(--chart-1) / 0.8)'
                    },
                    {
                        label: '<?php _e('Clicked', 'onesignal-pwa'); ?>',
                        data: <?php echo json_encode(array_column($stats['notification_performance'], 'clicked')); ?>,
                        backgroundColor: 'hsl(var(--chart-2) / 0.8)'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Device Distribution Chart
    const deviceCtx = document.getElementById('deviceDistributionChart');
    if (deviceCtx) {
        new Chart(deviceCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($stats['device_distribution'], 'device')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($stats['device_distribution'], 'count')); ?>,
                    backgroundColor: [
                        'hsl(var(--chart-1))',
                        'hsl(var(--chart-2))',
                        'hsl(var(--chart-3))',
                        'hsl(var(--chart-4))',
                        'hsl(var(--chart-5))'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Browser Distribution Chart
    const browserCtx = document.getElementById('browserDistributionChart');
    if (browserCtx) {
        new Chart(browserCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($stats['browser_distribution'], 'browser')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($stats['browser_distribution'], 'count')); ?>,
                    backgroundColor: [
                        'hsl(var(--chart-1))',
                        'hsl(var(--chart-2))',
                        'hsl(var(--chart-3))',
                        'hsl(var(--chart-4))',
                        'hsl(var(--chart-5))'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
});

function viewNotificationDetails(notificationId) {
    window.location.href = '<?php echo admin_url('admin.php?page=onesignal-pwa-notifications&action=view&id='); ?>' + notificationId;
}
</script>
