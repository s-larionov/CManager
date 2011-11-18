<?php

abstract class CManager_Controller_Route_Var_Abstract {
	/**
	 * @var string|string[]
	 */
	protected $_rawValue = null;

	/**
	 * @param mixed $variable
	 */
	abstract public function __construct($variable);

	/**
	 * @abstract
	 * @return boolean
	 */
	abstract public function isValidRouteVariable();

	/**
	 * @param string|string[] $rawValue
	 */
	public function setRawValue($rawValue) {
		$this->_rawValue = $rawValue;
	}

	/**
	 * @return string
	 */
	public function getRawValue() {
		return $this->_rawValue;
	}
}