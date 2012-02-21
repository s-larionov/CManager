<?php

class CManager_Controller_Router_Config_Structure extends CManager_Controller_Router_Config_Abstract {
	/**
	 * @var string
	 * @required
	 */
	public $layout;

	/**
	 * @var string
	 */
	public $namespace;

	/**
	 * @var CManager_Controller_Router_Config_Permission[]
	 * @multiple
	 */
	public $permission;

	/**
	 * @var CManager_Controller_Router_Config_Tag[]
	 * @multiple
	 */
	public $tag;

	/**
	 * @var CManager_Controller_Router_Config_Page[]
	 * @required
	 * @multiple
	 */
	public $page;
}
