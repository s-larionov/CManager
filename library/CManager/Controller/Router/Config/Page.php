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
			'namespace' => self::NAMESPACE_TAG_EXCLUSION,
			'required' => false,
			'single' => false,
			'inherit' => true
		),
		'route' => array(
			'namespace' => self::NAMESPACE_ROUTE,
			'required' => true,
			'single' => true
		),
		'title' => array(
			'namespace' => self::NAMESPACE_PAGE_TITLE,
			'required' => false,
			'single' => false
		),
		'tag' => array(
			'namespace' => self::NAMESPACE_TAG,
			'required' => false,
			'single' => false,
			'inherit' => true,
			'exclusion' => 'tag_exclusion'
		),
		'permission' => array(
			'namespace' => self::NAMESPACE_PERMISSION,
			'required' => false,
			'single' => false,
			'inherit' => true
		),
		'page' => array(
			'namespace' => self::NAMESPACE_PAGE,
			'required' => false,
			'single' => false
		),
		'nav' => array(
			'namespace' => self::NAMESPACE_PAGE_NAVIGATION,
			'required' => false,
			'single' => false
		)
	);
}