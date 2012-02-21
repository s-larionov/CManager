<?php

/**
 * @property string $name
 * @property string|null $pass
 */
class CManager_Controller_Router_Config_TagExclusion extends CManager_Controller_Router_Config_Abstract {
	/**
	 * @var string
	 * @required
	 */
	public $name;

	/**
	 * @var boolean
	 */
	public $pass = false;
}
