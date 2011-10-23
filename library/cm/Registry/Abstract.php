<?php

abstract class cm_Registry_Abstract {
	protected static $_repository = array();

	/**
	 * @static
	 * @param string $key
	 * @return mixed
	 * @throws cm_Registry_Exception
	 */
	public static function get($key) {
		if (!isset(self::$_repository[$key])) {
			throw new cm_Registry_Exception("Not found data in Registry for key {$key}.");
		}
		return self::$_repository[$key];
	}

	/**
	 * @static
	 * @param string $key
	 * @param mixed $value
	 */
	public static function set($key, $value) {
		self::$_repository[$key] = $value;
	}
}