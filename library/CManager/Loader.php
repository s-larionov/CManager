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

require_once 'CManager/Exception.php';

class CManager_Loader_Exception extends CManager_Exception {

}

class CManager_Loader {
	/**
	 * Load class by standardized class name
	 * @see http://framework.zend.com/manual/en/coding-standard.naming-conventions.html#coding-standard.naming-conventions.classes
	 *
	 * @param string $className
	 * @param string $directory
	 * @return string|bool Return included filename
	 * @throws CManager_Loader_Exception when file or class doesn't exists
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

		// ловим Warnings на отсутствие файла, но Fatal Error будут происходить все равно
		set_error_handler(array(__CLASS__, '_loadFileErrorHandler')); // Warnings and errors are suppressed
		if (!include_once($path)) {
			restore_error_handler();
			throw new CManager_Loader_Exception("Loading file {$path} error");
		}
		restore_error_handler();

		if (!class_exists($className) && !interface_exists($className)) {
			throw new CManager_Loader_Exception("Class {$className} not found in file {$path}.");
		}

		return $path;
	}

	/**
	 * @static
	 * @param integer $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param integer $errline
	 */
	protected static function _loadFileErrorHandler($errno, $errstr, $errfile, $errline) {}
}