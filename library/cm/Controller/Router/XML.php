<?php

class cm_Controller_Router_XML extends cm_Controller_Router_Abstract {
	/**
	 * @var string
	 */
	protected $_xmlFile = '';

	/**
	 * @param string $xmlFile
	 * @param cm_Controller_Request_HTTP $request
	 * @param cm_Controller_Response_HTTP $response
	 */
	public function __construct($xmlFile, $request = null, $response = null) {
		$this->_xmlFile = (string) $xmlFile;
		parent::__construct($request, $response);
	}

	/**
	 * @return array
	 * @throws cm_Controller_Router_Exception
	 */
	protected function _getStructure() {
		if (!file_exists($this->_xmlFile)) {
			throw new cm_Controller_Router_Exception("File '{$this->_xmlFile}' not found");
		}
		$structure = new cm_Controller_Router_XML_Section($this->_xmlFile);
		return $structure->toArray();
	}
}