<?php

abstract class CManager_Controller_Router_Config_Abstract {
	const PASS_ATTRIBUTE		= 'pass';

	protected $_name = '';
	/**
	 * array(
	 *     $attributeName => array(
	 *         (bool) required,   // optional, default = false
	 *         (bool) single,     // optional, default = true
	 *         (string) namespace - int, enum(val1,val2,val3...), string, float, bool, string, Structure_...
	 *     ),
	 *     ...
	 * }
	 *
	 * @var array
	 */
	protected $_attributes = array();

	/**
	 * ВАЖНО! Элементы ..._exclusion должны объявляться в самом начале!
	 * @see $this->_attributes
	 * @var array
	 */
	protected $_children = array();

	/**
	 * @var string[]|int[]|float[]|bool[]|CManager_Controller_Router_Config_Abstract[]
	 */
	protected $_data = array();

	/**
	 * @var CManager_Controller_Router_Config_Abstract
	 */
	protected $_parent = null;

	/**
	 * @var CManager_Controller_Router_Config_Adapter_Abstract
	 */
	protected $_adapter = null;

	/**
	 * @param mixed $config
	 * @param CManager_Controller_Router_Config_Adapter_Abstract $adapter
	 * @param CManager_Controller_Router_Config_Abstract|null $parent
	 */
	public function __construct($config, CManager_Controller_Router_Config_Adapter_Abstract $adapter, CManager_Controller_Router_Config_Abstract $parent = null) {
		$this->_adapter = $adapter;
		$this->_parent = $parent;
		$this->_parseAttributes($config);
		$this->_parseChildren($config);
	}

	/**
	 * @return CManager_Controller_Router_Config_Adapter_Abstract
	 */
	public function getAdapter() {
		return $this->_adapter;
	}

	/**
	 * @param mixed $element
	 * @throws CManager_Controller_Router_Config_Exception
	 */
	protected function _parseAttributes($element) {
		foreach ($this->_attributes as $field => $config) {
			$config	= $this->_extendConfig($config);
			$value	= $this->getAdapter()->getAttribute($element, $field);

			if ($config['required'] === true && $value === null) {
				throw new CManager_Controller_Router_Config_Exception("@{$field} is required for {$this}");
			}
			if (!$config['single']) {
				throw new CManager_Controller_Router_Config_Exception("@{$field} can't be multiple in {$this}");
			}

			$this->_set($field, $this->_createValue($value, $config, true));
			$this->_attributes[$field] = $config;
		}

		// реализовываем наследование
		foreach($this->_attributes as $field => $config) {
			$this->_tryInheritAttribute($field, $config);
		}
	}

	/**
	 * @param mixed $element
	 * @throws CManager_Controller_Router_Config_Xml_Exception
	 */
	protected function _parseChildren($element) {
		foreach($this->_children as $field => $config) {
			$config	= $this->_extendConfig($config);
			$value	= $this->getAdapter()->getChild($element, $field);

			if ($config['required'] === true && $value === null) {
				throw new CManager_Controller_Router_Config_Exception("Child '{$field}' is required");
			}
			if ($config['single'] && is_array($value) && count($value) > 1) {
				throw new CManager_Controller_Router_Config_Exception("Child '{$field}' should be only one");
			}
			if (!$config['single'] && !is_array($value)) {
				$value = $value === null? array(): array($value);
			}
			$this->_set($field, $this->_createValue($value, $config));
			$this->_children[$field] = $config;
		}

		// реализовываем наследование
		foreach($this->_children as $field => $config) {
			$this->_tryInheritChildren($field, $config);
		}
	}

	/**
	 * @param string $field
	 * @param array $config
	 * @return mixed
	 */
	protected function _tryInheritChildren($field, array $config) {
		if (!$config['inherit']) {
			return;
		}
		if ($config['single']) {
			throw new CManager_Controller_Router_Config_Exception("Inherited tags must be multiple");
		}

		// получаем список атрибутов @name, что бы НЕ НАСЛЕДОВАТЬ элементы с такими именами от родителя
		$elementsCurrent = $this->$field;
		$namesExists = array();
		foreach($elementsCurrent as $element) {
			$name = $element->name;
			if ($name !== null && !in_array($name, $namesExists)) {
				$namesExists[] = $name;
			}
		}
		// получаем список исключенных элементов (если указан $config['exclusion'])
		$namesExclusion = array();
		if ($config['exclusion']) {
			$exclusions = $this->{$config['exclusion']};
			if ($exclusions && is_array($exclusions)) {
				foreach($exclusions as $exclusion) {
					$name = $exclusion->name;
					if ($name !== null && !in_array($name, $namesExclusion)) {
						$namesExclusion[] = $name;
					}
				}
			}
		}

		// собираем элементы с родителей
		$parent = $this;
		while ($parent = $parent->getParent()) {
			$elements = $parent->$field;
			if ($elements !== null) {
				if (!is_array($elements)) {
					$elements = array($elements);
				}

				$namesExistsCurrent = array();
				foreach($elements as $element) {
					if ($element->{self::PASS_ATTRIBUTE} === null) {
						continue;
					}
					$name = $element->name;
					if ($name !== null && !in_array($name, $namesExists)) {
						$namesExistsCurrent[] = $name;
						if (in_array($name, $namesExclusion)) {
							continue;
						}
						$elementsCurrent[] = $element;
					} else if ($name === null) {
						$elementsCurrent[] = $element;
					}
				}
				$namesExists = array_merge($namesExists, $namesExistsCurrent);
			}
		}
		$this->_set($field, $elementsCurrent);
	}

