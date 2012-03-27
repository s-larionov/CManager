<?php

abstract class Mods_Glue_Abstract extends CManager_Controller_Action_Cache {
	/**
	 * @var boolean
	 */
	protected $_cacheEnabled = true;

	/**
	 * @var Mods_Glue_Glue
	 */
	protected $_glue = null;

	protected function _getCacheKey() {
		$fileNames = '';
		foreach($this->getGlue()->getFiles() as $file) {
			$fileNames .= $file->getFilename();
		}
		$hash = md5($fileNames);
		return "[*][glue-{$this->getTag()->name}][{$hash}]";
	}

	protected function _getCacheValidateHash() {
		$hash = '';
		$storageConfig = /** @var Zend_Config $storageConfig */ $this->getConfig()->get('storage');
		if ($storageConfig instanceof Zend_Config) {
			$hash = md5(implode('', $storageConfig->toArray()));
		}
		return $hash;
	}

	/**
	 * @throws Mods_Glue_Exception
	 * @return string|void
	 */
	public function run() {
		$this->tryLoadFromCache();

		$this->getGlue()->setStorage($this->_createStorage());

		$items = $this->getParam('item');
		if ($items === null) {
			throw new Mods_Glue_Exception("Items is empty");
		}
		if (!is_array($items) || !CManager_Helper_Array::isSimpleArray($items)) {
			$items = array($items);
		}
		foreach($items as $item) {
			$this->getGlue()->addFile(Mods_Glue_Glue::newFile($this->getConfig()->get('class'), $item));
		}

		try {
			$this->getGlue()->compile();
		} catch (Mods_Glue_Exception $e) {}

		$this->sendContent($this->getGlue()->render());
	}

	/**
	 * @throws Mods_Glue_Exception
	 * @return \Mods_Glue_Storage_Interface
	 */
	protected function _createStorage() {
		$config = /** @var Zend_Config $config */ $this->getConfig()->get('storage');
		if (!($config instanceof Zend_Config)) {
			throw new Mods_Glue_Exception("Storage not configured or wrong configuration data");
		}
		$adapter = (string) $config->get('adapter');
		if ($adapter == '') {
			throw new Mods_Glue_Exception("Storage adapter not configured");
		}
		$configArray = $config->toArray();
		try {
			$storage = CManager_Helper_Object::newInstance($adapter, 'Mods_Glue_Storage_Interface', array($configArray));
		} catch (CManager_Exception $e) {
			try {
				$storage = CManager_Helper_Object::newInstance("Mods_Glue_Storage_{$adapter}", 'Mods_Glue_Storage_Interface', array($configArray));
			} catch (CManager_Exception $e) {
				throw new Mods_Glue_Exception("Adapter '{$adapter}' not found");
			}
		}
		return $storage;
	}

	/**
	 * @return Mods_Glue_Glue
	 */
	public function getGlue() {
		if ($this->_glue === null) {
			$this->_glue = new Mods_Glue_Glue($this->getConfig()->toArray());
		}
		return $this->_glue;
	}

	/**
	 * @abstract
	 * @return Zend_Config
	 */
	abstract public function getConfig();
}
