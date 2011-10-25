<?php

interface CManager_Db_Manager_Adapter_Interface {
	/**
	 * @return void
	 */
	public function closeConnection();

	/**
	 * @return Zend_Db_Adapter_Abstract|mixed
	 */
	public function getAdapter();
}