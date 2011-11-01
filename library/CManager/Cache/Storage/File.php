<?php

class CManager_Cache_Storage_File extends CManager_Cache_Storage_Abstract {
	/**
	 * @param string $key
	 * @param null $time
	 * @return mixed
	 */
	public function load($key, $time = null) {
		if (!$time) {
			$time = time();
		}
		$key = $this->_prepareKey($key);
		$fileName = $this->_getFileName($key);
		if (!file_exists($fileName)) {
			return false;
		}
		
		$cacheContent = file_get_contents($fileName);
		
		list($cacheProps, $cacheContent) = explode("\n", $cacheContent, 2);
		$cacheProps = explode('|', $cacheProps);

		// ttl
		if (isset($cacheProps[0]) && (int)$cacheProps[0] < $time) {
			return false;
		}

		// format
		if (isset($cacheProps[1])) {
			switch($cacheProps[1]) {
				// serialized
				case 's':
					$cacheContent = @unserialize($cacheContent);
					break;
			}
		}

		return $cacheContent;
	}

	/**
	 * @param string $key
	 * @param mixed $data
	 * @param int $ttl
	 * @return mixed
	 */
	public function save($key, $data, $ttl = null) {
		if ($ttl === null) {
			$ttl = $this->_getDefaultTtl();
		}
		$key = $this->_prepareKey($key);
		$cacheProps = array();

		$cacheProps[0] = time() + $ttl;
		$cacheProps[1] = is_object($data) || is_array($data) ? 's' : '';

		if ($cacheProps[1] == 's') {
			$data = @serialize($data);
		}

		$data = implode('|', $cacheProps) ."\n". $data;

		file_put_contents($this->_getFileName($key), $data);
		return $data;
	}

	/**
	 * @param string $key
	 * @return boolean
	 */
	public function delete($key) {
		return @unlink($this->_getFileName($this->_prepareKey($key)));
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function read($key) {
		$key = $this->_prepareKey($key);
		$fileName = $this->_getFileName($key);
		if (!file_exists($fileName)) {
			return false;
		}
		$cacheContent = file_get_contents($fileName);

		list($cacheProps, $cacheContent) = explode("\n", $cacheContent, 2);
		$cacheProps = explode('|', $cacheProps);

		// format
		if (isset($cacheProps[1])) {
			switch($cacheProps[1]) {
				// serialized
				case 's':
					$cacheContent = @unserialize($cacheContent);
					break;
			}
		}

		return $cacheContent;
	}

	/**
	 * @param string $key
	 * @return string
	 */
	protected function _getFileName($key) {
		$key = str_replace('/', '[~]', $key);
		return (isset($this->_config['dir'])? $this->_config['dir']: '') . 'cmanager-cache-'. $key;
	}

}