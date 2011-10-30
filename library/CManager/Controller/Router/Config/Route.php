<?php

class CManager_Controller_Router_Config_Route extends CManager_Controller_Router_Config_Abstract {
	protected $_name = 'route';
	protected $_attributes = array(
		'url' => array(
			'namespace' => 'string',
			'required' => true
		)
	);
	protected $_children = array(
		'var' => array(
			'namespace' => self::NAMESPACE_ROUTE_VAR,
			'required' => false,
			'single' => false
		)
	);
}