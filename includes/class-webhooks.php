<?php

class WPL_Webhooks {

    public static function init() {
        add_action('wp_ajax_wpl_webhook_listener', [self::class, 'webhook_listener']);
        add_action('wp_ajax_nopriv_wpl_webhook_listener', [self::class, 'webhook_listener']);
    }

    public static function webhook_listener() {
        // Verify the authenticity of the request
        if (!self::verify_webhook_request()) {
            wp_send_json_error(['message' => 'Invalid request'], 403);
        }

        // Get the snippet data from the request
        $snippet_data = json_decode(file_get_contents('php://input'), true);

        if (!self::store_snippet($snippet_data)) {
            wp_send_json_error(['message' => 'Failed to store snippet'], 500);
        }

        wp_send_json_success(['message' => 'Snippet stored successfully']);
    }

    private static function verify_webhook_request() {
        // Add your verification logic here, e.g., HMAC signature validation
        return true;
    }

    private static function store_snippet($snippet_data) {
        if (empty($snippet_data) || !isset($snippet_data['file_name']) || !isset($snippet_data['content'])) {
            return false;
        }

        $uploads_dir = wp_upload_dir()['basedir'] . '/wpl-toolkit/';
        $file_path = $uploads_dir . sanitize_file_name($snippet_data['file_name']);

        // Ensure the uploads directory exists
        wp_mkdir_p($uploads_dir);

        return file_put_contents($file_path, $snippet_data['content']) !== false;
    }
}
