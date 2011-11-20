<?php

class CManager_Helper_Xml {
	/**
	 * @param string  $element	   Название _Xml-элемента
	 * @param mixed   $value		 Значение элемента
	 * @param array   $elementAttrs  Аттрибуты элемента
	 * @param array $config
	 * @return string				_Xml-строка
	 */
	public static function parse($element, $value, array $elementAttrs = array(), array $config = array()) {
		switch (true) {
			case is_numeric($value):
			case empty($value):
//			case $value === null:
				break;
			case is_bool($value) || $value == 'true' || $value == 'false':
				$value = $value? 'true': 'false';
				break;
			case is_array($value):
				$value = self::_parseArray($value, $config);
				break;
			case is_object($value) && is_callable(array($value, 'toXml')):
				return $value->toXml();
				break;
			default:
				$value = '<![CDATA['. $value .']]>';
		}
		$elementAttrsStr = '';
		foreach((array) $elementAttrs as $attrName => $attrValue) {
			if (!is_scalar($attrValue)) {
				if (is_object($attrValue)) {
					$attrValue = get_class($attrValue);
				} else {
					try {
						$attrValue = (string) $attrValue;
					} catch (Exception $e) {
						$attrValue = "";
					}
				}
			}
			$elementAttrsStr .= ' ' . $attrName .'="' . htmlspecialchars($attrValue) . '"';
		}
		$xml = empty($value) && !is_numeric($value)
			? "<{$element}{$elementAttrsStr}/>"
			: "<{$element}{$elementAttrsStr}>{$value}</{$element}>";
		return $xml;
	}

	/**
	 * @param array $array
	 * @param array $config
	 * @return string
	 */
	protected static function _parseArray(array $array, array $config = array()) {
		$config = array_merge(array(
				'rowElementName'	=> 'row',
				'keysAsElements'	=> null,
				'elementAttributes'	=> array(),
				'inheritConfig'		=> false
			), $config);

		$xml = '';

		if ($config['keysAsElements'] === null && !CManager_Helper_Array::isNumberedArray($array)) {
			$config['keysAsElements'] = true;
		} else if ($config['keysAsElements'] && CManager_Helper_Array::isNumberedArray($array)) {
			throw new CManager_Exception("Can't generate xml with named items for numbered array");
		}

		foreach($array as $key => $row) {
			$elementAttributesValues = array();
			if (!empty($config['elementAttributes']) && is_array($row)) {
				foreach($config['elementAttributes'] as $attr) {
					if (isset($row[$attr])) {
						$elementAttributesValues[$attr] = $row[$attr];
						$row[$attr] = null;
						unset($row[$attr]);
					} else {
						$elementAttributesValues[$attr] = '';
					}
				}
			}

			if ($config['keysAsElements']) {
				$xml .= self::parse($key, $row, $elementAttributesValues, $config['inheritConfig']? $config: array());
			} else {
				$xml .= self::parse($config['rowElementName'], $row, array_merge(array('key' => $key), $elementAttributesValues), $config['inheritConfig']? $config: array());
			}
		}
		return $xml;
	}
}