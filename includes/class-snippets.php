<?php
defined( 'ABSPATH' ) or die( 'you do not have access to this page!' );

require_once 'class-snippet-executor.php';


if ( ! class_exists( 'WPL_Snippets' ) ) {
	class WPL_Snippets {

		private static $_this;

		public function __construct() {
			if ( isset( self::$_this ) ) {
				wp_die( 'You can not create more than one instance of WPL_Snippets' );
			}

			self::$_this = $this;

			// Enqueue admin scripts and localize data for AJAX
			add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_admin_scripts' ) );
			// Register AJAX actions
			add_action( 'wp_ajax_wpl_enable_snippet', array( self::class, 'handle_enable_snippet' ) );
			add_action( 'wp_ajax_wpl_disable_snippet', array( self::class, 'handle_disable_snippet' ) );
			add_action( 'wp_ajax_wpl_delete_snippet', array( self::class, 'handle_delete_snippet' ) );
		}

		public static function this() {
			return self::$_this;
		}

		/**
		 * Execute a snippet if recovery mode is not active.
		 *
		 * @param string $file_path Path to the snippet file.
		 * @return bool True if snippet executed successfully, false otherwise.
		 */
		public static function run_snippet( $file_path ) {
			if ( WPL_Snippet_Executor::is_recovery_mode_active() ) {
				// Do not execute snippets if recovery mode is active
				return false;
			}

			// Execute the snippet and return the result
			return WPL_Snippet_Executor::execute_snippet( $file_path );
		}

		/**
		 * Handle enabling a snippet via AJAX
		 */
		public static function handle_enable_snippet() {
			// Ensure this function is triggered through an AJAX request
			if ( ! wp_doing_ajax() ) {
				wp_send_json_error( __( 'Invalid request method.', 'wpl-toolkit' ) );
				exit;
			}

			/**
			 * Verify the AJAX nonce for security
			 * First parameter is the action name, and second is the nonce key in the request
			 */
			check_ajax_referer( 'wpl_snippets_nonce', '_nonce' );

			// Sanitize and retrieve the snippet name from POST data
			$snippet = isset( $_POST['snippet'] ) ? sanitize_file_name( $_POST['snippet'] ) : '';

			// Check if snippet name is provided
			if ( empty( $snippet ) ) {
				wp_send_json_error( __( 'Snippet is missing.', 'wpl-toolkit' ) );
				exit;
			}

			// Construct the path to the snippet file and ensure it exists
			$snippet_path = WPLTK_SNIPPET_PATH . '/' . $snippet;
			if ( ! file_exists( $snippet_path ) || ! is_readable( $snippet_path ) ) {
				wp_send_json_error( __( 'Snippet file does not exist or is not readable.', 'wpl-toolkit' ) );
				exit;
			}

			// Optional: Check if the snippet file is valid PHP (optional security step)
			$file_info = pathinfo( $snippet_path );
			if ( $file_info['extension'] !== 'php' ) {
				wp_send_json_error( __( 'Invalid snippet file type. Only PHP snippets are allowed.', 'wpl-toolkit' ) );
				exit;
			}

			// Retrieve the list of enabled snippets from the database
			$all_snippets = get_option( 'wpltk_snippets', array() );

			// Check if the snippet is already enabled
			if ( isset( $all_snippets[ $snippet ] ) && $all_snippets[ $snippet ]['status'] === 'enabled' ) {
				wp_send_json_error( __( 'Snippet is already enabled.', 'wpl-toolkit' ) );
				exit;
			}

			// Prepare snippet metadata
			$snippet_data = array(
				'name'     => $snippet,
				'size'     => filesize( $snippet_path ),
				'status'   => 'enabled',
				'created'  => date( 'Y-m-d H:i:s' ),
				'modified' => date( 'Y-m-d H:i:s' ),
			);

			// Add or update the snippet in the enabled list
			$all_snippets[ $snippet ] = $snippet_data;

			wp_send_json_success( __( 'Snippet enabled successfully.', 'wpl-toolkit' ) );
			// Update the option in the database
			if ( update_option( 'wpltk_snippets', $all_snippets ) === false ) {
				wp_send_json_error( __( 'Failed to enable the snippet.', 'wpl-toolkit' ) );
				exit;
			}

			// Log success message for debugging if needed
			error_log( 'Snippet enabled: ' . $snippet );

			// Respond with a success message
			wp_send_json_success( __( 'Snippet enabled successfully.', 'wpl-toolkit' ) );
			exit;
		}

		/**
		 * Handle disabling a snippet
		 */
		public static function handle_disable_snippet() {
			check_ajax_referer( 'wpl_snippets_nonce', '_nonce' );

			$snippet = isset( $_POST['snippet'] ) ? sanitize_file_name( $_POST['snippet'] ) : '';

			if ( empty( $snippet ) ) {
				wp_send_json_error( __( 'Snippet name is missing.', 'wpl-toolkit' ) );
			}

			// Retrieve the list of enabled snippets from the database
			$all_snippets = get_option( 'wpltk_snippets', array() );

			// Check if the snippet is already enabled
			if ( ! isset( $all_snippets[ $snippet ] ) || $all_snippets[ $snippet ]['status'] === 'disabled' ) {
				wp_send_json_error( __( 'Snippet is already disabled.', 'wpl-toolkit' ) );
			}

			// Disable the snippet
			$all_snippets[ $snippet ]['status'] = 'disabled';

			// Update the option in the database
			if ( update_option( 'wpltk_snippets', $all_snippets ) === false ) {
				wp_send_json_error( __( 'Failed to disable the snippet.', 'wpl-toolkit' ) );
			}

			// Log success message for debugging if needed
			error_log( 'Snippet disabled: ' . $snippet );

			// Respond with a success message
			wp_send_json_success( __( 'Snippet disabled successfully.', 'wpl-toolkit' ) );
		}

		/**
		 * Handle deleting a snippet
		 */
		public static function handle_delete_snippet() {
			check_ajax_referer( 'wpl_toolkit_test_api', 'security' );

			$snippet = isset( $_POST['snippet'] ) ? sanitize_file_name( $_POST['snippet'] ) : '';

			if ( empty( $snippet ) ) {
				wp_send_json_error( __( 'Snippet name is missing.', 'wpl-toolkit' ) );
			}

			// Delete the snippet file
			$upload_dir = wp_upload_dir();
			$file_path  = $upload_dir['basedir'] . '/wpl-toolkit/' . $snippet;

			if ( file_exists( $file_path ) ) {
				unlink( $file_path );
				wp_send_json_success( __( 'Snippet deleted successfully.', 'wpl-toolkit' ) );
			} else {
				wp_send_json_error( __( 'Snippet not found.', 'wpl-toolkit' ) );
			}
		}

		/**
		 * Enqueue admin scripts and localize data for AJAX
		 */
		public static function enqueue_admin_scripts( $hook ) {
			// Only load scripts on the WPL Toolkit settings and snippets pages
			if ( $hook !== 'settings_page_wpl-toolkit-settings' ) {
				return;
			}

			// Enqueue admin scripts
			wp_enqueue_script(
				'wpl-admin-snippets-js',
				WPLTK_PLUGIN_URL . 'assets/js/wpl-admin-snippets.js',
				array( 'jquery' ),
				WPLTK_PLUGIN_VERSION,
				true
			);

			// Localize script to pass AJAX URL and nonce
			wp_localize_script(
				'wpl-admin-snippets-js',
				'wplSnippets',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'_nonce'  => wp_create_nonce( 'wpl_snippets_nonce' ),  // Use more descriptive nonce
					'actions' => array(
						'enable_snippet'  => 'wpl_enable_snippet',
						'disable_snippet' => 'wpl_disable_snippet',
						'delete_snippet'  => 'wpl_delete_snippet',
					),
				)
			);
		}
	}
}
