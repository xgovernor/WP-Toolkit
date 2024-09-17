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
