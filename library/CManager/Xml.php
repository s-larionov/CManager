<?php

class CManager_Xml {
	const CRLF = "\n";

	/**
	 * @param string  $element       Название _Xml-элемента
	 * @param mixed   $value         Значение элемента
	 * @param array   $elementAttrs  Аттрибуты элемента
	 * @return string                _Xml-строка
	 */
	public static function parse($element, $value, array $elementAttrs = array()) {
//		timer::start('toxml' . $element);
		switch (true) {
			case is_numeric($value):
			case empty($value):
//			case $value === null:
				break;
			case is_bool($value) || $value == 'true' || $value == 'false':
				$value = $value? 'true': 'false';
				break;
			case is_array($value):
				$value = self::_parseArray($value);
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
			? "<{$element}{$elementAttrsStr}/>" . self::CRLF
			: "<{$element}{$elementAttrsStr}>{$value}</{$element}>" . self::CRLF;
		return $xml;
	}

	/**
	* @param array   $array
	* @param string  $rowElementName
	* @param bool    $keysAsElements
	* @param array   $elementAttributes
	* @return string
	*/
	protected static function _parseArray(array $array, $rowElementName = 'row', $keysAsElements = true, $elementAttributes = array()) {
		$xml = '';
		$keysAsElements	= !($keysAsElements) || self::_isNumberedArray($array)? false: true;
		foreach($array as $key => $row) {
			$elementAttributesValues = array();
			if (!empty($elementAttributes) && is_array($row)) {
				foreach($elementAttributes as $attr) {
					if (isset($row[$attr])) {
						$elementAttributesValues[$attr] = $row[$attr];
						$row[$attr] = null;
						unset($row[$attr]);
					} else {
						$elementAttributesValues[$attr] = '';
					}
				}
			}

			if ($keysAsElements) {
				$xml .= self::parse($key, $row, $elementAttributesValues);
			} else {
				$xml .= self::parse($rowElementName, $row, array_merge(array('key' => $key), $elementAttributesValues));
			}
		}
		return $xml;
	}

	/**
	 * Проверяет являются ли ключи массива только числовыми.
	 * @param array $array
	 * @return bool true - не ассоциативный массив, false - ассоциативный
	 */
	protected static function _isNumberedArray(array $array) {
		$keys = array_keys($array);
		foreach ($keys as $key) {
			if (!is_numeric($key)) {
				return false;
			}
		}
		return true;
	}
}