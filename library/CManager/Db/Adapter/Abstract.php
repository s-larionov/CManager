<?php

abstract class CManager_Db_Adapter_Abstract {
	/**
	 * @return void
	 */
	abstract public function closeConnection();

	/**
	 * @return Zend_Db_Adapter_Abstract|mixed
	 */
	abstract public function getAdapter();
}