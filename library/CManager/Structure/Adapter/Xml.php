<?php

class CManager_Structure_Adapter_Xml extends CManager_Structure_Adapter_Abstract {
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
	 * @abstract
	 * @param SimpleXMLElement $element
	 * @return string[]
	 */
	public function getListAttributes($element) {
		$list = array();
		foreach($element->attributes() as $attribute) {
			$list[] = $attribute->getName();
		}
		return $list;
	}

	/**
	 * @param SimpleXMLElement $element
	 * @param string $childName
	 * @return array
	 */
	public function getChild($element, $childName) {
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