(function ($) {
    $(document).ready(function () {
        console.log('WPL Admin Snippets page loaded');
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
        // $('.wpl-toggle-snippet-status').on('click', function (e) {
        //     e.preventDefault();
        //     console.log('Toggle snippet status');

        //     const button = $(this);
        //     const snippetId = button.data('snippet-id');
        //     const nonce = button.data('nonce');

        //     // Disable button during the request
        //     button.prop('disabled', true);

        //     // AJAX request to toggle snippet status
        //     $.ajax({
        //         url: ajaxurl,
        //         type: 'POST',
        //         data: {
        //             // _ajax_nonce: nonce,
        //             action: 'wpl_toolkit_enable_snippet',
        //             snippet_id: snippetId,
        //             security: nonce,
        //         },
        //         success: function (response) {
        //             if (response.success) {
        //                 // Update button text based on the new status
        //                 const newStatus = response.data.status;
        //                 button.text(newStatus === 'enabled' ? 'Disable' : 'Enable');
        //                 button.prop('disabled', false);

        //                 // Optional: Show a success message
        //                 $('#snippet-toggle-result').html(
        //                     `<div class="notice notice-success is-dismissible"><p>${response.data.message}</p></div>`
        //                 );
        //             } else {
        //                 // Handle errors and display the error message
        //                 $('#snippet-toggle-result').html(
        //                     `<div class="notice notice-error is-dismissible"><p>${response.data.message}</p></div>`
        //                 );
        //             }
        //         },
        //         error: function () {
        //             // Error handling in case of server issues
        //             $('#snippet-toggle-result').html(
        //                 '<div class="notice notice-error is-dismissible"><p>An unexpected error occurred.</p></div>'
        //             );
        //             button.prop('disabled', false);
        //         },
        //     });
        // });

        $('.button[data-file]').on('click', function () {
            var action = $(this).attr('class').split(' ')[1].replace('action-', '');
            var file = $(this).data('file');

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'wpl_' + action + '_snippet',
                    snippet: file,
                    _nonce: wplSnippets._nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data);
                        // location.reload();
                    } else {
                        alert(response.data);
                    }
                },
                error: function (error) {
                    console.log(error);
                    alert(error.statusText);
                }
            });
        });
    });
})(jQuery);
