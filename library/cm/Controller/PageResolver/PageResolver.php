<?php

abstract class cm_Controller_PageResolver_Abstract {
	/**
	 * @var cm_Controller_Router_Abstract
	 */
	private $_router;

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

	public final function getStructure() {
		return $this->getRouter()->getStructure();
	}

	abstract public function getPage();
}
