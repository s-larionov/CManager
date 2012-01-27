<?php

class CManager_Controller_Route {

	/**
	 * Название параметра в generateUrl для RequestTag
	 * @see CManager_Controller_Request_Http
	 */
	const REQUEST_TAG_SEPARATOR_PARAM = 'RT';

	/**
	 * @var string
	 */
	protected $_route = '';

	/**
	 * @var CManager_Controller_Router_Config_RouteVar[]
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
	 * @return boolean
	 */
	public function hasParent() {
		return $this->_parent !== null;
	}

	/**
	 * Сгенерировать url на основе route и переданных параметров
	 *
	 * @param array $vars
	 * @param boolean $addQueryParams
	 * @return string
	 */
	public function generateUrl(array $vars = array(), $addQueryParams = true) {
		if (!is_array($vars)) {
			throw new CManager_Controller_Route_Exception('First argument must be array of strings');
		}

		$requestTag = null;
		if (isset($vars[self::REQUEST_TAG_SEPARATOR_PARAM])) {
			$requestTag = (string) $vars[self::REQUEST_TAG_SEPARATOR_PARAM];
			unset($vars[self::REQUEST_TAG_SEPARATOR_PARAM]);
		}

		$url = $this->_route;
		foreach($this->_vars as $var) {
			if (isset($vars[$var->name])) {
				// проверяем, если это объект и на соответствие интерфейсу
				if (is_object($vars[$var->name]) && $vars[$var->name] instanceof CManager_Controller_Route_Var_Abstract) {
					$value = $vars[$var->name]->getRawValue();
				} else {
					$value = $vars[$var->name];
				}
			} else if ($var->default !== null) {
				// если переменная не передана, но у нее есть значение по-умолчанию, то подставляем его
				$value = $var->default;
 			} else {
				throw new CManager_Controller_Route_Exception("Not all required parameters for route '{$this->getPageConfig()->name}' passed");
			}

			// если у переменной указан @explode, то собираем массив
			if ($var->explode !== null && is_array($value)) {
				$value = implode($var->explode, $value);
			} else {
				// иначе преобразовываем на всякий случай в строку
				$value = (string) $value;
			}

			// если у переменной указан @pattern, то пытаемся ее собрать в "оригинал"
			if ($var->pattern !== null) {
				$value = $this->_fillPattern($var, $value);
			}

			// проверяем значение переменной
			$valueRegExp = '~^(?:' . str_replace('~', '\\~', $var->rule) . ')$~';
			if (!preg_match($valueRegExp, $value)) {
				throw new CManager_Controller_Route_Exception("Variable '{$var->name}' for route '{$this->getPageConfig()->name}' is not valid");
			}

			// подставляем в url
			$url = str_replace('(:' . $var->name . ')', $value, $url);
			// удаляем переменную из списка. нужно что бы потом безпроблемно сгенерировать REQUEST_QUERY
			unset($vars[$var->name]);
		}

		// добавляем requestTag если он указан
		if ($requestTag !== null) {
			$url .= $this->getRouter()->getRequest()->getRTSeparator() . $requestTag;
		}

		// добавляем QUERY_STRING
		if ($addQueryParams && count($vars) > 0) {
			foreach($vars as $name => &$var) {
				$var = urlencode($name) . ($var !== ''? '=' . urlencode($var): '');
			}
			$url .= '?' . implode('&amp;', $vars);
		}
		return $url;
	}

	/**
	 * @param CManager_Controller_Router_Config_RouteVar $var
	 * @param array|string $varValue
	 * @return string
	 * @throws CManager_Controller_Route_Exception
	 */
	protected function _fillPattern(CManager_Controller_Router_Config_RouteVar $var, $varValue) {
		$value		= preg_replace('~\\([^)]+\\)~', '(:var)', $var->pattern);
		$varValue	= is_array($varValue)? array_values($varValue): array($varValue);

		foreach($varValue as $patternValue) {
			if (($pos = strpos($value, '(:var)')) !== false) {
				$value = substr_replace($value, $patternValue, $pos, 6);
			} else {
				throw new CManager_Controller_Route_Exception("Variable '{$var->name}' for route '{$this->getPageConfig()->name}' is not valid");
			}
		}
		if (strpos($value, '(:var)') !== false) {
			throw new CManager_Controller_Route_Exception("Variable '{$var->name}' for route '{$this->getPageConfig()->name}' must be is array with length " . (count($varValue) + substr_count($value, '(:var)')) . " items");
		}

		return $value;
	}

	/**
	 * Проверка урла на совпадение с роутом.
	 * Возвращает список переменных (возможен пустой массив) в случае совпадения, иначе false
	 *
	 * @param string $url
	 * @return string[]|boolean
	 * @throws CManager_Controller_Exception
	 */
	public function parse($url) {
		$url = trim($url, '/');
		// получаем RegExp для проверки url
		$rule = '^' . trim($this->_route, '/') . '$';

		// определяем $ruleVariables для правильного порядка (как указано в route/@url)
		// и для корректного извлечения значений из $url
		$ruleVariables = /** @var CManager_Controller_Router_Config_RouteVar[] $ruleVariables */ array();
		if (preg_match_all('~\(:(\w+)\)~', $rule, $ruleMatches)) {
			foreach($ruleMatches[1] as $varName) {
				if (!isset($this->_vars[$varName])) {
					throw new CManager_Controller_Route_Exception("Variable {$varName} nod defined in config");
				}
				if (isset($ruleVariables[$varName])) {
					throw new CManager_Controller_Route_Exception("Variable {$varName} multiple defined");
				}

				$ruleVariables[$varName]	= $this->_vars[$varName];
				$variableTpl				= ':' . $varName;
				$isOptional					= $ruleVariables[$varName]->default !== null;
				$variableRule				= $ruleVariables[$varName]->rule;

				if ($isOptional) {
					$default = $ruleVariables[$varName]->pattern !== null
							? $this->_fillPattern($ruleVariables[$varName], $ruleVariables[$varName]->default)
							: $ruleVariables[$varName]->default;
					$variableRule = "$variableRule|{$default}|";
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
	 * @param CManager_Controller_Router_Config_RouteVar $config
	 * @return mixed
	 */
	protected function _prepareVariable($value, CManager_Controller_Router_Config_RouteVar $config) {
		$value = urldecode($value);
		if (empty($value) && $config->default !== null) {
			$value = $config->pattern !== null
					? $this->_fillPattern($config, $config->default)
					: $config->default;
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
				$rawValue = $value;
				$value = /** @var CManager_Controller_Route_Var_Abstract $value */ CManager_Helper_Object::newInstance(
					$namespace,
					'CManager_Controller_Route_Var_Abstract',
					array($value)
				);
				$value->setRawValue($rawValue);
				if (!$value->isValidRouteVariable()) {
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
