<?php

class cm_Date_LocaleData_Scope {
	/**
	 * @var SimpleXMLElement
	 */
	protected $_xml = null;

	/**
	 * @var array
	 */
	protected $_values = array();

	/**
	 * @param SimpleXMLElement $xmlElement
	 */
	public function __construct(SimpleXMLElement $xmlElement) {
		$this->_xml = $xmlElement;
	}

	/**
	 * @param string $value
	 * @return string
	 */
	public function getItem($value) {
		if (!isset($this->_values[$value])) {
			$nodes = $this->_xml->xpath('item[@value = "' . $value . '"][1]');
			if (count($nodes) != 1) {
				throw new cm_Date_Exception("Item with @value = '{$value}' is not available in this scope");
			}
			$this->_values[$value] = (string) $nodes[0];
		}
		return $this->_values[$value];
	}
}
