<?php

    namespace thebuggenie\modules\slack;

    use thebuggenie\core\framework;

    /**
     * actions for the slack module
     */
    class Components extends framework\ActionComponent
    {

        /**
         * @return \thebuggenie\modules\slack\Slack
         * @throws \Exception
         */
        protected function _getModule()
        {
            return framework\Context::getModule('slack');
        }

        public function componentSettings()
        {
            $this->webhook_url = $this->_getModule()->getWebhookUrl();
        }

        public function componentProjectconfig_panel()
        {
            $this->integration_enabled = $this->module->isProjectIntegrationEnabled($this->project->getID());
        }

    }

