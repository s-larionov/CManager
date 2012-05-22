<?php

class CManager_Cache_Manager {
	const SCOPE_DEFAULT	= 'default';
	const ALIAS_DEFAULT	= 'default';
	const STORAGE_MOCK	= 'Mock';

	const CONFIG_PARAM_SCOPES = 'scopes';
	const CONFIG_PARAM_CONNECTIONS = 'connections';

	/**
	 * @var Zend_Config
	 */
	protected static $_config = null;

	/**
	 * @var CManager_Cache_Storage_Abstract[]
	 */
	protected static $_connections = array();

	/**
	 * Map of 'scope' => 'alias'
	 *
	 * @var string[]
	 */
	protected static $_scopesMap = array();

	/**
	 * @static
	 * @param string $mode
	 * @return Zend_Config
	 * @throws CManager_Cache_Manager_Exception
	 */
	protected static function _getConfig($mode = self::CONFIG_PARAM_CONNECTIONS) {
		if (self::$_config === null) {
			self::$_config = CManager_Registry::getConfig()->get('cache_manager');

			if (!self::$_config instanceof Zend_Config) {
				throw new CManager_Cache_Manager_Exception('Cache manager is not configured.');
			}
		}
		if (!isset(self::$_config->$mode) || !self::$_config->$mode instanceof Zend_Config) {
			throw new CManager_Cache_Manager_Exception("Cache manager {$mode} is not configured.");
		}
		return self::$_config->$mode;
	}

	/**
	 * @static
	 * @param string $connectionAlias
	 * @return Zend_Config
	 * @throws CManager_Cache_Manager_Exception
	 */
	protected static function _getAliasConfig($connectionAlias = self::ALIAS_DEFAULT) {
		$config = /** @var Zend_Config $config */ self::_getConfig('connections')->get($connectionAlias);
		if (!$config instanceof Zend_Config) {
			throw new CManager_Cache_Manager_Exception("Cache connection '{$connectionAlias}' is not configured.");
		}
		return $config;
	}

	/**
	 * @param string $alias
	 *
	 * @throws CManager_Cache_Manager_Exception
	 * @return CManager_Cache_Storage_Abstract
	 */
	public static function getConnectionByAlias($alias) {
		if (!array_key_exists($alias, self::$_connections)) {
			try {
				$config	= self::_getAliasConfig($alias);
			} catch (CManager_Cache_Manager_Exception $e) {
				$config = new Zend_Config(array());
			}
			$storage= $config->get('storage', self::STORAGE_MOCK);
			try {
				static::$_connections[$alias] = CManager_Helper_Object::newInstance(
					"CManager_Cache_Storage_{$storage}",
					'CManager_Cache_Storage_Abstract',
					array($config)
				);
			} catch (CManager_Exception $e) {
				throw new CManager_Cache_Manager_Exception("Cache storage '{$storage}' is not found.");
			}
		}

		return static::$_connections[$alias];
	}

	/**
	 * @static
	 * @param string $scope
	 * @param string $defaultScope
	 * @return CManager_Cache_Storage_Abstract
	 */
	public static function getConnectionByScope($scope, $defaultScope = self::SCOPE_DEFAULT) {
		return self::getConnectionByAlias(
			self::_getConnectionAliasByScope($scope, $defaultScope)
		);
	}

	/**
	 * @static
	 * @param string      $scopeName
	 * @param string|null $defaultScope
	 *
	 * @throws CManager_Cache_Manager_Exception
	 * @return string
	 */
	protected static function _getConnectionAliasByScope($scopeName, $defaultScope = self::SCOPE_DEFAULT) {
		if (empty(self::$_scopesMap)) {
			$scopesConfig = /** @var Zend_Config $scopesConfig */ self::_getConfig('scopes')->get('scope');
			if (!$scopesConfig instanceof Zend_Config) {
				$scopesConfig = new Zend_Config(array());
			}
			if (!is_numeric($scopesConfig->key())) {
				$scopesConfig = array($scopesConfig);
			}
			foreach($scopesConfig as /** @var Zend_Config $scope */ $scope) {
				self::$_scopesMap[$scope->get('name')] = $scope->get('connection');
			}
			if (empty(self::$_scopesMap)) {
				self::$_scopesMap[self::SCOPE_DEFAULT] = self::ALIAS_DEFAULT;
			}
		}
		if (!array_key_exists($scopeName, self::$_scopesMap)) {
			if ($defaultScope === null || $scopeName == $defaultScope) {
				throw new CManager_Cache_Manager_Exception("Cache scope '{$scopeName}' not configured");
			}
			self::$_scopesMap[$scopeName] = self::_getConnectionAliasByScope($defaultScope, null);
		}
		return self::$_scopesMap[$scopeName];
	}
}
