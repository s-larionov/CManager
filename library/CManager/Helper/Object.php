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
		if ($parentClassName !== null && $reflection->getName() != $parentClassName && !$reflection->isSubclassOf($parentClassName)) {
			throw new CManager_Exception("Class '$className' must be inheritance of '{$parentClassName}''");
		}

		try {
			return $reflection->newInstanceArgs($arguments);
		} catch (ReflectionException $e) {
			throw new CManager_Exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * @static
	 * @param Object|string $classOrObject
	 * @param string $parentClassOrInterface
	 * @return boolean
	 */
	public static function isSubclassOf($classOrObject, $parentClassOrInterface) {
		if (is_object($classOrObject)) {
			$reflection = new \ReflectionObject($classOrObject);
		} else {
			$reflection = new \ReflectionClass($classOrObject);
		}
		if ($reflection->isSubclassOf($parentClassOrInterface)) {
			return true;
		}

		try {
			return $reflection->implementsInterface($parentClassOrInterface);
		} catch (ReflectionException $e) {}
		return false;
	}


	/**
	 * @static
	 * @param integer $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param integer $errline
	 */
	public static function loadFileErrorHandler($errno, $errstr, $errfile, $errline) {}
}
