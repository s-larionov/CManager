<?php

class CManager_Cache_Storage_File extends CManager_Cache_Storage_Abstract {
	/**
	 * @var string|null
	 */
	protected $_directory = null;

	/**
	 * @param string $key
	 * @param mixed $data
	 * @param int|null $ttl
	 * @param string|null $validateHash
	 * @return mixed
	 */
	public function save($key, $data, $ttl = null, $validateHash = null) {
		if (!($expiredTime = $this->_getCacheExpiredTime($ttl))) {
			return $data;
		};
		$file = new CManager_File($this->_getFileName($this->_prepareKey($key)));
		$file->setContent($this->_packContent($data, $expiredTime, $validateHash), true);
		return $data;
	}

	/**
	 * @param string $key
	 * @return boolean
	 */
	public function delete($key) {
		$file = new CManager_File($this->_getFileName($this->_prepareKey($key)));
		return $file->delete();
	}

	/**
	 * @return string
	 */
	public function getDirectory() {
		if ($this->_directory === null) {
			$this->_directory = array_key_exists('dir', $this->_config)? rtrim($this->_config['dir'], '/') . '/': '';
		}
		return $this->_directory;
	}

	/**
	 * @param string $key
	 * @return string
	 */
	protected function _getFileName($key) {
		$key = str_replace('/', '~', trim($key, '/'));
		return "{$this->getDirectory()}{$key}";
	}

	/**
	 * @param string $key
	 * @return array|bool
	 */
	protected function _get($key) {
		$file = new CManager_File($this->_getFileName($this->_prepareKey($key)));
		if (!$file->exists() || !$file->isReadable()) {
			return false;
		}
		return $this->_unpackContent($file->getContent());
	}
}
