<?php
defined( 'ABSPATH' ) or die( 'you do not have access to this page!' );

if ( ! class_exists( 'WPL_Api' ) ) {
	class WPL_Api {

		private static $_this;

		public function __construct() {
			if ( isset( self::$_this ) ) {
				wp_die( 'You can not create more than one instance of WPL_Api' );
			}

			self::$_this = $this;
			// Enqueue admin scripts
			add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_admin_scripts' ) );
			add_action( 'wp_ajax_wpl_test_api_key', array( self::class, 'test_api_key' ) );
			add_action( 'wp_ajax_wpl_toolkit_download_snippet', array( self::class, 'download_snippet' ) );
			add_action( 'wp_ajax_wpl_toolkit_toggle_snippet', array( self::class, 'toggle_snippet_status' ) );
		}

		public static function this() {
			return self::$_this;
		}

		/**
		 * Enqueue admin scripts and localize data for AJAX
		 */
		public static function enqueue_admin_scripts( $hook ) {
			if ( $hook !== 'settings_page_wpl-toolkit-settings' ) {
				return;
			}

			wp_enqueue_script( 'wpl-admin-activation-js', WPLTK_PLUGIN_URL . 'assets/js/wpl-activation.js', array( 'jquery' ), WPLTK_PLUGIN_VERSION, true );
			wp_localize_script(
				'wpl-admin-activation-js',
				'wplActivation',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'_nonce'  => wp_create_nonce( 'wpl_test_api_key' ),
				)
			);
		}

		/**
		 * Handle API key testing via AJAX
		 */
		public static function test_api_key() {
			check_ajax_referer( 'wpl_test_api_key', '_nonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'wpl-toolkit' ) ) );
			}

			$api_key = sanitize_text_field( $_POST['api_key'] ?? '' );
			if ( empty( $api_key ) || strlen( $api_key ) < 20 ) {
				wp_send_json_error( array( 'message' => __( 'Invalid API Key format.', 'wpl-toolkit' ) ) );
			}

			$response = wp_remote_get( 'https://wpl-platform.com/api/test-key', array( 'headers' => array( 'Authorization' => 'Bearer ' . $api_key ) ) );
			if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
				wp_send_json_error( array( 'message' => __( 'Failed to validate API Key.', 'wpl-toolkit' ) ) );
			}

			$data = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( ! isset( $data['success'] ) ) {
				wp_send_json_error( array( 'message' => __( 'Invalid response from API.', 'wpl-toolkit' ) ) );
			}

			wp_send_json_success( array( 'message' => __( 'API Key is valid and working.', 'wpl-toolkit' ) ) );
		}

		/**
		 * Handle snippet download from the WPL platform
		 */
		public static function download_snippet() {
			check_ajax_referer( 'wpl_toolkit_download_snippet', 'security' );
			$snippet_id = sanitize_text_field( $_POST['snippet_id'] ?? '' );
			if ( empty( $snippet_id ) ) {
				wp_send_json_error( array( 'message' => __( 'Snippet ID is required.', 'wpl-toolkit' ) ) );
			}

			// Validating snippet_id format for security
			if ( ! preg_match( '/^[a-zA-Z0-9_-]+$/', $snippet_id ) ) {
				wp_send_json_error( array( 'message' => __( 'Invalid Snippet ID format.', 'wpl-toolkit' ) ) );
			}

			$api_key = get_option( 'wpltk_api_key' );
			if ( empty( $api_key ) ) {
				wp_send_json_error( array( 'message' => __( 'API Key not set.', 'wpl-toolkit' ) ) );
			}

			$response = wp_remote_get( 'https://wpl-platform.com/api/snippets/download/' . $snippet_id, array( 'headers' => array( 'Authorization' => 'Bearer ' . $api_key ) ) );
			if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
				wp_send_json_error( array( 'message' => __( 'Failed to download snippet.', 'wpl-toolkit' ) ) );
			}

			$data = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( ! isset( $data['content'] ) ) {
				wp_send_json_error( array( 'message' => __( 'Invalid snippet data received.', 'wpl-toolkit' ) ) );
			}

			$upload_dir = wp_upload_dir()['basedir'] . '/wpl';
			if ( ! file_exists( $upload_dir ) ) {
				wp_mkdir_p( $upload_dir );
			}
			$file_path = $upload_dir . '/' . sanitize_file_name( $snippet_id ) . '.php';

			if ( file_put_contents( $file_path, $data['content'] ) === false ) {
				wp_send_json_error( array( 'message' => __( 'Failed to save snippet locally.', 'wpl-toolkit' ) ) );
			}

			$snippets                = get_option( 'wpl_snippets', array() );
			$snippets[ $snippet_id ] = array(
				'title'     => sanitize_text_field( $data['title'] ?? 'Untitled Snippet' ),
				'file_path' => $file_path,
				'status'    => 'disabled',
			);
			update_option( 'wpl_snippets', $snippets );

			wp_send_json_success( array( 'message' => __( 'Snippet downloaded and saved successfully.', 'wpl-toolkit' ) ) );
		}

		/**
		 * Toggle the activation status of a snippet
		 */
		public static function toggle_snippet_status() {
			check_ajax_referer( 'wpl_toggle_snippet_status', 'security' );
			$snippet_id = sanitize_text_field( $_POST['snippet_id'] ?? '' );
			if ( empty( $snippet_id ) ) {
				wp_send_json_error( array( 'message' => __( 'Snippet ID is required.', 'wpl-toolkit' ) ) );
			}

			$snippets = get_option( 'wpl_snippets', array() );
			if ( ! isset( $snippets[ $snippet_id ] ) ) {
				wp_send_json_error( array( 'message' => __( 'Snippet not found.', 'wpl-toolkit' ) ) );
			}

			$snippets[ $snippet_id ]['status'] = ( $snippets[ $snippet_id ]['status'] === 'enabled' ) ? 'disabled' : 'enabled';
			update_option( 'wpl_snippets', $snippets );

			wp_send_json_success(
				array(
					'message' => __( 'Snippet status updated successfully.', 'wpl-toolkit' ),
					'status'  => $snippets[ $snippet_id ]['status'],
				)
			);
		}
	}
}
