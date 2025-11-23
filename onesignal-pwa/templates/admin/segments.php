<?php
/**
 * Segments Template - Complete Shadcn UI with Visual Rule Builder
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
$segment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($action === 'edit' && $segment_id) {
    $segment = OneSignal_PWA_Segment::get($segment_id);
    $segment_rules = json_decode($segment->rules, true);
} elseif ($action === 'create') {
    $segment = null;
    $segment_rules = array();
}
?>

<div class="wrap onesignal-pwa-wrap">
    <?php if ($action === 'list'): ?>
        <!-- Segments List View -->
        <div class="onesignal-flex onesignal-items-center onesignal-justify-between onesignal-mb-6">
            <div>
                <h1 class="onesignal-h1 onesignal-mb-2"><?php _e('Subscriber Segments', 'onesignal-pwa'); ?></h1>
                <p class="onesignal-p"><?php _e('Create targeted groups of subscribers based on their attributes', 'onesignal-pwa'); ?></p>
            </div>
            <a href="<?php echo admin_url('admin.php?page=onesignal-pwa-segments&action=create'); ?>" class="onesignal-btn onesignal-btn-primary">
                <span class="dashicons dashicons-plus-alt" style="margin-top: 4px;"></span>
                <?php _e('Create Segment', 'onesignal-pwa'); ?>
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="onesignal-grid onesignal-grid-cols-4 onesignal-mb-6">
            <div class="onesignal-stat-card">
                <div class="onesignal-stat-label"><?php _e('Total Segments', 'onesignal-pwa'); ?></div>
                <div class="onesignal-stat-value"><?php echo number_format(count($segments)); ?></div>
            </div>
            <div class="onesignal-stat-card">
                <div class="onesignal-stat-label"><?php _e('Active Segments', 'onesignal-pwa'); ?></div>
                <div class="onesignal-stat-value">
                    <?php echo number_format(count(array_filter($segments, function($s) { return $s->status === 'active'; }))); ?>
                </div>
            </div>
            <div class="onesignal-stat-card">
                <div class="onesignal-stat-label"><?php _e('Default Segments', 'onesignal-pwa'); ?></div>
                <div class="onesignal-stat-value">4</div>
            </div>
            <div class="onesignal-stat-card">
                <div class="onesignal-stat-label"><?php _e('Avg Segment Size', 'onesignal-pwa'); ?></div>
                <div class="onesignal-stat-value">
                    <?php
                    $avg = !empty($segments) ? array_sum(array_column($segments, 'estimated_size')) / count($segments) : 0;
                    echo number_format($avg);
                    ?>
                </div>
            </div>
        </div>

        <!-- Segments Table -->
        <div class="onesignal-card">
            <div class="onesignal-card-header">
                <div class="onesignal-flex onesignal-items-center onesignal-justify-between">
                    <h3 class="onesignal-card-title"><?php _e('All Segments', 'onesignal-pwa'); ?></h3>
                    <input type="search" class="onesignal-input" style="max-width: 300px;" placeholder="<?php _e('Search segments...', 'onesignal-pwa'); ?>">
                </div>
            </div>
            <div class="onesignal-card-content" style="padding: 0;">
                <div class="onesignal-table-wrapper">
                    <table class="onesignal-table">
                        <thead>
                            <tr>
                                <th><?php _e('Name', 'onesignal-pwa'); ?></th>
                                <th><?php _e('Description', 'onesignal-pwa'); ?></th>
                                <th><?php _e('Size', 'onesignal-pwa'); ?></th>
                                <th><?php _e('Status', 'onesignal-pwa'); ?></th>
                                <th><?php _e('Created', 'onesignal-pwa'); ?></th>
                                <th><?php _e('Actions', 'onesignal-pwa'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($segments)): ?>
                                <?php foreach ($segments as $seg): ?>
                                    <tr>
                                        <td><strong><?php echo esc_html($seg->name); ?></strong></td>
                                        <td><?php echo esc_html($seg->description); ?></td>
                                        <td><?php echo number_format($seg->estimated_size); ?> <?php _e('subscribers', 'onesignal-pwa'); ?></td>
                                        <td>
                                            <span class="onesignal-badge onesignal-badge-<?php echo $seg->status === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo esc_html(ucfirst($seg->status)); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date_i18n(get_option('date_format'), strtotime($seg->created_at)); ?></td>
                                        <td>
                                            <div class="onesignal-flex onesignal-gap-1">
                                                <a href="<?php echo admin_url('admin.php?page=onesignal-pwa-segments&action=edit&id=' . $seg->id); ?>" class="onesignal-btn onesignal-btn-ghost onesignal-btn-sm">
                                                    <span class="dashicons dashicons-edit" style="font-size: 16px;"></span>
                                                </a>
                                                <button onclick="deleteSegment(<?php echo $seg->id; ?>)" class="onesignal-btn onesignal-btn-ghost onesignal-btn-sm">
                                                    <span class="dashicons dashicons-trash" style="font-size: 16px;"></span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="onesignal-table-empty">
                                        <span class="dashicons dashicons-groups" style="font-size: 48px; opacity: 0.3;"></span>
                                        <br>
                                        <?php _e('No segments created yet.', 'onesignal-pwa'); ?>
                                        <br><br>
                                        <a href="<?php echo admin_url('admin.php?page=onesignal-pwa-segments&action=create'); ?>" class="onesignal-btn onesignal-btn-primary onesignal-btn-sm">
                                            <?php _e('Create Your First Segment', 'onesignal-pwa'); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php elseif ($action === 'create' || $action === 'edit'): ?>
        <!-- Create/Edit Segment View -->
        <div class="onesignal-flex onesignal-items-center onesignal-justify-between onesignal-mb-6">
            <div>
                <h1 class="onesignal-h1 onesignal-mb-2">
                    <?php echo $action === 'create' ? __('Create Segment', 'onesignal-pwa') : __('Edit Segment', 'onesignal-pwa'); ?>
                </h1>
                <p class="onesignal-p"><?php _e('Define rules to automatically group subscribers', 'onesignal-pwa'); ?></p>
            </div>
            <a href="<?php echo admin_url('admin.php?page=onesignal-pwa-segments'); ?>" class="onesignal-btn onesignal-btn-outline">
                <span class="dashicons dashicons-arrow-left-alt" style="margin-top: 4px;"></span>
                <?php _e('Back to Segments', 'onesignal-pwa'); ?>
            </a>
        </div>

        <form method="post" id="segment-form">
            <?php wp_nonce_field($action === 'create' ? 'create_segment' : 'edit_segment'); ?>
            <input type="hidden" name="action" value="<?php echo $action; ?>">
            <?php if ($segment_id): ?>
                <input type="hidden" name="segment_id" value="<?php echo $segment_id; ?>">
            <?php endif; ?>

            <div class="onesignal-grid onesignal-grid-cols-3 onesignal-gap-6">
                <!-- Main Form Column -->
                <div style="grid-column: span 2 / span 2;">
                    <!-- Basic Info Card -->
                    <div class="onesignal-card onesignal-mb-6">
                        <div class="onesignal-card-header">
                            <h3 class="onesignal-card-title"><?php _e('Segment Details', 'onesignal-pwa'); ?></h3>
                        </div>
                        <div class="onesignal-card-content">
                            <div class="onesignal-form-row">
                                <label for="segment-name" class="onesignal-label">
                                    <?php _e('Segment Name', 'onesignal-pwa'); ?> *
                                </label>
                                <input
                                    type="text"
                                    id="segment-name"
                                    name="segment_name"
                                    class="onesignal-input"
                                    required
                                    value="<?php echo $segment ? esc_attr($segment->name) : ''; ?>"
                                    placeholder="<?php _e('e.g., Active Mobile Users', 'onesignal-pwa'); ?>"
                                >
                            </div>

                            <div class="onesignal-form-row">
                                <label for="segment-description" class="onesignal-label">
                                    <?php _e('Description', 'onesignal-pwa'); ?>
                                </label>
                                <textarea
                                    id="segment-description"
                                    name="segment_description"
                                    class="onesignal-textarea"
                                    rows="2"
                                    placeholder="<?php _e('Describe this segment...', 'onesignal-pwa'); ?>"
                                ><?php echo $segment ? esc_textarea($segment->description) : ''; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Segment Rules Card -->
                    <div class="onesignal-card onesignal-mb-6">
                        <div class="onesignal-card-header">
                            <div class="onesignal-flex onesignal-items-center onesignal-justify-between">
                                <div>
                                    <h3 class="onesignal-card-title"><?php _e('Segment Rules', 'onesignal-pwa'); ?></h3>
                                    <p class="onesignal-card-description"><?php _e('Subscribers must match ALL rules', 'onesignal-pwa'); ?></p>
                                </div>
                                <button type="button" id="add-rule" class="onesignal-btn onesignal-btn-outline onesignal-btn-sm">
                                    <span class="dashicons dashicons-plus-alt" style="margin-top: 4px;"></span>
                                    <?php _e('Add Rule', 'onesignal-pwa'); ?>
                                </button>
                            </div>
                        </div>
                        <div class="onesignal-card-content">
                            <div id="segment-rules-container">
                                <?php if (!empty($segment_rules)): ?>
                                    <?php foreach ($segment_rules as $index => $rule): ?>
                                        <?php include ONESIGNAL_PWA_PLUGIN_DIR . 'templates/admin/partials/segment-rule.php'; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="onesignal-alert onesignal-alert-default">
                                        <div class="onesignal-alert-description">
                                            <?php _e('No rules defined. Click "Add Rule" to start building your segment.', 'onesignal-pwa'); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="onesignal-flex onesignal-gap-2">
                        <button type="submit" name="save_segment" class="onesignal-btn onesignal-btn-primary">
                            <span class="dashicons dashicons-saved" style="margin-top: 4px;"></span>
                            <?php echo $action === 'create' ? __('Create Segment', 'onesignal-pwa') : __('Update Segment', 'onesignal-pwa'); ?>
                        </button>
                        <button type="button" id="test-segment" class="onesignal-btn onesignal-btn-outline">
                            <span class="dashicons dashicons-yes-alt" style="margin-top: 4px;"></span>
                            <?php _e('Test Rules', 'onesignal-pwa'); ?>
                        </button>
                        <a href="<?php echo admin_url('admin.php?page=onesignal-pwa-segments'); ?>" class="onesignal-btn onesignal-btn-ghost">
                            <?php _e('Cancel', 'onesignal-pwa'); ?>
                        </a>
                    </div>
                </div>

                <!-- Preview Column -->
                <div>
                    <div class="onesignal-card" style="position: sticky; top: 32px;">
                        <div class="onesignal-card-header">
                            <h3 class="onesignal-card-title"><?php _e('Segment Preview', 'onesignal-pwa'); ?></h3>
                        </div>
                        <div class="onesignal-card-content">
                            <div class="onesignal-mb-4">
                                <div class="onesignal-flex onesignal-justify-between onesignal-mb-2">
                                    <span class="onesignal-small onesignal-font-medium"><?php _e('Estimated Size', 'onesignal-pwa'); ?></span>
                                    <span class="onesignal-small onesignal-font-bold" id="segment-size">0</span>
                                </div>
                                <div class="onesignal-flex onesignal-justify-between onesignal-mb-2">
                                    <span class="onesignal-small onesignal-font-medium"><?php _e('% of Total', 'onesignal-pwa'); ?></span>
                                    <span class="onesignal-small onesignal-font-bold" id="segment-percentage">0%</span>
                                </div>
                                <div class="onesignal-progress onesignal-mt-3">
                                    <div id="segment-progress" class="onesignal-progress-bar" style="width: 0%;"></div>
                                </div>
                            </div>

                            <div class="onesignal-mb-4">
                                <h4 class="onesignal-h4 onesignal-mb-2"><?php _e('Rule Summary', 'onesignal-pwa'); ?></h4>
                                <div id="rule-summary" class="onesignal-small onesignal-muted">
                                    <?php _e('No rules defined', 'onesignal-pwa'); ?>
                                </div>
                            </div>

                            <div>
                                <h4 class="onesignal-h4 onesignal-mb-2"><?php _e('Quick Templates', 'onesignal-pwa'); ?></h4>
                                <div class="onesignal-flex onesignal-flex-col onesignal-gap-2">
                                    <button type="button" class="onesignal-btn onesignal-btn-outline onesignal-btn-sm template-btn" data-template="mobile">
                                        <?php _e('Mobile Users', 'onesignal-pwa'); ?>
                                    </button>
                                    <button type="button" class="onesignal-btn onesignal-btn-outline onesignal-btn-sm template-btn" data-template="active">
                                        <?php _e('Active Users (7d)', 'onesignal-pwa'); ?>
                                    </button>
                                    <button type="button" class="onesignal-btn onesignal-btn-outline onesignal-btn-sm template-btn" data-template="chrome">
                                        <?php _e('Chrome Users', 'onesignal-pwa'); ?>
                                    </button>
                                    <button type="button" class="onesignal-btn onesignal-btn-outline onesignal-btn-sm template-btn" data-template="us">
                                        <?php _e('US Subscribers', 'onesignal-pwa'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<!-- Rule Template -->
<template id="segment-rule-template">
    <div class="onesignal-segment-rule">
        <div class="onesignal-segment-rule-row">
            <div class="onesignal-segment-rule-field">
                <select name="rules[__INDEX__][field]" class="onesignal-select rule-field">
                    <option value=""><?php _e('Select Field', 'onesignal-pwa'); ?></option>
                    <option value="device_type"><?php _e('Device Type', 'onesignal-pwa'); ?></option>
                    <option value="browser"><?php _e('Browser', 'onesignal-pwa'); ?></option>
                    <option value="os"><?php _e('Operating System', 'onesignal-pwa'); ?></option>
                    <option value="country"><?php _e('Country', 'onesignal-pwa'); ?></option>
                    <option value="language"><?php _e('Language', 'onesignal-pwa'); ?></option>
                    <option value="last_session"><?php _e('Last Session', 'onesignal-pwa'); ?></option>
                    <option value="session_count"><?php _e('Session Count', 'onesignal-pwa'); ?></option>
                    <option value="subscription_status"><?php _e('Subscription Status', 'onesignal-pwa'); ?></option>
                </select>
            </div>
            <div class="onesignal-segment-rule-operator">
                <select name="rules[__INDEX__][operator]" class="onesignal-select rule-operator">
                    <option value="equals"><?php _e('Equals', 'onesignal-pwa'); ?></option>
                    <option value="not_equals"><?php _e('Not Equals', 'onesignal-pwa'); ?></option>
                    <option value="contains"><?php _e('Contains', 'onesignal-pwa'); ?></option>
                    <option value="greater_than"><?php _e('Greater Than', 'onesignal-pwa'); ?></option>
                    <option value="less_than"><?php _e('Less Than', 'onesignal-pwa'); ?></option>
                </select>
            </div>
            <div class="onesignal-segment-rule-value">
                <input type="text" name="rules[__INDEX__][value]" class="onesignal-input rule-value" placeholder="<?php _e('Value', 'onesignal-pwa'); ?>">
            </div>
            <div class="onesignal-segment-rule-remove">
                <button type="button" class="onesignal-btn onesignal-btn-destructive onesignal-btn-sm remove-rule">
                    <span class="dashicons dashicons-trash" style="font-size: 16px;"></span>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
jQuery(document).ready(function($) {
    let ruleIndex = <?php echo !empty($segment_rules) ? count($segment_rules) : 0; ?>;

    // Add new rule
    $('#add-rule').on('click', function() {
        const template = $('#segment-rule-template').html();
        const html = template.replace(/__INDEX__/g, ruleIndex++);

        if ($('#segment-rules-container .onesignal-alert').length) {
            $('#segment-rules-container').html(html);
        } else {
            $('#segment-rules-container').append(html);
        }

        updateRuleSummary();
    });

    // Remove rule
    $(document).on('click', '.remove-rule', function() {
        $(this).closest('.onesignal-segment-rule').remove();
        if ($('#segment-rules-container .onesignal-segment-rule').length === 0) {
            $('#segment-rules-container').html('<div class="onesignal-alert onesignal-alert-default"><div class="onesignal-alert-description"><?php _e('No rules defined. Click "Add Rule" to start building your segment.', 'onesignal-pwa'); ?></div></div>');
        }
        updateRuleSummary();
    });

    // Update rule summary
    function updateRuleSummary() {
        const rules = [];
        $('.onesignal-segment-rule').each(function() {
            const field = $(this).find('.rule-field option:selected').text();
            const operator = $(this).find('.rule-operator option:selected').text();
            const value = $(this).find('.rule-value').val();
            if (field && operator && value) {
                rules.push(`${field} ${operator} "${value}"`);
            }
        });

        if (rules.length > 0) {
            $('#rule-summary').html(rules.join('<br>'));
        } else {
            $('#rule-summary').html('<?php _e('No rules defined', 'onesignal-pwa'); ?>');
        }
    }

    // Template buttons
    $('.template-btn').on('click', function() {
        const template = $(this).data('template');
        $('#segment-rules-container').empty();

        if (template === 'mobile') {
            addTemplateRule('device_type', 'equals', 'Mobile');
        } else if (template === 'active') {
            addTemplateRule('last_session', 'greater_than', '7 days ago');
        } else if (template === 'chrome') {
            addTemplateRule('browser', 'equals', 'Chrome');
        } else if (template === 'us') {
            addTemplateRule('country', 'equals', 'US');
        }
    });

    function addTemplateRule(field, operator, value) {
        const template = $('#segment-rule-template').html();
        const html = $(template.replace(/__INDEX__/g, ruleIndex++));

        html.find('.rule-field').val(field);
        html.find('.rule-operator').val(operator);
        html.find('.rule-value').val(value);

        $('#segment-rules-container').append(html);
        updateRuleSummary();
    }

    // Test segment
    $('#test-segment').on('click', function() {
        // Calculate segment size via AJAX
        alert('<?php _e('Segment testing feature coming soon!', 'onesignal-pwa'); ?>');
    });
});

function deleteSegment(id) {
    if (confirm(onesignalPWA.strings.confirmDelete)) {
        // Delete via AJAX
        jQuery.post(ajaxurl, {
            action: 'delete_segment',
            segment_id: id,
            nonce: onesignalPWA.nonce
        }, function(response) {
            location.reload();
        });
    }
}
</script>
