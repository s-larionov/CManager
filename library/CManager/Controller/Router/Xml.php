<?php

class CManager_Controller_Router_Xml extends CManager_Controller_Router_Abstract {

	public $structureMapper = array(
		CManager_Controller_Router_Config_Abstract::NAMESPACE_PAGE				=> 'CManager_Controller_Router_Config_Page',
		CManager_Controller_Router_Config_Abstract::NAMESPACE_PAGE_NAVIGATION	=> 'CManager_Controller_Router_Config_PageNav',
		CManager_Controller_Router_Config_Abstract::NAMESPACE_PAGE_TITLE		=> 'CManager_Controller_Router_Config_PageTitle',
		CManager_Controller_Router_Config_Abstract::NAMESPACE_PERMISSION		=> 'CManager_Controller_Router_Config_Permission',
		CManager_Controller_Router_Config_Abstract::NAMESPACE_ROUTE				=> 'CManager_Controller_Router_Config_Route',
		CManager_Controller_Router_Config_Abstract::NAMESPACE_ROUTE_VAR			=> 'CManager_Controller_Router_Config_RouteVar',
		CManager_Controller_Router_Config_Abstract::NAMESPACE_STRUCTURE			=> 'CManager_Controller_Router_Config_Structure',
		CManager_Controller_Router_Config_Abstract::NAMESPACE_TAG				=> 'CManager_Controller_Router_Config_Tag',
		CManager_Controller_Router_Config_Abstract::NAMESPACE_TAG_EXCLUSION		=> 'CManager_Controller_Router_Config_TagExclusion',
		CManager_Controller_Router_Config_Abstract::NAMESPACE_TAG_PARAM			=> 'CManager_Controller_Router_Config_TagParam'
	);

	/**
	 * @var string
	 */
	protected $_xmlFile = '';

	/**
	 * Load file error string.
	 * Is null if there was no error while file loading
	 *
	 * @var string
	*/
	protected $_loadFileErrorStr = null;

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
	 * @return CManager_Controller_Router_Config_Abstract
	 * @throws CManager_Controller_Router_Exception
	 */
	protected function _getStructure() {
		if (!file_exists($this->_xmlFile)) {
			throw new CManager_Controller_Router_Exception("File '{$this->_xmlFile}' not found");
		}

		set_error_handler(array($this, '_loadFileErrorHandler')); // Warnings and errors are suppressed
		$xml = simplexml_load_file($this->_xmlFile);
		restore_error_handler();

		// Check if there was a error while loading file
		if ($this->_loadFileErrorStr !== null) {
			throw new CManager_Controller_Router_Xml_Exception($this->_loadFileErrorStr);
		}

		return new CManager_Controller_Router_Config_Structure($xml, new CManager_Structure_Adapter_Xml());
	}

	/**
	 * Handle any errors from simplexml_load_file or parse_ini_file
	 *
	 * @param integer $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param integer $errline
	 */
	protected function _loadFileErrorHandler($errno, $errstr, $errfile, $errline) {
		if ($this->_loadFileErrorStr === null) {
			$this->_loadFileErrorStr = $errstr;
		} else {
			$this->_loadFileErrorStr .= (PHP_EOL . $errstr);
		}
	}
}