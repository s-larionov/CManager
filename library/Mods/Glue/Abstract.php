<?php

abstract class Mods_Glue_Abstract extends CManager_Controller_Action_Abstract {
	/**
	 * @var Mods_Glue_File_Abstract[]
	 */
	protected $_files = array();

	/**
	 * @var Mods_Glue_Storage_Abstract
	 */
	protected $_storage = null;

	public function run() {
		$storage = $this->getParam('storage');
		if (!is_array($storage)) {
			$storage = array($storage);
		}

		$this->setStorage(Mods_Glue_Storage_Factory::factory($storage));

		$files = $this->getParam('item', array());
		if (!is_array($files)) {
			$files = array($files);
		}
		foreach($files as $file) {

		}
	}

	/**
	 * @param Mods_Glue_Storage_Abstract $storage
	 * @return Mods_Glue_Abstract
	 */
	public function setStorage(Mods_Glue_Storage_Abstract $storage) {
		var_dump($storage);
		$this->_storage = $storage;
		return $this;
	}

	/**
	 * @return Mods_Glue_Storage_Abstract
	 * @throws Mods_Exception
	 */
	public function getStorage() {
		if ($this->_storage === null) {
			throw new Mods_Exception("Storage not configured");
		}
		return $this->_storage;
	}

	/**
	 * @param Mods_Glue_Abstract $file
	 * @param array $attributes
	 * @return Mods_Glue_Abstract
	 */
	public function addFile(Mods_Glue_Abstract $file, array $attributes = array()) {



		return $this;
	}

	/**
	 * Получение содержимого файла. Здесь его можно минимизировать,
	 * почистить от всего лишнего и т.д.
	 *
	 * @param string $filename
	 * @return string|false
	 */
	public function getFileContent($filename) {
		if (file_exists($filename)) {
			return file_get_contents($filename);
		}
		return false;
	}
}