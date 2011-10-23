<?php

/**
 *	@see Zend_Controller_Request_HTTP
 */
class cm_Controller_Request_HTTP extends cm_Controller_Request_Abstract {
	/**
	 *	пример разбора урла:
	 *	http://example.com/public/news/2008/05/15/1:sendComment?page=1
	 *		rawRequestUri:
	 *			/public/news/2008/05/15/1:sendComment?page=1
	 *			
	 *		requestUri:
	 *			/public/news/2008/05/15/1?page=1
	 *			
	 *		path:
	 *			/public/news/2008/05/15/1
	 *			
	 *		realPath:
	 *			/public/news/
	 *		
	 *		extraPath:
	 *			/2008/05/15/1
	 *		
	 *		queryString:
	 *			page=1
	 *		
	 *		requestTag:
	 *			sendComment
	 */

	/**
	 * REQUEST_URI
	 * @var string
	 */
	protected $_rawRequestUri;

	/**
	 * @var string
	 */
	protected $_requestUri;

	/**
	 * @var string
	 */
	protected $_referer;

	/**
	 * Остаток физически не существующего пути (не заведенного в системе). Значение этого
	 * свойства должен устанавливать роутер.
	 * @todo: Теоретически должно работать так (еще не реализовано):
	 *   Если этот остаток существует, система должна проверить к какому модулю этот
	 *   остаток относится. Если такого модуля на текущем урле ($_realPath) не нашлось
	 *   сервер должен отдать заголовок с ошибкой 404.
	 * @see cm_Controller_Front
	 * @see cm_Controller_Router_Abstract
	 * @var string
	 */
	protected $_extraPath;

	/**
	 * Путь, который не включает в себя $_extraPath, $_requestTag и строку запроса.
	 * @see cm_Controller_Router_Abstract
	 * @var string
	 */
	protected $_realPath;

	/**
	 *	Используется для вызова конкретного тэга текущего урла, без использования
	 *	разметки т.п. вещей. Т.е. результатом работы приложения будет вызов этого тэга.
	 *	
	 *	@see cm_Controller_Front
	 *	@var string
	 */
	protected $_requestTag;

	/**
	 *	Знак разделяющий $_requestTag и $_requestUri;
	 *	
	 *	@see cm_Controller_Front
	 *	@var string
	 */
	protected $_RTSeparator = 'call~';

	/**
	 *	@var string
	 */
	protected $_queryString;

	/**
	 * @param string $uri
	 * @param string $referer
	 */
	public function __construct($uri = null, $referer = null) {
		$this->setRawRequestUri($uri);
		$this->setReferer($referer);
	}

	/**
	 * Set the REQUEST_URI on which the instance operates
	 *
	 * If no request URI is passed, uses the value in $_SERVER['REQUEST_URI'],
	 * $_SERVER['HTTP_X_REWRITE_URL'], or $_SERVER['ORIG_PATH_INFO'] + $_SERVER['QUERY_STRING'].
	 *
	 * @param string $requestUri
	 * @return void
	 */
	public function setRawRequestUri($requestUri = null) {
		if ($requestUri === null) {
			if (isset($_SERVER['HTTP_X_REWRITE_URL'])) { // check this first so IIS will catch
				$requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
			} elseif (isset($_SERVER['REQUEST_URI'])) {
				$requestUri = $_SERVER['REQUEST_URI'];
			} elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0, PHP as CGI
				$requestUri = $_SERVER['ORIG_PATH_INFO'];
			} else {
				return;
			}
		} elseif (!is_string($requestUri)) {
			return;
		}

		$queryString = $_SERVER['QUERY_STRING'];
		if (false !== ($pos = strpos($requestUri, '?'))) {
			$queryString	= substr($requestUri, $pos + 1);
			$requestUri		= substr($requestUri, 0, $pos);
		}
		
