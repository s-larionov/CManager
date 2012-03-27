<?php

class CManager_Db_Adapter_Zend extends CManager_Db_Adapter_Abstract {
	/**
	 * @var Zend_Config
	 */
	protected $_config = null;

	/**
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected $adapter = null;

	/**
	 * @param Zend_Config $config
	 */
	public function __construct($config) {
		$this->_config = $config;
	}

	/**
	 * @return Zend_Db_Adapter_Abstract
	 */
	public function getAdapter() {
		if ($this->adapter === null) {
			$this->adapter = Zend_Db::factory($this->getConfig()->get('driver'), $this->getConfig());
		}
		return $this->adapter;
	}

	/**
	 * @return void
	 */
	public function closeConnection() {
		$this->getAdapter()->closeConnection();
	}

	/**
	 * @return Zend_Config
	 */
	public function getConfig() {
		return $this->_config;
	}

	/**
	 * @param Zend_Config $config
	 * @return CManager_Db_Adapter_Zend
	 */
	public function setConfig(Zend_Config $config) {
		$this->_config = $config;
		return $this;
	}
}
