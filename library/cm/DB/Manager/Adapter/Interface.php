<?php

interface cm_DB_Manager_Adapter_Interface {
	/**
	 * @return void
	 */
	public function closeConnection();

	/**
	 * @return Zend_Db_Adapter_Abstract|mixed
	 */
	public function getAdapter();
}