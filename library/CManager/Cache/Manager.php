<?php

class CManager_Cache_Manager {
	/**
	 * @var CManager_Cache_Storage_Abstract[]
	 */
	protected static $_connections = array();

	/**
	 * @param string $alias
	 * @return CManager_Db_Manager_Adapter_Interface
	 */
	public static function connection($alias) {
		if (!isset(self::$_connections[$alias])) {
			$config = CManager_Registry::getConfig()->get('cache_manager');

			if (!($config instanceof Zend_Config)) {
				throw new CManager_Db_Manager_Exception('Cache manager is not configured.');
			}

			$config = $config->get($alias);

			if (!($config instanceof Zend_Config)) {
				throw new CManager_Db_Manager_Exception("Cache connection '$alias' is not configured.");
			}

			if (!$config->adapter) {
				$config->adapter = 'Mock';
			}

			$className = 'CManager_Cache_Storage_'. $config->adapter;
			if (!class_exists($className)) {
				throw new CManager_Db_Manager_Exception("Cache manager adapter '{$config->adapter}' is not found.");
			}

			static::$_connections[$alias] = new $className($config);
		}

		return static::$_connections[$alias];
	}

	/**
	 * @return void
	 */
	public static function closeAllConnections() {
		foreach (static::$_connections as $connection) {
			$connection->closeConnection();
		}
	}

}