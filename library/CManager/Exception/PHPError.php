<?php

class CManager_Exception_PHPError extends CManager_Exception {
	public function __construct($code, $string, $file, $line) {
		static $errorTypes = array(
			E_ERROR					=> "Error",
			E_WARNING				=> "Warning",
			E_PARSE					=> "Parsing Error",
			E_NOTICE				=> "Notice",
			E_CORE_ERROR			=> "Core Error",
			E_CORE_WARNING			=> "Core Warning",
			E_COMPILE_ERROR			=> "Compile Error",
			E_COMPILE_WARNING		=> "Compile Warning",
			E_USER_ERROR			=> "User Error",
			E_USER_WARNING			=> "User Warning",
			E_USER_NOTICE			=> "User Notice",
			E_STRICT				=> "Runtime Notice",
			E_RECOVERABLE_ERROR		=> "Catchable Fatal Error"
		);

		$errorType = isset($errorTypes[$code]) ? $errorTypes[$code] : 'Unknown Error ('. $code .')';

		$message = '<strong>'. $errorType .'.</strong> '. $string;
		$this->file = $file;
		$this->line = $line;

		parent::__construct($message, true);
	}
}