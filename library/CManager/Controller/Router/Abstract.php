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
	private $page;

	/**
	 * @var CManager_Controller_Route[]
	 */
	protected $routes = null;

	/**
	 * Объект-парсер структуры. С его помошью ищем нужную страницу. Реализация паттерна Strategy.
	 *
	 * @var CManager_Controller_PageResolver
	 */
	protected $pageResolver = null;

	/**
	 * @var CManager_Controller_Router_Config_Routes
	 */
	protected $structure = null;

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
		if ($this->page === null) {
			try {
				$this->setPage($this->getPageResolver()->getPage());
			} catch (CManager_Controller_Page_404Exception $e) {
				$this->setPage($this->createPageByCode(404));
			}
		}
		return $this->page;
	}

	/**
	 * @param string $pageName
	 * @param string[] $variables
	 * @param boolean $addQueryParams
	 * @return string
	 * @throws CManager_Controller_Route_Exception
	 */
	public function generateUrl($pageName, array $variables = array(), $addQueryParams = true) {
		// если искомого роута не существует, то возвращаем ссылку-заглушку в виде хеша (#pagename?p1=1&amp;p2=2)
		if (!isset($this->routes[$pageName])) {
			$url = '#' . $pageName;
			foreach($variables as $name => &$value) {
				$value = urlencode($name) . ($value !== ''? '=' . urlencode($value): '');
			}
			if (count($variables) > 0) {
				$url .= '?' . implode('&amp;', $variables);
			}
			return $url;
		}
		return $this->routes[$pageName]->generateUrl($variables, $addQueryParams);
	}

	/*
	 * Возвращает полный конфиг (массив)
	 *
	 * @return CManager_Controller_Router_Config_Routes
	 */
	abstract protected function _getStructure();

	/**
	 * @return CManager_Controller_Router_Config_Routes
	 */
	final public function getStructure() {
		if ($this->structure === null) {
			$this->structure = $this->_getStructure();
		}
		return $this->structure;
	}

	/**
	 * @param CManager_Controller_Router_Config_Routes $structure
	 * @return CManager_Controller_Router_Abstract
	 */
	final public function setStructure(CManager_Controller_Router_Config_Routes $structure) {
		$this->structure = $structure;
		return $this;
	}

	/**
	 * @param CManager_Controller_Page $page
	 */
	final public function setPage(CManager_Controller_Page $page) {
		$page->init();
		$this->page = $page;
	}

	/**
	 * @return array
	 */
	final public function __sleep() {
		return array('routes', 'structure');
	}

	/**
	 * @abstract
	 * @param string $pageName
	 * @param array $variables
	 * @return CManager_Controller_Page
	 */
	public function createPage($pageName, array $variables = array()) {
		if ($route = $this->getRoutes($pageName)) {
			$pageConfig	= $route->getPageConfig();
			$classPage	= $pageConfig->namespace !== null? $pageConfig->namespace: self::DEFAULT_CLASS_PAGE;
			$page		= /** @var CManager_Controller_Page $page */ CManager_Helper_Object::newInstance(
					$classPage,
					self::DEFAULT_CLASS_PAGE,
					array($pageConfig, $this->getRequest(), $this->getResponse()));

			$page->setRoute($route)
				->setVariables($variables);

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
		if ($this->routes === null) {
			$this->routes = $this->generateRoutes($this->getStructure()->route);
		}
		if ($name !== null) {
			if (array_key_exists($name, $this->routes)) {
				return $this->routes[$name];
			}
			return null;
		}
		return $this->routes;
	}

	/**
	 * @param CManager_Controller_Router_Config_Route[] $routesConfig
	 * @param CManager_Controller_Route $parentRoute
	 * @return array
	 */
	protected function generateRoutes(array $routesConfig, CManager_Controller_Route $parentRoute = null) {
		$routes = /** @var CManager_Controller_Route[] $routesConfig */array();
		foreach($routesConfig as $route) {
			if (array_key_exists($route->name, $routesConfig)) {
				throw new CManager_Controller_Router_Exception("Route '{$route->name}' already exists in router");
			}
			$routes[$route->name] = $this->createRoute($route, $parentRoute);

			if (is_array($route->route)) {
				$routes = array_merge($routes, $this->generateRoutes($route->route, $routes[$route->name]));
			}
		}

		return $routesConfig;
	}

	/**
	 * @param CManager_Controller_Router_Config_Route $route
	 * @param CManager_Controller_Route $parentRoute
	 * @return CManager_Controller_Route
	 */
	protected function createRoute(CManager_Controller_Router_Config_Route $route, CManager_Controller_Route $parentRoute = null) {
		$route = new CManager_Controller_Route($route->url, $route->var);
		if ($parentRoute !== null) {
			$route->setParent($parentRoute);
		}

		$route->setRouter($this);

		return $route;
	}

	/**
	 * @param CManager_Controller_PageResolver $pageResolver
	 * @return void
	 */
	public function setPageResolver(CManager_Controller_PageResolver $pageResolver) {
		$this->pageResolver = $pageResolver;
		$this->pageResolver->setRouter($this);
	}

	/**
	 * @return CManager_Controller_PageResolver
	 */
	public function getPageResolver() {
		if ($this->pageResolver === null) {
			$this->setPageResolver(new CManager_Controller_PageResolver());
		}
		return $this->pageResolver;
	}
}
