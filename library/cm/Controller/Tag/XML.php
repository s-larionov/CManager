<?php

class cm_Controller_Tag_XML extends cm_Controller_Tag {
	/**
	 * @var SimpleXMLElement
	 */
	private $_structure;

	/**
	 * @param string $name
	 * @param string $namespace
	 * @param string $mode
	 * @param array|Zend_Config $params
	 * @param SimpleXMLElement $xml
	 */
	public function __construct($name, $namespace, $mode, $params = null, SimpleXMLElement $xml = null) {
		parent::__construct($name, $namespace, $mode, $params);
		$this->_structure = $xml;
	}

	/**
	 * @return null|SimpleXMLElement
	 * @throws cm_Controller_Tag_Exception
	 */
	public function getStructure() {
		if ($this->_structure === null) {
			throw new cm_Controller_Tag_Exception('Structure is not provided.');
		}
		return $this->_structure;
	}
}