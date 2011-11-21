<?php

class CManager_Display {
	/**
	 * @var CManager_Controller_Front
	 */
	protected static $_application = null;

	/**
	 * @return CManager_Controller_Front
	 */
	public static function getApplication() {
		return self::$_application;
	}

	/**
	 * @param CManager_Controller_Front $application
	 * @return void
	 */
	public static function setApplication(CManager_Controller_Front $application) {
		self::$_application = $application;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public static function tag($name) {
		return self::getApplication()->getRouter()->getPage()->runTagsByName($name);
	}

	/**
//	 * @param string $tag1Name
//	 * @param string $tag2Name
//	 * @param string $tag3Name
//	 * @param string ...
	 * @return string
	 */
	public static function tags() {
		$page = self::getApplication()->getRouter()->getPage();
		$out = '';

		foreach(func_get_args() as $tagName) {
			$out .= $page->runTagsByName($tagName);
		}

		return $out;
	}

	/**
	 * @param string $mode
	 * @return string
	 */
	public static function title($mode = 'default') {
		return self::getApplication()->getRouter()->getPage()->getTitle($mode);
	}

	/**
	 * Если не передан первый аргумент (или передан false),
	 * то будет сгенерирована ссылка на текущую страницу с текущими параметрами.
	 * Параметры можно переопределять.
	 *
	 * @static
	 * @param bool|string $name
	 * @param array $params
	 * @return string
	 */
	public static function url($name = false, array $params = array()) {
		$router = self::getApplication()->getRouter();
		if ($name === false) {
			$page = $router->getPage();
			return $page->getRoute()->generateUrl(array_merge($page->getVariables(), $params), false);
		}

		return self::getApplication()->getRouter()->generateUrl($name, $params);
	}

	/**
	 * @static
	 * @param bool|string $name
	 * @return string
	 * @see CManager_Display::url
	 */
	public static function urlFromXsl($name = false) {
		$params = array();
		for($i = 1, $count = func_num_args(); $i < $count; $i += 2) {
			$params[(string) func_get_arg($i)] = func_get_arg($i + 1);
		}
		return self::url($name, $params);
	}
}