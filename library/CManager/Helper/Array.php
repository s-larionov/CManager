<?php

class CManager_Helper_Array {
	/**
	 * Проверяет являются ли ключи массива только числовыми.
	 *
	 * @param array $array
	 * @return bool true - не ассоциативный массив, false - ассоциативный
	 */
	public static function isSimpleArray(array $array) {
		return (range(0, count($array) - 1) === array_keys($array));
	}

	/**
	 * Проверяет являются ли ключи массива только строковыми.
	 *
	 * @param array $array
	 * @return bool true - ассоциативный массив, false - не ассоциативный
	 */
	public static function hasNumberOrEmptyKeys(array $array) {
		foreach(array_keys($array) as $key) {
			if (is_numeric($key) || empty($key)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @static
	 * @param array[] $array
	 * @param array $keyValueData
	 * @param bool $multiple
	 * @return array|null
	 */
	public static function findArrayItem(array $array, array $keyValueData, $multiple = false) {
		$collection = array();
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
			}
			if (!$multiple) {
				return $item;
			}
			$collection[] = $item;
		}
		return $multiple? $collection: null;
	}

	/**
	 * @param array $array
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function getArrayValue(array $array, $key, $default = null) {
		if (array_key_exists($key, $array)) {
			return $array[$key];
		}
		return $default;
	}}
