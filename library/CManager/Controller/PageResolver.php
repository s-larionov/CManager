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
				$page = $this->getRouter()->createPage($pageName, $variables);
				return $page;
			}
		}

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

		return $this->getRouter()->createPageByCode(404);
	}
}
