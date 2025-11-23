/**
 * OneSignal PWA Admin JavaScript
 *
 * @package OneSignal_PWA
 */

(function($) {
    'use strict';

    /**
     * Initialize
     */
    function init() {
        // Test API connection
        $('#test-api-connection').on('click', testAPIConnection);

        // Preview notification
        $('#preview-notification').on('click', previewNotification);

        // Send test notification
        $('#send-test-notification').on('click', sendTestNotification);

        // Load analytics
        if ($('#analytics-chart').length) {
            loadAnalytics();
        }

        // Color picker
        if ($.fn.wpColorPicker) {
            $('.color-picker').wpColorPicker();
        }

        // Segment builder
        initSegmentBuilder();

        // Initialize charts
        initCharts();
    }

    /**
     * Test API connection
     */
    function testAPIConnection(e) {
        e.preventDefault();

        const $button = $(this);
        const originalText = $button.text();

        $button.text(onesignalPWA.strings.saving).prop('disabled', true);

        $.ajax({
            url: onesignalPWA.restUrl + 'status',
            method: 'GET',
            headers: {
                'X-WP-Nonce': onesignalPWA.nonce
            },
            success: function(response) {
                if (response.onesignal_configured) {
                    showNotice('API connection successful!', 'success');
                } else {
                    showNotice('API credentials not configured', 'warning');
                }
            },
            error: function(xhr) {
                showNotice('API connection failed: ' + xhr.responseJSON.message, 'error');
            },
            complete: function() {
                $button.text(originalText).prop('disabled', false);
            }
        });
    }

    /**
     * Preview notification
     */
    function previewNotification(e) {
        e.preventDefault();

        const title = $('#notification_title').val();
        const message = $('#notification_message').val();
        const icon = $('#notification_icon').val();

        // Create preview
        const preview = `
            <div class="notification-preview">
                <div class="notification-icon">
                    <img src="${icon || onesignalPWA.defaultIcon}" alt="Icon">
                </div>
                <div class="notification-content">
                    <div class="notification-title">${title}</div>
                    <div class="notification-message">${message}</div>
                </div>
            </div>
        `;

        $('#notification-preview-container').html(preview);
    }

    /**
     * Send test notification
     */
    function sendTestNotification(e) {
        e.preventDefault();

        const data = {
            title: $('#notification_title').val(),
            message: $('#notification_message').val(),
            url: $('#notification_url').val(),
            send_now: true
        };

        $.ajax({
            url: onesignalPWA.restUrl + 'notifications',
            method: 'POST',
            headers: {
                'X-WP-Nonce': onesignalPWA.nonce
            },
            data: JSON.stringify(data),
            contentType: 'application/json',
            success: function(response) {
                showNotice('Test notification sent successfully!', 'success');
            },
            error: function(xhr) {
                showNotice('Failed to send notification: ' + xhr.responseJSON.message, 'error');
            }
        });
    }

    /**
     * Load analytics
     */
    function loadAnalytics() {
        $.ajax({
            url: onesignalPWA.restUrl + 'analytics/overview',
            method: 'GET',
            headers: {
                'X-WP-Nonce': onesignalPWA.nonce
            },
            success: function(response) {
                updateAnalyticsDisplay(response);
            }
        });
    }

    /**
     * Update analytics display
     */
    function updateAnalyticsDisplay(data) {
        $('#total-subscribers').text(data.total_subscribers);
        $('#new-subscribers').text(data.new_subscribers);
        $('#total-notifications').text(data.total_notifications);
        $('#average-ctr').text(data.ctr + '%');
        $('#pwa-installs').text(data.pwa_installs);
    }

    /**
     * Initialize segment builder
     */
    function initSegmentBuilder() {
        $('#add-segment-rule').on('click', function() {
            const ruleTemplate = `
                <div class="segment-rule">
                    <select name="segment_rules[field][]">
                        <option value="created_at">Subscription Date</option>
                        <option value="last_session">Last Session</option>
                        <option value="country">Country</option>
                        <option value="device_type">Device Type</option>
                    </select>
                    <select name="segment_rules[operator][]">
                        <option value="equals">Equals</option>
                        <option value="not_equals">Not Equals</option>
                        <option value="greater_than">Greater Than</option>
                        <option value="less_than">Less Than</option>
                    </select>
                    <input type="text" name="segment_rules[value][]" placeholder="Value">
                    <button type="button" class="remove-rule">Remove</button>
                </div>
            `;
            $('#segment-rules').append(ruleTemplate);
        });

        $(document).on('click', '.remove-rule', function() {
            $(this).closest('.segment-rule').remove();
        });
    }

    /**
     * Initialize charts
     */
    function initCharts() {
        // Placeholder for Chart.js integration
        if (typeof Chart !== 'undefined' && $('#subscriber-growth-chart').length) {
            // Load and display subscriber growth chart
            loadSubscriberGrowthChart();
        }
    }

    /**
     * Load subscriber growth chart
     */
    function loadSubscriberGrowthChart() {
        $.ajax({
            url: onesignalPWA.restUrl + 'analytics/subscribers',
            method: 'GET',
            headers: {
                'X-WP-Nonce': onesignalPWA.nonce
            },
            success: function(response) {
                // Chart implementation would go here
                console.log('Subscriber growth data:', response);
            }
        });
    }

    /**
     * Show notice
     */
    function showNotice(message, type) {
        const noticeClass = 'notice notice-' + type;
        const notice = `
            <div class="${noticeClass} is-dismissible">
                <p>${message}</p>
            </div>
        `;

        $('.wrap h1').after(notice);

        setTimeout(function() {
            $('.notice').fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Initialize on document ready
    $(document).ready(init);

})(jQuery);
