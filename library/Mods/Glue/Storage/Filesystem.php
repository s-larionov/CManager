<?php

class Mods_Glue_Storage_Filesystem implements  Mods_Glue_Storage_Interface {

	/**
	 * @var string
	 */
	protected $_path = '';

	/**
	 * @var string
	 */
	protected $_filename = '(:filename)';

	/**
	 * @param array $config
	 */
	public function __construct(array $config) {
		if (isset($config['path'])) {
			$this->_path = $config['path'];
		}
		if (isset($config['filename'])) {
			$this->_path = $config['filename'];
		}
	}

	/**
	 * @param Mods_Glue_GroupFiles $fileGroup
	 * @return Mods_Glue_Storage_Filesystem
	 */
	public function put(Mods_Glue_GroupFiles $fileGroup) {
		file_put_contents(
			$this->_getFullFilename($fileGroup->getFilename()),
			$fileGroup->getContent()
		);
		return $this;
	}

	/**
	 * @param string $filename
	 * @return string
	 */
	protected function _getFullFilename($filename) {
		return $this->getPath()
				. DIRECTORY_SEPARATOR
				. basename(str_replace(
					'(:filename)',
					$filename,
					$this->getFilename()
				));
	}

	/**
	 * @param string $filename
	 * @return int
	 */
	public function getMTime($filename) {
		return filemtime($this->_getFullFilename($filename));
	}

	/**
	 * @param string $filename
	 * @return string
	 */
	public function get($filename) {
		file_get_contents($this->_getFullFilename($filename));
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return $this->_path;
	}

	/**
	 * @return string
	 */
	public function getFilename() {
		return $this->_filename;
	}
}
