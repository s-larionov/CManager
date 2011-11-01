<?php

class CManager_Cache_Storage_Mock extends CManager_Cache_Storage_Abstract {
	/**
	 * @param string $key
	 * @param int $time
	 * @return mixed
	 */
	public function load($key, $time = null) {
		return false;
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function read($key) {
		return false;
	}

	/**
	 * @param string $key
	 * @param mixed $data
	 * @param int $ttl
	 * @return boolean
	 */
	public function save($key, $data, $ttl = 0) {
		return false;
	}

	/**
	 * @param string $key
	 * @return boolean
	 */
	public function delete($key) {
		return false;
	}
}
