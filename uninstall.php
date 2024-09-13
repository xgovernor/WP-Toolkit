<?php

// If uninstall.php is not called by WordPress, terminate the script.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

/**
 * WPL_Uninstall class handles the uninstallation of the plugin.
 *
 * @since 1.0.0
 */
class WPL_Uninstall
{
    /**
     * Execute the uninstallation process.
     */
    public static function uninstall()
    {
        // Delete plugin-specific options from the database.
        self::delete_plugin_options();

        // Remove the /wpl-toolkit directory inside /uploads.
        self::remove_uploads_directory();

        // Flush rewrite rules to clean up any custom rules set by the plugin.
        flush_rewrite_rules();

        // Remove any hooks related to uninstallation.
        self::remove_uninstall_hooks();
    }

    /**
     * Delete plugin-specific options.
     *
     * @since 1.0.0
     */
    private static function delete_plugin_options()
    {
        // Delete options that store plugin settings.
        delete_option('wpl_toolkit_settings'); // General settings.
        delete_option('wpl_toolkit_activation'); // Activation status.
        delete_option('wpl_api_key'); // API key.
        delete_option('wpl_snippets'); // Snippets data.
    }

    /**
     * Remove the /wpl-toolkit directory inside /uploads if it exists.
     *
     * This directory is used to store the snippets downloaded from the WPL platform.
     *
     * @since 1.0.0
     */
    private static function remove_uploads_directory()
    {
        // Get the path to the /wpl-toolkit directory inside /uploads.
        $upload_dir = wp_upload_dir()['basedir'];
        $wpl_dir = $upload_dir . '/wpl-toolkit';

        // Check if the directory exists.
        if (is_dir($wpl_dir)) {
            // Attempt to delete the directory and its contents.
            if (!self::delete_directory_recursive($wpl_dir)) {
                error_log("Failed to remove /wpl-toolkit directory: $wpl_dir");
            }
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
        // Ensure the directory exists and is a valid directory.
        if (!is_dir($dir)) {
            return false;
        }

        // Get all files and directories within the specified directory.
        $items = array_diff(scandir($dir), ['.', '..']); // Exclude . and ..

        foreach ($items as $item) {
            $path = $dir . '/' . $item;

            // If the item is a directory, recursively delete its contents.
            if (is_dir($path)) {
                self::delete_directory_recursive($path);
            } else {
                // If the item is a file, attempt to delete it.
                if (!@unlink($path)) {
                    error_log("Failed to delete file: $path");
                }
            }
        }

        // Attempt to remove the now-empty directory.
        if (!@rmdir($dir)) {
            error_log("Failed to remove directory: $dir");
            return false;
        }

        return true;
    }

    /**
     * Remove hooks related to the plugin's uninstallation process.
     *
     * @since 1.0.0
     */
    private static function remove_uninstall_hooks()
    {
        // Ensure uninstall hooks are removed to prevent future unwanted executions.
        remove_action('admin_init', [self::class, 'uninstall']);
        remove_action('admin_menu', [self::class, 'uninstall']);
    }
}

// Execute the uninstall process.
WPL_Uninstall::uninstall();
