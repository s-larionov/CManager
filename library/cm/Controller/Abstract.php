<?php

class cm_Controller_Abstract extends cm_Event_Emitter {
	/**
	 * @var cm_Controller_Response_Abstract
	 */
	protected $_request;

	/**
	 * @var cm_Controller_Response_Abstract
	 */
	protected $_response;

	/**
	 * @param cm_Controller_Request_Abstract $request
	 * @param cm_Controller_Response_Abstract $response
	 * @return cm_Controller_Abstract
	 */
	public function __construct(cm_Controller_Request_Abstract $request = null, cm_Controller_Response_Abstract $response = null) {
		if ($request) {
			$this->setRequest($request);
		}
		if ($response) {
			$this->setResponse($response);
		}
	}

	/**
	 * @param cm_Controller_Request_Abstract $request
	 * @return void
	 */
	public function setRequest(cm_Controller_Request_Abstract $request) {
		$this->_request = $request;
	}

	/**
	 * @return cm_Controller_Request_HTTP
	 */
	public function getRequest() {
		return $this->_request;
	}

	/**
	 * @param cm_Controller_Response_Abstract $response
	 * @return void
	 */
	public function setResponse(cm_Controller_Response_Abstract $response) {
		$this->_response = $response;
	}

	/**
	 * @return cm_Controller_Response_Abstract
	 */
	public function getResponse() {
		return $this->_response;
	}

}