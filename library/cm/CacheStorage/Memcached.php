<?php

class cm_CacheStorage_Memcached extends cm_CacheStorage_Abstract
{
	/**
	 * @var array
	 */
	protected static $_connection = array();

	/**
	 * @var Zend_Config
	 */
	protected $_config = null;

	/**
	 * @var string
	 */
	protected $_persistentId = null;

	/**
	 * @var string
	 */
	protected $_persistentIds = array();

	/**
	 * @param array|Zend_Config $config
	 */
	public function __construct($config = array()) {
		if (!class_exists('Memcached')) {
			throw new cm_CacheStorage_Exception('class Memcached unavailable');
			// @todo: throw exception...
		}

		parent::__construct($config);

		$this->_initConnection();
	}

	/**
	 * @return void
	 */
	public function _initConnection() {
		$this->_getConnection();
	}

	/**
	 * @param string $persistentId
	 * @return void
	 */
	public function setPersistentId($persistentId) {
		array_unshift($this->_persistentId, $persistentId);
	}

	/**
	 * @return void
	 */
	public function restorePersistentId() {
		array_shift($this->_persistentId);
	}

	/**
	 * @return string
	 */
	public function getPersistentId() {
		if (!isset($this->_persistentId[0])) {
			$this->_persistentId[0] = key($this->_config);
		}
		return $this->_persistentId[0];
	}

	/**
	 * @return Memcached
	 */
	public function _getConnection() {
		$persistentId = $this->getPersistentId();

		if (!isset(self::$_connection[$persistentId])) {

			if (!isset($this->_config[$persistentId])) {
				// @todo: throw exception...
			}

			$config = $this->_config[$persistentId];
			
			if (!isset($config['server'])) {
				// @todo: throw exception...
			}

			self::$_connection[$persistentId] = new Memcached($persistentId);


			foreach ($config['server'] as $server) {
				self::$_connection[$persistentId]
					->addServer($server['host'], $server['port'], $server['weight']);
			}

			if (isset($config['options']) && is_array($config['options'])) {
				foreach ($config['options'] as $option => $value) {
					self::$_connection[$persistentId]
						->setOption(constant('Memcached::'. $option), $value);
				}
			}
		}

		return self::$_connection[$persistentId];
	}

	/**
	 * @param string $key
	 * @return boolean
	 */
	public function delete($key) {
		return $this->_getConnection()
			->delete($this->_prepareKey($key));
	}

	/**
	 * @param string $key
	 * @param mixed $data
	 * @param int $ttl
	 * @return boolean
	 */
	public function save($key, $data, $ttl = 0) {
		if ($ttl === null) {
			$ttl = $this->_getDefaultTtl();
		}
		return $this->_getConnection()
			->set($this->_prepareKey($key), $data, $ttl);
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function read($key) {
		return $this->_getConnection()
			->get($this->_prepareKey($key));
	}

	/**
	 * @param string $key
	 * @param int $time
	 * @return mixed
	 */
	public function load($key, $time = null) {
		$key = $this->_prepareKey($key);
		// TODO: Implement load() method.
	}

}
