<?php
/**
 * CManager Framework
 *
 * @category	Core
 * @package		Loader
 * @author		Vladimir Shushkov <vladimir@shushkov.ru>
 * @copyright	Copyright (c) 2008-2009 Vladimir Shushkov
 * @license		http://creativecommons.org/licenses/LGPL/2.1/ LGPL
 */

require_once 'cm/Exception.php';

class cm_Loader_Exception extends cm_Exception {

}

class cm_Loader {
	/**
	 * Load class by standardized class name
	 * @see http://framework.zend.com/manual/en/coding-standard.naming-conventions.html#coding-standard.naming-conventions.classes
	 *
	 * @param string $className
	 * @param string $directory
	 * @return string|bool Return included filename
	 * @throws cm_Loader_Exception when file or class doesn't exists
	 */
	public static function load($className, $directory = null) {
		if (class_exists($className) || interface_exists($className)) {
			return true;
		}

		$name = str_replace('_', DIRECTORY_SEPARATOR, $className);

		if (strpos($name, DIRECTORY_SEPARATOR) === false) {
			$name = $name . DIRECTORY_SEPARATOR . $name;
		}

		$path = $name .'.php';

		try {
			include_once $path;
		} catch (Exception $e) {
			throw new cm_Loader_Exception("Loading file {$path} error. ". $e->getMessage());
		}

		if (!class_exists($className) && !interface_exists($className)) {
			throw new cm_Loader_Exception("Class {$className} not found in file {$path}.");
		}

		return $path;
	}
}