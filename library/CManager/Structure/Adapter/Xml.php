<?php

class CManager_Structure_Adapter_Xml extends CManager_Structure_Adapter_Abstract {
	/**
	 * @var SimpleXMLElement
	 */
	protected $_xml = null;

	public function __construct(SimpleXMLElement $xml) {
		$this->_xml = $xml;
	}

	/**
	 * @param string $attributeName
	 * @return string|null
	 */
	public function getAttribute($attributeName) {
		$value = $this->getXml()->attributes()->$attributeName;
		if ($attributeName === 'value' && $value === null) {
			$value = trim($this->getXml());
			if ($value === '') {
				$value = null;
			}
		}
		if ($value !== null) {
			return (string) $value;
		}
		return null;
	}

	/**
	 * @abstract
	 * @return string[]
	 */
	public function getListAttributes() {
		$list = array();
		foreach($this->getXml()->attributes() as /** @var SimpleXMLElement $attribute */ $attribute) {
			$list[] = $attribute->getName();
		}
		return $list;
	}

	/**
	 * @param string $childName
	 * @return CManager_Structure_Adapter_Abstract[]|CManager_Structure_Adapter_Abstract|null
	 */
	public function getChild($childName) {
		$xml = /** @var SimpleXMLElement $xml */$this->getXml()->$childName;
		if (count($xml) === 0 || $xml === null) {
			return null;
		} else if (count($xml) > 1) {
			$array = array();
			foreach($xml as $item) {
				$array[] = new self($item);
			}
			return $array;
		} else {
			return new self($xml[0]);
		}
	}

	/**
	 * @return SimpleXMLElement
	 */
	public function getXml() {
		return $this->_xml;
	}

	/**
	 * @return string
	 */
	function __toString() {
		return (string) $this->getXml();
	}
}