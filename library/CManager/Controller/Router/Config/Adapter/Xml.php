<?php

class CManager_Controller_Router_Config_Adapter_Xml extends CManager_Controller_Router_Config_Adapter_Abstract {
	/**
	 * @var SimpleXMLElement
	 */
	protected $_xml;

	/**
	 * @param SimpleXMLElement $element
	 * @param string $attributeName
	 * @return string|null
	 */
	public function getAttribute($element, $attributeName) {
		$value = $element->attributes()->$attributeName;
		if ($attributeName === 'value' && $value === null) {
			$value = trim((string) $element);
			if ($value === '') {
				$value = null;
			}
		}
		return $value;
	}

	/**
	 * @param SimpleXMLElement $element
	 * @param string $childName
	 * @param boolean $includePass
	 * @return array
	 */
	public function getChild($element, $childName, $includePass = false) {
		$value = $element->$childName;
		if (count($value) === 0) {
			return null;
		} else if (count($value) > 1) {
			$array = array();
			foreach($value as $item) {
				$array[] = $item;
			}
			return $array;
		} else {
			return $value[0];
		}
	}
}