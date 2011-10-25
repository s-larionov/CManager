<?php

class CManager_CacheStorage_Memcache extends CManager_CacheStorage_Abstract {
	/**
	 * @var Memcache
	 */
	protected static $_connection = null;

	/**
	 * @param array|Zend_Config $config
	 */
	public function __construct($config = array()) {
		if (!extension_loaded('memcache')) {
			throw new CManager_CacheStorage_Exception('Memcache extension is not loaded.');
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
	 * @return Memcache
	 */
	public function _getConnection() {
		if (!isset(self::$_connection)) {
			self::$_connection = new Memcache();
			if (isset($this->_config['persistent']) && $this->_config['persistent']) {
				// иногда появляется такая ошибка
				// "send of 9 bytes failed with errno=10054 An existing
				//  connection was forcibly closed by the remote host."
				// поэтому поставил "@"
				@self::$_connection->pconnect($this->_config['host'], $this->_config['port']);
			} else {
				self::$_connection->connect($this->_config['host'], $this->_config['port']);
			}
		}
		return self::$_connection;
	}

	/**
	 * @param string $key
	 * @return boolean
	 */
	public function delete($key) {
		return $this->_getConnection()->delete($this->_prepareKey($key), 0);
	}

	/**
	 * @param string $key
	 * @param mixed $data
	 * @param int $ttl
	 * @return boolean
	 */
	public function save($key, $data, $ttl = null) {
		if ($ttl === null) {
			$ttl = $this->_getDefaultTtl();
		}

		$key = $this->_prepareKey($key);

		$cacheProps = array();

		$cacheProps[0] = time() + $ttl;
		$cacheProps[1] = is_object($data) || is_array($data) ? 's' : '';

		if ($cacheProps[1] == 's') {
			$data = @serialize($data);
		}

		$data = implode('|', $cacheProps) ."\n". $data;

		return $this->_getConnection()->set($key, $data, 0, 0);
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function read($key) {
		$key = $this->_prepareKey($key);

		$cacheContent = $this->_getConnection()
			->get($key);

		if (!$cacheContent) {
			return false;
		}

		list($cacheProps, $cacheContent) = explode("\n", $cacheContent, 2);
		$cacheProps = explode('|', $cacheProps);

		// format
		if (isset($cacheProps[1])) {
			switch($cacheProps[1]) {
				// serialized
				case 's':
					$cacheContent = @unserialize($cacheContent);
					break;
			}
		}

		return $cacheContent;
	}

	/**
	 * @param string $key
	 * @param int $time
	 * @return mixed
	 */
	public function load($key, $time = null) {
		if (!$time) {
			$time = time();
		}

		$key = $this->_prepareKey($key);

		$cacheContent = $this->_getConnection()
			->get($key);

		if (!$cacheContent) {
			return false;
		}

		list($cacheProps, $cacheContent) = explode("\n", $cacheContent, 2);
		$cacheProps = explode('|', $cacheProps);

		// ttl
		if (isset($cacheProps[0]) && (int)$cacheProps[0] < $time) {
			return false;
		}

		// format
		if (isset($cacheProps[1])) {
			switch($cacheProps[1]) {
				// serialized
				case 's':
					$cacheContent = @unserialize($cacheContent);
					break;
			}
		}

		return $cacheContent;
	}

}
