<?php

class CManager_Cache_Storage_Memcache extends CManager_Cache_Storage_Abstract {
	/**
	 * @var Memcache
	 */
	protected static $_connection = null;


	/**
	 * @param  $config
	 */
	public function __construct($config = array()) {
		if (!extension_loaded('memcache')) {
			throw new CManager_Cache_Storage_Exception('Memcache extension is not loaded.');
		}
		parent::__construct($config);
		$this->_initConnection();
	}

	/**
	 * @return void
	 */
	public function _initConnection() {
		$this->getConnection();
	}

	/**
	 * @return Memcache
	 */
	public function getConnection() {
		if (!isset(self::$_connection)) {
			self::$_connection = new Memcache();

			if (isset($this->_config['persistent'])
				&& $this->_config['persistent']
			) {
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
		return $this->getConnection()
			->delete($this->_prepareKey($key), 0);
	}

	/**
	 * Получить распакованное содержимое ключу (array('content' => ..., 'properties' => array(...))
	 *
	 * @param string $key
	 * @return array
	 */
	protected function _get($key) {
		$raw = $this->getConnection()->get($this->_prepareKey($key));
		return $this->_unpackContent($raw);
	}

	/**
	 * @param string $key
	 * @param mixed $data
	 * @param int|null $ttl
	 * @param string|null $validateHash
	 * @return boolean
	 */
	public function save($key, $data, $ttl = null, $validateHash = null) {
		if(!($expiredTime = $this->_getCacheExpiredTime($ttl))) {
			return false;
		}

		return $this->getConnection()->set(
			$this->_prepareKey($key),
			$this->_packContent($data, $expiredTime, $validateHash),
			0, 0
		);
	}
}
