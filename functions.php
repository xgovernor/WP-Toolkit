<?php
defined( 'ABSPATH' ) or die();
/**
 *  Only functions also required on front-end here
 */

/**
 * Get a Really Simple SSL option by name
 *
 * @param string $name
 * @param mixed  $default_value
 *
 * @return mixed
 */
function wpltk_get_option( string $name, $default_value = false ) {
	$name = sanitize_title( $name );

	if ( is_multisite() && wpltk_is_networkwide_active() ) {
		$options = get_site_option( 'wpltk_options', array() );
	} else {
		$options = get_option( 'wpltk_options', array() );
	}

	$value = $options[ $name ] ?? false;
	if ( false === $value && false !== $default_value ) {
		$value = $default_value;
	}

	if ( 1 === $value ) {
		$value = true;
	}

	return apply_filters( "wpltk_option_$name", $value, $name );
}

/**
 * Check if we should treat the plugin as networkwide or not.
 * Note that this function returns false for single sites! Always use icw is_multisite()
 *
 * @return bool
 */
function wpltk_is_networkwide_active(): bool {
	// Check if we are on multisite
	if ( ! is_multisite() ) {
		return false;
	}

	// Check if the plugin is active
	if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
	}

	// Check if the plugin is active
	if ( is_plugin_active_for_network( wplk_plugin ) ) {
		return true;
	}

	return false;
}

/**
 * Retrieves the path to a template file.
 *
 * @param string $template The name of the template to retrieve.
 * @param string $path (Optional) The path to look for the template file. If not specified, the default path will be used.
 *
 * @return string The full path to the template file.
 * @throws \RuntimeException Throws a runtime exception if the template file cannot be found.
 */
function wpltk_get_template( string $template, string $path = '' ): string {
	// Define the path in the theme where templates can be overridden.
	$theme_template_path = get_stylesheet_directory() . '/wpl-toolkit/templates/' . $template;

	// Check if the theme has an override for the template.
	if ( file_exists( $theme_template_path ) ) {
		return $theme_template_path;
	}
	// If $path is not set, use the default path
	if ( $path === '' ) {
		$path = rsssl_path . 'templates/'; // Remember this only works in free version, for pro we need to add the $path parameter/argument
	} else {
		// Ensure the path ends with a slash
		$path = trailingslashit( $path );
	}

	// Full path to the template file
	$full_path = $path . $template;

	// Check if the template exists in the specified path.
	if ( ! file_exists( $full_path ) ) {
		throw new \RuntimeException( 'Template not found: ' . $full_path );
	}

	return $full_path;
}

/**
 * Loads a template file and includes it.
 *
 * @param string $template The name of the template to load.
 * @param array  $vars (Optional) An associative array of variables to make available in the template scope.
 * @param string $path (Optional) The path to look for the template file. If not specified, the default path will be used.
 *
 * @return void
 * @throws Exception Throws an exception if the template file cannot be found.
 */
function wpltk_load_template( string $template, array $vars = array(), string $path = '' ) {
	// Extract variables to be available in the template scope.
	if ( is_array( $vars ) ) {
		extract( $vars );
	}

	// Get the template file, checking for theme overrides.
	$template_file = wpltk_get_template( $template, $path );

	// Include the template file.
	include $template_file;
}

/**
 * Get the path to the wp-config.php file.
 *
 * @return string Path to the wp-config.php file, or an empty string if not found.
 */
if ( ! function_exists( 'wpltk_wpconfig_path' ) ) {

	function wpltk_wpconfig_path(): string {
		// Check if wp-config.php exists in the standard location
		$config_path = ABSPATH . 'wp-config.php';
		if ( file_exists( $config_path ) ) {
			return $config_path;
		}

		// Check if wp-config.php exists one level above ABSPATH
		$parent_path = dirname( ABSPATH ) . '/wp-config.php';
		if ( file_exists( $parent_path ) ) {
			return $parent_path;
		}

		// Log an error if wp-config.php is not found
		error_log( 'wp-config.php file not found.' );

		// Return an empty string if the file is not found
		return '';
	}
}
