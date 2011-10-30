<?php

class CManager_Helper_File {
	/**
	 * @param string $file
	 * @param null|array|string|Zend_Config $dirs
	 * @param boolean $throw
	 * @return string|false
	 */
	public static function getFullPath($file, $dirs = null, $throw = false) {
		$dirs = $dirs instanceof Zend_Config
			? $dirs->toArray()
			: (!is_array($dirs)? array($dirs): $dirs);

		$result = false;
		foreach ($dirs as $dir) {
			$fullPath = rtrim($dir, '/'). '/'. ltrim($file, '/');
			if (file_exists($fullPath)) {
				$result = $fullPath;
				break;
			}
		}

		if (!$result && $throw) {
			throw new cm_Exception("Файл '$file' не найден.");
		}

		return $result;
	}
}