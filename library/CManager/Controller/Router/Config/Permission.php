<?php

class CManager_Controller_Router_Config_Permission extends CManager_Controller_Router_Config_Abstract {
	protected $_name = 'permission';
	protected $_attributes = array(
		'name' => array(
			'namespace' => 'string',
			'required' => true
		),
		'value' => array(
			'namespace' => 'enum(allow,deny)',
			'required' => true
		),
		'pass' => array(
			'namespace' => 'enum(pass)',
			'required' => false
		)
	);
}