<?php

class CManager_Controller_Router_Config_Route extends CManager_Controller_Router_Config_Abstract {
	/**
	 * @var string
	 * @require
	 */
	public $name;

	/**
	 * @var string
	 * @require
	 */
	public $url;

	/**
	 * @var CManager_Controller_Router_Config_RouteVar[]
	 * @multiple
	 *
	 * @inherit
	 * @identifyBy name
	 * @passBy pass
	 */
	public $var;

	/**
	 * @var CManager_Controller_Router_Config_Route[]
	 * @multiple
	 */
	public $route;

	/**
	 * @var int
	 */
	public $error_code = 200;

}
