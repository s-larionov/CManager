<?php

abstract class CManager_Controller_Response_Abstract {
	/**
	 * Body content
	 * @var array
	 */
	protected $_body = array();

	/**
	 * Exception stack
	 * @var Exception
	 */
	protected $_exceptions = array();

	/**
	 * Array of headers. Each header is an array with keys 'name' and 'value'
	 * @var array
	 */
	protected $_headers = array();

	/**
	 * Array of raw headers. Each header is a single string, the entire header to emit
	 * @var array
	 */
	protected $_headersRaw = array();

	/**
	 * HTTP response code to use in headers
	 * @var int
	 */
	protected $_httpResponseCode = 200;

	/**
	 * Flag; is this response a redirect?
	 * @var boolean
	 */
	protected $_isRedirect = false;

	/**
	 * Whether or not to render exceptions; off by default
	 * @var boolean
	 */
	protected $_renderExceptions = true;

	/**
	 * Flag; if true, when header operations are called after headers have been
	 * sent, an exception will be raised; otherwise, processing will continue
	 * as normal. Defaults to true.
	 *
	 * @see canSendHeaders()
	 * @var boolean
	 */
	public $headersSentThrowsException = true;

	private static $instance = false;

	public function getInstance() {
		if (self::$instance === false) {
			self::$instance = new CManager_Controller_Response_Http;
		}
		return self::$instance;
	}

	/**
	 * Set a header
	 *
	 * If $replace is true, replaces any headers already defined with that
	 * $name.
	 *
	 * @param string $name
	 * @param string $value
	 * @param boolean $replace
	 * @return Zend_Controller_Response_Abstract
	 */
	public function setHeader($name, $value, $replace = false) {
		$this->canSendHeaders(true);
		$name	= (string) $name;
		$value	= (string) $value;

		if ($replace) {
			foreach ($this->_headers as $key => $header) {
				if ($name == $header['name']) {
					unset($this->_headers[$key]);
				}
			}
		}

		$this->_headers[] = array(
			'name'		=> $name,
			'value'		=> $value,
			'replace'	=> $replace
		);

		return $this;
	}

	/**
	 * Set redirect URL
	 *
	 * Sets Location header and response code. Forces replacement of any prior
	 * redirects.
	 *
	 * @param string $url
	 * @param int $code
	 * @return CManager_Controller_Response_Abstract
	 */
	public function setRedirect($url, $code = 302) {
		$this->canSendHeaders(true);
		$this->setHeader('Location', $url, true)->setHttpResponseCode($code);
		return $this;
	}

	/**
	 * Is this a redirect?
	 *
	 * @return boolean
	 */
	public function isRedirect() {
		return $this->_isRedirect;
	}

	/**
	 * Return array of headers; see {@link $_headers} for format
	 *
	 * @return array
	 */
	public function getHeaders() {
		return $this->_headers;
	}

	/**
	 * Clear headers
	 *
	 * @return Zend_Controller_Response_Abstract
	 */
	public function clearHeaders() {
		$this->_headers = array();
		return $this;
	}

	/**
	 * Set raw HTTP header
	 *
	 * Allows setting non key => value headers, such as status codes
	 *
	 * @param string $value
	 * @return Zend_Controller_Response_Abstract
	 */
	public function setRawHeader($value) {
		$this->canSendHeaders(true);
		if ('Location' == substr($value, 0, 8)) {
			$this->_isRedirect = true;
		}
		$this->_headersRaw[] = (string) $value;
		return $this;
	}

	/**
	 * Retrieve all {@link setRawHeader() raw HTTP headers}
	 *
	 * @return array
	 */
	public function getRawHeaders() {
		return $this->_headersRaw;
	}

	/**
	 * Clear all {@link setRawHeader() raw HTTP headers}
	 *
	 * @return Zend_Controller_Response_Abstract
	 */
	public function clearRawHeaders() {
		$this->_headersRaw = array();
		return $this;
	}

	/**
	 * Clear all headers, normal and raw
	 *
	 * @return Zend_Controller_Response_Abstract
	 */
	public function clearAllHeaders() {
		return $this->clearHeaders()->clearRawHeaders();
	}

	/**
	 * Set HTTP response code to use with headers
	 *
	 * @param int $code
	 * @return CManager_Controller_Response_Abstract
	 */
	public function setHttpResponseCode($code) {
		if (!is_int($code) || (100 > $code) || (599 < $code)) {
			throw new CManager_Controller_Response_Exception('Invalid HTTP response code');
		}

		$this->_isRedirect = (300 <= $code) && (307 >= $code);

		$this->_httpResponseCode = $code;
		return $this;
	}

	/**
	 * Retrieve HTTP response code
	 *
	 * @return int
	 */
	public function getHttpResponseCode() {
		return $this->_httpResponseCode;
	}

	/**
	 * Can we send headers?
	 *
	 * @param boolean $throw Whether or not to throw an exception if headers have been sent; defaults to false
	 * @return boolean
	 * @throws cmController_Response_Exception
	 */
	public function canSendHeaders($throw = false) {
		$ok = headers_sent($file, $line);
		if ($ok && $throw && $this->headersSentThrowsException) {
			throw new CManager_Controller_Response_Exception("cannot_send_headers. $file ($line)", $file, $line);
		}
		return !$ok;
	}

