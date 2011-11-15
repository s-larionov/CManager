<?php

class CManager_File implements CManager_File_Interface {
	/**
	 * @var string
	 */
	protected $_filename;

	/**
	 * @var string
	 */
	protected $_content = null;

	/**
	 * @var array
	 */
	protected $_attributes = array();

	/**
	 * @param string $filename
	 */
	public function __construct($filename) {
		$this->_filename = (string) $filename;
	}

	/**
	 * @return false|string
	 */
	public function getContent() {
		if ($this->_content === null) {
			$this->load();
		}
		return $this->_content;
	}

	/**
	 * @param string $content
	 * @param boolean $autoSave
	 * @return CManager_File
	 * @throws CManager_File_Exception
	 */
	public function setContent($content, $autoSave = true) {
		$this->_content = (string) $content;
		if ($autoSave) {
			$this->save();
		}
		return $this;
	}

	/**
	 * @return CManager_File
	 * @throws CManager_File_Exception
	 */
	public function save() {
		if (!is_writable($this->getFilename())) {
			throw new CManager_File_Exception("File {$this->getFilename()} is not writable");
		}
		file_put_contents($this->getFilename(), $this->getContent());
		return $this;
	}

	/**
	 * @return CManager_File
	 * @throws CManager_File_Exception
	 */
	public function load() {
		if (!is_readable($this->getFilename())) {
			throw new CManager_File_Exception("File {$this->getFilename()} is not readable");
		}
		return file_get_contents($this->getFilename());
	}

	/**
	 * @return bool
	 */
	public function exists() {
		return file_exists($this->getFilename());
	}

	/**
	 * @return string
	 */
	public function getFilename() {
		return $this->_filename;
	}
}
