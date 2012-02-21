<?php

class CManager_Scheme_Serializer_Array implements CManager_Scheme_Serializer_Interface {
	/**
	 * @static
	 * @param CManager_Scheme_Abstract $scheme
	 * @return string
	 */
	public static function serialize(CManager_Scheme_Abstract $scheme) {
		$array = array();
		foreach(get_object_vars($scheme) as $name => $value) {
			if (is_array($value)) {
				$array[$name] = array();
				foreach($value as $valueItem) {
					if ($valueItem instanceof CManager_Scheme_Abstract) {
						$array[$name][] = self::serialize($valueItem);
					} else if (is_scalar($valueItem)) {
						$array[$name][] = $valueItem;
					} else {
						$array[$name][] = (string) $valueItem;
					}
				}
			} else if ($value instanceof CManager_Scheme_Abstract) {
				$array[$name] = self::serialize($value);
			} else if (is_scalar($value)) {
				$array[$name] = $value;
			} else {
				$array[$name] = (string) $value;
			}
		}
		return $array;
	}

	/**
	 * @param array $serializedScheme
	 * @param string $namespace
	 * @return CManager_Scheme_Abstract
	 */
	public static function unserialize($serializedScheme, $namespace) {
		return CManager_Helper_Object::newInstance($namespace, 'CManager_Scheme_Abstract', array(
			new CManager_Scheme_Serializer_Array($serializedScheme)
		));
	}
}
