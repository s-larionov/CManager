<?php

class cm_Controller_Tag {
	const MODE_NORMAL		= 'normal';
	const MODE_BACKGROUND	= 'background';
	const MODE_ACTION		= 'action';

	/**
	 * @var string
	 */
	private $_name;
	/**
	 * @var string
	 */
	private $_namespace;
	/**
	 * @var string
	 */
	private $_mode;
	/**
	 * @var Zend_Config
	 */
	private $_params;
	/**
	 * @var arrays
	 */
	private $_rawParams;

	/**
	 * @var cm_Controller_Action_Abstract|cm_Controller_Action_Cache
	 */
	private $_controller;

	/**
	 * @var bool
	 */
	protected $_disabled = false;

	/**
	 * @param string $name
	 * @param string $namespace
	 * @param string $mode
	 * @param array|Zend_Config $params
	 */
	public function __construct($name, $namespace, $mode, $params = null) {
		$this->_name = $name;
		$this->_namespace = $namespace;
		$this->_mode = $mode;
		
		if (is_object($params) && $params instanceof Zend_Config) {
			$this->_params = $params;
		} else {
			$this->_params = new Zend_Config((array) $params, true);
		}
		$this->_params->tagName	= $name;
		$this->_params->tagOwner= $this;
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key) {
		if (isset($this->{'_'. $key})) {
			return $this->{'_'. $key};
		}
		return null;
	}

	/**
	 * @param boolean $value
	 * @return boolean
	 */
	public function isDisabled($value = null) {
		if ($value !== null) {
			$this->_disabled = (bool) $value;
		}
		return $this->_disabled;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function setParam($key, $value) {
		$this->_params->$key = $value;
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function getParam($key) {
		return $this->_params->$key;
	}

	/**
	 * @throws cm_Controller_Exception
	 * @param cm_Controller_Request_Abstract $request
	 * @param cm_Controller_Response_Abstract $response
	 * @return cm_Controller_Action_Abstract|cm_Controller_Action_Cache
	 */
	public function getController($request = null, $response = null) {
		if ($this->_controller === null) {
			$className = str_replace('.', '_', $this->_namespace);
			$class = new ReflectionClass($className);
			if (!$class->isSubclassOf(new ReflectionClass('cm_Controller_Action_Abstract'))) {
				throw new cm_Controller_Exception("Вызов тэга {$this->_name}. ".
								"Класс должен быть наследником cm_Controller_Action_Abstract");
			}
			$this->_controller = $class->newInstance($this->_params, $request, $response);
		}
		if ($request !== null) {
			$this->_controller->setRequest($request);
		}
		if ($response !== null) {
			$this->_controller->setResponse($response);
		}
		return $this->_controller;
	}

	/**
	 * @param cm_Controller_Request_Abstract $request
	 * @param cm_Controller_Response_Abstract $response
	 * @return string
	 */
	public function run($request = null, $response = null) {
		if (!$this->isDisabled()) {
			$controller = $this->getController($request, $response);

			if ($controller instanceof cm_Controller_Action_Cache) {
				$controller->checkCache();
			}

			return $controller->run();
		}
		return '';
	}

/*
	public function checkExtraPath($request = null, $response = null) {
		return $this->getController($request, $response)->checkExtraPath();
	}
*/

	/**
	 * @return array
	 */
	public function toArray() {
		$params = clone($this->_params);
		return array(
			'name'		=> $this->_name,
			'namespace'	=> $this->_namespace,
			'mode'		=> $this->_mode,
			'params'	=> $params->toArray()
		);
	}

	/**
	 * @static
	 * @param array $array
	 * @return cm_Controller_Tag
	 * @throws cm_Controller_Exception
	 */
	public static function fromArray($array) {
		if (!isset($array['name']) || !isset($array['namespace'])) {
			throw new cm_Controller_Exception("Неверные параметры для десериализации тега");
		}
		
		if (!isset($array['mode'])) {
			$array['mode'] = self::MODE_NORMAL;
		}
		
		if (!isset($array['params'])) {
			$array['params'] = null;
		}
		
		return new cm_Controller_Tag($array['name'], $array['namespace'], $array['mode'], $array['params']);
	}
}