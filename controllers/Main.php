<?php

    namespace thebuggenie\modules\slack\controllers;

    use thebuggenie\core\entities\Project;
    use thebuggenie\core\framework,
        Maknz\Slack\Client as SlackClient;

    /**
     * Main controller for the slack module
     */
    class Main extends framework\Action
    {

        /**
         * @return \thebuggenie\modules\slack\Slack
         * @throws \Exception
         */
        protected function _getModule()
        {
            return framework\Context::getModule('slack');
        }

        public function runConfigureProjectSettings(framework\Request $request)
        {
            $this->forward403unless($request->isPost());
            $project_key = $request['project_key'];
            $project = Project::getByKey($project_key);

            if ($project instanceof Project && $this->getUser()->canManageProject($project))
            {
                $project_id = $project->getID();
                $module = $this->_getModule();

                $module->setChannelName($project_id, $request[\thebuggenie\modules\slack\Slack::SETTING_PROJECT_CHANNEL_NAME]);
                $module->setPostAsName($project_id, $request[\thebuggenie\modules\slack\Slack::SETTING_PROJECT_POST_AS_NAME]);
                $module->setProjectIntegrationEnabled($project_id, $request[\thebuggenie\modules\slack\Slack::SETTING_PROJECT_INTEGRATION_ENABLED]);
                $module->doesPostOnNewIssues($project_id, $request[\thebuggenie\modules\slack\Slack::SETTING_PROJECT_POST_ON_NEW_ISSUES]);
                $module->doesPostOnNewReleases($project_id, $request[\thebuggenie\modules\slack\Slack::SETTING_PROJECT_POST_ON_NEW_RELEASES]);

                return $this->renderJSON(array('failed' => false, 'message' => framework\Context::getI18n()->__('Settings saved')));
            }
            else
            {
                $this->forward403();
            }
        }

        public function runConfigureSlackSettings(framework\Request $request)
        {
            try {
                if (isset($request['webhook_url'])) {
                    $url = $request['webhook_url'];
                    if (!$url) {
                        $this->_getModule()->deleteSetting('webhook_url');
                    } else {
                        $pieces = parse_url($url);
                        if (!isset($pieces['scheme']) || !isset($pieces['path']) || !isset($pieces['host']) || $pieces['host'] =! 'hooks.slack.com') {
                            $this->getResponse()->setHttpStatus(400);
                            return $this->renderJSON(['error' => $this->getI18n()->__("Sorry, that did not make sense"), 'webhook_url' => $pieces]);
                        }

                        $settings = [
                            'username' => 'The Bug Genie automatron-bot',
                            'channel' => '#general',
                            'icon' => 'http://thebuggenie.com/images/logo_32.png',
                            'link_names' => true
                        ];

                        $client = new SlackClient($url, $settings);
                        $this->_getModule()->setWebhookUrl($url);
                    }
                }
            } catch (GithubException $e) {
                if ($e->getCode() == 404) {
                    $this->getResponse()->setHttpStatus(400);
                    return $this->renderJSON(['error' => $this->getI18n()->__("That repository does not exist")]);
                } else {
                    $this->getResponse()->setHttpStatus(400);
                    return $this->renderJSON(['error' => $this->getI18n()->__("Woops, there was an error trying to connect to Github")]);
                }
            } catch (\Exception $e) {
                $this->getResponse()->setHttpStatus(400);
                return $this->renderJSON(['error' => $this->getI18n()->__("Woops, there was an error trying to connect to Github")]);
            }

            return $this->renderJSON([
                'webhook_url' => $url
            ]);
        }

    }

