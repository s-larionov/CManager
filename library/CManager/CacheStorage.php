<?php

class CManager_CacheStorage {
	/**
	 * @var array
	 */
	private static $_instance = array();

	/**
	 * @static
	 * @throws CManager_CacheStorage_Exception
	 * @param string $storage
	 * @param array|Zend_Config $config
	 * @return CManager_CacheStorage_Interface
	 */
	public static function factory($storage, $config = array()) {
		$className = 'CManager_CacheStorage_'. $storage;

		if ($config instanceof Zend_Config) {
			$config = $config->toArray();
		}

		return new $className($config);
	}
}