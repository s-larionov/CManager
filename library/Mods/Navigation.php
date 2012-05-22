<?php

class Mods_Navigation extends CManager_Controller_Action_Cache {
	protected $_cacheEnabled = true;

	/**
	 * @var Mods_Navigation_Item[]
	 */
	protected static $_navigations	= null;
	/**
	 * @var string
	 */
	protected static $_currentUrl	= null;

	/**
	 * @static
	 * @return CManager_Controller_Router_Abstract
	 */
	protected  static function _getRouter() {
		static $router = null;
		if ($router === null) {
			$router = CManager_Registry::getFrontController()->getRouter();
		}
		return $router;
	}

	/**
	 * @static
	 * @return CManager_Controller_Page
	 */
	protected  static function _getCurrentPage() {
		static $page = null;
		if ($page === null) {
			$page = self::_getRouter()->getPage();
		}
		return $page;
	}

	public function run() {
		$this->tryLoadFromCache();

		$name	= $this->getParam('name');
		$xml	= $this->getNavigation($name)->toXml();
		$xsl	= $this->getParam('xsl');
		$params	= array(
			'nav-name'	=> $name,
			'class'		=> $this->getParam('class', ''),
			'title-mode'=> $this->getParam('title-mode', 'nav')
		);
		$this->sendContent(CManager_Dom_Document::xslTransformSource($xsl, $xml, $params));
	}

	/**
	 * @static
	 * @param string $name
	 * @param CManager_Controller_Router_Config_Page|null $fromPage
	 * @return Mods_Navigation_Item
	 */
	public static function getNavigation($name, CManager_Controller_Router_Config_Page $fromPage = null) {
//		$key = $fromPage !== null? "{$fromPage->name}-{$name}": $name;
		$key = $name;

		if ($fromPage === null && static::$_navigations === null) {
			static::_createNavigations();
		}
		if (!is_array(static::$_navigations)) {
			static::$_navigations = array();
		}
		if ($fromPage !== null && !array_key_exists($key, static::$_navigations)) {
			static::_createNavigations($fromPage);
		}

		if (array_key_exists($key, static::$_navigations) && static::$_navigations[$key] instanceof Mods_Navigation_Item) {
			return static::$_navigations[$key];
		}

		static::$_navigations[$key] = static::_createRootNavigation($name);
		return static::$_navigations[$key];
	}

	/**
	 * @static
	 * @param \CManager_Controller_Router_Config_Abstract|null $structure
	 * @return void
	 */
	protected static function _createNavigations(CManager_Controller_Router_Config_Abstract $structure = null) {
		$router = self::_getRouter();
		if ($structure === null) {
			$structure = $router->getStructure();
		}

		if (!is_array(static::$_navigations)) {
			static::$_navigations = array();
		}
		foreach($structure->page as $page) {
			try {
				$url = $router->generateUrl($page->name);
			} catch (CManager_Controller_Route_Exception $e) {
				continue;
			}
			static::_tryAppendPage($page, array(
				'url'		=> $url,
				'isCurrent'	=> $page->name == self::_getCurrentPage()->getStructure()->name
			));
		}
	}

	/**
	 * @static
	 * @param CManager_Controller_Router_Config_Page $pageConfig
	 * @param Mods_Navigation_Item|null $navigation
	 * @return void
	 */
	protected static function _isHerePage(CManager_Controller_Router_Config_Page $pageConfig,
										  Mods_Navigation_Item $navigation) {
		if (self::_getCurrentPage()->getStructure()->name == $pageConfig->name) {
			$navigation->here = true;
			return;
		}
		foreach($pageConfig->page as $page) {
			if (self::_getCurrentPage()->getStructure()->name == $page->name) {
				$navigation->here = true;
				return;
			}
			self::_isHerePage($page, $navigation);
		}
	}

	/**
	 * @static
	 * @param CManager_Controller_Router_Config_Page $pageConfig
	 * @param array $config
	 * @param Mods_Navigation_Item|null $navigation
	 * @return void
	 */
	protected static function _tryAppendPage(CManager_Controller_Router_Config_Page $pageConfig,
										  array $config,
										  Mods_Navigation_Item $navigation = null) {
		if (!static::_allowPermission($pageConfig->permission)
				|| !isset($config['url'])
				|| !isset($config['isCurrent'])) {
			return;
		}

		$navigationTags = $pageConfig->nav;

		if (empty($navigationTags) && $navigation !== null) {
			// определяем находится ли текущая страница внутри этой ветки навигации
			static::_isHerePage($pageConfig, $navigation);
		}
		if (empty($navigationTags)) {
			return;
		}

		$titles = array();
		foreach($pageConfig->title as $title) {
			$titles[] = array(
				'mode' => $title->mode,
				'value'=> $title->value
			);
		}

		foreach($navigationTags as $navigationTag) {
			if ($navigation !== null && $navigationTag->name != $navigation->getNavigationName()) {
				continue;
			}

			$navItem = new Mods_Navigation_Item($navigationTag->name,
				array_merge(array(
					'name' => $navigationTag->name,
					'value' => $navigationTag->value
				), array(
					'name'		=> $pageConfig->name,
					'title'		=> $titles,
					'url'		=> $config['url'],
					'current'	=> $config['isCurrent']
				)));

			if ($navigation === null) {
				if (!array_key_exists($navigationTag->name, static::$_navigations) || !(static::$_navigations[$navigationTag->name] instanceof Mods_Navigation_Item)) {
					static::$_navigations[$navigationTag->name] = static::_createRootNavigation($navigationTag->name);
				}
				static::$_navigations[$navigationTag->name]->addSubItem($navItem);
			} else {
				$navigation->addSubItem($navItem);
			}

			foreach($pageConfig->page as $page) {
				try {
					$url = self::_getRouter()->generateUrl($page->name);
				} catch (CManager_Controller_Route_Exception $e) {
					// определяем находится ли текущая страница внутри этой ветки навигации
					static::_isHerePage($page, $navItem);
					continue;
				}
				static::_tryAppendPage($page, array(
					'url'		=> $url,
					'isCurrent'	=> $page->name == self::_getCurrentPage()->getStructure()->name
				), $navItem);
			}
			if (count($pageConfig->page) > 0) {
				static::_isHerePage($pageConfig, $navItem);
			}
		}
	}

	protected static function _allowPermission(array $permissions) {
		return true;
	}

	/**
	 * @param string $name
	 * @return Mods_Navigation_Item
	 */
	protected function _createRootNavigation($name) {
		return new Mods_Navigation_Item($name, array(
			'name'	=> '__ROOT__',
			'title'	=> '__ROOT__',
			'url'	=> '__ROOT__'
		));
	}
}
