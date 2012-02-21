<?php

class CManager_Controller_Router_Config_Permission extends CManager_Controller_Router_Config_Abstract {
	/**
	 * @var string
	 * @required
	 */
	public $name;

	/**
	 * @var enum(allow,deny)
	 * @required
	 */
	public $value;

	/**
	 * @var boolean
	 */
	public $pass = false;

	/**
	 * @param string $role
	 * @return bool
	 */
	public function isAllow($role) {
		return ($this->name === (string) $role) && ($this->value === 'allow');
	}
}
