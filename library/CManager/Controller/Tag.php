<?php

class CManager_Controller_Tag {
	const MODE_NORMAL		= 'normal';
	const MODE_BACKGROUND	= 'background';

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
	 * @var CManager_Controller_Router_Config_TagParam[]
	 */
	private $_params = array();

	/**
	 * @var CManager_Controller_Action_Abstract|CManager_Controller_Action_Cache
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
	 * @param CManager_Controller_Router_Config_TagParam[] $params
	 */
	public function __construct($name, $namespace, $mode, array $params = array()) {
		$this->_name		= $name;
		$this->_namespace	= $namespace;
		$this->_mode		= $mode;
		$this->_setParams($params);
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
	 * @return mixed
	 */
	public function __get($key) {
		if (isset($this->{'_'. $key})) {
			return $this->{'_'. $key};
		}
		return null;
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 * @return string|array
	 */
	public function getParam($key, $default = null) {
		if (!array_key_exists($key, $this->_params)) {
			return $default;
		}
		return $this->_params[$key];
	}

	/**
	 * @param CManager_Controller_Router_Config_TagParam|array $params
	 */
	protected function _setParams(array $params) {
		foreach($params as $name => $param) {
			if ($param instanceof CManager_Controller_Router_Config_TagParam) {
				$name	= $param->name;
				$value	= $this->_prepareParam($param);
			} else {
				$value	= $param;
			}
			if (array_key_exists($name, $this->_params)) {
				if (!is_array($this->_params[$name]) || !array_key_exists(0, $this->_params[$name])) {
					$this->_params[$name] = array($this->_params[$name]);
				}
				$this->_params[$name][] = $value;
			} else {
				$this->_params[$name] = $value;
			}
		}
	}

	/**
	 * @param CManager_Controller_Router_Config_TagParam $param
	 * @return array|string
	 */
	protected function _prepareParam(CManager_Controller_Router_Config_TagParam $param) {
		$result = array();
		if (count($param->param) == 0) {
			return $param->value;
		}
		foreach($param->param as $subParam) {
			if (count($subParam->param) > 0) {
				$value = $this->_prepareParam($subParam);
			} else {
				$value = $subParam->value;
			}
			if (array_key_exists($subParam->name, $result)) {
				if (!is_array($result[$subParam->name]) || !array_key_exists(0, $result[$subParam->name])) {
					$result[$subParam->name] = array($result[$subParam->name]);
				}
				$result[$subParam->name][] = $value;
			} else {
				$result[$subParam->name] = $value;
			}
		}
		return $result;
	}
	
	/**
	 * @throws CManager_Controller_Exception
	 * @param CManager_Controller_Request_Abstract $request
	 * @param CManager_Controller_Response_Abstract $response
	 * @return CManager_Controller_Action_Abstract|CManager_Controller_Action_Cache
	 */
	public function getController($request = null, $response = null) {
		if ($this->_controller === null) {
			$className = $this->_namespace;
			$class = new ReflectionClass($className);
			if (!$class->isSubclassOf(new ReflectionClass('CManager_Controller_Action_Abstract'))) {
				throw new CManager_Controller_Exception("Вызов тэга {$this->_name}. ".
								"Класс должен быть наследником CManager_Controller_Action_Abstract");
			}
			$this->_controller = $class->newInstance($this, $request, $response);
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
	 * @param CManager_Controller_Request_Abstract $request
	 * @param CManager_Controller_Response_Abstract $response
	 * @return string
	 */
	public function run($request = null, $response = null) {
		if (!$this->isDisabled()) {
			$controller = $this->getController($request, $response);
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
	 * @return CManager_Controller_Tag
	 * @throws CManager_Controller_Exception
	 */
	public static function fromArray($array) {
		if (!isset($array['name']) || !isset($array['namespace'])) {
			throw new CManager_Controller_Exception("Неверные параметры для десериализации тега");
		}
		
		if (!isset($array['mode'])) {
			$array['mode'] = self::MODE_NORMAL;
		}
		
		if (!isset($array['params'])) {
			$array['params'] = null;
		}
		
		return new CManager_Controller_Tag($array['name'], $array['namespace'], $array['mode'], $array['params']);
	}
}