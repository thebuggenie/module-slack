<?php

    namespace thebuggenie\modules\slack;

    use thebuggenie\core\entities\Milestone;
    use thebuggenie\core\entities\Project;
    use thebuggenie\core\framework,
        Maknz\Slack\Client as SlackClient;

    /**
     * Slack module for integrating with Slack
     *
     * @author
     * @version 1.0
     * @license http://opensource.org/licenses/MPL-2.0 Mozilla Public License 2.0 (MPL 2.0)
     * @package slack
     * @subpackage core
     */

    /**
     * Slack module for integrating with Slack
     *
     * @package slack
     * @subpackage core
     *
     * @Table(name="\thebuggenie\core\entities\tables\Modules")
     */
    class Slack extends \thebuggenie\core\entities\Module
    {

        const VERSION = '1.0';

        const SETTING_WEBHOOK_URL = 'webhook_url';
        const SETTING_PROJECT_INTEGRATION_ENABLED = 'project_integration_enabled_';
        const SETTING_PROJECT_CHANNEL_NAME = 'project_post_to_channel_';
        const SETTING_PROJECT_POST_AS_NAME = 'project_post_to_channel_as_name_';
        const SETTING_PROJECT_POST_AS_LOGO = 'project_post_to_channel_as_logo_';
        const SETTING_PROJECT_POST_ON_NEW_ISSUES = 'project_post_to_channel_on_new_issues_';
        const SETTING_PROJECT_POST_ON_NEW_RELEASES = 'project_post_to_channel_on_new_releases_';

        protected $_has_config_settings = true;
        protected $_name = 'slack';
        protected $_longname = 'Slack integration';
        protected $_description = 'Slack description here';
        protected $_module_config_title = 'Slack integration';
        protected $_module_config_description = 'Configure the Slack integration';

        protected $_slack_config = [];

        /**
         * Return an instance of this module
         *
         * @return Slack
         */
        public static function getModule()
        {
            return framework\Context::getModule('slack');
        }

        protected function _initialize()
        {
            require THEBUGGENIE_MODULES_PATH . 'slack' . DS . 'vendor' . DS . 'autoload.php';
            $this->_slack_config = [
                'username' => '',
                'channel' => '',
                'icon' => 'http://thebuggenie.com/images/logo_32.png',
                'link_names' => true
            ];
        }

        protected function _getSettings(Project $project)
        {
            $project_id = $project->getID();
            $settings = $this->_slack_config;

            $settings['channel'] = $this->getChannelName($project_id);
            $settings['username'] = $this->getPostAsName($project_id);

            return $settings;
        }

        /**
         * @param Project $project
         * @return SlackClient
         */
        protected function _getProjectClient(Project $project)
        {
            $settings = $this->_getSettings($project);
            $client = new SlackClient($this->getWebhookUrl(), $settings);

            return $client;
        }

        public function listen_issueCreate(framework\Event $event)
        {
            framework\Context::loadLibrary('common');
            $issue = $event->getSubject();
            $project_id = $issue->getProjectID();
            if ($this->isProjectIntegrationEnabled($project_id) && $this->doesPostOnNewIssues($project_id))
            {
                $client = $this->_getProjectClient($issue->getProject());
                $text = \tbg_truncateText($issue->getDescription());
                $client
                    ->to($this->getChannelName($project_id))
                    ->enableMarkdown()
                    ->attach([
                        'fallback' => 'New issue posted',
                        'text' => $text,
                        'title' => $issue->getFormattedIssueNo(true, true) . ' - ' . $issue->getTitle(),
                        'title_link' => framework\Context::getRouting()->generate('viewissue', ['project_key' => $issue->getProject()->getKey(), 'issue_no' => $issue->getFormattedIssueNo()], false),
                        'color' => 'good',
                    ])
                    ->send('['.$issue->getProject()->getKey().'] A new issue was posted by @'.$issue->getPostedBy()->getUsername());
            }
        }

        public function listen_buildSave(framework\Event $event)
        {
            framework\Context::loadLibrary('common');
            $release = $event->getSubject();
            $project_id = $release->getProject()->getID();
            if ($this->isProjectIntegrationEnabled($project_id) && $this->doesPostOnNewReleases($project_id))
            {
                $client = $this->_getProjectClient($release->getProject());
                $fields = [
                    [
                        'title' => 'Version number',
                        'value' => $release->getVersion(),
                        'short' => true
                    ]
                ];
                if ($release->isReleased()) {
                    $fields[] = [
                        'title' => 'Release date',
                        'value' => tbg_formatTime($release->getReleaseDate(), 20),
                        'short' => true
                    ];
                }
                if ($release->getMilestone() instanceof Milestone) {
                    $fields[] = [
                        'title' => 'Milestone',
                        'value' => $release->getMilestone()->getName(),
                        'short' => true
                    ];
                }
                $client
                    ->to($this->getChannelName($project_id))
                    ->enableMarkdown()
                    ->attach([
                        'fallback' => 'New release',
                        'title' => $release->getName(),
                        'title_link' => framework\Context::getRouting()->generate('project_releases', ['project_key' => $release->getProject()->getKey()], false),
                        'color' => '#77A',
                        'fields' => $fields
                    ])
                    ->send('['.$release->getProject()->getKey().'] A new release is available');
            }
        }

        protected function _addListeners()
        {
            framework\Event::listen('core', 'thebuggenie\core\entities\Issue::createNew', array($this, 'listen_issueCreate'));
            framework\Event::listen('core', 'thebuggenie\core\entities\Build::_postSave', array($this, 'listen_buildSave'));
            framework\Event::listen('core', 'config_project_tabs_other', array($this, 'listen_projectconfig_tab'));
            framework\Event::listen('core', 'config_project_panes', array($this, 'listen_projectconfig_panel'));
        }

        public function listen_projectconfig_tab(framework\Event $event)
        {
            include_component('slack/projectconfig_tab', array('selected_tab' => $event->getParameter('selected_tab'), 'module' => $this));
        }

        public function listen_projectconfig_panel(framework\Event $event)
        {
            include_component('slack/projectconfig_panel', array('selected_tab' => $event->getParameter('selected_tab'), 'access_level' => $event->getParameter('access_level'), 'project' => $event->getParameter('project'), 'module' => $this));
        }

        protected function _install($scope)
        {
        }

        protected function _loadFixtures($scope)
        {
        }

        protected function _uninstall()
        {
        }

        public function getWebhookUrl()
        {
            return $this->getSetting(self::SETTING_WEBHOOK_URL);
        }

        public function setWebhookUrl($value)
        {
            return $this->saveSetting(self::SETTING_WEBHOOK_URL, $value);
        }

        public function isProjectIntegrationEnabled($project_id)
        {
            return (bool) $this->getSetting(self::SETTING_PROJECT_INTEGRATION_ENABLED . $project_id);
        }

        public function setProjectIntegrationEnabled($project_id, $value)
        {
            return $this->saveSetting(self::SETTING_PROJECT_INTEGRATION_ENABLED . $project_id, $value);
        }

        public function getChannelName($project_id)
        {
            return $this->getSetting(self::SETTING_PROJECT_CHANNEL_NAME . $project_id);
        }

        public function setChannelName($project_id, $channel_name)
        {
            return $this->saveSetting(self::SETTING_PROJECT_CHANNEL_NAME . $project_id, $channel_name);
        }

        public function getPostAsName($project_id)
        {
            $setting = $this->getSetting(self::SETTING_PROJECT_POST_AS_NAME . $project_id);
            return $setting ?: 'TBG Autobot';
        }

        public function setPostAsName($project_id, $name)
        {
            return $this->saveSetting(self::SETTING_PROJECT_POST_AS_NAME . $project_id, $name);
        }

        public function getPostAsLogo($project_id)
        {
            $setting = $this->getSetting(self::SETTING_PROJECT_POST_AS_LOGO . $project_id);
            return $setting ?: 'thebuggenie';
        }

        public function setPostAsLogo($project_id, $key)
        {
            return $this->saveSetting(self::SETTING_PROJECT_POST_AS_LOGO . $project_id, $key);
        }

        public function doesPostOnNewIssues($project_id, $value = null)
        {
            if ($value !== null) {
                return $this->saveSetting(self::SETTING_PROJECT_POST_ON_NEW_ISSUES . $project_id, (bool) $value);
            } else {
                $setting = $this->getSetting(self::SETTING_PROJECT_POST_ON_NEW_ISSUES . $project_id);
                return (isset($setting)) ? $setting : true;
            }
        }

        public function doesPostOnNewReleases($project_id, $value = null)
        {
            if ($value !== null) {
                return $this->saveSetting(self::SETTING_PROJECT_POST_ON_NEW_RELEASES . $project_id, (bool) $value);
            } else {
                $setting = $this->getSetting(self::SETTING_PROJECT_POST_ON_NEW_RELEASES . $project_id);
                return (isset($setting)) ? $setting : true;
            }
        }

    }
