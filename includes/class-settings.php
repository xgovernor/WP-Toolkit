<?php
/**
 * Settings and Snippets page class for WPL Toolkit
 */

class WPL_Settings
{
    public static function init()
    {
        // Add the settings and snippets pages to the admin menu
        add_action('admin_menu', [self::class, 'add_pages']);
        // Register settings and fields
        add_action('admin_init', [self::class, 'register_activation_settings']);
        add_action('admin_init', [self::class, 'register_settings']);

        // Enqueue admin scripts and styles
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_admin_scripts']);
    }

    /**
     * Add settings and snippets pages to the WordPress admin menu
     */
    public static function add_pages()
    {
        // If the logged in user doesn't have one of the whitelisted roles, exit
        if (!current_user_can('manage_options')) {
            return;
        }

        // Add snippets page as a submenu of settings menu
        add_submenu_page(
            'options-general.php',                      // Parent slug
            __('WPL Toolkit', 'wpl-toolkit'),           // Page title
            __('WPL Toolkit', 'wpl-toolkit'),           // Menu title
            'manage_options',                           // Capability
            'wpl-toolkit-settings',                     // Menu slug
            [self::class, 'render_wpl_toolkit_page'],   // Callback function
            10                                           // Position
        );
    }

    /**
     * Register settings and fields
     */
    public static function register_settings()
    {
        /**
         * Register settings
         * Note: The 'wpl' prefix is used to avoid conflicts with other plugins.
         * - Remove data on plugin deactivation
         * - Toggle WPL Toolbox access
         * - Add WPL Toolbox user access role
         */
        register_setting('wpl_toolkit_settings', 'wpl_snippets', [
            'sanitize_callback' => [self::class, 'sanitize_snippets'],
        ]);

        // Add settings section
        add_settings_section(
            'wpl_settings_general',
            __('Snippets', 'wpl-toolkit'),
            [self::class, 'settings_section_callback'],
            'wpl_toolkit_settings'
        );

        // Add toggle access field
        add_settings_field( 'wpl_toolkit_toggle_access', __('WPL Toolbox Access', 'wpl-toolkit'), [self::class, 'render_toggle_access'], 'wpl_toolkit_settings', 'wpl_settings_general');

        // Add allowed roles field
        add_settings_field('wpl_allowed_roles', __('Allowed Roles for Access', 'wpl-toolkit'), [self::class, 'allowed_roles_field_callback'], 'wpl_toolkit_settings', 'wpl_settings_general' );

        // Enable Plugin Auto Update
        add_settings_field( 'wpl_toolkit_auto_update', __('Enable Plugin Auto Update', 'wpl-toolkit'), [self::class, 'render_auto_update'], 'wpl_toolkit_settings', 'wpl_settings_general');

        // Add debug field
        add_settings_field( 'wpl_toolkit_debug', __('Debug Mode', 'wpl-toolkit'), [self::class, 'render_debug'], 'wpl_toolkit_settings', 'wpl_settings_general');

        // Add remove data on deactivation field
        add_settings_field( 'wpl_toolkit_remove_data', __('Remove Data On Deactivation', 'wpl-toolkit'), [self::class, 'render_remove_data'], 'wpl_toolkit_settings', 'wpl_settings_general');

    }

    /**
     * Callback for rendering the settings section description
     */
    public static function settings_section_callback()
    {
        echo '<p>' . esc_html__('Select the snippets you want to enable.', 'wpl-toolkit') . '</p>';
    }

    /**
     * Callback for rendering the toggle access field
     */
    public static function render_toggle_access()
    {
        $value = get_option('wpl_toolkit_toggle_access', 0);
        ?>
        <input type="checkbox" name="wpl_toolkit_toggle_access" value="1" <?php checked($value, 1); ?>>
        <?php
    }

