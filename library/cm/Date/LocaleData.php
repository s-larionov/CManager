<?php

class cm_Date_LocaleData {
	/**
	 * @var SimpleXMLElement
	 */
	protected $_xml = null;

	/**
	 * @var array
	 */
	protected $_sections = array();

	public function __construct($filename) {
		if (!file_exists($filename)) {
			throw new cm_Date_Exception("File '{$filename}' not exists");
		}
		$this->_xml = new SimpleXMLElement(file_get_contents($filename));
	}

	/**
	 * @param string $name
	 * @return cm_Date_LocaleData_Section
	 */
	public function getSection($name) {
		if (!isset($this->_sections[$name])) {

			$nodes = $this->_xml->xpath('section[@name = "' . $name . '"][1]');
			if (count($nodes) != 1) {
				throw new cm_Date_Exception("Section '{$name}' is not available in this xml file");
			}
			$this->_sections[$name] = new cm_Date_LocaleData_Section($nodes[0]);
		}
		return $this->_sections[$name];
	}
}
