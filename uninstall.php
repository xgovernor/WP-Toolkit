<?php
/**
 * Uninstall WPL Toolkit
 *
 * @since 1.0.0
 * @package WPL Toolkit
 */

// If uninstall.php is not called by WordPress, terminate the script.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

$wpl_options = get_option( 'wpltk_settings' );

if ( isset( $wpl_options['delete_data_on_uninstall'] ) && $wpl_options['delete_data_on_uninstall'] ) {

	// Delete options
	$wpltk_options = array(
		'wpltk_settings',             // General settings.
		'wpltk_key',                  // API key.
		'wpltk_snippets',             // Snippets data.
		'wpltk_version',              // Plugin version.
		'wpltk_license_attempt',      // License attempt
		'wpltk_license_status',       // License status.
		'wpltk_license_key',          // License key.
		'wpltk_debug',                // Debug mode.
		'wpltk_onboarding_dismissed', // Dismissed onboarding.
		'wpltk_show_onboarding',      // Show onboarding.
		'wpltk_admin_notices',         // Admin notices.
	);

	// Delete options and site options.
	foreach ( $wpltk_options as $wpltk_option ) {
		delete_option( $wpltk_option );
		delete_site_option( $wpltk_option ); // Multisite-safe deletion.
	}


	// Delete plugin files.
	// Initialize WP_Filesystem API.
	require_once ABSPATH . 'wp-admin/includes/file.php';
	if ( WP_Filesystem() ) {
		// Recursive delete function for directory removal using WP_Filesystem.
		function wpltk_delete_directory_wpfilesystem( $dir ) {
			global $wp_filesystem;
			if ( $wp_filesystem->is_dir( $dir ) ) {
				$objects = $wp_filesystem->dirlist( $dir );
				foreach ( $objects as $object => $objectdata ) {
					$file_path = $dir . '/' . $object;
					if ( $wp_filesystem->is_dir( $file_path ) ) {
						wpltk_delete_directory_wpfilesystem( $file_path );
					} else {
						$wp_filesystem->delete( $file_path );
					}
				}
				$wp_filesystem->rmdir( $dir );
			}
		}

		// Delete the plugin's custom directory from uploads.
		$upload_dir = wp_upload_dir();
		$wpltk_dir  = $upload_dir['basedir'] . '/wpl-toolkit';
		wpltk_delete_directory_wpfilesystem( $wpltk_dir );
	} else {
		// Handle WP_Filesystem initialization failure if necessary.
		error_log( 'WP_Filesystem could not be initialized during WPL Toolkit uninstall.' );
	}


	// Remove custom database tables.
	global $wpdb;
	$table_names = array(
		$wpdb->prefix . 'wpltk_snippets',         // Snippets table.
		$wpdb->prefix . 'wpltk_settings',         // Settings table.
		$wpdb->prefix . 'wpltk_file_hashes',      // File integrity hashes.
		$wpdb->prefix . 'wpltk_event_logs',       // Event logs.
		$wpdb->prefix . 'wpltk_csp_log',          // CSP logs.
		$wpdb->prefix . 'wpltk_login_attempts',   // Login attempts.
	);

	// Drop each table if it exists.
	foreach ( $table_names as $table_name ) {
		$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
	}

	// Flush rewrite rules to clean up any custom rules set by the plugin.
	flush_rewrite_rules();

}