		$this->_queryString		= $queryString;
		$this->_rawRequestUri	= $requestUri;
	}
	/**
	 * Set the HTTP_ on which the instance operates
	 * @param string $referer
	 * @return void
	 */
	public function setReferer($referer = null) {
		if ($referer === null) {
			$referer = $this->getServer('HTTP_REFERER');
		} elseif (!is_string($referer)) {
			return;
		}

		$info = parse_url($referer);
		if (isset($info['host']) && $this->get('HTTP_HOST') == $info['host']) {
			$referer = (isset($info['path'])? $info['path']: '').
				(isset($info['query'])? '?'.$info['query']: '').
				(isset($info['fragment'])? '#'.$info['fragment']: '');
		}

		$this->_referer = $referer;
	}

	/**
	 * @param string $uri
	 */
	public function setRequestUri($uri) {
		return $this->setRawRequestUri($uri);
	}

	/**
	 * @param bool $withSign
	 * @return string
	 */
	public function getQueryString($withSign = false) {
		return ($withSign && $this->_queryString ? '?' : '') . $this->_queryString;
	}

	/**
	 * Returns the REQUEST_URI taking into account
	 * platform differences between Apache and IIS
	 *
	 * @param $withQueryString boolean
	 * @return string
	 */
	public function getRawRequestUri($withQueryString = true) {
		if (empty($this->_rawRequestUri)) {
			$this->setRawRequestUri();
		}
		return $this->_rawRequestUri .
				($withQueryString && $this->_queryString? '?' . $this->_queryString: '');
	}

	/**
	 * @param bool $withQueryString
	 * @param string $insertRT
	 * @return string
	 */
	public function getRequestUri($withQueryString = true, $insertRT = null) {
		if (empty($this->_requestUri)) {
			$requestUri = $this->getRawRequestUri(false);

			// выдираем $_requestTag
			$sep = $this->getRTSeparator();
			if (false !== strpos($requestUri, $sep)) {
				$requestUri = preg_replace("~". preg_quote($sep, '~') ."[^\/\s\?]+$~", '', $requestUri);
			}
			$this->_requestUri = $requestUri;
		}

		if ($insertRT) {
			$insertRT = (substr($this->_requestUri, -1) != '/' ? '/' : '') . $this->getRTSeparator() . $insertRT;
		}

		return $this->_requestUri . $insertRT . ($withQueryString === true &&
											$this->_queryString ? '?'. $this->_queryString : '');
	}
	
	/** 
	 * @return string
	 */
	function getRTSeparator() {
		return $this->_RTSeparator;
	}
	
	/**
	 * @return string
	 */
	function getReferer() {
		return $this->_referer;
	}

	/**
	 * @param string $separator
	 * @return void
	 */
	function setRTSeparator($separator) {
		$this->_RTSeparator = (string) $separator;
	}

	/**
	 * @return string
	 */
	public function getRequestTag() {
		if (empty($this->_requestTag)) {
			$requestUri = $this->getRawRequestUri(false);
			$sep = $this->getRTSeparator();
			
			if (false !== strpos($requestUri, $sep)) {
				preg_match("~". preg_quote($sep, '~') ."([a-zA-Z0-9\\-_]+)$~", $requestUri, $m);
				$this->_requestTag = $m[1];
			}
		}
		return $this->_requestTag;
	}

	/**
	 * @return bool
	 */
	public function hasRequestTag() {
		$tag = $this->getRequestTag();
		return !empty($tag);
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return $this->getRequestUri(false);
	}

	/**
	 * @return string
	 */
	public function getRealPath() {
		if (empty($this->_realPath)) {
			if (count($this->getExtraPath())) {
				$xtrapath = implode('/', $this->getExtraPath());
				$path = trim($this->getPath(), '/');
				$pos = strpos($path, $xtrapath);
				$rpath = substr($path, 0, $pos - 1);

				$this->_realPath = !$rpath? '/': '/'. $rpath .'/';
			} else {
				$this->_realPath = $this->getPath();
			}
		}
		return $this->_realPath;
	}

	/**
	 * @param string
	 * @return void
	 */
	public function setExtraPath($path) {
		// @todo: выполняем провеки
		$this->_extraPath = $path;
	}

	/**
	 * @return string
	 */
	public function getExtraPath() {
		return $this->_extraPath;
	}

	/**
	 * @return string
	 */
	public function hasExtraPath() {
		return !empty($this->_extraPath);
	}

	/**
	 * Access values contained in the superglobals as public members
	 * Order of precedence: 1. GET, 2. POST, 3. COOKIE, 4. SERVER, 5. ENV
	 *
	 * @see http://msdn.microsoft.com/en-us/library/system.web.httprequest.item.aspx
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key) {
		switch (true) {
			/*case isset($this->_params[$key]):
				return $this->_params[$key];*/
			case isset($_GET[$key]):
				return $_GET[$key];
			case isset($_POST[$key]):
				return $_POST[$key];
			case isset($_COOKIE[$key]):
				return $_COOKIE[$key];
			case ($key == 'REQUEST_URI'):
				return $this->getRequestUri();
