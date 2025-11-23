<?php
/**
 * Manifest Generation Class
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OneSignal PWA Manifest Class
 */
class OneSignal_PWA_Manifest {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'add_rewrite_rules'));
        add_action('template_redirect', array($this, 'serve_manifest'));
        add_action('wp_head', array($this, 'add_manifest_link'), 1);
    }

    /**
     * Add rewrite rules for manifest
     */
    public function add_rewrite_rules() {
        add_rewrite_rule('^manifest\.json$', 'index.php?onesignal_pwa_manifest=1', 'top');
        add_rewrite_tag('%onesignal_pwa_manifest%', '([^&]+)');
    }

    /**
     * Serve manifest file
     */
    public function serve_manifest() {
        if (get_query_var('onesignal_pwa_manifest')) {
            header('Content-Type: application/manifest+json; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: public, max-age=3600');

            echo json_encode($this->generate_manifest(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Add manifest link to wp_head
     */
    public function add_manifest_link() {
        $manifest_url = home_url('/manifest.json');
        echo '<link rel="manifest" href="' . esc_url($manifest_url) . '">' . "\n";

        // Add theme color
        $theme_color = OneSignal_PWA_Settings::get('theme_color', '#000000');
        echo '<meta name="theme-color" content="' . esc_attr($theme_color) . '">' . "\n";

        // Add apple-mobile-web-app-capable
        echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
        echo '<meta name="apple-mobile-web-app-status-bar-style" content="default">' . "\n";

        $app_name = OneSignal_PWA_Settings::get('app_name', get_bloginfo('name'));
        echo '<meta name="apple-mobile-web-app-title" content="' . esc_attr($app_name) . '">' . "\n";

        // Add apple touch icons
        $icon_192 = OneSignal_PWA_Settings::get('icon_192');
        if ($icon_192) {
            echo '<link rel="apple-touch-icon" href="' . esc_url($icon_192) . '">' . "\n";
        }
    }

    /**
     * Generate manifest data
     *
     * @return array
     */
    public function generate_manifest() {
        $settings = OneSignal_PWA_Settings::get_pwa_settings();

        $manifest = array(
            'name' => $settings['app_name'],
            'short_name' => $settings['app_short_name'],
            'description' => $settings['app_description'],
            'start_url' => $settings['start_url'],
            'scope' => $settings['scope'],
            'display' => $settings['display_mode'],
            'orientation' => $settings['orientation'],
            'theme_color' => $settings['theme_color'],
            'background_color' => $settings['background_color'],
            'icons' => $this->get_icons(),
            'categories' => $this->get_categories(),
            'lang' => get_locale(),
            'dir' => is_rtl() ? 'rtl' : 'ltr',
        );

        // Add shortcuts if configured
        $shortcuts = $this->get_shortcuts();
        if (!empty($shortcuts)) {
            $manifest['shortcuts'] = $shortcuts;
        }

        // Add screenshots if configured
        $screenshots = $this->get_screenshots();
        if (!empty($screenshots)) {
            $manifest['screenshots'] = $screenshots;
        }

        return apply_filters('onesignal_pwa_manifest', $manifest);
    }

    /**
     * Get icons array
     *
     * @return array
     */
    private function get_icons() {
        $icons = array();
        $sizes = array(72, 96, 128, 144, 152, 192, 384, 512);

        foreach ($sizes as $size) {
            $icon_url = OneSignal_PWA_Settings::get("icon_{$size}");
            if ($icon_url) {
                $icons[] = array(
                    'src' => $icon_url,
                    'sizes' => "{$size}x{$size}",
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
                );
            }
        }

        // If no icons configured, use default
        if (empty($icons)) {
            $default_icon = $this->get_default_icon();
            if ($default_icon) {
                $icons[] = array(
                    'src' => $default_icon,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
                );
            }
        }

        return $icons;
    }

    /**
     * Get default icon
     *
     * @return string
     */
    private function get_default_icon() {
        // Try to get site icon first
        $site_icon_url = get_site_icon_url(512);
        if ($site_icon_url) {
            return $site_icon_url;
        }

        // Use plugin default icon
        return ONESIGNAL_PWA_PLUGIN_URL . 'assets/images/icon-512.png';
    }

    /**
     * Get app categories
     *
     * @return array
     */
    private function get_categories() {
        $categories = OneSignal_PWA_Settings::get('categories', array());

        if (empty($categories)) {
            // Auto-detect based on site type
            if (class_exists('WooCommerce')) {
                $categories[] = 'shopping';
            }
            if (get_option('show_on_front') === 'posts') {
                $categories[] = 'news';
            }
            if (empty($categories)) {
                $categories[] = 'lifestyle';
            }
        }

        return $categories;
    }

    /**
     * Get shortcuts
     *
     * @return array
     */
    private function get_shortcuts() {
        $shortcuts = OneSignal_PWA_Settings::get('shortcuts', array());

        if (empty($shortcuts)) {
            // Auto-generate based on site type
            if (class_exists('WooCommerce')) {
                $shortcuts = array(
                    array(
                        'name' => __('Shop', 'onesignal-pwa'),
                        'short_name' => __('Shop', 'onesignal-pwa'),
                        'url' => get_permalink(wc_get_page_id('shop')),
                        'icons' => array(
                            array('src' => ONESIGNAL_PWA_PLUGIN_URL . 'assets/images/shop-icon.png', 'sizes' => '96x96')
                        ),
                    ),
                    array(
                        'name' => __('Cart', 'onesignal-pwa'),
                        'short_name' => __('Cart', 'onesignal-pwa'),
                        'url' => wc_get_cart_url(),
                        'icons' => array(
                            array('src' => ONESIGNAL_PWA_PLUGIN_URL . 'assets/images/cart-icon.png', 'sizes' => '96x96')
                        ),
                    ),
                );
            } else {
                $shortcuts = array(
                    array(
                        'name' => __('Home', 'onesignal-pwa'),
                        'short_name' => __('Home', 'onesignal-pwa'),
                        'url' => home_url('/'),
                        'icons' => array(
                            array('src' => ONESIGNAL_PWA_PLUGIN_URL . 'assets/images/home-icon.png', 'sizes' => '96x96')
                        ),
                    ),
                );
            }
        }

        return array_slice($shortcuts, 0, 4); // Maximum 4 shortcuts
    }

    /**
     * Get screenshots
     *
     * @return array
     */
    private function get_screenshots() {
        $screenshots = OneSignal_PWA_Settings::get('screenshots', array());
        return $screenshots;
    }

    /**
     * Validate manifest
     *
     * @return bool|WP_Error
     */
    public function validate() {
        $manifest = $this->generate_manifest();

        // Check required fields
        $required = array('name', 'short_name', 'start_url', 'display', 'icons');

        foreach ($required as $field) {
            if (empty($manifest[$field])) {
                return new WP_Error('invalid_manifest', sprintf(__('Manifest is missing required field: %s', 'onesignal-pwa'), $field));
            }
        }

        // Check icons
        if (empty($manifest['icons'])) {
            return new WP_Error('invalid_manifest', __('Manifest must include at least one icon', 'onesignal-pwa'));
        }

        return true;
    }
}
