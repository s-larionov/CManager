<?php

class CManager_Controller_Route {
	/**
	 * @var string
	 */
	protected $_route = '';

	/**
	 * @var array
	 */
	protected $_vars = array();

	/**
	 * @var CManager_Controller_Router_Config_Page
	 */
	protected $_pageConfig = array();

	/**
	 * @var CManager_Controller_Route
	 */
	protected $_parent = null;

	/**
	 * @var CManager_Controller_Router_Abstract
	 */
	protected $_router = null;

	/**
	 * @param string $route
	 * @param CManager_Controller_Router_Config_RouteVar[] $varsRules
	 */
	public function __construct($route, array $varsRules = array()) {
		if (!is_array($varsRules)) {
			throw new CManager_Controller_Route_Exception('Route config must be array');
		}
		$this->_route = (string) $route;

		foreach($varsRules as $var) {
			$this->_vars[$var->name] = $var;
		}
	}

	/**
	 * @param CManager_Controller_Route $parent
	 */
	public function setParent(CManager_Controller_Route $parent) {
		$this->_parent = $parent;
	}

	/**
	 * @return CManager_Controller_Route
	 */
	public function getParent() {
		return $this->_parent;
	}

	/**
	 * @return bool
	 */
	public function hasParent() {
		return $this->_parent !== null;
	}

	/**
	 * Сгенерировать url на основе route и переданных параметров
	 *
	 * @param string[] $vars
	 * @param bool $addQueryParams
	 * @return string
	 */
	public function generateUrl(array $vars = array(), $addQueryParams = true) {
		if (!is_array($vars)) {
			throw new CManager_Controller_Route_Exception('First argument must be array of strings');
		}
		$url = $this->_route;
		foreach($this->_vars as $var) {
			if (isset($vars[$var->name])) {
				// проверяем, если это объект и на соответствие интерфейсу
				if (is_object($vars[$var->name]) && $vars[$var->name] instanceof CManager_Controller_Route_Var_Abstract) {
					$value = $vars[$var->name]->getRawValue();
				} else {
					$value = (string) $vars[$var->name];
				}
				// если переменная передана, то подставляем ее значение (предварительно провалидировав)
				if (!preg_match('~^' . str_replace('~', '\\~', $var->rule) . '$~', $value)) {
					throw new CManager_Controller_Route_Exception("Variable '{$var->name}' for route '{$this->getPageConfig()->name}' is not valid");
				}
				$url = str_replace('(:' . $var->name . ')', $value, $url);
				// удаляем переменную из списка. нужно что бы потом безпроблемно сгенерировать REQUEST_QUERY
				unset($vars[$var->name]);
			} else if ($var->default !== null) {
				// если переменная не передана, но у нее есть значение по-умолчанию, то подставляем его
				$url = str_replace('(:' . $var->name . ')', $var->default, $url);
 			} else {
				throw new CManager_Controller_Route_Exception("Not all required parameters for route '{$this->getPageConfig()->name}' passed");
			}
		}
		if ($addQueryParams && count($vars) > 0) {
			foreach($vars as $name => &$var) {
				$var = urlencode($name) . ($var !== ''? '=' . urlencode($var): '');
			}
			$url .= '?' . implode('&amp;', $vars);
		}
		return $url;
	}

	/**
	 * Проверка урла на совпадение с роутом.
	 * Возвращает список переменных (возможен пустой массив) в случае совпадения, иначе false
	 *
	 * @param string $url
	 * @return string[]|bool
	 * @throws CManager_Controller_Exception
	 */
	public function parse($url) {
		$url = trim($url, '/');
		// получаем RegExp для проверки url
		$rule = '^' . trim($this->_route, '/') . '$';

		$ruleVariables = array();
		if (preg_match_all('~\(:(\w+)\)~', $rule, $ruleMatches)) {
			foreach($ruleMatches[1] as $varName) {
				if (!isset($this->_vars[$varName])) {
					throw new CManager_Controller_Route_Exception("Variable {$varName} nod defined in config");
				}
				if (isset($ruleVariables[$varName])) {
					throw new CManager_Controller_Route_Exception("Variable {$varName} multiple defined");
				}
				$ruleVariables[$varName] = $this->_vars[$varName];
				$variableTpl = ':' . $varName;
				$isOptional = isset($ruleVariables[$varName]->default);
				$variableRule =  $ruleVariables[$varName]->rule;
				if ($isOptional) {
					$variableRule = "$variableRule|{$ruleVariables[$varName]->default}|";
				}
				$rule = str_replace($variableTpl, $variableRule, $rule);
			}
		}

		$rule = '~' . str_replace('~', '\\~', $rule) . '~';
		if (preg_match($rule, $url, $matches)) {
			// вытаскиваем значения переменных
			$variables = array();
			$i = 0;
			foreach($ruleVariables as $variableName => $variableConfig) {
				$variables[$variableName] = $this->_prepareVariable($matches[++$i], $variableConfig);
			}

			return $variables;
		}

		return false;
	}

	/**
	 * @param string $value
	 * @param string $rawValue
	 * @param CManager_Controller_Router_Config_RouteVar $config
	 * @return mixed
	 */
	protected function _prepareVariable($value, CManager_Controller_Router_Config_RouteVar $config) {
		$rawValue = $value;
		$value = urldecode($value);
		if (empty($value) && $config->default !== null) {
			$value = $config->default;
		}
		if ($config->pattern !== null) {
			if (preg_match('~^' . str_replace('~', '\\~', $config->pattern) . '$~', $value, $match)) {
				unset($match[0]);
				if (count($match) > 1) {
					$value = array_values($match);
				} else {
					$value = $match[1];
				}
			} else {
				$value = null;
			}
		}

		if ($config->explode !== null) {
			$value = explode($config->explode, $value);
		}
		$namespace = $config->namespace !== null? $config->namespace: 'string';
		switch(true) {
			case $namespace == 'int':
				if (is_array($value)) {
					foreach($value as &$val) {
						$val = (int) $val;
					}
				} else {
					$value = (int) $value;
				}
				break;
			case $namespace == 'float':
			case $namespace == 'double':
				$value = (double) $value;
				break;
			case $namespace == 'bool':
			case $namespace == 'boolean':
				$value = (bool) $value;
				break;
			case $namespace == 'string':
				break;
			case class_exists($namespace) && $value !== null:
				$value = new $namespace($value);
				$value->setRawValue($rawValue);
				if (!($value instanceof CManager_Controller_Route_Var_Abstract) || !$value->isValidRouteVariable()) {
					$value = null;
				}
				break;
			case $value === null:
				break;
			default:
				throw new CManager_Controller_Route_Exception("Namespace {$namespace} not defined");
		}

		return $value;
	}

	/**
	 * @return CManager_Controller_Router_Config_Page
	 */
	public function getPageConfig() {
		return $this->_pageConfig;
	}

	/**
	 * @param CManager_Controller_Router_Config_Page  $config
	 * @return CManager_Controller_Route
	 */
	public function setPageConfig(CManager_Controller_Router_Config_Page $config) {
		$this->_pageConfig = $config;
		return $this;
	}

	/**
	 * @param CManager_Controller_Router_Abstract $router
	 * @return CManager_Controller_Route
	 */
	public function setRouter(CManager_Controller_Router_Abstract $router) {
		$this->_router = $router;
		return $this;
	}

	/**
	 * @return CManager_Controller_Router_Abstract
	 */
	public function getRouter() {
		return $this->_router;
	}

	/**
	 * @return CManager_Controller_Router_Config_Permission[]
	 */
	public function getPermissions() {
		return $this->getPageConfig()->permission;
	}
}
