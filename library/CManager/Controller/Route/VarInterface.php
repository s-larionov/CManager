<?php

interface CManager_Controller_Route_VarInterface {
	/**
	 * @param mixed $variable
	 */
	public function __construct($variable);

	/**
	 * @abstract
	 * @return boolean
	 */
	public function isValidRouteVariable();
}