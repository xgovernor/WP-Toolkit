(function ($) {
    $(document).ready(function () {
        // Handle Test Connection button click
        $('#test-api-key').on('click', function (e) {
            e.preventDefault();

            const apiKey = $('input[name="wpl_api_key"]').val();
            const security = wplToolkit.security; // Nonce value

            // Display loading message
            $('#test-api-key-result').html('<p>Testing API Key...</p>');

            // AJAX request to test the API key
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'wpl_toolkit_test_api_key',
                    api_key: apiKey,
                    security: security,
                },
                success: function (response) {
                    if (response.success) {
                        $('#test-api-key-result').html('<p style="color: green;">' + response.data.message + '</p>');
                    } else {
                        $('#test-api-key-result').html('<p style="color: red;">' + response.data.message + '</p>');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#test-api-key-result').html('<p style="color: red;">' + (textStatus === 'timeout' ? 'Request timed out. Please try again.' : 'An unexpected error occurred: ' + errorThrown) + '</p>');
                },
            });

        });

         // Handle Snippet Download button click
        $('#download-snippet').on('click', function (e) {
            e.preventDefault();

            const snippetId = $('input[name="snippet_id"]').val();
            const security = wplToolkit.security; // Nonce value

            // Display loading message
            $('#snippet-download-result').html('<p>Downloading snippet...</p>');

            // AJAX request to download the snippet
            $.ajax({
                url: ajaxurl, // WordPress AJAX endpoint
                method: 'POST',
                data: {
                    action: 'wpl_toolkit_download_snippet',
                    snippet_id: snippetId,
                    security: security,
                },
                success: function (response) {
                    if (response.success) {
                        $('#snippet-download-result').html('<p style="color: green;">' + response.data.message + '</p>');
                    } else {
                        $('#snippet-download-result').html('<p style="color: red;">' + response.data.message + '</p>');
                    }
                },
                error: function () {
                    $('#snippet-download-result').html('<p style="color: red;">An error occurred. Please try again.</p>');
                },
            });
        });

        // Handle snippet status toggle
        $('.wpl-toggle-snippet-status').on('click', function (e) {
            e.preventDefault();

            const button = $(this);
            const snippetId = button.data('snippet-id');
            const nonce = button.data('nonce');

            // Disable button during the request
            button.prop('disabled', true);

            // AJAX request to toggle snippet status
            $.ajax({
                url: wplToolkit.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpl_toggle_snippet_status',
                    snippet_id: snippetId,
                    security: nonce,
                },
                success: function (response) {
                    if (response.success) {
                        // Update button text based on the new status
                        const newStatus = response.data.status;
                        button.text(newStatus === 'enabled' ? 'Disable' : 'Enable');
                        button.prop('disabled', false);

                        // Optional: Show a success message
                        $('#snippet-toggle-result').html(
                            `<div class="notice notice-success is-dismissible"><p>${response.data.message}</p></div>`
                        );
                    } else {
                        // Handle errors and display the error message
                        $('#snippet-toggle-result').html(
                            `<div class="notice notice-error is-dismissible"><p>${response.data.message}</p></div>`
                        );
                    }
                },
                error: function () {
                    // Error handling in case of server issues
                    $('#snippet-toggle-result').html(
                        '<div class="notice notice-error is-dismissible"><p>An unexpected error occurred.</p></div>'
                    );
                    button.prop('disabled', false);
                },
            });
        });
    });
})(jQuery);
