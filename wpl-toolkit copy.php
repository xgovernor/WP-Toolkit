<?php
/**
 * Plugin Name: WPL Toolkit
 *
 * @version   1.0.0
 * @package WPL_Toolkit
 * @author Abu Taher Muhammad (https://at-mah.vercel.app)
 * @author Dot9 (https://github.com/dot9)
 * @copyright Copyright (c) 2024, WPL Toolkit Plugins
 * @license https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/xgovernor/wp-toolkit
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
 * Text Domain: toolkit
 * Domain Path: /languages
 * Network: true
 */
/*
	Copyright 2024  WPL Toolkit Plugins BV  (email : support@wpl-toolkit.com)
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

defined( 'ABSPATH' ) || die( 'Hi there!  I\'m just a plugin, not much I can do when called directly.' );


if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
	include_once dirname(__FILE__) . '/vendor/autoload.php';
}

use WPLTK\App\Helpers\Arr;
use WPLTK\App\Helpers\Helper;


if ( ! class_exists( 'WPL_Toolkit' ) ) {
	class WPL_Toolkit {

		private static $instance;
		public $multisite;
		public $cache;
		// public $server;
		// public $admin;
		// public $progress;
		// public $onboarding;
		// public $placeholder;
		public $wp_cli;
		// public $mailer_admin;
		// public $site_health;
		// public $vulnerabilities;
		public $settings;
		public $api;
		public $webhooks;
		public $snippets;
		public $snippet_executor;

		private function __construct() {
		}

		/**
		 * Get the instance of the plugin.
		 *
		 * @return WPL_Toolkit
		 */
		public static function instance(): WPL_Toolkit {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WPL_Toolkit ) ) {
				self::$instance = new WPL_Toolkit();
				self::$instance->setup_constants();
				self::$instance->includes();

				if ( is_multisite() ) {
					self::$instance->multisite = new wpltk_multisite();
				}

				if ( wpltk_admin_logged_in() ) {
					self::$instance->cache = new WPLTK_Cache();
					// self::$instance->placeholder = new WPLTK_Placeholder();
					// self::$instance->server = new WPLTK_Server();
					// self::$instance->admin = new WPLTK_Admin();
					// self::$instance->mailer_admin = new WPLTK_Mailer_Admin();
					// self::$instance->onboarding = new WPLTK_Onboarding();
					// self::$instance->progress = new WPLTK_Progress();
					// self::$instance->certificate = new WPLTK_Certificate();
					// self::$instance->site_health = new WPLTK_Site_Health();
					if ( defined( 'WP_CLI' ) && WP_CLI ) {
						self::$instance->wp_cli = new WPLTK_WP_CLI();
					}

					self::$instance->settings         = new WPL_Settings();
					self::$instance->api              = new WPL_Api();
					self::$instance->webhooks         = new WPL_Webhooks();
					self::$instance->snippets         = new WPL_Snippets();
					self::$instance->snippet_executor = new WPL_Snippet_Executor();
				}

				self::$instance->hooks();
			}

			return self::$instance;
		}

		private function setup_constants(): void
		{
			define( 'WPLTK_PLUGIN_BASENAME', value: plugin_basename( __FILE__ ) );
			define( 'WPLTK_PLUGIN_VERSION', value: '1.0.0' );
			define( 'WPLTK_PLUGIN_PATH', value: trailingslashit( plugin_dir_path( __FILE__ ) ) );
			define( 'WPLTK_PLUGIN_URL', value: plugin_dir_url( __FILE__ ) );
			$upload_dir = wp_upload_dir();  // Get the WP uploads directory info
			define( 'WPLTK_SNIPPET_PATH', value: $upload_dir['basedir'] . '/wpl-toolkit' );
			define( 'WPLTK_TEMPLATE_PATH', value: WPLTK_PLUGIN_PATH . 'templates/' );
			define( 'WPLTK_SAFE_INTEGRATION_TIMEOUT', value: 60 * 60 * 24 );  // Snippets integration test's save timeout frame in seconds. After this time, the snippet will be automatically disabled if the integration test fails.
		}

		private function includes(): void {
			// Load plugin text domain for translations
			load_plugin_textdomain('wpl-toolkit', false, dirname(path: plugin_basename(__FILE__)) . '/languages');
			require_once WPLTK_PLUGIN_PATH . 'functions.php';
			require_once WPLTK_PLUGIN_PATH . 'includes/class-settings.php';
			require_once WPLTK_PLUGIN_PATH . 'includes/class-api.php';
			require_once WPLTK_PLUGIN_PATH . 'includes/class-webhooks.php';
			require_once WPLTK_PLUGIN_PATH . 'includes/class-snippets.php';
			require_once WPLTK_PLUGIN_PATH . 'includes/class-snippet-executor.php';

			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				require_once WPLTK_PLUGIN_PATH . 'includes/class-wp-cli.php';
			}

			if ( is_multisite() ) {
				require_once WPLTK_PLUGIN_PATH . 'includes/class-multisite.php';
			}

			if ( wpltk_admin_logged_in() ) {
				// require_once( WPLTK_PLUGIN_PATH . 'compatibility.php');
				// require_once( WPLTK_PLUGIN_PATH . 'upgrade.php');
				// require_once( WPLTK_PLUGIN_PATH . 'settings/settings.php' );
				// require_once( WPLTK_PLUGIN_PATH . 'modal/modal.php' );
				// require_once( WPLTK_PLUGIN_PATH . 'onboarding/class-onboarding.php' );
				// require_once( WPLTK_PLUGIN_PATH . 'placeholders/class-placeholder.php' );
				// require_once( WPLTK_PLUGIN_PATH . 'class-admin.php');
				// require_once( WPLTK_PLUGIN_PATH . 'mailer/class-mail-admin.php');
				require_once WPLTK_PLUGIN_PATH . 'includes/class-cache.php';
				// require_once( WPLTK_PLUGIN_PATH . 'class-server.php');
				// require_once( WPLTK_PLUGIN_PATH . 'progress/class-progress.php');
				// require_once( WPLTK_PLUGIN_PATH . 'class-site-health.php');
				// require_once( WPLTK_PLUGIN_PATH . 'mailer/class-mail.php');
				// if ( isset($_GET['install_pro'])) {
				// require_once( WPLTK_PLUGIN_PATH . 'upgrade/upgrade-to-pro.php');
				// }
			}

			// require_once( WPLTK_PLUGIN_PATH . 'lets-encrypt/cron.php' );
			// require_once( WPLTK_PLUGIN_PATH . '/security/security.php');
		}

		private function hooks(): void {
			if ( wpltk_admin_logged_in() ) {

				// add_action('admin_notices', array( $this, 'admin_notices'));
				// if ( is_multisite() ) {
				// add_action('network_admin_notices', array( $this, 'admin_notices'));
				// }
			}

			// add_action('wp_loaded', array(self::$instance->front_end, 'force_ssl'), 20);

			// if ( wpltk_admin_logged_in() ) {
			// add_action('plugins_loaded', array(self::$instance->admin, 'init'), 10);
			// }
		}

		/**
		 * Activate the plugin.
		 *
		 * Adds default options and creates the plugin's upload directory.
		 *
		 * @since 1.0.0
		 */
		public static function activate(): void {
			$wpltk_options = array(
				array(
					'key'   => 'wpltk_api_key',
					'value' => '',
				),
				array(
					'key'   => 'wpltk_snippets',
					'value' => array(),
				),
				array(
					'key'   => 'wpltk_show_onboarding',
					'value' => 1,  // 1 = on, 0 = off
				),
				array(
					'key'   => 'wpltk_onboarding_dismissed',
					'value' => '', // null = not disabled, <timestamp> = disabled until timestamp
				),
				array(
					'key'   => 'wpltk_update_notice',
					'value' => 0, // 1 = on, 0 = off
				),
				array(
					'key'   => 'wpltk_update_notice_dismissed',
					'value' => '', // null = not disabled, <timestamp> = disabled until timestamp
				),
				array(
					'key'   => 'wpltk_freeze_access',
					'value' => 0,
				),
				array(
					'key'   => 'wpltk_version',
					'value' => '1.0.0',
				),
				array(
					'key'   => 'wpltk_previous_version',
					'value' => '1.0.0',
				),
				array(
					'key'   => 'wpltk_installation_date',
					'value' => gmdate( 'Y-m-d H:i:s', time() ), // YYYY-MM-DD HH:MM:SS
				),
				array(
					'key'   => 'wpltk_activation_date',
					'value' => gmdate( 'Y-m-d H:i:s', time() ),
				),
				array(
					'key'   => 'wpltk_options',
					'value' => array(),
				),
				array(
					'key'   => 'wpltk_settings',
					'value' => array(),
				),
			);

			foreach ( $wpltk_options as $option ) {
				if ( ! add_option( $option['key'], $option['value'] ) ) {
					error_log( '[WPL Activation] Failed to add option: ' . $option['key'] );
				}
			}

			// Create /wpl-toolkit directory inside /uploads
			self::wpl_create_dir();

			flush_rewrite_rules();
		}

		/**
		 * Deactivate the plugin
		 */
		public static function deactivate(): void {
			$db_options = array(
				'wpltk_api_key',
				'wpltk_snippets',
				'wpltk_show_onboarding',
				'wpltk_onboarding_dismissed',
				'wpltk_update_notice',
				'wpltk_update_notice_dismissed',
				'wpltk_freeze_access',
				'wpltk_version',
				'wpltk_previous_version',
				'wpltk_installation_date',
				'wpltk_activation_date',
				'wpltk_options',
				'wpltk_settings',
			);
			foreach ( $db_options as $option ) {
				delete_option( $option );
				delete_site_option( $option );
			}

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
		private static function wpl_create_dir(): void {
			$wpl_dir = wp_upload_dir()['basedir'] . '/wpl-toolkit';
			// Create /wpl-toolkit directory inside /uploads if it doesn't exist
			wp_mkdir_p( $wpl_dir );
		}

		/**
		 * Remove /wpl-toolkit directory inside /uploads if it exists.
		 *
		 * This directory is used to store the snippets downloaded from the WPL platform.
		 *
		 * @since 1.0.0
		 */
		private static function wpl_remove_dir(): void {
			// Get the path to the /wpl-toolkit directory inside /uploads
			$dir = wp_upload_dir()['basedir'] . '/wpl-toolkit';

			// Check if the directory exists
			if ( is_dir( filename: $dir ) ) {
				// Attempt to delete the directory and its contents
				self::delete_directory_recursive( dir: $dir );
			}
		}

		/**
		 * Recursively delete a directory and its contents.
		 *
		 * @param string $dir The directory path to delete.
		 * @return bool True on success, false on failure.
		 */
		private static function delete_directory_recursive( $dir ): bool {
			// Ensure the directory exists and is a directory
			if ( ! is_dir( filename: $dir ) ) {
				return false;
			}

			// Get all files and directories within the directory
			$items = scandir( directory: $dir );
			foreach ( $items as $item ) {
				// Skip the current and parent directory pointers
				if ( $item === '.' || $item === '..' ) {
					continue;
				}

				$path = $dir . '/' . $item;

				// If the item is a directory, recursively delete it
				if ( is_dir( filename: $path ) ) {
					self::delete_directory_recursive( dir: $path );
				} else {
					// Otherwise, it's a file, so delete it
					if ( ! unlink( filename: $path ) ) {
						error_log( message: "Failed to delete file: $path" );
					}
				}
			}

			// Remove the now-empty directory
			if ( ! rmdir( directory: $dir ) ) {
				error_log( message: "Failed to remove directory: $dir" );
				return false;
			}

			return true;
		}
	}

	/**
	 * Returns the one and only instance of WPL_Toolkit.
	 *
	 * @since 1.0.0
	 * @return WPL_Toolkit The one and only WPL_Toolkit instance.
	 */
	function WPLTK(): WPL_Toolkit {
		return WPL_Toolkit::instance();
	}

	add_action( 'plugins_loaded', 'WPLTK', 8 );

	// Register activation and deactivation hooks
	register_activation_hook( __FILE__, array( 'WPL_Toolkit', 'activate' ) );
	register_deactivation_hook( __FILE__, array( 'WPL_Toolkit', 'deactivate' ) );
}


