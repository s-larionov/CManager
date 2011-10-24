<?php

class cm_Display {
	/**
	 * @var cm_Controller_Front
	 */
	protected static $_application = null;

	/**
	 * @return cm_Controller_Front
	 */
	public static function getApplication() {
		return self::$_application;
	}

	/**
	 * @param cm_Controller_Front $application
	 * @return void
	 */
	public static function setApplication(cm_Controller_Front $application) {
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
	 * @return string
	 */
	public static function title() {
		return self::getApplication()->getRouter()->getPage()->getTitle();
	}

}