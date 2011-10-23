<?php

/**
 *	Router разбирает вохдной урл и подготавливает на его основе
 *	информацию о текущей странице (тэги, заголовок, разметку) для фронт-конроллера.
 */

abstract class cm_Controller_Router_Abstract extends cm_Controller_Abstract {
	/*
	 * запрашиваемая страница
	 * @var cm_Controller_Page
	 */
	private $_page;

	/**
	 * @var cm_Controller_Route[]
	 */
	protected $_routes = array();

	/**
	 * Объект-парсер структуры. С его помошью ищем нужную страницу. Реализация паттерна Strategy.
	 *
	 * @var cm_Controller_PageResolver
	 */
	protected $_pageResolver;
	/**
	 * @return void
	 */
	public function run() {
		/**
		 * Если на странице установлено
		 * перенаправление - делаем его
		 */
		$this->getPage()->tryRedirect();
		// TODO:
	}

	/**
	 * Возвращает страницу
	 * @return cm_Controller_Page
	 */
	final public function getPage() {
		if ($this->_page === null) {
			$this->_page = $this->getPageResolver()->getPage();
		}
		return $this->_page;
	}

	/**
	 * @param string $pageName
	 * @param string[] $variables
	 * @return string
	 * @throws cm_Controller_Route_Exception
	 */
	public function generateUrl($pageName, $variables = array()) {
		if (!isset($this->_routes[$pageName])) {
			throw new cm_Controller_Route_Exception("Page {$pageName} doesn't exists in routes config");
		}
		return $this->_routes[$pageName]->generateUrl($variables);
	}

	/*
	 * Возвращает полный конфиг (массив)
	 *
	 * @return array
	 */
	abstract protected function _getStructure();

	/**
	 * @param cm_Controller_Page $page
	 */
	final public function setPage(cm_Controller_Page $page) {
		$this->_page = $page;
	}

	/**
	 * @abstract
	 * @param array $config
	 * @return cm_Controller_Page
	 */
	public function createPage($config = null) {
		if ($config !== null) {
			$page = new cm_Controller_Page($config, $this->getRequest(), $this->getResponse());
			$page->setCode(200);
			return $page;
		}
		return $this->createPageByCode(404);
	}

	/**
	 * @abstract
	 * @param int $code
	 * @return cm_Controller_Page
	 */
	public function createPageByCode($code = 404) {
		foreach($this->getRoutes() as $route) {
			if ($code === (int) $route->getPageConfig('error_code')) {
				$page = new cm_Controller_Page($route->getPageConfig(), $this->getRequest(), $this->getResponse());
				$page->setCode($code);
				return $page;
			}
		}
		if ($code != 404) {
			return $this->createPageByCode(404);
		}
		throw new cm_Controller_Router_Exception('Page with code 404 not found');
	}

	/**
	 * @return cm_Controller_Route[]
	 */
	public function getRoutes() {
		$structure = $this->_getStructure();
		return array();
	}

	/**
	 * @param cm_Controller_PageResolver $pageResolver
	 * @return void
	 */
	public function setPageResolver(cm_Controller_PageResolver $pageResolver) {
		$this->_pageResolver = $pageResolver;
		$this->_pageResolver->setRouter($this);
	}

	/**
	 * @return cm_Controller_Router_XML_PageResolver_Abstract
	 */
	public function getPageResolver() {
		if ($this->_pageResolver === null) {
			$this->setPageResolver(new cm_Controller_PageResolver());
		}
		return $this->_pageResolver;
	}
}