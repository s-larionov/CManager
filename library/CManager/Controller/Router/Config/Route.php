<?php

/**
 * @property string $url
 * @property CManager_Controller_Router_Config_RouteVar[] $var
 */
class CManager_Controller_Router_Config_Route extends CManager_Controller_Router_Config_Abstract {
	/**
	 * @var string
	 * @required
	 */
	public $url;

	/**
	 * @var CManager_Controller_Router_Config_RouteVar[]
	 * @multiple
	 */
	public $var;
}
