<?php

class cm_CacheStorage {
	/**
	 * @var array
	 */
	private static $_instance = array();

	/**
	 * @static
	 * @throws cm_CacheStorage_Exception
	 * @param string $storage
	 * @param array|Zend_Config $config
	 * @return cm_CacheStorage_Interface
	 */
	public static function factory($storage, $config = array()) {
		$className = 'cm_CacheStorage_'. $storage;

		if ($config instanceof Zend_Config) {
			$config = $config->toArray();
		}

		return new $className($config);
	}
}