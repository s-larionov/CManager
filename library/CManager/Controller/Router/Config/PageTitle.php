<?php

/**
 * @property string $mode
 * @property string|null $value
 */
class CManager_Controller_Router_Config_PageTitle extends CManager_Controller_Router_Config_Abstract {
	/**
	 * @var string
	 */
	public $mode = 'default';

	/**
	 * @var string
	 * @required
	 */
	public $value;
}
