<?php
/**
 * Icon Processing Class
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OneSignal PWA Icon Processor Class
 */
class OneSignal_PWA_Icon_Processor {

    /**
     * Required icon sizes
     */
    const ICON_SIZES = array(72, 96, 128, 144, 152, 192, 384, 512);

    /**
     * Process and generate all icon sizes
     *
     * @param string $source_file Path to source image
     * @return array|WP_Error Array of generated icon URLs or error
     */
    public static function generate_icons($source_file) {
        if (!file_exists($source_file)) {
            return new WP_Error('file_not_found', __('Source file not found', 'onesignal-pwa'));
        }

        // Check if GD or ImageMagick is available
        if (!extension_loaded('gd') && !extension_loaded('imagick')) {
            return new WP_Error('no_image_library', __('GD or ImageMagick extension is required', 'onesignal-pwa'));
        }

        $upload_dir = wp_upload_dir();
        $icons_dir = $upload_dir['basedir'] . '/onesignal-pwa-icons';

        // Create icons directory if it doesn't exist
        if (!file_exists($icons_dir)) {
            wp_mkdir_p($icons_dir);
        }

        $icons = array();

        foreach (self::ICON_SIZES as $size) {
            $result = self::resize_image($source_file, $icons_dir, $size);

            if (is_wp_error($result)) {
                return $result;
            }

            $icons[$size] = $result;

            // Save to settings
            OneSignal_PWA_Settings::set("icon_{$size}", $result);
        }

        return $icons;
    }

    /**
     * Resize image to specific size
     *
     * @param string $source_file
     * @param string $dest_dir
     * @param int $size
     * @return string|WP_Error
     */
    private static function resize_image($source_file, $dest_dir, $size) {
        $image = wp_get_image_editor($source_file);

        if (is_wp_error($image)) {
            return $image;
        }

        // Resize image
        $image->resize($size, $size, true);

        // Generate filename
        $filename = "icon-{$size}x{$size}.png";
        $dest_file = $dest_dir . '/' . $filename;

        // Save image
        $saved = $image->save($dest_file, 'image/png');

        if (is_wp_error($saved)) {
            return $saved;
        }

        // Return URL
        $upload_dir = wp_upload_dir();
        return $upload_dir['baseurl'] . '/onesignal-pwa-icons/' . $filename;
    }

    /**
     * Upload and process icon
     *
     * @param array $file $_FILES array element
     * @return array|WP_Error
     */
    public static function upload_icon($file) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return new WP_Error('upload_error', __('File upload failed', 'onesignal-pwa'));
        }

        // Check file type
        $allowed_types = array('image/png', 'image/jpeg', 'image/jpg');
        $file_type = wp_check_filetype($file['name']);

        if (!in_array($file['type'], $allowed_types)) {
            return new WP_Error('invalid_type', __('Only PNG and JPEG images are allowed', 'onesignal-pwa'));
        }

        // Check image dimensions
        $image_info = getimagesize($file['tmp_name']);

        if ($image_info[0] < 512 || $image_info[1] < 512) {
            return new WP_Error('invalid_size', __('Image must be at least 512x512 pixels', 'onesignal-pwa'));
        }

        // Generate icons
        return self::generate_icons($file['tmp_name']);
    }

    /**
     * Get icon URL
     *
     * @param int $size
     * @return string
     */
    public static function get_icon_url($size) {
        return OneSignal_PWA_Settings::get("icon_{$size}", '');
    }

    /**
     * Get all icon URLs
     *
     * @return array
     */
    public static function get_all_icons() {
        $icons = array();

        foreach (self::ICON_SIZES as $size) {
            $url = self::get_icon_url($size);
            if ($url) {
                $icons[$size] = $url;
            }
        }

        return $icons;
    }

    /**
     * Delete all generated icons
     *
     * @return bool
     */
    public static function delete_icons() {
        $upload_dir = wp_upload_dir();
        $icons_dir = $upload_dir['basedir'] . '/onesignal-pwa-icons';

        if (file_exists($icons_dir)) {
            $files = glob($icons_dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($icons_dir);
        }

        // Remove from settings
        foreach (self::ICON_SIZES as $size) {
            OneSignal_PWA_Settings::delete("icon_{$size}");
        }

        return true;
    }

    /**
     * Generate maskable icon
     *
     * @param string $source_file
     * @param int $size
     * @return string|WP_Error
     */
    public static function generate_maskable_icon($source_file, $size = 512) {
        // Add padding to create safe zone for maskable icons
        $image = wp_get_image_editor($source_file);

        if (is_wp_error($image)) {
            return $image;
        }

        // Calculate padding (20% safe zone)
        $padding = intval($size * 0.2);
        $inner_size = $size - ($padding * 2);

        // Resize to inner size
        $image->resize($inner_size, $inner_size, true);

        // Create canvas with padding
        // This would require custom GD code for adding padding

        $upload_dir = wp_upload_dir();
        $icons_dir = $upload_dir['basedir'] . '/onesignal-pwa-icons';
        $filename = "icon-{$size}x{$size}-maskable.png";
        $dest_file = $icons_dir . '/' . $filename;

        $saved = $image->save($dest_file, 'image/png');

        if (is_wp_error($saved)) {
            return $saved;
        }

        return $upload_dir['baseurl'] . '/onesignal-pwa-icons/' . $filename;
    }
}
