<?php

/**
 * @file plugins/generic/VOJSgoogleAnalytics/VOJSGoogleAnalyticsPlugin.inc.php
 *
 * @class VOJSGoogleAnalyticsPlugin
 * @ingroup plugins_generic_VOJSgoogleAnalytics
 *
 * @brief VOJS Google Analytics plugin class
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class VOJSGoogleAnalyticsPlugin extends GenericPlugin {
	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path, $mainContextId = null) {
		$success = parent::register($category, $path, $mainContextId);
		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return true;
		if ($success && $this->getEnabled($mainContextId)) {
			// Insert Google Analytics page tag to footer
			HookRegistry::register('TemplateManager::display', array($this, 'registerScript'));
		}
		return $success;
	}

	/**
	 * @copydoc Plugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.generic.VOJSgoogleAnalytics.displayName');
	}

	/**
	 * @copydoc Plugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.VOJSgoogleAnalytics.description');
	}

    /**
     * @copydoc Plugin::getActions()
     */
    function getActions($request, $verb) {
        $router = $request->getRouter();
        import('lib.pkp.classes.linkAction.request.AjaxModal');
        return array_merge(
            $this->getEnabled()?array(
                new LinkAction(
                    'settings',
                    new AjaxModal(
                        $router->url($request, null, null, 'manage', null, array('verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'generic')),
                        $this->getDisplayName()
                    ),
                    __('manager.plugins.settings'),
                    null
                ),
            ):array(),
            parent::getActions($request, $verb)
        );
    }

 	/**
	 * @copydoc Plugin::manage()
	 */
	function manage($args, $request) {
		switch ($request->getUserVar('verb')) {
			case 'settings':
				$context = $request->getContext();

				AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON,  LOCALE_COMPONENT_PKP_MANAGER);
				$templateMgr = TemplateManager::getManager($request);
				$templateMgr->registerPlugin('function', 'plugin_url', array($this, 'smartyPluginUrl'));

				$this->import('VOJSGoogleAnalyticsSettingsForm');
				$form = new VOJSGoogleAnalyticsSettingsForm($this, $context->getId());

				if ($request->getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						return new JSONMessage(true);
					}
				} else {
					$form->initData();
				}
				return new JSONMessage(true, $form->fetch($request));
		}
		return parent::manage($args, $request);
	}

	/**
	 * Register the Google Analytics script tag
	 * @param $hookName string
	 * @param $params array
	 */
	function registerScript($hookName, $params) {
		$request = Application::get()->getRequest();
		$context = $request->getContext();
		if (!$context) return false;
		$router = $request->getRouter();
		if (!is_a($router, 'PKPPageRouter')) return false;

		$googleAnalyticsSiteId = $this->getSetting($context->getId(), 'googleAnalyticsSiteId');
		if (empty($googleAnalyticsSiteId)) return false;

		$googleAnalyticsCode = "<script async src=\"https://www.googletagmanager.com/gtag/js?id=$googleAnalyticsSiteId\"></script>
            <script>
                    window.dataLayer = window.dataLayer || [];
              function gtag(){dataLayer.push(arguments);}
              gtag('js', new Date());

              gtag('config', '$googleAnalyticsSiteId');
            </script>";

		$templateMgr = TemplateManager::getManager($request);
        $templateMgr->addHeader(
            'googleanalytics',
            $googleAnalyticsCode,
            array(
                'priority' => STYLE_SEQUENCE_CORE,
            )
        );

		return false;
	}
}

