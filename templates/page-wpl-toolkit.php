<?php
/**
 * Callback for rendering the settings page
 *
 * @since 1.0.0
 * @package WPL Toolkit
 */

// Get the current tab or default to 'activation'.
$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'activation';
?>
<div class="privacy-settings-header wp">
	<div class="privacy-settings-title-section">
		<h1 style="margin-bottom: 34px;"><?php esc_html_e( 'WPL Toolkit Settings', 'wpl-toolkit' ); ?></h1>
		</div>

	<nav class="privacy-settings-tabs-wrapper hide-if-no-js" style="grid-template-columns: repeat(4, 1fr);" aria-label="WPL Toolkit Settings Tabs">
		<a href="?page=wpl-toolkit-settings&tab=snippets" class="privacy-settings-tab <?php echo $active_tab === 'snippets' ? 'active' : ''; ?>">
			<?php esc_html_e( 'Snippets', 'wpl-toolkit' ); ?>
		</a>
		<a href="?page=wpl-toolkit-settings&tab=activation"
			class="privacy-settings-tab <?php echo $active_tab === 'activation' ? 'active' : ''; ?>">
			<?php esc_html_e( 'Activation', 'wpl-toolkit' ); ?>
		</a>
		<a href="?page=wpl-toolkit-settings&tab=settings"
			class="privacy-settings-tab <?php echo $active_tab === 'settings' ? 'active' : ''; ?>">
			<?php esc_html_e( 'Settings', 'wpl-toolkit' ); ?>
		</a>
		<a href="?page=wpl-toolkit-settings&tab=help"
			class="privacy-settings-tab <?php echo $active_tab === 'help' ? 'active' : ''; ?>">
			<?php esc_html_e( 'Help', 'wpl-toolkit' ); ?>
		</a>
		</nav>
		</div>

<div class="wrap">
	<?php
	// Display the content of the active tab.
	switch ( $active_tab ) {
		case 'snippets':
			self::render_snippets_tab();
			break;
		case 'activation':
			self::render_activation_tab();
			break;
		case 'settings':
			self::render_settings_tab();
			break;
		case 'help':
			self::render_help_tab();
			break;
		default:
			self::render_activation_tab();
			break;
	}
	?>
</div>
<?php
