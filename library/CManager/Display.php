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
	 * @static
	 * @param string $name
	 * @param array $params
	 * @return string
	 */
	public static function url($name, array $params = array()) {
		return self::getApplication()->getRouter()->generateUrl($name, $params);
	}
}