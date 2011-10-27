<?php

class CManager_Controller_Router_Config_Tag extends CManager_Controller_Router_Config_Abstract {
	protected $_name = 'tag';
	protected $_attributes = array(
		'name' => array(
			'namespace' => 'string',
			'required' => true
		),
		'pass' => array(
			'namespace' => 'enum(pass)',
			'required' => false
		),
		'namespace' => array(
			'namespace' => 'string',
			'required' => true
		),
		'mode' => array(
			'namespace' => 'enum(normal,background)',
			'required' => false,
			'default' => 'normal'
		)
	);
	protected $_children = array(
		'param' => array(
			'namespace' => 'TagParam',
			'required' => false,
			'single' => false
		)
	);
}