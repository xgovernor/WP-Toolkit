<?php
/**
 * Callback for rendering the settings page
 *
 * @package WPL Toolkit
 */

?>
<div class="wrap" style="max-width: 800px; margin: 0 auto; padding-top: 1px">
	<h2><?php esc_html_e( 'Settings', 'wpl-toolkit' ); ?></h2>
	<p><?php esc_html_e( 'This section provides settings for the WPL Toolkit plugin.', 'wpl-toolkit' ); ?></p>

	<form method="post" action="options.php">
		<?php
		settings_fields( 'wpl_toolkit_settings' );

		do_settings_sections( 'wpl_toolkit_settings' );

		submit_button( __( 'Save Changes', 'wpl-toolkit' ) );
		?>
	</form>
</div>

<?php
