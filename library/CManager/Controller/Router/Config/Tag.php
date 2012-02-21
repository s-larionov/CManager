<?php

/**
 * @property string			$name
 * @property string|null	$pass
 * @property string			$namespace
 * @property string			$mode
 *
 * @property CManager_Controller_Router_Config_TagParam[]	$param
 */
class CManager_Controller_Router_Config_Tag extends CManager_Controller_Router_Config_Abstract {
	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var boolean
	 */
	public $pass;

	/**
	 * @var string
	 * @required
	 */
	public $namespace;

	/**
	 * @var enum(normal,background)
	 */
	public $mode = 'normal';

	/**
	 * @var CManager_Controller_Router_Config_TagParam[]
	 * @multiple
	 */
	public $param;
}
