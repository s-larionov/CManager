<?php

/**
 *	Router разбирает вохдной урл и подготавливает на его основе
 *	информацию о текущей странице (тэги, заголовок, разметку) для фронт-конроллера.
 */

abstract class CManager_Controller_Router_Abstract extends CManager_Controller_Abstract {
	
	const DEFAULT_CLASS_PAGE = 'CManager_Controller_Page';
	
	/*
	 * запрашиваемая страница
	 * @var CManager_Controller_Page
	 */
	private $_page;

	/**
	 * @var CManager_Controller_Route[]
	 */
	protected $_routes = null;

	/**
	 * Объект-парсер структуры. С его помошью ищем нужную страницу. Реализация паттерна Strategy.
	 *
	 * @var CManager_Controller_PageResolver
	 */
	protected $_pageResolver = null;

	/**
	 * @var CManager_Controller_Router_Config_Structure
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
	}

	/**
	 * Возвращает страницу
	 * @return CManager_Controller_Page
	 */
	final public function getPage() {
		if ($this->_page === null) {
			try {
				$this->_page = $this->getPageResolver()->getPage();
			} catch (CManager_Controller_Page_404Exception $e) {
				$this->_page = $this->createPageByCode(404);
			}
		}
		return $this->_page;
	}

	/**
	 * @param string $pageName
	 * @param string[] $variables
	 * @param boolean $addQueryParams
	 * @return string
	 * @throws CManager_Controller_Route_Exception
	 */
	public function generateUrl($pageName, array $variables = array(), $addQueryParams = true) {
		if (!isset($this->_routes[$pageName])) {
			$url = '#' . $pageName;
			foreach($variables as $name => &$value) {
				$value = urlencode($name) . ($value !== ''? '=' . urlencode($value): '');
			}
			if (count($variables) > 0) {
				$url .= '?' . implode('&amp;', $variables);
			}
			return $url;
		}
		return $this->_routes[$pageName]->generateUrl($variables, $addQueryParams);
	}

	/*
	 * Возвращает полный конфиг (массив)
	 *
	 * @return CManager_Controller_Router_Config_Abstract
	 */
	abstract protected function _getStructure();

	/**
	 * @return CManager_Controller_Router_Config_Structure
	 */
	final public function getStructure() {
		if ($this->_structure === null) {
			$this->_structure = $this->_getStructure();
		}
		return $this->_structure;
	}

	/**
	 * @param CManager_Controller_Router_Config_Structure $structure
	 * @return CManager_Controller_Router_Abstract
	 */
	final public function setStructure(CManager_Controller_Router_Config_Structure $structure) {
		$this->_structure = $structure;
		return $this;
	}

	/**
	 * @param CManager_Controller_Page $page
	 */
	final public function setPage(CManager_Controller_Page $page) {
		$this->_page = $page;
	}

	/**
	 * @return array
	 */
	final public function __sleep() {
		return array('_routes', '_structure');
	}

	/**
	 * @abstract
	 * @param string $pageName
	 * @param array $variables
	 * @return CManager_Controller_Page
	 */
	public function createPage($pageName, array $variables = array()) {
		$routes = $this->getRoutes();
		if (isset($routes[$pageName])) {
			$pageConfig = $routes[$pageName]->getPageConfig();
			$classPage = $pageConfig->namespace !== null? $pageConfig->namespace: self::DEFAULT_CLASS_PAGE;

			$page = /** @var CManager_Controller_Page $page */ CManager_Helper_Object::newInstance(
					$classPage,
					self::DEFAULT_CLASS_PAGE,
					array($pageConfig, $this->getRequest(), $this->getResponse()));

			$page->setRoute($routes[$pageName])
				->setVariables($variables)
				->init();
			return $page;
		}
		return $this->createPageByCode(404);
	}

	/**
	 * @abstract
	 * @param int $code
	 * @param array $variables
	 * @return CManager_Controller_Page
	 */
	public function createPageByCode($code = 404, array $variables = array()) {
		foreach($this->getRoutes() as $pageName => $route) {
			if ($code === (int) $route->getPageConfig()->error_code) {
				$page = $this->createPage($pageName, $variables);
				return $page;
			}
		}
		if ($code != 404) {
			return $this->createPageByCode(404);
		}
		throw new CManager_Controller_Router_Exception('Page with code 404 not found');
	}

	/**
	 * @param string|null $name
	 * @return CManager_Controller_Route[]|CManager_Controller_Route
	 */
	public function getRoutes($name = null) {
		if ($this->_routes === null) {
			$structure = $this->getStructure();
			if (!$structure->page || !is_array($structure->page)) {
				throw new CManager_Controller_Router_Exception("Wrong router config data");
			}
			$this->_routes = $this->_generateRoutes($structure->page);
		}
		if ($name !== null) {
			if (array_key_exists($name, $this->_routes)) {
				return $this->_routes[$name];
			}
			return null;
		}
		return $this->_routes;
	}

	/**
	 * @param CManager_Controller_Router_Config_Page[] $pages
	 * @param CManager_Controller_Route $parentRoute
	 * @return array
	 */
	protected function _generateRoutes(array $pages, CManager_Controller_Route $parentRoute = null) {
		$routes = array();
		foreach($pages as $page) {
			if (array_key_exists($page->name, $routes)) {
				throw new CManager_Controller_Router_Exception("Page '{$page->name}' already exists in router");
			}
			$routes[$page->name] = $this->_createRoute($page, $parentRoute);
			if (is_array($page->page)) {
				$routes = array_merge($routes, $this->_generateRoutes($page->page, $routes[$page->name]));
			}
		}

		return $routes;
	}

	/**
	 * @param CManager_Controller_Router_Config_Page $page
	 * @param CManager_Controller_Route $parentRoute
	 * @return CManager_Controller_Route
	 */
	protected function _createRoute(CManager_Controller_Router_Config_Page $page, CManager_Controller_Route $parentRoute = null) {
		$route = new CManager_Controller_Route($page->route->url, $page->route->var);
		if ($parentRoute !== null) {
			$route->setParent($parentRoute);
		}

		$route->setPageConfig($page);
		$route->setRouter($this);

		return $route;
	}

	/**
	 * @param CManager_Controller_PageResolver $pageResolver
	 * @return void
	 */
	public function setPageResolver(CManager_Controller_PageResolver $pageResolver) {
		$this->_pageResolver = $pageResolver;
		$this->_pageResolver->setRouter($this);
	}

	/**
	 * @return CManager_Controller_PageResolver
	 */
	public function getPageResolver() {
		if ($this->_pageResolver === null) {
			$this->setPageResolver(new CManager_Controller_PageResolver());
		}
		return $this->_pageResolver;
	}
}