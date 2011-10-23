<?php

class cm_Controller_Response_HTTP extends cm_Controller_Response_Abstract {
	/**
	 * @var array
	 */
	protected $_cookies = array();

	/**
	 * @param array|string $name
	 * @param string $value
	 * @param array $params
	 * @return cm_Controller_Response_HTTP
	 */
	public function setCookie($name, $value, $params = array()) {
		$data = $name;

		if (is_array($data)) {
			$params = $value;
		} else {
			$data = array($name => $value);
		}

		$params = array_merge(array(
			'domain'	=> $_SERVER['HTTP_HOST'],
			'expires'	=> null,
			'path'		=> '/',
			'httponly'	=> null,
			'secure'	=> false
		), $params);

		foreach ($data as $name => $value) {
			$this->_cookies[$name] = $params;
			$this->_cookies[$name]['value'] = $value;
		}

		return $this;
	}

	/**
	 * @param boolean $exit
	 * @return cm_Controller_Response_HTTP
	 */
	public function sendHeaders($exit = false) {
		foreach ($this->_cookies as $name => $params) {
			if ($params['value'] === null) {
				$params['expires'] = null;
				if (isset($_COOKIE[$name])) {
					unset($_COOKIE[$name]);
				}
			}
			setcookie($name, $params['value'],
				$params['expires'], $params['path'], $params['domain'],
				$params['secure'], $params['httponly']
			);
		}
		return parent::sendHeaders($exit);
	}
}