<?php

class CManager_Date_LocaleData_Section {
	/**
	 * @var SimpleXMLElement
	 */
	protected $_xml = null;

	/**
	 * @var array
	 */
	protected $_scopes = array();

	/**
	 * @param SimpleXMLElement $xmlElement
	 */
	public function __construct(SimpleXMLElement $xmlElement) {
		$this->_xml = $xmlElement;
	}

	/**
	 * @param string $mode
	 * @return CManager_Date_LocaleData_Scope
	 */
	public function getScope($mode) {
		if (!isset($this->_scopes[$mode])) {
			$nodes = $this->_xml->xpath('scope[@mode = "' . $mode . '"][1]');
			if (count($nodes) != 1) {
				throw new CManager_Date_Exception("Scope with mode '{$mode}' is not available in this section");
			}
			$this->_scopes[$mode] = new CManager_Date_LocaleData_Scope($nodes[0]);
		}
		return $this->_scopes[$mode];
	}
}
