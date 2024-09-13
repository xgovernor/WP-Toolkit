<?php
/**
 * API communication class for WPL Toolkit
 */

class WPL_Api
{
    public static function init()
    {
        // Enqueue admin scripts
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_admin_scripts']);

        // Register AJAX action for testing API key
        add_action('wp_ajax_wpl_toolkit_test_api_key', [self::class, 'test_api_key']);

        // Register AJAX action for downloading snippets
        add_action('wp_ajax_wpl_toolkit_download_snippet', [self::class, 'download_snippet']);

        // Register AJAX action for toggling snippet status
        add_action('wp_ajax_wpl_toolkit_toggle_snippet', [self::class, 'toggle_snippet_status']);
    }

    /**
     * Enqueue admin scripts and localize data for AJAX
     */
    public static function enqueue_admin_scripts($hook)
    {
        // Only load scripts on our settings page
        if ($hook !== 'toplevel_page_wpl-toolkit-settings') {
            return;
        }

        wp_enqueue_script(
            'wpl-toolkit-admin-js',
            WPL_TOOLKIT_URL . 'assets/js/wpl-toolkit-admin.js',
            ['jquery'],
            WPL_TOOLKIT_VERSION,
            true
        );

        // Localize script to pass AJAX URL and nonce
        wp_localize_script('wpl-toolkit-admin-js', 'wplToolkit', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'security' => wp_create_nonce('wpl_toolkit_test_api'),
        ]);
    }

    /**
     * Handle API key testing via AJAX
     */
    public static function test_api_key()
    {
        check_ajax_referer('wpl_toolkit_test_api', 'security');

        $api_key = sanitize_text_field($_POST['api_key'] ?? '');

        if (empty($api_key) || strlen($api_key) < 20) {
            wp_send_json_error(['message' => __('Invalid API Key format.', 'wpl-toolkit')]);
        }

        $response = wp_remote_get('https://wpl-platform.com/api/test-key', [
            'headers' => ['Authorization' => 'Bearer ' . $api_key],
        ]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            wp_send_json_error(['message' => __('Failed to validate API Key. Please try again.', 'wpl-toolkit')]);
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!isset($data['success'])) {
            wp_send_json_error(['message' => __('Invalid response from API.', 'wpl-toolkit')]);
        }

        wp_send_json_success(['message' => __('API Key is valid and working.', 'wpl-toolkit')]);
    }


    /**
     * Handle snippet download from the WPL platform
     */
    public static function download_snippet()
    {
        check_ajax_referer('wpl_toolkit_download_snippet', 'security');

        $snippet_id = sanitize_text_field($_POST['snippet_id'] ?? '');

        if (empty($snippet_id)) {
            wp_send_json_error(['message' => __('Snippet ID is required.', 'wpl-toolkit')]);
        }

        $api_key = get_option('wpl_api_key');
        if (empty($api_key)) {
            wp_send_json_error(['message' => __('API Key not set. Please configure it in the settings.', 'wpl-toolkit')]);
        }

        // API request to download snippet
        $response = wp_remote_get('https://wpl-platform.com/api/snippets/download/' . $snippet_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
            ],
        ]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            wp_send_json_error(['message' => __('Failed to download snippet. Please try again.', 'wpl-toolkit')]);
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!isset($data['content'])) {
            wp_send_json_error(['message' => __('Invalid snippet data received.', 'wpl-toolkit')]);
        }

        $upload_dir = wp_upload_dir()['basedir'] . '/wpl';
        if (!file_exists($upload_dir)) {
            wp_mkdir_p($upload_dir);
        }

        $file_path = $upload_dir . '/' . sanitize_file_name($snippet_id) . '.php';

        if (file_put_contents($file_path, $data['content']) === false) {
            wp_send_json_error(['message' => __('Failed to save snippet locally.', 'wpl-toolkit')]);
        }

        // Save snippet metadata in the options table, including status (enabled/disabled)
        $snippets = get_option('wpl_snippets', []);
        $snippets[$snippet_id] = [
            'title' => sanitize_text_field($data['title'] ?? 'Untitled Snippet'),
            'file_path' => $file_path,
            'status' => 'disabled', // Default status when downloaded
        ];
        update_option('wpl_snippets', $snippets);

        wp_send_json_success(['message' => __('Snippet downloaded and saved successfully.', 'wpl-toolkit')]);
    }

    /**
     * Toggle the activation status of a snippet
     */
    public static function toggle_snippet_status()
    {
        // Verify nonce for security
        check_ajax_referer('wpl_toggle_snippet_status', 'security');

        $snippet_id = sanitize_text_field($_POST['snippet_id'] ?? '');
        if (empty($snippet_id)) {
            wp_send_json_error(['message' => __('Snippet ID is required.', 'wpl-toolkit')]);
        }

        $snippets = get_option('wpl_snippets', []);
        if (!isset($snippets[$snippet_id])) {
            wp_send_json_error(['message' => __('Snippet not found.', 'wpl-toolkit')]);
        }

        // Toggle the status between enabled and disabled
        $snippets[$snippet_id]['status'] = ($snippets[$snippet_id]['status'] === 'enabled') ? 'disabled' : 'enabled';
        // Update snippets in the database
        update_option('wpl_snippets', $snippets);

        wp_send_json_success([
            'message' => __('Snippet status updated successfully.', 'wpl-toolkit'),
            'status' => $snippets[$snippet_id]['status'],
        ]);

        wp_send_json_error(['message' => __('Failed to toggle snippet status.', 'wpl-toolkit')]);
    }
}

// Initialize the API class
// WPL_Api::init();
