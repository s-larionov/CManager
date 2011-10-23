<?php

class cm_Registry extends cm_Registry_Abstract {
	/**
	 * @var cm_Controller_Front
	 */
	protected static $_application;

	/**
	 * @var cm_Controller_Request_Abstract
	 */
	protected static $_request;

	/**
	 * @var cm_Controller_Response_Abstract
	 */
	protected static $_response;

	/**
	 * @var Zend_Config
	 */
	protected static $_config;

	/**
	 * @param cm_Controller_Front $app
	 * @return void
	 */
	public static function setFrontController(cm_Controller_Front $app) {
		self::$_application = $app;
	}

	/**
	 * @return cm_Controller_Front
	 */
	public static function getFrontController() {
		if (!self::$_application) {
			throw new cm_Registry_Exception('FrontController not defined');
		}
		return self::$_application;
	}

	/**
	 * @return cm_Controller_Request_Abstract
	 */
	public static function getRequest() {
		if (!self::$_request) {
			throw new cm_Registry_Exception('Request not defined');
		}
		return self::$_request;
	}

	/**
	 * @param cm_Controller_Request_Abstract $request
	 * @return void
	 */
	public static function setRequest(cm_Controller_Request_Abstract $request) {
		self::$_request = $request;
	}

	/**
	 * @return cm_Controller_Response_Abstract
	 */
	public static function getResponse() {
		if (!self::$_response) {
			throw new cm_Registry_Exception('Response not defined');
		}
		return self::$_response;
	}

	/**
	 * @param cm_Controller_Response_Abstract $response
	 * @return void
	 */
	public static function setResponse(cm_Controller_Response_Abstract $response) {
		self::$_response = $response;
	}

	/**
	 * @return Zend_Config
	 */
	public static function getConfig() {
		if (!self::$_config) {
			throw new cm_Registry_Exception('Config not defined');
		}
		return self::$_config;
	}

	/**
	 * @param Zend_Config|array $config
	 * @return void
	 */
	public static function setConfig($config) {
		if (is_array($config)) {
			$config = new Zend_Config($config, true);
		}

		if (!($config instanceof Zend_Config)) {
			throw new cm_Registry_Exception('Wrong config data.');
		}

		self::$_config = $config;
	}
}