if ( ! function_exists( function: 'wpltk_add_manage_wpltk_capability' ) ) {

	/**
	 * Add a user capability to WordPress and add to admin and editor role
	 */
	function wpltk_add_manage_wpltk_capability(): void {
		$role = get_role( 'administrator' );

		if ( $role && ! $role->has_cap( 'manage_wpltk' ) ) {
			$role->add_cap( 'manage_wpltk' );
		}
	}

	register_activation_hook( __FILE__, 'wpltk_add_manage_wpltk_capability' );
}

if ( ! function_exists( 'wpltk_user_can_manage' ) ) {

	/**
	 * Check if user has required capability
	 *
	 * @return bool
	 */
	function wpltk_user_can_manage(): bool {
		if ( current_user_can( 'manage_wpltk' ) ) {
			return true;
		}

		// allow wp-cli access to manage_wpltk
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'wpltk_admin_logged_in' ) ) {

	function wpltk_admin_logged_in(): bool {
		$wpcli = defined( 'WP_CLI' ) && WP_CLI;
		return ( is_admin() && wpltk_user_can_manage() ) || wpltk_is_logged_in_rest() || wp_doing_cron() || $wpcli || defined( 'WPLTK_DOING_SYSTEM_STATUS' ) || defined( 'WPLTK_LEARNING_MODE' );
	}
}

if ( ! function_exists( 'wpltk_is_logged_in_rest' ) ) {

	function wpltk_is_logged_in_rest(): bool {
		$valid_request = isset( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], '/wpltk/v1/' ) !== false;
		if ( ! $valid_request ) {
			return false;
		}
		return is_user_logged_in();
	}
}
