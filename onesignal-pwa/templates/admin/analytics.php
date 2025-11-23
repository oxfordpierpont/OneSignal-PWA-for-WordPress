<?php
/**
 * Analytics Template - Complete Dashboard with Advanced Metrics
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Date range filter
$date_range = isset($_GET['range']) ? sanitize_text_field($_GET['range']) : '30';
$start_date = date('Y-m-d', strtotime("-{$date_range} days"));
$end_date = date('Y-m-d');
?>

<div class="wrap onesignal-pwa-wrap">
    <div class="onesignal-flex onesignal-items-center onesignal-justify-between onesignal-mb-6">
        <div>
            <h1 class="onesignal-h1 onesignal-mb-2"><?php _e('Analytics Dashboard', 'onesignal-pwa'); ?></h1>
            <p class="onesignal-p"><?php _e('Comprehensive analytics and performance metrics', 'onesignal-pwa'); ?></p>
        </div>
        <div class="onesignal-flex onesignal-gap-2">
            <select id="date-range-filter" class="onesignal-select" style="width: auto;">
                <option value="7" <?php selected($date_range, '7'); ?>><?php _e('Last 7 days', 'onesignal-pwa'); ?></option>
                <option value="30" <?php selected($date_range, '30'); ?>><?php _e('Last 30 days', 'onesignal-pwa'); ?></option>
                <option value="90" <?php selected($date_range, '90'); ?>><?php _e('Last 90 days', 'onesignal-pwa'); ?></option>
                <option value="365" <?php selected($date_range, '365'); ?>><?php _e('Last year', 'onesignal-pwa'); ?></option>
            </select>
            <button class="onesignal-btn onesignal-btn-outline" onclick="window.print();">
                <span class="dashicons dashicons-download" style="margin-top: 4px;"></span>
                <?php _e('Export', 'onesignal-pwa'); ?>
            </button>
        </div>
    </div>

    <!-- Key Metrics Overview -->
    <div class="onesignal-grid onesignal-grid-cols-4 onesignal-mb-6">
        <div class="onesignal-stat-card">
            <div class="onesignal-stat-label"><?php _e('Total Impressions', 'onesignal-pwa'); ?></div>
            <div class="onesignal-stat-value"><?php echo number_format($analytics_data['total_impressions']); ?></div>
            <div class="onesignal-stat-change <?php echo $analytics_data['impressions_change'] >= 0 ? 'positive' : 'negative'; ?>">
                <span class="dashicons dashicons-arrow-<?php echo $analytics_data['impressions_change'] >= 0 ? 'up' : 'down'; ?>-alt" style="font-size: 12px;"></span>
                <?php echo abs($analytics_data['impressions_change']); ?>%
            </div>
        </div>

        <div class="onesignal-stat-card">
            <div class="onesignal-stat-label"><?php _e('Total Clicks', 'onesignal-pwa'); ?></div>
            <div class="onesignal-stat-value"><?php echo number_format($analytics_data['total_clicks']); ?></div>
            <div class="onesignal-stat-change <?php echo $analytics_data['clicks_change'] >= 0 ? 'positive' : 'negative'; ?>">
                <span class="dashicons dashicons-arrow-<?php echo $analytics_data['clicks_change'] >= 0 ? 'up' : 'down'; ?>-alt" style="font-size: 12px;"></span>
                <?php echo abs($analytics_data['clicks_change']); ?>%
            </div>
        </div>

        <div class="onesignal-stat-card">
            <div class="onesignal-stat-label"><?php _e('Average CTR', 'onesignal-pwa'); ?></div>
            <div class="onesignal-stat-value"><?php echo $analytics_data['avg_ctr']; ?>%</div>
            <div class="onesignal-stat-change <?php echo $analytics_data['ctr_change'] >= 0 ? 'positive' : 'negative'; ?>">
                <span class="dashicons dashicons-arrow-<?php echo $analytics_data['ctr_change'] >= 0 ? 'up' : 'down'; ?>-alt" style="font-size: 12px;"></span>
                <?php echo abs($analytics_data['ctr_change']); ?>%
            </div>
        </div>

        <div class="onesignal-stat-card">
            <div class="onesignal-stat-label"><?php _e('Conversion Rate', 'onesignal-pwa'); ?></div>
            <div class="onesignal-stat-value"><?php echo $analytics_data['conversion_rate']; ?>%</div>
            <div class="onesignal-stat-change <?php echo $analytics_data['conversion_change'] >= 0 ? 'positive' : 'negative'; ?>">
                <span class="dashicons dashicons-arrow-<?php echo $analytics_data['conversion_change'] >= 0 ? 'up' : 'down'; ?>-alt" style="font-size: 12px;"></span>
                <?php echo abs($analytics_data['conversion_change']); ?>%
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="onesignal-grid onesignal-grid-cols-2 onesignal-mb-6">
        <!-- Performance Over Time -->
        <div class="onesignal-card">
            <div class="onesignal-card-header">
                <h3 class="onesignal-card-title"><?php _e('Performance Over Time', 'onesignal-pwa'); ?></h3>
                <p class="onesignal-card-description"><?php _e('Impressions and clicks trend', 'onesignal-pwa'); ?></p>
            </div>
            <div class="onesignal-card-content">
                <div class="onesignal-chart-container">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
        </div>

        <!-- CTR Trend -->
        <div class="onesignal-card">
            <div class="onesignal-card-header">
                <h3 class="onesignal-card-title"><?php _e('Click-Through Rate Trend', 'onesignal-pwa'); ?></h3>
                <p class="onesignal-card-description"><?php _e('Daily CTR performance', 'onesignal-pwa'); ?></p>
            </div>
            <div class="onesignal-card-content">
                <div class="onesignal-chart-container">
                    <canvas id="ctrChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Engagement Metrics -->
    <div class="onesignal-grid onesignal-grid-cols-3 onesignal-mb-6">
        <div class="onesignal-card">
            <div class="onesignal-card-header">
                <h3 class="onesignal-card-title"><?php _e('Top Performing Notifications', 'onesignal-pwa'); ?></h3>
            </div>
            <div class="onesignal-card-content">
                <div class="onesignal-table-wrapper">
                    <table class="onesignal-table">
                        <thead>
                            <tr>
                                <th><?php _e('Title', 'onesignal-pwa'); ?></th>
                                <th><?php _e('CTR', 'onesignal-pwa'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($analytics_data['top_notifications'])): ?>
                                <?php foreach ($analytics_data['top_notifications'] as $notif): ?>
                                    <tr>
                                        <td><?php echo esc_html(wp_trim_words($notif->title, 8)); ?></td>
                                        <td><strong><?php echo $notif->ctr; ?>%</strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="2" class="onesignal-text-center onesignal-muted"><?php _e('No data yet', 'onesignal-pwa'); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="onesignal-card">
            <div class="onesignal-card-header">
                <h3 class="onesignal-card-title"><?php _e('Subscriber Activity', 'onesignal-pwa'); ?></h3>
            </div>
            <div class="onesignal-card-content">
                <div class="onesignal-chart-container" style="height: 250px;">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
        </div>

        <div class="onesignal-card">
            <div class="onesignal-card-header">
                <h3 class="onesignal-card-title"><?php _e('Engagement by Time', 'onesignal-pwa'); ?></h3>
            </div>
            <div class="onesignal-card-content">
                <div class="onesignal-chart-container" style="height: 250px;">
                    <canvas id="timeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Metrics Table -->
    <div class="onesignal-card">
        <div class="onesignal-card-header">
            <h3 class="onesignal-card-title"><?php _e('Detailed Campaign Performance', 'onesignal-pwa'); ?></h3>
        </div>
        <div class="onesignal-card-content" style="padding: 0;">
            <div class="onesignal-table-wrapper">
                <table class="onesignal-table">
                    <thead>
                        <tr>
                            <th><?php _e('Date', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Campaign', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Sent', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Delivered', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Clicked', 'onesignal-pwa'); ?></th>
                            <th><?php _e('CTR', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Conversions', 'onesignal-pwa'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($analytics_data['campaign_details'])): ?>
                            <?php foreach ($analytics_data['campaign_details'] as $campaign): ?>
                                <tr>
                                    <td><?php echo date_i18n(get_option('date_format'), strtotime($campaign->sent_at)); ?></td>
                                    <td><strong><?php echo esc_html($campaign->title); ?></strong></td>
                                    <td><?php echo number_format($campaign->sent); ?></td>
                                    <td><?php echo number_format($campaign->delivered); ?></td>
                                    <td><?php echo number_format($campaign->clicked); ?></td>
                                    <td>
                                        <span class="onesignal-badge onesignal-badge-<?php echo $campaign->ctr > 5 ? 'success' : ($campaign->ctr > 2 ? 'warning' : 'secondary'); ?>">
                                            <?php echo $campaign->ctr; ?>%
                                        </span>
                                    </td>
                                    <td><?php echo number_format($campaign->conversions); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="onesignal-table-empty"><?php _e('No campaign data available', 'onesignal-pwa'); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Performance Over Time Chart
    new Chart(document.getElementById('performanceChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($analytics_data['performance_timeline'], 'date')); ?>,
            datasets: [{
                label: '<?php _e('Impressions', 'onesignal-pwa'); ?>',
                data: <?php echo json_encode(array_column($analytics_data['performance_timeline'], 'impressions')); ?>,
                borderColor: 'hsl(var(--chart-1))',
                backgroundColor: 'hsl(var(--chart-1) / 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: '<?php _e('Clicks', 'onesignal-pwa'); ?>',
                data: <?php echo json_encode(array_column($analytics_data['performance_timeline'], 'clicks')); ?>,
                borderColor: 'hsl(var(--chart-2))',
                backgroundColor: 'hsl(var(--chart-2) / 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // CTR Chart
    new Chart(document.getElementById('ctrChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($analytics_data['ctr_timeline'], 'date')); ?>,
            datasets: [{
                label: '<?php _e('CTR %', 'onesignal-pwa'); ?>',
                data: <?php echo json_encode(array_column($analytics_data['ctr_timeline'], 'ctr')); ?>,
                backgroundColor: 'hsl(var(--chart-3) / 0.8)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // Activity Chart
    new Chart(document.getElementById('activityChart'), {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Inactive', 'Unsubscribed'],
            datasets: [{
                data: [<?php echo $analytics_data['active_subscribers']; ?>, <?php echo $analytics_data['inactive_subscribers']; ?>, <?php echo $analytics_data['unsubscribed']; ?>],
                backgroundColor: ['hsl(var(--chart-2))', 'hsl(var(--chart-3))', 'hsl(var(--destructive))']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // Time of Day Chart
    new Chart(document.getElementById('timeChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($analytics_data['hourly_engagement'], 'hour')); ?>,
            datasets: [{
                label: '<?php _e('Clicks', 'onesignal-pwa'); ?>',
                data: <?php echo json_encode(array_column($analytics_data['hourly_engagement'], 'clicks')); ?>,
                backgroundColor: 'hsl(var(--chart-4) / 0.8)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // Date range filter
    $('#date-range-filter').on('change', function() {
        window.location.href = '<?php echo admin_url('admin.php?page=onesignal-pwa-analytics'); ?>&range=' + $(this).val();
    });
});
</script>
