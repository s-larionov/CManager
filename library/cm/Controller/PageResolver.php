<?php

// @todo: не проверял еще

class cm_Controller_PageResolver {
	/**
	 * @var cm_Controller_Router_Abstract
	 */
	private $_router = null;

	/**
	 * @param cm_Controller_Router_Abstract $router
	 */
	public final function setRouter(cm_Controller_Router_Abstract $router) {
		$this->_router = $router;
	}

	/**
	 * @return cm_Controller_Router_Abstract
	 */
	public final function getRouter() {
		return $this->_router;
	}

	/**
	 * @return cm_Controller_Page
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

		return $this->getRouter()->createPageByCode(404);
	}
}
