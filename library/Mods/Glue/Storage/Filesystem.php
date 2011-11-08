<?php

class Mods_Glue_Storage_Filesystem implements  Mods_Glue_Storage_Interface {

	/**
	 * @var string
	 */
	protected $_path = '';

	/**
	 * @param array $config
	 */
	public function __construct(array $config) {
		if (isset($config['path'])) {
			$this->_path = str_replace('(:root)', $this->_getDocumentRoot(),$config['path']);
		}
	}

	/**
	 * @return string
	 */
	protected function _getDocumentRoot() {
		if ($root = CManager_Registry::getConfig()->root) {
			return $root;
		} else if (defined('APPLICATION_ROOT')) {
			return APPLICATION_ROOT;
		}
		return '';
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
		return $this->getPath() . DIRECTORY_SEPARATOR . $filename;
	}

	/**
	 * @param Mods_Glue_GroupFiles $fileGroup
	 * @return int
	 */
	public function getMTime(Mods_Glue_GroupFiles $fileGroup) {
		return $this->getMTimeByFilename($fileGroup->getFilename());
	}

	/**
	 * @param string $filename
	 * @return int
	 */
	public function getMTimeByFilename($filename) {
		$fullFilename = $this->_getFullFilename($filename);
		if (file_exists($fullFilename)) {
			return filemtime($fullFilename);
		}
		return 0;
	}

	/**
	 * @param Mods_Glue_GroupFiles $fileGroup
	 * @return string
	 */
	public function get(Mods_Glue_GroupFiles $fileGroup) {
		return $this->getByFilename($fileGroup->getFilename());
	}

	/**
	 * @param string $filename
	 * @return string
	 */
	public function getByFilename($filename) {
		file_get_contents($this->_getFullFilename($filename));
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return $this->_path;
	}
}
