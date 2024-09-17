(function ($) {
    $(document).ready(function () {
        console.log('WPL Activation page loaded');

        // Handle Test API Key button click
        $('#test-api-key').on('click', function (e) {
            e.preventDefault();

            const apiKey = $('input[name="wpl_api_key"]').val();

            // Display loading message
            $('#test-api-key-result').html('<p>Testing API Key...</p>');

            // AJAX request to test the API key
            $.ajax({
                url: wplActivation.ajaxurl, // Correct the AJAX URL
                method: 'POST',
                data: {
                    action: 'wpl_test_api_key', // Correct action name
                    _nonce: wplActivation._nonce, // Correct nonce reference
                    api_key: apiKey,
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
    });
})(jQuery);
