<?php
/**
 * WPL - Settings
 *
 * @since 1.0.0
 *
 * @package WPLTK
 */

defined( 'ABSPATH' ) or die( 'you do not have access to this page!' );

if ( ! class_exists( 'WPL_Settings' ) ) {

	/**
	 * Summary of WPL_Settings
	 */
	class WPL_Settings {

		private static $_this;

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {
			if ( isset( self::$_this ) ) {
				wp_die( 'You can not create more than one instance of WPL_Settings' );
			}

			self::$_this = $this;
			add_action( 'admin_menu', array( $this, 'add_pages' ) );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			add_action( 'admin_init', array( $this, 'register_activation_settings' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
			add_filter('plugin_action_links_' . WPLTK_PLUGIN_BASENAME, array($this, 'plugin_action_links'), 10, 2);
		}

		public function plugin_action_links($links): array
		{
			$pre_links = array(
				'<a href="https://wpl-toolkit.com" target="_blank">' . __('WPL Studio', 'wpl-toolkit') . '</a>',
				'<a href="' . admin_url('admin.php?page=wpl-toolkit-settings&tab=settings') . '">' . __('Settings', 'wpl-toolkit') . '</a>',
			);
			$post_links = array(
				'<a href="' . admin_url('admin.php?page=wpl-toolkit-settings&tab=help') . '">' . __('Help', 'wpl-toolkit') . '</a>',
			);
			// $links[] = '<a href="' . admin_url('admin.php?page=wpl-toolkit-settings') . '">' . __('Settings', 'wpl-toolkit') . '</a>';
			return array_merge($pre_links, $links, $post_links);
		}

		/**
		 * Get the single instance of WPL_Settings.
		 *
		 * @since 1.0.0
		 *
		 * @return WPL_Settings The single instance of WPL_Settings.
		 */
		public static function this(): mixed
		{
			return self::$_this;
		}

		/**
		 * Add the admin menu page for WPL Toolkit.
		 *
		 * Checks if the logged in user has the capability to manage options.
		 * If they don't, the function exits early.
		 *
		 * @since 1.0.0
		 */
		public static function add_pages(): void
		{
			// If the logged in user doesn't have one of the whitelisted roles, exit
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			add_menu_page(
				__('WPL Toolkit', 'wpl-toolkit'), // Page title
				__('WPL Toolkit', 'wpl-toolkit'),  // Page title
				'manage_options', // Capability
				'wpl-toolkit-settings', // Menu slug
				array(self::class, 'render_wpl_toolkit_page'), // Callback function
				'dashicons-superhero', // Icon
				100// Position
			);
		}

		/**
		 * Register settings
		 *
		 * Note: The 'wpl' prefix is used to avoid conflicts with other plugins.
		 *
		 * - Remove data on plugin deactivation
		 * - Toggle WPL Toolbox access
		 * - Add WPL Toolbox user access role
		 *
		 * @since 1.0.0
		 */
		public static function register_settings() {
			/**
			 * Register settings
			 * Note: The 'wpl' prefix is used to avoid conflicts with other plugins.
			 * - Remove data on plugin deactivation
			 * - Toggle WPL Toolbox access
			 * - Add WPL Toolbox user access role
			 */
			register_setting(
				'wpl_toolkit_settings',
				'wpl_snippets',
				array(
					'sanitize_callback' => array( self::class, 'sanitize_snippets' ),
				)
			);

			// Add settings section
			add_settings_section(
				'wpl_settings_general',
				__( 'Snippets', 'wpl-toolkit' ),
				array( self::class, 'settings_section_callback' ),
				'wpl_toolkit_settings'
			);

			// Add toggle access field
			add_settings_field( 'wpl_toolkit_toggle_access', __( 'WPL Toolbox Access', 'wpl-toolkit' ), array( self::class, 'render_toggle_access' ), 'wpl_toolkit_settings', 'wpl_settings_general' );

			// Add allowed roles field
			add_settings_field( 'wpl_allowed_roles', __( 'Allowed Roles for Access', 'wpl-toolkit' ), array( self::class, 'allowed_roles_field_callback' ), 'wpl_toolkit_settings', 'wpl_settings_general' );

			// Enable Plugin Auto Update
			add_settings_field( 'wpl_toolkit_auto_update', __( 'Enable Plugin Auto Update', 'wpl-toolkit' ), array( self::class, 'render_auto_update' ), 'wpl_toolkit_settings', 'wpl_settings_general' );

			// Add debug field
			add_settings_field( 'wpl_toolkit_debug', __( 'Debug Mode', 'wpl-toolkit' ), array( self::class, 'render_debug' ), 'wpl_toolkit_settings', 'wpl_settings_general' );

			// Add remove data on deactivation field
			add_settings_field( 'wpl_toolkit_remove_data', __( 'Remove Data On Deactivation', 'wpl-toolkit' ), array( self::class, 'render_remove_data' ), 'wpl_toolkit_settings', 'wpl_settings_general' );
		}

		/**
		 * Callback for rendering the settings section description
		 */
		public static function settings_section_callback() {
			echo '<p>' . esc_html__( 'Select the snippets you want to enable.', 'wpl-toolkit' ) . '</p>';
		}

		/**
		 * Callback for rendering the toggle access field
		 */
		public static function render_toggle_access() {
			$value = get_option( 'wpl_toolkit_toggle_access', 0 );
			?>
							<input type="checkbox" name="wpl_toolkit_toggle_access" value="1" <?php checked( $value, 1 ); ?>>
							<?php
		}

		public static function allowed_roles_field_callback() {
			$roles          = wp_roles()->get_names();
			$selected_roles = get_option( 'wpl_allowed_roles', array( 'administrator' ) );
			?>
				<select name="wpl_allowed_roles[]" multiple>
					<?php foreach ( $roles as $role_key => $role_name ) : ?>
							<option value="<?php echo esc_attr( $role_key ); ?>" <?php echo in_array( $role_key, $selected_roles ) ? 'selected' : ''; ?>>
								<?php echo esc_html( $role_name ); ?>
							</option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Select which roles can access and manage WPL Toolkit.', 'wpl-toolkit' ); ?></p>
				<?php
		}

		public static function render_auto_update() {
			$value = get_option( 'wpl_automatic_updates', 1 );
			?>
							<input type="checkbox" name="wpl_automatic_updates" value="1" <?php checked( 1, $value ); ?>>
							<p class="description"><?php esc_html_e( 'Enable automatic updates for the WPL Toolkit.', 'wpl-toolkit' ); ?></p>
							<?php
		}

		/**
		 * Callback for rendering the debug field
		 */
		public static function render_debug() {
			$value = get_option( 'wpl_toolkit_debug', 0 );
			?>
							<input type="checkbox" name="wpl_toolkit_debug" value="1" <?php checked( $value, 1 ); ?>>
							<?php
		}

		/**
		 * Callback for rendering the remove data on deactivation field
		 */
		public static function render_remove_data() {
			$value = get_option( 'wpl_toolkit_remove_data', 0 );
			?>
							<input type="checkbox" name="wpl_toolkit_remove_data" value="1" <?php checked( $value, 1 ); ?>>
							<?php
		}

		/**
		 * Register activation settings, sections, and fields
		 */
		public static function register_activation_settings() {
			// Register the API key setting
			register_setting(
				'wpl_toolkit_api_settings',
				'wpltk_api_key',
				array(
					'sanitize_callback' => array( self::class, 'sanitize_api_key' ),
				)
			);

			// Add API settings section
			add_settings_section(
				'wpl_toolkit_api_section',
				__( 'API Settings', 'wpl-toolkit' ),
				array( self::class, 'render_api_settings_section' ),
				'wpl_toolkit_api_settings'
			);

			// Add API key field
			add_settings_field(
				'wpltk_api_key',
				__( 'API Key', 'wpl-toolkit' ),
				array( self::class, 'api_key_field_callback' ),
				'wpl_toolkit_api_settings',
				'wpl_toolkit_api_section'
			);
		}

		/**
		 * Callback for rendering the settings section description
		 */
		public static function render_api_settings_section() {
			echo '<p>' . esc_html__( 'Enter your API key to connect with the WPL platform.', 'wpl-toolkit' ) . '</p>';
		}

		/**
		 * Callback for rendering the API key field
		 */
		public static function api_key_field_callback() {
			$api_key = get_option( 'wpltk_api_key' );
			?>
			<input type="text" name="wpl_api_key" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text" />
			<button type="button" class="button" id="test-api-key"><?php esc_html_e( 'Test Connection', 'wpl-toolkit' ); ?></button>
			<p class="description"><?php esc_html_e( 'Ensure your API key is valid and working.', 'wpl-toolkit' ); ?></p>
			<div id="test-api-key-result"></div>
			<?php
		}

		/**
		 * Sanitize the API key before saving it
		 *
		 * @param string $input The API key input
		 * @return string The sanitized API key
		 */
		public static function sanitize_api_key( $input ) {
			$sanitized = sanitize_text_field( trim( $input ) );

			if ( empty( $sanitized ) || ! self::is_valid_api_key( $sanitized ) ) {
				add_settings_error( 'wpltk_api_key', 'invalid_key', __( 'The API key is invalid or improperly formatted. Please check and try again.', 'wpl-toolkit' ), 'error' );
				return get_option( 'wpltk_api_key' ); // Return previous value if validation fails
			}

			return $sanitized;
		}

		private static function is_valid_api_key( $key ) {
			// Perform more detailed validation if needed (e.g., regex check, length check)
			return strlen( $key ) >= 20; // Example condition
		}

		/**
		 * Render the settings page with dynamic tabs: Activation, Settings, Help.
		 */
		public static function render_wpl_toolkit_page() {
			include plugin_dir_path( __FILE__ ) . '../templates/page-wpl-toolkit.php';
		}

		/**
		 * Render the snippets tab content.
		 */
		private static function render_snippets_tab() {
			include plugin_dir_path( __FILE__ ) . '../templates/page-snippets.php';
		}

		/**
		 * Render the Activation tab content.
		 */
		private static function render_activation_tab() {
			include plugin_dir_path( __FILE__ ) . '../templates/page-activation.php';
		}

		/**
		 * Render the Settings tab content.
		 */
		private static function render_settings_tab() {
			include plugin_dir_path( __FILE__ ) . '../templates/page-settings.php';
		}

		/**
		 * Render the Help tab content.
		 */
		private static function render_help_tab() {
			include plugin_dir_path( __FILE__ ) . '../templates/page-help.php';
		}

		/**
		 * Enqueue admin scripts and localize data for AJAX
		 */
		public static function enqueue_admin_scripts($hook): void
		{
			// Only load scripts on the WPL Toolkit settings and snippets pages
			if ($hook !== 'toplevel_page_wpl-toolkit-settings') {
				return;
			}

			// Enqueue custom stylesheet for the plugin
			wp_enqueue_style(
				'wpl-toolkit-admin-css', // Handle for the stylesheet
				WPLTK_PLUGIN_URL . 'assets/css/admin-style.css', // Path to the CSS file
				array(), // Dependencies
				WPLTK_PLUGIN_VERSION, // Version number
				'all' // Media type (all, screen, print, etc.)
			);
		}
	}
}
