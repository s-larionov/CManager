<?php

/**
 * @property string $name
 * @property string|null $value
 * @property CManager_Controller_Router_Config_TagParam[] $param
 */
class CManager_Controller_Router_Config_TagParam extends CManager_Controller_Router_Config_Abstract {
	/**
	 * @var string
	 * @required
	 */
	public $name;

	/**
	 * @var string
	 */
	public $value;

	/**
	 * @var CManager_Controller_Router_Config_TagParam[]
	 * @multiple
	 */
	public $param;
}
