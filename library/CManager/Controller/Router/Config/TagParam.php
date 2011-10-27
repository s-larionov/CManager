<?php

class CManager_Controller_Router_Config_TagParam extends CManager_Controller_Router_Config_Abstract {
	protected $_name = 'param';
	protected $_attributes = array(
		'name' => array(
			'namespace' => 'string',
			'required' => true
		),
		'value' => array(
			'namespace' => 'string',
			'required' => false
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