/*			case ($key == 'PATH_INFO'):
				return $this->getPathInfo();*/
			case isset($_SERVER[$key]):
				return $_SERVER[$key];
			case isset($_ENV[$key]):
				return $_ENV[$key];
			default:
				return null;
		}
	}

	/**
	 * Alias to __get
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		return $this->__get($key);
	}

	/**
	 * Set values
	 *
	 * In order to follow {@link __get()}, which operates on a number of
	 * superglobals, setting values through overloading is not allowed and will
	 * raise an exception. Use setParam() instead.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 * @throws cm_Controller_Request_Exception
	 */
	public function __set($key, $value) {
		throw new cm_Controller_Request_Exception('Setting values in superglobals not allowed; please use setParam()');
	}

	/**
	 * Alias to __set()
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function set($key, $value) {
		$this->__set($key, $value);
	}

	/**
	 * Check to see if a property is set
	 * @param string $key
	 * @return boolean
	 */
	public function __isset($key) {
		switch (true) {
			case isset($_GET[$key]):
				return true;
			case isset($_POST[$key]):
				return true;
			case isset($_COOKIE[$key]):
				return true;
			case isset($_SERVER[$key]):
				return true;
			case isset($_ENV[$key]):
				return true;
			default:
				return false;
		}
	}
	
	/**
	 * Alias to __isset()
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function has($key) {
		return $this->__isset($key);
	}

	/**
	 * Retrieve a member of the $_GET superglobal
	 *
	 * If no $key is passed, returns the entire $_GET array.
	 *
	 * @todo How to retrieve from nested arrays
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @return mixed Returns null if key does not exist
	 */
	public function getQuery($key = null, $default = null) {
		if (null === $key) {
			return $_GET;
		}
		return (isset($_GET[$key]))? $_GET[$key]: $default;
	}

	/**
	 * Retrieve a member of the $_POST superglobal
	 *
	 * If no $key is passed, returns the entire $_POST array.
	 *
	 * @todo How to retrieve from nested arrays
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @return mixed Returns null if key does not exist
	 */
	public function getPost($key = null, $default = null) {
		if (null === $key) {
			return $_POST;
		}
		return (isset($_POST[$key]))? $_POST[$key]: $default;
	}

	/**
	 * Retrieve a member of the $_COOKIE superglobal
	 *
	 * If no $key is passed, returns the entire $_COOKIE array.
	 *
	 * @todo How to retrieve from nested arrays
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @return mixed Returns null if key does not exist
	 */
	public function getCookie($key = null, $default = null) {
		if (null === $key) {
			return $_COOKIE;
		}
		return (isset($_COOKIE[$key]))? $_COOKIE[$key]: $default;
	}

	/**
	 * Retrieve a member of the $_SERVER superglobal
	 *
	 * If no $key is passed, returns the entire $_COOKIE array.
	 *
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @return mixed Returns null if key does not exist
	 */
	public function getServer($key = null, $default = null) {
		if (null === $key) {
			return $_SERVER;
		}
		return (isset($_SERVER[$key]))? $_SERVER[$key]: $default;
	}

	/**
	 * Retrieve a member of the $_ENV superglobal
	 *
	 * If no $key is passed, returns the entire $_COOKIE array.
	 *
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @return mixed Returns null if key does not exist
	 */
	public function getEnv($key = null, $default = null) {
		if (null === $key) {
			return $_ENV;
		}
		return (isset($_ENV[$key])) ? $_ENV[$key] : $default;
	}

	/**
	 * Return the method by which the request was made
	 *
	 * @return string
	 */
	public function getMethod() {
		return strtoupper($this->getServer('REQUEST_METHOD'));
	}

	/**
	 * Was the request made by POST?
	 *
	 * @return boolean
	 */
	public function isPost() {
		return ('POST' == $this->getMethod());
	}

	/**
	 * Return the value of the given HTTP header. Pass the header name as the
	 * plain, HTTP-specified header name. Ex.: Ask for 'Accept' to get the
	 * Accept header, 'Accept-Encoding' to get the Accept-Encoding header.
	 *
	 * @param string HTTP header name
	 * @return string|false HTTP header value, or false if not found
	 * @throws cm_Controller_Request_Exception
	 */
	public function getHeader($header) {
		if (empty($header)) {
			throw new cm_Controller_Request_Exception('An HTTP header name is required');
		}

		// Try to get it from the $_SERVER array first
		$temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
		if (!empty($_SERVER[$temp])) {
			return $_SERVER[$temp];
		}

		// This seems to be the only way to get the Authorization header on
		// Apache
		if (function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
			if (!empty($headers[$header])) {
				return $headers[$header];
			}
		}
		return false;
	}

	/**
	 * Is the request a Javascript XMLHttpRequest?
	 *
	 * Should work with Prototype/Script.aculo.us, possibly others.
	 *
	 * @return boolean
	 */
	public function isXmlHttpRequest() {
		return ($this->getHeader('X_REQUESTED_WITH') == 'XMLHttpRequest');
	}
}
