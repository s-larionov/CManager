<?php

class CManager_Db_Manager_Adapter_MondoDbNative implements CManager_Db_Manager_Adapter_Interface {
	/**
	 * @var Zend_Config
	 */
	protected $_config = null;

	/**
	 * @var Mongo
	 */
	protected $_adapter = null;

	/**
	 * @var MongoDB
	 */
	protected $_database = null;

	/**
	 * @param Zend_Config $config
	 */
	public function __construct($config) {
		$this->_config = $config;
	}

	/**
	 * @return MongoDB
	 */
	public function getAdapter() {
		if ($this->_adapter === null) {
			$config = $this->_config->toArray();

			if (!isset($config['server'])) {
				throw new CManager_Db_Manager_Exception('Mongo error: Parameter "server" is undefined');
			}

			if (!isset($config['dbname'])) {
				throw new CManager_Db_Manager_Exception('Mongo error: Parameter "dbname" is undefined');
			}

			if (!isset($config['options'])) {
				$config['options'] = array(
					'connect' => true
				);
			}

			try {
				$this->_adapter = new Mongo($config['server'], $config['options']);
				$this->_database = $this->_adapter->selectDB($config['dbname']);
			} catch (MongoConnectionException $e) {

				throw new CManager_Db_Manager_Exception('Mongo error: '. $e->getMessage());
			}

		}

		return $this->_database;
	}

	/**
	 * @return void
	 */
	public function closeConnection()
	{
		$this->_adapter->close();
	}
}