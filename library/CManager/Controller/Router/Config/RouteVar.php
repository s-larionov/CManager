<?php

class CManager_Controller_Router_Config_RouteVar extends CManager_Controller_Router_Config_Abstract {
	protected $_name = 'var';
	protected $_attributes = array(
		'name' => array(
			'namespace' => 'string',
			'required' => true
		),
		'rule' => array(
			'namespace' => 'string',
			'required' => true
		),
		'explode' => array(
			'namespace' => 'string',
			'required' => false
		),
		'default' => array(
			'namespace' => 'string',
			'required' => false
		),
		'pattern' => array(
			'namespace' => 'string',
			'required' => false
		),
		'namespace' => array(
			'namespace' => 'string',
			'required' => false
		)
	);
}