    public static function allowed_roles_field_callback() {
        $roles = wp_roles()->get_names();
        $selected_roles = get_option('wpl_allowed_roles', ['administrator']);
        ?>
        <select name="wpl_allowed_roles[]" multiple>
            <?php foreach ($roles as $role_key => $role_name) : ?>
                <option value="<?php echo esc_attr($role_key); ?>" <?php echo in_array($role_key, $selected_roles) ? 'selected' : ''; ?>>
                    <?php echo esc_html($role_name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description"><?php esc_html_e('Select which roles can access and manage WPL Toolkit.', 'wpl-toolkit'); ?></p>
        <?php
    }

    public static function render_auto_update() {
        $value = get_option('wpl_automatic_updates', 1);
        ?>
        <input type="checkbox" name="wpl_automatic_updates" value="1" <?php checked(1, $value); ?>>
        <p class="description"><?php esc_html_e('Enable automatic updates for the WPL Toolkit.', 'wpl-toolkit'); ?></p>
        <?php
    }

    /**
     * Callback for rendering the debug field
     */
    public static function render_debug()
    {
        $value = get_option('wpl_toolkit_debug', 0);
        ?>
        <input type="checkbox" name="wpl_toolkit_debug" value="1" <?php checked($value, 1); ?>>
        <?php
    }

    /**
     * Callback for rendering the remove data on deactivation field
     */
    public static function render_remove_data()
    {
        $value = get_option('wpl_toolkit_remove_data', 0);
        ?>
        <input type="checkbox" name="wpl_toolkit_remove_data" value="1" <?php checked($value, 1); ?>>
        <?php
    }

    /**
     * Register activation settings, sections, and fields
     */
    public static function register_activation_settings()
    {
        // Register the API key setting
        register_setting('wpl_toolkit_api_settings', 'wpl_api_key', [
            'sanitize_callback' => [self::class, 'sanitize_api_key'],
        ]);

        // Add API settings section
        add_settings_section(
            'wpl_toolkit_api_section',
            __('API Settings', 'wpl-toolkit'),
            [self::class, 'render_api_settings_section'],
            'wpl_toolkit_api_settings'
        );

        // Add API key field
        add_settings_field(
            'wpl_api_key',
            __('API Key', 'wpl-toolkit'),
            [self::class, 'api_key_field_callback'],
            'wpl_toolkit_api_settings',
            'wpl_toolkit_api_section'
        );
    }

    /**
     * Callback for rendering the settings section description
     */
    public static function render_api_settings_section()
    {
        echo '<p>' . esc_html__('Enter your API key to connect with the WPL platform.', 'wpl-toolkit') . '</p>';
    }

    /**
     * Callback for rendering the API key field
     */
    public static function api_key_field_callback()
    {
        $api_key = get_option('wpl_api_key');
        ?>
        <input type="text" name="wpl_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" />
        <button type="button" class="button" id="test-api-key"><?php esc_html_e('Test Connection', 'wpl-toolkit'); ?></button>
        <p class="description"><?php esc_html_e('Ensure your API key is valid and working.', 'wpl-toolkit'); ?></p>
        <div id="test-api-key-result"></div>
        <?php
    }

    /**
     * Sanitize the API key before saving it
     *
     * @param string $input The API key input
     * @return string The sanitized API key
     */
    public static function sanitize_api_key($input) {
        $sanitized = sanitize_text_field(trim($input));

        if (empty($sanitized) || !self::is_valid_api_key($sanitized)) {
            add_settings_error('wpl_api_key', 'invalid_key', __('The API key is invalid or improperly formatted. Please check and try again.', 'wpl-toolkit'), 'error');
            return get_option('wpl_api_key'); // Return previous value if validation fails
        }

        return $sanitized;
    }

    private static function is_valid_api_key($key) {
        // Perform more detailed validation if needed (e.g., regex check, length check)
        return strlen($key) >= 20; // Example condition
    }

    /**
     * Render the settings page with dynamic tabs: Activation, Settings, Help.
     */
    public static function render_wpl_toolkit_page()
    {
        include plugin_dir_path( __FILE__ ) . '../templates/page-wpl-toolkit.php';
    }

    /**
     * Render the snippets tab content.
     */
    private static function render_snippets_tab()
    {
        include plugin_dir_path( __FILE__ ) . '../templates/page-snippets.php';
    }

    /**
     * Render the Activation tab content.
     */
    private static function render_activation_tab()
    {
        include plugin_dir_path( __FILE__ ) . '../templates/page-activation.php';
    }

    /**
     * Render the Settings tab content.
     */
    private static function render_settings_tab()
    {
        include plugin_dir_path( __FILE__ ) . '../templates/page-settings.php';
    }

    /**
     * Render the Help tab content.
     */
    private static function render_help_tab()
    {
        include plugin_dir_path( __FILE__ ) . '../templates/page-help.php';
    }

    /**
     * Enqueue admin scripts and localize data for AJAX
     */
    public static function enqueue_admin_scripts($hook)
    {
        // Only load scripts on the WPL Toolkit settings and snippets pages
        if ($hook !== 'settings_page_wpl-toolkit-settings') {
            return;
        }

        // Enqueue custom stylesheet for the plugin
        wp_enqueue_style(
            'wpl-toolkit-admin-css', // Handle for the stylesheet
            WPL_TOOLKIT_URL . 'assets/css/admin-style.css', // Path to the CSS file
            [], // Dependencies
            WPL_TOOLKIT_VERSION, // Version number
            'all' // Media type (all, screen, print, etc.)
        );

        // Enqueue admin scripts
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

}

