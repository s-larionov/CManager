<?php

class CManager_Scheme_Serializer_Json extends CManager_Scheme_Serializer_Array {
	/**
	 * @static
	 * @param CManager_Scheme_Abstract $scheme
	 * @return string
	 */
	public static function serialize(CManager_Scheme_Abstract $scheme) {
		return json_encode(parent::serialize($scheme));
	}

	/**
	 * @param string $serializedScheme
	 * @param string $namespace
	 * @return CManager_Scheme_Abstract
	 */
	public static function unserialize($serializedScheme, $namespace) {
		return CManager_Helper_Object::newInstance($namespace, 'CManager_Scheme_Abstract', array(
			new CManager_Scheme_Adapter_Json($serializedScheme)
		));
	}
}
