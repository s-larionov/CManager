<?php

abstract class CManager_CacheStorage_Abstract {

	private static $instance = array();

	/**
	 * @var Zend_Config
	 */
	protected $_config = null;

	/**
	 * @var string
	 */
	protected $_namespace = '';

	final public static function factory($storage) {
		if (!isset(self::$instance[$storage])) {
			try {
				$object = new ReflectionClass('CManager_CacheStorage_'. $storage);
				self::$instance[$storage] = $object->newInstance();
			} catch (ReflectionException $e) {
				throw new CManager_CacheStorage_Exception('trying_use_undefined_cache_strorage', $storage);
			}
		}
		return self::$instance[$storage];
	}

	/**
	 * @param array|Zend_Config $config
	 */
	public function __construct($config = array()) {
		if (!$config) {
			$config = array();
		}

		if ($config instanceof Zend_Config) {
			$config = $config->toArray();
		}

		$this->_config = (array) $config;

		if (isset($this->_config['namespace'])) {
			$this->setNamespace($this->_config['namespace']);
		}
	}

	/**
	 * @param string $namespace
	 * @return CManager_CacheStorage_Interface
	 */
	final public function setNamespace($namespace) {
		$this->_namespace = (string) $namespace;
	}

	/**
	 * @return int
	 */
	protected function _getDefaultTtl() {
		return isset($this->_config['default_ttl'])? (int) $this->_config['default_ttl']: 0;
	}

	/**
	 * @param string $key
	 * @return string
	 */
	protected function _prepareKey($key) {
		return "{$this->_namespace}::{$key}";
	}

	abstract function load($key, $time = null);
	abstract function read($key);
	abstract function save($key, $data, $ttl = 0);
	abstract function delete($key);

}