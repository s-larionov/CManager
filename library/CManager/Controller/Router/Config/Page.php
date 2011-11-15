<?php

/**
 * @property string			$name
 * @property string			$layout
 * @property string			$namespace
 * @property int			$error_code
 * @property string			$content_type
 * @property string|null	$redirect
 * @property boolean		$start
 *
 * @property CManager_Controller_Router_Config_TagExclusion[]	$tag_exclusion
 * @property CManager_Controller_Router_Config_Route			$route
 * @property CManager_Controller_Router_Config_PageTitle[]		$title
 * @property CManager_Controller_Router_Config_Tag[]			$tag
 * @property CManager_Controller_Router_Config_Permission[]		$permission
 * @property CManager_Controller_Router_Config_Page[]			$page
 * @property CManager_Controller_Router_Config_PageNav[]		$nav
 */
class CManager_Controller_Router_Config_Page extends CManager_Controller_Router_Config_Abstract {
	protected $_name = 'page';
	protected $_attributes = array(
		'name' => array(
			'namespace' => 'string',
			'required' => true
		),
		'layout' => array(
			'namespace' => 'string',
			'required' => false,
			'inherit' => true
		),
		'namespace' => array(
			'namespace' => 'string',
			'required' => false,
			'inherit' => true
		),
		'error_code' => array(
			'namespace' => 'int',
			'required' => false,
			'default' => 200
		),
		'content_type' => array(
			'namespace' => 'string',
			'required' => false,
			'default' => 'text/html; charset=utf8'
		),
		'redirect' => array(
			'namespace' => 'string',
			'required' => false
		),
		'start' => array(
			'namespace' => 'boolean',
			'required' => false,
			'default' => false
		)
	);
	protected $_children = array(
		'tag_exclusion' => array(
			'namespace' => 'CManager_Controller_Router_Config_TagExclusion',
			'required' => false,
			'single' => false,
			'inherit' => true
		),
		'route' => array(
			'namespace' => 'CManager_Controller_Router_Config_Route',
			'required' => true,
			'single' => true
		),
		'title' => array(
			'namespace' => 'CManager_Controller_Router_Config_PageTitle',
			'required' => false,
			'single' => false
		),
		'tag' => array(
			'namespace' => 'CManager_Controller_Router_Config_Tag',
			'required' => false,
			'single' => false,
			'inherit' => true,
			'exclusion' => 'tag_exclusion'
		),
		'permission' => array(
			'namespace' => 'CManager_Controller_Router_Config_Permission',
			'required' => false,
			'single' => false,
			'inherit' => true
		),
		'page' => array(
			'namespace' => 'CManager_Controller_Router_Config_Page',
			'required' => false,
			'single' => false
		),
		'nav' => array(
			'namespace' => 'CManager_Controller_Router_Config_PageNav',
			'required' => false,
			'single' => false
		)
	);
}