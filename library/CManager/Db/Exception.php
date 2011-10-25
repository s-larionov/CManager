<?php

class CManager_Db_Exception extends CManager_Exception {
	/*
	 *	$errorInfo = array(
	 *		0 => SQLSTATE error code (a five-character alphanumeric identifier defined in the ANSI SQL standard).
	 *		1 => Driver-specific error code.
	 *		2 => Driver-specific error message.
	 *	);
	 */
	public function __construct($errorInfo, $sql) {
		parent::__construct('[db] '. $errorInfo[2] ."\n<br/>[sql] ". $sql, true);
	}

}