<?php
/**
 * Callback for rendering the activation page
 *
 * @package WPL Toolkit
 *
 * @since 1.0.0
 */

?>
<div class="wrap" style="max-width: 800px; margin: 0 auto; padding-top: 1px">
	<form method="post" action="options.php">
		<?php
		// Output security fields for the registered setting "wpl_toolkit_api_settings".
		settings_fields( 'wpl_toolkit_api_settings' );

		// Output setting sections and their fields.
		do_settings_sections( 'wpl_toolkit_api_settings' );

		submit_button( __( 'Save Changes', 'wpl-toolkit' ) );
		?>
	</form>

	<div id="test-api-key-result"></div>
</div>

<?php
