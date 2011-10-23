<?php

class cm_Controller_Router_XML extends cm_Controller_Router_Abstract {

	/**
	 * @param string $xmlFile
	 * @param cm_Controller_Request_HTTP $request
	 * @param cm_Controller_Response_HTTP $response
	 */
	public function __construct($xmlFile, $request = null, $response = null) {
		parent::__construct($request, $response);
	}

	protected function _getStructure() {
		return array();
	}
}