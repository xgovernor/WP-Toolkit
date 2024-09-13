<?php
$snippets = get_option('wpl_snippets', []);
?>
<div class="wrap" style="max-width: 800px; margin: 0 auto; padding-top: 1px">
    <h2><?php esc_html_e('Manage Snippets', 'wpl-toolkit'); ?></h2>
    <p><?php esc_html_e('Manage your snippets here.', 'wpl-toolkit'); ?></p>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Snippet Title', 'wpl-toolkit'); ?></th>
                <th><?php _e('Status', 'wpl-toolkit'); ?></th>
                <th><?php _e('Actions', 'wpl-toolkit'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($snippets as $id => $snippet): ?>
                <tr>
                    <td><?php echo esc_html($snippet['title']); ?></td>
                    <td><?php echo esc_html(ucfirst($snippet['status'])); ?></td>
                    <td>
                        <button
                            class="button wpl-toggle-snippet-status"
                            data-snippet-id="<?php echo esc_attr($id); ?>"
                            data-nonce="<?php echo wp_create_nonce('wpl_toggle_snippet_status'); ?>">
                            <?php echo $snippet['status'] === 'enabled' ? __('Disable', 'wpl-toolkit') : __('Enable', 'wpl-toolkit'); ?>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div id="snippet-toggle-result"></div>
</div>
<?php
