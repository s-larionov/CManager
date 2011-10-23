<?php

class cm_DB_Manager {
	/**
	 * @var cm_DB_Manager_Adapter_Interface[]
	 */
	protected static $_connections = array();

	/**
	 * @param string $alias
	 * @return cm_DB_Manager_Adapter_Interface
	 */
	public static function connection($alias) {
		if (!isset(self::$_connections[$alias])) {
			$config = cm_Registry::getConfig()->get('database_manager');

			if (!($config instanceof Zend_Config)) {
				throw new cm_DB_Manager_Exception('Database manager is not configured.');
			}

			$config = $config->get($alias);

			if (!($config instanceof Zend_Config)) {
				throw new cm_DB_Manager_Exception("Database connection '$alias' is not defined.");
			}

			if (!$config->adapter) {
				$config->adapter = 'Zend';
			}

			$className = 'cm_DB_Manager_Adapter_'. $config->adapter;
			if (!class_exists($className)) {
				throw new cm_DB_Manager_Exception("Database manager adapter '{$config->adapter}' is not found.");
			}

			self::$_connections[$alias] = new $className($config);
		}

		return self::$_connections[$alias];
	}

	/**
	 * @return void
	 */
	public function closeAllConnections() {
		foreach (self::$_connections as $connection) {
			$connection->closeConnection();
		}
	}

}