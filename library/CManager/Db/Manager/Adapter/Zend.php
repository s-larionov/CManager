<?php

class CManager_Db_Manager_Adapter_Zend implements CManager_Db_Manager_Adapter_Interface {
	/**
	 * @var Zend_Config
	 */
	protected $_config = null;

	/**
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected $_adapter = null;

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
		if ($this->_adapter === null) {
			$this->_adapter = Zend_Db::factory($this->_config->driver, $this->_config);
			$this->_adapter->query('SET NAMES utf8');
		}

		return $this->_adapter;
	}

	/**
	 * @return void
	 */
	public function closeConnection() {
		$this->getAdapter()->closeConnection();
	}
}