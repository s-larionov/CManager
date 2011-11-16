<?php

class CManager_Helper_Array {
	/**
	 * Проверяет являются ли ключи массива только числовыми.
	 * @param array $array
	 * @return bool true - не ассоциативный массив, false - ассоциативный
	 */
	public static function isNumberedArray(array $array) {
		$keys = array_keys($array);
		foreach ($keys as $key) {
			if (!is_numeric($key)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @static
	 * @param array $array
	 * @param array $keyValueData
	 * @return array|null
	 */
	public static function findArrayItem(array $array, array $keyValueData) {
		foreach($array as $item) {
			if (!is_array($item)) {
				throw new CManager_Exception('Item is not array');
			}
			foreach($keyValueData as $key => $value) {
				if (!array_key_exists($key, $item)) {
					throw new CManager_Exception("Offset '{$key}' doesn't exists in item");
				}
				if ($item[$key] != $value) {
					continue 2;
				}
				return $item;
			}
		}
		return null;
	}
}