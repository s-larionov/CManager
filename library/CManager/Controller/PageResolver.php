<?php

// @todo: не проверял еще

class CManager_Controller_PageResolver {
	/**
	 * @var CManager_Controller_Router_Abstract
	 */
	private $_router = null;

	/**
	 * @param CManager_Controller_Router_Abstract $router
	 */
	public final function setRouter(CManager_Controller_Router_Abstract $router) {
		$this->_router = $router;
	}

	/**
	 * @return CManager_Controller_Router_Abstract
	 */
	public final function getRouter() {
		return $this->_router;
	}

	/**
	 * @return CManager_Controller_Page
	 */
	public function getPage() {
		$routes	= $this->getRouter()->getRoutes();
		$request= $this->getRouter()->getRequest();

		$path = $request->getPath();
		foreach($routes as $pageName => $route) {
			$variables = $route->parse($path);
			if ($variables !== false) {
				return $this->getRouter()->createPage($pageName, $variables);
			}
		}

		CManager_Timer::start('application->run router->find start page');
		if ($path == '/') {
			foreach($routes as $pageName => $route) {
				if ($route->getPageConfig()->start === true) {
					$this->getRouter()->getResponse()
							->setRedirect($route->generateUrl(), 301)
							->sendResponse();
					exit;
				}
			}
		}
		CManager_Timer::end('application->run router->find start page');

		CManager_Timer::start('application->run router->create 404 page');
		$page = $this->getRouter()->createPageByCode(404);
		CManager_Timer::end('application->run router->create 404 page');

		return $page;
	}
}
