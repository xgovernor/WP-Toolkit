<?php
    $upload_dir = wp_upload_dir();
    $dir = $upload_dir['basedir'] . '/wpl-toolkit';

    // Ensure the directory exists
    if (!is_dir($dir)) {
        echo '<div class="notice notice-error"><p>' . esc_html__('The snippets directory does not exist.', 'wpl-toolkit') . '</p></div>';
        return;
    }

    // Get list of files in the directory
    $files = array_diff(scandir($dir), array('.', '..'));

    // Initialize an empty array to store the snippets
    $snippets = array();

    foreach ($files as $file) {
    // Assuming each file is a snippet and you want to display its name and additional details
    $file_path = $dir . '/' . $file;
    if (is_file($file_path)) {
        // Add the snippet file to the array with appropriate details
        // You can modify the details based on your needs
        $snippets[] = array(
            'name' => esc_html($file),
            'file' => esc_html($file),
            'status' => '<span class="status-enabled">' . esc_html__('Enabled', 'wpl-toolkit') . '</span>', // Placeholder status
            'actions' => '<button type="button" class="button action-enable" data-file="' . esc_attr($file) . '">' . esc_html__('Enable', 'wpl-toolkit') . '</button>
                          <button type="button" class="button action-disable" data-file="' . esc_attr($file) . '">' . esc_html__('Disable', 'wpl-toolkit') . '</button>'
        );
    }
}
?>
<div class="wrap" style="max-width: 800px; margin: 0 auto; padding-top: 1px">
    <h2><?php esc_html_e('Manage Snippets', 'wpl-toolkit'); ?></h2>
    <p><?php esc_html_e('Manage your snippets here.', 'wpl-toolkit'); ?></p>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e('Snippet Name', 'wpl-toolkit'); ?></th>
                <th><?php esc_html_e('File', 'wpl-toolkit'); ?></th>
                <th><?php esc_html_e('Status', 'wpl-toolkit'); ?></th>
                <th><?php esc_html_e('Actions', 'wpl-toolkit'); ?></th>
            </tr>
        </thead>

        <tbody>
            <?php if (empty($snippets)) : ?>
                <tr>
                    <td colspan="4"><?php esc_html_e('No snippets found.', 'wpl-toolkit'); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ($snippets as $snippet) : ?>
                    <tr>
                        <td><?php echo $snippet['name']; ?></td>
                        <td><?php echo $snippet['file']; ?></td>
                        <td><?php echo $snippet['status']; ?></td>
                        <td><?php echo $snippet['actions']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div id="snippet-toggle-result"></div>

    <script>
        jQuery(document).ready(function($) {
            // Handle action buttons
            $('.button[data-file]').on('click', function() {
                var action = $(this).attr('class').split(' ')[1].replace('action-', '');
                var file = $(this).data('file');

                $.ajax({
                    url: wplToolkit.ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'wpl_toolkit_' + action + '_snippet',
                        snippet: file,
                        security: wplToolkit.security
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data);
                            location.reload();
                        } else {
                            alert(response.data);
                        }
                    },
                    error: function() {
                        alert('An error occurred.');
                    }
                });
            });
        });
    </script>
</div>
<?php
