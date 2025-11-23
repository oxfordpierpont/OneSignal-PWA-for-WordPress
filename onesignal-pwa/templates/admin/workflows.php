<?php
/**
 * Workflows Template - Automation Builder
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
            <h1 class="onesignal-h1 onesignal-mb-2"><?php _e('Automated Workflows', 'onesignal-pwa'); ?></h1>
            <p class="onesignal-p"><?php _e('Automate your push notification campaigns', 'onesignal-pwa'); ?></p>
        </div>
        <a href="<?php echo add_query_arg('action', 'create'); ?>" class="onesignal-btn onesignal-btn-primary">
            <span class="dashicons dashicons-plus-alt" style="margin-top: 4px;"></span>
            <?php _e('Create Workflow', 'onesignal-pwa'); ?>
        </a>
    </div>

    <!-- Workflow Stats -->
    <div class="onesignal-grid onesignal-grid-cols-4 onesignal-mb-6">
        <div class="onesignal-stat-card">
            <div class="onesignal-stat-label"><?php _e('Total Workflows', 'onesignal-pwa'); ?></div>
            <div class="onesignal-stat-value"><?php echo count($workflows); ?></div>
        </div>
        <div class="onesignal-stat-card">
            <div class="onesignal-stat-label"><?php _e('Active', 'onesignal-pwa'); ?></div>
            <div class="onesignal-stat-value"><?php echo count(array_filter($workflows, fn($w) => $w->status === 'active')); ?></div>
        </div>
        <div class="onesignal-stat-card">
            <div class="onesignal-stat-label"><?php _e('Executions (30d)', 'onesignal-pwa'); ?></div>
            <div class="onesignal-stat-value">0</div>
        </div>
        <div class="onesignal-stat-card">
            <div class="onesignal-stat-label"><?php _e('Conversion Rate', 'onesignal-pwa'); ?></div>
            <div class="onesignal-stat-value">0%</div>
        </div>
    </div>

    <!-- Workflows Table -->
    <div class="onesignal-card">
        <div class="onesignal-card-header">
            <h3 class="onesignal-card-title"><?php _e('All Workflows', 'onesignal-pwa'); ?></h3>
        </div>
        <div class="onesignal-card-content" style="padding: 0;">
            <div class="onesignal-table-wrapper">
                <table class="onesignal-table">
                    <thead>
                        <tr>
                            <th><?php _e('Name', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Trigger', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Status', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Executions', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Last Run', 'onesignal-pwa'); ?></th>
                            <th><?php _e('Actions', 'onesignal-pwa'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($workflows)): ?>
                            <?php foreach ($workflows as $workflow): ?>
                                <tr>
                                    <td><strong><?php echo esc_html($workflow->name); ?></strong></td>
                                    <td><?php echo esc_html($workflow->trigger_type); ?></td>
                                    <td>
                                        <span class="onesignal-badge onesignal-badge-<?php echo $workflow->status === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo esc_html(ucfirst($workflow->status)); ?>
                                        </span>
                                    </td>
                                    <td>0</td>
                                    <td><?php echo $workflow->last_run_at ? date_i18n(get_option('date_format'), strtotime($workflow->last_run_at)) : '—'; ?></td>
                                    <td>
                                        <div class="onesignal-flex onesignal-gap-1">
                                            <a href="<?php echo add_query_arg(['action' => 'edit', 'id' => $workflow->id]); ?>" class="onesignal-btn onesignal-btn-ghost onesignal-btn-sm">
                                                <span class="dashicons dashicons-edit" style="font-size: 16px;"></span>
                                            </a>
                                            <button onclick="toggleWorkflow(<?php echo $workflow->id; ?>)" class="onesignal-btn onesignal-btn-ghost onesignal-btn-sm">
                                                <span class="dashicons dashicons-controls-<?php echo $workflow->status === 'active' ? 'pause' : 'play'; ?>" style="font-size: 16px;"></span>
                                            </button>
                                            <button onclick="deleteWorkflow(<?php echo $workflow->id; ?>)" class="onesignal-btn onesignal-btn-ghost onesignal-btn-sm">
                                                <span class="dashicons dashicons-trash" style="font-size: 16px;"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="onesignal-table-empty">
                                <span class="dashicons dashicons-networking" style="font-size: 48px; opacity: 0.3;"></span>
                                <br><?php _e('No workflows created yet', 'onesignal-pwa'); ?>
                                <br><br>
                                <a href="<?php echo add_query_arg('action', 'create'); ?>" class="onesignal-btn onesignal-btn-primary onesignal-btn-sm">
                                    <?php _e('Create Your First Workflow', 'onesignal-pwa'); ?>
                                </a>
                            </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function toggleWorkflow(id) {
    jQuery.post(ajaxurl, {
        action: 'toggle_workflow',
        workflow_id: id,
        nonce: onesignalPWA.nonce
    }, function() { location.reload(); });
}

function deleteWorkflow(id) {
    if (confirm(onesignalPWA.strings.confirmDelete)) {
        jQuery.post(ajaxurl, {
            action: 'delete_workflow',
            workflow_id: id,
            nonce: onesignalPWA.nonce
        }, function() { location.reload(); });
    }
}
</script>
