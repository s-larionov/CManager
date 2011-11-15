<?php

class CManager_Controller_Router_Xml extends CManager_Controller_Router_Abstract {
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