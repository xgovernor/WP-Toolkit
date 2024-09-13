<?php

/**
 * Webhooks class for WPL Toolkit
 */

class WPL_Snippets
{
    public static function init()
    {
        // Register AJAX actions
        add_action('wp_ajax_wpl_toolkit_enable_snippet', [self::class, 'handle_enable_snippet']);
        add_action('wp_ajax_wpl_toolkit_disable_snippet', [self::class, 'handle_disable_snippet']);
        add_action('wp_ajax_wpl_toolkit_delete_snippet', [self::class, 'handle_delete_snippet']);
    }

     /**
     * Handle enabling a snippet
     */
    public static function handle_enable_snippet()
    {
        check_ajax_referer('wpl_toolkit_test_api', 'security');

        $snippet = isset($_POST['snippet']) ? sanitize_file_name($_POST['snippet']) : '';

        if (empty($snippet)) {
            wp_send_json_error(__('Snippet name is missing.', 'wpl-toolkit'));
        }

        // Implement your enabling logic here
        // For example, update snippet status in a database or file

        wp_send_json_success(__('Snippet enabled successfully.', 'wpl-toolkit'));
    }

    /**
     * Handle disabling a snippet
     */
    public static function handle_disable_snippet()
    {
        check_ajax_referer('wpl_toolkit_test_api', 'security');

        $snippet = isset($_POST['snippet']) ? sanitize_file_name($_POST['snippet']) : '';

        if (empty($snippet)) {
            wp_send_json_error(__('Snippet name is missing.', 'wpl-toolkit'));
        }

        // Implement your disabling logic here
        // For example, update snippet status in a database or file

        wp_send_json_success(__('Snippet disabled successfully.', 'wpl-toolkit'));
    }

    /**
     * Handle deleting a snippet
     */
    public static function handle_delete_snippet()
    {
        check_ajax_referer('wpl_toolkit_test_api', 'security');

        $snippet = isset($_POST['snippet']) ? sanitize_file_name($_POST['snippet']) : '';

        if (empty($snippet)) {
            wp_send_json_error(__('Snippet name is missing.', 'wpl-toolkit'));
        }

        // Delete the snippet file
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['basedir'] . '/wpl-toolkit/' . $snippet;

        if (file_exists($file_path)) {
            unlink($file_path);
            wp_send_json_success(__('Snippet deleted successfully.', 'wpl-toolkit'));
        } else {
            wp_send_json_error(__('Snippet not found.', 'wpl-toolkit'));
        }
    }
}
