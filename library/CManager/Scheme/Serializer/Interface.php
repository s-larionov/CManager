<?php

interface CManager_Scheme_Serializer_Interface {
	/**
	 * @static
	 * @param CManager_Scheme_Abstract $scheme
	 * @return mixed
	 */
	public static function serialize(CManager_Scheme_Abstract $scheme);

	/**
	 * @param mixed $serializedScheme
	 * @param string $namespace
	 * @return CManager_Scheme_Abstract
	 */
	public static function unserialize($serializedScheme, $namespace);
}
