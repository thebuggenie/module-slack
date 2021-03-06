<div id="tab_slack_pane"<?php if ($selected_tab != 'slack'): ?> style="display: none;"<?php endif; ?>>
    <?php if ($access_level != \thebuggenie\core\framework\Settings::ACCESS_FULL): ?>
        <div class="rounded_box red" style="margin-top: 10px;">
            <?= __('You do not have the relevant permissions to access these settings'); ?>
        </div>
    <?php else: ?>
    <form action="<?= make_url('configure_slack_project_settings', array('project_key' => $project->getKey())); ?>" accept-charset="<?= \thebuggenie\core\framework\Context::getI18n()->getCharset(); ?>" action="<?= make_url('configure_slack_project_settings', array('project_key' => $project->getKey())); ?>" method="post" onsubmit="TBG.Main.Helpers.formSubmit('<?= make_url('configure_slack_project_settings', array('project_key' => $project->getKey())); ?>', 'slack_form');return false;" id="slack_form">
            <div class="project_save_container">
                <span id="slack_form_indicator" style="display: none;"><?= image_tag('spinning_20.gif'); ?></span>
                <input class="button button-silver" type="submit" id="slack_form_button" value="<?= __('Save settings'); ?>">
            </div>
            <div class="address-settings">
                <table class="padded_table" cellpadding=0 cellspacing=0>
                    <tr>
                        <td><label for="use_prefix"><?= __('Enable integration'); ?></label></td>
                        <td>
                            <?php if ($access_level == \thebuggenie\core\framework\Settings::ACCESS_FULL): ?>
                                <select name="<?= \thebuggenie\modules\slack\Slack::SETTING_PROJECT_INTEGRATION_ENABLED; ?>" id="slack_enable_integration" style="width: 70px;">
                                    <option value=1<?php if ($integration_enabled): ?> selected<?php endif; ?>><?= __('Yes'); ?></option>
                                    <option value=0<?php if (!$integration_enabled): ?> selected<?php endif; ?>><?= __('No'); ?></option>
                                </select>
                            <?php else: ?>
                                <?= ($integration_enabled) ? __('Yes') : __('No'); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="slack_project_channel_input"><?= __('Post to channel'); ?></label></td>
                        <td>
                            <?php if ($access_level == \thebuggenie\core\framework\Settings::ACCESS_FULL): ?>
                                <input type="text" name="<?= \thebuggenie\modules\slack\Slack::SETTING_PROJECT_CHANNEL_NAME; ?>" id="slack_project_channel_input" value="<?= $module->getChannelName($project->getID()); ?>" style="width: 200px;" placeholder="#general">
                            <?php else: ?>
                                <?= $module->getChannelName($project->getID()); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="config_explanation" colspan="2"><?= __('All messages will be posted to this channel'); ?></td>
                    </tr>
                    <tr>
                        <td><label for="slack_project_post_as_name"><?= __('Bot name'); ?></label></td>
                        <td>
                            <?php if ($access_level == \thebuggenie\core\framework\Settings::ACCESS_FULL): ?>
                                <input type="text" name="<?= \thebuggenie\modules\slack\Slack::SETTING_PROJECT_POST_AS_NAME; ?>" id="slack_project_post_as_name" value="<?= $module->getPostAsName($project->getID()); ?>" style="width: 50%;">
                            <?php else: ?>
                                <?= $module->getPostAsName($project->getID()); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="slack_project_post_as_logo"><?= __('Bot avatar'); ?></label></td>
                        <td>
                            <?php if ($access_level == \thebuggenie\core\framework\Settings::ACCESS_FULL): ?>
                                <select name="<?= \thebuggenie\modules\slack\Slack::SETTING_PROJECT_POST_AS_LOGO; ?>" id="slack_project_post_as_logo" style="width: 170px;">
                                    <option value='thebuggenie'<?php if ($module->getPostAsLogo($project->getID()) == 'thebuggenie'): ?> selected<?php endif; ?>><?= __('The Bug Genie logo'); ?></option>
                                    <option value='project'<?php if ($module->getPostAsLogo($project->getID()) == 'project'): ?> selected<?php endif; ?>><?= __('Project logo'); ?></option>
                                </select>
                            <?php else: ?>
                                <?= ($module->getPostAsLogo($project->getID())) ? __('The Bug Genie logo') : __('Project logo'); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="config_explanation" colspan="2"><?= __('The bot settings will be the name and avatar for the bot user that will post to your Slack channel'); ?></td>
                    </tr>
                    <tr>
                        <td><label for="slack_project_post_on_new_issues"><?= __('Post on new issues'); ?></label></td>
                        <td>
                            <?php if ($access_level == \thebuggenie\core\framework\Settings::ACCESS_FULL): ?>
                                <select name="<?= \thebuggenie\modules\slack\Slack::SETTING_PROJECT_POST_ON_NEW_ISSUES; ?>" id="slack_project_post_on_new_issues" style="width: 70px;">
                                    <option value=1<?php if ($module->doesPostOnNewIssues($project->getID())): ?> selected<?php endif; ?>><?= __('Yes'); ?></option>
                                    <option value=0<?php if (!$module->doesPostOnNewIssues($project->getID())): ?> selected<?php endif; ?>><?= __('No'); ?></option>
                                </select>
                            <?php else: ?>
                                <?= ($module->doesPostOnNewIssues($project->getID())) ? __('Yes') : __('No'); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="slack_project_post_on_new_releases"><?= __('Post on new releases'); ?></label></td>
                        <td>
                            <?php if ($access_level == \thebuggenie\core\framework\Settings::ACCESS_FULL): ?>
                                <select name="<?= \thebuggenie\modules\slack\Slack::SETTING_PROJECT_POST_ON_NEW_RELEASES; ?>" id="slack_project_post_on_new_releases" style="width: 70px;">
                                    <option value=1<?php if ($module->doesPostOnNewReleases($project->getID())): ?> selected<?php endif; ?>><?= __('Yes'); ?></option>
                                    <option value=0<?php if (!$module->doesPostOnNewReleases($project->getID())): ?> selected<?php endif; ?>><?= __('No'); ?></option>
                                </select>
                            <?php else: ?>
                                <?= ($module->doesPostOnNewReleases($project->getID())) ? __('Yes') : __('No'); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </form>
    <?php endif; ?>
</div>
