<?php

class cm_Controller_Route {
	/**
	 * @var string
	 */
	protected $_route = '';

	/**
	 * @var array
	 */
	protected $_vars = array();

	/**
	 * @var array
	 */
	protected $_pageConfig = array();

	/**
	 * @var cm_Controller_Route
	 */
	protected $_parent = null;

	/**
	 * @var cm_Controller_Router_Abstract
	 */
	protected $_router = null;

	/**
	 * @param string $route
	 * @param array $varsRules
	 */
	public function __construct($route, array $varsRules = array()) {
		if (!is_array($varsRules)) {
			throw new cm_Controller_Route_Exception('Route config must be array');
		}
		$this->_route = (string) $route;

		foreach($varsRules as $var) {
			if (!isset($var['name']) || !isset($var['rule'])) {
				throw new cm_Controller_Route_Exception('Attributes @name and @rule are required');
			}
			$varName = $var['name'];
			unset($var['name']);
			$this->_vars[$varName] = $var;
		}
	}

	/**
	 * @param cm_Controller_Route $parent
	 */
	public function setParent(cm_Controller_Route $parent) {
		$this->_parent = $parent;
	}

	/**
	 * @return cm_Controller_Route
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
	 * @param bool $quoteQueryParams
	 * @return string
	 */
	public function generateUrl(array $vars = array(), $quoteQueryParams = true) {
		if (!is_array($vars)) {
			throw new cm_Controller_Route_Exception('First argument must be array of strings');
		}
		$url = $this->_route;
		foreach($this->_vars as $var) {
			if (isset($vars[$var['name']])) {
				// если переменная передана, то подставляем ее значение
				$url = str_replace('$' . $var['name'], $vars[$var['name']], $url);
				// удаляем переменную из списка. нужно что бы потом безпроблемно сгенерировать REQUEST_QUERY
				unset($vars[$var['name']]);
			} else if (isset($var['default'])) {
				// если переменная не передана, но у нее есть значение по-умолчанию, то подставляем его
				$url = str_replace('$' . $var['name'], $vars[$var['name']], $url);
 			} else {
				throw new cm_Controller_Route_Exception('Not all required parameters passed');
			}
		}
		if ($quoteQueryParams) {
			foreach($vars as &$var) {
				$var = urlencode($var);
			}
		}
		if (count($vars) > 0) {
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
	 * @throws cm_Controller_Exception
	 */
	public function parse($url) {
		$url = trim($url, '/');
		// получаем RegExp для проверки url
		$rule = '~^' . str_replace('~', '\\~', trim($this->_route, '/')) . '$~';

//		$countUrlChunks		= count(explode('/', $url));
//		$countRuleChunks	= count(explode('/', trim($this->_route, '/')));
//		$countOptionalVars	= 0;

		$ruleVariables = array();
		if (preg_match_all('~\(:(\w+)\)~', $rule, $ruleMatches)) {
			foreach($ruleMatches[1] as $varName) {
				if (!isset($this->_vars[$varName])) {
					throw new cm_Controller_Route_Exception("Variable {$varName} nod defined in config");
				}
				if (isset($ruleVariables[$varName])) {
					throw new cm_Controller_Route_Exception("Variable {$varName} multiple defined");
				}
				$ruleVariables[$varName] = $this->_vars[$varName];
				$variableTpl = ':' . $varName;
				$isOptional = isset($var['default']);
				$variableRule = $ruleVariables[$varName]['rule'] . ($isOptional ? "|{$var['default']}|": '');
				$rule = str_replace($variableTpl, $variableRule, $rule);

//				if ($isOptional) {
//					$countOptionalVars++;
//				}
			}
		}

//		$countEmptyVars		= $countRuleChunks - $countUrlChunks;

//		if ($countEmptyVars > $countOptionalVars) {
//			return false;
//		}

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
	 * @param array $config
	 * @return mixed
	 */
	protected function _prepareVariable($value, array $config) {
		if (empty($value) && isset($config['default'])) {
			$value = $config['default'];
		}
		$value = urldecode($value);
		if (isset($config['explode'])) {
			$value = explode($config['explode'], $value);
		}
		return $value;
	}

	/**
	 * @param string $param
	 * @return string[]|string
	 */
	public function getPageConfig($param = null) {
		if ($param === null) {
			return $this->_pageConfig;
		} else if (isset($this->_pageConfig[(string) $param])) {
			return $this->_pageConfig[(string) $param];
		}
		return null;
	}

	/**
	 * @param array  $config
	 * @return cm_Controller_Route
	 */
	public function setPageConfig(array $config) {
		$this->_pageConfig = $config;
		return $this;
	}

	/**
	 * @param cm_Controller_Router_Abstract $router
	 * @return cm_Controller_Route
	 */
	public function setRouter(cm_Controller_Router_Abstract $router) {
		$this->_router = $router;
		return $this;
	}

	/**
	 * @return cm_Controller_Router_Abstract
	 */
	public function getRouter() {
		return $this->_router;
	}
}