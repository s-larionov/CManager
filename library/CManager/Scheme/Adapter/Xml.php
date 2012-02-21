<?php

class CManager_Scheme_Adapter_Xml extends CManager_Scheme_Adapter_Abstract {
	/**
	 * @var SimpleXMLElement
	 */
	protected $xml = null;

	public function __construct(SimpleXMLElement $xml) {
		$this->xml = $xml;
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
	 * @param string $childName
	 * @return CManager_Structure_Adapter_Abstract[]|CManager_Structure_Adapter_Abstract|null
	 */
	protected function getChild($childName) {
		$xml = /** @var SimpleXMLElement $xml */$this->getXml()->{$childName};
		if (count($xml) === 0 || $xml === null) {
			return null;
		} else if (count($xml) > 1) {
			$value = array();
			foreach($xml as $item) {
				$value[] = new self($item);
			}
			return $value;
		} else {
			return new self($xml[0]);
		}
	}

	public function get($name) {
		if (($value = $this->getAttribute($name)) !== null) {
			return $value;
		} else if (($value = $this->getChild($name)) !== null) {
			return $value;
		}
		return null;
	}

	/**
	 * @return SimpleXMLElement
	 */
	protected function getXml() {
		return $this->xml;
	}

	public function __toString() {
		return (string) $this->getXml();
	}
}
