<?php

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
			'default' => 'text/html'
		),
		'redirect' => array(
			'namespace' => 'string',
			'required' => false
		)
	);
	protected $_children = array(
		'tag_exclusion' => array(
			'namespace' => 'TagExclusion',
			'required' => false,
			'single' => false,
			'inherit' => true
		),
		'route' => array(
			'namespace' => 'Route',
			'required' => true,
			'single' => true
		),
		'title' => array(
			'namespace' => 'PageTitle',
			'required' => false,
			'single' => false
		),
		'tag' => array(
			'namespace' => 'Tag',
			'required' => false,
			'single' => false,
			'inherit' => true,
			'exclusion' => 'tag_exclusion'
		),
		'permission' => array(
			'namespace' => 'Permission',
			'required' => false,
			'single' => false,
			'inherit' => true
		),
		'page' => array(
			'namespace' => 'Page',
			'required' => false,
			'single' => false
		),
		'nav' => array(
			'namespace' => 'PageNav',
			'required' => false,
			'single' => false
		)
	);
}