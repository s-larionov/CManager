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
	 * @return cm_Controller_Route[]
	 */
	public final function getRoutes() {
		return $this->getRouter()->getRoutes();
	}

	public function getPage() {
		$this->getRouter()->getRoutes();
		// @todo
	}
}
