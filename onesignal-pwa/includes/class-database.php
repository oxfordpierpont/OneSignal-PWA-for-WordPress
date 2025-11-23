<?php
/**
 * Database Management Class
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OneSignal PWA Database Class
 */
class OneSignal_PWA_Database {

    /**
     * Database version
     */
    const DB_VERSION = '1.0.0';

    /**
     * Create database tables
     */
    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_prefix = $wpdb->prefix . 'onesignal_pwa_';

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Subscribers table
        $sql_subscribers = "CREATE TABLE {$table_prefix}subscribers (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            player_id VARCHAR(255) NOT NULL UNIQUE,
            external_id VARCHAR(255),
            user_id BIGINT UNSIGNED,
            email VARCHAR(255),
            tags LONGTEXT,
            segments LONGTEXT,
            device_type VARCHAR(50),
            device_model VARCHAR(100),
            browser VARCHAR(100),
            browser_version VARCHAR(50),
            os VARCHAR(100),
            os_version VARCHAR(50),
            country VARCHAR(100),
            region VARCHAR(100),
            city VARCHAR(100),
            timezone VARCHAR(100),
            language VARCHAR(10),
            first_session DATETIME NOT NULL,
            last_session DATETIME NOT NULL,
            session_count INT DEFAULT 1,
            subscription_status VARCHAR(50) DEFAULT 'subscribed',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_player_id (player_id),
            INDEX idx_external_id (external_id),
            INDEX idx_user_id (user_id),
            INDEX idx_email (email),
            INDEX idx_subscription_status (subscription_status),
            INDEX idx_created_at (created_at)
        ) $charset_collate;";

        // Notifications table
        $sql_notifications = "CREATE TABLE {$table_prefix}notifications (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            onesignal_id VARCHAR(255),
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            url VARCHAR(2083),
            icon_url VARCHAR(2083),
            large_image_url VARCHAR(2083),
            action_buttons LONGTEXT,
            segments LONGTEXT,
            filters LONGTEXT,
            custom_data LONGTEXT,
            status VARCHAR(50) DEFAULT 'draft',
            scheduled_at DATETIME,
            sent_at DATETIME,
            delivered INT DEFAULT 0,
            clicked INT DEFAULT 0,
            errored INT DEFAULT 0,
            conversion_count INT DEFAULT 0,
            revenue DECIMAL(10,2) DEFAULT 0,
            metadata LONGTEXT,
            created_by BIGINT UNSIGNED,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_scheduled_at (scheduled_at),
            INDEX idx_sent_at (sent_at),
            INDEX idx_created_by (created_by)
        ) $charset_collate;";

        // Segments table
        $sql_segments = "CREATE TABLE {$table_prefix}segments (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            rules LONGTEXT NOT NULL,
            is_dynamic BOOLEAN DEFAULT TRUE,
            estimated_size INT DEFAULT 0,
            last_calculated DATETIME,
            created_by BIGINT UNSIGNED,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_name (name),
            INDEX idx_created_by (created_by)
        ) $charset_collate;";

        // Workflows table
        $sql_workflows = "CREATE TABLE {$table_prefix}workflows (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            trigger_type VARCHAR(100) NOT NULL,
            trigger_config LONGTEXT,
            steps LONGTEXT NOT NULL,
            status VARCHAR(50) DEFAULT 'draft',
            entry_count INT DEFAULT 0,
            completion_count INT DEFAULT 0,
            conversion_count INT DEFAULT 0,
            revenue DECIMAL(10,2) DEFAULT 0,
            created_by BIGINT UNSIGNED,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            activated_at DATETIME,
            deactivated_at DATETIME,
            INDEX idx_status (status),
            INDEX idx_trigger_type (trigger_type),
            INDEX idx_created_by (created_by)
        ) $charset_collate;";

        // Workflow instances table
        $sql_workflow_instances = "CREATE TABLE {$table_prefix}workflow_instances (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            workflow_id BIGINT UNSIGNED NOT NULL,
            subscriber_id BIGINT UNSIGNED NOT NULL,
            current_step INT DEFAULT 0,
            status VARCHAR(50) DEFAULT 'active',
            started_at DATETIME NOT NULL,
            completed_at DATETIME,
            converted BOOLEAN DEFAULT FALSE,
            conversion_value DECIMAL(10,2),
            metadata LONGTEXT,
            INDEX idx_workflow_id (workflow_id),
            INDEX idx_subscriber_id (subscriber_id),
            INDEX idx_status (status)
        ) $charset_collate;";

        // Analytics table
        $sql_analytics = "CREATE TABLE {$table_prefix}analytics (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            event_type VARCHAR(100) NOT NULL,
            event_data LONGTEXT,
            subscriber_id BIGINT UNSIGNED,
            notification_id BIGINT UNSIGNED,
            workflow_id BIGINT UNSIGNED,
            session_id VARCHAR(255),
            page_url VARCHAR(2083),
            referrer_url VARCHAR(2083),
            device_type VARCHAR(50),
            browser VARCHAR(50),
            os VARCHAR(50),
            country VARCHAR(100),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_event_type (event_type),
            INDEX idx_subscriber_id (subscriber_id),
            INDEX idx_notification_id (notification_id),
            INDEX idx_workflow_id (workflow_id),
            INDEX idx_created_at (created_at)
        ) $charset_collate;";

        // Cache table
        $sql_cache = "CREATE TABLE {$table_prefix}cache (
            cache_key VARCHAR(255) NOT NULL PRIMARY KEY,
            cache_value LONGTEXT NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_expires_at (expires_at)
        ) $charset_collate;";

        // Templates table
        $sql_templates = "CREATE TABLE {$table_prefix}templates (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            type VARCHAR(50) DEFAULT 'notification',
            template_data LONGTEXT NOT NULL,
            is_default BOOLEAN DEFAULT FALSE,
            created_by BIGINT UNSIGNED,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_name (name),
            INDEX idx_type (type),
            INDEX idx_created_by (created_by)
        ) $charset_collate;";

        // Execute table creation
        dbDelta($sql_subscribers);
        dbDelta($sql_notifications);
        dbDelta($sql_segments);
        dbDelta($sql_workflows);
        dbDelta($sql_workflow_instances);
        dbDelta($sql_analytics);
        dbDelta($sql_cache);
        dbDelta($sql_templates);

        // Update database version
        update_option('onesignal_pwa_db_version', self::DB_VERSION);

        // Create default segments
        self::create_default_segments();

        // Create default templates
        self::create_default_templates();
    }

    /**
     * Create default segments
     */
    private static function create_default_segments() {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_segments';

        $default_segments = array(
            array(
                'name' => 'All Subscribers',
                'description' => 'All active subscribers',
                'rules' => json_encode(array(
                    array('field' => 'subscription_status', 'operator' => 'equals', 'value' => 'subscribed')
                )),
                'is_dynamic' => 1
            ),
            array(
                'name' => 'New Subscribers',
                'description' => 'Subscribers from last 7 days',
                'rules' => json_encode(array(
                    array('field' => 'created_at', 'operator' => 'greater_than', 'value' => '7 days ago')
                )),
                'is_dynamic' => 1
            ),
            array(
                'name' => 'Active Users',
                'description' => 'Users who visited in last 30 days',
                'rules' => json_encode(array(
                    array('field' => 'last_session', 'operator' => 'greater_than', 'value' => '30 days ago')
                )),
                'is_dynamic' => 1
            ),
            array(
                'name' => 'Inactive Users',
                'description' => 'Users who haven\'t visited in 60+ days',
                'rules' => json_encode(array(
                    array('field' => 'last_session', 'operator' => 'less_than', 'value' => '60 days ago')
                )),
                'is_dynamic' => 1
            )
        );

        foreach ($default_segments as $segment) {
            $wpdb->insert($table, $segment);
        }
    }

    /**
     * Create default templates
     */
    private static function create_default_templates() {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_templates';

        $default_templates = array(
            array(
                'name' => 'Welcome Message',
                'description' => 'Welcome new subscribers',
                'type' => 'notification',
                'template_data' => json_encode(array(
                    'title' => 'Welcome to {{site_name}}!',
                    'message' => 'Thanks for subscribing. Stay tuned for updates.',
                    'url' => '{{home_url}}'
                )),
                'is_default' => 1
            ),
            array(
                'name' => 'New Post Alert',
                'description' => 'Alert for new blog post',
                'type' => 'notification',
                'template_data' => json_encode(array(
                    'title' => 'New Post: {{post_title}}',
                    'message' => '{{post_excerpt}}',
                    'url' => '{{post_url}}'
                )),
                'is_default' => 1
            ),
            array(
                'name' => 'Cart Abandonment',
                'description' => 'Abandoned cart reminder',
                'type' => 'notification',
                'template_data' => json_encode(array(
                    'title' => 'You left items in your cart!',
                    'message' => 'Complete your purchase and save {{cart_total}}',
                    'url' => '{{cart_url}}'
                )),
                'is_default' => 1
            ),
            array(
                'name' => 'Back in Stock',
                'description' => 'Product back in stock notification',
                'type' => 'notification',
                'template_data' => json_encode(array(
                    'title' => '{{product_name}} is back in stock!',
                    'message' => 'Get it before it sells out again.',
                    'url' => '{{product_url}}'
                )),
                'is_default' => 1
            )
        );

        foreach ($default_templates as $template) {
            $wpdb->insert($table, $template);
        }
    }

    /**
     * Drop all plugin tables
     */
    public static function drop_tables() {
        global $wpdb;

        $table_prefix = $wpdb->prefix . 'onesignal_pwa_';
        $tables = array(
            'subscribers',
            'notifications',
            'segments',
            'workflows',
            'workflow_instances',
            'analytics',
            'cache',
            'templates'
        );

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$table_prefix}{$table}");
        }

        delete_option('onesignal_pwa_db_version');
    }

    /**
     * Upgrade database if needed
     */
    public static function maybe_upgrade() {
        $current_version = get_option('onesignal_pwa_db_version', '0.0.0');

        if (version_compare($current_version, self::DB_VERSION, '<')) {
            self::create_tables();
        }
    }

    /**
     * Clean expired cache
     */
    public static function clean_expired_cache() {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_cache';

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$table} WHERE expires_at < %s",
                current_time('mysql')
            )
        );
    }

    /**
     * Clean old analytics data
     *
     * @param int $days Number of days to keep
     */
    public static function clean_old_analytics($days = 90) {
        global $wpdb;
        $table = $wpdb->prefix . 'onesignal_pwa_analytics';

        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$table} WHERE created_at < %s",
                $date
            )
        );
    }
}