	/**
	 * @param string $field
	 * @param array $config
	 * @return mixed
	 */
	protected function _tryInheritAttribute($field, array $config) {
		if (!$config['inherit']) {
			return;
		}

		if ($this->$field !== $config['default']) {
			return;
		}

		// находим такой же аттрибут у ближайшего родителя
		$parent = $this;
		while ($parent = $parent->getParent()) {
			$attribute = $parent->$field;
			if ($attribute !== null) {
				$this->_set($field, $attribute);
				return;
			}
		}
	}

	/**
	 * @param string $field
	 * @param mixed $value
	 * @throws CManager_Controller_Router_Config_Exception
	 */
	protected function _set($field, $value) {
		$this->_data[$field] = $value;
	}

	/**
	 * @param string $field
	 * @return mixed
	 * @throws CManager_Controller_Router_Config_Exception
	 */
	public function __get($field) {
		if (array_key_exists($field, $this->_data)) {
			return $this->_data[$field];
		}
		return null;
//		throw new CManager_Controller_Router_Config_Exception("{$field} not defined");
	}

	/**
	 * @param array|string $config
	 * @return array
	 */
	protected function _extendConfig($config) {
		if (!is_array($config)) {
			$config = array('namespace' => $config);
		}
		if (!isset($config['namespace'])) {
			throw new CManager_Controller_Router_Config_Exception("Attribute @namespace is required for configuration");
		}
		$config = array_merge(array(
			// должен встречаться минимум один раз
			'required'	=> false,
			// может встречать максимум один раз
			'single'	=> true,
			// значение по-умолчанию
			'default'	=> null,

			// наследывать от родителей одноименные теги (по @name, если @name не указывается, то автоматически наследуются)
			'inherit'	=> false,
			// название тега, отменяющего наследование (у тега должен быть обязательный атрибут @name)
			'exclusion'	=> null
		), $config);
		return $config;
	}

	/**
	 * @param array|string|null $value
	 * @param array $config
	 * @param bool $onlyScalar
	 * @return mixed
	 * @throws CManager_Controller_Router_Config_Exception
	 */
	protected function _createValue($value = null, array $config, $onlyScalar = false) {
		if ($value === null || (is_array($value) && count($value) == 0 && $config['single'])) {
			if ($config['default'] === null) {
				return null;
			}
			$value = $config['default'];
			$onlyScalar = true;
		}

		if (!$config['single']) {
			if (!is_array($value)) {
				$value = array($value);
			}
			$result = array();
			$config['single'] = true;
			foreach($value as $item) {
				$result[] = $this->_createValue($item, $config, $onlyScalar);
			}
			return $result;
		}

		switch(true) {
			case strpos($config['namespace'], 'enum') === 0:
				$enumValues = explode(',', substr($config['namespace'], 5, -1));
				$value = (string) $value;
				if (!in_array($value, $enumValues)) {
					throw new CManager_Controller_Router_Config_Exception("Value '{$value}' not in {$config['namespace']}");
				}
				break;
			case $config['namespace'] == 'int':
				$value = (int) $value;
				break;
			case $config['namespace'] == 'float':
			case $config['namespace'] == 'double':
				$value = (double) $value;
				break;
			case $config['namespace'] == 'bool':
			case $config['namespace'] == 'boolean':
				$value = (bool) $value;
				break;
			case $config['namespace'] == 'string':
				$value = (string) $value;
				break;
			case !$onlyScalar && class_exists('CManager_Controller_Router_Config_' . $config['namespace']):
				if (is_array($value)) {
					throw new CManager_Controller_Router_Config_Exception("Value for {$config['namespace']} is array");
				}
				$config['namespace'] = 'CManager_Controller_Router_Config_' . $config['namespace'];
				$value = new $config['namespace']($value, $this->getAdapter(), $this);
				break;
			default:
				throw new CManager_Controller_Router_Config_Exception("Namespace {$config['namespace']} not defined");
		}
		return $value;
	}

	/**
	 * @return array
	 */
	public function toArray() {
		$array = array();
		foreach($this->_data as $field => $value) {
			$array[$field] = $this->_toArrayValue($value);
		}
		return $array;
	}

	/**
	 * @param mixed $value
	 * @return array|mixed|string
	 */
	protected function _toArrayValue($value) {
		$result = array();
		if (is_array($value)) {
			foreach($value as $item) {
				$result[] = $this->_toArrayValue($item);
			}
		} else if (is_object($value)) {
			if (is_callable(array($value, 'toArray'))) {
				$result = $value->toArray();
			} else {
				$result = (string) $value;
			}
		} else {
			$result = $value;
		}
		return $result;
	}

	/**
	 * @param CManager_Controller_Router_Config_Abstract $parent
	 * @return CManager_Controller_Router_Config_Abstract
	 */
	public function setParent(CManager_Controller_Router_Config_Abstract $parent) {
		$this->_parent = $parent;
		return $this;
	}

	/**
	 * НЕ РАБОТАЕТ ПОСЛЕ unserialize!!!
	 * @return CManager_Controller_Router_Config_Abstract|null
	 */
	public function getParent() {
		return $this->_parent;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 * @param string $name
	 * @return CManager_Controller_Router_Config_Abstract
	 */
	public function setName($name) {
		$this->_name = (string) $name;
		return $this;
	}

	public function __toString() {
		$name = $this->name;
		return $this->getName() . ($name !== null? "[{$name}]": '');
	}

	/**
	 * @return array
	 */
	public function __sleep() {
		return array('_data');
	}

	public function __wakeup() {
		foreach($this->_data as &$item) {
			if (is_array($item)) {
				foreach($item as &$arrayItem) {
					if ($item instanceof self) {
						$arrayItem->setParent($this);
					}
				}
				continue;
			}
			if ($item instanceof self) {
				$item->setParent($this);
			}
		}
	}
}