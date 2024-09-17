<?php
/**
 * WPL Toolkit - Snippet Executor
 *
 * @since 1.0.0
 * @package WPL Toolkit
 */

defined( 'ABSPATH' ) || die( 'You do not have access to this page!' );

if ( ! class_exists( 'WPL_Snippet_Executor' ) ) {
	class WPL_Snippet_Executor {


		private static $_this;

		public function __construct() {
			if ( isset( self::$_this ) ) {
				wp_die( 'You cannot create more than one instance of WPL_Snippet_Executor.' );
			}

			self::$_this = $this;
		}

		public static function instance() {
			return self::$_this;
		}

		/**
		 * Execute a snippet file in an isolated environment
		 *
		 * @param string $file_path Path to the snippet file
		 * @return bool True on success, false on failure
		 */
		public static function execute_snippet( $file_path ) {
			// Sanitize the file path
			$file_path = sanitize_text_field( $file_path );

			// Check if the file exists, is readable, and is a valid PHP file
			if ( ! self::is_valid_file( $file_path ) ) {
				error_log( 'Invalid or unreadable snippet file: ' . $file_path );
				return false;
			}

			// Use a closure to isolate the execution context
			$execute = function () use ( $file_path ) {
				include $file_path;
			};

			try {
				ob_start();
				$execute();
				ob_end_clean();
			} catch ( \Throwable $e ) { // Handle any kind of error (PHP 7+)
				self::handle_execution_error( $e );
				return false;
			}

			return true;
		}

		/**
		 * Validate snippet file
		 *
		 * @param string $file_path Path to the snippet file
		 * @return bool True if valid, false otherwise
		 */
		private static function is_valid_file( $file_path ) {
			// Ensure file exists, is readable, and has a .php extension
			return file_exists( $file_path ) && is_readable( $file_path ) && pathinfo( $file_path, PATHINFO_EXTENSION ) === 'php';
		}

		/**
		 * Handle errors during snippet execution
		 *
		 * @param \Throwable $e The caught error/exception
		 */
		private static function handle_execution_error( \Throwable $e ) {
			// Log the error with a stack trace for debugging
			error_log(
				sprintf(
					'Snippet execution error [%s]: %s in %s on line %d',
					get_class( $e ),
					$e->getMessage(),
					$e->getFile(),
					$e->getLine()
				)
			);

			// Optional: Notify administrators via email or trigger other alerts
			// wp_mail(...);

			// Trigger emergency recovery mode if necessary
			do_action( 'wpl_before_recovery_mode', $e ); // Hook for custom actions
			self::trigger_recovery_mode();
			do_action( 'wpl_after_recovery_mode', $e );  // Hook for logging/recovery actions
		}

		/**
		 * Trigger emergency recovery mode to disable all snippets
		 */
		private static function trigger_recovery_mode() {
			// Activate recovery mode by setting a transient or an option
			update_option( 'wpl_recovery_mode', true );

			// Optionally, notify administrators or take further actions
			// wp_mail(...);

			do_action( 'wpl_recovery_mode_activated' ); // Custom hook for recovery mode
		}

		/**
		 * Check if emergency recovery mode is active
		 *
		 * @return bool True if recovery mode is active, false otherwise
		 */
		public static function is_recovery_mode_active() {
			return (bool) get_option( 'wpl_recovery_mode', false );
		}

		/**
		 * Disable recovery mode and reactivate all snippets
		 */
		public static function disable_recovery_mode() {
			delete_option( 'wpl_recovery_mode' );
			do_action( 'wpl_recovery_mode_disabled' ); // Hook for post-recovery actions
		}
	}
}
