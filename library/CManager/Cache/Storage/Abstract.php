<?php

abstract class CManager_Cache_Storage_Abstract {
	const PROPERTIES_SEPARATOR = "\n~~~\n";

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
				self::$instance[$storage] = CManager_Helper_Object::newInstance("cm_CacheStorage_{$storage}");
			} catch (ReflectionException $e) {
				throw new CManager_Cache_Storage_Exception('Trying use undefined cache storage', $storage);
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
	 * @return cm_CacheStorage_Interface
	 */
	final public function setNamespace($namespace) {
		$this->_namespace = (string) $namespace;
	}

	/**
	 * @return string
	 */
	final public function getNamespace() {
		return $this->_namespace;
	}

	/**
	 * @param string $key
	 * @return string
	 */
	protected function _prepareKey($key) {
		if ($namespace = $this->getNamespace()) {
			return "{$namespace}::{$key}";
		}
		return $key;
	}

	/**
	 * Get Unix timestamp for expired date for cache by TTL (in seconds)
	 *
	 * @param int|null $ttl
	 * @return int
	 */
	protected function _getCacheExpiredTime($ttl) {
		if (!$ttl) {
			$ttl = array_key_exists('default_ttl', $this->_config)? (int) $this->_config['default_ttl']: 0;
		}
		if ($ttl == -1) {
			$ttl = 31536000; // year in seconds
		}
		if ($ttl == 0) {
			return 0;
		}
		return time() + $ttl;
	}

	/**
	 * @param mixed $content
	 * @param int $expiredTime
	 * @param string|null $validateHash
	 * @return string
	 */
	protected function _packContent($content, $expiredTime, $validateHash = null) {
		$properties = array(
			'expired'		=> $expiredTime,
			'validateHash'	=> $validateHash,
			'serialized'	=> is_object($content) || is_array($content)? true: false // флаг сериализации
		);

		if ($properties['serialized']) {
			$content = @serialize($content);
		}

		return json_encode($properties) . self::PROPERTIES_SEPARATOR . $content;
	}

	/**
	 * @param string $packedContent
	 * @return array
	 */
	protected function _unpackContent($packedContent) {
		$defaultProperties = array(
			'expired'		=> 0,
			'validateHash'	=> null,
			'serialized'	=> false
		);

		if (strpos($packedContent, self::PROPERTIES_SEPARATOR) === false) {
			$content	= $packedContent;
			$properties	= $defaultProperties;
		} else {
			list($properties, $content) = explode(self::PROPERTIES_SEPARATOR, $packedContent, 2);
			$properties	= @json_decode($properties, true);
			$properties	= array_merge($defaultProperties, !is_array($properties)? array(): $properties);
		}

		if ($properties['serialized']) {
			$content = @unserialize($content);
		}

		return array(
			'properties'=> $properties,
			'content'	=> $content
		);
	}

	/**
	 * @param string $key
	 * @return int|false
	 */
	public function getTime($key) {
		if ($data = $this->_get($key)) {
			return (int) $data['properties']['expired'];
		}
		return false;
	}

	/**
	 * Получает данные из кеша если они валидные. Иначе возвращает false
	 *
	 * @param string $key
	 * @param int|null $time
	 * @param string|null $validateHash @see method save
	 * @return mixed
	 */
	public function load($key, $time = null, $validateHash = null) {
		if (!$time) {
			$time = time();
		}

		// получаем данные по ключу
		if ($data = $this->_get($key)) {
			// проверяем на актуальность
			if ($data['properties']['expired'] < $time || $data['properties']['validateHash'] !== $validateHash) {
				return false;
			}
			return $data['content'];
		}

		return false;
	}

	/**
	 * Получает данные из кеша даже если они уже невалидны. Если данных в кеше нет - возвращает false
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function read($key) {
		// получаем данные по ключу
		if ($data = $this->_get($key)) {
			return $data['content'];
		}
		return false;
	}

	/**
	 * Сохранить данные в кеш по определенному ключу.
	 *
	 * @abstract
	 * @param string		$key
	 * @param mixed			$data
	 * @param int|null		$ttl
	 * @param string|null	$validateHash	Дополнительная инвалидация кеша. Строка уникальная для внешних факторов, влияющих
	 * 										на актуальность данных по этому ключу.
	 * @return mixed
	 */
	abstract public function save($key, $data, $ttl = null, $validateHash = null);

	/**
	 * @abstract
	 * @param string $key
	 */
	abstract public function delete($key);

	/**
	 * Получить распакованное содержимое ключу (array('content' => ..., 'properties' => array(...))
	 *
	 * @abstract
	 * @param string $key
	 * @return array
	 */
	abstract protected function _get($key);

}
