<?php

// TODO
//class CManager_Structure_Adapter_Xml extends CManager_Structure_Adapter_Abstract {
//	/**
//	 * @param array $element
//	 * @param string $attributeName
//	 * @return string|null
//	 */
//	public function getAttribute($element, $attributeName) {
//		if (!isset($element['@'])) {
//			$element['@'] = array();
//		}
//		if (!isset($element['@'][$attributeName])) {
//			return null;
//		}
//		return (string) $element['@'][$attributeName];
//	}
//
//	/**
//	 * @param array $element
//	 * @param string $childName
//	 * @return array
//	 */
//	public function getChild($element, $childName) {
//		$value = $element->$childName;
//		if (count($value) === 0) {
//			return null;
//		} else if (count($value) > 1) {
//			$array = array();
//			foreach($value as $item) {
//				$array[] = $item;
//			}
//			return $array;
//		} else {
//			return $value[0];
//		}
//	}
//}