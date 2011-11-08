<?php

abstract class Mods_Glue_Abstract extends CManager_Controller_Action_Abstract {
	/**
	 * @var Mods_Glue_Glue
	 */
	protected $_glue = null;

	public function run() {
		$this->getGlue()->setStorage($this->_createStorage());

		$items = $this->getParam('item');
		if ($items === null) {
			throw new Mods_Glue_Exception("Items is empty");
		}
		if (!is_array($items) || !CManager_Helper_Array::isNumberedArray($items)) {
			$items = array($items);
		}
		foreach($items as $item) {
			$this->getGlue()->addFile(Mods_Glue_Glue::newFile($this->getConfig()->class, $item));
		}

		try {
			$this->getGlue()->compile();
		} catch (Mods_Glue_Exception $e) {}

		$this->sendContent($this->getGlue()->render());
	}

	/**
	 * @return Mods_Glue_Storage_Interface
	 */
	protected function _createStorage() {
		$config = /** @var Zend_Config $config */ $this->getConfig()->storage;
		if (!($config instanceof Zend_Config)) {
			throw new Mods_Glue_Exception("Storage not configured or wrong configuration data");
		}
		$adapter = (string) $config->adapter;
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