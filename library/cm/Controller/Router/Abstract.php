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
	protected $_routes = null;

	/**
	 * Объект-парсер структуры. С его помошью ищем нужную страницу. Реализация паттерна Strategy.
	 *
	 * @var cm_Controller_PageResolver
	 */
	protected $_pageResolver;

	/**
	 * @var array
	 */
	protected $_structure = null;

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
	 * @return array
	 */
	final public function getStructure() {
		if ($this->_structure === null) {
			$this->_structure = $this->_getStructure();
		}
		return $this->_structure;
	}

	/**
	 * @param array $structure
	 * @return cm_Controller_Router_Abstract
	 */
	final public function setStructure(array $structure) {
		$this->_structure = $structure;
		return $this;
	}

	/**
	 * @param cm_Controller_Page $page
	 */
	final public function setPage(cm_Controller_Page $page) {
		$this->_page = $page;
	}

	final public function __sleep() {
		return array('_structure');
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
		if ($this->_routes === null) {
			$structure = $this->_getStructure();
			if (!isset($structure['page'])) {
				throw new cm_Controller_Router_Exception("Wrong router config data");
			}

			var_dump($structure);

			$this->_routes = $this->_generateRoutes($structure);
		}
		var_dump($this->_routes);
		return $this->_routes;
	}

	/**
	 * @param array $structure
	 * @return array
	 */
	protected function _generateRoutes(array $structure, cm_Controller_Route $parentRoute = null) {
		if (!is_array($structure['page']) || !array_key_exists(0, $structure['page'])) {
			$structure['page'] = array($structure['page']);
		}
		$routes = array();
		foreach($structure['page'] as $pageConfig) {
			if (!isset($pageConfig['name'])) {
				throw new cm_Controller_Router_Exception("Attribute @name is required for page configuration");
			}
			$routes[$pageConfig['name']] = $this->_createRoute($pageConfig, $parentRoute);
			if (isset($pageConfig['page'])) {
				$routes = array_merge($routes, $this->_generateRoutes($pageConfig, $routes[$pageConfig['name']]));
			}
		}

		return $routes;
	}

	/**
	 * @param array $pageConfig
	 * @param cm_Controller_Route $parentRoute
	 * @return cm_Controller_Route
	 */
	protected function _createRoute(array $pageConfig, cm_Controller_Route $parentRoute = null) {
		if (!isset($pageConfig['route'])) {
			throw new cm_Controller_Router_Exception("Parameter route required");
		}
		if (!is_array($pageConfig['route']) || !isset($pageConfig['route']['url'])) {
			throw new cm_Controller_Router_Exception("Parameter route must have attribute @url");
		}
		$route = $pageConfig['route']['url'];

		if (isset($pageConfig['route']['var'])) {
			$vars = $pageConfig['route']['var'];
			if (!is_array($vars)) {
				$vars = array($vars);
			}
		} else {
			$vars = array();
		}

		$config = array();
		foreach($config as $param => $value) {
			if ($param == 'page' || $param == 'route') {
				continue;
			}
			$config[$param] = $value;
		}

		$route = new cm_Controller_Route($route, $vars, $config);
		if ($parentRoute !== null) {
			$route->setParent($parentRoute);
		}
		return $route;
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