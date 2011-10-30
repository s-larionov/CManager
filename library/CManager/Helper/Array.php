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
}