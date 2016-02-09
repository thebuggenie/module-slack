<style>
    .address-container:before {
        background-image: url('<?= image_url('cfg_icon_slack_padded.png', false, 'slack'); ?>');
    }
</style>
<div class="address-settings">
    <p><?= __('The Bug Genie can integrate with %slack_icon Slack (%link_to_slack) to notify about events such as new issues, releases and more. These integrations are configured per-project, but we need the url for the incoming webhook to be able to talk to Slack.', ['%slack_icon' => image_tag('icon_slack.png', ['style' => 'display: inline-block; width: 16px; vertical-align: middle; margin-left: 3px;'], false, 'slack'), '%link_to_slack' => '<a href="http://slack.com">http://slack.com</a>']); ?></p>
    <p><?= __('To communicate with Slack, you need to create a new incoming webhook for your team. To do this, go to this page: %link_to_new_webhook, create a new webhook using default settings and a channel you choose (will be overriden per project), then paste the webhook url below.', ['%link_to_new_webhook' => link_tag('https://my.slack.com/services/new/incoming-webhook', null, ['target' => '_blank'])]); ?></p>
    <form action="<?= make_url('configure_slack_settings'); ?>" accept-charset="<?= \thebuggenie\core\framework\Context::getI18n()->getCharset(); ?>" action="<?= make_url('configure_slack_settings'); ?>" method="post" onsubmit="return false;" id="slack_settings_form" class="<?php if ($webhook_url) echo 'disabled'; ?>">
        <div class="address-container<?php if ($webhook_url) echo ' verified'; ?>" id="slack_address_container">
            <img class="verified" src="<?= image_url('icon_ok.png'); ?>">
            <input type="text" id="slack_webhook_url_input" value="<?= $webhook_url; ?>" name="webhook_url" <?php if ($webhook_url) echo 'disabled'; ?> placeholder="https://hooks.slack.com/services/[...]">
        </div>
        <input type="submit" id="slack_form_button" class="button" value="<?= __('Next'); ?>">
        <a href="#" id="slack_settings_change_button" class="button button-silver change-button"><?= __('Change'); ?></a>
        <span id="slack_form_indicator" style="display: none;" class="indicator"><?= image_tag('spinning_20.gif'); ?></span>
    </form>
</div>
