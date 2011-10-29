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

		static::$_navigations[$name] = static::_createRootNavigation();
		return static::$_navigations[$name];
	}

	/**
	 * @static
	 * @return void
	 */
	protected static function _createNavigations() {
		$structure = CManager_Registry::getFrontController()->getRouter()->getStructure();
		static::$_navigations = array();
		foreach($structure->page as $page) {
			static::_appendPage($page);
		}
	}

	/**
	 * @static
	 * @param CManager_Controller_Router_Config_Page $pageConfig
	 * @param Mods_Navigation_Item|null $navigation
	 * @return void
	 */
	protected static function _appendPage(CManager_Controller_Router_Config_Page $pageConfig, Mods_Navigation_Item $navigation = null) {

		if (!static::_allowPermission($pageConfig)) {
			return;
		}

		$navTags = $pageConfig->nav;
		if (count($navTags) == 0) {
			return;
		}
		$title = static::_getPageTitle($pageConfig);
		try {

			$url = CManager_Registry::getFrontController()->getRouter()
					->generateUrl($pageConfig->name, array(), false);
		} catch (CManager_Controller_Route_Exception $e) {
			return;
		}

		foreach($navTags as $navTag) {
			if ($navigation !== null && $navTag->name != $navigation->getName()) {
				continue;
			}

			$navItem = new Mods_Navigation_Item($navTag->name, $title, $url);
			if ($navigation === null) {
				if (!array_key_exists($navTag->name, static::$_navigations) || !(static::$_navigations[$navTag->name] instanceof Mods_Navigation_Item)) {
					static::$_navigations[$navTag->name] = static::_createRootNavigation();
				}
				static::$_navigations[$navTag->name]->addSubItem($pageConfig->name, $navItem);
			} else {
				$navigation->addSubItem($pageConfig->name, $navItem);
			}

			foreach($pageConfig->page as $page) {
				static::_appendPage($page, $navItem);
			}
		}
	}

	protected static function _allowPermission(CManager_Controller_Router_Config_Page $pageConfig) {
		return true;
	}

	protected function _createRootNavigation() {
		return new Mods_Navigation_Item('root', '', '');
	}

	protected static function _getPageTitle(CManager_Controller_Router_Config_Page $page, $mode = 'default') {
		$title = null;
		foreach($page->title as $title) {
			if ($title->mode == $mode) {
				$title = $title->value;
				break;
			}
		}
		if ($title === null && count($page->title) > 0) {
			$title = $page->title[0]->value;
		}
		return $title;
	}
}