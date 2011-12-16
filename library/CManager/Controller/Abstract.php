<?php

class CManager_Controller_Abstract extends CManager_EventEmitter {
	/**
	 * @var CManager_Controller_Response_Abstract
	 */
	protected $_request;

	/**
	 * @var CManager_Controller_Response_Abstract
	 */
	protected $_response;

	/**
	 * @param CManager_Controller_Request_Abstract $request
	 * @param CManager_Controller_Response_Abstract $response
	 * @return CManager_Controller_Abstract
	 */
	public function __construct(CManager_Controller_Request_Abstract $request = null, CManager_Controller_Response_Abstract $response = null) {
		if ($request) {
			$this->setRequest($request);
		}
		if ($response) {
			$this->setResponse($response);
		}
	}

	/**
	 * @param CManager_Controller_Request_Abstract $request
	 * @return void
	 */
	public function setRequest(CManager_Controller_Request_Abstract $request) {
		$this->_request = $request;
	}

	/**
	 * @return CManager_Controller_Request_Http
	 */
	public function getRequest() {
		return $this->_request;
	}

	/**
	 * @param CManager_Controller_Response_Abstract $response
	 * @return void
	 */
	public function setResponse(CManager_Controller_Response_Abstract $response) {
		$this->_response = $response;
	}

	/**
	 * @return CManager_Controller_Response_Abstract|CManager_Controller_Response_Http
	 */
	public function getResponse() {
		return $this->_response;
	}

}