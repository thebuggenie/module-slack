require(['domReady', 'thebuggenie/tbg', 'jquery'], function (domReady, tbgjs, $) {
    domReady(function () {
        var submitSetupStep = function(e) {
            var form_id        = 'slack_settings_form',
                $form          = $('#' + form_id),
                $indicator     = $('#' + form_id + '_indicator'),
                $submit_button = $('#' + form_id + '_button'),
                url            = $form.attr("action");

            $indicator.show();
            e.preventDefault();

            var submitStep = function () {
                return new Promise(function (resolve, reject) {
                    $.ajax({
                        type: 'POST',
                        data: $form.serialize(),
                        url: url,
                        success: resolve,
                        error: function (details) {
                            $indicator.hide();
                            $submit_button.attr('disabled', false);
                            reject(details);
                        }
                    });
                });
            };

            submitStep()
                .then(function (result) {
                    $indicator.hide();
                    $form.addClass('disabled');
                    $('#slack_address_container').addClass('verified');
                    $('#slack_webhook_url_input').attr('disabled', true);
                }, function (details) {
                    tbgjs.Main.Helpers.Message.error(details.responseJSON.error);
                });
        };

        $('#slack_settings_form').submit(submitSetupStep);

        $('#slack_settings_change_button').click(function (e) {
            e.preventDefault();

            $('#slack_settings_form').removeClass('disabled');
            $('#slack_address_container').removeClass('verified');
            $('#slack_webhook_url_input').attr('disabled', false);
        })
    });
});
