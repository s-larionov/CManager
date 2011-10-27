<?php

class CManager_Controller_Router_Config_PageNav extends CManager_Controller_Router_Config_Abstract {
	protected $_name = 'nav';
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
}