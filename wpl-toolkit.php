<?php
/**
 * Plugin Name: WPL Toolkit
 *
 * @package WPL Toolkit
 * @author Abu Taher Muhammad
 * @copyright Copyright (c) 2022, Abu Taher Muhammad
 * @license https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @wordpress-plugin
 * Plugin URI: https://wordpress.org/plugins/wpl-toolkit/
 * Description: Manage WPL snippets in WordPress, allowing upload, enable/disable, and other snippet operations.
 * Version: 1.0.0
 * Requires at least: 4.9
 * Requires PHP: 7.2
 * Author: Abu Taher Muhammad
 * Author URI: https://at-mah.vercel.app
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpl-toolkit
 * Domain Path: /languages
 * Network: true
 * Update URI: https://wpl-toolkit.com
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('WPL_TOOLKIT_VERSION', '1.0.0');
define('WPL_TOOLKIT_DIR', plugin_dir_path(__FILE__));
define('WPL_TOOLKIT_URL', plugin_dir_url(__FILE__));

// Include core files
require_once WPL_TOOLKIT_DIR . 'includes/class-settings.php';
require_once WPL_TOOLKIT_DIR . 'includes/class-api.php';
require_once WPL_TOOLKIT_DIR . 'includes/class-webhooks.php';
require_once WPL_TOOLKIT_DIR . 'includes/class-snippets.php';

// Activation hook
register_activation_hook(__FILE__, ['WPL_Toolkit', 'activate']);

// Initialize the plugin
add_action('plugins_loaded', ['WPL_Toolkit', 'init']);

/**
 * Main plugin class
 */
class WPL_Toolkit
{
    /**
     * Initialize the plugin
     */
        public static function init()
    {
        // Load plugin text domain for translations
        load_plugin_textdomain('wpl-toolkit', false, dirname(plugin_basename(__FILE__)) . '/languages');

        // Initialize plugin components
        WPL_Settings::init();
        WPL_Api::init();
        WPL_Webhooks::init();
        WPL_Snippets::init();
    }

    /**
     * Activate the plugin
     */
    public static function activate()
    {
        // Setup initial options, database tables, or other necessary setup tasks
        add_option('wpl_api_key', '');
        add_option('wpl_snippets', []);

        // Create /wpl-toolkit directory inside /uploads
        self::wpl_create_dir();

        flush_rewrite_rules();
    }

    /**
     * Deactivate the plugin
     */
    public static function deactivate()
    {
        // Remove plugin options
        delete_option('wpl_api_key');
        delete_option('wpl_snippets');

        // Remove /wpl-toolkit directory inside /uploads
        self::wpl_remove_dir();

        flush_rewrite_rules();
    }

    /**
     * Create /wpl-toolkit directory inside /uploads if it doesn't exist.
     *
     * This directory is used to store the snippets downloaded from the WPL platform.
     *
     * @since 1.0.0
     */
    private static function wpl_create_dir()
    {
        // Create /wpl-toolkit directory inside /uploads if it doesn't exist
        wp_mkdir_p(wp_upload_dir()['basedir'] . '/wpl-toolkit');
    }

    /**
     * Remove /wpl-toolkit directory inside /uploads if it exists.
     *
     * This directory is used to store the snippets downloaded from the WPL platform.
     *
     * @since 1.0.0
     */
    private static function wpl_remove_dir()
    {
        // Get the path to the /wpl-toolkit directory inside /uploads
        $dir = wp_upload_dir()['basedir'] . '/wpl-toolkit';

        // Check if the directory exists
        if (is_dir($dir)) {
            // Attempt to delete the directory and its contents
            self::delete_directory_recursive($dir);
        }
    }

    /**
     * Recursively delete a directory and its contents.
     *
     * @param string $dir The directory path to delete.
     * @return bool True on success, false on failure.
     */
    private static function delete_directory_recursive($dir)
    {
        // Ensure the directory exists and is a directory
        if (!is_dir($dir)) {
            return false;
        }

        // Get all files and directories within the directory
        $items = scandir($dir);
        foreach ($items as $item) {
            // Skip the current and parent directory pointers
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;

            // If the item is a directory, recursively delete it
            if (is_dir($path)) {
                self::delete_directory_recursive($path);
            } else {
                // Otherwise, it's a file, so delete it
                if (!unlink($path)) {
                    error_log("Failed to delete file: $path");
                }
            }
        }

        // Remove the now-empty directory
        if (!rmdir($dir)) {
            error_log("Failed to remove directory: $dir");
            return false;
        }

        return true;
    }

}
