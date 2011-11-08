<?php

class CManager_Helper_Object {
	/**
	 * @static
	 * @param string $className
	 * @param string|null $parentClassName
	 * @param array $arguments
	 * @return object
	 * @throws CManager_Exception
	 */
	public static function newInstance($className, $parentClassName = null, array $arguments = array()) {
		try {
			CManager_Loader::load($className);
		} catch (CManager_Loader_Exception $e) {
			throw new CManager_Exception("Class {$className} not found");
		}

		$reflection = new ReflectionClass($className);
		if ($parentClassName !== null && !$reflection->isSubclassOf($parentClassName)) {
			throw new CManager_Exception("Object must be subclass of {$parentClassName}");
		}

		return $reflection->newInstanceArgs($arguments);
	}
}