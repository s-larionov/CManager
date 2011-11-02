<?php

class Mods_Glue_Storage_Filesystem extends Mods_Glue_Storage_Abstract {

	/**
	 * @var string
	 */
	protected $_fromPath = null;

	/**
	 * @var string
	 */
	protected $_compilePath = null;

	/**
	 * @var string
	 */
	protected $_documentRoot = null;

	/**
	 * @param array $config
	 */
	public function __construct(array $config) {
		if (isset($config['document_root'])) {
			$this->setDocumentRoot($config['document_root']);
		}
		if (!isset($config['from_path'])) {
			throw new Mods_Glue_Storage_Exception("Parameter 'from_path' is required");
		}
		$config['from_path'] = str_replace('(:root)', $this->getDocumentRoot(), $config['from_path']);
		if (!is_readable($config['from_path'])) {
			throw new Mods_Glue_Storage_Exception("Path {$config['from_path']} is not readable");
		}
		if (!isset($config['compile_path'])) {
			throw new Mods_Glue_Storage_Exception("Parameter 'compile_path' is required");
		}
		$config['compile_path'] = str_replace('(:root)', $this->getDocumentRoot(), $config['compile_path']);
		if (!is_writable($config['compile_path'])) {
			throw new Mods_Glue_Storage_Exception("Path {$config['compile_path']} is not writable");
		}
		if (!isset($config['link'])) {
			throw new Mods_Glue_Storage_Exception("Parameter 'link' is required");
		}
		$this->_path = rtrim($config['path'], '/') . '/';
	}

	public function putFile(Mods_Glue_File_Abstract $file) {
		// TODO: Implement putFile() method.
	}

	/**
	 * @return string
	 * @throws Mods_Glue_Exception
	 */
	public function getDocumentRoot() {
		if ($this->_documentRoot === null) {
			$config = CManager_Registry::getConfig();
			if ($config->root) {
				$this->setRoot($config->root);
			} else if (defined('APPLICATION_ROOT')) {
				$this->setRoot(APPLICATION_ROOT);
			} else if (isset($_SERVER['DOCUMENT_ROOT'])) {
				$this->setRoot($_SERVER['DOCUMENT_ROOT']);
			} else {
				throw new Mods_Glue_Exception("Document root is not defined");
			}
		}
		return $this->_root;
	}

	/**
	 * @param string $documentRoot
	 * @return Mods_Glue_Storage_Filesystem
	 */
	public function setDocumentRoot($documentRoot) {
		if (!file_exists($documentRoot)) {
			throw new Mods_Glue_Storage_Exception("Document root {$documentRoot} does not exists");
		}
		$this->_documentRoot = (string) $documentRoot;
	}
}