	/**
	 * Send all headers
	 *
	 * Sends any headers specified. If an {@link setHttpResponseCode() HTTP response code}
	 * has been specified, it is sent with the first header.
	 *
	 * @param bool $exit
	 * @return Zend_Controller_Response_Abstract
	 */
	public function sendHeaders($exit = false) {
		// Only check if we can send headers if we have headers to send
		if (count($this->_headersRaw) || count($this->_headers) || (200 != $this->_httpResponseCode)) {
			$this->canSendHeaders(true);
		} elseif (200 == $this->_httpResponseCode) {
			// Haven't changed the response code, and we have no headers
			return $this;
		}

		$httpCodeSent = false;

		foreach ($this->_headersRaw as $header) {
			if (!$httpCodeSent && $this->_httpResponseCode) {
				header($header, true, $this->_httpResponseCode);
				$httpCodeSent = true;
			} else {
				header($header);
			}
		}

		foreach ($this->_headers as $header) {
			if (!$httpCodeSent && $this->_httpResponseCode) {
				header($header['name'] . ': ' . $header['value'], $header['replace'], $this->_httpResponseCode);
				$httpCodeSent = true;
			} else {
				header($header['name'] . ': ' . $header['value'], $header['replace']);
			}
		}

		if (!$httpCodeSent) {
			header('HTTP/1.1 ' . $this->_httpResponseCode);
			$httpCodeSent = true;
		}

		if ($exit) {
			exit();
		}

		return $this;
	}

	/**
	 * Set body content
	 *
	 * If $name is not passed, or is not a string, resets the entire body and
	 * sets the 'default' key to $content.
	 *
	 * If $name is a string, sets the named segment in the body array to
	 * $content.
	 *
	 * @param string $content
	 * @param string $name
	 * @return Zend_Controller_Response_Abstract
	 */
	public function setBody($content, $name = 'default') {
		if ((null === $name) || !is_string($name)) {
			$this->_body = array('default' => (string) $content);
		} else {
			$this->_body[$name] = (string) $content;
		}

		return $this;
	}

	/**
	 * Echo the body segments
	 *
	 * @return void
	 */
	public function outputBody() {
		foreach ($this->_body as $content) {
			echo $content;
		}
	}

	/**
	 * Register an exception with the response
	 *
	 * @param Exception $e
	 * @return Zend_Controller_Response_Abstract
	 */
	public function setException(Exception $e) {
		$this->_exceptions[] = $e;
		return $this;
	}

	/**
	 * Retrieve the exception stack
	 *
	 * @return array
	 */
	public function getException() {
		return $this->_exceptions;
	}

	/**
	 * Has an exception been registered with the response?
	 *
	 * @return boolean
	 */
	public function isException() {
		return !empty($this->_exceptions);
	}

	/**
	 * Does the response object contain an exception of a given type?
	 *
	 * @param  string $type
	 * @return boolean
	 */
	public function hasExceptionOfType($type) {
		foreach ($this->_exceptions as $e) {
			if ($e instanceof $type) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Does the response object contain an exception with a given message?
	 *
	 * @param  string $message
	 * @return boolean
	 */
	public function hasExceptionOfMessage($message) {
		foreach ($this->_exceptions as $e) {
			if ($message == $e->getMessage()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Does the response object contain an exception with a given code?
	 *
	 * @param  int $code
	 * @return boolean
	 */
	public function hasExceptionOfCode($code) {
		$code = (int) $code;
		foreach ($this->_exceptions as $e) {
			if ($code == $e->getCode()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Retrieve all exceptions of a given type
	 *
	 * @param  string $type
	 * @return false|array
	 */
	public function getExceptionByType($type) {
		$exceptions = array();
		foreach ($this->_exceptions as $e) {
			if ($e instanceof $type) {
				$exceptions[] = $e;
			}
		}

		if (empty($exceptions)) {
			$exceptions = false;
		}

		return $exceptions;
	}

	/**
	 * Retrieve all exceptions of a given message
	 *
	 * @param  string $message
	 * @return false|array
	 */
	public function getExceptionByMessage($message) {
		$exceptions = array();
		foreach ($this->_exceptions as $e) {
			if ($message == $e->getMessage()) {
				$exceptions[] = $e;
			}
		}

		if (empty($exceptions)) {
			$exceptions = false;
		}

		return $exceptions;
	}

	/**
	 * Retrieve all exceptions of a given code
	 *
	 * @param mixed $code
	 * @return Exception
	 */
	public function getExceptionByCode($code) {
		$code       = (int) $code;
		$exceptions = array();
		foreach ($this->_exceptions as $e) {
			if ($code == $e->getCode()) {
				$exceptions[] = $e;
			}
		}

		if (empty($exceptions)) {
			$exceptions = false;
		}

		return $exceptions;
	}

	/**
	 * Whether or not to render exceptions (off by default)
	 *
	 * If called with no arguments or a null argument, returns the value of the
	 * flag; otherwise, sets it and returns the current value.
	 *
	 * @param boolean $flag Optional
	 * @return boolean
	 */
	public function renderExceptions($flag = null) {
		if (null !== $flag) {
			$this->_renderExceptions = (bool) $flag;
		}

		return $this->_renderExceptions;
	}

	/**
	 * Send the response, including all headers, rendering exceptions if so
	 * requested.
	 *
	 * @return void
	 */
	public function sendResponse($exit = false) {
		$this->sendHeaders();
		$this->outputBody();
		if ($exit) {
			exit;
		}
	}

	/**
	 * Magic __toString functionality
	 *
	 * Proxies to {@link sendResponse()} and returns response value as string
	 * using output buffering.
	 *
	 * @return string
	 */
	public function __toString() {
		ob_start();
		$this->sendResponse();
		return ob_get_clean();
	}
}
