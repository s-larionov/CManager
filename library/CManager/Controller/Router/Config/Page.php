<?php

class CManager_Controller_Router_Config_Page extends CManager_Controller_Router_Config_Abstract {
	/**
	 * @var string
	 * @required
	 */
	public $name;

	/**
	 * @var string
	 * @inherit
	 */
	public $layout;

	/**
	 * @var string
	 * @inherit
	 */
	public $namespace;

	/**
	 * @var int
	 */
	public $error_code = 200;

	/**
	 * @var string
	 */
	public $content_type = 'text/html; charset=utf-8';

	/**
	 * @var string
	 */
	public $redirect;

	/**
	 * @var boolean
	 */
	public $start = false;

	/**
	 * @var CManager_Controller_Router_Config_Route
	 * @required
	 */
	public $route = array();

	/**
	 * @var CManager_Controller_Router_Config_PageTitle[]
	 * @multiple
	 */
	public $title = array();

	/**
	 * @var CManager_Controller_Router_Config_TagExclusion[]
	 * @multiple
	 * @inherit
	 * @passBy pass
	 * @identifyBy name
	 */
	public $tag_exclusion = array();

	/**
	 * @var CManager_Controller_Router_Config_Tag[]
	 * @multiple
	 * @inherit
	 * @passBy pass
	 * @identifyBy name
	 * @exclusionBy tag_exclusion
	 */
	public $tag = array();

	/**
	 * @var CManager_Controller_Router_Config_Permission[]
	 * @multiple
	 * @inherit
	 * @passBy pass
	 * @identifyBy name
	 */
	public $permission = array();

	/**
	 * @var CManager_Controller_Router_Config_Page[]
	 * @multiple
	 */
	public $page = array();

	/**
	 * @var CManager_Controller_Router_Config_PageNav[]
	 * @multiple
	 */
	public $nav = array();
}
