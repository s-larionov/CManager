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
	 * @param string $route
	 * @param array $varsRules
	 * @param array $pageConfig
	 */
	public function __construct($route, $varsRules = array(), $pageConfig = array()) {
		if (!is_array($varsRules)) {
			throw new cm_Controller_Route_Exception('Route config must be array');
		}
		if (!is_array($pageConfig)) {
			throw new cm_Controller_Route_Exception('Page config must be array');
		}

		$this->_route = (string) $route;
		foreach($varsRules as $var) {
			if (!isset($var['name']) || isset($var['rule'])) {
				throw new cm_Controller_Route_Exception('Attributes @name and @rule are required');
			}
			$this->_vars[] = $var;
		}
		$this->_pageConfig = $pageConfig;
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
	public function generateUrl($vars = array(), $quoteQueryParams = true) {
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
		// получаем RegExp для проверки url
		$rule = '~^' . str_replace('~', '\\~', trim($this->_route, '/')) . '$~';

		$urlChunks	= explode('/', trim($url, '/'));
		$ruleChunks	= explode('/', $rule);

		foreach($ruleChunks as $chunk) {

		}



		// в случае если кол-во элементов в правиле
		// не соответсвует кол-ву эл-ов в url,
		// то сразу возвращаем несовпадение (false)
		// Учитываем необязательные параметры.
		// @todo: сделать обработку @multiple
		$countUrlChunks		= count($urlChunks);
		$countRuleChunks	= count($ruleChunks);
		$optionalVars		= array();
		$countOptionalVars	= 0;
		foreach($this->_vars as $var) {
			if (isset($var['default'])) {
				$optionalVars[] = $var;
			}
/*			if (isset($var['multiple'])) {
				$countOptionalVars += (int) $var['multiple'];
			}*/
		}
		$countOptionalVars	+= count($optionalVars);
		$countEmptyVars		= $countRuleChunks - $countUrlChunks;

		if ($countEmptyVars > $countOptionalVars) {
			return false;
		}

		// добавляем значения в urlChunks для неуказанных элементов
		if ($countEmptyVars > 0 && $countOptionalVars > 0) {
			for ($i = $countOptionalVars - $countEmptyVars; $i < $countOptionalVars; $i++) {
				$urlChunks[] = (string) $optionalVars[$i]['default'];
			}
		}

		// @todo либо проверять порядок переменных в настройках роута, либо переделать так, что бы порядок не был важен
		// генерируем регексп
		foreach($this->_vars as $var) {
			$isOptional	= isset($var['default']);
			$varName	= '$' . $var['name'];
			if (strpos($rule, $varName) === false) {
				throw new cm_Controller_Route_Exception("Variable {$varName} not fall in route rule, but exists in route's config");
			}
			$rule = str_replace($varName,
								$var['rule'] . ($isOptional? '|' . $var['default']: ''),
								$rule);
		}

		$varsValues = array();
		// проверяем
		if (preg_match($rule, implode('/', $urlChunks), $matches)) {
			// убираем первое совпадение
			array_shift($matches);
			// вытаскиваем значения переменных
			foreach($matches as $i => $match) {
				$varsValues[(string) $this->_vars[$i]['name']] = $match;
			}
			return $varsValues;
		}

		return false;
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
}