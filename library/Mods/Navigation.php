<?php

class Mods_Navigation extends CManager_Controller_Action_Abstract {
	/**
	 * @var Mods_Navigation_Item[]
	 */
	protected static $_navigations = null;

	public function run() {
		$xml	= $this->getNavigation('main')->toXml();
		$xsl	= $this->getParam('xsl');
		$params	= array();
		$this->sendContent(CManager_Dom_Document::xslTransformSource($xsl, $xml, $params));
	}

	/**
	 * @static
	 * @param string $name
	 * @return Mods_Navigation_Item
	 */
	public static function getNavigation($name) {
		if (static::$_navigations === null) {
			static::_createNavigations();
		}

		if (array_key_exists($name, static::$_navigations) && static::$_navigations[$name] instanceof Mods_Navigation_Item) {
			return static::$_navigations[$name];
		}

		static::$_navigations[$name] = static::_createRootNavigation($name);
		return static::$_navigations[$name];
	}

	/**
	 * @static
	 * @return void
	 */
	protected static function _createNavigations() {
		$structure = CManager_Registry::getFrontController()->getRouter()->getStructure();

		$router		= CManager_Registry::getFrontController()->getRouter();
		$page		= $router->getPage();
		$currentUrl	= $page->getRoute()->generateUrl($page->getVariables(), false);

		static::$_navigations = array();
		foreach($structure->page as $page) {
			try {
				$url = $router->generateUrl($page->name);
			} catch (CManager_Controller_Route_Exception $e) {
				continue;
			}
			static::_appendPage($page, array(
				'url'		=> $url,
				'isCurrent'	=> $currentUrl == $url,
				'currentUrl'=> $currentUrl
			));
		}
	}

	/**
	 * @static
	 * @param CManager_Controller_Router_Config_Page $pageConfig
	 * @param array $config
	 * @param Mods_Navigation_Item|null $navigation
	 * @return void
	 */
	protected static function _appendPage(CManager_Controller_Router_Config_Page $pageConfig,
										  array $config,
										  Mods_Navigation_Item $navigation = null) {
		if (!static::_allowPermission($pageConfig->permission)
				|| !isset($config['url'])
				|| !isset($config['currentUrl'])
				|| !isset($config['isCurrent'])) {
			return;
		}

		$navigationTags = $pageConfig->nav;
		if (count($navigationTags) == 0) {
			return;
		}

		$titles = array();
		foreach($pageConfig->title as $title) {
			$titles[] = $title->toArray();
		}

		foreach($navigationTags as $navigationTag) {
			if ($navigation !== null && $navigationTag->name != $navigation->getNavigationName()) {
				continue;
			}

			$navItem = new Mods_Navigation_Item($navigationTag->name,
				array_merge($navigationTag->toArray(), array(
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
					$url = CManager_Registry::getFrontController()->getRouter()->generateUrl($page->name);
				} catch (CManager_Controller_Route_Exception $e) {
					continue;
				}
				static::_appendPage($page, array(
					'url'		=> $url,
					'isCurrent'	=> $config['currentUrl'] == $url,
					'currentUrl'=> $config['currentUrl']
				), $navItem);
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