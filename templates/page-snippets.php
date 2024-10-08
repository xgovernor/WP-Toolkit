<?php
/**
 * Callback for rendering the snippets page
 *
 * @since 1.0.0
 * @package WPL Toolkit
 */

$uploads_dir = wp_upload_dir()['basedir'] . '/wpl-toolkit/';
$snippets    = array();

// Ensure the directory exists.
if ( ! is_dir( $uploads_dir ) ) {
	echo '<div class="notice notice-error"><p>' . esc_html__( 'The snippets directory does not exist.', 'wpl-toolkit' ) . '</p></div>';
	return;
}

// Get list of files in the directory.
$files = array_diff( scandir( $uploads_dir ), array( '.', '..' ) );

foreach ( $files as $file ) {
	// Assuming each file is a snippet and you want to display its name and additional details
	$file_path = $uploads_dir . '/' . $file;
	if ( is_file( $file_path ) ) {
		// Add the snippet file to the array with appropriate details.
		// You can modify the details based on your needs.
		$snippets[] = array(
			'name'     => esc_html( $file ), // Convert into File Name" forma.t
			'size'     => esc_html( filesize( $file_path ) ),
			'modified' => gmdate( 'F d Y H:i:s', filemtime( $file_path ) ),
			'status'   => '<span class="status-enabled">' . esc_html__( 'Enabled', 'wpl-toolkit' ) . '</span>', // Placeholder status.
		);
	}
}
?>
<div class="wrap" style="max-width: 800px; margin: 0 auto; padding-top: 1px">

	<div id="snippet-toggle-result"></div>

	<h2><?php esc_html_e( 'Manage Snippets', 'wpl-toolkit' ); ?></h2>
	<p><?php esc_html_e( 'Manage your snippets here.', 'wpl-toolkit' ); ?></p>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Snippet Name', 'wpl-toolkit' ); ?></th>
				<th><?php esc_html_e( 'Size', 'wpl-toolkit' ); ?></th>
				<th><?php esc_html_e( 'Last Modified', 'wpl-toolkit' ); ?></th>
				<th><?php esc_html_e( 'Status', 'wpl-toolkit' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'wpl-toolkit' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php if ( empty( $snippets ) ) : ?>
				<tr>
					<td colspan="4"><?php esc_html_e( 'No snippets found.', 'wpl-toolkit' ); ?></td>
			</tr>
					<?php else : ?>
						<?php foreach ( $snippets as $snippet ) : ?>
							<tr>
								<td><?php echo $snippet['name']; ?></td>
							<td><?php echo ceil( $snippet['size'] / 1024 ) . ' KB'; ?></td>
							<td><?php echo $snippet['modified']; ?></td>
							<td><?php echo $snippet['status']; ?></td>
							<td>
								<button type="button" class="button action-enable" data-file="<?php echo $snippet['name']; ?>"><?php echo esc_html__( 'Enable', 'wpl-toolkit' ); ?></button>
								<button type="button" class="button action-disable"
									data-file="<?php echo esc_attr( $snippet['name'] ); ?>"><?php echo esc_html__( 'Disable', 'wpl-toolkit' ); ?></button>
								</td>
								</tr>
								<?php endforeach; ?>
								<?php endif; ?>
								</tbody>
								</table>
</div>
<?php
