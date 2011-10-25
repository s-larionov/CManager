<?php

class CManager_Controller_Router_Xml extends CManager_Controller_Router_Abstract {
	/**
	 * @var string
	 */
	protected $_xmlFile = '';

	/**
	 * @param string $xmlFile
	 * @param CManager_Controller_Request_Http $request
	 * @param CManager_Controller_Response_Http $response
	 */
	public function __construct($xmlFile, $request = null, $response = null) {
		$this->_xmlFile = (string) $xmlFile;
		parent::__construct($request, $response);
	}

	/**
	 * @return array
	 * @throws CManager_Controller_Router_Exception
	 */
	protected function _getStructure() {
		if (!file_exists($this->_xmlFile)) {
			throw new CManager_Controller_Router_Exception("File '{$this->_xmlFile}' not found");
		}
		$structure = new CManager_Controller_Router_Xml_Section($this->_xmlFile);
		return $structure->toArray();
	}
}